import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-react';
import { useMemo, useState } from 'react';

import { TableHead } from '@/components/ui/table';
import { cn } from '@/lib/utils';

export type Direccion = 'asc' | 'desc';
export interface Orden<K extends string> {
    clave: K;
    dir: Direccion;
}
type Valor = string | number | null | undefined;

/**
 * Orden por columna en el cliente, para tablas que ya traen todas sus filas
 * (los reportes). Los vacíos van siempre al final. `valores` debe ser estable
 * (constante del módulo o useMemo).
 */
export function useOrdenColumnas<T, K extends string>(filas: T[], valores: Record<K, (fila: T) => Valor>, inicial?: Orden<NoInfer<K>>) {
    const [orden, setOrden] = useState<Orden<K> | undefined>(inicial);

    const ordenadas = useMemo(() => {
        if (!orden) return filas;
        const valor = valores[orden.clave];
        const signo = orden.dir === 'asc' ? 1 : -1;
        return [...filas].sort((a, b) => {
            const x = valor(a);
            const y = valor(b);
            const vx = x === null || x === undefined;
            const vy = y === null || y === undefined;
            if (vx || vy) return vx === vy ? 0 : vx ? 1 : -1;
            const c = typeof x === 'number' && typeof y === 'number' ? x - y : String(x).localeCompare(String(y), 'es', { numeric: true, sensitivity: 'base' });
            return c * signo;
        });
    }, [filas, orden, valores]);

    /** Primer clic: ascendente; segundo: descendente; tercero: vuelve al orden por defecto. */
    const alternar = (clave: K) => setOrden((o) => (o?.clave !== clave ? { clave, dir: 'asc' } : o.dir === 'asc' ? { clave, dir: 'desc' } : inicial));

    return { ordenadas, orden, alternar };
}

/** Encabezado que ordena la tabla al hacer clic (anuncia el orden con aria-sort). */
export function CabeceraOrdenable<K extends string>({ clave, orden, onOrdenar, children, className }: { clave: K; orden?: Orden<K>; onOrdenar: (clave: K) => void; children: string; className?: string }) {
    const activo = orden?.clave === clave;
    const Icono = !activo ? ArrowUpDown : orden.dir === 'asc' ? ArrowUp : ArrowDown;
    return (
        <TableHead className={className} aria-sort={activo ? (orden.dir === 'asc' ? 'ascending' : 'descending') : 'none'}>
            <button type="button" onClick={() => onOrdenar(clave)} className={cn('inline-flex items-center gap-1 rounded-sm opacity-85 transition-opacity hover:opacity-100', activo && 'opacity-100')}>
                {children}
                <Icono className={cn('size-3.5', !activo && 'opacity-50')} aria-hidden />
                <span className="sr-only">(ordenar)</span>
            </button>
        </TableHead>
    );
}
