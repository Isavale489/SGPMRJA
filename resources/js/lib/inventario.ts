/**
 * Contratos del inventario entre páginas (y entre pestañas): la compra
 * prellenada con los faltantes (localStorage) y el aviso de cambio de stock
 * (BroadcastChannel + evento storage). Cotizaciones, Pedidos, Órdenes,
 * Movimientos y Compras los comparten.
 */

const CANAL_STOCK = 'sgpmrja_stock';
const CLAVE_STOCK = 'sgpmrja_stock_change';
const CLAVE_PREFILL = 'sgpmrja_compra_prefill';

/** Avisa a las otras pestañas que el stock cambió (recalculan su proyección de insumos). */
export function avisarCambioStock(motivo: string): void {
    try {
        const canal = new BroadcastChannel(CANAL_STOCK);
        canal.postMessage({ type: 'stock-change', motivo });
        canal.close();
    } catch {
        // sin BroadcastChannel: queda el evento storage
    }
    try {
        window.localStorage.setItem(CLAVE_STOCK, String(Date.now()));
    } catch {
        // almacenamiento bloqueado: las otras pestañas recalculan al volver a ellas
    }
}

/** Escucha los cambios de stock hechos en otras pestañas. Devuelve la función para dejar de escuchar. */
export function alCambiarStock(cb: () => void): () => void {
    let canal: BroadcastChannel | undefined;
    try {
        canal = new BroadcastChannel(CANAL_STOCK);
        canal.onmessage = (ev) => { if ((ev.data as { type?: string })?.type === 'stock-change') cb(); };
    } catch {
        // sin BroadcastChannel: queda el evento storage
    }
    const almacen = (ev: StorageEvent) => { if (ev.key === CLAVE_STOCK) cb(); };
    window.addEventListener('storage', almacen);
    return () => {
        canal?.close();
        window.removeEventListener('storage', almacen);
    };
}

/** Insumo faltante que llega para precargar una compra. */
export interface FaltanteCompra {
    insumo_id: number;
    nombre?: string;
    cantidad: number;
}

/** Deja los faltantes para el formulario de compra (la abre `urlCrear?prefill=1`). */
export function guardarFaltantes(insumos: FaltanteCompra[], origen: string): void {
    try {
        window.localStorage.setItem(CLAVE_PREFILL, JSON.stringify({ origen, ts: Date.now(), insumos }));
    } catch {
        // sin almacenamiento: la compra se abre vacía
    }
}

/** Lee y consume los faltantes (una sola vez: recargar no los vuelve a aplicar). */
export function tomarFaltantes(): FaltanteCompra[] {
    try {
        const crudo = window.localStorage.getItem(CLAVE_PREFILL);
        window.localStorage.removeItem(CLAVE_PREFILL);
        const datos = crudo ? (JSON.parse(crudo) as { insumos?: FaltanteCompra[] }) : null;
        return Array.isArray(datos?.insumos) ? datos.insumos.filter((i) => i && Number(i.insumo_id) > 0) : [];
    } catch {
        return [];
    }
}
