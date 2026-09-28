import type { ReactNode } from 'react';

import { Paginacion } from '@/components/app/paginacion';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { cn } from '@/lib/utils';
import type { Paginado } from '@/types';

export interface Columna<T> {
    id: string;
    encabezado: ReactNode;
    celda: (fila: T) => ReactNode;
    className?: string;
}

interface Props<T> {
    pagina: Paginado<T>;
    columnas: Columna<T>[];
    /** Props Inertia que se recargan al paginar (normalmente la del listado). */
    only: string[];
    cargando?: boolean;
    vacio: ReactNode;
    idFila: (fila: T) => string | number;
}

/**
 * Tabla con datos paginados en el servidor (reemplaza a DataTables + yajra).
 * Filtrado, orden y paginación los resuelve Laravel; la tabla solo dibuja la
 * página actual. Sin librería de tablas a propósito: no hay lógica de cliente
 * que la justifique (ver docs/conventions/frontend.md).
 */
export function TablaServidor<T>({ pagina, columnas, only, cargando, vacio, idFila }: Props<T>) {
    return (
        <div className="grid gap-3">
            <div
                className={cn('border-border bg-card overflow-x-auto rounded-lg border transition-opacity duration-medio', cargando && 'opacity-60')}
                aria-busy={cargando}
            >
                <Table>
                    <TableHeader>
                        <TableRow className="hover:bg-transparent">
                            {columnas.map((c) => (
                                <TableHead key={c.id} className={cn('text-muted-foreground h-10 text-xs font-medium uppercase tracking-wide', c.className)}>
                                    {c.encabezado}
                                </TableHead>
                            ))}
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {pagina.data.length === 0 ? (
                            <TableRow className="hover:bg-transparent">
                                <TableCell colSpan={columnas.length} className="text-muted-foreground h-32 text-center">
                                    {vacio}
                                </TableCell>
                            </TableRow>
                        ) : (
                            pagina.data.map((fila) => (
                                <TableRow key={idFila(fila)}>
                                    {columnas.map((c) => (
                                        <TableCell key={c.id} className={c.className}>{c.celda(fila)}</TableCell>
                                    ))}
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
            </div>
            <Paginacion pagina={pagina} only={only} />
        </div>
    );
}
