import { Head, usePage } from '@inertiajs/react';
import { Menu, Moon, Sun } from 'lucide-react';
import { useEffect, type ReactNode } from 'react';
import { toast } from 'sonner';

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

interface Props {
    titulo: string;
    /** Acciones a la derecha del título (p. ej. "Nuevo proveedor"). */
    acciones?: ReactNode;
    children: ReactNode;
}

function Logo({ nombre }: { nombre: string }) {
    return (
        <a href="/dashboard" className="flex h-14 items-center gap-2 px-5">
            <img src="/atlantico-logo-wide.png" alt={nombre} className="h-7 w-auto dark:brightness-0 dark:invert" />
        </a>
    );
}

/** Layout de toda página Inertia del panel: sidebar, barra superior, título y avisos. */
export default function AppLayout({ titulo, acciones, children }: Props) {
    const { flash, app } = usePage().props;
    const { tema, alternar } = useTema();

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
                    <header className="bg-background/85 border-border sticky top-0 z-30 flex h-14 items-center gap-1 border-b px-2 backdrop-blur sm:gap-3 sm:px-6">
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
                                <Sidebar />
                            </SheetContent>
                        </Sheet>
                        {/* Empresa y sistema (como el layout anterior); solo si hay espacio. */}
                        <div className="min-w-0 flex-1 text-center">
                            <p className="hidden truncate text-sm font-semibold leading-tight xl:block">Manufacturas R.J. Atlántico</p>
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
                            <h1 className="text-xl font-semibold tracking-tight">{titulo}</h1>
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
        </TooltipProvider>
    );
}
