<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empleado extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'empleado';

    protected $fillable = [
        'persona_id',
        'codigo_empleado',
        'fecha_ingreso',
        'cargo',
        'departamento',
        'salario',
        'estado'
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'salario' => 'decimal:2',
        'estado' => 'boolean'
    ];

    // Relación
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    // Accessors para compatibilidad con tablas normalizadas
    public function getNombreAttribute()
    {
        return $this->persona ? $this->persona->nombre : null;
    }

    public function getApellidoAttribute()
    {
        return $this->persona ? $this->persona->apellido : null;
    }

    public function getEmailAttribute()
    {
        return $this->persona ? $this->persona->email : null;
    }

    /**
     * Obtener teléfono principal usando la tabla normalizada
     */
    public function getTelefonoAttribute()
    {
        return $this->persona ? $this->persona->telefono_principal : null;
    }

    /**
     * Obtener todos los teléfonos del empleado
     */
    public function getTelefonosAttribute()
    {
        return $this->persona ? $this->persona->telefonos : collect();
    }

    public function getDocumentoAttribute()
    {
        if ($this->persona) {
            return $this->persona->tipo_documento . $this->persona->documento_identidad;
        }
        return null;
    }

    /**
     * Obtener dirección principal usando la tabla normalizada
     */
    public function getDireccionAttribute()
    {
        $direccionPrincipal = $this->persona ? $this->persona->direccion_principal : null;
        return $direccionPrincipal ? $direccionPrincipal->direccion : null;
    }

    /**
     * Obtener todas las direcciones del empleado
     */
    public function getDireccionesAttribute()
    {
        return $this->persona ? $this->persona->direcciones : collect();
    }

    /**
     * Obtener ciudad de la dirección principal
     */
    public function getCiudadAttribute()
    {
        $direccionPrincipal = $this->persona ? $this->persona->direccion_principal : null;
        return $direccionPrincipal ? $direccionPrincipal->ciudad : null;
    }
}
