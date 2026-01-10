<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Telefono extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'telefono';

    protected $fillable = [
        'persona_id',
        'numero',
        'tipo',
        'es_principal',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
    ];

    /**
     * Tipos de teléfono disponibles
     */
    public const TIPOS = [
        'movil' => 'Móvil',
        'casa' => 'Casa',
        'trabajo' => 'Trabajo',
    ];

    /**
     * Relación con Persona
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    /**
     * Scope para obtener solo teléfonos principales
     */
    public function scopePrincipal($query)
    {
        return $query->where('es_principal', true);
    }

    /**
     * Obtener el nombre del tipo
     */
    public function getTipoNombreAttribute()
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }
}
