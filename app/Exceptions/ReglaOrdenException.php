<?php

namespace App\Exceptions;

use InvalidArgumentException;

/**
 * Regla de negocio de una Orden de Producción que se evalúa dentro de una
 * transacción (con la fila bloqueada). Lleva el campo del formulario al que
 * se atribuye el error para que el controlador lo muestre en su sitio.
 */
class ReglaOrdenException extends InvalidArgumentException
{
    public function __construct(string $mensaje, public readonly string $campo = 'general')
    {
        parent::__construct($mensaje);
    }
}
