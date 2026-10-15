import { Link, router } from '@inertiajs/react';
import { Ban, BellRing, CheckCheck, Copy, EllipsisVertical, Eye, FileText, Pencil, Plus, Search, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';

import { BarraFiltros } from '@/components/app/barra-filtros';
import { ConfirmarPeligro } from '@/components/app/confirmar-peligro';
import { EstadoBadge } from '@/components/app/estado-badge';
import { ExportarPdf } from '@/components/app/exportar-pdf';
import { columnasExistencias } from '@/components/app/tabla-existencias';
import { TablaServidor, type Columna } from '@/components/app/tabla-servidor';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useFiltrosUrl } from '@/hooks/use-filtros-url';
import { usePermisos } from '@/hooks/use-permisos';
import AppLayout from '@/layouts/app-layout';
import { formatoBs, formatoFecha, formatoUsd } from '@/lib/formato';
import { avisarCambioStock } from '@/lib/inventario';

import { accionesDe } from './acciones';
import { DetalleCompra } from './detalle-compra';
import type { CompraFila, FiltrosCompras, PaginaCompras, VistaCompras } from './tipos';

const TODOS = 'todos';
const PROPS = ['compras', 'existencias', 'filtros', 'vista'];

type Accion = 'procesar' | 'anular' | 'eliminar';

export default function ComprasIndex({ vista, filtros: iniciales, compras, existencias, detalle, proveedores, tiposInsumo, urls }: PaginaCompras) {
    const { puede } = usePermisos();
    const { ver: verInicial, ...filtrosIniciales } = iniciales;
    const { filtros, cambiar, limpiar, cargando } = useFiltrosUrl<FiltrosCompras>(urls.index, filtrosIniciales, PROPS);
    const [ver, setVer] = useState<string | undefined>(verInicial);
    const [confirmando, setConfirmando] = useState<{ accion: Accion; compra: { id: number } }>();
    // Pestaña elegida al instante (la de la URL llega con la respuesta); «activas» no va en la URL.
    const vistaActual: VistaCompras = 'vista' in filtros ? (filtros.vista ?? 'activas') : vista;
    const hayFiltros = Object.entries(filtros).some(([k, v]) => k !== 'vista' && v);
    const nombreProveedor = (id?: string) => proveedores.find((p) => String(p.id) === id)?.nombre;

    // Mientras se pide la ficha se muestra «cargando»; si al volver no llegó,
    // la compra no existe (enlace viejo o ID a mano) y se cierra con aviso.
    const [pidiendo, setPidiendo] = useState(false);
    const abrirDetalle = (id: number) => {
        setVer(String(id));
        setPidiendo(true);
        router.get(urls.index, { ...filtros, ver: id }, { only: ['detalle'], preserveState: true, preserveScroll: true, replace: true, onFinish: (v) => {
            // Una visita interrumpida (se abrió otra ficha) no cuenta como «llegó sin ficha».
            if (!v.interrupted && !v.cancelled) setPidiendo(false);
        } });
    };
    const noExiste = Boolean(ver) && !pidiendo && detalle?.id !== Number(ver);
    useEffect(() => {
        if (!noExiste) return;
        toast.error(`La compra #${ver} no existe o fue eliminada.`);
        cerrarDetalle();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [noExiste]);
    const cerrarDetalle = () => {
        setVer(undefined);
        router.get(urls.index, { ...filtros }, { only: ['detalle'], preserveState: true, preserveScroll: true, replace: true });
    };

    const ejecutar = (accion: Accion | 'clonar', id: number) => {
        const url = `${urls.index}/${id}`;
        const opciones = { preserveScroll: true };
        // Un rechazo del servidor también vuelve con éxito (redirect con flash de error): solo se avisa si cambió el stock.
        const siCambio = (motivo: 'compra-procesada' | 'compra-anulada') => (p: { props: { flash?: unknown } }) => {
            if (!(p.props.flash as { error?: string | null } | undefined)?.error) avisarCambioStock(motivo);
        };
        if (accion === 'procesar') router.patch(`${url}/procesar`, {}, { ...opciones, onSuccess: siCambio('compra-procesada') });
        if (accion === 'anular') router.patch(`${url}/anular`, {}, { ...opciones, onSuccess: siCambio('compra-anulada') });
        if (accion === 'clonar') router.post(`${url}/clonar`, {}, opciones);
        if (accion === 'eliminar') {
            if (String(id) === ver) setVer(undefined);
            router.delete(url, opciones);
        }
    };

    const columnas: Columna<CompraFila>[] = [
        { id: 'numero', encabezado: 'Nro.', celda: (c) => <span className="text-muted-foreground tabular">#{c.id}</span> },
        {
            id: 'proveedor',
            encabezado: 'Proveedor',
            celda: (c) => (
                <>
                    <span className="font-medium">{c.proveedor ?? '—'}</span>
                    {c.proveedor_doc && <span className="text-muted-foreground block text-xs tabular">{c.proveedor_doc}</span>}
                </>
            ),
        },
        { id: 'factura', encabezado: 'Factura', celda: (c) => <span className="tabular">{c.numero_factura ?? <span className="text-muted-foreground">S/N</span>}</span> },
        { id: 'fecha', encabezado: 'Fecha', celda: (c) => <span className="tabular whitespace-nowrap">{c.fecha ? formatoFecha(c.fecha) : '—'}</span> },
        {
            id: 'total',
            encabezado: 'Total',
            className: 'text-right',
            celda: (c) => (
                <span className="inline-flex flex-col items-end">
                    <span className="tabular font-semibold">{formatoUsd(c.total)}</span>
                    <span className="text-muted-foreground tabular text-xs">{formatoBs(c.total_bs)}</span>
                </span>
            ),
        },
        {
            id: 'estado',
            encabezado: 'Estado',
            celda: (c) => (
                <>
                    <EstadoBadge estado={c.estado} className="capitalize" />
                    {c.estado === 'anulada' && c.anulado_por && (
                        <span className="text-muted-foreground mt-1 block text-xs">
                            por {c.anulado_por}
                            {c.fecha_anulacion && ` · ${formatoFecha(c.fecha_anulacion)}`}
                        </span>
                    )}
                </>
            ),
        },
        {
            id: 'acciones',
            encabezado: <span className="sr-only">Acciones</span>,
            className: 'text-right',
            celda: (c) => {
                const a = accionesDe(c, puede);
                const hayMenu = a.pdf || a.editar || a.procesar || a.eliminar || a.anular || a.clonar || c.clonada;
                return (
                    <div className="flex justify-end gap-1">
                        <Button variant="ghost" size="icon" onClick={() => abrirDetalle(c.id)} aria-label={`Ver compra #${c.id}`}>
                            <Eye />
                        </Button>
                        {hayMenu && (
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button variant="ghost" size="icon" aria-label={`Más acciones de la compra #${c.id}`}>
                                        <EllipsisVertical />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    {a.pdf && (
                                        <DropdownMenuItem tono="documento" asChild>
                                            <a href={`${urls.index}/${c.id}/pdf`} target="_blank" rel="noopener"><FileText /> Ver PDF</a>
                                        </DropdownMenuItem>
                                    )}
                                    {a.editar && (
                                        <DropdownMenuItem tono="editar" asChild>
                                            <Link href={`${urls.index}/${c.id}/editar`}><Pencil /> Editar</Link>
                                        </DropdownMenuItem>
                                    )}
                                    {a.procesar && (
                                        <DropdownMenuItem tono="principal" onSelect={() => setConfirmando({ accion: 'procesar', compra: c })}>
                                            <CheckCheck /> Procesar
                                        </DropdownMenuItem>
                                    )}
                                    {a.clonar && (
                                        <DropdownMenuItem tono="principal" onSelect={() => ejecutar('clonar', c.id)}>
                                            <Copy /> Clonar como borrador
                                        </DropdownMenuItem>
                                    )}
                                    {c.estado === 'anulada' && c.clonada && (
                                        <DropdownMenuItem tono="principal" disabled>
                                            <Copy /> Ya fue clonada
                                        </DropdownMenuItem>
                                    )}
                                    {(a.eliminar || a.anular) && <DropdownMenuSeparator />}
                                    {a.eliminar && (
                                        <DropdownMenuItem tono="peligro" onSelect={() => setConfirmando({ accion: 'eliminar', compra: c })}>
                                            <Trash2 /> Eliminar borrador
                                        </DropdownMenuItem>
                                    )}
                                    {a.anular && (
                                        <DropdownMenuItem tono="aviso" onSelect={() => setConfirmando({ accion: 'anular', compra: c })}>
                                            <Ban /> Anular
                                        </DropdownMenuItem>
                                    )}
                                </DropdownMenuContent>
                            </DropdownMenu>
                        )}
                    </div>
                );
            },
        },
    ];

    const confirmacion = confirmando && TEXTOS[confirmando.accion](confirmando.compra.id);

    return (
        <AppLayout
            titulo="Compras"
            acciones={
                <>
                    {puede('compras.pdf') && (
                        <ExportarPdf
                            url={urls.reportePdf}
                            recurso="compras"
                            fecha="Compra"
                            filtros={[
                                { parametro: 'proveedor_id', etiqueta: 'Proveedor', todos: 'Todos los proveedores', opciones: proveedores.map((p) => ({ valor: String(p.id), etiqueta: p.nombre })) },
                                { parametro: 'estado', etiqueta: 'Estado', todos: 'Todos', opciones: [{ valor: 'recibida', etiqueta: 'Recibida' }, { valor: 'borrador', etiqueta: 'Borrador' }, { valor: 'anulada', etiqueta: 'Anulada' }] },
                                { parametro: 'orden', etiqueta: 'Ordenar por', todos: 'Fecha reciente', opciones: [{ valor: 'monto_desc', etiqueta: 'Mayor monto' }, { valor: 'monto_asc', etiqueta: 'Menor monto' }] },
                            ]}
                        />
                    )}
                    {puede('compras.gestionar') && (
                        <Button asChild>
                            <Link href={urls.crear}><Plus /> Nueva compra</Link>
                        </Button>
                    )}
                </>
            }
        >
            <div className="grid gap-4">
                <Tabs value={vistaActual} onValueChange={(v) => { limpiar(); cambiar('vista', v === 'activas' ? undefined : (v as VistaCompras)); }}>
                    <TabsList>
                        <TabsTrigger value="activas">Compras</TabsTrigger>
                        <TabsTrigger value="anuladas">Anuladas</TabsTrigger>
                        <TabsTrigger value="existencias">Existencias</TabsTrigger>
                    </TabsList>
                </Tabs>

                <BarraFiltros>
                    <div className="relative min-w-56 flex-1">
                        <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2" />
                        <Input
                            type="search"
                            value={filtros.buscar ?? ''}
                            onChange={(e) => cambiar('buscar', e.target.value)}
                            placeholder={vistaActual === 'existencias' ? 'Buscar insumo…' : 'Buscar por proveedor, documento o factura…'}
                            aria-label="Buscar"
                            className="pl-8"
                        />
                    </div>
                    {vistaActual === 'existencias' ? (
                        <>
                            <Select value={filtros.tipo_insumo ?? TODOS} onValueChange={(v) => cambiar('tipo_insumo', v === TODOS ? undefined : v)}>
                                <SelectTrigger className="w-44" aria-label="Filtrar por tipo de insumo"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={TODOS}>Todos los tipos</SelectItem>
                                    {tiposInsumo.map((t) => <SelectItem key={t} value={t}>{t}</SelectItem>)}
                                </SelectContent>
                            </Select>
                            <Button variant={filtros.alerta ? 'secondary' : 'outline'} aria-pressed={Boolean(filtros.alerta)} onClick={() => cambiar('alerta', filtros.alerta ? undefined : '1')}>
                                <BellRing /> Solo en o bajo el mínimo
                            </Button>
                        </>
                    ) : (
                        <>
                            {vistaActual === 'activas' && (
                                <Select value={filtros.estado ?? TODOS} onValueChange={(v) => cambiar('estado', v === TODOS ? undefined : (v as FiltrosCompras['estado']))}>
                                    <SelectTrigger className="w-44" aria-label="Filtrar por estado"><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value={TODOS}>Todas las activas</SelectItem>
                                        <SelectItem value="borrador">Borradores</SelectItem>
                                        <SelectItem value="recibida">Recibidas</SelectItem>
                                    </SelectContent>
                                </Select>
                            )}
                            <Select value={filtros.proveedor ?? TODOS} onValueChange={(v) => cambiar('proveedor', v === TODOS ? undefined : v)}>
                                <SelectTrigger className="w-52" aria-label="Filtrar por proveedor"><SelectValue>{nombreProveedor(filtros.proveedor) ?? 'Todos los proveedores'}</SelectValue></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={TODOS}>Todos los proveedores</SelectItem>
                                    {proveedores.map((p) => <SelectItem key={p.id} value={String(p.id)}>{p.nombre}</SelectItem>)}
                                </SelectContent>
                            </Select>
                            <Input type="date" value={filtros.desde ?? ''} onChange={(e) => cambiar('desde', e.target.value || undefined)} aria-label="Desde" className="w-40" />
                            <Input type="date" value={filtros.hasta ?? ''} onChange={(e) => cambiar('hasta', e.target.value || undefined)} aria-label="Hasta" className="w-40" />
                        </>
                    )}
                    {hayFiltros && <Button variant="ghost" onClick={() => limpiar(['vista'])}>Limpiar</Button>}
                </BarraFiltros>

                {vistaActual === 'existencias' && (
                    <p className="text-muted-foreground -mt-1 text-sm">El costo es el de la última compra procesada de cada insumo.</p>
                )}

                {vista !== 'existencias' && compras && (
                    <TablaServidor
                        pagina={compras}
                        columnas={columnas}
                        only={PROPS}
                        cargando={cargando}
                        idFila={(c) => c.id}
                        vacio={hayFiltros ? 'Ninguna compra coincide con los filtros.' : vista === 'anuladas' ? 'No hay compras anuladas.' : 'Aún no hay compras registradas.'}
                    />
                )}
                {vista === 'existencias' && existencias && (
                    <TablaServidor pagina={existencias} columnas={columnasExistencias({ costo: 'Precio de entrada' })} only={PROPS} cargando={cargando} idFila={(i) => i.id} vacio="Ningún insumo inventariable coincide." />
                )}
            </div>

            <DetalleCompra
                compra={ver && detalle?.id === Number(ver) ? detalle : null}
                cargando={Boolean(ver) && pidiendo}
                onCerrar={cerrarDetalle}
                urls={urls}
                onAccion={(accion, c) => (accion === 'clonar' ? ejecutar('clonar', c.id) : setConfirmando({ accion, compra: c }))}
            />

            <ConfirmarPeligro
                abierto={Boolean(confirmando)}
                onCerrar={() => setConfirmando(undefined)}
                titulo={confirmacion?.titulo ?? ''}
                descripcion={confirmacion?.descripcion ?? ''}
                accion={confirmacion?.accion}
                destructiva={confirmando?.accion !== 'procesar'}
                onConfirmar={() => confirmando && ejecutar(confirmando.accion, confirmando.compra.id)}
            />
        </AppLayout>
    );
}

const TEXTOS: Record<Accion, (id: number) => { titulo: string; descripcion: string; accion: string }> = {
    procesar: (id) => ({
        titulo: `¿Procesar la compra #${id}?`,
        descripcion: 'Las cantidades se suman a la existencia de cada insumo y su costo se actualiza. Después ya no se puede editar; solo anular.',
        accion: 'Procesar',
    }),
    anular: (id) => ({
        titulo: `¿Anular la compra #${id}?`,
        descripcion: 'Se descuenta de la existencia lo que entró con esta compra. Si parte ya se consumió, no se puede anular. Luego podrás clonarla como un borrador nuevo.',
        accion: 'Anular',
    }),
    eliminar: (id) => ({
        titulo: `¿Eliminar el borrador #${id}?`,
        descripcion: 'Se borra definitivamente. Un borrador nunca movió el inventario.',
        accion: 'Eliminar',
    }),
};
