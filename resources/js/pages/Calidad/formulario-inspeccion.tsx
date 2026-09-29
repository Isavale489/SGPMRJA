import { useForm } from '@inertiajs/react';
import { Minus, Plus, ShieldCheck, ShieldX } from 'lucide-react';
import { useEffect, useMemo } from 'react';

import { Campo } from '@/components/app/campo';
import { DialogoFormulario } from '@/components/app/dialogo-formulario';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { formatoFecha, formatoNumero } from '@/lib/formato';
import { cn } from '@/lib/utils';

import type { OrdenEnCola } from './Index';

/**
 * Reparte `total` defectuosas entre el equipo sin pasar lo que produjo cada
 * uno (mismo criterio que la vista anterior): se editan después si hace falta.
 */
function repartir(total: number, equipo: OrdenEnCola['equipo']): Record<number, number> {
    const out: Record<number, number> = Object.fromEntries(equipo.map((e) => [e.id, 0]));
    let resto = total;
    let activos = equipo.filter((e) => e.producida > 0);
    while (resto > 0 && activos.length) {
        const base = Math.max(1, Math.floor(resto / activos.length));
        for (const e of activos) {
            const cabe = Math.min(base, e.producida - (out[e.id] ?? 0), resto);
            out[e.id] = (out[e.id] ?? 0) + cabe;
            resto -= cabe;
            if (resto === 0) break;
        }
        activos = activos.filter((e) => e.producida - (out[e.id] ?? 0) > 0);
    }
    return out;
}

const RESULTADO = { aprobado: 'Aprobado', observado: 'Aprobado con observaciones', rechazado: 'Rechazado' } as const;

interface Props {
    orden: OrdenEnCola;
    pedido: string;
    url: string;
    onCerrar: () => void;
}

/** Errores que el formulario muestra en su propio campo; el resto va arriba. */
const EN_SU_CAMPO = ['cantidad_inspeccionada', 'cantidad_rechazada', 'observaciones', 'rechazos'];

export function FormularioInspeccion({ orden, pedido, url, onCerrar }: Props) {
    const form = useForm({
        inspeccionada: String(orden.cantidad_producida),
        rechazada: 0,
        observaciones: '',
        // El reparto inicial (todo en 0) ya forma parte del estado de partida:
        // si lo pusiera el efecto de abajo, el formulario nacería «con cambios».
        rechazos: (orden.equipo.length > 1 ? repartir(0, orden.equipo) : {}) as Record<number, number>,
    });
    const { data, setData } = form;
    const e = form.errors as Record<string, string | undefined>;
    const erroresRechazos = Object.entries(e).filter(([k, v]) => v && (k === 'rechazos' || k.startsWith('rechazos.'))).map(([, v]) => v as string);
    const erroresGenerales = Object.entries(e).filter(([k, v]) => v && !EN_SU_CAMPO.includes(k) && !k.startsWith('rechazos.')).map(([, v]) => v as string);

    const inspeccionada = Math.min(orden.cantidad_producida, Math.max(0, parseInt(data.inspeccionada, 10) || 0));
    const rechazada = Math.min(data.rechazada, inspeccionada);
    const conformes = inspeccionada - rechazada;
    const conEquipo = orden.equipo.length > 1 && rechazada > 0;
    const sumaAtribucion = Object.values(data.rechazos).reduce((a, n) => a + (n || 0), 0);

    // Al cambiar las defectuosas, re-repartir automáticamente entre el equipo.
    // Depende de lo elegido, no del valor ya topado: un valor a medio escribir en
    // «Inspeccionadas» no debe pisar el reparto que el usuario ajustó.
    useEffect(() => {
        if (orden.equipo.length > 1) setData('rechazos', repartir(Math.min(data.rechazada, orden.cantidad_producida), orden.equipo));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [data.rechazada]);

    const veredicto = useMemo(
        () =>
            rechazada > 0
                ? { tono: 'destructive', icono: ShieldX, titulo: 'Rechazo: la orden vuelve a producción', sub: `${rechazada} a reproceso; luego se re-inspecciona.` }
                : { tono: 'success', icono: ShieldCheck, titulo: 'Conforme', sub: 'La orden queda aprobada por calidad.' },
        [rechazada],
    );

    form.transform((d) => ({
        cantidad_inspeccionada: inspeccionada,
        cantidad_aprobada: conformes,
        cantidad_rechazada: rechazada,
        resultado: rechazada > 0 ? 'rechazado' : 'aprobado',
        observaciones: rechazada > 0 ? d.observaciones.trim() : null,
        rechazos: conEquipo ? Object.entries(d.rechazos).map(([empleado_id, cantidad]) => ({ empleado_id: Number(empleado_id), cantidad })) : [],
    }));

    const Icono = veredicto.icono;

    return (
        <DialogoFormulario
            abierto
            onCerrar={onCerrar}
            titulo="Registrar inspección"
            descripcion={`${orden.producto} · ${pedido} · Orden #${orden.id}`}
            sucio={form.isDirty}
            procesando={form.processing}
            textoGuardar={rechazada > 0 ? 'Registrar rechazo' : 'Aprobar inspección'}
            onGuardar={() => form.post(`${url}/${orden.id}/inspeccionar`, { preserveScroll: true, onSuccess: onCerrar })}
            className="max-h-[92svh] overflow-y-auto sm:max-w-lg"
        >
            {erroresGenerales.map((m) => <p key={m} role="alert" className="text-destructive text-sm">{m}</p>)}

            {/* En móvil, un campo por fila: con tres columnas el de Defectuosas (− número +) no se lee. */}
            <section className="grid gap-3 sm:grid-cols-3">
                <Campo etiqueta="Inspeccionadas" error={e.cantidad_inspeccionada} ayuda={`De ${formatoNumero(orden.cantidad_producida)} producidas`}>
                    <Input
                        type="number"
                        min={1}
                        max={orden.cantidad_producida}
                        inputMode="numeric"
                        value={data.inspeccionada}
                        onChange={(ev) => setData('inspeccionada', ev.target.value.replace(/\D/g, ''))}
                        // No se inspecciona más de lo producido (el servidor también lo valida). El
                        // tope va al salir del campo: al teclear, «18» camino de «8» no debe volverse «10».
                        onBlur={() => {
                            if (data.inspeccionada === '') return;
                            setData((d) => ({ ...d, inspeccionada: String(inspeccionada), rechazada: Math.min(d.rechazada, inspeccionada) }));
                        }}
                        className="tabular"
                    />
                </Campo>
                <Campo etiqueta="Defectuosas" error={e.cantidad_rechazada}>
                    {(control) => (
                        <div className="flex items-center gap-1">
                            <Button type="button" variant="outline" size="icon" onClick={() => setData('rechazada', Math.max(0, rechazada - 1))} aria-label="Quitar una defectuosa"><Minus /></Button>
                            <Input
                                {...control}
                                type="number"
                                min={0}
                                inputMode="numeric"
                                value={rechazada}
                                onChange={(ev) => setData('rechazada', Math.max(0, parseInt(ev.target.value, 10) || 0))}
                                className="tabular text-center"
                            />
                            <Button type="button" variant="outline" size="icon" onClick={() => setData('rechazada', Math.min(inspeccionada, rechazada + 1))} aria-label="Sumar una defectuosa"><Plus /></Button>
                        </div>
                    )}
                </Campo>
                <div className="grid content-start gap-1.5">
                    <span className="text-sm font-medium">Conformes</span>
                    <span className="text-success flex h-9 items-center text-lg font-semibold tabular" aria-live="polite">{formatoNumero(conformes)}</span>
                </div>
            </section>

            <div className="bg-muted flex h-2 overflow-hidden rounded-full" aria-hidden>
                <div className="bg-success transition-all duration-medio" style={{ width: inspeccionada ? `${(conformes / inspeccionada) * 100}%` : '0%' }} />
                <div className="bg-destructive transition-all duration-medio" style={{ width: inspeccionada ? `${(rechazada / inspeccionada) * 100}%` : '0%' }} />
            </div>

            <div className={cn('flex items-start gap-3 rounded-md border p-3', veredicto.tono === 'success' ? 'border-success/30 bg-success/8' : 'border-destructive/30 bg-destructive/8')} aria-live="polite">
                <Icono className={cn('mt-0.5 size-5 shrink-0', veredicto.tono === 'success' ? 'text-success' : 'text-destructive')} />
                <div>
                    <p className="text-sm font-medium">{veredicto.titulo}</p>
                    <p className="text-muted-foreground text-xs">{veredicto.sub}</p>
                </div>
            </div>

            {conEquipo && (
                <fieldset className="grid gap-2">
                    <legend className="mb-1 text-sm font-medium">¿De quién son las defectuosas? <span className="text-destructive">*</span></legend>
                    {orden.equipo.map((m) => (
                        <div key={m.id} className="flex items-center gap-2 text-sm">
                            <span className="flex-1 truncate">{m.nombre} <span className="text-muted-foreground text-xs">({formatoNumero(m.producida)} producidas)</span></span>
                            <Input
                                type="number"
                                min={0}
                                max={m.producida}
                                inputMode="numeric"
                                value={data.rechazos[m.id] ?? 0}
                                aria-label={`Defectuosas de ${m.nombre}`}
                                onChange={(ev) => setData('rechazos', { ...data.rechazos, [m.id]: Math.max(0, parseInt(ev.target.value, 10) || 0) })}
                                className="tabular w-24"
                            />
                        </div>
                    ))}
                    <p className={cn('text-xs', sumaAtribucion === rechazada ? 'text-muted-foreground' : 'text-destructive')}>
                        Atribuidas {formatoNumero(sumaAtribucion)} de {formatoNumero(rechazada)}
                    </p>
                    {erroresRechazos.map((m) => <p key={m} role="alert" className="text-destructive text-xs">{m}</p>)}
                </fieldset>
            )}

            {rechazada > 0 && (
                <Campo etiqueta="Motivo del rechazo" requerido error={e.observaciones}>
                    <Textarea rows={2} maxLength={1000} value={data.observaciones} onChange={(ev) => setData('observaciones', ev.target.value)} placeholder="Describe el defecto encontrado (costura, mancha, talla…)" />
                </Campo>
            )}

            {orden.historial.length > 0 && (
                <section className="grid gap-2">
                    <h3 className="text-sm font-medium">Inspecciones previas</h3>
                    <ul className="grid gap-1.5">
                        {orden.historial.map((h, i) => (
                            <li key={i} className="bg-muted/50 rounded-md p-2 text-xs">
                                <span className="font-medium">{RESULTADO[h.resultado]}</span>
                                <span className="text-muted-foreground"> · {h.fecha ? `${formatoFecha(h.fecha)} ${h.fecha.slice(11)}` : '—'} · {h.inspector ?? '—'} · {formatoNumero(h.aprobada)} conformes, {formatoNumero(h.rechazada)} defectuosas</span>
                                {h.observaciones && <p className="text-muted-foreground mt-0.5">{h.observaciones}</p>}
                            </li>
                        ))}
                    </ul>
                </section>
            )}
        </DialogoFormulario>
    );
}
