<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Insumo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'insumo';

    protected $fillable = [
        'nombre',
        'codigo',
        'tipo',
        'unidad_medida',
        'is_inventoriable',
        'costo_unitario',
        'aplica_iva',
        'stock_actual',
        'stock_minimo',
        'stock_maximo',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'is_inventoriable' => 'boolean',
        'aplica_iva' => 'boolean',
        'costo_unitario' => 'decimal:2',
        'stock_actual' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'stock_maximo' => 'decimal:2',
    ];

    public function ordenesProduccion()
    {
        return $this->belongsToMany(OrdenProduccion::class, 'detalle_orden_insumo')
            ->withPivot(['cantidad_estimada', 'cantidad_utilizada'])
            ->withTimestamps();
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInsumo::class);
    }

    public function compraDetalles()
    {
        return $this->hasMany(CompraDetalle::class);
    }

    /**
     * Scope: solo insumos de tipo 'Tela' activos.
     * Usado en el form de productos para definir la materia prima de la variante.
     */
    public function scopeTelas($query)
    {
        return $query->where('tipo', 'Tela')->where('estado', true);
    }

    /**
     * Estado de stock del insumo comparando `stock_actual` contra sus topes.
     * Misma semántica que MovimientoInsumo::scopeFiltroStock (fuente única):
     *  - critico: stock_actual <= stock_minimo
     *  - exceso : stock_maximo > 0 && stock_actual > stock_maximo
     *  - optimo : resto
     * `stock_maximo = 0` = "sin máximo definido" (no cuenta como exceso).
     */
    public function estadoStock(): string
    {
        if ($this->stock_actual <= $this->stock_minimo) {
            return 'critico';
        }
        if ($this->stock_maximo > 0 && $this->stock_actual > $this->stock_maximo) {
            return 'exceso';
        }
        return 'optimo';
    }
}
