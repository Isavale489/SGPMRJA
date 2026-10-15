import { Head, usePage } from '@inertiajs/react';
import { Menu, Moon, PanelLeftClose, PanelLeftOpen, Sun } from 'lucide-react';
import { useEffect, useLayoutEffect, useState, type ReactNode } from 'react';
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
import { useMenuColapsado } from '@/hooks/use-menu-colapsado';
import { useTema } from '@/hooks/use-tema';
import { enlaceActivo } from '@/lib/navegacion';
import { cn } from '@/lib/utils';

interface Props {
    titulo: string;
    /** Acciones a la derecha del título (p. ej. "Nuevo proveedor"). */
    acciones?: ReactNode;
    children: ReactNode;
}

/**
 * Logo en la barra superior, como el layout «detached» anterior. Va directo sobre el navy:
 * el logo está hecho para fondo oscuro («Manufacturas R.J.» es texto blanco).
 */
function Logo({ nombre }: { nombre: string }) {
    return (
        // Por debajo de lg (teléfono y tableta) no cabe junto a las utilidades: va dentro del menú desplegable.
        <a href="/dashboard" className="hidden h-14 shrink-0 items-center px-1 lg:flex">
            <img src="/atlantico-logo-wide.png" alt={nombre} className="h-14 w-auto object-contain" />
        </a>
    );
}

/** Layout de toda página Inertia del panel: sidebar, barra superior, título y avisos. */
export default function AppLayout({ titulo, acciones, children }: Props) {
    const { flash, app, seccion, navegacion } = usePage().props;
    const { url } = usePage();
    const { tema, alternar } = useTema();
    const menu = useMenuColapsado();
    // Grupo elegido desde el menú colapsado: se abre al expandir.
    const [grupoAbierto, setGrupoAbierto] = useState<string | null>(null);
    const expandirEn = (grupo: string) => {
        setGrupoAbierto(grupo);
        menu.setColapsado(false);
    };
    // El botón de la barra no abre ningún grupo: el elegido antes no debe volver a abrirse.
    const alternarMenu = () => {
        setGrupoAbierto(null);
        menu.alternar();
    };
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
            {/*
              * Barra navy de lado a lado (layout «detached» del panel anterior): logo, botón del menú
              * y utilidades. `dark` la vuelve una isla oscura en ambos temas: botones, textos y
              * píldoras toman los tokens oscuros sin estilos propios.
              */}
            <header className="dark bg-topbar text-foreground sticky top-0 z-30 flex h-16 items-center gap-0.5 px-1.5 shadow-[0_2px_10px_rgb(15_26_49/0.28)] sm:gap-3 sm:px-4 [&_[data-pildora]]:border-white/20 [&_[data-pildora]]:bg-white/10 [&_[data-pildora]]:backdrop-blur-sm">
                {/* Menú en pantallas chicas: el sidebar fijo solo existe desde lg. */}
                <Sheet>
                    <SheetTrigger asChild>
                        <Button variant="ghost" size="icon" className="lg:hidden" aria-label="Abrir menú">
                            <Menu />
                        </Button>
                    </SheetTrigger>
                    {/* Franja navy con el logo (hecho para fondo oscuro); el botón de cerrar va en blanco encima. */}
                    <SheetContent side="left" className="bg-sidebar w-72 p-0 [&>button]:text-white [&>button]:opacity-85">
                        <SheetTitle className="sr-only">Menú</SheetTitle>
                        <div className="bg-topbar flex h-16 shrink-0 items-center px-4">
                            <img src="/atlantico-logo-wide.png" alt="" className="h-12 w-auto" />
                        </div>
                        {/* Con desplazamiento: en un teléfono bajo, con grupos abiertos, el menú no cabe. */}
                        <div className="min-h-0 flex-1 overflow-y-auto">
                            <Sidebar />
                        </div>
                    </SheetContent>
                </Sheet>
                <Logo nombre={app.nombre} />
                <Button
                    variant="ghost"
                    size="icon"
                    className="hidden lg:inline-flex"
                    onClick={alternarMenu}
                    aria-label={menu.colapsado ? 'Expandir el menú' : 'Contraer el menú'}
                    aria-expanded={!menu.colapsado}
                    aria-controls="menu-lateral"
                >
                    {menu.colapsado ? <PanelLeftOpen /> : <PanelLeftClose />}
                </Button>
                {/* Empresa y sistema (como el layout anterior); solo si hay espacio. */}
                <div className="min-w-0 flex-1 text-center">
                    <p className="hidden truncate text-sm font-semibold leading-tight text-white xl:block">Manufacturas R.J. Atlántico</p>
                    <p className="text-muted-foreground hidden truncate text-xs leading-tight xl:block">
                        Software para la gestión de pedidos en Manufacturas R.J. Atlántico C.A.
                    </p>
                </div>
                {/* Desde xl: entre 640 y 1279 px la barra no tiene espacio para el reloj. */}
                <span className="hidden xl:inline-flex">
                    <Reloj />
                </span>
                <TasaBcv />
                <Button variant="ghost" size="icon" onClick={alternar} aria-label={tema === 'dark' ? 'Usar tema claro' : 'Usar tema oscuro'}>
                    {tema === 'dark' ? <Sun /> : <Moon />}
                </Button>
                <span className="hidden sm:contents">
                    <PantallaCompleta />
                </span>
                <Notificaciones />
                <MenuUsuario />
            </header>

            <div className="flex">
                {/* Debajo de la barra, fijo al desplazar la página; ancho o solo íconos. */}
                <aside
                    id="menu-lateral"
                    className={cn(
                        'border-sidebar-border bg-sidebar sticky top-16 hidden h-[calc(100svh-4rem)] shrink-0 flex-col border-r transition-[width] duration-medio ease-salida lg:flex',
                        menu.colapsado ? 'w-[4.5rem]' : 'w-64',
                    )}
                >
                    <div className="flex-1 overflow-y-auto overflow-x-hidden">
                        <Sidebar colapsado={menu.colapsado} onExpandir={expandirEn} grupoAbierto={grupoAbierto} />
                    </div>
                </aside>

                <div className="flex min-h-[calc(100svh-4rem)] min-w-0 flex-1 flex-col">
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
