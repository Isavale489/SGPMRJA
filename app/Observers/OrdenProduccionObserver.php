<?php

namespace App\Observers;

use App\Models\OrdenProduccion;
use App\Models\Pedido;

class OrdenProduccionObserver
{
    /**
     * Mapa de herencia: estatus inicial de la OP según el estado del pedido del
     * que se genera. Cualquier pedido vivo produce una orden que arranca
     * 'Pendiente'; el pedido 'Cancelado' se maneja aparte (se bloquea).
     */
    private const ESTADO_INICIAL_POR_PEDIDO = [
        'Pendiente'  => 'Pendiente',
        'Procesando' => 'Pendiente',
        'Completado' => 'Pendiente',
    ];

    /**
     * Al crear una OP, hereda y sincroniza su estatus inicial con el pedido
     * padre. Si el pedido está cancelado, se impide generar la orden.
     */
    public function creating(OrdenProduccion $orden): void
    {
        if (!$orden->pedido_id) {
            return; // Órdenes manuales (sin pedido): no hay estatus que heredar.
        }

        $pedido = Pedido::find($orden->pedido_id);
        if (!$pedido) {
            return;
        }

        if ($pedido->estado === 'Cancelado') {
            throw new \RuntimeException(
                'No se puede generar una orden de producción para un pedido cancelado.'
            );
        }

        // La OP nace alineada al pedido (autoritativo aunque el llamador omita el estado).
        $orden->estado = self::ESTADO_INICIAL_POR_PEDIDO[$pedido->estado] ?? 'Pendiente';
    }
}
