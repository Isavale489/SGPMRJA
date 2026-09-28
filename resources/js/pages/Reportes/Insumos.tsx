import type { AgChartOptions } from 'ag-charts-community';
import { Search } from 'lucide-react';
import { useMemo, useState } from 'react';

import { PanelOrdenable } from '@/components/app/panel-ordenable';
import { TarjetaGrafico, partirEtiqueta } from '@/components/app/grafico';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { formatoNumero } from '@/lib/formato';

import type { ConsumoInsumo } from './tipos';

const TODOS = 'todos';

/** Lo que las órdenes descontaron del inventario (sin eliminadas ni canceladas en Pendiente). */
export default function ReporteInsumos({ insumos }: { insumos: ConsumoInsumo[] }) {
    const [buscar, setBuscar] = useState('');
    const [tipo, setTipo] = useState(TODOS);
    const tipos = useMemo(() => [...new Set(insumos.map((i) => i.tipo))].sort(), [insumos]);

    const porTipo = useMemo<Omit<AgChartOptions, 'theme'>>(() => {
        const mapa = new Map<string, number>();
        for (const i of insumos) mapa.set(i.tipo, (mapa.get(i.tipo) ?? 0) + i.total);
        return {
            data: [...mapa].map(([t, total]) => ({ tipo: t, total: Math.round(total * 100) / 100 })).sort((a, b) => b.total - a.total),
            series: [{ type: 'donut', angleKey: 'total', calloutLabelKey: 'tipo', innerRadiusRatio: 0.62, cornerRadius: 4 }],
            legend: { position: 'bottom' },
        };
    }, [insumos]);

    const top = useMemo(() => insumos.slice(0, 10), [insumos]);
    const barras = useMemo<Omit<AgChartOptions, 'theme'>>(() => ({
        data: top.map((i) => ({ nombre: i.nombre, total: i.total, unidad: i.unidad })),
        series: [{ type: 'bar', direction: 'horizontal', xKey: 'nombre', yKey: 'total', yName: 'Utilizado', fill: '#0ea5e9', cornerRadius: 4, label: { placement: 'outside-end' } }],
        axes: { x: { type: 'category', position: 'left', label: { formatter: ({ value }: { value: string }) => partirEtiqueta(String(value)) } }, y: { type: 'number', position: 'bottom' } },
    }) as Omit<AgChartOptions, 'theme'>, [top]);

    const filas = insumos.filter((i) => (tipo === TODOS || i.tipo === tipo) && (!buscar.trim() || i.nombre.toLowerCase().includes(buscar.trim().toLowerCase())));

    return (
        <AppLayout titulo="Consumo de insumos">
            <div className="grid grid-cols-1 gap-4">
                <PanelOrdenable
                    clave="sgpmrja-rep-insumos-layout"
                    widgets={[
                        { id: 'tipo', titulo: 'Consumo por tipo de insumo', render: (agarre) => <TarjetaGrafico titulo="Consumo por tipo de insumo" archivo="consumo-por-tipo" opciones={porTipo} vacio={!insumos.length} agarre={agarre} /> },
                        { id: 'top', titulo: 'Los 10 más utilizados', render: (agarre) => <TarjetaGrafico titulo="Los 10 más utilizados" archivo="top-insumos" opciones={barras} vacio={!insumos.length} alto={Math.max(340, top.length * 52)} agarre={agarre} /> },
                    ]}
                />

                <div className="flex flex-wrap items-center gap-2">
                    <div className="relative min-w-56 flex-1">
                        <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2" />
                        <Input type="search" value={buscar} onChange={(e) => setBuscar(e.target.value)} placeholder="Buscar insumo…" aria-label="Buscar insumo" className="pl-8" />
                    </div>
                    <Select value={tipo} onValueChange={setTipo}>
                        <SelectTrigger className="w-44" aria-label="Filtrar por tipo"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value={TODOS}>Todos los tipos</SelectItem>
                            {tipos.map((t) => <SelectItem key={t} value={t}>{t}</SelectItem>)}
                        </SelectContent>
                    </Select>
                </div>

                <div className="bg-card overflow-x-auto rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow className="hover:bg-transparent">
                                <TableHead>Insumo</TableHead>
                                <TableHead>Tipo</TableHead>
                                <TableHead className="text-right">Total utilizado</TableHead>
                                <TableHead className="text-right">Órdenes</TableHead>
                                <TableHead className="text-right">Promedio por orden</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filas.map((i) => (
                                <TableRow key={i.id}>
                                    <TableCell className="font-medium">{i.nombre}</TableCell>
                                    <TableCell className="text-muted-foreground">{i.tipo}</TableCell>
                                    <TableCell className="text-right tabular">{formatoNumero(i.total)} <span className="text-muted-foreground text-xs">{i.unidad}</span></TableCell>
                                    <TableCell className="text-right tabular">{i.ordenes}</TableCell>
                                    <TableCell className="text-right tabular">{formatoNumero(i.ordenes ? i.total / i.ordenes : 0)}</TableCell>
                                </TableRow>
                            ))}
                            {!filas.length && <TableRow className="hover:bg-transparent"><TableCell colSpan={5} className="text-muted-foreground h-24 text-center">{insumos.length ? 'Ningún insumo coincide.' : 'Aún no hay consumo registrado.'}</TableCell></TableRow>}
                        </TableBody>
                    </Table>
                </div>
            </div>
        </AppLayout>
    );
}
