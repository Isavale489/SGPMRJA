import { Link, router } from '@inertiajs/react';
import { ListChecks, Plus, Search, UserRoundCheck } from 'lucide-react';
import { useState } from 'react';

import { ConfirmarPeligro } from '@/components/app/confirmar-peligro';
import { ExportarPdf } from '@/components/app/exportar-pdf';
import { TablaServidor, type Columna } from '@/components/app/tabla-servidor';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useFiltrosUrl } from '@/hooks/use-filtros-url';
import { usePermisos } from '@/hooks/use-permisos';
import AppLayout from '@/layouts/app-layout';
import { formatoFecha, formatoNumero } from '@/lib/formato';

import { Avance } from './avance';
import { Barra } from './barra';
import { CancelarOrden } from './cancelar';
import { DetalleOrden } from './detalle-orden';
import { DialogoPedido } from './dialogo-pedido';
import { Etapas } from './etapas';
import { MisOrdenes } from './mis-ordenes';
import { progreso, tituloPedido, type FiltrosOrdenes, type ModoOrden, type OrdenFila, type PaginaOrdenes, type PedidoConOrdenes } from './tipos';

const TODOS = 'todos';
const PROPS = ['registros', 'filtros'];
const ESTADOS = ['Pendiente', 'En Proceso', 'Finalizado', 'Cancelado'] as const;

const clavePedido = (p: PedidoConOrdenes) => (p.pedido_id === null ? 'manual' : String(p.pedido_id));

export default function OrdenesIndex({ filtros: iniciales, registros, ordenes, orden, misOrdenes, empleados, urls }: PaginaOrdenes) {
    const { puede } = usePermisos();
    const { pedido: pedidoInicial, ver: verInicial, empleado: empleadoInicial, ...filtrosIniciales } = iniciales;
    const { filtros, cambiar, limpiar, cargando } = useFiltrosUrl<FiltrosOrdenes>(urls.index, filtrosIniciales, PROPS);

    // Lo abierto va en la URL (?pedido, ?ver, ?empleado): recargar conserva la vista.
    const [pedido, setPedido] = useState<string | undefined>(pedidoInicial);
    const [ver, setVer] = useState<{ id: string; modo: ModoOrden; quien?: string } | undefined>(verInicial ? { id: verInicial, modo: 'ver' } : undefined);
    const [empleado, setEmpleado] = useState<string | undefined>(empleadoInicial);
    const [misAbierto, setMisAbierto] = useState(Boolean(empleadoInicial));
    const [cancelando, setCancelando] = useState<OrdenFila>();
    const [eliminando, setEliminando] = useState<OrdenFila>();
    const hayFiltros = Boolean(filtros.buscar || filtros.estado || filtros.desde || filtros.hasta || (filtros.orden && filtros.orden !== 'recientes'));

    const visitar = (abiertos: { pedido?: string; ver?: string; empleado?: string }, only: string[]) =>
        router.get(urls.index, { ...filtros, ...abiertos }, { only, preserveState: true, preserveScroll: true, replace: true });

    const abrirPedido = (clave?: string) => {
        setPedido(clave);
        visitar({ pedido: clave, empleado }, ['ordenes']);
    };
    // `quien`: al registrar avance desde «Órdenes por empleado», el avance se le atribuye a él.
    const abrirOrden = (id: number, modo: ModoOrden, quien?: string) => {
        setVer({ id: String(id), modo, quien });
        visitar({ pedido, ver: String(id), empleado }, ['orden']);
    };
    const cerrarOrden = () => {
        setVer(undefined);
        visitar({ pedido, empleado }, ['orden']);
    };
    const elegirEmpleado = (id?: string) => {
        setEmpleado(id);
        visitar({ pedido, ver: ver?.id, empleado: id }, ['misOrdenes']);
    };

    // Tras una acción se recargan la tabla, las órdenes abiertas y el detalle.
    const mutar = { preserveState: true, preserveScroll: true };
    const ordenCargada = ver && orden?.id === Number(ver.id) ? orden : null;

    const columnas: Columna<PedidoConOrdenes>[] = [
        { id: 'pedido', encabezado: 'Pedido', celda: (p) => <span className="font-medium">{tituloPedido(clavePedido(p))}</span> },
        { id: 'cliente', encabezado: 'Cliente', celda: (p) => <span className="text-muted-foreground">{p.cliente ?? '—'}</span> },
        {
            id: 'ordenes',
            encabezado: 'Órdenes',
            celda: (p) => (
                <span className="flex flex-wrap gap-x-3 gap-y-0.5 text-xs">
                    <span className="text-sm font-medium tabular">{p.total_ordenes}</span>
                    {p.pendientes > 0 && <span className="text-warning">{p.pendientes} pendiente{p.pendientes === 1 ? '' : 's'}</span>}
                    {p.en_proceso > 0 && <span className="text-primary">{p.en_proceso} en proceso</span>}
                    {p.finalizadas > 0 && <span className="text-success">{p.finalizadas} finalizada{p.finalizadas === 1 ? '' : 's'}</span>}
                    {p.canceladas > 0 && <span className="text-destructive">{p.canceladas} cancelada{p.canceladas === 1 ? '' : 's'}</span>}
                </span>
            ),
        },
        {
            id: 'progreso',
            encabezado: 'Progreso',
            className: 'w-56',
            celda: (p) => <Barra valor={progreso(p.producido, p.solicitado)} texto={`${formatoNumero(p.producido)} de ${formatoNumero(p.solicitado)}`} />,
        },
        { id: 'entrega', encabezado: 'Entrega estimada', celda: (p) => <span className="tabular">{p.entrega ? formatoFecha(p.entrega) : '—'}</span> },
        {
            id: 'acciones',
            encabezado: <span className="sr-only">Acciones</span>,
            className: 'text-right',
            celda: (p) => (
                <Button variant="outline" size="sm" onClick={() => abrirPedido(clavePedido(p))} aria-label={`Ver órdenes de ${tituloPedido(clavePedido(p))}`}>
                    <ListChecks /> Ver órdenes
                </Button>
            ),
        },
    ];

    return (
        <AppLayout
            titulo="Órdenes de producción"
            acciones={
                <>
                    <Button variant="ghost" onClick={() => setMisAbierto(true)}><UserRoundCheck /> Órdenes por empleado</Button>
                    {puede('ordenes.pdf') && (
                        <ExportarPdf
                            url={urls.reportePdf}
                            recurso="órdenes"
                            fecha="Entrega estimada"
                            filtros={[
                                { parametro: 'estado', etiqueta: 'Estado', todos: 'Todos los estados', opciones: ESTADOS.map((e) => ({ valor: e, etiqueta: e })) },
                                { parametro: 'orden', etiqueta: 'Ordenar por', todos: 'Más recientes', opciones: [{ valor: 'progreso_desc', etiqueta: 'Mayor progreso' }, { valor: 'progreso_asc', etiqueta: 'Menor progreso' }] },
                            ]}
                        />
                    )}
                    {puede('ordenes.gestionar') && (
                        <Button asChild><Link href={urls.crear}><Plus /> Nueva orden</Link></Button>
                    )}
                </>
            }
        >
            <div className="grid gap-4">
                <p className="text-muted-foreground -mt-3 text-sm">Una fila por pedido. Cada línea del pedido puede repartirse en varias órdenes, cada una con su equipo.</p>
                <div className="flex flex-wrap items-center gap-2">
                    <div className="relative min-w-56 flex-1">
                        <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2" />
                        <Input type="search" value={filtros.buscar ?? ''} onChange={(e) => cambiar('buscar', e.target.value)} placeholder="Buscar por pedido o cliente…" aria-label="Buscar" className="pl-8" />
                    </div>
                    <Select value={filtros.estado ?? TODOS} onValueChange={(v) => cambiar('estado', v === TODOS ? undefined : (v as FiltrosOrdenes['estado']))}>
                        <SelectTrigger className="w-44" aria-label="Filtrar por estado"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value={TODOS}>Todos los estados</SelectItem>
                            {ESTADOS.map((e) => <SelectItem key={e} value={e}>{e}</SelectItem>)}
                        </SelectContent>
                    </Select>
                    <Input type="date" value={filtros.desde ?? ''} onChange={(e) => cambiar('desde', e.target.value || undefined)} aria-label="Entrega desde" className="w-40" />
                    <Input type="date" value={filtros.hasta ?? ''} onChange={(e) => cambiar('hasta', e.target.value || undefined)} aria-label="Entrega hasta" className="w-40" />
                    <Select value={filtros.orden ?? 'recientes'} onValueChange={(v) => cambiar('orden', v === 'recientes' ? undefined : (v as FiltrosOrdenes['orden']))}>
                        <SelectTrigger className="w-44" aria-label="Ordenar"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="recientes">Más recientes</SelectItem>
                            <SelectItem value="progreso_desc">Mayor progreso</SelectItem>
                            <SelectItem value="progreso_asc">Menor progreso</SelectItem>
                        </SelectContent>
                    </Select>
                    {hayFiltros && <Button variant="ghost" onClick={() => limpiar()}>Limpiar</Button>}
                </div>

                <TablaServidor
                    pagina={registros}
                    columnas={columnas}
                    only={PROPS}
                    cargando={cargando}
                    idFila={(p) => clavePedido(p)}
                    vacio={hayFiltros ? 'Ningún pedido coincide con los filtros.' : 'Aún no hay órdenes de producción.'}
                />
            </div>

            <DialogoPedido
                clave={pedido}
                ordenes={ordenes}
                onCerrar={() => abrirPedido(undefined)}
                onAbrir={abrirOrden}
                onCancelar={setCancelando}
                onEliminar={setEliminando}
                urls={urls}
            />

            <MisOrdenes
                abierto={misAbierto}
                onCerrar={() => { setMisAbierto(false); if (empleado) elegirEmpleado(undefined); }}
                empleados={empleados}
                empleado={empleado}
                datos={misOrdenes}
                onElegir={elegirEmpleado}
                onAvance={(id) => abrirOrden(id, 'avance', empleado)}
            />

            <DetalleOrden
                abierto={ver?.modo === 'ver'}
                orden={ver?.modo === 'ver' ? ordenCargada : null}
                onCerrar={cerrarOrden}
                onModo={(modo) => ver && setVer({ ...ver, modo })}
                urls={urls}
            />
            {ver?.modo === 'avance' && <Avance orden={ordenCargada} empleadoId={ver.quien ? Number(ver.quien) : undefined} onCerrar={cerrarOrden} url={urls.index} />}
            {ver?.modo === 'etapas' && <Etapas orden={ordenCargada} empleados={empleados} onCerrar={cerrarOrden} url={urls.index} />}

            {cancelando && <CancelarOrden key={cancelando.id} orden={cancelando} onCerrar={() => setCancelando(undefined)} url={urls.index} />}

            <ConfirmarPeligro
                abierto={Boolean(eliminando)}
                onCerrar={() => setEliminando(undefined)}
                titulo={`¿Eliminar la orden #${eliminando?.id ?? ''}?`}
                descripcion="Se borra la orden y se devuelven al inventario los insumos que había comprometido. Solo es posible mientras está Pendiente."
                onConfirmar={() => eliminando && router.delete(`${urls.index}/${eliminando.id}`, mutar)}
            />
        </AppLayout>
    );
}
