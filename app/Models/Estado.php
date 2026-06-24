<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Catálogo: estados (entidades federales) de Venezuela.
 */
class Estado extends Model
{
    protected $table = 'estado';

    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function municipios()
    {
        return $this->hasMany(Municipio::class);
    }
}
