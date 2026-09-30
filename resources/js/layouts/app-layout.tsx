import { Head, usePage } from '@inertiajs/react';
import { Menu, Moon, Sun } from 'lucide-react';
import { useEffect, useLayoutEffect, type ReactNode } from 'react';
import { toast } from 'sonner';

import { Confirmador } from '@/components/app/confirmador';
import { Icono } from '@/components/app/icono';
import { MenuUsuario } from '@/components/app/menu-usuario';
import { Notificaciones } from '@/components/app/notificaciones';
import { PantallaCompleta } from '@/components/app/pantalla-completa';
import { Reloj } from '@/components/app/reloj';
import { Sidebar } from '@/components/app/sidebar';
import { TasaBcv } from '@/components/app/tasa-bcv';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { useTema } from '@/hooks/use-tema';
import { enlaceActivo } from '@/lib/navegacion';

interface Props {
    titulo: string;
    /** Acciones a la derecha del título (p. ej. "Nuevo proveedor"). */
    acciones?: ReactNode;
    children: ReactNode;
}

function Logo({ nombre }: { nombre: string }) {
    return (
        // El logo es ancho (≈2:1): a 80 px de alto ocupa ~166 px del sidebar de 256.
        <a href="/dashboard" className="flex h-24 items-center justify-center px-5">
            <img src="/atlantico-logo-wide.png" alt={nombre} className="h-20 w-auto max-w-full object-contain dark:brightness-0 dark:invert" />
        </a>
    );
}

/** Layout de toda página Inertia del panel: sidebar, barra superior, título y avisos. */
export default function AppLayout({ titulo, acciones, children }: Props) {
    const { flash, app, seccion, navegacion } = usePage().props;
    const { url } = usePage();
    const { tema, alternar } = useTema();
    // Ícono de la página: el de su enlace en el menú; si no tiene (Configuración), el de la sección.
    const icono = enlaceActivo(navegacion, url)?.icono ?? seccion?.icono;

    // La sección va en <html>, no en el layout: los diálogos y menús que Radix monta
    // fuera del árbol también heredan sus colores. Antes del pintado, sin parpadeo.
    useLayoutEffect(() => {
        const raiz = document.documentElement;
        if (seccion) raiz.dataset.seccion = seccion.clave;
        else delete raiz.dataset.seccion;
        // Al salir hacia una página sin AppLayout (Error) no debe quedar la sección anterior.
        return () => {
            delete raiz.dataset.seccion;
        };
    }, [seccion]);

    // Mensajes flash del servidor (redirect()->with('success', ...)) → aviso.
    useEffect(() => {
        if (flash.success) toast.success(flash.success);
        if (flash.error) toast.error(flash.error);
    }, [flash.success, flash.error]);

    return (
        <TooltipProvider delayDuration={300}>
            <Head title={titulo} />
            <div className="flex min-h-svh">
                <aside className="border-sidebar-border bg-sidebar sticky top-0 hidden h-svh w-64 shrink-0 flex-col border-r lg:flex">
                    <Logo nombre={app.nombre} />
                    <div className="flex-1 overflow-y-auto">
                        <Sidebar />
                    </div>
                </aside>

                <div className="flex min-w-0 flex-1 flex-col">
                    {/*
                      * Barra navy del panel anterior. `dark` la vuelve una isla oscura en ambos temas:
                      * botones, textos y píldoras toman los tokens oscuros sin estilos propios.
                      */}
                    <header className="dark bg-topbar text-foreground sticky top-0 z-30 flex h-14 items-center gap-1 px-2 shadow-[0_2px_10px_rgb(15_26_49/0.28)] sm:gap-3 sm:px-6 [&_[data-pildora]]:border-white/20 [&_[data-pildora]]:bg-white/10 [&_[data-pildora]]:backdrop-blur-sm">
                        {/* Menú en pantallas chicas: el sidebar fijo solo existe desde lg. */}
                        <Sheet>
                            <SheetTrigger asChild>
                                <Button variant="ghost" size="icon" className="lg:hidden" aria-label="Abrir menú">
                                    <Menu />
                                </Button>
                            </SheetTrigger>
                            <SheetContent side="left" className="bg-sidebar w-72 p-0">
                                <SheetTitle className="sr-only">Menú</SheetTitle>
                                <Logo nombre={app.nombre} />
                                {/* Con desplazamiento: en un teléfono bajo, con grupos abiertos, el menú no cabe. */}
                                <div className="min-h-0 flex-1 overflow-y-auto">
                                    <Sidebar />
                                </div>
                            </SheetContent>
                        </Sheet>
                        {/* Empresa y sistema (como el layout anterior); solo si hay espacio. */}
                        <div className="min-w-0 flex-1 text-center">
                            <p className="hidden truncate text-sm font-semibold leading-tight text-white xl:block">Manufacturas R.J. Atlántico</p>
                            <p className="text-muted-foreground hidden truncate text-xs leading-tight xl:block">
                                Software para la gestión de pedidos en Manufacturas R.J. Atlántico C.A.
                            </p>
                        </div>
                        <span className="hidden md:inline-flex">
                            <Reloj />
                        </span>
                        <TasaBcv />
                        <Button
                            variant="ghost"
                            size="icon"
                            onClick={alternar}
                            aria-label={tema === 'dark' ? 'Usar tema claro' : 'Usar tema oscuro'}
                        >
                            {tema === 'dark' ? <Sun /> : <Moon />}
                        </Button>
                        <span className="hidden sm:contents">
                            <PantallaCompleta />
                        </span>
                        <Notificaciones />
                        <MenuUsuario />
                    </header>

                    {/* min-w-0: sin esto, una tabla ancha estira la página en vez de desplazarse dentro de su contenedor. */}
                    <main className="mx-auto w-full min-w-0 max-w-7xl flex-1 px-4 py-6 sm:px-6">
                        <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                            <div className="flex min-w-0 items-center gap-3">
                                {icono && (
                                    <span className="bg-seccion-degradado grid size-10 shrink-0 place-items-center rounded-xl text-white shadow-[0_6px_14px_-6px_var(--seccion-acento)]" aria-hidden>
                                        <Icono nombre={icono} className="size-5" />
                                    </span>
                                )}
                                <div className="min-w-0">
                                    {seccion && <p className="text-seccion text-[0.7rem] font-semibold uppercase tracking-[0.1em]">{seccion.titulo}</p>}
                                    <h1 className="text-xl font-semibold tracking-tight">{titulo}</h1>
                                </div>
                            </div>
                            {acciones && <div className="flex flex-wrap items-center gap-2">{acciones}</div>}
                        </div>
                        {children}
                    </main>

                    <footer className="text-muted-foreground border-border border-t px-4 py-3 text-xs sm:px-6">
                        <div className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-4 gap-y-1">
                            <span>© {new Date().getFullYear()} Grupo Textil 636 Informática</span>
                            <span>Manufacturas R.J. Atlántico</span>
                        </div>
                    </footer>
                </div>
            </div>
            <Toaster richColors position="top-right" />
            <Confirmador />
        </TooltipProvider>
    );
}
