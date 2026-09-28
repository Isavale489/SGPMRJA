import { useForm } from '@inertiajs/react';
import { useState } from 'react';

import { Campo } from '@/components/app/campo';
import { DialogoFormulario } from '@/components/app/dialogo-formulario';
import { Monto } from '@/components/app/monto';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';

import type { InsumoFila, PaginaInsumos } from './tipos';

interface Props {
    abierto: boolean;
    onCerrar: () => void;
    insumo?: InsumoFila;
    tipos: string[];
    unidades: string[];
    urls: PaginaInsumos['urls'];
}

const texto = (n?: number) => (n === undefined ? '' : String(n));

/** Interruptor con su etiqueta y explicación (inventariable, IVA). */
function Interruptor({ id, etiqueta, ayuda, valor, onCambiar }: { id: string; etiqueta: string; ayuda: string; valor: boolean; onCambiar: (v: boolean) => void }) {
    return (
        <div className="flex items-start gap-3">
            <Switch id={id} checked={valor} onCheckedChange={onCambiar} className="mt-0.5" aria-describedby={`${id}-ayuda`} />
            <div className="grid gap-0.5">
                <Label htmlFor={id}>{etiqueta}</Label>
                <p id={`${id}-ayuda`} className="text-muted-foreground text-xs">{ayuda}</p>
            </div>
        </div>
    );
}

/** Montar con una `key` distinta en cada apertura (ver docs/conventions/frontend.md). */
export function FormularioInsumo({ abierto, onCerrar, insumo, tipos, unidades, urls }: Props) {
    const form = useForm({
        nombre: insumo?.nombre ?? '',
        codigo: insumo?.codigo ?? '',
        tipo: insumo?.tipo ?? '',
        unidad_medida: insumo?.unidad_medida ?? '',
        costo_unitario: texto(insumo?.costo_unitario),
        aplica_iva: insumo?.aplica_iva ?? true,
        is_inventoriable: insumo?.is_inventoriable ?? true,
        stock_minimo: insumo ? texto(insumo.stock_minimo) : '0',
        stock_actual: insumo ? texto(insumo.stock_actual) : '0',
        stock_maximo: insumo ? texto(insumo.stock_maximo) : '0',
    });
    const { data, setData, errors } = form;
    const [nombreRepetido, setNombreRepetido] = useState(false);
    const codigoFijo = Boolean(insumo?.codigo); // inmutable una vez asignado
    const opciones = { preserveScroll: true, onSuccess: onCerrar };
    // Un tipo ya asignado que luego se desactivó sigue visible para no dejar el select vacío.
    const opcionesTipo = data.tipo && !tipos.includes(data.tipo) ? [data.tipo, ...tipos] : tipos;

    // Aviso (no bloquea): el servidor no exige nombre único.
    const revisarNombre = async () => {
        const nombre = data.nombre.trim();
        if (nombre.length < 3) return setNombreRepetido(false);
        const params = new URLSearchParams({ nombre });
        if (insumo) params.set('exclude_id', String(insumo.id));
        try {
            const r = await fetch(`${urls.checkNombre}?${params}`, { headers: { Accept: 'application/json' } });
            setNombreRepetido(((await r.json()) as { exists: boolean }).exists);
        } catch {
            // sin red: no es un error
        }
    };

    return (
        <DialogoFormulario
            abierto={abierto}
            onCerrar={onCerrar}
            titulo={insumo ? 'Editar insumo' : 'Agregar insumo'}
            sucio={form.isDirty}
            procesando={form.processing}
            textoGuardar={insumo ? 'Guardar cambios' : 'Agregar insumo'}
            onGuardar={() => (insumo ? form.put(`${urls.index}/${insumo.id}`, opciones) : form.post(urls.index, opciones))}
            className="max-h-[92svh] overflow-y-auto sm:max-w-2xl"
        >
            <div className="grid gap-4 sm:grid-cols-[1fr_10rem]">
                <Campo etiqueta="Nombre" requerido error={errors.nombre} ayuda={nombreRepetido ? 'Ya existe un insumo con ese nombre.' : undefined}>
                    <Input value={data.nombre} maxLength={100} onChange={(e) => setData('nombre', e.target.value)} onBlur={revisarNombre} />
                </Campo>
                <Campo etiqueta="Código" error={errors.codigo} ayuda={codigoFijo ? 'No se puede cambiar.' : 'Opcional. Letras y números.'}>
                    <Input
                        value={data.codigo}
                        maxLength={8}
                        readOnly={codigoFijo}
                        autoCapitalize="characters"
                        className="font-mono uppercase read-only:bg-muted read-only:text-muted-foreground"
                        onChange={(e) => setData('codigo', e.target.value.toUpperCase())}
                    />
                </Campo>
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
                <Campo etiqueta="Tipo" requerido error={errors.tipo}>
                    {(control) => (
                        <Select value={data.tipo || undefined} onValueChange={(v) => setData('tipo', v)}>
                            <SelectTrigger {...control} className="w-full"><SelectValue placeholder="Selecciona un tipo" /></SelectTrigger>
                            <SelectContent>
                                {opcionesTipo.map((t) => (
                                    <SelectItem key={t} value={t}>{t}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    )}
                </Campo>
                <Campo etiqueta="Unidad de medida" requerido error={errors.unidad_medida}>
                    {(control) => (
                        <Select value={data.unidad_medida || undefined} onValueChange={(v) => setData('unidad_medida', v)}>
                            <SelectTrigger {...control} className="w-full"><SelectValue placeholder="Selecciona una unidad" /></SelectTrigger>
                            <SelectContent>
                                {unidades.map((u) => (
                                    <SelectItem key={u} value={u}>{u}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    )}
                </Campo>
                <Campo etiqueta="Costo unitario ($)" requerido error={errors.costo_unitario}>
                    <Input type="number" inputMode="decimal" min={0} step="0.01" value={data.costo_unitario} onChange={(e) => setData('costo_unitario', e.target.value)} className="tabular" />
                </Campo>
                <div className="grid content-end pb-1">
                    {Number(data.costo_unitario) > 0 && <Monto usd={Number(data.costo_unitario)} />}
                </div>
            </div>

            <div className="border-border grid gap-4 rounded-lg border p-4">
                <Interruptor
                    id="aplica-iva"
                    etiqueta="Gravable con IVA"
                    ayuda="Desmárcalo si el insumo es exento."
                    valor={data.aplica_iva}
                    onCambiar={(v) => setData('aplica_iva', v)}
                />
                <Interruptor
                    id="inventariable"
                    etiqueta="Inventariable"
                    ayuda="Lleva existencias y movimientos. Si no lo es, no se descuenta del inventario."
                    valor={data.is_inventoriable}
                    onCambiar={(v) => setData('is_inventoriable', v)}
                />
                {data.is_inventoriable && (
                    <div className="grid gap-4 sm:grid-cols-3">
                        <Campo etiqueta="Existencia mínima" error={errors.stock_minimo}>
                            <Input type="number" inputMode="decimal" min={0} step="0.01" value={data.stock_minimo} onChange={(e) => setData('stock_minimo', e.target.value)} className="tabular" />
                        </Campo>
                        <Campo etiqueta="Existencia actual" error={errors.stock_actual} ayuda={insumo ? 'Las entradas llegan por Compras y Producción.' : undefined}>
                            <Input type="number" inputMode="decimal" min={0} step="0.01" value={data.stock_actual} onChange={(e) => setData('stock_actual', e.target.value)} className="tabular" />
                        </Campo>
                        <Campo etiqueta="Existencia máxima" error={errors.stock_maximo}>
                            <Input type="number" inputMode="decimal" min={0} step="0.01" value={data.stock_maximo} onChange={(e) => setData('stock_maximo', e.target.value)} className="tabular" />
                        </Campo>
                    </div>
                )}
            </div>
        </DialogoFormulario>
    );
}
