<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtributoValor extends Model
{
    use HasFactory;

    protected $table = 'atributo_valor';

    protected $fillable = [
        'atributo_id',
        'nombre',
        'codigo',
        'orden',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    public function atributo()
    {
        return $this->belongsTo(Atributo::class);
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_atributo_valor')
            ->withTimestamps();
    }
}
