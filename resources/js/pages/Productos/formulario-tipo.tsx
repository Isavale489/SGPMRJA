import { useForm } from '@inertiajs/react';
import { ArrowDown, ArrowUp, ImagePlus, Plus, Search, X } from 'lucide-react';
import { useMemo, useState } from 'react';

import { Campo } from '@/components/app/campo';
import { DialogoFormulario } from '@/components/app/dialogo-formulario';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';

import type { CatalogoTipos, TipoProductoFila } from './Index';

interface Formulario {
    nombre: string;
    prefijo: string;
    descripcion: string;
    precio_confeccion: string;
    requiere_tela: boolean;
    requiere_produccion: boolean;
    consumo_tela_por_unidad: string;
    imagen: File | null;
    /** En el orden en que se muestran: el índice + 1 es el orden del SKU. */
    atributos: number[];
    telas: number[];
    insumos: { id: number; cantidad: string }[];
}

interface Props {
    abierto: boolean;
    registro?: TipoProductoFila;
    onCerrar: () => void;
    catalogo: CatalogoTipos;
    /** Base de /tipo-productos. */
    url: string;
}

/**
 * Alta/edición de un tipo de producto. El prefijo forma parte del SKU: se fija
 * al crear y queda de solo lectura (el servidor lo ignora en edición).
 */
export function FormularioTipo({ abierto, registro, onCerrar, catalogo, url }: Props) {
    const form = useForm<Formulario>({
        nombre: registro?.nombre ?? '',
        prefijo: registro?.prefijo ?? '',
        descripcion: registro?.descripcion ?? '',
        precio_confeccion: registro ? String(registro.precio_confeccion) : '',
        requiere_tela: registro?.requiere_tela ?? true,
        requiere_produccion: registro?.requiere_produccion ?? true,
        consumo_tela_por_unidad: registro ? String(registro.consumo_tela_por_unidad) : '',
        imagen: null,
        atributos: registro?.atributos.slice().sort((a, b) => a.orden - b.orden).map((a) => a.id) ?? [],
        telas: registro?.telas.map((t) => t.id) ?? [],
        insumos: registro?.insumos.map((i) => ({ id: i.id, cantidad: String(i.cantidad) })) ?? [],
    });
    const { data, setData, errors } = form;
    const e = errors as Record<string, string | undefined>;
    const primerError = (prefijo: string) => Object.entries(e).find(([k]) => k.startsWith(prefijo))?.[1];

    const [vistaPrevia, setVistaPrevia] = useState<string | null>(registro?.imagen ?? null);
    const [buscarTela, setBuscarTela] = useState('');

    const telasFiltradas = useMemo(() => {
        const q = buscarTela.trim().toLowerCase();
        return q ? catalogo.telas.filter((t) => `${t.nombre} ${t.codigo ?? ''}`.toLowerCase().includes(q)) : catalogo.telas;
    }, [buscarTela, catalogo.telas]);

    const atributo = (id: number) => catalogo.atributos.find((a) => a.id === id);
    const insumo = (id: number) => catalogo.insumos.find((i) => i.id === id);
    const moverAtributo = (i: number, delta: number) => {
        const lista = [...data.atributos];
        const destino = i + delta;
        if (destino < 0 || destino >= lista.length) return;
        [lista[i], lista[destino]] = [lista[destino]!, lista[i]!];
        setData('atributos', lista);
    };

    // Estado del formulario → payload de GuardarTipoProductoRequest.
    form.transform((d) => ({
        nombre: d.nombre,
        ...(registro ? { _method: 'put' } : { prefijo: d.prefijo }),
        descripcion: d.descripcion || null,
        precio_confeccion: d.precio_confeccion === '' ? 0 : d.precio_confeccion,
        requiere_tela: d.requiere_tela,
        requiere_produccion: d.requiere_produccion,
        consumo_tela_por_unidad: d.requiere_tela ? (d.consumo_tela_por_unidad === '' ? 0 : d.consumo_tela_por_unidad) : 0,
        ...(d.imagen ? { imagen: d.imagen } : {}),
        atributos: d.atributos.map((id, i) => ({ id, orden: i + 1 })),
        telas: d.requiere_tela ? d.telas : [],
        insumos_default: d.insumos.filter((x) => x.cantidad !== '').map((x) => ({ id: x.id, cantidad_estimada: x.cantidad })),
    }));

    // Con archivo, PUT va como POST + _method (PHP no lee multipart en PUT).
    const guardar = () =>
        form.post(registro ? `${url}/${registro.id}` : url, { preserveScroll: true, forceFormData: true, onSuccess: onCerrar });

    const atributosDisponibles = catalogo.atributos.filter((a) => !data.atributos.includes(a.id));
    const insumosDisponibles = catalogo.insumos.filter((i) => !data.insumos.some((x) => x.id === i.id));

    return (
        <DialogoFormulario
            abierto={abierto}
            onCerrar={onCerrar}
            titulo={registro ? 'Editar tipo de producto' : 'Agregar tipo de producto'}
            descripcion="El tipo es la unidad del catálogo: las variantes (tela y atributos) se eligen al cotizar."
            sucio={form.isDirty}
            procesando={form.processing}
            textoGuardar={registro ? 'Guardar cambios' : 'Agregar tipo'}
            onGuardar={guardar}
            className="max-h-[92svh] overflow-y-auto sm:max-w-2xl"
        >
            <section className="grid gap-4 sm:grid-cols-[1fr_8rem]">
                <Campo etiqueta="Nombre" requerido error={e.nombre}>
                    <Input value={data.nombre} maxLength={100} onChange={(ev) => setData('nombre', ev.target.value)} />
                </Campo>
                <Campo
                    etiqueta="Prefijo"
                    requerido
                    error={e.prefijo}
                    ayuda={registro ? 'Fijo: forma parte del SKU.' : 'Letras, hasta 5. Inicia el SKU.'}
                >
                    <Input
                        value={data.prefijo}
                        maxLength={5}
                        readOnly={Boolean(registro)}
                        onChange={(ev) => setData('prefijo', ev.target.value.replace(/[^a-zA-Z]/g, '').toUpperCase())}
                        className="font-mono uppercase"
                    />
                </Campo>
            </section>

            <Campo etiqueta="Descripción" error={e.descripcion}>
                <Textarea rows={2} maxLength={500} value={data.descripcion} onChange={(ev) => setData('descripcion', ev.target.value)} />
            </Campo>

            <section className="grid gap-4 sm:grid-cols-2">
                <Campo etiqueta="Precio de confección (USD)" error={e.precio_confeccion} ayuda="Se suma al costo de la tela para sugerir el precio.">
                    <Input type="number" min={0} step="0.01" inputMode="decimal" value={data.precio_confeccion} onChange={(ev) => setData('precio_confeccion', ev.target.value)} className="tabular" />
                </Campo>
                <Campo etiqueta="Imagen" error={e.imagen}>
                    {(control) => (
                        <label className="border-input hover:bg-accent flex h-9 cursor-pointer items-center gap-2 rounded-md border px-2 text-sm transition-colors duration-rapido">
                            {vistaPrevia ? <img src={vistaPrevia} alt="" className="size-6 rounded object-cover" /> : <ImagePlus className="text-muted-foreground size-4" />}
                            <span className="text-muted-foreground truncate">{data.imagen?.name ?? (vistaPrevia ? 'Cambiar imagen' : 'Elegir imagen')}</span>
                            <input
                                {...control}
                                type="file"
                                accept="image/*"
                                className="sr-only"
                                onChange={(ev) => {
                                    const f = ev.target.files?.[0] ?? null;
                                    setData('imagen', f);
                                    setVistaPrevia(f ? URL.createObjectURL(f) : (registro?.imagen ?? null));
                                }}
                            />
                        </label>
                    )}
                </Campo>
            </section>

            <section className="grid gap-3 sm:grid-cols-2">
                <label className="flex items-center justify-between gap-3 rounded-md border px-3 py-2 text-sm">
                    <span>
                        Se fabrica
                        <span className="text-muted-foreground block text-xs">Sin esto es reventa: no genera órdenes de producción.</span>
                    </span>
                    <Switch checked={data.requiere_produccion} onCheckedChange={(v) => setData('requiere_produccion', v)} aria-label="Se fabrica" />
                </label>
                <label className="flex items-center justify-between gap-3 rounded-md border px-3 py-2 text-sm">
                    <span>
                        Lleva tela
                        <span className="text-muted-foreground block text-xs">La tela se elige al cotizar, entre las permitidas.</span>
                    </span>
                    <Switch checked={data.requiere_tela} onCheckedChange={(v) => setData('requiere_tela', v)} aria-label="Lleva tela" />
                </label>
            </section>

            {data.requiere_tela && (
                <section className="grid gap-3 rounded-md border p-3">
                    <Campo etiqueta="Consumo de tela por unidad" error={e.consumo_tela_por_unidad} ayuda="Se usa para calcular la tela de cada orden de producción.">
                        <Input type="number" min={0} step="0.01" inputMode="decimal" value={data.consumo_tela_por_unidad} onChange={(ev) => setData('consumo_tela_por_unidad', ev.target.value)} className="tabular sm:max-w-40" />
                    </Campo>
                    <fieldset className="grid gap-2">
                        <legend className="mb-1 text-sm font-medium">
                            Telas permitidas <span className="text-muted-foreground font-normal">({data.telas.length})</span>
                        </legend>
                        <div className="relative">
                            <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2" />
                            <Input type="search" value={buscarTela} onChange={(ev) => setBuscarTela(ev.target.value)} placeholder="Buscar tela…" aria-label="Buscar tela" className="pl-8" />
                        </div>
                        <div className="grid max-h-40 gap-1 overflow-y-auto">
                            {telasFiltradas.length === 0 && <p className="text-muted-foreground px-1 text-sm">No hay telas que coincidan.</p>}
                            {telasFiltradas.map((t) => (
                                <label key={t.id} className="hover:bg-accent flex cursor-pointer items-center gap-2 rounded px-1.5 py-1 text-sm">
                                    <input
                                        type="checkbox"
                                        className="accent-primary size-4"
                                        checked={data.telas.includes(t.id)}
                                        onChange={(ev) => setData('telas', ev.target.checked ? [...data.telas, t.id] : data.telas.filter((x) => x !== t.id))}
                                    />
                                    <span className="flex-1">{t.nombre}</span>
                                    {t.codigo && <code className="text-muted-foreground font-mono text-xs">{t.codigo}</code>}
                                </label>
                            ))}
                        </div>
                        {primerError('telas') && <p className="text-destructive text-xs">{primerError('telas')}</p>}
                    </fieldset>
                </section>
            )}

            <fieldset className="grid gap-2">
                <legend className="mb-1 text-sm font-medium">Atributos de la variante</legend>
                <p className="text-muted-foreground -mt-1 text-xs">El orden de la lista es el orden de los códigos en el SKU.</p>
                {data.atributos.map((id, i) => (
                    <div key={id} className="flex items-center gap-2 rounded-md border px-2 py-1.5 text-sm">
                        <span className="text-muted-foreground w-5 text-right tabular">{i + 1}.</span>
                        <span className="flex-1">{atributo(id)?.nombre ?? `#${id}`}</span>
                        <code className="text-muted-foreground font-mono text-xs">{atributo(id)?.codigo}</code>
                        <Button type="button" variant="ghost" size="icon" disabled={i === 0} onClick={() => moverAtributo(i, -1)} aria-label={`Subir ${atributo(id)?.nombre}`}><ArrowUp /></Button>
                        <Button type="button" variant="ghost" size="icon" disabled={i === data.atributos.length - 1} onClick={() => moverAtributo(i, 1)} aria-label={`Bajar ${atributo(id)?.nombre}`}><ArrowDown /></Button>
                        <Button type="button" variant="ghost" size="icon" onClick={() => setData('atributos', data.atributos.filter((x) => x !== id))} aria-label={`Quitar ${atributo(id)?.nombre}`}><X /></Button>
                    </div>
                ))}
                {atributosDisponibles.length > 0 && (
                    <Select value="" onValueChange={(v) => setData('atributos', [...data.atributos, Number(v)])}>
                        <SelectTrigger className="w-full sm:w-64" aria-label="Agregar atributo">
                            <SelectValue placeholder="Agregar atributo…" />
                        </SelectTrigger>
                        <SelectContent>
                            {atributosDisponibles.map((a) => (
                                <SelectItem key={a.id} value={String(a.id)}>{a.nombre} ({a.valores} valores)</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                )}
                {primerError('atributos') && <p className="text-destructive text-xs">{primerError('atributos')}</p>}
            </fieldset>

            <fieldset className="grid gap-2">
                <legend className="mb-1 text-sm font-medium">Insumos por unidad</legend>
                <p className="text-muted-foreground -mt-1 text-xs">Lo constante de cada prenda (hilo, botones, etiquetas). La tela no va aquí: depende de la variante.</p>
                {data.insumos.map((x, i) => (
                    <div key={x.id} className="flex items-center gap-2 text-sm">
                        <span className="flex-1 truncate">{insumo(x.id)?.nombre ?? `#${x.id}`}</span>
                        <Input
                            type="number"
                            min={0}
                            step="0.01"
                            inputMode="decimal"
                            value={x.cantidad}
                            aria-label={`Cantidad de ${insumo(x.id)?.nombre}`}
                            onChange={(ev) => setData('insumos', data.insumos.map((y, j) => (j === i ? { ...y, cantidad: ev.target.value } : y)))}
                            className="tabular w-28"
                        />
                        <span className="text-muted-foreground w-16 text-xs">{insumo(x.id)?.unidad}</span>
                        <Button type="button" variant="ghost" size="icon" onClick={() => setData('insumos', data.insumos.filter((_, j) => j !== i))} aria-label={`Quitar ${insumo(x.id)?.nombre}`}><X /></Button>
                    </div>
                ))}
                {insumosDisponibles.length > 0 && (
                    <Select value="" onValueChange={(v) => setData('insumos', [...data.insumos, { id: Number(v), cantidad: '1' }])}>
                        <SelectTrigger className="w-full sm:w-64" aria-label="Agregar insumo">
                            <span className="text-muted-foreground flex items-center gap-1.5"><Plus className="size-4" /> Agregar insumo…</span>
                        </SelectTrigger>
                        <SelectContent>
                            {insumosDisponibles.map((i) => (
                                <SelectItem key={i.id} value={String(i.id)}>{i.nombre}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                )}
                {primerError('insumos_default') && <p className="text-destructive text-xs">{primerError('insumos_default')}</p>}
            </fieldset>
        </DialogoFormulario>
    );
}
