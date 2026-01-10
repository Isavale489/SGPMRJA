<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Persona extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'persona';

    protected $fillable = [
        'nombre',
        'apellido',
        'documento_identidad',
        'tipo_documento',
        'email',
        'estado_persona',
        'fecha_nacimiento',
        'genero'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date'
    ];

    // Relaciones
    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function empleado()
    {
        return $this->hasOne(Empleado::class);
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class);
    }

    /**
     * Relación con teléfonos (normalizada)
     */
    public function telefonos()
    {
        return $this->hasMany(Telefono::class);
    }

    /**
     * Relación con direcciones (normalizada)
     */
    public function direcciones()
    {
        return $this->hasMany(Direccion::class);
    }

    // Accessors
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} {$this->apellido}";
    }

    public function getDocumentoCompletoAttribute()
    {
        return "{$this->tipo_documento}{$this->documento_identidad}";
    }

    /**
     * Obtener teléfono principal (de la tabla normalizada)
     */
    public function getTelefonoPrincipalAttribute()
    {
        $telefonoPrincipal = $this->telefonos()->where('es_principal', true)->first();
        return $telefonoPrincipal ? $telefonoPrincipal->numero : null;
    }

    /**
     * Obtener dirección principal (de la tabla normalizada)
     */
    public function getDireccionPrincipalAttribute()
    {
        return $this->direcciones()->where('es_principal', true)->first();
    }
}
