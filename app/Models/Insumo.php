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
        'tipo',
        'unidad_medida',
        'costo_unitario',
        'stock_actual',
        'stock_minimo',
        'proveedor_id',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'costo_unitario' => 'decimal:2',
        'stock_actual' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function ordenesProduccion()
    {
        return $this->belongsToMany(OrdenProduccion::class, 'detalle_orden_insumos')
            ->withPivot(['cantidad_estimada', 'cantidad_utilizada'])
            ->withTimestamps();
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInsumo::class);
    }
}
