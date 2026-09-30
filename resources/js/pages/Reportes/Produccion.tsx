import type { AgChartOptions } from 'ag-charts-community';
import { useMemo } from 'react';

import { PanelOrdenable } from '@/components/app/panel-ordenable';
import { TarjetaGrafico, colorEstado, usePaleta } from '@/components/app/grafico';
import { CabeceraSeccion, CuerpoRayado } from '@/components/app/tabla-seccion';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableCell, TableHead, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { formatoNumero } from '@/lib/formato';

import { EficienciaChip } from './eficiencia-chip';
import type { MesProduccion } from './tipos';

interface Props {
    estados: { estado: string; total: number }[];
    mensual: MesProduccion[];
}

/** Órdenes por estado y producción de los últimos 12 meses (por mes de inicio). */
export default function ReporteProduccion({ estados, mensual }: Props) {
    const total = estados.reduce((s, e) => s + e.total, 0);
    const paleta = usePaleta();

    const donut = useMemo<Omit<AgChartOptions, 'theme'>>(() => {
        const datos = [...estados].sort((a, b) => b.total - a.total);
        return {
            data: datos,
            series: [{
                type: 'donut', angleKey: 'total', calloutLabelKey: 'estado', sectorLabelKey: 'total', innerRadiusRatio: 0.62, cornerRadius: 4,
                fills: datos.map((d) => colorEstado(paleta, d.estado)),
                innerLabels: [{ text: String(total), fontSize: 26, fontWeight: 'bold' }, { text: 'órdenes', fontSize: 12, spacing: 4 }],
            }],
            legend: { position: 'bottom' },
        };
    }, [estados, total, paleta]);

    const barras = useMemo<Omit<AgChartOptions, 'theme'>>(() => ({
        data: [...mensual].reverse().map((m) => ({ mes: `${m.mes_nombre.slice(0, 3)}-${m.anio}`, producido: m.producido, defectuoso: m.defectuoso })),
        series: [
            { type: 'bar', xKey: 'mes', yKey: 'producido', yName: 'Producido', fill: paleta.finalizado, cornerRadius: 4 },
            { type: 'bar', xKey: 'mes', yKey: 'defectuoso', yName: 'Defectuoso', fill: paleta.cancelado, cornerRadius: 4 },
        ],
        axes: { x: { type: 'category', position: 'bottom' }, y: { type: 'number', position: 'left', title: { text: 'Unidades' } } },
        legend: { position: 'bottom' },
    }) as Omit<AgChartOptions, 'theme'>, [mensual, paleta]);

    return (
        <AppLayout titulo="Reporte de producción">
            <div className="grid grid-cols-1 gap-4">
                <PanelOrdenable
                    clave="sgpmrja-rep-produccion-layout"
                    widgets={[
                        { id: 'estados', titulo: 'Órdenes por estado', render: (agarre) => <TarjetaGrafico titulo="Órdenes por estado" archivo="ordenes-por-estado" opciones={donut} vacio={!estados.length} agarre={agarre} /> },
                        { id: 'mensual', titulo: 'Producción mensual', render: (agarre) => <TarjetaGrafico titulo="Producción mensual" archivo="produccion-mensual" opciones={barras} vacio={!mensual.length} agarre={agarre} /> },
                    ]}
                />
                <Card>
                    <CardHeader><CardTitle className="text-base">Estadísticas por mes</CardTitle></CardHeader>
                    <CardContent className="overflow-x-auto">
                        <Table>
                            <CabeceraSeccion>
                                <TableRow className="hover:bg-transparent">
                                    <TableHead>Mes</TableHead>
                                    <TableHead className="text-right">Producido</TableHead>
                                    <TableHead className="text-right">Defectuoso</TableHead>
                                    <TableHead>Eficiencia</TableHead>
                                </TableRow>
                            </CabeceraSeccion>
                            <CuerpoRayado>
                                {mensual.map((m) => (
                                    <TableRow key={`${m.anio}-${m.mes}`}>
                                        <TableCell>{m.mes_nombre} {m.anio}</TableCell>
                                        <TableCell className="text-right tabular">{formatoNumero(m.producido)}</TableCell>
                                        <TableCell className="text-right tabular">{formatoNumero(m.defectuoso)}</TableCell>
                                        <TableCell><EficienciaChip valor={m.eficiencia} /></TableCell>
                                    </TableRow>
                                ))}
                                {!mensual.length && <TableRow><TableCell colSpan={4} className="text-muted-foreground h-20 text-center">Aún no hay producción registrada.</TableCell></TableRow>}
                            </CuerpoRayado>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
