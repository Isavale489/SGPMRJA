import type { EstadoCompra } from './tipos';

/** Qué se puede hacer con una compra según su estado (mismas reglas que CompraService). */
export function accionesDe(c: { estado: EstadoCompra; clonada: boolean }, puede: (p: string) => boolean) {
    return {
        editar: c.estado === 'borrador' && puede('compras.gestionar'),
        procesar: c.estado === 'borrador' && puede('compras.procesar'),
        eliminar: c.estado === 'borrador' && puede('compras.gestionar'),
        anular: c.estado === 'recibida' && puede('compras.anular'),
        clonar: c.estado === 'anulada' && !c.clonada && puede('compras.clonar'),
        pdf: puede('compras.pdf'),
    };
}
