import { Link, router } from '@inertiajs/react';
import { Archive, ArrowLeft, Briefcase, CalendarDays, Eye, Mail, MapPin, MoreVertical, Pencil, Phone, Plus, RotateCcw, Search, Trash2, UserRoundCheck, X } from 'lucide-react';
import { useState } from 'react';

import { ConfirmarPeligro } from '@/components/app/confirmar-peligro';
import { Dato } from '@/components/app/dato';
import { ExportarPdf } from '@/components/app/exportar-pdf';
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
import { formatoFecha } from '@/lib/formato';

import { FormularioEmpleado } from './formulario-empleado';
import { GENERO, type EmpleadoFila, type FiltrosEmpleados, type PaginaEmpleados } from './tipos';

const TODOS = 'todos'; // los Select de Radix no admiten '' como valor
const TABLA = ['empleados', 'filtros'];
const TIPO_TEL = { movil: 'Móvil', casa: 'Casa', trabajo: 'Trabajo' } as const;

export default function EmpleadosIndex({ empleados, filtros: filtrosIniciales, departamentos, cargos, estados, urls }: PaginaEmpleados) {
    const { puede } = usePermisos();
    const gestionar = puede('empleados.gestionar');
    const historial = Boolean(filtrosIniciales.historial);
    const { filtros, cambiar, limpiar, cargando } = useFiltrosUrl<FiltrosEmpleados>(urls.index, filtrosIniciales, TABLA);

    // `apertura` cambia en cada apertura → el formulario se monta de nuevo con los datos correctos.
    const [formulario, setFormulario] = useState<{ abierto: boolean; empleado?: EmpleadoFila; apertura: number }>({ abierto: false, apertura: 0 });
    const abrirFormulario = (empleado?: EmpleadoFila) => setFormulario((f) => ({ abierto: true, empleado, apertura: f.apertura + 1 }));
    const [viendo, setViendo] = useState<EmpleadoFila>();
    // Fuera del menú: si la confirmación viviera dentro, el menú quedaría abierto al confirmar.
    const [inhabilitando, setInhabilitando] = useState<EmpleadoFila>();

    const hayFiltros = Boolean(filtros.buscar || filtros.departamento || filtros.cargo || (filtros.orden && filtros.orden !== 'recientes'));
    const cargosFiltro = filtros.departamento ? cargos.filter((c) => String(c.departamento_id) === filtros.departamento) : cargos;

    const columnas: Columna<EmpleadoFila>[] = [
        { id: 'codigo', encabezado: 'Código', celda: (e) => <span className="font-mono text-xs">{e.codigo}</span> },
        {
            id: 'nombre',
            encabezado: 'Empleado',
            celda: (e) => (
                <span className="grid">
                    <span className="font-medium">{e.nombre}</span>
                    <span className="text-muted-foreground font-mono text-xs">{e.documento}</span>
                </span>
            ),
        },
        {
            id: 'cargo',
            encabezado: 'Cargo',
            celda: (e) => (
                <span className="grid">
                    <span>{e.cargo ?? '—'}</span>
                    <span className="text-muted-foreground text-xs">{e.departamento ?? '—'}</span>
                </span>
            ),
        },
        {
            id: 'telefono',
            encabezado: 'Teléfono',
            celda: (e) => <span className="tabular">{(e.telefonos.find((t) => t.es_principal) ?? e.telefonos[0])?.numero ?? '—'}</span>,
        },
        {
            id: 'acciones',
            encabezado: <span className="sr-only">Acciones</span>,
            className: 'w-24 text-right',
            celda: (e) => (
                <div className="flex justify-end gap-1">
                    <Button variant="ghost" size="icon" onClick={() => setViendo(e)} aria-label={`Ver ${e.nombre}`}><Eye /></Button>
                    {gestionar && (
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="ghost" size="icon" aria-label={`Más acciones para ${e.nombre}`}><MoreVertical /></Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                {e.inhabilitado ? (
                                    <DropdownMenuItem onSelect={() => router.post(`${urls.index}/${e.id}/restore`, {}, { preserveScroll: true })}>
                                        <RotateCcw /> Restaurar
                                    </DropdownMenuItem>
                                ) : (
                                    <>
                                        <DropdownMenuItem onSelect={() => abrirFormulario(e)}><Pencil /> Editar</DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem onSelect={() => setInhabilitando(e)} variant="destructive"><Trash2 /> Inhabilitar</DropdownMenuItem>
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
            titulo={historial ? 'Empleados inhabilitados' : 'Empleados'}
            acciones={
                <>
                    <Button variant="ghost" asChild>
                        {historial ? <Link href={urls.index}><ArrowLeft /> Solo activos</Link> : <Link href={`${urls.index}?historial=1`}><Archive /> Inhabilitados</Link>}
                    </Button>
                    {puede('empleados.pdf') && (
                        <ExportarPdf
                            url={urls.reportePdf}
                            recurso="empleados"
                            fecha="Ingreso"
                            filtros={[
                                { parametro: 'departamento_id', etiqueta: 'Departamento', todos: 'Todos los departamentos', opciones: departamentos.map((d) => ({ valor: String(d.id), etiqueta: d.nombre })) },
                                { parametro: 'cargo_id', etiqueta: 'Cargo', todos: 'Todos los cargos', opciones: cargos.map((c) => ({ valor: String(c.id), etiqueta: c.nombre })) },
                                { parametro: 'estatus', etiqueta: 'Estatus', todos: 'Activos', opciones: [{ valor: '0', etiqueta: 'Inhabilitados' }] },
                            ]}
                        />
                    )}
                    {gestionar && !historial && (
                        <Button onClick={() => abrirFormulario()}><Plus /> Agregar empleado</Button>
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
                            placeholder="Buscar por nombre, documento, código o cargo…"
                            aria-label="Buscar empleado"
                            className="pl-8"
                        />
                    </div>
                    <Select
                        value={filtros.departamento ?? TODOS}
                        onValueChange={(v) => { cambiar('departamento', v === TODOS ? undefined : v); cambiar('cargo', undefined); }}
                    >
                        <SelectTrigger className="w-56" aria-label="Filtrar por departamento"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value={TODOS}>Todos los departamentos</SelectItem>
                            {departamentos.map((d) => <SelectItem key={d.id} value={String(d.id)}>{d.nombre}</SelectItem>)}
                        </SelectContent>
                    </Select>
                    <Select value={filtros.cargo ?? TODOS} onValueChange={(v) => cambiar('cargo', v === TODOS ? undefined : v)}>
                        <SelectTrigger className="w-44" aria-label="Filtrar por cargo"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value={TODOS}>Todos los cargos</SelectItem>
                            {cargosFiltro.map((c) => <SelectItem key={c.id} value={String(c.id)}>{c.nombre}</SelectItem>)}
                        </SelectContent>
                    </Select>
                    <Select value={filtros.orden ?? 'recientes'} onValueChange={(v) => cambiar('orden', v as FiltrosEmpleados['orden'])}>
                        <SelectTrigger className="w-52" aria-label="Ordenar"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="recientes">Más recientes primero</SelectItem>
                            <SelectItem value="codigo">Código</SelectItem>
                            <SelectItem value="nombre_asc">Nombre (A-Z)</SelectItem>
                            <SelectItem value="nombre_desc">Nombre (Z-A)</SelectItem>
                        </SelectContent>
                    </Select>
                    {hayFiltros && <Button variant="ghost" onClick={() => limpiar(['historial'])}><X /> Limpiar</Button>}
                </div>

                <TablaServidor
                    pagina={empleados}
                    columnas={columnas}
                    only={TABLA}
                    cargando={cargando}
                    idFila={(e) => e.id}
                    vacio={hayFiltros ? 'Ningún empleado coincide con los filtros.' : historial ? 'No hay empleados inhabilitados.' : 'Aún no hay empleados registrados.'}
                />
            </div>

            <ConfirmarPeligro
                abierto={Boolean(inhabilitando)}
                onCerrar={() => setInhabilitando(undefined)}
                titulo={`¿Inhabilitar a ${inhabilitando?.nombre ?? ''}?`}
                descripcion="Pasa al historial y deja de aparecer al asignar órdenes de producción. Se puede restaurar cuando quieras."
                accion="Inhabilitar"
                onConfirmar={() => inhabilitando && router.delete(`${urls.index}/${inhabilitando.id}`, { preserveScroll: true })}
            />
            <DetalleEmpleado empleado={viendo} onCerrar={() => setViendo(undefined)} />
            {gestionar && (
                <FormularioEmpleado
                    key={formulario.apertura}
                    abierto={formulario.abierto}
                    empleado={formulario.empleado}
                    departamentos={departamentos}
                    cargos={cargos}
                    estados={estados}
                    urls={urls}
                    onCerrar={() => setFormulario((f) => ({ ...f, abierto: false }))}
                />
            )}
        </AppLayout>
    );
}

function DetalleEmpleado({ empleado: e, onCerrar }: { empleado?: EmpleadoFila; onCerrar: () => void }) {
    return (
        <Dialog open={Boolean(e)} onOpenChange={(a) => !a && onCerrar()}>
            <DialogContent className="sm:max-w-lg">
                {e && (
                    <>
                        <DialogHeader>
                            <DialogTitle>{e.nombre}</DialogTitle>
                            <DialogDescription className="font-mono">
                                {e.codigo} · {e.documento}
                                {e.inhabilitado && ' · Inhabilitado'}
                            </DialogDescription>
                        </DialogHeader>
                        <dl className="grid gap-4 sm:grid-cols-2">
                            <Dato icono={<Briefcase />} etiqueta="Cargo">
                                {e.cargo ?? '—'}
                                <span className="text-muted-foreground block text-xs">{e.departamento}</span>
                            </Dato>
                            <Dato icono={<CalendarDays />} etiqueta="Ingreso">{e.fecha_ingreso ? formatoFecha(e.fecha_ingreso) : '—'}</Dato>
                            <Dato icono={<Mail />} etiqueta="Correo">{e.email ?? '—'}</Dato>
                            <Dato icono={<CalendarDays />} etiqueta="Nacimiento">
                                {e.fecha_nacimiento ? formatoFecha(e.fecha_nacimiento) : '—'}
                                {e.genero && <span className="text-muted-foreground block text-xs">{GENERO[e.genero]}</span>}
                            </Dato>
                            <Dato icono={<Phone />} etiqueta="Teléfonos">
                                {e.telefonos.length
                                    ? e.telefonos.map((t) => (
                                          <span key={t.numero} className="block tabular">
                                              {t.numero} <span className="text-muted-foreground text-xs">({TIPO_TEL[t.tipo]}{t.es_principal ? ', principal' : ''})</span>
                                          </span>
                                      ))
                                    : '—'}
                            </Dato>
                            <Dato icono={<MapPin />} etiqueta="Dirección">
                                {e.direccion ?? '—'}
                                {e.estado_territorial && <span className="text-muted-foreground block text-xs">{[e.ciudad, e.estado_territorial].filter(Boolean).join(', ')}</span>}
                            </Dato>
                            {e.otros_roles.length > 0 && (
                                <Dato icono={<UserRoundCheck />} etiqueta="También registrado como"><span className="capitalize">{e.otros_roles.join(', ')}</span></Dato>
                            )}
                        </dl>
                    </>
                )}
            </DialogContent>
        </Dialog>
    );
}
