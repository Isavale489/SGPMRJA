import { Check, ChevronLeft, ChevronRight } from 'lucide-react';
import { useEffect, useRef, useState, type ReactNode } from 'react';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export interface PasoAsistente {
    titulo: string;
    /** Subtítulo del paso (qué se hace en él). */
    descripcion?: string;
    contenido: ReactNode;
    /** Valida el paso antes de avanzar: devuelve el motivo si no se puede, o null. */
    validar?: () => string | null;
}

interface Props {
    pasos: PasoAsistente[];
    /** Botón(es) del último paso (p. ej. «Guardar»). */
    final: ReactNode;
    /**
     * Paso al que saltar desde fuera (p. ej. el que tiene un error del
     * servidor). Cambiar `salto.n` lo aplica aunque el índice se repita.
     */
    salto?: { paso: number; n: number };
    /** Paso inicial (p. ej. 1 al editar, si el primero quedó fijo). */
    inicial?: number;
    className?: string;
}

/**
 * Asistente por pasos (reemplaza al .wiz-stepper de las vistas Blade): los
 * marcadores numerados muestran el avance; se vuelve atrás libremente y, para
 * ir adelante, se validan todos los pasos intermedios. La validación de verdad
 * la hace el servidor al guardar: aquí solo se guía.
 */
export function Asistente({ pasos, final, salto, inicial = 0, className }: Props) {
    const [actual, setActual] = useState(inicial);
    const [aviso, setAviso] = useState<string | null>(null);
    const [alcanzado, setAlcanzado] = useState(inicial);
    const cuerpo = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (salto) ir(salto.paso, true);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [salto?.n]);

    function ir(destino: number, forzar = false) {
        const d = Math.max(0, Math.min(destino, pasos.length - 1));
        if (!forzar && d > actual) {
            for (let i = actual; i < d; i++) {
                const error = pasos[i]?.validar?.() ?? null;
                if (error) {
                    setActual(i);
                    setAviso(error);
                    return;
                }
            }
        }
        setAviso(null);
        setActual(d);
        setAlcanzado((a) => Math.max(a, d));
        // Llevar el foco al título del paso (lectores de pantalla y teclado).
        requestAnimationFrame(() => cuerpo.current?.querySelector<HTMLElement>('[data-titulo-paso]')?.focus());
    }

    const paso = pasos[actual]!;
    const ultimo = actual === pasos.length - 1;

    // Enter en un campo de un paso intermedio: avanza (con su validación) en vez de
    // enviar el formulario, que se saltaría los pasos siguientes y el resumen.
    const alPulsar = (ev: React.KeyboardEvent<HTMLDivElement>) => {
        if (ev.key !== 'Enter' || actual >= pasos.length - 1 || ev.defaultPrevented) return;
        const t = ev.target as HTMLElement;
        // Solo lo que está en este asistente: React sube los eventos a través de los
        // portales, y un diálogo de alta rápida abierto desde un paso tiene su propio Enter.
        if (!ev.currentTarget.contains(t)) return;
        if (!(t instanceof HTMLInputElement)) return;
        ev.preventDefault();
        ev.stopPropagation();
        // En un buscador sin opción elegida, Enter no hace nada (tampoco envía el formulario).
        if (t.closest('[role="combobox"],[role="listbox"]')) return;
        ir(actual + 1);
    };

    return (
        <div className={cn('grid gap-4', className)} onKeyDown={alPulsar}>
            <nav aria-label="Pasos">
                <ol className="flex items-center gap-1 overflow-x-auto pb-1">
                    {pasos.map((p, i) => {
                        const hecho = i < actual;
                        const disponible = i <= alcanzado || i === actual + 1;
                        return (
                            <li key={p.titulo} className="flex min-w-0 flex-1 items-center gap-1 last:flex-none">
                                <button
                                    type="button"
                                    onClick={() => ir(i)}
                                    disabled={!disponible}
                                    aria-current={i === actual ? 'step' : undefined}
                                    className={cn(
                                        'flex shrink-0 items-center gap-2 rounded-md px-1.5 py-1 text-sm transition-colors disabled:cursor-not-allowed',
                                        i === actual ? 'text-foreground font-medium' : 'text-muted-foreground hover:text-foreground',
                                    )}
                                >
                                    <span
                                        className={cn(
                                            'grid size-7 place-items-center rounded-full border text-xs font-semibold tabular transition-colors',
                                            i === actual ? 'bg-primary border-primary text-primary-foreground' : hecho ? 'bg-primary/10 border-primary/30 text-primary' : 'bg-background',
                                        )}
                                    >
                                        {hecho ? <Check className="size-3.5" /> : i + 1}
                                    </span>
                                    <span className="hidden sm:inline">{p.titulo}</span>
                                    <span className="sr-only sm:hidden">{p.titulo}</span>
                                </button>
                                {i < pasos.length - 1 && <span aria-hidden className={cn('h-px min-w-4 flex-1 transition-colors', i < actual ? 'bg-primary' : 'bg-border')} />}
                            </li>
                        );
                    })}
                </ol>
            </nav>

            <div ref={cuerpo} className="grid gap-4">
                <header>
                    <h2 data-titulo-paso tabIndex={-1} className="text-base font-semibold outline-none">
                        <span className="text-muted-foreground font-normal sm:hidden">
                            Paso {actual + 1} de {pasos.length}:{' '}
                        </span>
                        {paso.titulo}
                    </h2>
                    {paso.descripcion && <p className="text-muted-foreground text-sm">{paso.descripcion}</p>}
                </header>
                {paso.contenido}
                {aviso && (
                    <p role="alert" className="text-destructive text-sm">
                        {aviso}
                    </p>
                )}
            </div>

            <div className="flex flex-wrap items-center justify-between gap-2 border-t pt-4">
                <Button type="button" variant="outline" onClick={() => ir(actual - 1)} disabled={actual === 0}>
                    <ChevronLeft /> Anterior
                </Button>
                {ultimo ? (
                    <div className="flex flex-wrap gap-2">{final}</div>
                ) : (
                    <Button type="button" onClick={() => ir(actual + 1)}>
                        Siguiente <ChevronRight />
                    </Button>
                )}
            </div>
        </div>
    );
}
