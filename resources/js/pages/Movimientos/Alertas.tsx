import { Link } from '@inertiajs/react';
import { ArrowLeft, History, ShoppingBag } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { formatoNumero } from '@/lib/formato';

interface Props {
    insumos: { id: number; nombre: string; codigo: string | null; tipo: string; unidad: string; actual: number; minimo: number; maximo: number }[];
    urls: { index: string; historial: string; compras: string };
}

/** Insumos en o bajo su existencia mínima: lo que hay que reponer. */
export default function Alertas({ insumos, urls }: Props) {
    return (
        <AppLayout
            titulo="Alertas de existencia"
            acciones={
                <>
                    <Button variant="ghost" asChild><Link href={urls.index}><ArrowLeft /> Movimientos</Link></Button>
                    {/* Compras sigue en Blade → enlace normal. */}
                    <Button asChild><a href={urls.compras}><ShoppingBag /> Ir a Compras</a></Button>
                </>
            }
        >
            <p className="text-muted-foreground -mt-3 mb-4 text-sm">
                {insumos.length ? `${insumos.length} ${insumos.length === 1 ? 'insumo está' : 'insumos están'} en o bajo su existencia mínima.` : 'Ningún insumo está bajo su existencia mínima.'}
            </p>
            {insumos.length > 0 && (
                <div className="bg-card overflow-x-auto rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow className="hover:bg-transparent">
                                <TableHead>Insumo</TableHead>
                                <TableHead className="text-right">Actual</TableHead>
                                <TableHead className="text-right">Mínima</TableHead>
                                <TableHead className="text-right">Faltan para el mínimo</TableHead>
                                <TableHead className="text-right">Para llegar al máximo</TableHead>
                                <TableHead><span className="sr-only">Historial</span></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {insumos.map((i) => (
                                <TableRow key={i.id}>
                                    <TableCell>
                                        <span className="font-medium">{i.nombre}</span>
                                        {i.codigo && <code className="text-muted-foreground ml-1.5 font-mono text-xs">{i.codigo}</code>}
                                        <span className="text-muted-foreground block text-xs">{i.tipo}</span>
                                    </TableCell>
                                    <TableCell className="text-destructive text-right font-medium tabular">{formatoNumero(i.actual)} {i.unidad}</TableCell>
                                    <TableCell className="text-right tabular">{formatoNumero(i.minimo)}</TableCell>
                                    <TableCell className="text-right tabular">{formatoNumero(Math.max(0, i.minimo - i.actual))}</TableCell>
                                    <TableCell className="text-muted-foreground text-right tabular">{i.maximo > 0 ? formatoNumero(Math.max(0, i.maximo - i.actual)) : '—'}</TableCell>
                                    <TableCell className="text-right">
                                        <Button variant="ghost" size="icon" asChild>
                                            <Link href={`${urls.historial}/${i.id}`} aria-label={`Historial de ${i.nombre}`}><History /></Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            )}
        </AppLayout>
    );
}
