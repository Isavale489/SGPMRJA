import { usePage } from '@inertiajs/react';
import { Scissors, Shirt } from 'lucide-react';
import type { ReactNode } from 'react';

import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { formatoBs, formatoFecha, formatoNumero, formatoUsd } from '@/lib/formato';
import { cn } from '@/lib/utils';

import type { BordadoLinea, TasaGuardada, Terminos } from './tipos';

/** Una fila de la tabla de productos (grupo guardado o bloque del asistente). */
export interface FilaProductos {
    clave: string;
    nombre: string;
    codigo: string | null;
    variante: string;
    imagen: string | null;
    color: { nombre: string; hex: string | null } | null;
    tallas: { talla: string; genero: string | null; cantidad: number }[];
    unidades: number;
    precio_base: number;
    recargo: number;
    bordados: BordadoLinea[];
    subtotal: number;
}

/** Muestra de color (con borde si es muy clara). */
export function Muestra({ hex, className }: { hex: string | null | undefined; className?: string }) {
    return <span aria-hidden className={cn('inline-block size-3 shrink-0 rounded-full border border-black/15', className)} style={{ background: hex ?? 'transparent' }} />;
}

/** Tabla agrupada por producto + color + bordados (Ver y paso Productos). */
/** `tasa`: la guardada del documento; sin ella (asistente), la vigente del día. */
export function TablaProductos({
    filas,
    tasa: guardada,
    acciones,
}: {
    filas: FilaProductos[];
    tasa?: TasaGuardada | { valor: number; fecha: string } | null;
    acciones?: (f: FilaProductos) => ReactNode;
}) {
    const { tasaBcv } = usePage().props;
    const tasa = guardada === undefined ? tasaBcv : guardada;
    return (
        <div className="overflow-x-auto rounded-lg border">
            <Table>
                <TableHeader>
                    <TableRow className="hover:bg-transparent">
                        <TableHead className="w-8">#</TableHead>
                        <TableHead>Producto</TableHead>
                        <TableHead>Color</TableHead>
                        <TableHead>Tallas y cantidades</TableHead>
                        <TableHead className="text-right">Unid.</TableHead>
                        <TableHead className="text-right">Precio U.</TableHead>
                        <TableHead className="text-right">Subtotal</TableHead>
                        {acciones && (
                            <TableHead className="w-28">
                                <span className="sr-only">Acciones</span>
                            </TableHead>
                        )}
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {filas.map((f, i) => (
                        <TableRow key={f.clave} className="align-top">
                            <TableCell className="text-muted-foreground tabular">{i + 1}</TableCell>
                            <TableCell className="min-w-48">
                                <span className="font-medium">{f.nombre}</span>
                                {f.codigo && <code className="text-muted-foreground block font-mono text-xs">{f.codigo}</code>}
                                {f.variante && <span className="text-muted-foreground block text-xs">{f.variante}</span>}
                            </TableCell>
                            <TableCell className="whitespace-nowrap">
                                {f.color ? (
                                    <span className="inline-flex items-center gap-1.5 text-sm">
                                        <Muestra hex={f.color.hex} /> {f.color.nombre}
                                    </span>
                                ) : (
                                    <span className="text-muted-foreground text-sm">Sin color</span>
                                )}
                            </TableCell>
                            <TableCell className="min-w-52">
                                <div className="flex flex-wrap gap-1">
                                    {f.tallas.map((t, n) => (
                                        <span key={n} className="bg-muted inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-xs tabular">
                                            {t.talla}
                                            {t.genero && <span className="text-muted-foreground">· {t.genero}</span>}
                                            <span className="text-muted-foreground">×</span>
                                            <strong>{t.cantidad}</strong>
                                        </span>
                                    ))}
                                </div>
                                <LineaBordado bordados={f.bordados} recargo={f.recargo} unidades={f.unidades} tasa={tasa} />
                            </TableCell>
                            <TableCell className="text-right font-medium tabular">{formatoNumero(f.unidades)}</TableCell>
                            <TableCell className="text-right tabular whitespace-nowrap">
                                {formatoUsd(f.precio_base + f.recargo)}
                                {f.recargo > 0 && (
                                    <span className="text-muted-foreground block text-xs">
                                        {formatoUsd(f.precio_base)} + {formatoUsd(f.recargo)}
                                    </span>
                                )}
                            </TableCell>
                            <TableCell className="text-right font-semibold tabular whitespace-nowrap">
                                {formatoUsd(f.subtotal)}
                                {tasa && <span className="text-muted-foreground block text-xs font-normal">{formatoBs(f.subtotal * tasa.valor)}</span>}
                            </TableCell>
                            {acciones && <TableCell className="text-right">{acciones(f)}</TableCell>}
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </div>
    );
}

function LineaBordado({ bordados, recargo, unidades, tasa }: { bordados: BordadoLinea[]; recargo: number; unidades: number; tasa: TasaGuardada | { valor: number; fecha: string } | null }) {
    if (!bordados.length) return <p className="text-muted-foreground mt-1.5 text-xs">Sin bordado</p>;
    const total = recargo * unidades;
    const detalle = bordados.map((b) => `${b.logo ? `${b.logo} → ` : ''}${b.nombre_aplicado} ×${b.cantidad}`).join(' · ');
    return (
        <p className="text-primary mt-1.5 flex items-start gap-1 text-xs" title={detalle}>
            <Scissors className="mt-0.5 size-3 shrink-0" />
            <span>
                {bordados.length} {bordados.length === 1 ? 'bordado' : 'bordados'} · +{formatoUsd(recargo)}/u
                {total > 0 && tasa && ` · ${formatoUsd(total)} (${formatoBs(total * tasa.valor)})`}
                <span className="text-muted-foreground block">{detalle}</span>
            </span>
        </p>
    );
}

/** Miniatura del producto (imagen del tipo o ícono). */
export function Miniatura({ src, className }: { src: string | null; className?: string }) {
    return (
        <span className={cn('bg-muted grid size-12 shrink-0 place-items-center overflow-hidden rounded-md', className)}>
            {src ? <img src={src} alt="" className="size-full object-cover" /> : <Shirt className="text-muted-foreground size-5" />}
        </span>
    );
}

/**
 * Resumen final: tasa (con su fecha), subtotal, IVA, total y equivalente en Bs.
 * Con `tasa` usa la guardada del documento; sin ella, la vigente del día.
 */
export function ResumenTotales({
    subtotal,
    iva,
    porcentajeIva,
    total,
    tasa,
    className,
}: {
    subtotal: number;
    iva: number;
    porcentajeIva: number;
    total: number;
    tasa?: TasaGuardada;
    className?: string;
}) {
    const { tasaBcv } = usePage().props;
    const t = tasa === undefined ? tasaBcv : tasa;
    return (
        <dl className={cn('bg-muted/40 grid gap-2 rounded-lg border p-4 text-sm', className)}>
            <div className="text-muted-foreground flex justify-between gap-3 text-xs">
                <dt>{t?.fecha ? `Tasa BCV (${formatoFecha(t.fecha)})` : t ? 'Tasa guardada' : 'Tasa BCV'}</dt>
                <dd className="tabular">{t ? formatoBs(t.valor) : 'No disponible'}</dd>
            </div>
            <div className="flex justify-between gap-3">
                <dt className="text-muted-foreground">Subtotal</dt>
                <dd className="tabular">{formatoUsd(subtotal)}</dd>
            </div>
            <div className="flex justify-between gap-3">
                <dt className="text-muted-foreground">IVA ({formatoNumero(porcentajeIva)} %)</dt>
                <dd className="tabular">{formatoUsd(iva)}</dd>
            </div>
            <div className="flex justify-between gap-3 border-t pt-2 text-base font-semibold">
                <dt>Total</dt>
                <dd className="tabular">{formatoUsd(total)}</dd>
            </div>
            <div className="text-muted-foreground flex justify-between gap-3">
                <dt>Equivalente en Bs</dt>
                <dd className="tabular">{t ? formatoBs(total * t.valor) : 'Sin tasa BCV'}</dd>
            </div>
        </dl>
    );
}

/** Términos y condiciones (se incluyen en el PDF). El abono y los días vienen de la configuración. */
export function TerminosCondiciones({ terminos }: { terminos: Terminos }) {
    const resto = 100 - terminos.abono;
    return (
        <section className="grid gap-2 rounded-lg border p-3 text-sm">
            <h3 className="font-medium">Términos y condiciones</h3>
            <details open className="group">
                <summary className="cursor-pointer font-medium">Condiciones para pedidos</summary>
                <ul className="text-muted-foreground mt-2 grid list-disc gap-1 pl-5">
                    <li>
                        <strong className="text-foreground">Formalización del pedido:</strong> para iniciar la producción, el cliente debe abonar el {formatoNumero(terminos.abono)} % del costo total.
                    </li>
                    <li>
                        <strong className="text-foreground">Tiempo de ejecución:</strong> {terminos.dias} días hábiles, contados desde la confirmación del pago inicial.
                    </li>
                    <li>
                        <strong className="text-foreground">Saldo restante:</strong> el {formatoNumero(resto)} % restante se cancela al momento de la entrega.
                    </li>
                    <li>
                        <strong className="text-foreground">Modificaciones:</strong> una vez formalizado el pedido, no se aceptan cambios en tallas, cantidades ni diseño.
                    </li>
                    <li>
                        <strong className="text-foreground">Entrega:</strong> el plazo comienza a contarse desde el abono del {formatoNumero(terminos.abono)} % inicial.
                    </li>
                </ul>
            </details>
            <details>
                <summary className="cursor-pointer font-medium">Servicio de bordado</summary>
                <ul className="text-muted-foreground mt-2 grid list-disc gap-1 pl-5">
                    <li>
                        <strong className="text-foreground">Prendas externas:</strong> deben estar limpias y en buen estado; no nos responsabilizamos por prendas con desgaste o costuras débiles.
                    </li>
                    <li>
                        <strong className="text-foreground">Aprobación del diseño:</strong> el cliente aprueba ubicación y tamaño antes de iniciar; una vez comenzado, no hay cambios.
                    </li>
                    <li>
                        <strong className="text-foreground">Anticipo:</strong> 50 % para programar el trabajo, 50 % a la entrega.
                    </li>
                    <li>
                        <strong className="text-foreground">Tiempo de entrega:</strong> de 7 a 10 días hábiles según el volumen, desde el anticipo y la aprobación del diseño.
                    </li>
                    <li>
                        <strong className="text-foreground">Digitalización:</strong> si el logo no está en el sistema, el cliente debe enviarlo en JPG/PNG; se hace una prueba física antes de bordar
                        las prendas.
                    </li>
                </ul>
            </details>
            <p className="text-muted-foreground text-xs">Estos términos se incluyen en el PDF.</p>
        </section>
    );
}

/** Iniciales para el avatar del cliente o del creador. */
export function iniciales(nombre: string | null | undefined) {
    const partes = (nombre ?? '').trim().split(/\s+/).filter(Boolean);
    if (!partes.length) return '—';
    return (partes.length > 1 ? partes[0]![0]! + partes[1]![0]! : partes[0]!.slice(0, 2)).toUpperCase();
}
