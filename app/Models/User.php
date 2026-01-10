<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'persona_id',
        'name',
        'avatar',
        'role',
        'email',
        'password',
        'estado',
    ];

    // Rel ación con Persona
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function ordenesCreadas()
    {
        return $this->hasMany(OrdenProduccion::class, 'created_by');
    }

    public function produccionDiaria()
    {
        return $this->hasMany(ProduccionDiaria::class, 'operario_id');
    }

    public function movimientosRegistrados()
    {
        return $this->hasMany(MovimientoInsumo::class, 'created_by');
    }

    public function pedidosCreados()
    {
        return $this->hasMany(Pedido::class, 'user_id');
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
    ];


}
