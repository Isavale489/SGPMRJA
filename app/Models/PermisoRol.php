<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Permiso 'modulo.accion' otorgado a un rol (FEAT-005).
 *
 * Sin fila para (rol_id, permiso) = denegado. El rol Administrador no consulta
 * esta tabla (bypass por Gate::before). La matriz del panel de seguridad
 * (TASK-039) sincroniza estas filas por rol.
 */
class PermisoRol extends Model
{
    protected $table = 'permiso_rol';

    protected $fillable = [
        'rol_id',
        'permiso',
    ];

    /**
     * Rol al que pertenece este permiso.
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }
}
