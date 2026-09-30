import { router } from '@inertiajs/react';
import { useEffect } from 'react';

import { confirmar } from '@/components/app/confirmador';

/** ¿Descartar los cambios? Diálogo del sistema (no el `confirm` del navegador). */
function preguntarDescarte(): Promise<boolean> {
    return confirmar({
        titulo: 'Cambios sin guardar',
        descripcion: 'Tienes cambios sin guardar. Si sales, se pierden.',
        accion: 'Descartar',
        cancelar: 'Seguir editando',
    });
}

// Un solo listener aunque haya varios formularios con cambios a la vez (p. ej. una
// página y un diálogo encima): así se pregunta una vez por navegación.
let activos = 0;
let quitarListeners: (() => void) | null = null;
// La visita que el usuario ya aceptó descartar se vuelve a lanzar; esta marca evita preguntar dos veces.
let visitaAceptada = false;

function instalar() {
    const alSalir = (e: BeforeUnloadEvent) => e.preventDefault();
    window.addEventListener('beforeunload', alSalir);
    // Navegación Inertia (<Link>, router.visit) que no sea la propia recarga parcial.
    // El evento no espera una promesa: se cancela y, si el usuario descarta, se repite la visita.
    const quitar = router.on('before', (evento) => {
        const visita = evento.detail.visit;
        // Recargas parciales (only/except/reset), prefetch y envíos no salen de la página.
        if (visita.only.length || visita.except.length || visita.reset.length || visita.prefetch || visita.method !== 'get') return;
        if (visitaAceptada) return;
        evento.preventDefault();
        void preguntarDescarte().then((descartar) => {
            if (!descartar) return;
            // `url` ya trae la query (data fusionada) y el hash. Los callbacks del
            // original (onSuccess, onFinish…) no viajan en la visita: no se repiten.
            const { url, replace, preserveScroll, preserveState, headers, async, preserveUrl, fresh, viewTransition, showProgress } = visita;
            // fireBeforeEvent es síncrono dentro de router.visit: la marca solo vale para esta visita.
            visitaAceptada = true;
            try {
                router.visit(url, { replace, preserveScroll, preserveState, headers, async, preserveUrl, fresh, viewTransition, showProgress });
            } finally {
                visitaAceptada = false;
            }
        });
    });

    return () => {
        window.removeEventListener('beforeunload', alSalir);
        quitar();
    };
}

/**
 * Equivalente de AtlanticoGuard (layout Blade): mientras `sucio` sea true,
 * avisa antes de navegar a otra página. Para el cierre de un diálogo, usar
 * `confirmarDescarte()` en su onOpenChange.
 *
 * Cerrar la pestaña, recargar o escribir otra URL sigue mostrando el aviso
 * propio del navegador: por seguridad, ninguna página puede reemplazarlo.
 * Atrás/adelante del navegador NO se protege: Inertia restaura el historial
 * sin disparar `before` (ni el guard Blade anterior lo cubría).
 */
export function useGuardCambios(sucio: boolean) {
    useEffect(() => {
        if (!sucio) return;
        if (activos++ === 0) quitarListeners = instalar();

        return () => {
            if (--activos === 0) {
                quitarListeners?.();
                quitarListeners = null;
            }
        };
    }, [sucio]);
}

/** ¿Se puede cerrar? Si hay cambios, pregunta con el diálogo del sistema. */
export function confirmarDescarte(sucio: boolean): Promise<boolean> {
    return sucio ? preguntarDescarte() : Promise.resolve(true);
}
