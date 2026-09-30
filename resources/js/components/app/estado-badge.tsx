import { cn } from '@/lib/utils';

/**
 * Estados de negocio → tono visual. Un solo lugar para que todas las tablas coincidan.
 * Solo tokens de ESTADO (warning, info, success, destructive, especial), nunca primary:
 * primary cambia con la sección y un «En Proceso» no puede verse verde en Operativa.
 * Los gráficos usan los mismos tokens (usePaleta en grafico.tsx).
 */
const TONOS: Record<string, string> = {
    Pendiente: 'bg-warning/12 text-warning ring-warning/25',
    Aprobada: 'bg-success/12 text-success ring-success/25',
    'En Proceso': 'bg-info/12 text-info ring-info/25',
    Procesando: 'bg-info/12 text-info ring-info/25',
    Finalizado: 'bg-success/12 text-success ring-success/25',
    Completado: 'bg-success/12 text-success ring-success/25',
    Convertida: 'bg-especial/12 text-especial ring-especial/25',
    recibida: 'bg-success/12 text-success ring-success/25',
    borrador: 'bg-muted text-muted-foreground ring-border',
    Cancelado: 'bg-destructive/10 text-destructive ring-destructive/25',
    Cancelada: 'bg-destructive/10 text-destructive ring-destructive/25',
    anulada: 'bg-destructive/10 text-destructive ring-destructive/25',
    Vencida: 'bg-muted text-muted-foreground ring-border',
};

export function EstadoBadge({ estado, className }: { estado: string; className?: string }) {
    return (
        <span
            className={cn(
                'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset',
                TONOS[estado] ?? 'bg-muted text-muted-foreground ring-border',
                className,
            )}
        >
            {estado}
        </span>
    );
}
