import { ArrowLeft, Home } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import SimpleLayout from '@/layouts/simple-layout';

const TEXTOS: Record<number, { titulo: string; detalle: string }> = {
    403: { titulo: 'Sin permiso', detalle: 'Tu rol no tiene acceso a esta página. Si crees que es un error, pídele al administrador que revise tus permisos.' },
    404: { titulo: 'Página no encontrada', detalle: 'La página que buscas no existe o se movió. Revisa la dirección o vuelve al inicio.' },
    419: { titulo: 'La sesión expiró', detalle: 'Pasó mucho tiempo sin actividad. Recarga la página e inténtalo de nuevo.' },
    429: { titulo: 'Demasiados intentos', detalle: 'Espera un momento antes de volver a intentarlo.' },
    500: { titulo: 'Algo salió mal', detalle: 'Ocurrió un error en el servidor. Ya quedó registrado; intenta de nuevo en unos minutos.' },
    503: { titulo: 'En mantenimiento', detalle: 'El sistema está en mantenimiento. Vuelve en unos minutos.' },
};

/** Página de error (la renderiza App\Exceptions\Handler; no depende de props compartidas ni de la sesión). */
export default function PaginaError({ status }: { status: number }) {
    const t = TEXTOS[status] ?? TEXTOS[500]!;
    return (
        <SimpleLayout titulo={`${status} · ${t.titulo}`}>
            <Card>
                <CardContent className="grid justify-items-center gap-3 py-6 text-center">
                    <p className="text-primary text-6xl font-semibold tabular">{status}</p>
                    <h1 className="text-xl font-semibold">{t.titulo}</h1>
                    <p className="text-muted-foreground text-sm">{t.detalle}</p>
                    <div className="mt-2 flex flex-wrap justify-center gap-2">
                        <Button variant="outline" onClick={() => window.history.back()}>
                            <ArrowLeft /> Volver
                        </Button>
                        <Button asChild>
                            <a href="/dashboard">
                                <Home /> Ir al inicio
                            </a>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </SimpleLayout>
    );
}
