import { Link } from '@inertiajs/react';
import { Ban, EllipsisVertical, Eye, FileText, Layers, Pencil, Plus, Trash2 } from 'lucide-react';

import { EstadoBadge } from '@/components/app/estado-badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Skeleton } from '@/components/ui/skeleton';
import { usePermisos } from '@/hooks/use-permisos';
import { formatoFecha, formatoNumero } from '@/lib/formato';

import { Barra } from './barra';
import { progreso, tituloPedido, type ModoOrden, type OrdenFila } from './tipos';

interface Props {
    clave?: string;
    ordenes: OrdenFila[] | null;
    onCerrar: () => void;
    onAbrir: (id: number, modo: ModoOrden) => void;
    onCancelar: (o: OrdenFila) => void;
    onEliminar: (o: OrdenFila) => void;
    urls: { index: string; crear: string };
}

/** Órdenes de un pedido: progreso, equipo y acciones de cada una. */
export function DialogoPedido({ clave, ordenes, onCerrar, onAbrir, onCancelar, onEliminar, urls }: Props) {
    const { puede } = usePermisos();

    return (
        <Dialog open={Boolean(clave)} onOpenChange={(a) => !a && onCerrar()}>
            <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-3xl">
                <DialogHeader>
                    <DialogTitle>{clave ? tituloPedido(clave) : 'Órdenes'}</DialogTitle>
                    <DialogDescription>
                        {ordenes ? `${ordenes.length} ${ordenes.length === 1 ? 'orden' : 'órdenes'} de producción` : 'Cargando…'}
                    </DialogDescription>
                </DialogHeader>

                {!ordenes ? (
                    <div className="grid gap-2" aria-hidden><Skeleton className="h-24" /><Skeleton className="h-24" /></div>
                ) : ordenes.length === 0 ? (
                    <p className="text-muted-foreground py-6 text-center text-sm">Este pedido ya no tiene órdenes.</p>
                ) : (
                    <ul className="grid gap-3">
                        {ordenes.map((o) => {
                            const activa = o.estado === 'Pendiente' || o.estado === 'En Proceso';
                            return (
                                <li key={o.id} className="grid gap-3 rounded-lg border p-3">
                                    <div className="flex flex-wrap items-start justify-between gap-2">
                                        <div className="min-w-0">
                                            <p className="font-medium">
                                                <span className="text-muted-foreground tabular">#{o.id}</span> {o.producto}
                                            </p>
                                            {o.variante && <p className="text-muted-foreground text-xs">{o.variante}</p>}
                                        </div>
                                        <div className="flex items-center gap-1">
                                            <EstadoBadge estado={o.estado} />
                                            <Button variant="ghost" size="icon" onClick={() => onAbrir(o.id, 'ver')} aria-label={`Ver orden #${o.id}`}><Eye /></Button>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="icon" aria-label={`Más acciones de la orden #${o.id}`}><EllipsisVertical /></Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    {activa && puede('ordenes.avance') && (
                                                        <DropdownMenuItem tono="principal" onSelect={() => onAbrir(o.id, 'avance')}><Plus /> Registrar avance</DropdownMenuItem>
                                                    )}
                                                    <DropdownMenuItem tono="principal" onSelect={() => onAbrir(o.id, 'etapas')}><Layers /> Etapas</DropdownMenuItem>
                                                    {o.estado !== 'Cancelado' && puede('ordenes.gestionar') && (
                                                        <DropdownMenuItem tono="editar" asChild><Link href={`${urls.index}/${o.id}/edit`}><Pencil /> Editar</Link></DropdownMenuItem>
                                                    )}
                                                    {puede('ordenes.pdf') && (
                                                        <DropdownMenuItem tono="documento" asChild><a href={`${urls.index}/${o.id}/pdf`} target="_blank" rel="noopener"><FileText /> Ver PDF</a></DropdownMenuItem>
                                                    )}
                                                    {((o.estado !== 'Cancelado' && puede('ordenes.cancelar')) || (o.estado === 'Pendiente' && puede('ordenes.gestionar'))) && <DropdownMenuSeparator />}
                                                    {o.estado !== 'Cancelado' && puede('ordenes.cancelar') && (
                                                        <DropdownMenuItem tono="aviso" onSelect={() => onCancelar(o)}><Ban /> Cancelar orden</DropdownMenuItem>
                                                    )}
                                                    {o.estado === 'Pendiente' && puede('ordenes.gestionar') && (
                                                        <DropdownMenuItem tono="peligro" onSelect={() => onEliminar(o)}><Trash2 /> Eliminar</DropdownMenuItem>
                                                    )}
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </div>
                                    </div>
                                    <Barra valor={progreso(o.cantidad_producida, o.cantidad_solicitada)} texto={`${formatoNumero(o.cantidad_producida)} de ${formatoNumero(o.cantidad_solicitada)} producidas`} />
                                    <p className="text-muted-foreground flex flex-wrap gap-x-4 gap-y-1 text-xs">
                                        <span>Equipo: {o.equipo.length ? o.equipo.map((e) => `${e.nombre} (${e.producida}/${e.cantidad})`).join(', ') : '—'}</span>
                                        <span className="tabular">{o.fecha_inicio ? formatoFecha(o.fecha_inicio) : '—'} → {o.fecha_fin_estimada ? formatoFecha(o.fecha_fin_estimada) : '—'}</span>
                                        {o.etapas > 0 && <span>{o.etapas} {o.etapas === 1 ? 'etapa' : 'etapas'}</span>}
                                        {o.cantidad_defectuosa > 0 && <span className="text-destructive">{o.cantidad_defectuosa} defectuosas</span>}
                                    </p>
                                </li>
                            );
                        })}
                    </ul>
                )}
                {puede('ordenes.gestionar') && clave && clave !== 'manual' && (
                    <Button variant="outline" asChild className="justify-self-start">
                        <Link href={`${urls.crear}?pedido=${clave}`}><Plus /> Nueva orden de este pedido</Link>
                    </Button>
                )}
            </DialogContent>
        </Dialog>
    );
}
