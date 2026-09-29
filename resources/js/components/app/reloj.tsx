import { CalendarClock } from 'lucide-react';
import { useEffect, useState } from 'react';

// Hora de Venezuela (la del sistema), no la del equipo del usuario.
const FECHA = new Intl.DateTimeFormat('es-VE', { timeZone: 'America/Caracas', day: '2-digit', month: '2-digit', year: 'numeric' });
const HORA = new Intl.DateTimeFormat('es-VE', { timeZone: 'America/Caracas', hour: 'numeric', minute: '2-digit', hour12: true });

/** Píldora con la fecha y la hora actuales (el reloj del layout anterior). */
export function Reloj() {
    const [ahora, setAhora] = useState(() => new Date());
    useEffect(() => {
        const id = window.setInterval(() => setAhora(new Date()), 1000);
        return () => window.clearInterval(id);
    }, []);

    return (
        <span className="border-border bg-card text-muted-foreground inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border px-2.5 py-1 text-xs">
            <CalendarClock className="size-3.5" aria-hidden />
            <time dateTime={ahora.toISOString()} className="tabular">
                <span className="text-foreground font-medium">{FECHA.format(ahora)}</span> · {HORA.format(ahora)}
            </time>
        </span>
    );
}
