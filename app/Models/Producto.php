<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'producto';

    protected $fillable = [
        'tipo_producto_id',
        'codigo',
        'descripcion',
        'modelo',
        'precio_base',
        'imagen',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'precio_base' => 'decimal:2',
    ];

    /**
     * Accessors que se incluyen en JSON automáticamente
     */
    protected $appends = ['nombre_completo'];

    /**
     * Relación con tipo de producto
     */
    public function tipoProducto()
    {
        return $this->belongsTo(TipoProducto::class);
    }

    /**
     * Accessor para obtener el nombre completo (tipo + modelo)
     */
    public function getNombreCompletoAttribute(): string
    {
        $tipo = $this->tipoProducto ? $this->tipoProducto->nombre : '';
        return trim($tipo . ' ' . $this->modelo);
    }

    /**
     * Accessor para nombre (compatibilidad con código existente)
     */
    public function getNombreAttribute(): string
    {
        return $this->nombre_completo;
    }

    public function ordenesProduccion()
    {
        return $this->hasMany(OrdenProduccion::class);
    }

    public function pedidos()
    {
        return $this->hasMany(DetallePedido::class);
    }
}
