<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Catálogo: municipios de Venezuela (pertenecen a un estado).
 * En la UI se muestran como "Municipio".
 */
class Municipio extends Model
{
    protected $table = 'municipio';

    public $timestamps = false;

    protected $fillable = ['estado_id', 'nombre'];

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }
}
