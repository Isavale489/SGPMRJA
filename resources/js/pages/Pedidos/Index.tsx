import { Link, router } from '@inertiajs/react';
import { Ban, EllipsisVertical, Eye, FileText, Pencil, Plus, RotateCcw, Search, Trash2, Wallet } from 'lucide-react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';

import { Campo } from '@/components/app/campo';
import { ConfirmarPeligro } from '@/components/app/confirmar-peligro';
import { EstadoBadge } from '@/components/app/estado-badge';
import { ExportarPdf } from '@/components/app/exportar-pdf';
import { Monto } from '@/components/app/monto';
import { TablaServidor, type Columna } from '@/components/app/tabla-servidor';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useFiltrosUrl } from '@/hooks/use-filtros-url';
import { usePermisos } from '@/hooks/use-permisos';
import AppLayout from '@/layouts/app-layout';
import { formatoFecha, formatoNumero } from '@/lib/formato';
import { cn } from '@/lib/utils';

import { accionesDe } from './acciones';
import { DetallePedido } from './detalle-pedido';
import type { FiltrosPedidos, PaginaPedidos, PedidoFila } from './tipos';

const TODOS = 'todos';
const PROPS = ['registros', 'filtros'];
const ORDENES = { recientes: 'Más recientes', monto_desc: 'Mayor monto', entrega_asc: 'Entrega más próxima' } as const;

type Accion = 'cancelar' | 'reactivar' | 'eliminar';

const limpios = (f: FiltrosPedidos) => Object.fromEntries(Object.entries(f).filter(([, v]) => v !== undefined && v !== ''));

/** Barra de abono: cuánto del total ya se pagó (el mínimo formaliza el pedido). */
export function BarraAbono({ porcentaje, minimo, className }: { porcentaje: number; minimo: number; className?: string }) {
    const p = Math.max(0, Math.min(100, porcentaje));
    return (
        <span className={cn('bg-muted relative block h-1.5 w-full overflow-hidden rounded-full', className)} role="img" aria-label={`Abonado ${formatoNumero(p)} %`}>
            <span className={cn('absolute inset-y-0 left-0 rounded-full', p >= 100 ? 'bg-success' : p >= minimo ? 'bg-info' : 'bg-warning')} style={{ width: `${p}%` }} />
            <span className="bg-foreground/40 absolute inset-y-0 w-px" style={{ left: `${Math.min(100, minimo)}%` }} aria-hidden />
        </span>
    );
}

export default function PedidosIndex({ registros, filtros: iniciales, detalle, estados, terminos, urls }: PaginaPedidos) {
    const { puede } = usePermisos();
    const { ver: verInicial, ...filtrosIniciales } = iniciales;
    const { filtros, cambiar, limpiar, cargando } = useFiltrosUrl<FiltrosPedidos>(urls.index, filtrosIniciales, PROPS);
    const [ver, setVer] = useState<string | undefined>(verInicial);
    const [confirmando, setConfirmando] = useState<{ accion: Accion; pedido: PedidoFila }>();
    const hayFiltros = Boolean(filtros.buscar || filtros.estado || filtros.desde || filtros.hasta || (filtros.orden && filtros.orden !== 'recientes'));

    // Ficha «Ver» por recarga parcial (?ver=ID). Si al volver no llegó, no existe.
    const [pidiendo, setPidiendo] = useState(false);
    const abrirDetalle = (id: number) => {
        setVer(String(id));
        setPidiendo(true);
        router.get(
            urls.index,
            { ...limpios(filtros), ver: id },
            {
                only: ['detalle'],
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onFinish: (v) => {
                    if (!v.interrupted && !v.cancelled) setPidiendo(false);
                },
            },
        );
    };
    const cerrarDetalle = () => {
        setVer(undefined);
        router.get(urls.index, limpios(filtros), { only: ['detalle'], preserveState: true, preserveScroll: true, replace: true });
    };
    const noExiste = Boolean(ver) && !pidiendo && detalle?.id !== Number(ver);
    useEffect(() => {
        if (!noExiste) return;
        toast.error(`El pedido #${ver} no existe o fue eliminado.`);
        cerrarDetalle();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [noExiste]);

    const ejecutar = (accion: Accion, id: number) => {
        const opciones = { preserveScroll: true, preserveState: true };
        if (accion === 'eliminar') {
            if (String(id) === ver) setVer(undefined);
            router.delete(`${urls.index}/${id}`, opciones);
        } else {
            router.patch(`${urls.index}/${id}/${accion}`, {}, opciones);
        }
    };

    const columnas: Columna<PedidoFila>[] = [
        {
            id: 'numero',
            encabezado: 'Nro.',
            celda: (p) => (
                <span className="text-muted-foreground tabular">
                    #{p.id}
                    {p.cotizacion_id && <span className="block text-xs">Cot. #{p.cotizacion_id}</span>}
                </span>
            ),
        },
        {
            id: 'cliente',
            encabezado: 'Cliente',
            celda: (p) => (
                <>
                    <span className={p.cliente_inhabilitado ? 'text-muted-foreground font-medium' : 'font-medium'}>{p.cliente}</span>
                    {p.cliente_inhabilitado && <span className="bg-destructive/10 text-destructive ml-2 rounded-full px-2 py-0.5 text-xs">Inhabilitado</span>}
                    {p.cliente_doc && <span className="text-muted-foreground block text-xs tabular">{p.cliente_doc}</span>}
                </>
            ),
        },
        {
            id: 'entrega',
            encabezado: 'Entrega',
            celda: (p) => (
                <span className="tabular whitespace-nowrap">
                    {p.entrega ? formatoFecha(p.entrega) : '—'}
                    {p.fecha && <span className="text-muted-foreground block text-xs">Pedido {formatoFecha(p.fecha)}</span>}
                </span>
            ),
        },
        { id: 'total', encabezado: 'Total', className: 'text-right', celda: (p) => <Monto usd={p.total} tasa={p.tasa} className="items-end" /> },
        {
            id: 'abono',
            encabezado: 'Abonado',
            className: 'min-w-32',
            celda: (p) => (
                <span className="grid gap-1">
                    <span className="text-xs tabular">{formatoNumero(p.porcentaje_abonado)} %</span>
                    <BarraAbono porcentaje={p.porcentaje_abonado} minimo={terminos.abono} />
                </span>
            ),
        },
        {
            id: 'estado',
            encabezado: 'Estado',
            celda: (p) => (
                <>
                    <EstadoBadge estado={p.estado} />
                    {p.prioridad !== 'Normal' && <span className="text-warning mt-1 block text-xs">Prioridad {p.prioridad.toLowerCase()}</span>}
                </>
            ),
        },
        {
            id: 'acciones',
            encabezado: <span className="sr-only">Acciones</span>,
            className: 'text-right',
            celda: (p) => {
                const a = accionesDe(p, puede);
                return (
                    <div className="flex justify-end gap-1">
                        <Button variant="ghost" size="icon" onClick={() => abrirDetalle(p.id)} aria-label={`Ver pedido #${p.id}`}>
                            <Eye />
                        </Button>
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="ghost" size="icon" aria-label={`Más acciones del pedido #${p.id}`}>
                                    <EllipsisVertical />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                {a.editar && (
                                    <DropdownMenuItem asChild>
                                        <Link href={`${urls.index}/${p.id}/editar`}>
                                            {p.estado === 'Completado' ? <Wallet /> : <Pencil />} {p.estado === 'Completado' ? 'Registrar pago' : 'Editar pagos y entrega'}
                                        </Link>
                                    </DropdownMenuItem>
                                )}
                                {a.pdf && (
                                    <DropdownMenuItem asChild>
                                        <a href={`${urls.index}/${p.id}/pdf`} target="_blank" rel="noopener">
                                            <FileText /> Ver PDF
                                        </a>
                                    </DropdownMenuItem>
                                )}
                                {a.reactivar && (
                                    <DropdownMenuItem onSelect={() => setConfirmando({ accion: 'reactivar', pedido: p })}>
                                        <RotateCcw /> Reactivar
                                    </DropdownMenuItem>
                                )}
                                {(a.cancelar || a.eliminar) && <DropdownMenuSeparator />}
                                {a.cancelar && (
                                    <DropdownMenuItem variant="destructive" onSelect={() => setConfirmando({ accion: 'cancelar', pedido: p })}>
                                        <Ban /> Cancelar pedido
                                    </DropdownMenuItem>
                                )}
                                {a.eliminar && (
                                    <DropdownMenuItem variant="destructive" onSelect={() => setConfirmando({ accion: 'eliminar', pedido: p })}>
                                        <Trash2 /> Eliminar
                                    </DropdownMenuItem>
                                )}
                                {!a.editar && !a.pdf && !a.reactivar && !a.cancelar && !a.eliminar && <DropdownMenuItem disabled>Sin acciones disponibles</DropdownMenuItem>}
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                );
            },
        },
    ];

    const confirmacion = confirmando && textos(confirmando.accion, confirmando.pedido);

    return (
        <AppLayout
            titulo="Pedidos"
            acciones={
                <>
                    {puede('pedidos.pdf') && (
                        <ExportarPdf
                            url={urls.reportePdf}
                            recurso="pedidos"
                            fecha="Entrega"
                            filtros={[
                                { parametro: 'estado', etiqueta: 'Estado', todos: 'Todos los estados', opciones: estados.map((e) => ({ valor: e, etiqueta: e })) },
                                {
                                    parametro: 'orden',
                                    etiqueta: 'Ordenar por',
                                    todos: ORDENES.recientes,
                                    opciones: [
                                        { valor: 'monto_desc', etiqueta: ORDENES.monto_desc },
                                        { valor: 'entrega_asc', etiqueta: ORDENES.entrega_asc },
                                    ],
                                },
                            ]}
                            extra={(poner) => (
                                <Campo etiqueta="Cliente" ayuda="Nombre, razón social o documento (opcional).">
                                    <Input onChange={(e) => poner('cliente', e.target.value.trim() || undefined)} placeholder="Ej.: Confecciones" />
                                </Campo>
                            )}
                        />
                    )}
                    {puede('pedidos.gestionar') && (
                        <Button asChild>
                            <Link href={urls.crear}>
                                <Plus /> Nuevo pedido
                            </Link>
                        </Button>
                    )}
                </>
            }
        >
            <div className="grid gap-4">
                <div className="flex flex-wrap items-center gap-2">
                    <div className="relative min-w-56 flex-1">
                        <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2" />
                        <Input
                            type="search"
                            value={filtros.buscar ?? ''}
                            onChange={(e) => cambiar('buscar', e.target.value)}
                            placeholder="Buscar por n.º de pedido o cotización, cliente, documento o estado…"
                            aria-label="Buscar"
                            className="pl-8"
                        />
                    </div>
                    <Select value={filtros.estado ?? TODOS} onValueChange={(v) => cambiar('estado', v === TODOS ? undefined : (v as FiltrosPedidos['estado']))}>
                        <SelectTrigger className="w-44" aria-label="Filtrar por estado">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={TODOS}>Todos los estados</SelectItem>
                            {estados.map((e) => (
                                <SelectItem key={e} value={e}>
                                    {e}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Input type="date" value={filtros.desde ?? ''} onChange={(e) => cambiar('desde', e.target.value || undefined)} aria-label="Entrega desde" className="w-40" />
                    <Input type="date" value={filtros.hasta ?? ''} onChange={(e) => cambiar('hasta', e.target.value || undefined)} aria-label="Entrega hasta" className="w-40" />
                    <Select value={filtros.orden ?? 'recientes'} onValueChange={(v) => cambiar('orden', v === 'recientes' ? undefined : (v as FiltrosPedidos['orden']))}>
                        <SelectTrigger className="w-48" aria-label="Ordenar">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {Object.entries(ORDENES).map(([v, e]) => (
                                <SelectItem key={v} value={v}>
                                    {e}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    {hayFiltros && (
                        <Button variant="ghost" onClick={() => limpiar()}>
                            Limpiar
                        </Button>
                    )}
                </div>

                <TablaServidor
                    pagina={registros}
                    columnas={columnas}
                    only={PROPS}
                    cargando={cargando}
                    idFila={(p) => p.id}
                    vacio={hayFiltros ? 'Ningún pedido coincide con los filtros.' : 'Aún no hay pedidos. Se crean desde una cotización aprobada.'}
                />
            </div>

            <DetallePedido pedido={ver && detalle?.id === Number(ver) ? detalle : null} cargando={Boolean(ver) && pidiendo} onCerrar={cerrarDetalle} terminos={terminos} urls={urls} />

            <ConfirmarPeligro
                abierto={Boolean(confirmando)}
                onCerrar={() => setConfirmando(undefined)}
                titulo={confirmacion?.titulo ?? ''}
                descripcion={confirmacion?.descripcion ?? ''}
                accion={confirmacion?.accion}
                destructiva={confirmando?.accion !== 'reactivar'}
                onConfirmar={() => confirmando && ejecutar(confirmando.accion, confirmando.pedido.id)}
            />
        </AppLayout>
    );
}

function textos(accion: Accion, p: PedidoFila): { titulo: string; descripcion: string; accion: string } {
    switch (accion) {
        case 'cancelar':
            return {
                titulo: `¿Cancelar el pedido #${p.id}?`,
                descripcion: 'Deja de avanzar en producción. La cotización sigue como Convertida; se puede reactivar después.',
                accion: 'Cancelar pedido',
            };
        case 'reactivar':
            return { titulo: `¿Reactivar el pedido #${p.id}?`, descripcion: 'Vuelve a quedar gobernado por producción.', accion: 'Reactivar' };
        case 'eliminar':
            return {
                titulo: `¿Eliminar el pedido #${p.id}?`,
                descripcion: p.cotizacion_id
                    ? `La cotización #${p.cotizacion_id} vuelve a Aprobada y se puede convertir de nuevo. Los pagos registrados se descartan.`
                    : 'Esta acción no se puede deshacer.',
                accion: 'Eliminar',
            };
    }
}
