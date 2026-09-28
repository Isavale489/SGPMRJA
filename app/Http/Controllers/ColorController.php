<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Http\Requests\GuardarColorRequest;
use App\Models\Color;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ColorController extends Controller
{
    use RespondeSegunCliente;

    /**
     * Colores activos agrupados por 'grupo' (JSON). Lo usa el selector de
     * color de Cotizaciones y Pedidos (Blade/jQuery).
     */
    public function getColores(Request $request): JsonResponse
    {
        return response()->json(
            Color::activo()->orderBy('grupo')->orderBy('nombre')->get(['id', 'nombre', 'hex_referencial', 'grupo'])
        );
    }

    public function index(Request $request): Response
    {
        $filtros = array_filter($request->only(['buscar', 'historial']), fn ($v) => $v !== null && $v !== '');
        $query = Color::orderBy('grupo')->orderBy('nombre');
        if (! empty($filtros['historial'])) {
            $query->onlyTrashed();
        }
        if (! empty($filtros['buscar'])) {
            $buscar = trim($filtros['buscar']);
            $query->where(fn ($q) => $q->where('nombre', 'like', "%{$buscar}%")->orWhere('grupo', 'like', "%{$buscar}%"));
        }

        return Inertia::render('Colores/Index', [
            'registros' => $query->paginate(15)->withQueryString()->through(fn (Color $c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'hex' => $c->hex_referencial,
                'grupo' => $c->grupo,
                'inhabilitado' => $c->trashed(),
            ]),
            'filtros' => (object) $filtros,
            // Grupos existentes (sugerencias del formulario), incluidos los de inhabilitados.
            'grupos' => Color::withTrashed()->whereNotNull('grupo')->where('grupo', '!=', '')->distinct()->orderBy('grupo')->pluck('grupo'),
            'urls' => ['index' => route('colores.index', absolute: false)],
        ]);
    }

    public function store(GuardarColorRequest $request)
    {
        $color = Color::create([...$request->validated(), 'activo' => true]);

        return $this->responder($request, 'Color creado correctamente.', [
            'success' => true, 'message' => 'Color creado correctamente.', 'color' => $color,
        ]);
    }

    public function update(GuardarColorRequest $request, Color $color)
    {
        $color->update($request->validated());

        return $this->responder($request, 'Color actualizado correctamente.', [
            'success' => true, 'message' => 'Color actualizado correctamente.', 'color' => $color,
        ]);
    }

    public function destroy(Request $request, Color $color)
    {
        $color->delete();

        return $this->responder($request, 'Color inhabilitado correctamente.');
    }

    public function restore(Request $request, int $id)
    {
        Color::onlyTrashed()->findOrFail($id)->restore();

        return $this->responder($request, 'Color restaurado correctamente.');
    }
}
