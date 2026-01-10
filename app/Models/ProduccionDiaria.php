<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProduccionDiaria extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'produccion_diaria';

    protected $fillable = [
        'orden_id',
        'operario_id',
        'cantidad_producida',
        'cantidad_defectuosa',
        'observaciones',
    ];

    protected $casts = [
        'cantidad_producida' => 'integer',
        'cantidad_defectuosa' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_id');
    }

    public function operario()
    {
        return $this->belongsTo(Empleado::class, 'operario_id');
    }
}
