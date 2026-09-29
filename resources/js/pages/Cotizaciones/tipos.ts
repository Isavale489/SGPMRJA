import type { Paginado } from '@/types';

export type EstadoCotizacion = 'Pendiente' | 'Aprobada' | 'Vencida' | 'Convertida' | 'Cancelada';
export type Prioridad = 'Normal' | 'Alta' | 'Urgente';

/** Tasa guardada en la cotización; `fecha` null si no coincide con una tasa BCV publicada. */
export type TasaGuardada = { valor: number; fecha: string | null } | null;

/** Espejo de CotizacionController::fila() (lo verifica CotizacionesPaginaTest). */
export interface CotizacionFila {
    id: number;
    cliente: string;
    cliente_doc: string | null;
    cliente_inhabilitado: boolean;
    fecha: string | null;
    validez: string | null;
    total: number;
    tasa: TasaGuardada;
    estado: EstadoCotizacion;
    prioridad: Prioridad;
}

/** Espejo de Cliente::resumenParaCotizacion() (y de clientes.search). */
export interface ClienteCotizacion {
    id: number;
    nombre: string;
    documento: string | null;
    juridico: boolean;
    telefono: string | null;
    email: string | null;
    inhabilitado: boolean;
    cotizaciones: number;
    ultima: string | null;
}

export interface Creador {
    nombre: string;
    avatar: string | null;
    fecha: string | null;
}

export interface BordadoLinea {
    ubicacion_bordado_id: number | null;
    nombre_aplicado: string;
    logo_id: number | null;
    logo: string | null;
    es_personalizada: boolean;
    precio_aplicado: number;
    cantidad: number;
}

export interface TallaLinea {
    talla_id: number;
    talla: string;
    genero_id: number;
    genero: string | null;
    cantidad: number;
    descripcion: string | null;
}

/** Espejo de App\Support\GruposCotizacion::desde(): una fila de la tabla de productos. */
export interface GrupoCotizacion {
    clave: string;
    producto_id: number | null;
    tipo_producto_id: number | null;
    insumo_tela_id: number | null;
    atributo_valor_ids: number[];
    nombre: string;
    codigo: string | null;
    variante: string;
    imagen: string | null;
    color: { id: number; nombre: string; hex: string | null } | null;
    precio_base: number;
    recargo: number;
    precio_unitario: number;
    bordados: BordadoLinea[];
    tallas: TallaLinea[];
    unidades: number;
    subtotal: number;
}

/** Espejo de CotizacionController::detalle(): lo que muestra «Ver». */
export interface CotizacionDetalle extends CotizacionFila {
    notas: string | null;
    condiciones: string | null;
    cliente_datos: ClienteCotizacion | null;
    creador: Creador | null;
    grupos: GrupoCotizacion[];
}

export interface Terminos {
    abono: number;
    dias: number;
}

export type FiltrosCotizaciones = {
    buscar?: string;
    estado?: EstadoCotizacion;
    desde?: string;
    hasta?: string;
    orden?: 'recientes' | 'total_desc' | 'total_asc';
};

export interface PaginaCotizaciones {
    registros: Paginado<CotizacionFila>;
    filtros: FiltrosCotizaciones & { ver?: string };
    detalle: CotizacionDetalle | null;
    estados: EstadoCotizacion[];
    diasVigencia: number;
    iva: number;
    terminos: Terminos;
    urls: { index: string; crear: string; reportePdf: string; buscarCliente: string; convertir: string };
}

/* ── Asistente (crear / editar) ─────────────────────────────────────────── */

export interface TipoCatalogo {
    id: number;
    nombre: string;
    prefijo: string | null;
    imagen: string | null;
    precio: number;
    requiere_tela: boolean;
    telas: { id: number; nombre: string; codigo: string | null }[];
    atributos: { id: number; nombre: string; valores: { id: number; nombre: string; codigo: string | null }[] }[];
}

export interface ColorCatalogo {
    id: number;
    nombre: string;
    grupo: string | null;
    hex: string | null;
}

export interface TallaCatalogo {
    id: number;
    nombre: string;
    grupo: string;
}

export interface UbicacionCatalogo {
    id: number;
    nombre: string;
    grupo: string;
    precio: number;
}

export interface LogoCatalogo {
    id: number;
    nombre: string;
    archivo: string | null;
}

/** Cotización que se edita (CotizacionController::propsFormulario). */
export interface CotizacionEditable {
    id: number;
    estado: EstadoCotizacion;
    cliente: ClienteCotizacion | null;
    fecha: string | null;
    validez: string | null;
    prioridad: Prioridad;
    notas: string | null;
    creador: Creador | null;
    grupos: GrupoCotizacion[];
}

export interface PaginaFormularioCotizacion {
    cotizacion: CotizacionEditable | null;
    catalogo: TipoCatalogo[];
    colores: ColorCatalogo[];
    tallas: TallaCatalogo[];
    generos: { id: number; nombre: string }[];
    ubicaciones: UbicacionCatalogo[];
    logos: LogoCatalogo[];
    maxBordados: number;
    diasVigencia: number;
    iva: number;
    terminos: Terminos;
    estadosVe: Record<string, string[]>;
    urls: {
        index: string;
        guardar: string;
        resolverVariante: string;
        proyeccion: string;
        crearCompra: string;
        buscarCliente: string;
        buscarPersona: string;
        desdePersona: string;
        clientes: string;
        checkDocumento: string;
        checkEmail: string;
        colores: string;
        logos: string;
        telas: string;
    };
}

/**
 * Un bloque de la cotización en el asistente: una variante + color + precio +
 * bordados, con sus tallas × género (cada talla × género es una línea en la BD).
 */
export interface Bloque {
    /** Clave local (no se envía). */
    id: string;
    producto_id: number | null;
    tipo_producto_id: number | null;
    insumo_tela_id: number | null;
    atributo_valor_ids: number[];
    nombre: string;
    codigo: string | null;
    variante: string;
    imagen: string | null;
    color_id: number | null;
    /** Precio base por unidad (sin el recargo del bordado). */
    precio: number;
    bordados: BordadoLinea[];
    tallas: { talla_id: number; genero_id: number; cantidad: number; descripcion: string | null }[];
}

/** Respuesta de cotizaciones.resolverVariante (ProductoController::resolverVariante). */
export interface VarianteResuelta {
    found: boolean;
    dynamic?: boolean;
    message?: string;
    producto?: { id: number | null; codigo: string; precio_base: number; imagen: string | null; tipo_nombre: string | null; tela_nombre: string | null };
}
