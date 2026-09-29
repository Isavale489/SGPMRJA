import { router } from '@inertiajs/react';
import { Building2, Mail, Phone, Plus, UserRound, X } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

import { Buscador } from '@/components/app/buscador';
import { ConfirmarPeligro } from '@/components/app/confirmar-peligro';
import { Button } from '@/components/ui/button';
import { usePermisos } from '@/hooks/use-permisos';
import { FormularioProveedor } from '@/pages/Proveedores/formulario-proveedor';
import type { ProveedorResumen } from '@/pages/Proveedores/tipos';

import type { PaginaFormularioCompra } from './tipos';

/** Persona ya registrada (cliente, empleado) que aún no es proveedor: /personas-search. */
interface PersonaSinProveedor {
    persona_id: number;
    proveedor_id: number | null;
    documento: string;
    nombre: string;
    roles: string[];
}

type Resultado = { tipo: 'proveedor'; p: ProveedorResumen } | { tipo: 'persona'; p: PersonaSinProveedor };

const ROLES: Record<string, string> = { cliente: 'cliente', empleado: 'empleado' };

async function json<T>(url: string): Promise<T> {
    const r = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!r.ok) throw new Error(String(r.status));
    return (await r.json()) as T;
}

interface Props {
    proveedor: ProveedorResumen | null;
    onCambiar: (p: ProveedorResumen | null) => void;
    error?: string;
    urls: PaginaFormularioCompra['urls'];
    estados: PaginaFormularioCompra['estados'];
}

export function SelectorProveedor({ proveedor, onCambiar, error, urls, estados }: Props) {
    const { puede } = usePermisos();
    const [alta, setAlta] = useState<{ abierto: boolean; apertura: number }>({ abierto: false, apertura: 0 });
    const [convertir, setConvertir] = useState<PersonaSinProveedor>();

    // Proveedores por nombre o documento; con un documento (y permiso para dar de
    // alta proveedores), también personas que aún no lo son.
    const puedeRegistrar = puede('proveedores.gestionar');
    const buscar = async (q: string): Promise<Resultado[]> => {
        const proveedores = await json<ProveedorResumen[]>(`${urls.buscarProveedor}?${new URLSearchParams({ q })}`);
        const resultados: Resultado[] = proveedores.map((p) => ({ tipo: 'proveedor', p }));
        if (puedeRegistrar && /^\d{6,}$/.test(q)) {
            const personas = await json<PersonaSinProveedor[]>(`${urls.buscarPersona}?${new URLSearchParams({ q })}`).catch(() => []);
            resultados.push(...personas.filter((p) => !p.proveedor_id).map((p) => ({ tipo: 'persona' as const, p })));
        }
        return resultados;
    };

    const registrarPersona = (persona: PersonaSinProveedor) =>
        router.post(`${urls.desdePersona}/${persona.persona_id}`, {}, {
            preserveState: true,
            preserveScroll: true,
            only: ['flash'],
            onSuccess: (pagina) => {
                const creado = (pagina.flash as { proveedor?: ProveedorResumen }).proveedor;
                if (creado) onCambiar(creado);
            },
            onError: (errores) => toast.error(Object.values(errores)[0] ?? 'No se pudo registrar como proveedor.'),
        });

    if (proveedor) {
        return (
            <div className="bg-muted/40 flex items-start gap-3 rounded-lg border p-3">
                <span className="bg-primary/10 text-primary grid size-10 shrink-0 place-items-center rounded-full">
                    {proveedor.tipo === 'natural' ? <UserRound className="size-5" /> : <Building2 className="size-5" />}
                </span>
                <div className="min-w-0 flex-1 text-sm">
                    <p className="font-medium">{proveedor.nombre}</p>
                    <p className="text-muted-foreground tabular">{proveedor.doc}</p>
                    <p className="text-muted-foreground mt-1 flex flex-wrap gap-x-4 gap-y-0.5 text-xs">
                        {proveedor.tel && <span className="inline-flex items-center gap-1"><Phone className="size-3" /> {proveedor.tel}</span>}
                        {proveedor.email && <span className="inline-flex items-center gap-1"><Mail className="size-3" /> {proveedor.email}</span>}
                    </p>
                </div>
                <Button type="button" variant="ghost" size="sm" onClick={() => onCambiar(null)}>
                    <X /> Cambiar
                </Button>
            </div>
        );
    }

    return (
        <div className="grid gap-1.5">
            <Buscador<Resultado>
                etiqueta="Buscar proveedor"
                placeholder="Nombre, RIF o cédula del proveedor…"
                remoto
                buscarVacio
                buscar={buscar}
                aria-invalid={error ? true : undefined}
                clave={(r) => `${r.tipo}-${r.tipo === 'proveedor' ? r.p.id : r.p.persona_id}`}
                opcion={(r) =>
                    r.tipo === 'proveedor' ? (
                        <span className="flex items-baseline justify-between gap-3">
                            <span className="truncate font-medium">{r.p.nombre}</span>
                            <span className="text-muted-foreground shrink-0 text-xs tabular">{r.p.doc}</span>
                        </span>
                    ) : (
                        <span className="grid">
                            <span className="flex items-baseline justify-between gap-3">
                                <span className="truncate font-medium">{r.p.nombre}</span>
                                <span className="text-muted-foreground shrink-0 text-xs tabular">{r.p.documento}</span>
                            </span>
                            <span className="text-muted-foreground text-xs">
                                Registrado como {r.p.roles.map((x) => ROLES[x] ?? x).join(' y ')} · registrar como proveedor
                            </span>
                        </span>
                    )
                }
                onElegir={(r) => (r.tipo === 'proveedor' ? onCambiar(r.p) : setConvertir(r.p))}
                vacio={(q) => (q ? 'Ningún proveedor coincide.' : 'Aún no hay proveedores.')}
                pie={() =>
                    puede('proveedores.gestionar') ? (
                        <Button type="button" variant="ghost" size="sm" className="w-full justify-start" onMouseDown={(e) => e.preventDefault()} onClick={() => setAlta((a) => ({ abierto: true, apertura: a.apertura + 1 }))}>
                            <Plus /> Nuevo proveedor
                        </Button>
                    ) : null
                }
            />
            {error && <p className="text-destructive text-xs">{error}</p>}

            {puede('proveedores.gestionar') && (
                <FormularioProveedor
                    key={alta.apertura}
                    abierto={alta.abierto}
                    onCerrar={() => setAlta((a) => ({ ...a, abierto: false }))}
                    estados={estados}
                    urls={{ index: urls.proveedores, checkDocumento: urls.checkDocumento, checkRif: urls.checkRif, checkEmail: urls.checkEmail }}
                    onCreado={onCambiar}
                />
            )}

            <ConfirmarPeligro
                abierto={Boolean(convertir)}
                onCerrar={() => setConvertir(undefined)}
                destructiva={false}
                titulo="¿Registrar como proveedor?"
                descripcion={`${convertir?.nombre ?? ''} ya está registrado en el sistema. Se crea el proveedor con sus mismos datos, sin duplicarlos.`}
                accion="Registrar proveedor"
                onConfirmar={() => convertir && registrarPersona(convertir)}
            />
        </div>
    );
}
