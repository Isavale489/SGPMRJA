import { Link } from '@inertiajs/react';

import { Monto } from '@/components/app/monto';
import type { Columna } from '@/components/app/tabla-servidor';
import { formatoNumero } from '@/lib/formato';
import { cn } from '@/lib/utils';

/** Espejo de App\Support\ExistenciasInsumo::paginar() (lo verifican MovimientosPaginaTest y ComprasPaginaTest). */
export interface ExistenciaFila {
    id: number;
    nombre: string;
    codigo: string | null;
    tipo: string;
    unidad: string;
    minimo: number;
    actual: number;
    maximo: number;
    costo: number;
    estado: 'bajo' | 'medio' | 'normal';
}

const ESTADO: Record<ExistenciaFila['estado'], { etiqueta: string; clase: string }> = {
    bajo: { etiqueta: 'En o bajo el mínimo', clase: 'bg-destructive/10 text-destructive ring-destructive/25' },
    medio: { etiqueta: 'Cerca del mínimo', clase: 'bg-warning/12 text-warning ring-warning/25' },
    normal: { etiqueta: 'Normal', clase: 'bg-success/12 text-success ring-success/25' },
};

/**
 * Columnas de la pestaña «Existencias» (Movimientos y Compras). Con
 * `historial`, el nombre enlaza al historial del insumo; sin él (quien no ve
 * Movimientos), es texto.
 */
export function columnasExistencias({ historial, costo = 'Costo unitario' }: { historial?: string; costo?: string } = {}): Columna<ExistenciaFila>[] {
    const nombre = (i: ExistenciaFila) => (
        <>
            <span className="font-medium">{i.nombre}</span>
            {i.codigo && <code className="text-muted-foreground ml-1.5 font-mono text-xs">{i.codigo}</code>}
            <span className="text-muted-foreground block text-xs">{i.tipo}</span>
        </>
    );

    return [
        {
            id: 'insumo',
            encabezado: 'Insumo',
            celda: (i) => (historial ? <Link href={`${historial}/${i.id}`} className="hover:underline">{nombre(i)}</Link> : nombre(i)),
        },
        { id: 'minimo', encabezado: 'Mínima', className: 'text-right', celda: (i) => <span className="tabular text-muted-foreground">{formatoNumero(i.minimo)}</span> },
        { id: 'actual', encabezado: 'Actual', className: 'text-right', celda: (i) => <span className="tabular font-medium">{formatoNumero(i.actual)} <span className="text-muted-foreground text-xs font-normal">{i.unidad}</span></span> },
        { id: 'maximo', encabezado: 'Máxima', className: 'text-right', celda: (i) => <span className="tabular text-muted-foreground">{i.maximo > 0 ? formatoNumero(i.maximo) : '—'}</span> },
        { id: 'costo', encabezado: costo, celda: (i) => <Monto usd={i.costo} /> },
        {
            id: 'estado',
            encabezado: 'Estado',
            celda: (i) => <span className={cn('inline-flex rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset', ESTADO[i.estado].clase)}>{ESTADO[i.estado].etiqueta}</span>,
        },
    ];
}
