import { Check } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

import type { Empleado } from './tipos';

/** Unidades de la orden asignadas a un empleado. */
export interface Asignacion {
    id: number;
    cantidad: string;
}

/** Reparte `total` en partes iguales (el resto, a los primeros). */
export function repartir(total: number, ids: number[]): Asignacion[] {
    if (!ids.length) return [];
    const base = Math.floor(total / ids.length);
    const resto = total - base * ids.length;
    return ids.map((id, i) => ({ id, cantidad: String(base + (i < resto ? 1 : 0)) }));
}

interface Props {
    empleados: Empleado[];
    total: number;
    valor: Asignacion[];
    /** `manual`: el usuario tocó el reparto (ya no se recalcula al cambiar las unidades). */
    onCambiar: (asignacion: Asignacion[], manual: boolean) => void;
    error?: string;
    /** Empleados que no se pueden quitar (ya registraron producción). */
    fijos?: number[];
}

/**
 * Equipo de una orden y cuántas unidades hace cada uno. La suma debe dar las
 * unidades de la orden (el servidor lo exige): con un solo empleado es automático.
 */
export function EquipoReparto({ empleados, total, valor, onCambiar, error, fijos = [] }: Props) {
    const elegidos = valor.map((a) => a.id);
    const suma = valor.reduce((s, a) => s + (parseInt(a.cantidad, 10) || 0), 0);
    const nombre = (id: number) => empleados.find((e) => e.id === id)?.nombre ?? `Empleado #${id}`;

    const alternar = (id: number) => {
        if (fijos.includes(id)) return;
        const ids = elegidos.includes(id) ? elegidos.filter((x) => x !== id) : [...elegidos, id];
        onCambiar(repartir(total, ids), false);
    };

    return (
        <fieldset className="grid gap-2">
            <legend className="mb-1 text-sm font-medium">Equipo <span className="text-destructive">*</span></legend>
            <div className="flex flex-wrap gap-2">
                {empleados.map((e) => {
                    const activo = elegidos.includes(e.id);
                    return (
                        <button
                            key={e.id}
                            type="button"
                            role="checkbox"
                            aria-checked={activo}
                            disabled={fijos.includes(e.id)}
                            onClick={() => alternar(e.id)}
                            className={cn('inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-sm transition-colors', activo ? 'border-primary bg-primary/10 text-primary' : 'hover:bg-muted', fijos.includes(e.id) && 'cursor-not-allowed opacity-80')}
                            title={fijos.includes(e.id) ? 'Ya registró producción: no se puede quitar' : undefined}
                        >
                            {activo && <Check className="size-3.5" />} {e.nombre}
                        </button>
                    );
                })}
                {empleados.length === 0 && <p className="text-muted-foreground text-sm">No hay empleados activos en el departamento de Producción.</p>}
            </div>

            {valor.length > 1 && (
                <div className="bg-muted/40 grid gap-2 rounded-md p-3">
                    {valor.map((a, i) => (
                        <div key={a.id} className="flex items-center gap-2 text-sm">
                            <span className="min-w-0 flex-1 truncate">{nombre(a.id)}</span>
                            <Input
                                type="number" min={1} inputMode="numeric"
                                value={a.cantidad}
                                onChange={(ev) => onCambiar(valor.map((x, k) => (k === i ? { ...x, cantidad: ev.target.value } : x)), true)}
                                aria-label={`Unidades de ${nombre(a.id)}`}
                                className="tabular w-24"
                            />
                        </div>
                    ))}
                    <div className="flex items-center justify-between gap-2 text-xs">
                        <span className={cn('tabular', suma === total ? 'text-muted-foreground' : 'text-destructive')}>Asignadas {suma} de {total}</span>
                        <Button type="button" variant="ghost" size="sm" onClick={() => onCambiar(repartir(total, elegidos), false)}>Repartir parejo</Button>
                    </div>
                </div>
            )}
            {error && <p className="text-destructive text-xs">{error}</p>}
        </fieldset>
    );
}
