<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compra extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'compra';

    protected $fillable = [
        'proveedor_id',
        'user_id',
        'numero_factura',
        'fecha_compra',
        'fecha_vencimiento',
        'tipo_pago',
        'subtotal',
        'iva',
        'total',
        'observaciones',
        'estado',
        'origen_compra_id',
    ];

    protected $casts = [
        'fecha_compra'      => 'date',
        'fecha_vencimiento' => 'date',
        'subtotal'          => 'decimal:2',
        'iva'               => 'decimal:2',
        'total'             => 'decimal:2',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function detalles()
    {
        return $this->hasMany(CompraDetalle::class);
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function origenCompra()
    {
        return $this->belongsTo(Compra::class, 'origen_compra_id');
    }

    public function clones()
    {
        return $this->hasMany(Compra::class, 'origen_compra_id');
    }
}
