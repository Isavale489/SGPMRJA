import { Link } from '@inertiajs/react';
import { Ban, CalendarDays, CheckCheck, Copy, FileText, Landmark, Mail, Pencil, Phone, ReceiptText, Trash2, UserRound } from 'lucide-react';

import { Dato } from '@/components/app/dato';
import { EstadoBadge } from '@/components/app/estado-badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Skeleton } from '@/components/ui/skeleton';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { usePermisos } from '@/hooks/use-permisos';
import { formatoBs, formatoFecha, formatoNumero, formatoUsd } from '@/lib/formato';

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

                        <dl className="grid gap-4 sm:grid-cols-2">
                            <Dato icono={<UserRound />} etiqueta="Proveedor">
                                <span className="font-medium">{compra.proveedor?.nombre ?? '—'}</span>
                                {compra.proveedor?.doc && <span className="text-muted-foreground block text-xs tabular">{compra.proveedor.doc}</span>}
                            </Dato>
                            <Dato icono={<ReceiptText />} etiqueta="Factura">
                                <span className="tabular">{compra.numero_factura ?? 'S/N'}</span>
                            </Dato>
                            {compra.proveedor?.tel && (
                                <Dato icono={<Phone />} etiqueta="Teléfono">
                                    <span className="tabular">{compra.proveedor.tel}</span>
                                </Dato>
                            )}
                            {compra.proveedor?.email && (
                                <Dato icono={<Mail />} etiqueta="Correo">{compra.proveedor.email}</Dato>
                            )}
                            <Dato icono={<CalendarDays />} etiqueta="Fecha de compra">
                                <span className="tabular">{compra.fecha ? formatoFecha(compra.fecha) : '—'}</span>
                            </Dato>
                            <Dato icono={<Landmark />} etiqueta={compra.tasa_fecha ? `Tasa BCV (${formatoFecha(compra.tasa_fecha)})` : 'Tasa de la compra'}>
                                <span className="tabular">{compra.tasa ? `Bs ${formatoTasa(compra.tasa)}` : '—'}</span>
                                {compra.tasa && !compra.tasa_fecha && <span className="text-muted-foreground block text-xs">Ingresada manualmente</span>}
                            </Dato>
                        </dl>

                        <div className="overflow-x-auto rounded-lg border">
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
                                <TableFooter>
                                    <FilaTotal etiqueta="Subtotal" bs={compra.subtotal_bs} usd={compra.subtotal} />
                                    <FilaTotal etiqueta={`IVA (${formatoNumero(compra.iva_porcentaje)} %)`} bs={compra.iva_bs} usd={compra.iva} />
                                    <FilaTotal etiqueta="Total" bs={compra.total_bs} usd={compra.total} fuerte />
                                </TableFooter>
                            </Table>
                        </div>

                        {compra.observaciones && (
                            <div className="text-sm">
                                <p className="text-muted-foreground text-xs">Observaciones</p>
                                <p className="whitespace-pre-line">{compra.observaciones}</p>
                            </div>
                        )}

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

function FilaTotal({ etiqueta, bs, usd, fuerte }: { etiqueta: string; bs: number; usd: number; fuerte?: boolean }) {
    return (
        <TableRow className={fuerte ? 'text-base font-semibold' : 'font-normal'}>
            <TableCell colSpan={3} className="text-right">{etiqueta}</TableCell>
            <TableCell className="text-right tabular">
                {formatoBs(bs)}
                <span className="text-muted-foreground block text-xs font-normal">{formatoUsd(usd)}</span>
            </TableCell>
        </TableRow>
    );
}
