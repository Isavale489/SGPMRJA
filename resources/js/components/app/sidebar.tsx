import { Link, usePage } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import { useState } from 'react';

import { Icono } from '@/components/app/icono';
import { cn } from '@/lib/utils';
import type { EnlaceNavegacion, GrupoNavegacion, ItemNavegacion } from '@/types';

const esGrupo = (i: ItemNavegacion): i is GrupoNavegacion => 'items' in i;

function contieneActivo(item: ItemNavegacion, url: string): boolean {
    return esGrupo(item) ? item.items.some((h) => contieneActivo(h, url)) : estaActivo(item, url);
}

function estaActivo(enlace: EnlaceNavegacion, url: string): boolean {
    const ruta = url.split('?')[0] ?? '';
    return enlace.url === '/' ? ruta === '/' : ruta === enlace.url || ruta.startsWith(`${enlace.url}/`);
}

function Enlace({ enlace, nivel }: { enlace: EnlaceNavegacion; nivel: number }) {
    const { url } = usePage();
    const activo = estaActivo(enlace, url);
    const clases = cn(
        'group flex items-center gap-2.5 rounded-md px-2.5 py-1.5 text-sm transition-colors duration-rapido',
        nivel > 0 && 'pl-9',
        activo
            ? 'bg-sidebar-accent text-sidebar-accent-foreground font-medium'
            : 'text-sidebar-foreground hover:bg-sidebar-accent/60 hover:text-sidebar-accent-foreground',
    );
    const contenido = (
        <>
            {nivel === 0 && <Icono nombre={enlace.icono} className="size-4 shrink-0 opacity-80" />}
            <span className="truncate">{enlace.titulo}</span>
        </>
    );

    // Módulo aún en Blade → <a> con recarga completa (un <Link> esperaría JSON de Inertia).
    return enlace.inertia ? (
        <Link href={enlace.url} className={clases} aria-current={activo ? 'page' : undefined}>
            {contenido}
        </Link>
    ) : (
        <a href={enlace.url} className={clases} aria-current={activo ? 'page' : undefined}>
            {contenido}
        </a>
    );
}

function Grupo({ grupo, nivel }: { grupo: GrupoNavegacion; nivel: number }) {
    const { url } = usePage();
    const [abierto, setAbierto] = useState(() => contieneActivo(grupo, url));

    return (
        <div>
            <button
                type="button"
                onClick={() => setAbierto((a) => !a)}
                aria-expanded={abierto}
                className={cn(
                    'text-sidebar-foreground hover:bg-sidebar-accent/60 flex w-full items-center gap-2.5 rounded-md px-2.5 py-1.5 text-sm transition-colors duration-rapido',
                    nivel > 0 && 'pl-9',
                )}
            >
                {nivel === 0 && <Icono nombre={grupo.icono} className="size-4 shrink-0 opacity-80" />}
                <span className="flex-1 truncate text-left">{grupo.titulo}</span>
                <ChevronRight
                    className={cn('size-3.5 opacity-60 transition-transform duration-medio ease-salida', abierto && 'rotate-90')}
                    aria-hidden
                />
            </button>
            {abierto && (
                <div className="mt-0.5 grid gap-0.5">
                    {grupo.items.map((h) => (
                        <Item key={h.titulo} item={h} nivel={nivel + 1} />
                    ))}
                </div>
            )}
        </div>
    );
}

function Item({ item, nivel }: { item: ItemNavegacion; nivel: number }) {
    return esGrupo(item) ? <Grupo grupo={item} nivel={nivel} /> : <Enlace enlace={item} nivel={nivel} />;
}

export function Sidebar() {
    const { navegacion } = usePage().props;

    return (
        <nav aria-label="Principal" className="grid gap-0.5 p-3">
            {navegacion.map((item) => (
                <Item key={item.titulo} item={item} nivel={0} />
            ))}
        </nav>
    );
}
