<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInsumo extends Model
{
    use HasFactory;

    protected $table = 'movimiento_insumo';

    protected $fillable = [
        'insumo_id',
        'tipo_movimiento',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'motivo',
        'created_by',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'stock_anterior' => 'decimal:2',
        'stock_nuevo' => 'decimal:2',
    ];

    /**
     * Etiquetas legibles del filtro de estado de stock. Se usan tanto en el
     * chip "Filtros aplicados" del PDF como referencia para las opciones de la UI.
     */
    public const ETIQUETAS_STOCK = [
        'critico' => 'Crítico (por debajo del mínimo)',
        'exceso'  => 'Exceso (por encima del máximo)',
        'optimo'  => 'Óptimo (dentro del rango)',
    ];

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }

    /**
     * Filtra movimientos según el estado de stock del insumo asociado, comparando
     * `stock_actual` contra `stock_minimo`/`stock_maximo`. `stock_maximo` = 0 se
     * interpreta como "sin máximo definido" y por eso se ignora en Exceso. Si el
     * estado no es válido, devuelve la consulta intacta (no altera nada).
     */
    public function scopeFiltroStock($query, ?string $estado)
    {
        if (!array_key_exists($estado, self::ETIQUETAS_STOCK)) {
            return $query;
        }

        return $query->whereHas('insumo', function ($i) use ($estado) {
            if ($estado === 'critico') {
                $i->whereColumn('stock_actual', '<=', 'stock_minimo');
            } elseif ($estado === 'exceso') {
                $i->where('stock_maximo', '>', 0)
                  ->whereColumn('stock_actual', '>', 'stock_maximo');
            } else { // optimo
                $i->whereColumn('stock_actual', '>', 'stock_minimo')
                  ->where(function ($q) {
                      $q->where('stock_maximo', '<=', 0)
                        ->orWhereColumn('stock_actual', '<=', 'stock_maximo');
                  });
            }
        });
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
