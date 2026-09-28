import { ArrowDown, ArrowUp, GripVertical } from 'lucide-react';
import { useState, type ReactNode } from 'react';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export interface Widget {
    id: string;
    titulo: string;
    /** Recibe el agarre para ponerlo en el encabezado de la tarjeta. */
    render: (agarre: ReactNode) => ReactNode;
    /** Ocupa las dos columnas. */
    ancho?: boolean;
}

function leerOrden(clave: string): string[] {
    try {
        const v = JSON.parse(window.localStorage.getItem(clave) ?? '[]');
        return Array.isArray(v) ? v.filter((x): x is string => typeof x === 'string') : [];
    } catch {
        return [];
    }
}

/**
 * Tarjetas que el usuario reordena arrastrando el agarre (o con las flechas,
 * desde el teclado). El orden se guarda en este navegador con la misma clave
 * que usaba la vista anterior, así que se conserva.
 */
export function PanelOrdenable({ clave, widgets }: { clave: string; widgets: Widget[] }) {
    const [orden, setOrden] = useState<string[]>(() => {
        const guardado = leerOrden(clave).filter((id) => widgets.some((w) => w.id === id));
        return [...guardado, ...widgets.map((w) => w.id).filter((id) => !guardado.includes(id))];
    });
    const [arrastrando, setArrastrando] = useState<string>();

    const guardar = (nuevo: string[]) => {
        setOrden(nuevo);
        try {
            window.localStorage.setItem(clave, JSON.stringify(nuevo));
        } catch {
            // almacenamiento bloqueado: el orden dura la visita
        }
    };
    const mover = (id: string, destino: number) => {
        const sin = orden.filter((x) => x !== id);
        sin.splice(Math.max(0, Math.min(destino, sin.length)), 0, id);
        guardar(sin);
    };

    return (
        <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
            {orden.map((id, i) => {
                const w = widgets.find((x) => x.id === id);
                if (!w) return null;
                const agarre = (
                    <span className="-ml-1 flex items-center">
                        <span
                            draggable
                            onDragStart={(e) => { setArrastrando(id); e.dataTransfer.effectAllowed = 'move'; }}
                            onDragEnd={() => setArrastrando(undefined)}
                            className="text-muted-foreground hover:text-foreground cursor-grab p-1 active:cursor-grabbing"
                            title="Arrastra para reordenar"
                            aria-hidden
                        >
                            <GripVertical className="size-4" />
                        </span>
                        <span className="sr-only focus-within:not-sr-only focus-within:flex">
                            <Button variant="ghost" size="icon" className="size-6" disabled={i === 0} onClick={() => mover(id, i - 1)} aria-label={`Subir «${w.titulo}»`}><ArrowUp /></Button>
                            <Button variant="ghost" size="icon" className="size-6" disabled={i === orden.length - 1} onClick={() => mover(id, i + 1)} aria-label={`Bajar «${w.titulo}»`}><ArrowDown /></Button>
                        </span>
                    </span>
                );
                return (
                    <div
                        key={id}
                        data-widget={id}
                        onDragOver={(e) => { if (arrastrando && arrastrando !== id) e.preventDefault(); }}
                        onDrop={(e) => { e.preventDefault(); if (arrastrando) mover(arrastrando, i); setArrastrando(undefined); }}
                        className={cn('min-w-0 transition-opacity', w.ancho && 'lg:col-span-2', arrastrando === id && 'opacity-50')}
                    >
                        {w.render(agarre)}
                    </div>
                );
            })}
        </div>
    );
}
