import type { Telefono } from '@/components/app/campo-telefonos';
import type { Paginado } from '@/types';

export type TipoCliente = 'natural' | 'juridico' | 'gubernamental';

/**
 * Espejo de ClienteController::fila(). ClientePaginaTest falla si el
 * servidor manda una clave de más o de menos: al cambiar uno, cambia el otro.
 */
export interface ClienteFila {
    id: number;
    tipo: TipoCliente;
    tipo_documento: string | null;
    numero_documento: string | null;
    documento: string | null;
    nombre: string | null;
    email: string | null;
    telefonos: Telefono[];
    direccion: string | null;
    estado_territorial: string | null;
    ciudad: string | null;
    /** La persona también es empleado/proveedor: editarla cambia esos registros. */
    otros_roles: ('empleado' | 'proveedor')[];
    inhabilitado: boolean;
    creado: string | null;
}

// `type` (no interface): así es asignable a Record<string, …> que usa useFiltrosUrl.
export type FiltrosClientes = {
    buscar?: string;
    tipo?: TipoCliente;
    estado?: string;
    orden?: 'recientes' | 'antiguos' | 'nombre_asc' | 'nombre_desc';
    historial?: string;
};

export interface PaginaClientes {
    clientes: Paginado<ClienteFila>;
    filtros: FiltrosClientes;
    /** Estado → municipios (catálogo geográfico). */
    estados: Record<string, string[]>;
    urls: {
        index: string;
        reportePdf: string;
        checkDocumento: string;
        checkEmail: string;
    };
}

export const TIPOS_CLIENTE: { valor: TipoCliente; etiqueta: string }[] = [
    { valor: 'natural', etiqueta: 'Natural' },
    { valor: 'juridico', etiqueta: 'Jurídico' },
    { valor: 'gubernamental', etiqueta: 'Gubernamental' },
];
export const ETIQUETA_TIPO: Record<TipoCliente, string> = { natural: 'Natural', juridico: 'Jurídico', gubernamental: 'Gubernamental' };
