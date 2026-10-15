import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

import { Asistente } from '@/components/app/asistente';
import { Campo } from '@/components/app/campo';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useGuardCambios } from '@/hooks/use-guard-cambios';
import AppLayout from '@/layouts/app-layout';
import { formatoFecha, formatoNumero } from '@/lib/formato';

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

    // Las unidades solo cambian con la orden Pendiente y sin material cortado.
    const pendiente = orden.estado === 'Pendiente' && !orden.con_produccion;
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

    const [salto, setSalto] = useState<{ paso: number; n: number }>();
    const suma = data.empleados.reduce((a, x) => a + (parseInt(x.cantidad, 10) || 0), 0);
    const nombre = (id: number) => lista.find((x) => x.id === id)?.nombre ?? `#${id}`;
    // Mismas reglas que el servidor: con producción no se vuelve a Pendiente y
    // solo se finaliza con todas las unidades producidas.
    const puedePendiente = !orden.con_produccion;
    const puedeFinalizar = orden.cantidad_producida >= total;
    const bajoLoProducido = data.empleados.find((a) => (parseInt(a.cantidad, 10) || 0) < (orden.equipo.find((m) => m.id === a.id)?.producida ?? 0));

    const guardar = (ev: React.FormEvent) => {
        ev.preventDefault();
        if (ev.target !== ev.currentTarget) return; // el submit de un diálogo abierto desde un paso
        form.put(urls.guardar, {
            preserveScroll: true,
            onError: () => {
                toast.error('Revisa los campos marcados.');
                setSalto((s) => ({ paso: 1, n: (s?.n ?? 0) + 1 }));
            },
        });
    };

    return (
        <AppLayout
            titulo={`Editar orden #${orden.id}`}
            acciones={
                <Button variant="ghost" asChild>
                    <Link href={urls.index}>
                        <ArrowLeft /> Órdenes
                    </Link>
                </Button>
            }
        >
            <form id="form-orden" noValidate className="max-w-4xl" onSubmit={guardar}>
                <Card>
                    <CardContent>
                        <Asistente
                            salto={salto}
                            final={
                                <Button type="submit" disabled={form.processing}>
                                    <Save /> Guardar cambios
                                </Button>
                            }
                            pasos={[
                                {
                                    titulo: 'Pedido',
                                    descripcion: 'La línea del pedido queda fija al editar: para cambiarla, cancela la orden y crea otra.',
                                    contenido: (
                                        <dl className="grid gap-3 rounded-lg border p-4 text-sm sm:grid-cols-2">
                                            <div>
                                                <dt className="text-muted-foreground text-xs">Producto</dt>
                                                <dd className="font-medium">
                                                    {orden.producto}
                                                    {orden.variante && <span className="text-muted-foreground font-normal"> · {orden.variante}</span>}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt className="text-muted-foreground text-xs">Pedido</dt>
                                                <dd>
                                                    {orden.pedido_id ? `Pedido #${orden.pedido_id}` : 'Orden manual'}
                                                    {orden.cliente && ` · ${orden.cliente}`}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt className="text-muted-foreground text-xs">Producción</dt>
                                                <dd className="tabular">
                                                    {formatoNumero(orden.cantidad_producida)} de {formatoNumero(orden.cantidad)} producidas
                                                </dd>
                                            </div>
                                            <div>
                                                <dt className="text-muted-foreground text-xs">Estado actual</dt>
                                                <dd>{orden.estado}</dd>
                                            </div>
                                        </dl>
                                    ),
                                },
                                {
                                    titulo: 'Asignación',
                                    descripcion: 'Equipo, cronograma y estado. Las unidades solo cambian con la orden Pendiente.',
                                    validar: () =>
                                        total < 1
                                            ? 'Indica las unidades.'
                                            : !data.empleados.length
                                              ? 'Asigna al menos un empleado.'
                                              : suma !== total
                                                ? `El reparto del equipo debe sumar ${total}.`
                                                : bajoLoProducido
                                                  ? `La parte de ${nombre(bajoLoProducido.id)} no puede ser menor que lo que ya produjo.`
                                                  : (data.estado === 'Pendiente' && !puedePendiente) || (data.estado === 'Finalizado' && !puedeFinalizar)
                                                    ? 'El estado elegido no corresponde con lo producido.'
                                                    : !data.fecha_inicio || !data.fecha_fin_estimada || data.fecha_fin_estimada <= data.fecha_inicio
                                                      ? 'El fin estimado debe ser posterior al inicio.'
                                                      : null,
                                    contenido: (
                                        <div className="grid gap-4">
                                            {e.general && (
                                                <p className="text-destructive text-sm" role="alert">
                                                    {e.general}
                                                </p>
                                            )}
                                            <div className="grid gap-4 sm:grid-cols-4">
                                                <Campo etiqueta="Unidades" requerido error={e.cantidad} ayuda={pendiente ? `Hasta ${orden.cantidad_maxima}` : 'Solo con la orden Pendiente'}>
                                                    <Input
                                                        type="number"
                                                        min={1}
                                                        max={orden.cantidad_maxima}
                                                        inputMode="numeric"
                                                        value={data.cantidad}
                                                        disabled={!pendiente}
                                                        onChange={(ev) => {
                                                            const n = parseInt(ev.target.value, 10) || 0;
                                                            setData({
                                                                ...data,
                                                                cantidad: ev.target.value,
                                                                empleados: data.repartoManual
                                                                    ? data.empleados
                                                                    : repartir(
                                                                          n,
                                                                          data.empleados.map((a) => a.id),
                                                                      ),
                                                            });
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
                                                <Campo
                                                    etiqueta="Estado"
                                                    requerido
                                                    error={e.estado}
                                                    ayuda={!puedeFinalizar ? 'Finalizado se habilita con todas las unidades producidas.' : 'Para cancelar, usa «Cancelar orden».'}
                                                >
                                                    {(control) => (
                                                        <Select value={data.estado} onValueChange={(v) => setData('estado', v as typeof data.estado)}>
                                                            <SelectTrigger {...control} className="w-full">
                                                                <SelectValue />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectItem value="Pendiente" disabled={!puedePendiente}>
                                                                    Pendiente
                                                                </SelectItem>
                                                                <SelectItem value="En Proceso">En Proceso</SelectItem>
                                                                <SelectItem value="Finalizado" disabled={!puedeFinalizar}>
                                                                    Finalizado
                                                                </SelectItem>
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
                                        </div>
                                    ),
                                },
                                {
                                    titulo: 'Insumos',
                                    descripcion: 'No se editan: ya se descontaron del inventario al crear la orden.',
                                    contenido: orden.insumos.length ? (
                                        <ul className="grid gap-1 rounded-lg border p-3 text-sm">
                                            {orden.insumos.map((i) => (
                                                <li key={i.id} className="flex justify-between gap-3">
                                                    <span>{i.nombre}</span>
                                                    <span className="tabular text-muted-foreground">
                                                        {formatoNumero(i.estimada)} {i.unidad}
                                                    </span>
                                                </li>
                                            ))}
                                        </ul>
                                    ) : (
                                        <p className="text-muted-foreground text-sm">Esta orden no tiene insumos.</p>
                                    ),
                                },
                                {
                                    titulo: 'Resumen',
                                    descripcion: 'Revisa los cambios antes de guardar.',
                                    contenido: (
                                        <dl className="grid gap-3 rounded-lg border p-4 text-sm sm:grid-cols-2">
                                            <div>
                                                <dt className="text-muted-foreground text-xs">Unidades</dt>
                                                <dd className="tabular">{total}</dd>
                                            </div>
                                            <div>
                                                <dt className="text-muted-foreground text-xs">Estado</dt>
                                                <dd>{data.estado}</dd>
                                            </div>
                                            <div>
                                                <dt className="text-muted-foreground text-xs">Cronograma</dt>
                                                <dd className="tabular">
                                                    {data.fecha_inicio ? formatoFecha(data.fecha_inicio) : '—'} → {data.fecha_fin_estimada ? formatoFecha(data.fecha_fin_estimada) : '—'}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt className="text-muted-foreground text-xs">Equipo</dt>
                                                <dd>{data.empleados.map((x) => `${nombre(x.id)} (${x.cantidad})`).join(', ') || '—'}</dd>
                                            </div>
                                            {data.notas.trim() && (
                                                <div className="sm:col-span-2">
                                                    <dt className="text-muted-foreground text-xs">Notas</dt>
                                                    <dd className="whitespace-pre-line">{data.notas}</dd>
                                                </div>
                                            )}
                                        </dl>
                                    ),
                                },
                            ]}
                        />
                    </CardContent>
                </Card>
            </form>
        </AppLayout>
    );
}
