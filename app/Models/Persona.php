<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Persona extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'persona';

    protected $fillable = [
        'nombre',
        'documento_identidad',
        'tipo_documento',
        'email',
        'estado_geografico'
    ];

    protected $appends = ['nombre_completo'];

    // Relaciones
    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function empleado()
    {
        return $this->hasOne(Empleado::class);
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class);
    }

    public function proveedor()
    {
        return $this->hasOne(Proveedor::class);
    }

    /**
     * Relación con teléfonos (normalizada)
     */
    public function telefonos()
    {
        return $this->hasMany(Telefono::class);
    }

    /**
     * Relación con direcciones (normalizada)
     */
    public function direcciones()
    {
        return $this->hasMany(Direccion::class);
    }

    // Accessors
    // El nombre completo vive ya consolidado en `nombre` (natural: nombre +
    // apellido; jurídica: razón social). Se conserva el accessor por
    // compatibilidad con el código existente que lo invoca.
    public function getNombreCompletoAttribute()
    {
        return trim((string) $this->nombre);
    }

    public function getDocumentoCompletoAttribute()
    {
        return "{$this->tipo_documento}{$this->documento_identidad}";
    }

    /**
     * Obtener teléfono principal (de la tabla normalizada)
     * Usa la colección cargada para evitar N+1 queries.
     */
    public function getTelefonoPrincipalAttribute()
    {
        $telefonoPrincipal = $this->telefonos->firstWhere('es_principal', true);
        return $telefonoPrincipal ? $telefonoPrincipal->numero : null;
    }

    /**
     * Obtener dirección principal (de la tabla normalizada)
     * Usa la colección cargada para evitar N+1 queries.
     */
    public function getDireccionPrincipalAttribute()
    {
        return $this->direcciones->firstWhere('es_principal', true);
    }
}
