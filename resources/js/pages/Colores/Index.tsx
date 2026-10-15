import { useForm } from '@inertiajs/react';
import { useId } from 'react';

import { Campo } from '@/components/app/campo';
import { DialogoFormulario } from '@/components/app/dialogo-formulario';
import { PaginaCatalogo } from '@/components/app/pagina-catalogo';
import { Input } from '@/components/ui/input';
import type { Paginado } from '@/types';

/** Espejo de ColorController::index() (lo verifica CatalogosPaginaTest). */
export interface Color {
    id: number;
    nombre: string;
    hex: string;
    grupo: string | null;
    inhabilitado: boolean;
}

interface Props {
    registros: Paginado<Color>;
    filtros: { buscar?: string; historial?: string };
    grupos: string[];
    urls: { index: string };
}

const Muestra = ({ hex }: { hex: string }) => (
    <span className="border-border inline-block size-6 shrink-0 rounded-md border" style={{ background: hex }} aria-hidden />
);

export default function ColoresIndex({ registros, filtros, grupos, urls }: Props) {
    return (
        <PaginaCatalogo
            titulo="Colores"
            recurso="color"
            permiso="colores.gestionar"
            registros={registros}
            filtros={filtros}
            url={urls.index}
            avisoInhabilitar="Deja de ofrecerse en cotizaciones y pedidos nuevos. Los documentos existentes no cambian."
            columnas={[
                {
                    id: 'nombre',
                    encabezado: 'Color',
                    celda: (c) => (
                        <span className="flex items-center gap-2.5">
                            <Muestra hex={c.hex} />
                            <span className="font-medium">{c.nombre}</span>
                        </span>
                    ),
                },
                { id: 'hex', encabezado: 'HEX', celda: (c) => <code className="text-muted-foreground font-mono text-xs">{c.hex}</code> },
                { id: 'grupo', encabezado: 'Grupo', celda: (c) => <span className="text-muted-foreground">{c.grupo ?? '—'}</span> },
            ]}
            formulario={(p) => <Formulario key={p.apertura} {...p} url={urls.index} grupos={grupos} />}
        />
    );
}

function Formulario({ abierto, registro, onCerrar, url, grupos }: { abierto: boolean; registro?: Color; onCerrar: () => void; url: string; grupos: string[] }) {
    const form = useForm({ nombre: registro?.nombre ?? '', hex_referencial: registro?.hex ?? '#1E3C72', grupo: registro?.grupo ?? '' });
    const opciones = { preserveScroll: true, onSuccess: onCerrar };
    const idGrupos = useId();
    const hexValido = /^#[0-9A-Fa-f]{6}$/.test(form.data.hex_referencial);

    return (
        <DialogoFormulario
            abierto={abierto}
            onCerrar={onCerrar}
            titulo={registro ? 'Editar color' : 'Agregar color'}
            sucio={form.isDirty}
            procesando={form.processing}
            textoGuardar={registro ? 'Guardar cambios' : 'Agregar color'}
            onGuardar={() => (registro ? form.put(`${url}/${registro.id}`, opciones) : form.post(url, opciones))}
        >
            <Campo etiqueta="Nombre" requerido error={form.errors.nombre}>
                <Input value={form.data.nombre} maxLength={100} onChange={(e) => form.setData('nombre', e.target.value)} />
            </Campo>
            <Campo etiqueta="Color HEX" requerido error={form.errors.hex_referencial} ayuda="Formato #RRGGBB. Es una referencia visual.">
                {(control) => (
                    <div className="flex items-center gap-2">
                        <input
                            type="color"
                            aria-label="Elegir color"
                            value={hexValido ? form.data.hex_referencial : '#000000'}
                            onChange={(e) => form.setData('hex_referencial', e.target.value.toUpperCase())}
                            className="border-input size-9 shrink-0 cursor-pointer rounded-md border bg-transparent p-0.5"
                        />
                        <Input
                            {...control}
                            value={form.data.hex_referencial}
                            maxLength={7}
                            onChange={(e) => form.setData('hex_referencial', e.target.value.toUpperCase())}
                            className="font-mono"
                        />
                    </div>
                )}
            </Campo>
            <Campo etiqueta="Grupo" error={form.errors.grupo} ayuda="Agrupa los colores en el selector de cotizaciones (p. ej. Azules).">
                {(control) => (
                    <>
                        <Input {...control} value={form.data.grupo} maxLength={100} list={idGrupos} onChange={(e) => form.setData('grupo', e.target.value)} />
                        <datalist id={idGrupos}>
                            {grupos.map((g) => (
                                <option key={g} value={g} />
                            ))}
                        </datalist>
                    </>
                )}
            </Campo>
        </DialogoFormulario>
    );
}
