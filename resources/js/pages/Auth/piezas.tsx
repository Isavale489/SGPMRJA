import { Link } from '@inertiajs/react';
import { Eye, EyeOff, Loader2, type LucideIcon } from 'lucide-react';
import { useState, type ComponentProps, type ReactNode } from 'react';

import { Campo } from '@/components/app/campo';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

/** Campo con icono a la izquierda y, si es contraseña, botón para mostrarla. */
export function CampoAcceso({
    etiqueta,
    icono: Icono,
    error,
    ayuda,
    clave,
    className,
    ...input
}: { etiqueta: string; icono: LucideIcon; error?: string; ayuda?: string; clave?: boolean; className?: string } & ComponentProps<typeof Input>) {
    const [visible, setVisible] = useState(false);
    return (
        <Campo etiqueta={etiqueta} error={error} ayuda={ayuda} className={className}>
            {(control) => (
                <div className="relative">
                    <Icono className="text-primary pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2" aria-hidden />
                    {/* control primero: sus aria-* en undefined no deben pisar los que pasa la página. */}
                    <Input {...control} {...input} aria-invalid={input['aria-invalid'] ?? control['aria-invalid']} aria-describedby={input['aria-describedby'] ?? control['aria-describedby']} type={clave ? (visible ? 'text' : 'password') : input.type} className={cn('h-11 pl-9', clave && 'pr-10')} />
                    {clave && (
                        <button
                            type="button"
                            onClick={() => setVisible((v) => !v)}
                            aria-label={visible ? 'Ocultar la contraseña' : 'Mostrar la contraseña'}
                            aria-pressed={visible}
                            className="text-muted-foreground hover:text-foreground absolute right-2 top-1/2 grid size-7 -translate-y-1/2 place-items-center rounded"
                        >
                            {visible ? <EyeOff className="size-4" /> : <Eye className="size-4" />}
                        </button>
                    )}
                </div>
            )}
        </Campo>
    );
}

/** Botón de envío con el estado «procesando». */
export function BotonAcceso({ procesando, texto, textoProcesando, icono: Icono, id, className }: { procesando: boolean; texto: string; textoProcesando: string; icono: LucideIcon; id?: string; className?: string }) {
    return (
        <Button type="submit" id={id} disabled={procesando} className={cn('h-11 px-6 font-semibold', className)}>
            {procesando ? <Loader2 className="animate-spin" /> : <Icono />}
            {procesando ? textoProcesando : texto}
        </Button>
    );
}

/** Enlace de navegación entre pantallas de acceso. */
export function EnlaceAcceso({ href, icono: Icono, children }: { href: string; icono?: LucideIcon; children: ReactNode }) {
    return (
        <Link href={href} className="text-primary inline-flex items-center gap-1 text-sm hover:underline">
            {Icono && <Icono className="size-4" aria-hidden />}
            {children}
        </Link>
    );
}

/** Texto introductorio de la tarjeta. */
export function Intro({ children }: { children: ReactNode }) {
    return <p className="text-muted-foreground text-sm">{children}</p>;
}
