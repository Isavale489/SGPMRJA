import { useForm } from '@inertiajs/react';
import { Check } from 'lucide-react';

import { Campo } from '@/components/app/campo';
import { DialogoFormulario } from '@/components/app/dialogo-formulario';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';

import type { AtributoFila, Opcion, ValorFila } from './tipos';

interface PropsDialogo {
    abierto: boolean;
    onCerrar: () => void;
}

const AYUDA_CODIGO = 'Solo letras mayúsculas y números. No se puede cambiar después: forma el SKU.';

/** Código: se escribe en mayúsculas; en la edición se muestra sin poder cambiarlo. */
function CampoCodigo({ valor, editando, error, maximo, onCambiar }: {
    valor: string; editando: boolean; error?: string; maximo: number; onCambiar: (v: string) => void;
}) {
    return (
        <Campo etiqueta="Código" requerido={!editando} error={error} ayuda={editando ? 'Inmutable: forma el SKU de los productos.' : AYUDA_CODIGO}>
            <Input
                value={valor}
                maxLength={maximo}
                readOnly={editando}
                autoCapitalize="characters"
                className="font-mono uppercase read-only:bg-muted read-only:text-muted-foreground"
                onChange={(e) => onCambiar(e.target.value.toUpperCase())}
            />
        </Campo>
    );
}

export function FormularioAtributo({ abierto, onCerrar, atributo, tiposProducto, url }: PropsDialogo & {
    atributo?: AtributoFila; tiposProducto: Opcion[]; url: string;
}) {
    const form = useForm({
        nombre: atributo?.nombre ?? '',
        codigo: atributo?.codigo ?? '',
        descripcion: atributo?.descripcion ?? '',
        tipos_producto: atributo?.tipos_producto_ids ?? [],
    });
    const opciones = { preserveScroll: true, onSuccess: onCerrar };
    const alternarTipo = (id: number) =>
        form.setData('tipos_producto', form.data.tipos_producto.includes(id)
            ? form.data.tipos_producto.filter((t) => t !== id)
            : [...form.data.tipos_producto, id]);

    return (
        <DialogoFormulario
            abierto={abierto}
            onCerrar={onCerrar}
            titulo={atributo ? 'Editar atributo' : 'Agregar atributo'}
            descripcion="Característica de confección que varía entre productos (manga, cuello, corte…)."
            sucio={form.isDirty}
            procesando={form.processing}
            textoGuardar={atributo ? 'Guardar cambios' : 'Agregar atributo'}
            onGuardar={() => (atributo ? form.put(`${url}/${atributo.id}`, opciones) : form.post(url, opciones))}
            className="sm:max-w-lg"
        >
            <Campo etiqueta="Nombre" requerido error={form.errors.nombre}>
                <Input value={form.data.nombre} maxLength={80} placeholder="Ej.: Manga" onChange={(e) => form.setData('nombre', e.target.value)} />
            </Campo>
            <CampoCodigo valor={form.data.codigo} editando={Boolean(atributo)} error={form.errors.codigo} maximo={8} onCambiar={(v) => form.setData('codigo', v)} />
            <Campo etiqueta="Descripción" error={form.errors.descripcion}>
                <Textarea value={form.data.descripcion} maxLength={191} rows={2} placeholder="Opcional" onChange={(e) => form.setData('descripcion', e.target.value)} />
            </Campo>
            <fieldset className="grid gap-2">
                <legend className="mb-1.5 text-sm font-medium">Tipos de producto</legend>
                {tiposProducto.length === 0 ? (
                    <p className="text-muted-foreground text-sm">No hay tipos de producto registrados.</p>
                ) : (
                    <div className="flex flex-wrap gap-1.5">
                        {tiposProducto.map((t) => {
                            const activo = form.data.tipos_producto.includes(t.id);
                            return (
                                <button
                                    key={t.id}
                                    type="button"
                                    aria-pressed={activo}
                                    onClick={() => alternarTipo(t.id)}
                                    className={cn(
                                        'focus-visible:ring-ring/50 inline-flex items-center gap-1 rounded-full border px-3 py-1 text-sm transition-colors duration-rapido outline-none focus-visible:ring-[3px]',
                                        activo ? 'border-primary bg-primary/10 text-primary' : 'border-border text-muted-foreground hover:bg-muted',
                                    )}
                                >
                                    {activo && <Check className="size-3.5" />}
                                    {t.nombre}
                                </button>
                            );
                        })}
                    </div>
                )}
                <p className="text-muted-foreground text-xs">Los tipos marcados ofrecen este atributo al configurar sus variantes.</p>
            </fieldset>
        </DialogoFormulario>
    );
}

export function FormularioValor({ abierto, onCerrar, atributo, valor, url }: PropsDialogo & {
    atributo: AtributoFila; valor?: ValorFila; url: string;
}) {
    const form = useForm({ nombre: valor?.nombre ?? '', codigo: valor?.codigo ?? '' });
    const opciones = { preserveScroll: true, onSuccess: onCerrar };
    const base = `${url}/${atributo.id}/valores`;

    return (
        <DialogoFormulario
            abierto={abierto}
            onCerrar={onCerrar}
            titulo={valor ? 'Editar valor' : 'Agregar valor'}
            descripcion={`Opción del atributo «${atributo.nombre}». Nombre y código no se repiten dentro del atributo.`}
            sucio={form.isDirty}
            procesando={form.processing}
            textoGuardar={valor ? 'Guardar cambios' : 'Agregar valor'}
            onGuardar={() => (valor ? form.put(`${base}/${valor.id}`, opciones) : form.post(base, opciones))}
        >
            <Campo etiqueta="Nombre" requerido error={form.errors.nombre}>
                <Input value={form.data.nombre} maxLength={80} placeholder="Ej.: Larga" onChange={(e) => form.setData('nombre', e.target.value)} />
            </Campo>
            <CampoCodigo valor={form.data.codigo} editando={Boolean(valor)} error={form.errors.codigo} maximo={8} onCambiar={(v) => form.setData('codigo', v)} />
        </DialogoFormulario>
    );
}
