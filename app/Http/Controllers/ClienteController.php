<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Persona;
use App\Models\Telefono;
use App\Models\Direccion;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Services\ClienteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;


class ClienteController extends Controller
{
    public function __construct(
        private ClienteService $clienteService
    ) {
    }

    public function index(Request $request)
    {
        $historial = $request->has('historial');
        return view('admin.clientes.index', compact('historial'));
    }

    public function getClientes(Request $request)
    {
        // ── Base query con relaciones ──
        $query = Cliente::with(['persona.telefonos', 'persona.direcciones']);

        // ══════════════════════════════════════════════════════════
        // FILTROS AVANZADOS — Server-Side (Patrón Maestro S-07)
        // Cada filtro se aplica solo si el frontend envía un valor.
        // Para replicar en otros módulos: copiar este bloque y
        // ajustar los nombres de columna/accessor según el modelo.
        // ══════════════════════════════════════════════════════════

        // Filtro: Tipo de Cliente (natural, juridico, gubernamental)
        if ($request->filled('filter_tipo_cliente')) {
            $query->where('tipo_cliente', $request->input('filter_tipo_cliente'));
        }

        // Activos vs Historial: lo define la página (no un filtro). La principal
        // muestra solo activos; el historial (?historial=true) solo inhabilitados.
        if ($request->boolean('historial')) {
            $query->onlyTrashed();
        }

        // Filtro: Estado Territorial
        if ($request->filled('filter_estado_territorial')) {
            $estado = $request->input('filter_estado_territorial');
            $query->whereHas('persona.direcciones', function ($q) use ($estado) {
                $q->where('estado', $estado);
            });
        }

        // Filtro: Documento (búsqueda parcial por cédula/RIF)
        if ($request->filled('filter_documento')) {
            $doc = $request->input('filter_documento');
            $query->whereHas('persona', function ($q) use ($doc) {
                $q->where(DB::raw("CONCAT(tipo_documento, documento_identidad)"), 'LIKE', "%{$doc}%");
            });
        }

        // ══════════════════════════════════════════════════════════
        // ORDENAMIENTO — Selector "Ordenar por" del frontend
        // Valores posibles: recientes, antiguos, nombre_asc, nombre_desc
        // Fallback: más recientes primero (created_at DESC)
        // ══════════════════════════════════════════════════════════
        $orden = $request->input('filter_orden', 'recientes');

        switch ($orden) {
            case 'antiguos':
                $query->orderBy('cliente.created_at', 'asc');
                break;
            case 'nombre_asc':
                $query->join('persona', 'cliente.persona_id', '=', 'persona.id')
                      ->orderBy('persona.nombre', 'asc')
                      ->select('cliente.*');
                break;
            case 'nombre_desc':
                $query->join('persona', 'cliente.persona_id', '=', 'persona.id')
                      ->orderBy('persona.nombre', 'desc')
                      ->select('cliente.*');
                break;
            case 'recientes':
            default:
                $query->orderBy('cliente.created_at', 'desc');
                break;
        }

        return DataTables::of($query)
            // Búsqueda estricta: solo por la identidad del cliente (nombre/apellido,
            // documento y email de la persona). Sobrescribe el buscador global de
            // DataTables para no romper sobre columnas derivadas de relaciones.
            ->filter(function ($query) use ($request) {
                $keyword = trim((string) $request->input('search.value'));
                if ($keyword === '') {
                    return;
                }
                $query->whereHas('persona', function ($p) use ($keyword) {
                    $p->where('nombre', 'like', "%{$keyword}%")
                      ->orWhere('apellido', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%")
                      ->orWhereRaw("CONCAT(nombre, ' ', apellido) like ?", ["%{$keyword}%"])
                      ->orWhereRaw("CONCAT(tipo_documento, documento_identidad) like ?", ["%{$keyword}%"]);
                });
            }, true)
            ->addColumn('nombre', fn($c) => $c->nombre ?? 'N/A')
            ->addColumn('apellido', fn($c) => $c->apellido ?? '')
            ->addColumn('tipo_cliente', fn($c) => $c->tipo_cliente)
            ->addColumn('email', fn($c) => $c->email)
            ->addColumn('telefono', fn($c) => $c->telefono)
            ->addColumn('documento', fn($c) => $c->documento)
            ->addColumn('direccion', fn($c) => $c->direccion)
            ->addColumn('estado_territorial', fn($c) => $c->estado_territorial)
            ->addColumn('ciudad', fn($c) => $c->ciudad)
            ->addColumn('estatus', fn($c) => $c->estatus)
            ->addColumn('created_at', fn($c) => $c->created_at ? $c->created_at->format('d/m/Y H:i') : null)
            ->addColumn('trashed', fn($c) => $c->trashed())
            ->make(true);
    }

    public function store(StoreClienteRequest $request)
    {
        $clienteId = $this->clienteService->crear($request->validated());

        return response()->json([
            'message' => 'Cliente creado exitosamente.',
            'cliente_id' => $clienteId
        ]);
    }

    public function edit($id)
    {
        $cliente = Cliente::with(['persona.telefonos', 'persona.direcciones'])->findOrFail($id);

        // Obtener teléfono y dirección principal
        $telefonoPrincipal = $cliente->telefono;
        $direccionPrincipal = $cliente->persona ? $cliente->persona->direccion_principal : null;

        // Detectar si la persona también está en otro módulo
        $otherRole = null;
        if ($cliente->persona && Empleado::where('persona_id', $cliente->persona_id)->exists()) {
            $otherRole = 'empleado';
        }

        // Formatear respuesta para compatibilidad con el frontend existente
        return response()->json([
            'id' => $cliente->id,
            'persona_id' => $cliente->persona_id,
            'nombre' => $cliente->persona ? $cliente->persona->nombre : '',
            'apellido' => $cliente->persona ? $cliente->persona->apellido : '',
            'tipo_cliente' => $cliente->tipo_cliente,
            'email' => $cliente->persona ? $cliente->persona->email : '',
            'telefono' => $telefonoPrincipal ?? '',
            'documento' => $cliente->persona ? ($cliente->persona->tipo_documento . $cliente->persona->documento_identidad) : '',
            'direccion' => $direccionPrincipal ? $direccionPrincipal->direccion : '',
            'estado_territorial' => $direccionPrincipal ? $direccionPrincipal->estado : '',
            'ciudad' => $direccionPrincipal ? $direccionPrincipal->ciudad : '',
            'estatus' => $cliente->estatus,
            'other_role' => $otherRole,
            // Datos adicionales para UI de múltiples teléfonos/direcciones
            'telefonos' => $cliente->persona ? $cliente->persona->telefonos : [],
            'direcciones' => $cliente->persona ? $cliente->persona->direcciones : [],
        ]);
    }

    public function update(UpdateClienteRequest $request, $id)
    {
        $cliente = Cliente::with(['persona.telefonos', 'persona.direcciones'])->findOrFail($id);

        $this->clienteService->actualizar($cliente, $request->validated());

        return response()->json(['message' => 'Cliente actualizado exitosamente.']);
    }

    public function destroy($id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado.'], 404);
        }

        // Verificar si tiene cotizaciones asociadas
        $cotizacionesCount = $cliente->cotizaciones()->count();

        $cliente->delete(); // SoftDelete: marca deleted_at

        \Log::warning('Cliente inhabilitado (soft delete)', [
            'cliente_id' => $id,
            'cotizaciones_count' => $cotizacionesCount,
            'user_id' => auth()->id(),
        ]);

        if ($cotizacionesCount > 0) {
            return response()->json([
                'message' => 'Cliente inhabilitado exitosamente.',
                'warning' => 'Este cliente tenía ' . $cotizacionesCount . ' cotización(es). Los registros históricos se mantienen.'
            ]);
        }

        return response()->json(['message' => 'Cliente inhabilitado exitosamente.']);
    }

    /**
     * Restaurar un cliente inhabilitado (soft-deleted).
     */
    public function restore($id)
    {
        $cliente = Cliente::onlyTrashed()->findOrFail($id);
        $cliente->restore();

        \Log::info('Cliente restaurado', [
            'cliente_id' => $id,
            'user_id' => auth()->id(),
        ]);

        return response()->json(['message' => 'Cliente restaurado exitosamente.']);
    }

    public function show($id)
    {
        // withTrashed: también se ven detalles de clientes inhabilitados (desde el historial)
        $cliente = Cliente::withTrashed()->with(['persona.telefonos', 'persona.direcciones'])->findOrFail($id);
        return response()->json([
            'id' => $cliente->id,
            'nombre' => $cliente->nombre ?? 'N/A',
            'apellido' => $cliente->apellido ?? '',
            'tipo_cliente' => $cliente->tipo_cliente,
            'email' => $cliente->email,
            'telefono' => $cliente->telefono,
            'documento' => $cliente->documento,
            'direccion' => $cliente->direccion,
            'estado_territorial' => $cliente->estado_territorial,
            'ciudad' => $cliente->ciudad,
            'estatus' => $cliente->estatus,
            'trashed' => $cliente->trashed(),
            'created_at' => $cliente->created_at ? $cliente->created_at->format('d/m/Y H:i:s') : null,
            'updated_at' => $cliente->updated_at ? $cliente->updated_at->format('d/m/Y H:i:s') : null
        ]);
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
                    $sub->where('documento_identidad', 'LIKE', "%{$escaped}%")
                        ->orWhere('nombre', 'LIKE', "%{$escaped}%")
                        ->orWhere('apellido', 'LIKE', "%{$escaped}%");
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
                'apellido' => $cliente->apellido ?? '',
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
                    'apellido'         => $persona->apellido ?? '',
                    'tipo_documento'   => $persona->tipo_documento,
                    'email'            => $persona->email ?? '',
                    'telefono'         => $persona->telefonoPrincipal ?? '',
                    'estado_geografico'=> $persona->estado_geografico ?? ($dir?->estado ?? ''),
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
        $clientes = $query->get();
        $pdf = Pdf::loadView('admin.clientes.reporte_pdf', compact('clientes'))->setPaper('a4', 'landscape');
        return $pdf->download('reporte_clientes_' . now()->format('Ymd_His') . '.pdf');
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
