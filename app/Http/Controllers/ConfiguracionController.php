<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Models\Configuracion;
use App\Models\Impuesto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

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
    use RespondeSegunCliente;

    public function index(): Response
    {
        // Catálogo de impuestos (tabla `impuesto`): pestaña propia del panel,
        // fuera del registry. El IVA queda siempre primero.
        $impuestos = Impuesto::orderByRaw('codigo = ? DESC', [Impuesto::CODIGO_IVA])->orderBy('nombre')->get();

        return Inertia::render('Configuracion/Index', [
            'modulos' => array_values(array_map(fn ($m) => [...$m, 'parametros' => array_values(array_map(fn ($p) => $this->campo($p), $m['parametros']))], $this->registryPorModulo())),
            'impuestos' => $impuestos->map(fn (Impuesto $i) => [
                'id' => $i->id,
                'codigo' => $i->codigo,
                'nombre' => $i->nombre,
                'porcentaje' => (float) $i->porcentaje,
                'descripcion' => $i->descripcion,
                'estado' => $i->estado,
                'es_iva' => $i->codigo === Impuesto::CODIGO_IVA,
            ])->all(),
            'urls' => [
                'modulos' => url('/configuracion'),
                'impuestos' => url('/configuracion-impuestos'),
                'seguridad' => url('/configuracion/seguridad'),
                'usuarios' => route('users.index', absolute: false),
                'perfil' => route('profile.edit', absolute: false),
            ],
        ]);
    }

    /** Un parámetro tal como lo dibuja el formulario (tipo, límites y valor por defecto legible). */
    private function campo(array $p): array
    {
        $reglas = $p['reglas'] ?? '';
        $default = $p['default'] ?? (isset($p['config_key']) ? config($p['config_key']) : null);

        return [
            'clave' => $p['clave'],
            'nombre' => $p['nombre'],
            'descripcion' => $p['descripcion'] ?? null,
            'tipo' => $p['tipo'],
            'sufijo' => $p['sufijo'] ?? null,
            'valor' => $p['valor'],
            'es_default' => $p['es_default'],
            'default' => $default,
            'requerido' => str_contains($reglas, 'required'),
            'min' => preg_match('/(?:^|\|)min:([\d.]+)/', $reglas, $m) ? (float) $m[1] : null,
            'max' => preg_match('/(?:^|\|)max:([\d.]+)/', $reglas, $m) ? (float) $m[1] : null,
        ];
    }

    /**
     * Guarda los overrides de un módulo. Payload esperado:
     * { "valores": { "pedidos.abono_minimo": "50", ... } }
     */
    public function update(Request $request, string $modulo): JsonResponse|RedirectResponse
    {
        $parametros = $this->parametrosDelModulo($modulo);

        $valores = $request->input('valores');
        if (!is_array($valores) || empty($valores)) {
            return $this->rechazar($request, 'No se recibieron valores para guardar.');
        }

        // Nunca persistir claves arbitrarias: todo lo enviado debe pertenecer
        // al módulo de la URL según el registry.
        $desconocidas = array_diff(array_keys($valores), array_keys($parametros));
        if (!empty($desconocidas)) {
            return $this->rechazar($request, 'Se recibieron parámetros que no pertenecen a este módulo: ' . implode(', ', $desconocidas) . '.');
        }

        // Reglas y etiquetas desde el registry. Las claves llevan punto
        // (p.ej. pedidos.abono_minimo), que para el validador significa
        // anidamiento: se escapa con \. para validarlas como clave literal.
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
        if ($validator->fails() && $this->esInertia($request)) {
            // Cada error queda en valores.<clave> (el campo del formulario).
            throw ValidationException::withMessages(collect($validator->errors()->toArray())->mapWithKeys(fn ($m, $k) => ["valores.{$k}" => $m])->all());
        }
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

        return $this->responder($request, 'Configuración guardada correctamente.');
    }

    /**
     * Elimina el override de un parámetro: vuelve al valor por defecto.
     */
    public function reset(Request $request, string $modulo, string $clave): JsonResponse|RedirectResponse
    {
        $parametros = $this->parametrosDelModulo($modulo);

        if (!array_key_exists($clave, $parametros)) {
            abort(404);
        }

        Configuracion::where('clave', $clave)->delete();
        Cache::forget('parametros');

        return $this->responder($request, 'Parámetro restablecido a su valor por defecto.', [
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
