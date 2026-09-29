<?php

namespace App\Http\Requests;

use App\Services\BordadoPricingService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Alta y edición de una cotización (antes, validación inline repetida en
 * store/update de CotizacionController). El estado NO se recibe: cambia solo
 * por sus acciones (aprobar, cancelar, reactivar, convertir).
 */
class GuardarCotizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => 'required|exists:cliente,id',
            'fecha_cotizacion' => 'required|date',
            'fecha_validez' => 'required|date|after_or_equal:fecha_cotizacion',
            'prioridad' => 'nullable|in:Normal,Alta,Urgente',
            'notas' => 'nullable|string|max:2000',
            'condiciones_terminos' => 'nullable|string',
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
            'productos.*.talla_id' => ['required', 'integer', Rule::exists('talla', 'id')],
            'productos.*.color_id' => ['nullable', 'integer', Rule::exists('color', 'id')],
            'productos.*.genero_id' => ['required', 'integer', Rule::exists('genero', 'id')],
            'productos.*.insumos' => 'nullable|array',
            'productos.*.insumos.*.id' => 'required|exists:insumo,id',
            'productos.*.insumos.*.cantidad_estimada' => 'required|numeric|min:0.01',
            'productos.*.bordados' => 'nullable|array|required_if:productos.*.lleva_bordado,true|min:1',
            'productos.*.bordados.*.ubicacion_bordado_id' => 'nullable|exists:bordado_ubicacion,id',
            'productos.*.bordados.*.nombre_aplicado' => 'required|string|max:120',
            'productos.*.bordados.*.logo_id' => 'nullable|exists:logo,id',
            'productos.*.bordados.*.es_personalizada' => 'nullable|boolean',
            'productos.*.bordados.*.precio_aplicado' => 'required|numeric|min:0',
            'productos.*.bordados.*.cantidad' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Tope de bordados por prenda: la SUMA de cantidades de cada línea de
     * bordado (una ubicación con cantidad 10 son 10 bordados).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $productos = $this->input('productos');
            if (! is_array($productos)) {
                return;
            }
            $max = (int) parametro('cotizaciones.max_bordados_producto');
            foreach (BordadoPricingService::indicesQueExcedenMaximo($productos, $max) as $i) {
                $v->errors()->add("productos.$i.bordados", "No se pueden agregar más de {$max} bordados por producto.");
            }
        });
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'fecha_cotizacion.required' => 'La fecha de cotización es obligatoria.',
            'fecha_cotizacion.date' => 'La fecha de cotización debe ser una fecha válida.',
            'fecha_validez.required' => 'La fecha de validez es obligatoria.',
            'fecha_validez.date' => 'La fecha de validez debe ser una fecha válida.',
            'fecha_validez.after_or_equal' => 'La fecha de validez debe ser igual o posterior a la fecha de cotización.',
            'prioridad.in' => 'La prioridad no es válida.',
            'productos.required' => 'Debe agregar al menos un producto.',
            'productos.min' => 'Debe agregar al menos un producto.',
            'productos.*.producto_id.required_without' => 'Debe seleccionar un producto o configurar una variante (tipo).',
            'productos.*.producto_id.exists' => 'El producto seleccionado no existe.',
            'productos.*.tipo_producto_id.required_without' => 'Debe seleccionar un producto o configurar una variante (tipo).',
            'productos.*.tipo_producto_id.exists' => 'El tipo de producto seleccionado no existe.',
            'productos.*.cantidad.required' => 'La cantidad es obligatoria.',
            'productos.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'productos.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
            'productos.*.precio_unitario.numeric' => 'El precio debe ser un número.',
            'productos.*.precio_unitario.min' => 'El precio no puede ser negativo.',
            'productos.*.descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
            'productos.*.bordados.*.logo_id.exists' => 'El logo seleccionado no existe en el catálogo.',
            'productos.*.bordados.required_if' => 'Debe seleccionar al menos una ubicación de bordado.',
            'productos.*.bordados.min' => 'Debe seleccionar al menos una ubicación de bordado.',
            'productos.*.bordados.*.nombre_aplicado.required' => 'Cada bordado debe tener un nombre de ubicación.',
            'productos.*.bordados.*.precio_aplicado.required' => 'Cada bordado debe tener un precio aplicado.',
            'productos.*.bordados.*.precio_aplicado.numeric' => 'El precio aplicado de cada bordado debe ser numérico.',
            'productos.*.bordados.*.precio_aplicado.min' => 'El precio aplicado de cada bordado no puede ser negativo.',
            'productos.*.bordados.*.cantidad.min' => 'La cantidad de cada bordado debe ser al menos 1.',
            'productos.*.talla_id.required' => 'La talla es obligatoria.',
            'productos.*.talla_id.exists' => 'La talla seleccionada no es válida.',
            'productos.*.color_id.exists' => 'El color seleccionado no es válido.',
            'productos.*.genero_id.required' => 'El género es obligatorio.',
            'productos.*.genero_id.exists' => 'El género seleccionado no es válido.',
            'productos.*.insumos.*.id.required' => 'Debe seleccionar un insumo.',
            'productos.*.insumos.*.id.exists' => 'El insumo seleccionado no existe.',
            'productos.*.insumos.*.cantidad_estimada.required' => 'La cantidad estimada del insumo es obligatoria.',
            'productos.*.insumos.*.cantidad_estimada.numeric' => 'La cantidad estimada debe ser un número.',
            'productos.*.insumos.*.cantidad_estimada.min' => 'La cantidad estimada debe ser mayor a 0.',
        ];
    }
}
