import { Link } from '@inertiajs/react';
import { ArrowLeft, ChevronRight, LockKeyholeOpen, Mail, ShieldQuestion, type LucideIcon } from 'lucide-react';

import AuthLayout from '@/layouts/auth-layout';
import { cn } from '@/lib/utils';

import { EnlaceAcceso, Intro } from '../piezas';

interface Props {
    urls: { correo: string; preguntas: string; login: string };
}

function Opcion({ href, icono: Icono, titulo, detalle, conexion, tono }: { href: string; icono: LucideIcon; titulo: string; detalle: string; conexion: string; tono: 'primary' | 'success' }) {
    return (
        <Link
            href={href}
            className={cn(
                'group flex items-center gap-4 rounded-xl border p-4 transition hover:-translate-y-0.5 hover:shadow-md',
                tono === 'primary' ? 'hover:border-primary/40' : 'hover:border-success/40',
            )}
        >
            <span className={cn('grid size-11 shrink-0 place-items-center rounded-lg', tono === 'primary' ? 'bg-primary/10 text-primary' : 'bg-success/10 text-success')}>
                <Icono className="size-5" aria-hidden />
            </span>
            <span className="min-w-0 flex-1">
                <span className="block font-semibold">{titulo}</span>
                <span className="text-muted-foreground block text-sm">{detalle}</span>
                <span className="block text-xs font-medium">{conexion}</span>
            </span>
            <ChevronRight className="text-muted-foreground size-5 transition group-hover:translate-x-0.5" aria-hidden />
        </Link>
    );
}

/** Elegir cómo recuperar la contraseña: enlace por correo o preguntas de seguridad. */
export default function Metodo({ urls }: Props) {
    return (
        <AuthLayout titulo="Recuperar contraseña" icono={LockKeyholeOpen}>
            <Intro>Elige cómo quieres recuperar tu contraseña.</Intro>
            <div className="grid gap-3">
                <Opcion href={urls.correo} icono={Mail} tono="primary" titulo="Correo electrónico" detalle="Te enviaremos un enlace para restablecerla." conexion="Requiere conexión a internet." />
                <Opcion href={urls.preguntas} icono={ShieldQuestion} tono="success" titulo="Preguntas de seguridad" detalle="Responde tus 3 preguntas configuradas." conexion="No requiere conexión a internet." />
            </div>
            <div className="text-center">
                <EnlaceAcceso href={urls.login} icono={ArrowLeft}>
                    Volver al inicio de sesión
                </EnlaceAcceso>
            </div>
        </AuthLayout>
    );
}
