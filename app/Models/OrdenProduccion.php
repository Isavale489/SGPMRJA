<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdenProduccion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orden_produccion';

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'cantidad_solicitada',
        'cantidad_producida',
        'fecha_inicio',
        'fecha_fin_estimada',
        'estado',
        'costo_estimado',
        'logo',
        'notas',
        'created_by',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin_estimada' => 'date',
        'costo_estimado' => 'decimal:2',
        'cantidad_solicitada' => 'integer',
        'cantidad_producida' => 'integer',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function insumos()
    {
        return $this->belongsToMany(Insumo::class, 'detalle_orden_insumo')
            ->withPivot(['cantidad_estimada', 'cantidad_utilizada'])
            ->withTimestamps();
    }

    public function produccionDiaria()
    {
        return $this->hasMany(ProduccionDiaria::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
