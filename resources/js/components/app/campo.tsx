import { useId, type ReactElement, cloneElement } from 'react';

import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

interface Props {
    etiqueta: string;
    /** Mensaje de error del servidor (form.errors.campo de useForm). */
    error?: string;
    ayuda?: string;
    requerido?: boolean;
    className?: string;
    children: ReactElement<{ id?: string; 'aria-invalid'?: boolean; 'aria-describedby'?: string }>;
}

/**
 * Etiqueta + control + error del servidor, con accesibilidad resuelta
 * (id, aria-invalid, aria-describedby). Los errores vienen de Laravel
 * (FormRequest) vía useForm: no se duplica la validación en el cliente.
 */
export function Campo({ etiqueta, error, ayuda, requerido, className, children }: Props) {
    const id = useId();
    const idMensaje = `${id}-mensaje`;

    return (
        <div className={cn('grid gap-1.5', className)}>
            <Label htmlFor={id}>
                {etiqueta}
                {requerido && <span className="text-destructive"> *</span>}
            </Label>
            {cloneElement(children, {
                id,
                'aria-invalid': error ? true : undefined,
                'aria-describedby': error || ayuda ? idMensaje : undefined,
            })}
            {error ? (
                <p id={idMensaje} className="text-destructive text-xs">{error}</p>
            ) : ayuda ? (
                <p id={idMensaje} className="text-muted-foreground text-xs">{ayuda}</p>
            ) : null}
        </div>
    );
}
