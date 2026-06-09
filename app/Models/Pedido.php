<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pedido';

    protected $fillable = [
        'cotizacion_id',
        'cliente_id',
        'fecha_pedido',
        'fecha_entrega_estimada',
        'fecha_formalizacion',
        'estado',
        'total',
        'user_id',
        'abono',
        'prioridad',
    ];

    protected $casts = [
        'fecha_pedido' => 'date:Y-m-d',
        'fecha_entrega_estimada' => 'date:Y-m-d',
        'fecha_formalizacion' => 'date:Y-m-d',
        'total' => 'decimal:2',
    ];

    /**
     * Días hábiles (lun-vie) de plazo de entrega contados desde la formalización.
     */
    public const DIAS_HABILES_ENTREGA = 30;

    /**
     * Relación con el usuario que creó el pedido
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Pagos normalizados (tabla pago_pedido)
     */
    public function pagos()
    {
        return $this->hasMany(PagoPedido::class);
    }

    /**
     * Recalcula el abono como suma de los montos de pagos
     */
    public function recalcularAbono(): void
    {
        $this->update(['abono' => $this->pagos()->sum('monto')]);
    }

    /**
     * Porcentaje mínimo de abono (sobre el total) requerido para que el pedido
     * pueda avanzar a producción / generar órdenes. Configurable (default 50%).
     */
    public static function porcentajeAbonoMinimo(): float
    {
        return (float) config('pedidos.abono_minimo_porcentaje', 50);
    }

    /**
     * Monto mínimo de abono requerido para pasar a producción.
     */
    public function montoAbonoMinimo(): float
    {
        return round((float) $this->total * self::porcentajeAbonoMinimo() / 100, 2);
    }

    /**
     * Porcentaje del total efectivamente abonado (pagos validados).
     */
    public function porcentajeAbonado(): float
    {
        $total = (float) $this->total;
        if ($total <= 0) {
            return 0.0;
        }

        return round((float) $this->abono / $total * 100, 2);
    }

    /**
     * ¿El pedido cumple el abono mínimo para avanzar a producción?
     * Compara el abono validado contra el monto mínimo (con tolerancia de centavo).
     */
    public function cumpleAbonoMinimo(): bool
    {
        return (float) $this->abono + 0.001 >= $this->montoAbonoMinimo();
    }

    /**
     * ¿El pedido está formalizado? La formalización es un hito permanente: se
     * marca cuando el abono alcanza por primera vez el mínimo (50%) y no se
     * revierte aunque luego se editen los pagos.
     */
    public function estaFormalizado(): bool
    {
        return !is_null($this->fecha_formalizacion);
    }

    /**
     * Marca la formalización si el pedido acaba de alcanzar el abono mínimo y aún
     * no estaba formalizado. Fija la fecha de formalización (hoy). La fecha de
     * entrega elegida en el wizard MANDA: solo se autocalcula (formalización +
     * DIAS_HABILES_ENTREGA días hábiles, lun-vie) si el pedido no la tiene
     * (pedidos legacy). Idempotente: una vez formalizado no recalcula.
     *
     * Lo invoca PedidoService::syncPagos() tras recalcular el abono.
     */
    public function marcarFormalizacionSiCorresponde(): void
    {
        if ($this->estaFormalizado() || !$this->cumpleAbonoMinimo()) {
            return;
        }

        $hoy = now();
        $datos = ['fecha_formalizacion' => $hoy->toDateString()];

        if (empty($this->fecha_entrega_estimada)) {
            // addWeekdays() avanza solo días hábiles (salta sábado y domingo).
            $datos['fecha_entrega_estimada'] = $hoy->copy()->addWeekdays(self::DIAS_HABILES_ENTREGA)->toDateString();
        }

        $this->update($datos);
    }

    /**
     * ¿Las líneas del pedido (productos, tallas, cantidades, diseño) están
     * congeladas? Ocurre una vez formalizado, con producción iniciada o
     * completado. En ese estado solo se permiten cambios de pagos.
     */
    public function lineasBloqueadas(): bool
    {
        return $this->estaFormalizado()
            || $this->tieneProduccionActiva()
            || $this->estado === 'Completado';
    }

    /**
     * Relación con los productos del pedido
     */
    public function productos()
    {
        return $this->hasMany(DetallePedido::class);
    }

    /**
     * Relación con el cliente (normalizada)
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Relación con la cotización de origen (si fue creado desde una)
     */
    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }

    /**
     * Órdenes de producción del pedido (1 por línea de producto).
     */
    public function ordenes()
    {
        return $this->hasMany(OrdenProduccion::class);
    }

    /**
     * Progreso de producción del pedido (0..100), agregado desde sus órdenes.
     *
     * El pedido es "apto" para tantas órdenes como líneas de producto tiene.
     * Cada línea aporta 1/N: una orden completa de una línea = +100/N. Las
     * líneas sin orden aún aportan 0. Promedio sobre el total de líneas.
     */
    /**
     * Líneas que SÍ entran al flujo de producción (excluye reventa).
     * Carga las relaciones de tipo si aún no están cargadas para evitar N+1.
     */
    public function lineasProducibles()
    {
        $productos = $this->relationLoaded('productos')
            ? $this->productos
            : $this->productos()->with(['producto.tipoProducto', 'tipoProducto'])->get();

        return $productos->filter(fn ($d) => $d->requiereProduccion())->values();
    }

    public function getProgresoProduccionAttribute(): float
    {
        $totalLineas = $this->lineasProducibles()->count();

        if ($totalLineas === 0) {
            return 0.0;
        }

        $ordenes = $this->relationLoaded('ordenes')
            ? $this->ordenes
            : $this->ordenes()->get();

        $suma = $ordenes->sum(fn ($orden) => $orden->progreso); // fracción 0..1 por orden

        return round(min(1.0, $suma / $totalLineas) * 100, 1);
    }

    /**
     * Recalcula el estado del pedido a partir de sus órdenes de producción.
     *
     * Reglas:
     *   - Cancelado es terminal/manual: nunca se auto-recalcula.
     *   - Sin órdenes activas (o todas canceladas) → Pendiente.
     *   - Todas las líneas con orden Finalizada → Completado.
     *   - Al menos una orden En Proceso o Finalizada → Procesando.
     *   - Solo órdenes Pendientes → Pendiente.
     *
     * El estado deja de ser manual (salvo Cancelar/Reactivar): refleja la
     * realidad de producción. Ver docs y PedidoController::cancelar/reactivar.
     */
    public function recalcularEstado(): void
    {
        if ($this->estado === 'Cancelado') {
            return; // terminal y manual
        }

        $totalLineas = $this->lineasProducibles()->count();
        $activas = $this->ordenes()->get()->where('estado', '!=', 'Cancelado');

        if ($totalLineas === 0 || $activas->isEmpty()) {
            $nuevo = 'Pendiente';
        } else {
            $finalizadas = $activas->where('estado', 'Finalizado')->count();
            $enMarcha    = $activas->whereIn('estado', ['En Proceso', 'Finalizado'])->count();

            if ($finalizadas >= $totalLineas) {
                $nuevo = 'Completado';
            } elseif ($enMarcha > 0) {
                $nuevo = 'Procesando';
            } else {
                $nuevo = 'Pendiente';
            }
        }

        if ($this->estado !== $nuevo) {
            $this->update(['estado' => $nuevo]);
        }
    }

    /**
     * ¿El pedido ya tiene producción iniciada? (al menos una orden no cancelada).
     * Si la tiene, editar/eliminar el pedido recrearía sus líneas y dejaría
     * huérfanas las órdenes que las referencian, por eso se bloquea.
     */
    public function tieneProduccionActiva(): bool
    {
        return $this->ordenes()->where('estado', '!=', 'Cancelado')->exists();
    }

    /**
     * ¿El pedido tiene producción en curso? (alguna orden vinculada activa o en
     * proceso, es decir aún no finalizada ni cancelada). Bloquea la cancelación
     * del pedido: primero hay que cancelar/finalizar esas órdenes.
     */
    public function tieneProduccionEnCurso(): bool
    {
        return $this->ordenes()
            ->whereIn('estado', ['Pendiente', 'En Proceso'])
            ->exists();
    }

    // ============================================
    // ACCESSORS para obtener datos del cliente
    // ============================================

    /**
     * Obtener nombre completo del cliente
     */
    public function getClienteNombreCompletoAttribute()
    {
        if (!$this->cliente)
            return null;
        $nombre = $this->cliente->nombre ?? '';
        $apellido = $this->cliente->apellido ?? '';
        return trim($nombre . ' ' . $apellido) ?: null;
    }

    /**
     * Obtener email del cliente
     */
    public function getClienteEmailNormalizadoAttribute()
    {
        return $this->cliente?->email;
    }

    /**
     * Obtener teléfono del cliente
     */
    public function getClienteTelefonoNormalizadoAttribute()
    {
        return $this->cliente?->telefono;
    }

    /**
     * Obtener documento del cliente
     */
    public function getClienteDocumentoAttribute()
    {
        return $this->cliente?->documento;
    }
}

