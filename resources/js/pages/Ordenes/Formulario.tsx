import { Link, useForm, usePage } from '@inertiajs/react';
import { AlertTriangle, ArrowLeft, Lock, Plus, Save, Scissors, Search, ShoppingBag, Trash2, X } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { toast } from 'sonner';

import { Campo } from '@/components/app/campo';
import { Asistente } from '@/components/app/asistente';
import { ProyeccionInsumos, comprarFaltantes } from '@/components/app/proyeccion-insumos';
import { Button } from '@/components/ui/button';
import { Card, CardAction, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useGuardCambios } from '@/hooks/use-guard-cambios';
import AppLayout from '@/layouts/app-layout';
import { formatoFecha, formatoNumero, hoyLocalIso } from '@/lib/formato';
import type { FaltanteCompra } from '@/lib/inventario';
import { cn } from '@/lib/utils';

import { EquipoReparto, repartir, type Asignacion } from './equipo-reparto';
import type { LineaDisponible, PaginaFormularioOrdenes, PedidoDisponible } from './tipos';

interface InsumoParte {
    id: number;
    nombre: string;
    unidad: string;
    /** Consumo por unidad de la plantilla del tipo; null si se agregó a mano. */
    por_unidad: number | null;
    cantidad: string;
    /** Editado a mano: ya no se recalcula al cambiar las unidades. */
    manual: boolean;
}

interface Parte {
    clave: string;
    detalle_id: number;
    cantidad: string;
    empleados: Asignacion[];
    repartoManual: boolean;
    fecha_inicio: string;
    fecha_fin_estimada: string;
    notas: string;
    insumos: InsumoParte[];
}

/** Suma días a una fecha "AAAA-MM-DD" en hora local (sin pasar por UTC). */
function sumarDias(iso: string, dias: number): string {
    const [a, m, d] = iso.split('-').map(Number);
    const f = new Date(a!, m! - 1, d! + dias);
    return `${f.getFullYear()}-${String(f.getMonth() + 1).padStart(2, '0')}-${String(f.getDate()).padStart(2, '0')}`;
}

const redondear = (n: number) => Math.round(n * 100) / 100;
const entero = (v: string) => Math.max(0, parseInt(v, 10) || 0);
let secuencia = 0;

function escalarInsumos(insumos: InsumoParte[], cantidad: number): InsumoParte[] {
    return insumos.map((i) => (i.manual || i.por_unidad === null ? i : { ...i, cantidad: String(redondear(i.por_unidad * cantidad)) }));
}

function nuevaParte(linea: LineaDisponible, cantidad: number, pedido: PedidoDisponible, base?: Parte): Parte {
    const inicio = base?.fecha_inicio ?? hoyLocalIso();
    // Fin por defecto: la entrega del pedido si es posterior al inicio; si no, una semana.
    const fin = base?.fecha_fin_estimada ?? (pedido.fecha_entrega && pedido.fecha_entrega > inicio ? pedido.fecha_entrega : sumarDias(inicio, 7));
    return {
        clave: `p${++secuencia}`,
        detalle_id: linea.detalle_id,
        cantidad: String(cantidad),
        empleados: [],
        repartoManual: false,
        fecha_inicio: inicio,
        fecha_fin_estimada: fin,
        notas: '',
        insumos: escalarInsumos(
            linea.insumos.map((i) => ({ id: i.id, nombre: i.nombre, unidad: i.unidad, por_unidad: i.por_unidad, cantidad: '', manual: false })),
            cantidad,
        ),
    };
}

export default function FormularioOrdenes({ pedidos, empleados, insumos, abonoMinimo, urls }: PaginaFormularioOrdenes) {
    const pagina = usePage();
    const faltantes = (pagina.flash as { faltantes?: FaltanteCompra[] }).faltantes;
    const pedidoUrl = new URLSearchParams(typeof window !== 'undefined' ? window.location.search : '').get('pedido');
    const [buscar, setBuscar] = useState('');

    const form = useForm<{ pedido_id: number | ''; ordenes: Parte[] }>({ pedido_id: '', ordenes: [] });
    const { data, setData } = form;
    const e = form.errors as Record<string, string | undefined>;
    useGuardCambios(data.ordenes.length > 0 && !form.processing);

    const pedido = pedidos.find((p) => p.id === data.pedido_id);
    const lineaDe = (id: number) => pedido?.lineas.find((l) => l.detalle_id === id);
    const usadas = (detalleId: number, excepto?: string) => data.ordenes.filter((p) => p.detalle_id === detalleId && p.clave !== excepto).reduce((s, p) => s + entero(p.cantidad), 0);

    const elegirPedido = (p: PedidoDisponible) => {
        if (!p.cumple_abono) return;
        // Por defecto se producen todas las líneas con unidades pendientes, una orden por línea.
        setData({ pedido_id: p.id, ordenes: p.lineas.filter((l) => l.pendiente > 0).map((l) => nuevaParte(l, l.pendiente, p)) });
    };
    // Enlace «Nueva orden de este pedido» (?pedido=ID): se elige al entrar.
    useEffect(() => {
        const p = pedidos.find((x) => String(x.id) === pedidoUrl);
        if (p && p.cumple_abono && data.pedido_id === '') elegirPedido(p);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const cambiarParte = (clave: string, cambio: (p: Parte) => Parte) =>
        setData(
            'ordenes',
            data.ordenes.map((p) => (p.clave === clave ? cambio(p) : p)),
        );

    const alternarLinea = (l: LineaDisponible, producir: boolean) => {
        if (!pedido) return;
        setData('ordenes', producir ? [...data.ordenes, nuevaParte(l, l.pendiente, pedido)] : data.ordenes.filter((p) => p.detalle_id !== l.detalle_id));
    };

    // Dividir: la parte nueva se lleva la mitad de las unidades de esta (otro equipo, otras fechas).
    const dividir = (parte: Parte) => {
        const l = lineaDe(parte.detalle_id);
        const cant = entero(parte.cantidad);
        if (!l || !pedido || cant < 2) return;
        const mitad = Math.floor(cant / 2);
        const resto = cant - mitad;
        const i = data.ordenes.findIndex((p) => p.clave === parte.clave);
        const recortada = conCantidad(parte, resto);
        const nueva = nuevaParte(l, mitad, pedido, parte);
        setData('ordenes', [...data.ordenes.slice(0, i), recortada, nueva, ...data.ordenes.slice(i + 1)]);
    };
    const conCantidad = (p: Parte, cant: number): Parte => ({
        ...p,
        cantidad: String(cant),
        insumos: escalarInsumos(p.insumos, cant),
        empleados: p.repartoManual
            ? p.empleados
            : repartir(
                  cant,
                  p.empleados.map((x) => x.id),
              ),
    });

    // Aplicar las mismas fechas a todas las órdenes.
    const [fechasTodas, setFechasTodas] = useState({ inicio: hoyLocalIso(), fin: '' });

    const requeridos = useMemo(() => {
        const mapa = new Map<number, number>();
        for (const p of data.ordenes) for (const i of p.insumos) mapa.set(i.id, (mapa.get(i.id) ?? 0) + (parseFloat(i.cantidad) || 0));
        return [...mapa].map(([insumo_id, cantidad]) => ({ insumo_id, cantidad: redondear(cantidad) }));
    }, [data.ordenes]);

    form.transform((d) => ({
        pedido_id: d.pedido_id,
        ordenes: d.ordenes.map((p) => ({
            detalle_pedido_id: p.detalle_id,
            cantidad: entero(p.cantidad),
            empleados: p.empleados.map((x) => ({ id: x.id, cantidad: entero(x.cantidad) })),
            fecha_inicio: p.fecha_inicio,
            fecha_fin_estimada: p.fecha_fin_estimada,
            notas: p.notas.trim() || null,
            insumos: p.insumos.map((i) => ({ id: i.id, cantidad_estimada: parseFloat(i.cantidad) || 0 })),
        })),
    }));

    const [salto, setSalto] = useState<{ paso: number; n: number }>();
    // Errores del servidor → el paso donde se corrigen.
    const pasoConError = (errores: Record<string, string>) => {
        const k = Object.keys(errores);
        if (k.some((c) => /^ordenes\.\d+\.(cantidad|empleados|fecha|notas)/.test(c))) return 1;
        if (k.some((c) => /^ordenes\.\d+\.insumos/.test(c))) return 2;
        return 3;
    };
    const guardar = (ev: React.FormEvent) => {
        ev.preventDefault();
        form.post(urls.guardar, {
            preserveScroll: true,
            onError: (errores) => {
                toast.error('Revisa las órdenes marcadas.');
                setSalto((s) => ({ paso: pasoConError(errores), n: (s?.n ?? 0) + 1 }));
            },
        });
    };

    const filtrados = pedidos.filter((p) => {
        const k = buscar.trim().toLowerCase();
        return !k || String(p.id).includes(k.replace('#', '')) || (p.cliente ?? '').toLowerCase().includes(k);
    });
    const nombreEmpleado = (id: number) => empleados.find((x) => x.id === id)?.nombre ?? `#${id}`;

    const validarAsignacion = () => {
        for (const [n, p] of data.ordenes.entries()) {
            const l = lineaDe(p.detalle_id)!;
            const cant = entero(p.cantidad);
            if (cant < 1) return `Orden ${n + 1}: indica las unidades.`;
            if (usadas(p.detalle_id) > l.pendiente) return `«${l.producto}»: se asignan ${usadas(p.detalle_id)} y solo quedan ${l.pendiente}.`;
            if (!p.empleados.length) return `Orden ${n + 1}: asigna al menos un empleado.`;
            if (p.empleados.reduce((s, x) => s + entero(x.cantidad), 0) !== cant) return `Orden ${n + 1}: el reparto del equipo debe sumar ${cant}.`;
            if (!p.fecha_inicio || !p.fecha_fin_estimada || p.fecha_fin_estimada <= p.fecha_inicio) return `Orden ${n + 1}: el fin estimado debe ser posterior al inicio.`;
        }
        return null;
    };
    const validarInsumos = () => {
        for (const [n, p] of data.ordenes.entries()) {
            if (!p.insumos.length) return `Orden ${n + 1}: necesita al menos un insumo.`;
            if (p.insumos.some((i) => !(parseFloat(i.cantidad) > 0))) return `Orden ${n + 1}: cada insumo necesita una cantidad mayor que cero.`;
        }
        return null;
    };
    const textoCrear = `Crear ${data.ordenes.length === 1 ? 'orden' : `${data.ordenes.length} órdenes`}`;
    const encabezadoParte = (p: Parte, n: number) => {
        const l = lineaDe(p.detalle_id)!;
        const grupo = data.ordenes.filter((x) => x.detalle_id === p.detalle_id);
        return (
            <>
                <CardTitle className="text-base">
                    Orden {n + 1}: {l.producto}
                    {grupo.length > 1 && (
                        <span className="text-muted-foreground text-sm font-normal">
                            {' '}
                            · parte {grupo.indexOf(p) + 1} de {grupo.length}
                        </span>
                    )}
                </CardTitle>
                <CardDescription>{[l.variante, `${entero(p.cantidad)} unidades`].filter(Boolean).join(' · ')}</CardDescription>
            </>
        );
    };

    return (
        <AppLayout
            titulo="Nueva orden de producción"
            acciones={
                <Button variant="ghost" asChild>
                    <Link href={urls.index}>
                        <ArrowLeft /> Órdenes
                    </Link>
                </Button>
            }
        >
            <form id="form-ordenes" onSubmit={guardar} noValidate className="grid gap-4">
                {(e.general || e.pedido_id) && (
                    <div className="border-destructive/30 bg-destructive/8 flex flex-wrap items-start gap-3 rounded-md border p-3 text-sm" role="alert">
                        <AlertTriangle className="text-destructive mt-0.5 size-4 shrink-0" />
                        <p className="flex-1">{e.general ?? e.pedido_id}</p>
                        {faltantes && faltantes.length > 0 && (
                            <Button type="button" size="sm" variant="outline" onClick={() => comprarFaltantes(faltantes, 'produccion', urls.crearCompra)}>
                                <ShoppingBag /> Comprar lo que falta
                            </Button>
                        )}
                    </div>
                )}
                <Card>
                    <CardContent>
                        <Asistente
                            salto={salto}
                            final={
                                <Button type="submit" disabled={form.processing || !data.ordenes.length}>
                                    <Save /> {textoCrear}
                                </Button>
                            }
                            pasos={[
                                {
                                    titulo: 'Pedido',
                                    descripcion: pedido ? 'Marca las líneas del pedido que vas a producir.' : 'Busca el pedido para el que vas a generar órdenes.',
                                    validar: () => (!pedido ? 'Elige un pedido.' : !data.ordenes.length ? 'Activa al menos una línea para producir.' : null),
                                    contenido: !pedido ? (
                                        <div className="grid gap-4">
                                            <p className="text-muted-foreground -mt-3 text-sm">
                                                Elige el pedido. Solo aparecen los que tienen líneas por fabricar; se necesita el abono mínimo del {formatoNumero(abonoMinimo)} %.
                                            </p>
                                            <div className="relative max-w-md">
                                                <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2" />
                                                <Input
                                                    type="search"
                                                    value={buscar}
                                                    onChange={(ev) => setBuscar(ev.target.value)}
                                                    placeholder="Buscar por número o cliente…"
                                                    aria-label="Buscar pedido"
                                                    className="pl-8"
                                                />
                                            </div>
                                            {filtrados.length === 0 && (
                                                <p className="text-muted-foreground rounded-lg border border-dashed p-8 text-center text-sm">
                                                    {pedidos.length ? 'Ningún pedido coincide.' : 'No hay pedidos con líneas por fabricar.'}
                                                </p>
                                            )}
                                            <ul className="grid gap-3 md:grid-cols-2">
                                                {filtrados.map((p) => {
                                                    const pendientes = p.lineas.filter((l) => l.pendiente > 0).length;
                                                    const bloqueado = !p.cumple_abono || pendientes === 0;
                                                    return (
                                                        <li key={p.id}>
                                                            <button
                                                                type="button"
                                                                onClick={() => elegirPedido(p)}
                                                                disabled={bloqueado}
                                                                className={cn(
                                                                    'bg-card hover:border-primary grid w-full gap-2 rounded-lg border p-4 text-left transition-colors disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:border-border',
                                                                )}
                                                            >
                                                                <span className="flex items-baseline justify-between gap-2">
                                                                    <span className="font-medium">Pedido #{p.id}</span>
                                                                    <span className="text-muted-foreground text-xs">
                                                                        {p.estado}
                                                                        {p.fecha_entrega && ` · entrega ${formatoFecha(p.fecha_entrega)}`}
                                                                    </span>
                                                                </span>
                                                                <span className="text-sm">
                                                                    {p.cliente ?? '—'} <span className="text-muted-foreground tabular text-xs">{p.cliente_documento}</span>
                                                                </span>
                                                                <span className="text-muted-foreground text-xs">
                                                                    {p.lineas.length} {p.lineas.length === 1 ? 'línea' : 'líneas'} · {pendientes} con unidades por asignar
                                                                </span>
                                                                {!p.cumple_abono && (
                                                                    <span className="text-destructive flex items-center gap-1 text-xs">
                                                                        <Lock className="size-3" /> Abono {formatoNumero(p.porcentaje_abonado)} %: falta llegar al {formatoNumero(abonoMinimo)} % para
                                                                        producir.
                                                                    </span>
                                                                )}
                                                            </button>
                                                        </li>
                                                    );
                                                })}
                                            </ul>
                                        </div>
                                    ) : (
                                        <div className="grid gap-4">
                                            <div className="flex flex-wrap items-center justify-between gap-2">
                                                <p className="text-sm">
                                                    <span className="font-medium">Pedido #{pedido.id}</span> · {pedido.cliente}
                                                    {pedido.fecha_entrega && <span className="text-muted-foreground"> · entrega {formatoFecha(pedido.fecha_entrega)}</span>}
                                                </p>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() =>
                                                        (data.ordenes.length === 0 || window.confirm('¿Cambiar de pedido? Se pierde lo cargado.')) && setData({ pedido_id: '', ordenes: [] })
                                                    }
                                                >
                                                    Cambiar pedido
                                                </Button>
                                            </div>
                                            <Card>
                                                <CardHeader>
                                                    <CardTitle className="text-base">Líneas a producir</CardTitle>
                                                    <CardDescription>Cada línea se puede repartir en varias órdenes, cada una con su equipo.</CardDescription>
                                                </CardHeader>
                                                <CardContent className="grid gap-2">
                                                    {pedido.lineas.map((l) => {
                                                        const activa = data.ordenes.some((p) => p.detalle_id === l.detalle_id);
                                                        const libre = l.pendiente - usadas(l.detalle_id);
                                                        return (
                                                            <div key={l.detalle_id} className="flex flex-wrap items-center gap-3 rounded-md border p-2 text-sm">
                                                                <Switch
                                                                    checked={activa}
                                                                    disabled={l.pendiente === 0}
                                                                    onCheckedChange={(v) => alternarLinea(l, v)}
                                                                    aria-label={`Producir ${l.producto}`}
                                                                />
                                                                <span className="min-w-0 flex-1">
                                                                    <span className="font-medium">{l.producto}</span>
                                                                    {l.variante && <span className="text-muted-foreground"> · {l.variante}</span>}
                                                                    {l.bordados > 0 && (
                                                                        <span className="text-muted-foreground">
                                                                            {' '}
                                                                            · {l.bordados} bordado{l.bordados === 1 ? '' : 's'}
                                                                        </span>
                                                                    )}
                                                                </span>
                                                                <span className={cn('tabular text-xs', libre < 0 ? 'text-destructive' : 'text-muted-foreground')}>
                                                                    {l.pendiente === 0
                                                                        ? `Las ${l.cantidad} ya tienen orden`
                                                                        : activa
                                                                          ? `${usadas(l.detalle_id)} de ${l.pendiente} asignadas`
                                                                          : `${l.pendiente} de ${l.cantidad} por asignar`}
                                                                </span>
                                                            </div>
                                                        );
                                                    })}
                                                </CardContent>
                                            </Card>
                                        </div>
                                    ),
                                },
                                {
                                    titulo: 'Asignación',
                                    descripcion: 'Unidades, equipo y cronograma de cada orden. «Dividir» reparte una línea en otra orden.',
                                    validar: validarAsignacion,
                                    contenido: (
                                        <div className="grid gap-4">
                                            {data.ordenes.length > 1 && (
                                                <div className="bg-muted/40 flex flex-wrap items-end gap-3 rounded-lg border p-3">
                                                    <Campo etiqueta="Inicio (todas)">
                                                        <Input type="date" value={fechasTodas.inicio} onChange={(ev) => setFechasTodas((f) => ({ ...f, inicio: ev.target.value }))} />
                                                    </Campo>
                                                    <Campo etiqueta="Fin estimado (todas)">
                                                        <Input
                                                            type="date"
                                                            value={fechasTodas.fin}
                                                            min={fechasTodas.inicio}
                                                            onChange={(ev) => setFechasTodas((f) => ({ ...f, fin: ev.target.value }))}
                                                        />
                                                    </Campo>
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        disabled={!fechasTodas.inicio || !fechasTodas.fin}
                                                        onClick={() =>
                                                            setData(
                                                                'ordenes',
                                                                data.ordenes.map((p) => ({ ...p, fecha_inicio: fechasTodas.inicio, fecha_fin_estimada: fechasTodas.fin })),
                                                            )
                                                        }
                                                    >
                                                        Aplicar a todas
                                                    </Button>
                                                </div>
                                            )}
                                            {data.ordenes.map((p, n) => {
                                                const l = lineaDe(p.detalle_id)!;
                                                const maximo = l.pendiente - usadas(p.detalle_id, p.clave);
                                                const err = (c: string) => e[`ordenes.${n}.${c}`];
                                                return (
                                                    <Card key={p.clave}>
                                                        <CardHeader>
                                                            {encabezadoParte(p, n)}
                                                            <CardAction className="flex gap-1">
                                                                <Button type="button" variant="ghost" size="sm" disabled={entero(p.cantidad) < 2} onClick={() => dividir(p)}>
                                                                    <Scissors /> Dividir
                                                                </Button>
                                                                <Button
                                                                    type="button"
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    aria-label={`Quitar la orden ${n + 1}`}
                                                                    onClick={() =>
                                                                        setData(
                                                                            'ordenes',
                                                                            data.ordenes.filter((x) => x.clave !== p.clave),
                                                                        )
                                                                    }
                                                                >
                                                                    <Trash2 />
                                                                </Button>
                                                            </CardAction>
                                                        </CardHeader>
                                                        <CardContent className="grid gap-4">
                                                            <div className="grid gap-4 sm:grid-cols-3">
                                                                <Campo etiqueta="Unidades" requerido error={err('cantidad')} ayuda={`Hasta ${maximo} en esta orden`}>
                                                                    <Input
                                                                        type="number"
                                                                        min={1}
                                                                        max={maximo}
                                                                        inputMode="numeric"
                                                                        value={p.cantidad}
                                                                        onChange={(ev) => cambiarParte(p.clave, (x) => conCantidad(x, entero(ev.target.value)))}
                                                                        className="tabular"
                                                                    />
                                                                </Campo>
                                                                <Campo etiqueta="Inicio" requerido error={err('fecha_inicio')}>
                                                                    <Input
                                                                        type="date"
                                                                        value={p.fecha_inicio}
                                                                        onChange={(ev) => cambiarParte(p.clave, (x) => ({ ...x, fecha_inicio: ev.target.value }))}
                                                                    />
                                                                </Campo>
                                                                <Campo etiqueta="Fin estimado" requerido error={err('fecha_fin_estimada')}>
                                                                    <Input
                                                                        type="date"
                                                                        value={p.fecha_fin_estimada}
                                                                        min={p.fecha_inicio}
                                                                        onChange={(ev) => cambiarParte(p.clave, (x) => ({ ...x, fecha_fin_estimada: ev.target.value }))}
                                                                    />
                                                                </Campo>
                                                            </div>
                                                            <EquipoReparto
                                                                empleados={empleados}
                                                                total={entero(p.cantidad)}
                                                                valor={p.empleados}
                                                                error={err('empleados') ?? e[`ordenes.${n}.empleados.0.id`]}
                                                                onCambiar={(asignacion, manual) => cambiarParte(p.clave, (x) => ({ ...x, empleados: asignacion, repartoManual: manual }))}
                                                            />
                                                            <Campo etiqueta="Notas" error={err('notas')}>
                                                                <Input
                                                                    value={p.notas}
                                                                    onChange={(ev) => cambiarParte(p.clave, (x) => ({ ...x, notas: ev.target.value }))}
                                                                    placeholder="Indicaciones para el taller (opcional)"
                                                                />
                                                            </Campo>
                                                        </CardContent>
                                                    </Card>
                                                );
                                            })}
                                        </div>
                                    ),
                                },
                                {
                                    titulo: 'Insumos',
                                    descripcion: 'Vienen del tipo de producto, por unidad. Ajústalos si hace falta: se descuentan del inventario al crear la orden.',
                                    validar: validarInsumos,
                                    contenido: (
                                        <div className="grid gap-4">
                                            {data.ordenes.map((p, n) => {
                                                const err = (c: string) => e[`ordenes.${n}.${c}`];
                                                const libres = insumos.filter((i) => !p.insumos.some((x) => x.id === i.id));
                                                return (
                                                    <Card key={p.clave}>
                                                        <CardHeader>{encabezadoParte(p, n)}</CardHeader>
                                                        <CardContent className="grid gap-2">
                                                            {p.insumos.map((i, j) => (
                                                                <div key={i.id} className="flex flex-wrap items-center gap-2 text-sm">
                                                                    <span className="min-w-0 flex-1">
                                                                        {i.nombre}
                                                                        {i.por_unidad !== null && !i.manual && (
                                                                            <span className="text-muted-foreground text-xs">
                                                                                {' '}
                                                                                · {formatoNumero(i.por_unidad)} {i.unidad} por unidad
                                                                            </span>
                                                                        )}
                                                                    </span>
                                                                    <Input
                                                                        type="number"
                                                                        min={0.01}
                                                                        step="0.01"
                                                                        inputMode="decimal"
                                                                        value={i.cantidad}
                                                                        onChange={(ev) =>
                                                                            cambiarParte(p.clave, (x) => ({
                                                                                ...x,
                                                                                insumos: x.insumos.map((y, k) => (k === j ? { ...y, cantidad: ev.target.value, manual: true } : y)),
                                                                            }))
                                                                        }
                                                                        aria-label={`Cantidad de ${i.nombre} en la orden ${n + 1}`}
                                                                        aria-invalid={err(`insumos.${j}.cantidad_estimada`) ? true : undefined}
                                                                        className="tabular w-28"
                                                                    />
                                                                    <span className="text-muted-foreground w-14 text-xs">{i.unidad}</span>
                                                                    <Button
                                                                        type="button"
                                                                        variant="ghost"
                                                                        size="icon"
                                                                        aria-label={`Quitar ${i.nombre}`}
                                                                        onClick={() => cambiarParte(p.clave, (x) => ({ ...x, insumos: x.insumos.filter((_, k) => k !== j) }))}
                                                                    >
                                                                        <X />
                                                                    </Button>
                                                                </div>
                                                            ))}
                                                            {err('insumos') && <p className="text-destructive text-xs">{err('insumos')}</p>}
                                                            {libres.length > 0 && (
                                                                <Select
                                                                    value=""
                                                                    onValueChange={(v) => {
                                                                        const ins = insumos.find((i) => String(i.id) === v);
                                                                        if (ins)
                                                                            cambiarParte(p.clave, (x) => ({
                                                                                ...x,
                                                                                insumos: [
                                                                                    ...x.insumos,
                                                                                    { id: ins.id, nombre: ins.nombre, unidad: ins.unidad, por_unidad: null, cantidad: '', manual: true },
                                                                                ],
                                                                            }));
                                                                    }}
                                                                >
                                                                    <SelectTrigger className="w-64" aria-label={`Agregar insumo a la orden ${n + 1}`}>
                                                                        <Plus className="size-4" />
                                                                        <SelectValue placeholder="Agregar insumo…" />
                                                                    </SelectTrigger>
                                                                    <SelectContent>
                                                                        {libres.map((i) => (
                                                                            <SelectItem key={i.id} value={String(i.id)}>
                                                                                {i.nombre} · hay {formatoNumero(i.stock)} {i.unidad}
                                                                            </SelectItem>
                                                                        ))}
                                                                    </SelectContent>
                                                                </Select>
                                                            )}
                                                        </CardContent>
                                                    </Card>
                                                );
                                            })}
                                        </div>
                                    ),
                                },
                                {
                                    titulo: 'Resumen',
                                    descripcion: 'Revisa las órdenes y la existencia de insumos antes de crearlas.',
                                    contenido: (
                                        <div className="grid gap-4">
                                            <div className="overflow-x-auto rounded-lg border">
                                                <Table>
                                                    <TableHeader>
                                                        <TableRow className="hover:bg-transparent">
                                                            <TableHead>Orden</TableHead>
                                                            <TableHead className="text-right">Unidades</TableHead>
                                                            <TableHead>Equipo</TableHead>
                                                            <TableHead>Cronograma</TableHead>
                                                            <TableHead className="text-right">Insumos</TableHead>
                                                        </TableRow>
                                                    </TableHeader>
                                                    <TableBody>
                                                        {data.ordenes.map((p, n) => (
                                                            <TableRow key={p.clave}>
                                                                <TableCell>
                                                                    <span className="text-muted-foreground tabular">{n + 1}.</span> {lineaDe(p.detalle_id)?.producto}
                                                                </TableCell>
                                                                <TableCell className="text-right tabular">{entero(p.cantidad)}</TableCell>
                                                                <TableCell className="text-sm">{p.empleados.map((x) => `${nombreEmpleado(x.id)} (${entero(x.cantidad)})`).join(', ')}</TableCell>
                                                                <TableCell className="tabular text-sm">
                                                                    {p.fecha_inicio ? formatoFecha(p.fecha_inicio) : '—'} → {p.fecha_fin_estimada ? formatoFecha(p.fecha_fin_estimada) : '—'}
                                                                </TableCell>
                                                                <TableCell className="text-right tabular">{p.insumos.length}</TableCell>
                                                            </TableRow>
                                                        ))}
                                                    </TableBody>
                                                </Table>
                                            </div>
                                            <ProyeccionInsumos url={urls.proyeccion} requeridos={requeridos} urlCrearCompra={urls.crearCompra} origen="produccion" />
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
