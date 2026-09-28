import { router } from '@inertiajs/react';
import { ArrowDown, ArrowLeft, ArrowUp, MoreVertical, Pencil, Plus, Trash2 } from 'lucide-react';
import { useState, type ReactNode } from 'react';

import { ConfirmarPeligro } from '@/components/app/confirmar-peligro';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { usePermisos } from '@/hooks/use-permisos';
import AppLayout from '@/layouts/app-layout';
import { formatoNumero } from '@/lib/formato';
import { cn } from '@/lib/utils';

import { FormularioAtributo, FormularioValor } from './formularios';
import type { AtributoFila, PaginaAtributos, ValorFila } from './tipos';

/** Recarga parcial al cambiar de atributo: solo sus valores. */
const DETALLE = ['seleccionado', 'valores'];

type Dialogo<T> = { abierto: boolean; registro?: T; apertura: number };
type Borrando = { tipo: 'atributo'; registro: AtributoFila } | { tipo: 'valor'; registro: ValorFila };

function Codigo({ children }: { children: ReactNode }) {
    return <span className="bg-primary/10 text-primary rounded px-1.5 py-0.5 font-mono text-xs font-semibold tracking-wide">{children}</span>;
}

function Encabezado({ id, titulo, children }: { id: string; titulo: ReactNode; children?: ReactNode }) {
    return (
        <div className="border-border flex min-h-14 items-center gap-2 border-b px-4 py-2">
            <h2 id={id} className="min-w-0 flex-1 truncate text-sm font-semibold">{titulo}</h2>
            {children}
        </div>
    );
}

const th = 'text-muted-foreground h-10 text-xs font-medium uppercase tracking-wide';

export default function AtributosIndex({ atributos, seleccionado, valores, tiposProducto, urls }: PaginaAtributos) {
    const { puede } = usePermisos();
    const gestionar = puede('atributos.gestionar');
    const actual = atributos.find((a) => a.id === seleccionado);
    const [cargando, setCargando] = useState(false);

    const [dAtributo, setDAtributo] = useState<Dialogo<AtributoFila>>({ abierto: false, apertura: 0 });
    const [dValor, setDValor] = useState<Dialogo<ValorFila>>({ abierto: false, apertura: 0 });
    const abrirAtributo = (registro?: AtributoFila) => setDAtributo((d) => ({ abierto: true, registro, apertura: d.apertura + 1 }));
    const abrirValor = (registro?: ValorFila) => setDValor((d) => ({ abierto: true, registro, apertura: d.apertura + 1 }));
    // Fuera de los menús: si la confirmación viviera dentro, el menú quedaría abierto al confirmar.
    const [borrando, setBorrando] = useState<Borrando>();

    const seleccionar = (id: number | null) =>
        router.get(urls.index, id ? { atributo: id } : {}, {
            only: DETALLE,
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => setCargando(true),
            onFinish: () => setCargando(false),
        });

    const mover = (indice: number, paso: -1 | 1) => {
        if (!actual) return;
        const ids = valores.map((v) => v.id);
        ids.splice(indice + paso, 0, ...ids.splice(indice, 1));
        router.put(`${urls.index}/${actual.id}/valores-reorder`, { ids }, { only: ['valores'], preserveScroll: true, preserveState: true });
    };

    const confirmarBorrado = () => {
        if (!borrando) return;
        const destino = borrando.tipo === 'atributo'
            ? `${urls.index}/${borrando.registro.id}`
            : `${urls.index}/${actual?.id}/valores/${borrando.registro.id}`;
        router.delete(destino, { preserveScroll: true });
    };

    return (
        <AppLayout
            titulo="Atributos de confección"
            acciones={gestionar && (
                <Button onClick={() => abrirAtributo()}>
                    <Plus /> Agregar atributo
                </Button>
            )}
        >
            <div className="grid items-start gap-4 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">
                {/* Maestro: atributos */}
                <section aria-labelledby="titulo-atributos" className={cn('border-border bg-card overflow-hidden rounded-lg border', actual && 'max-lg:hidden')}>
                    <Encabezado id="titulo-atributos" titulo={<>Atributos <span className="text-muted-foreground font-normal">({formatoNumero(atributos.length)})</span></>} />
                    {atributos.length === 0 ? (
                        <p className="text-muted-foreground px-4 py-10 text-center text-sm">
                            Aún no hay atributos. {gestionar && 'Agrega el primero (manga, cuello, corte…).'}
                        </p>
                    ) : (
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow className="hover:bg-transparent">
                                        <TableHead className={th}>Atributo</TableHead>
                                        <TableHead className={th}>Código</TableHead>
                                        <TableHead className={cn(th, 'text-right')}>Valores</TableHead>
                                        <TableHead className={cn(th, 'text-right')}>Tipos</TableHead>
                                        {gestionar && <TableHead className={cn(th, 'w-12')}><span className="sr-only">Acciones</span></TableHead>}
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {atributos.map((a) => {
                                        const activo = a.id === seleccionado;
                                        return (
                                            <TableRow
                                                key={a.id}
                                                data-state={activo ? 'selected' : undefined}
                                                className="cursor-pointer"
                                                onClick={() => !activo && seleccionar(a.id)}
                                            >
                                                <TableCell>
                                                    {/* El botón da el foco y el nombre accesible; la fila entera también responde al clic. */}
                                                    <button
                                                        type="button"
                                                        aria-current={activo ? 'true' : undefined}
                                                        aria-label={`Ver valores de ${a.nombre}`}
                                                        onClick={(e) => { e.stopPropagation(); if (!activo) seleccionar(a.id); }}
                                                        className="focus-visible:ring-ring/50 rounded-sm text-left font-medium outline-none focus-visible:ring-[3px]"
                                                    >
                                                        {a.nombre}
                                                        {a.descripcion && <span className="text-muted-foreground block text-xs font-normal">{a.descripcion}</span>}
                                                    </button>
                                                </TableCell>
                                                <TableCell><Codigo>{a.codigo}</Codigo></TableCell>
                                                <TableCell className="tabular text-right">{formatoNumero(a.valores)}</TableCell>
                                                <TableCell className="tabular text-right">{formatoNumero(a.tipos_producto_ids.length)}</TableCell>
                                                {gestionar && (
                                                    <TableCell className="text-right" onClick={(e) => e.stopPropagation()}>
                                                        <DropdownMenu>
                                                            <DropdownMenuTrigger asChild>
                                                                <Button variant="ghost" size="icon" aria-label={`Acciones para ${a.nombre}`}><MoreVertical /></Button>
                                                            </DropdownMenuTrigger>
                                                            <DropdownMenuContent align="end">
                                                                <DropdownMenuItem onSelect={() => abrirAtributo(a)}><Pencil /> Editar</DropdownMenuItem>
                                                                <DropdownMenuSeparator />
                                                                <DropdownMenuItem variant="destructive" onSelect={() => setBorrando({ tipo: 'atributo', registro: a })}>
                                                                    <Trash2 /> Eliminar
                                                                </DropdownMenuItem>
                                                            </DropdownMenuContent>
                                                        </DropdownMenu>
                                                    </TableCell>
                                                )}
                                            </TableRow>
                                        );
                                    })}
                                </TableBody>
                            </Table>
                        </div>
                    )}
                </section>

                {/* Detalle: valores del atributo seleccionado. En móvil se ve un panel a la vez. */}
                {actual && (
                    <Button variant="ghost" size="sm" className="justify-self-start lg:hidden" onClick={() => seleccionar(null)}>
                        <ArrowLeft /> Atributos
                    </Button>
                )}
                <section
                    aria-labelledby="titulo-valores"
                    aria-busy={cargando}
                    className={cn('border-border bg-card overflow-hidden rounded-lg border transition-opacity duration-medio', cargando && 'opacity-60', !actual && 'max-lg:hidden')}
                >
                    <Encabezado id="titulo-valores" titulo={actual ? <>Valores de {actual.nombre}</> : 'Valores'}>
                        {gestionar && actual && (
                            <Button size="sm" onClick={() => abrirValor()}>
                                <Plus /> Agregar valor
                            </Button>
                        )}
                    </Encabezado>
                    {!actual ? (
                        <p className="text-muted-foreground px-4 py-16 text-center text-sm">Selecciona un atributo para ver y ordenar sus valores.</p>
                    ) : valores.length === 0 ? (
                        <p className="text-muted-foreground px-4 py-16 text-center text-sm">Este atributo no tiene valores. {gestionar && 'Agrega el primero.'}</p>
                    ) : (
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow className="hover:bg-transparent">
                                        <TableHead className={cn(th, 'w-10 text-right')}>#</TableHead>
                                        <TableHead className={th}>Valor</TableHead>
                                        <TableHead className={th}>Código</TableHead>
                                        <TableHead className={cn(th, 'text-right')}>Productos</TableHead>
                                        {gestionar && <TableHead className={cn(th, 'w-32')}><span className="sr-only">Acciones</span></TableHead>}
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {valores.map((v, i) => (
                                        <TableRow key={v.id}>
                                            <TableCell className="text-muted-foreground tabular text-right">{i + 1}</TableCell>
                                            <TableCell className="font-medium">{v.nombre}</TableCell>
                                            <TableCell><Codigo>{v.codigo}</Codigo></TableCell>
                                            <TableCell className={cn('tabular text-right', v.productos === 0 && 'text-muted-foreground')}>{formatoNumero(v.productos)}</TableCell>
                                            {gestionar && (
                                                <TableCell>
                                                    <div className="flex justify-end">
                                                        <Button variant="ghost" size="icon" disabled={i === 0} onClick={() => mover(i, -1)} aria-label={`Subir ${v.nombre}`}>
                                                            <ArrowUp />
                                                        </Button>
                                                        <Button variant="ghost" size="icon" disabled={i === valores.length - 1} onClick={() => mover(i, 1)} aria-label={`Bajar ${v.nombre}`}>
                                                            <ArrowDown />
                                                        </Button>
                                                        <DropdownMenu>
                                                            <DropdownMenuTrigger asChild>
                                                                <Button variant="ghost" size="icon" aria-label={`Acciones para ${v.nombre}`}><MoreVertical /></Button>
                                                            </DropdownMenuTrigger>
                                                            <DropdownMenuContent align="end">
                                                                <DropdownMenuItem onSelect={() => abrirValor(v)}><Pencil /> Editar</DropdownMenuItem>
                                                                <DropdownMenuSeparator />
                                                                <DropdownMenuItem variant="destructive" onSelect={() => setBorrando({ tipo: 'valor', registro: v })}>
                                                                    <Trash2 /> Eliminar
                                                                </DropdownMenuItem>
                                                            </DropdownMenuContent>
                                                        </DropdownMenu>
                                                    </div>
                                                </TableCell>
                                            )}
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    )}
                </section>
            </div>

            <ConfirmarPeligro
                abierto={Boolean(borrando)}
                onCerrar={() => setBorrando(undefined)}
                titulo={borrando?.tipo === 'atributo' ? `¿Eliminar el atributo «${borrando.registro.nombre}»?` : `¿Eliminar el valor «${borrando?.registro.nombre ?? ''}»?`}
                descripcion={borrando?.tipo === 'atributo'
                    ? 'Se borra junto con sus valores y no se puede deshacer. No se permite si algún tipo de producto lo usa.'
                    : 'Se borra y no se puede deshacer. No se permite si algún producto lo usa.'}
                onConfirmar={confirmarBorrado}
            />
            {gestionar && (
                <>
                    <FormularioAtributo
                        key={`a${dAtributo.apertura}`}
                        abierto={dAtributo.abierto}
                        atributo={dAtributo.registro}
                        tiposProducto={tiposProducto}
                        url={urls.index}
                        onCerrar={() => setDAtributo((d) => ({ ...d, abierto: false }))}
                    />
                    {actual && (
                        <FormularioValor
                            key={`v${dValor.apertura}`}
                            abierto={dValor.abierto}
                            atributo={actual}
                            valor={dValor.registro}
                            url={urls.index}
                            onCerrar={() => setDValor((d) => ({ ...d, abierto: false }))}
                        />
                    )}
                </>
            )}
        </AppLayout>
    );
}
