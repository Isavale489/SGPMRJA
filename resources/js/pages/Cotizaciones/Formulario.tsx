import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, CalendarDays, PackagePlus, Pencil, Save, Scissors, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';
import { toast } from 'sonner';

import { Asistente } from '@/components/app/asistente';
import { Campo } from '@/components/app/campo';
import { ConfirmarPeligro } from '@/components/app/confirmar-peligro';
import { Monto } from '@/components/app/monto';
import { ProyeccionInsumos } from '@/components/app/proyeccion-insumos';
import { TasaBcv } from '@/components/app/tasa-bcv';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { useGuardCambios } from '@/hooks/use-guard-cambios';
import AppLayout from '@/layouts/app-layout';
import { formatoFecha, formatoNumero, hoyLocalIso } from '@/lib/formato';
import { cn } from '@/lib/utils';

import { AgregarProducto } from './agregar-producto';
import { ConfigurarBordados } from './bordados';
import { bloqueDesdeGrupo, lineasDe, precioFinal, recargo, subtotalBloque, totales, unidades } from './calculos';
import { ResumenTotales, TablaProductos, TerminosCondiciones, iniciales, type FilaProductos } from './piezas';
import { SelectorCliente } from './selector-cliente';
import type { Bloque, ClienteCotizacion, ColorCatalogo, LogoCatalogo, PaginaFormularioCotizacion, Prioridad, TipoCatalogo } from './tipos';

const PRIORIDADES: { valor: Prioridad; ayuda: string }[] = [
    { valor: 'Normal', ayuda: 'Plazo estándar' },
    { valor: 'Alta', ayuda: 'Antes que las normales' },
    { valor: 'Urgente', ayuda: 'Primero en producción' },
];

/** Suma días a una fecha ISO local (sin pasar por UTC). */
function sumarDias(iso: string, dias: number): string {
    const [a, m, d] = iso.split('-').map(Number);
    const f = new Date(a!, (m ?? 1) - 1, (d ?? 1) + dias);
    return `${f.getFullYear()}-${String(f.getMonth() + 1).padStart(2, '0')}-${String(f.getDate()).padStart(2, '0')}`;
}

interface Datos {
    cliente_id: number | '';
    fecha_cotizacion: string;
    fecha_validez: string;
    prioridad: Prioridad;
    notas: string;
    bloques: Bloque[];
}

/**
 * Crear o editar una cotización: asistente Cliente → Productos → Resumen (el
 * mismo de la vista anterior, ahora en su propia página). Cada producto es un
 * bloque (variante + color + precio + bordados) con sus tallas × género; al
 * guardar se envía una línea por talla × género, como antes.
 */
export default function FormularioCotizacion(props: PaginaFormularioCotizacion) {
    const { cotizacion, iva, terminos, diasVigencia, urls } = props;
    const edicion = Boolean(cotizacion);
    const [cliente, setCliente] = useState<ClienteCotizacion | null>(cotizacion?.cliente ?? null);
    const [catalogo, setCatalogo] = useState<TipoCatalogo[]>(props.catalogo);
    const [colores, setColores] = useState<ColorCatalogo[]>(props.colores);
    const [logos, setLogos] = useState<LogoCatalogo[]>(props.logos);

    const emision = cotizacion?.fecha ?? hoyLocalIso();
    const form = useForm<Datos>({
        cliente_id: cotizacion?.cliente?.id ?? '',
        fecha_cotizacion: emision,
        fecha_validez: cotizacion?.validez ?? sumarDias(emision, diasVigencia),
        prioridad: cotizacion?.prioridad ?? 'Normal',
        notas: cotizacion?.notas ?? '',
        bloques: cotizacion?.grupos.map(bloqueDesdeGrupo) ?? [],
    });
    const { data, setData } = form;
    const e = form.errors as Record<string, string | undefined>;
    useGuardCambios(form.isDirty && !form.processing);

    const t = totales(data.bloques, iva);
    const [producto, setProducto] = useState<{ bloque?: Bloque; apertura: number }>();
    const [bordando, setBordando] = useState<Bloque>();
    const [quitando, setQuitando] = useState<Bloque>();

    const nombreTalla = useMemo(() => new Map(props.tallas.map((x) => [x.id, x.nombre])), [props.tallas]);
    const nombreGenero = useMemo(() => new Map(props.generos.map((x) => [x.id, x.nombre])), [props.generos]);
    const filas: FilaProductos[] = data.bloques.map((b) => {
        const color = colores.find((c) => c.id === b.color_id);
        return {
            clave: b.id,
            nombre: b.nombre,
            codigo: b.codigo,
            variante: b.variante,
            imagen: b.imagen,
            color: color ? { nombre: color.nombre, hex: color.hex } : null,
            tallas: b.tallas
                .filter((x) => x.cantidad > 0)
                .map((x) => ({ talla: nombreTalla.get(x.talla_id) ?? `#${x.talla_id}`, genero: nombreGenero.get(x.genero_id) ?? null, cantidad: x.cantidad })),
            unidades: unidades(b),
            precio_base: b.precio,
            recargo: recargo(b.bordados),
            bordados: b.bordados,
            subtotal: subtotalBloque(b),
        };
    });
    const porClave = (clave: string) => data.bloques.find((b) => b.id === clave);
    const reemplazar = (bloque: Bloque) => setData('bloques', data.bloques.some((b) => b.id === bloque.id) ? data.bloques.map((b) => (b.id === bloque.id ? bloque : b)) : [...data.bloques, bloque]);

    // Lo que se fabricaría: el servidor calcula insumos desde el tipo y la tela (aviso NO bloqueante).
    const lineasProyeccion = data.bloques
        .filter((b) => unidades(b) > 0)
        .map((b) => ({ producto_id: b.producto_id, tipo_producto_id: b.tipo_producto_id, tela_id: b.insumo_tela_id, cantidad: unidades(b) }));

    form.transform((d) => ({
        cliente_id: d.cliente_id,
        fecha_cotizacion: d.fecha_cotizacion,
        fecha_validez: d.fecha_validez,
        prioridad: d.prioridad,
        notas: d.notas.trim() || null,
        productos: lineasDe(d.bloques),
    }));

    // Errores del servidor → el paso donde está ese campo.
    const [salto, setSalto] = useState<{ paso: number; n: number }>();
    const pasoConError = (errores: Record<string, string>) => {
        const k = Object.keys(errores);
        if (k.some((c) => ['cliente_id', 'fecha_cotizacion', 'fecha_validez', 'prioridad'].includes(c))) return 0;
        if (k.some((c) => c.startsWith('productos'))) return 1;
        return 2;
    };
    const errorProductos = Object.entries(e).find(([k]) => k.startsWith('productos'))?.[1];

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

    const titulo = cotizacion ? `Editar cotización #${cotizacion.id}` : 'Nueva cotización';

    return (
        <AppLayout
            titulo={titulo}
            acciones={
                <>
                    <TasaBcv />
                    <Button variant="ghost" asChild>
                        <Link href={urls.index}>
                            <ArrowLeft /> Cotizaciones
                        </Link>
                    </Button>
                </>
            }
        >
            <form id="form-cotizacion" noValidate className="max-w-6xl" onSubmit={guardar}>
                <Card>
                    <CardContent className="grid gap-4">
                        {(cliente || cotizacion?.creador) && (
                            <div className="text-muted-foreground flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                                {cliente && (
                                    <span>
                                        Para: <strong className="text-foreground">{cliente.nombre}</strong>
                                        {cliente.documento && ` · ${cliente.documento}`}
                                    </span>
                                )}
                                {cotizacion?.creador && (
                                    <span className="inline-flex items-center gap-1.5">
                                        {cotizacion.creador.avatar ? (
                                            <img src={cotizacion.creador.avatar} alt="" className="size-5 rounded-full object-cover" />
                                        ) : (
                                            <span className="bg-primary/10 text-primary grid size-5 place-items-center rounded-full text-[0.6rem] font-semibold">
                                                {iniciales(cotizacion.creador.nombre)}
                                            </span>
                                        )}
                                        Creada por {cotizacion.creador.nombre}
                                        {cotizacion.creador.fecha && ` · ${formatoFecha(cotizacion.creador.fecha)}`}
                                    </span>
                                )}
                            </div>
                        )}
                        <Asistente
                            salto={salto}
                            final={
                                <Button type="submit" disabled={form.processing}>
                                    <Save /> {edicion ? 'Guardar cambios' : 'Crear cotización'}
                                </Button>
                            }
                            pasos={[
                                {
                                    titulo: 'Cliente',
                                    descripcion: 'A quién se cotiza, hasta cuándo vale y con qué prioridad pasa a producción.',
                                    validar: () =>
                                        !data.cliente_id
                                            ? 'Elige el cliente.'
                                            : !data.fecha_validez || data.fecha_validez < data.fecha_cotizacion
                                              ? 'La fecha de validez debe ser igual o posterior a la de emisión.'
                                              : null,
                                    contenido: (
                                        <div className="grid gap-4">
                                            <SelectorCliente
                                                cliente={cliente}
                                                fijo={edicion}
                                                error={e.cliente_id}
                                                urls={urls}
                                                estados={props.estadosVe}
                                                onCambiar={(c) => {
                                                    setCliente(c);
                                                    setData('cliente_id', c?.id ?? '');
                                                }}
                                            />
                                            <div className="grid gap-4 sm:grid-cols-2">
                                                <Campo etiqueta="Fecha de emisión" requerido error={e.fecha_cotizacion} ayuda={edicion ? 'La emisión no cambia al editar.' : undefined}>
                                                    <Input
                                                        type="date"
                                                        value={data.fecha_cotizacion}
                                                        readOnly={edicion}
                                                        disabled={edicion}
                                                        max={hoyLocalIso()}
                                                        onChange={(ev) => setData('fecha_cotizacion', ev.target.value)}
                                                    />
                                                </Campo>
                                                <Campo etiqueta="Válida hasta" requerido error={e.fecha_validez} ayuda={`Por defecto, ${diasVigencia} días desde la emisión.`}>
                                                    <Input type="date" value={data.fecha_validez} min={data.fecha_cotizacion} onChange={(ev) => setData('fecha_validez', ev.target.value)} />
                                                </Campo>
                                            </div>
                                            <div className="flex flex-wrap items-center gap-2" role="group" aria-label="Validez rápida">
                                                <CalendarDays className="text-muted-foreground size-4" aria-hidden />
                                                {[0, 15, 30, 60].map((dias) => {
                                                    const valor = sumarDias(data.fecha_cotizacion, dias);
                                                    return (
                                                        <Button
                                                            key={dias}
                                                            type="button"
                                                            size="sm"
                                                            variant={data.fecha_validez === valor ? 'secondary' : 'outline'}
                                                            aria-pressed={data.fecha_validez === valor}
                                                            onClick={() => setData('fecha_validez', valor)}
                                                        >
                                                            {dias === 0 ? 'Hoy' : `+${dias} días`}
                                                        </Button>
                                                    );
                                                })}
                                            </div>
                                            <fieldset className="grid gap-2">
                                                <legend className="mb-1 text-sm font-medium">Prioridad</legend>
                                                <div className="grid gap-2 sm:grid-cols-3">
                                                    {PRIORIDADES.map((p) => (
                                                        <label
                                                            key={p.valor}
                                                            className={cn(
                                                                'flex cursor-pointer items-center gap-2 rounded-md border p-2 text-sm',
                                                                data.prioridad === p.valor && 'border-primary bg-primary/5',
                                                            )}
                                                        >
                                                            <input
                                                                type="radio"
                                                                name="prioridad"
                                                                value={p.valor}
                                                                checked={data.prioridad === p.valor}
                                                                onChange={() => setData('prioridad', p.valor)}
                                                                className="accent-primary"
                                                            />
                                                            <span className="grid">
                                                                <span className="font-medium">{p.valor}</span>
                                                                <span className="text-muted-foreground text-xs">{p.ayuda}</span>
                                                            </span>
                                                        </label>
                                                    ))}
                                                </div>
                                                <p className="text-muted-foreground text-xs">Se hereda al pedido cuando se convierte.</p>
                                            </fieldset>
                                        </div>
                                    ),
                                },
                                {
                                    titulo: 'Productos',
                                    descripcion: 'Variante, color, tallas por género, precio y bordados de cada producto.',
                                    validar: () =>
                                        !data.bloques.length
                                            ? 'Agrega al menos un producto.'
                                            : data.bloques.some((b) => unidades(b) <= 0)
                                              ? 'Cada producto necesita al menos una talla con cantidad.'
                                              : data.bloques.some((b) => precioFinal(b) <= 0)
                                                ? 'Cada producto necesita un precio mayor a cero.'
                                                : null,
                                    contenido: (
                                        <div className="grid gap-4">
                                            {errorProductos && (
                                                <p className="text-destructive text-sm" role="alert">
                                                    {errorProductos}
                                                </p>
                                            )}
                                            <dl className="grid grid-cols-3 gap-3">
                                                <div className="rounded-lg border p-3">
                                                    <dt className="text-muted-foreground text-xs">Productos</dt>
                                                    <dd className="text-lg font-semibold tabular">{data.bloques.length}</dd>
                                                </div>
                                                <div className="rounded-lg border p-3">
                                                    <dt className="text-muted-foreground text-xs">Subtotal</dt>
                                                    <dd>
                                                        <Monto usd={t.subtotal} />
                                                    </dd>
                                                </div>
                                                <div className="rounded-lg border p-3">
                                                    <dt className="text-muted-foreground text-xs">Total con IVA</dt>
                                                    <dd>
                                                        <Monto usd={t.total} />
                                                    </dd>
                                                </div>
                                            </dl>
                                            {data.bloques.length ? (
                                                <TablaProductos
                                                    filas={filas}

                                                    acciones={(f) => {
                                                        const b = porClave(f.clave);
                                                        if (!b) return null;
                                                        return (
                                                            <div className="flex justify-end gap-1">
                                                                <Button
                                                                    type="button"
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    onClick={() => setProducto((p) => ({ bloque: b, apertura: (p?.apertura ?? 0) + 1 }))}
                                                                    aria-label={`Editar ${b.nombre}`}
                                                                >
                                                                    <Pencil />
                                                                </Button>
                                                                <Button type="button" variant="ghost" size="icon" onClick={() => setBordando(b)} aria-label={`Bordados de ${b.nombre}`}>
                                                                    <Scissors />
                                                                </Button>
                                                                <Button
                                                                    type="button"
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    className="text-destructive"
                                                                    onClick={() => setQuitando(b)}
                                                                    aria-label={`Quitar ${b.nombre}`}
                                                                >
                                                                    <Trash2 />
                                                                </Button>
                                                            </div>
                                                        );
                                                    }}
                                                />
                                            ) : (
                                                <div className="text-muted-foreground grid justify-items-center gap-2 rounded-lg border border-dashed p-8 text-center text-sm">
                                                    <p>Aún no hay productos en la cotización.</p>
                                                </div>
                                            )}
                                            <Button type="button" variant="outline" className="justify-self-start" onClick={() => setProducto((p) => ({ apertura: (p?.apertura ?? 0) + 1 }))}>
                                                <PackagePlus /> {data.bloques.length ? 'Agregar otro producto' : 'Abrir catálogo'}
                                            </Button>
                                        </div>
                                    ),
                                },
                                {
                                    titulo: 'Resumen',
                                    descripcion: 'Revisa todo antes de guardar.',
                                    contenido: (
                                        <div className="grid gap-4 lg:grid-cols-[1fr_22rem] lg:items-start">
                                            <div className="grid min-w-0 gap-4">
                                                <TablaProductos filas={filas} />
                                                <ProyeccionInsumos url={urls.proyeccion} lineas={lineasProyeccion} urlCrearCompra={urls.crearCompra} origen="cotizacion" />
                                                <Campo etiqueta="Notas internas" error={e.notas} ayuda="No salen en el PDF.">
                                                    <Textarea rows={3} maxLength={2000} value={data.notas} onChange={(ev) => setData('notas', ev.target.value)} />
                                                </Campo>
                                            </div>
                                            <div className="grid gap-4">
                                                <p className="text-muted-foreground text-sm">
                                                    {cliente?.nombre ?? '—'} · Emitida {formatoFecha(data.fecha_cotizacion)} · Válida hasta {formatoFecha(data.fecha_validez)} · Prioridad{' '}
                                                    {data.prioridad} · {formatoNumero(data.bloques.reduce((s, b) => s + unidades(b), 0))} unidades
                                                </p>
                                                <ResumenTotales subtotal={t.subtotal} iva={t.iva} porcentajeIva={iva} total={t.total} />
                                                <TerminosCondiciones terminos={terminos} />
                                            </div>
                                        </div>
                                    ),
                                },
                            ]}
                        />
                    </CardContent>
                </Card>
            </form>

            {producto && (
                <AgregarProducto
                    key={producto.apertura}
                    abierto
                    onCerrar={() => setProducto(undefined)}
                    bloque={producto.bloque}
                    onAgregar={(b) => reemplazar(b)}
                    catalogo={catalogo}
                    onTelaCreada={(tipoId, tela) => setCatalogo((c) => c.map((x) => (x.id === tipoId ? { ...x, telas: [...x.telas, tela] } : x)))}
                    colores={colores}
                    onColorCreado={(c) => setColores((l) => [...l, c])}
                    tallas={props.tallas}
                    generos={props.generos}
                    urls={urls}
                />
            )}

            {bordando && (
                <ConfigurarBordados
                    bloque={bordando}
                    ubicaciones={props.ubicaciones}
                    logos={logos}
                    onLogoCreado={(l) => setLogos((x) => [...x, l])}
                    maxBordados={props.maxBordados}
                    urls={urls}
                    onCerrar={() => setBordando(undefined)}
                    onGuardar={(bordados) => {
                        reemplazar({ ...bordando, bordados });
                        setBordando(undefined);
                    }}
                />
            )}

            <ConfirmarPeligro
                abierto={Boolean(quitando)}
                onCerrar={() => setQuitando(undefined)}
                titulo={`¿Quitar ${quitando?.nombre ?? 'el producto'}?`}
                descripcion="Se quita de la cotización con sus tallas y bordados."
                accion="Quitar"
                onConfirmar={() => {
                    if (quitando)
                        setData(
                            'bloques',
                            data.bloques.filter((b) => b.id !== quitando.id),
                        );
                    setQuitando(undefined);
                }}
            />
        </AppLayout>
    );
}
