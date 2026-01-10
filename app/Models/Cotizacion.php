<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cotizacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cotizacion';

    protected $fillable = [
        'cliente_id',
        'fecha_cotizacion',
        'fecha_validez',
        'estado',
        'total',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function productos()
    {
        return $this->hasMany(DetalleCotizacion::class);
    }

    /**
     * Relación con el pedido creado desde esta cotización
     */
    public function pedido()
    {
        return $this->hasOne(Pedido::class);
    }
}
