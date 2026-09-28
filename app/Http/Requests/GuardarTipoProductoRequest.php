<?php

namespace App\Http\Requests;

use App\Models\TipoProducto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Alta/edición de un tipo de producto (la unidad de catálogo, FEAT-003).
 *
 * El `prefijo` forma parte del SKU: se fija al crear y **no cambia después**
 * (docs/conventions/code-immutability.md). En edición se ignora aunque llegue,
 * para que un HTML manipulado no pueda reescribir los SKU del tipo.
 */
class GuardarTipoProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // CheckPermiso
    }

    private function tipo(): ?TipoProducto
    {
        $t = $this->route('tipoProducto');

        return $t instanceof TipoProducto ? $t : null;
    }

    protected function prepareForValidation(): void
    {
        $datos = ['nombre' => trim((string) $this->input('nombre'))];
        if (! $this->tipo()) {
            $datos['prefijo'] = strtoupper(trim((string) $this->input('prefijo')));
        }
        $this->merge($datos);
    }

    public function rules(): array
    {
        $tipo = $this->tipo();

        return [
            'nombre' => ['required', 'string', 'max:100', Rule::unique('tipo_producto', 'nombre')->ignore($tipo)],
            // Solo al crear; en edición `validated()` no lo incluye y el prefijo queda como estaba.
            ...($tipo ? [] : ['prefijo' => ['required', 'string', 'max:5', 'alpha', Rule::unique('tipo_producto', 'prefijo')]]),
            'descripcion' => ['nullable', 'string', 'max:500'],
            'precio_confeccion' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'requiere_tela' => ['nullable', 'boolean'],
            'requiere_produccion' => ['nullable', 'boolean'],
            'consumo_tela_por_unidad' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'imagen' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp,bmp,avif', 'max:10240'],
            'atributos' => ['nullable', 'array'],
            'atributos.*.id' => ['required_with:atributos', 'integer', 'exists:atributo,id'],
            'atributos.*.orden' => ['required_with:atributos', 'integer', 'min:1', 'max:99'],
            'insumos_default' => ['nullable', 'array'],
            'insumos_default.*.id' => ['required_with:insumos_default', 'integer', 'exists:insumo,id'],
            'insumos_default.*.cantidad_estimada' => ['required_with:insumos_default', 'numeric', 'min:0.01'],
            'telas' => ['nullable', 'array'],
            // Una "tela permitida" debe ser un insumo de tipo Tela.
            'telas.*' => ['integer', Rule::exists('insumo', 'id')->where('tipo', 'Tela')],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe un tipo con este nombre.',
            'prefijo.required' => 'El prefijo de código es obligatorio.',
            'prefijo.unique' => 'Ya existe un tipo con este prefijo.',
            'prefijo.alpha' => 'El prefijo solo puede contener letras.',
            'prefijo.max' => 'El prefijo no puede tener más de 5 caracteres.',
            'imagen.image' => 'El archivo debe ser una imagen válida.',
            'imagen.mimes' => 'Formato no permitido. Use JPG, PNG, GIF, WEBP, BMP o AVIF.',
            'imagen.max' => 'La imagen no puede superar 10MB.',
            'telas.*.exists' => 'Solo se pueden permitir insumos de tipo Tela.',
            'atributos.*.orden.min' => 'El orden de cada atributo debe ser 1 o mayor.',
            'insumos_default.*.cantidad_estimada.min' => 'La cantidad por unidad debe ser mayor a 0.',
        ];
    }

    public function attributes(): array
    {
        return ['precio_confeccion' => 'precio de confección', 'consumo_tela_por_unidad' => 'consumo de tela por unidad'];
    }
}
