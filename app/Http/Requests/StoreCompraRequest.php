<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proveedor_id'                => ['required', 'integer', 'exists:proveedor,id'],
            'numero_factura'              => ['nullable', 'string', 'max:30'],
            'fecha_compra'                => ['required', 'date'],
            'fecha_vencimiento'           => ['nullable', 'date', 'after_or_equal:fecha_compra', 'required_if:tipo_pago,credito'],
            'tipo_pago'                   => ['required', 'in:contado,credito'],
            'iva_porcentaje'              => ['required', 'numeric', 'min:0', 'max:100'],
            'observaciones'               => ['nullable', 'string', 'max:500'],
            'items'                       => ['required', 'array', 'min:1'],
            'items.*.insumo_id'           => ['required', 'integer', 'exists:insumo,id'],
            'items.*.cantidad'            => ['required', 'numeric', 'min:0.01'],
            'items.*.costo_unitario'      => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $items = $this->input('items', []);
            $ids   = array_column($items, 'insumo_id');
            if (count($ids) !== count(array_unique($ids))) {
                $v->errors()->add('items', 'No se puede incluir el mismo insumo más de una vez en la misma compra.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'proveedor_id.required'            => 'Seleccione un proveedor.',
            'proveedor_id.exists'              => 'El proveedor seleccionado no existe.',
            'fecha_compra.required'            => 'La fecha de compra es obligatoria.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento no puede ser anterior a la fecha de compra.',
            'fecha_vencimiento.required_if'    => 'La fecha de vencimiento es obligatoria para pagos a crédito.',
            'tipo_pago.required'               => 'Seleccione el tipo de pago.',
            'tipo_pago.in'                     => 'El tipo de pago debe ser contado o crédito.',
            'iva_porcentaje.required'           => 'Indique el porcentaje de IVA.',
            'iva_porcentaje.min'                => 'El IVA no puede ser negativo.',
            'iva_porcentaje.max'                => 'El IVA no puede superar 100%.',
            'items.required'                   => 'Debe agregar al menos un insumo.',
            'items.min'                        => 'Debe agregar al menos un insumo.',
            'items.*.insumo_id.required'       => 'Seleccione el insumo en cada fila.',
            'items.*.insumo_id.exists'         => 'Uno de los insumos seleccionados no existe.',
            'items.*.cantidad.required'        => 'Ingrese la cantidad para cada ítem.',
            'items.*.cantidad.min'             => 'La cantidad debe ser mayor a cero.',
            'items.*.costo_unitario.required'  => 'Ingrese el costo unitario para cada ítem.',
            'items.*.costo_unitario.min'       => 'El costo unitario debe ser mayor a cero.',
        ];
    }
}
