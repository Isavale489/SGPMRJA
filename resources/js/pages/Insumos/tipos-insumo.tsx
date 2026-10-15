import { router, useForm } from '@inertiajs/react';
import { Check, Pencil, Plus, RotateCcw, Tags, Trash2, X } from 'lucide-react';
import { useState, type FormEvent } from 'react';

import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { usePermisos } from '@/hooks/use-permisos';
import { formatoNumero } from '@/lib/formato';
import { cn } from '@/lib/utils';

import type { TipoInsumoFila } from './tipos';

const RECARGA = { preserveScroll: true, preserveState: true };

/** Una fila del catálogo; en edición, el nombre pasa a ser un campo. */
function Fila({ tipo, url, gestionar }: { tipo: TipoInsumoFila; url: string; gestionar: boolean }) {
    const [editando, setEditando] = useState(false);
    const form = useForm({ nombre: tipo.nombre });
    const guardar = (e: FormEvent) => {
        e.preventDefault();
        form.put(`${url}/${tipo.id}`, { ...RECARGA, onSuccess: () => setEditando(false) });
    };

    if (editando) {
        return (
            <li>
                <form onSubmit={guardar} className="grid gap-1 py-2">
                    <div className="flex gap-2">
                        <Input value={form.data.nombre} maxLength={100} autoFocus aria-label={`Nuevo nombre de ${tipo.nombre}`} aria-invalid={form.errors.nombre ? true : undefined} onChange={(e) => form.setData('nombre', e.target.value)} />
                        <Button type="submit" size="icon" disabled={form.processing} aria-label="Guardar nombre"><Check /></Button>
                        <Button type="button" size="icon" variant="ghost" onClick={() => { form.reset(); form.clearErrors(); setEditando(false); }} aria-label="Cancelar"><X /></Button>
                    </div>
                    {form.errors.nombre && <p className="text-destructive text-xs">{form.errors.nombre}</p>}
                    {tipo.insumos > 0 && <p className="text-muted-foreground text-xs">Se renombra también en sus {formatoNumero(tipo.insumos)} insumo(s).</p>}
                </form>
            </li>
        );
    }

    return (
        <li className="flex min-h-11 items-center gap-2 py-1">
            <span className={cn('min-w-0 flex-1 truncate text-sm', tipo.inhabilitado && 'text-muted-foreground line-through')}>{tipo.nombre}</span>
            <span className="text-muted-foreground tabular text-xs">{formatoNumero(tipo.insumos)} insumo{tipo.insumos === 1 ? '' : 's'}</span>
            {gestionar && (tipo.inhabilitado ? (
                <Button variant="ghost" size="icon" aria-label={`Restaurar ${tipo.nombre}`} onClick={() => router.patch(`${url}/${tipo.id}/restore`, {}, RECARGA)}>
                    <RotateCcw />
                </Button>
            ) : (
                <>
                    <Button variant="ghost" size="icon" aria-label={`Renombrar ${tipo.nombre}`} onClick={() => setEditando(true)}><Pencil /></Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        aria-label={`Inhabilitar ${tipo.nombre}`}
                        title={tipo.insumos > 0 ? 'Tiene insumos: no se puede inhabilitar' : undefined}
                        disabled={tipo.insumos > 0}
                        onClick={() => router.delete(`${url}/${tipo.id}`, RECARGA)}
                    >
                        <Trash2 />
                    </Button>
                </>
            ))}
        </li>
    );
}

/** Catálogo gestionable de tipos de insumo (insumo.tipo guarda el nombre). */
export function TiposInsumo({ tipos, url }: { tipos: TipoInsumoFila[]; url: string }) {
    const { puede } = usePermisos();
    const gestionar = puede('tipo-insumos.gestionar');
    const form = useForm({ nombre: '' });
    const agregar = (e: FormEvent) => {
        e.preventDefault();
        form.post(url, { ...RECARGA, onSuccess: () => form.reset() });
    };
    const activos = tipos.filter((t) => !t.inhabilitado);
    const inhabilitados = tipos.filter((t) => t.inhabilitado);

    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button variant="ghost"><Tags /> Tipos de insumo</Button>
            </DialogTrigger>
            <DialogContent className="max-h-[88svh] overflow-y-auto sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Tipos de insumo</DialogTitle>
                    <DialogDescription>Catálogo con el que se clasifica cada insumo. Un tipo con insumos no se puede inhabilitar.</DialogDescription>
                </DialogHeader>
                {gestionar && (
                    <form onSubmit={agregar} className="grid gap-1">
                        <div className="flex gap-2">
                            <Input value={form.data.nombre} maxLength={100} placeholder="Nuevo tipo (p. ej. Entretela)" aria-label="Nombre del nuevo tipo" aria-invalid={form.errors.nombre ? true : undefined} onChange={(e) => form.setData('nombre', e.target.value)} />
                            <Button type="submit" disabled={form.processing || !form.data.nombre.trim()}><Plus /> Agregar</Button>
                        </div>
                        {form.errors.nombre && <p className="text-destructive text-xs">{form.errors.nombre}</p>}
                    </form>
                )}
                <ul className="divide-border divide-y" aria-label="Tipos activos">
                    {activos.map((t) => <Fila key={`${t.id}-${t.nombre}`} tipo={t} url={url} gestionar={gestionar} />)}
                </ul>
                {inhabilitados.length > 0 && (
                    <section className="grid gap-1">
                        <h3 className="text-muted-foreground text-xs font-medium uppercase tracking-wide">Inhabilitados</h3>
                        <ul className="divide-border divide-y" aria-label="Tipos inhabilitados">
                            {inhabilitados.map((t) => <Fila key={t.id} tipo={t} url={url} gestionar={gestionar} />)}
                        </ul>
                    </section>
                )}
            </DialogContent>
        </Dialog>
    );
}
