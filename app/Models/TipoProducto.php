<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoProducto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipo_producto';

    protected $fillable = [
        'nombre',
        'prefijo',
        'descripcion',
        'imagen',
        'precio_confeccion',
        'requiere_tela',
        'consumo_tela_por_unidad',
    ];

    protected $casts = [
        'precio_confeccion' => 'decimal:2',
        'requiere_tela' => 'boolean',
        'consumo_tela_por_unidad' => 'decimal:2',
    ];

    protected $appends = ['imagen_url'];

    /**
     * URL pública de la imagen del catálogo (null si no tiene).
     */
    public function getImagenUrlAttribute(): ?string
    {
        return $this->imagen ? asset($this->imagen) : null;
    }

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    /**
     * Atributos de confección que aplican a este tipo.
     * Pivot: tipo_producto_atributo (es_obligatorio, orden).
     */
    public function atributos()
    {
        return $this->belongsToMany(Atributo::class, 'tipo_producto_atributo')
            ->withPivot(['es_obligatorio', 'orden'])
            ->withTimestamps()
            ->orderBy('tipo_producto_atributo.orden');
    }

    /**
     * Insumos por defecto para una orden de producción de este tipo.
     * Pivot: tipo_producto_insumo (cantidad_estimada).
     * Al crear una orden, se prellenan estos insumos con sus cantidades.
     */
    public function insumosDefault()
    {
        return $this->belongsToMany(Insumo::class, 'tipo_producto_insumo')
            ->withPivot('cantidad_estimada')
            ->withTimestamps();
    }

    /**
     * Telas permitidas para este tipo de producto (FEAT-003).
     * Pivot: tipo_producto_tela. Son insumos con tipo='Tela'.
     * Alimenta el selector de variante de la cotización sin requerir
     * una fila `producto` por combinación.
     */
    public function telas()
    {
        return $this->belongsToMany(Insumo::class, 'tipo_producto_tela')
            ->withTimestamps();
    }
}
