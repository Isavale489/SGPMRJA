<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Catálogo de género de prenda (Dama / Caballero / Unisex).
 * Mismo patrón que Color y Talla: dimensión de línea elegida por el cliente.
 */
class Genero extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'genero';

    protected $fillable = [
        'nombre',
        'etiqueta',
        'icono',
        'orden',
        'activo',
    ];

    protected $casts = [
        'orden' => 'integer',
        'activo' => 'boolean',
    ];

    /**
     * Scope: solo géneros activos.
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
}
