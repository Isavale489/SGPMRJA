<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Override de un parámetro del sistema (FEAT-004).
 *
 * Cada fila es un valor que el administrador cambió desde el panel de
 * configuración. La metadata del parámetro (módulo, tipo, reglas, default)
 * NO vive aquí: está en el registry config/parametros.php. Leer valores
 * siempre a través del helper parametro(), nunca de este modelo directo.
 */
class Configuracion extends Model
{
    protected $table = 'configuracion';

    protected $fillable = [
        'clave',
        'valor',
        'updated_by_id',
    ];

    /**
     * Usuario que realizó el último cambio del parámetro.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }
}
