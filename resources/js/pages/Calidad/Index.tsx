import { router } from '@inertiajs/react';
import { ClipboardCheck, RotateCcw, Search } from 'lucide-react';
import { useState } from 'react';

import { ExportarPdf } from '@/components/app/exportar-pdf';
import { TablaServidor, type Columna } from '@/components/app/tabla-servidor';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useFiltrosUrl } from '@/hooks/use-filtros-url';
import { usePermisos } from '@/hooks/use-permisos';
import AppLayout from '@/layouts/app-layout';
import { formatoFecha, formatoNumero } from '@/lib/formato';
import type { Paginado } from '@/types';

import { FormularioInspeccion } from './formulario-inspeccion';

/** Espejo de ControlCalidadController::index() (lo verifica CalidadPaginaTest). */
export interface PedidoEnCola {
    pedido_id: number | null;
    cliente: string | null;
    ordenes: number;
    reinspecciones: number;
    ultima_fin: string | null;
}

/** Espejo de ControlCalidadController::cola(). */
export interface OrdenEnCola {
    id: number;
    producto: string;
    cantidad_solicitada: number;
    cantidad_producida: number;
    cantidad_defectuosa: number;
    fecha_fin: string | null;
    equipo: { id: number; nombre: string; producida: number }[];
    historial: {
        fecha: string | null;
        inspector: string | null;
        inspeccionada: number;
        aprobada: number;
        rechazada: number;
        resultado: 'aprobado' | 'observado' | 'rechazado';
        observaciones: string | null;
    }[];
}

type Filtros = { buscar?: string; estado?: 'pendiente' | 'reinspeccion'; orden?: 'recientes' | 'antiguos' };

interface Props {
    registros: Paginado<PedidoEnCola>;
    filtros: Filtros & { pedido?: string };
    cola: OrdenEnCola[] | null;
    urls: { index: string; reportePdf: string };
}

const TODOS = 'todos';
const clavePedido = (p: PedidoEnCola) => (p.pedido_id === null ? 'manual' : String(p.pedido_id));
const tituloPedido = (clave: string) => (clave === 'manual' ? 'Órdenes manuales' : `Pedido #${clave}`);

export default function CalidadIndex({ registros, filtros: iniciales, cola, urls }: Props) {
    const { puede } = usePermisos();
    const inspeccionar = puede('calidad.inspeccionar');
    const { pedido: pedidoInicial, ...filtrosIniciales } = iniciales;
    const { filtros, cambiar, limpiar, cargando } = useFiltrosUrl<Filtros>(urls.index, filtrosIniciales, ['registros', 'filtros']);

    const [pedido, setPedido] = useState<string | undefined>(pedidoInicial);
    const [inspeccion, setInspeccion] = useState<{ orden: OrdenEnCola; apertura: number }>();
    const hayFiltros = Boolean(filtros.buscar || filtros.estado || (filtros.orden && filtros.orden !== 'recientes'));

    // Abrir un pedido recarga solo su cola (con equipo e historial de cada orden).
    const verPedido = (clave: string) => {
        setPedido(clave);
        router.get(urls.index, { ...filtros, pedido: clave }, { only: ['cola'], preserveState: true, preserveScroll: true, replace: true });
    };
    const cerrarPedido = () => {
        setPedido(undefined);
        router.get(urls.index, { ...filtros }, { only: ['cola'], preserveState: true, preserveScroll: true, replace: true });
    };

    const columnas: Columna<PedidoEnCola>[] = [
        {
            id: 'pedido',
            encabezado: 'Pedido',
            celda: (p) => <span className="font-medium">{p.pedido_id === null ? 'Órdenes manuales' : `Pedido #${p.pedido_id}`}</span>,
        },
        { id: 'cliente', encabezado: 'Cliente', celda: (p) => <span className="text-muted-foreground">{p.cliente ?? '—'}</span> },
        {
            id: 'ordenes',
            encabezado: 'En cola',
            className: 'text-right',
            celda: (p) => (
                <span className="tabular">
                    {formatoNumero(p.ordenes)} {p.ordenes === 1 ? 'orden' : 'órdenes'}
                    {p.reinspecciones > 0 && (
                        <span className="text-warning ml-2 inline-flex items-center gap-1 text-xs">
                            <RotateCcw className="size-3" /> {p.reinspecciones} re-inspección
                        </span>
                    )}
                </span>
            ),
        },
        { id: 'fin', encabezado: 'Última finalización', celda: (p) => <span className="tabular">{p.ultima_fin ? formatoFecha(p.ultima_fin) : '—'}</span> },
        {
            id: 'acciones',
            encabezado: <span className="sr-only">Acciones</span>,
            className: 'text-right',
            celda: (p) => (
                <Button variant="outline" size="sm" onClick={() => verPedido(clavePedido(p))}>
                    <ClipboardCheck /> Ver órdenes
                </Button>
            ),
        },
    ];

    return (
        <AppLayout
            titulo="Control de calidad"
            acciones={
                <ExportarPdf
                    url={urls.reportePdf}
                    recurso="inspecciones"
                    fecha="Inspección"
                    filtros={[
                        {
                            parametro: 'resultado',
                            etiqueta: 'Resultado',
                            todos: 'Todos los resultados',
                            opciones: [
                                { valor: 'aprobado', etiqueta: 'Aprobado' },
                                { valor: 'observado', etiqueta: 'Aprobado con observaciones' },
                                { valor: 'rechazado', etiqueta: 'Rechazado' },
                            ],
                        },
                    ]}
                />
            }
        >
            <div className="grid gap-4">
                <p className="text-muted-foreground -mt-3 text-sm">Órdenes finalizadas que esperan inspección, agrupadas por pedido.</p>
                <div className="flex flex-wrap items-center gap-2">
                    <div className="relative min-w-56 flex-1">
                        <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2" />
                        <Input type="search" value={filtros.buscar ?? ''} onChange={(e) => cambiar('buscar', e.target.value)} placeholder="Buscar por cliente, producto o n.º de pedido…" aria-label="Buscar pedido" className="pl-8" />
                    </div>
                    <Select value={filtros.estado ?? TODOS} onValueChange={(v) => cambiar('estado', v === TODOS ? undefined : (v as Filtros['estado']))}>
                        <SelectTrigger className="w-60" aria-label="Filtrar por estado de calidad"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value={TODOS}>Todas</SelectItem>
                            <SelectItem value="pendiente">Pendiente (sin inspeccionar)</SelectItem>
                            <SelectItem value="reinspeccion">Re-inspección (tras reproceso)</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select value={filtros.orden ?? 'recientes'} onValueChange={(v) => cambiar('orden', v as Filtros['orden'])}>
                        <SelectTrigger className="w-52" aria-label="Ordenar"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="recientes">Finalización reciente</SelectItem>
                            <SelectItem value="antiguos">Finalización antigua</SelectItem>
                        </SelectContent>
                    </Select>
                    {hayFiltros && <Button variant="ghost" onClick={() => limpiar()}>Limpiar</Button>}
                </div>
                <TablaServidor
                    pagina={registros}
                    columnas={columnas}
                    only={['registros', 'filtros']}
                    cargando={cargando}
                    idFila={clavePedido}
                    vacio={hayFiltros ? 'Ningún pedido coincide con los filtros.' : 'No hay órdenes pendientes de inspección.'}
                />
            </div>

            <Dialog open={Boolean(pedido)} onOpenChange={(a) => !a && cerrarPedido()}>
                <DialogContent className="sm:max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>{pedido ? tituloPedido(pedido) : ''}</DialogTitle>
                        <DialogDescription>Órdenes finalizadas que esperan inspección.</DialogDescription>
                    </DialogHeader>
                    {cola === null ? (
                        <p className="text-muted-foreground py-6 text-center text-sm">Cargando órdenes…</p>
                    ) : cola.length === 0 ? (
                        <p className="text-muted-foreground py-6 text-center text-sm">Este pedido ya no tiene órdenes pendientes.</p>
                    ) : (
                        <ul className="grid gap-2">
                            {cola.map((o) => (
                                <li key={o.id} className="flex flex-wrap items-center gap-3 rounded-md border p-3">
                                    <div className="min-w-0 flex-1">
                                        <p className="truncate font-medium">{o.producto}</p>
                                        <p className="text-muted-foreground text-xs">
                                            Orden #{o.id} · {formatoNumero(o.cantidad_producida)} de {formatoNumero(o.cantidad_solicitada)} producidas
                                            {o.fecha_fin && ` · finalizó ${formatoFecha(o.fecha_fin)}`}
                                            {o.historial.length > 0 && ' · re-inspección'}
                                        </p>
                                    </div>
                                    {inspeccionar && (
                                        <Button size="sm" onClick={() => setInspeccion((i) => ({ orden: o, apertura: (i?.apertura ?? 0) + 1 }))}>
                                            Inspeccionar
                                        </Button>
                                    )}
                                </li>
                            ))}
                        </ul>
                    )}
                </DialogContent>
            </Dialog>

            {inspeccion && (
                <FormularioInspeccion
                    key={inspeccion.apertura}
                    orden={inspeccion.orden}
                    pedido={pedido ? tituloPedido(pedido) : ''}
                    url={urls.index}
                    onCerrar={() => setInspeccion(undefined)}
                />
            )}
        </AppLayout>
    );
}
