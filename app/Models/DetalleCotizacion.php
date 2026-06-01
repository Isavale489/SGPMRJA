<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Color;
use App\Models\Talla;

class DetalleCotizacion extends Model
{
    use HasFactory;

    protected $table = 'detalle_cotizacion';

    protected $fillable = [
        'cotizacion_id',
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
        'lleva_bordado' => 'boolean',
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
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

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function bordados()
    {
        return $this->hasMany(DetalleCotizacionBordado::class, 'detalle_cotizacion_id')->orderBy('orden')->orderBy('id');
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
            return $b->relationLoaded('logo') && $b->logo
                ? $b->logo->name
                : ($b->nombre_logo_aplicado ?: null);
        })->filter()->unique()->values();

        return $logos->isNotEmpty() ? $logos->implode(', ') : null;
    }
}
