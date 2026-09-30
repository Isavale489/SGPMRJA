import { cn } from '@/lib/utils';

type Tono = 'warning' | 'info' | 'success' | 'destructive' | 'especial' | 'neutro';

/**
 * Estados de negocio → tono. Un solo lugar para que todas las tablas coincidan.
 * Solo tonos de ESTADO, nunca primary: primary cambia con la sección y un
 * «En Proceso» no puede verse verde en Operativa. Los gráficos usan los mismos
 * tokens (colorEstado en grafico.tsx).
 */
const TONO_DE: Record<string, Tono> = {
    Pendiente: 'warning',
    Aprobada: 'success',
    'En Proceso': 'info',
    Procesando: 'info',
    Finalizado: 'success',
    Completado: 'success',
    Convertida: 'especial',
    recibida: 'success',
    borrador: 'neutro',
    Cancelado: 'destructive',
    Cancelada: 'destructive',
    anulada: 'destructive',
    Vencida: 'neutro',
};

const CLASES: Record<Tono, string> = {
    warning: 'bg-warning/12 text-warning ring-warning/25',
    info: 'bg-info/12 text-info ring-info/25',
    success: 'bg-success/12 text-success ring-success/25',
    destructive: 'bg-destructive/10 text-destructive ring-destructive/25',
    especial: 'bg-especial/12 text-especial ring-especial/25',
    neutro: 'bg-muted text-muted-foreground ring-border',
};

/**
 * `data-tono`: sobre la franja en degradado de un diálogo, plataforma.css le da
 * fondo blanco y el color del estado para fondo claro (el tinte al 12 % no se lee ahí).
 */
export function EstadoBadge({ estado, className }: { estado: string; className?: string }) {
    const tono = TONO_DE[estado] ?? 'neutro';
    return (
        <span
            data-slot="estado-badge"
            data-tono={tono}
            className={cn('inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset', CLASES[tono], className)}
        >
            {estado}
        </span>
    );
}
