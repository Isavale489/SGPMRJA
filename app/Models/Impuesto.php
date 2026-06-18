<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Impuesto del sistema (IVA y, a futuro, otros impuestos venezolanos).
 *
 * Fuente de verdad de las tasas: para el IVA usar Impuesto::tasaIva(), que el
 * cálculo de compras consume. La gestión vive en el panel /configuracion.
 */
class Impuesto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'impuesto';

    /** Código reservado del IVA (único punto donde se conoce "cuál es el IVA"). */
    public const CODIGO_IVA = 'IVA';

    protected $fillable = [
        'codigo',
        'nombre',
        'porcentaje',
        'descripcion',
        'estado',
    ];

    protected $casts = [
        'porcentaje' => 'decimal:2',
    ];

    /**
     * Tasa de IVA general vigente (%). Lee la fila activa con código IVA;
     * si no existe (BD sin sembrar), cae al default de config/impuestos.php
     * para que el cálculo nunca se rompa.
     */
    public static function tasaIva(): float
    {
        $tasa = static::query()
            ->where('codigo', self::CODIGO_IVA)
            ->where('estado', 'activo')
            ->value('porcentaje');

        return (float) ($tasa ?? config('impuestos.iva', 16));
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}
