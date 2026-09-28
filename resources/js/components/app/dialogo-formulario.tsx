import type { FormEvent, ReactNode } from 'react';

import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { confirmarDescarte, useGuardCambios } from '@/hooks/use-guard-cambios';

interface Props {
    abierto: boolean;
    onCerrar: () => void;
    titulo: string;
    descripcion?: string;
    /** form.isDirty: activa el aviso de cambios sin guardar. */
    sucio: boolean;
    procesando: boolean;
    onGuardar: () => void;
    textoGuardar: string;
    className?: string;
    children: ReactNode;
}

/**
 * Estructura común de un formulario en diálogo: título, cuerpo, Cancelar/Guardar
 * y aviso de cambios sin guardar (al cerrar el diálogo o salir de la página).
 * Montarlo con una `key` por apertura (ver docs/conventions/frontend.md).
 */
export function DialogoFormulario({ abierto, onCerrar, titulo, descripcion, sucio, procesando, onGuardar, textoGuardar, className, children }: Props) {
    useGuardCambios(abierto && sucio);

    const cerrar = (abrir: boolean) => {
        if (!abrir && confirmarDescarte(sucio)) onCerrar();
    };
    const enviar = (e: FormEvent) => {
        e.preventDefault();
        onGuardar();
    };

    return (
        <Dialog open={abierto} onOpenChange={cerrar}>
            <DialogContent className={className ?? 'sm:max-w-md'}>
                <DialogHeader>
                    <DialogTitle>{titulo}</DialogTitle>
                    {descripcion && <DialogDescription>{descripcion}</DialogDescription>}
                </DialogHeader>
                <form onSubmit={enviar} className="grid gap-4" noValidate>
                    {children}
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => cerrar(false)}>Cancelar</Button>
                        <Button type="submit" disabled={procesando}>{procesando ? 'Guardando…' : textoGuardar}</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
