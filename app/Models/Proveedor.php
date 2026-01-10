<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'proveedor';

    protected $fillable = [
        'razon_social',
        'rif',
        'direccion',
        'telefono',
        'email',
        'contacto',
        'telefono_contacto',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function insumos()
    {
        return $this->hasMany(Insumo::class);
    }
}
