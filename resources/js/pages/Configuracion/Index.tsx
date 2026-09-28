import { Link, router, useForm } from '@inertiajs/react';
import { Info, Pencil, Percent, Plus, RotateCcw, Save, Settings2, ShieldCheck, Trash2, UserCog, Users } from 'lucide-react';
import { useState } from 'react';

import { Campo } from '@/components/app/campo';
import { ConfirmarPeligro } from '@/components/app/confirmar-peligro';
import { DialogoFormulario } from '@/components/app/dialogo-formulario';
import { Button } from '@/components/ui/button';
import { Card, CardAction, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useGuardCambios } from '@/hooks/use-guard-cambios';
import { usePermisos } from '@/hooks/use-permisos';
import AppLayout from '@/layouts/app-layout';
import { formatoNumero } from '@/lib/formato';
import { cn } from '@/lib/utils';

/** Espejo de ConfiguracionController::campo() (lo verifica ConfiguracionPaginaTest). */
export interface Parametro {
    clave: string;
    nombre: string;
    descripcion: string | null;
    tipo: 'decimal' | 'entero' | 'booleano' | 'texto';
    sufijo: string | null;
    valor: string | number | boolean | null;
    es_default: boolean;
    default: string | number | boolean | null;
    requerido: boolean;
    min: number | null;
    max: number | null;
}

export interface ModuloConfig {
    slug: string;
    nombre: string;
    parametros: Parametro[];
}

export interface ImpuestoFila {
    id: number;
    codigo: string;
    nombre: string;
    porcentaje: number;
    descripcion: string | null;
    estado: 'activo' | 'inactivo';
    es_iva: boolean;
}

interface Props {
    modulos: ModuloConfig[];
    impuestos: ImpuestoFila[];
    urls: { modulos: string; impuestos: string; seguridad: string; usuarios: string; perfil: string };
}

const texto = (v: Parametro['valor']) => (v === null || v === undefined ? '' : String(v));
const legible = (p: Parametro, v: Parametro['valor']) => (p.tipo === 'booleano' ? (v === true || v === '1' || v === 'true' ? 'Sí' : 'No') : `${texto(v)}${p.sufijo ? ` ${p.sufijo}` : ''}`);
const mutar = { preserveScroll: true, preserveState: true };

export default function ConfiguracionIndex({ modulos, impuestos, urls }: Props) {
    const { puede, esAdmin } = usePermisos();
    const gestionar = puede('configuracion.gestionar');
    const pestanas = [...modulos.map((m) => m.slug), 'impuestos'];
    // #slug en la URL (así enlaza Seguridad): abre esa pestaña.
    const [pestana, setPestana] = useState(() => {
        const h = typeof window !== 'undefined' ? window.location.hash.slice(1) : '';
        return pestanas.includes(h) ? h : pestanas[0]!;
    });
    const elegir = (slug: string) => {
        setPestana(slug);
        window.history.replaceState(window.history.state, '', `#${slug}`);
    };
    const modulo = modulos.find((m) => m.slug === pestana);
    const item = (activo: boolean) => cn('flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm transition-colors', activo ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-muted');

    return (
        <AppLayout titulo="Configuración del sistema">
            <p className="text-muted-foreground -mt-3 mb-4 text-sm">Parámetros de negocio que aplica todo el sistema. Cada cambio queda registrado con quién lo hizo.</p>
            <div className="grid gap-4 lg:grid-cols-[15rem_1fr] lg:items-start">
                <nav aria-label="Secciones de configuración" className="bg-card grid gap-1 rounded-lg border p-2">
                    <p className="text-muted-foreground px-3 pt-1 pb-1 text-xs font-medium uppercase">Parámetros</p>
                    {modulos.map((m) => (
                        <button key={m.slug} type="button" aria-current={pestana === m.slug ? 'page' : undefined} className={item(pestana === m.slug)} onClick={() => elegir(m.slug)}>
                            <Settings2 className="size-4" /> {m.nombre}
                        </button>
                    ))}
                    <button type="button" aria-current={pestana === 'impuestos' ? 'page' : undefined} className={item(pestana === 'impuestos')} onClick={() => elegir('impuestos')}>
                        <Percent className="size-4" /> Impuestos
                    </button>
                    <p className="text-muted-foreground px-3 pt-3 pb-1 text-xs font-medium uppercase">Otras configuraciones</p>
                    {puede('users.ver') && <Link href={urls.usuarios} className={item(false)}><Users className="size-4" /> Usuarios</Link>}
                    {/* Perfil y Seguridad siguen en Blade → enlace normal. */}
                    <a href={urls.perfil} className={item(false)}><UserCog className="size-4" /> Mi perfil</a>
                    {esAdmin && <a href={urls.seguridad} className={item(false)}><ShieldCheck className="size-4" /> Roles y permisos</a>}
                </nav>
                <div className="min-w-0">
                    {modulo ? (
                        <FormularioModulo key={`${modulo.slug}:${modulo.parametros.map((p) => `${p.clave}=${texto(p.valor)}`).join('|')}`} modulo={modulo} url={`${urls.modulos}/${modulo.slug}`} gestionar={gestionar} />
                    ) : (
                        <Impuestos impuestos={impuestos} url={urls.impuestos} gestionar={gestionar} />
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

/** Parámetros de un módulo. Se remonta (key) cuando cambian los valores guardados. */
function FormularioModulo({ modulo, url, gestionar }: { modulo: ModuloConfig; url: string; gestionar: boolean }) {
    const iniciales: Record<string, string | boolean> = Object.fromEntries(modulo.parametros.map((p) => [p.clave, p.tipo === 'booleano' ? p.valor === true || p.valor === '1' : texto(p.valor)]));
    const form = useForm<{ valores: Record<string, string | boolean> }>({ valores: iniciales });
    const e = form.errors as Record<string, string | undefined>;
    useGuardCambios(form.isDirty && !form.processing);

    const guardar = (ev: React.FormEvent) => {
        ev.preventDefault();
        // Solo lo que cambió: así un parámetro que se deja igual sigue siendo «por defecto».
        form.transform((d) => ({ valores: Object.fromEntries(Object.entries(d.valores).filter(([k, v]) => v !== iniciales[k]).map(([k, v]) => [k, typeof v === 'boolean' ? (v ? 1 : 0) : v])) }));
        form.put(url, mutar);
    };

    return (
        <form onSubmit={guardar} noValidate>
            <Card>
                <CardHeader>
                    <CardTitle className="text-base">{modulo.nombre}</CardTitle>
                    <CardDescription>{modulo.parametros.length} {modulo.parametros.length === 1 ? 'parámetro' : 'parámetros'}</CardDescription>
                </CardHeader>
                <CardContent className="grid gap-6">
                    {modulo.parametros.map((p) => {
                        const valor = form.data.valores[p.clave];
                        return (
                            <div key={p.clave} className="grid gap-2 border-b pb-6 last:border-b-0 last:pb-0">
                                <div className="flex flex-wrap items-center justify-between gap-2">
                                    <Label htmlFor={`param-${p.clave}`} className="text-sm">{p.nombre}{p.requerido && <span className="text-destructive"> *</span>}</Label>
                                    {p.es_default ? (
                                        <span className="bg-muted text-muted-foreground rounded-full px-2 py-0.5 text-xs">Por defecto: {legible(p, p.default)}</span>
                                    ) : (
                                        gestionar && (
                                            <Button type="button" variant="ghost" size="sm" onClick={() => router.delete(`${url}/${encodeURIComponent(p.clave)}`, mutar)} title={`Volver al valor por defecto (${legible(p, p.default)})`}>
                                                <RotateCcw /> Restablecer ({legible(p, p.default)})
                                            </Button>
                                        )
                                    )}
                                </div>
                                {p.tipo === 'booleano' ? (
                                    <Switch id={`param-${p.clave}`} checked={Boolean(valor)} disabled={!gestionar} onCheckedChange={(v) => form.setData('valores', { ...form.data.valores, [p.clave]: v })} />
                                ) : (
                                    <div className="flex max-w-xs items-center gap-2">
                                        <Input
                                            id={`param-${p.clave}`}
                                            type={p.tipo === 'texto' ? 'text' : 'number'}
                                            step={p.tipo === 'entero' ? 1 : 0.01}
                                            min={p.min ?? undefined}
                                            max={p.tipo === 'texto' ? undefined : (p.max ?? undefined)}
                                            maxLength={p.tipo === 'texto' ? (p.max ?? undefined) : undefined}
                                            inputMode={p.tipo === 'entero' ? 'numeric' : p.tipo === 'decimal' ? 'decimal' : undefined}
                                            value={String(valor ?? '')}
                                            disabled={!gestionar}
                                            aria-invalid={e[`valores.${p.clave}`] ? true : undefined}
                                            aria-describedby={`ayuda-${p.clave}`}
                                            onChange={(ev) => form.setData('valores', { ...form.data.valores, [p.clave]: ev.target.value })}
                                            className="tabular"
                                        />
                                        {p.sufijo && <span className="text-muted-foreground text-sm">{p.sufijo}</span>}
                                    </div>
                                )}
                                {e[`valores.${p.clave}`] && <p className="text-destructive text-xs">{e[`valores.${p.clave}`]}</p>}
                                {p.descripcion && (
                                    <div id={`ayuda-${p.clave}`} className="text-muted-foreground flex gap-2 text-xs">
                                        <Info className="mt-0.5 size-3.5 shrink-0" />
                                        <div className="grid gap-1">{p.descripcion.split(/\r?\n/).map((l, i) => <p key={i}>{l}</p>)}</div>
                                    </div>
                                )}
                            </div>
                        );
                    })}
                    {gestionar && (
                        <div className="flex justify-end gap-2">
                            {form.isDirty && <Button type="button" variant="ghost" onClick={() => form.reset()}>Descartar cambios</Button>}
                            <Button type="submit" disabled={!form.isDirty || form.processing}><Save /> Guardar</Button>
                        </div>
                    )}
                </CardContent>
            </Card>
        </form>
    );
}

function Impuestos({ impuestos, url, gestionar }: { impuestos: ImpuestoFila[]; url: string; gestionar: boolean }) {
    const [editando, setEditando] = useState<{ impuesto?: ImpuestoFila; apertura: number }>();
    const [eliminando, setEliminando] = useState<ImpuestoFila>();

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-base">Impuestos</CardTitle>
                <CardDescription>El IVA es la tasa que usan las compras: no se elimina ni se desactiva.</CardDescription>
                {gestionar && <CardAction><Button onClick={() => setEditando((a) => ({ apertura: (a?.apertura ?? 0) + 1 }))}><Plus /> Agregar impuesto</Button></CardAction>}
            </CardHeader>
            <CardContent className="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow className="hover:bg-transparent">
                            <TableHead>Código</TableHead>
                            <TableHead>Nombre</TableHead>
                            <TableHead className="text-right">Porcentaje</TableHead>
                            <TableHead>Estado</TableHead>
                            {gestionar && <TableHead><span className="sr-only">Acciones</span></TableHead>}
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {impuestos.map((i) => (
                            <TableRow key={i.id}>
                                <TableCell><code className="font-mono text-xs">{i.codigo}</code></TableCell>
                                <TableCell>{i.nombre}{i.descripcion && <span className="text-muted-foreground block text-xs">{i.descripcion}</span>}</TableCell>
                                <TableCell className="text-right tabular">{formatoNumero(i.porcentaje)} %</TableCell>
                                <TableCell className={i.estado === 'activo' ? 'text-success' : 'text-muted-foreground'}>{i.estado === 'activo' ? 'Activo' : 'Inactivo'}</TableCell>
                                {gestionar && (
                                    <TableCell className="text-right">
                                        <Button variant="ghost" size="icon" aria-label={`Editar ${i.nombre}`} onClick={() => setEditando((a) => ({ impuesto: i, apertura: (a?.apertura ?? 0) + 1 }))}><Pencil /></Button>
                                        {!i.es_iva && <Button variant="ghost" size="icon" aria-label={`Eliminar ${i.nombre}`} onClick={() => setEliminando(i)}><Trash2 /></Button>}
                                    </TableCell>
                                )}
                            </TableRow>
                        ))}
                        {impuestos.length === 0 && <TableRow className="hover:bg-transparent"><TableCell colSpan={5} className="text-muted-foreground h-20 text-center">No hay impuestos registrados.</TableCell></TableRow>}
                    </TableBody>
                </Table>
            </CardContent>
            {editando && <FormularioImpuesto key={editando.apertura} impuesto={editando.impuesto} url={url} onCerrar={() => setEditando(undefined)} />}
            <ConfirmarPeligro
                abierto={Boolean(eliminando)}
                onCerrar={() => setEliminando(undefined)}
                titulo={`¿Eliminar el impuesto ${eliminando?.nombre ?? ''}?`}
                descripcion="Deja de estar disponible. Si luego registras otro con el mismo código, se recupera este."
                onConfirmar={() => eliminando && router.delete(`${url}/${eliminando.id}`, mutar)}
            />
        </Card>
    );
}

function FormularioImpuesto({ impuesto, url, onCerrar }: { impuesto?: ImpuestoFila; url: string; onCerrar: () => void }) {
    const form = useForm({
        codigo: impuesto?.codigo ?? '',
        nombre: impuesto?.nombre ?? '',
        porcentaje: impuesto ? String(impuesto.porcentaje) : '',
        descripcion: impuesto?.descripcion ?? '',
        estado: impuesto?.estado ?? 'activo',
    });
    const iva = Boolean(impuesto?.es_iva);

    return (
        <DialogoFormulario
            abierto
            onCerrar={onCerrar}
            titulo={impuesto ? 'Editar impuesto' : 'Agregar impuesto'}
            sucio={form.isDirty}
            procesando={form.processing}
            textoGuardar={impuesto ? 'Guardar cambios' : 'Agregar impuesto'}
            onGuardar={() => (impuesto ? form.put(`${url}/${impuesto.id}`, { ...mutar, onSuccess: onCerrar }) : form.post(url, { ...mutar, onSuccess: onCerrar }))}
        >
            <div className="grid gap-4 sm:grid-cols-2">
                <Campo etiqueta="Código" requerido={!iva} error={form.errors.codigo} ayuda={iva ? 'El código del IVA no cambia.' : 'Se guarda en mayúsculas.'}>
                    <Input value={form.data.codigo} maxLength={20} disabled={iva} onChange={(e) => form.setData('codigo', e.target.value.toUpperCase())} className="font-mono" />
                </Campo>
                <Campo etiqueta="Porcentaje" requerido error={form.errors.porcentaje}>
                    <Input type="number" min={0} max={100} step="0.01" inputMode="decimal" value={form.data.porcentaje} onChange={(e) => form.setData('porcentaje', e.target.value)} className="tabular" />
                </Campo>
            </div>
            <Campo etiqueta="Nombre" requerido error={form.errors.nombre}>
                <Input value={form.data.nombre} maxLength={100} onChange={(e) => form.setData('nombre', e.target.value)} />
            </Campo>
            <Campo etiqueta="Descripción" error={form.errors.descripcion}>
                <Input value={form.data.descripcion} maxLength={255} onChange={(e) => form.setData('descripcion', e.target.value)} />
            </Campo>
            <div className="flex items-center gap-3">
                <Switch id="impuesto-activo" checked={form.data.estado === 'activo'} disabled={iva} onCheckedChange={(v) => form.setData('estado', v ? 'activo' : 'inactivo')} />
                <Label htmlFor="impuesto-activo">{iva ? 'El IVA siempre está activo' : 'Activo'}</Label>
            </div>
        </DialogoFormulario>
    );
}
