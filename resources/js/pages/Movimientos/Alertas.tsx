import { Link, router } from '@inertiajs/react';
import { ArrowLeft, History, ShoppingBag } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { CabeceraSeccion, CuerpoRayado } from '@/components/app/tabla-seccion';
import { Table, TableCell, TableHead, TableRow } from '@/components/ui/table';
import { usePermisos } from '@/hooks/use-permisos';
import AppLayout from '@/layouts/app-layout';
import { formatoNumero } from '@/lib/formato';
import { guardarFaltantes } from '@/lib/inventario';

interface Props {
    insumos: { id: number; nombre: string; codigo: string | null; tipo: string; unidad: string; actual: number; minimo: number; maximo: number }[];
    urls: { index: string; historial: string; compras: string };
}

/** Insumos en o bajo su existencia mínima: lo que hay que reponer. */
export default function Alertas({ insumos, urls }: Props) {
    const { puede } = usePermisos();
    // Reponer hasta el máximo (o, si no tiene, hasta el mínimo).
    const comprar = () => {
        guardarFaltantes(insumos.map((i) => ({ insumo_id: i.id, nombre: i.nombre, cantidad: Math.max(0, (i.maximo > 0 ? i.maximo : i.minimo) - i.actual) })).filter((f) => f.cantidad > 0), 'alertas');
        router.visit(`${urls.compras}/crear?prefill=1`);
    };

    return (
        <AppLayout
            titulo="Alertas de existencia"
            acciones={
                <>
                    <Button variant="ghost" asChild><Link href={urls.index}><ArrowLeft /> Movimientos</Link></Button>
                    {puede('compras.gestionar') && insumos.length > 0 && <Button onClick={comprar}><ShoppingBag /> Comprar lo que falta</Button>}
                </>
            }
        >
            <p className="text-muted-foreground -mt-3 mb-4 text-sm">
                {insumos.length ? `${insumos.length} ${insumos.length === 1 ? 'insumo está' : 'insumos están'} en o bajo su existencia mínima.` : 'Ningún insumo está bajo su existencia mínima.'}
            </p>
            {insumos.length > 0 && (
                <div className="bg-card overflow-x-auto rounded-lg border">
                    <Table>
                        <CabeceraSeccion>
                            <TableRow className="hover:bg-transparent">
                                <TableHead>Insumo</TableHead>
                                <TableHead className="text-right">Actual</TableHead>
                                <TableHead className="text-right">Mínima</TableHead>
                                <TableHead className="text-right">Faltan para el mínimo</TableHead>
                                <TableHead className="text-right">Para llegar al máximo</TableHead>
                                <TableHead><span className="sr-only">Historial</span></TableHead>
                            </TableRow>
                        </CabeceraSeccion>
                        <CuerpoRayado>
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
                        </CuerpoRayado>
                    </Table>
                </div>
            )}
        </AppLayout>
    );
}
