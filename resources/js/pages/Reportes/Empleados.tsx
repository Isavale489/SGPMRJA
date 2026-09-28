import type { AgChartOptions } from 'ag-charts-community';
import { Search, X } from 'lucide-react';
import { useMemo, useState } from 'react';

import { PanelOrdenable } from '@/components/app/panel-ordenable';
import { COLOR_CONFORME, COLOR_DEFECTO, TarjetaGrafico, colorEficiencia, partirEtiqueta } from '@/components/app/grafico';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { formatoNumero } from '@/lib/formato';

import { EficienciaChip } from './eficiencia-chip';
import type { RendimientoEmpleado } from './tipos';

/**
 * Rendimiento por persona desde el reparto real de cada orden (lo asignado,
 * producido y defectuoso de cada empleado). Clic en una barra: filtra la tabla.
 */
export default function ReporteEmpleados({ empleados }: { empleados: RendimientoEmpleado[] }) {
    const [buscar, setBuscar] = useState('');
    const [elegido, setElegido] = useState<string>();
    const alto = Math.max(340, empleados.length * 52 + 64); // la leyenda roba ~64 px

    const alHacerClic = useMemo(() => ({
        seriesNodeClick: (e: { datum?: { nombre?: string } }) => {
            const n = e.datum?.nombre;
            if (n) setElegido((actual) => (actual === n ? undefined : n));
        },
    }), []);

    const produccion = useMemo<Omit<AgChartOptions, 'theme'>>(() => ({
        data: empleados.map((e) => ({ nombre: e.nombre, producido: e.total_producido, defectuoso: e.total_defectuoso })),
        series: [
            { type: 'bar', direction: 'horizontal', xKey: 'nombre', yKey: 'producido', yName: 'Conformes', stacked: true, fill: COLOR_CONFORME },
            { type: 'bar', direction: 'horizontal', xKey: 'nombre', yKey: 'defectuoso', yName: 'Defectuosas', stacked: true, fill: COLOR_DEFECTO },
        ],
        axes: { x: { type: 'category', position: 'left', label: { formatter: ({ value }: { value: string }) => partirEtiqueta(String(value)) } }, y: { type: 'number', position: 'bottom', title: { text: 'Unidades' } } },
        legend: { position: 'bottom' },
        listeners: alHacerClic,
    }) as Omit<AgChartOptions, 'theme'>, [empleados, alHacerClic]);

    const eficiencia = useMemo<Omit<AgChartOptions, 'theme'>>(() => {
        const datos = empleados.filter((e) => e.eficiencia !== null).map((e) => ({ nombre: e.nombre, eficiencia: e.eficiencia }));
        return {
            data: datos,
            series: [{
                type: 'bar', direction: 'horizontal', xKey: 'nombre', yKey: 'eficiencia', yName: 'Eficiencia', cornerRadius: 4,
                itemStyler: ({ datum }: { datum: { eficiencia: number } }) => ({ fill: colorEficiencia(datum.eficiencia) }),
                label: { placement: 'inside-end', color: '#ffffff', fontWeight: 'bold', formatter: ({ value }: { value: number }) => `${formatoNumero(value)} %` },
            }],
            axes: { x: { type: 'category', position: 'left', label: { formatter: ({ value }: { value: string }) => partirEtiqueta(String(value)) } }, y: { type: 'number', position: 'bottom', min: 0, max: 100, title: { text: '%' } } },
            listeners: alHacerClic,
        } as Omit<AgChartOptions, 'theme'>;
    }, [empleados, alHacerClic]);

    const filas = empleados.filter((e) => (!elegido || e.nombre === elegido) && (!buscar.trim() || e.nombre.toLowerCase().includes(buscar.trim().toLowerCase())));

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
                    {elegido && <Button variant="secondary" onClick={() => setElegido(undefined)}><X /> {elegido}</Button>}
                </div>

                <div className="bg-card overflow-x-auto rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow className="hover:bg-transparent">
                                <TableHead>Empleado</TableHead>
                                <TableHead className="text-right">Órdenes</TableHead>
                                <TableHead className="text-right">Asignado</TableHead>
                                <TableHead className="text-right">Producido</TableHead>
                                <TableHead className="text-right">Defectuoso</TableHead>
                                <TableHead>Eficiencia</TableHead>
                                <TableHead className="text-right">Promedio por orden</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filas.map((e) => (
                                <TableRow key={e.empleado_id}>
                                    <TableCell className="font-medium">{e.nombre}</TableCell>
                                    <TableCell className="text-right tabular">{e.total_ordenes}</TableCell>
                                    <TableCell className="text-right tabular">{formatoNumero(e.total_asignado)}</TableCell>
                                    <TableCell className="text-right tabular">{formatoNumero(e.total_producido)}</TableCell>
                                    <TableCell className="text-right tabular">{formatoNumero(e.total_defectuoso)}</TableCell>
                                    <TableCell><EficienciaChip valor={e.eficiencia} /></TableCell>
                                    <TableCell className="text-right tabular">{formatoNumero(e.total_ordenes ? e.total_producido / e.total_ordenes : 0)}</TableCell>
                                </TableRow>
                            ))}
                            {!filas.length && <TableRow className="hover:bg-transparent"><TableCell colSpan={7} className="text-muted-foreground h-24 text-center">{empleados.length ? 'Ningún empleado coincide.' : 'Aún no hay producción registrada.'}</TableCell></TableRow>}
                        </TableBody>
                    </Table>
                </div>
            </div>
        </AppLayout>
    );
}
