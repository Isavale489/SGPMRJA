<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    use HasFactory;

    protected $table = 'detalle_pedido';

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'cantidad',
        'descripcion',
        'lleva_bordado',
        'nombre_logo',
        'ubicacion_logo',
        'cantidad_logo',
        'color',
        'talla',
        'precio_unitario',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'lleva_bordado' => 'boolean',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function insumos()
    {
        return $this->belongsToMany(Insumo::class, 'detalle_pedido_insumo', 'detalle_pedido_id', 'insumo_id')
            ->withPivot('cantidad_estimada');
    }
}
