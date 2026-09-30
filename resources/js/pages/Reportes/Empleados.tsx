import type { AgChartOptions } from 'ag-charts-community';
import { Search, X } from 'lucide-react';
import { useMemo, useState } from 'react';

import { PanelOrdenable } from '@/components/app/panel-ordenable';
import { TarjetaGrafico, colorEficiencia, partirEtiqueta, usePaleta } from '@/components/app/grafico';
import { CabeceraOrdenable, useOrdenColumnas } from '@/components/app/orden-columnas';
import { CabeceraSeccion, CuerpoRayado } from '@/components/app/tabla-seccion';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableCell, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { formatoNumero } from '@/lib/formato';

import { EficienciaChip } from './eficiencia-chip';
import type { RendimientoEmpleado } from './tipos';

const promedio = (e: RendimientoEmpleado) => (e.total_ordenes ? e.total_producido / e.total_ordenes : 0);
const COLUMNAS = {
    nombre: (e: RendimientoEmpleado) => e.nombre,
    ordenes: (e: RendimientoEmpleado) => e.total_ordenes,
    asignado: (e: RendimientoEmpleado) => e.total_asignado,
    producido: (e: RendimientoEmpleado) => e.total_producido,
    defectuoso: (e: RendimientoEmpleado) => e.total_defectuoso,
    eficiencia: (e: RendimientoEmpleado) => e.eficiencia,
    promedio,
};

/**
 * Rendimiento por persona desde el reparto real de cada orden (lo asignado,
 * producido y defectuoso de cada empleado). Clic en una barra: filtra la tabla.
 */
export default function ReporteEmpleados({ empleados }: { empleados: RendimientoEmpleado[] }) {
    const [buscar, setBuscar] = useState('');
    const [elegido, setElegido] = useState<number>();
    const alto = Math.max(340, empleados.length * 52 + 64); // la leyenda roba ~64 px

    // La categoría del eje es el id (dos personas con el mismo nombre son dos barras); se muestra el nombre.
    const paleta = usePaleta();
    const eje = useMemo(() => {
        const nombres = new Map(empleados.map((e) => [String(e.empleado_id), e.nombre]));
        return { type: 'category' as const, position: 'left' as const, label: { formatter: ({ value }: { value: string | number }) => partirEtiqueta(nombres.get(String(value)) ?? String(value)) } };
    }, [empleados]);
    const tooltipUnidades = {
        renderer: ({ datum, yKey, yName }: { datum: Record<string, number | string>; yKey: string; yName?: string }) => ({ heading: '', title: String(datum.nombre), data: [{ label: yName ?? yKey, value: formatoNumero(Number(datum[yKey])) }] }),
    };

    const alHacerClic = useMemo(() => ({
        seriesNodeClick: (e: { datum?: { id?: number } }) => {
            const id = e.datum?.id;
            if (id) setElegido((actual) => (actual === id ? undefined : id));
        },
    }), []);

    const produccion = useMemo<Omit<AgChartOptions, 'theme'>>(() => ({
        data: empleados.map((e) => ({ id: e.empleado_id, nombre: e.nombre, producido: e.total_producido, defectuoso: e.total_defectuoso })),
        series: [
            { type: 'bar', direction: 'horizontal', xKey: 'id', yKey: 'producido', yName: 'Conformes', stacked: true, fill: paleta.finalizado, tooltip: tooltipUnidades },
            { type: 'bar', direction: 'horizontal', xKey: 'id', yKey: 'defectuoso', yName: 'Defectuosas', stacked: true, fill: paleta.cancelado, tooltip: tooltipUnidades },
        ],
        axes: { x: eje, y: { type: 'number', position: 'bottom', title: { text: 'Unidades' } } },
        legend: { position: 'bottom' },
        listeners: alHacerClic,
    }) as Omit<AgChartOptions, 'theme'>, [empleados, alHacerClic, eje, paleta]);

    const eficiencia = useMemo<Omit<AgChartOptions, 'theme'>>(() => {
        const datos = empleados.filter((e) => e.eficiencia !== null).map((e) => ({ id: e.empleado_id, nombre: e.nombre, eficiencia: e.eficiencia }));
        return {
            data: datos,
            series: [{
                type: 'bar', direction: 'horizontal', xKey: 'id', yKey: 'eficiencia', yName: 'Eficiencia', cornerRadius: 4,
                itemStyler: ({ datum }: { datum: { eficiencia: number } }) => ({ fill: colorEficiencia(paleta, datum.eficiencia) }),
                label: { placement: 'inside-end', color: paleta.sobreColor, fontWeight: 'bold', formatter: ({ value }: { value: number }) => `${formatoNumero(value)} %` },
                tooltip: { renderer: ({ datum }: { datum: { nombre: string; eficiencia: number } }) => ({ heading: '', title: datum.nombre, data: [{ label: 'Eficiencia', value: `${formatoNumero(datum.eficiencia)} %` }] }) },
            }],
            axes: { x: eje, y: { type: 'number', position: 'bottom', min: 0, max: 100, title: { text: '%' } } },
            listeners: alHacerClic,
        } as Omit<AgChartOptions, 'theme'>;
    }, [empleados, alHacerClic, eje, paleta]);

    const filtradas = useMemo(
        () => empleados.filter((e) => (!elegido || e.empleado_id === elegido) && (!buscar.trim() || e.nombre.toLowerCase().includes(buscar.trim().toLowerCase()))),
        [empleados, elegido, buscar],
    );
    const { ordenadas: filas, orden, alternar } = useOrdenColumnas(filtradas, COLUMNAS);
    const nombreElegido = empleados.find((e) => e.empleado_id === elegido)?.nombre;

    return (
        <AppLayout titulo="Rendimiento de empleados">
            <div className="grid grid-cols-1 gap-4">
                <PanelOrdenable
                    clave="sgpmrja-rep-empleados-layout"
                    widgets={[
                        { id: 'produccion', titulo: 'Producción por empleado', render: (agarre) => <TarjetaGrafico titulo="Producción por empleado" archivo="produccion-por-empleado" opciones={produccion} vacio={!empleados.length} alto={alto} agarre={agarre} /> },
                        { id: 'eficiencia', titulo: 'Eficiencia por empleado', render: (agarre) => <TarjetaGrafico titulo="Eficiencia por empleado" archivo="eficiencia-por-empleado" opciones={eficiencia} vacio={!empleados.some((e) => e.eficiencia !== null)} alto={alto - 64} agarre={agarre} /> },
                    ]}
                />

                <div className="flex flex-wrap items-center gap-2">
                    <div className="relative min-w-56 flex-1">
                        <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2" />
                        <Input type="search" value={buscar} onChange={(e) => setBuscar(e.target.value)} placeholder="Buscar empleado…" aria-label="Buscar empleado" className="pl-8" />
                    </div>
                    {elegido && <Button variant="secondary" onClick={() => setElegido(undefined)} aria-label={`Quitar el filtro de ${nombreElegido}`}><X /> {nombreElegido}</Button>}
                </div>

                <div className="bg-card overflow-x-auto rounded-lg border">
                    <Table>
                        <CabeceraSeccion>
                            <TableRow className="hover:bg-transparent">
                                <CabeceraOrdenable clave="nombre" orden={orden} onOrdenar={alternar}>Empleado</CabeceraOrdenable>
                                <CabeceraOrdenable clave="ordenes" orden={orden} onOrdenar={alternar} className="text-right">Órdenes</CabeceraOrdenable>
                                <CabeceraOrdenable clave="asignado" orden={orden} onOrdenar={alternar} className="text-right">Asignado</CabeceraOrdenable>
                                <CabeceraOrdenable clave="producido" orden={orden} onOrdenar={alternar} className="text-right">Producido</CabeceraOrdenable>
                                <CabeceraOrdenable clave="defectuoso" orden={orden} onOrdenar={alternar} className="text-right">Defectuoso</CabeceraOrdenable>
                                <CabeceraOrdenable clave="eficiencia" orden={orden} onOrdenar={alternar}>Eficiencia</CabeceraOrdenable>
                                <CabeceraOrdenable clave="promedio" orden={orden} onOrdenar={alternar} className="text-right">Promedio por orden</CabeceraOrdenable>
                            </TableRow>
                        </CabeceraSeccion>
                        <CuerpoRayado>
                            {filas.map((e) => (
                                <TableRow key={e.empleado_id}>
                                    <TableCell className="font-medium">{e.nombre}</TableCell>
                                    <TableCell className="text-right tabular">{e.total_ordenes}</TableCell>
                                    <TableCell className="text-right tabular">{formatoNumero(e.total_asignado)}</TableCell>
                                    <TableCell className="text-right tabular">{formatoNumero(e.total_producido)}</TableCell>
                                    <TableCell className="text-right tabular">{formatoNumero(e.total_defectuoso)}</TableCell>
                                    <TableCell><EficienciaChip valor={e.eficiencia} /></TableCell>
                                    <TableCell className="text-right tabular">{formatoNumero(promedio(e))}</TableCell>
                                </TableRow>
                            ))}
                            {!filas.length && <TableRow className="hover:bg-transparent"><TableCell colSpan={7} className="text-muted-foreground h-24 text-center">{empleados.length ? 'Ningún empleado coincide.' : 'Aún no hay producción registrada.'}</TableCell></TableRow>}
                        </CuerpoRayado>
                    </Table>
                </div>
            </div>
        </AppLayout>
    );
}
