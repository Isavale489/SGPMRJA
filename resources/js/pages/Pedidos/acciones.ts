import type { PedidoFila } from './tipos';

/** Qué acciones ofrece cada fila (el servidor vuelve a validar cada una). */
export function accionesDe(p: Pick<PedidoFila, 'estado' | 'con_produccion'>, puede: (permiso: string) => boolean) {
    const gestionar = puede('pedidos.gestionar');
    const abierto = p.estado !== 'Completado' && p.estado !== 'Cancelado';
    return {
        pdf: puede('pedidos.pdf'),
        // Pagos, entrega y prioridad; completado, solo pagos (el saldo a la entrega).
        editar: gestionar && p.estado !== 'Cancelado',
        cancelar: gestionar && abierto,
        reactivar: gestionar && p.estado === 'Cancelado',
        eliminar: gestionar && abierto && !p.con_produccion,
    };
}
