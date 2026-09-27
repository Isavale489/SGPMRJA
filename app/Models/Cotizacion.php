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
     * Vigencia de precios por defecto (días). La fuente de verdad del
     * vencimiento es la fecha_validez de CADA cotización (lo pactado con ese
     * cliente); este parámetro solo actúa como:
     *   1. Default de fecha_validez al crear (emisión + N días, ajustable).
     *   2. Fallback para cotizaciones legacy sin fecha_validez.
     *   3. Plazo de la nueva validez al reactivar una Vencida.
     * Configurable desde el panel /configuracion (default 15).
     */
    public static function diasVigencia(): int
    {
        return (int) parametro('cotizaciones.dias_vigencia');
    }

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
     * Fecha límite de vigencia de precios: la fecha_validez pactada en la
     * cotización; fallback emisión + diasVigencia() para legacy sin fecha.
     */
    public function fechaLimiteVigencia(): ?\Illuminate\Support\Carbon
    {
        if ($this->fecha_validez) {
            return $this->fecha_validez->copy();
        }

        return $this->fecha_cotizacion
            ? $this->fecha_cotizacion->copy()->addDays(self::diasVigencia())
            : null;
    }

    /**
     * ¿La cotización está vencida por vigencia de precios?
     * True si su fecha límite (fecha_validez, o el fallback) ya pasó.
     * Cálculo dinámico: no depende de que el job ya haya marcado el estado.
     */
    public function estaVencidaPorVigencia(): bool
    {
        $limite = $this->fechaLimiteVigencia();

        return $limite !== null && $limite->endOfDay()->isPast();
    }

    /**
     * Scope: cotizaciones cuya vigencia de precios ya expiró — fecha_validez
     * pasada, o (legacy sin fecha_validez) emitidas hace más de diasVigencia()
     * días. Misma condición que aplica el job que marca 'Vencida'.
     */
    public function scopeVigenciaExpirada($query)
    {
        $hoy    = now()->toDateString();
        $limite = now()->subDays(self::diasVigencia())->toDateString();

        return $query->where(function ($q) use ($hoy, $limite) {
            $q->whereDate('fecha_validez', '<', $hoy)
              ->orWhere(function ($q2) use ($limite) {
                  $q2->whereNull('fecha_validez')
                     ->whereNotNull('fecha_cotizacion')
                     ->whereDate('fecha_cotizacion', '<', $limite);
              });
        });
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
     * precios ya expiró (más de diasVigencia() días desde la emisión) o cuya
     * fecha_validez explícita ya pasó. Idempotente; lo invoca el scheduler y la
     * carga del listado.
     */
    public static function actualizarCotizacionesVencidas()
    {
        self::whereIn('estado', ['Pendiente', 'Aprobada'])
            ->vigenciaExpirada()
            ->update(['estado' => 'Vencida']);
    }
}
