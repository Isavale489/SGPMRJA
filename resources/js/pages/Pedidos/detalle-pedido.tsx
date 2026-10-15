import { Link } from '@inertiajs/react';
import { BadgeCheck, CalendarCheck, CalendarDays, FileText, Flag, IdCard, Mail, Pencil, Phone, UserRound, Zap } from 'lucide-react';

import { Asistente } from '@/components/app/asistente';
import { Dato } from '@/components/app/dato';
import { EstadoBadge } from '@/components/app/estado-badge';
import { Monto } from '@/components/app/monto';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Skeleton } from '@/components/ui/skeleton';
import { usePermisos } from '@/hooks/use-permisos';
import { formatoFecha, formatoNumero } from '@/lib/formato';
import { TablaProductos, iniciales } from '@/pages/Cotizaciones/piezas';
import type { Terminos } from '@/pages/Cotizaciones/tipos';

import { accionesDe } from './acciones';
import { ListaPagos, ResumenPago } from './piezas';
import type { PedidoDetalle } from './tipos';

interface Props {
    pedido: PedidoDetalle | null;
    cargando: boolean;
    onCerrar: () => void;
    terminos: Terminos;
    urls: { index: string };
}

/** «Ver» de un pedido, por pasos: Cliente → Productos → Pago. */
export function DetallePedido({ pedido: p, cargando, onCerrar, terminos, urls }: Props) {
    const { puede } = usePermisos();
    const a = p ? accionesDe(p, puede) : null;

    return (
        <Dialog open={Boolean(p) || cargando} onOpenChange={(abierto) => !abierto && onCerrar()}>
            <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-4xl">
                {!p ? (
                    <>
                        <DialogHeader>
                            <DialogTitle>Pedido</DialogTitle>
                            <DialogDescription>Cargando…</DialogDescription>
                        </DialogHeader>
                        <div className="grid gap-3" aria-hidden>
                            <Skeleton className="h-16" />
                            <Skeleton className="h-40" />
                        </div>
                    </>
                ) : (
                    <>
                        <DialogHeader>
                            <DialogTitle className="flex flex-wrap items-center gap-2">
                                Pedido #{p.id} <EstadoBadge estado={p.estado} />
                            </DialogTitle>
                            <DialogDescription className="flex flex-wrap items-center gap-2">
                                {p.creador ? (
                                    <>
                                        {p.creador.avatar ? (
                                            <img src={p.creador.avatar} alt="" className="size-5 rounded-full object-cover" />
                                        ) : (
                                            <span className="grid size-5 place-items-center rounded-full bg-white/20 text-[0.6rem] font-semibold text-white">{iniciales(p.creador.nombre)}</span>
                                        )}
                                        Creado por {p.creador.nombre}
                                        {p.creador.fecha && ` el ${formatoFecha(p.creador.fecha)} ${p.creador.fecha.slice(11)}`}
                                    </>
                                ) : (
                                    'Creador desconocido'
                                )}
                                {p.cotizacion_id && <span>· desde la cotización #{p.cotizacion_id}</span>}
                            </DialogDescription>
                        </DialogHeader>

                        <Asistente
                            key={p.id}
                            final={
                                <div className="flex flex-wrap gap-2">
                                    {a?.editar && (
                                        <Button variant="outline" asChild>
                                            <Link href={`${urls.index}/${p.id}/editar`}>
                                                <Pencil /> {p.estado === 'Completado' ? 'Registrar pago' : 'Editar pagos y entrega'}
                                            </Link>
                                        </Button>
                                    )}
                                    {a?.pdf && (
                                        <Button variant="outline" asChild>
                                            <a href={`${urls.index}/${p.id}/pdf`} target="_blank" rel="noopener">
                                                <FileText /> Exportar PDF
                                            </a>
                                        </Button>
                                    )}
                                </div>
                            }
                            pasos={[
                                {
                                    titulo: 'Cliente',
                                    descripcion: 'Datos del cliente y del pedido.',
                                    contenido: (
                                        <div className="grid gap-4 md:grid-cols-2">
                                            <section className="grid gap-3 rounded-lg border p-4">
                                                <h3 className="text-sm font-medium">Cliente</h3>
                                                <dl className="grid gap-3 sm:grid-cols-2">
                                                    <Dato icono={<UserRound />} etiqueta={p.cliente_datos?.juridico ? 'Razón social' : 'Nombre'}>
                                                        <span className={p.cliente_inhabilitado ? 'text-muted-foreground' : undefined}>{p.cliente}</span>
                                                        {p.cliente_inhabilitado && <span className="bg-destructive/10 text-destructive ml-2 rounded-full px-2 py-0.5 text-xs">Inhabilitado</span>}
                                                    </Dato>
                                                    <Dato icono={<IdCard />} etiqueta="Documento">
                                                        <span className="tabular">{p.cliente_doc ?? '—'}</span>
                                                    </Dato>
                                                    <Dato icono={<Phone />} etiqueta="Teléfono">
                                                        {p.cliente_datos?.telefono ?? '—'}
                                                    </Dato>
                                                    <Dato icono={<Mail />} etiqueta="Correo">
                                                        {p.cliente_datos?.email ?? '—'}
                                                    </Dato>
                                                </dl>
                                            </section>
                                            <section className="grid gap-3 rounded-lg border p-4">
                                                <h3 className="text-sm font-medium">Pedido</h3>
                                                <dl className="grid gap-3 sm:grid-cols-2">
                                                    <Dato icono={<CalendarDays />} etiqueta="Fecha del pedido">
                                                        {p.fecha ? formatoFecha(p.fecha) : '—'}
                                                    </Dato>
                                                    <Dato icono={<CalendarCheck />} etiqueta="Entrega estimada">
                                                        {p.entrega ? formatoFecha(p.entrega) : '—'}
                                                    </Dato>
                                                    <Dato icono={<Flag />} etiqueta="Estado">
                                                        <EstadoBadge estado={p.estado} />
                                                    </Dato>
                                                    <Dato icono={<Zap />} etiqueta="Prioridad">
                                                        {p.prioridad}
                                                    </Dato>
                                                    <Dato icono={<BadgeCheck />} etiqueta="Formalizado">
                                                        {p.formalizacion ? `El ${formatoFecha(p.formalizacion)}` : 'Aún no (falta el abono mínimo)'}
                                                    </Dato>
                                                </dl>
                                            </section>
                                        </div>
                                    ),
                                },
                                {
                                    titulo: 'Productos',
                                    descripcion: 'Lo pactado en la cotización: no cambia después de crear el pedido.',
                                    contenido: (
                                        <div className="grid gap-3">
                                            <div className="grid grid-cols-2 gap-3 sm:max-w-md">
                                                <div className="rounded-lg border p-3">
                                                    <p className="text-muted-foreground text-xs">Líneas</p>
                                                    <p className="text-lg font-semibold tabular">{formatoNumero(p.grupos.length)}</p>
                                                </div>
                                                <div className="rounded-lg border p-3">
                                                    <p className="text-muted-foreground text-xs">Total</p>
                                                    <Monto usd={p.total} tasa={p.tasa} />
                                                </div>
                                            </div>
                                            {p.grupos.length ? (
                                                <TablaProductos filas={p.grupos} tasa={p.tasa} />
                                            ) : (
                                                <p className="text-muted-foreground rounded-lg border border-dashed p-6 text-center text-sm">Este pedido no tiene productos registrados.</p>
                                            )}
                                        </div>
                                    ),
                                },
                                {
                                    titulo: 'Pago',
                                    descripcion: 'Pagos registrados, abono y saldo por cobrar.',
                                    contenido: (
                                        <div className="grid gap-4 lg:grid-cols-[1fr_22rem] lg:items-start">
                                            <ListaPagos pagos={p.pagos} tasa={p.tasa} />
                                            <ResumenPago total={p.total} abono={p.abono} minimoPorcentaje={terminos.abono} tasa={p.tasa} />
                                        </div>
                                    ),
                                },
                            ]}
                        />
                    </>
                )}
            </DialogContent>
        </Dialog>
    );
}
