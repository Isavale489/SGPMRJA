import { cn } from '@/lib/utils';

/** Estados de negocio → tono visual. Un solo lugar para que todas las tablas coincidan. */
const TONOS: Record<string, string> = {
    Pendiente: 'bg-warning/12 text-warning ring-warning/25',
    Aprobada: 'bg-success/12 text-success ring-success/25',
    'En Proceso': 'bg-primary/10 text-primary ring-primary/25',
    Procesando: 'bg-primary/10 text-primary ring-primary/25',
    Finalizado: 'bg-success/12 text-success ring-success/25',
    Completado: 'bg-success/12 text-success ring-success/25',
    Convertida: 'bg-secondary text-secondary-foreground ring-border',
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
