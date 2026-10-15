<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Alta/edición de insumo. También lo usan las altas rápidas de Compras y de
 * Movimientos de insumo (jQuery).
 *
 * - El código se guarda en MAYÚSCULAS (el input solo lo muestra así).
 * - `is_inventoriable` y `aplica_iva` valen true si no se envían: Movimientos
 *   no manda la bandera pero sí la existencia inicial (el insumo se tiene que
 *   poder mover). La página Inertia siempre manda las dos.
 * - Un insumo no inventariable no lleva stock (se excluye de la validación).
 */
class GuardarInsumoRequest extends FormRequest
{
    public const UNIDADES = ['Metro', 'Kg', 'Gramo', 'Unidad', 'Rollo', 'Cono', 'Docena'];

    public function authorize(): bool
    {
        return true; // CheckPermiso
    }

    protected function prepareForValidation(): void
    {
        $codigo = strtoupper(trim((string) $this->input('codigo')));
        $this->merge([
            'nombre' => trim((string) $this->input('nombre')),
            'codigo' => $codigo === '' ? null : $codigo,
            'is_inventoriable' => $this->boolean('is_inventoriable', true) ? 1 : 0,
            'aplica_iva' => $this->boolean('aplica_iva', true) ? 1 : 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'codigo' => ['nullable', 'string', 'min:2', 'max:8', 'regex:/^[A-Z0-9]+$/', Rule::unique('insumo', 'codigo')->ignore($this->route('insumo'))],
            // Del catálogo gestionable y activo (insumo.tipo guarda el nombre).
            'tipo' => ['required', 'string', Rule::exists('tipo_insumo', 'nombre')->where('activo', true)->whereNull('deleted_at')],
            'unidad_medida' => ['required', Rule::in(self::UNIDADES)],
            'is_inventoriable' => ['boolean'],
            'aplica_iva' => ['boolean'],
            'costo_unitario' => ['required', 'numeric', 'min:0.01'],
            'stock_actual' => ['exclude_if:is_inventoriable,0', 'nullable', 'numeric', 'min:0'],
            'stock_minimo' => ['exclude_if:is_inventoriable,0', 'nullable', 'numeric', 'min:0'],
            'stock_maximo' => ['exclude_if:is_inventoriable,0', 'nullable', 'numeric', 'min:0', 'gte:stock_minimo'],
        ];
    }

    public function attributes(): array
    {
        return [
            'unidad_medida' => 'unidad de medida',
            'costo_unitario' => 'costo unitario',
            'stock_actual' => 'existencia actual',
            'stock_minimo' => 'existencia mínima',
            'stock_maximo' => 'existencia máxima',
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.regex' => 'El código solo admite letras mayúsculas y números.',
            'codigo.unique' => 'Ya existe un insumo con este código.',
            'tipo.exists' => 'Elige un tipo de insumo del catálogo.',
            'stock_maximo.gte' => 'La existencia máxima no puede ser menor que la mínima.',
        ];
    }

    /** Datos a guardar: sin stock si no es inventariable. */
    public function datos(): array
    {
        $inventariable = (bool) $this->validated('is_inventoriable');

        return [
            ...$this->safe()->only(['nombre', 'tipo', 'unidad_medida', 'costo_unitario']),
            'is_inventoriable' => $inventariable,
            'aplica_iva' => (bool) $this->validated('aplica_iva'),
            'stock_actual' => $inventariable ? ($this->validated('stock_actual') ?? 0) : 0,
            'stock_minimo' => $inventariable ? ($this->validated('stock_minimo') ?? 0) : 0,
            'stock_maximo' => $inventariable ? ($this->validated('stock_maximo') ?? 0) : 0,
        ];
    }
}
