<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cotizacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cotizacion';

    protected $fillable = [
        'cliente_id',
        'fecha_cotizacion',
        'fecha_validez',
        'estado',
        'total',
        'tasa_cambio_valor',
        'notas',
        'condiciones_terminos',
        'user_id',
        'prioridad',
    ];

    protected $casts = [
        'fecha_cotizacion'   => 'date',
        'fecha_validez'      => 'date',
        'total'              => 'decimal:2',
        'tasa_cambio_valor'  => 'decimal:4',
    ];

    /**
     * Vigencia de precios: una cotización deja de ser convertible a pedido si
     * han pasado más de N días continuos desde su fecha de emisión
     * (fecha_cotizacion). Es la regla de negocio autoritativa; el estado
     * 'Vencida' es solo el reflejo persistido de esta misma condición.
     */
    public const DIAS_VIGENCIA = 15;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function productos()
    {
        return $this->hasMany(DetalleCotizacion::class);
    }

    /**
     * Relación con el pedido creado desde esta cotización
     */
    public function pedido()
    {
        return $this->hasOne(Pedido::class);
    }

    /**
     * Fecha límite de vigencia de precios (emisión + DIAS_VIGENCIA).
     */
    public function fechaLimiteVigencia(): ?\Illuminate\Support\Carbon
    {
        return $this->fecha_cotizacion
            ? $this->fecha_cotizacion->copy()->addDays(self::DIAS_VIGENCIA)
            : null;
    }

    /**
     * ¿La cotización está vencida por vigencia de precios?
     * True si pasaron más de DIAS_VIGENCIA días continuos desde la emisión.
     * Cálculo dinámico: no depende de que el job ya haya marcado el estado.
     */
    public function estaVencidaPorVigencia(): bool
    {
        $limite = $this->fechaLimiteVigencia();

        return $limite !== null && $limite->endOfDay()->isPast();
    }

    /**
     * Scope: cotizaciones cuya vigencia de precios ya expiró (emitidas hace
     * más de DIAS_VIGENCIA días). Útil para el job que marca 'Vencida'.
     */
    public function scopeVigenciaExpirada($query)
    {
        return $query->whereNotNull('fecha_cotizacion')
            ->whereDate('fecha_cotizacion', '<', now()->subDays(self::DIAS_VIGENCIA)->toDateString());
    }

    /**
     * Verificar si la cotización puede ser convertida a pedido.
     * Requiere estar Aprobada, no convertida y con la vigencia de precios vigente.
     */
    public function puedeConvertirse(): bool
    {
        return $this->estado === 'Aprobada'
            && !$this->yaFueConvertida()
            && !$this->estaVencidaPorVigencia();
    }

    /**
     * Verificar si la cotización ya fue convertida a pedido.
     */
    public function yaFueConvertida(): bool
    {
        return $this->pedido()->exists();
    }

    /**
     * Actualizar automáticamente cotizaciones vencidas.
     * Marca como 'Vencida' toda cotización Pendiente/Aprobada cuya vigencia de
     * precios ya expiró (más de DIAS_VIGENCIA días desde la emisión) o cuya
     * fecha_validez explícita ya pasó. Idempotente; lo invoca el scheduler y la
     * carga del listado.
     */
    public static function actualizarCotizacionesVencidas()
    {
        $hoy    = now()->toDateString();
        $limite = now()->subDays(self::DIAS_VIGENCIA)->toDateString();

        self::whereIn('estado', ['Pendiente', 'Aprobada'])
            ->where(function ($q) use ($hoy, $limite) {
                $q->whereDate('fecha_cotizacion', '<', $limite)
                  ->orWhere('fecha_validez', '<', $hoy);
            })
            ->update(['estado' => 'Vencida']);
    }
}
