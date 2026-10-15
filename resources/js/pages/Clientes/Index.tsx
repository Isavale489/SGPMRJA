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

import { DetalleCliente } from './detalle-cliente';
import { FormularioCliente } from './formulario-cliente';
import { ETIQUETA_TIPO, TIPOS_CLIENTE, type ClienteFila, type FiltrosClientes, type PaginaClientes } from './tipos';

const TODOS = 'todos'; // los Select de Radix no admiten '' como valor
const TABLA = ['clientes', 'filtros'];

export default function ClientesIndex({ clientes, filtros: filtrosIniciales, estados, urls }: PaginaClientes) {
    const { puede } = usePermisos();
    const gestionar = puede('clientes.gestionar');
    const historial = Boolean(filtrosIniciales.historial);
    const { filtros, cambiar, limpiar, cargando } = useFiltrosUrl<FiltrosClientes>(urls.index, filtrosIniciales, TABLA);

    // `apertura` cambia en cada apertura → el formulario se monta de nuevo con los datos correctos.
    const [formulario, setFormulario] = useState<{ abierto: boolean; cliente?: ClienteFila; apertura: number }>({ abierto: false, apertura: 0 });
    const abrirFormulario = (cliente?: ClienteFila) => setFormulario((f) => ({ abierto: true, cliente, apertura: f.apertura + 1 }));
    const [viendo, setViendo] = useState<ClienteFila>();
    // Fuera del menú: si la confirmación viviera dentro, el menú quedaría abierto al confirmar.
    const [inhabilitando, setInhabilitando] = useState<ClienteFila>();

    const hayFiltros = Boolean(filtros.buscar || filtros.tipo || filtros.estado || (filtros.orden && filtros.orden !== 'recientes'));
    const inhabilitar = (p: ClienteFila) => router.delete(`${urls.index}/${p.id}`, { preserveScroll: true });
    const restaurar = (p: ClienteFila) => router.post(`${urls.index}/${p.id}/restore`, {}, { preserveScroll: true });

    const columnas: Columna<ClienteFila>[] = [
        { id: 'documento', encabezado: 'Documento', celda: (p) => <span className="font-mono text-xs">{p.documento}</span> },
        {
            id: 'nombre',
            encabezado: 'Nombre / Razón social',
            celda: (p) => (
                <span className="grid">
                    <span className="font-medium">{p.nombre}</span>
                    <span className="text-muted-foreground text-xs">{ETIQUETA_TIPO[p.tipo]}</span>
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
            titulo={historial ? 'Clientes inhabilitados' : 'Clientes'}
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
                    {puede('clientes.pdf') && (
                        <ExportarPdf
                            url={urls.reportePdf}
                            recurso="clientes"
                            filtros={[
                                { parametro: 'tipo_cliente', etiqueta: 'Tipo', todos: 'Todos los tipos', opciones: TIPOS_CLIENTE },
                                { parametro: 'estado', etiqueta: 'Estatus', todos: 'Activos', opciones: [{ valor: '0', etiqueta: 'Inhabilitados' }] },
                            ]}
                        />
                    )}
                    {gestionar && !historial && (
                        <Button onClick={() => abrirFormulario()}>
                            <Plus /> Agregar cliente
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
                            aria-label="Buscar cliente"
                            className="pl-8"
                        />
                    </div>
                    <Select value={filtros.tipo ?? TODOS} onValueChange={(v) => cambiar('tipo', v === TODOS ? undefined : (v as FiltrosClientes['tipo']))}>
                        <SelectTrigger className="w-40" aria-label="Filtrar por tipo"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value={TODOS}>Todos los tipos</SelectItem>
                            {TIPOS_CLIENTE.map((t) => (
                                <SelectItem key={t.valor} value={t.valor}>{t.etiqueta}</SelectItem>
                            ))}
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
                    <Select value={filtros.orden ?? 'recientes'} onValueChange={(v) => cambiar('orden', v as FiltrosClientes['orden'])}>
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
                    pagina={clientes}
                    columnas={columnas}
                    only={TABLA}
                    cargando={cargando}
                    idFila={(p) => p.id}
                    vacio={hayFiltros ? 'Ningún cliente coincide con los filtros.' : historial ? 'No hay clientes inhabilitados.' : 'Aún no hay clientes registrados.'}
                />
            </div>

            <ConfirmarPeligro
                abierto={Boolean(inhabilitando)}
                onCerrar={() => setInhabilitando(undefined)}
                titulo={`¿Inhabilitar a ${inhabilitando?.nombre ?? ''}?`}
                descripcion="Pasa al historial y deja de aparecer en cotizaciones nuevas. Sus cotizaciones y pedidos se conservan. Se puede restaurar cuando quieras."
                accion="Inhabilitar"
                onConfirmar={() => inhabilitando && inhabilitar(inhabilitando)}
            />
            <DetalleCliente cliente={viendo} onCerrar={() => setViendo(undefined)} />
            {gestionar && (
                <FormularioCliente
                    key={formulario.apertura}
                    abierto={formulario.abierto}
                    cliente={formulario.cliente}
                    onCerrar={() => setFormulario((f) => ({ ...f, abierto: false }))}
                    estados={estados}
                    urls={urls}
                />
            )}
        </AppLayout>
    );
}
