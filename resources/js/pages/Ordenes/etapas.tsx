import { router, useForm } from '@inertiajs/react';
import { Plus, Trash2, X } from 'lucide-react';
import { useState } from 'react';

import { Campo } from '@/components/app/campo';
import { confirmar } from '@/components/app/confirmador';
import { EstadoBadge } from '@/components/app/estado-badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { usePermisos } from '@/hooks/use-permisos';

import type { Empleado, EstadoOrden, OrdenDetalle } from './tipos';

const ESTADOS: EstadoOrden[] = ['Pendiente', 'En Proceso', 'Finalizado', 'Cancelado'];

interface Props {
    orden: OrdenDetalle | null;
    empleados: Empleado[];
    onCerrar: () => void;
    url: string;
}

/**
 * Etapas de una orden (corte, costura, bordado…), cada una con su equipo.
 * Finalizar la última etapa exige que la producción ya esté registrada.
 */
export function Etapas({ orden, empleados, onCerrar, url }: Props) {
    const { puede } = usePermisos();
    const gestionar = puede('ordenes.gestionar');
    const [agregando, setAgregando] = useState(0);
    const base = orden ? `${url}/${orden.id}/subordenes` : '';
    const mutar = { preserveScroll: true, preserveState: true };

    return (
        <Dialog open onOpenChange={(a) => !a && onCerrar()}>
            <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Etapas{orden && ` de la orden #${orden.id}`}</DialogTitle>
                    <DialogDescription>{orden ? orden.producto : 'Cargando…'}</DialogDescription>
                </DialogHeader>
                {!orden ? (
                    <Skeleton className="h-32" aria-hidden />
                ) : (
                    <>
                        {orden.etapas.length === 0 && <p className="text-muted-foreground text-sm">Aún no hay etapas. Son opcionales: sirven para seguir el avance por tarea.</p>}
                        <ul className="grid gap-2">
                            {orden.etapas.map((e) => (
                                <li key={e.id} className="grid gap-2 rounded-lg border p-3 sm:grid-cols-[1fr_auto] sm:items-center">
                                    <div className="min-w-0 text-sm">
                                        <p className="font-medium">{e.nombre}{e.cantidad ? <span className="text-muted-foreground font-normal"> · {e.cantidad} u</span> : null}</p>
                                        <p className="text-muted-foreground text-xs">{e.empleados.map((x) => (x.rol ? `${x.nombre} (${x.rol})` : x.nombre)).join(', ')}</p>
                                        {e.notas && <p className="text-muted-foreground text-xs">{e.notas}</p>}
                                    </div>
                                    <div className="flex items-center gap-2">
                                        {gestionar && orden.estado !== 'Cancelado' ? (
                                            <Select value={e.estado} onValueChange={(v) => router.patch(`${base}/${e.id}/estado`, { estado: v }, mutar)}>
                                                <SelectTrigger className="w-36" aria-label={`Estado de ${e.nombre}`}><SelectValue /></SelectTrigger>
                                                <SelectContent>{ESTADOS.map((x) => <SelectItem key={x} value={x}>{x}</SelectItem>)}</SelectContent>
                                            </Select>
                                        ) : <EstadoBadge estado={e.estado} />}
                                        {gestionar && (
                                            <Button variant="ghost" size="icon" aria-label={`Eliminar la etapa ${e.nombre}`} onClick={() => void confirmar({ titulo: 'Eliminar etapa', descripcion: `¿Eliminar la etapa «${e.nombre}»?`, accion: 'Eliminar' }).then((si) => si && router.delete(`${base}/${e.id}`, mutar))}>
                                                <Trash2 />
                                            </Button>
                                        )}
                                    </div>
                                </li>
                            ))}
                        </ul>
                        {gestionar && orden.estado !== 'Cancelado' && (
                            agregando ? (
                                <NuevaEtapa key={agregando} url={base} empleados={empleados} equipo={orden.equipo.map((m) => m.id)} onListo={() => setAgregando(0)} />
                            ) : (
                                <Button variant="outline" className="justify-self-start" onClick={() => setAgregando((n) => n + 1)}><Plus /> Agregar etapa</Button>
                            )
                        )}
                    </>
                )}
            </DialogContent>
        </Dialog>
    );
}

function NuevaEtapa({ url, empleados, equipo, onListo }: { url: string; empleados: Empleado[]; equipo: number[]; onListo: () => void }) {
    // Por defecto, el mismo equipo de la orden.
    const form = useForm({ nombre: '', cantidad_asignada: '', notas: '', empleados: equipo.map((id) => ({ id, rol: '' })) });
    const e = form.errors as Record<string, string | undefined>;
    const libres = empleados.filter((x) => !form.data.empleados.some((s) => s.id === x.id));
    const nombre = (id: number) => empleados.find((x) => x.id === id)?.nombre ?? `Empleado #${id}`;

    form.transform((d) => ({
        nombre: d.nombre.trim(),
        cantidad_asignada: d.cantidad_asignada ? Number(d.cantidad_asignada) : null,
        notas: d.notas.trim() || null,
        empleados: d.empleados.map((s) => ({ id: s.id, rol: s.rol.trim() || null })),
    }));

    return (
        <form
            className="grid gap-3 rounded-lg border border-dashed p-3"
            onSubmit={(ev) => { ev.preventDefault(); form.post(url, { preserveScroll: true, preserveState: true, onSuccess: onListo }); }}
        >
            <div className="grid gap-3 sm:grid-cols-[1fr_8rem]">
                <Campo etiqueta="Etapa" requerido error={e.nombre}>
                    <Input value={form.data.nombre} maxLength={120} placeholder="Corte, costura, bordado…" onChange={(ev) => form.setData('nombre', ev.target.value)} autoFocus />
                </Campo>
                <Campo etiqueta="Unidades" error={e.cantidad_asignada}>
                    <Input type="number" min={1} inputMode="numeric" value={form.data.cantidad_asignada} onChange={(ev) => form.setData('cantidad_asignada', ev.target.value)} className="tabular" />
                </Campo>
            </div>
            <fieldset className="grid gap-2">
                <legend className="mb-1 text-sm font-medium">Empleados <span className="text-destructive">*</span></legend>
                {form.data.empleados.map((s, n) => (
                    <div key={s.id} className="flex items-center gap-2 text-sm">
                        <span className="min-w-0 flex-1 truncate">{nombre(s.id)}</span>
                        <Input value={s.rol} maxLength={80} placeholder="Rol (opcional)" aria-label={`Rol de ${nombre(s.id)}`} className="w-40" onChange={(ev) => form.setData('empleados', form.data.empleados.map((x, i) => (i === n ? { ...x, rol: ev.target.value } : x)))} />
                        <Button type="button" variant="ghost" size="icon" aria-label={`Quitar a ${nombre(s.id)}`} onClick={() => form.setData('empleados', form.data.empleados.filter((_, i) => i !== n))}><X /></Button>
                    </div>
                ))}
                {libres.length > 0 && (
                    <Select value="" onValueChange={(v) => form.setData('empleados', [...form.data.empleados, { id: Number(v), rol: '' }])}>
                        <SelectTrigger className="w-60" aria-label="Agregar empleado a la etapa"><SelectValue placeholder="Agregar empleado…" /></SelectTrigger>
                        <SelectContent>{libres.map((x) => <SelectItem key={x.id} value={String(x.id)}>{x.nombre}</SelectItem>)}</SelectContent>
                    </Select>
                )}
                {e.empleados && <p className="text-destructive text-xs">{e.empleados}</p>}
            </fieldset>
            <Campo etiqueta="Notas" error={e.notas}>
                <Input value={form.data.notas} maxLength={500} onChange={(ev) => form.setData('notas', ev.target.value)} />
            </Campo>
            <div className="flex justify-end gap-2">
                <Button type="button" variant="ghost" onClick={onListo}>Descartar</Button>
                <Button type="submit" disabled={form.processing}>Crear etapa</Button>
            </div>
        </form>
    );
}
