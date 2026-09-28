import type { ReactNode } from 'react';

import {
    AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription,
    AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger,
} from '@/components/ui/alert-dialog';

interface Props {
    titulo: string;
    descripcion: ReactNode;
    accion?: string;
    onConfirmar: () => void;
    /** Modo con disparador: el elemento que abre la confirmación (un botón suelto). */
    children?: ReactNode;
    /**
     * Modo controlado: abrir desde fuera. Úsalo cuando la acción sale de un menú
     * (DropdownMenu): si la confirmación vive DENTRO del menú, el menú queda
     * abierto al confirmar y deja la página inaccesible.
     */
    abierto?: boolean;
    onCerrar?: () => void;
}

/** Confirmación para acciones destructivas (eliminar, anular). Reemplaza al modal danger + SweetAlert. */
export function ConfirmarPeligro({ children, titulo, descripcion, accion = 'Eliminar', onConfirmar, abierto, onCerrar }: Props) {
    const controlado = abierto !== undefined;

    return (
        <AlertDialog {...(controlado ? { open: abierto, onOpenChange: (a: boolean) => !a && onCerrar?.() } : {})}>
            {!controlado && <AlertDialogTrigger asChild>{children}</AlertDialogTrigger>}
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{titulo}</AlertDialogTitle>
                    <AlertDialogDescription>{descripcion}</AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                    <AlertDialogAction
                        onClick={onConfirmar}
                        className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                    >
                        {accion}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
