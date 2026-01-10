<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Direccion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'direccion';

    protected $fillable = [
        'persona_id',
        'direccion',
        'ciudad',
        'tipo',
        'es_principal',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
    ];

    /**
     * Tipos de dirección disponibles
     */
    public const TIPOS = [
        'casa' => 'Casa',
        'trabajo' => 'Trabajo',
        'envio' => 'Envío',
    ];

    /**
     * Relación con Persona
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    /**
     * Scope para obtener solo direcciones principales
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

    /**
     * Obtener dirección completa
     */
    public function getDireccionCompletaAttribute()
    {
        return $this->direccion . ($this->ciudad ? ', ' . $this->ciudad : '');
    }
}
