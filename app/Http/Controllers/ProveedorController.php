<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Http\Requests\GuardarProveedorRequest;
use App\Models\Proveedor;
use App\Models\Persona;
use App\Services\ProveedorService;
use App\Support\CatalogoGeografico;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProveedorController extends Controller
{
    use RespondeSegunCliente;

    public function __construct(
        private ProveedorService $proveedorService
    ) {
    }
    /** Filtros de la tabla (query string): la URL es el estado de la vista. */
    private const FILTROS = ['buscar', 'tipo', 'estado', 'orden', 'historial'];

    public function index(Request $request): Response
    {
        $filtros = array_filter($request->only(self::FILTROS), fn ($v) => $v !== null && $v !== '');

        return Inertia::render('Proveedores/Index', [
            'proveedores' => $this->consulta($filtros)
                ->paginate(15)
                ->withQueryString()
                ->through(fn (Proveedor $p) => $this->fila($p)),
            'filtros' => (object) $filtros,
            'estados' => CatalogoGeografico::mapa(),
            'urls' => [
                'index' => route('proveedores.index', absolute: false),
                'reportePdf' => route('proveedores.reporte.pdf', absolute: false),
                'checkDocumento' => route('proveedores.check-documento', absolute: false),
                'checkRif' => route('proveedores.check-rif', absolute: false),
                'checkEmail' => route('proveedores.check-email', absolute: false),
            ],
        ]);
    }

    /**
     * Consulta de la tabla. Mismas reglas que tenía el endpoint DataTables:
     * búsqueda por nombre/razón social, documento o email; filtros por tipo y
     * estado territorial; historial = solo inhabilitados.
     */
    private function consulta(array $filtros): Builder
    {
        $query = Proveedor::with(['persona.telefonos', 'persona.direccion']);

        if (! empty($filtros['historial'])) {
            $query->onlyTrashed();
        }
        if (! empty($filtros['tipo'])) {
            $query->where('tipo_proveedor', $filtros['tipo']);
        }
        if (! empty($filtros['estado'])) {
            $estado = $filtros['estado'];
            $query->whereHas('persona.direcciones', fn ($q) => $q->whereHas('estadoRel', fn ($e) => $e->where('nombre', $estado)));
        }
        if (! empty($filtros['buscar'])) {
            $buscar = trim($filtros['buscar']);
            $query->whereHas('persona', function ($p) use ($buscar) {
                // `nombre` ya contiene el nombre completo / razón social.
                $p->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('email', 'like', "{$buscar}%")
                    ->orWhereRaw('CONCAT(tipo_documento, documento_identidad) like ?', ["{$buscar}%"])
                    ->orWhere('documento_identidad', 'like', "{$buscar}%");
            });
        }

        return match ($filtros['orden'] ?? 'recientes') {
            'antiguos' => $query->orderBy('proveedor.created_at')->orderBy('proveedor.id'),
            'nombre_asc', 'nombre_desc' => $query
                ->join('persona', 'proveedor.persona_id', '=', 'persona.id')
                ->orderBy('persona.nombre', $filtros['orden'] === 'nombre_asc' ? 'asc' : 'desc')
                ->select('proveedor.*'),
            default => $query->orderByDesc('proveedor.created_at')->orderByDesc('proveedor.id'),
        };
    }

    /**
     * Fila de la tabla: trae todo lo que usan "Ver" y "Editar", así la vista
     * no necesita otra petición. Espejo de `ProveedorFila` en
     * resources/js/pages/Proveedores/tipos.ts (lo verifica ProveedorPaginaTest).
     */
    private function fila(Proveedor $p): array
    {
        $persona = $p->persona;
        $direccion = $persona?->direccion;

        return [
            'id' => $p->id,
            'tipo' => $p->tipo_proveedor === 'natural' ? 'natural' : 'juridico',
            'tipo_documento' => $persona?->tipo_documento,
            'numero_documento' => $persona?->documento_identidad,
            'documento' => $p->documento,
            'nombre' => $p->nombre_completo,
            'email' => $persona?->email,
            'telefonos' => $persona ? $persona->telefonos->map(fn ($t) => [
                'numero' => $t->numero,
                'tipo' => $t->tipo,
                'es_principal' => (bool) $t->es_principal,
            ])->values()->all() : [],
            'direccion' => $direccion?->direccion,
            'estado_territorial' => $direccion?->estado,
            'ciudad' => $direccion?->ciudad,
            'contacto' => $p->contacto,
            'telefono_contacto' => $p->telefono_contacto,
            'inhabilitado' => $p->trashed(),
            'creado' => $p->created_at?->format('Y-m-d H:i'),
        ];
    }

    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));

        $query = Proveedor::with(['persona.telefonos'])
            ->where('estado', 1)
            ->withCount('compras')
            ->withMax('compras', 'fecha_compra');

        if ($q) {
            $query->whereHas('persona', function ($sub) use ($q) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('documento_identidad', 'like', "{$q}%");
            });
        }

        return response()->json(
            $query->orderByDesc('id')->limit(50)->get()->map(fn($p) => [
                'id'     => $p->id,
                'nombre' => $p->nombre_completo ?? '—',
                'doc'    => $p->documento ?? '',
                'tel'    => $p->telefono_unificado ?? '',
                'email'  => $p->email_unificado ?? '',
                'tipo'   => $p->tipo_proveedor,
                'compras' => $p->compras_count ?? 0,
                'ultima'  => $p->compras_max_fecha_compra,
            ])
        );
    }

    public function store(GuardarProveedorRequest $request)
    {
        $datos = $request->validated();
        $proveedor = $datos['tipo_proveedor'] === 'natural'
            ? $this->proveedorService->crearNatural($datos)
            : $this->proveedorService->crearJuridico($datos);

        $payload = $this->proveedorPayload($proveedor);

        return $this->responder($request, 'Proveedor creado exitosamente.', [
            'success' => 'Proveedor creado exitosamente.',
            'proveedor' => $payload,
        ], ['proveedor' => $payload]); // alta rápida desde Compras
    }

    /**
     * Crea un proveedor reutilizando una persona ya existente en el sistema
     * (cliente, empleado u otro rol). Idempotente: si la persona ya es
     * proveedor activo lo devuelve; si está inhabilitado lo reactiva.
     * Mismo patrón que ClienteController@createFromPersona.
     */
    public function createFromPersona(Request $request, int $personaId): JsonResponse|RedirectResponse
    {
        $persona = Persona::find($personaId);

        if (!$persona) {
            return $this->rechazar($request, 'Persona no encontrada.', 404);
        }

        // Ya es proveedor activo → devolverlo
        $proveedorExistente = Proveedor::where('persona_id', $persona->id)
            ->where('estado', 1)
            ->first();

        if ($proveedorExistente) {
            return $this->respuestaDesdePersona($request, 'La persona ya estaba registrada como proveedor activo.', true, $proveedorExistente);
        }

        // Existe pero inhabilitado (estado 0 o trashed) → reactivar
        $proveedorInactivo = Proveedor::withTrashed()
            ->where('persona_id', $persona->id)
            ->first();

        if ($proveedorInactivo) {
            if ($proveedorInactivo->trashed()) {
                $proveedorInactivo->restore();
            }
            $proveedorInactivo->update(['estado' => 1]);

            return $this->respuestaDesdePersona($request, 'Proveedor reactivado correctamente.', true, $proveedorInactivo);
        }

        // Crear nuevo proveedor sobre la persona existente.
        // J-/G- → jurídico; V-/E-/sin prefijo → natural.
        $tipoProveedor = in_array($persona->tipo_documento, ['J-', 'G-'], true)
            ? 'juridico'
            : 'natural';

        $proveedor = Proveedor::create([
            'persona_id'     => $persona->id,
            'tipo_proveedor' => $tipoProveedor,
            'estado'         => 1,
        ]);

        return $this->respuestaDesdePersona($request, 'Proveedor creado a partir de la persona registrada.', false, $proveedor);
    }

    /** Compras (Inertia) recibe el proveedor por flash; el JSON se mantiene para clientes jQuery. */
    private function respuestaDesdePersona(Request $request, string $mensaje, bool $reusado, Proveedor $proveedor): JsonResponse|RedirectResponse
    {
        $payload = $this->proveedorPayload($proveedor);

        return $this->responder($request, $mensaje, ['success' => true, 'message' => $mensaje, 'reused' => $reusado, 'proveedor' => $payload], ['proveedor' => $payload]);
    }

    /**
     * Serializa un proveedor para el autocomplete/card del wizard de compras.
     */
    private function proveedorPayload(Proveedor $proveedor): array
    {
        $proveedor->loadMissing('persona');

        return [
            'id'      => $proveedor->id,
            'nombre'  => $proveedor->nombre_completo,
            'doc'     => $proveedor->documento ?? '',
            'tel'     => $proveedor->telefono_unificado ?? '',
            'email'   => $proveedor->email_unificado ?? '',
            'tipo'    => $proveedor->tipo_proveedor ?? '',
            'compras' => 0,
            'ultima'  => null,
        ];
    }

    public function update(GuardarProveedorRequest $request, Proveedor $proveedor)
    {
        $datos = $request->validated();
        $datos['tipo_proveedor'] === 'natural'
            ? $this->proveedorService->actualizarNatural($proveedor, $datos)
            : $this->proveedorService->actualizarJuridico($proveedor, $datos);

        return $this->responder($request, 'Proveedor actualizado exitosamente.', ['success' => 'Proveedor actualizado exitosamente.']);
    }

    public function destroy(Request $request, Proveedor $proveedor)
    {
        $proveedor->delete(); // SoftDelete: pasa al historial (no se borra)

        return $this->responder($request, 'Proveedor inhabilitado exitosamente.', ['success' => 'Proveedor inhabilitado exitosamente.']);
    }

    /**
     * Restaurar un proveedor inhabilitado (soft-deleted).
     */
    public function restore(Request $request, $id)
    {
        Proveedor::onlyTrashed()->findOrFail($id)->restore();

        return $this->responder($request, 'Proveedor restaurado exitosamente.', ['success' => 'Proveedor restaurado exitosamente.']);
    }

    public function reportePdf(Request $request)
    {
        $query = Proveedor::with('persona.telefonos', 'persona.direcciones');
        if ($request->filled('tipo_proveedor')) {
            $query->where('tipo_proveedor', $request->tipo_proveedor);
        }
        // Estatus: 1 = activos (default), 0 = inhabilitados (trashed) — estándar de inhabilitación
        if ($request->input('estatus') === '0') {
            $query->onlyTrashed();
        }
        // Rango por fecha de registro (created_at)
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }
        $proveedores = $query->get();

        $filtros = [];
        if ($request->filled('tipo_proveedor')) {
            $filtros['Tipo'] = ucfirst($request->tipo_proveedor);
        }
        if ($request->input('estatus') === '0') {
            $filtros['Estatus'] = 'Inhabilitados';
        }
        if ($rango = \App\Support\ReporteFiltros::rango($request->fecha_desde, $request->fecha_hasta)) {
            $filtros['Fecha de registro'] = $rango;
        }

        $pdf = \PDF::loadView('admin.proveedores.reporte_pdf', compact('proveedores', 'filtros'))
            ->setPaper('a4', 'landscape');
        return $pdf->stream('proveedores_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }

    public function checkRif(Request $request)
    {
        $rif = $request->input('rif');
        if (!$rif)
            return response()->json(['exists' => false]);

        // Parsear RIF y buscar en persona
        $tipoDoc = 'J-';
        $docId = $rif;
        if (preg_match('/^(V-|J-|E-|G-)(.+)$/', $rif, $matches)) {
            $tipoDoc = $matches[1];
            $docId = $matches[2];
        }

        $exists = Persona::where('tipo_documento', $tipoDoc)
            ->where('documento_identidad', $docId)
            ->exists();
        return response()->json(['exists' => $exists]);
    }

    public function checkDocumento(Request $request)
    {
        $numero = $request->input('numero');
        if (!$numero)
            return response()->json(['exists' => false]);
        $exists = Persona::where('documento_identidad', $numero)->exists();
        return response()->json(['exists' => $exists]);
    }

    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        $excludeId = $request->input('exclude_id'); // ID del proveedor en edición
        if (!$email)
            return response()->json(['exists' => false]);

        $query = Persona::where('email', $email);
        if ($excludeId) {
            $proveedor = Proveedor::find($excludeId);
            if ($proveedor) {
                $query->where('id', '!=', $proveedor->persona_id);
            }
        }
        $exists = $query->exists();
        return response()->json(['exists' => $exists]);
    }
}
