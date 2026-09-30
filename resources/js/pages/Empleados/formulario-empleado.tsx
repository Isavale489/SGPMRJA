import { router, useForm } from '@inertiajs/react';
import { Check, Link2, Plus, UserRoundCheck, X } from 'lucide-react';
import { useState } from 'react';

import { Campo } from '@/components/app/campo';
import { CampoTelefonos, telefonoVacio, type Telefono } from '@/components/app/campo-telefonos';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { confirmarDescarte, useGuardCambios } from '@/hooks/use-guard-cambios';
import { usePermisos } from '@/hooks/use-permisos';
import { hoyLocalIso } from '@/lib/formato';

import { GENERO, type EmpleadoFila, type Opcion, type PaginaEmpleados } from './tipos';

type Prefijo = 'V-' | 'E-' | 'J-' | 'G-';
const PREFIJOS: Prefijo[] = ['V-', 'E-', 'J-', 'G-'];
const SIN_GENERO = 'ninguno';

interface Formulario {
    tipo_documento: Prefijo;
    documento_identidad: string;
    nombre: string;
    email: string;
    telefonos: Telefono[];
    fecha_nacimiento: string;
    genero: '' | 'M' | 'F';
    direccion: string;
    estado_geografico: string;
    ciudad: string;
    departamento_id: string;
    cargo_id: string;
    fecha_ingreso: string;
}

/** Respuesta de empleados.check-documento. */
interface PersonaRegistrada {
    nombre: string;
    tipo_documento: Prefijo | null;
    email: string;
    telefonos: Telefono[];
    estado_geografico: string;
    ciudad: string;
    direccion: string;
}

function inicial(e?: EmpleadoFila): Formulario {
    return {
        tipo_documento: (e?.tipo_documento as Prefijo | null) ?? 'V-',
        documento_identidad: e?.numero_documento ?? '',
        nombre: e?.nombre ?? '',
        email: e?.email ?? '',
        telefonos: e?.telefonos.length ? e.telefonos : [telefonoVacio(true)],
        fecha_nacimiento: e?.fecha_nacimiento ?? '',
        genero: e?.genero ?? '',
        direccion: e?.direccion ?? '',
        estado_geografico: e?.estado_territorial ?? '',
        ciudad: e?.ciudad ?? '',
        departamento_id: e?.departamento_id ? String(e.departamento_id) : '',
        cargo_id: e?.cargo_id ? String(e.cargo_id) : '',
        fecha_ingreso: e?.fecha_ingreso ?? hoyLocalIso(),
    };
}

/**
 * Alta rápida de un departamento o cargo sin salir del formulario. Usa los
 * mismos endpoints que sus páginas; al volver, el nuevo queda elegido.
 */
function AltaRapida({ etiqueta, url, datos, recargar, onCreado }: {
    etiqueta: string; url: string; datos: Record<string, string>; recargar: string; onCreado: (lista: Opcion[], nombre: string) => void;
}) {
    const [abierta, setAbierta] = useState(false);
    const [nombre, setNombre] = useState('');
    const [error, setError] = useState<string>();
    const [enviando, setEnviando] = useState(false);

    const crear = () => {
        router.post(url, { ...datos, nombre }, {
            // `flash` también: si no, el aviso de éxito quedaría pendiente para la próxima página.
            only: [recargar, 'flash'],
            preserveState: true,
            preserveScroll: true,
            onStart: () => setEnviando(true),
            onFinish: () => setEnviando(false),
            onError: (e) => setError(e.nombre ?? Object.values(e)[0]),
            onSuccess: (pagina) => {
                onCreado(pagina.props[recargar] as Opcion[], nombre.trim());
                setAbierta(false);
                setNombre('');
                setError(undefined);
            },
        });
    };

    if (!abierta) {
        return (
            <Button type="button" variant="ghost" size="sm" className="justify-self-start" onClick={() => setAbierta(true)}>
                <Plus /> Nuevo {etiqueta}
            </Button>
        );
    }
    return (
        <div className="grid gap-1">
            <div className="flex gap-2">
                <Input
                    value={nombre}
                    autoFocus
                    maxLength={100}
                    aria-label={`Nombre del nuevo ${etiqueta}`}
                    aria-invalid={error ? true : undefined}
                    onChange={(e) => setNombre(e.target.value)}
                    // Enter no debe enviar el formulario del empleado.
                    onKeyDown={(e) => { if (e.key === 'Enter') { e.preventDefault(); crear(); } }}
                />
                <Button type="button" size="icon" disabled={enviando || !nombre.trim()} onClick={crear} aria-label={`Guardar ${etiqueta}`}><Check /></Button>
                <Button type="button" size="icon" variant="ghost" onClick={() => { setAbierta(false); setError(undefined); }} aria-label="Cancelar"><X /></Button>
            </div>
            {error && <p className="text-destructive text-xs">{error}</p>}
        </div>
    );
}

interface Props {
    abierto: boolean;
    onCerrar: () => void;
    empleado?: EmpleadoFila;
    departamentos: PaginaEmpleados['departamentos'];
    cargos: PaginaEmpleados['cargos'];
    estados: PaginaEmpleados['estados'];
    urls: PaginaEmpleados['urls'];
}

/** Montar con una `key` distinta en cada apertura (ver docs/conventions/frontend.md). */
export function FormularioEmpleado({ abierto, onCerrar, empleado, departamentos, cargos, estados, urls }: Props) {
    const edicion = Boolean(empleado);
    const { puede } = usePermisos();
    const form = useForm<Formulario>(inicial(empleado));
    const { data, setData, errors } = form;
    const [aviso, setAviso] = useState<{ documento?: string; email?: string }>({});
    // Persona ya registrada como cliente: se ofrece usar sus datos en vez de duplicarla.
    const [encontrada, setEncontrada] = useState<{ rol: string; persona: PersonaRegistrada }>();
    const [vinculada, setVinculada] = useState<string>();

    useGuardCambios(abierto && form.isDirty);

    form.transform((d) => ({
        ...(edicion ? { codigo_empleado: empleado?.codigo } : { tipo_documento: d.tipo_documento, documento_identidad: d.documento_identidad }),
        nombre: d.nombre.trim(),
        email: d.email.trim() || null,
        telefonos: d.telefonos,
        fecha_nacimiento: d.fecha_nacimiento || null,
        genero: d.genero || null,
        direccion: d.direccion.trim() || null,
        estado_geografico: d.estado_geografico || null,
        ciudad: d.ciudad || null,
        departamento_id: d.departamento_id,
        cargo_id: d.cargo_id,
        fecha_ingreso: d.fecha_ingreso,
    }));

    const cambiarDocumento = (cambios: Partial<Formulario>) => {
        setData((d) => ({ ...d, ...cambios }));
        setAviso((a) => ({ ...a, documento: undefined }));
        setEncontrada(undefined);
        setVinculada(undefined);
    };

    // Aviso temprano (el servidor igual valida al guardar).
    const revisarDocumento = async () => {
        if (edicion || data.documento_identidad.length < 6) return;
        try {
            const r = await fetch(`${urls.checkDocumento}?numero=${encodeURIComponent(data.documento_identidad)}`, { headers: { Accept: 'application/json' } });
            const res = (await r.json()) as { exists: boolean; other_role: string | null; persona: PersonaRegistrada | null };
            setAviso((a) => ({ ...a, documento: res.exists ? 'Este documento ya pertenece a un empleado registrado.' : undefined }));
            setEncontrada(!res.exists && res.other_role && res.persona ? { rol: res.other_role, persona: res.persona } : undefined);
        } catch {
            // sin red: el servidor valida al guardar
        }
    };

    const revisarEmail = async () => {
        const email = data.email.trim();
        if (!email || vinculada) return;
        const params = new URLSearchParams({ email });
        if (empleado) params.set('exclude_id', String(empleado.id));
        try {
            const r = await fetch(`${urls.checkEmail}?${params}`, { headers: { Accept: 'application/json' } });
            const { exists } = (await r.json()) as { exists: boolean };
            setAviso((a) => ({ ...a, email: exists ? 'Este correo ya está registrado.' : undefined }));
        } catch {
            // sin red: el servidor valida al guardar
        }
    };

    const usarDatos = () => {
        if (!encontrada) return;
        const p = encontrada.persona;
        setData((d) => ({
            ...d,
            tipo_documento: p.tipo_documento && PREFIJOS.includes(p.tipo_documento) ? p.tipo_documento : d.tipo_documento,
            nombre: p.nombre ?? '',
            email: p.email ?? '',
            telefonos: p.telefonos?.length ? p.telefonos.map((t) => ({ numero: t.numero, tipo: t.tipo, es_principal: Boolean(t.es_principal) })) : d.telefonos,
            direccion: p.direccion ?? '',
            estado_geografico: p.estado_geografico ?? '',
            ciudad: p.ciudad ?? '',
        }));
        setVinculada(encontrada.rol);
        setEncontrada(undefined);
        setAviso({});
    };

    const errorDe = (clave: string) => (errors as Record<string, string | undefined>)[clave];

    const guardar = (e: React.FormEvent) => {
        e.preventDefault();
        e.stopPropagation(); // abierto desde otra página (alta rápida): su submit no dispara el de la página
        const opciones = { preserveScroll: true, onSuccess: () => { form.setDefaults(); onCerrar(); } };
        if (empleado) form.put(`${urls.index}/${empleado.id}`, opciones);
        else form.post(urls.index, opciones);
    };

    const cerrar = (abrir: boolean) => {
        if (!abrir) void confirmarDescarte(form.isDirty).then((si) => si && onCerrar());
    };

    const municipios = estados[data.estado_geografico] ?? [];
    const cargosDelDepto = cargos.filter((c) => String(c.departamento_id) === data.departamento_id);
    const bloqueado = Boolean(vinculada);
    const elegirPorNombre = (lista: Opcion[], nombre: string) => lista.find((o) => o.nombre.toLowerCase() === nombre.toLowerCase());

    return (
        <Dialog open={abierto} onOpenChange={cerrar}>
            <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>{edicion ? `Editar empleado ${empleado?.codigo}` : 'Agregar empleado'}</DialogTitle>
                    <DialogDescription>{edicion ? 'El documento y el código no se pueden cambiar.' : 'El código se asigna solo al guardar.'}</DialogDescription>
                </DialogHeader>

                {empleado && empleado.otros_roles.length > 0 && (
                    <p className="bg-primary/8 text-primary ring-primary/20 flex items-start gap-2 rounded-md px-3 py-2 text-sm ring-1 ring-inset">
                        <UserRoundCheck className="mt-0.5 size-4 shrink-0" />
                        Esta persona también está registrada como {empleado.otros_roles.join(' y ')}. Los cambios en sus datos también se verán allí.
                    </p>
                )}

                <form id="form-empleado" onSubmit={guardar} className="grid gap-5" noValidate>
                    <section className="grid gap-4 sm:grid-cols-2">
                        <Campo etiqueta="Documento de identidad" requerido error={errorDe('documento_identidad') ?? aviso.documento}>
                            {(control) => (
                                <div className="flex gap-2">
                                    <Select value={data.tipo_documento} onValueChange={(v) => cambiarDocumento({ tipo_documento: v as Prefijo })} disabled={edicion || bloqueado}>
                                        <SelectTrigger className="w-20" aria-label="Prefijo del documento"><SelectValue /></SelectTrigger>
                                        <SelectContent>
                                            {PREFIJOS.map((p) => <SelectItem key={p} value={p}>{p}</SelectItem>)}
                                        </SelectContent>
                                    </Select>
                                    <Input
                                        {...control}
                                        inputMode="numeric"
                                        maxLength={9}
                                        placeholder="Número"
                                        value={data.documento_identidad}
                                        disabled={edicion}
                                        onChange={(e) => cambiarDocumento({ documento_identidad: e.target.value.replace(/\D/g, '').slice(0, 9) })}
                                        onBlur={revisarDocumento}
                                        className="tabular flex-1"
                                    />
                                </div>
                            )}
                        </Campo>
                        <Campo etiqueta="Nombre y apellido" requerido error={errorDe('nombre')}>
                            <Input value={data.nombre} maxLength={100} readOnly={bloqueado} onChange={(e) => setData('nombre', e.target.value)} />
                        </Campo>

                        {encontrada && (
                            <div className="border-primary/30 bg-primary/5 grid gap-2 rounded-md border p-3 text-sm sm:col-span-2">
                                <p>
                                    Esta persona ya está registrada como <strong>{encontrada.rol}</strong>: <strong>{encontrada.persona.nombre}</strong>
                                    {encontrada.persona.email && <span className="text-muted-foreground"> · {encontrada.persona.email}</span>}
                                </p>
                                <Button type="button" size="sm" className="justify-self-start" onClick={usarDatos}><Link2 /> Usar sus datos</Button>
                            </div>
                        )}
                        {vinculada && (
                            <p className="text-primary flex items-center gap-1.5 text-xs sm:col-span-2">
                                <Link2 className="size-3.5" /> Datos de la persona registrada como {vinculada}. Se editan desde ese módulo.
                            </p>
                        )}

                        <Campo etiqueta="Correo electrónico" error={errorDe('email') ?? aviso.email}>
                            <Input type="email" value={data.email} maxLength={255} readOnly={bloqueado} onChange={(e) => setData('email', e.target.value)} onBlur={revisarEmail} />
                        </Campo>
                        <div className="grid grid-cols-2 gap-4">
                            <Campo etiqueta="Fecha de nacimiento" error={errorDe('fecha_nacimiento')}>
                                <Input type="date" value={data.fecha_nacimiento} max={hoyLocalIso()} onChange={(e) => setData('fecha_nacimiento', e.target.value)} />
                            </Campo>
                            <Campo etiqueta="Género" error={errorDe('genero')}>
                                {(control) => (
                                    <Select value={data.genero || SIN_GENERO} onValueChange={(v) => setData('genero', v === SIN_GENERO ? '' : (v as 'M' | 'F'))}>
                                        <SelectTrigger {...control} className="w-full"><SelectValue /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value={SIN_GENERO}>Sin indicar</SelectItem>
                                            {Object.entries(GENERO).map(([v, t]) => <SelectItem key={v} value={v}>{t}</SelectItem>)}
                                        </SelectContent>
                                    </Select>
                                )}
                            </Campo>
                        </div>
                    </section>

                    <CampoTelefonos valor={data.telefonos} onChange={(t) => setData('telefonos', t)} errores={errors as Record<string, string | undefined>} />

                    <section className="grid gap-4 sm:grid-cols-3">
                        <div className="grid content-start gap-1">
                            <Campo etiqueta="Departamento" requerido error={errorDe('departamento_id')}>
                                {(control) => (
                                    <Select value={data.departamento_id || undefined} onValueChange={(v) => setData((d) => ({ ...d, departamento_id: v, cargo_id: '' }))}>
                                        <SelectTrigger {...control} className="w-full"><SelectValue placeholder="Selecciona" /></SelectTrigger>
                                        <SelectContent>
                                            {departamentos.map((d) => <SelectItem key={d.id} value={String(d.id)}>{d.nombre}</SelectItem>)}
                                        </SelectContent>
                                    </Select>
                                )}
                            </Campo>
                            {puede('departamentos.gestionar') && (
                                <AltaRapida
                                    etiqueta="departamento"
                                    url={urls.departamentos}
                                    datos={{}}
                                    recargar="departamentos"
                                    onCreado={(lista, nombre) => {
                                        const nuevo = elegirPorNombre(lista, nombre);
                                        if (nuevo) setData((d) => ({ ...d, departamento_id: String(nuevo.id), cargo_id: '' }));
                                    }}
                                />
                            )}
                        </div>
                        <div className="grid content-start gap-1">
                            <Campo etiqueta="Cargo" requerido error={errorDe('cargo_id')}>
                                {(control) => (
                                    <Select value={data.cargo_id || undefined} onValueChange={(v) => setData('cargo_id', v)} disabled={!data.departamento_id}>
                                        <SelectTrigger {...control} className="w-full">
                                            <SelectValue placeholder={data.departamento_id ? (cargosDelDepto.length ? 'Selecciona' : 'Sin cargos') : 'Elige un departamento'} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {cargosDelDepto.map((c) => <SelectItem key={c.id} value={String(c.id)}>{c.nombre}</SelectItem>)}
                                        </SelectContent>
                                    </Select>
                                )}
                            </Campo>
                            {puede('cargos.gestionar') && data.departamento_id && (
                                <AltaRapida
                                    key={data.departamento_id}
                                    etiqueta="cargo"
                                    url={urls.cargos}
                                    datos={{ departamento_id: data.departamento_id }}
                                    recargar="cargos"
                                    onCreado={(lista, nombre) => {
                                        const nuevo = (lista as PaginaEmpleados['cargos']).find((c) => String(c.departamento_id) === data.departamento_id && c.nombre.toLowerCase() === nombre.toLowerCase());
                                        if (nuevo) setData('cargo_id', String(nuevo.id));
                                    }}
                                />
                            )}
                        </div>
                        <Campo etiqueta="Fecha de ingreso" requerido error={errorDe('fecha_ingreso')}>
                            <Input type="date" value={data.fecha_ingreso} max={hoyLocalIso()} onChange={(e) => setData('fecha_ingreso', e.target.value)} />
                        </Campo>
                    </section>

                    <section className="grid gap-4 sm:grid-cols-3">
                        <Campo etiqueta="Dirección" error={errorDe('direccion')} className="sm:col-span-3">
                            <Input value={data.direccion} maxLength={500} onChange={(e) => setData('direccion', e.target.value)} />
                        </Campo>
                        <Campo etiqueta="Estado" error={errorDe('estado_geografico')} className="sm:col-span-1">
                            {(control) => (
                                <Select value={data.estado_geografico || undefined} onValueChange={(v) => setData((d) => ({ ...d, estado_geografico: v, ciudad: '' }))}>
                                    <SelectTrigger {...control} className="w-full"><SelectValue placeholder="Selecciona un estado" /></SelectTrigger>
                                    <SelectContent>
                                        {Object.keys(estados).map((e) => <SelectItem key={e} value={e}>{e}</SelectItem>)}
                                    </SelectContent>
                                </Select>
                            )}
                        </Campo>
                        <Campo etiqueta="Municipio" error={errorDe('ciudad')} className="sm:col-span-2">
                            {(control) => (
                                <Select value={data.ciudad || undefined} onValueChange={(v) => setData('ciudad', v)} disabled={!municipios.length}>
                                    <SelectTrigger {...control} className="w-full">
                                        <SelectValue placeholder={municipios.length ? 'Selecciona un municipio' : 'Primero selecciona un estado'} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {municipios.map((m) => <SelectItem key={m} value={m}>{m}</SelectItem>)}
                                    </SelectContent>
                                </Select>
                            )}
                        </Campo>
                    </section>
                </form>

                <DialogFooter>
                    <Button type="button" variant="outline" onClick={() => cerrar(false)}>Cancelar</Button>
                    <Button type="submit" form="form-empleado" disabled={form.processing || Boolean(aviso.documento) || Boolean(encontrada)}>
                        {form.processing ? 'Guardando…' : edicion ? 'Guardar cambios' : 'Agregar empleado'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
