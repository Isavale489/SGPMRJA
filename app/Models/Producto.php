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
        'insumo_tela_id',
        'codigo',
        'descripcion',
        'precio_base',
        'atributos_snapshot',
        'imagen',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'precio_base' => 'decimal:2',
        'atributos_snapshot' => 'array',
    ];

    /**
     * Accessors que se incluyen en JSON automáticamente
     */
    protected $appends = ['nombre_completo', 'nombre'];

    /**
     * Relación con tipo de producto
     */
    public function tipoProducto()
    {
        return $this->belongsTo(TipoProducto::class);
    }

    public function tela()
    {
        return $this->belongsTo(Insumo::class, 'insumo_tela_id');
    }

    public function atributoValores()
    {
        return $this->belongsToMany(AtributoValor::class, 'producto_atributo_valor')
            ->withTimestamps();
    }

    /**
     * Nombre legible: tipo + tela + valores de atributos.
     * Ej: "Camisa Oxford Larga Mao". Si solo hay tipo, devuelve solo el tipo.
     * Para evitar N+1, eager-load tela y tipoProducto en consultas que usen este atributo.
     */
    public function getNombreCompletoAttribute(): string
    {
        $partes = [$this->tipoProducto?->nombre ?? ''];

        if ($this->insumo_tela_id) {
            $tela = $this->relationLoaded('tela') ? $this->tela : $this->tela()->first();
            if ($tela) {
                $partes[] = $tela->nombre;
            }
        }

        if (is_array($this->atributos_snapshot)) {
            $partes = array_merge($partes, array_values($this->atributos_snapshot));
        }

        return trim(implode(' ', array_filter($partes)));
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
