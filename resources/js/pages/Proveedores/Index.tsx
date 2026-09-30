import { Link, router } from '@inertiajs/react';
import { Archive, ArrowLeft, Eye, MoreVertical, Pencil, Plus, RotateCcw, Search, X } from 'lucide-react';
import { useState } from 'react';

import { BarraFiltros } from '@/components/app/barra-filtros';
import { ConfirmarPeligro } from '@/components/app/confirmar-peligro';
import { ExportarPdf } from '@/components/app/exportar-pdf';
import { TablaServidor, type Columna } from '@/components/app/tabla-servidor';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useFiltrosUrl } from '@/hooks/use-filtros-url';
import { usePermisos } from '@/hooks/use-permisos';
import AppLayout from '@/layouts/app-layout';

import { DetalleProveedor } from './detalle-proveedor';
import { FormularioProveedor } from './formulario-proveedor';
import type { FiltrosProveedores, PaginaProveedores, ProveedorFila } from './tipos';

const TODOS = 'todos'; // los Select de Radix no admiten '' como valor
const TABLA = ['proveedores', 'filtros'];

export default function ProveedoresIndex({ proveedores, filtros: filtrosIniciales, estados, urls }: PaginaProveedores) {
    const { puede } = usePermisos();
    const gestionar = puede('proveedores.gestionar');
    const historial = Boolean(filtrosIniciales.historial);
    const { filtros, cambiar, limpiar, cargando } = useFiltrosUrl<FiltrosProveedores>(urls.index, filtrosIniciales, TABLA);

    // `apertura` cambia en cada apertura → el formulario se monta de nuevo con los datos correctos.
    const [formulario, setFormulario] = useState<{ abierto: boolean; proveedor?: ProveedorFila; apertura: number }>({ abierto: false, apertura: 0 });
    const abrirFormulario = (proveedor?: ProveedorFila) => setFormulario((f) => ({ abierto: true, proveedor, apertura: f.apertura + 1 }));
    const [viendo, setViendo] = useState<ProveedorFila>();
    // Fuera del menú: si la confirmación viviera dentro, el menú quedaría abierto al confirmar.
    const [inhabilitando, setInhabilitando] = useState<ProveedorFila>();

    const hayFiltros = Boolean(filtros.buscar || filtros.tipo || filtros.estado || (filtros.orden && filtros.orden !== 'recientes'));
    const inhabilitar = (p: ProveedorFila) => router.delete(`${urls.index}/${p.id}`, { preserveScroll: true });
    const restaurar = (p: ProveedorFila) => router.post(`${urls.index}/${p.id}/restore`, {}, { preserveScroll: true });

    const columnas: Columna<ProveedorFila>[] = [
        { id: 'documento', encabezado: 'Documento', celda: (p) => <span className="font-mono text-xs">{p.documento}</span> },
        {
            id: 'nombre',
            encabezado: 'Nombre / Razón social',
            celda: (p) => (
                <span className="grid">
                    <span className="font-medium">{p.nombre}</span>
                    <span className="text-muted-foreground text-xs">{p.tipo === 'natural' ? 'Natural' : 'Jurídico'}</span>
                </span>
            ),
        },
        {
            id: 'telefono',
            encabezado: 'Teléfono',
            celda: (p) => <span className="tabular">{(p.telefonos.find((t) => t.es_principal) ?? p.telefonos[0])?.numero ?? '—'}</span>,
        },
        { id: 'email', encabezado: 'Correo', celda: (p) => <span className="text-muted-foreground">{p.email ?? '—'}</span> },
        {
            id: 'acciones',
            encabezado: <span className="sr-only">Acciones</span>,
            className: 'w-24 text-right',
            celda: (p) => (
                <div className="flex justify-end gap-1">
                    <Button variant="ghost" size="icon" onClick={() => setViendo(p)} aria-label={`Ver ${p.nombre}`}>
                        <Eye />
                    </Button>
                    {gestionar && (
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="ghost" size="icon" aria-label={`Más acciones para ${p.nombre}`}>
                                    <MoreVertical />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                {p.inhabilitado ? (
                                    <DropdownMenuItem tono="restaurar" onSelect={() => restaurar(p)}>
                                        <RotateCcw /> Restaurar
                                    </DropdownMenuItem>
                                ) : (
                                    <>
                                        <DropdownMenuItem tono="editar" onSelect={() => abrirFormulario(p)}>
                                            <Pencil /> Editar
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem tono="aviso" onSelect={() => setInhabilitando(p)}>
                                            <Archive /> Inhabilitar
                                        </DropdownMenuItem>
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
            titulo={historial ? 'Proveedores inhabilitados' : 'Proveedores'}
            acciones={
                <>
                    {historial ? (
                        <Button variant="ghost" asChild>
                            <Link href={urls.index}><ArrowLeft /> Solo activos</Link>
                        </Button>
                    ) : (
                        <Button variant="ghost" asChild>
                            <Link href={`${urls.index}?historial=1`}><Archive /> Inhabilitados</Link>
                        </Button>
                    )}
                    <ExportarPdf
                        url={urls.reportePdf}
                        recurso="proveedores"
                        filtros={[
                            { parametro: 'tipo_proveedor', etiqueta: 'Tipo', todos: 'Todos los tipos', opciones: [{ valor: 'natural', etiqueta: 'Natural' }, { valor: 'juridico', etiqueta: 'Jurídico' }] },
                            { parametro: 'estatus', etiqueta: 'Estatus', todos: 'Activos', opciones: [{ valor: '0', etiqueta: 'Inhabilitados' }] },
                        ]}
                    />
                    {gestionar && !historial && (
                        <Button onClick={() => abrirFormulario()}>
                            <Plus /> Agregar proveedor
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
                            placeholder="Buscar por nombre, documento o correo…"
                            aria-label="Buscar proveedor"
                            className="pl-8"
                        />
                    </div>
                    <Select value={filtros.tipo ?? TODOS} onValueChange={(v) => cambiar('tipo', v === TODOS ? undefined : (v as FiltrosProveedores['tipo']))}>
                        <SelectTrigger className="w-40" aria-label="Filtrar por tipo"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value={TODOS}>Todos los tipos</SelectItem>
                            <SelectItem value="natural">Natural</SelectItem>
                            <SelectItem value="juridico">Jurídico</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select value={filtros.estado ?? TODOS} onValueChange={(v) => cambiar('estado', v === TODOS ? undefined : v)}>
                        <SelectTrigger className="w-44" aria-label="Filtrar por estado"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value={TODOS}>Todos los estados</SelectItem>
                            {Object.keys(estados).map((e) => (
                                <SelectItem key={e} value={e}>{e}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select value={filtros.orden ?? 'recientes'} onValueChange={(v) => cambiar('orden', v as FiltrosProveedores['orden'])}>
                        <SelectTrigger className="w-52" aria-label="Ordenar"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="recientes">Más recientes primero</SelectItem>
                            <SelectItem value="antiguos">Más antiguos primero</SelectItem>
                            <SelectItem value="nombre_asc">Nombre (A-Z)</SelectItem>
                            <SelectItem value="nombre_desc">Nombre (Z-A)</SelectItem>
                        </SelectContent>
                    </Select>
                    {hayFiltros && (
                        <Button variant="ghost" onClick={() => limpiar(['historial'])}>
                            <X /> Limpiar
                        </Button>
                    )}
                </BarraFiltros>

                <TablaServidor
                    pagina={proveedores}
                    columnas={columnas}
                    only={TABLA}
                    cargando={cargando}
                    idFila={(p) => p.id}
                    vacio={hayFiltros ? 'Ningún proveedor coincide con los filtros.' : historial ? 'No hay proveedores inhabilitados.' : 'Aún no hay proveedores registrados.'}
                />
            </div>

            <ConfirmarPeligro
                abierto={Boolean(inhabilitando)}
                onCerrar={() => setInhabilitando(undefined)}
                titulo={`¿Inhabilitar a ${inhabilitando?.nombre ?? ''}?`}
                descripcion="Pasa al historial y deja de aparecer en compras nuevas. Se puede restaurar cuando quieras."
                accion="Inhabilitar"
                onConfirmar={() => inhabilitando && inhabilitar(inhabilitando)}
            />
            <DetalleProveedor proveedor={viendo} onCerrar={() => setViendo(undefined)} />
            {gestionar && (
                <FormularioProveedor
                    key={formulario.apertura}
                    abierto={formulario.abierto}
                    proveedor={formulario.proveedor}
                    onCerrar={() => setFormulario((f) => ({ ...f, abierto: false }))}
                    estados={estados}
                    urls={urls}
                />
            )}
        </AppLayout>
    );
}
