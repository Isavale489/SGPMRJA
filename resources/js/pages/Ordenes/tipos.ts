import type { Paginado } from '@/types';

export type EstadoOrden = 'Pendiente' | 'En Proceso' | 'Finalizado' | 'Cancelado';

/** Espejo de OrdenProduccionController::pedidosConOrdenes() (lo verifica OrdenesPaginaTest). */
export interface PedidoConOrdenes {
    pedido_id: number | null;
    cliente: string | null;
    total_ordenes: number;
    pendientes: number;
    en_proceso: number;
    finalizadas: number;
    canceladas: number;
    solicitado: number;
    producido: number;
    entrega: string | null;
}

export interface MiembroEquipo {
    id: number;
    nombre: string;
    cantidad: number;
    producida: number;
    defectuosa: number;
}

/** Espejo de OrdenProduccionController::ordenesDePedido(). */
export interface OrdenFila {
    id: number;
    producto: string;
    variante: string | null;
    equipo: MiembroEquipo[];
    cantidad_solicitada: number;
    cantidad_producida: number;
    cantidad_defectuosa: number;
    estado: EstadoOrden;
    fecha_inicio: string | null;
    fecha_fin_estimada: string | null;
    etapas: number;
    creado_por: string | null;
}

export interface Etapa {
    id: number;
    nombre: string;
    cantidad: number | null;
    estado: EstadoOrden;
    notas: string | null;
    empleados: { id: number; nombre: string; rol: string | null }[];
}

/** Espejo de OrdenProduccionController::detalle(). */
export interface OrdenDetalle {
    id: number;
    pedido_id: number | null;
    pedido_cancelado: boolean;
    cliente: string | null;
    cliente_documento: string | null;
    producto: string;
    variante: string | null;
    imagen: string | null;
    estado: EstadoOrden;
    cantidad_solicitada: number;
    cantidad_producida: number;
    cantidad_defectuosa: number;
    fecha_inicio: string | null;
    fecha_fin_estimada: string | null;
    fecha_fin_real: string | null;
    notas: string | null;
    motivo_cancelacion: string | null;
    creado: string | null;
    creador: { nombre: string; avatar: string | null } | null;
    equipo: MiembroEquipo[];
    insumos: { id: number; nombre: string; unidad: string; estimada: number }[];
    bordados: { id: number; aplicacion: string | null; logo: string | null; cantidad: number }[];
    etapas: Etapa[];
}

/** Espejo de OrdenProduccionController::ordenesDeEmpleado(). */
export interface OrdenDeEmpleado {
    id: number;
    pedido_id: number | null;
    producto: string;
    estado: EstadoOrden;
    cantidad_solicitada: number;
    cantidad_producida: number;
    mi_cantidad: number | null;
    mi_producida: number | null;
    fecha_fin_estimada: string | null;
}

export interface Empleado {
    id: number;
    nombre: string;
}

export type FiltrosOrdenes = {
    buscar?: string;
    estado?: EstadoOrden;
    desde?: string;
    hasta?: string;
    orden?: 'recientes' | 'progreso_desc' | 'progreso_asc';
};

export interface PaginaOrdenes {
    filtros: FiltrosOrdenes & { pedido?: string; ver?: string; empleado?: string };
    registros: Paginado<PedidoConOrdenes>;
    ordenes: OrdenFila[] | null;
    orden: OrdenDetalle | null;
    misOrdenes: { empleado: Empleado; ordenes: OrdenDeEmpleado[] } | null;
    empleados: Empleado[];
    urls: { index: string; crear: string; reportePdf: string };
}

/** Espejo de OrdenProduccionController::pedidosDisponibles(). */
export interface LineaDisponible {
    detalle_id: number;
    producto: string;
    variante: string | null;
    bordados: number;
    cantidad: number;
    pendiente: number;
    insumos: { id: number; nombre: string; unidad: string; por_unidad: number }[];
}

export interface PedidoDisponible {
    id: number;
    cliente: string | null;
    cliente_documento: string | null;
    fecha_pedido: string | null;
    fecha_entrega: string | null;
    estado: string;
    porcentaje_abonado: number;
    cumple_abono: boolean;
    lineas: LineaDisponible[];
}

export interface InsumoProduccion {
    id: number;
    nombre: string;
    unidad: string;
    stock: number;
}

export interface PaginaFormularioOrdenes {
    pedidos: PedidoDisponible[];
    empleados: Empleado[];
    insumos: InsumoProduccion[];
    abonoMinimo: number;
    urls: { index: string; guardar: string; proyeccion: string; crearCompra: string | null };
}

export interface OrdenEditable {
    id: number;
    pedido_id: number | null;
    cliente: string | null;
    producto: string;
    variante: string | null;
    estado: EstadoOrden;
    cantidad: number;
    cantidad_producida: number;
    /** Producido o rechazado en Calidad (la tela ya se cortó). */
    con_produccion: boolean;
    cantidad_maxima: number;
    fecha_inicio: string | null;
    fecha_fin_estimada: string | null;
    notas: string | null;
    equipo: MiembroEquipo[];
    insumos: { id: number; nombre: string; unidad: string; estimada: number }[];
}

export interface PaginaEditarOrden {
    orden: OrdenEditable;
    empleados: Empleado[];
    urls: { index: string; guardar: string };
}

export const progreso = (producida: number, solicitada: number) => (solicitada > 0 ? Math.min(100, Math.round((producida / solicitada) * 100)) : 0);

/** Qué muestra el diálogo de una orden abierta (?ver=ID). */
export type ModoOrden = 'ver' | 'avance' | 'etapas';

export const tituloPedido = (clave: string) => (clave === 'manual' ? 'Órdenes manuales' : `Pedido #${clave}`);
