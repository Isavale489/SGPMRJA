<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Rol administrable (FEAT-005).
 *
 * 'Administrador' y 'Supervisor' nacen como roles de sistema (es_sistema = 1):
 * no se renombran ni eliminan. El Administrador además tiene acceso total por
 * Gate::before y nunca consulta la tabla permiso_rol. Los demás roles los crea
 * el administrador desde el panel de seguridad y se gobiernan por la matriz de
 * permisos (tabla permiso_rol).
 */
class Rol extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rol';

    protected $fillable = [
        'nombre',
        'descripcion',
        'es_sistema',
    ];

    protected $casts = [
        'es_sistema' => 'boolean',
    ];

    /**
     * Usuarios que tienen asignado este rol.
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    /**
     * Filas de permisos otorgados a este rol.
     */
    public function permisos(): HasMany
    {
        return $this->hasMany(PermisoRol::class, 'rol_id');
    }

    /**
     * Lista plana de los permisos otorgados ('modulo.accion').
     */
    public function permisosArray(): array
    {
        return $this->permisos()->pluck('permiso')->all();
    }
}
