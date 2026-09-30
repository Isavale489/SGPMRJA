import { Link } from '@inertiajs/react';
import { Ban, CheckCheck, Copy, FileText, Mail, Pencil, Phone, Trash2 } from 'lucide-react';
import type { ReactNode } from 'react';

import { EstadoBadge } from '@/components/app/estado-badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Skeleton } from '@/components/ui/skeleton';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { usePermisos } from '@/hooks/use-permisos';
import { formatoBs, formatoFecha, formatoNumero, formatoUsd } from '@/lib/formato';
import { cn } from '@/lib/utils';

import { accionesDe } from './acciones';
import type { CompraDetalle } from './tipos';

interface Props {
    compra: CompraDetalle | null;
    cargando: boolean;
    onCerrar: () => void;
    urls: { index: string };
    onAccion: (accion: 'procesar' | 'anular' | 'eliminar' | 'clonar', compra: CompraDetalle) => void;
}

const formatoTasa = (v: number) => new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 4 }).format(v);

/** Ficha «Ver» de una compra: proveedor, comprobante, líneas en Bs y $, totales y acciones según su estado. */
export function DetalleCompra({ compra, cargando, onCerrar, urls, onAccion }: Props) {
    const { puede } = usePermisos();
    const a = compra ? accionesDe(compra, puede) : null;

    return (
        <Dialog open={Boolean(compra) || cargando} onOpenChange={(abierto) => !abierto && onCerrar()}>
            <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-3xl">
                {!compra ? (
                    <>
                        <DialogHeader>
                            <DialogTitle>Compra</DialogTitle>
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
                                Compra #{compra.id} <EstadoBadge estado={compra.estado} className="capitalize" />
                            </DialogTitle>
                            <DialogDescription>
                                Registrada por {compra.registrado_por.nombre}
                                {compra.creado && ` el ${formatoFecha(compra.creado)} ${compra.creado.slice(11)}`}
                            </DialogDescription>
                        </DialogHeader>

                        {compra.estado === 'anulada' && (
                            <p className="border-destructive/30 bg-destructive/8 rounded-md border p-3 text-sm">
                                Anulada{compra.anulado_por && ` por ${compra.anulado_por}`}
                                {compra.fecha_anulacion && ` el ${formatoFecha(compra.fecha_anulacion)} ${compra.fecha_anulacion.slice(11)}`}. El stock se revirtió.
                                {compra.clonada && ' Ya se clonó como borrador nuevo.'}
                            </p>
                        )}

                        {/* Vista de documento (la «cv-doc» del panel anterior): membrete, comprobante, detalle y totales. */}
                        <article className="bg-card grid gap-5 rounded-xl border p-4 sm:p-5" aria-label={`Comprobante de la compra #${compra.id}`}>
                            <header className="grid gap-4 sm:grid-cols-[1fr_minmax(0,17rem)] sm:items-start">
                                <div className="min-w-0">
                                    <p className="text-muted-foreground text-[0.65rem] font-semibold uppercase tracking-[0.12em]">Proveedor</p>
                                    <p className="text-seccion text-lg font-bold leading-tight">{compra.proveedor?.nombre ?? '—'}</p>
                                    {compra.proveedor?.doc && <p className="text-muted-foreground font-mono text-xs">{compra.proveedor.doc}</p>}
                                    <div className="text-muted-foreground mt-1.5 flex flex-wrap gap-x-4 gap-y-1 text-xs">
                                        {compra.proveedor?.tel && <span className="inline-flex items-center gap-1 tabular"><Phone className="size-3.5" aria-hidden /> {compra.proveedor.tel}</span>}
                                        {compra.proveedor?.email && <span className="inline-flex items-center gap-1"><Mail className="size-3.5" aria-hidden /> {compra.proveedor.email}</span>}
                                    </div>
                                </div>
                                <dl className="grid gap-1.5 text-sm">
                                    <Meta etiqueta="Factura">{compra.numero_factura ?? 'S/N'}</Meta>
                                    <Meta etiqueta="Fecha">{compra.fecha ? formatoFecha(compra.fecha) : '—'}</Meta>
                                    <Meta etiqueta={compra.tasa_fecha ? `Tasa BCV (${formatoFecha(compra.tasa_fecha)})` : 'Tasa (manual)'}>
                                        {compra.tasa ? `Bs ${formatoTasa(compra.tasa)}` : '—'}
                                    </Meta>
                                </dl>
                            </header>

                            <p className="text-muted-foreground border-seccion-acento/30 bg-muted/50 rounded-md border border-dashed px-3 py-1.5 text-center text-[0.65rem] font-semibold uppercase tracking-[0.12em]">
                                Detalle de insumos
                            </p>

                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="hover:bg-transparent">
                                            <TableHead>Insumo</TableHead>
                                            <TableHead className="text-right">Cantidad</TableHead>
                                            <TableHead className="text-right">Costo unitario</TableHead>
                                            <TableHead className="text-right">Subtotal</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {compra.items.map((i) => (
                                            <TableRow key={i.id}>
                                                <TableCell>
                                                    <span className="font-medium">{i.insumo}</span>
                                                    {i.codigo && <code className="text-muted-foreground ml-1.5 font-mono text-xs">{i.codigo}</code>}
                                                    {!i.aplica_iva && <span className="text-muted-foreground block text-xs">Exento de IVA</span>}
                                                </TableCell>
                                                <TableCell className="text-right tabular">{formatoNumero(i.cantidad)} <span className="text-muted-foreground text-xs">{i.unidad}</span></TableCell>
                                                <TableCell className="text-right tabular">
                                                    {formatoBs(i.costo_bs)}
                                                    <span className="text-muted-foreground block text-xs">{formatoUsd(i.costo)}</span>
                                                </TableCell>
                                                <TableCell className="text-right tabular">
                                                    {formatoBs(i.subtotal_bs)}
                                                    <span className="text-muted-foreground block text-xs">{formatoUsd(i.subtotal)}</span>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>

                            {/* Barra de totales: el monto a pagar, destacado. */}
                            <dl className="bg-muted/40 grid overflow-hidden rounded-lg border sm:grid-cols-3">
                                <Total etiqueta="Subtotal" bs={compra.subtotal_bs} usd={compra.subtotal} />
                                <Total etiqueta={`IVA (${formatoNumero(compra.iva_porcentaje)} %)`} bs={compra.iva_bs} usd={compra.iva} />
                                <Total etiqueta="Total a pagar" bs={compra.total_bs} usd={compra.total} pagar />
                            </dl>

                            {compra.observaciones && (
                                <div className="border-warning bg-warning/8 rounded-md border-l-[3px] px-3 py-2 text-sm">
                                    <p className="text-warning text-xs font-semibold">Observaciones</p>
                                    <p className="whitespace-pre-line">{compra.observaciones}</p>
                                </div>
                            )}
                        </article>

                        {a && (
                            <div className="flex flex-wrap gap-2 border-t pt-4">
                                {a.pdf && (
                                    <Button variant="outline" asChild>
                                        <a href={`${urls.index}/${compra.id}/pdf`} target="_blank" rel="noopener"><FileText /> Ver PDF</a>
                                    </Button>
                                )}
                                {a.editar && (
                                    <Button variant="outline" asChild>
                                        <Link href={`${urls.index}/${compra.id}/editar`}><Pencil /> Editar</Link>
                                    </Button>
                                )}
                                {a.clonar && <Button variant="outline" onClick={() => onAccion('clonar', compra)}><Copy /> Clonar como borrador</Button>}
                                <span className="flex-1" />
                                {a.eliminar && <Button variant="ghost" className="text-destructive" onClick={() => onAccion('eliminar', compra)}><Trash2 /> Eliminar</Button>}
                                {a.anular && <Button variant="outline" className="text-destructive" onClick={() => onAccion('anular', compra)}><Ban /> Anular</Button>}
                                {a.procesar && <Button onClick={() => onAccion('procesar', compra)}><CheckCheck /> Procesar</Button>}
                            </div>
                        )}
                    </>
                )}
            </DialogContent>
        </Dialog>
    );
}

/** Dato del comprobante con línea punteada hasta el valor, como en un recibo impreso. */
function Meta({ etiqueta, children }: { etiqueta: string; children: ReactNode }) {
    return (
        <div className="flex items-baseline gap-2">
            <dt className="text-muted-foreground shrink-0 text-xs">{etiqueta}</dt>
            <span className="border-seccion-acento/30 min-w-4 flex-1 border-b-2 border-dotted" aria-hidden />
            <dd className="text-seccion shrink-0 font-semibold tabular">{children}</dd>
        </div>
    );
}

function Total({ etiqueta, bs, usd, pagar }: { etiqueta: string; bs: number; usd: number; pagar?: boolean }) {
    return (
        <div className={cn('grid gap-0.5 border-t px-4 py-3 first:border-t-0 sm:border-t-0 sm:border-l sm:first:border-l-0', pagar && 'bg-success/8 shadow-[inset_3px_0_0_var(--success)]')}>
            <dt className="text-muted-foreground text-xs">{etiqueta}</dt>
            <dd className={cn('tabular', pagar ? 'text-success text-xl font-bold' : 'font-semibold')}>
                {formatoBs(bs)}
                <span className="text-muted-foreground block text-xs font-normal">{formatoUsd(usd)}</span>
            </dd>
        </div>
    );
}
