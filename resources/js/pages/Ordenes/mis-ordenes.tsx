import { Plus } from 'lucide-react';

import { Campo } from '@/components/app/campo';
import { EstadoBadge } from '@/components/app/estado-badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { usePermisos } from '@/hooks/use-permisos';
import { formatoFecha, formatoNumero } from '@/lib/formato';

import { Barra } from './barra';
import { progreso, type Empleado, type PaginaOrdenes } from './tipos';

interface Props {
    abierto: boolean;
    onCerrar: () => void;
    empleados: Empleado[];
    empleado?: string;
    datos: PaginaOrdenes['misOrdenes'];
    onElegir: (id?: string) => void;
    onAvance: (ordenId: number) => void;
}

/** Órdenes en las que trabaja un empleado (responsable o parte del equipo), las activas primero. */
export function MisOrdenes({ abierto, onCerrar, empleados, empleado, datos, onElegir, onAvance }: Props) {
    const { puede } = usePermisos();
    const cargando = Boolean(empleado) && datos?.empleado.id !== Number(empleado);

    return (
        <Dialog open={abierto} onOpenChange={(a) => !a && onCerrar()}>
            <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Órdenes por empleado</DialogTitle>
                    <DialogDescription>Lo que tiene asignado cada persona de Producción.</DialogDescription>
                </DialogHeader>
                <Campo etiqueta="Empleado">
                    {(control) => (
                        <Select value={empleado ?? ''} onValueChange={(v) => onElegir(v)}>
                            <SelectTrigger {...control} className="w-full sm:w-72"><SelectValue placeholder="Elige un empleado" /></SelectTrigger>
                            <SelectContent>{empleados.map((e) => <SelectItem key={e.id} value={String(e.id)}>{e.nombre}</SelectItem>)}</SelectContent>
                        </Select>
                    )}
                </Campo>
                {cargando && <Skeleton className="h-24" aria-hidden />}
                {!cargando && datos && (
                    datos.ordenes.length === 0 ? (
                        <p className="text-muted-foreground py-4 text-center text-sm">{datos.empleado.nombre} no tiene órdenes asignadas.</p>
                    ) : (
                        <ul className="grid gap-2">
                            {datos.ordenes.map((o) => {
                                const activa = o.estado === 'Pendiente' || o.estado === 'En Proceso';
                                const suya = o.mi_cantidad !== null;
                                return (
                                    <li key={o.id} className="grid gap-2 rounded-lg border p-3">
                                        <div className="flex flex-wrap items-start justify-between gap-2 text-sm">
                                            <p><span className="text-muted-foreground tabular">#{o.id}</span> <span className="font-medium">{o.producto}</span><span className="text-muted-foreground"> · {o.pedido_id ? `Pedido #${o.pedido_id}` : 'Orden manual'}</span></p>
                                            <EstadoBadge estado={o.estado} />
                                        </div>
                                        <Barra
                                            valor={suya ? progreso(o.mi_producida ?? 0, o.mi_cantidad ?? 0) : progreso(o.cantidad_producida, o.cantidad_solicitada)}
                                            texto={suya ? `Su parte: ${formatoNumero(o.mi_producida ?? 0)} de ${formatoNumero(o.mi_cantidad ?? 0)} (orden: ${o.cantidad_producida}/${o.cantidad_solicitada})` : `${o.cantidad_producida} de ${o.cantidad_solicitada}`}
                                        />
                                        <div className="flex flex-wrap items-center justify-between gap-2 text-xs">
                                            <span className="text-muted-foreground">Entrega: {o.fecha_fin_estimada ? formatoFecha(o.fecha_fin_estimada) : '—'}</span>
                                            {activa && puede('ordenes.avance') && <Button size="sm" variant="outline" onClick={() => onAvance(o.id)}><Plus /> Registrar avance</Button>}
                                        </div>
                                    </li>
                                );
                            })}
                        </ul>
                    )
                )}
            </DialogContent>
        </Dialog>
    );
}
