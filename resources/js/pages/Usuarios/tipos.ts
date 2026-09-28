import type { Paginado } from '@/types';

/**
 * Espejo de UserController::fila(). UsuarioPaginaTest falla si el
 * servidor manda una clave de más o de menos: al cambiar uno, cambia el otro.
 */
export interface UsuarioFila {
    id: number;
    nombre: string;
    email: string;
    rol_id: number | null;
    rol: string | null;
    /** URL de la foto subida; null → se muestran las iniciales. */
    avatar: string | null;
    /** Los usuarios nunca se borran: inhabilitado = sin acceso (estado 0). */
    inhabilitado: boolean;
    recuperacion_bloqueada: boolean;
    intentos_fallidos: number;
    /** El administrador le asignó una clave temporal: la cambia al entrar. */
    debe_cambiar_clave: boolean;
    es_propio: boolean;
    creado: string | null;
}

// `type` (no interface): así es asignable a Record<string, …> que usa useFiltrosUrl.
export type FiltrosUsuarios = {
    buscar?: string;
    rol?: string;
    historial?: string;
};

export interface PaginaUsuarios {
    usuarios: Paginado<UsuarioFila>;
    filtros: FiltrosUsuarios;
    roles: { id: number; nombre: string }[];
    urls: { index: string; reportePdf: string; checkEmail: string };
}

export const iniciales = (nombre: string) =>
    nombre.split(/\s+/).filter(Boolean).slice(0, 2).map((p) => p[0]?.toUpperCase()).join('');
