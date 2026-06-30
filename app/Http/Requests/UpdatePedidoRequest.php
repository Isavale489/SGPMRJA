<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => 'required|exists:cliente,id',
            'fecha_pedido' => 'required|date',
            'fecha_entrega_estimada' => 'required|date|after_or_equal:fecha_pedido',
            'estado' => 'required|in:Pendiente,Procesando,Completado,Cancelado',
            'pagos' => 'nullable|array',
            'pagos.*.metodo' => 'required|in:efectivo,transferencia,pago_movil',
            'pagos.*.monto' => 'required|numeric|min:0',
            'pagos.*.banco_id' => 'nullable|exists:banco,id',
            'pagos.*.referencia' => 'nullable|string|max:255',
            'prioridad' => 'required|in:Normal,Alta,Urgente',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'nullable|required_without:productos.*.tipo_producto_id|integer|exists:producto,id',
            'productos.*.tipo_producto_id' => 'nullable|required_without:productos.*.producto_id|integer|exists:tipo_producto,id',
            'productos.*.insumo_tela_id' => 'nullable|integer|exists:insumo,id',
            'productos.*.atributo_valor_ids' => 'nullable|array',
            'productos.*.atributo_valor_ids.*' => 'integer|exists:atributo_valor,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'nullable|numeric|min:0',
            'productos.*.descripcion' => 'nullable|string|max:500',
            'productos.*.lleva_bordado' => 'nullable|boolean',
            'productos.*.bordados' => 'nullable|array|required_if:productos.*.lleva_bordado,true|min:1|max:' . parametro('cotizaciones.max_bordados_producto'),
            'productos.*.bordados.*.ubicacion_bordado_id' => 'nullable|exists:bordado_ubicacion,id',
            'productos.*.bordados.*.nombre_aplicado' => 'required|string|max:120',
            'productos.*.bordados.*.logo_id' => 'required|exists:logo,id',
            'productos.*.bordados.*.es_personalizada' => 'nullable|boolean',
            'productos.*.bordados.*.precio_aplicado' => 'required|numeric|min:0',
            'productos.*.bordados.*.cantidad' => 'nullable|integer|min:1',
            'productos.*.color_id' => ['nullable', 'integer', Rule::exists('color', 'id')],
            'productos.*.talla_id' => ['nullable', 'integer', Rule::exists('talla', 'id')],
            'productos.*.genero_id' => ['required', 'integer', Rule::exists('genero', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'productos.*.genero_id.required' => 'El género es obligatorio.',
            'productos.*.genero_id.exists' => 'El género seleccionado no es válido.',
            'productos.*.bordados.max' => 'No se pueden agregar más de ' . parametro('cotizaciones.max_bordados_producto') . ' bordados por producto.',
        ];
    }
}
