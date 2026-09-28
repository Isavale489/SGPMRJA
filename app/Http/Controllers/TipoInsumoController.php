<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Models\Insumo;
use App\Models\TipoInsumo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Catálogo gestionable de tipos de insumo. Se administra desde la página de
 * Insumos (diálogo "Tipos de insumo"), que recibe el catálogo como prop.
 */
class TipoInsumoController extends Controller
{
    use RespondeSegunCliente;

    /** El catálogo vive en la página de Insumos. */
    public function index()
    {
        return to_route('insumos.index');
    }

    private function validar(Request $request, ?TipoInsumo $tipo = null): string
    {
        $request->merge(['nombre' => trim((string) $request->input('nombre'))]);
        $request->validate([
            'nombre' => ['required', 'string', 'min:2', 'max:100', Rule::unique('tipo_insumo', 'nombre')->ignore($tipo)],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.unique' => 'Ya existe un tipo de insumo con este nombre.',
        ]);

        return $request->input('nombre');
    }

    public function store(Request $request)
    {
        TipoInsumo::create(['nombre' => $this->validar($request), 'activo' => true]);

        return $this->responder($request, 'Tipo de insumo creado correctamente.');
    }

    public function update(Request $request, TipoInsumo $tipoInsumo)
    {
        $nombreAnterior = $tipoInsumo->nombre;
        $nombreNuevo = $this->validar($request, $tipoInsumo);

        $tipoInsumo->update(['nombre' => $nombreNuevo]);

        // Propagar el renombrado a los insumos que usan este tipo (tipo es texto).
        if ($nombreAnterior !== $nombreNuevo) {
            Insumo::withTrashed()->where('tipo', $nombreAnterior)->update(['tipo' => $nombreNuevo]);
        }

        return $this->responder($request, 'Tipo de insumo actualizado correctamente.');
    }

    public function destroy(Request $request, TipoInsumo $tipoInsumo)
    {
        if ($tipoInsumo->insumos()->exists()) {
            return $this->rechazar($request, 'No se puede inhabilitar: hay insumos que usan este tipo.');
        }

        $tipoInsumo->delete();

        return $this->responder($request, 'Tipo de insumo inhabilitado correctamente.');
    }

    public function restore(Request $request, int $id)
    {
        TipoInsumo::onlyTrashed()->findOrFail($id)->restore();

        return $this->responder($request, 'Tipo de insumo restaurado correctamente.');
    }
}
