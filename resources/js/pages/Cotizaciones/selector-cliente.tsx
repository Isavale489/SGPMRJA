import { FileText, Mail, Phone, Plus, X } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

import { Buscador } from '@/components/app/buscador';
import { ConfirmarPeligro } from '@/components/app/confirmar-peligro';
import { Button } from '@/components/ui/button';
import { usePermisos } from '@/hooks/use-permisos';
import { formatoFecha } from '@/lib/formato';
import { FormularioCliente } from '@/pages/Clientes/formulario-cliente';

import { iniciales } from './piezas';
import type { ClienteCotizacion, PaginaFormularioCotizacion } from './tipos';

/** Persona ya registrada (empleado, proveedor) que aún no es cliente: /personas-search. */
interface Persona {
    persona_id: number;
    cliente_id: number | null;
    documento: string;
    tipo_documento: string | null;
    nombre: string;
    email: string;
    telefono: string;
    roles: string[];
}

type Resultado = { tipo: 'cliente'; c: ClienteCotizacion } | { tipo: 'persona'; p: Persona };

const ROLES: Record<string, string> = { empleado: 'empleado', proveedor_natural: 'proveedor', proveedor_juridico: 'proveedor' };
const csrf = () => document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

async function json<T>(url: string): Promise<T> {
    const r = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!r.ok) throw new Error(String(r.status));
    return (await r.json()) as T;
}

interface Props {
    cliente: ClienteCotizacion | null;
    onCambiar: (c: ClienteCotizacion | null) => void;
    error?: string;
    /** Al editar, el cliente queda fijo (como en la vista anterior). */
    fijo?: boolean;
    urls: PaginaFormularioCotizacion['urls'];
    estados: Record<string, string[]>;
}

/**
 * Cliente de la cotización: busca clientes por nombre o documento y, con un
 * documento de 6+ dígitos, también personas registradas en otro rol (se ofrece
 * registrarlas como cliente sin duplicar sus datos). «Nuevo cliente» abre el
 * mismo formulario del módulo Clientes.
 */
export function SelectorCliente({ cliente, onCambiar, error, fijo, urls, estados }: Props) {
    const { puede } = usePermisos();
    const [alta, setAlta] = useState<{ abierto: boolean; apertura: number }>({ abierto: false, apertura: 0 });
    const [convertir, setConvertir] = useState<Persona>();
    const puedeRegistrar = puede('clientes.gestionar');

    const buscar = async (q: string): Promise<Resultado[]> => {
        const clientes = await json<ClienteCotizacion[]>(`${urls.buscarCliente}?${new URLSearchParams({ q })}`);
        const resultados: Resultado[] = clientes.map((c) => ({ tipo: 'cliente', c }));
        if (puedeRegistrar && /^\d{6,}$/.test(q)) {
            const personas = await json<Persona[]>(`${urls.buscarPersona}?${new URLSearchParams({ q })}`).catch(() => []);
            resultados.push(...personas.filter((p) => !p.cliente_id).map((p) => ({ tipo: 'persona' as const, p })));
        }
        return resultados;
    };

    const registrarPersona = async (p: Persona) => {
        try {
            const r = await fetch(`${urls.desdePersona}/${p.persona_id}`, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() } });
            const res = (await r.json()) as { success?: boolean; cliente_id?: number; message?: string };
            if (!r.ok || !res.cliente_id) throw new Error(res.message ?? 'No se pudo registrar como cliente.');
            toast.success(res.message ?? 'Cliente registrado.');
            onCambiar({
                id: res.cliente_id,
                nombre: p.nombre,
                documento: p.documento,
                juridico: p.tipo_documento === 'J-' || p.tipo_documento === 'G-',
                telefono: p.telefono || null,
                email: p.email || null,
                inhabilitado: false,
                cotizaciones: 0,
                ultima: null,
            });
        } catch (e) {
            toast.error(e instanceof Error ? e.message : 'No se pudo registrar como cliente.');
        }
    };

    if (cliente) {
        return (
            <div className="bg-muted/40 flex items-start gap-3 rounded-lg border p-3">
                <span className="bg-primary text-primary-foreground grid size-11 shrink-0 place-items-center rounded-full text-sm font-semibold">{iniciales(cliente.nombre)}</span>
                <div className="min-w-0 flex-1 text-sm">
                    <p className="font-medium">
                        {cliente.nombre}
                        {cliente.inhabilitado && <span className="bg-destructive/10 text-destructive ml-2 rounded-full px-2 py-0.5 text-xs">Inhabilitado</span>}
                    </p>
                    <p className="text-muted-foreground tabular">{cliente.documento ?? 'Sin documento'}</p>
                    <p className="text-muted-foreground mt-1 flex flex-wrap gap-x-4 gap-y-0.5 text-xs">
                        {cliente.telefono && (
                            <span className="inline-flex items-center gap-1">
                                <Phone className="size-3" /> {cliente.telefono}
                            </span>
                        )}
                        {cliente.email && (
                            <span className="inline-flex items-center gap-1 break-all">
                                <Mail className="size-3 shrink-0" /> {cliente.email}
                            </span>
                        )}
                    </p>
                    {cliente.cotizaciones > 0 && (
                        <p className="text-muted-foreground mt-1 inline-flex items-center gap-1 text-xs">
                            <FileText className="size-3" /> {cliente.cotizaciones} {cliente.cotizaciones === 1 ? 'cotización previa' : 'cotizaciones previas'}
                            {cliente.ultima && ` · última: ${formatoFecha(cliente.ultima)}`}
                        </p>
                    )}
                </div>
                {!fijo && (
                    <Button type="button" variant="ghost" size="sm" onClick={() => onCambiar(null)}>
                        <X /> Cambiar
                    </Button>
                )}
            </div>
        );
    }

    return (
        <div className="grid gap-1.5">
            <Buscador<Resultado>
                etiqueta="Buscar cliente"
                placeholder="Nombre, cédula o RIF del cliente…"
                remoto
                buscarVacio
                buscar={buscar}
                aria-invalid={error ? true : undefined}
                clave={(r) => (r.tipo === 'cliente' ? `c-${r.c.id}` : `p-${r.p.persona_id}`)}
                opcion={(r) =>
                    r.tipo === 'cliente' ? (
                        <span className="flex items-baseline justify-between gap-3">
                            <span className="truncate font-medium">{r.c.nombre}</span>
                            <span className="text-muted-foreground shrink-0 text-xs tabular">{r.c.documento}</span>
                        </span>
                    ) : (
                        <span className="grid">
                            <span className="flex items-baseline justify-between gap-3">
                                <span className="truncate font-medium">{r.p.nombre}</span>
                                <span className="text-muted-foreground shrink-0 text-xs tabular">{r.p.documento}</span>
                            </span>
                            <span className="text-muted-foreground text-xs">Registrado como {[...new Set(r.p.roles.map((x) => ROLES[x] ?? x))].join(' y ')} · registrar como cliente</span>
                        </span>
                    )
                }
                onElegir={(r) => (r.tipo === 'cliente' ? onCambiar(r.c) : setConvertir(r.p))}
                vacio={(q) => (q ? 'Ningún cliente coincide.' : 'Aún no hay clientes.')}
                pie={() =>
                    puedeRegistrar ? (
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            className="w-full justify-start"
                            onMouseDown={(e) => e.preventDefault()}
                            onClick={() => setAlta((a) => ({ abierto: true, apertura: a.apertura + 1 }))}
                        >
                            <Plus /> Nuevo cliente
                        </Button>
                    ) : null
                }
            />
            {error && <p className="text-destructive text-xs">{error}</p>}
            <p className="text-muted-foreground text-xs">Escribe el documento para buscar también entre empleados y proveedores ya registrados.</p>

            {puedeRegistrar && (
                <FormularioCliente
                    key={alta.apertura}
                    abierto={alta.abierto}
                    onCerrar={() => setAlta((a) => ({ ...a, abierto: false }))}
                    estados={estados}
                    urls={{ index: urls.clientes, checkDocumento: urls.checkDocumento, checkEmail: urls.checkEmail }}
                    onCreado={onCambiar}
                />
            )}

            <ConfirmarPeligro
                abierto={Boolean(convertir)}
                onCerrar={() => setConvertir(undefined)}
                destructiva={false}
                titulo="¿Registrar como cliente?"
                descripcion={`${convertir?.nombre ?? ''} ya está registrado en el sistema. Se crea el cliente con sus mismos datos, sin duplicarlos.`}
                accion="Registrar cliente"
                onConfirmar={() => convertir && registrarPersona(convertir)}
            />
        </div>
    );
}
