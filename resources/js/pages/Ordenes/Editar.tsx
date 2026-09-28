import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import { toast } from 'sonner';

import { Campo } from '@/components/app/campo';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useGuardCambios } from '@/hooks/use-guard-cambios';
import AppLayout from '@/layouts/app-layout';
import { formatoNumero } from '@/lib/formato';

import { EquipoReparto, repartir, type Asignacion } from './equipo-reparto';
import type { EstadoOrden, PaginaEditarOrden } from './tipos';

/**
 * Editar una orden: equipo y reparto, fechas, estado y notas. Las unidades
 * solo cambian con la orden Pendiente; producto e insumos quedan fijos (ya
 * comprometieron stock: para rehacerlos, cancelar y crear otra).
 */
export default function EditarOrden({ orden, empleados, urls }: PaginaEditarOrden) {
    const form = useForm({
        cantidad: String(orden.cantidad),
        fecha_inicio: orden.fecha_inicio ?? '',
        fecha_fin_estimada: orden.fecha_fin_estimada ?? '',
        estado: orden.estado as Exclude<EstadoOrden, 'Cancelado'>,
        notas: orden.notas ?? '',
        empleados: orden.equipo.map((e) => ({ id: e.id, cantidad: String(e.cantidad) })) as Asignacion[],
        repartoManual: true,
    });
    const { data, setData, errors } = form;
    const e = errors as Record<string, string | undefined>;
    useGuardCambios(form.isDirty && !form.processing);

    const pendiente = orden.estado === 'Pendiente';
    const total = parseInt(data.cantidad, 10) || 0;
    const fijos = orden.equipo.filter((m) => m.producida > 0 || m.defectuosa > 0).map((m) => m.id);
    // Empleados que ya no están en Producción pero siguen en el equipo: se muestran igual.
    const lista = [...empleados, ...orden.equipo.filter((m) => !empleados.some((x) => x.id === m.id)).map((m) => ({ id: m.id, nombre: m.nombre }))];

    form.transform((d) => ({
        cantidad: parseInt(d.cantidad, 10) || 0,
        fecha_inicio: d.fecha_inicio,
        fecha_fin_estimada: d.fecha_fin_estimada,
        estado: d.estado,
        notas: d.notas.trim() || null,
        empleados: d.empleados.map((a) => ({ id: a.id, cantidad: parseInt(a.cantidad, 10) || 0 })),
    }));

    return (
        <AppLayout
            titulo={`Editar orden #${orden.id}`}
            acciones={
                <>
                    <Button variant="ghost" asChild><Link href={urls.index}><ArrowLeft /> Órdenes</Link></Button>
                    <Button type="submit" form="form-orden" disabled={form.processing}><Save /> Guardar cambios</Button>
                </>
            }
        >
            <form
                id="form-orden"
                noValidate
                className="grid max-w-3xl gap-4"
                onSubmit={(ev) => { ev.preventDefault(); form.put(urls.guardar, { preserveScroll: true, onError: () => toast.error('Revisa los campos marcados.') }); }}
            >
                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">{orden.producto}</CardTitle>
                        <CardDescription>
                            {orden.pedido_id ? `Pedido #${orden.pedido_id}` : 'Orden manual'}
                            {orden.cliente && ` · ${orden.cliente}`}
                            {orden.variante && ` · ${orden.variante}`}
                            {` · ${formatoNumero(orden.cantidad_producida)} de ${formatoNumero(orden.cantidad)} producidas`}
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4">
                        {e.general && <p className="text-destructive text-sm" role="alert">{e.general}</p>}
                        <div className="grid gap-4 sm:grid-cols-4">
                            <Campo etiqueta="Unidades" requerido error={e.cantidad} ayuda={pendiente ? `Hasta ${orden.cantidad_maxima}` : 'Solo con la orden Pendiente'}>
                                <Input
                                    type="number" min={1} max={orden.cantidad_maxima} inputMode="numeric"
                                    value={data.cantidad}
                                    disabled={!pendiente}
                                    onChange={(ev) => {
                                        const n = parseInt(ev.target.value, 10) || 0;
                                        setData({ ...data, cantidad: ev.target.value, empleados: data.repartoManual ? data.empleados : repartir(n, data.empleados.map((a) => a.id)) });
                                    }}
                                    className="tabular"
                                />
                            </Campo>
                            <Campo etiqueta="Inicio" requerido error={e.fecha_inicio}>
                                <Input type="date" value={data.fecha_inicio} onChange={(ev) => setData('fecha_inicio', ev.target.value)} />
                            </Campo>
                            <Campo etiqueta="Fin estimado" requerido error={e.fecha_fin_estimada}>
                                <Input type="date" value={data.fecha_fin_estimada} min={data.fecha_inicio} onChange={(ev) => setData('fecha_fin_estimada', ev.target.value)} />
                            </Campo>
                            <Campo etiqueta="Estado" requerido error={e.estado} ayuda="Para cancelar, usa «Cancelar orden».">
                                {(control) => (
                                    <Select value={data.estado} onValueChange={(v) => setData('estado', v as typeof data.estado)}>
                                        <SelectTrigger {...control} className="w-full"><SelectValue /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="Pendiente">Pendiente</SelectItem>
                                            <SelectItem value="En Proceso">En Proceso</SelectItem>
                                            <SelectItem value="Finalizado">Finalizado</SelectItem>
                                        </SelectContent>
                                    </Select>
                                )}
                            </Campo>
                        </div>

                        <EquipoReparto
                            empleados={lista}
                            total={total}
                            valor={data.empleados}
                            fijos={fijos}
                            error={e.empleados}
                            onCambiar={(asignacion, manual) => setData({ ...data, empleados: asignacion, repartoManual: manual })}
                        />

                        <Campo etiqueta="Notas" error={e.notas}>
                            <Textarea rows={2} value={data.notas} onChange={(ev) => setData('notas', ev.target.value)} />
                        </Campo>

                        {orden.insumos.length > 0 && (
                            <section className="grid gap-1 text-sm">
                                <h3 className="font-medium">Insumos comprometidos</h3>
                                <p className="text-muted-foreground text-xs">No se editan: ya se descontaron del inventario.</p>
                                <ul className="grid gap-1">
                                    {orden.insumos.map((i) => (
                                        <li key={i.id} className="flex justify-between gap-3"><span>{i.nombre}</span><span className="tabular text-muted-foreground">{formatoNumero(i.estimada)} {i.unidad}</span></li>
                                    ))}
                                </ul>
                            </section>
                        )}
                    </CardContent>
                </Card>
            </form>
        </AppLayout>
    );
}
