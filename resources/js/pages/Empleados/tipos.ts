import type { Telefono } from '@/components/app/campo-telefonos';
import type { Paginado } from '@/types';

/**
 * Espejo de EmpleadoController::fila(). EmpleadoPaginaTest falla si el
 * servidor manda una clave de más o de menos: al cambiar uno, cambia el otro.
 */
export interface EmpleadoFila {
    id: number;
    codigo: string;
    tipo_documento: string | null;
    numero_documento: string | null;
    documento: string | null;
    nombre: string | null;
    email: string | null;
    telefonos: Telefono[];
    direccion: string | null;
    estado_territorial: string | null;
    ciudad: string | null;
    fecha_nacimiento: string | null;
    genero: 'M' | 'F' | null;
    fecha_ingreso: string | null;
    departamento_id: number | null;
    departamento: string | null;
    cargo_id: number | null;
    cargo: string | null;
    /** La persona también es cliente/proveedor: editarla cambia esos registros. */
    otros_roles: ('cliente' | 'proveedor')[];
    inhabilitado: boolean;
    creado: string | null;
}

// `type` (no interface): así es asignable a Record<string, …> que usa useFiltrosUrl.
export type FiltrosEmpleados = {
    buscar?: string;
    departamento?: string;
    cargo?: string;
    orden?: 'recientes' | 'codigo' | 'nombre_asc' | 'nombre_desc';
    historial?: string;
};

export interface Opcion {
    id: number;
    nombre: string;
}

export interface PaginaEmpleados {
    empleados: Paginado<EmpleadoFila>;
    filtros: FiltrosEmpleados;
    departamentos: Opcion[];
    cargos: (Opcion & { departamento_id: number })[];
    /** Estado → municipios (catálogo geográfico). */
    estados: Record<string, string[]>;
    urls: {
        index: string;
        reportePdf: string;
        checkDocumento: string;
        checkEmail: string;
        departamentos: string;
        cargos: string;
    };
}

export const GENERO = { M: 'Masculino', F: 'Femenino' } as const;
