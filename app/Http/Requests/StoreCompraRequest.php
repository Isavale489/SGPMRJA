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
                'nullable', 'string', 'max:10',
                // Solo dígitos y guiones (ej. 0001-0456).
                'regex:/^[0-9\-]+$/',
                // Una misma factura no puede repetirse para el mismo proveedor.
                // Ignora la compra en edición y los borradores con factura vacía (null).
                Rule::unique('compra', 'numero_factura')
                    ->where(fn($q) => $q
                        ->where('proveedor_id', $this->input('proveedor_id'))
                        ->whereNull('deleted_at'))
                    ->ignore($this->route('compra')),
            ],
            'fecha_compra'                => ['required', 'date', 'before_or_equal:today'],
            // Tasa BCV (USD/VES) con la que se convierte el costo en Bs a USD.
            'tasa_cambio'                 => ['required', 'numeric', 'min:0.0001'],
            'observaciones'               => ['nullable', 'string', 'max:500'],
            'items'                       => ['required', 'array', 'min:1'],
            'items.*.insumo_id'           => [
                'required', 'integer',
                // El insumo debe existir, estar habilitado (estado=1) y ser
                // inventariable: solo esos pueden recibir movimientos de stock.
                Rule::exists('insumo', 'id')
                    ->where('estado', 1)
                    ->where('is_inventoriable', 1),
            ],
            'items.*.cantidad'            => ['required', 'numeric', 'min:0.01'],
            // El costo se ingresa en bolívares (lo que se paga al proveedor);
            // el USD se deriva en el servicio dividiendo por la tasa.
            'items.*.costo_unitario_bs'   => ['required', 'numeric', 'min:0.01'],
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
            'numero_factura.regex'             => 'El número de factura solo puede contener dígitos y guiones.',
            'numero_factura.max'               => 'El número de factura no puede superar los 10 caracteres.',
            'fecha_compra.required'            => 'La fecha de compra es obligatoria.',
            'fecha_compra.before_or_equal'     => 'La fecha de compra no puede ser futura.',
            'tasa_cambio.required'             => 'Ingrese la tasa de cambio (Bs por USD) de la compra.',
            'tasa_cambio.min'                  => 'La tasa de cambio debe ser mayor a cero.',
            'items.required'                   => 'Debe agregar al menos un insumo.',
            'items.min'                        => 'Debe agregar al menos un insumo.',
            'items.*.insumo_id.required'       => 'Seleccione el insumo en cada fila.',
            'items.*.insumo_id.exists'         => 'Uno de los insumos seleccionados no existe, está inhabilitado o no es inventariable.',
            'items.*.cantidad.required'        => 'Ingrese la cantidad para cada ítem.',
            'items.*.cantidad.min'             => 'La cantidad debe ser mayor a cero.',
            'items.*.costo_unitario_bs.required' => 'Ingrese el costo en Bs para cada ítem.',
            'items.*.costo_unitario_bs.min'      => 'El costo en Bs debe ser mayor a cero.',
        ];
    }
}
