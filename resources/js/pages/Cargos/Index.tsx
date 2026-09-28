import { useForm } from '@inertiajs/react';

import { Campo } from '@/components/app/campo';
import { DialogoFormulario } from '@/components/app/dialogo-formulario';
import { PaginaCatalogo } from '@/components/app/pagina-catalogo';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { formatoNumero } from '@/lib/formato';
import type { Paginado } from '@/types';

/** Espejo de CargoController::index() (lo verifica CatalogosPaginaTest). */
export interface Cargo {
    id: number;
    nombre: string;
    departamento_id: number;
    departamento: string | null;
    empleados: number;
    inhabilitado: boolean;
}

type Filtros = { buscar?: string; departamento?: string; historial?: string };
const TODOS = 'todos';

interface Props {
    registros: Paginado<Cargo>;
    filtros: Filtros;
    departamentos: { id: number; nombre: string }[];
    urls: { index: string };
}

export default function CargosIndex({ registros, filtros, departamentos, urls }: Props) {
    return (
        <PaginaCatalogo
            titulo="Cargos"
            recurso="cargo"
            permiso="cargos.gestionar"
            registros={registros}
            filtros={filtros}
            url={urls.index}
            avisoInhabilitar="Solo se puede inhabilitar si no tiene empleados. Pasa al historial y se puede restaurar."
            filtrosExtra={(f, cambiar) => (
                <Select value={f.departamento ?? TODOS} onValueChange={(v) => cambiar('departamento', v === TODOS ? undefined : v)}>
                    <SelectTrigger className="w-52" aria-label="Filtrar por departamento"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value={TODOS}>Todos los departamentos</SelectItem>
                        {departamentos.map((d) => (
                            <SelectItem key={d.id} value={String(d.id)}>{d.nombre}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            )}
            columnas={[
                { id: 'nombre', encabezado: 'Cargo', celda: (c) => <span className="font-medium">{c.nombre}</span> },
                { id: 'departamento', encabezado: 'Departamento', celda: (c) => <span className="text-muted-foreground">{c.departamento ?? '—'}</span> },
                { id: 'empleados', encabezado: 'Empleados', className: 'text-right', celda: (c) => <span className="tabular">{formatoNumero(c.empleados)}</span> },
            ]}
            formulario={(p) => <Formulario key={p.apertura} {...p} url={urls.index} departamentos={departamentos} />}
        />
    );
}

function Formulario({ abierto, registro, onCerrar, url, departamentos }: {
    abierto: boolean; registro?: Cargo; onCerrar: () => void; url: string; departamentos: Props['departamentos'];
}) {
    const form = useForm({ nombre: registro?.nombre ?? '', departamento_id: registro ? String(registro.departamento_id) : '' });
    const opciones = { preserveScroll: true, onSuccess: onCerrar };

    return (
        <DialogoFormulario
            abierto={abierto}
            onCerrar={onCerrar}
            titulo={registro ? 'Editar cargo' : 'Agregar cargo'}
            descripcion="El nombre no se repite dentro de un mismo departamento."
            sucio={form.isDirty}
            procesando={form.processing}
            textoGuardar={registro ? 'Guardar cambios' : 'Agregar cargo'}
            onGuardar={() => (registro ? form.put(`${url}/${registro.id}`, opciones) : form.post(url, opciones))}
        >
            <Campo etiqueta="Departamento" requerido error={form.errors.departamento_id}>
                {(control) => (
                    <Select value={form.data.departamento_id || undefined} onValueChange={(v) => form.setData('departamento_id', v)}>
                        <SelectTrigger {...control} className="w-full"><SelectValue placeholder="Selecciona un departamento" /></SelectTrigger>
                        <SelectContent>
                            {departamentos.map((d) => (
                                <SelectItem key={d.id} value={String(d.id)}>{d.nombre}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                )}
            </Campo>
            <Campo etiqueta="Nombre" requerido error={form.errors.nombre}>
                <Input value={form.data.nombre} maxLength={100} onChange={(e) => form.setData('nombre', e.target.value)} />
            </Campo>
        </DialogoFormulario>
    );
}
