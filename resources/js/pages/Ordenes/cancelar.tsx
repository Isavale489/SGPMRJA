import { useForm } from '@inertiajs/react';

import { Campo } from '@/components/app/campo';
import { DialogoFormulario } from '@/components/app/dialogo-formulario';
import { Textarea } from '@/components/ui/textarea';

import type { OrdenFila } from './tipos';

/**
 * Pendiente: se repone el stock comprometido. En proceso o finalizada: el
 * material ya se cortó (merma), no se repone y hay que indicar el motivo.
 */
export function CancelarOrden({ orden, onCerrar, url }: { orden: OrdenFila; onCerrar: () => void; url: string }) {
    const repone = orden.estado === 'Pendiente';
    const form = useForm({ motivo_cancelacion: '' });

    return (
        <DialogoFormulario
            abierto
            onCerrar={onCerrar}
            titulo={`¿Cancelar la orden #${orden.id}?`}
            descripcion={repone ? 'Los insumos comprometidos vuelven al inventario.' : 'El material ya se cortó: se registra como merma y no vuelve al inventario.'}
            sucio={form.isDirty}
            procesando={form.processing}
            textoGuardar="Cancelar orden"
            textoCerrar="Volver"
            onGuardar={() => form.patch(`${url}/${orden.id}/cancelar`, { preserveScroll: true, preserveState: true, onSuccess: onCerrar })}
        >
            <Campo etiqueta="Motivo" requerido={!repone} error={form.errors.motivo_cancelacion}>
                <Textarea rows={3} maxLength={500} value={form.data.motivo_cancelacion} onChange={(e) => form.setData('motivo_cancelacion', e.target.value)} placeholder={repone ? 'Opcional' : 'Por qué se cancela y qué pasó con el material'} />
            </Campo>
        </DialogoFormulario>
    );
}
