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
        'contacto',
        'telefono_contacto',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    /**
     * Relación con Persona (para todos los proveedores)
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

    public function compras()
    {
        return $this->hasMany(Compra::class);
    }

    // ============================================
    // ACCESSORS para datos unificados
    // ============================================

    public function getNombreCompletoAttribute()
    {
        return $this->persona ? trim($this->persona->nombre_completo) : null;
    }

    public function getDocumentoAttribute()
    {
        return $this->persona ? $this->persona->documento_completo : null;
    }

    public function getTelefonoUnificadoAttribute()
    {
        return $this->persona ? $this->persona->telefono_principal : null;
    }

    public function getEmailUnificadoAttribute()
    {
        return $this->persona ? $this->persona->email : null;
    }

    public function getDireccionUnificadaAttribute()
    {
        if (!$this->persona) return null;
        $dir = $this->persona->direccion_principal;
        return $dir ? $dir->direccion : null;
    }

    public function esNatural()
    {
        return $this->tipo_proveedor === 'natural';
    }

    public function esJuridico()
    {
        return $this->tipo_proveedor === 'juridico';
    }
}
