import { usePage } from '@inertiajs/react';

/**
 * ¿El usuario puede 'modulo.accion'? Espejo de tienePermiso() en PHP: el
 * Administrador puede todo; el resto, según permiso_rol.
 *
 * Solo decide qué se MUESTRA. La autorización real la hace el servidor
 * (middleware CheckPermiso): ocultar un botón no protege nada.
 */
export function usePermisos() {
    const { auth } = usePage().props;
    const puede = (permiso: string) => auth.esAdmin || auth.permisos.includes(permiso);

    return { puede };
}
