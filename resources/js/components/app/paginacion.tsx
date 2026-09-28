import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

import { cn } from '@/lib/utils';
import { formatoNumero } from '@/lib/formato';
import type { Paginado } from '@/types';

/** Paginación del LengthAwarePaginator de Laravel (conserva los filtros de la URL). */
export function Paginacion<T>({ pagina, only }: { pagina: Paginado<T>; only: string[] }) {
    if (pagina.total === 0) return null;

    // links: [« Anterior, 1, 2, …, Siguiente »] — los extremos se dibujan como flechas.
    const numeros = pagina.links.slice(1, -1);
    const anterior = pagina.links[0];
    const siguiente = pagina.links[pagina.links.length - 1];
    const boton = 'inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2 text-sm transition-colors duration-rapido';

    return (
        <nav aria-label="Paginación" className="flex flex-wrap items-center justify-between gap-3 text-sm">
            <p className="text-muted-foreground tabular">
                {formatoNumero(pagina.from ?? 0)}–{formatoNumero(pagina.to ?? 0)} de {formatoNumero(pagina.total)}
            </p>
            <div className="flex items-center gap-1">
                <Enlace url={anterior?.url} only={only} className={boton} etiqueta="Página anterior">
                    <ChevronLeft className="size-4" />
                </Enlace>
                {numeros.map((l, i) =>
                    l.url ? (
                        <Link
                            key={i}
                            href={l.url}
                            only={only}
                            preserveScroll
                            preserveState
                            aria-current={l.active ? 'page' : undefined}
                            className={cn(boton, l.active ? 'bg-primary text-primary-foreground' : 'hover:bg-accent')}
                        >
                            {l.label}
                        </Link>
                    ) : (
                        <span key={i} className={cn(boton, 'text-muted-foreground')}>…</span>
                    ),
                )}
                <Enlace url={siguiente?.url} only={only} className={boton} etiqueta="Página siguiente">
                    <ChevronRight className="size-4" />
                </Enlace>
            </div>
        </nav>
    );
}

function Enlace({ url, only, className, etiqueta, children }: { url?: string | null; only: string[]; className: string; etiqueta: string; children: React.ReactNode }) {
    return url ? (
        <Link href={url} only={only} preserveScroll preserveState aria-label={etiqueta} className={cn(className, 'hover:bg-accent')}>
            {children}
        </Link>
    ) : (
        <span aria-disabled className={cn(className, 'text-muted-foreground/50')} aria-label={etiqueta}>
            {children}
        </span>
    );
}
