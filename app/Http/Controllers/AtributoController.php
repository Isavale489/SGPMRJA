<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Http\Requests\GuardarAtributoRequest;
use App\Models\Atributo;
use App\Models\AtributoValor;
use App\Models\TipoProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Atributos de confección (maestro) y sus valores (detalle, en
 * AtributoValorController). El atributo seleccionado vive en la URL
 * (?atributo=ID): elegir otro recarga solo sus valores.
 */
class AtributoController extends Controller
{
    use RespondeSegunCliente;

    public function index(Request $request)
    {
        // Contrato vivo: el formulario de tipos de Productos (jQuery) hace $.getJSON(atributos.index).
        if (($request->wantsJson() || $request->ajax()) && ! $this->esInertia($request)) {
            return response()->json($this->atributos()->map(fn (array $a) => [
                ...collect($a)->only(['id', 'nombre', 'codigo', 'descripcion'])->all(),
                'valores_count' => $a['valores'],
                'tipos_producto_count' => count($a['tipos_producto_ids']),
                'tipos_producto_ids' => $a['tipos_producto_ids'],
            ]));
        }

        $seleccionado = Atributo::find($request->integer('atributo') ?: null);

        return Inertia::render('Atributos/Index', [
            'atributos' => fn () => $this->atributos(),
            'seleccionado' => $seleccionado?->id,
            'valores' => fn () => $seleccionado
                ? $seleccionado->valores()->withCount('productos')->get()->map(fn (AtributoValor $v) => [
                    'id' => $v->id,
                    'nombre' => $v->nombre,
                    'codigo' => $v->codigo,
                    'orden' => $v->orden,
                    'productos' => $v->productos_count,
                ])
                : [],
            'tiposProducto' => fn () => TipoProducto::orderBy('nombre')->get(['id', 'nombre']),
            'urls' => ['index' => route('atributos.index', absolute: false)],
        ]);
    }

    /** Filas del listado (lo verifica AtributoPaginaTest contra AtributoFila de tipos.ts). */
    private function atributos()
    {
        return Atributo::withCount('valores')->with('tiposProducto:id')->orderBy('nombre')->get()
            ->map(fn (Atributo $a) => [
                'id' => $a->id,
                'nombre' => $a->nombre,
                'codigo' => $a->codigo,
                'descripcion' => $a->descripcion,
                'valores' => $a->valores_count,
                'tipos_producto_ids' => $a->tiposProducto->pluck('id')->values()->all(),
            ]);
    }

    public function store(GuardarAtributoRequest $request)
    {
        $atributo = DB::transaction(function () use ($request) {
            $atributo = Atributo::create($request->safe()->only(['nombre', 'codigo', 'descripcion']));
            $atributo->tiposProducto()->sync($request->input('tipos_producto', []));

            return $atributo;
        });

        if ($this->esInertia($request)) {
            // Queda seleccionado para cargarle sus valores de inmediato.
            return to_route('atributos.index', ['atributo' => $atributo->id])->with('success', 'Atributo creado correctamente.');
        }

        return response()->json([
            'success' => true, 'message' => 'Atributo creado correctamente.',
            'atributo' => $atributo->loadCount(['valores', 'tiposProducto']),
        ]);
    }

    public function update(GuardarAtributoRequest $request, Atributo $atributo)
    {
        // El código no se toca: forma el SKU de los productos ya generados.
        DB::transaction(function () use ($request, $atributo) {
            $atributo->update($request->safe()->only(['nombre', 'descripcion']));
            $atributo->tiposProducto()->sync($request->input('tipos_producto', []));
        });

        return $this->responder($request, 'Atributo actualizado correctamente.', [
            'success' => true, 'message' => 'Atributo actualizado correctamente.',
            'atributo' => $atributo->loadCount(['valores', 'tiposProducto']),
        ]);
    }

    public function destroy(Request $request, Atributo $atributo)
    {
        if ($atributo->tiposProducto()->exists()) {
            return $this->rechazar($request, 'No se puede eliminar: el atributo está asignado a uno o más tipos de producto.');
        }

        $productosAfectados = DB::table('producto_atributo_valor')
            ->join('atributo_valor', 'atributo_valor.id', '=', 'producto_atributo_valor.atributo_valor_id')
            ->where('atributo_valor.atributo_id', $atributo->id)
            ->count();

        if ($productosAfectados > 0) {
            return $this->rechazar($request, "No se puede eliminar: {$productosAfectados} producto(s) usan valores de este atributo.");
        }

        $atributo->delete();

        if ($this->esInertia($request)) {
            return to_route('atributos.index')->with('success', 'Atributo eliminado correctamente.');
        }

        return response()->json(['success' => true, 'message' => 'Atributo eliminado correctamente.']);
    }
}
