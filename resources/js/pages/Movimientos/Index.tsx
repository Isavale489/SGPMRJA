import { Link, useForm } from '@inertiajs/react';
import { ArrowDownRight, ArrowUpRight, BellRing, Eye, History, Minus, Search, TrendingUp } from 'lucide-react';
import { useState } from 'react';

import { Campo } from '@/components/app/campo';
import { DialogoFormulario } from '@/components/app/dialogo-formulario';
import { ExportarPdf } from '@/components/app/exportar-pdf';
import { Asistente } from '@/components/app/asistente';
import { columnasExistencias, type ExistenciaFila } from '@/components/app/tabla-existencias';
import { TablaServidor, type Columna } from '@/components/app/tabla-servidor';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import { useFiltrosUrl } from '@/hooks/use-filtros-url';
import { usePermisos } from '@/hooks/use-permisos';
import AppLayout from '@/layouts/app-layout';
import { formatoFecha, formatoNumero } from '@/lib/formato';
import { cn } from '@/lib/utils';
import type { Paginado } from '@/types';

/** Espejo de MovimientoInsumoController::movimientos() (lo verifica MovimientosPaginaTest). */
export interface MovimientoFila {
    id: number;
    tipo: 'Entrada' | 'Salida';
    insumo_id: number;
    insumo: string | null;
    codigo: string | null;
    unidad: string | null;
    cantidad: number;
    stock_anterior: number;
    stock_nuevo: number;
    motivo: string | null;
    usuario: string | null;
    fecha: string | null;
}

type Filtros = {
    vista?: 'movimientos' | 'existencias';
    buscar?: string;
    tipo?: 'Entrada' | 'Salida';
    insumo?: string;
    stock?: 'critico' | 'optimo' | 'exceso';
    desde?: string;
    hasta?: string;
    tipo_insumo?: string;
    alerta?: string;
};

interface Insumo {
    id: number;
    nombre: string;
    codigo: string | null;
    unidad: string;
    inventariable: boolean;
    stock: number;
}

interface Props {
    vista: 'movimientos' | 'existencias';
    filtros: Filtros;
    movimientos: Paginado<MovimientoFila> | null;
    existencias: Paginado<ExistenciaFila> | null;
    insumos: Insumo[];
    tiposInsumo: string[];
    urls: { index: string; store: string; reportePdf: string; alertas: string; rotacion: string; historial: string };
}

const TODOS = 'todos';
const PROPS = ['movimientos', 'existencias', 'filtros', 'vista'];

export default function MovimientosIndex({ vista, filtros: iniciales, movimientos, existencias, insumos, tiposInsumo, urls }: Props) {
    const { puede } = usePermisos();
    const { filtros, cambiar, limpiar, cargando } = useFiltrosUrl<Filtros>(urls.index, iniciales, PROPS);
    const [salida, setSalida] = useState<{ abierto: boolean; apertura: number }>({ abierto: false, apertura: 0 });
    const [viendo, setViendo] = useState<MovimientoFila>();
    const hayFiltros = Object.entries(filtros).some(([k, v]) => k !== 'vista' && v);
    const inventariables = insumos.filter((i) => i.inventariable);
    const nombreInsumo = (id?: string) => insumos.find((i) => String(i.id) === id)?.nombre;

    const colMovimientos: Columna<MovimientoFila>[] = [
        { id: 'fecha', encabezado: 'Fecha', celda: (m) => <span className="tabular whitespace-nowrap">{m.fecha ? `${formatoFecha(m.fecha)} ${m.fecha.slice(11)}` : '—'}</span> },
        {
            id: 'insumo',
            encabezado: 'Insumo',
            celda: (m) => (
                <Link href={`${urls.historial}/${m.insumo_id}`} className="hover:underline">
                    <span className="font-medium">{m.insumo ?? '—'}</span>
                    {m.codigo && <code className="text-muted-foreground ml-1.5 font-mono text-xs">{m.codigo}</code>}
                </Link>
            ),
        },
        {
            id: 'tipo',
            encabezado: 'Tipo',
            celda: (m) => (
                <span className={cn('inline-flex items-center gap-1 text-sm', m.tipo === 'Entrada' ? 'text-success' : 'text-destructive')}>
                    {m.tipo === 'Entrada' ? <ArrowUpRight className="size-4" /> : <ArrowDownRight className="size-4" />} {m.tipo}
                </span>
            ),
        },
        { id: 'cantidad', encabezado: 'Cantidad', className: 'text-right', celda: (m) => <span className="tabular">{formatoNumero(m.cantidad)} <span className="text-muted-foreground text-xs">{m.unidad}</span></span> },
        { id: 'stock', encabezado: 'Existencia', className: 'text-right', celda: (m) => <span className="tabular text-muted-foreground">{formatoNumero(m.stock_anterior)} → <span className="text-foreground">{formatoNumero(m.stock_nuevo)}</span></span> },
        { id: 'motivo', encabezado: 'Motivo', celda: (m) => <span className="text-muted-foreground line-clamp-1 max-w-72">{m.motivo ?? '—'}</span> },
        {
            id: 'ver',
            encabezado: <span className="sr-only">Ver</span>,
            className: 'text-right',
            celda: (m) => (
                <Button variant="ghost" size="icon" onClick={() => setViendo(m)} aria-label={`Ver movimiento #${m.id}`}>
                    <Eye />
                </Button>
            ),
        },
    ];

    const colExistencias = columnasExistencias({ historial: urls.historial });

    return (
        <AppLayout
            titulo="Movimientos de insumos"
            acciones={
                <>
                    <Button variant="ghost" asChild><Link href={urls.alertas}><BellRing /> Alertas</Link></Button>
                    <Button variant="ghost" asChild><Link href={urls.rotacion}><TrendingUp /> Rotación</Link></Button>
                    <ExportarPdf
                        url={urls.reportePdf}
                        recurso="movimientos"
                        fecha="Movimiento"
                        filtros={[
                            { parametro: 'tipo_movimiento', etiqueta: 'Tipo', todos: 'Entradas y salidas', opciones: [{ valor: 'Entrada', etiqueta: 'Entradas' }, { valor: 'Salida', etiqueta: 'Salidas' }] },
                            { parametro: 'insumo_id', etiqueta: 'Insumo', todos: 'Todos los insumos', opciones: insumos.map((i) => ({ valor: String(i.id), etiqueta: i.nombre })) },
                            { parametro: 'estado_stock', etiqueta: 'Estado de stock', todos: 'Cualquiera', opciones: [{ valor: 'critico', etiqueta: 'Crítico' }, { valor: 'optimo', etiqueta: 'Óptimo' }, { valor: 'exceso', etiqueta: 'Exceso' }] },
                        ]}
                    />
                    {puede('movimiento-insumo.gestionar') && (
                        <Button onClick={() => setSalida((s) => ({ abierto: true, apertura: s.apertura + 1 }))}>
                            <Minus /> Registrar salida
                        </Button>
                    )}
                </>
            }
        >
            <div className="grid gap-4">
                <p className="text-muted-foreground -mt-3 text-sm">Las entradas llegan por Compras y Producción; aquí solo se registran salidas manuales.</p>
                <Tabs value={filtros.vista ?? vista} onValueChange={(v) => { limpiar(); cambiar('vista', v as Filtros['vista']); }}>
                    <TabsList>
                        <TabsTrigger value="movimientos">Movimientos</TabsTrigger>
                        <TabsTrigger value="existencias">Existencias</TabsTrigger>
                    </TabsList>
                </Tabs>

                <div className="flex flex-wrap items-center gap-2">
                    <div className="relative min-w-56 flex-1">
                        <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2" />
                        <Input type="search" value={filtros.buscar ?? ''} onChange={(e) => cambiar('buscar', e.target.value)} placeholder={vista === 'movimientos' ? 'Buscar por insumo, código o motivo…' : 'Buscar insumo…'} aria-label="Buscar" className="pl-8" />
                    </div>
                    {vista === 'movimientos' ? (
                        <>
                            <Select value={filtros.tipo ?? TODOS} onValueChange={(v) => cambiar('tipo', v === TODOS ? undefined : (v as Filtros['tipo']))}>
                                <SelectTrigger className="w-44" aria-label="Filtrar por tipo"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={TODOS}>Entradas y salidas</SelectItem>
                                    <SelectItem value="Entrada">Entradas</SelectItem>
                                    <SelectItem value="Salida">Salidas</SelectItem>
                                </SelectContent>
                            </Select>
                            <Select value={filtros.insumo ?? TODOS} onValueChange={(v) => cambiar('insumo', v === TODOS ? undefined : v)}>
                                <SelectTrigger className="w-52" aria-label="Filtrar por insumo"><SelectValue>{nombreInsumo(filtros.insumo) ?? 'Todos los insumos'}</SelectValue></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={TODOS}>Todos los insumos</SelectItem>
                                    {insumos.map((i) => <SelectItem key={i.id} value={String(i.id)}>{i.nombre}</SelectItem>)}
                                </SelectContent>
                            </Select>
                            <Input type="date" value={filtros.desde ?? ''} onChange={(e) => cambiar('desde', e.target.value || undefined)} aria-label="Desde" className="w-40" />
                            <Input type="date" value={filtros.hasta ?? ''} onChange={(e) => cambiar('hasta', e.target.value || undefined)} aria-label="Hasta" className="w-40" />
                        </>
                    ) : (
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
                    )}
                    {hayFiltros && <Button variant="ghost" onClick={() => limpiar(['vista'])}>Limpiar</Button>}
                </div>

                {vista === 'movimientos' && movimientos && (
                    <TablaServidor pagina={movimientos} columnas={colMovimientos} only={PROPS} cargando={cargando} idFila={(m) => m.id} vacio={hayFiltros ? 'Ningún movimiento coincide con los filtros.' : 'Aún no hay movimientos.'} />
                )}
                {vista === 'existencias' && existencias && (
                    <TablaServidor pagina={existencias} columnas={colExistencias} only={PROPS} cargando={cargando} idFila={(i) => i.id} vacio="Ningún insumo inventariable coincide." />
                )}
            </div>

            <Dialog open={Boolean(viendo)} onOpenChange={(a) => !a && setViendo(undefined)}>
                <DialogContent className="sm:max-w-md">
                    {viendo && (
                        <>
                            <DialogHeader>
                                <DialogTitle>{viendo.tipo} de {viendo.insumo}</DialogTitle>
                                <DialogDescription>Movimiento #{viendo.id} · {viendo.fecha ? `${formatoFecha(viendo.fecha)} ${viendo.fecha.slice(11)}` : '—'} · {viendo.usuario ?? 'Sistema'}</DialogDescription>
                            </DialogHeader>
                            <Asistente
                                key={viendo.id}
                                final={
                                    <Button variant="outline" asChild>
                                        <Link href={`${urls.historial}/${viendo.insumo_id}`}><History /> Ver historial del insumo</Link>
                                    </Button>
                                }
                                pasos={[
                                    {
                                        titulo: 'Insumo',
                                        contenido: (
                                            <dl className="grid gap-3 text-sm sm:grid-cols-2">
                                                <div><dt className="text-muted-foreground text-xs">Insumo</dt><dd className="font-medium">{viendo.insumo}{viendo.codigo && <code className="text-muted-foreground ml-1.5 font-mono text-xs">{viendo.codigo}</code>}</dd></div>
                                                <div><dt className="text-muted-foreground text-xs">Tipo de movimiento</dt><dd className={viendo.tipo === 'Entrada' ? 'text-success' : 'text-destructive'}>{viendo.tipo}</dd></div>
                                                <div className="sm:col-span-2"><dt className="text-muted-foreground text-xs">Motivo</dt><dd>{viendo.motivo ?? '—'}</dd></div>
                                            </dl>
                                        ),
                                    },
                                    {
                                        titulo: 'Stock y registro',
                                        contenido: (
                                            <div className="grid gap-4 text-sm">
                                                <dl className="grid grid-cols-3 gap-3">
                                                    <div><dt className="text-muted-foreground text-xs">Antes</dt><dd className="tabular">{formatoNumero(viendo.stock_anterior)}</dd></div>
                                                    <div><dt className="text-muted-foreground text-xs">{viendo.tipo}</dt><dd className="tabular font-medium">{formatoNumero(viendo.cantidad)} {viendo.unidad}</dd></div>
                                                    <div><dt className="text-muted-foreground text-xs">Después</dt><dd className="tabular">{formatoNumero(viendo.stock_nuevo)}</dd></div>
                                                </dl>
                                                <p className="text-muted-foreground">Registrado por {viendo.usuario ?? 'Sistema'}{viendo.fecha && ` el ${formatoFecha(viendo.fecha)} ${viendo.fecha.slice(11)}`}.</p>
                                            </div>
                                        ),
                                    },
                                ]}
                            />
                        </>
                    )}
                </DialogContent>
            </Dialog>

            {puede('movimiento-insumo.gestionar') && (
                <FormularioSalida key={salida.apertura} abierto={salida.abierto} onCerrar={() => setSalida((s) => ({ ...s, abierto: false }))} insumos={inventariables} url={urls.store} />
            )}
        </AppLayout>
    );
}

function FormularioSalida({ abierto, onCerrar, insumos, url }: { abierto: boolean; onCerrar: () => void; insumos: Insumo[]; url: string }) {
    const form = useForm({ insumo_id: '', tipo_movimiento: 'Salida', cantidad: '', motivo: '' });
    const elegido = insumos.find((i) => String(i.id) === form.data.insumo_id);

    return (
        <DialogoFormulario
            abierto={abierto}
            onCerrar={onCerrar}
            titulo="Registrar salida"
            descripcion="Consumo, merma o ajuste. Las entradas se registran en Compras."
            sucio={form.isDirty}
            procesando={form.processing}
            textoGuardar="Registrar salida"
            onGuardar={() => form.post(url, { preserveScroll: true, onSuccess: onCerrar })}
        >
            <Campo etiqueta="Insumo" requerido error={form.errors.insumo_id}>
                {(control) => (
                    <Select value={form.data.insumo_id || undefined} onValueChange={(v) => form.setData('insumo_id', v)}>
                        <SelectTrigger {...control} className="w-full"><SelectValue placeholder="Selecciona un insumo inventariable" /></SelectTrigger>
                        <SelectContent>
                            {insumos.map((i) => (
                                <SelectItem key={i.id} value={String(i.id)}>{i.nombre} · {formatoNumero(i.stock)} {i.unidad}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                )}
            </Campo>
            <Campo etiqueta="Cantidad" requerido error={form.errors.cantidad} ayuda={elegido ? `Existencia actual: ${formatoNumero(elegido.stock)} ${elegido.unidad}` : undefined}>
                <Input type="number" min={0.01} step="0.01" inputMode="decimal" max={elegido?.stock} value={form.data.cantidad} onChange={(e) => form.setData('cantidad', e.target.value)} className="tabular" />
            </Campo>
            <Campo etiqueta="Motivo" requerido error={form.errors.motivo}>
                <Textarea rows={2} maxLength={500} value={form.data.motivo} onChange={(e) => form.setData('motivo', e.target.value)} placeholder="Consumo en taller, merma, ajuste por conteo…" />
            </Campo>
        </DialogoFormulario>
    );
}
