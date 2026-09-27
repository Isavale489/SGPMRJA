import type { ReactNode } from 'react';

import {
    AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription,
    AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger,
} from '@/components/ui/alert-dialog';

interface Props {
    /** Elemento que abre la confirmación (normalmente un botón). */
    children: ReactNode;
    titulo: string;
    descripcion: ReactNode;
    accion?: string;
    onConfirmar: () => void;
}

/** Confirmación para acciones destructivas (eliminar, anular). Reemplaza al modal danger + SweetAlert. */
export function ConfirmarPeligro({ children, titulo, descripcion, accion = 'Eliminar', onConfirmar }: Props) {
    return (
        <AlertDialog>
            <AlertDialogTrigger asChild>{children}</AlertDialogTrigger>
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
