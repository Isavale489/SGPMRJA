<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubOrdenProduccion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sub_orden_produccion';

    protected $fillable = [
        'orden_produccion_id',
        'nombre',
        'cantidad_asignada',
        'estado',
        'notas',
    ];

    protected $casts = [
        'cantidad_asignada' => 'integer',
    ];

    /**
     * Orden de producción principal a la que pertenece la sub-orden.
     */
    public function ordenProduccion()
    {
        return $this->belongsTo(OrdenProduccion::class);
    }

    /**
     * Empleados asignados a la sub-orden (many-to-many, con rol opcional).
     */
    public function empleados()
    {
        return $this->belongsToMany(Empleado::class, 'sub_orden_empleado')
            ->withPivot('rol')
            ->withTimestamps();
    }
}
