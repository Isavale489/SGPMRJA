<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * FEAT-006 — Inspección de Control de Calidad de una Orden de Producción.
 */
class ControlCalidad extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'control_calidad';

    protected $fillable = [
        'orden_produccion_id',
        'inspector_id',
        'cantidad_inspeccionada',
        'cantidad_aprobada',
        'cantidad_rechazada',
        'resultado',
        'observaciones',
        'fecha_inspeccion',
    ];

    protected $casts = [
        'cantidad_inspeccionada' => 'integer',
        'cantidad_aprobada' => 'integer',
        'cantidad_rechazada' => 'integer',
        'fecha_inspeccion' => 'datetime',
    ];

    /** Resultados posibles del veredicto de calidad. */
    public const RESULTADOS = [
        'aprobado' => 'Aprobado',
        'rechazado' => 'Rechazado',
        'observado' => 'Aprobado con observaciones',
    ];

    public function ordenProduccion()
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_produccion_id');
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
