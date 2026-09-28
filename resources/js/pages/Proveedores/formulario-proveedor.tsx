import { useForm } from '@inertiajs/react';
import { useState } from 'react';

import { Campo } from '@/components/app/campo';
import { CampoTelefonos, telefonoVacio, type Telefono } from '@/components/app/campo-telefonos';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { confirmarDescarte, useGuardCambios } from '@/hooks/use-guard-cambios';

import type { PaginaProveedores, ProveedorFila, ProveedorResumen } from './tipos';

type Prefijo = 'V-' | 'E-' | 'J-' | 'G-';

/** V/E → persona natural (cédula); J/G → jurídico (RIF). Regla del sistema: el tipo se deriva del prefijo. */
const tipoDesdePrefijo = (p: Prefijo) => (p === 'V-' || p === 'E-' ? 'natural' : 'juridico');
const PREFIJOS_CONTACTO = ['0412', '0422', '0414', '0424', '0416', '0426'];

interface Formulario {
    tipo_documento: Prefijo;
    numero: string;
    nombre: string;
    email: string;
    direccion: string;
    telefonos: Telefono[];
    estado_territorial: string;
    ciudad: string;
    contacto: string;
    contacto_prefijo: string;
    contacto_numero: string;
}

function inicial(p?: ProveedorFila): Formulario {
    // `|| '0424'`, no un default de desestructuración: ''.split('-') da [''] y '' no es undefined.
    const [cPrefijoLeido, cNumero = ''] = (p?.telefono_contacto ?? '').split('-');
    const cPrefijo = cPrefijoLeido || '0424';
    return {
        tipo_documento: (p?.tipo_documento as Prefijo | null) ?? 'J-',
        numero: p?.numero_documento ?? '',
        nombre: p?.nombre ?? '',
        email: p?.email ?? '',
        direccion: p?.direccion ?? '',
        telefonos: p?.telefonos.length ? p.telefonos : [telefonoVacio(true)],
        estado_territorial: p?.estado_territorial ?? '',
        ciudad: p?.ciudad ?? '',
        contacto: p?.contacto ?? '',
        contacto_prefijo: cPrefijo,
        contacto_numero: cNumero,
    };
}

interface Props {
    abierto: boolean;
    onCerrar: () => void;
    /** Proveedor a editar; sin él, es un alta. */
    proveedor?: ProveedorFila;
    estados: PaginaProveedores['estados'];
    urls: Pick<PaginaProveedores['urls'], 'index' | 'checkDocumento' | 'checkRif' | 'checkEmail'>;
    /** Alta rápida desde otra página (Compras): recibe el proveedor creado y conserva el estado de esa página. */
    onCreado?: (proveedor: ProveedorResumen) => void;
}

/**
 * Montar con una `key` distinta en cada apertura (ver Index.tsx): useForm toma
 * sus valores iniciales al montarse, y un setDefaults()+reset() en el mismo
 * ciclo no sirve porque setDefaults actualiza el estado de forma asíncrona.
 */
export function FormularioProveedor({ abierto, onCerrar, proveedor, estados, urls, onCreado }: Props) {
    const edicion = Boolean(proveedor);
    const form = useForm<Formulario>(inicial(proveedor));
    const { data, setData, errors } = form;
    const tipo = tipoDesdePrefijo(data.tipo_documento);
    const [aviso, setAviso] = useState<{ documento?: string; email?: string }>({});

    useGuardCambios(abierto && form.isDirty);

    // Estado del formulario → payload que conoce el servidor (el mismo que envía Compras).
    form.transform((d) => {
        const comunes = {
            tipo_proveedor: tipo,
            email: d.email.trim(),
            direccion: d.direccion.trim(),
            telefonos: d.telefonos,
            estado_territorial: d.estado_territorial || null,
            ciudad: d.ciudad || null,
        };
        return tipo === 'juridico'
            ? {
                  ...comunes,
                  rif: `${d.tipo_documento}${d.numero}`,
                  razon_social: d.nombre.trim(),
                  contacto: d.contacto.trim() || null,
                  telefono_contacto: d.contacto_numero ? `${d.contacto_prefijo}-${d.contacto_numero}` : null,
              }
            : { ...comunes, tipo_documento: d.tipo_documento, documento_identidad: d.numero, nombre: d.nombre.trim() };
    });

    const errorDe = (...claves: string[]) => {
        for (const c of claves) {
            const e = (errors as Record<string, string | undefined>)[c];
            if (e) return e;
        }
        return undefined;
    };

    // Aviso temprano de duplicados (el servidor igual lo valida al guardar).
    const revisar = async (campo: 'documento' | 'email') => {
        const params = new URLSearchParams();
        let url: string;
        if (campo === 'email') {
            if (!data.email.trim()) return;
            url = urls.checkEmail;
            params.set('email', data.email.trim());
            if (proveedor) params.set('exclude_id', String(proveedor.id));
        } else {
            if (edicion || data.numero.length < 5) return;
            url = tipo === 'juridico' ? urls.checkRif : urls.checkDocumento;
            params.set(tipo === 'juridico' ? 'rif' : 'numero', tipo === 'juridico' ? `${data.tipo_documento}${data.numero}` : data.numero);
        }
        try {
            const r = await fetch(`${url}?${params}`, { headers: { Accept: 'application/json' } });
            const { exists } = (await r.json()) as { exists: boolean };
            setAviso((a) => ({ ...a, [campo]: exists ? (campo === 'email' ? 'Este correo ya está registrado.' : 'Este documento ya está registrado.') : undefined }));
        } catch {
            // sin red: el servidor valida al guardar
        }
    };

    const guardar = (e: React.FormEvent) => {
        e.preventDefault();
        const opciones = { preserveScroll: true, onSuccess: () => { form.setDefaults(); onCerrar(); } };
        if (proveedor) form.put(`${urls.index}/${proveedor.id}`, opciones);
        else if (onCreado)
            form.post(urls.index, {
                preserveScroll: true,
                preserveState: true, // no perder lo cargado en la página que abrió el alta
                only: ['flash'],
                onSuccess: (pagina) => {
                    const creado = (pagina.flash as { proveedor?: ProveedorResumen }).proveedor;
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

    return (
        <Dialog open={abierto} onOpenChange={cerrar}>
            <DialogContent className="max-h-[92svh] overflow-y-auto sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>{edicion ? 'Editar proveedor' : 'Agregar proveedor'}</DialogTitle>
                    <DialogDescription>
                        {tipo === 'juridico' ? 'Proveedor jurídico (empresa u organismo).' : 'Proveedor natural (persona).'} El tipo se define por el prefijo del documento.
                    </DialogDescription>
                </DialogHeader>

                <form id="form-proveedor" onSubmit={guardar} className="grid gap-5" noValidate>
                    <section className="grid gap-4 sm:grid-cols-2">
                        <Campo
                            etiqueta={tipo === 'juridico' ? 'RIF' : 'Cédula'}
                            requerido
                            error={errorDe('rif', 'documento_identidad') ?? aviso.documento}
                            ayuda={edicion ? 'El documento no se puede cambiar.' : 'V/E → natural · J/G → jurídico'}
                        >
                            {(control) => (
                            <div className="flex gap-2">
                                <Select
                                    value={data.tipo_documento}
                                    onValueChange={(v) => { setData('tipo_documento', v as Prefijo); setAviso((a) => ({ ...a, documento: undefined })); }}
                                    disabled={edicion}
                                >
                                    <SelectTrigger className="w-20" aria-label="Prefijo del documento">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {(['V-', 'E-', 'J-', 'G-'] as const).map((p) => (
                                            <SelectItem key={p} value={p}>{p}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <Input
                                    {...control}
                                    inputMode="numeric"
                                    maxLength={9}
                                    placeholder="Número"
                                    value={data.numero}
                                    disabled={edicion}
                                    onChange={(e) => setData('numero', e.target.value.replace(/\D/g, '').slice(0, 9))}
                                    onBlur={() => revisar('documento')}
                                    className="tabular flex-1"
                                />
                            </div>
                            )}
                        </Campo>
                        <Campo etiqueta={tipo === 'juridico' ? 'Razón social' : 'Nombre completo'} requerido error={errorDe('razon_social', 'nombre')}>
                            <Input value={data.nombre} maxLength={100} onChange={(e) => setData('nombre', e.target.value)} />
                        </Campo>
                        <Campo etiqueta="Correo electrónico" requerido error={errorDe('email') ?? aviso.email}>
                            <Input type="email" value={data.email} maxLength={100} onChange={(e) => setData('email', e.target.value)} onBlur={() => revisar('email')} />
                        </Campo>
                        <Campo etiqueta="Dirección" requerido error={errorDe('direccion')}>
                            <Input value={data.direccion} maxLength={200} onChange={(e) => setData('direccion', e.target.value)} />
                        </Campo>
                    </section>

                    <CampoTelefonos
                        valor={data.telefonos}
                        onChange={(t) => setData('telefonos', t)}
                        errores={errors as Record<string, string | undefined>}
                    />

                    <section className="grid gap-4 sm:grid-cols-2">
                        <Campo etiqueta="Estado" error={errorDe('estado_territorial')}>
                            {(control) => (
                            <Select
                                value={data.estado_territorial || undefined}
                                onValueChange={(v) => setData((d) => ({ ...d, estado_territorial: v, ciudad: '' }))}
                            >
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

                    {tipo === 'juridico' && (
                        <section className="grid gap-4 sm:grid-cols-2">
                            <Campo etiqueta="Persona de contacto" error={errorDe('contacto')}>
                                <Input value={data.contacto} maxLength={100} onChange={(e) => setData('contacto', e.target.value)} />
                            </Campo>
                            <Campo etiqueta="Teléfono de contacto" error={errorDe('telefono_contacto')}>
                                {(control) => (
                                <div className="flex gap-2">
                                    <Select value={data.contacto_prefijo} onValueChange={(v) => setData('contacto_prefijo', v)}>
                                        <SelectTrigger className="w-24" aria-label="Prefijo del teléfono de contacto">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {(PREFIJOS_CONTACTO.includes(data.contacto_prefijo) ? PREFIJOS_CONTACTO : [data.contacto_prefijo, ...PREFIJOS_CONTACTO]).map((p) => (
                                                <SelectItem key={p} value={p}>{p}</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <Input
                                        {...control}
                                        inputMode="numeric"
                                        maxLength={7}
                                        placeholder="1234567"
                                        value={data.contacto_numero}
                                        onChange={(e) => setData('contacto_numero', e.target.value.replace(/\D/g, '').slice(0, 7))}
                                        className="tabular flex-1"
                                    />
                                </div>
                                )}
                            </Campo>
                        </section>
                    )}
                </form>

                <DialogFooter>
                    <Button type="button" variant="outline" onClick={() => cerrar(false)}>Cancelar</Button>
                    <Button type="submit" form="form-proveedor" disabled={form.processing}>
                        {form.processing ? 'Guardando…' : edicion ? 'Guardar cambios' : 'Agregar proveedor'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
