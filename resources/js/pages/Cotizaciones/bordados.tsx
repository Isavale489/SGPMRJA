import { usePage } from '@inertiajs/react';
import { Plus, Scissors, Search, Trash2, X } from 'lucide-react';
import { useMemo, useState } from 'react';
import { toast } from 'sonner';

import { Buscador } from '@/components/app/buscador';
import { Campo } from '@/components/app/campo';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { usePermisos } from '@/hooks/use-permisos';
import { formatoBs, formatoFecha, formatoUsd } from '@/lib/formato';
import { cn } from '@/lib/utils';

import { postJson } from './agregar-producto';
import { redondear } from './calculos';
import type { Bloque, BordadoLinea, LogoCatalogo, PaginaFormularioCotizacion, UbicacionCatalogo } from './tipos';

const num = (v: string) => {
    const n = parseFloat(v);
    return Number.isFinite(n) ? n : 0;
};
const entero = (v: string) => Math.max(1, parseInt(v, 10) || 1);

interface FilaCatalogo {
    marcada: boolean;
    precio: string;
    cantidad: string;
    logo_id: number | null;
}
interface FilaPropia {
    id: number;
    nombre: string;
    precio: string;
    cantidad: string;
    logo_id: number | null;
}

let secuencia = 0;
const filaBase = (u: UbicacionCatalogo): FilaCatalogo => ({ marcada: false, precio: String(u.precio), cantidad: '1', logo_id: null });

interface Props {
    bloque: Bloque;
    ubicaciones: UbicacionCatalogo[];
    logos: LogoCatalogo[];
    onLogoCreado: (logo: LogoCatalogo) => void;
    maxBordados: number;
    urls: Pick<PaginaFormularioCotizacion['urls'], 'logos'>;
    onCerrar: () => void;
    onGuardar: (bordados: BordadoLinea[]) => void;
}

/**
 * Bordados de un producto de la cotización: ubicaciones del catálogo (con su
 * precio base editable) y personalizadas, cada una con cantidad y logo
 * (opcional). El tope `max_bordados_producto` es la SUMA de cantidades, igual
 * que en el servidor. Guardar sin ubicaciones deja el producto «Sin bordado».
 */
export function ConfigurarBordados({ bloque, ubicaciones, logos, onLogoCreado, maxBordados, urls, onCerrar, onGuardar }: Props) {
    const { tasaBcv } = usePage().props;
    const [filtro, setFiltro] = useState('');
    const [catalogo, setCatalogo] = useState<Record<number, FilaCatalogo>>(() => {
        const inicial: Record<number, FilaCatalogo> = {};
        for (const u of ubicaciones) inicial[u.id] = filaBase(u);
        for (const b of bloque.bordados) {
            if (!b.es_personalizada && b.ubicacion_bordado_id && b.ubicacion_bordado_id in inicial) {
                inicial[b.ubicacion_bordado_id] = { marcada: true, precio: String(b.precio_aplicado), cantidad: String(Math.max(1, b.cantidad)), logo_id: b.logo_id };
            }
        }
        return inicial;
    });
    const [propias, setPropias] = useState<FilaPropia[]>(() =>
        bloque.bordados
            .filter((b) => b.es_personalizada || !b.ubicacion_bordado_id || !ubicaciones.some((u) => u.id === b.ubicacion_bordado_id))
            .map((b) => ({ id: ++secuencia, nombre: b.nombre_aplicado, precio: String(b.precio_aplicado), cantidad: String(Math.max(1, b.cantidad)), logo_id: b.logo_id })),
    );
    const [intento, setIntento] = useState(false);

    const usados = Object.values(catalogo).reduce((s, f) => s + (f.marcada ? entero(f.cantidad) : 0), 0) + propias.reduce((s, f) => s + entero(f.cantidad), 0);
    const recargo = redondear(
        Object.values(catalogo).reduce((s, f) => s + (f.marcada ? num(f.precio) * entero(f.cantidad) : 0), 0) + propias.reduce((s, f) => s + num(f.precio) * entero(f.cantidad), 0),
    );
    /** ¿Cabe `extra` bordados más? Si no, avisa (el servidor rechazaría la cotización). */
    const cabe = (extra: number) => {
        if (usados + extra <= maxBordados) return true;
        toast.warning(`Máximo ${maxBordados} bordados por producto (suma de cantidades). Ya van ${usados}.`);
        return false;
    };

    const grupos = useMemo(() => {
        const q = filtro.trim().toLowerCase();
        const mapa = new Map<string, UbicacionCatalogo[]>();
        for (const u of ubicaciones) {
            if (q && !u.nombre.toLowerCase().includes(q) && !u.grupo.toLowerCase().includes(q)) continue;
            mapa.set(u.grupo, [...(mapa.get(u.grupo) ?? []), u]);
        }
        return [...mapa];
    }, [ubicaciones, filtro]);

    const fila = (u: UbicacionCatalogo) => catalogo[u.id] ?? filaBase(u);
    const cambiarCatalogo = (u: UbicacionCatalogo, cambios: Partial<FilaCatalogo>) => setCatalogo((c) => ({ ...c, [u.id]: { ...(c[u.id] ?? filaBase(u)), ...cambios } }));
    const cambiarPropia = (id: number, cambios: Partial<FilaPropia>) => setPropias((p) => p.map((f) => (f.id === id ? { ...f, ...cambios } : f)));
    const nombreLogo = (id: number | null) => logos.find((l) => l.id === id)?.nombre ?? null;

    const guardar = () => {
        setIntento(true);
        if (propias.some((f) => !f.nombre.trim())) return toast.error('Cada ubicación personalizada necesita un nombre.');
        if (usados > maxBordados) return toast.error(`Máximo ${maxBordados} bordados por producto.`);
        const lineas: BordadoLinea[] = [
            ...ubicaciones
                .filter((u) => fila(u).marcada)
                .map((u) => {
                    const f = fila(u);
                    return {
                        ubicacion_bordado_id: u.id,
                        nombre_aplicado: u.nombre,
                        logo_id: f.logo_id,
                        logo: nombreLogo(f.logo_id),
                        es_personalizada: false,
                        precio_aplicado: num(f.precio),
                        cantidad: entero(f.cantidad),
                    };
                }),
            ...propias.map((f) => ({
                ubicacion_bordado_id: null,
                nombre_aplicado: f.nombre.trim(),
                logo_id: f.logo_id,
                logo: nombreLogo(f.logo_id),
                es_personalizada: true,
                precio_aplicado: num(f.precio),
                cantidad: entero(f.cantidad),
            })),
        ];
        onGuardar(lineas);
    };

    return (
        <Dialog open onOpenChange={(a) => !a && onCerrar()}>
            <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-3xl">
                <DialogHeader>
                    <DialogTitle>Bordados</DialogTitle>
                    <DialogDescription>
                        {bloque.nombre}
                        {bloque.variante && ` · ${bloque.variante}`}
                    </DialogDescription>
                </DialogHeader>

                <div className="bg-muted/40 flex flex-wrap items-center justify-between gap-2 rounded-lg border p-3 text-sm" aria-live="polite">
                    <span>
                        <strong className={cn('tabular', usados > maxBordados && 'text-destructive')}>
                            {usados} / {maxBordados}
                        </strong>{' '}
                        bordados por prenda
                    </span>
                    <span className="text-right">
                        Recargo por prenda: <strong className="tabular">{formatoUsd(recargo)}</strong>
                        {tasaBcv && (
                            <span className="text-muted-foreground block text-xs tabular">
                                {formatoBs(recargo * tasaBcv.valor)} · Tasa BCV ({formatoFecha(tasaBcv.fecha)})
                            </span>
                        )}
                    </span>
                </div>

                <div className="relative">
                    <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2" />
                    <Input type="search" value={filtro} onChange={(e) => setFiltro(e.target.value)} placeholder="Buscar ubicación…" aria-label="Buscar ubicación" className="pl-8" />
                </div>

                <div className="grid gap-3">
                    {grupos.map(([grupo, lista]) => (
                        <fieldset key={grupo} className="grid gap-2 rounded-lg border p-3">
                            <legend className="px-1 text-sm font-medium">{grupo}</legend>
                            {lista.map((u) => {
                                const f = fila(u);
                                return (
                                    <div key={u.id} className={cn('grid gap-2 rounded-md p-2 sm:grid-cols-[1fr_7rem_5rem_14rem] sm:items-center', f.marcada && 'bg-primary/5')}>
                                        <label className="flex items-center gap-2 text-sm">
                                            <input
                                                type="checkbox"
                                                className="accent-primary size-4"
                                                checked={f.marcada}
                                                onChange={(e) => {
                                                    if (e.target.checked && !cabe(entero(f.cantidad))) return;
                                                    cambiarCatalogo(u, { marcada: e.target.checked });
                                                }}
                                            />
                                            <span className="flex-1">{u.nombre}</span>
                                            <EstadoFila marcada={f.marcada} logo={f.logo_id} />
                                        </label>
                                        {f.marcada && (
                                            <>
                                                <Input
                                                    type="number"
                                                    min={0}
                                                    step="0.01"
                                                    inputMode="decimal"
                                                    value={f.precio}
                                                    onChange={(e) => cambiarCatalogo(u, { precio: e.target.value })}
                                                    aria-label={`Precio de ${u.nombre} ($)`}
                                                    className="tabular"
                                                />
                                                <Input
                                                    type="number"
                                                    min={1}
                                                    inputMode="numeric"
                                                    value={f.cantidad}
                                                    onChange={(e) => {
                                                        const nueva = entero(e.target.value);
                                                        if (nueva > entero(f.cantidad) && !cabe(nueva - entero(f.cantidad))) return;
                                                        cambiarCatalogo(u, { cantidad: e.target.value });
                                                    }}
                                                    aria-label={`Cantidad de ${u.nombre}`}
                                                    className="tabular"
                                                />
                                                <SelectorLogo
                                                    valor={f.logo_id}
                                                    logos={logos}
                                                    onCambiar={(id) => cambiarCatalogo(u, { logo_id: id })}
                                                    onLogoCreado={onLogoCreado}
                                                    url={urls.logos}
                                                    ubicacion={u.nombre}
                                                />
                                            </>
                                        )}
                                    </div>
                                );
                            })}
                        </fieldset>
                    ))}
                    {!grupos.length && (
                        <p className="text-muted-foreground text-center text-sm">
                            {ubicaciones.length ? `Ninguna ubicación coincide con «${filtro}».` : 'No hay ubicaciones de bordado en el catálogo.'}
                        </p>
                    )}
                </div>

                <fieldset className="grid gap-2 rounded-lg border p-3">
                    <legend className="px-1 text-sm font-medium">Ubicaciones personalizadas</legend>
                    {propias.map((f) => (
                        <div key={f.id} className="grid gap-2 sm:grid-cols-[1fr_7rem_5rem_14rem_auto] sm:items-center">
                            <Input
                                value={f.nombre}
                                maxLength={120}
                                placeholder="Nombre de la ubicación"
                                onChange={(e) => cambiarPropia(f.id, { nombre: e.target.value })}
                                aria-label="Nombre de la ubicación personalizada"
                                aria-invalid={intento && !f.nombre.trim() ? true : undefined}
                            />
                            <Input
                                type="number"
                                min={0}
                                step="0.01"
                                inputMode="decimal"
                                value={f.precio}
                                onChange={(e) => cambiarPropia(f.id, { precio: e.target.value })}
                                aria-label={`Precio de ${f.nombre || 'la ubicación personalizada'} ($)`}
                                className="tabular"
                            />
                            <Input
                                type="number"
                                min={1}
                                inputMode="numeric"
                                value={f.cantidad}
                                onChange={(e) => {
                                    const nueva = entero(e.target.value);
                                    if (nueva > entero(f.cantidad) && !cabe(nueva - entero(f.cantidad))) return;
                                    cambiarPropia(f.id, { cantidad: e.target.value });
                                }}
                                aria-label={`Cantidad de ${f.nombre || 'la ubicación personalizada'}`}
                                className="tabular"
                            />
                            <SelectorLogo
                                valor={f.logo_id}
                                logos={logos}
                                onCambiar={(id) => cambiarPropia(f.id, { logo_id: id })}
                                onLogoCreado={onLogoCreado}
                                url={urls.logos}
                                ubicacion={f.nombre || 'la ubicación personalizada'}
                            />
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                onClick={() => setPropias((p) => p.filter((x) => x.id !== f.id))}
                                aria-label={`Quitar ${f.nombre || 'la ubicación personalizada'}`}
                            >
                                <Trash2 />
                            </Button>
                            {intento && !f.nombre.trim() && <p className="text-destructive text-xs sm:col-span-5">Falta el nombre.</p>}
                        </div>
                    ))}
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        className="justify-self-start"
                        onClick={() => {
                            if (cabe(1)) setPropias((p) => [...p, { id: ++secuencia, nombre: '', precio: '0', cantidad: '1', logo_id: null }]);
                        }}
                    >
                        <Plus /> Ubicación personalizada
                    </Button>
                </fieldset>

                <DialogFooter>
                    <Button type="button" variant="ghost" onClick={onCerrar}>
                        Cancelar
                    </Button>
                    <Button type="button" onClick={guardar}>
                        <Scissors /> {usados ? 'Aplicar bordados' : 'Dejar sin bordado'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

function EstadoFila({ marcada, logo }: { marcada: boolean; logo: number | null }) {
    if (!marcada) return <span className="text-muted-foreground text-xs">No incluida</span>;
    return logo ? <span className="text-success text-xs">Completa</span> : <span className="text-muted-foreground text-xs">Sin logo</span>;
}

/** Logo de una ubicación: buscador por nombre o archivo; «Registrar logo» si no está (permiso logos.crear). */
function SelectorLogo({
    valor,
    logos,
    onCambiar,
    onLogoCreado,
    url,
    ubicacion,
}: {
    valor: number | null;
    logos: LogoCatalogo[];
    onCambiar: (id: number | null) => void;
    onLogoCreado: (l: LogoCatalogo) => void;
    url: string;
    ubicacion: string;
}) {
    const { puede } = usePermisos();
    const [alta, setAlta] = useState<string>();
    const elegido = logos.find((l) => l.id === valor);

    if (elegido) {
        return (
            <span className="bg-muted flex min-w-0 items-center gap-1 rounded-md px-2 py-1 text-sm">
                <span className="truncate" title={elegido.archivo ?? undefined}>
                    {elegido.nombre}
                </span>
                <Button type="button" variant="ghost" size="icon" className="ml-auto size-6" onClick={() => onCambiar(null)} aria-label={`Quitar el logo de ${ubicacion}`}>
                    <X />
                </Button>
            </span>
        );
    }
    return (
        <>
            <Buscador<LogoCatalogo>
                etiqueta={`Logo de ${ubicacion}`}
                placeholder="Logo (opcional)…"
                buscarVacio
                buscar={(q) => {
                    const k = q.toLowerCase();
                    return logos.filter((l) => !k || l.nombre.toLowerCase().includes(k) || (l.archivo ?? '').toLowerCase().includes(k)).slice(0, 20);
                }}
                clave={(l) => l.id}
                opcion={(l) => (
                    <span className="flex items-baseline justify-between gap-2">
                        <span className="truncate">{l.nombre}</span>
                        {l.archivo && <span className="text-muted-foreground shrink-0 text-xs">{l.archivo}</span>}
                    </span>
                )}
                onElegir={(l) => onCambiar(l.id)}
                vacio={(q) => (q ? 'Ningún logo coincide.' : 'No hay logos registrados.')}
                pie={(q) =>
                    puede('logos.crear') ? (
                        <Button type="button" variant="ghost" size="sm" className="w-full justify-start" onMouseDown={(e) => e.preventDefault()} onClick={() => setAlta(q)}>
                            <Plus /> Registrar logo{q && ` «${q}»`}
                        </Button>
                    ) : null
                }
            />
            {alta !== undefined && (
                <AltaLogo
                    nombreInicial={alta}
                    url={url}
                    onCerrar={() => setAlta(undefined)}
                    onCreado={(l) => {
                        onLogoCreado(l);
                        onCambiar(l.id);
                        setAlta(undefined);
                    }}
                />
            )}
        </>
    );
}

function AltaLogo({ nombreInicial, url, onCerrar, onCreado }: { nombreInicial: string; url: string; onCerrar: () => void; onCreado: (l: LogoCatalogo) => void }) {
    const [nombre, setNombre] = useState(nombreInicial);
    const [archivo, setArchivo] = useState('');
    const [errores, setErrores] = useState<Record<string, string>>({});
    const [enviando, setEnviando] = useState(false);

    const guardar = async () => {
        setEnviando(true);
        const r = await postJson<{ logo: { id: number; name: string; original_filename: string | null } }>(url, { name: nombre.trim(), original_filename: archivo.trim() || null });
        setEnviando(false);
        if (!r.ok) {
            setErrores(r.errores);
            if (!Object.keys(r.errores).length) toast.error(r.mensaje);
            return;
        }
        toast.success(`Logo «${r.datos.logo.name}» registrado.`);
        onCreado({ id: r.datos.logo.id, nombre: r.datos.logo.name, archivo: r.datos.logo.original_filename });
    };

    return (
        <Dialog open onOpenChange={(a) => !a && onCerrar()}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Registrar logo</DialogTitle>
                    <DialogDescription>Queda elegido para esta ubicación.</DialogDescription>
                </DialogHeader>
                <Campo etiqueta="Nombre" requerido error={errores.name}>
                    <Input value={nombre} maxLength={120} onChange={(e) => setNombre(e.target.value)} autoFocus />
                </Campo>
                <Campo etiqueta="Archivo" error={errores.original_filename} ayuda="Si lo dejas vacío: «<nombre>.emb».">
                    <Input value={archivo} maxLength={150} placeholder="logo.emb" onChange={(e) => setArchivo(e.target.value)} />
                </Campo>
                <DialogFooter>
                    <Button type="button" variant="ghost" onClick={onCerrar}>
                        Cancelar
                    </Button>
                    <Button type="button" onClick={guardar} disabled={!nombre.trim() || enviando}>
                        Registrar logo
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
