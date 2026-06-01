<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Color;
use App\Models\Talla;

class DetallePedido extends Model
{
    use HasFactory;

    protected $table = 'detalle_pedido';

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'tela_snapshot',
        'atributos_snapshot',
        'cantidad',
        'descripcion',
        'lleva_bordado',
        'color_id',
        'talla_id',
        'precio_unitario',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'lleva_bordado' => 'boolean',
        'tela_snapshot' => 'array',
        'atributos_snapshot' => 'array',
    ];

    protected $appends = [
        'ubicacion_logo',
        'cantidad_logo',
    ];

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function talla()
    {
        return $this->belongsTo(Talla::class);
    }

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

    public function bordados()
    {
        return $this->hasMany(DetallePedidoBordado::class, 'detalle_pedido_id')->orderBy('orden')->orderBy('id');
    }

    public function getUbicacionLogoAttribute()
    {
        if (!$this->lleva_bordado) {
            return null;
        }

        return $this->bordados->pluck('nombre_aplicado')->implode(', ') ?: null;
    }

    public function getCantidadLogoAttribute()
    {
        if (!$this->lleva_bordado) {
            return null;
        }

        $cantidad = $this->bordados->sum(function ($item) {
            return (int) ($item->cantidad ?? 1);
        });

        return $cantidad ?: null;
    }

    public function getNombreLogoAttribute()
    {
        $bordados = $this->relationLoaded('bordados')
            ? $this->bordados
            : $this->bordados()->with('logo:id,name')->get();

        $logos = $bordados->map(function ($b) {
            // Preferir nombre del catálogo (logo FK), fallback al snapshot
            return $b->relationLoaded('logo') && $b->logo
                ? $b->logo->name
                : ($b->nombre_logo_aplicado ?: null);
        })->filter()->unique()->values();

        return $logos->isNotEmpty() ? $logos->implode(', ') : null;
    }
}
