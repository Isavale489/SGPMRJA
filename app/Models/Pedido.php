<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pedido';

    protected $fillable = [
        'cotizacion_id',
        'cliente_id',
        'fecha_pedido',
        'fecha_entrega_estimada',
        'estado',
        'total',
        'user_id',
        'abono',
        'efectivo_pagado',
        'transferencia_pagado',
        'pago_movil_pagado',
        'referencia_transferencia',
        'referencia_pago_movil',
        'banco_id', // Legacy support
        'banco_transferencia_id',
        'banco_pago_movil_id',
        'prioridad',
    ];

    protected $casts = [
        'fecha_pedido' => 'date:Y-m-d',
        'fecha_entrega_estimada' => 'date:Y-m-d',
        'total' => 'decimal:2',
    ];

    /**
     * Relación con el usuario que creó el pedido
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el banco para transferencias (Legacy o general)
     */
    public function banco()
    {
        return $this->belongsTo(Banco::class);
    }

    /**
     * Relación con el banco para transferencias
     */
    public function bancoTransferencia()
    {
        return $this->belongsTo(Banco::class, 'banco_transferencia_id');
    }

    /**
     * Relación con el banco para pago móvil
     */
    public function bancoPagoMovil()
    {
        return $this->belongsTo(Banco::class, 'banco_pago_movil_id');
    }

    /**
     * Relación con los productos del pedido
     */
    public function productos()
    {
        return $this->hasMany(DetallePedido::class);
    }

    /**
     * Relación con el cliente (normalizada)
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Relación con la cotización de origen (si fue creado desde una)
     */
    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }

    // ============================================
    // ACCESSORS para obtener datos del cliente
    // ============================================

    /**
     * Obtener nombre completo del cliente
     */
    public function getClienteNombreCompletoAttribute()
    {
        if (!$this->cliente)
            return null;
        $nombre = $this->cliente->nombre ?? '';
        $apellido = $this->cliente->apellido ?? '';
        return trim($nombre . ' ' . $apellido) ?: null;
    }

    /**
     * Obtener email del cliente
     */
    public function getClienteEmailNormalizadoAttribute()
    {
        return $this->cliente?->email;
    }

    /**
     * Obtener teléfono del cliente
     */
    public function getClienteTelefonoNormalizadoAttribute()
    {
        return $this->cliente?->telefono;
    }

    /**
     * Obtener documento del cliente
     */
    public function getClienteDocumentoAttribute()
    {
        return $this->cliente?->documento;
    }
}

