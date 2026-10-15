import { usePage } from '@inertiajs/react';

import { cn } from '@/lib/utils';
import { formatoBs, formatoFecha, formatoUsd } from '@/lib/formato';

interface Props {
    usd: number;
    /**
     * Tasa congelada del documento (p. ej. la de la cotización). Si falta, usa la
     * vigente del día. `fecha` null: el valor guardado no coincide con una tasa
     * BCV publicada (se muestra como «tasa guardada», sin inventar una fecha).
     */
    tasa?: { valor: number; fecha: string | null } | null;
    className?: string;
}

/**
 * Monto en USD con su equivalente en Bs y la tasa usada (con fecha).
 * Regla del sistema: todo monto en $ se muestra con su equivalente en Bs.
 */
export function Monto({ usd, tasa, className }: Props) {
    const { tasaBcv } = usePage().props;
    const t = tasa ?? tasaBcv;

    return (
        <span className={cn('inline-flex flex-col', className)}>
            <span className="text-foreground tabular font-semibold">{formatoUsd(usd)}</span>
            {t ? (
                <span className="text-muted-foreground tabular text-xs" title={t.fecha ? `Tasa BCV (${formatoFecha(t.fecha)})` : 'Tasa guardada en el documento'}>
                    {formatoBs(usd * t.valor)} · {t.fecha ? `Tasa ${formatoFecha(t.fecha)}` : 'tasa guardada'}
                </span>
            ) : (
                <span className="text-muted-foreground text-xs">Sin tasa BCV</span>
            )}
        </span>
    );
}
