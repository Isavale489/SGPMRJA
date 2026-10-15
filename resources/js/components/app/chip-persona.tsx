import { iniciales } from '@/lib/formato';
import { cn } from '@/lib/utils';

interface Props {
    /** Rótulo pequeño encima del nombre («Cliente», «Creada por»). */
    rol: string;
    nombre: string;
    /** Documento o dato corto junto al nombre (va en monoespaciada). */
    detalle?: string | null;
    /** Foto; sin ella, las iniciales sobre el degradado de la sección. */
    avatar?: string | null;
    className?: string;
}

/**
 * Píldora con una persona del documento («para quién» y «quién lo creó»): el chip de
 * cliente del asistente del panel anterior. Toma el color de la sección vigente.
 */
export function ChipPersona({ rol, nombre, detalle, avatar, className }: Props) {
    return (
        <span
            data-slot="chip-persona"
            className={cn(
                'border-seccion-acento/25 bg-seccion-acento/[0.06] inline-flex max-w-full items-center gap-2.5 rounded-full border py-1 pr-3.5 pl-1 shadow-[0_2px_8px_-4px_var(--seccion-acento)]',
                className,
            )}
        >
            {avatar ? (
                <img src={avatar} alt="" className="size-8 shrink-0 rounded-full object-cover" />
            ) : (
                <span className="bg-seccion-degradado grid size-8 shrink-0 place-items-center rounded-full text-[0.7rem] font-bold text-white" aria-hidden>
                    {iniciales(nombre)}
                </span>
            )}
            <span className="grid min-w-0 leading-tight">
                <span className="text-muted-foreground text-[0.68rem] font-semibold uppercase tracking-[0.08em]">{rol}</span>
                <span className="text-seccion truncate text-sm font-bold">
                    {nombre}
                    {detalle && <span className="text-muted-foreground ml-1.5 font-mono text-xs font-normal">{detalle}</span>}
                </span>
            </span>
        </span>
    );
}
