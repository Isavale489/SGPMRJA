import { Link, router } from '@inertiajs/react';
import { Archive, ArrowLeft, MoreVertical, Pencil, Plus, RotateCcw, Search } from 'lucide-react';
import { useState, type ReactNode } from 'react';

import { BarraFiltros } from '@/components/app/barra-filtros';
import { ConfirmarPeligro } from '@/components/app/confirmar-peligro';
import { TablaServidor, type Columna } from '@/components/app/tabla-servidor';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { useFiltrosUrl } from '@/hooks/use-filtros-url';
import { usePermisos } from '@/hooks/use-permisos';
import AppLayout from '@/layouts/app-layout';
import type { Paginado } from '@/types';

export interface RegistroCatalogo {
    id: number;
    nombre: string;
    inhabilitado: boolean;
}

type Filtros = Record<string, string | undefined>;

interface Props<T extends RegistroCatalogo, F extends Filtros> {
    /** "Departamentos", "Cargos"… */
    titulo: string;
    /** Singular en minúscula para los textos: "departamento". */
    recurso: string;
    /** Permiso de escritura: 'departamentos.gestionar'. */
    permiso: string;
    registros: Paginado<T>;
    filtros: F;
    url: string;
    /** Base de Inhabilitar/Restaurar si difiere de la página (p. ej. /productos lista tipos de /tipo-productos). */
    urlMutaciones?: string;
    columnas: Columna<T>[];
    /** Filtros extra junto al buscador (reciben el estado y el setter). */
    filtrosExtra?: (filtros: F, cambiar: <K extends keyof F>(clave: K, valor: F[K]) => void) => ReactNode;
    /** Mensaje del diálogo de inhabilitar (p. ej. qué impide hacerlo). */
    avisoInhabilitar?: string;
    /** Botones extra del encabezado, antes de «Agregar» (p. ej. Exportar PDF). */
    accionesExtra?: ReactNode;
    
    /** El formulario (DialogoFormulario), montado con `key={p.apertura}` para reiniciarlo en cada apertura. */
    formulario: (p: { apertura: number; abierto: boolean; registro?: T; onCerrar: () => void }) => ReactNode;
}

/**
 * Página estándar de un catálogo: título, historial de inhabilitados,
 * búsqueda en la URL, tabla paginada en el servidor y acciones por fila
 * (Editar, Inhabilitar con confirmación, Restaurar). Cada módulo solo
 * aporta sus columnas y su formulario.
 */
export function PaginaCatalogo<T extends RegistroCatalogo, F extends Filtros>({
    titulo, recurso, permiso, registros, filtros: iniciales, url, urlMutaciones, columnas, filtrosExtra, avisoInhabilitar, accionesExtra, formulario,
}: Props<T, F>) {
    const { puede } = usePermisos();
    const base = urlMutaciones ?? url;
    const gestionar = puede(permiso);
    const historial = Boolean(iniciales.historial);
    const { filtros, cambiar, cargando } = useFiltrosUrl<F>(url, iniciales, ['registros', 'filtros']);
    const [estado, setEstado] = useState<{ abierto: boolean; registro?: T; apertura: number }>({ abierto: false, apertura: 0 });
    const abrir = (registro?: T) => setEstado((e) => ({ abierto: true, registro, apertura: e.apertura + 1 }));
    // Fuera del menú: si la confirmación viviera dentro, el menú quedaría abierto al confirmar.
    const [inhabilitando, setInhabilitando] = useState<T>();

    const acciones: Columna<T> = {
        id: 'acciones',
        encabezado: <span className="sr-only">Acciones</span>,
        className: 'w-14 text-right',
        celda: (r) =>
            gestionar && (
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="ghost" size="icon" aria-label={`Acciones para ${r.nombre}`}>
                            <MoreVertical />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        {r.inhabilitado ? (
                            <DropdownMenuItem tono="restaurar" onSelect={() => router.patch(`${base}/${r.id}/restore`, {}, { preserveScroll: true })}>
                                <RotateCcw /> Restaurar
                            </DropdownMenuItem>
                        ) : (
                            <>
                                <DropdownMenuItem tono="editar" onSelect={() => abrir(r)}>
                                    <Pencil /> Editar
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem tono="aviso" onSelect={() => setInhabilitando(r)}>
                                    <Archive /> Inhabilitar
                                </DropdownMenuItem>
                            </>
                        )}
                    </DropdownMenuContent>
                </DropdownMenu>
            ),
    };

    return (
        <AppLayout
            titulo={historial ? `${titulo} inhabilitados` : titulo}
            acciones={
                <>
                    <Button variant="ghost" asChild>
                        {historial ? (
                            <Link href={url}><ArrowLeft /> Solo activos</Link>
                        ) : (
                            <Link href={`${url}?historial=1`}><Archive /> Inhabilitados</Link>
                        )}
                    </Button>
                    {accionesExtra}
                    {gestionar && !historial && (
                        <Button onClick={() => abrir()}>
                            <Plus /> Agregar {recurso}
                        </Button>
                    )}
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
                            onChange={(e) => cambiar('buscar', e.target.value as F['buscar'])}
                            placeholder="Buscar…"
                            aria-label={`Buscar ${recurso}`}
                            className="pl-8"
                        />
                    </div>
                    {filtrosExtra?.(filtros, cambiar)}
                </BarraFiltros>
                <TablaServidor
                    pagina={registros}
                    columnas={[...columnas, acciones]}
                    only={['registros', 'filtros']}
                    cargando={cargando}
                    idFila={(r) => r.id}
                    vacio={filtros.buscar ? 'Nada coincide con la búsqueda.' : historial ? 'No hay registros inhabilitados.' : 'Aún no hay registros.'}
                />
            </div>
            <ConfirmarPeligro
                abierto={Boolean(inhabilitando)}
                onCerrar={() => setInhabilitando(undefined)}
                titulo={`¿Inhabilitar ${recurso} «${inhabilitando?.nombre ?? ''}»?`}
                descripcion={avisoInhabilitar ?? 'Pasa al historial. Se puede restaurar cuando quieras.'}
                accion="Inhabilitar"
                onConfirmar={() => inhabilitando && router.delete(`${base}/${inhabilitando.id}`, { preserveScroll: true })}
            />
            {gestionar && formulario({ apertura: estado.apertura, abierto: estado.abierto, registro: estado.registro, onCerrar: () => setEstado((e) => ({ ...e, abierto: false })) })}
        </AppLayout>
    );
}
