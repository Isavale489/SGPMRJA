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

/*
 * Atrás/adelante del navegador. Inertia restaura el historial en `popstate` sin
 * disparar `before`, así que con cambios se apila una entrada «centinela» con la
 * misma URL: Atrás solo quita la centinela (la página no cambia) y este listener
 * frena a Inertia y pregunta. Seguir editando la vuelve a poner;
 * Descartar retrocede de verdad. Mientras hay centinela, Adelante no tiene destino.
 */
let centinela: string | null = null;
// El Atrás que el usuario ya aceptó (Descartar) lo procesa Inertia sin preguntar.
let dejarPasar = false;
// Descartar puede salir del sitio (primera página de la pestaña): sin el aviso del navegador encima.
let saliendo = false;
// Quitar la centinela al limpiar el formulario: ese Atrás no debe repintar la página.
let alQuitarCentinela: (() => void) | null = null;
let quitarCentinelaPendiente: ReturnType<typeof setTimeout> | undefined;

function alPopstate(e: PopStateEvent) {
    if (alQuitarCentinela) {
        e.stopImmediatePropagation();
        const reponer = alQuitarCentinela;
        alQuitarCentinela = null;
        reponer();
        return;
    }
    if (dejarPasar) {
        dejarPasar = false;
        return;
    }
    // state null: navegación a un #ancla de la misma página, no el historial de Inertia.
    if (centinela === null || activos === 0 || e.state === null) return;
    e.stopImmediatePropagation();
    centinela = null;
    void preguntarDescarte().then((descartar) => {
        if (activos === 0) return;
        if (!descartar) {
            ponerCentinela();
            return;
        }
        saliendo = true;
        dejarPasar = true;
        history.back();
        // Sin página anterior en la pestaña, back() no hace nada: se vuelve a proteger.
        setTimeout(() => {
            if (!dejarPasar || activos === 0) return;
            dejarPasar = false;
            saliendo = false;
            ponerCentinela();
        }, 1000);
    });
}

/**
 * Se llama en inertia.tsx ANTES de createInertiaApp: los listeners de `window`
 * corren en el orden en que se registraron (también los de captura, en Chromium),
 * y este tiene que poder frenar el `popstate` de Inertia.
 */
export function vigilarHistorial() {
    window.addEventListener('popstate', alPopstate);
}

/** URL sin #ancla: Configuración cambia el hash con replaceState sin salir de la página. */
const urlActual = () => location.origin + location.pathname + location.search;

function ponerCentinela() {
    history.pushState(history.state, '', location.href);
    centinela = urlActual();
}

/** Sin cambios y aún en la misma página: se retira la centinela para no dejar una entrada repetida. */
function quitarCentinela() {
    quitarCentinelaPendiente = undefined;
    const url = centinela;
    centinela = null;
    if (url === null || urlActual() !== url) return;
    const estado: unknown = history.state;
    alQuitarCentinela = () => history.replaceState(estado, '', location.href);
    history.back();
}

function instalar() {
    const alSalir = (e: BeforeUnloadEvent) => {
        if (!saliendo) e.preventDefault();
    };
    window.addEventListener('beforeunload', alSalir);
    // Navegación Inertia (<Link>, router.visit) que no sea la propia recarga parcial.
    // El evento no espera una promesa: se cancela y, si el usuario descarta, se repite la visita.
    const quitar = router.on('before', (evento) => {
        const visita = evento.detail.visit;
        // Recargas parciales (only/except/reset) y prefetch no salen de la página.
        if (visita.only.length || visita.except.length || visita.reset.length || visita.prefetch) return;
        if (visita.method !== 'get' || visitaAceptada) {
            // La página nueva ocupa la entrada de la centinela (no queda repetida en el historial).
            if (centinela !== null) visita.replace = true;
            return;
        }
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

    // Un desmontaje y montaje seguidos (StrictMode, `sucio` que parpadea) conservan la misma centinela.
    if (quitarCentinelaPendiente !== undefined) {
        clearTimeout(quitarCentinelaPendiente);
        quitarCentinelaPendiente = undefined;
    }
    if (centinela !== urlActual()) ponerCentinela();

    return () => {
        window.removeEventListener('beforeunload', alSalir);
        quitar();
        saliendo = false;
        // Diferido: si fue una navegación, para entonces la URL ya cambió y no hay nada que quitar.
        quitarCentinelaPendiente = setTimeout(quitarCentinela, 0);
    };
}

/**
 * Equivalente de AtlanticoGuard (layout Blade): mientras `sucio` sea true,
 * avisa antes de navegar a otra página, también con Atrás del navegador. Para
 * el cierre de un diálogo, usar `confirmarDescarte()` en su onOpenChange.
 *
 * Cerrar la pestaña, recargar o escribir otra URL sigue mostrando el aviso
 * propio del navegador: por seguridad, ninguna página puede reemplazarlo.
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
