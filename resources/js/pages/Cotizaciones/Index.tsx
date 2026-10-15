import { Link, router } from '@inertiajs/react';
import { ArrowRightLeft, Ban, CheckCheck, EllipsisVertical, Eye, FileText, Pencil, Plus, RotateCcw, Search, Trash2, Undo2, X } from 'lucide-react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';

import { BarraFiltros } from '@/components/app/barra-filtros';
import { Buscador } from '@/components/app/buscador';
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
import { formatoFecha } from '@/lib/formato';

import { accionesDe } from './calculos';
import { DetalleCotizacion } from './detalle-cotizacion';
import type { ClienteCotizacion, CotizacionFila, FiltrosCotizaciones, PaginaCotizaciones } from './tipos';

const TODOS = 'todos';
const PROPS = ['registros', 'filtros'];
const ORDENES = { recientes: 'Más recientes', total_desc: 'Mayor total', total_asc: 'Menor total' } as const;

type Accion = 'Aprobada' | 'Pendiente' | 'Cancelada' | 'reactivar' | 'eliminar';

export default function CotizacionesIndex({ registros, filtros: iniciales, detalle, estados, diasVigencia, iva, terminos, urls }: PaginaCotizaciones) {
    const { puede } = usePermisos();
    const { ver: verInicial, ...filtrosIniciales } = iniciales;
    const { filtros, cambiar, limpiar, cargando } = useFiltrosUrl<FiltrosCotizaciones>(urls.index, filtrosIniciales, PROPS);
    const [ver, setVer] = useState<string | undefined>(verInicial);
    const [confirmando, setConfirmando] = useState<{ accion: Accion; cotizacion: CotizacionFila }>();
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
        toast.error(`La cotización #${ver} no existe o fue eliminada.`);
        cerrarDetalle();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [noExiste]);

    const ejecutar = (accion: Accion, id: number) => {
        const opciones = { preserveScroll: true, preserveState: true };
        if (accion === 'eliminar') {
            if (String(id) === ver) setVer(undefined);
            router.delete(`${urls.index}/${id}`, opciones);
        } else if (accion === 'reactivar') {
            router.post(`${urls.index}/${id}/reactivar`, {}, opciones);
        } else {
            router.put(`${urls.index}/${id}/estado`, { estado: accion }, opciones);
        }
    };

    const columnas: Columna<CotizacionFila>[] = [
        { id: 'numero', encabezado: 'Nro.', celda: (c) => <span className="text-muted-foreground tabular">#{c.id}</span> },
        {
            id: 'cliente',
            encabezado: 'Cliente',
            celda: (c) => (
                <>
                    <span className={c.cliente_inhabilitado ? 'text-muted-foreground font-medium' : 'font-medium'}>{c.cliente}</span>
                    {c.cliente_inhabilitado && <span className="bg-destructive/10 text-destructive ml-2 rounded-full px-2 py-0.5 text-xs">Inhabilitado</span>}
                    {c.cliente_doc && <span className="text-muted-foreground block text-xs tabular">{c.cliente_doc}</span>}
                </>
            ),
        },
        {
            id: 'fecha',
            encabezado: 'Fecha',
            celda: (c) => (
                <span className="tabular whitespace-nowrap">
                    {c.fecha ? formatoFecha(c.fecha) : '—'}
                    {c.validez && <span className="text-muted-foreground block text-xs">Válida hasta {formatoFecha(c.validez)}</span>}
                </span>
            ),
        },
        { id: 'total', encabezado: 'Total', className: 'text-right', celda: (c) => <Monto usd={c.total} tasa={c.tasa} className="items-end" /> },
        {
            id: 'estado',
            encabezado: 'Estado',
            celda: (c) => (
                <>
                    <EstadoBadge estado={c.estado} />
                    {c.prioridad !== 'Normal' && <span className="text-warning mt-1 block text-xs">Prioridad {c.prioridad.toLowerCase()}</span>}
                </>
            ),
        },
        {
            id: 'acciones',
            encabezado: <span className="sr-only">Acciones</span>,
            className: 'text-right',
            celda: (c) => {
                const a = accionesDe(c, puede);
                const cambios = a.aprobar || a.pendiente || a.cancelar || a.reactivar;
                return (
                    <div className="flex justify-end gap-1">
                        <Button variant="ghost" size="icon" onClick={() => abrirDetalle(c.id)} aria-label={`Ver cotización #${c.id}`}>
                            <Eye />
                        </Button>
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="ghost" size="icon" aria-label={`Más acciones de la cotización #${c.id}`}>
                                    <EllipsisVertical />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                {a.convertirPedido && (
                                    <DropdownMenuItem tono="principal" asChild>
                                        <Link href={`${urls.convertir}?cotizacion=${c.id}`}>
                                            <ArrowRightLeft /> Convertir a pedido
                                        </Link>
                                    </DropdownMenuItem>
                                )}
                                {a.reactivar && (
                                    <DropdownMenuItem tono="restaurar" onSelect={() => setConfirmando({ accion: 'reactivar', cotizacion: c })}>
                                        <RotateCcw /> Reactivar cotización
                                    </DropdownMenuItem>
                                )}
                                {a.aprobar && (
                                    <DropdownMenuItem tono="principal" onSelect={() => setConfirmando({ accion: 'Aprobada', cotizacion: c })}>
                                        <CheckCheck /> Aprobar
                                    </DropdownMenuItem>
                                )}
                                {a.pendiente && (
                                    <DropdownMenuItem tono="aviso" onSelect={() => setConfirmando({ accion: 'Pendiente', cotizacion: c })}>
                                        <Undo2 /> {c.estado === 'Cancelada' ? 'Reactivar (Pendiente)' : 'Volver a Pendiente'}
                                    </DropdownMenuItem>
                                )}
                                {a.editar && (
                                    <DropdownMenuItem tono="editar" asChild>
                                        <Link href={`${urls.index}/${c.id}/editar`}>
                                            <Pencil /> Editar
                                        </Link>
                                    </DropdownMenuItem>
                                )}
                                {a.pdf && (
                                    <DropdownMenuItem tono="documento" asChild>
                                        <a href={`${urls.index}/${c.id}/pdf`} target="_blank" rel="noopener">
                                            <FileText /> Ver PDF
                                        </a>
                                    </DropdownMenuItem>
                                )}
                                {(a.cancelar || a.eliminar) && (cambios || a.editar || a.pdf) && <DropdownMenuSeparator />}
                                {a.cancelar && (
                                    <DropdownMenuItem tono="aviso" onSelect={() => setConfirmando({ accion: 'Cancelada', cotizacion: c })}>
                                        <Ban /> Cancelar cotización
                                    </DropdownMenuItem>
                                )}
                                {a.eliminar && (
                                    <DropdownMenuItem tono="peligro" onSelect={() => setConfirmando({ accion: 'eliminar', cotizacion: c })}>
                                        <Trash2 /> Eliminar
                                    </DropdownMenuItem>
                                )}
                                {!cambios && !a.editar && !a.pdf && !a.eliminar && !a.convertirPedido && <DropdownMenuItem disabled>Sin acciones disponibles</DropdownMenuItem>}
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                );
            },
        },
    ];

    const confirmacion = confirmando && textos(confirmando.accion, confirmando.cotizacion.id, diasVigencia);

    return (
        <AppLayout
            titulo="Cotizaciones"
            acciones={
                <>
                    {puede('cotizaciones.pdf') && (
                        <ExportarPdf
                            url={urls.reportePdf}
                            recurso="cotizaciones"
                            fecha="Emisión"
                            filtros={[
                                { parametro: 'estado', etiqueta: 'Estado', todos: 'Todos los estados', opciones: estados.map((e) => ({ valor: e, etiqueta: e })) },
                                {
                                    parametro: 'orden',
                                    etiqueta: 'Ordenar por',
                                    todos: ORDENES.recientes,
                                    opciones: [
                                        { valor: 'total_desc', etiqueta: ORDENES.total_desc },
                                        { valor: 'total_asc', etiqueta: ORDENES.total_asc },
                                    ],
                                },
                            ]}
                            extra={(poner) => <ClientePdf url={urls.buscarCliente} onCambiar={(id) => poner('cliente_id', id)} />}
                        />
                    )}
                    {puede('cotizaciones.gestionar') && (
                        <Button asChild>
                            <Link href={urls.crear}>
                                <Plus /> Nueva cotización
                            </Link>
                        </Button>
                    )}
                </>
            }
        >
            <div className="grid gap-4">
                <BarraFiltros>
                    <div className="relative min-w-56 flex-1">
                        <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2" />
                        <Input
                            type="search"
                            value={filtros.buscar ?? ''}
                            onChange={(e) => cambiar('buscar', e.target.value)}
                            placeholder="Buscar por n.º, cliente, documento o estado…"
                            aria-label="Buscar"
                            className="pl-8"
                        />
                    </div>
                    <Select value={filtros.estado ?? TODOS} onValueChange={(v) => cambiar('estado', v === TODOS ? undefined : (v as FiltrosCotizaciones['estado']))}>
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
                    <Input type="date" value={filtros.desde ?? ''} onChange={(e) => cambiar('desde', e.target.value || undefined)} aria-label="Emitidas desde" className="w-40" />
                    <Input type="date" value={filtros.hasta ?? ''} onChange={(e) => cambiar('hasta', e.target.value || undefined)} aria-label="Emitidas hasta" className="w-40" />
                    <Select value={filtros.orden ?? 'recientes'} onValueChange={(v) => cambiar('orden', v === 'recientes' ? undefined : (v as FiltrosCotizaciones['orden']))}>
                        <SelectTrigger className="w-40" aria-label="Ordenar">
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
                </BarraFiltros>

                <TablaServidor
                    pagina={registros}
                    columnas={columnas}
                    only={PROPS}
                    cargando={cargando}
                    idFila={(c) => c.id}
                    vacio={hayFiltros ? 'Ninguna cotización coincide con los filtros.' : 'Aún no hay cotizaciones.'}
                />
            </div>

            <DetalleCotizacion
                cotizacion={ver && detalle?.id === Number(ver) ? detalle : null}
                cargando={Boolean(ver) && pidiendo}
                onCerrar={cerrarDetalle}
                iva={iva}
                terminos={terminos}
                urls={urls}
            />

            <ConfirmarPeligro
                abierto={Boolean(confirmando)}
                onCerrar={() => setConfirmando(undefined)}
                titulo={confirmacion?.titulo ?? ''}
                descripcion={confirmacion?.descripcion ?? ''}
                accion={confirmacion?.accion}
                destructiva={confirmando?.accion === 'eliminar' || confirmando?.accion === 'Cancelada'}
                onConfirmar={() => confirmando && ejecutar(confirmando.accion, confirmando.cotizacion.id)}
            />
        </AppLayout>
    );
}

const limpios = (f: FiltrosCotizaciones) => Object.fromEntries(Object.entries(f).filter(([, v]) => v !== undefined && v !== ''));

function textos(accion: Accion, id: number, dias: number): { titulo: string; descripcion: string; accion: string } {
    switch (accion) {
        case 'Aprobada':
            return { titulo: `¿Aprobar la cotización #${id}?`, descripcion: 'Queda lista para convertirse en pedido mientras siga vigente.', accion: 'Aprobar' };
        case 'Pendiente':
            return { titulo: `¿Pasar la cotización #${id} a Pendiente?`, descripcion: 'Vuelve a quedar por aprobar.', accion: 'Pasar a Pendiente' };
        case 'Cancelada':
            return { titulo: `¿Cancelar la cotización #${id}?`, descripcion: 'Deja de poder convertirse en pedido. Se puede volver a Pendiente después.', accion: 'Cancelar cotización' };
        case 'reactivar':
            return { titulo: `¿Reactivar la cotización #${id}?`, descripcion: `Vuelve a Pendiente con ${dias} días de validez desde hoy y la tasa BCV del día.`, accion: 'Reactivar' };
        case 'eliminar':
            return { titulo: `¿Eliminar la cotización #${id}?`, descripcion: 'Esta acción no se puede deshacer.', accion: 'Eliminar' };
    }
}

/** Cliente del reporte PDF: buscador remoto (nombre o documento). */
function ClientePdf({ url, onCambiar }: { url: string; onCambiar: (id?: string) => void }) {
    const [elegido, setElegido] = useState<ClienteCotizacion>();
    const buscar = async (q: string) => {
        const r = await fetch(`${url}?${new URLSearchParams({ q })}`, { headers: { Accept: 'application/json' } });
        return r.ok ? ((await r.json()) as ClienteCotizacion[]) : [];
    };
    return (
        <Campo etiqueta="Cliente">
            {(control) =>
                elegido ? (
                    <div className="flex items-center justify-between gap-2 rounded-md border px-3 py-2 text-sm">
                        <span className="truncate">
                            {elegido.nombre} <span className="text-muted-foreground tabular">{elegido.documento}</span>
                        </span>
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            aria-label="Quitar el cliente"
                            onClick={() => {
                                setElegido(undefined);
                                onCambiar(undefined);
                            }}
                        >
                            <X />
                        </Button>
                    </div>
                ) : (
                    <Buscador<ClienteCotizacion>
                        id={control.id}
                        etiqueta="Buscar cliente del reporte"
                        placeholder="Todos los clientes (busca por nombre o documento)"
                        remoto
                        buscar={buscar}
                        clave={(c) => c.id}
                        opcion={(c) => (
                            <span className="flex items-baseline justify-between gap-3">
                                <span className="truncate">{c.nombre}</span>
                                <span className="text-muted-foreground shrink-0 text-xs tabular">{c.documento}</span>
                            </span>
                        )}
                        onElegir={(c) => {
                            setElegido(c);
                            onCambiar(String(c.id));
                        }}
                        vacio={() => 'Ningún cliente coincide.'}
                    />
                )
            }
        </Campo>
    );
}
