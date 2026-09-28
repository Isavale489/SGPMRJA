<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Persona;
use App\Services\ClienteService;
use App\Support\CatalogoGeografico;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClienteController extends Controller
{
    use RespondeSegunCliente;

    public function __construct(
        private ClienteService $clienteService
    ) {
    }

    /** Filtros de la tabla (query string): la URL es el estado de la vista. */
    private const FILTROS = ['buscar', 'tipo', 'estado', 'orden', 'historial'];

    public function index(Request $request): Response
    {
        $filtros = array_filter($request->only(self::FILTROS), fn ($v) => $v !== null && $v !== '');

        return Inertia::render('Clientes/Index', [
            'clientes' => $this->consulta($filtros)
                ->paginate(15)
                ->withQueryString()
                ->through(fn (Cliente $c) => $this->fila($c)),
            'filtros' => (object) $filtros,
            'estados' => CatalogoGeografico::mapa(),
            'urls' => [
                'index' => route('clientes.index', absolute: false),
                'reportePdf' => route('clientes.reporte.pdf', absolute: false),
                'checkDocumento' => route('clientes.check-documento', absolute: false),
                'checkEmail' => route('clientes.check-email', absolute: false),
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
        $query = Cliente::with(['persona.telefonos', 'persona.direccion', 'persona.empleado:id,persona_id', 'persona.proveedor:id,persona_id']);

        if (! empty($filtros['historial'])) {
            $query->onlyTrashed();
        }
        if (! empty($filtros['tipo'])) {
            $query->where('tipo_cliente', $filtros['tipo']);
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
            'antiguos' => $query->orderBy('cliente.created_at')->orderBy('cliente.id'),
            'nombre_asc', 'nombre_desc' => $query
                ->join('persona', 'cliente.persona_id', '=', 'persona.id')
                ->orderBy('persona.nombre', $filtros['orden'] === 'nombre_asc' ? 'asc' : 'desc')
                ->select('cliente.*'),
            default => $query->orderByDesc('cliente.created_at')->orderByDesc('cliente.id'),
        };
    }

    /**
     * Fila de la tabla: trae todo lo que usan "Ver" y "Editar", así la vista
     * no necesita otra petición. Espejo de `ClienteFila` en
     * resources/js/pages/Clientes/tipos.ts (lo verifica ClientePaginaTest).
     */
    private function fila(Cliente $c): array
    {
        $persona = $c->persona;
        $direccion = $persona?->direccion;

        return [
            'id' => $c->id,
            'tipo' => $c->tipo_cliente,
            'tipo_documento' => $persona?->tipo_documento,
            'numero_documento' => $persona?->documento_identidad,
            'documento' => $c->documento,
            'nombre' => $persona?->nombre,
            'email' => $persona?->email,
            'telefonos' => $persona ? $persona->telefonos->map(fn ($t) => [
                'numero' => $t->numero,
                'tipo' => $t->tipo,
                'es_principal' => (bool) $t->es_principal,
            ])->values()->all() : [],
            'direccion' => $direccion?->direccion,
            'estado_territorial' => $direccion?->estado,
            'ciudad' => $direccion?->ciudad,
            // La persona es compartida: editar sus datos también cambia estos registros.
            'otros_roles' => array_values(array_filter([
                $persona?->empleado ? 'empleado' : null,
                $persona?->proveedor ? 'proveedor' : null,
            ])),
            'inhabilitado' => $c->trashed(),
            'creado' => $c->created_at?->format('Y-m-d H:i'),
        ];
    }

    public function store(StoreClienteRequest $request)
    {
        $clienteId = $this->clienteService->crear($request->validated());

        // Cotizaciones y Pedidos (alta rápida, jQuery) leen `cliente_id`.
        return $this->responder($request, 'Cliente creado exitosamente.', [
            'message' => 'Cliente creado exitosamente.',
            'cliente_id' => $clienteId,
        ]);
    }

    public function update(UpdateClienteRequest $request, $id)
    {
        $cliente = Cliente::with(['persona.telefonos', 'persona.direcciones'])->findOrFail($id);

        $this->clienteService->actualizar($cliente, $request->validated());

        return $this->responder($request, 'Cliente actualizado exitosamente.', ['message' => 'Cliente actualizado exitosamente.']);
    }

    public function destroy(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);
        $cotizacionesCount = $cliente->cotizaciones()->count();

        $cliente->delete(); // SoftDelete: marca deleted_at

        \Log::warning('Cliente inhabilitado (soft delete)', [
            'cliente_id' => $id,
            'cotizaciones_count' => $cotizacionesCount,
            'user_id' => auth()->id(),
        ]);

        $aviso = $cotizacionesCount > 0
            ? 'Este cliente tenía '.$cotizacionesCount.' cotización(es). Los registros históricos se mantienen.'
            : null;

        return $this->responder($request, trim('Cliente inhabilitado exitosamente. '.$aviso), array_filter([
            'message' => 'Cliente inhabilitado exitosamente.',
            'warning' => $aviso,
        ]));
    }

    /**
     * Restaurar un cliente inhabilitado (soft-deleted).
     */
    public function restore(Request $request, $id)
    {
        Cliente::onlyTrashed()->findOrFail($id)->restore();

        \Log::info('Cliente restaurado', [
            'cliente_id' => $id,
            'user_id' => auth()->id(),
        ]);

        return $this->responder($request, 'Cliente restaurado exitosamente.', ['message' => 'Cliente restaurado exitosamente.']);
    }

    /**
     * Buscar clientes por documento de identidad (para autocompletado AJAX)
     */
    public function searchAjax(Request $request)
    {
        $query = trim((string) $request->input('q'));
        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $query);

        $clientes = Cliente::with(['persona.telefonos', 'persona.direcciones'])
            ->when($query !== '', function ($q) use ($escaped) {
                $q->whereHas('persona', function ($sub) use ($escaped) {
                    $sub->where('documento_identidad', 'LIKE', "{$escaped}%")
                        ->orWhere('nombre', 'LIKE', "%{$escaped}%");
                });
            })
            ->where('estatus', 1)
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        // Formatear respuesta usando accessors del modelo Cliente
        $resultado = $clientes->map(function ($cliente) {
            return [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre ?? 'N/A',
                'apellido' => '',
                'email' => $cliente->email,
                'telefono' => $cliente->telefono, // Usa accessor
                'documento' => $cliente->documento,
            ];
        });

        return response()->json($resultado);
    }

    /**
     * Verificar si un documento ya existe (AJAX)
     */
    public function checkDocumento(Request $request)
    {
        $numero = $request->input('numero');
        if (!$numero) {
            return response()->json(['exists' => false]);
        }

        // Solo bloquear si ya existe un CLIENTE con ese documento.
        // Una persona puede ser empleado y cliente al mismo tiempo (persona compartida).
        $persona = Persona::with(['telefonos', 'direcciones'])->where('documento_identidad', $numero)->first();
        $exists = $persona && Cliente::where('persona_id', $persona->id)->exists();

        $otherRole = null;
        $personaData = null;
        if ($persona && !$exists) {
            if (Empleado::where('persona_id', $persona->id)->exists()) {
                $otherRole = 'empleado';
                $dir = $persona->direccionPrincipal;
                $personaData = [
                    'nombre'           => $persona->nombre,
                    'apellido'         => '',
                    'tipo_documento'   => $persona->tipo_documento,
                    'email'            => $persona->email ?? '',
                    'telefono'         => $persona->telefonoPrincipal ?? '',
                    'telefonos'        => $persona->telefonos,
                    'estado_geografico'=> $dir?->estado ?? '',
                    'ciudad'           => $dir?->ciudad ?? '',
                    'direccion'        => $dir?->direccion ?? '',
                ];
            }
        }

        return response()->json(['exists' => $exists, 'other_role' => $otherRole, 'persona' => $personaData]);
    }

    /**
     * Exportar reporte de clientes en PDF
     */
    public function exportarPDF(Request $request)
    {
        $query = Cliente::with('persona');
        // Estatus: 1 = activos (default), 0 = inhabilitados (trashed) — estándar de inhabilitación
        if ($request->input('estado') === '0') {
            $query->onlyTrashed();
        }
        if ($request->filled('tipo_cliente')) {
            $query->where('tipo_cliente', $request->tipo_cliente);
        }
        // Rango por fecha de registro (created_at)
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }
        $clientes = $query->get();

        $filtros = [];
        if ($request->input('estado') === '0') {
            $filtros['Estatus'] = 'Inhabilitados';
        }
        if ($request->filled('tipo_cliente')) {
            $filtros['Tipo'] = ucfirst($request->tipo_cliente);
        }
        if ($rango = \App\Support\ReporteFiltros::rango($request->fecha_desde, $request->fecha_hasta)) {
            $filtros['Fecha de registro'] = $rango;
        }

        $pdf = Pdf::loadView('admin.clientes.reporte_pdf', compact('clientes', 'filtros'))->setPaper('a4', 'landscape');
        return $pdf->stream('reporte_clientes_' . now()->format('Ymd_His') . '.pdf');
    }

    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        if (!$email)
            return response()->json(['exists' => false]);

        $query = \App\Models\Persona::where('email', $email);

        $excludeClienteId = $request->input('exclude_id');
        if ($excludeClienteId) {
            $cliente = Cliente::find($excludeClienteId);
            if ($cliente && $cliente->persona_id) {
                $query->where('id', '!=', $cliente->persona_id);
            }
        }

        return response()->json(['exists' => $query->exists()]);
    }

    /**
     * Crear un Cliente reutilizando una Persona existente (empleado, proveedor, etc.).
     * Usado por el autocompletado de cotizaciones para evitar duplicar identidades.
     * El tipo_cliente se deriva del tipo_documento: V/E → natural, J → juridico, G → gubernamental.
     */
    public function createFromPersona(int $personaId): JsonResponse
    {
        $persona = Persona::with(['telefonos', 'direcciones'])->find($personaId);

        if (!$persona) {
            return response()->json([
                'success' => false,
                'message' => 'Persona no encontrada.',
            ], 404);
        }

        // Si ya existe un cliente activo vinculado, devolverlo
        $clienteExistente = Cliente::where('persona_id', $persona->id)
            ->where('estatus', 1)
            ->first();

        if ($clienteExistente) {
            return response()->json([
                'success'    => true,
                'message'    => 'La persona ya estaba registrada como cliente activo.',
                'cliente_id' => $clienteExistente->id,
                'reused'     => true,
            ]);
        }

        // Si existe pero está inhabilitado (estatus 0 o trashed), reactivar
        $clienteInactivo = Cliente::withTrashed()
            ->where('persona_id', $persona->id)
            ->first();

        if ($clienteInactivo) {
            if ($clienteInactivo->trashed()) {
                $clienteInactivo->restore();
            }
            $clienteInactivo->update(['estatus' => 1]);

            return response()->json([
                'success'    => true,
                'message'    => 'Cliente reactivado correctamente.',
                'cliente_id' => $clienteInactivo->id,
                'reused'     => true,
            ]);
        }

        // Detectar tipo de cliente según prefijo del documento
        $tipoCliente = match ($persona->tipo_documento) {
            'J-'    => 'juridico',
            'G-'    => 'gubernamental',
            default => 'natural', // V-, E-, sin prefijo
        };

        $cliente = Cliente::create([
            'persona_id'   => $persona->id,
            'tipo_cliente' => $tipoCliente,
            'estatus'      => 1,
        ]);

        return response()->json([
            'success'    => true,
            'message'    => 'Cliente creado a partir de la persona registrada.',
            'cliente_id' => $cliente->id,
            'reused'     => false,
        ]);
    }
}
