import { Link } from '@inertiajs/react';
import { ArrowLeft, Clock, Lock } from 'lucide-react';

import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/auth-layout';
import { cn } from '@/lib/utils';

interface Props {
    /** soft: bloqueo temporal; hard: lo desbloquea un administrador. */
    tipo: 'soft' | 'hard';
    /** Hora de fin del bloqueo temporal, en hora de Venezuela (HH:mm). */
    hasta: string | null;
    urls: { login: string };
}

/** La recuperación de esta cuenta está bloqueada por intentos fallidos. */
export default function Bloqueo({ tipo, hasta, urls }: Props) {
    const total = tipo === 'hard';
    const Icono = total ? Lock : Clock;

    return (
        <AuthLayout titulo="Acceso bloqueado" icono={Lock}>
            <span className={cn('mx-auto grid size-16 place-items-center rounded-full', total ? 'bg-destructive/10 text-destructive' : 'bg-warning/15 text-warning')}>
                <Icono className="size-8" aria-hidden />
            </span>
            <div role="alert" className={cn('rounded-lg border p-4 text-center text-sm', total ? 'border-destructive/30 bg-destructive/5' : 'border-warning/30 bg-warning/10')}>
                {total ? (
                    <>
                        <p className="mb-1 font-semibold">Recuperación bloqueada</p>
                        <p>
                            Tu cuenta excedió el número máximo de intentos fallidos de recuperación. Contacta al <strong>administrador del sistema</strong> para desbloquearla.
                        </p>
                    </>
                ) : (
                    <>
                        <p className="mb-1 font-semibold">Demasiados intentos</p>
                        <p>
                            Por seguridad, hemos bloqueado temporalmente la recuperación de esta cuenta.{' '}
                            {hasta ? (
                                <>
                                    Inténtalo de nuevo después de las <strong className="tabular">{hasta}</strong>.
                                </>
                            ) : (
                                'Inténtalo de nuevo más tarde.'
                            )}
                        </p>
                    </>
                )}
            </div>
            <Button asChild className="h-11 justify-self-center px-6 font-semibold">
                <Link href={urls.login}>
                    <ArrowLeft /> Volver al inicio de sesión
                </Link>
            </Button>
        </AuthLayout>
    );
}
