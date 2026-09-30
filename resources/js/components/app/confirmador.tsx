import { useSyncExternalStore, type ReactNode } from 'react';

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
const oyentes = new Set<() => void>();
const avisar = () => oyentes.forEach((o) => o());

/**
 * Reemplazo de `window.confirm` con el diálogo del sistema. Resuelve true si
 * el usuario confirma; false si desiste, pulsa Escape o hace clic fuera.
 */
export function confirmar(opciones: OpcionesConfirmar): Promise<boolean> {
    // Sin <Confirmador /> montado (no debería pasar fuera de AppLayout) se cae al del navegador.
    if (!montado) return Promise.resolve(window.confirm(typeof opciones.descripcion === 'string' ? opciones.descripcion : opciones.titulo));
    pendiente?.responder(false);
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

    return (
        <AlertDialog open={actual !== null} onOpenChange={(abierto) => !abierto && actual?.responder(false)}>
            {actual && (
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>{actual.titulo}</AlertDialogTitle>
                        <AlertDialogDescription>{actual.descripcion}</AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>{actual.cancelar ?? 'Cancelar'}</AlertDialogCancel>
                        <AlertDialogAction
                            onClick={() => actual.responder(true)}
                            className={actual.destructiva === false ? undefined : 'bg-destructive text-destructive-foreground hover:bg-destructive/90'}
                        >
                            {actual.accion ?? 'Aceptar'}
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            )}
        </AlertDialog>
    );
}
