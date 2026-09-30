import { Link, usePage } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import { useState } from 'react';

import { Icono } from '@/components/app/icono';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { contieneActivo, esGrupo, estaActivo } from '@/lib/navegacion';
import { cn } from '@/lib/utils';
import type { EnlaceNavegacion, GrupoNavegacion, ItemNavegacion } from '@/types';

function Enlace({ enlace, nivel }: { enlace: EnlaceNavegacion; nivel: number }) {
    const { url } = usePage();
    const activo = estaActivo(enlace, url);
    const clases = cn(
        'group flex items-center gap-2.5 rounded-md px-2.5 py-1.5 text-sm transition-colors duration-rapido',
        nivel > 0 && 'pl-9',
        // Riel izquierdo siempre presente (transparente) para que el texto no salte al activarse.
        'border-l-[3px] border-transparent',
        activo
            ? 'bg-seccion-acento/12 text-seccion border-seccion-acento font-semibold'
            : 'text-sidebar-foreground hover:bg-seccion-acento/8 hover:text-seccion',
    );
    const contenido = (
        <>
            {nivel === 0 && <Icono nombre={enlace.icono} className={cn('size-4 shrink-0', activo ? 'text-seccion' : 'opacity-80')} />}
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

function Grupo({ grupo, nivel, abrirAlInicio }: { grupo: GrupoNavegacion; nivel: number; abrirAlInicio?: boolean }) {
    const { url } = usePage();
    const [abierto, setAbierto] = useState(() => abrirAlInicio || contieneActivo(grupo, url));
    const conActivo = contieneActivo(grupo, url);

    return (
        // Cada sección se pinta con su color (config/secciones.php), sea cual sea la página abierta.
        <div data-seccion={grupo.seccion ?? undefined}>
            <button
                type="button"
                onClick={() => setAbierto((a) => !a)}
                aria-expanded={abierto}
                className={cn(
                    'hover:bg-seccion-acento/8 flex w-full items-center gap-2.5 rounded-md px-2.5 py-1.5 text-sm transition-colors duration-rapido',
                    nivel > 0 && 'pl-9',
                    conActivo ? 'text-seccion font-medium' : 'text-sidebar-foreground',
                )}
            >
                {nivel === 0 && <Icono nombre={grupo.icono} className="text-seccion size-4 shrink-0" />}
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

function Item({ item, nivel, abrirAlInicio }: { item: ItemNavegacion; nivel: number; abrirAlInicio?: boolean }) {
    return esGrupo(item) ? <Grupo grupo={item} nivel={nivel} abrirAlInicio={abrirAlInicio} /> : <Enlace enlace={item} nivel={nivel} />;
}

/**
 * Menú colapsado (solo íconos, como el modo «sm» del panel anterior): cada ícono con el
 * color de su sección y su nombre en un tooltip. Un grupo no despliega un flyout: al
 * hacer clic se expande el menú con ese grupo abierto.
 */
function MenuColapsado({ onExpandir }: { onExpandir: (grupo: string) => void }) {
    const { navegacion } = usePage().props;
    const { url } = usePage();
    const clases = (activo: boolean) =>
        cn(
            'grid size-10 place-items-center rounded-md border-l-[3px] border-transparent transition-colors duration-rapido',
            activo ? 'bg-seccion-acento/12 text-seccion border-seccion-acento' : 'text-seccion hover:bg-seccion-acento/8',
        );

    return (
        <nav aria-label="Principal" className="grid justify-items-center gap-1 p-3">
            {navegacion.map((item) => {
                const activo = contieneActivo(item, url);
                return (
                    <div key={item.titulo} data-seccion={esGrupo(item) ? (item.seccion ?? undefined) : 'marca'}>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                {esGrupo(item) ? (
                                    <button type="button" className={clases(activo)} aria-label={item.titulo} onClick={() => onExpandir(item.titulo)}>
                                        <Icono nombre={item.icono} className="size-5" />
                                    </button>
                                ) : item.inertia ? (
                                    <Link href={item.url} className={clases(activo)} aria-label={item.titulo} aria-current={activo ? 'page' : undefined}>
                                        <Icono nombre={item.icono} className="size-5" />
                                    </Link>
                                ) : (
                                    <a href={item.url} className={clases(activo)} aria-label={item.titulo} aria-current={activo ? 'page' : undefined}>
                                        <Icono nombre={item.icono} className="size-5" />
                                    </a>
                                )}
                            </TooltipTrigger>
                            <TooltipContent side="right">{item.titulo}</TooltipContent>
                        </Tooltip>
                    </div>
                );
            })}
        </nav>
    );
}

interface Props {
    /** Solo íconos (escritorio). */
    colapsado?: boolean;
    /** Desde el modo colapsado: expandir el menú con ese grupo abierto. */
    onExpandir?: (grupo: string) => void;
    /** Grupo que abre al montarse (el que se eligió en el modo colapsado). */
    grupoAbierto?: string | null;
}

export function Sidebar({ colapsado = false, onExpandir, grupoAbierto }: Props) {
    const { navegacion } = usePage().props;

    if (colapsado && onExpandir) return <MenuColapsado onExpandir={onExpandir} />;

    return (
        <nav aria-label="Principal" className="grid gap-0.5 p-3">
            {navegacion.map((item) =>
                esGrupo(item) ? (
                    <Item key={item.titulo} item={item} nivel={0} abrirAlInicio={item.titulo === grupoAbierto} />
                ) : (
                    // Enlace suelto (Inicio): azul de marca, no el color de la página abierta.
                    <div key={item.titulo} data-seccion="marca">
                        <Item item={item} nivel={0} />
                    </div>
                ),
            )}
        </nav>
    );
}
