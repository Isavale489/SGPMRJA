<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Http\Requests\GuardarInsumoRequest;
use App\Models\Insumo;
use App\Models\TipoInsumo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InsumoController extends Controller
{
    use RespondeSegunCliente;

    /** Filtros de la tabla (query string): la URL es el estado de la vista. */
    private const FILTROS = ['buscar', 'tipo', 'stock', 'orden', 'historial'];

    public function index(Request $request): Response
    {
        $filtros = array_filter($request->only(self::FILTROS), fn ($v) => $v !== null && $v !== '');

        return Inertia::render('Insumos/Index', [
            'insumos' => $this->consulta($filtros)
                ->paginate(15)
                ->withQueryString()
                ->through(fn (Insumo $i) => $this->fila($i)),
            'filtros' => (object) $filtros,
            // Catálogo completo (con inhabilitados) para gestionarlo; el formulario usa solo los activos.
            'tiposInsumo' => fn () => TipoInsumo::withTrashed()->withCount('insumos')->orderBy('nombre')->get()
                ->map(fn (TipoInsumo $t) => [
                    'id' => $t->id,
                    'nombre' => $t->nombre,
                    'insumos' => $t->insumos_count,
                    'activo' => (bool) $t->activo,
                    'inhabilitado' => $t->trashed(),
                ]),
            'unidades' => GuardarInsumoRequest::UNIDADES,
            'urls' => [
                'index' => route('insumos.index', absolute: false),
                'reportePdf' => route('insumos.reporte.pdf', absolute: false),
                'checkNombre' => route('insumos.check-nombre', absolute: false),
                'tipos' => route('tipo-insumos.index', absolute: false),
            ],
        ]);
    }

    /**
     * Consulta de la tabla. Mismas reglas que tenía el endpoint DataTables:
     * búsqueda por nombre, código o tipo (prefijo); filtros por tipo y stock.
     */
    private function consulta(array $filtros): Builder
    {
        $query = Insumo::query();

        if (! empty($filtros['historial'])) {
            $query->onlyTrashed();
        }
        if (! empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }
        match ($filtros['stock'] ?? null) {
            'con_stock' => $query->where('stock_actual', '>', 0),
            'agotado' => $query->where('stock_actual', '<=', 0),
            // Inventariables en o por debajo de su mínimo (lo que hay que reponer).
            'bajo' => $query->where('is_inventoriable', true)->whereColumn('stock_actual', '<=', 'stock_minimo'),
            default => null,
        };
        if (! empty($filtros['buscar'])) {
            $buscar = trim($filtros['buscar']);
            $query->where(fn ($q) => $q->where('nombre', 'like', "%{$buscar}%")
                ->orWhere('codigo', 'like', "{$buscar}%")
                ->orWhere('tipo', 'like', "{$buscar}%"));
        }

        return match ($filtros['orden'] ?? 'recientes') {
            'mayor_costo' => $query->orderByDesc('costo_unitario')->orderBy('id'),
            'menor_costo' => $query->orderBy('costo_unitario')->orderBy('id'),
            'mayor_stock' => $query->orderByDesc('stock_actual')->orderBy('id'),
            'menor_stock' => $query->orderBy('stock_actual')->orderBy('id'),
            'nombre' => $query->orderBy('nombre'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };
    }

    /**
     * Fila de la tabla (todo lo que usan Ver y Editar). Espejo de `InsumoFila`
     * en resources/js/pages/Insumos/tipos.ts (lo verifica InsumoPaginaTest).
     */
    private function fila(Insumo $i): array
    {
        $actual = (float) $i->stock_actual;
        $minimo = (float) $i->stock_minimo;

        return [
            'id' => $i->id,
            'nombre' => $i->nombre,
            'codigo' => $i->codigo,
            'tipo' => $i->tipo,
            'unidad_medida' => $i->unidad_medida,
            'is_inventoriable' => (bool) $i->is_inventoriable,
            'aplica_iva' => (bool) $i->aplica_iva,
            'costo_unitario' => (float) $i->costo_unitario,
            'stock_actual' => $actual,
            'stock_minimo' => $minimo,
            'stock_maximo' => (float) $i->stock_maximo,
            // Mismo criterio que la vista anterior: bajo ≤ mínimo < medio ≤ 1,5 × mínimo < normal.
            'nivel_stock' => ! $i->is_inventoriable ? null : ($actual <= $minimo ? 'bajo' : ($actual <= $minimo * 1.5 ? 'medio' : 'normal')),
            'inhabilitado' => $i->trashed(),
            'creado' => $i->created_at?->format('Y-m-d H:i'),
        ];
    }

    public function store(GuardarInsumoRequest $request)
    {
        // Todo insumo nace habilitado; el estatus se gobierna con Inhabilitar/Habilitar.
        $insumo = Insumo::create([...$request->datos(), 'codigo' => $request->validated('codigo'), 'estado' => true]);

        // Alta rápida desde Compras: el formulario de la compra recibe el insumo nuevo.
        return $this->responder($request, 'Insumo creado exitosamente.', ['success' => 'Insumo creado exitosamente.', 'insumo' => $insumo], [
            'insumo' => ['id' => $insumo->id, 'nombre' => $insumo->nombre, 'codigo' => $insumo->codigo, 'tipo' => $insumo->tipo, 'unidad' => $insumo->unidad_medida, 'costo' => (float) $insumo->costo_unitario, 'aplica_iva' => (bool) $insumo->aplica_iva, 'inventariable' => (bool) $insumo->is_inventoriable, 'stock' => (float) $insumo->stock_actual],
        ]);
    }

    public function update(GuardarInsumoRequest $request, $id)
    {
        $insumo = Insumo::findOrFail($id);
        $datos = $request->datos();
        // El código es inmutable una vez asignado; si estaba vacío, se puede asignar.
        if (empty($insumo->codigo) && $request->validated('codigo')) {
            $datos['codigo'] = $request->validated('codigo');
        }
        // 'estado' NO se edita aquí: lo gobiernan Inhabilitar/Habilitar.
        $insumo->update($datos);

        return $this->responder($request, 'Insumo actualizado exitosamente.', ['success' => 'Insumo actualizado exitosamente.']);
    }

    public function destroy(Request $request, $id)
    {
        $insumo = Insumo::findOrFail($id);
        // Inhabilitar = estado=false (lo respetan los selectores `where('estado',true)`)
        // + soft delete (queda en el historial, reversible con Habilitar).
        $insumo->update(['estado' => false]);
        $insumo->delete();

        return $this->responder($request, 'Insumo inhabilitado exitosamente.', ['success' => 'Insumo inhabilitado exitosamente.']);
    }

    /**
     * Habilitar un insumo inhabilitado (soft-deleted): lo restaura y reactiva su estado.
     */
    public function restore(Request $request, $id)
    {
        $insumo = Insumo::onlyTrashed()->findOrFail($id);
        $insumo->restore();
        $insumo->update(['estado' => true]);

        return $this->responder($request, 'Insumo habilitado exitosamente.', ['success' => 'Insumo habilitado exitosamente.']);
    }

    public function checkNombre(Request $request)
    {
        $nombre = $request->input('nombre');
        $excludeId = $request->input('exclude_id');
        if (!$nombre) return response()->json(['exists' => false]);

        $query = Insumo::whereRaw('LOWER(nombre) = ?', [strtolower($nombre)]);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return response()->json(['exists' => $query->exists()]);
    }

    public function reportePdf(Request $request)
    {
        $query = Insumo::query();
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('stock')) {
            if ($request->stock === 'con_stock') $query->where('stock_actual', '>', 0);
            elseif ($request->stock === 'agotado') $query->where('stock_actual', '<=', 0);
        }
        // Rango por fecha de registro (created_at)
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }
        $insumos = $query->get();

        $filtros = [];
        if ($request->filled('tipo')) {
            $filtros['Tipo'] = ucfirst($request->tipo);
        }
        if ($request->filled('stock')) {
            $filtros['Stock'] = $request->stock === 'con_stock' ? 'Con stock'
                : ($request->stock === 'agotado' ? 'Agotado' : $request->stock);
        }
        if ($rango = \App\Support\ReporteFiltros::rango($request->fecha_desde, $request->fecha_hasta)) {
            $filtros['Fecha de registro'] = $rango;
        }

        $pdf = \PDF::loadView('admin.insumos.reporte_pdf', compact('insumos', 'filtros'))
            ->setPaper('a4', 'landscape');
        return $pdf->stream('insumos_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }
}
