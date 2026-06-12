<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Panel de configuración del sistema (FEAT-004).
 *
 * El catálogo de parámetros vive en config/parametros.php (registry); la tabla
 * `configuracion` solo guarda overrides. Este controller no conoce parámetros
 * concretos: agrupa el registry por módulo y valida con las reglas declaradas
 * en cada entrada.
 */
class ConfiguracionController extends Controller
{
    public function index()
    {
        $modulos = $this->registryPorModulo();

        return view('admin.configuracion.index', compact('modulos'));
    }

    /**
     * Guarda los overrides de un módulo. Payload esperado:
     * { "valores": { "impuestos.iva": "8", ... } }
     */
    public function update(Request $request, string $modulo)
    {
        $parametros = $this->parametrosDelModulo($modulo);

        $valores = $request->input('valores');
        if (!is_array($valores) || empty($valores)) {
            return response()->json(['success' => false, 'message' => 'No se recibieron valores para guardar.'], 422);
        }

        // Nunca persistir claves arbitrarias: todo lo enviado debe pertenecer
        // al módulo de la URL según el registry.
        $desconocidas = array_diff(array_keys($valores), array_keys($parametros));
        if (!empty($desconocidas)) {
            return response()->json([
                'success' => false,
                'message' => 'Se recibieron parámetros que no pertenecen a este módulo: ' . implode(', ', $desconocidas) . '.',
            ], 422);
        }

        // Reglas y etiquetas desde el registry. Las claves llevan punto
        // (impuestos.iva), que para el validador significa anidamiento:
        // se escapa con \. para validarlas como clave literal.
        $reglas = [];
        $etiquetas = [];
        foreach ($valores as $clave => $valor) {
            $claveEscapada = str_replace('.', '\.', $clave);
            $reglas[$claveEscapada] = $parametros[$clave]['reglas'];
            // La etiqueta se registra con la clave SIN escapar: así la resuelve
            // el validador al armar el mensaje (:attribute).
            $etiquetas[$clave] = $parametros[$clave]['nombre'];
        }

        $validator = Validator::make($valores, $reglas, [], $etiquetas);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Revisa los valores ingresados.',
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        foreach ($valores as $clave => $valor) {
            Configuracion::updateOrCreate(
                ['clave' => $clave],
                ['valor' => (string) $valor, 'updated_by_id' => Auth::id()]
            );
        }

        Cache::forget('parametros');

        return response()->json([
            'success' => true,
            'message' => 'Configuración guardada correctamente.',
        ]);
    }

    /**
     * Elimina el override de un parámetro: vuelve al valor por defecto.
     */
    public function reset(string $modulo, string $clave)
    {
        $parametros = $this->parametrosDelModulo($modulo);

        if (!array_key_exists($clave, $parametros)) {
            abort(404);
        }

        Configuracion::where('clave', $clave)->delete();
        Cache::forget('parametros');

        return response()->json([
            'success' => true,
            'message' => 'Parámetro restablecido a su valor por defecto.',
            'default' => parametro($clave),
        ]);
    }

    /**
     * Registry agrupado por módulo, con valor efectivo y flag es_default
     * por parámetro. Estructura por módulo: ['slug', 'nombre', 'parametros'].
     */
    private function registryPorModulo(): array
    {
        $overrides = Configuracion::pluck('valor', 'clave')->all();

        $modulos = [];
        foreach (config('parametros', []) as $clave => $definicion) {
            $slug = Str::slug($definicion['modulo']);

            $modulos[$slug] ??= [
                'slug'       => $slug,
                'nombre'     => $definicion['modulo'],
                'parametros' => [],
            ];

            $modulos[$slug]['parametros'][$clave] = $definicion + [
                'clave'      => $clave,
                'valor'      => parametro($clave),
                'es_default' => !array_key_exists($clave, $overrides),
            ];
        }

        return $modulos;
    }

    /**
     * Parámetros del registry que pertenecen al módulo de la URL (por slug).
     * 404 si el módulo no existe en el registry.
     */
    private function parametrosDelModulo(string $modulo): array
    {
        $parametros = collect(config('parametros', []))
            ->filter(fn ($definicion) => Str::slug($definicion['modulo']) === Str::slug($modulo))
            ->all();

        if (empty($parametros)) {
            abort(404);
        }

        return $parametros;
    }
}
