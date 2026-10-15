import { useCallback, useState } from 'react';

/** Misma clave y valores que el layout Blade ('lg' | 'sm'): la preferencia se conserva. */
const CLAVE = 'sgpmrja-sidebar-size';

function leer(): boolean {
    try {
        return localStorage.getItem(CLAVE) === 'sm';
    } catch {
        return false; // almacenamiento bloqueado: menú expandido
    }
}

/** Menú lateral colapsado (solo íconos) en escritorio; se recuerda entre visitas. */
export function useMenuColapsado() {
    const [colapsado, setColapsado] = useState(leer);

    const cambiar = useCallback((valor: boolean) => {
        setColapsado(valor);
        try {
            localStorage.setItem(CLAVE, valor ? 'sm' : 'lg');
        } catch {
            // modo privado: dura lo que dure la página
        }
    }, []);

    return { colapsado, setColapsado: cambiar, alternar: () => cambiar(!colapsado) };
}
