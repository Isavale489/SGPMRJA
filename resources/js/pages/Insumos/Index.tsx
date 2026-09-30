import { Link, router } from '@inertiajs/react';
import { Archive, ArrowLeft, Barcode, Boxes, Eye, MoreVertical, Pencil, Plus, Receipt, RotateCcw, Search, Tag, X } from 'lucide-react';
import { useState } from 'react';

import { BarraFiltros } from '@/components/app/barra-filtros';
import { ConfirmarPeligro } from '@/components/app/confirmar-peligro';
import { Dato } from '@/components/app/dato';
import { ExportarPdf } from '@/components/app/exportar-pdf';
import { Monto } from '@/components/app/monto';
import { TablaServidor, type Columna } from '@/components/app/tabla-servidor';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useFiltrosUrl } from '@/hooks/use-filtros-url';
import { usePermisos } from '@/hooks/use-permisos';
import AppLayout from '@/layouts/app-layout';
import { formatoFecha, formatoNumero } from '@/lib/formato';
import { cn } from '@/lib/utils';

import { FormularioInsumo } from './formulario-insumo';
import { TiposInsumo } from './tipos-insumo';
import type { FiltrosInsumos, InsumoFila, PaginaInsumos } from './tipos';

const TODOS = 'todos'; // los Select de Radix no admiten '' como valor
const TABLA = ['insumos', 'filtros'];
const STOCK = [
    { valor: 'con_stock', etiqueta: 'Con existencia' },
    { valor: 'agotado', etiqueta: 'Agotados' },
    { valor: 'bajo', etiqueta: 'En o bajo el mínimo' },
] as const;
const NIVEL = {
    bajo: { texto: 'Bajo', clase: 'bg-destructive/10 text-destructive ring-destructive/25' },
    medio: { texto: 'Medio', clase: 'bg-warning/12 text-warning ring-warning/25' },
    normal: { texto: 'Normal', clase: 'bg-success/12 text-success ring-success/25' },
} as const;

/** Existencia con su unidad y el nivel respecto al mínimo. */
function Existencia({ insumo }: { insumo: InsumoFila }) {
    if (!insumo.is_inventoriable) return <span className="text-muted-foreground text-xs">No inventariable</span>;
    const nivel = insumo.nivel_stock ? NIVEL[insumo.nivel_stock] : null;
    return (
        <span className="inline-flex flex-wrap items-center gap-2">
            <span className="tabular font-medium">{formatoNumero(insumo.stock_actual)}</span>
            <span className="text-muted-foreground text-xs">{insumo.unidad_medida}</span>
            {nivel && <span className={cn('rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset', nivel.clase)}>{nivel.texto}</span>}
        </span>
    );
}

export default function InsumosIndex({ insumos, filtros: filtrosIniciales, tiposInsumo, unidades, urls }: PaginaInsumos) {
    const { puede } = usePermisos();
    const gestionar = puede('insumos.gestionar');
    const historial = Boolean(filtrosIniciales.historial);
    const { filtros, cambiar, limpiar, cargando } = useFiltrosUrl<FiltrosInsumos>(urls.index, filtrosIniciales, TABLA);
    const tiposActivos = tiposInsumo.filter((t) => !t.inhabilitado && t.activo).map((t) => t.nombre);

    const [formulario, setFormulario] = useState<{ abierto: boolean; insumo?: InsumoFila; apertura: number }>({ abierto: false, apertura: 0 });
    const abrirFormulario = (insumo?: InsumoFila) => setFormulario((f) => ({ abierto: true, insumo, apertura: f.apertura + 1 }));
    const [viendo, setViendo] = useState<InsumoFila>();
    // Fuera del menú: si la confirmación viviera dentro, el menú quedaría abierto al confirmar.
    const [inhabilitando, setInhabilitando] = useState<InsumoFila>();

    const hayFiltros = Boolean(filtros.buscar || filtros.tipo || filtros.stock || (filtros.orden && filtros.orden !== 'recientes'));

    const columnas: Columna<InsumoFila>[] = [
        { id: 'codigo', encabezado: 'Código', celda: (i) => <span className="font-mono text-xs">{i.codigo ?? '—'}</span> },
        {
            id: 'nombre',
            encabezado: 'Insumo',
            celda: (i) => (
                <span className="grid">
                    <span className="font-medium">{i.nombre}</span>
                    <span className="text-muted-foreground text-xs">{i.tipo}</span>
                </span>
            ),
        },
        { id: 'existencia', encabezado: 'Existencia', celda: (i) => <Existencia insumo={i} /> },
        { id: 'costo', encabezado: 'Costo unitario', className: 'text-right', celda: (i) => <Monto usd={i.costo_unitario} className="items-end" /> },
        {
            id: 'acciones',
            encabezado: <span className="sr-only">Acciones</span>,
            className: 'w-24 text-right',
            celda: (i) => (
                <div className="flex justify-end gap-1">
                    <Button variant="ghost" size="icon" onClick={() => setViendo(i)} aria-label={`Ver ${i.nombre}`}><Eye /></Button>
                    {gestionar && (
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="ghost" size="icon" aria-label={`Más acciones para ${i.nombre}`}><MoreVertical /></Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                {i.inhabilitado ? (
                                    <DropdownMenuItem tono="restaurar" onSelect={() => router.post(`${urls.index}/${i.id}/restore`, {}, { preserveScroll: true })}>
                                        <RotateCcw /> Habilitar
                                    </DropdownMenuItem>
                                ) : (
                                    <>
                                        <DropdownMenuItem tono="editar" onSelect={() => abrirFormulario(i)}><Pencil /> Editar</DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem tono="aviso" onSelect={() => setInhabilitando(i)}><Archive /> Inhabilitar</DropdownMenuItem>
                                    </>
                                )}
                            </DropdownMenuContent>
                        </DropdownMenu>
                    )}
                </div>
            ),
        },
    ];

    return (
        <AppLayout
            titulo={historial ? 'Insumos inhabilitados' : 'Insumos'}
            acciones={
                <>
                    <Button variant="ghost" asChild>
                        {historial ? <Link href={urls.index}><ArrowLeft /> Solo activos</Link> : <Link href={`${urls.index}?historial=1`}><Archive /> Inhabilitados</Link>}
                    </Button>
                    {puede('tipo-insumos.ver') && <TiposInsumo tipos={tiposInsumo} url={urls.tipos} />}
                    <ExportarPdf
                        url={urls.reportePdf}
                        recurso="insumos"
                        filtros={[
                            { parametro: 'tipo', etiqueta: 'Tipo', todos: 'Todos los tipos', opciones: tiposActivos.map((t) => ({ valor: t, etiqueta: t })) },
                            { parametro: 'stock', etiqueta: 'Existencia', todos: 'Todas', opciones: [{ valor: 'con_stock', etiqueta: 'Con existencia' }, { valor: 'agotado', etiqueta: 'Agotados' }] },
                        ]}
                    />
                    {gestionar && !historial && (
                        <Button onClick={() => abrirFormulario()}><Plus /> Agregar insumo</Button>
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
                            placeholder="Buscar por nombre, código o tipo…"
                            aria-label="Buscar insumo"
                            className="pl-8"
                        />
                    </div>
                    <Select value={filtros.tipo ?? TODOS} onValueChange={(v) => cambiar('tipo', v === TODOS ? undefined : v)}>
                        <SelectTrigger className="w-44" aria-label="Filtrar por tipo"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value={TODOS}>Todos los tipos</SelectItem>
                            {tiposActivos.map((t) => (
                                <SelectItem key={t} value={t}>{t}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select value={filtros.stock ?? TODOS} onValueChange={(v) => cambiar('stock', v === TODOS ? undefined : (v as FiltrosInsumos['stock']))}>
                        <SelectTrigger className="w-48" aria-label="Filtrar por existencia"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value={TODOS}>Toda existencia</SelectItem>
                            {STOCK.map((s) => (
                                <SelectItem key={s.valor} value={s.valor}>{s.etiqueta}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select value={filtros.orden ?? 'recientes'} onValueChange={(v) => cambiar('orden', v as FiltrosInsumos['orden'])}>
                        <SelectTrigger className="w-52" aria-label="Ordenar"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="recientes">Más recientes primero</SelectItem>
                            <SelectItem value="nombre">Nombre (A-Z)</SelectItem>
                            <SelectItem value="mayor_costo">Mayor costo</SelectItem>
                            <SelectItem value="menor_costo">Menor costo</SelectItem>
                            <SelectItem value="mayor_stock">Mayor existencia</SelectItem>
                            <SelectItem value="menor_stock">Menor existencia</SelectItem>
                        </SelectContent>
                    </Select>
                    {hayFiltros && (
                        <Button variant="ghost" onClick={() => limpiar(['historial'])}><X /> Limpiar</Button>
                    )}
                </BarraFiltros>

                <TablaServidor
                    pagina={insumos}
                    columnas={columnas}
                    only={TABLA}
                    cargando={cargando}
                    idFila={(i) => i.id}
                    vacio={hayFiltros ? 'Ningún insumo coincide con los filtros.' : historial ? 'No hay insumos inhabilitados.' : 'Aún no hay insumos registrados.'}
                />
            </div>

            <ConfirmarPeligro
                abierto={Boolean(inhabilitando)}
                onCerrar={() => setInhabilitando(undefined)}
                titulo={`¿Inhabilitar «${inhabilitando?.nombre ?? ''}»?`}
                descripcion="Pasa al historial y deja de aparecer en compras, órdenes y movimientos. Se puede habilitar cuando quieras."
                accion="Inhabilitar"
                onConfirmar={() => inhabilitando && router.delete(`${urls.index}/${inhabilitando.id}`, { preserveScroll: true })}
            />
            <DetalleInsumo insumo={viendo} onCerrar={() => setViendo(undefined)} />
            {gestionar && (
                <FormularioInsumo
                    key={formulario.apertura}
                    abierto={formulario.abierto}
                    insumo={formulario.insumo}
                    tipos={tiposActivos}
                    unidades={unidades}
                    urls={urls}
                    onCerrar={() => setFormulario((f) => ({ ...f, abierto: false }))}
                />
            )}
        </AppLayout>
    );
}

function DetalleInsumo({ insumo, onCerrar }: { insumo?: InsumoFila; onCerrar: () => void }) {
    return (
        <Dialog open={Boolean(insumo)} onOpenChange={(a) => !a && onCerrar()}>
            <DialogContent className="sm:max-w-lg">
                {insumo && (
                    <>
                        <DialogHeader>
                            <DialogTitle>{insumo.nombre}</DialogTitle>
                            <DialogDescription>
                                {insumo.tipo} · {insumo.unidad_medida}
                                {insumo.inhabilitado && ' · Inhabilitado'}
                            </DialogDescription>
                        </DialogHeader>
                        <dl className="grid gap-4 sm:grid-cols-2">
                            <Dato icono={<Barcode />} etiqueta="Código"><span className="font-mono">{insumo.codigo ?? '—'}</span></Dato>
                            <Dato icono={<Receipt />} etiqueta="Costo unitario">
                                <Monto usd={insumo.costo_unitario} />
                                <span className="text-muted-foreground block text-xs">{insumo.aplica_iva ? 'Gravable con IVA' : 'Exento de IVA'}</span>
                            </Dato>
                            <Dato icono={<Boxes />} etiqueta="Existencia"><Existencia insumo={insumo} /></Dato>
                            {insumo.is_inventoriable && (
                                <Dato icono={<Tag />} etiqueta="Mínima / máxima">
                                    <span className="tabular">{formatoNumero(insumo.stock_minimo)} / {formatoNumero(insumo.stock_maximo)} {insumo.unidad_medida}</span>
                                </Dato>
                            )}
                            {insumo.creado && <Dato icono={<Archive />} etiqueta="Registrado">{formatoFecha(insumo.creado)}</Dato>}
                        </dl>
                    </>
                )}
            </DialogContent>
        </Dialog>
    );
}
