<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'persona_id',
        'avatar',
        'role_id',
        'email',
        'password',
        'estado',
        'recovery_locked_until',
        'recovery_failed_attempts',
        'recovery_must_reset_questions',
        'password_reset_by_admin',
    ];

    /**
     * Relación con Persona (datos personales normalizados)
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    /**
     * Nombre completo desde la relación persona
     */
    public function getNombreCompletoAttribute()
    {
        return $this->persona ? $this->persona->nombre_completo : $this->name;
    }

    /**
     * Teléfono principal desde la relación persona
     */
    public function getTelefonoAttribute()
    {
        return $this->persona ? $this->persona->telefono_principal : null;
    }

    /**
     * Dirección principal desde la relación persona
     */
    public function getDireccionAttribute()
    {
        return $this->persona ? $this->persona->direccion_principal : null;
    }

    /**
     * URL segura del avatar: verifica existencia física del archivo.
     * Si no existe, devuelve un avatar generado con la inicial del usuario.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }

        // Fallback: avatar con inicial vía ui-avatars.com
        $name = urlencode($this->name ?? 'U');
        return "https://ui-avatars.com/api/?name={$name}&color=FFFFFF&background=1e3c72&bold=true&size=128";
    }

    public function ordenesCreadas()
    {
        return $this->hasMany(OrdenProduccion::class, 'created_by');
    }

    public function movimientosRegistrados()
    {
        return $this->hasMany(MovimientoInsumo::class, 'created_by');
    }

    public function pedidosCreados()
    {
        return $this->hasMany(Pedido::class, 'user_id');
    }

    /**
     * Rol administrable asignado al usuario (FEAT-005).
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'role_id');
    }

    /**
     * Accessor de compatibilidad: expone el nombre del rol como `$user->role`,
     * tal como lo esperaban las vistas/controllers previos al modelo de roles
     * administrables. Null-safe: si el rol fue soft-deleted o no existe, devuelve
     * null en vez de romper las vistas (§7 del spec FEAT-005).
     */
    public function getRoleAttribute()
    {
        return $this->rol?->nombre;
    }

    // Métodos para verificar roles
    public function isAdmin()
    {
        return $this->role === 'Administrador';
    }

    public function isSupervisor()
    {
        return $this->role === 'Supervisor';
    }


    // Método para verificar múltiples roles
    public function hasRole($roles)
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'recovery_locked_until' => 'datetime',
        'recovery_must_reset_questions' => 'boolean',
        'password_reset_by_admin' => 'boolean',
    ];

    /**
     * Preguntas de seguridad configuradas por el usuario (1 a 3).
     */
    public function recoveryQuestions()
    {
        return $this->hasMany(UserRecoveryQuestion::class)->orderBy('orden');
    }

    /**
     * Bitácora de intentos de recuperación.
     */
    public function recoveryAttempts()
    {
        return $this->hasMany(RecoveryAttempt::class);
    }

    /**
     * Indica si el usuario tiene las 3 preguntas de seguridad configuradas.
     */
    public function hasRecoveryQuestionsConfigured(): bool
    {
        return $this->recoveryQuestions()->count() === 3;
    }

    /**
     * Indica si el usuario está bajo bloqueo temporal de recuperación.
     */
    public function isRecoveryLocked(): bool
    {
        return $this->recovery_locked_until !== null
            && $this->recovery_locked_until->isFuture();
    }

    /**
     * Indica si el usuario alcanzó el bloqueo total (requiere admin).
     */
    public function isRecoveryHardLocked(): bool
    {
        return $this->recovery_failed_attempts >= config('recovery_questions.max_attempts_hard_lock', 10);
    }
}
