import { useForm } from '@inertiajs/react';

import { Campo } from '@/components/app/campo';
import { DialogoFormulario } from '@/components/app/dialogo-formulario';
import { PaginaCatalogo } from '@/components/app/pagina-catalogo';
import { Input } from '@/components/ui/input';
import { formatoNumero } from '@/lib/formato';
import type { Paginado } from '@/types';

/** Espejo de DepartamentoController::index() (lo verifica CatalogosPaginaTest). */
export interface Departamento {
    id: number;
    nombre: string;
    cargos: number;
    empleados: number;
    inhabilitado: boolean;
}

interface Props {
    registros: Paginado<Departamento>;
    filtros: { buscar?: string; historial?: string };
    urls: { index: string };
}

export default function DepartamentosIndex({ registros, filtros, urls }: Props) {
    return (
        <PaginaCatalogo
            titulo="Departamentos"
            recurso="departamento"
            permiso="departamentos.gestionar"
            registros={registros}
            filtros={filtros}
            url={urls.index}
            avisoInhabilitar="Solo se puede inhabilitar si no tiene cargos ni empleados. Pasa al historial y se puede restaurar."
            columnas={[
                { id: 'nombre', encabezado: 'Departamento', celda: (d) => <span className="font-medium">{d.nombre}</span> },
                { id: 'cargos', encabezado: 'Cargos', className: 'text-right', celda: (d) => <span className="tabular">{formatoNumero(d.cargos)}</span> },
                { id: 'empleados', encabezado: 'Empleados', className: 'text-right', celda: (d) => <span className="tabular">{formatoNumero(d.empleados)}</span> },
            ]}
            formulario={(p) => <Formulario key={p.apertura} {...p} url={urls.index} />}
        />
    );
}

function Formulario({ abierto, registro, onCerrar, url }: { abierto: boolean; registro?: Departamento; onCerrar: () => void; url: string }) {
    const form = useForm({ nombre: registro?.nombre ?? '' });
    const opciones = { preserveScroll: true, onSuccess: onCerrar };

    return (
        <DialogoFormulario
            abierto={abierto}
            onCerrar={onCerrar}
            titulo={registro ? 'Editar departamento' : 'Agregar departamento'}
            sucio={form.isDirty}
            procesando={form.processing}
            textoGuardar={registro ? 'Guardar cambios' : 'Agregar departamento'}
            onGuardar={() => (registro ? form.put(`${url}/${registro.id}`, opciones) : form.post(url, opciones))}
        >
            <Campo etiqueta="Nombre" requerido error={form.errors.nombre}>
                <Input value={form.data.nombre} maxLength={100} autoFocus onChange={(e) => form.setData('nombre', e.target.value)} />
            </Campo>
        </DialogoFormulario>
    );
}
