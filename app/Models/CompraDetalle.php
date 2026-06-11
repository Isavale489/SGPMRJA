<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraDetalle extends Model
{
    use HasFactory;

    protected $table = 'compra_detalle';

    protected $fillable = [
        'compra_id',
        'insumo_id',
        'cantidad',
        'costo_unitario',
        'costo_unitario_bs',
        'aplica_iva',
        'subtotal',
    ];

    protected $casts = [
        'cantidad'          => 'decimal:2',
        'costo_unitario'    => 'decimal:2',
        'costo_unitario_bs' => 'decimal:2',
        'aplica_iva'        => 'boolean',
        'subtotal'       => 'decimal:2',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }
}
