<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Http\Requests\GuardarCargoRequest;
use App\Models\Cargo;
use App\Models\Departamento;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CargoController extends Controller
{
    use RespondeSegunCliente;

    public function index(Request $request): Response
    {
        $filtros = array_filter($request->only(['buscar', 'departamento', 'historial']), fn ($v) => $v !== null && $v !== '');
        $query = Cargo::with('departamento')->withCount('empleados')->orderBy('nombre');
        if (! empty($filtros['historial'])) {
            $query->onlyTrashed();
        }
        if (! empty($filtros['departamento'])) {
            $query->where('departamento_id', $filtros['departamento']);
        }
        if (! empty($filtros['buscar'])) {
            $query->where('nombre', 'like', '%'.trim($filtros['buscar']).'%');
        }

        return Inertia::render('Cargos/Index', [
            'registros' => $query->paginate(15)->withQueryString()->through(fn (Cargo $c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'departamento_id' => $c->departamento_id,
                'departamento' => $c->departamento?->nombre,
                'empleados' => $c->empleados_count,
                'inhabilitado' => $c->trashed(),
            ]),
            'filtros' => (object) $filtros,
            'departamentos' => Departamento::orderBy('nombre')->get(['id', 'nombre']),
            'urls' => ['index' => route('cargos.index', absolute: false)],
        ]);
    }

    public function store(GuardarCargoRequest $request)
    {
        $cargo = Cargo::create([...$request->validated(), 'activo' => true])->load('departamento');

        return $this->responder($request, 'Cargo creado correctamente.', [
            'success' => true, 'message' => 'Cargo creado correctamente.', 'cargo' => $cargo,
        ]);
    }

    public function update(GuardarCargoRequest $request, Cargo $cargo)
    {
        $cargo->update($request->validated());

        return $this->responder($request, 'Cargo actualizado correctamente.', [
            'success' => true, 'message' => 'Cargo actualizado correctamente.', 'cargo' => $cargo->load('departamento'),
        ]);
    }

    public function destroy(Request $request, Cargo $cargo)
    {
        if ($cargo->empleados()->exists()) {
            return $this->rechazar($request, 'No se puede inhabilitar: el cargo tiene empleados asociados.');
        }

        $cargo->delete();

        return $this->responder($request, 'Cargo inhabilitado correctamente.');
    }

    public function restore(Request $request, int $id)
    {
        Cargo::onlyTrashed()->findOrFail($id)->restore();

        return $this->responder($request, 'Cargo restaurado correctamente.');
    }
}
