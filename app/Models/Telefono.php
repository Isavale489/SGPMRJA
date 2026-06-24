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

    /**
     * Sincroniza el conjunto de teléfonos de una persona con el set recibido
     * (reemplazo completo). Garantiza exactamente un principal. Si el set viene
     * vacío, NO toca los teléfonos actuales. Usado por Cliente/Empleado/Proveedor.
     *
     * @param array $telefonos lista de ['numero', 'tipo', 'es_principal']
     */
    public static function sincronizar(Persona $persona, array $telefonos): void
    {
        $telefonos = array_values(array_filter($telefonos, fn ($t) => !empty($t['numero'])));
        if (empty($telefonos)) {
            return;
        }

        // Normalizar a exactamente un principal
        $principalAsignado = false;
        foreach ($telefonos as &$t) {
            $esPrincipal = !empty($t['es_principal']) && !$principalAsignado;
            $t['es_principal'] = $esPrincipal;
            if ($esPrincipal) {
                $principalAsignado = true;
            }
        }
        unset($t);
        if (!$principalAsignado) {
            $telefonos[0]['es_principal'] = true;
        }

        // Reemplazo completo (forceDelete evita acumular soft-deleted)
        $persona->telefonos()->forceDelete();
        foreach ($telefonos as $t) {
            self::create([
                'persona_id' => $persona->id,
                'numero' => $t['numero'],
                'tipo' => $t['tipo'] ?? 'movil',
                'es_principal' => $t['es_principal'],
            ]);
        }
    }
}
