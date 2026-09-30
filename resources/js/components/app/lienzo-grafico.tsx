import { AreaSeriesModule, BarSeriesModule, CategoryAxisModule, DonutSeriesModule, LegendModule, ModuleRegistry, NumberAxisModule, type AgChartInstance, type AgChartOptions } from 'ag-charts-community';
import { AgCharts } from 'ag-charts-react';
import type { Ref } from 'react';

// Solo lo que usan los gráficos del sistema (barras, área, donut, ejes, leyenda).
ModuleRegistry.registerModules([AreaSeriesModule, BarSeriesModule, DonutSeriesModule, CategoryAxisModule, NumberAxisModule, LegendModule]);

interface Props {
    opciones: AgChartOptions;
    alto: number;
    ref: Ref<AgChartInstance>;
}

/**
 * Único módulo que importa AG Charts (~1,3 MB). `TarjetaGrafico` lo carga con
 * `lazy()`: la página se pinta de una vez y el gráfico llega después.
 */
export default function LienzoGrafico({ opciones, alto, ref }: Props) {
    return <AgCharts ref={ref} options={opciones} style={{ height: alto }} />;
}
