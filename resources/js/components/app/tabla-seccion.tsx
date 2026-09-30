import type { ComponentProps } from 'react';

import { TableBody, TableHeader } from '@/components/ui/table';
import { cn } from '@/lib/utils';

/**
 * Cabecera de las tablas de LISTADO con la identidad de la sección (la «dt-transactional»
 * del panel anterior): fondo oscuro del grupo, texto blanco en mayúsculas y borde de acento.
 * Las tablas de detalle dentro de diálogos y formularios siguen con la cabecera clara.
 */
export function CabeceraSeccion({ className, ...props }: ComponentProps<typeof TableHeader>) {
    return (
        <TableHeader
            className={cn(
                'bg-seccion-fondo [&_tr]:border-seccion-acento [&_tr]:border-b-2 [&_tr:hover]:bg-transparent',
                '[&_th]:h-10 [&_th]:text-xs [&_th]:font-semibold [&_th]:uppercase [&_th]:tracking-wide [&_th]:text-white',
                className,
            )}
            {...props}
        />
    );
}

/** Cuerpo con filas rayadas y hover en el tono de la sección; la fila seleccionada, más marcada. */
export function CuerpoRayado({ className, ...props }: ComponentProps<typeof TableBody>) {
    return (
        <TableBody
            className={cn(
                // :where() deja el rayado sin peso: el hover y la selección le ganan también en las filas pares.
                '[&_tr:where(:nth-child(even):not([data-state=selected]))]:bg-seccion-acento/[0.045]',
                '[&_tr:hover]:bg-seccion-acento/10 [&_tr[data-state=selected]]:bg-seccion-acento/15',
                className,
            )}
            {...props}
        />
    );
}
