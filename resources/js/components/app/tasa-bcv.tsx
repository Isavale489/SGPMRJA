import { usePage } from '@inertiajs/react';
import { Landmark } from 'lucide-react';

import { formatoBs, formatoFecha } from '@/lib/formato';

/** Píldora con la tasa BCV vigente. Regla del sistema: la tasa SIEMPRE va con su fecha. */
export function TasaBcv() {
    const { tasaBcv } = usePage().props;

    if (!tasaBcv) {
        return <span className="text-muted-foreground text-xs">Tasa BCV no disponible</span>;
    }

    return (
        <span
            data-bcv-pill
            data-pildora
            className="border-border bg-card text-muted-foreground inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border px-2.5 py-1 text-xs"
        >
            <Landmark className="hidden size-3.5 sm:block" aria-hidden />
            {/* En pantallas chicas se acorta la etiqueta, nunca la fecha. */}
            <span><span className="hidden sm:inline">Tasa </span>BCV ({formatoFecha(tasaBcv.fecha)}):</span>
            <strong className="text-foreground tabular font-semibold">{formatoBs(tasaBcv.valor)}</strong>
        </span>
    );
}
