import { router } from '@inertiajs/react';
import { useRef, useSyncExternalStore, type ReactNode } from 'react';

import {
    AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription,
    AlertDialogFooter, AlertDialogHeader, AlertDialogTitle,
} from '@/components/ui/alert-dialog';

export interface OpcionesConfirmar {
    titulo: string;
    descripcion: ReactNode;
    /** Texto del botón que confirma. */
    accion?: string;
    /** Texto del botón que desiste. */
    cancelar?: string;
    /** false: la acción no destruye nada (botón normal en vez de rojo). */
    destructiva?: boolean;
}

interface Pendiente extends OpcionesConfirmar {
    responder: (si: boolean) => void;
}

// Una sola confirmación a la vez, fuera del árbol de React: así se puede pedir
// desde un listener del router o un onOpenChange sin montar un diálogo por uso.
let pendiente: Pendiente | null = null;
let montado = false;
// Si la página cambia con la pregunta abierta (p. ej. Atrás del navegador), la pregunta ya no aplica.
let rutaAlPreguntar = '';
router.on('navigate', () => {
    if (pendiente && location.pathname !== rutaAlPreguntar) pendiente.responder(false);
});
const oyentes = new Set<() => void>();
const avisar = () => oyentes.forEach((o) => o());

/**
 * Reemplazo de `window.confirm` con el diálogo del sistema. Resuelve true si
 * el usuario confirma; false si desiste o pulsa Escape (el AlertDialog no se
 * cierra con un clic fuera).
 */
export function confirmar(opciones: OpcionesConfirmar): Promise<boolean> {
    // Sin <Confirmador /> montado (no debería pasar fuera de AppLayout) se cae al del navegador.
    if (!montado) return Promise.resolve(window.confirm(typeof opciones.descripcion === 'string' ? opciones.descripcion : opciones.titulo));
    pendiente?.responder(false);
    rutaAlPreguntar = location.pathname;
    return new Promise((resolve) => {
        let respondido = false;
        pendiente = {
            ...opciones,
            // El clic en la acción también cierra el diálogo (onOpenChange false): solo cuenta la primera respuesta.
            responder: (si) => {
                if (respondido) return;
                respondido = true;
                pendiente = null;
                avisar();
                resolve(si);
            },
        };
        avisar();
    });
}

function suscribir(oyente: () => void) {
    oyentes.add(oyente);
    montado = true;
    return () => {
        oyentes.delete(oyente);
        montado = oyentes.size > 0;
    };
}

/** Se monta una vez en AppLayout; muestra lo que pida `confirmar()`. */
export function Confirmador() {
    const actual = useSyncExternalStore(suscribir, () => pendiente);
    // La última solicitud sigue pintada mientras el diálogo se cierra (animación de salida).
    const ultima = useRef<Pendiente | null>(null);
    if (actual) ultima.current = actual;
    const visible = actual ?? ultima.current;

    return (
        <AlertDialog open={actual !== null} onOpenChange={(abierto) => !abierto && actual?.responder(false)}>
            {visible && (
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>{visible.titulo}</AlertDialogTitle>
                        <AlertDialogDescription>{visible.descripcion}</AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>{visible.cancelar ?? 'Cancelar'}</AlertDialogCancel>
                        <AlertDialogAction
                            onClick={() => visible.responder(true)}
                            className={visible.destructiva === false ? undefined : 'bg-destructive text-destructive-foreground hover:bg-destructive/90'}
                        >
                            {visible.accion ?? 'Aceptar'}
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            )}
        </AlertDialog>
    );
}
