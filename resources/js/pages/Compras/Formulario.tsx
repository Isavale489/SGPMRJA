import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, PackagePlus, Plus, Save, Trash2 } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { toast } from 'sonner';

import { Asistente } from '@/components/app/asistente';
import { Buscador } from '@/components/app/buscador';
import { Campo } from '@/components/app/campo';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { useGuardCambios } from '@/hooks/use-guard-cambios';
import { usePermisos } from '@/hooks/use-permisos';
import AppLayout from '@/layouts/app-layout';
import { formatoBs, formatoFecha, formatoNumero, formatoUsd, hoyLocalIso } from '@/lib/formato';
import { tomarFaltantes } from '@/lib/inventario';
import { cn } from '@/lib/utils';
import { FormularioInsumo } from '@/pages/Insumos/formulario-insumo';
import type { InsumoCreado } from '@/pages/Insumos/tipos';
import type { ProveedorResumen } from '@/pages/Proveedores/tipos';

import { SelectorProveedor } from './selector-proveedor';
import type { InsumoComprable, PaginaFormularioCompra } from './tipos';

interface Linea {
    insumo_id: number;
    cantidad: string;
    costo_unitario_bs: string;
    aplica_iva: boolean;
    /** El costo lo sugirió el sistema ($ × tasa): se recalcula si cambia la tasa, hasta que el usuario lo edite. */
    sugerido?: boolean;
}

interface Datos {
    proveedor_id: number | '';
    numero_factura: string;
    fecha_compra: string;
    tasa_cambio: string;
    observaciones: string;
    items: Linea[];
}

/** Respuesta de compras.tasa (CompraController::getTasa). */
interface RespuestaTasa {
    encontrada: boolean;
    exacta?: boolean;
    valor?: number;
    fecha_bcv?: string;
    message?: string;
}

const num = (v: string) => {
    const n = parseFloat(v);
    return Number.isFinite(n) ? n : 0;
};
/** Redondeo a 2 decimales como PHP round(): la mitad se aleja del cero y 1.005 da 1.01 (no 1.00 por el binario). */
const redondear = (n: number) => {
    const x = Math.abs(n) * 100;
    return (Math.sign(n) * Math.round(x + x * Number.EPSILON * 4)) / 100;
};

/**
 * Mismo cálculo que CompraService: el costo en $ de cada línea es el de Bs
 * dividido por la tasa (redondeado a 2); el IVA grava solo las líneas marcadas.
 */
function totales(items: Linea[], tasa: number, iva: number) {
    let subUsd = 0,
        gravUsd = 0,
        subBs = 0,
        gravBs = 0;
    for (const l of items) {
        const cant = num(l.cantidad);
        const bs = num(l.costo_unitario_bs);
        const usd = tasa > 0 ? redondear(bs / tasa) : 0;
        subUsd += cant * usd;
        subBs += cant * bs;
        if (l.aplica_iva) {
            gravUsd += cant * usd;
            gravBs += cant * bs;
        }
    }
    const ivaUsd = redondear((gravUsd * iva) / 100);
    const ivaBs = redondear((gravBs * iva) / 100);
    return { subUsd: redondear(subUsd), ivaUsd, totalUsd: redondear(subUsd + ivaUsd), subBs: redondear(subBs), ivaBs, totalBs: redondear(subBs + ivaBs) };
}

export default function FormularioCompra({ compra, insumos, iva, tiposInsumo, unidades, estados, urls }: PaginaFormularioCompra) {
    const { puede } = usePermisos();
    const [proveedor, setProveedor] = useState<ProveedorResumen | null>(compra?.proveedor ?? null);
    // Guardada: «anterior» si la tasa BCV es de un día previo a la compra (fin de semana, feriado).
    const tasaGuardada = compra
        ? ({ estado: !compra.tasa_fecha ? 'manual' : compra.tasa_fecha === compra.fecha_compra ? 'bcv' : 'anterior', fecha: compra.tasa_fecha ?? undefined } as const)
        : null;
    const [tasa, setTasa] = useState<{ estado: 'buscando' | 'bcv' | 'anterior' | 'manual'; fecha?: string; aviso?: string }>(tasaGuardada ?? { estado: 'buscando' });
    const [altaInsumo, setAltaInsumo] = useState<{ abierto: boolean; apertura: number; nombre: string }>({ abierto: false, apertura: 0, nombre: '' });

    const form = useForm<Datos>({
        proveedor_id: compra?.proveedor?.id ?? '',
        numero_factura: compra?.numero_factura ?? '',
        fecha_compra: compra?.fecha_compra ?? hoyLocalIso(),
        tasa_cambio: compra ? String(compra.tasa_cambio) : '',
        observaciones: compra?.observaciones ?? '',
        items: compra?.items.map((i) => ({ insumo_id: i.insumo_id, cantidad: String(i.cantidad), costo_unitario_bs: String(i.costo_unitario_bs), aplica_iva: i.aplica_iva })) ?? [],
    });
    const { data, setData, errors } = form;
    const e = errors as Record<string, string | undefined>;
    useGuardCambios(form.isDirty && !form.processing);

    const porId = useMemo(() => new Map(insumos.map((i) => [i.id, i])), [insumos]);
    const tasaNum = num(data.tasa_cambio);
    const t = totales(data.items, tasaNum, iva);

    // Tasa BCV vigente para la fecha de compra. Al editar, con la fecha guardada
    // se usa la tasa guardada (comparar la fecha, y no «primera vez», aguanta el
    // doble montaje de StrictMode). Si el usuario escribe la tasa mientras se
    // consulta, lo que escribió manda.
    const tecleada = useRef(false);
    useEffect(() => {
        tecleada.current = false;
        if (compra && data.fecha_compra === compra.fecha_compra) {
            setData('tasa_cambio', String(compra.tasa_cambio));
            setTasa(tasaGuardada!);
            return;
        }
        if (!data.fecha_compra) return;
        let vigente = true;
        setTasa({ estado: 'buscando' });
        fetch(`${urls.tasa}?${new URLSearchParams({ fecha: data.fecha_compra })}`, { headers: { Accept: 'application/json' } })
            .then((r) => r.json() as Promise<RespuestaTasa>)
            .then((r) => {
                if (!vigente || tecleada.current) return;
                if (r.encontrada && r.valor) {
                    setData('tasa_cambio', String(r.valor));
                    setTasa({ estado: r.exacta ? 'bcv' : 'anterior', fecha: r.fecha_bcv });
                } else {
                    setTasa({ estado: 'manual', aviso: r.message });
                }
            })
            .catch(() => vigente && !tecleada.current && setTasa({ estado: 'manual', aviso: 'No se pudo consultar la tasa BCV. Ingrésala manualmente.' }));
        return () => {
            vigente = false;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [data.fecha_compra]);

    // Compra prellenada con los faltantes de una cotización, pedido u orden (?prefill=1).
    // Se consumen una sola vez: recargar con ?prefill=1 ya no los vuelve a aplicar.
    const costoBs = (i: InsumoComprable | InsumoCreado, tasaActual = tasaNum) => (tasaActual > 0 && i.costo > 0 ? String(redondear(i.costo * tasaActual)) : '');
    useEffect(() => {
        if (compra || new URLSearchParams(window.location.search).get('prefill') !== '1') return;
        const todos = tomarFaltantes();
        const faltantes = todos.filter((f) => porId.has(Number(f.insumo_id)));
        const fuera = todos.filter((f) => !porId.has(Number(f.insumo_id)));
        if (!faltantes.length) {
            if (fuera.length) toast.warning(`No se agregaron (inhabilitados o no inventariables): ${fuera.map((f) => f.nombre).join(', ')}.`, { duration: 10000 });
            return;
        }
        setData(
            'items',
            faltantes.map((f) => {
                const i = porId.get(Number(f.insumo_id))!;
                // Hacia arriba a 2 decimales, sin que el binario sume 0,01 de más (1,1 → 1,10, no 1,11).
                return { insumo_id: i.id, cantidad: String(Math.ceil(f.cantidad * 100 - 1e-9) / 100), costo_unitario_bs: '', aplica_iva: i.aplica_iva, sugerido: true };
            }),
        );
        toast.info(`Se cargaron ${faltantes.length} ${faltantes.length === 1 ? 'insumo faltante' : 'insumos faltantes'}. Revisa cantidades y costos.`);
        if (fuera.length) toast.warning(`No se agregaron (inhabilitados o no inventariables): ${fuera.map((f) => f.nombre).join(', ')}.`, { duration: 10000 });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    // Costos sugeridos (último costo $ × tasa): siguen a la tasa mientras el usuario
    // no los toque, así una tasa escrita a medias no deja costos congelados.
    useEffect(() => {
        if (tasaNum <= 0 || !data.items.some((l) => l.sugerido || !l.costo_unitario_bs)) return;
        setData(
            'items',
            data.items.map((l) => (l.sugerido || !l.costo_unitario_bs ? { ...l, costo_unitario_bs: costoBs(porId.get(l.insumo_id) ?? ({ costo: 0 } as InsumoComprable)), sugerido: true } : l)),
        );
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [tasaNum]);

    const agregar = (i: InsumoComprable | InsumoCreado) => {
        if (data.items.some((l) => l.insumo_id === i.id)) return toast.info(`${i.nombre} ya está en la compra.`);
        setData('items', [...data.items, { insumo_id: i.id, cantidad: '', costo_unitario_bs: costoBs(i), aplica_iva: i.aplica_iva, sugerido: true }]);
        setEnfocar(i.id);
    };
    // Foco en la cantidad de la línea nueva, cuando ya está en pantalla.
    const [enfocar, setEnfocar] = useState<number>();
    useEffect(() => {
        const input = enfocar ? document.getElementById(`linea-${enfocar}-cantidad`) : null;
        if (input) {
            input.focus();
            setEnfocar(undefined);
        }
    }, [enfocar, data.items]);
    const cambiarLinea = (indice: number, cambios: Partial<Linea>) =>
        setData(
            'items',
            data.items.map((l, n) => (n === indice ? { ...l, ...cambios } : l)),
        );
    const quitar = (indice: number) =>
        setData(
            'items',
            data.items.filter((_, n) => n !== indice),
        );

    const elegirProveedor = (p: ProveedorResumen | null) => {
        setProveedor(p);
        setData('proveedor_id', p?.id ?? '');
    };

    const disponibles = (q: string) => {
        const k = q.toLowerCase();
        return insumos
            .filter((i) => !data.items.some((l) => l.insumo_id === i.id))
            .filter((i) => !k || i.nombre.toLowerCase().includes(k) || (i.codigo ?? '').toLowerCase().startsWith(k))
            .slice(0, 30);
    };

    form.transform((d) => ({
        ...d,
        numero_factura: d.numero_factura.trim() || null,
        observaciones: d.observaciones.trim() || null,
        tasa_cambio: num(d.tasa_cambio),
        items: d.items.map((l) => ({ insumo_id: l.insumo_id, cantidad: num(l.cantidad), costo_unitario_bs: num(l.costo_unitario_bs), aplica_iva: l.aplica_iva ? 1 : 0 })),
    }));

    // Errores del servidor → el paso donde está ese campo.
    const [salto, setSalto] = useState<{ paso: number; n: number }>();
    const pasoConError = (errores: Record<string, string>) => {
        const k = Object.keys(errores);
        if (k.some((c) => ['proveedor_id', 'numero_factura', 'fecha_compra', 'tasa_cambio'].includes(c))) return 0;
        if (k.some((c) => c.startsWith('items'))) return 1;
        return 2;
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
        if (compra) form.put(urls.guardar, opciones);
        else form.post(urls.guardar, opciones);
    };

    const titulo = compra ? `Editar borrador #${compra.id}` : 'Nueva compra';
    const botonNuevoInsumo = puede('insumos.gestionar') && (
        <Button type="button" variant="outline" size="sm" className="justify-self-start" onClick={() => setAltaInsumo((a) => ({ abierto: true, apertura: a.apertura + 1, nombre: '' }))}>
            <PackagePlus /> Nuevo insumo
        </Button>
    );

    return (
        <AppLayout
            titulo={titulo}
            acciones={
                <Button variant="ghost" asChild>
                    <Link href={urls.index}>
                        <ArrowLeft /> Compras
                    </Link>
                </Button>
            }
        >
            <p className="text-muted-foreground -mt-3 mb-4 text-sm">Se guarda como borrador: el inventario no cambia hasta que la compra se procese.</p>
            <form id="form-compra" onSubmit={guardar} noValidate>
                <Card>
                    <CardContent>
                        <Asistente
                            salto={salto}
                            final={
                                <Button type="submit" disabled={form.processing}>
                                    <Save /> {compra ? 'Guardar cambios' : 'Guardar borrador'}
                                </Button>
                            }
                            pasos={[
                                {
                                    titulo: 'Proveedor',
                                    descripcion: 'Elige el proveedor y registra los datos del comprobante.',
                                    validar: () =>
                                        !data.proveedor_id
                                            ? 'Elige el proveedor.'
                                            : !data.fecha_compra
                                              ? 'Indica la fecha de compra.'
                                              : tasaNum <= 0
                                                ? 'Indica la tasa de la compra (Bs por $).'
                                                : null,
                                    contenido: (
                                        <div className="grid gap-4">
                                            <SelectorProveedor proveedor={proveedor} onCambiar={elegirProveedor} error={e.proveedor_id} urls={urls} estados={estados} />
                                            <div className="grid gap-4 sm:grid-cols-3">
                                                <Campo etiqueta="N° de factura" error={e.numero_factura} ayuda="Dígitos y guiones. Opcional en el borrador.">
                                                    <Input
                                                        value={data.numero_factura}
                                                        maxLength={10}
                                                        inputMode="numeric"
                                                        placeholder="0001-0456"
                                                        onChange={(ev) => setData('numero_factura', ev.target.value.replace(/[^0-9-]/g, ''))}
                                                        className="tabular"
                                                    />
                                                </Campo>
                                                <Campo etiqueta="Fecha de compra" requerido error={e.fecha_compra}>
                                                    <Input type="date" value={data.fecha_compra} max={hoyLocalIso()} onChange={(ev) => setData('fecha_compra', ev.target.value)} />
                                                </Campo>
                                                <Campo
                                                    etiqueta="Tasa (Bs por $)"
                                                    requerido
                                                    error={e.tasa_cambio ?? (tasa.estado === 'manual' ? tasa.aviso : undefined)}
                                                    ayuda={
                                                        tasa.estado === 'buscando'
                                                            ? 'Consultando tasa BCV…'
                                                            : tasa.fecha
                                                              ? `Tasa BCV (${formatoFecha(tasa.fecha)})${tasa.estado === 'anterior' ? ': la última publicada antes de esa fecha' : ''}`
                                                              : 'Tasa escrita a mano (sin fecha BCV)'
                                                    }
                                                >
                                                    <Input
                                                        type="number"
                                                        min={0.0001}
                                                        step="0.0001"
                                                        inputMode="decimal"
                                                        value={data.tasa_cambio}
                                                        onChange={(ev) => {
                                                            tecleada.current = true;
                                                            setData('tasa_cambio', ev.target.value);
                                                            setTasa({ estado: 'manual' });
                                                        }}
                                                        className="tabular"
                                                    />
                                                </Campo>
                                            </div>
                                        </div>
                                    ),
                                },
                                {
                                    titulo: 'Ítems',
                                    descripcion: 'Insumos de la factura: cantidad, costo en Bs e IVA de cada uno.',
                                    validar: () =>
                                        !data.items.length
                                            ? 'Agrega al menos un insumo.'
                                            : data.items.some((l) => num(l.cantidad) <= 0 || num(l.costo_unitario_bs) <= 0)
                                              ? 'Cada insumo necesita cantidad y costo mayores que cero.'
                                              : null,
                                    contenido: (
                                        <div className="grid gap-3">
                                            {botonNuevoInsumo}
                                            <Buscador<InsumoComprable>
                                                etiqueta="Agregar insumo"
                                                placeholder="Agregar insumo por nombre o código…"
                                                buscarVacio
                                                buscar={disponibles}
                                                clave={(i) => i.id}
                                                opcion={(i) => (
                                                    <span className="flex items-baseline justify-between gap-3">
                                                        <span className="truncate">
                                                            <span className="font-medium">{i.nombre}</span>
                                                            {i.codigo && <code className="text-muted-foreground ml-1.5 font-mono text-xs">{i.codigo}</code>}
                                                        </span>
                                                        <span className="text-muted-foreground shrink-0 text-xs tabular">
                                                            Hay {formatoNumero(i.stock)} {i.unidad}
                                                        </span>
                                                    </span>
                                                )}
                                                onElegir={agregar}
                                                vacio={(q) =>
                                                    puede('insumos.gestionar') && q ? (
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="sm"
                                                            onMouseDown={(ev) => ev.preventDefault()}
                                                            onClick={() => setAltaInsumo((a) => ({ abierto: true, apertura: a.apertura + 1, nombre: q }))}
                                                        >
                                                            <Plus /> Crear el insumo «{q}»
                                                        </Button>
                                                    ) : (
                                                        'Ningún insumo inventariable coincide.'
                                                    )
                                                }
                                            />
                                            {e.items && <p className="text-destructive text-sm">{e.items}</p>}

                                            {data.items.length === 0 ? (
                                                <p className="text-muted-foreground rounded-lg border border-dashed p-6 text-center text-sm">
                                                    Agrega los insumos de la factura con su cantidad y costo.
                                                </p>
                                            ) : (
                                                <div className="overflow-x-auto rounded-lg border">
                                                    <Table>
                                                        <TableHeader>
                                                            <TableRow className="hover:bg-transparent">
                                                                <TableHead>Insumo</TableHead>
                                                                <TableHead className="w-32">Cantidad</TableHead>
                                                                <TableHead className="w-40">Costo unitario (Bs)</TableHead>
                                                                <TableHead className="w-16 text-center">IVA</TableHead>
                                                                <TableHead className="text-right">Subtotal</TableHead>
                                                                <TableHead className="w-10">
                                                                    <span className="sr-only">Quitar</span>
                                                                </TableHead>
                                                            </TableRow>
                                                        </TableHeader>
                                                        <TableBody>
                                                            {data.items.map((l, n) => {
                                                                const i = porId.get(l.insumo_id);
                                                                const nombre = i?.nombre ?? `Insumo #${l.insumo_id}`;
                                                                const errorDe = (c: string) => e[`items.${n}.${c}`];
                                                                const bs = num(l.costo_unitario_bs);
                                                                const subBs = num(l.cantidad) * bs;
                                                                return (
                                                                    <TableRow key={l.insumo_id} className="align-top">
                                                                        <TableCell>
                                                                            <span className="font-medium">{nombre}</span>
                                                                            {i?.codigo && <code className="text-muted-foreground ml-1.5 font-mono text-xs">{i.codigo}</code>}
                                                                            <span className="text-muted-foreground block text-xs">
                                                                                {i ? `Hay ${formatoNumero(i.stock)} ${i.unidad}` : 'No disponible para compras'}
                                                                            </span>
                                                                            {errorDe('insumo_id') && <span className="text-destructive block text-xs">{errorDe('insumo_id')}</span>}
                                                                        </TableCell>
                                                                        <TableCell>
                                                                            <Input
                                                                                id={`linea-${l.insumo_id}-cantidad`}
                                                                                type="number"
                                                                                min={0.01}
                                                                                step="0.01"
                                                                                inputMode="decimal"
                                                                                value={l.cantidad}
                                                                                onChange={(ev) => cambiarLinea(n, { cantidad: ev.target.value })}
                                                                                aria-label={`Cantidad de ${nombre}`}
                                                                                aria-invalid={errorDe('cantidad') ? true : undefined}
                                                                                className="tabular"
                                                                            />
                                                                            {errorDe('cantidad') && <span className="text-destructive mt-1 block text-xs">{errorDe('cantidad')}</span>}
                                                                        </TableCell>
                                                                        <TableCell>
                                                                            <Input
                                                                                type="number"
                                                                                min={0.01}
                                                                                step="0.01"
                                                                                inputMode="decimal"
                                                                                value={l.costo_unitario_bs}
                                                                                onChange={(ev) => cambiarLinea(n, { costo_unitario_bs: ev.target.value, sugerido: false })}
                                                                                aria-label={`Costo unitario en bolívares de ${nombre}`}
                                                                                aria-invalid={errorDe('costo_unitario_bs') ? true : undefined}
                                                                                className="tabular"
                                                                            />
                                                                            {errorDe('costo_unitario_bs') ? (
                                                                                <span className="text-destructive mt-1 block text-xs">{errorDe('costo_unitario_bs')}</span>
                                                                            ) : (
                                                                                tasaNum > 0 &&
                                                                                bs > 0 && (
                                                                                    <span className="text-muted-foreground mt-1 block text-xs tabular">{formatoUsd(redondear(bs / tasaNum))}</span>
                                                                                )
                                                                            )}
                                                                        </TableCell>
                                                                        <TableCell className="text-center">
                                                                            <Switch
                                                                                checked={l.aplica_iva}
                                                                                onCheckedChange={(v) => cambiarLinea(n, { aplica_iva: v })}
                                                                                aria-label={`${nombre} paga IVA`}
                                                                                className="mt-2"
                                                                            />
                                                                        </TableCell>
                                                                        <TableCell className="text-right tabular">
                                                                            <span className="mt-2 block">{formatoBs(subBs)}</span>
                                                                            {tasaNum > 0 && (
                                                                                <span className="text-muted-foreground block text-xs">{formatoUsd(num(l.cantidad) * redondear(bs / tasaNum))}</span>
                                                                            )}
                                                                        </TableCell>
                                                                        <TableCell>
                                                                            <Button type="button" variant="ghost" size="icon" onClick={() => quitar(n)} aria-label={`Quitar ${nombre}`}>
                                                                                <Trash2 />
                                                                            </Button>
                                                                        </TableCell>
                                                                    </TableRow>
                                                                );
                                                            })}
                                                        </TableBody>
                                                    </Table>
                                                </div>
                                            )}
                                        </div>
                                    ),
                                },
                                {
                                    titulo: 'Resumen',
                                    descripcion: 'Revisa el desglose y agrega notas antes de guardar.',
                                    contenido: (
                                        <div className="grid gap-4 lg:grid-cols-[1fr_20rem] lg:items-start">
                                            <div className="grid min-w-0 gap-4">
                                                <div className="grid gap-1 text-sm">
                                                    <p className="font-medium">
                                                        {proveedor?.nombre ?? '—'} <span className="text-muted-foreground font-normal tabular">{proveedor?.doc}</span>
                                                    </p>
                                                    <p className="text-muted-foreground">
                                                        Factura {data.numero_factura || 'S/N'} · {data.fecha_compra ? formatoFecha(data.fecha_compra) : '—'} · {tasa.fecha ? `Tasa BCV (${formatoFecha(tasa.fecha)})` : 'Tasa manual'}: Bs {formatoNumero(tasaNum)}
                                                    </p>
                                                </div>
                                                <ul className="grid gap-1 rounded-lg border p-3 text-sm">
                                                    {data.items.map((l) => {
                                                        const i = porId.get(l.insumo_id);
                                                        return (
                                                            <li key={l.insumo_id} className="flex flex-wrap justify-between gap-x-3">
                                                                <span>
                                                                    {i?.nombre ?? `Insumo #${l.insumo_id}`}{' '}
                                                                    <span className="text-muted-foreground text-xs">
                                                                        × {formatoNumero(num(l.cantidad))} {i?.unidad}
                                                                        {!l.aplica_iva && ' · exento'}
                                                                    </span>
                                                                </span>
                                                                <span className="tabular">{formatoBs(num(l.cantidad) * num(l.costo_unitario_bs))}</span>
                                                            </li>
                                                        );
                                                    })}
                                                </ul>
                                                <Campo etiqueta="Observaciones" error={e.observaciones}>
                                                    <Textarea
                                                        rows={3}
                                                        maxLength={500}
                                                        value={data.observaciones}
                                                        onChange={(ev) => setData('observaciones', ev.target.value)}
                                                        placeholder="Condiciones, entrega, referencias… (opcional)"
                                                    />
                                                </Campo>
                                            </div>
                                            <div className="bg-muted/40 grid gap-3 rounded-lg border p-4 text-sm">
                                                <Total
                                                    etiqueta={`Subtotal (${data.items.length} ${data.items.length === 1 ? 'insumo' : 'insumos'})`}
                                                    bs={t.subBs}
                                                    usd={tasaNum > 0 ? t.subUsd : null}
                                                />
                                                <Total etiqueta={`IVA (${formatoNumero(iva)} %)`} bs={t.ivaBs} usd={tasaNum > 0 ? t.ivaUsd : null} />
                                                <div className="border-t pt-3">
                                                    <Total etiqueta="Total" bs={t.totalBs} usd={tasaNum > 0 ? t.totalUsd : null} fuerte />
                                                </div>
                                                <p className="text-muted-foreground text-xs">El IVA grava solo los insumos marcados. El costo en $ actualiza el costo de cada insumo al procesar.</p>
                                            </div>
                                        </div>
                                    ),
                                },
                            ]}
                        />
                    </CardContent>
                </Card>
            </form>

            {puede('insumos.gestionar') && (
                <FormularioInsumo
                    key={altaInsumo.apertura}
                    abierto={altaInsumo.abierto}
                    onCerrar={() => setAltaInsumo((a) => ({ ...a, abierto: false }))}
                    tipos={tiposInsumo}
                    unidades={unidades}
                    urls={{ index: urls.insumos, checkNombre: urls.checkNombre }}
                    nombreInicial={altaInsumo.nombre}
                    recargar={['insumos']}
                    paraCompra
                    onCreado={(i) => (i.inventariable ? agregar(i) : toast.warning(`${i.nombre} no es inventariable: no se puede comprar.`))}
                />
            )}
        </AppLayout>
    );
}

function Total({ etiqueta, bs, usd, fuerte }: { etiqueta: string; bs: number; usd: number | null; fuerte?: boolean }) {
    return (
        <div className="flex items-baseline justify-between gap-3">
            <span className={cn(fuerte ? 'font-semibold' : 'text-muted-foreground')}>{etiqueta}</span>
            <span className="text-right tabular">
                <span className={cn('block', fuerte && 'text-base font-semibold')}>{formatoBs(bs)}</span>
                {usd !== null && <span className="text-muted-foreground block text-xs">{formatoUsd(usd)}</span>}
            </span>
        </div>
    );
}
