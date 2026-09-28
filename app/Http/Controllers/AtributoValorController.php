<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Http\Requests\GuardarAtributoValorRequest;
use App\Models\Atributo;
use App\Models\AtributoValor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Valores de un atributo (detalle). Se listan en AtributoController::index. */
class AtributoValorController extends Controller
{
    use RespondeSegunCliente;

    public function store(GuardarAtributoValorRequest $request, Atributo $atributo)
    {
        $orden = $request->filled('orden')
            ? (int) $request->orden
            : ((int) $atributo->valores()->max('orden') + 1);

        $valor = $atributo->valores()->create([
            'nombre' => $request->validated('nombre'),
            'codigo' => $request->validated('codigo'),
            'orden' => $orden,
        ]);

        return $this->responder($request, 'Valor agregado correctamente.', [
            'success' => true, 'message' => 'Valor agregado correctamente.', 'valor' => $valor->loadCount('productos'),
        ]);
    }

    public function update(GuardarAtributoValorRequest $request, Atributo $atributo, AtributoValor $valor)
    {
        abort_unless($valor->atributo_id === $atributo->id, 404);

        // El código no se toca: forma el SKU de los productos ya generados.
        $valor->update([
            'nombre' => $request->validated('nombre'),
            ...($request->filled('orden') ? ['orden' => (int) $request->orden] : []),
        ]);

        return $this->responder($request, 'Valor actualizado correctamente.', [
            'success' => true, 'message' => 'Valor actualizado correctamente.', 'valor' => $valor->loadCount('productos'),
        ]);
    }

    public function destroy(Request $request, Atributo $atributo, AtributoValor $valor)
    {
        abort_unless($valor->atributo_id === $atributo->id, 404);

        $usos = $valor->productos()->count();
        if ($usos > 0) {
            return $this->rechazar($request, "No se puede eliminar: {$usos} producto(s) usan este valor.");
        }

        $valor->delete();

        return $this->responder($request, 'Valor eliminado correctamente.');
    }

    /** Reordena en bloque: recibe { ids: [v1, v2, v3] } y les asigna ese orden. */
    public function reorder(Request $request, Atributo $atributo)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:atributo_valor,id',
        ]);

        $idsValidos = $atributo->valores()->whereIn('id', $request->ids)->pluck('id')->all();
        if (count($idsValidos) !== count($request->ids)) {
            return $this->rechazar($request, 'Algún valor no pertenece a este atributo.');
        }

        DB::transaction(function () use ($request) {
            foreach ($request->ids as $idx => $id) {
                AtributoValor::where('id', $id)->update(['orden' => $idx + 1]);
            }
        });

        // Sin aviso en Inertia: las flechas ya muestran el cambio y un flash por clic satura.
        return $this->esInertia($request) ? back() : response()->json(['success' => true, 'message' => 'Orden actualizado.']);
    }
}
