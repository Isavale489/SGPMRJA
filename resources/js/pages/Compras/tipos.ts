import type { ExistenciaFila } from '@/components/app/tabla-existencias';
import type { ProveedorResumen } from '@/pages/Proveedores/tipos';
import type { Paginado } from '@/types';

export type EstadoCompra = 'borrador' | 'recibida' | 'anulada';

/** Espejo de CompraController::compras() (lo verifica ComprasPaginaTest). */
export interface CompraFila {
    id: number;
    numero_factura: string | null;
    proveedor: string | null;
    proveedor_doc: string | null;
    fecha: string | null;
    total: number;
    total_bs: number;
    estado: EstadoCompra;
    clonada: boolean;
    registrado_por: string | null;
    anulado_por: string | null;
    fecha_anulacion: string | null;
}

/** Espejo de CompraController::detalle(). */
export interface CompraDetalle {
    id: number;
    estado: EstadoCompra;
    clonada: boolean;
    numero_factura: string | null;
    fecha: string | null;
    observaciones: string | null;
    subtotal: number;
    iva: number;
    iva_porcentaje: number;
    total: number;
    subtotal_bs: number;
    iva_bs: number;
    total_bs: number;
    tasa: number | null;
    tasa_fecha: string | null;
    creado: string | null;
    proveedor: ProveedorResumen | null;
    registrado_por: { nombre: string; avatar: string | null };
    anulado_por: string | null;
    fecha_anulacion: string | null;
    items: {
        id: number;
        insumo: string;
        codigo: string | null;
        unidad: string | null;
        cantidad: number;
        costo: number;
        costo_bs: number;
        subtotal: number;
        subtotal_bs: number;
        aplica_iva: boolean;
    }[];
}

export type VistaCompras = 'activas' | 'anuladas' | 'existencias';

export type FiltrosCompras = {
    vista?: VistaCompras;
    buscar?: string;
    estado?: 'borrador' | 'recibida';
    proveedor?: string;
    desde?: string;
    hasta?: string;
    tipo_insumo?: string;
    alerta?: string;
    ver?: string;
};

export interface PaginaCompras {
    vista: VistaCompras;
    filtros: FiltrosCompras;
    compras: Paginado<CompraFila> | null;
    existencias: Paginado<ExistenciaFila> | null;
    detalle: CompraDetalle | null;
    proveedores: { id: number; nombre: string }[];
    tiposInsumo: string[];
    urls: { index: string; crear: string; reportePdf: string };
}

/** Espejo de CompraController::formulario(). */
export interface InsumoComprable {
    id: number;
    nombre: string;
    codigo: string | null;
    tipo: string;
    unidad: string;
    costo: number;
    aplica_iva: boolean;
    stock: number;
}

export interface CompraEditable {
    id: number;
    proveedor: ProveedorResumen | null;
    numero_factura: string | null;
    fecha_compra: string | null;
    tasa_cambio: number;
    observaciones: string | null;
    items: { insumo_id: number; cantidad: number; costo_unitario_bs: number; aplica_iva: boolean }[];
}

export interface PaginaFormularioCompra {
    compra: CompraEditable | null;
    insumos: InsumoComprable[];
    iva: number;
    tiposInsumo: string[];
    unidades: string[];
    estados: Record<string, string[]>;
    urls: {
        index: string;
        guardar: string;
        tasa: string;
        buscarProveedor: string;
        buscarPersona: string;
        proveedores: string;
        desdePersona: string;
        checkDocumento: string;
        checkRif: string;
        checkEmail: string;
        insumos: string;
        checkNombre: string;
    };
}
