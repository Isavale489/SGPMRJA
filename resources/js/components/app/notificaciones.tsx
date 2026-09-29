import { Link, router } from '@inertiajs/react';
import { AlertTriangle, ArrowRight, Bell, CheckCheck, OctagonAlert, RotateCcw, WifiOff, X } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';

import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { usePermisos } from '@/hooks/use-permisos';
import { cn } from '@/lib/utils';

/** Espejo de NotificacionController::sistema(). */
interface Notificacion {
    id: string;
    tipo: string;
    severidad: 'danger' | 'warning' | 'info' | 'success';
    titulo: string;
    mensaje: string;
    url: string | null;
}

const ENDPOINT = '/notificaciones/sistema';
const CADA_MS = 60_000;
// Las ocultas duran lo que dura la pestaña (como en el layout anterior).
const CLAVE_OCULTAS = 'sgp.notif.dismissed';

function leerOcultas(): Set<string> {
    try {
        return new Set(JSON.parse(sessionStorage.getItem(CLAVE_OCULTAS) ?? '[]') as string[]);
    } catch {
        return new Set();
    }
}

function guardarOcultas(ocultas: Set<string>) {
    try {
        if (ocultas.size) sessionStorage.setItem(CLAVE_OCULTAS, JSON.stringify([...ocultas]));
        else sessionStorage.removeItem(CLAVE_OCULTAS);
    } catch {
        /* almacenamiento lleno o privado: se ocultan solo en memoria */
    }
}

/**
 * Campana de notificaciones del sistema (hoy: insumos en o bajo su existencia
 * mínima). Consulta cada minuto y al abrirse; ocultar una vale para la sesión.
 */
export function Notificaciones() {
    const { puede } = usePermisos();
    const [items, setItems] = useState<Notificacion[] | null>(null);
    const [error, setError] = useState(false);
    const [ocultas, setOcultas] = useState<Set<string>>(() => leerOcultas());
    const enCurso = useRef(false);

    const consultar = useCallback(() => {
        if (enCurso.current) return;
        enCurso.current = true;
        fetch(ENDPOINT, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then((r) => {
                if (!r.ok) throw new Error(String(r.status));
                return r.json() as Promise<{ items?: Notificacion[] }>;
            })
            .then((d) => {
                setItems(Array.isArray(d.items) ? d.items : []);
                setError(false);
            })
            .catch(() => setError(true))
            .finally(() => {
                enCurso.current = false;
            });
    }, []);

    useEffect(() => {
        consultar();
        // Sin consultar con la pestaña oculta; al volver, se pone al día.
        const id = window.setInterval(() => document.visibilityState === 'visible' && consultar(), CADA_MS);
        const alVolver = () => document.visibilityState === 'visible' && consultar();
        document.addEventListener('visibilitychange', alVolver);
        return () => {
            window.clearInterval(id);
            document.removeEventListener('visibilitychange', alVolver);
        };
    }, [consultar]);

    const cambiarOcultas = (nuevas: Set<string>) => {
        setOcultas(nuevas);
        guardarOcultas(nuevas);
    };
    const visibles = (items ?? []).filter((n) => !ocultas.has(n.id));
    const cantidad = visibles.length;

    return (
        <DropdownMenu onOpenChange={(abierto) => abierto && consultar()}>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon" className="relative" aria-label={cantidad ? `Notificaciones: ${cantidad} sin revisar` : 'Notificaciones'}>
                    <Bell />
                    {cantidad > 0 && (
                        <span className="bg-destructive absolute -right-0.5 -top-0.5 grid h-4 min-w-4 place-items-center rounded-full px-1 text-[10px] font-semibold leading-none text-white tabular">
                            {cantidad > 99 ? '99+' : cantidad}
                        </span>
                    )}
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-[min(22rem,calc(100vw-2rem))] p-0">
                <div className="flex items-start justify-between gap-2 border-b px-3 py-2.5">
                    <div>
                        <p className="text-sm font-semibold">Notificaciones</p>
                        <p className="text-muted-foreground text-xs">Alertas activas del sistema</p>
                    </div>
                    {ocultas.size > 0 && (
                        <Button variant="ghost" size="icon" className="size-7" onClick={() => cambiarOcultas(new Set())} aria-label="Mostrar las notificaciones ocultas" title="Mostrar las ocultas">
                            <RotateCcw />
                        </Button>
                    )}
                </div>

                <div className="max-h-80 overflow-y-auto p-1">
                    {items === null && !error && <p className="text-muted-foreground px-3 py-6 text-center text-xs">Cargando notificaciones…</p>}
                    {error && items === null && (
                        <div className="px-3 py-6 text-center">
                            <WifiOff className="text-destructive mx-auto mb-2 size-5" aria-hidden />
                            <p className="text-sm font-medium">No se pudieron cargar</p>
                            <p className="text-muted-foreground text-xs">Reintenta más tarde.</p>
                        </div>
                    )}
                    {items !== null && cantidad === 0 && (
                        <div className="px-3 py-6 text-center">
                            <CheckCheck className="text-success mx-auto mb-2 size-5" aria-hidden />
                            <p className="text-sm font-medium">Todo al día</p>
                            <p className="text-muted-foreground text-xs">No hay notificaciones del sistema.</p>
                        </div>
                    )}
                    {visibles.map((n) => {
                        const Icono = n.severidad === 'danger' ? OctagonAlert : AlertTriangle;
                        return (
                            // La X va FUERA del ítem: dentro, Radix la tomaría como elegir el ítem (navegar y cerrar).
                            <div key={n.id} className="flex items-start gap-1">
                                <DropdownMenuItem className="min-w-0 flex-1 items-start gap-3 py-2" onSelect={() => n.url && puede('movimiento-insumo.ver') && router.visit(n.url)}>
                                    <span
                                        className={cn(
                                            'mt-0.5 grid size-7 shrink-0 place-items-center rounded-full',
                                            n.severidad === 'danger' ? 'bg-destructive/10 text-destructive' : 'bg-warning/15 text-warning',
                                        )}
                                    >
                                        <Icono className="size-4" aria-hidden />
                                    </span>
                                    <span className="min-w-0 flex-1">
                                        <span className="block text-sm font-medium">{n.titulo}</span>
                                        <span className="text-muted-foreground block text-xs">{n.mensaje}</span>
                                    </span>
                                </DropdownMenuItem>
                                <button
                                    type="button"
                                    className="text-muted-foreground hover:bg-accent hover:text-foreground mt-2 rounded p-1"
                                    aria-label={`Ocultar «${n.titulo}» en esta sesión`}
                                    title="Ocultar en esta sesión"
                                    onClick={() => cambiarOcultas(new Set([...ocultas, n.id]))}
                                >
                                    <X className="size-3.5" />
                                </button>
                            </div>
                        );
                    })}
                </div>

                {puede('movimiento-insumo.ver') && (
                    <div className="border-t p-1">
                        <DropdownMenuItem asChild className="justify-center text-xs font-medium">
                            <Link href="/movimiento-insumo/alertas">
                                Ver todas las alertas de existencias <ArrowRight className="size-3.5" />
                            </Link>
                        </DropdownMenuItem>
                    </div>
                )}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
