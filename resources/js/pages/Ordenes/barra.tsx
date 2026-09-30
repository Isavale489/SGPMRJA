import { cn } from '@/lib/utils';

/** Barra de progreso con su porcentaje y un texto («6 de 10»). */
export function Barra({ valor, texto, className }: { valor: number; texto?: string; className?: string }) {
    return (
        <div className={cn('grid gap-1', className)}>
            <div className="flex justify-between text-xs">
                <span className="text-muted-foreground tabular">{texto}</span>
                <span className="tabular font-medium">{valor} %</span>
            </div>
            <div className="bg-muted h-2 overflow-hidden rounded-full" role="progressbar" aria-valuenow={valor} aria-valuemin={0} aria-valuemax={100} aria-label={texto}>
                <div className={cn('h-full rounded-full transition-all duration-medio', valor >= 100 ? 'bg-success' : 'bg-info')} style={{ width: `${valor}%` }} />
            </div>
        </div>
    );
}
