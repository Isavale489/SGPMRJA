<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cliente';

    protected $fillable = [
        'persona_id',
        'tipo_cliente',
        'estatus', // Renombrado de 'estado' para evitar confusión con estado territorial
    ];

    protected $dates = ['deleted_at'];

    // Relación con Persona
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    // Relación con Cotizaciones
    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class);
    }

    /**
     * Tarjeta del cliente en el asistente de Cotizaciones (y lo que devuelve
     * el alta rápida de cliente): datos de contacto y sus cotizaciones previas.
     * Espejo de `ClienteCotizacion` en resources/js/pages/Cotizaciones/tipos.ts.
     */
    public function resumenParaCotizacion(): array
    {
        $this->loadMissing('persona');
        $previas = $this->cotizaciones()->selectRaw('COUNT(*) as n, MAX(fecha_cotizacion) as ultima')->first();

        return [
            'id' => $this->id,
            'nombre' => $this->nombre ?? 'Sin nombre',
            'documento' => $this->documento,
            'juridico' => in_array($this->persona?->tipo_documento, ['J-', 'G-'], true),
            'telefono' => $this->telefono ?: null,
            'email' => $this->email ?: null,
            'inhabilitado' => $this->trashed() || ! $this->estatus,
            'cotizaciones' => (int) ($previas->n ?? 0),
            'ultima' => $previas?->ultima ? substr((string) $previas->ultima, 0, 10) : null,
        ];
    }

    // Accessors para mantener compatibilidad con el código existente
    public function getNombreAttribute()
    {
        return $this->persona ? $this->persona->nombre : null;
    }

    public function getEmailAttribute()
    {
        return $this->persona ? $this->persona->email : null;
    }

    /**
     * Obtener teléfono principal usando la tabla normalizada
     */
    public function getTelefonoAttribute()
    {
        return $this->persona ? $this->persona->telefono_principal : null;
    }

    /**
     * Obtener todos los teléfonos del cliente
     */
    public function getTelefonosAttribute()
    {
        return $this->persona ? $this->persona->telefonos : collect();
    }

    public function getDocumentoAttribute()
    {
        if ($this->persona) {
            return $this->persona->tipo_documento . $this->persona->documento_identidad;
        }
        return null;
    }

    /**
     * Obtener dirección principal usando la tabla normalizada
     */
    public function getDireccionAttribute()
    {
        $direccionPrincipal = $this->persona ? $this->persona->direccion_principal : null;
        return $direccionPrincipal ? $direccionPrincipal->direccion : null;
    }

    /**
     * Obtener todas las direcciones del cliente
     */
    public function getDireccionesAttribute()
    {
        return $this->persona ? $this->persona->direcciones : collect();
    }

    /**
     * Obtener ciudad de la dirección principal
     */
    public function getCiudadAttribute()
    {
        $direccionPrincipal = $this->persona ? $this->persona->direccion_principal : null;
        return $direccionPrincipal ? $direccionPrincipal->ciudad : null;
    }

    /**
     * Obtener estado/territorio de la dirección principal
     */
    public function getEstadoTerritorialAttribute()
    {
        $direccionPrincipal = $this->persona ? $this->persona->direccion_principal : null;
        return $direccionPrincipal ? $direccionPrincipal->estado : null;
    }
}

