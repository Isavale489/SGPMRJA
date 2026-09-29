import { usePage } from '@inertiajs/react';

import { formatoBs, formatoFecha, formatoNumero, formatoUsd } from '@/lib/formato';
import { cn } from '@/lib/utils';
import type { TasaGuardada } from '@/pages/Cotizaciones/tipos';

import { METODO, type Pago } from './tipos';

/** La tasa del pedido (la de su cotización); si no hay, la vigente del día. */
export function useTasaPedido(tasa: TasaGuardada | undefined) {
    const { tasaBcv } = usePage().props;
    return tasa ?? tasaBcv ?? null;
}

/** Monto en $ con su equivalente en Bs debajo. */
export function Doble({ usd, tasa, className, fuerte }: { usd: number; tasa: { valor: number } | null; className?: string; fuerte?: boolean }) {
    return (
        <span className={cn('inline-flex flex-col items-end', className)}>
            <span className={cn('tabular', fuerte && 'font-semibold')}>{formatoUsd(usd)}</span>
            {tasa && <span className="text-muted-foreground text-xs tabular">{formatoBs(usd * tasa.valor)}</span>}
        </span>
    );
}

/** Total, abonado, mínimo para formalizar y saldo, con su equivalente en Bs y la tasa con su fecha. */
export function ResumenPago({
    total,
    abono,
    minimoPorcentaje,
    minimo: minimoFijo,
    tasa,
    className,
}: {
    total: number;
    abono: number;
    minimoPorcentaje: number;
    minimo?: number;
    tasa: TasaGuardada | undefined;
    className?: string;
}) {
    const t = useTasaPedido(tasa);
    // `minimo`: el piso real (en pedidos legacy puede ser menor que el % configurado).
    const minimo = minimoFijo ?? Math.round(total * minimoPorcentaje) / 100;
    const legacy = minimoFijo !== undefined && minimoFijo + 0.001 < Math.round(total * minimoPorcentaje) / 100;
    const saldo = Math.max(0, Math.round((total - abono) * 100) / 100);
    const porcentaje = total > 0 ? (abono / total) * 100 : 0;
    return (
        <dl className={cn('bg-muted/40 grid gap-2 rounded-lg border p-4 text-sm', className)}>
            <div className="text-muted-foreground flex justify-between gap-3 text-xs">
                <dt>{t?.fecha ? `Tasa BCV (${formatoFecha(t.fecha)})` : t ? 'Tasa guardada' : 'Tasa BCV'}</dt>
                <dd className="tabular">{t ? formatoBs(t.valor) : 'No disponible'}</dd>
            </div>
            <div className="flex justify-between gap-3">
                <dt className="text-muted-foreground">Total del pedido</dt>
                <dd>
                    <Doble usd={total} tasa={t} fuerte />
                </dd>
            </div>
            <div className="flex justify-between gap-3">
                <dt className="text-muted-foreground">Abonado ({formatoNumero(porcentaje)} %)</dt>
                <dd>
                    <Doble usd={abono} tasa={t} />
                </dd>
            </div>
            <div className="flex justify-between gap-3">
                <dt className="text-muted-foreground">{legacy ? 'Abono mínimo (lo ya registrado)' : `Abono mínimo (${formatoNumero(minimoPorcentaje)} %)`}</dt>
                <dd className={cn('tabular', abono + 0.001 < minimo ? 'text-warning' : 'text-success')}>{formatoUsd(minimo)}</dd>
            </div>
            <div className="flex justify-between gap-3 border-t pt-2 font-semibold">
                <dt>Saldo por cobrar</dt>
                <dd>
                    <Doble usd={saldo} tasa={t} fuerte />
                </dd>
            </div>
        </dl>
    );
}

/** Lista de pagos registrados (solo lectura), con su equivalente en Bs. */
export function ListaPagos({ pagos, tasa }: { pagos: Pago[]; tasa: TasaGuardada | undefined }) {
    const t = useTasaPedido(tasa);
    if (!pagos.length) return <p className="text-muted-foreground rounded-lg border border-dashed p-4 text-center text-sm">Aún no hay pagos registrados.</p>;
    return (
        <ul className="divide-y rounded-lg border">
            {pagos.map((p, i) => (
                <li key={i} className="flex flex-wrap items-center justify-between gap-2 p-3 text-sm">
                    <span>
                        <span className="font-medium">{METODO[p.metodo]}</span>
                        {p.banco && <span className="text-muted-foreground"> · {p.banco}</span>}
                        {p.referencia && <span className="text-muted-foreground block text-xs tabular">Ref. {p.referencia}</span>}
                    </span>
                    <Doble usd={p.monto} tasa={t} />
                </li>
            ))}
        </ul>
    );
}
