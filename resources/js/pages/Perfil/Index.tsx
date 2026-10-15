import { useForm } from '@inertiajs/react';
import { AlertTriangle, Camera, KeyRound, Lock, Mail, Pencil, ShieldCheck, ShieldQuestion, Upload } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

import { Campo } from '@/components/app/campo';
import { DialogoFormulario } from '@/components/app/dialogo-formulario';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { formatoFecha, iniciales } from '@/lib/formato';
import { POLITICA_CONTRASENA } from '@/lib/contrasena';
import { cn } from '@/lib/utils';

/** Espejo de ProfileController::edit() (lo verifica PerfilPaginaTest). */
export interface PaginaPerfil {
    usuario: { name: string; email: string; avatar: string | null; rol: string | null; activo: boolean; desde: string | null };
    catalogo: { id: number; texto: string }[];
    preguntas: { orden: number; pregunta_id: number }[];
    configuradas: boolean;
    debeReconfigurar: boolean;
    forzado: string | null;
    sinCambios: boolean;
    urls: { perfil: string; contrasena: string; preguntas: string; avatar: string };
}

type Dialogo = 'datos' | 'contrasena' | 'preguntas' | 'foto';

export default function Perfil({ usuario, catalogo, preguntas, configuradas, debeReconfigurar, forzado, sinCambios, urls }: PaginaPerfil) {
    // Sin preguntas (o tras una recuperación) el sistema trae aquí: se abre el formulario directo.
    const [abierto, setAbierto] = useState<{ cual: Dialogo; n: number } | undefined>(() => (!configuradas || debeReconfigurar || forzado ? { cual: 'preguntas', n: 1 } : undefined));
    const abrir = (cual: Dialogo) => setAbierto((a) => ({ cual, n: (a?.n ?? 0) + 1 }));
    const cerrar = () => setAbierto(undefined);

    const estadoPreguntas = debeReconfigurar
        ? { texto: 'Debes reconfigurarlas', clase: 'bg-warning/15 text-warning' }
        : configuradas ? { texto: 'Configuradas', clase: 'bg-success/12 text-success' } : { texto: 'Pendientes', clase: 'bg-destructive/10 text-destructive' };

    return (
        <AppLayout titulo="Mi perfil">
            <div className="grid max-w-4xl gap-4">
                {forzado && (
                    <p role="alert" className="border-warning/30 bg-warning/10 flex items-start gap-2 rounded-lg border p-3 text-sm">
                        <AlertTriangle className="text-warning mt-0.5 size-4 shrink-0" /> {forzado}
                    </p>
                )}

                <Card>
                    <CardContent className="flex flex-wrap items-center gap-4">
                        <button type="button" onClick={() => abrir('foto')} className="group relative rounded-full" aria-label="Cambiar foto de perfil">
                            <Avatar className="size-20">
                                {usuario.avatar && <AvatarImage src={usuario.avatar} alt="" />}
                                <AvatarFallback className="bg-primary text-primary-foreground text-2xl">{iniciales(usuario.name)}</AvatarFallback>
                            </Avatar>
                            <span className="bg-background absolute right-0 bottom-0 grid size-7 place-items-center rounded-full border shadow-sm"><Camera className="size-3.5" /></span>
                        </button>
                        <div className="min-w-0 flex-1">
                            <p className="text-lg font-semibold">{usuario.name}</p>
                            <p className="text-muted-foreground flex items-center gap-1 text-sm"><Mail className="size-3.5" /> {usuario.email}</p>
                            <p className="text-muted-foreground mt-1 flex flex-wrap gap-x-3 text-xs">
                                <span className="inline-flex items-center gap-1"><ShieldCheck className="size-3.5" /> {usuario.rol ?? '—'}</span>
                                <span>{usuario.activo ? 'Cuenta activa' : 'Cuenta inhabilitada'}</span>
                                {usuario.desde && <span>Desde {formatoFecha(usuario.desde)}</span>}
                            </p>
                        </div>
                        <Button variant="outline" onClick={() => abrir('datos')}><Pencil /> Editar datos</Button>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-base"><KeyRound className="size-4" /> Contraseña</CardTitle>
                        <CardDescription>Cámbiala de vez en cuando y no la uses en otros sitios.</CardDescription>
                    </CardHeader>
                    <CardContent><Button variant="outline" onClick={() => abrir('contrasena')}><Lock /> Cambiar contraseña</Button></CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex flex-wrap items-center gap-2 text-base">
                            <ShieldQuestion className="size-4" /> Preguntas de seguridad
                            <span className={cn('rounded-full px-2 py-0.5 text-xs font-normal', estadoPreguntas.clase)}>{estadoPreguntas.texto}</span>
                        </CardTitle>
                        <CardDescription>Si olvidas tu contraseña y no tienes internet, recuperas la cuenta respondiendo tus 3 preguntas.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {sinCambios && <p className="text-muted-foreground mb-3 text-sm">No cambiaste ninguna pregunta.</p>}
                        <Button variant="outline" onClick={() => abrir('preguntas')}>{configuradas ? 'Actualizar preguntas' : 'Configurar preguntas'}</Button>
                    </CardContent>
                </Card>
            </div>

            {abierto?.cual === 'datos' && <FormularioDatos key={abierto.n} usuario={usuario} url={urls.perfil} onCerrar={cerrar} />}
            {abierto?.cual === 'contrasena' && <FormularioContrasena key={abierto.n} url={urls.contrasena} onCerrar={cerrar} />}
            {abierto?.cual === 'preguntas' && <FormularioPreguntas key={abierto.n} catalogo={catalogo} preguntas={preguntas} configuradas={configuradas} reconfigurar={configuradas && debeReconfigurar} url={urls.preguntas} onCerrar={cerrar} />}
            {abierto?.cual === 'foto' && <FormularioFoto key={abierto.n} actual={usuario.avatar} nombre={usuario.name} url={urls.avatar} onCerrar={cerrar} />}
        </AppLayout>
    );
}

const opciones = (onCerrar: () => void) => ({ preserveScroll: true, onSuccess: onCerrar });

function FormularioDatos({ usuario, url, onCerrar }: { usuario: PaginaPerfil['usuario']; url: string; onCerrar: () => void }) {
    const form = useForm({ name: usuario.name, email: usuario.email, current_password: '' });
    // Cambiar el correo (vía de recuperación de la cuenta) pide la contraseña.
    const cambiaCorreo = form.data.email.trim().toLowerCase() !== usuario.email.toLowerCase();
    form.transform((d) => ({ name: d.name, email: d.email, ...(cambiaCorreo ? { current_password: d.current_password } : {}) }));
    return (
        <DialogoFormulario abierto onCerrar={onCerrar} titulo="Editar datos" sucio={form.isDirty} procesando={form.processing} textoGuardar="Guardar cambios" onGuardar={() => form.patch(url, opciones(onCerrar))}>
            <Campo etiqueta="Nombre" requerido error={form.errors.name}>
                <Input value={form.data.name} maxLength={255} autoComplete="name" onChange={(e) => form.setData('name', e.target.value)} />
            </Campo>
            <Campo etiqueta="Correo electrónico" requerido error={form.errors.email}>
                <Input type="email" value={form.data.email} maxLength={255} autoComplete="email" onChange={(e) => form.setData('email', e.target.value)} />
            </Campo>
            {cambiaCorreo && (
                <Campo etiqueta="Tu contraseña actual" requerido error={form.errors.current_password} ayuda="Para confirmar el cambio de correo.">
                    <Input type="password" autoComplete="current-password" value={form.data.current_password} onChange={(e) => form.setData('current_password', e.target.value)} />
                </Campo>
            )}
        </DialogoFormulario>
    );
}

function FormularioContrasena({ url, onCerrar }: { url: string; onCerrar: () => void }) {
    const form = useForm({ current_password: '', password: '', password_confirmation: '' });
    return (
        <DialogoFormulario
            abierto
            onCerrar={onCerrar}
            titulo="Cambiar contraseña"
            sucio={form.isDirty}
            procesando={form.processing}
            textoGuardar="Cambiar contraseña"
            // PasswordController valida en la bolsa de errores 'updatePassword'.
            onGuardar={() => form.put(url, { ...opciones(onCerrar), errorBag: 'updatePassword', onError: () => form.reset('password', 'password_confirmation') })}
        >
            <Campo etiqueta="Contraseña actual" requerido error={form.errors.current_password}>
                <Input type="password" autoComplete="current-password" value={form.data.current_password} onChange={(e) => form.setData('current_password', e.target.value)} />
            </Campo>
            <Campo etiqueta="Contraseña nueva" requerido error={form.errors.password} ayuda={POLITICA_CONTRASENA}>
                <Input type="password" autoComplete="new-password" value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} />
            </Campo>
            <Campo etiqueta="Confirmar contraseña nueva" requerido error={form.errors.password_confirmation}>
                <Input type="password" autoComplete="new-password" value={form.data.password_confirmation} onChange={(e) => form.setData('password_confirmation', e.target.value)} />
            </Campo>
        </DialogoFormulario>
    );
}

interface Bloque {
    editing: boolean;
    pregunta_id: string;
    respuesta: string;
}

/**
 * Las 3 preguntas. Configuración inicial: las 3 obligatorias. Edición: se
 * cambian solo los bloques elegidos y se pide la contraseña actual.
 * Reconfiguración obligatoria: las 3 abiertas (con su pregunta actual) y con
 * contraseña. Las respuestas nunca vuelven del servidor (están cifradas).
 */
function FormularioPreguntas({
    catalogo,
    preguntas,
    configuradas,
    reconfigurar,
    url,
    onCerrar,
}: {
    catalogo: PaginaPerfil['catalogo'];
    preguntas: PaginaPerfil['preguntas'];
    configuradas: boolean;
    reconfigurar: boolean;
    url: string;
    onCerrar: () => void;
}) {
    const actual = (orden: number) => preguntas.find((p) => p.orden === orden)?.pregunta_id;
    const form = useForm<{ bloques: Bloque[]; current_password: string }>({
        bloques: [1, 2, 3].map((o) => ({ editing: !configuradas || reconfigurar, pregunta_id: configuradas ? String(actual(o) ?? '') : '', respuesta: '' })),
        current_password: '',
    });
    const e = form.errors as Record<string, string | undefined>;
    const alguno = form.data.bloques.some((b) => b.editing);
    const texto = (id?: number | string) => catalogo.find((c) => String(c.id) === String(id))?.texto ?? '—';
    const cambiar = (i: number, c: Partial<Bloque>) => form.setData('bloques', form.data.bloques.map((b, k) => (k === i ? { ...b, ...c } : b)));

    form.transform((d) => ({
        cambios: d.bloques.map((b) => (b.editing ? { editing: '1', pregunta_id: b.pregunta_id, respuesta: b.respuesta } : { editing: '0' })),
        ...(configuradas ? { current_password: d.current_password } : {}),
    }));

    return (
        <DialogoFormulario
            abierto
            onCerrar={onCerrar}
            titulo={configuradas ? 'Actualizar preguntas de seguridad' : 'Configurar preguntas de seguridad'}
            descripcion={reconfigurar ? 'Por seguridad, vuelve a responder las 3 preguntas.' : configuradas ? 'Cambia solo los bloques que quieras.' : 'Elige 3 preguntas distintas y respóndelas. No distingue mayúsculas.'}
            sucio={form.isDirty}
            procesando={form.processing}
            textoGuardar="Guardar preguntas"
            onGuardar={() => form.patch(url, opciones(onCerrar))}
            className="max-h-[92svh] overflow-y-auto sm:max-w-xl"
        >
            {e.cambios && <p className="text-destructive text-sm" role="alert">{e.cambios}</p>}
            {form.data.bloques.map((b, i) => {
                const usadas = form.data.bloques.filter((_, k) => k !== i).map((x) => x.pregunta_id);
                const err = (c: string) => e[`cambios.${i}.${c}`] ?? (c === 'respuesta' ? e[`cambios.${i + 1}`] : undefined);
                return (
                    <fieldset key={i} className="grid gap-3 rounded-lg border p-3">
                        <legend className="px-1 text-sm font-medium">Pregunta {i + 1}</legend>
                        {!b.editing ? (
                            <div className="flex flex-wrap items-center justify-between gap-2 text-sm">
                                <div>
                                    <p>{texto(b.pregunta_id)}</p>
                                    <p className="text-muted-foreground text-xs">●●●●●●●● · respuesta cifrada</p>
                                </div>
                                <Button type="button" variant="ghost" size="sm" onClick={() => cambiar(i, { editing: true, respuesta: '' })}><Pencil /> Cambiar</Button>
                            </div>
                        ) : (
                            <>
                                <Campo etiqueta="Pregunta" requerido error={err('pregunta_id')}>
                                    {(control) => (
                                        <Select value={b.pregunta_id || undefined} onValueChange={(v) => cambiar(i, { pregunta_id: v })}>
                                            <SelectTrigger {...control} className="w-full"><SelectValue placeholder="Elige una pregunta" /></SelectTrigger>
                                            <SelectContent>
                                                {catalogo.filter((c) => !usadas.includes(String(c.id))).map((c) => <SelectItem key={c.id} value={String(c.id)}>{c.texto}</SelectItem>)}
                                            </SelectContent>
                                        </Select>
                                    )}
                                </Campo>
                                <Campo etiqueta="Respuesta" requerido error={err('respuesta')}>
                                    <Input value={b.respuesta} maxLength={255} autoComplete="off" placeholder="Tu respuesta" onChange={(ev) => cambiar(i, { respuesta: ev.target.value })} />
                                </Campo>
                                {configuradas && !reconfigurar && (
                                    <Button type="button" variant="ghost" size="sm" className="justify-self-start" onClick={() => cambiar(i, { editing: false, pregunta_id: String(actual(i + 1) ?? ''), respuesta: '' })}>No cambiar esta</Button>
                                )}
                            </>
                        )}
                    </fieldset>
                );
            })}
            {configuradas && alguno && (
                <Campo etiqueta="Tu contraseña actual" requerido error={e.current_password} ayuda="Para confirmar que eres tú.">
                    <Input type="password" autoComplete="current-password" value={form.data.current_password} onChange={(ev) => form.setData('current_password', ev.target.value)} />
                </Campo>
            )}
        </DialogoFormulario>
    );
}

function FormularioFoto({ actual, nombre, url, onCerrar }: { actual: string | null; nombre: string; url: string; onCerrar: () => void }) {
    const form = useForm<{ avatar: File | null }>({ avatar: null });
    const entrada = useRef<HTMLInputElement>(null);
    const [vista, setVista] = useState<string | null>(actual);
    const [arrastrando, setArrastrando] = useState(false);

    useEffect(() => () => { if (vista && vista !== actual) URL.revokeObjectURL(vista); }, [vista, actual]);
    const elegir = (f?: File | null) => {
        if (!f) return;
        if (!f.type.startsWith('image/')) {
            // Se descarta también lo elegido antes: no se sube una foto con el error a la vista.
            form.setData('avatar', null);
            setVista(actual);
            form.setError('avatar', 'El archivo debe ser una imagen.');
            return;
        }
        form.clearErrors('avatar');
        form.setData('avatar', f);
        setVista(URL.createObjectURL(f));
    };

    return (
        <DialogoFormulario
            abierto
            onCerrar={onCerrar}
            titulo="Cambiar foto de perfil"
            sucio={Boolean(form.data.avatar)}
            procesando={form.processing}
            textoGuardar="Guardar foto"
            onGuardar={() => form.post(url, { ...opciones(onCerrar), forceFormData: true })}
        >
            <button
                type="button"
                onClick={() => entrada.current?.click()}
                onDragOver={(ev) => { ev.preventDefault(); setArrastrando(true); }}
                onDragLeave={() => setArrastrando(false)}
                onDrop={(ev) => { ev.preventDefault(); setArrastrando(false); elegir(ev.dataTransfer.files[0]); }}
                className={cn('grid justify-items-center gap-3 rounded-lg border-2 border-dashed p-6 text-center text-sm transition-colors', arrastrando ? 'border-primary bg-primary/5' : 'hover:bg-muted/50')}
            >
                <Avatar className="size-24">
                    {vista && <AvatarImage src={vista} alt="" />}
                    <AvatarFallback className="bg-primary text-primary-foreground text-3xl">{iniciales(nombre)}</AvatarFallback>
                </Avatar>
                <span><Upload className="mr-1 inline size-4" /><strong>Elige una imagen</strong> o arrástrala aquí</span>
                <span className="text-muted-foreground text-xs">JPG, PNG o GIF · máximo 2 MB</span>
            </button>
            <input ref={entrada} type="file" accept="image/png,image/jpeg,image/gif" className="sr-only" aria-label="Archivo de la foto" onChange={(ev) => elegir(ev.target.files?.[0])} />
            {form.errors.avatar && <p className="text-destructive text-sm">{form.errors.avatar}</p>}
        </DialogoFormulario>
    );
}
