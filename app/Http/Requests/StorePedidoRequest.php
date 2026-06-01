<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cotizacion_id' => [
                'nullable',
                'exists:cotizacion,id',
                Rule::unique('pedido', 'cotizacion_id'),
            ],
            'cliente_id' => 'required|exists:cliente,id',
            'fecha_pedido' => 'required|date',
            'fecha_entrega_estimada' => 'nullable|date|after_or_equal:fecha_pedido',
            'pagos' => 'nullable|array',
            'pagos.*.metodo' => 'required|in:efectivo,transferencia,pago_movil',
            'pagos.*.monto' => 'required|numeric|min:0',
            'pagos.*.banco_id' => 'nullable|exists:banco,id',
            'pagos.*.referencia' => 'nullable|string|max:255',
            'prioridad' => 'required|in:Normal,Alta,Urgente',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:producto,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'nullable|numeric|min:0',
            'productos.*.descripcion' => 'nullable|string|max:500',
            'productos.*.lleva_bordado' => 'nullable|boolean',
            'productos.*.bordados' => 'nullable|array|required_if:productos.*.lleva_bordado,true|min:1',
            'productos.*.bordados.*.ubicacion_bordado_id' => 'nullable|exists:bordado_ubicacion,id',
            'productos.*.bordados.*.nombre_aplicado' => 'required|string|max:120',
            'productos.*.bordados.*.logo_id' => 'required|exists:logo,id',
            'productos.*.bordados.*.es_personalizada' => 'nullable|boolean',
            'productos.*.bordados.*.precio_aplicado' => 'required|numeric|min:0',
            'productos.*.bordados.*.cantidad' => 'nullable|integer|min:1',
            'productos.*.color_id' => ['nullable', 'integer', Rule::exists('color', 'id')],
            'productos.*.talla_id' => ['nullable', 'integer', Rule::exists('talla', 'id')],
        ];
    }
}
