/**
 * Datos que HandleInertiaRequests::share() entrega a TODAS las páginas.
 * Si cambias el middleware, cambia esto: el compilador marcará cada uso.
 */
export interface UsuarioAutenticado {
    id: number;
    name: string;
    email: string;
    /** Foto subida; null → se muestran las iniciales. */
    avatar_url: string | null;
    rol: string | null;
}

export interface EnlaceNavegacion {
    titulo: string;
    icono: string;
    url: string;
    ruta: string;
    /** La página destino ya es Inertia (navegar con <Link>); si no, recarga completa. */
    inertia: boolean;
}

export interface GrupoNavegacion {
    titulo: string;
    icono: string;
    items: ItemNavegacion[];
}

export type ItemNavegacion = EnlaceNavegacion | GrupoNavegacion;

export interface DatosCompartidos {
    app: { nombre: string };
    auth: {
        user: UsuarioAutenticado | null;
        esAdmin: boolean;
        permisos: string[];
    };
    navegacion: ItemNavegacion[];
    tasaBcv: { valor: number; fecha: string } | null;
    flash: { success: string | null; error: string | null };
    [key: string]: unknown;
}

/** LengthAwarePaginator de Laravel tal como lo serializa Inertia. */
export interface Paginado<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: { url: string | null; label: string; active: boolean }[];
}
