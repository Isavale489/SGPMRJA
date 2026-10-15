import { Plus, Star, X } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { cn } from '@/lib/utils';

export interface Telefono {
    numero: string;
    tipo: 'movil' | 'casa' | 'trabajo';
    es_principal: boolean;
}

const PREFIJOS = ['0412', '0422', '0414', '0424', '0416', '0426'];
const TIPOS = { movil: 'Móvil', casa: 'Casa', trabajo: 'Trabajo' } as const;
export const MAX_TELEFONOS = 3;

export const telefonoVacio = (principal: boolean): Telefono => ({ numero: '0424-', tipo: 'movil', es_principal: principal });

interface Props {
    valor: Telefono[];
    onChange: (telefonos: Telefono[]) => void;
    /** Errores del servidor: form.errors (se leen las claves telefonos / telefonos.N.campo). */
    errores: Record<string, string | undefined>;
}

/**
 * Hasta 3 teléfonos "PREFIJO-NÚMERO" (0424-1234567), con tipo y uno principal.
 * El servidor
 * normaliza y valida (Telefono::sincronizar, GuardarProveedorRequest).
 */
export function CampoTelefonos({ valor, onChange, errores }: Props) {
    const cambiar = (i: number, parcial: Partial<Telefono>) =>
        onChange(valor.map((t, j) => (j === i ? { ...t, ...parcial } : t)));
    const principal = (i: number) => onChange(valor.map((t, j) => ({ ...t, es_principal: j === i })));
    const quitar = (i: number) => {
        const resto = valor.filter((_, j) => j !== i);
        if (resto.length && !resto.some((t) => t.es_principal)) resto[0] = { ...resto[0]!, es_principal: true };
        onChange(resto);
    };

    return (
        <fieldset className="grid gap-2">
            <legend className="mb-1.5 text-sm font-medium">
                Teléfonos <span className="text-destructive">*</span>
            </legend>
            {valor.map((t, i) => {
                const [prefijo = '0424', numero = ''] = t.numero.split('-');
                // Un fijo existente (p. ej. 0255) se conserva aunque no esté en la lista de móviles.
                const prefijos = PREFIJOS.includes(prefijo) ? PREFIJOS : [prefijo, ...PREFIJOS];
                const error = errores[`telefonos.${i}.numero`] ?? errores[`telefonos.${i}.tipo`];

                return (
                    <div key={i} className="grid gap-1">
                        <div className="flex flex-wrap items-center gap-2 sm:flex-nowrap">
                            <Select value={t.tipo} onValueChange={(v) => cambiar(i, { tipo: v as Telefono['tipo'] })}>
                                <SelectTrigger className="w-28" aria-label={`Tipo del teléfono ${i + 1}`}>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {Object.entries(TIPOS).map(([v, etiqueta]) => (
                                        <SelectItem key={v} value={v}>{etiqueta}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <Select value={prefijo} onValueChange={(p) => cambiar(i, { numero: `${p}-${numero}` })}>
                                <SelectTrigger className="w-24" aria-label={`Prefijo del teléfono ${i + 1}`}>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {prefijos.map((p) => (
                                        <SelectItem key={p} value={p}>{p}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <Input
                                inputMode="numeric"
                                placeholder="1234567"
                                maxLength={7}
                                value={numero}
                                aria-label={`Número del teléfono ${i + 1}`}
                                aria-invalid={error ? true : undefined}
                                onChange={(e) => cambiar(i, { numero: `${prefijo}-${e.target.value.replace(/\D/g, '').slice(0, 7)}` })}
                                className="tabular min-w-28 flex-1"
                            />
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                onClick={() => principal(i)}
                                aria-pressed={t.es_principal}
                                aria-label={t.es_principal ? 'Teléfono principal' : 'Marcar como principal'}
                                title={t.es_principal ? 'Principal' : 'Marcar como principal'}
                            >
                                <Star className={cn('size-4', t.es_principal ? 'fill-warning text-warning' : 'text-muted-foreground')} />
                            </Button>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                onClick={() => quitar(i)}
                                disabled={valor.length === 1}
                                aria-label={`Quitar teléfono ${i + 1}`}
                            >
                                <X className="size-4" />
                            </Button>
                        </div>
                        {error && <p className="text-destructive text-xs">{error}</p>}
                    </div>
                );
            })}
            {errores.telefonos && <p className="text-destructive text-xs">{errores.telefonos}</p>}
            <Button
                type="button"
                variant="outline"
                size="sm"
                className="justify-self-start"
                disabled={valor.length >= MAX_TELEFONOS}
                onClick={() => onChange([...valor, telefonoVacio(valor.length === 0)])}
            >
                <Plus /> Agregar teléfono
            </Button>
        </fieldset>
    );
}
