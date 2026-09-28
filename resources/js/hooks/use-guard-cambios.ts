import { router } from '@inertiajs/react';
import { useEffect } from 'react';

const AVISO = 'Tienes cambios sin guardar. ¿Salir y descartarlos?';

/**
 * Equivalente de AtlanticoGuard (layout Blade): mientras `sucio` sea true,
 * avisa antes de cerrar la pestaña o navegar a otra página. Para el cierre
 * de un diálogo, usar `confirmarDescarte()` en su onOpenChange.
 */
export function useGuardCambios(sucio: boolean) {
    useEffect(() => {
        if (!sucio) return;

        const alSalir = (e: BeforeUnloadEvent) => e.preventDefault();
        window.addEventListener('beforeunload', alSalir);
        // Navegación Inertia (<Link>, router.visit) que no sea la propia recarga parcial.
        const quitar = router.on('before', (evento) => {
            const visita = evento.detail.visit;
            if (visita.only.length === 0 && visita.method === 'get' && !window.confirm(AVISO)) {
                evento.preventDefault();
            }
        });

        return () => {
            window.removeEventListener('beforeunload', alSalir);
            quitar();
        };
    }, [sucio]);
}

/** ¿Se puede cerrar? Si hay cambios, pregunta. */
export function confirmarDescarte(sucio: boolean): boolean {
    return !sucio || window.confirm(AVISO);
}
