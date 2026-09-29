import { useForm } from '@inertiajs/react';
import { Link2, UserRoundCheck } from 'lucide-react';
import { useState } from 'react';

import { Campo } from '@/components/app/campo';
import { CampoTelefonos, telefonoVacio, type Telefono } from '@/components/app/campo-telefonos';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { confirmarDescarte, useGuardCambios } from '@/hooks/use-guard-cambios';

import type { ClienteCotizacion } from '@/pages/Cotizaciones/tipos';

import { ETIQUETA_TIPO, type ClienteFila, type PaginaClientes, type TipoCliente } from './tipos';

type Prefijo = 'V-' | 'E-' | 'J-' | 'G-';
const PREFIJOS: Prefijo[] = ['V-', 'E-', 'J-', 'G-'];

/** Regla del sistema: el tipo NO se elige, lo define el prefijo del documento. */
const tipoDesdePrefijo = (p: Prefijo): TipoCliente => (p === 'J-' ? 'juridico' : p === 'G-' ? 'gubernamental' : 'natural');
/** Cédula hasta 8 dígitos; RIF hasta 9. */
const largoDocumento = (p: Prefijo) => (p === 'J-' || p === 'G-' ? 9 : 8);

interface Formulario {
    tipo_documento: Prefijo;
    numero: string;
    nombre: string;
    email: string;
    telefonos: Telefono[];
    direccion: string;
    estado_territorial: string;
    ciudad: string;
}

/** Respuesta de clientes.check-documento (el mismo contrato que usa Cotizaciones). */
interface PersonaRegistrada {
    nombre: string;
    tipo_documento: Prefijo | null;
    email: string;
    telefonos: Telefono[];
    estado_geografico: string;
    ciudad: string;
    direccion: string;
}

function inicial(c?: ClienteFila): Formulario {
    return {
        tipo_documento: (c?.tipo_documento as Prefijo | null) ?? 'V-',
        numero: c?.numero_documento ?? '',
        nombre: c?.nombre ?? '',
        email: c?.email ?? '',
        telefonos: c?.telefonos.length ? c.telefonos : [telefonoVacio(true)],
        direccion: c?.direccion ?? '',
        estado_territorial: c?.estado_territorial ?? '',
        ciudad: c?.ciudad ?? '',
    };
}

interface Props {
    abierto: boolean;
    onCerrar: () => void;
    /** Cliente a editar; sin él, es un alta. */
    cliente?: ClienteFila;
    estados: PaginaClientes['estados'];
    urls: Pick<PaginaClientes['urls'], 'index' | 'checkDocumento' | 'checkEmail'>;
    /**
     * Alta rápida desde otro módulo (el asistente de Cotizaciones): recibe la
     * tarjeta del cliente creado (flash `cliente`) sin remontar la página.
     */
    onCreado?: (cliente: ClienteCotizacion) => void;
}

/** Montar con una `key` distinta en cada apertura (ver docs/conventions/frontend.md). */
export function FormularioCliente({ abierto, onCerrar, cliente, estados, urls, onCreado }: Props) {
    const edicion = Boolean(cliente);
    const form = useForm<Formulario>(inicial(cliente));
    const { data, setData, errors } = form;
    const tipo = tipoDesdePrefijo(data.tipo_documento);
    const natural = tipo === 'natural';
    const [aviso, setAviso] = useState<{ documento?: string; email?: string }>({});
    // Persona ya registrada en otro rol (empleado): se ofrece usar sus datos en vez de duplicarla.
    const [encontrada, setEncontrada] = useState<{ rol: string; persona: PersonaRegistrada }>();
    const [vinculada, setVinculada] = useState<string>();

    useGuardCambios(abierto && form.isDirty);

    // Estado del formulario → payload que conoce el servidor (el mismo que envían Cotizaciones y Pedidos).
    form.transform((d) => ({
        documento: `${d.tipo_documento}${d.numero}`,
        tipo_cliente: tipo,
        nombre: d.nombre.trim(),
        email: d.email.trim() || null,
        telefonos: d.telefonos,
        direccion: d.direccion.trim() || null,
        estado_territorial: d.estado_territorial || null,
        ciudad: d.ciudad || null,
    }));

    const cambiarDocumento = (cambios: Partial<Formulario>) => {
        setData((d) => ({ ...d, ...cambios }));
        setAviso((a) => ({ ...a, documento: undefined }));
        setEncontrada(undefined);
        setVinculada(undefined);
    };

    // Aviso temprano (el servidor igual valida al guardar).
    const revisarDocumento = async () => {
        if (edicion || data.numero.length < 6) return;
        try {
            const r = await fetch(`${urls.checkDocumento}?numero=${encodeURIComponent(data.numero)}`, { headers: { Accept: 'application/json' } });
            const res = (await r.json()) as { exists: boolean; other_role: string | null; persona: PersonaRegistrada | null };
            setAviso((a) => ({ ...a, documento: res.exists ? 'Este documento ya está registrado como cliente.' : undefined }));
            setEncontrada(!res.exists && res.other_role && res.persona ? { rol: res.other_role, persona: res.persona } : undefined);
        } catch {
            // sin red: el servidor valida al guardar
        }
    };

    const revisarEmail = async () => {
        const email = data.email.trim();
        if (!email || vinculada) return;
        const params = new URLSearchParams({ email });
        if (cliente) params.set('exclude_id', String(cliente.id));
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
            estado_territorial: p.estado_geografico ?? '',
            ciudad: p.ciudad ?? '',
        }));
        setVinculada(encontrada.rol);
        setEncontrada(undefined);
        setAviso({});
    };

    const errorDe = (clave: string) => (errors as Record<string, string | undefined>)[clave];

    const guardar = (e: React.FormEvent) => {
        e.preventDefault();
        const opciones = { preserveScroll: true, onSuccess: () => { form.setDefaults(); onCerrar(); } };
        if (cliente) form.put(`${urls.index}/${cliente.id}`, opciones);
        else if (onCreado)
            form.post(urls.index, {
                preserveScroll: true,
                preserveState: true, // no perder lo cargado en la página que abrió el alta
                only: ['flash'],
                onSuccess: (pagina) => {
                    const creado = (pagina.flash as { cliente?: ClienteCotizacion }).cliente;
                    form.setDefaults();
                    onCerrar();
                    if (creado) onCreado(creado);
                },
            });
        else form.post(urls.index, opciones);
    };

    const cerrar = (abrir: boolean) => {
        if (!abrir && confirmarDescarte(form.isDirty)) onCerrar();
    };

    const municipios = estados[data.estado_territorial] ?? [];
    const bloqueado = Boolean(vinculada); // datos de la persona vinculada: se editan desde su módulo

    return (
        <Dialog open={abierto} onOpenChange={cerrar}>
            <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>{edicion ? 'Editar cliente' : 'Agregar cliente'}</DialogTitle>
                    <DialogDescription>
                        Cliente {ETIQUETA_TIPO[tipo].toLowerCase()}. El tipo se define por el prefijo del documento (V/E natural · J jurídico · G gubernamental).
                    </DialogDescription>
                </DialogHeader>

                {cliente && cliente.otros_roles.length > 0 && (
                    <p className="bg-primary/8 text-primary ring-primary/20 flex items-start gap-2 rounded-md px-3 py-2 text-sm ring-1 ring-inset">
                        <UserRoundCheck className="mt-0.5 size-4 shrink-0" />
                        Esta persona también está registrada como {cliente.otros_roles.join(' y ')}. Los cambios en sus datos también se verán allí.
                    </p>
                )}

                <form id="form-cliente" onSubmit={guardar} className="grid gap-5" noValidate>
                    <section className="grid gap-4 sm:grid-cols-2">
                        <Campo
                            etiqueta={natural ? 'Cédula' : 'RIF'}
                            requerido
                            error={errorDe('documento') ?? aviso.documento}
                            ayuda={edicion ? 'El documento no se puede cambiar.' : `Entre 6 y ${largoDocumento(data.tipo_documento)} dígitos.`}
                        >
                            {(control) => (
                                <div className="flex gap-2">
                                    <Select
                                        value={data.tipo_documento}
                                        onValueChange={(v) => cambiarDocumento({ tipo_documento: v as Prefijo, numero: data.numero.slice(0, largoDocumento(v as Prefijo)) })}
                                        disabled={edicion || bloqueado}
                                    >
                                        <SelectTrigger className="w-20" aria-label="Prefijo del documento">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {PREFIJOS.map((p) => (
                                                <SelectItem key={p} value={p}>{p}</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <Input
                                        {...control}
                                        inputMode="numeric"
                                        maxLength={largoDocumento(data.tipo_documento)}
                                        placeholder="Número"
                                        value={data.numero}
                                        disabled={edicion}
                                        onChange={(e) => cambiarDocumento({ numero: e.target.value.replace(/\D/g, '').slice(0, largoDocumento(data.tipo_documento)) })}
                                        onBlur={revisarDocumento}
                                        className="tabular flex-1"
                                    />
                                </div>
                            )}
                        </Campo>
                        <Campo etiqueta={natural ? 'Nombre y apellido' : 'Razón social'} requerido error={errorDe('nombre')}>
                            <Input value={data.nombre} maxLength={200} readOnly={bloqueado} onChange={(e) => setData('nombre', e.target.value)} />
                        </Campo>

                        {encontrada && (
                            <div className="border-primary/30 bg-primary/5 grid gap-2 rounded-md border p-3 text-sm sm:col-span-2">
                                <p>
                                    Esta persona ya está registrada como <strong>{encontrada.rol}</strong>: <strong>{encontrada.persona.nombre}</strong>
                                    {encontrada.persona.email && <span className="text-muted-foreground"> · {encontrada.persona.email}</span>}
                                </p>
                                <Button type="button" size="sm" className="justify-self-start" onClick={usarDatos}>
                                    <Link2 /> Usar sus datos
                                </Button>
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
                        <Campo etiqueta="Dirección" error={errorDe('direccion')}>
                            <Input value={data.direccion} maxLength={500} onChange={(e) => setData('direccion', e.target.value)} />
                        </Campo>
                    </section>

                    <CampoTelefonos valor={data.telefonos} onChange={(t) => setData('telefonos', t)} errores={errors as Record<string, string | undefined>} />

                    <section className="grid gap-4 sm:grid-cols-2">
                        <Campo etiqueta="Estado" error={errorDe('estado_territorial')}>
                            {(control) => (
                                <Select value={data.estado_territorial || undefined} onValueChange={(v) => setData((d) => ({ ...d, estado_territorial: v, ciudad: '' }))}>
                                    <SelectTrigger {...control} className="w-full">
                                        <SelectValue placeholder="Selecciona un estado" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {Object.keys(estados).map((e) => (
                                            <SelectItem key={e} value={e}>{e}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            )}
                        </Campo>
                        <Campo etiqueta="Municipio" error={errorDe('ciudad')}>
                            {(control) => (
                                <Select value={data.ciudad || undefined} onValueChange={(v) => setData('ciudad', v)} disabled={!municipios.length}>
                                    <SelectTrigger {...control} className="w-full">
                                        <SelectValue placeholder={municipios.length ? 'Selecciona un municipio' : 'Primero selecciona un estado'} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {municipios.map((m) => (
                                            <SelectItem key={m} value={m}>{m}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            )}
                        </Campo>
                    </section>
                </form>

                <DialogFooter>
                    <Button type="button" variant="outline" onClick={() => cerrar(false)}>Cancelar</Button>
                    <Button type="submit" form="form-cliente" disabled={form.processing || Boolean(aviso.documento) || Boolean(encontrada)}>
                        {form.processing ? 'Guardando…' : edicion ? 'Guardar cambios' : 'Agregar cliente'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
