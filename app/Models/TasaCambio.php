<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
     * Tasa actualmente VIGENTE de una moneda. Una tasa que el BCV publica en la
     * tarde rige legalmente desde las 00:00 del día siguiente, por lo que aquí se
     * aplica el techo `fecha_bcv <= hoy`: cualquier tasa con fecha futura queda
     * invisible hasta la medianoche. Reutiliza tasaVigente() (la lógica del techo).
     */
    public static function obtenerTasaActual(string $moneda = 'USD'): ?self
    {
        return self::tasaVigente(Carbon::today()->toDateString(), $moneda);
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
     * Fecha BCV de la tasa vigente para una fecha de referencia, SOLO si su
     * valor coincide con el snapshot guardado en el documento. Los snapshots
     * (compra.tasa_cambio, cotizacion.tasa_cambio_valor) no persisten la fecha
     * de la tasa, así que se re-deriva; la comparación evita mostrar una fecha
     * que no corresponde al valor (p. ej. tasas ingresadas manualmente o
     * corregidas después en la tabla). Devuelve null si no hay coincidencia.
     */
    public static function fechaParaValor($valorSnapshot, ?string $fechaRef = null, string $moneda = 'USD'): ?Carbon
    {
        $valorSnapshot = (float) $valorSnapshot;
        if ($valorSnapshot <= 0) {
            return null;
        }

        $tasa = self::tasaVigente($fechaRef ?? Carbon::today()->toDateString(), $moneda);

        return ($tasa && abs((float) $tasa->valor - $valorSnapshot) < 0.0001)
            ? $tasa->fecha_bcv
            : null;
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
