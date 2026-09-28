import type { Paginado } from '@/types';

/**
 * Espejo de ProveedorController::fila(). ProveedorPaginaTest falla si el
 * servidor manda una clave de más o de menos: al cambiar uno, cambia el otro.
 */
export interface TelefonoProveedor {
    numero: string;
    tipo: 'movil' | 'casa' | 'trabajo';
    es_principal: boolean;
}

export interface ProveedorFila {
    id: number;
    tipo: 'natural' | 'juridico';
    tipo_documento: string | null;
    numero_documento: string | null;
    documento: string | null;
    nombre: string | null;
    email: string | null;
    telefonos: TelefonoProveedor[];
    direccion: string | null;
    estado_territorial: string | null;
    ciudad: string | null;
    contacto: string | null;
    telefono_contacto: string | null;
    inhabilitado: boolean;
    creado: string | null;
}

// `type` (no interface): así es asignable a Record<string, …> que usa useFiltrosUrl.
export type FiltrosProveedores = {
    buscar?: string;
    tipo?: 'natural' | 'juridico';
    estado?: string;
    orden?: 'recientes' | 'antiguos' | 'nombre_asc' | 'nombre_desc';
    historial?: string;
};

export interface PaginaProveedores {
    proveedores: Paginado<ProveedorFila>;
    filtros: FiltrosProveedores;
    /** Estado → municipios (config/catálogo geográfico). */
    estados: Record<string, string[]>;
    urls: {
        index: string;
        reportePdf: string;
        checkDocumento: string;
        checkRif: string;
        checkEmail: string;
    };
}
