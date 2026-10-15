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
 * Atrás del navegador. Inertia restaura el historial en `popstate` sin disparar
 * `before`, así que con cambios se apila una entrada «centinela» con la misma URL:
 * Atrás solo la quita (la página no cambia) y este listener frena a Inertia y
 * pregunta. Seguir editando vuelve a la centinela; Descartar retrocede de verdad.
 * Mientras hay centinela, Adelante no tiene destino.
 *
 * Con la Navigation API (Chromium, Safari y Firefox recientes) se sabe cuántas
 * entradas retrocedió el usuario (dos Atrás seguidos, clic largo en Atrás); sin
 * ella se asume una.
 */
let centinela: string | null = null;
let indiceCentinela: number | null = null;
let preguntando = false;
// El próximo popstate lo provocamos nosotros: Inertia no lo ve.
let tragar: (() => void) | null = null;
// El Atrás que el usuario ya aceptó (Descartar) lo procesa Inertia sin preguntar.
let dejarPasar = false;
// Descartar puede salir del sitio (primera página de la pestaña): sin el aviso del navegador encima.
let saliendo = false;
// Un envío con centinela ocupa su entrada (replace): no se retira hasta que termine.
let visitaEnVuelo = false;
let retiroTrasVisita = false;
let quitarCentinelaPendiente: ReturnType<typeof setTimeout> | undefined;

const indiceActual = () => ('navigation' in window ? (window.navigation.currentEntry?.index ?? null) : null);

/** URL sin #ancla: Configuración cambia el hash con replaceState sin salir de la página. */
const urlActual = () => location.origin + location.pathname + location.search;

function alPopstate(e: PopStateEvent) {
    if (tragar) {
        e.stopImmediatePropagation();
        const f = tragar;
        tragar = null;
        f();
        return;
    }
    if (dejarPasar) {
        dejarPasar = false;
        saliendo = false; // un popstate del mismo documento: no se salió del sitio
        return;
    }
    // Otro Atrás con el aviso abierto: se resuelve al responder, según dónde quedó el historial.
    if (preguntando) {
        e.stopImmediatePropagation();
        return;
    }
    // state null: navegación a un #ancla de la misma página, no el historial de Inertia.
    if (centinela === null || activos === 0 || e.state === null) return;
    e.stopImmediatePropagation();
    preguntando = true;
    void preguntarDescarte().then((descartar) => {
        preguntando = false;
        if (activos === 0 || centinela === null) return;
        const indice = indiceActual();
        const retroceso = indiceCentinela !== null && indice !== null ? indiceCentinela - indice : null;
        if (!descartar) {
            if (retroceso !== null && retroceso > 0) {
                // Vuelve a la misma centinela: se conserva lo que había detrás.
                tragar = () => {};
                history.go(retroceso);
            } else {
                ponerCentinela();
            }
            return;
        }
        const url = centinela;
        centinela = null;
        indiceCentinela = null;
        const enLaPaginaReal = retroceso !== null ? retroceso === 1 : urlActual() === url;
        if (!enLaPaginaReal) {
            // Retrocedió más de una entrada: ya está donde pidió; que Inertia pinte esa página.
            dejarPasar = true;
            window.dispatchEvent(new PopStateEvent('popstate', { state: history.state as unknown }));
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
    router.on('finish', () => {
        if (!visitaEnVuelo) return;
        visitaEnVuelo = false;
        if (!retiroTrasVisita) return;
        retiroTrasVisita = false;
        quitarCentinelaPendiente = setTimeout(quitarCentinela, 0);
    });
}

function ponerCentinela() {
    history.pushState(history.state, '', location.href);
    centinela = urlActual();
    indiceCentinela = indiceActual();
}

/** Sin cambios y aún en la misma página: se retira la centinela para no dejar una entrada repetida. */
function quitarCentinela() {
    quitarCentinelaPendiente = undefined;
    if (activos > 0) return; // se volvió a instalar: sigue valiendo
    if (visitaEnVuelo) {
        retiroTrasVisita = true;
        return;
    }
    const url = centinela;
    const indice = indiceCentinela;
    centinela = null;
    indiceCentinela = null;
    // Si una navegación ocupó la entrada (replace a otra URL), ya no hay centinela que quitar.
    if (url === null || urlActual() !== url) return;
    if (indice !== null && indiceActual() !== indice) return;
    const estado: unknown = history.state;
    const href = location.href; // con el #hash de ahora, no el de la entrada de abajo
    tragar = () => {
        history.replaceState(estado, '', href);
        // Si el formulario volvió a tener cambios mientras se retiraba (p. ej. un envío rechazado), se repone.
        if (activos > 0) ponerCentinela();
    };
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
            // La página nueva ocupa la entrada de la centinela (no queda repetida en el historial);
            // por eso la centinela no se retira hasta que la visita termine (ver vigilarHistorial).
            if (centinela !== null) {
                visita.replace = true;
                visitaEnVuelo = true;
            }
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
    retiroTrasVisita = false;
    // Con un retiro en vuelo, lo repone `tragar` al terminar (apilar ahora caería antes del back()).
    if (centinela !== urlActual() && !tragar) ponerCentinela();

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
