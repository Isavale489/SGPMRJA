<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Se lanza cuando una Orden de Producción no puede crearse por falta de stock
 * de materia prima. Se distingue de otras validaciones (p. ej. reparto por
 * empleado) para que el controlador pueda responder con la lista de faltantes
 * y ofrecer el atajo "Crear compra prellenada".
 */
class StockInsuficienteException extends RuntimeException
{
    //
}
