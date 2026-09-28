import { Link } from '@inertiajs/react';
import { ArrowDownRight, ArrowLeft, ArrowUpRight } from 'lucide-react';

import { TablaServidor, type Columna } from '@/components/app/tabla-servidor';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { formatoFecha, formatoNumero } from '@/lib/formato';
import { cn } from '@/lib/utils';
import type { Paginado } from '@/types';

interface Movimiento {
    id: number;
    tipo: 'Entrada' | 'Salida';
    cantidad: number;
    stock_anterior: number;
    stock_nuevo: number;
    motivo: string | null;
    usuario: string | null;
    fecha: string | null;
}

interface Props {
    insumo: { id: number; nombre: string; codigo: string | null; unidad: string; actual: number; minimo: number; maximo: number };
    movimientos: Paginado<Movimiento>;
    urls: { index: string };
}

/** Historial de movimientos de un insumo (Kardex simple). */
export default function Historial({ insumo, movimientos, urls }: Props) {
    const columnas: Columna<Movimiento>[] = [
        { id: 'fecha', encabezado: 'Fecha', celda: (m) => <span className="tabular whitespace-nowrap">{m.fecha ? `${formatoFecha(m.fecha)} ${m.fecha.slice(11)}` : '—'}</span> },
        {
            id: 'tipo',
            encabezado: 'Tipo',
            celda: (m) => (
                <span className={cn('inline-flex items-center gap-1', m.tipo === 'Entrada' ? 'text-success' : 'text-destructive')}>
                    {m.tipo === 'Entrada' ? <ArrowUpRight className="size-4" /> : <ArrowDownRight className="size-4" />} {m.tipo}
                </span>
            ),
        },
        { id: 'cantidad', encabezado: 'Cantidad', className: 'text-right', celda: (m) => <span className="tabular">{m.tipo === 'Entrada' ? '+' : '−'}{formatoNumero(m.cantidad)}</span> },
        { id: 'saldo', encabezado: 'Existencia', className: 'text-right', celda: (m) => <span className="tabular">{formatoNumero(m.stock_nuevo)}</span> },
        { id: 'motivo', encabezado: 'Motivo', celda: (m) => <span className="text-muted-foreground">{m.motivo ?? '—'}</span> },
        { id: 'usuario', encabezado: 'Registró', celda: (m) => <span className="text-muted-foreground">{m.usuario ?? 'Sistema'}</span> },
    ];

    return (
        <AppLayout
            titulo={`Historial de ${insumo.nombre}`}
            acciones={<Button variant="ghost" asChild><Link href={urls.index}><ArrowLeft /> Movimientos</Link></Button>}
        >
            <div className="grid gap-4">
                <dl className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    {[
                        ['Código', insumo.codigo ?? '—'],
                        ['Existencia actual', `${formatoNumero(insumo.actual)} ${insumo.unidad}`],
                        ['Mínima', formatoNumero(insumo.minimo)],
                        ['Máxima', insumo.maximo > 0 ? formatoNumero(insumo.maximo) : '—'],
                    ].map(([k, v]) => (
                        <div key={k} className="bg-card rounded-lg border p-3">
                            <dt className="text-muted-foreground text-xs">{k}</dt>
                            <dd className={cn('tabular font-medium', k === 'Existencia actual' && insumo.actual <= insumo.minimo && 'text-destructive')}>{v}</dd>
                        </div>
                    ))}
                </dl>
                <TablaServidor pagina={movimientos} columnas={columnas} only={['movimientos']} idFila={(m) => m.id} vacio="Este insumo aún no tiene movimientos." />
            </div>
        </AppLayout>
    );
}
