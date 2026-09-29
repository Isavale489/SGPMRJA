<?php

namespace App\Http\Requests;

use App\Models\PagoPedido;
use App\Models\Pedido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Crear un pedido (desde una cotización) o editar uno existente. Las líneas NO
 * se reciben: al crear se copian de la cotización en el servidor y después
 * quedan congeladas. Aquí se valida lo que antes solo validaba el navegador:
 * montos, banco y referencia de cada pago, un solo pago en efectivo y la fecha
 * de entrega.
 */
class GuardarPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    private function pedido(): ?Pedido
    {
        $ruta = $this->route('pedido');

        return $ruta instanceof Pedido ? $ruta : (is_numeric($ruta) ? Pedido::find($ruta) : null);
    }

    public function rules(): array
    {
        $pedido = $this->pedido();
        // Al crear, la entrega no puede ser antes de hoy; al editar, no antes de la fecha del pedido.
        $minimoEntrega = $pedido?->fecha_pedido?->toDateString() ?? 'today';

        // Un banco inhabilitado sigue valiendo en los pagos que YA lo usaban (no se falsea el historial).
        $bancosPrevios = $pedido ? $pedido->pagos()->whereNotNull('banco_id')->pluck('banco_id')->all() : [];
        // Completado: solo pagos (entrega y prioridad no cambian; una entrega legada no bloquea el saldo).
        $soloPagos = $pedido?->estado === 'Completado';

        return [
            ...($pedido ? [] : ['cotizacion_id' => ['required', 'integer', 'exists:cotizacion,id']]),
            'fecha_entrega_estimada' => $soloPagos ? ['exclude'] : ['required', 'date', 'after_or_equal:'.$minimoEntrega],
            'prioridad' => $soloPagos ? ['exclude'] : ['required', Rule::in(['Normal', 'Alta', 'Urgente'])],
            'pagos' => ['present', 'array'],
            'pagos.*.metodo' => ['required', Rule::in(PagoPedido::METODOS)],
            'pagos.*.monto' => ['required', 'numeric', 'min:0.01'],
            'pagos.*.banco_id' => ['nullable', 'required_unless:pagos.*.metodo,efectivo', 'integer', Rule::exists('banco', 'id')->where(fn ($q) => $q->where(fn ($w) => $w->whereNull('deleted_at')->orWhereIn('id', $bancosPrevios)))],
            'pagos.*.referencia' => ['nullable', 'required_unless:pagos.*.metodo,efectivo', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $pagos = $this->input('pagos');
            if (is_array($pagos) && count(array_filter($pagos, fn ($p) => is_array($p) && ($p['metodo'] ?? null) === 'efectivo')) > 1) {
                $v->errors()->add('pagos', 'Registra el efectivo en un solo pago.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'cotizacion_id.required' => 'Elige la cotización aprobada de la que sale el pedido.',
            'fecha_entrega_estimada.required' => 'Indica la fecha de entrega.',
            'fecha_entrega_estimada.after_or_equal' => $this->pedido() ? 'La entrega no puede ser antes de la fecha del pedido.' : 'La entrega no puede ser antes de hoy.',
            'prioridad.in' => 'La prioridad no es válida.',
            'pagos.present' => 'Registra al menos el abono mínimo.',
            'pagos.*.metodo.in' => 'El método de pago no es válido.',
            'pagos.*.monto.min' => 'El monto debe ser de al menos $0,01.',
            'pagos.*.banco_id.required_unless' => 'Elige el banco.',
            'pagos.*.banco_id.exists' => 'El banco no es válido.',
            'pagos.*.referencia.required_unless' => 'Indica la referencia.',
        ];
    }
}
