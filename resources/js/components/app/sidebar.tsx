import { Link, usePage } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

import { Icono } from '@/components/app/icono';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { contieneActivo, esGrupo, estaActivo } from '@/lib/navegacion';
import { cn } from '@/lib/utils';
import type { EnlaceNavegacion, GrupoNavegacion, ItemNavegacion } from '@/types';

/*
 * Sangría por anidamiento: cada nivel cuelga de una línea guía tenue (del color de su sección)
 * y su texto queda alineado con el del padre. Todo elemento clicable lleva el riel izquierdo
 * de 3 px (transparente salvo en el activo) y, dentro de un grupo, se monta sobre la línea
 * (-ml-px) para que el riel del activo la tape en vez de quedar al lado.
 */
const BASE = 'flex items-center gap-2.5 rounded-md border-l-[3px] border-transparent py-1.5 pr-2.5 text-sm transition-colors duration-rapido';
const sangria = (nivel: number) => (nivel === 0 ? 'pl-2.5' : '-ml-px pl-4');

function Enlace({ enlace, nivel }: { enlace: EnlaceNavegacion; nivel: number }) {
    const { url } = usePage();
    const activo = estaActivo(enlace, url);
    const clases = cn(
        'group',
        BASE,
        sangria(nivel),
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

interface PropsGrupo {
    grupo: GrupoNavegacion;
    nivel: number;
    abierto: boolean;
    onAlternar: () => void;
    /** Elegido desde el menú colapsado: toma el foco al montarse. */
    enfocar?: boolean;
}

function Grupo({ grupo, nivel, abierto, onAlternar, enfocar }: PropsGrupo) {
    const { url } = usePage();
    const conActivo = contieneActivo(grupo, url);
    const boton = useRef<HTMLButtonElement>(null);
    // Elegido desde el menú colapsado: el foco sigue en el grupo (el botón de antes se desmontó).
    useEffect(() => {
        if (enfocar) boton.current?.focus();
        // eslint-disable-next-line react-hooks/exhaustive-deps -- solo al montarse
    }, []);

    return (
        // Cada sección se pinta con su color (config/secciones.php), sea cual sea la página abierta.
        <div data-seccion={grupo.seccion ?? undefined}>
            <button
                ref={boton}
                type="button"
                onClick={onAlternar}
                aria-expanded={abierto}
                className={cn(
                    'hover:bg-seccion-acento/8',
                    // El botón no se estira solo como un <a> en grid: con -ml-px le falta ese píxel.
                    nivel === 0 ? 'w-full' : 'w-[calc(100%+1px)]',
                    BASE,
                    sangria(nivel),
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
                // La línea cae bajo el ícono del padre (nivel 0) o bajo el inicio de su texto.
                <div className={cn('border-seccion-acento/25 mt-0.5 border-l', nivel === 0 ? 'ml-5' : 'ml-[1.125rem]')}>
                    <Lista items={grupo.items} nivel={nivel + 1} />
                </div>
            )}
        </div>
    );
}

/**
 * Hermanos de un mismo nivel, en acordeón: un solo grupo abierto a la vez. Al entrar abre el
 * que contiene la página activa (o el elegido desde el menú colapsado); se pueden cerrar todos.
 */
function Lista({ items, nivel, inicial }: { items: ItemNavegacion[]; nivel: number; inicial?: string | null }) {
    const { url } = usePage();
    const [abierto, setAbierto] = useState<string | null>(
        () => inicial ?? items.find((i) => esGrupo(i) && contieneActivo(i, url))?.titulo ?? null,
    );

    return (
        <div className="grid gap-0.5">
            {items.map((item) =>
                esGrupo(item) ? (
                    <Grupo
                        key={item.titulo}
                        grupo={item}
                        nivel={nivel}
                        abierto={abierto === item.titulo}
                        onAlternar={() => setAbierto((a) => (a === item.titulo ? null : item.titulo))}
                        enfocar={nivel === 0 && item.titulo === inicial}
                    />
                ) : nivel === 0 ? (
                    // Enlace suelto (Inicio): azul de marca, no el color de la página abierta.
                    <div key={item.titulo} data-seccion="marca" className="grid">
                        <Enlace enlace={item} nivel={0} />
                    </div>
                ) : (
                    <Enlace key={item.titulo} enlace={item} nivel={nivel} />
                ),
            )}
        </div>
    );
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
                                    <button type="button" className={clases(activo)} aria-label={item.titulo} aria-expanded={false} aria-controls="menu-lateral" onClick={() => onExpandir(item.titulo)}>
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
        <nav aria-label="Principal" className="p-3">
            <Lista items={navegacion} nivel={0} inicial={grupoAbierto} />
        </nav>
    );
}
