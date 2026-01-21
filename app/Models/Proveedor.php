<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'proveedor';

    protected $fillable = [
        'tipo_proveedor',
        'persona_id',
        'razon_social',
        'rif',
        'direccion',
        'telefono',
        'email',
        'contacto',
        'telefono_contacto',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    /**
     * Relación con Persona (solo para proveedores naturales)
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    /**
     * Relación con Insumos
     */
    public function insumos()
    {
        return $this->hasMany(Insumo::class);
    }

    // ============================================
    // ACCESSORS para datos unificados
    // ============================================

    /**
     * Obtener nombre/razón social según tipo
     */
    public function getNombreCompletoAttribute()
    {
        if ($this->tipo_proveedor === 'natural' && $this->persona) {
            return $this->persona->nombre_completo;
        }
        return $this->razon_social;
    }

    /**
     * Obtener documento (RIF o cédula) según tipo
     */
    public function getDocumentoAttribute()
    {
        if ($this->tipo_proveedor === 'natural' && $this->persona) {
            return $this->persona->documento_completo;
        }
        return $this->rif;
    }

    /**
     * Obtener teléfono según tipo
     */
    public function getTelefonoUnificadoAttribute()
    {
        if ($this->tipo_proveedor === 'natural' && $this->persona) {
            return $this->persona->telefono_principal;
        }
        return $this->telefono;
    }

    /**
     * Obtener email según tipo
     */
    public function getEmailUnificadoAttribute()
    {
        if ($this->tipo_proveedor === 'natural' && $this->persona) {
            return $this->persona->email;
        }
        return $this->email;
    }

    /**
     * Obtener dirección según tipo
     */
    public function getDireccionUnificadaAttribute()
    {
        if ($this->tipo_proveedor === 'natural' && $this->persona) {
            $dir = $this->persona->direccion_principal;
            return $dir ? $dir->direccion : null;
        }
        return $this->direccion;
    }

    /**
     * Verificar si es proveedor natural
     */
    public function esNatural()
    {
        return $this->tipo_proveedor === 'natural';
    }

    /**
     * Verificar si es proveedor jurídico
     */
    public function esJuridico()
    {
        return $this->tipo_proveedor === 'juridico';
    }
}
