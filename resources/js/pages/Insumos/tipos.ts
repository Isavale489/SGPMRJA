import type { Paginado } from '@/types';

/**
 * Espejo de InsumoController::fila(). InsumoPaginaTest falla si el
 * servidor manda una clave de más o de menos: al cambiar uno, cambia el otro.
 */
export interface InsumoFila {
    id: number;
    nombre: string;
    codigo: string | null;
    tipo: string;
    unidad_medida: string;
    /** Vinculante: si es false, no lleva stock ni admite movimientos. */
    is_inventoriable: boolean;
    aplica_iva: boolean;
    costo_unitario: number;
    stock_actual: number;
    stock_minimo: number;
    stock_maximo: number;
    /** null si no es inventariable. */
    nivel_stock: 'bajo' | 'medio' | 'normal' | null;
    inhabilitado: boolean;
    creado: string | null;
}

export interface TipoInsumoFila {
    id: number;
    nombre: string;
    insumos: number;
    activo: boolean;
    inhabilitado: boolean;
}

// `type` (no interface): así es asignable a Record<string, …> que usa useFiltrosUrl.
export type FiltrosInsumos = {
    buscar?: string;
    tipo?: string;
    stock?: 'con_stock' | 'agotado' | 'bajo';
    orden?: 'recientes' | 'nombre' | 'mayor_costo' | 'menor_costo' | 'mayor_stock' | 'menor_stock';
    historial?: string;
};

export interface PaginaInsumos {
    insumos: Paginado<InsumoFila>;
    filtros: FiltrosInsumos;
    tiposInsumo: TipoInsumoFila[];
    unidades: string[];
    urls: { index: string; reportePdf: string; checkNombre: string; tipos: string };
}
