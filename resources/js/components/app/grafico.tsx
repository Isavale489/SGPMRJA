import type { AgChartInstance, AgChartOptions } from 'ag-charts-community';
import { Download } from 'lucide-react';
import { Component, lazy, Suspense, useCallback, useMemo, useRef, useState, type ReactNode } from 'react';

import { Button } from '@/components/ui/button';
import { Card, CardAction, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { useTema } from '@/hooks/use-tema';
import { cn } from '@/lib/utils';

// AG Charts va en su propio chunk: este archivo solo importa sus tipos, así las
// páginas con gráficos no esperan esa descarga para pintarse.
const LienzoGrafico = lazy(() => import('./lienzo-grafico'));

/**
 * Si el chunk del gráfico no llega (red caída, o una pestaña abierta desde antes
 * de un deploy que pide un hash que ya no existe), el `import()` rechazado
 * desmontaría todo el panel: este límite lo deja en un aviso dentro de la tarjeta.
 */
class LimiteGrafico extends Component<{ alto: number; children: ReactNode }, { fallo: boolean }> {
    state = { fallo: false };

    static getDerivedStateFromError() {
        return { fallo: true };
    }

    render() {
        if (!this.state.fallo) return this.props.children;
        return (
            <p role="alert" className="text-muted-foreground grid place-items-center text-center text-sm" style={{ height: Math.min(this.props.alto, 200) }}>
                No se pudo cargar el gráfico. Recarga la página.
            </p>
        );
    }
}

/** Colores de estado de las órdenes (los mismos en todo el sistema). */
export const COLOR_ESTADO: Record<string, string> = {
    Pendiente: '#f7b84b',
    'En Proceso': '#299cdb',
    Finalizado: '#0ab39c',
    Cancelado: '#f06548',
};
export const COLOR_CONFORME = '#0ab39c';
export const COLOR_DEFECTO = '#f06548';

/** Semáforo de eficiencia (decisión del equipo): verde ≥ 90, celeste 70–89, rojo < 70. */
export function colorEficiencia(v: number | null): string {
    if (v === null) return '#878a99';
    return v >= 90 ? '#0ab39c' : v >= 70 ? '#0ea5e9' : '#f06548';
}

/** Parte un nombre largo en líneas de hasta 18 caracteres (el eje de categorías respeta los \n). */
export function partirEtiqueta(texto: string, max = 18): string {
    const lineas: string[] = [];
    let actual = '';
    for (const palabra of texto.split(/\s+/)) {
        if ((actual + ' ' + palabra).trim().length > max && actual) {
            lineas.push(actual);
            actual = palabra;
        } else actual = `${actual} ${palabra}`.trim();
    }
    if (actual) lineas.push(actual);
    return lineas.join('\n');
}

interface Props {
    titulo: string;
    /** Nombre del PNG al descargar. */
    archivo: string;
    opciones: Omit<AgChartOptions, 'theme'>;
    /** Alto del área del gráfico en px. */
    alto?: number;
    vacio?: boolean;
    /** Contenido del encabezado a la izquierda del botón de descarga (p. ej. el agarre del panel). */
    agarre?: ReactNode;
    className?: string;
}

/**
 * Tarjeta con un gráfico AG Charts: tema claro/oscuro del sistema, fondo
 * transparente integrado a la tarjeta y descarga en PNG (con fondo sólido:
 * sin él, el PNG sale transparente).
 */
export function TarjetaGrafico({ titulo, archivo, opciones, alto = 340, vacio, agarre, className }: Props) {
    const { tema } = useTema();
    const chart = useRef<AgChartInstance | null>(null);
    // La descarga se habilita cuando el gráfico ya existe (llega después de la página).
    const [listo, setListo] = useState(false);
    const refGrafico = useCallback((c: AgChartInstance | null) => {
        chart.current = c;
        setListo(c !== null);
    }, []);
    const caja = useRef<HTMLDivElement>(null);
    const oscuro = tema === 'dark';

    const completas = useMemo(
        () => ({ ...opciones, theme: { baseTheme: oscuro ? 'ag-default-dark' : 'ag-default', overrides: { common: { background: { visible: false } } } } }) as AgChartOptions,
        [opciones, oscuro],
    );

    const descargar = async () => {
        const c = chart.current;
        if (!c) return;
        try {
            // El fondo real de la tarjeta (sigue al tema); si no se puede leer, uno fijo por tema.
            const tarjeta = caja.current?.closest<HTMLElement>('[data-slot="card"]');
            const fondo = (tarjeta && getComputedStyle(tarjeta).backgroundColor) || (oscuro ? '#1b1f2a' : '#ffffff');
            await c.updateDelta({ background: { visible: true, fill: fondo } } as Partial<AgChartOptions>);
            await c.download({ fileName: archivo });
        } finally {
            await c.updateDelta({ background: { visible: false } } as Partial<AgChartOptions>);
        }
    };

    return (
        <Card className={cn('gap-2', className)}>
            <CardHeader>
                <CardTitle className="flex items-center gap-2 text-base">{agarre}{titulo}</CardTitle>
                <CardAction>
                    <Button variant="ghost" size="icon" onClick={descargar} disabled={vacio || !listo} aria-label={`Descargar «${titulo}» como imagen`}>
                        <Download />
                    </Button>
                </CardAction>
            </CardHeader>
            <CardContent ref={caja}>
                {vacio ? (
                    <p className="text-muted-foreground grid place-items-center text-sm" style={{ height: Math.min(alto, 200) }}>Aún no hay datos para este gráfico.</p>
                ) : (
                    <LimiteGrafico alto={alto}>
                        <Suspense fallback={<Skeleton role="status" aria-label="Cargando gráfico…" style={{ height: alto }} />}>
                            <LienzoGrafico ref={refGrafico} opciones={completas} alto={alto} />
                        </Suspense>
                    </LimiteGrafico>
                )}
            </CardContent>
        </Card>
    );
}
