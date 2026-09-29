import { useForm } from '@inertiajs/react';
import { Eye, EyeOff, ImageUp, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

import { Campo, type PropsControl } from '@/components/app/campo';
import { DialogoFormulario } from '@/components/app/dialogo-formulario';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { POLITICA_CONTRASENA } from '@/lib/contrasena';

import { iniciales, type PaginaUsuarios, type UsuarioFila } from './tipos';

/** Contraseña con botón para mostrarla. */
function Clave({ valor, onCambiar, autoComplete, ...control }: Partial<PropsControl> & { valor: string; onCambiar: (v: string) => void; autoComplete: string }) {
    const [visible, setVisible] = useState(false);
    return (
        <div className="relative">
            <Input {...control} type={visible ? 'text' : 'password'} value={valor} autoComplete={autoComplete} maxLength={191} className="pr-10" onChange={(e) => onCambiar(e.target.value)} />
            <Button
                type="button"
                variant="ghost"
                size="icon"
                className="absolute top-1/2 right-0.5 size-8 -translate-y-1/2"
                aria-label={visible ? 'Ocultar contraseña' : 'Mostrar contraseña'}
                onClick={() => setVisible((v) => !v)}
            >
                {visible ? <EyeOff /> : <Eye />}
            </Button>
        </div>
    );
}

/** Foto de perfil: vista previa redonda, elegir o soltar un archivo. El servidor valida tipo y tamaño. */
function CampoAvatar({ nombre, actual, archivo, error, onCambiar }: {
    nombre: string; actual: string | null; archivo: File | null; error?: string; onCambiar: (f: File | null) => void;
}) {
    const input = useRef<HTMLInputElement>(null);
    const [vista, setVista] = useState<string | null>(null);
    const [encima, setEncima] = useState(false);

    useEffect(() => {
        if (!archivo) return setVista(null);
        const url = URL.createObjectURL(archivo);
        setVista(url);
        return () => URL.revokeObjectURL(url);
    }, [archivo]);

    return (
        <div className="grid gap-1.5">
            <div
                className={`border-border flex items-center gap-4 rounded-lg border border-dashed p-3 transition-colors duration-rapido ${encima ? 'border-primary bg-primary/5' : ''}`}
                onDragOver={(e) => { e.preventDefault(); setEncima(true); }}
                onDragLeave={() => setEncima(false)}
                onDrop={(e) => { e.preventDefault(); setEncima(false); const f = e.dataTransfer.files[0]; if (f) onCambiar(f); }}
            >
                <Avatar className="size-16">
                    {(vista ?? actual) && <AvatarImage src={vista ?? actual ?? undefined} alt="" className="object-cover" />}
                    <AvatarFallback className="text-lg">{iniciales(nombre || '?')}</AvatarFallback>
                </Avatar>
                <div className="grid min-w-0 gap-1">
                    <p className="text-sm font-medium">Foto de perfil</p>
                    <p className="text-muted-foreground truncate text-xs">{archivo ? archivo.name : 'JPG, PNG o GIF, hasta 2 MB. Puedes soltarla aquí.'}</p>
                    <div className="flex gap-2">
                        <Button type="button" variant="outline" size="sm" onClick={() => input.current?.click()}><ImageUp /> Elegir foto</Button>
                        {archivo && <Button type="button" variant="ghost" size="sm" onClick={() => onCambiar(null)}><X /> Quitar</Button>}
                    </div>
                </div>
                <input
                    ref={input}
                    type="file"
                    accept="image/jpeg,image/png,image/gif"
                    className="sr-only"
                    aria-label="Foto de perfil"
                    onChange={(e) => onCambiar(e.target.files?.[0] ?? null)}
                />
            </div>
            {error && <p className="text-destructive text-xs">{error}</p>}
        </div>
    );
}

export function FormularioUsuario({ abierto, onCerrar, usuario, roles, urls }: {
    abierto: boolean; onCerrar: () => void; usuario?: UsuarioFila; roles: PaginaUsuarios['roles']; urls: PaginaUsuarios['urls'];
}) {
    const form = useForm<{ name: string; email: string; role_id: string; password: string; password_confirmation: string; avatar: File | null; _method?: string }>({
        name: usuario?.nombre ?? '',
        email: usuario?.email ?? '',
        role_id: usuario?.rol_id ? String(usuario.rol_id) : '',
        password: '',
        password_confirmation: '',
        avatar: null,
    });
    const { data, setData, errors } = form;
    const [correoRepetido, setCorreoRepetido] = useState(false);
    const opciones = { preserveScroll: true, onSuccess: onCerrar };

    const revisarCorreo = async () => {
        const email = data.email.trim();
        if (!email) return setCorreoRepetido(false);
        const params = new URLSearchParams({ email });
        if (usuario) params.set('exclude_id', String(usuario.id));
        try {
            const r = await fetch(`${urls.checkEmail}?${params}`, { headers: { Accept: 'application/json' } });
            setCorreoRepetido(((await r.json()) as { exists: boolean }).exists);
        } catch {
            // sin red: el servidor valida al guardar
        }
    };

    const guardar = () => {
        if (!usuario) return form.post(urls.index, { ...opciones, forceFormData: true });
        // Con archivo, PUT va como POST + _method (PHP no lee multipart en PUT).
        form.transform((d) => ({ name: d.name, email: d.email, role_id: d.role_id, avatar: d.avatar, _method: 'put' }));
        form.post(`${urls.index}/${usuario.id}`, { ...opciones, forceFormData: true });
    };

    return (
        <DialogoFormulario
            abierto={abierto}
            onCerrar={onCerrar}
            titulo={usuario ? 'Editar usuario' : 'Agregar usuario'}
            descripcion={usuario ? 'La contraseña no se cambia aquí: usa "Resetear contraseña".' : 'Nace activo. Configurará sus preguntas de seguridad al entrar.'}
            sucio={form.isDirty}
            procesando={form.processing}
            textoGuardar={usuario ? 'Guardar cambios' : 'Agregar usuario'}
            onGuardar={guardar}
            className="max-h-[92svh] overflow-y-auto sm:max-w-lg"
        >
            <CampoAvatar nombre={data.name} actual={usuario?.avatar ?? null} archivo={data.avatar} error={errors.avatar} onCambiar={(f) => setData('avatar', f)} />
            <Campo etiqueta="Nombre" requerido error={errors.name}>
                <Input value={data.name} maxLength={255} autoComplete="off" onChange={(e) => setData('name', e.target.value)} />
            </Campo>
            <Campo etiqueta="Correo electrónico" requerido error={errors.email ?? (correoRepetido ? 'Este correo ya está registrado.' : undefined)}>
                <Input type="email" value={data.email} maxLength={255} autoComplete="off" onChange={(e) => setData('email', e.target.value)} onBlur={revisarCorreo} />
            </Campo>
            <Campo etiqueta="Rol" requerido error={errors.role_id}>
                {(control) => (
                    <Select value={data.role_id || undefined} onValueChange={(v) => setData('role_id', v)}>
                        <SelectTrigger {...control} className="w-full"><SelectValue placeholder="Selecciona un rol" /></SelectTrigger>
                        <SelectContent>
                            {roles.map((r) => <SelectItem key={r.id} value={String(r.id)}>{r.nombre}</SelectItem>)}
                        </SelectContent>
                    </Select>
                )}
            </Campo>
            {!usuario && (
                <div className="grid gap-4 sm:grid-cols-2">
                    <Campo etiqueta="Contraseña" requerido error={errors.password} ayuda={POLITICA_CONTRASENA}>
                        {(control) => <Clave {...control} valor={data.password} autoComplete="new-password" onCambiar={(v) => setData('password', v)} />}
                    </Campo>
                    <Campo etiqueta="Confirmar contraseña" requerido>
                        {(control) => <Clave {...control} valor={data.password_confirmation} autoComplete="new-password" onCambiar={(v) => setData('password_confirmation', v)} />}
                    </Campo>
                </div>
            )}
        </DialogoFormulario>
    );
}

export function ResetearClave({ usuario, url, onCerrar }: { usuario: UsuarioFila; url: string; onCerrar: () => void }) {
    const form = useForm({ password: '', password_confirmation: '' });

    return (
        <DialogoFormulario
            abierto
            onCerrar={onCerrar}
            titulo="Resetear contraseña"
            descripcion={`Asigna una contraseña temporal a ${usuario.nombre} (${usuario.email}). Deberá cambiarla y configurar de nuevo sus preguntas de seguridad al entrar.`}
            sucio={form.isDirty}
            procesando={form.processing}
            textoGuardar="Resetear contraseña"
            onGuardar={() => form.post(`${url}/${usuario.id}/reset-password`, { preserveScroll: true, onSuccess: onCerrar })}
        >
            <Campo etiqueta="Contraseña temporal" requerido error={form.errors.password} ayuda={POLITICA_CONTRASENA}>
                {(control) => <Clave {...control} valor={form.data.password} autoComplete="new-password" onCambiar={(v) => form.setData('password', v)} />}
            </Campo>
            <Campo etiqueta="Confirmar contraseña temporal" requerido>
                {(control) => <Clave {...control} valor={form.data.password_confirmation} autoComplete="new-password" onCambiar={(v) => form.setData('password_confirmation', v)} />}
            </Campo>
        </DialogoFormulario>
    );
}
