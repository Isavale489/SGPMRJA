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

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
