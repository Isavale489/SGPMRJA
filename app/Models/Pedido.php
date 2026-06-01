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
        'estado',
        'total',
        'user_id',
        'abono',
        'prioridad',
    ];

    protected $casts = [
        'fecha_pedido' => 'date:Y-m-d',
        'fecha_entrega_estimada' => 'date:Y-m-d',
        'total' => 'decimal:2',
    ];

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
    public function getProgresoProduccionAttribute(): float
    {
        $totalLineas = $this->relationLoaded('productos')
            ? $this->productos->count()
            : $this->productos()->count();

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

        $totalLineas = $this->productos()->count();
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

