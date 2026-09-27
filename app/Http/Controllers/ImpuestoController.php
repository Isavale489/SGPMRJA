<?php

namespace App\Http\Controllers;

use App\Models\Impuesto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Gestión del catálogo de impuestos (tabla `impuesto`).
 *
 * Vive DENTRO del panel /configuracion (no es un módulo del sidebar). El IVA
 * es la fuente de verdad de la tasa para compras (Impuesto::tasaIva()); por eso
 * la fila con código IVA no se puede eliminar ni desactivar desde aquí.
 */
class ImpuestoController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['codigo'] = strtoupper(trim($data['codigo']));

        // `codigo` es UNIQUE a nivel de BD e incluye los soft-deleted (la
        // constraint no los excluye). La validación de unicidad solo mira los
        // activos (whereNull), así que un código reutilizado tras borrar pasaría
        // validación pero chocaría al INSERT (error 1062). Reusamos la fila
        // soft-deleted: la restauramos y actualizamos. Ver docs/conventions/softdeletes-unique.md.
        $previo = Impuesto::onlyTrashed()->where('codigo', $data['codigo'])->first();
        if ($previo) {
            $previo->restore();
            $previo->update($data);
        } else {
            Impuesto::create($data);
        }

        return response()->json(['success' => true, 'message' => 'Impuesto creado correctamente.']);
    }

    public function update(Request $request, Impuesto $impuesto)
    {
        $data = $this->validar($request, $impuesto);

        // El código del IVA es inmutable: es el único punto donde el sistema
        // sabe "cuál es el IVA" para el cálculo de compras.
        if ($impuesto->codigo === Impuesto::CODIGO_IVA) {
            $data['codigo'] = Impuesto::CODIGO_IVA;
            $data['estado'] = 'activo';
        } else {
            $data['codigo'] = strtoupper(trim($data['codigo']));
        }

        $impuesto->update($data);

        return response()->json(['success' => true, 'message' => 'Impuesto actualizado correctamente.']);
    }

    public function destroy(Impuesto $impuesto)
    {
        if ($impuesto->codigo === Impuesto::CODIGO_IVA) {
            return response()->json([
                'success' => false,
                'message' => 'El IVA no se puede eliminar: es el impuesto base de las compras.',
            ], 422);
        }

        $impuesto->delete();

        return response()->json(['success' => true, 'message' => 'Impuesto eliminado.']);
    }

    private function validar(Request $request, ?Impuesto $impuesto = null): array
    {
        $esIva = $impuesto && $impuesto->codigo === Impuesto::CODIGO_IVA;

        return $request->validate([
            'codigo' => [
                $esIva ? 'nullable' : 'required',
                'string',
                'max:20',
                Rule::unique('impuesto', 'codigo')->ignore($impuesto?->id)->whereNull('deleted_at'),
            ],
            'nombre'      => ['required', 'string', 'max:100'],
            'porcentaje'  => ['required', 'numeric', 'min:0', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'estado'      => ['required', Rule::in(['activo', 'inactivo'])],
        ], [], [
            'codigo'     => 'código',
            'porcentaje' => 'porcentaje',
        ]);
    }
}
