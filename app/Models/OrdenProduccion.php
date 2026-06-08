<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdenProduccion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orden_produccion';

    protected $fillable = [
        'pedido_id',
        'detalle_pedido_id',
        'producto_id',
        'empleado_id',
        'cantidad_solicitada',
        'cantidad_producida',
        'cantidad_defectuosa',
        'fecha_inicio',
        'fecha_fin_estimada',
        'fecha_fin_real',
        'estado',
        'notas',
        'motivo_cancelacion',
        'created_by',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin_estimada' => 'date',
        'fecha_fin_real' => 'date',
        'cantidad_solicitada' => 'integer',
        'cantidad_producida' => 'integer',
        'cantidad_defectuosa' => 'integer',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function insumos()
    {
        return $this->belongsToMany(Insumo::class, 'detalle_orden_insumo')
            ->withPivot(['cantidad_estimada', 'cantidad_utilizada'])
            ->withTimestamps();
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function detallePedido()
    {
        return $this->belongsTo(DetallePedido::class, 'detalle_pedido_id');
    }

    /**
     * Nombre legible de lo que se fabrica (FEAT-003):
     *  - Legacy: nombre del Producto concreto.
     *  - Dinámico (producto_id NULL): se arma desde el snapshot de la línea
     *    (tipo + tela + atributos); fallback al SKU congelado.
     * Para evitar N+1, eager-load `producto` y `detallePedido.tipoProducto`.
     */
    public function getNombreProductoAttribute(): string
    {
        if ($this->producto) {
            return $this->producto->nombre;
        }

        $d = $this->detallePedido;
        if ($d) {
            $partes = [];
            if ($d->tipoProducto) {
                $partes[] = $d->tipoProducto->nombre;
            }
            if (is_array($d->tela_snapshot) && !empty($d->tela_snapshot['nombre'])) {
                $partes[] = $d->tela_snapshot['nombre'];
            }
            if (is_array($d->atributos_snapshot)) {
                $partes = array_merge($partes, array_values($d->atributos_snapshot));
            }
            $nombre = trim(implode(' ', $partes));
            if ($nombre !== '') {
                return $nombre;
            }
            if (!empty($d->sku_snapshot)) {
                return $d->sku_snapshot;
            }
        }

        return 'Producto #' . ($this->producto_id ?? $this->id);
    }

    /**
     * Fracción de avance de la orden (0..1) = producida / solicitada.
     */
    public function getProgresoAttribute(): float
    {
        if (!$this->cantidad_solicitada) {
            return 0.0;
        }

        return min(1.0, $this->cantidad_producida / $this->cantidad_solicitada);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    /**
     * Sub-órdenes de producción (etapas/tareas) que dependen de esta orden.
     */
    public function subordenes()
    {
        return $this->hasMany(SubOrdenProduccion::class);
    }
}
