import { useCallback, useSyncExternalStore } from 'react';

export type Tema = 'light' | 'dark';

/** Misma clave que el layout Blade: el tema se conserva al pasar entre páginas viejas y nuevas. */
const CLAVE = 'sgpmrja-theme';
const EVENTO = 'sgpmrja-tema';

function leer(): Tema {
    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
}

function suscribir(avisar: () => void) {
    window.addEventListener(EVENTO, avisar);
    return () => window.removeEventListener(EVENTO, avisar);
}

/** Tema claro/oscuro. El valor inicial lo aplica resources/views/inertia.blade.php antes del primer pintado. */
export function useTema() {
    const tema = useSyncExternalStore(suscribir, leer, () => 'light' as Tema);

    const setTema = useCallback((nuevo: Tema) => {
        document.documentElement.classList.toggle('dark', nuevo === 'dark');
        try {
            localStorage.setItem(CLAVE, nuevo);
        } catch {
            // modo privado / almacenamiento bloqueado: el tema dura la sesión
        }
        window.dispatchEvent(new Event(EVENTO));
    }, []);

    return { tema, setTema, alternar: () => setTema(tema === 'dark' ? 'light' : 'dark') };
}
