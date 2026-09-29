import { Maximize, Minimize } from 'lucide-react';
import { useEffect, useState } from 'react';

import { Button } from '@/components/ui/button';

/** Alterna la pantalla completa. No se muestra donde el navegador no la permite (p. ej. iPhone). */
export function PantallaCompleta() {
    const [activa, setActiva] = useState(false);
    const [disponible, setDisponible] = useState(false);

    useEffect(() => {
        setDisponible(Boolean(document.fullscreenEnabled));
        const alCambiar = () => setActiva(Boolean(document.fullscreenElement));
        document.addEventListener('fullscreenchange', alCambiar);
        return () => document.removeEventListener('fullscreenchange', alCambiar);
    }, []);

    if (!disponible) return null;

    const alternar = () => {
        if (document.fullscreenElement) void document.exitFullscreen();
        else void document.documentElement.requestFullscreen().catch(() => undefined);
    };

    return (
        <Button variant="ghost" size="icon" onClick={alternar} aria-label={activa ? 'Salir de pantalla completa' : 'Pantalla completa'}>
            {activa ? <Minimize /> : <Maximize />}
        </Button>
    );
}
