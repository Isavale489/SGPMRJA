import type { ClienteCotizacion, Creador, GrupoCotizacion, Prioridad, TasaGuardada, Terminos } from '@/pages/Cotizaciones/tipos';
import type { Paginado } from '@/types';

export type EstadoPedido = 'Pendiente' | 'Procesando' | 'Completado' | 'Cancelado';
export type MetodoPago = 'efectivo' | 'transferencia' | 'pago_movil';

export const METODO: Record<MetodoPago, string> = { efectivo: 'Efectivo', transferencia: 'Transferencia', pago_movil: 'Pago móvil' };

/** Espejo de PedidoController::fila() (lo verifica PedidosPaginaTest). */
export interface PedidoFila {
    id: number;
    cotizacion_id: number | null;
    cliente: string;
    cliente_doc: string | null;
    cliente_inhabilitado: boolean;
    fecha: string | null;
    entrega: string | null;
    total: number;
    abono: number;
    porcentaje_abonado: number;
    tasa: TasaGuardada;
    estado: EstadoPedido;
    prioridad: Prioridad;
    formalizado: boolean;
    con_produccion: boolean;
}

export interface Pago {
    metodo: MetodoPago;
    monto: number;
    banco_id: number | null;
    banco: string | null;
    referencia: string | null;
}

/** Espejo de PedidoController::detalle(): lo que muestra «Ver». */
export interface PedidoDetalle extends PedidoFila {
    formalizacion: string | null;
    cliente_datos: ClienteCotizacion | null;
    creador: Creador | null;
    grupos: GrupoCotizacion[];
    pagos: Pago[];
}

export type FiltrosPedidos = {
    buscar?: string;
    estado?: EstadoPedido;
    desde?: string;
    hasta?: string;
    orden?: 'recientes' | 'monto_desc' | 'entrega_asc';
};

export interface PaginaPedidos {
    registros: Paginado<PedidoFila>;
    filtros: FiltrosPedidos & { ver?: string };
    detalle: PedidoDetalle | null;
    estados: EstadoPedido[];
    terminos: Terminos;
    urls: { index: string; crear: string; reportePdf: string };
}

/* ── Asistente (crear / editar) ─────────────────────────────────────────── */

/** Cotización convertible (PedidoController::cotizacionesDisponibles). */
export interface CotizacionDisponible {
    id: number;
    cliente: string;
    cliente_doc: string | null;
    fecha: string | null;
    validez: string | null;
    total: number;
    /** Tasa guardada en la cotización (para el equivalente en Bs). */
    tasa: number | null;
    prioridad: Prioridad;
    lineas: number;
}

/** La cotización elegida (PedidoController::cotizacionElegida). */
export interface CotizacionElegida {
    id: number;
    estado: string;
    fecha: string | null;
    validez: string | null;
    total: number;
    tasa: TasaGuardada;
    prioridad: Prioridad;
    cliente: ClienteCotizacion | null;
    grupos: GrupoCotizacion[];
}

export interface PedidoEditable extends PedidoDetalle {
    /** Lo mínimo que puede quedar abonado (pedidos legacy con abono bajo). */
    abono_minimo: number;
}

export interface PaginaFormularioPedido {
    pedido: PedidoEditable | null;
    cotizaciones: CotizacionDisponible[];
    cotizacion: CotizacionElegida | null;
    cotizacionPedida: number | null;
    bancos: { id: number; nombre: string }[];
    metodos: MetodoPago[];
    terminos: Terminos;
    hoy: string;
    entregaPropuesta: string;
    urls: { index: string; crear: string; guardar: string; proyeccion: string; crearCompra: string | null; cotizaciones: string };
}
