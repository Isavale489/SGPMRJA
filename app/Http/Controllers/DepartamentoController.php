<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Http\Requests\GuardarDepartamentoRequest;
use App\Models\Departamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepartamentoController extends Controller
{
    use RespondeSegunCliente;

    /**
     * Página Inertia del catálogo. El formulario de Empleados (Blade/jQuery)
     * pide la lista en JSON con $.get: se distingue por X-Inertia, no por
     * ajax() (Inertia también manda X-Requested-With).
     */
    public function index(Request $request): Response|JsonResponse
    {
        if ($request->ajax() && ! $this->esInertia($request)) {
            return response()->json(Departamento::withCount(['cargos', 'empleados'])->orderBy('nombre')->get());
        }

        $filtros = array_filter($request->only(['buscar', 'historial']), fn ($v) => $v !== null && $v !== '');
        $query = Departamento::withCount(['cargos', 'empleados'])->orderBy('nombre');
        if (! empty($filtros['historial'])) {
            $query->onlyTrashed();
        }
        if (! empty($filtros['buscar'])) {
            $query->where('nombre', 'like', '%'.trim($filtros['buscar']).'%');
        }

        return Inertia::render('Departamentos/Index', [
            'registros' => $query->paginate(15)->withQueryString()->through(fn (Departamento $d) => [
                'id' => $d->id,
                'nombre' => $d->nombre,
                'cargos' => $d->cargos_count,
                'empleados' => $d->empleados_count,
                'inhabilitado' => $d->trashed(),
            ]),
            'filtros' => (object) $filtros,
            'urls' => ['index' => route('departamentos.index', absolute: false)],
        ]);
    }

    public function store(GuardarDepartamentoRequest $request)
    {
        $departamento = Departamento::create(['nombre' => $request->validated('nombre'), 'activo' => true]);

        return $this->responder($request, 'Departamento creado correctamente.', [
            'success' => true, 'message' => 'Departamento creado correctamente.', 'departamento' => $departamento,
        ]);
    }

    public function update(GuardarDepartamentoRequest $request, Departamento $departamento)
    {
        $departamento->update(['nombre' => $request->validated('nombre')]);

        return $this->responder($request, 'Departamento actualizado correctamente.', [
            'success' => true, 'message' => 'Departamento actualizado correctamente.', 'departamento' => $departamento,
        ]);
    }

    public function destroy(Request $request, Departamento $departamento)
    {
        if ($departamento->cargos()->exists()) {
            return $this->rechazar($request, 'No se puede inhabilitar: el departamento tiene cargos asociados.');
        }
        if ($departamento->empleados()->exists()) {
            return $this->rechazar($request, 'No se puede inhabilitar: el departamento tiene empleados asociados.');
        }

        $departamento->delete();

        return $this->responder($request, 'Departamento inhabilitado correctamente.');
    }

    public function restore(Request $request, int $id)
    {
        Departamento::onlyTrashed()->findOrFail($id)->restore();

        return $this->responder($request, 'Departamento restaurado correctamente.');
    }
}
