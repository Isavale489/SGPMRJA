<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Atributo extends Model
{
    use HasFactory;

    protected $table = 'atributo';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
    ];

    public function valores()
    {
        return $this->hasMany(AtributoValor::class)->orderBy('orden')->orderBy('nombre');
    }

    public function tiposProducto()
    {
        return $this->belongsToMany(TipoProducto::class, 'tipo_producto_atributo')
            ->withPivot(['es_obligatorio', 'orden'])
            ->withTimestamps();
    }
}
