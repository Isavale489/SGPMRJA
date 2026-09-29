import { Link, router, useForm } from '@inertiajs/react';
import { AlertTriangle, ArrowLeft, Banknote, CalendarDays, FileText, Landmark, Plus, Save, Smartphone, Trash2, X } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

import { Asistente } from '@/components/app/asistente';
import { Buscador } from '@/components/app/buscador';
import { Campo } from '@/components/app/campo';
import { EstadoBadge } from '@/components/app/estado-badge';
import { Monto } from '@/components/app/monto';
import { ProyeccionInsumos } from '@/components/app/proyeccion-insumos';
import { TasaBcv } from '@/components/app/tasa-bcv';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useGuardCambios } from '@/hooks/use-guard-cambios';
import AppLayout from '@/layouts/app-layout';
import { formatoBs, formatoFecha, formatoNumero, formatoUsd } from '@/lib/formato';
import { cn } from '@/lib/utils';
import { TablaProductos, TerminosCondiciones } from '@/pages/Cotizaciones/piezas';
import type { ClienteCotizacion, GrupoCotizacion, Prioridad, TasaGuardada } from '@/pages/Cotizaciones/tipos';

import { ResumenPago, useTasaPedido } from './piezas';
import { METODO, type MetodoPago, type PaginaFormularioPedido } from './tipos';

const PRIORIDADES: Prioridad[] = ['Normal', 'Alta', 'Urgente'];
const ICONO_METODO: Record<MetodoPago, typeof Banknote> = { efectivo: Banknote, transferencia: Landmark, pago_movil: Smartphone };
const num = (v: string) => {
    const n = parseFloat(v);
    return Number.isFinite(n) ? n : 0;
};
const r2 = (n: number) => Math.round(n * 100) / 100;

/** Suma días hábiles (lun–vie) a una fecha ISO local, como Carbon::addWeekdays. */
function sumarHabiles(iso: string, dias: number): string {
    const [a, m, d] = iso.split('-').map(Number);
    const f = new Date(a!, (m ?? 1) - 1, d ?? 1);
    let resto = dias;
    while (resto > 0) {
        f.setDate(f.getDate() + 1);
        if (f.getDay() !== 0 && f.getDay() !== 6) resto--;
    }
    return `${f.getFullYear()}-${String(f.getMonth() + 1).padStart(2, '0')}-${String(f.getDate()).padStart(2, '0')}`;
}

interface FilaPago {
    metodo: MetodoPago;
    monto: string;
    banco_id: string;
    referencia: string;
}

interface Datos {
    cotizacion_id: number | '';
    fecha_entrega_estimada: string;
    prioridad: Prioridad;
    pagos: FilaPago[];
}

/**
 * Crear un pedido desde una cotización aprobada o editar uno existente:
 * asistente Cliente → Productos → Pago → Resumen (el mismo de la vista
 * anterior). Los productos son los de la cotización y no se editan aquí; lo que
 * cambia es la entrega, la prioridad y los pagos. Un pedido completado solo
 * recibe pagos (el saldo a la entrega).
 */
export default function FormularioPedido(props: PaginaFormularioPedido) {
    const { pedido, cotizaciones, cotizacion, bancos, terminos, hoy, entregaPropuesta, urls } = props;
    const edicion = Boolean(pedido);
    const soloPagos = pedido?.estado === 'Completado';
    const [elegidaId, setElegidaId] = useState<number | null>(props.cotizacionPedida);
    const [pidiendo, setPidiendo] = useState(false);

    const form = useForm<Datos>({
        cotizacion_id: props.cotizacionPedida ?? '',
        fecha_entrega_estimada: pedido?.entrega ?? entregaPropuesta,
        prioridad: pedido?.prioridad ?? cotizaciones.find((c) => c.id === props.cotizacionPedida)?.prioridad ?? 'Normal',
        pagos: pedido?.pagos.map((p) => ({ metodo: p.metodo, monto: String(p.monto), banco_id: p.banco_id ? String(p.banco_id) : '', referencia: p.referencia ?? '' })) ?? [],
    });
    const { data, setData } = form;
    const e = form.errors as Record<string, string | undefined>;
    useGuardCambios(form.isDirty && !form.processing);

    // Lo que se muestra: el pedido (edición) o la cotización elegida (alta).
    const disponible = cotizaciones.find((c) => c.id === elegidaId);
    const elegida = !edicion && cotizacion && cotizacion.id === elegidaId ? cotizacion : null;
    const noConvertible = !edicion && elegidaId !== null && !pidiendo && (!disponible || (cotizacion && cotizacion.id === elegidaId && cotizacion.estado !== 'Aprobada'));
    const cliente: ClienteCotizacion | null = pedido?.cliente_datos ?? elegida?.cliente ?? null;
    const grupos: GrupoCotizacion[] = pedido?.grupos ?? elegida?.grupos ?? [];
    const total = pedido?.total ?? elegida?.total ?? 0;
    const tasa: TasaGuardada | undefined = pedido ? (pedido.tasa ?? undefined) : (elegida?.tasa ?? undefined);
    const t = useTasaPedido(tasa);

    const abono = r2(data.pagos.reduce((s, p) => s + num(p.monto), 0));
    const minimo = pedido ? pedido.abono_minimo : r2((total * terminos.abono) / 100);
    const minimoEntrega = pedido?.fecha ?? hoy;

    const elegir = (id: number | null) => {
        setElegidaId(id);
        const c = cotizaciones.find((x) => x.id === id);
        setData({ ...data, cotizacion_id: id ?? '', prioridad: c?.prioridad ?? 'Normal' });
        if (!id) return;
        setPidiendo(true);
        router.get(
            urls.crear,
            { cotizacion: id },
            { only: ['cotizacion', 'cotizacionPedida'], preserveState: true, preserveScroll: true, replace: true, onFinish: (v) => !v.interrupted && setPidiendo(false) },
        );
    };

    // ── Pagos ──
    const efectivos = data.pagos.filter((p) => p.metodo === 'efectivo').length;
    const agregarPago = (metodo: MetodoPago) => {
        const falta = r2(Math.max(0, (abono < minimo ? minimo : total) - abono));
        setData('pagos', [...data.pagos, { metodo, monto: falta > 0 ? String(falta) : '', banco_id: '', referencia: '' }]);
    };
    const cambiarPago = (i: number, cambios: Partial<FilaPago>) =>
        setData(
            'pagos',
            data.pagos.map((p, k) => (k === i ? { ...p, ...cambios } : p)),
        );
    const errorPago = (i: number, campo: string) => e[`pagos.${i}.${campo}`];
    const validarPagos = () => {
        if (data.pagos.some((p) => num(p.monto) <= 0)) return 'Cada pago necesita un monto mayor a cero.';
        if (data.pagos.some((p) => p.metodo !== 'efectivo' && (!p.banco_id || !p.referencia.trim()))) return 'Las transferencias y los pagos móviles necesitan banco y referencia.';
        if (efectivos > 1) return 'Registra el efectivo en un solo pago.';
        if (abono > total + 0.001) return `Los pagos (${formatoUsd(abono)}) superan el total del pedido (${formatoUsd(total)}).`;
        if (abono + 0.001 < minimo)
            return pedido && minimo + 0.001 < r2((total * terminos.abono) / 100)
                ? `El abono (${formatoUsd(abono)}) no puede quedar por debajo de lo ya registrado (${formatoUsd(minimo)}).`
                : `El abono (${formatoUsd(abono)}) no alcanza el mínimo de ${formatoUsd(minimo)} (${formatoNumero(terminos.abono)} % del total).`;
        return null;
    };

    form.transform((d) => ({
        ...(edicion ? {} : { cotizacion_id: d.cotizacion_id }),
        // Completado: solo pagos (entrega y prioridad no cambian).
        ...(soloPagos ? {} : { fecha_entrega_estimada: d.fecha_entrega_estimada, prioridad: d.prioridad }),
        pagos: d.pagos.map((p) => ({
            metodo: p.metodo,
            monto: num(p.monto),
            banco_id: p.metodo === 'efectivo' ? null : Number(p.banco_id) || null,
            referencia: p.metodo === 'efectivo' ? null : p.referencia.trim() || null,
        })),
    }));

    const [salto, setSalto] = useState<{ paso: number; n: number }>();
    const pasoConError = (errores: Record<string, string>) => {
        const k = Object.keys(errores);
        if (k.some((c) => ['cotizacion_id', 'fecha_entrega_estimada', 'prioridad'].includes(c))) return 0;
        if (k.some((c) => c.startsWith('pagos'))) return 2;
        return 3;
    };
    const guardar = (ev: React.FormEvent) => {
        ev.preventDefault();
        if (ev.target !== ev.currentTarget) return; // el submit de un diálogo abierto desde un paso
        const opciones = {
            preserveScroll: true,
            onError: (errores: Record<string, string>) => {
                toast.error('Revisa los campos marcados.');
                setSalto((s) => ({ paso: pasoConError(errores), n: (s?.n ?? 0) + 1 }));
            },
        };
        if (edicion) form.put(urls.guardar, opciones);
        else form.post(urls.guardar, opciones);
    };

    const lineasProyeccion = grupos.map((g) => ({ producto_id: g.producto_id, tipo_producto_id: g.tipo_producto_id, tela_id: g.insumo_tela_id, cantidad: g.unidades }));
    const titulo = pedido ? (soloPagos ? `Registrar pago · pedido #${pedido.id}` : `Editar pedido #${pedido.id}`) : 'Nuevo pedido';

    return (
        <AppLayout
            titulo={titulo}
            acciones={
                <>
                    <TasaBcv />
                    <Button variant="ghost" asChild>
                        <Link href={urls.index}>
                            <ArrowLeft /> Pedidos
                        </Link>
                    </Button>
                </>
            }
        >
            <form id="form-pedido" noValidate className="max-w-6xl" onSubmit={guardar}>
                <Card>
                    <CardContent className="grid gap-4">
                        {e.general && (
                            <p className="border-destructive/30 bg-destructive/8 text-destructive flex items-start gap-2 rounded-md border p-3 text-sm" role="alert">
                                <AlertTriangle className="mt-0.5 size-4 shrink-0" /> {e.general}
                            </p>
                        )}
                        <Asistente
                            salto={salto}
                            final={
                                <Button type="submit" disabled={form.processing}>
                                    <Save /> {edicion ? 'Guardar cambios' : 'Crear pedido'}
                                </Button>
                            }
                            pasos={[
                                {
                                    titulo: 'Cliente',
                                    descripcion: edicion ? 'El cliente viene de la cotización. Aquí se ajustan la entrega y la prioridad.' : 'El pedido sale de una cotización aprobada: elige cuál.',
                                    validar: () =>
                                        !edicion && !elegida
                                            ? 'Elige la cotización aprobada de la que sale el pedido.'
                                            : noConvertible
                                              ? 'Esa cotización ya no se puede convertir.'
                                              : !soloPagos && (!data.fecha_entrega_estimada || data.fecha_entrega_estimada < minimoEntrega)
                                                ? edicion
                                                    ? 'La entrega no puede ser antes de la fecha del pedido.'
                                                    : 'La entrega no puede ser antes de hoy.'
                                                : null,
                                    contenido: (
                                        <div className="grid gap-4">
                                            {!edicion &&
                                                (elegidaId === null ? (
                                                    <Campo
                                                        etiqueta="Cotización aprobada"
                                                        requerido
                                                        error={e.cotizacion_id}
                                                        ayuda={cotizaciones.length ? `${cotizaciones.length} cotizaciones aprobadas y vigentes sin pedido.` : undefined}
                                                    >
                                                        {cotizaciones.length ? (
                                                            <Buscador
                                                                etiqueta="Buscar cotización"
                                                                placeholder="N.º de cotización, cliente o documento…"
                                                                buscarVacio
                                                                buscar={(q) => {
                                                                    const k = q.trim().toLowerCase().replace(/^#/, '');
                                                                    return cotizaciones
                                                                        .filter(
                                                                            (c) => !k || String(c.id) === k || c.cliente.toLowerCase().includes(k) || (c.cliente_doc ?? '').toLowerCase().includes(k),
                                                                        )
                                                                        .slice(0, 30);
                                                                }}
                                                                clave={(c) => c.id}
                                                                opcion={(c) => (
                                                                    <span className="flex items-baseline justify-between gap-3">
                                                                        <span className="truncate">
                                                                            <span className="text-muted-foreground tabular">#{c.id}</span> <span className="font-medium">{c.cliente}</span>
                                                                            <span className="text-muted-foreground block text-xs">
                                                                                {c.lineas} {c.lineas === 1 ? 'línea' : 'líneas'} · válida hasta {c.validez ? formatoFecha(c.validez) : '—'}
                                                                            </span>
                                                                        </span>
                                                                        <span className="shrink-0 text-right tabular">
                                                                            {formatoUsd(c.total)}
                                                                            {c.tasa && <span className="text-muted-foreground block text-xs">{formatoBs(c.total * c.tasa)}</span>}
                                                                        </span>
                                                                    </span>
                                                                )}
                                                                onElegir={(c) => elegir(c.id)}
                                                                vacio={() => 'Ninguna cotización coincide.'}
                                                            />
                                                        ) : (
                                                            <p className="text-muted-foreground rounded-lg border border-dashed p-4 text-sm">
                                                                No hay cotizaciones aprobadas y vigentes sin pedido.{' '}
                                                                <Link href={urls.cotizaciones} className="text-primary underline">
                                                                    Ir a Cotizaciones
                                                                </Link>
                                                            </p>
                                                        )}
                                                    </Campo>
                                                ) : (
                                                    <div
                                                        className={cn(
                                                            'flex flex-wrap items-start justify-between gap-3 rounded-lg border p-3 text-sm',
                                                            noConvertible ? 'border-destructive/40 bg-destructive/5' : 'bg-muted/40',
                                                        )}
                                                    >
                                                        <div className="grid gap-0.5">
                                                            <span className="flex items-center gap-2 font-medium">
                                                                <FileText className="size-4" /> Cotización #{elegidaId}
                                                            </span>
                                                            {pidiendo ? (
                                                                <span className="text-muted-foreground">Cargando…</span>
                                                            ) : noConvertible ? (
                                                                <span className="text-destructive">
                                                                    {cotizacion && cotizacion.id === elegidaId
                                                                        ? `Está ${cotizacion.estado.toLowerCase()}: no se puede convertir.`
                                                                        : 'Ya tiene pedido, venció o no está aprobada.'}
                                                                </span>
                                                            ) : (
                                                                elegida && (
                                                                    <span className="text-muted-foreground">
                                                                        Emitida {elegida.fecha ? formatoFecha(elegida.fecha) : '—'} · válida hasta{' '}
                                                                        {elegida.validez ? formatoFecha(elegida.validez) : '—'} · {formatoUsd(elegida.total)}
                                                                    </span>
                                                                )
                                                            )}
                                                        </div>
                                                        <Button type="button" variant="ghost" size="sm" onClick={() => elegir(null)}>
                                                            <X /> Cambiar
                                                        </Button>
                                                    </div>
                                                ))}

                                            {pedido && (
                                                <p className="text-muted-foreground flex flex-wrap items-center gap-2 text-sm">
                                                    <EstadoBadge estado={pedido.estado} />
                                                    {pedido.formalizacion
                                                        ? `Formalizado el ${formatoFecha(pedido.formalizacion)}: las líneas quedaron fijas.`
                                                        : pedido.porcentaje_abonado + 0.001 < terminos.abono
                                                          ? 'Aún no alcanza el abono mínimo.'
                                                          : 'Las líneas quedaron fijas al crearlo.'}
                                                </p>
                                            )}
                                            {cliente && (
                                                <div className="grid gap-1 rounded-lg border p-3 text-sm">
                                                    <p className="font-medium">
                                                        {cliente.nombre} <span className="text-muted-foreground font-normal tabular">{cliente.documento}</span>
                                                    </p>
                                                    <p className="text-muted-foreground text-xs">
                                                        {[cliente.telefono, cliente.email].filter(Boolean).join(' · ') || 'Sin teléfono ni correo'}
                                                        {cliente.inhabilitado && <span className="text-destructive"> · cliente inhabilitado</span>}
                                                    </p>
                                                </div>
                                            )}

                                            <div className="grid gap-4 sm:grid-cols-2">
                                                <Campo
                                                    etiqueta="Entrega estimada"
                                                    requerido
                                                    error={e.fecha_entrega_estimada}
                                                    ayuda={soloPagos ? 'El pedido está completado: la entrega ya no cambia.' : `Los términos prometen ${terminos.dias} días hábiles desde el abono.`}
                                                >
                                                    <Input
                                                        type="date"
                                                        value={data.fecha_entrega_estimada}
                                                        min={minimoEntrega}
                                                        disabled={soloPagos}
                                                        onChange={(ev) => setData('fecha_entrega_estimada', ev.target.value)}
                                                    />
                                                </Campo>
                                                <fieldset className="grid gap-2" disabled={soloPagos}>
                                                    <legend className="mb-1 text-sm font-medium">Prioridad</legend>
                                                    <div className="flex flex-wrap gap-2">
                                                        {PRIORIDADES.map((p) => (
                                                            <label
                                                                key={p}
                                                                className={cn(
                                                                    'flex cursor-pointer items-center gap-2 rounded-md border px-3 py-1.5 text-sm',
                                                                    data.prioridad === p && 'border-primary bg-primary/5',
                                                                    soloPagos && 'cursor-not-allowed opacity-60',
                                                                )}
                                                            >
                                                                <input
                                                                    type="radio"
                                                                    name="prioridad"
                                                                    value={p}
                                                                    checked={data.prioridad === p}
                                                                    onChange={() => setData('prioridad', p)}
                                                                    className="accent-primary"
                                                                />
                                                                {p}
                                                            </label>
                                                        ))}
                                                    </div>
                                                    {!edicion && <p className="text-muted-foreground text-xs">Viene de la cotización; se puede cambiar.</p>}
                                                </fieldset>
                                            </div>
                                            {!soloPagos && (
                                                <div className="flex flex-wrap items-center gap-2" role="group" aria-label="Entrega rápida">
                                                    <CalendarDays className="text-muted-foreground size-4" aria-hidden />
                                                    {[15, terminos.dias, 45]
                                                        .filter((d, i, l) => l.indexOf(d) === i)
                                                        .map((dias) => {
                                                            const valor = sumarHabiles(hoy, dias);
                                                            return (
                                                                <Button
                                                                    key={dias}
                                                                    type="button"
                                                                    size="sm"
                                                                    variant={data.fecha_entrega_estimada === valor ? 'secondary' : 'outline'}
                                                                    aria-pressed={data.fecha_entrega_estimada === valor}
                                                                    onClick={() => setData('fecha_entrega_estimada', valor)}
                                                                >
                                                                    {dias} días hábiles
                                                                </Button>
                                                            );
                                                        })}
                                                </div>
                                            )}
                                        </div>
                                    ),
                                },
                                {
                                    titulo: 'Productos',
                                    descripcion: 'Lo pactado en la cotización (solo lectura).',
                                    contenido: (
                                        <div className="grid gap-3">
                                            <p className="text-muted-foreground text-sm">
                                                {edicion
                                                    ? 'Las líneas del pedido quedaron fijas al crearlo: no se aceptan cambios en productos, tallas, cantidades ni diseño.'
                                                    : 'Para cambiar productos, tallas o precios, edita la cotización antes de convertirla.'}
                                            </p>
                                            {grupos.length ? (
                                                <TablaProductos filas={grupos} tasa={tasa ?? null} />
                                            ) : (
                                                <p className="text-muted-foreground rounded-lg border border-dashed p-6 text-center text-sm">Elige una cotización para ver sus productos.</p>
                                            )}
                                            <p className="text-right text-sm">
                                                Total: <Monto usd={total} tasa={tasa ?? null} className="items-end" />
                                            </p>
                                        </div>
                                    ),
                                },
                                {
                                    titulo: 'Pago',
                                    descripcion: `Para formalizar el pedido hace falta un abono mínimo del ${formatoNumero(terminos.abono)} %.`,
                                    validar: validarPagos,
                                    contenido: (
                                        <div className="grid gap-4 lg:grid-cols-[1fr_22rem] lg:items-start">
                                            <div className="grid min-w-0 gap-3">
                                                {e.pagos && (
                                                    <p className="text-destructive text-sm" role="alert">
                                                        {e.pagos}
                                                    </p>
                                                )}
                                                {data.pagos.map((p, i) => {
                                                    const Icono = ICONO_METODO[p.metodo];
                                                    return (
                                                        <fieldset key={i} className="grid gap-3 rounded-lg border p-3 sm:grid-cols-[10rem_9rem_1fr_1fr_auto] sm:items-start">
                                                            <legend className="sr-only">Pago {i + 1}</legend>
                                                            <span className="flex items-center gap-2 pt-2 text-sm font-medium">
                                                                <Icono className="text-muted-foreground size-4" /> {METODO[p.metodo]}
                                                            </span>
                                                            <Campo
                                                                etiqueta="Monto ($)"
                                                                error={errorPago(i, 'monto')}
                                                                ayuda={
                                                                    t && num(p.monto) > 0
                                                                        ? `${formatoBs(num(p.monto) * t.valor)} · ${t.fecha ? `Tasa BCV (${formatoFecha(t.fecha)})` : 'tasa guardada'}`
                                                                        : undefined
                                                                }
                                                            >
                                                                <Input
                                                                    type="number"
                                                                    min={0.01}
                                                                    step="0.01"
                                                                    inputMode="decimal"
                                                                    value={p.monto}
                                                                    onChange={(ev) => cambiarPago(i, { monto: ev.target.value })}
                                                                    className="tabular"
                                                                    aria-label={`Monto del pago ${i + 1} en dólares`}
                                                                />
                                                            </Campo>
                                                            {p.metodo !== 'efectivo' ? (
                                                                <>
                                                                    <Campo etiqueta="Banco" error={errorPago(i, 'banco_id')}>
                                                                        {(control) => (
                                                                            <Select value={p.banco_id || undefined} onValueChange={(v) => cambiarPago(i, { banco_id: v })}>
                                                                                <SelectTrigger {...control} className="w-full" aria-label={`Banco del pago ${i + 1}`}>
                                                                                    <SelectValue placeholder="Elige el banco" />
                                                                                </SelectTrigger>
                                                                                <SelectContent>
                                                                                    {bancos.map((b) => (
                                                                                        <SelectItem key={b.id} value={String(b.id)}>
                                                                                            {b.nombre}
                                                                                        </SelectItem>
                                                                                    ))}
                                                                                </SelectContent>
                                                                            </Select>
                                                                        )}
                                                                    </Campo>
                                                                    <Campo etiqueta="Referencia" error={errorPago(i, 'referencia')}>
                                                                        <Input
                                                                            value={p.referencia}
                                                                            maxLength={255}
                                                                            inputMode="numeric"
                                                                            onChange={(ev) => cambiarPago(i, { referencia: ev.target.value })}
                                                                            className="tabular"
                                                                            aria-label={`Referencia del pago ${i + 1}`}
                                                                        />
                                                                    </Campo>
                                                                </>
                                                            ) : (
                                                                <span className="text-muted-foreground pt-2 text-xs sm:col-span-2">En efectivo no se pide banco ni referencia.</span>
                                                            )}
                                                            <Button
                                                                type="button"
                                                                variant="ghost"
                                                                size="icon"
                                                                className="text-destructive sm:mt-6"
                                                                onClick={() =>
                                                                    setData(
                                                                        'pagos',
                                                                        data.pagos.filter((_, k) => k !== i),
                                                                    )
                                                                }
                                                                aria-label={`Quitar el pago ${i + 1}`}
                                                            >
                                                                <Trash2 />
                                                            </Button>
                                                        </fieldset>
                                                    );
                                                })}
                                                {!data.pagos.length && (
                                                    <p className="text-muted-foreground rounded-lg border border-dashed p-4 text-center text-sm">
                                                        Agrega el primer pago (al menos {formatoUsd(minimo)}).
                                                    </p>
                                                )}
                                                <div className="flex flex-wrap gap-2">
                                                    {props.metodos.map((m) => {
                                                        const Icono = ICONO_METODO[m];
                                                        return (
                                                            <Button key={m} type="button" variant="outline" size="sm" disabled={m === 'efectivo' && efectivos > 0} onClick={() => agregarPago(m)}>
                                                                <Plus /> <Icono /> {METODO[m]}
                                                            </Button>
                                                        );
                                                    })}
                                                </div>
                                            </div>
                                            <ResumenPago total={total} abono={abono} minimoPorcentaje={terminos.abono} minimo={minimo} tasa={tasa} />
                                        </div>
                                    ),
                                },
                                {
                                    titulo: 'Resumen',
                                    descripcion: 'Revisa todo antes de guardar.',
                                    contenido: (
                                        <div className="grid gap-4 lg:grid-cols-[1fr_22rem] lg:items-start">
                                            <div className="grid min-w-0 gap-4">
                                                <dl className="grid gap-3 rounded-lg border p-4 text-sm sm:grid-cols-2">
                                                    <div>
                                                        <dt className="text-muted-foreground text-xs">Cliente</dt>
                                                        <dd className="font-medium">{cliente?.nombre ?? '—'}</dd>
                                                    </div>
                                                    <div>
                                                        <dt className="text-muted-foreground text-xs">Cotización</dt>
                                                        <dd>#{pedido?.cotizacion_id ?? elegidaId ?? '—'}</dd>
                                                    </div>
                                                    <div>
                                                        <dt className="text-muted-foreground text-xs">Entrega estimada</dt>
                                                        <dd>{data.fecha_entrega_estimada ? formatoFecha(data.fecha_entrega_estimada) : '—'}</dd>
                                                    </div>
                                                    <div>
                                                        <dt className="text-muted-foreground text-xs">Prioridad</dt>
                                                        <dd>{data.prioridad}</dd>
                                                    </div>
                                                    <div className="sm:col-span-2">
                                                        <dt className="text-muted-foreground text-xs">Pagos</dt>
                                                        <dd>{data.pagos.length ? data.pagos.map((p) => `${METODO[p.metodo]} ${formatoUsd(num(p.monto))}`).join(' · ') : 'Ninguno'}</dd>
                                                    </div>
                                                </dl>
                                                {!edicion && grupos.length > 0 && (
                                                    <ProyeccionInsumos url={urls.proyeccion} lineas={lineasProyeccion} urlCrearCompra={urls.crearCompra ?? undefined} origen="pedido" />
                                                )}
                                                <TerminosCondiciones terminos={terminos} />
                                            </div>
                                            <ResumenPago total={total} abono={abono} minimoPorcentaje={terminos.abono} minimo={minimo} tasa={tasa} />
                                        </div>
                                    ),
                                },
                            ]}
                        />
                    </CardContent>
                </Card>
            </form>
        </AppLayout>
    );
}
