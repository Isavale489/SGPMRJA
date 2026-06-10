<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
            'numero_factura'              => [
                'nullable', 'string', 'max:30',
                // Una misma factura no puede repetirse para el mismo proveedor.
                // Ignora la compra en edición y los borradores con factura vacía (null).
                Rule::unique('compra', 'numero_factura')
                    ->where(fn($q) => $q
                        ->where('proveedor_id', $this->input('proveedor_id'))
                        ->whereNull('deleted_at'))
                    ->ignore($this->route('compra')),
            ],
            'fecha_compra'                => ['required', 'date', 'before_or_equal:today'],
            'observaciones'               => ['nullable', 'string', 'max:500'],
            'items'                       => ['required', 'array', 'min:1'],
            'items.*.insumo_id'           => ['required', 'integer', 'exists:insumo,id'],
            'items.*.cantidad'            => ['required', 'numeric', 'min:0.01'],
            'items.*.costo_unitario'      => ['required', 'numeric', 'min:0.01'],
            'items.*.aplica_iva'          => ['sometimes', 'boolean'],
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
            'numero_factura.unique'            => 'Ya existe una compra de este proveedor con ese número de factura.',
            'fecha_compra.required'            => 'La fecha de compra es obligatoria.',
            'fecha_compra.before_or_equal'     => 'La fecha de compra no puede ser futura.',
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
