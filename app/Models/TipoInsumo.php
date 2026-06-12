<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoInsumo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipo_insumo';

    protected $fillable = ['nombre', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    /**
     * Insumos de este tipo. La FK es el NOMBRE del tipo (insumo.tipo es texto),
     * para no romper la lógica central que filtra por tipo='Tela'.
     */
    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'tipo', 'nombre');
    }
}
