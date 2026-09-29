import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';

import { Toaster } from '@/components/ui/sonner';

/**
 * Pantalla suelta, sin menú: páginas de error (que también ven usuarios sin
 * sesión, y a las que puede no llegar ninguna prop compartida) y pasos
 * obligatorios como el cambio de contraseña temporal.
 */
export default function SimpleLayout({ titulo, children }: { titulo: string; children: ReactNode }) {
    return (
        <>
            <Head title={titulo} />
            <main className="bg-muted/40 grid min-h-svh place-items-center p-4">
                <div className="grid w-full max-w-md gap-6">
                    <a href="/dashboard" className="justify-self-center">
                        <img src="/atlantico-logo-wide.png" alt="Manufacturas R.J. Atlántico" className="h-9 w-auto dark:brightness-0 dark:invert" />
                    </a>
                    {children}
                </div>
            </main>
            <Toaster richColors position="top-right" />
        </>
    );
}
