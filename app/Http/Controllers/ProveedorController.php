<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\Persona;
use App\Services\ProveedorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ProveedorController extends Controller
{
    public function __construct(
        private ProveedorService $proveedorService
    ) {
    }
    public function index(Request $request)
    {
        $historial = $request->has('historial');
        return view('admin.proveedores.index', compact('historial'));
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
                    ->orWhere('apellido', 'like', "%{$q}%")
                    ->orWhere('documento_identidad', 'like', "%{$q}%");
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

    public function getProveedores(Request $request)
    {
        // ── Base query con relaciones ──
        $query = Proveedor::with(['persona.telefonos', 'persona.direcciones']);

        // ══════════════════════════════════════════════════════════
        // FILTROS AVANZADOS — Server-Side (Patrón Maestro S-07)
        // Réplica exacta del patrón de ClienteController.
        // ══════════════════════════════════════════════════════════

        // Filtro: Tipo de Proveedor (natural, juridico)
        if ($request->filled('filter_tipo_proveedor')) {
            $query->where('tipo_proveedor', $request->input('filter_tipo_proveedor'));
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
                $query->orderBy('proveedor.created_at', 'asc');
                break;
            case 'nombre_asc':
                $query->join('persona', 'proveedor.persona_id', '=', 'persona.id')
                      ->orderBy('persona.nombre', 'asc')
                      ->select('proveedor.*');
                break;
            case 'nombre_desc':
                $query->join('persona', 'proveedor.persona_id', '=', 'persona.id')
                      ->orderBy('persona.nombre', 'desc')
                      ->select('proveedor.*');
                break;
            case 'recientes':
            default:
                $query->orderBy('proveedor.created_at', 'desc');
                break;
        }

        return DataTables::of($query)
            ->addColumn('nombre_display', fn($p) => $p->nombre_completo ?? 'N/A')
            ->addColumn('documento_display', fn($p) => $p->documento ?? 'N/A')
            ->addColumn('tipo_proveedor', fn($p) => $p->tipo_proveedor ?? 'juridico')
            ->addColumn('tipo_display', fn($p) => ($p->tipo_proveedor ?? 'juridico') === 'natural' ? 'Natural' : 'Jurídico')
            ->addColumn('telefono_display', fn($p) => $p->telefono_unificado)
            ->addColumn('email_display', fn($p) => $p->email_unificado)
            ->addColumn('estado', fn($p) => $p->estado)
            ->addColumn('trashed', fn($p) => $p->trashed())
            ->make(true);
    }

    public function store(Request $request)
    {
        $tipoProveedor = $request->input('tipo_proveedor', 'juridico');

        if ($tipoProveedor === 'natural') {
            $request->validate([
                'tipo_proveedor' => 'required|in:natural,juridico',
                'nombre' => 'required|string|max:100',
                'apellido' => 'required|string|max:100',
                'tipo_documento' => 'required|in:V-,E-,J-,G-',
                'documento_identidad' => 'required|string|max:20|unique:persona,documento_identidad',
                'telefono' => 'required|string|max:20',
                'email' => 'required|email|max:255|unique:persona,email',
                'direccion' => 'required|string|max:255',
                'ciudad' => 'nullable|string|max:100',
                'estado_territorial' => 'nullable|string|max:50',
            ]);

            $proveedor = $this->proveedorService->crearNatural($request->all());
            return response()->json([
                'success'   => 'Proveedor natural creado exitosamente.',
                'proveedor' => $this->proveedorPayload($proveedor),
            ]);
        } else {
            $request->validate([
                'tipo_proveedor' => 'required|in:natural,juridico',
                'razon_social' => 'required|string|max:100',
                'rif' => 'required|string|max:15|unique:persona,documento_identidad,NULL,id,tipo_documento,' . $this->parseRifPrefix($request->rif),
                'direccion' => 'required|string|max:200',
                'telefono' => 'required|string|max:20',
                'email' => 'required|email|max:100|unique:persona,email',
                'contacto' => 'nullable|string|max:100',
                'telefono_contacto' => 'nullable|string|max:20',
            ]);

            $proveedor = $this->proveedorService->crearJuridico($request->all());
            return response()->json([
                'success'   => 'Proveedor jurídico creado exitosamente.',
                'proveedor' => $this->proveedorPayload($proveedor),
            ]);
        }
    }

    /**
     * Crea un proveedor reutilizando una persona ya existente en el sistema
     * (cliente, empleado u otro rol). Idempotente: si la persona ya es
     * proveedor activo lo devuelve; si está inhabilitado lo reactiva.
     * Mismo patrón que ClienteController@createFromPersona.
     */
    public function createFromPersona(int $personaId): JsonResponse
    {
        $persona = Persona::find($personaId);

        if (!$persona) {
            return response()->json([
                'success' => false,
                'message' => 'Persona no encontrada.',
            ], 404);
        }

        // Ya es proveedor activo → devolverlo
        $proveedorExistente = Proveedor::where('persona_id', $persona->id)
            ->where('estado', 1)
            ->first();

        if ($proveedorExistente) {
            return response()->json([
                'success'   => true,
                'message'   => 'La persona ya estaba registrada como proveedor activo.',
                'reused'    => true,
                'proveedor' => $this->proveedorPayload($proveedorExistente),
            ]);
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

            return response()->json([
                'success'   => true,
                'message'   => 'Proveedor reactivado correctamente.',
                'reused'    => true,
                'proveedor' => $this->proveedorPayload($proveedorInactivo),
            ]);
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

        return response()->json([
            'success'   => true,
            'message'   => 'Proveedor creado a partir de la persona registrada.',
            'reused'    => false,
            'proveedor' => $this->proveedorPayload($proveedor),
        ]);
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

    public function show($id)
    {
        // withTrashed: también se ven detalles de proveedores inhabilitados (desde el historial)
        $proveedor = Proveedor::withTrashed()->with('persona.telefonos', 'persona.direcciones')->findOrFail($id);
        $persona = $proveedor->persona;
        $telefonoPrincipal = $persona ? $persona->telefonos->where('es_principal', true)->first() : null;
        $direccionPrincipal = $persona ? $persona->direcciones->where('es_principal', true)->first() : null;

        $data = [
            'id' => $proveedor->id,
            'tipo_proveedor' => $proveedor->tipo_proveedor,
            'persona_id' => $proveedor->persona_id,
            'telefono' => $telefonoPrincipal ? $telefonoPrincipal->numero : null,
            'email' => $persona ? $persona->email : null,
            'direccion' => $direccionPrincipal ? $direccionPrincipal->direccion : null,
            'contacto' => $proveedor->contacto,
            'telefono_contacto' => $proveedor->telefono_contacto,
            'nombre_display' => $proveedor->nombre_completo,
            'documento_display' => $proveedor->documento,
            'estado' => $proveedor->estado,
            'trashed' => $proveedor->trashed(),
            'created_at' => $proveedor->created_at->format('d/m/Y H:i:s'),
            'updated_at' => $proveedor->updated_at->format('d/m/Y H:i:s'),
        ];

        if ($proveedor->esNatural() && $persona) {
            $data['nombre'] = $persona->nombre;
            $data['apellido'] = $persona->apellido;
            $data['tipo_documento'] = $persona->tipo_documento;
            $data['documento_identidad'] = $persona->documento_identidad;
            $data['ciudad'] = $direccionPrincipal ? $direccionPrincipal->ciudad : null;
            $data['estado_territorial'] = $direccionPrincipal ? $direccionPrincipal->estado : null;
        } else {
            // Para jurídico: reconstruir campos para compatibilidad con la vista
            $data['razon_social'] = $persona ? $persona->nombre : null;
            $data['rif'] = $persona ? $persona->tipo_documento . $persona->documento_identidad : null;
        }

        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $tipoProveedor = $request->input('tipo_proveedor', $proveedor->tipo_proveedor ?? 'juridico');

        if ($tipoProveedor === 'natural') {
            $request->validate([
                'nombre' => 'required|string|max:100',
                'apellido' => 'required|string|max:100',
                'tipo_documento' => 'required|in:V-,E-,J-,G-',
                'documento_identidad' => 'required|string|max:20|unique:persona,documento_identidad,' . ($proveedor->persona_id ?? 0),
                'telefono' => 'required|string|max:20',
                'email' => 'required|email|max:255|unique:persona,email,' . ($proveedor->persona_id ?? 0),
                'direccion' => 'required|string|max:255',
                'ciudad' => 'nullable|string|max:100',
                'estado_territorial' => 'nullable|string|max:50',
            ]);

            $this->proveedorService->actualizarNatural($proveedor, $request->all());
            return response()->json(['success' => 'Proveedor actualizado exitosamente.']);
        } else {
            $request->validate([
                'razon_social' => 'required|string|max:100',
                'rif' => 'required|string|max:15',
                'direccion' => 'required|string|max:200',
                'telefono' => 'required|string|max:20',
                'email' => 'required|email|max:100|unique:persona,email,' . ($proveedor->persona_id ?? 0),
                'contacto' => 'nullable|string|max:100',
                'telefono_contacto' => 'nullable|string|max:20',
                'ciudad' => 'nullable|string|max:100',
                'estado_territorial' => 'nullable|string|max:50',
            ]);

            $this->proveedorService->actualizarJuridico($proveedor, $request->all());
            return response()->json(['success' => 'Proveedor actualizado exitosamente.']);
        }
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->delete(); // SoftDelete: marca deleted_at
        return response()->json(['success' => 'Proveedor inhabilitado exitosamente.']);
    }

    /**
     * Restaurar un proveedor inhabilitado (soft-deleted).
     */
    public function restore($id)
    {
        $proveedor = Proveedor::onlyTrashed()->findOrFail($id);
        $proveedor->restore();

        return response()->json(['success' => 'Proveedor restaurado exitosamente.']);
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
        $proveedores = $query->get();
        $pdf = \PDF::loadView('admin.proveedores.reporte_pdf', compact('proveedores'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('proveedores_' . now()->format('Y-m-d_H-i-s') . '.pdf');
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

    /**
     * Extraer prefijo del RIF para validación unique compuesta.
     */
    private function parseRifPrefix(string $rif): string
    {
        if (preg_match('/^(V-|J-|E-|G-)/', $rif, $matches)) {
            return $matches[1];
        }
        return 'J-';
    }
}
