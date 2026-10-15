import { formatoNumero } from '@/lib/formato';
import { cn } from '@/lib/utils';

import { NIVEL, nivel } from './tipos';

/** % de eficiencia con su semáforo (verde ≥ 90, celeste 70–89, rojo < 70). */
export function EficienciaChip({ valor, className }: { valor: number | null; className?: string }) {
    const n = NIVEL[nivel(valor)];
    return (
        <span className={cn('inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium tabular ring-1 ring-inset', n.clase, className)} title={n.etiqueta}>
            {valor === null ? 'Sin producción' : `${formatoNumero(valor)} %`}
        </span>
    );
}

/** Barra segmentada: conformes / defectuosas, proporcional a las unidades. */
export function BarraConformes({ producido, defectuoso, className }: { producido: number; defectuoso: number; className?: string }) {
    const total = producido + defectuoso;
    return (
        <div className={cn('bg-muted flex h-2 overflow-hidden rounded-full', className)} role="img" aria-label={`${producido} conformes y ${defectuoso} defectuosas`}>
            {total > 0 && <div className="bg-success h-full" style={{ width: `${(producido / total) * 100}%` }} />}
            {total > 0 && <div className="bg-destructive h-full" style={{ width: `${(defectuoso / total) * 100}%` }} />}
        </div>
    );
}
