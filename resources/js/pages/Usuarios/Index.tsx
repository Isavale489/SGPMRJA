import { Link, router } from '@inertiajs/react';
import { Archive, ArrowLeft, CalendarDays, Eye, KeyRound, LockKeyholeOpen, Mail, MoreVertical, Pencil, Plus, RotateCcw, Search, ShieldAlert, ShieldCheck, UserX, X } from 'lucide-react';
import { useState } from 'react';

import { BarraFiltros } from '@/components/app/barra-filtros';
import { ConfirmarPeligro } from '@/components/app/confirmar-peligro';
import { Dato } from '@/components/app/dato';
import { ExportarPdf } from '@/components/app/exportar-pdf';
import { TablaServidor, type Columna } from '@/components/app/tabla-servidor';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
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

import { FormularioUsuario, ResetearClave } from './formularios';
import { iniciales, type FiltrosUsuarios, type PaginaUsuarios, type UsuarioFila } from './tipos';

const TODOS = 'todos'; // los Select de Radix no admiten '' como valor
const TABLA = ['usuarios', 'filtros'];

function Foto({ usuario, className }: { usuario: UsuarioFila; className?: string }) {
    return (
        <Avatar className={className ?? 'size-8'}>
            {usuario.avatar && <AvatarImage src={usuario.avatar} alt="" className="object-cover" />}
            <AvatarFallback className="text-xs">{iniciales(usuario.nombre)}</AvatarFallback>
        </Avatar>
    );
}

export default function UsuariosIndex({ usuarios, filtros: filtrosIniciales, roles, urls }: PaginaUsuarios) {
    const { puede } = usePermisos();
    const gestionar = puede('users.gestionar');
    const historial = Boolean(filtrosIniciales.historial);
    const { filtros, cambiar, limpiar, cargando } = useFiltrosUrl<FiltrosUsuarios>(urls.index, filtrosIniciales, TABLA);

    const [formulario, setFormulario] = useState<{ abierto: boolean; usuario?: UsuarioFila; apertura: number }>({ abierto: false, apertura: 0 });
    const abrirFormulario = (usuario?: UsuarioFila) => setFormulario((f) => ({ abierto: true, usuario, apertura: f.apertura + 1 }));
    const [viendo, setViendo] = useState<UsuarioFila>();
    // Fuera del menú: si los diálogos vivieran dentro, el menú quedaría abierto al confirmar.
    const [inhabilitando, setInhabilitando] = useState<UsuarioFila>();
    const [reseteando, setReseteando] = useState<UsuarioFila>();

    const hayFiltros = Boolean(filtros.buscar || filtros.rol);
    const accion = (u: UsuarioFila, ruta: string) => router.post(`${urls.index}/${u.id}/${ruta}`, {}, { preserveScroll: true });

    const columnas: Columna<UsuarioFila>[] = [
        {
            id: 'nombre',
            encabezado: 'Usuario',
            celda: (u) => (
                <span className="flex items-center gap-3">
                    <Foto usuario={u} />
                    <span className="grid min-w-0">
                        <span className="font-medium">{u.nombre}{u.es_propio && <span className="text-muted-foreground font-normal"> (tú)</span>}</span>
                        <span className="text-muted-foreground truncate text-xs">{u.email}</span>
                    </span>
                </span>
            ),
        },
        { id: 'rol', encabezado: 'Rol', celda: (u) => u.rol ?? '—' },
        {
            id: 'acceso',
            encabezado: 'Acceso',
            celda: (u) => (
                <span className="flex flex-wrap gap-1.5">
                    {u.recuperacion_bloqueada ? (
                        <span className="bg-destructive/10 text-destructive ring-destructive/25 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset">
                            <ShieldAlert className="size-3" /> Recuperación bloqueada
                        </span>
                    ) : (
                        <span className="text-muted-foreground inline-flex items-center gap-1 text-xs"><ShieldCheck className="size-3" /> Normal</span>
                    )}
                    {u.debe_cambiar_clave && (
                        <span className="bg-warning/12 text-warning ring-warning/25 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset">Clave temporal</span>
                    )}
                </span>
            ),
        },
        {
            id: 'acciones',
            encabezado: <span className="sr-only">Acciones</span>,
            className: 'w-24 text-right',
            celda: (u) => (
                <div className="flex justify-end gap-1">
                    <Button variant="ghost" size="icon" onClick={() => setViendo(u)} aria-label={`Ver ${u.nombre}`}><Eye /></Button>
                    {gestionar && (
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="ghost" size="icon" aria-label={`Más acciones para ${u.nombre}`}><MoreVertical /></Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                {u.inhabilitado ? (
                                    <DropdownMenuItem tono="restaurar" onSelect={() => accion(u, 'restore')}><RotateCcw /> Habilitar</DropdownMenuItem>
                                ) : (
                                    <>
                                        <DropdownMenuItem tono="editar" onSelect={() => abrirFormulario(u)}><Pencil /> Editar</DropdownMenuItem>
                                        {u.recuperacion_bloqueada && (
                                            <DropdownMenuItem tono="restaurar" onSelect={() => accion(u, 'unlock-recovery')}><LockKeyholeOpen /> Desbloquear recuperación</DropdownMenuItem>
                                        )}
                                        {/* La propia cuenta no se resetea ni se inhabilita desde aquí (el servidor también lo impide). */}
                                        {!u.es_propio && (
                                            <>
                                                <DropdownMenuItem tono="principal" onSelect={() => setReseteando(u)}><KeyRound /> Resetear contraseña</DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem tono="aviso" onSelect={() => setInhabilitando(u)}><UserX /> Inhabilitar</DropdownMenuItem>
                                            </>
                                        )}
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
            titulo={historial ? 'Usuarios inhabilitados' : 'Usuarios'}
            acciones={
                <>
                    <Button variant="ghost" asChild>
                        {historial ? <Link href={urls.index}><ArrowLeft /> Solo activos</Link> : <Link href={`${urls.index}?historial=1`}><Archive /> Inhabilitados</Link>}
                    </Button>
                    {puede('users.pdf') && (
                        <ExportarPdf
                            url={urls.reportePdf}
                            recurso="usuarios"
                            filtros={[
                                { parametro: 'role_id', etiqueta: 'Rol', todos: 'Todos los roles', opciones: roles.map((r) => ({ valor: String(r.id), etiqueta: r.nombre })) },
                                { parametro: 'estatus', etiqueta: 'Estatus', todos: 'Todos', opciones: [{ valor: '1', etiqueta: 'Activos' }, { valor: '0', etiqueta: 'Inhabilitados' }] },
                            ]}
                        />
                    )}
                    {gestionar && !historial && <Button onClick={() => abrirFormulario()}><Plus /> Agregar usuario</Button>}
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
                            placeholder="Buscar por nombre, correo o rol…"
                            aria-label="Buscar usuario"
                            className="pl-8"
                        />
                    </div>
                    <Select value={filtros.rol ?? TODOS} onValueChange={(v) => cambiar('rol', v === TODOS ? undefined : v)}>
                        <SelectTrigger className="w-48" aria-label="Filtrar por rol"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value={TODOS}>Todos los roles</SelectItem>
                            {roles.map((r) => <SelectItem key={r.id} value={String(r.id)}>{r.nombre}</SelectItem>)}
                        </SelectContent>
                    </Select>
                    {hayFiltros && <Button variant="ghost" onClick={() => limpiar(['historial'])}><X /> Limpiar</Button>}
                </BarraFiltros>

                <TablaServidor
                    pagina={usuarios}
                    columnas={columnas}
                    only={TABLA}
                    cargando={cargando}
                    idFila={(u) => u.id}
                    vacio={hayFiltros ? 'Ningún usuario coincide con los filtros.' : historial ? 'No hay usuarios inhabilitados.' : 'Aún no hay usuarios.'}
                />
            </div>

            <ConfirmarPeligro
                abierto={Boolean(inhabilitando)}
                onCerrar={() => setInhabilitando(undefined)}
                titulo={`¿Inhabilitar a ${inhabilitando?.nombre ?? ''}?`}
                descripcion="No podrá entrar al sistema. La cuenta no se borra (se conservan sus registros) y se puede habilitar cuando quieras."
                accion="Inhabilitar"
                onConfirmar={() => inhabilitando && router.delete(`${urls.index}/${inhabilitando.id}`, { preserveScroll: true })}
            />
            {reseteando && <ResetearClave key={reseteando.id} usuario={reseteando} url={urls.index} onCerrar={() => setReseteando(undefined)} />}
            <DetalleUsuario usuario={viendo} onCerrar={() => setViendo(undefined)} />
            {gestionar && (
                <FormularioUsuario
                    key={formulario.apertura}
                    abierto={formulario.abierto}
                    usuario={formulario.usuario}
                    roles={roles}
                    urls={urls}
                    onCerrar={() => setFormulario((f) => ({ ...f, abierto: false }))}
                />
            )}
        </AppLayout>
    );
}

function DetalleUsuario({ usuario: u, onCerrar }: { usuario?: UsuarioFila; onCerrar: () => void }) {
    return (
        <Dialog open={Boolean(u)} onOpenChange={(a) => !a && onCerrar()}>
            <DialogContent className="sm:max-w-md">
                {u && (
                    <>
                        <DialogHeader className="flex-row items-center gap-3">
                            <Foto usuario={u} className="size-12" />
                            <div className="grid gap-1">
                                <DialogTitle>{u.nombre}</DialogTitle>
                                <DialogDescription>{u.rol ?? 'Sin rol'}{u.inhabilitado && ' · Inhabilitado'}</DialogDescription>
                            </div>
                        </DialogHeader>
                        <dl className="grid gap-4">
                            <Dato icono={<Mail />} etiqueta="Correo">{u.email}</Dato>
                            <Dato icono={<ShieldCheck />} etiqueta="Recuperación de contraseña">
                                {u.recuperacion_bloqueada ? 'Bloqueada' : 'Normal'}
                                <span className="text-muted-foreground block text-xs">{u.intentos_fallidos} intento(s) fallido(s){u.debe_cambiar_clave && ' · clave temporal pendiente de cambio'}</span>
                            </Dato>
                            {u.creado && <Dato icono={<CalendarDays />} etiqueta="Registrado">{formatoFecha(u.creado)}</Dato>}
                        </dl>
                    </>
                )}
            </DialogContent>
        </Dialog>
    );
}
