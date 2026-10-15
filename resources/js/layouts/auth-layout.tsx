import { Head, usePage } from '@inertiajs/react';
import { CircleCheck, Clock, Moon, Sun, type LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';

import { useTema } from '@/hooks/use-tema';

interface Props {
    /** Título de la tarjeta (y de la pestaña). */
    titulo: string;
    icono: LucideIcon;
    children: ReactNode;
}

/**
 * Pantallas de acceso (login y recuperación de contraseña): fondo azul del
 * sistema, logo, tarjeta con franja de título y avisos del servidor. Mismo
 * diseño que tenía el layout Blade `<x-guest-layout>`.
 */
export default function AuthLayout({ titulo, icono: Icono, children }: Props) {
    const { flash } = usePage().props;
    const { tema, alternar } = useTema();

    return (
        <>
            <Head title={titulo} />
            <div className="relative grid min-h-svh place-items-center bg-[linear-gradient(135deg,#0f2044_0%,#1e3c72_45%,#2a5298_100%)] px-4 py-10 dark:bg-[linear-gradient(135deg,#080e1c_0%,#111827_50%,#1a2540_100%)]">
                {/* Puntos sutiles de fondo. */}
                <div
                    aria-hidden
                    className="pointer-events-none fixed inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.04)_1px,transparent_1px),radial-gradient(circle_at_80%_80%,rgba(255,255,255,0.04)_1px,transparent_1px)] bg-[length:60px_60px]"
                />
                <button
                    type="button"
                    onClick={alternar}
                    aria-label={tema === 'dark' ? 'Usar tema claro' : 'Usar tema oscuro'}
                    className="fixed right-4 top-4 z-10 grid size-10 place-items-center rounded-full border border-white/25 bg-white/10 text-white backdrop-blur transition hover:scale-105 hover:bg-white/20"
                >
                    {tema === 'dark' ? <Sun className="size-5" /> : <Moon className="size-5" />}
                </button>

                <div className="relative w-full max-w-md">
                    <a href="/" className="mb-6 flex justify-center">
                        <img src="/atlantico-logo-wide.png" alt="Manufacturas R.J. Atlántico" width={240} className="h-auto w-60" />
                    </a>

                    <div className="bg-card text-card-foreground overflow-hidden rounded-2xl shadow-[0_24px_64px_rgba(0,0,0,0.35)] dark:shadow-[0_24px_64px_rgba(0,0,0,0.6)]">
                        <div className="bg-[linear-gradient(135deg,#1e3c72_0%,#2a5298_100%)] px-7 py-5 text-center">
                            <h1 className="flex items-center justify-center gap-2 text-base font-semibold text-white">
                                <Icono className="size-5 text-white/80" aria-hidden /> {titulo}
                            </h1>
                        </div>
                        <div className="grid gap-5 p-6 sm:p-8">
                            {flash.aviso && (
                                <p role="alert" className="border-warning/30 bg-warning/10 flex items-start gap-2 rounded-lg border p-3 text-sm">
                                    <Clock className="text-warning mt-0.5 size-4 shrink-0" aria-hidden /> {flash.aviso}
                                </p>
                            )}
                            {flash.status && (
                                <p role="status" className="border-success/30 bg-success/10 flex items-start gap-2 rounded-lg border p-3 text-sm">
                                    <CircleCheck className="text-success mt-0.5 size-4 shrink-0" aria-hidden /> {flash.status}
                                </p>
                            )}
                            {children}
                        </div>
                    </div>

                    <p className="mt-5 text-center text-xs text-white/55">© {new Date().getFullYear()} Grupo Textil 636 · Informática</p>
                </div>
            </div>
        </>
    );
}
