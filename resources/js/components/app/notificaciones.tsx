import { Link, router } from '@inertiajs/react';
import { AlertTriangle, ArrowRight, Bell, CheckCheck, OctagonAlert, RotateCcw, WifiOff, X } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

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
/** Al cambiar de página el layout se vuelve a montar: no se consulta de nuevo si el dato es reciente. */
const FRESCO_MS = 30_000;
// Las ocultas duran lo que dura la pestaña (como en el layout anterior).
const CLAVE_OCULTAS = 'sgp.notif.dismissed';

// Compartido entre montajes del layout (una navegación Inertia lo remonta).
const cache: { items: Notificacion[] | null; en: number; enCurso: Promise<Notificacion[]> | null } = { items: null, en: 0, enCurso: null };

function pedir(): Promise<Notificacion[]> {
    cache.enCurso ??= fetch(ENDPOINT, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then((r) => {
            // Sin sesión (vencida, o cerrada porque la clave cambió en otro equipo): al login.
            if (r.status === 401) window.location.href = '/login';
            if (!r.ok) throw new Error(String(r.status));
            return r.json() as Promise<{ items?: Notificacion[] }>;
        })
        .then((d) => {
            cache.items = Array.isArray(d.items) ? d.items : [];
            cache.en = Date.now();
            return cache.items;
        })
        .finally(() => {
            cache.enCurso = null;
        });
    return cache.enCurso;
}

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
    const verAlertas = puede('movimiento-insumo.ver');
    const [items, setItems] = useState<Notificacion[] | null>(cache.items);
    const [error, setError] = useState(false);
    const [ocultas, setOcultas] = useState<Set<string>>(() => leerOcultas());

    const consultar = useCallback((siViejo = false) => {
        if (siViejo && cache.items && Date.now() - cache.en < FRESCO_MS) return () => undefined;
        let vigente = true;
        pedir()
            .then((lista) => {
                if (!vigente) return;
                setItems(lista);
                setError(false);
            })
            .catch(() => vigente && setError(true));
        return () => {
            vigente = false;
        };
    }, []);

    useEffect(() => {
        const cancelar = [consultar(true)];
        // Sin consultar con la pestaña oculta; al volver, se pone al día.
        const id = window.setInterval(() => document.visibilityState === 'visible' && cancelar.push(consultar()), CADA_MS);
        const alVolver = () => document.visibilityState === 'visible' && cancelar.push(consultar(true));
        document.addEventListener('visibilitychange', alVolver);
        return () => {
            window.clearInterval(id);
            document.removeEventListener('visibilitychange', alVolver);
            cancelar.forEach((c) => c());
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
                    {/* Ítems del menú (no botones sueltos): así se llega con las flechas del teclado. */}
                    {ocultas.size > 0 && (
                        <DropdownMenuItem
                            className="size-7 justify-center p-0"
                            aria-label="Mostrar las notificaciones ocultas"
                            title="Mostrar las ocultas"
                            onSelect={(e) => {
                                e.preventDefault();
                                cambiarOcultas(new Set());
                            }}
                        >
                            <RotateCcw />
                        </DropdownMenuItem>
                    )}
                </div>

                {error && items !== null && (
                    <p className="text-destructive bg-destructive/5 flex items-center gap-1.5 border-b px-3 py-1.5 text-xs">
                        <WifiOff className="size-3.5" aria-hidden /> No se pudieron actualizar; se muestran las últimas.
                    </p>
                )}

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
                        const contenido = (
                            <>
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
                            </>
                        );
                        return (
                            // Dos ítems en la fila: el aviso (navega) y la X (oculta). Si la X fuera hija del
                            // aviso, Radix tomaría su clic como elegir el aviso.
                            <div key={n.id} className="flex items-start gap-1">
                                {n.url && verAlertas ? (
                                    <DropdownMenuItem className="min-w-0 flex-1 items-start gap-3 py-2" onSelect={() => router.visit(n.url!)}>
                                        {contenido}
                                    </DropdownMenuItem>
                                ) : (
                                    // Sin permiso para ver las alertas: solo informa, no parece clicable.
                                    <div className="flex min-w-0 flex-1 items-start gap-3 px-2 py-2">{contenido}</div>
                                )}
                                <DropdownMenuItem
                                    className="text-muted-foreground mt-1.5 size-7 justify-center p-0"
                                    aria-label={`Ocultar en esta sesión: ${n.titulo}, ${n.mensaje}`}
                                    title="Ocultar en esta sesión"
                                    onSelect={(e) => {
                                        e.preventDefault(); // ocultar no cierra el menú
                                        cambiarOcultas(new Set([...ocultas, n.id]));
                                    }}
                                >
                                    <X className="size-3.5" />
                                </DropdownMenuItem>
                            </div>
                        );
                    })}
                </div>

                {verAlertas && (
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
