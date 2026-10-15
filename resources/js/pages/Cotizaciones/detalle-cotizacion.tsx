import { CalendarCheck, CalendarDays, FileText, Flag, IdCard, Mail, Phone, UserRound, Zap } from 'lucide-react';

import { Asistente } from '@/components/app/asistente';
import { Dato } from '@/components/app/dato';
import { EstadoBadge } from '@/components/app/estado-badge';
import { Monto } from '@/components/app/monto';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Skeleton } from '@/components/ui/skeleton';
import { usePermisos } from '@/hooks/use-permisos';
import { formatoFecha, formatoNumero } from '@/lib/formato';

import { redondear } from './calculos';
import { ResumenTotales, TablaProductos, TerminosCondiciones, iniciales } from './piezas';
import type { CotizacionDetalle, Terminos } from './tipos';

interface Props {
    cotizacion: CotizacionDetalle | null;
    cargando: boolean;
    onCerrar: () => void;
    iva: number;
    terminos: Terminos;
    urls: { index: string };
}

/** «Ver» de una cotización, por pasos como en la vista anterior: Cliente → Productos → Resumen. */
export function DetalleCotizacion({ cotizacion: c, cargando, onCerrar, iva, terminos, urls }: Props) {
    const { puede } = usePermisos();
    const subtotal = c ? c.total : 0;
    const montoIva = redondear((subtotal * iva) / 100);

    return (
        <Dialog open={Boolean(c) || cargando} onOpenChange={(a) => !a && onCerrar()}>
            <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-4xl">
                {!c ? (
                    <>
                        <DialogHeader>
                            <DialogTitle>Cotización</DialogTitle>
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
                                Cotización #{c.id} <EstadoBadge estado={c.estado} />
                            </DialogTitle>
                            <DialogDescription className="flex flex-wrap items-center gap-2">
                                {c.creador ? (
                                    <>
                                        {c.creador.avatar ? (
                                            <img src={c.creador.avatar} alt="" className="size-5 rounded-full object-cover" />
                                        ) : (
                                            <span className="grid size-5 place-items-center rounded-full bg-white/20 text-[0.6rem] font-semibold text-white">{iniciales(c.creador.nombre)}</span>
                                        )}
                                        Creada por {c.creador.nombre}
                                        {c.creador.fecha && ` el ${formatoFecha(c.creador.fecha)} ${c.creador.fecha.slice(11)}`}
                                    </>
                                ) : (
                                    'Creador desconocido'
                                )}
                            </DialogDescription>
                        </DialogHeader>

                        <Asistente
                            key={c.id}
                            final={
                                puede('cotizaciones.pdf') && (
                                    <Button variant="outline" asChild>
                                        <a href={`${urls.index}/${c.id}/pdf`} target="_blank" rel="noopener">
                                            <FileText /> Exportar PDF
                                        </a>
                                    </Button>
                                )
                            }
                            pasos={[
                                {
                                    titulo: 'Cliente',
                                    descripcion: 'Datos del cliente y de la cotización.',
                                    contenido: (
                                        <div className="grid gap-4 md:grid-cols-2">
                                            <section className="grid gap-3 rounded-lg border p-4">
                                                <h3 className="text-sm font-medium">Cliente</h3>
                                                <dl className="grid gap-3 sm:grid-cols-2">
                                                    <Dato icono={<UserRound />} etiqueta={c.cliente_datos?.juridico ? 'Razón social' : 'Nombre'}>
                                                        <span className={c.cliente_inhabilitado ? 'text-muted-foreground' : undefined}>{c.cliente}</span>
                                                        {c.cliente_inhabilitado && <span className="bg-destructive/10 text-destructive ml-2 rounded-full px-2 py-0.5 text-xs">Inhabilitado</span>}
                                                    </Dato>
                                                    <Dato icono={<IdCard />} etiqueta="Documento">
                                                        <span className="tabular">{c.cliente_doc ?? '—'}</span>
                                                    </Dato>
                                                    <Dato icono={<Phone />} etiqueta="Teléfono">
                                                        {c.cliente_datos?.telefono ?? '—'}
                                                    </Dato>
                                                    <Dato icono={<Mail />} etiqueta="Correo">
                                                        {c.cliente_datos?.email ?? '—'}
                                                    </Dato>
                                                </dl>
                                            </section>
                                            <section className="grid gap-3 rounded-lg border p-4">
                                                <h3 className="text-sm font-medium">Cotización</h3>
                                                <dl className="grid gap-3 sm:grid-cols-2">
                                                    <Dato icono={<CalendarDays />} etiqueta="Fecha de emisión">
                                                        {c.fecha ? formatoFecha(c.fecha) : '—'}
                                                    </Dato>
                                                    <Dato icono={<CalendarCheck />} etiqueta="Válida hasta">
                                                        {c.validez ? formatoFecha(c.validez) : '—'}
                                                    </Dato>
                                                    <Dato icono={<Flag />} etiqueta="Estado">
                                                        <EstadoBadge estado={c.estado} />
                                                    </Dato>
                                                    <Dato icono={<Zap />} etiqueta="Prioridad">
                                                        {c.prioridad}
                                                    </Dato>
                                                </dl>
                                                {c.notas && (
                                                    <p className="text-muted-foreground border-t pt-3 text-sm whitespace-pre-line">
                                                        <span className="text-foreground block text-xs font-medium">Notas internas</span>
                                                        {c.notas}
                                                    </p>
                                                )}
                                            </section>
                                        </div>
                                    ),
                                },
                                {
                                    titulo: 'Productos',
                                    descripcion: 'Productos, tallas, colores y bordados incluidos.',
                                    contenido: (
                                        <div className="grid gap-3">
                                            <div className="grid grid-cols-2 gap-3 sm:max-w-md">
                                                <div className="rounded-lg border p-3">
                                                    <p className="text-muted-foreground text-xs">Líneas</p>
                                                    <p className="text-lg font-semibold tabular">{formatoNumero(c.grupos.length)}</p>
                                                </div>
                                                <div className="rounded-lg border p-3">
                                                    <p className="text-muted-foreground text-xs">Subtotal</p>
                                                    <Monto usd={c.total} tasa={c.tasa} />
                                                </div>
                                            </div>
                                            {c.grupos.length ? (
                                                <TablaProductos filas={c.grupos} tasa={c.tasa} />
                                            ) : (
                                                <p className="text-muted-foreground rounded-lg border border-dashed p-6 text-center text-sm">Esta cotización no tiene productos registrados.</p>
                                            )}
                                        </div>
                                    ),
                                },
                                {
                                    titulo: 'Resumen',
                                    descripcion: 'Totales, equivalente en Bs y términos de la cotización.',
                                    contenido: (
                                        <div className="grid gap-4 lg:grid-cols-[20rem_1fr] lg:items-start">
                                            <ResumenTotales subtotal={subtotal} iva={montoIva} porcentajeIva={iva} total={redondear(subtotal + montoIva)} tasa={c.tasa} />
                                            <TerminosCondiciones terminos={terminos} />
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
