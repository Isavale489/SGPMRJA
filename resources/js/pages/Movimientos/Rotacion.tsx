import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { CabeceraSeccion, CuerpoRayado } from '@/components/app/tabla-seccion';
import { Table, TableCell, TableHead, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { formatoNumero } from '@/lib/formato';

interface Props {
    insumos: { id: number; nombre: string; codigo: string | null; unidad: string; salidas: number; actual: number; minimo: number }[];
    urls: { index: string; historial: string };
}

/** Insumos ordenados por salidas acumuladas: prioridad de reposición. */
export default function Rotacion({ insumos, urls }: Props) {
    const max = Math.max(1, ...insumos.map((i) => i.salidas));

    return (
        <AppLayout titulo="Análisis de rotación" acciones={<Button variant="ghost" asChild><Link href={urls.index}><ArrowLeft /> Movimientos</Link></Button>}>
            <p className="text-muted-foreground -mt-3 mb-4 text-sm">Salidas acumuladas por insumo (histórico). Los de mayor rotación, primero.</p>
            <div className="bg-card overflow-x-auto rounded-lg border">
                <Table>
                    <CabeceraSeccion>
                        <TableRow className="hover:bg-transparent">
                            <TableHead className="w-10">#</TableHead>
                            <TableHead>Insumo</TableHead>
                            <TableHead className="w-2/5">Salidas acumuladas</TableHead>
                            <TableHead className="text-right">Existencia actual</TableHead>
                        </TableRow>
                    </CabeceraSeccion>
                    <CuerpoRayado>
                        {insumos.length === 0 && (
                            <TableRow className="hover:bg-transparent"><TableCell colSpan={4} className="text-muted-foreground h-24 text-center">No hay insumos inventariables.</TableCell></TableRow>
                        )}
                        {insumos.map((i, n) => (
                            <TableRow key={i.id}>
                                <TableCell className="text-muted-foreground tabular">{n + 1}</TableCell>
                                <TableCell>
                                    <Link href={`${urls.historial}/${i.id}`} className="font-medium hover:underline">{i.nombre}</Link>
                                    {i.codigo && <code className="text-muted-foreground ml-1.5 font-mono text-xs">{i.codigo}</code>}
                                </TableCell>
                                <TableCell>
                                    <div className="flex items-center gap-2">
                                        <div className="bg-muted h-2 flex-1 overflow-hidden rounded-full" aria-hidden>
                                            <div className="bg-primary h-full rounded-full" style={{ width: `${(i.salidas / max) * 100}%` }} />
                                        </div>
                                        <span className="tabular w-24 text-right text-sm">{formatoNumero(i.salidas)} {i.unidad}</span>
                                    </div>
                                </TableCell>
                                <TableCell className={`text-right tabular ${i.actual <= i.minimo ? 'text-destructive font-medium' : ''}`}>{formatoNumero(i.actual)}</TableCell>
                            </TableRow>
                        ))}
                    </CuerpoRayado>
                </Table>
            </div>
        </AppLayout>
    );
}
