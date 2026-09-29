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

        return [
            ...($pedido ? [] : ['cotizacion_id' => ['required', 'integer', 'exists:cotizacion,id']]),
            'fecha_entrega_estimada' => ['required', 'date', 'after_or_equal:'.$minimoEntrega],
            'prioridad' => ['required', Rule::in(['Normal', 'Alta', 'Urgente'])],
            'pagos' => ['present', 'array'],
            'pagos.*.metodo' => ['required', Rule::in(PagoPedido::METODOS)],
            'pagos.*.monto' => ['required', 'numeric', 'gt:0'],
            'pagos.*.banco_id' => ['nullable', 'required_unless:pagos.*.metodo,efectivo', 'integer', Rule::exists('banco', 'id')->whereNull('deleted_at')],
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
            'pagos.*.monto.gt' => 'El monto debe ser mayor a cero.',
            'pagos.*.banco_id.required_unless' => 'Elige el banco.',
            'pagos.*.banco_id.exists' => 'El banco no es válido.',
            'pagos.*.referencia.required_unless' => 'Indica la referencia.',
        ];
    }
}
