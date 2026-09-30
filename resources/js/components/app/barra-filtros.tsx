import type { ComponentProps } from 'react';

import { cn } from '@/lib/utils';

/**
 * Barra de búsqueda y filtros de un listado, con el tinte de la sección (los «filtros
 * avanzados» navy/emerald/sky del panel anterior): fondo apenas teñido y riel izquierdo.
 * Va justo encima de la tabla; los controles adentro son los de siempre.
 */
export function BarraFiltros({ className, ...props }: ComponentProps<'div'>) {
    return (
        <div
            role="search"
            className={cn(
                'border-seccion-acento/15 border-l-seccion-acento flex flex-wrap items-center gap-2 rounded-lg border border-l-[3px] bg-linear-to-r from-seccion-acento/[0.06] to-seccion-acento/[0.015] p-2.5',
                // Los controles traen fondo transparente: sobre el tinte, en el color de tarjeta.
                '[&_[data-slot=input]]:bg-card [&_[data-slot=select-trigger]]:bg-card',
                className,
            )}
            {...props}
        />
    );
}
