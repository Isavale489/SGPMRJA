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
        'stock_actual',
        'stock_minimo',
        'stock_maximo',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'is_inventoriable' => 'boolean',
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
}
