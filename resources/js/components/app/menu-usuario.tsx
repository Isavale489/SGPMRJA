import { Link, usePage } from '@inertiajs/react';
import { LogOut, Settings, UserRound } from 'lucide-react';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { usePermisos } from '@/hooks/use-permisos';

const csrf = () => document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

const iniciales = (nombre: string) =>
    nombre.split(/\s+/).slice(0, 2).map((p) => p[0]?.toUpperCase() ?? '').join('');

export function MenuUsuario() {
    const { auth } = usePage().props;
    const { puede } = usePermisos();
    if (!auth.user) return null;

    return (
        <DropdownMenu>
            <DropdownMenuTrigger className="hover:bg-accent flex items-center gap-2 rounded-md px-1.5 py-1 transition-colors duration-rapido">
                <Avatar className="size-7">
                    {auth.user.avatar_url && <AvatarImage src={auth.user.avatar_url} alt="" />}
                    <AvatarFallback className="text-xs">{iniciales(auth.user.name)}</AvatarFallback>
                </Avatar>
                <span className="hidden text-left leading-tight sm:block">
                    <span className="block text-sm font-medium">{auth.user.name}</span>
                    <span className="text-muted-foreground block text-xs">{auth.user.rol}</span>
                </span>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-52">
                <DropdownMenuLabel className="truncate font-normal">{auth.user.email}</DropdownMenuLabel>
                <DropdownMenuSeparator />
                {puede('configuracion.ver') && (
                    <DropdownMenuItem asChild>
                        <Link href="/configuracion">
                            <Settings /> Configuración
                        </Link>
                    </DropdownMenuItem>
                )}
                {/* /profile sigue en Blade → enlace normal. */}
                <DropdownMenuItem asChild>
                    <a href="/profile">
                        <UserRound /> Mi perfil
                    </a>
                </DropdownMenuItem>
                {/* Form clásico, no <Link>: /logout redirige a una página Blade y
                    Inertia mostraría ese HTML como error dentro de un modal. */}
                <form method="post" action="/logout">
                    <input type="hidden" name="_token" value={csrf()} />
                    <DropdownMenuItem asChild>
                        <button type="submit" className="w-full">
                            <LogOut /> Cerrar sesión
                        </button>
                    </DropdownMenuItem>
                </form>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
