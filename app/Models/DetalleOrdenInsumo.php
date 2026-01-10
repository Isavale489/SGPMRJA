<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleOrdenInsumo extends Model
{
    use HasFactory;

    protected $table = 'detalle_orden_insumo';

    protected $fillable = [
        'orden_produccion_id',
        'insumo_id',
        'cantidad_estimada',
        'cantidad_utilizada'
    ];

    public function ordenProduccion()
    {
        return $this->belongsTo(OrdenProduccion::class);
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }
}
