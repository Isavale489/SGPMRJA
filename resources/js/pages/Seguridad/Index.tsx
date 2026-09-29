import { Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Copy, Eye, Lock, Pencil, Plus, Save, Search, ShieldCheck, Trash2, Undo2, Users } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';

import { Campo } from '@/components/app/campo';
import { ConfirmarPeligro } from '@/components/app/confirmar-peligro';
import { DialogoFormulario } from '@/components/app/dialogo-formulario';
import { Icono } from '@/components/app/icono';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import { confirmarDescarte, useGuardCambios } from '@/hooks/use-guard-cambios';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';

/** Espejo de SeguridadController::serializarRol() (lo verifica SeguridadPaginaTest). */
export interface RolFila {
    id: number;
    nombre: string;
    descripcion: string | null;
    es_sistema: boolean;
    es_admin: boolean;
    usuarios_count: number;
    permisos_count: number;
}

export interface ModuloMatriz {
    slug: string;
    nombre: string;
    icono: string;
    acciones: { accion: string; descripcion: string }[];
}

export interface SeccionMatriz {
    nombre: string;
    tema: 'maestros' | 'operativa' | 'reportes' | 'admin';
    icono: string;
    modulos: ModuloMatriz[];
}

interface Props {
    roles: RolFila[];
    permisos: Record<string, string[]>;
    secciones: SeccionMatriz[];
    urls: { roles: string; permisos: string; configuracion: string };
}

// Identidad por sección (la misma del menú): maestros navy, operativa emerald, reportes sky; admin, azul propio del panel.
const TEMA: Record<SeccionMatriz['tema'], string> = {
    maestros: 'text-primary bg-primary/10',
    operativa: 'text-emerald-600 bg-emerald-500/10 dark:text-emerald-400',
    reportes: 'text-sky-600 bg-sky-500/10 dark:text-sky-300',
    admin: 'text-blue-600 bg-blue-500/10 dark:text-blue-400',
};
const mutar = { preserveScroll: true, preserveState: true };
const ACCION: Record<string, string> = { ver: 'Ver', pdf: 'PDF' };
const etiqueta = (a: string) => ACCION[a] ?? a.charAt(0).toUpperCase() + a.slice(1).replace(/[-_]/g, ' ');

export default function SeguridadIndex({ roles, permisos, secciones, urls }: Props) {
    const editables = roles.filter((r) => !r.es_admin);
    const [pestana, setPestana] = useState<'roles' | 'permisos'>('roles');
    const [elegido, setRolId] = useState<number | undefined>(editables[0]?.id);
    // Si el rol elegido se eliminó, se pasa al primero que quede.
    const rolId = editables.some((r) => r.id === elegido) ? elegido : editables[0]?.id;
    const [sucio, setSucio] = useState(false);

    const irAPermisos = (id: number) => {
        if (id !== rolId && !confirmarDescarte(sucio)) return;
        setRolId(id);
        setPestana('permisos');
    };

    return (
        <AppLayout titulo="Roles y permisos" acciones={<Button variant="ghost" asChild><Link href={urls.configuracion}><ArrowLeft /> Configuración</Link></Button>}>
            <p className="text-muted-foreground -mt-3 mb-4 text-sm">Solo el Administrador entra aquí. Nadie puede darse acceso a este panel desde la matriz.</p>
            <Tabs value={pestana} onValueChange={(v) => setPestana(v as typeof pestana)}>
                <TabsList>
                    <TabsTrigger value="roles">Roles</TabsTrigger>
                    <TabsTrigger value="permisos">Permisos</TabsTrigger>
                </TabsList>
                <TabsContent value="roles" className="mt-4">
                    <PestanaRoles roles={roles} url={urls.roles} onPermisos={irAPermisos} />
                </TabsContent>
                <TabsContent value="permisos" className="mt-4" forceMount hidden={pestana !== 'permisos'}>
                    <Matriz key={rolId} roles={roles} permisos={permisos} secciones={secciones} rolId={rolId} onRol={(id) => { if (confirmarDescarte(sucio)) setRolId(id); }} url={urls.permisos} onSucio={setSucio} />
                </TabsContent>
            </Tabs>
        </AppLayout>
    );
}

function PestanaRoles({ roles, url, onPermisos }: { roles: RolFila[]; url: string; onPermisos: (id: number) => void }) {
    const [editando, setEditando] = useState<{ rol?: RolFila; apertura: number }>();
    const [eliminando, setEliminando] = useState<RolFila>();

    return (
        <>
            <div className="mb-4 flex flex-wrap items-center justify-between gap-2">
                <p className="text-muted-foreground text-sm">Los roles de sistema (Administrador, Supervisor) no se renombran ni se eliminan.</p>
                <Button onClick={() => setEditando((e) => ({ apertura: (e?.apertura ?? 0) + 1 }))}><Plus /> Nuevo rol</Button>
            </div>
            <ul className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                {roles.map((r) => {
                    const bloqueo = r.es_sistema ? 'Rol de sistema: no se elimina' : r.usuarios_count > 0 ? 'Tiene usuarios asignados' : null;
                    return (
                        <li key={r.id}>
                            <Card className="h-full">
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        {r.nombre}
                                        {r.es_sistema && <span className="bg-muted text-muted-foreground inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-normal"><Lock className="size-3" /> Sistema</span>}
                                    </CardTitle>
                                    <CardDescription>{r.descripcion || 'Sin descripción'}</CardDescription>
                                </CardHeader>
                                <CardContent className="grid gap-3">
                                    <p className="text-muted-foreground flex gap-4 text-sm">
                                        <span className="inline-flex items-center gap-1"><Users className="size-4" /> {r.usuarios_count} {r.usuarios_count === 1 ? 'usuario' : 'usuarios'}</span>
                                        <span className="inline-flex items-center gap-1"><ShieldCheck className="size-4" /> {r.es_admin ? 'Acceso total' : `${r.permisos_count} permisos`}</span>
                                    </p>
                                    <div className="flex flex-wrap gap-2">
                                        {!r.es_admin && <Button variant="outline" size="sm" onClick={() => onPermisos(r.id)}><ShieldCheck /> Permisos</Button>}
                                        <Button variant="ghost" size="sm" onClick={() => setEditando((e) => ({ rol: r, apertura: (e?.apertura ?? 0) + 1 }))} aria-label={`Editar el rol ${r.nombre}`}><Pencil /> {r.es_sistema ? 'Descripción' : 'Editar'}</Button>
                                        <Button variant="ghost" size="sm" className="text-destructive" disabled={Boolean(bloqueo)} title={bloqueo ?? undefined} onClick={() => setEliminando(r)} aria-label={`Eliminar el rol ${r.nombre}`}><Trash2 /> Eliminar</Button>
                                    </div>
                                    {bloqueo && !r.es_sistema && <p className="text-muted-foreground text-xs">{bloqueo}: reasígnalos para poder eliminarlo.</p>}
                                </CardContent>
                            </Card>
                        </li>
                    );
                })}
            </ul>
            {editando && <FormularioRol key={editando.apertura} rol={editando.rol} url={url} onCerrar={() => setEditando(undefined)} onCreado={onPermisos} />}
            <ConfirmarPeligro
                abierto={Boolean(eliminando)}
                onCerrar={() => setEliminando(undefined)}
                titulo={`¿Eliminar el rol ${eliminando?.nombre ?? ''}?`}
                descripcion="Se borran también sus permisos. No afecta a ningún usuario (no tiene asignados)."
                onConfirmar={() => eliminando && router.delete(`${url}/${eliminando.id}`, mutar)}
            />
        </>
    );
}

function FormularioRol({ rol, url, onCerrar, onCreado }: { rol?: RolFila; url: string; onCerrar: () => void; onCreado: (id: number) => void }) {
    const form = useForm({ nombre: rol?.nombre ?? '', descripcion: rol?.descripcion ?? '' });
    const sistema = Boolean(rol?.es_sistema);

    return (
        <DialogoFormulario
            abierto
            onCerrar={onCerrar}
            titulo={rol ? (sistema ? `Descripción de ${rol.nombre}` : 'Editar rol') : 'Nuevo rol'}
            descripcion={rol ? undefined : 'Después de crearlo, asígnale sus permisos.'}
            sucio={form.isDirty}
            procesando={form.processing}
            textoGuardar={rol ? 'Guardar cambios' : 'Crear rol'}
            onGuardar={() =>
                rol
                    ? form.put(`${url}/${rol.id}`, { ...mutar, onSuccess: onCerrar })
                    : form.post(url, {
                          ...mutar,
                          onSuccess: (pagina) => {
                              onCerrar();
                              const id = (pagina.flash as { rol?: number }).rol;
                              if (id) onCreado(id);
                          },
                      })
            }
        >
            <Campo etiqueta="Nombre del rol" requerido={!sistema} error={form.errors.nombre} ayuda={sistema ? 'El nombre de un rol de sistema no cambia.' : undefined}>
                <Input value={form.data.nombre} maxLength={60} disabled={sistema} placeholder="Vendedor, Almacén, Producción…" onChange={(e) => form.setData('nombre', e.target.value)} />
            </Campo>
            <Campo etiqueta="Descripción" error={form.errors.descripcion}>
                <Textarea rows={2} maxLength={255} value={form.data.descripcion} placeholder="Para qué sirve este rol (opcional)" onChange={(e) => form.setData('descripcion', e.target.value)} />
            </Campo>
        </DialogoFormulario>
    );
}

interface PropsMatriz {
    roles: RolFila[];
    permisos: Record<string, string[]>;
    secciones: SeccionMatriz[];
    rolId?: number;
    onRol: (id: number) => void;
    url: string;
    onSucio: (sucio: boolean) => void;
}

/**
 * Matriz módulo × acción de un rol. «Ver» es prerrequisito: marcar cualquier
 * acción marca «Ver»; quitar «Ver» quita todo el módulo (el servidor lo exige igual).
 */
function Matriz({ roles, permisos, secciones, rolId, onRol, url, onSucio }: PropsMatriz) {
    const rol = roles.find((r) => r.id === rolId);
    // Firma por contenido: recargar las props (p. ej. al editar otro rol en la
    // pestaña Roles) no borra las marcas sin guardar; guardar este rol sí las alinea.
    const firma = [...(rolId ? (permisos[rolId] ?? []) : [])].sort().join('|');
    const guardados = useMemo(() => new Set(firma ? firma.split('|') : []), [firma]);
    const [marcados, setMarcados] = useState<Set<string>>(guardados);
    const [buscar, setBuscar] = useState('');
    const [guardando, setGuardando] = useState(false);
    useEffect(() => setMarcados(new Set(guardados)), [guardados]);

    const sucio = marcados.size !== guardados.size || [...marcados].some((p) => !guardados.has(p));
    useEffect(() => onSucio(sucio), [sucio, onSucio]);
    useGuardCambios(sucio && !guardando);

    const visibles = useMemo(() => {
        const k = buscar.trim().toLowerCase();
        return secciones.map((s) => ({ ...s, modulos: s.modulos.filter((m) => !k || m.nombre.toLowerCase().includes(k)) })).filter((s) => s.modulos.length);
    }, [secciones, buscar]);
    const claves = (m: ModuloMatriz) => m.acciones.map((a) => `${m.slug}.${a.accion}`);
    const todas = visibles.flatMap((s) => s.modulos.flatMap(claves));

    const cambiar = (fn: (s: Set<string>) => void) => setMarcados((prev) => { const s = new Set(prev); fn(s); return s; });
    const alternar = (m: ModuloMatriz, accion: string, activo: boolean) => cambiar((s) => {
        if (accion === 'ver' && !activo) claves(m).forEach((c) => s.delete(c));
        else if (activo) { s.add(`${m.slug}.${accion}`); if (m.acciones.some((a) => a.accion === 'ver')) s.add(`${m.slug}.ver`); }
        else s.delete(`${m.slug}.${accion}`);
    });
    const fijar = (lista: string[], activo: boolean) => cambiar((s) => lista.forEach((c) => (activo ? s.add(c) : s.delete(c))));
    // Solo toca los módulos visibles: los que oculta la búsqueda conservan sus marcas.
    const soloVer = () =>
        cambiar((s) =>
            visibles.forEach((sec) =>
                sec.modulos.forEach((m) => {
                    claves(m).forEach((c) => s.delete(c));
                    if (m.acciones.some((a) => a.accion === 'ver')) s.add(`${m.slug}.ver`);
                }),
            ),
        );

    const guardar = () => {
        if (!rol) return;
        setGuardando(true);
        router.put(`${url}/${rol.id}`, { permisos: [...marcados] }, { ...mutar, onFinish: () => setGuardando(false) });
    };

    const admin = roles.find((r) => r.es_admin);
    const editables = roles.filter((r) => !r.es_admin);

    return (
        <div className="grid gap-4">
            <Card>
                <CardContent className="flex flex-wrap items-end gap-3">
                    <Campo etiqueta="Rol a configurar" className="min-w-56">
                        {(control) => (
                            <Select value={rolId ? String(rolId) : undefined} onValueChange={(v) => onRol(Number(v))}>
                                <SelectTrigger {...control} className="w-full"><SelectValue placeholder="Selecciona un rol" /></SelectTrigger>
                                <SelectContent>{editables.map((r) => <SelectItem key={r.id} value={String(r.id)}>{r.nombre}</SelectItem>)}</SelectContent>
                            </Select>
                        )}
                    </Campo>
                    {rol && (
                        <>
                            <div className="relative min-w-48 flex-1">
                                <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2" />
                                <Input type="search" value={buscar} onChange={(e) => setBuscar(e.target.value)} placeholder="Buscar módulo…" aria-label="Buscar módulo" className="pl-8" />
                            </div>
                            <Select value="" onValueChange={(v) => setMarcados(new Set(permisos[v] ?? []))}>
                                <SelectTrigger className="w-44" aria-label="Copiar los permisos de otro rol"><Copy className="size-4" /><SelectValue placeholder="Copiar de…" /></SelectTrigger>
                                <SelectContent>{editables.filter((r) => r.id !== rol.id).map((r) => <SelectItem key={r.id} value={String(r.id)}>{r.nombre}</SelectItem>)}</SelectContent>
                            </Select>
                            <Button variant="outline" onClick={soloVer} title="Solo «Ver» en los módulos visibles (rol de consulta)"><Eye /> Solo ver</Button>
                            <Button variant="outline" onClick={() => fijar(todas, true)}>Marcar todo</Button>
                            <Button variant="outline" onClick={() => fijar(todas, false)}>Limpiar</Button>
                        </>
                    )}
                </CardContent>
            </Card>

            {admin && (
                <p className="bg-muted/50 text-muted-foreground flex items-center gap-2 rounded-lg border p-3 text-sm">
                    <Lock className="size-4 shrink-0" /> {admin.nombre}: acceso total a todo el sistema; no se configura en la matriz.
                </p>
            )}

            {rol && (
                <>
                    <div className="bg-background/95 sticky top-14 z-10 flex flex-wrap items-center justify-between gap-2 rounded-lg border p-3 backdrop-blur">
                        <p className="text-sm">
                            <span className="font-medium">{rol.nombre}</span> tiene <span className="tabular font-medium">{marcados.size}</span> permisos
                            {sucio && <span className="bg-warning/15 text-warning ml-2 rounded-full px-2 py-0.5 text-xs">Cambios sin guardar</span>}
                        </p>
                        <div className="flex gap-2">
                            {sucio && <Button variant="ghost" onClick={() => setMarcados(new Set(guardados))}><Undo2 /> Descartar</Button>}
                            <Button onClick={guardar} disabled={!sucio || guardando}><Save /> Guardar permisos</Button>
                        </div>
                    </div>

                    {visibles.map((s) => {
                        const deSeccion = s.modulos.flatMap(claves);
                        const otorgadas = deSeccion.filter((c) => marcados.has(c)).length;
                        return (
                            <Card key={s.nombre}>
                                <CardHeader>
                                    <div className="flex flex-wrap items-center justify-between gap-2">
                                        <CardTitle className="flex items-center gap-3 text-base">
                                            <span className={cn('grid size-8 place-items-center rounded-lg', TEMA[s.tema])}><Icono nombre={s.icono} className="size-4" /></span>
                                            {s.nombre}
                                            <span className="text-muted-foreground text-sm font-normal tabular">{otorgadas}/{deSeccion.length}</span>
                                        </CardTitle>
                                        <div className="flex flex-wrap gap-1">
                                            <Button variant="ghost" size="sm" onClick={() => fijar(deSeccion, true)} aria-label={`Marcar toda la sección ${s.nombre}`}>Marcar sección</Button>
                                            <Button variant="ghost" size="sm" onClick={() => fijar(deSeccion, false)} aria-label={`Limpiar la sección ${s.nombre}`}>Limpiar sección</Button>
                                        </div>
                                    </div>
                                </CardHeader>
                                <CardContent className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                    {s.modulos.map((m) => {
                                        const cs = claves(m);
                                        const todo = cs.every((c) => marcados.has(c));
                                        const parcial = !todo && cs.some((c) => marcados.has(c));
                                        return (
                                            <fieldset key={m.slug} className="grid content-start gap-2 rounded-lg border p-3">
                                                <legend className="sr-only">{m.nombre}</legend>
                                                <div className="flex items-center justify-between gap-2">
                                                    <span className="flex items-center gap-2 text-sm font-medium"><Icono nombre={m.icono} className="text-muted-foreground size-4" /> {m.nombre}</span>
                                                    <label className="text-muted-foreground flex items-center gap-1.5 text-xs">
                                                        <CasillaTodo checked={todo} parcial={parcial} onChange={(v) => fijar(cs, v)} etiqueta={`Todo el módulo ${m.nombre}`} />
                                                        Todo
                                                    </label>
                                                </div>
                                                <div className="flex flex-wrap gap-x-4 gap-y-1.5">
                                                    {m.acciones.map((a) => (
                                                        <label key={a.accion} className="flex items-center gap-1.5 text-sm" title={a.descripcion}>
                                                            <input
                                                                type="checkbox"
                                                                className="accent-primary size-4"
                                                                checked={marcados.has(`${m.slug}.${a.accion}`)}
                                                                onChange={(e) => alternar(m, a.accion, e.target.checked)}
                                                                aria-label={`${m.nombre}: ${a.descripcion}`}
                                                            />
                                                            {etiqueta(a.accion)}
                                                        </label>
                                                    ))}
                                                </div>
                                            </fieldset>
                                        );
                                    })}
                                </CardContent>
                            </Card>
                        );
                    })}
                    {!visibles.length && <p className="text-muted-foreground text-center text-sm">Ningún módulo coincide con «{buscar}».</p>}
                </>
            )}
            {!rol && <p className="text-muted-foreground rounded-lg border border-dashed p-8 text-center text-sm">Crea un rol en la pestaña Roles para asignarle permisos.</p>}
        </div>
    );
}

/** Casilla «Todo» del módulo: indeterminada cuando hay acciones sueltas (el lector de pantalla la anuncia como «mixta»). */
function CasillaTodo({ checked, parcial, onChange, etiqueta }: { checked: boolean; parcial: boolean; onChange: (v: boolean) => void; etiqueta: string }) {
    const ref = useRef<HTMLInputElement>(null);
    useEffect(() => {
        if (ref.current) ref.current.indeterminate = parcial;
    }, [parcial]);
    return <input ref={ref} type="checkbox" className="accent-primary size-4" checked={checked} onChange={(e) => onChange(e.target.checked)} aria-label={etiqueta} />;
}
