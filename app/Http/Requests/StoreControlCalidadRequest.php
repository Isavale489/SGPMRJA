<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * FEAT-006 — Validación de una inspección de control de calidad.
 */
class StoreControlCalidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cantidad_inspeccionada' => 'required|integer|min:1',
            'cantidad_aprobada'      => 'required|integer|min:0',
            'cantidad_rechazada'     => 'required|integer|min:0',
            'resultado'              => 'required|in:aprobado,rechazado,observado',
            // Motivo obligatorio salvo que el resultado sea "aprobado" (todo conforme).
            'observaciones'          => 'required_unless:resultado,aprobado|nullable|string|max:1000',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $data = $v->getData();
            $insp = (int) ($data['cantidad_inspeccionada'] ?? 0);
            $aprob = (int) ($data['cantidad_aprobada'] ?? 0);
            $rech = (int) ($data['cantidad_rechazada'] ?? 0);
            $resultado = $data['resultado'] ?? null;

            if ($aprob + $rech !== $insp) {
                $v->errors()->add('cantidad_inspeccionada', 'Aprobadas + rechazadas debe igualar las inspeccionadas.');
            }
            // Coherencia veredicto ↔ rechazadas
            if ($rech > 0 && $resultado === 'aprobado') {
                $v->errors()->add('resultado', 'Hay unidades rechazadas: el resultado no puede ser "aprobado".');
            }
            if ($rech === 0 && $resultado === 'rechazado') {
                $v->errors()->add('resultado', 'No hay unidades rechazadas: el resultado no puede ser "rechazado".');
            }
        });
    }

    public function messages(): array
    {
        return [
            'cantidad_inspeccionada.required' => 'Indica cuántas unidades inspeccionaste.',
            'cantidad_inspeccionada.min'      => 'Debes inspeccionar al menos una unidad.',
            'cantidad_aprobada.required'      => 'Indica las unidades aprobadas (conformes).',
            'cantidad_rechazada.required'     => 'Indica las unidades rechazadas (defectuosas).',
            'resultado.in'                    => 'El resultado no es válido.',
            'observaciones.required_unless'   => 'El motivo es obligatorio cuando hay unidades rechazadas u observadas.',
        ];
    }
}
