import { Link } from '@inertiajs/react';
import { CalendarDays, FileText, Layers, Pencil, Plus, Shirt, UserRound } from 'lucide-react';
import { useState } from 'react';

import { Asistente } from '@/components/app/asistente';
import { Dato } from '@/components/app/dato';
import { EstadoBadge } from '@/components/app/estado-badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Skeleton } from '@/components/ui/skeleton';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { usePermisos } from '@/hooks/use-permisos';
import { formatoFecha, formatoNumero } from '@/lib/formato';

import { Barra } from './barra';
import { progreso, type ModoOrden, type OrdenDetalle } from './tipos';

interface Props {
    abierto: boolean;
    orden: OrdenDetalle | null;
    onCerrar: () => void;
    onModo: (modo: ModoOrden) => void;
    urls: { index: string };
}

const fecha = (f: string | null) => (f ? formatoFecha(f) : '—');

/** Ficha «Ver» de una orden: producto, cronograma, equipo con su avance, insumos, bordados y etapas. */
export function DetalleOrden({ abierto, orden, onCerrar, onModo, urls }: Props) {
    // Foto del producto que ya no existe (p. ej. dump importado sin storage/): se usa el ícono.
    // Se guarda la URL que falló: al abrir otra orden en el mismo diálogo vuelve a intentarse.
    const [fotoRota, setFotoRota] = useState<string | null>(null);
    const { puede } = usePermisos();
    const activa = orden && (orden.estado === 'Pendiente' || orden.estado === 'En Proceso');

    return (
        <Dialog open={abierto} onOpenChange={(a) => !a && onCerrar()}>
            <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-3xl">
                {!orden ? (
                    <>
                        <DialogHeader>
                            <DialogTitle>Orden de producción</DialogTitle>
                            <DialogDescription>Cargando…</DialogDescription>
                        </DialogHeader>
                        <div className="grid gap-3" aria-hidden>
                            <Skeleton className="h-20" />
                            <Skeleton className="h-40" />
                        </div>
                    </>
                ) : (
                    <>
                        <DialogHeader>
                            <DialogTitle className="flex flex-wrap items-center gap-2">
                                Orden #{orden.id} <EstadoBadge estado={orden.estado} />
                            </DialogTitle>
                            <DialogDescription>
                                {orden.pedido_id ? `Pedido #${orden.pedido_id}` : 'Orden manual'}
                                {orden.creador && ` · creada por ${orden.creador.nombre}`}
                                {orden.creado && ` el ${formatoFecha(orden.creado)}`}
                            </DialogDescription>
                        </DialogHeader>

                        {orden.estado === 'Cancelado' && orden.motivo_cancelacion && (
                            <p className="border-destructive/30 bg-destructive/8 rounded-md border p-3 text-sm">Cancelada: {orden.motivo_cancelacion}</p>
                        )}
                        {orden.pedido_cancelado && orden.estado !== 'Cancelado' && (
                            <p className="border-warning/30 bg-warning/10 rounded-md border p-3 text-sm">El pedido está cancelado: la orden no admite avances.</p>
                        )}

                        {/* Hero (el del panel anterior): qué se fabrica y cuánto va, visible en todos los pasos. */}
                        <section className="bg-card flex items-center gap-4 rounded-xl border p-3" aria-label="Resumen de la orden">
                            {orden.imagen && fotoRota !== orden.imagen ? (
                                <img src={orden.imagen} alt="" onError={() => setFotoRota(orden.imagen)} className="size-14 shrink-0 rounded-xl border bg-white object-contain p-1" />
                            ) : (
                                <span className="bg-seccion-degradado grid size-14 shrink-0 place-items-center rounded-xl text-white shadow-[0_6px_16px_-6px_var(--seccion-acento)]" aria-hidden>
                                    <Shirt className="size-6" />
                                </span>
                            )}
                            <div className="grid min-w-0 flex-1 gap-2">
                                <div className="min-w-0">
                                    <p className="truncate font-semibold leading-tight">{orden.producto}</p>
                                    {orden.variante && <p className="text-muted-foreground truncate text-xs">{orden.variante}</p>}
                                </div>
                                <Barra
                                    valor={progreso(orden.cantidad_producida, orden.cantidad_solicitada)}
                                    texto={`${formatoNumero(orden.cantidad_producida)} de ${formatoNumero(orden.cantidad_solicitada)} unidades`}
                                />
                            </div>
                        </section>

                        <Asistente
                            key={orden.id}
                            final={<span />}
                            pasos={[
                                {
                                    titulo: 'Orden',
                                    contenido: (
                                        <div className="grid gap-4">
                                            <dl className="grid gap-4 sm:grid-cols-2">
                                                <Dato icono={<UserRound />} etiqueta="Cliente">
                                                    {orden.cliente ?? '—'}
                                                    {orden.cliente_documento && <span className="text-muted-foreground block text-xs tabular">{orden.cliente_documento}</span>}
                                                </Dato>
                                                <Dato icono={<CalendarDays />} etiqueta="Cronograma">
                                                    <span className="tabular">
                                                        {fecha(orden.fecha_inicio)} → {fecha(orden.fecha_fin_estimada)}
                                                    </span>
                                                    {orden.fecha_fin_real && <span className="text-muted-foreground block text-xs">Terminó el {fecha(orden.fecha_fin_real)}</span>}
                                                </Dato>
                                            </dl>
                                            <section className="grid content-start gap-2">
                                                <h3 className="text-sm font-medium">Bordado / diseño</h3>
                                                {orden.bordados.length ? (
                                                    <ul className="grid gap-1 text-sm">
                                                        {orden.bordados.map((b) => (
                                                            <li key={b.id}>
                                                                {b.aplicacion ?? '—'}{' '}
                                                                <span className="text-muted-foreground">
                                                                    · {b.logo ?? 'Logo'} · {b.cantidad}
                                                                </span>
                                                            </li>
                                                        ))}
                                                    </ul>
                                                ) : (
                                                    <p className="text-muted-foreground text-sm">Sin bordado.</p>
                                                )}
                                            </section>
                                            {orden.notas && (
                                                <div className="text-sm">
                                                    <p className="text-muted-foreground text-xs">Notas</p>
                                                    <p className="whitespace-pre-line">{orden.notas}</p>
                                                </div>
                                            )}
                                        </div>
                                    ),
                                },
                                {
                                    titulo: 'Insumos',
                                    contenido: (
                                        <section className="grid content-start gap-2">
                                            <h3 className="text-sm font-medium">Insumos comprometidos</h3>
                                            {orden.insumos.length ? (
                                                <ul className="grid gap-1 text-sm">
                                                    {orden.insumos.map((i) => (
                                                        <li key={i.id} className="flex justify-between gap-3">
                                                            <span>{i.nombre}</span>
                                                            <span className="tabular text-muted-foreground">
                                                                {formatoNumero(i.estimada)} {i.unidad}
                                                            </span>
                                                        </li>
                                                    ))}
                                                </ul>
                                            ) : (
                                                <p className="text-muted-foreground text-sm">Sin insumos.</p>
                                            )}
                                        </section>
                                    ),
                                },
                                {
                                    titulo: 'Progreso',
                                    contenido: (
                                        <div className="grid gap-4">
                                            <Barra
                                                valor={progreso(orden.cantidad_producida, orden.cantidad_solicitada)}
                                                texto={`${formatoNumero(orden.cantidad_producida)} de ${formatoNumero(orden.cantidad_solicitada)} producidas${orden.cantidad_defectuosa ? ` · ${orden.cantidad_defectuosa} defectuosas` : ''}`}
                                            />
                                            <section className="grid gap-2">
                                                <h3 className="text-sm font-medium">Equipo</h3>
                                                <div className="overflow-x-auto rounded-lg border">
                                                    <Table>
                                                        <TableHeader>
                                                            <TableRow className="hover:bg-transparent">
                                                                <TableHead>Empleado</TableHead>
                                                                <TableHead className="text-right">Asignadas</TableHead>
                                                                <TableHead className="text-right">Producidas</TableHead>
                                                                <TableHead className="text-right">Defectuosas</TableHead>
                                                            </TableRow>
                                                        </TableHeader>
                                                        <TableBody>
                                                            {orden.equipo.map((e) => (
                                                                <TableRow key={e.id}>
                                                                    <TableCell>{e.nombre}</TableCell>
                                                                    <TableCell className="text-right tabular">{e.cantidad}</TableCell>
                                                                    <TableCell className="text-right tabular">{e.producida}</TableCell>
                                                                    <TableCell className="text-right tabular">{e.defectuosa || '—'}</TableCell>
                                                                </TableRow>
                                                            ))}
                                                            {orden.equipo.length === 0 && (
                                                                <TableRow>
                                                                    <TableCell colSpan={4} className="text-muted-foreground text-center">
                                                                        Sin equipo registrado.
                                                                    </TableCell>
                                                                </TableRow>
                                                            )}
                                                        </TableBody>
                                                    </Table>
                                                </div>
                                            </section>
                                            {orden.etapas.length > 0 && (
                                                <section className="grid gap-2">
                                                    <h3 className="text-sm font-medium">Etapas</h3>
                                                    <ul className="flex flex-wrap gap-2">
                                                        {orden.etapas.map((e) => (
                                                            <li key={e.id} className="flex items-center gap-2 rounded-md border px-2 py-1 text-sm">
                                                                {e.nombre} <EstadoBadge estado={e.estado} />
                                                            </li>
                                                        ))}
                                                    </ul>
                                                </section>
                                            )}
                                        </div>
                                    ),
                                },
                            ]}
                        />

                        <div className="flex flex-wrap gap-2 border-t pt-4">
                            {puede('ordenes.pdf') && (
                                <Button variant="outline" asChild>
                                    <a href={`${urls.index}/${orden.id}/pdf`} target="_blank" rel="noopener">
                                        <FileText /> Ver PDF
                                    </a>
                                </Button>
                            )}
                            {orden.estado !== 'Cancelado' && puede('ordenes.gestionar') && (
                                <Button variant="outline" asChild>
                                    <Link href={`${urls.index}/${orden.id}/edit`}>
                                        <Pencil /> Editar
                                    </Link>
                                </Button>
                            )}
                            <Button variant="outline" onClick={() => onModo('etapas')}>
                                <Layers /> Etapas
                            </Button>
                            <span className="flex-1" />
                            {activa && !orden.pedido_cancelado && puede('ordenes.avance') && (
                                <Button onClick={() => onModo('avance')}>
                                    <Plus /> Registrar avance
                                </Button>
                            )}
                        </div>
                    </>
                )}
            </DialogContent>
        </Dialog>
    );
}
