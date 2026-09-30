import { Search } from 'lucide-react';
import { useMemo, useState } from 'react';

import { CabeceraOrdenable, useOrdenColumnas } from '@/components/app/orden-columnas';
import { CabeceraSeccion, CuerpoRayado } from '@/components/app/tabla-seccion';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableCell, TableHead, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { formatoNumero } from '@/lib/formato';
import { cn } from '@/lib/utils';

import { BarraConformes, EficienciaChip } from './eficiencia-chip';
import { NIVEL, nivel, type KpisEficiencia, type Nivel, type PedidoEficiencia } from './tipos';

const TODOS = 'todos';
const titulo = (p: PedidoEficiencia) => (p.pedido_id === null ? 'Órdenes manuales' : `Pedido #${p.pedido_id}`);
const COLUMNAS = {
    pedido: (p: PedidoEficiencia) => p.pedido_id, // las manuales (sin pedido) al final
    conformes: (p: PedidoEficiencia) => p.producido,
    eficiencia: (p: PedidoEficiencia) => p.eficiencia,
    ordenes: (p: PedidoEficiencia) => p.total_ordenes,
};

/**
 * Eficiencia (first-pass yield) = conformes / (conformes + defectuosas),
 * ponderada por unidades en cada pedido. Los más críticos van primero.
 */
export default function ReporteEficiencia({ pedidos, kpis }: { pedidos: PedidoEficiencia[]; kpis: KpisEficiencia }) {
    const [buscar, setBuscar] = useState('');
    const [filtro, setFiltro] = useState<Nivel | typeof TODOS>(TODOS);
    const [abierto, setAbierto] = useState<PedidoEficiencia>();

    const filtradas = useMemo(() => {
        const k = buscar.trim().toLowerCase();
        return pedidos.filter((p) => (filtro === TODOS || nivel(p.eficiencia) === filtro) && (!k || titulo(p).toLowerCase().includes(k) || p.cliente.toLowerCase().includes(k)));
    }, [pedidos, buscar, filtro]);
    // Por defecto, los más críticos primero (menor eficiencia; sin producción al final).
    const { ordenadas: filas, orden, alternar } = useOrdenColumnas(filtradas, COLUMNAS, { clave: 'eficiencia', dir: 'asc' });

    const g = kpis.eficiencia_global;

    return (
        <AppLayout titulo="Eficiencia de producción">
            <div className="grid grid-cols-1 gap-4">
                <Card>
                    <CardContent className="grid gap-4 md:grid-cols-[auto_1fr] md:items-center">
                        <div>
                            <p className="text-muted-foreground text-xs">Eficiencia global</p>
                            <p className={cn('text-5xl font-semibold tabular', g === null ? 'text-muted-foreground' : nivel(g) === 'ok' ? 'text-success' : nivel(g) === 'warn' ? 'text-info' : 'text-destructive')}>
                                {g === null ? '—' : `${formatoNumero(g)} %`}
                            </p>
                        </div>
                        <div className="grid gap-2">
                            <BarraConformes producido={kpis.producido} defectuoso={kpis.defectuoso} className="h-4" />
                            <p className="text-muted-foreground flex flex-wrap gap-x-4 text-xs">
                                <span><span className="bg-success mr-1 inline-block size-2 rounded-full" />{formatoNumero(kpis.producido)} conformes</span>
                                <span><span className="bg-destructive mr-1 inline-block size-2 rounded-full" />{formatoNumero(kpis.defectuoso)} defectuosas</span>
                                <span>{kpis.pedidos_produccion} de {kpis.pedidos_total} pedidos con producción</span>
                            </p>
                            <p className="text-muted-foreground text-xs">Eficiencia = conformes ÷ (conformes + defectuosas). Cada reproceso aprobado vuelve a sumar a conformes.</p>
                        </div>
                    </CardContent>
                </Card>

                <div className="flex flex-wrap items-center gap-2">
                    <div className="relative min-w-56 flex-1">
                        <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2" />
                        <Input type="search" value={buscar} onChange={(e) => setBuscar(e.target.value)} placeholder="Buscar por pedido o cliente…" aria-label="Buscar" className="pl-8" />
                    </div>
                    <Select value={filtro} onValueChange={(v) => setFiltro(v as Nivel | typeof TODOS)}>
                        <SelectTrigger className="w-48" aria-label="Filtrar por eficiencia"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value={TODOS}>Todas las eficiencias</SelectItem>
                            <SelectItem value="ok">Alta (≥ 90 %)</SelectItem>
                            <SelectItem value="warn">Media (70–89 %)</SelectItem>
                            <SelectItem value="bad">Baja (&lt; 70 %)</SelectItem>
                            <SelectItem value="na">Sin producción</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div className="bg-card overflow-x-auto rounded-lg border">
                    <Table>
                        <CabeceraSeccion>
                            <TableRow className="hover:bg-transparent">
                                <CabeceraOrdenable clave="pedido" orden={orden} onOrdenar={alternar}>Pedido</CabeceraOrdenable>
                                <CabeceraOrdenable clave="conformes" orden={orden} onOrdenar={alternar} className="w-2/5">Conformes / defectuosas</CabeceraOrdenable>
                                <CabeceraOrdenable clave="eficiencia" orden={orden} onOrdenar={alternar}>Eficiencia</CabeceraOrdenable>
                                <CabeceraOrdenable clave="ordenes" orden={orden} onOrdenar={alternar} className="text-right">Órdenes</CabeceraOrdenable>
                                <TableHead><span className="sr-only">Detalle</span></TableHead>
                            </TableRow>
                        </CabeceraSeccion>
                        <CuerpoRayado>
                            {filas.map((p) => (
                                <TableRow key={p.pedido_id ?? 'manual'}>
                                    <TableCell><span className="font-medium">{titulo(p)}</span><span className="text-muted-foreground block text-xs">{p.cliente}</span></TableCell>
                                    <TableCell>
                                        <BarraConformes producido={p.producido} defectuoso={p.defectuoso} />
                                        <span className="text-muted-foreground mt-1 block text-xs tabular">{formatoNumero(p.producido)} / {formatoNumero(p.defectuoso)} de {formatoNumero(p.solicitado)} solicitadas</span>
                                    </TableCell>
                                    <TableCell><EficienciaChip valor={p.eficiencia} /></TableCell>
                                    <TableCell className="text-right tabular">{p.total_ordenes}</TableCell>
                                    <TableCell className="text-right"><Button variant="outline" size="sm" onClick={() => setAbierto(p)} aria-label={`Ver órdenes de ${titulo(p)}`}>Ver órdenes</Button></TableCell>
                                </TableRow>
                            ))}
                            {!filas.length && <TableRow className="hover:bg-transparent"><TableCell colSpan={5} className="text-muted-foreground h-24 text-center">{pedidos.length ? 'Ningún pedido coincide.' : 'Aún no hay órdenes de producción.'}</TableCell></TableRow>}
                        </CuerpoRayado>
                    </Table>
                </div>
            </div>

            <Dialog open={Boolean(abierto)} onOpenChange={(a) => !a && setAbierto(undefined)}>
                <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-2xl">
                    {abierto && (
                        <>
                            <DialogHeader>
                                <DialogTitle>{titulo(abierto)}</DialogTitle>
                                <DialogDescription>{abierto.cliente}</DialogDescription>
                            </DialogHeader>
                            <dl className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                {[['Solicitado', abierto.solicitado], ['Producido', abierto.producido], ['Defectuoso', abierto.defectuoso]].map(([k, v]) => (
                                    <div key={k} className="rounded-lg border p-3"><dt className="text-muted-foreground text-xs">{k}</dt><dd className="text-lg font-semibold tabular">{formatoNumero(Number(v))}</dd></div>
                                ))}
                                <div className="rounded-lg border p-3"><dt className="text-muted-foreground text-xs">Eficiencia</dt><dd className="mt-1"><EficienciaChip valor={abierto.eficiencia} className="text-sm" /></dd></div>
                            </dl>
                            <h3 className="text-sm font-medium">{abierto.ordenes.length === 1 ? 'Orden de producción' : `Órdenes de producción (${abierto.ordenes.length})`}</h3>
                            <ul className="grid gap-2">
                                {abierto.ordenes.map((o) => (
                                    <li key={o.orden_id} className="grid gap-1.5 rounded-lg border p-3 text-sm">
                                        <div className="flex flex-wrap items-baseline justify-between gap-2">
                                            <span><span className="text-muted-foreground tabular">#{o.orden_id}</span> {o.producto} <span className="text-muted-foreground text-xs">· {o.estado}</span></span>
                                            <EficienciaChip valor={o.eficiencia} />
                                        </div>
                                        <BarraConformes producido={o.producido} defectuoso={o.defectuoso} />
                                        <span className="text-muted-foreground text-xs tabular">{o.producido} conformes · {o.defectuoso} defectuosas · {o.solicitado} solicitadas</span>
                                    </li>
                                ))}
                            </ul>
                            <p className="text-muted-foreground text-xs">Umbrales: {NIVEL.ok.etiqueta} ≥ 90 %, {NIVEL.warn.etiqueta} 70–89 %, {NIVEL.bad.etiqueta} &lt; 70 %.</p>
                        </>
                    )}
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
