import { AlertTriangle, CheckCircle2, Loader2, ShoppingBag } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

import { Button } from '@/components/ui/button';
import { formatoNumero } from '@/lib/formato';
import { alCambiarStock, guardarFaltantes, type FaltanteCompra } from '@/lib/inventario';
import { cn } from '@/lib/utils';

/** Respuesta de DisponibilidadInsumoService::proyectarInsumos(). */
interface Proyeccion {
    items: {
        insumo_id: number;
        nombre: string;
        codigo: string | null;
        unidad: string;
        requerido: number;
        stock: number;
        faltante: number;
        restante: number;
        estado: 'falta' | 'ajustado' | 'ok';
    }[];
    hay_faltantes: boolean;
    hay_alertas: boolean;
}

const csrf = () => document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

/** Abre una compra prellenada con los faltantes (en otra pestaña, como antes). */
export function comprarFaltantes(faltantes: FaltanteCompra[], origen: string, urlCrearCompra: string) {
    guardarFaltantes(faltantes, origen);
    window.open(`${urlCrearCompra}?prefill=1`, '_blank', 'noopener');
}

interface Props {
    url: string;
    /** Insumo → cantidad total requerida (se agregan los repetidos). */
    requeridos?: { insumo_id: number; cantidad: number }[];
    /**
     * O bien líneas a fabricar (Cotizaciones/Pedidos): el servidor calcula los
     * insumos desde el tipo de producto y la tela (DisponibilidadInsumoService::proyectar).
     */
    lineas?: { producto_id?: number | null; tipo_producto_id?: number | null; tela_id?: number | null; cantidad: number }[];
    urlCrearCompra?: string;
    origen: string;
}

/**
 * Aviso NO bloqueante: ¿alcanza el stock para lo que se va a producir? Se
 * recalcula al cambiar lo requerido (400 ms de pausa) o el stock en otra pestaña,
 * y ofrece comprar lo que falta.
 */
export function ProyeccionInsumos({ url, requeridos, lineas, urlCrearCompra, origen }: Props) {
    const [datos, setDatos] = useState<Proyeccion>();
    const [cargando, setCargando] = useState(false);
    const [version, setVersion] = useState(0);
    const turno = useRef(0);
    const cuerpo = lineas ? { lineas } : { insumos: requeridos ?? [] };
    const vacio = lineas ? !lineas.length : !requeridos?.length;
    const clave = JSON.stringify(cuerpo);

    // Una compra procesada en otra pestaña cambia el stock: recalcular.
    useEffect(() => alCambiarStock(() => setVersion((v) => v + 1)), []);

    useEffect(() => {
        if (vacio) {
            setDatos(undefined);
            return;
        }
        const mio = ++turno.current;
        setCargando(true);
        const t = setTimeout(() => {
            fetch(url, {
                method: 'POST',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: clave,
            })
                .then((r) => (r.ok ? (r.json() as Promise<Proyeccion>) : Promise.reject(new Error(String(r.status)))))
                .then((d) => mio === turno.current && setDatos(d))
                .catch(() => mio === turno.current && setDatos(undefined))
                .finally(() => mio === turno.current && setCargando(false));
        }, 400);
        return () => clearTimeout(t);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [clave, url, version]);

    if (vacio) return null;
    const faltantes = datos?.items.filter((i) => i.estado === 'falta') ?? [];

    return (
        <section className="grid gap-2 rounded-lg border p-3" aria-live="polite" aria-busy={cargando}>
            <div className="flex flex-wrap items-center justify-between gap-2">
                <h3 className="flex items-center gap-2 text-sm font-medium">
                    {cargando ? <Loader2 className="size-4 animate-spin" /> : datos?.hay_faltantes ? <AlertTriangle className="text-destructive size-4" /> : <CheckCircle2 className="text-success size-4" />}
                    Existencia de insumos
                </h3>
                {faltantes.length > 0 && urlCrearCompra && (
                    <Button type="button" size="sm" variant="outline" onClick={() => comprarFaltantes(faltantes.map((f) => ({ insumo_id: f.insumo_id, nombre: f.nombre, cantidad: f.faltante })), origen, urlCrearCompra)}>
                        <ShoppingBag /> Comprar lo que falta
                    </Button>
                )}
            </div>
            {datos && (
                <ul className="grid gap-1 text-sm">
                    {datos.items.map((i) => (
                        <li key={i.insumo_id} className="flex flex-wrap items-baseline justify-between gap-x-3">
                            <span>{i.nombre}</span>
                            <span className={cn('tabular text-xs', i.estado === 'falta' ? 'text-destructive font-medium' : i.estado === 'ajustado' ? 'text-warning' : 'text-muted-foreground')}>
                                Necesita {formatoNumero(i.requerido)} {i.unidad} · hay {formatoNumero(i.stock)}
                                {i.estado === 'falta' ? ` · faltan ${formatoNumero(i.faltante)}` : i.estado === 'ajustado' ? ' · queda bajo el mínimo' : ''}
                            </span>
                        </li>
                    ))}
                    {datos.items.length === 0 && <li className="text-muted-foreground text-xs">Ningún insumo inventariable en juego.</li>}
                </ul>
            )}
            {datos?.hay_faltantes && <p className="text-muted-foreground text-xs">No alcanza el stock: al guardar, las órdenes no se crearán hasta que entre lo que falta.</p>}
        </section>
    );
}
