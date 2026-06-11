<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TasaCambio extends Model
{
    use HasFactory;

    protected $table = 'tasa_cambio';

    protected $fillable = [
        'moneda',
        'valor',
        'fecha_bcv',
        'fuente',
    ];

    protected $casts = [
        'valor' => 'decimal:4',
        'fecha_bcv' => 'date',
    ];

    /**
     * Obtiene la tasa más reciente de una moneda
     */
    public static function obtenerTasaActual(string $moneda = 'USD'): ?self
    {
        return self::where('moneda', strtoupper($moneda))
            ->orderBy('fecha_bcv', 'desc')
            ->first();
    }

    /**
     * Obtiene el valor de la tasa USD actual o un valor por defecto
     */
    public static function obtenerValorUsd(): float
    {
        $tasa = self::obtenerTasaActual('USD');
        return $tasa ? (float) $tasa->valor : 0.00;
    }

    /**
     * Tasa vigente para una fecha dada: la del día exacto si existe; si no,
     * la última publicada ANTES de esa fecha. El BCV publica en días hábiles
     * y esa tasa rige hasta la siguiente publicación, así que una compra en
     * sábado/feriado usa la tasa del último día hábil. Devuelve null si no hay
     * ninguna tasa registrada en o antes de la fecha (p. ej. fechas muy viejas).
     */
    public static function tasaVigente(string $fecha, string $moneda = 'USD'): ?self
    {
        return self::where('moneda', strtoupper($moneda))
            ->whereDate('fecha_bcv', '<=', $fecha)
            ->orderBy('fecha_bcv', 'desc')
            ->first();
    }
}
