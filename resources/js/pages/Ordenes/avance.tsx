import { useForm } from '@inertiajs/react';

import { Campo } from '@/components/app/campo';
import { DialogoFormulario } from '@/components/app/dialogo-formulario';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';
import { formatoNumero } from '@/lib/formato';
import { cn } from '@/lib/utils';

import type { OrdenDetalle } from './tipos';

interface Props {
    orden: OrdenDetalle | null;
    /** Empleado a preseleccionar (si es del equipo y le falta producir). */
    empleadoId?: number;
    onCerrar: () => void;
    url: string;
}

/** Registrar unidades producidas. Con equipo, se atribuyen a un empleado (con su tope). */
export function Avance({ orden, empleadoId, onCerrar, url }: Props) {
    if (!orden) {
        return (
            <Dialog open onOpenChange={(a) => !a && onCerrar()}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader><DialogTitle>Registrar avance</DialogTitle><DialogDescription>Cargando la orden…</DialogDescription></DialogHeader>
                    <Skeleton className="h-32" aria-hidden />
                </DialogContent>
            </Dialog>
        );
    }
    return <FormularioAvance key={orden.id} orden={orden} empleadoId={empleadoId} onCerrar={onCerrar} url={url} />;
}

function FormularioAvance({ orden, empleadoId, onCerrar, url }: Omit<Props, 'orden'> & { orden: OrdenDetalle }) {
    const conEquipo = orden.equipo.length > 1;
    const restante = (id: number) => {
        const e = orden.equipo.find((m) => m.id === id);
        return e ? e.cantidad - e.producida : 0;
    };
    const pendiente = (e: OrdenDetalle['equipo'][number]) => e.cantidad > e.producida;
    const primero = orden.equipo.find((e) => e.id === empleadoId && pendiente(e)) ?? orden.equipo.find(pendiente) ?? orden.equipo[0];
    const restanteOrden = orden.cantidad_solicitada - orden.cantidad_producida;
    const form = useForm({
        empleado_id: primero ? String(primero.id) : '',
        cantidad_producida: String(primero ? (conEquipo ? restante(primero.id) : Math.max(0, primero.cantidad - primero.producida)) : restanteOrden),
    });
    const tope = form.data.empleado_id ? restante(Number(form.data.empleado_id)) : restanteOrden;

    form.transform((d) => ({ cantidad_producida: parseInt(d.cantidad_producida, 10) || 0, empleado_id: d.empleado_id ? Number(d.empleado_id) : null }));

    return (
        <DialogoFormulario
            abierto
            onCerrar={onCerrar}
            titulo="Registrar avance"
            descripcion={`Orden #${orden.id} · ${orden.producto} · ${formatoNumero(orden.cantidad_producida)} de ${formatoNumero(orden.cantidad_solicitada)} producidas`}
            sucio={form.isDirty}
            procesando={form.processing}
            textoGuardar="Registrar avance"
            onGuardar={() => form.post(`${url}/${orden.id}/avance`, { preserveScroll: true, preserveState: true, onSuccess: onCerrar })}
        >
            {conEquipo && (
                <fieldset className="grid gap-2">
                    <legend className="mb-1 text-sm font-medium">¿Quién produjo? <span className="text-destructive">*</span></legend>
                    {orden.equipo.map((e) => {
                        const r = e.cantidad - e.producida;
                        const elegido = form.data.empleado_id === String(e.id);
                        return (
                            <label key={e.id} className={cn('flex cursor-pointer items-center gap-3 rounded-md border p-2 text-sm', elegido && 'border-primary bg-primary/5', r <= 0 && 'cursor-not-allowed opacity-60')}>
                                <input
                                    type="radio"
                                    name="empleado"
                                    value={e.id}
                                    checked={elegido}
                                    disabled={r <= 0}
                                    onChange={() => { form.setData({ empleado_id: String(e.id), cantidad_producida: String(r) }); }}
                                    className="accent-primary"
                                />
                                <span className="flex-1">{e.nombre}</span>
                                <span className="text-muted-foreground tabular text-xs">{e.producida} de {e.cantidad} · {r > 0 ? `le faltan ${r}` : 'completó su parte'}</span>
                            </label>
                        );
                    })}
                    {form.errors.empleado_id && <p className="text-destructive text-xs">{form.errors.empleado_id}</p>}
                </fieldset>
            )}
            <Campo etiqueta="Unidades producidas" requerido error={form.errors.cantidad_producida} ayuda={`Hasta ${formatoNumero(tope)}. Los defectos se registran en Control de Calidad.`}>
                <Input type="number" min={1} max={tope} inputMode="numeric" value={form.data.cantidad_producida} onChange={(ev) => form.setData('cantidad_producida', ev.target.value)} className="tabular" />
            </Campo>
        </DialogoFormulario>
    );
}
