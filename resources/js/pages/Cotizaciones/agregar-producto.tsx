import { Check, Loader2, Minus, Palette, Plus, RotateCcw, Search, Shirt, SplitSquareHorizontal } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { toast } from 'sonner';

import { Asistente } from '@/components/app/asistente';
import { Campo } from '@/components/app/campo';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { usePermisos } from '@/hooks/use-permisos';
import { formatoBs, formatoNumero, formatoUsd } from '@/lib/formato';
import { cn } from '@/lib/utils';
import { usePage } from '@inertiajs/react';

import { nuevoId } from './calculos';
import { Muestra } from './piezas';
import type { Bloque, ColorCatalogo, PaginaFormularioCotizacion, TallaCatalogo, TipoCatalogo, VarianteResuelta } from './tipos';

const csrf = () => document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
const num = (v: string) => {
    const n = parseFloat(v);
    return Number.isFinite(n) ? n : 0;
};
const claveCelda = (talla: number, genero: number) => `${talla}-${genero}`;

/** POST JSON a un endpoint que responde JSON (clientes jQuery): 422 → errores por campo. */
export async function postJson<T>(url: string, datos: Record<string, unknown>): Promise<{ ok: true; datos: T } | { ok: false; errores: Record<string, string>; mensaje: string }> {
    const r = await fetch(url, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() }, body: JSON.stringify(datos) });
    const cuerpo = (await r.json().catch(() => ({}))) as T & { message?: string; errors?: Record<string, string[]> };
    if (r.ok) return { ok: true, datos: cuerpo };
    const errores = Object.fromEntries(Object.entries(cuerpo.errors ?? {}).map(([k, v]) => [k, v[0] ?? '']));
    return { ok: false, errores, mensaje: cuerpo.message ?? 'No se pudo guardar.' };
}

interface Props {
    abierto: boolean;
    onCerrar: () => void;
    /** Bloque que se edita (sin él, es un producto nuevo). */
    bloque?: Bloque;
    onAgregar: (bloque: Bloque) => void;
    catalogo: TipoCatalogo[];
    onTelaCreada: (tipoId: number, tela: TipoCatalogo['telas'][number]) => void;
    colores: ColorCatalogo[];
    onColorCreado: (color: ColorCatalogo) => void;
    tallas: TallaCatalogo[];
    generos: { id: number; nombre: string }[];
    urls: PaginaFormularioCotizacion['urls'];
}

type Orden = 'relevancia' | 'precio-asc' | 'precio-desc' | 'nombre';

/**
 * Agregar (o editar) un producto de la cotización. Reemplaza al catálogo, al
 * selector de variante y al configurador de la vista anterior (tres modales):
 * ahora son los pasos de un mismo asistente. Volver al paso Variante es el
 * antiguo «Cambiar variante»: se conservan color, tallas y precio.
 */
export function AgregarProducto({ abierto, onCerrar, bloque, onAgregar, catalogo, onTelaCreada, colores, onColorCreado, tallas, generos, urls }: Props) {
    const { puede } = usePermisos();
    const { tasaBcv } = usePage().props;
    const [salto, setSalto] = useState<{ paso: number; n: number }>();
    const saltar = (paso: number) => setSalto((s) => ({ paso, n: (s?.n ?? 0) + 1 }));

    // ── Paso 1: producto (tipo del catálogo) ────────────────────────────────
    const [busqueda, setBusqueda] = useState('');
    const [tiposFiltro, setTiposFiltro] = useState<number[]>([]);
    const [precioMin, setPrecioMin] = useState('');
    const [precioMax, setPrecioMax] = useState('');
    const [orden, setOrden] = useState<Orden>('relevancia');
    const [tipoId, setTipoId] = useState<number | null>(bloque?.tipo_producto_id ?? null);
    const tipo = catalogo.find((t) => t.id === tipoId) ?? null;

    const visibles = useMemo(() => {
        const q = busqueda.trim().toLowerCase();
        const min = precioMin === '' ? null : num(precioMin);
        const max = precioMax === '' ? null : num(precioMax);
        const lista = catalogo.filter(
            (t) =>
                (!q || t.nombre.toLowerCase().includes(q) || (t.prefijo ?? '').toLowerCase().startsWith(q)) &&
                (!tiposFiltro.length || tiposFiltro.includes(t.id)) &&
                (min === null || t.precio >= min) &&
                (max === null || t.precio <= max),
        );
        if (orden === 'precio-asc') lista.sort((a, b) => a.precio - b.precio);
        else if (orden === 'precio-desc') lista.sort((a, b) => b.precio - a.precio);
        else lista.sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'));
        return lista;
    }, [catalogo, busqueda, tiposFiltro, precioMin, precioMax, orden]);

    // ── Paso 2: variante (tela + atributos) ─────────────────────────────────
    const [telaId, setTelaId] = useState<number | null>(bloque?.insumo_tela_id ?? null);
    const [valores, setValores] = useState<Record<number, number>>(() => valoresDe(bloque, catalogo));
    const [resuelta, setResuelta] = useState<{ estado: 'nada' | 'buscando' | 'lista' | 'falta'; mensaje?: string; datos?: NonNullable<VarianteResuelta['producto']> & { dinamica: boolean } }>(
        bloque
            ? {
                  estado: 'lista',
                  datos: {
                      id: bloque.producto_id,
                      codigo: bloque.codigo ?? '',
                      precio_base: bloque.precio_catalogo ?? bloque.precio,
                      imagen: bloque.imagen,
                      tipo_nombre: bloque.nombre,
                      tela_nombre: null,
                      dinamica: !bloque.producto_id,
                  },
              }
            : { estado: 'nada' },
    );
    const atributosConValores = tipo?.atributos.filter((a) => a.valores.length) ?? [];
    const requierenTela = Boolean(tipo?.requiere_tela);
    const elegidos = (tipo && tipo.telas.length ? (telaId ? 1 : 0) : 0) + atributosConValores.filter((a) => valores[a.id]).length;
    const porElegir = (tipo && tipo.telas.length ? 1 : 0) + atributosConValores.length;
    const completa = (!requierenTela || Boolean(telaId)) && atributosConValores.every((a) => valores[a.id]);
    // La combinación guardada del producto que se edita (a prueba del doble efecto de StrictMode).
    const claveVariante = JSON.stringify([tipoId, telaId, valores, completa]);
    const claveGuardada = useRef(bloque ? claveVariante : null);

    // Resolver la variante (SKU + precio base) cuando la combinación está completa.
    useEffect(() => {
        const alEditar = claveGuardada.current === claveVariante;
        if (!alEditar) claveGuardada.current = null; // cambió la combinación: ya es una variante nueva
        // Al editar, la variante guardada ya está resuelta (SKU y precio negociado). Una
        // materializada trae su precio de catálogo; de una dinámica solo se consulta el precio
        // de catálogo para mostrarlo como «Precio base» y que «Restaurar» vuelva a él. Si la
        // combinación ya no existe, se queda la guardada.
        if (alEditar && (bloque?.producto_id || !tipo || !completa)) return;
        if (!tipo || !completa) {
            setResuelta({ estado: 'nada' });
            return;
        }
        let vigente = true;
        if (!alEditar) setResuelta({ estado: 'buscando' });
        const p = new URLSearchParams({ tipo_producto_id: String(tipo.id) });
        if (telaId) p.set('insumo_tela_id', String(telaId));
        Object.values(valores).forEach((v) => p.append('atributo_valor_ids[]', String(v)));
        fetch(`${urls.resolverVariante}?${p}`, { headers: { Accept: 'application/json' } })
            .then((r) => r.json() as Promise<VarianteResuelta>)
            .then((r) => {
                if (!vigente) return;
                if (alEditar) {
                    const base = r.found && r.producto ? r.producto.precio_base : null;
                    if (base !== null) setResuelta((x) => (x.datos ? { ...x, datos: { ...x.datos, precio_base: base } } : x));
                    return;
                }
                if (r.found && r.producto) {
                    setResuelta({ estado: 'lista', datos: { ...r.producto, dinamica: Boolean(r.dynamic) } });
                    if (!precioTocado.current) setPrecio(String(r.producto.precio_base));
                } else setResuelta({ estado: 'falta', mensaje: r.message ?? 'Esta combinación no existe en el catálogo.' });
            })
            .catch(() => vigente && !alEditar && setResuelta({ estado: 'falta', mensaje: 'No se pudo resolver la variante. Intenta de nuevo.' }));
        return () => {
            vigente = false;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [claveVariante]);

    const elegirTipo = (t: TipoCatalogo) => {
        if (t.id !== tipoId) {
            setTipoId(t.id);
            setTelaId(null);
            setValores({});
            claveGuardada.current = null;
        }
        saltar(1);
    };

    // ── Paso 3: color, tallas × género y precio ─────────────────────────────
    const [colorId, setColorId] = useState<number | null>(bloque?.color_id ?? null);
    const [buscarColor, setBuscarColor] = useState('');
    const [celdas, setCeldas] = useState<Record<string, number>>(() => Object.fromEntries((bloque?.tallas ?? []).map((t) => [claveCelda(t.talla_id, t.genero_id), t.cantidad])));
    const grupos = [...new Set(tallas.map((t) => t.grupo))];
    const [escala, setEscala] = useState<string>(() => {
        const conDatos = bloque?.tallas.map((t) => tallas.find((x) => x.id === t.talla_id)?.grupo).find(Boolean);
        return conDatos ?? (grupos.includes('Letras') ? 'Letras' : (grupos[0] ?? ''));
    });
    const [precio, setPrecio] = useState(bloque ? String(bloque.precio) : '');
    const precioTocado = useRef(Boolean(bloque));
    const [reparto, setReparto] = useState<{ genero: string; total: string }>({ genero: String(generos[0]?.id ?? ''), total: '' });

    const totalUnidades = Object.values(celdas).reduce((s, n) => s + n, 0);
    const unidadesEscala = (g: string) => tallas.filter((t) => t.grupo === g).reduce((s, t) => s + generos.reduce((a, gen) => a + (celdas[claveCelda(t.id, gen.id)] ?? 0), 0), 0);
    const precioNum = num(precio);
    const precioBase = resuelta.datos?.precio_base ?? 0;
    const color = colores.find((c) => c.id === colorId);

    const cambiarCelda = (talla: number, genero: number, cantidad: number) =>
        setCeldas((c) => {
            const k = claveCelda(talla, genero);
            const { [k]: _, ...resto } = c;
            return cantidad > 0 ? { ...resto, [k]: cantidad } : resto;
        });

    // Reparte un total entre las tallas de la escala visible para un género (los demás se conservan).
    const distribuir = () => {
        const total = Math.floor(num(reparto.total));
        const genero = Number(reparto.genero);
        const lista = tallas.filter((t) => t.grupo === escala);
        if (!total || total < 1 || !genero || !lista.length) return toast.warning('Indica el género y cuántas unidades repartir.');
        const base = Math.floor(total / lista.length);
        const resto = total % lista.length;
        lista.forEach((t, i) => cambiarCelda(t.id, genero, base + (i < resto ? 1 : 0)));
        setReparto((r) => ({ ...r, total: '' }));
    };

    const coloresVisibles = colores.filter((c) => !buscarColor.trim() || c.nombre.toLowerCase().includes(buscarColor.trim().toLowerCase()));
    const porGrupoColor = coloresVisibles.reduce<Record<string, ColorCatalogo[]>>((acc, c) => {
        (acc[c.grupo || 'Otros'] ??= []).push(c);
        return acc;
    }, {});

    // Altas rápidas (color, tela) en su propio diálogo.
    const [altaColor, setAltaColor] = useState(0);
    const [altaTela, setAltaTela] = useState(0);

    const armar = (): Bloque | null => {
        if (!tipo || !resuelta.datos) return null;
        const d = resuelta.datos;
        const tela = tipo.telas.find((t) => t.id === telaId);
        const variante = [
            tela?.nombre ?? d.tela_nombre,
            ...atributosConValores.map((a) => {
                const v = a.valores.find((x) => x.id === valores[a.id]);
                return v ? `${a.nombre}: ${v.nombre}` : null;
            }),
        ].filter(Boolean) as string[];
        return {
            id: bloque?.id ?? nuevoId(),
            producto_id: d.dinamica ? null : d.id,
            tipo_producto_id: tipo.id,
            insumo_tela_id: d.dinamica ? telaId : null,
            atributo_valor_ids: d.dinamica ? Object.values(valores) : [],
            nombre: tipo.nombre,
            codigo: d.codigo || null,
            variante: variante.length ? variante.join(' · ') : (bloque?.variante ?? ''),
            imagen: tipo.imagen,
            color_id: colorId,
            precio: precioNum,
            // Si es la variante guardada, su precio de catálogo tal cual (null si no se conoce:
            // nunca el negociado); si se resolvió otra, el del resolver.
            precio_catalogo: d.dinamica ? null : bloque?.producto_id === d.id ? (bloque.precio_catalogo ?? null) : d.precio_base,
            bordados: bloque?.bordados ?? [],
            tallas: Object.entries(celdas).map(([k, cantidad]) => {
                const [talla_id, genero_id] = k.split('-').map(Number) as [number, number];
                const previa = bloque?.tallas.find((t) => t.talla_id === talla_id && t.genero_id === genero_id);
                return { talla_id, genero_id, cantidad, descripcion: previa?.descripcion ?? null };
            }),
        };
    };

    const [reinicio, setReinicio] = useState(0);
    const agregar = (otro: boolean) => {
        const b = armar();
        if (!b) return;
        onAgregar(b);
        if (!otro) return onCerrar();
        toast.success(`${b.nombre} agregado. Configura el siguiente.`);
        setTipoId(null);
        setTelaId(null);
        setValores({});
        setResuelta({ estado: 'nada' });
        setColorId(null);
        setCeldas({});
        setPrecio('');
        precioTocado.current = false;
        setReinicio((r) => r + 1);
    };
    const listoParaAgregar = Boolean(resuelta.datos) && colorId !== null && totalUnidades > 0 && precioNum > 0;
    // Por qué no se puede agregar todavía (el botón deshabilitado solo no lo dice).
    const falta = [!resuelta.datos && 'la variante', colorId === null && 'el color', totalUnidades <= 0 && 'al menos una talla con cantidad', precioNum <= 0 && 'un precio mayor a cero'].filter(
        Boolean,
    );

    return (
        <Dialog open={abierto} onOpenChange={(a) => !a && onCerrar()}>
            <DialogContent className="max-h-[94svh] overflow-y-auto sm:max-w-5xl">
                <DialogHeader>
                    <DialogTitle>{bloque ? 'Editar producto' : 'Agregar producto'}</DialogTitle>
                    <DialogDescription>
                        {tipo
                            ? `${tipo.nombre}${resuelta.datos?.codigo ? ` · ${resuelta.datos.codigo}` : ''}`
                            : `Catálogo · ${catalogo.length} ${catalogo.length === 1 ? 'tipo disponible' : 'tipos disponibles'}`}
                    </DialogDescription>
                </DialogHeader>

                <Asistente
                    key={reinicio}
                    inicial={bloque ? 2 : 0}
                    salto={salto}
                    final={
                        <>
                            {!listoParaAgregar && (
                                <span className="text-muted-foreground self-center text-xs" aria-live="polite">
                                    Falta {falta.join(', ')}.
                                </span>
                            )}
                            {!bloque && (
                                <Button type="button" variant="outline" disabled={!listoParaAgregar} onClick={() => agregar(true)}>
                                    <Plus /> Agregar y configurar otro
                                </Button>
                            )}
                            <Button type="button" disabled={!listoParaAgregar} onClick={() => agregar(false)}>
                                <Check /> {bloque ? 'Guardar cambios' : 'Agregar a la cotización'}
                            </Button>
                        </>
                    }
                    pasos={[
                        {
                            titulo: 'Producto',
                            descripcion: 'Elige el tipo de prenda del catálogo.',
                            validar: () => (!tipo ? 'Elige un producto del catálogo.' : null),
                            contenido: (
                                <div className="grid gap-3">
                                    <div className="flex flex-wrap items-end gap-2">
                                        <div className="relative min-w-52 flex-1">
                                            <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2" />
                                            <Input
                                                type="search"
                                                value={busqueda}
                                                onChange={(e) => setBusqueda(e.target.value)}
                                                placeholder="Buscar por nombre o prefijo…"
                                                aria-label="Buscar en el catálogo"
                                                className="pl-8"
                                            />
                                        </div>
                                        <Input
                                            type="number"
                                            min={0}
                                            step="0.01"
                                            inputMode="decimal"
                                            value={precioMin}
                                            onChange={(e) => setPrecioMin(e.target.value)}
                                            placeholder="Precio mín."
                                            aria-label="Precio mínimo"
                                            className="w-28"
                                        />
                                        <Input
                                            type="number"
                                            min={0}
                                            step="0.01"
                                            inputMode="decimal"
                                            value={precioMax}
                                            onChange={(e) => setPrecioMax(e.target.value)}
                                            placeholder="Precio máx."
                                            aria-label="Precio máximo"
                                            className="w-28"
                                        />
                                        <Select value={orden} onValueChange={(v) => setOrden(v as Orden)}>
                                            <SelectTrigger className="w-48" aria-label="Ordenar el catálogo">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="relevancia">Más relevantes</SelectItem>
                                                <SelectItem value="precio-asc">Precio: menor a mayor</SelectItem>
                                                <SelectItem value="precio-desc">Precio: mayor a menor</SelectItem>
                                                <SelectItem value="nombre">Nombre A–Z</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    {catalogo.length > 1 && (
                                        <div className="flex flex-wrap gap-1.5" role="group" aria-label="Filtrar por tipo">
                                            {catalogo.map((t) => {
                                                const activo = tiposFiltro.includes(t.id);
                                                return (
                                                    <Button
                                                        key={t.id}
                                                        type="button"
                                                        size="sm"
                                                        variant={activo ? 'secondary' : 'outline'}
                                                        aria-pressed={activo}
                                                        onClick={() => setTiposFiltro((f) => (activo ? f.filter((x) => x !== t.id) : [...f, t.id]))}
                                                    >
                                                        {t.nombre}
                                                    </Button>
                                                );
                                            })}
                                            {(tiposFiltro.length > 0 || busqueda || precioMin || precioMax) && (
                                                <Button
                                                    type="button"
                                                    size="sm"
                                                    variant="ghost"
                                                    onClick={() => {
                                                        setTiposFiltro([]);
                                                        setBusqueda('');
                                                        setPrecioMin('');
                                                        setPrecioMax('');
                                                        setOrden('relevancia');
                                                    }}
                                                >
                                                    Limpiar
                                                </Button>
                                            )}
                                        </div>
                                    )}
                                    <p className="text-muted-foreground text-xs" aria-live="polite">
                                        {visibles.length} {visibles.length === 1 ? 'resultado' : 'resultados'}
                                    </p>
                                    {visibles.length === 0 ? (
                                        <p className="text-muted-foreground rounded-lg border border-dashed p-6 text-center text-sm">Sin resultados. Ajusta los filtros o cambia la búsqueda.</p>
                                    ) : (
                                        <ul className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                            {visibles.map((t) => (
                                                <li key={t.id}>
                                                    <button
                                                        type="button"
                                                        onClick={() => elegirTipo(t)}
                                                        aria-pressed={t.id === tipoId}
                                                        className={cn(
                                                            'hover:border-primary/60 focus-visible:ring-ring flex w-full items-center gap-3 rounded-lg border p-3 text-left transition-colors outline-none focus-visible:ring-2',
                                                            t.id === tipoId && 'border-primary bg-primary/5',
                                                        )}
                                                    >
                                                        <span className="bg-muted grid size-14 shrink-0 place-items-center overflow-hidden rounded-md">
                                                            {t.imagen ? <img src={t.imagen} alt="" className="size-full object-cover" /> : <Shirt className="text-muted-foreground size-6" />}
                                                        </span>
                                                        <span className="min-w-0 flex-1">
                                                            <span className="block truncate font-medium">{t.nombre}</span>
                                                            <span className="text-muted-foreground block text-xs">
                                                                {t.requiere_tela || t.telas.length ? `${t.telas.length} ${t.telas.length === 1 ? 'tela' : 'telas'}` : 'Sin tela'} · {t.atributos.length}{' '}
                                                                {t.atributos.length === 1 ? 'atributo' : 'atributos'}
                                                            </span>
                                                            <span className="block text-xs tabular">
                                                                {t.precio > 0 ? (
                                                                    <>
                                                                        desde {formatoUsd(t.precio)}
                                                                        {tasaBcv && <span className="text-muted-foreground"> · {formatoBs(t.precio * tasaBcv.valor)}</span>}
                                                                    </>
                                                                ) : (
                                                                    <span className="text-muted-foreground">Precio al configurar</span>
                                                                )}
                                                            </span>
                                                        </span>
                                                    </button>
                                                </li>
                                            ))}
                                        </ul>
                                    )}
                                </div>
                            ),
                        },
                        {
                            titulo: 'Variante',
                            descripcion: 'Elige la combinación que vas a cotizar. El SKU y el precio base se resuelven solos.',
                            validar: () =>
                                !tipo
                                    ? 'Elige un producto del catálogo.'
                                    : !completa
                                      ? requierenTela && !telaId
                                          ? 'Elige la tela.'
                                          : 'Elige un valor para cada atributo.'
                                      : resuelta.estado === 'buscando'
                                        ? 'Espera un momento: se está resolviendo la variante.'
                                        : resuelta.estado !== 'lista'
                                          ? (resuelta.mensaje ?? 'Esta combinación no existe en el catálogo.')
                                          : null,
                            contenido: tipo ? (
                                <div className="grid gap-4">
                                    {porElegir > 0 && (
                                        <p className={cn('text-xs', elegidos >= porElegir ? 'text-success' : 'text-muted-foreground')} aria-live="polite">
                                            {elegidos >= porElegir ? 'Combinación completa' : `${elegidos} de ${porElegir} elegidos`}
                                        </p>
                                    )}
                                    {(tipo.telas.length > 0 || requierenTela) && (
                                        <GrupoChips
                                            titulo={`Tela${requierenTela ? '' : ' (opcional)'}`}
                                            opciones={tipo.telas.map((t) => ({ id: t.id, nombre: t.nombre, codigo: t.codigo }))}
                                            elegido={telaId}
                                            onElegir={(id) => setTelaId(id === telaId && !requierenTela ? null : id)}
                                            vacio="Este tipo aún no tiene telas asignadas."
                                            accion={
                                                puede('tipo-productos.gestionar') && (
                                                    <Button type="button" variant="ghost" size="sm" onClick={() => setAltaTela((n) => n + 1)}>
                                                        <Plus /> Nueva tela
                                                    </Button>
                                                )
                                            }
                                        />
                                    )}
                                    {tipo.atributos.map((a) =>
                                        a.valores.length ? (
                                            <GrupoChips
                                                key={a.id}
                                                titulo={a.nombre}
                                                opciones={a.valores}
                                                elegido={valores[a.id] ?? null}
                                                onElegir={(id) => setValores((v) => ({ ...v, [a.id]: id }))}
                                            />
                                        ) : (
                                            <p key={a.id} className="text-muted-foreground text-sm">
                                                <span className="font-medium">{a.nombre}:</span> sin valores definidos.
                                            </p>
                                        ),
                                    )}
                                    {!tipo.telas.length && !tipo.atributos.length && (
                                        <p className="text-muted-foreground text-sm">
                                            {requierenTela
                                                ? 'Este tipo requiere tela y aún no tiene ninguna asignada: agrégala con «Nueva tela».'
                                                : 'Este tipo no tiene tela ni atributos: se cotiza tal cual.'}
                                        </p>
                                    )}
                                    <div aria-live="polite">
                                        {resuelta.estado === 'buscando' && (
                                            <p className="text-muted-foreground flex items-center gap-2 text-sm">
                                                <Loader2 className="size-4 animate-spin" /> Resolviendo la variante…
                                            </p>
                                        )}
                                        {resuelta.estado === 'lista' && resuelta.datos && (
                                            <div className="border-success/30 bg-success/8 flex flex-wrap items-center justify-between gap-3 rounded-lg border p-3 text-sm">
                                                <span>
                                                    <span className="text-muted-foreground block text-xs">Variante encontrada</span>
                                                    <code className="font-mono font-semibold">{resuelta.datos.codigo || '—'}</code>
                                                </span>
                                                <span className="text-right">
                                                    <span className="text-muted-foreground block text-xs">Precio base</span>
                                                    <span className="tabular font-semibold">{formatoUsd(resuelta.datos.precio_base)}</span>
                                                    {tasaBcv && <span className="text-muted-foreground block text-xs tabular">{formatoBs(resuelta.datos.precio_base * tasaBcv.valor)}</span>}
                                                </span>
                                            </div>
                                        )}
                                        {resuelta.estado === 'falta' && (
                                            <p role="alert" className="border-destructive/30 bg-destructive/8 rounded-lg border p-3 text-sm">
                                                {resuelta.mensaje}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            ) : (
                                <p className="text-muted-foreground text-sm">Primero elige un producto del catálogo.</p>
                            ),
                        },
                        {
                            titulo: 'Configurar',
                            descripcion: 'Color, tallas y cantidades, y el precio unitario acordado.',
                            validar: () =>
                                colorId === null
                                    ? 'Elige el color.'
                                    : totalUnidades <= 0
                                      ? 'Indica al menos una unidad en alguna talla.'
                                      : precioNum <= 0
                                        ? 'El precio unitario debe ser mayor que cero.'
                                        : null,
                            contenido: (
                                <div className="grid gap-5">
                                    {/* 1. Color */}
                                    <section className="grid gap-2">
                                        <div className="flex flex-wrap items-center justify-between gap-2">
                                            <h3 className="text-sm font-medium">
                                                1. Color <span className="text-muted-foreground font-normal">· {color?.nombre ?? 'sin elegir'}</span>
                                            </h3>
                                            {puede('colores.gestionar') && (
                                                <Button type="button" variant="ghost" size="sm" onClick={() => setAltaColor((n) => n + 1)}>
                                                    <Palette /> Nuevo color
                                                </Button>
                                            )}
                                        </div>
                                        <Input
                                            type="search"
                                            value={buscarColor}
                                            onChange={(e) => setBuscarColor(e.target.value)}
                                            placeholder="Buscar color…"
                                            aria-label="Buscar color"
                                            className="sm:max-w-xs"
                                        />
                                        <div className="grid max-h-56 gap-2 overflow-y-auto pr-1" role="radiogroup" aria-label="Color">
                                            {Object.keys(porGrupoColor).length === 0 && <p className="text-muted-foreground text-sm">Ningún color coincide con la búsqueda.</p>}
                                            {Object.entries(porGrupoColor)
                                                .sort(([a], [b]) => a.localeCompare(b, 'es'))
                                                .map(([grupo, lista]) => (
                                                    <div key={grupo} className="grid gap-1">
                                                        <p className="text-muted-foreground text-xs">{grupo}</p>
                                                        <div className="flex flex-wrap gap-1.5">
                                                            {lista.map((c) => (
                                                                <button
                                                                    key={c.id}
                                                                    type="button"
                                                                    role="radio"
                                                                    aria-checked={c.id === colorId}
                                                                    onClick={() => setColorId(c.id)}
                                                                    className={cn(
                                                                        'focus-visible:ring-ring inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs outline-none focus-visible:ring-2',
                                                                        c.id === colorId ? 'border-primary bg-primary/10 font-medium' : 'hover:bg-muted',
                                                                    )}
                                                                >
                                                                    <Muestra hex={c.hex} /> {c.nombre}
                                                                </button>
                                                            ))}
                                                        </div>
                                                    </div>
                                                ))}
                                        </div>
                                    </section>

                                    {/* 2. Tallas × género */}
                                    <section className="grid gap-2">
                                        <h3 className="text-sm font-medium">
                                            2. Tallas y cantidades{' '}
                                            <span className="text-muted-foreground font-normal">
                                                · {formatoNumero(totalUnidades)} {totalUnidades === 1 ? 'unidad' : 'unidades'}
                                            </span>
                                        </h3>
                                        {grupos.length > 1 && (
                                            <div className="flex flex-wrap gap-1.5" role="group" aria-label="Escala de tallas">
                                                {grupos.map((g) => {
                                                    const n = unidadesEscala(g);
                                                    return (
                                                        <Button
                                                            key={g}
                                                            type="button"
                                                            size="sm"
                                                            variant={g === escala ? 'secondary' : 'outline'}
                                                            aria-pressed={g === escala}
                                                            onClick={() => setEscala(g)}
                                                        >
                                                            {g}
                                                            {n > 0 && g !== escala && <span className="bg-primary text-primary-foreground rounded-full px-1.5 text-[0.65rem] tabular">{n}</span>}
                                                        </Button>
                                                    );
                                                })}
                                            </div>
                                        )}
                                        <div className="overflow-x-auto rounded-lg border">
                                            <table className="w-full text-sm">
                                                <thead>
                                                    <tr className="bg-muted/50">
                                                        <th scope="col" className="px-3 py-2 text-left font-medium">
                                                            Talla
                                                        </th>
                                                        {generos.map((g) => (
                                                            <th key={g.id} scope="col" className="px-2 py-2 text-center font-medium">
                                                                {g.nombre}
                                                            </th>
                                                        ))}
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {tallas
                                                        .filter((t) => t.grupo === escala)
                                                        .map((t) => (
                                                            <tr key={t.id} className="border-t">
                                                                <th scope="row" className="px-3 py-1.5 text-left font-medium">
                                                                    {t.nombre}
                                                                </th>
                                                                {generos.map((g) => {
                                                                    const v = celdas[claveCelda(t.id, g.id)] ?? 0;
                                                                    return (
                                                                        <td key={g.id} className="px-1 py-1">
                                                                            <div className={cn('mx-auto flex w-32 items-center gap-0.5 rounded-md', v > 0 && 'bg-primary/8')}>
                                                                                <Button
                                                                                    type="button"
                                                                                    variant="ghost"
                                                                                    size="icon"
                                                                                    className="size-8"
                                                                                    tabIndex={-1}
                                                                                    onClick={() => cambiarCelda(t.id, g.id, Math.max(0, v - 1))}
                                                                                    aria-label={`Restar una: ${t.nombre} · ${g.nombre}`}
                                                                                >
                                                                                    <Minus />
                                                                                </Button>
                                                                                <Input
                                                                                    type="number"
                                                                                    min={0}
                                                                                    step={1}
                                                                                    inputMode="numeric"
                                                                                    value={v || ''}
                                                                                    placeholder="0"
                                                                                    onChange={(e) => cambiarCelda(t.id, g.id, Math.max(0, parseInt(e.target.value, 10) || 0))}
                                                                                    aria-label={`Talla ${t.nombre} · ${g.nombre}`}
                                                                                    className="h-8 px-1 text-center tabular"
                                                                                />
                                                                                <Button
                                                                                    type="button"
                                                                                    variant="ghost"
                                                                                    size="icon"
                                                                                    className="size-8"
                                                                                    tabIndex={-1}
                                                                                    onClick={() => cambiarCelda(t.id, g.id, v + 1)}
                                                                                    aria-label={`Sumar una: ${t.nombre} · ${g.nombre}`}
                                                                                >
                                                                                    <Plus />
                                                                                </Button>
                                                                            </div>
                                                                        </td>
                                                                    );
                                                                })}
                                                            </tr>
                                                        ))}
                                                </tbody>
                                            </table>
                                        </div>
                                        <div className="flex flex-wrap items-end gap-2 rounded-lg border border-dashed p-2">
                                            <span className="text-muted-foreground flex items-center gap-1 self-center text-xs">
                                                <SplitSquareHorizontal className="size-3.5" /> Distribuir uniforme en {escala}:
                                            </span>
                                            <Select value={reparto.genero} onValueChange={(v) => setReparto((r) => ({ ...r, genero: v }))}>
                                                <SelectTrigger className="h-8 w-36" aria-label="Género a distribuir">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {generos.map((g) => (
                                                        <SelectItem key={g.id} value={String(g.id)}>
                                                            {g.nombre}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <Input
                                                type="number"
                                                min={1}
                                                step={1}
                                                inputMode="numeric"
                                                value={reparto.total}
                                                onChange={(e) => setReparto((r) => ({ ...r, total: e.target.value }))}
                                                placeholder="Total"
                                                aria-label="Unidades a distribuir"
                                                className="h-8 w-24 tabular"
                                            />
                                            <Button type="button" size="sm" variant="outline" onClick={distribuir}>
                                                Distribuir
                                            </Button>
                                        </div>
                                    </section>

                                    {/* 3. Precio */}
                                    <section className="grid gap-2">
                                        <h3 className="text-sm font-medium">3. Precio unitario</h3>
                                        <div className="flex flex-wrap items-end gap-3">
                                            <Campo etiqueta="Precio unitario ($)" ayuda={`Precio base: ${formatoUsd(precioBase)}. Cámbialo si negociaste otro con el cliente.`} className="w-56">
                                                <Input
                                                    type="number"
                                                    min={0}
                                                    step="0.01"
                                                    inputMode="decimal"
                                                    value={precio}
                                                    onChange={(e) => {
                                                        precioTocado.current = true;
                                                        setPrecio(e.target.value);
                                                    }}
                                                    className="tabular"
                                                />
                                            </Campo>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                disabled={precioNum === precioBase}
                                                onClick={() => {
                                                    precioTocado.current = false;
                                                    setPrecio(String(precioBase));
                                                }}
                                            >
                                                <RotateCcw /> Restaurar
                                            </Button>
                                        </div>
                                    </section>

                                    <dl className="bg-muted/40 grid grid-cols-3 gap-3 rounded-lg border p-3 text-sm">
                                        <div>
                                            <dt className="text-muted-foreground text-xs">Unidades</dt>
                                            <dd className="font-semibold tabular">{formatoNumero(totalUnidades)}</dd>
                                        </div>
                                        <div>
                                            <dt className="text-muted-foreground text-xs">Precio unitario</dt>
                                            <dd className="tabular">
                                                {formatoUsd(precioNum)}
                                                {tasaBcv && <span className="text-muted-foreground block text-xs">{formatoBs(precioNum * tasaBcv.valor)}</span>}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="text-muted-foreground text-xs">Subtotal</dt>
                                            <dd className="font-semibold tabular">
                                                {formatoUsd(precioNum * totalUnidades)}
                                                {tasaBcv && <span className="text-muted-foreground block text-xs font-normal">{formatoBs(precioNum * totalUnidades * tasaBcv.valor)}</span>}
                                            </dd>
                                        </div>
                                    </dl>
                                    <p className="text-muted-foreground text-xs">El bordado se configura después, desde la tabla de productos.</p>
                                </div>
                            ),
                        },
                    ]}
                />

                {altaColor > 0 && (
                    <AltaColor
                        key={altaColor}
                        colores={colores}
                        url={urls.colores}
                        onCerrar={() => setAltaColor(0)}
                        onCreado={(c) => {
                            onColorCreado(c);
                            setColorId(c.id);
                            setBuscarColor('');
                        }}
                    />
                )}
                {altaTela > 0 && tipo && (
                    <AltaTela
                        key={altaTela}
                        url={`${urls.telas}/${tipo.id}/telas`}
                        onCerrar={() => setAltaTela(0)}
                        onCreada={(t) => {
                            onTelaCreada(tipo.id, t);
                            setTelaId(t.id);
                        }}
                    />
                )}
            </DialogContent>
        </Dialog>
    );
}

/** Valores de atributo ya elegidos (al editar), por atributo. */
function valoresDe(bloque: Bloque | undefined, catalogo: TipoCatalogo[]): Record<number, number> {
    const tipo = catalogo.find((t) => t.id === bloque?.tipo_producto_id);
    if (!bloque || !tipo) return {};
    const out: Record<number, number> = {};
    for (const a of tipo.atributos) {
        const v = a.valores.find((x) => bloque.atributo_valor_ids.includes(x.id));
        if (v) out[a.id] = v.id;
    }
    return out;
}

function GrupoChips({
    titulo,
    opciones,
    elegido,
    onElegir,
    vacio,
    accion,
}: {
    titulo: string;
    opciones: { id: number; nombre: string; codigo?: string | null }[];
    elegido: number | null;
    onElegir: (id: number) => void;
    vacio?: string;
    accion?: React.ReactNode;
}) {
    const actual = opciones.find((o) => o.id === elegido);
    return (
        <section className="grid gap-2">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <h3 className="flex items-center gap-2 text-sm font-medium">
                    {actual ? <Check className="text-success size-4" /> : <span className="bg-border size-2 rounded-full" aria-hidden />}
                    {titulo}
                    {actual && <span className="text-muted-foreground font-normal">· {actual.nombre}</span>}
                </h3>
                {accion}
            </div>
            {opciones.length ? (
                <div className="flex flex-wrap gap-1.5" role="radiogroup" aria-label={titulo}>
                    {opciones.map((o) => (
                        <button
                            key={o.id}
                            type="button"
                            role="radio"
                            aria-checked={o.id === elegido}
                            onClick={() => onElegir(o.id)}
                            className={cn(
                                'focus-visible:ring-ring inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-sm outline-none focus-visible:ring-2',
                                o.id === elegido ? 'border-primary bg-primary/10 font-medium' : 'hover:bg-muted',
                            )}
                        >
                            {o.id === elegido && <Check className="size-3.5" />}
                            {o.nombre}
                            {o.codigo && <code className="text-muted-foreground font-mono text-xs">{o.codigo}</code>}
                        </button>
                    ))}
                </div>
            ) : (
                <p className="text-muted-foreground text-sm">{vacio}</p>
            )}
        </section>
    );
}

/** Alta rápida de color (extensión del maestro Colores): queda elegido. */
function AltaColor({ colores, url, onCerrar, onCreado }: { colores: ColorCatalogo[]; url: string; onCerrar: () => void; onCreado: (c: ColorCatalogo) => void }) {
    const grupos = [...new Set(colores.map((c) => c.grupo).filter(Boolean))].sort((a, b) => a!.localeCompare(b!, 'es')) as string[];
    const [nombre, setNombre] = useState('');
    const [grupo, setGrupo] = useState('');
    const [grupoNuevo, setGrupoNuevo] = useState('');
    const [hex, setHex] = useState('#1B3A5C');
    const [errores, setErrores] = useState<Record<string, string>>({});
    const [enviando, setEnviando] = useState(false);
    const NUEVO = '__nuevo__';
    const SIN = '__sin__';

    const guardar = async () => {
        setEnviando(true);
        const r = await postJson<{ color: { id: number; nombre: string; grupo: string | null; hex_referencial: string } }>(url, {
            nombre: nombre.trim(),
            grupo: grupo === NUEVO ? grupoNuevo.trim() || null : grupo || null,
            hex_referencial: hex.toUpperCase(),
        });
        setEnviando(false);
        if (!r.ok) return setErrores(Object.keys(r.errores).length ? r.errores : { nombre: r.mensaje });
        const c = r.datos.color;
        onCreado({ id: c.id, nombre: c.nombre, grupo: c.grupo, hex: c.hex_referencial });
        toast.success(`Color «${c.nombre}» creado.`);
        onCerrar();
    };

    return (
        <Dialog open onOpenChange={(a) => !a && onCerrar()}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Nuevo color</DialogTitle>
                    <DialogDescription>Se agrega al catálogo de colores y queda elegido.</DialogDescription>
                </DialogHeader>
                {/* <form> propio: Enter guarda. stopPropagation: el diálogo vive en un portal y el
                    submit subiría por el árbol de React hasta el formulario de la página. */}
                <form
                    className="grid gap-4"
                    onSubmit={(e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        if (!enviando) void guardar();
                    }}
                >
                    <div className="grid gap-4">
                        <p className="bg-muted/50 flex items-center gap-2 rounded-md p-3 text-sm font-medium">
                            <Muestra hex={/^#[0-9a-f]{6}$/i.test(hex) ? hex : null} className="size-6" /> {nombre.trim() || 'Nombre del color'}
                        </p>
                        <Campo etiqueta="Nombre" requerido error={errores.nombre}>
                            <Input value={nombre} maxLength={100} onChange={(e) => setNombre(e.target.value)} placeholder="Ej.: Azul marino" />
                        </Campo>
                        <Campo etiqueta="Grupo" error={errores.grupo}>
                            {(control) => (
                                <Select value={grupo || SIN} onValueChange={(v) => setGrupo(v === SIN ? '' : v)}>
                                    <SelectTrigger {...control} className="w-full">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value={SIN}>Sin grupo</SelectItem>
                                        {grupos.map((g) => (
                                            <SelectItem key={g} value={g}>
                                                {g}
                                            </SelectItem>
                                        ))}
                                        <SelectItem value={NUEVO}>Nuevo grupo…</SelectItem>
                                    </SelectContent>
                                </Select>
                            )}
                        </Campo>
                        {grupo === NUEVO && (
                            <Campo etiqueta="Nombre del grupo nuevo">
                                <Input value={grupoNuevo} maxLength={100} onChange={(e) => setGrupoNuevo(e.target.value)} />
                            </Campo>
                        )}
                        <Campo etiqueta="Color HEX referencial" requerido error={errores.hex_referencial}>
                            {(control) => (
                                <div className="flex items-center gap-2">
                                    <input
                                        type="color"
                                        value={/^#[0-9a-f]{6}$/i.test(hex) ? hex : '#000000'}
                                        onChange={(e) => setHex(e.target.value.toUpperCase())}
                                        aria-label="Elegir el color"
                                        className="h-9 w-12 cursor-pointer rounded border"
                                    />
                                    <Input {...control} value={hex} maxLength={7} onChange={(e) => setHex(e.target.value.toUpperCase())} className="w-32 font-mono uppercase" />
                                </div>
                            )}
                        </Campo>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onCerrar}>
                            Cancelar
                        </Button>
                        <Button type="submit" disabled={enviando}>
                            {enviando ? 'Guardando…' : 'Guardar y elegir'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

const UNIDADES_TELA = ['Metro', 'Kg', 'Gramo', 'Unidad', 'Rollo', 'Cono', 'Docena'];

/** Alta rápida de tela (insumo tipo Tela) asignada al tipo de producto: queda elegida. */
function AltaTela({ url, onCerrar, onCreada }: { url: string; onCerrar: () => void; onCreada: (t: TipoCatalogo['telas'][number]) => void }) {
    const [d, setD] = useState({ nombre: '', codigo: '', unidad: 'Metro', inventariable: true, minimo: '', actual: '0', maximo: '', costo: '', activo: true });
    const [errores, setErrores] = useState<Record<string, string>>({});
    const [enviando, setEnviando] = useState(false);
    const poner = (c: Partial<typeof d>) => setD((x) => ({ ...x, ...c }));

    const guardar = async () => {
        setEnviando(true);
        const r = await postJson<{ tela: { id: number; nombre: string; codigo: string | null }; message: string }>(url, {
            nombre: d.nombre.trim(),
            codigo: d.codigo.trim().toUpperCase() || null,
            unidad_medida: d.unidad,
            is_inventoriable: d.inventariable ? 1 : 0,
            costo_unitario: d.costo,
            estado: d.activo ? 1 : 0,
            ...(d.inventariable ? { stock_minimo: d.minimo || 0, stock_actual: d.actual || 0, stock_maximo: d.maximo || 0 } : {}),
        });
        setEnviando(false);
        if (!r.ok) return setErrores(Object.keys(r.errores).length ? r.errores : { nombre: r.mensaje });
        onCreada(r.datos.tela);
        toast.success(r.datos.message ?? 'Tela creada.');
        onCerrar();
    };

    return (
        <Dialog open onOpenChange={(a) => !a && onCerrar()}>
            <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nueva tela</DialogTitle>
                    <DialogDescription>Se registra como insumo de tipo Tela, se asigna a este producto y queda elegida.</DialogDescription>
                </DialogHeader>
                {/* <form> propio: Enter guarda. stopPropagation: el diálogo vive en un portal y el
                    submit subiría por el árbol de React hasta el formulario de la página. */}
                <form
                    className="grid gap-4"
                    onSubmit={(e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        if (!enviando) void guardar();
                    }}
                >
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Campo etiqueta="Nombre" requerido error={errores.nombre} className="sm:col-span-2">
                            <Input value={d.nombre} maxLength={100} onChange={(e) => poner({ nombre: e.target.value })} />
                        </Campo>
                        <Campo etiqueta="Código" error={errores.codigo} ayuda="2 a 8 letras mayúsculas o números. Forma parte del SKU.">
                            <Input value={d.codigo} maxLength={8} onChange={(e) => poner({ codigo: e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '') })} className="font-mono uppercase" />
                        </Campo>
                        <Campo etiqueta="Unidad de medida" requerido error={errores.unidad_medida}>
                            {(control) => (
                                <Select value={d.unidad} onValueChange={(v) => poner({ unidad: v })}>
                                    <SelectTrigger {...control} className="w-full">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {UNIDADES_TELA.map((u) => (
                                            <SelectItem key={u} value={u}>
                                                {u}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            )}
                        </Campo>
                        <label className="flex items-center gap-2 text-sm sm:col-span-2">
                            <Switch checked={d.inventariable} onCheckedChange={(v) => poner({ inventariable: v })} /> Inventariable (gestiona existencias)
                        </label>
                        {d.inventariable && (
                            <div className="grid grid-cols-3 gap-2 sm:col-span-2">
                                <Campo etiqueta="Existencia mínima" error={errores.stock_minimo}>
                                    <Input type="number" min={0} step="0.01" value={d.minimo} onChange={(e) => poner({ minimo: e.target.value })} className="tabular" />
                                </Campo>
                                <Campo etiqueta="Existencia actual" error={errores.stock_actual}>
                                    <Input type="number" min={0} step="0.01" value={d.actual} onChange={(e) => poner({ actual: e.target.value })} className="tabular" />
                                </Campo>
                                <Campo etiqueta="Existencia máxima" error={errores.stock_maximo}>
                                    <Input type="number" min={0} step="0.01" value={d.maximo} onChange={(e) => poner({ maximo: e.target.value })} className="tabular" />
                                </Campo>
                            </div>
                        )}
                        <Campo etiqueta="Costo unitario ($)" requerido error={errores.costo_unitario}>
                            <Input type="number" min={0.01} step="0.01" inputMode="decimal" value={d.costo} onChange={(e) => poner({ costo: e.target.value })} className="tabular" />
                        </Campo>
                        <label className="flex items-center gap-2 self-end pb-2 text-sm">
                            <Switch checked={d.activo} onCheckedChange={(v) => poner({ activo: v })} /> Activa
                        </label>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onCerrar}>
                            Cancelar
                        </Button>
                        <Button type="submit" disabled={enviando}>
                            {enviando ? 'Guardando…' : 'Guardar y elegir'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
