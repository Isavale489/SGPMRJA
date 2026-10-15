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

/**
 * Colores para AG Charts, leídos de los tokens de plataforma.css: el mismo estado se ve
 * igual en un badge y en un gráfico, y cada tema tiene los suyos. Solo tokens que NO
 * cambian con la sección (el gráfico se arma antes de que el layout la aplique).
 */
export interface Paleta {
    pendiente: string;
    enProceso: string;
    finalizado: string;
    cancelado: string;
    especial: string;
    neutro: string;
    /** Serie que no es un estado (p. ej. pedidos por mes). */
    marca: string;
    /** Barras de cantidades (consumo, uso). */
    info: string;
    /** Etiqueta dentro de una barra de color (blanco en claro, oscuro en oscuro). */
    sobreColor: string;
}

function leerPaleta(): Paleta {
    const css = getComputedStyle(document.documentElement);
    const token = (nombre: string) => css.getPropertyValue(`--${nombre}`).trim();
    return {
        pendiente: token('warning'),
        enProceso: token('info'),
        finalizado: token('success'),
        cancelado: token('destructive'),
        especial: token('especial'),
        neutro: token('muted-foreground'),
        marca: token('marca'),
        info: token('info'),
        sobreColor: token('primary-foreground'),
    };
}

/** Paleta del tema vigente; se vuelve a leer al cambiarlo (la clase .dark ya está puesta). */
export function usePaleta(): Paleta {
    const { tema } = useTema();
    // eslint-disable-next-line react-hooks/exhaustive-deps -- el tema cambia los valores de los tokens
    return useMemo(leerPaleta, [tema]);
}

/** Color de un estado de pedido u orden (mismos grupos que EstadoBadge). */
export function colorEstado(p: Paleta, estado: string): string {
    switch (estado) {
        case 'Pendiente':
            return p.pendiente;
        case 'En Proceso':
        case 'Procesando':
            return p.enProceso;
        case 'Finalizado':
        case 'Completado':
        case 'Aprobada':
        case 'recibida':
            return p.finalizado;
        case 'Cancelado':
        case 'Cancelada':
        case 'anulada':
            return p.cancelado;
        case 'Convertida':
            return p.especial;
        default:
            return p.neutro;
    }
}

/** Semáforo de eficiencia (decisión del equipo): verde ≥ 90, celeste 70–89, rojo < 70. */
export function colorEficiencia(p: Paleta, v: number | null): string {
    if (v === null) return p.neutro;
    return v >= 90 ? p.finalizado : v >= 70 ? p.enProceso : p.cancelado;
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
