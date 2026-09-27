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
        'estado_id',
        'municipio_id',
    ];

    /**
     * Exponer estado/ciudad (nombres del catálogo) al serializar, igual que
     * cuando eran columnas de texto.
     */
    protected $appends = ['estado', 'ciudad'];

    /**
     * La ubicación (estado/municipio) se carga siempre para resolver los
     * accessors `estado` y `ciudad` sin incurrir en consultas N+1.
     */
    protected $with = ['estadoRel', 'municipioRel'];

    /**
     * Relación con Persona
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    /**
     * Relaciones con el catálogo geográfico.
     */
    public function estadoRel()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    public function municipioRel()
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }

    /**
     * Accessors de compatibilidad: el resto del sistema lee `$direccion->estado`
     * y `$direccion->ciudad` como texto. Devuelven el nombre del catálogo.
     */
    public function getEstadoAttribute()
    {
        return $this->estadoRel?->nombre;
    }

    public function getCiudadAttribute()
    {
        return $this->municipioRel?->nombre;
    }

    /**
     * Resolver nombres (estado/municipio) a sus IDs del catálogo.
     * Devuelve ['estado_id' => ?int, 'municipio_id' => ?int].
     */
    public static function resolverUbicacion(?string $estadoNombre, ?string $municipioNombre): array
    {
        $estadoId = null;
        $municipioId = null;

        if (!empty($estadoNombre)) {
            $estadoId = Estado::where('nombre', $estadoNombre)->value('id');
        }
        if ($estadoId && !empty($municipioNombre)) {
            $municipioId = Municipio::where('estado_id', $estadoId)
                ->where('nombre', $municipioNombre)
                ->value('id');
        }

        return ['estado_id' => $estadoId, 'municipio_id' => $municipioId];
    }
}
