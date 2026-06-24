<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Persona;
use App\Services\EmpleadoService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use PDF;

class EmpleadoController extends Controller
{
    public function __construct(
        private EmpleadoService $empleadoService
    ) {
    }

    public function index(Request $request)
    {
        $historial = $request->has('historial');
        $departamentos = Departamento::orderBy('nombre')->pluck('nombre', 'id');
        $cargos = Cargo::orderBy('nombre')->pluck('nombre', 'id');
        return view('admin.empleados.index', compact('departamentos', 'cargos', 'historial'));
    }


    public function getEmpleados(Request $request)
    {
        // ── Base query con relaciones ──
        $query = Empleado::with(['persona.telefonos', 'persona.direcciones', 'cargo', 'departamento']);

        // ══════════════════════════════════════════════════════════
        // FILTROS AVANZADOS — Server-Side (Patrón Maestro S-07)
        // ══════════════════════════════════════════════════════════

        // Filtro: Departamento
        if ($request->filled('filter_departamento')) {
            $query->where('departamento_id', $request->input('filter_departamento'));
        }

        // Filtro: Cargo
        if ($request->filled('filter_cargo')) {
            $query->where('cargo_id', $request->input('filter_cargo'));
        }

        // Activos vs Historial: lo define la página (no un filtro). La principal
        // muestra solo activos; el historial (?historial=true) solo inhabilitados.
        if ($request->boolean('historial')) {
            $query->onlyTrashed();
        }

        // ══════════════════════════════════════════════════════════
        // ORDENAMIENTO — Selector "Ordenar por" del frontend
        // Fallback: más recientes primero (created_at DESC)
        // ══════════════════════════════════════════════════════════
        $orden = $request->input('filter_orden', 'recientes');

        switch ($orden) {
            case 'nombre_asc':
                $query->join('persona', 'empleado.persona_id', '=', 'persona.id')
                      ->orderBy('persona.nombre', 'asc')
                      ->select('empleado.*');
                break;
            case 'nombre_desc':
                $query->join('persona', 'empleado.persona_id', '=', 'persona.id')
                      ->orderBy('persona.nombre', 'desc')
                      ->select('empleado.*');
                break;
            case 'recientes':
            default:
                $query->orderBy('empleado.created_at', 'desc');
                break;
        }

        return DataTables::of($query)
            // Búsqueda estricta: identidad del empleado (nombre/apellido, documento
            // y email de la persona) más su cargo y departamento. Sobrescribe el
            // buscador global para filtrar exactamente por esas columnas.
            ->filter(function ($query) use ($request) {
                $keyword = trim((string) $request->input('search.value'));
                if ($keyword === '') {
                    return;
                }
                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('persona', function ($p) use ($keyword) {
                        // `nombre` ya contiene el nombre completo del empleado.
                        $p->where('nombre', 'like', "%{$keyword}%")
                          ->orWhere('email', 'like', "{$keyword}%")
                          ->orWhereRaw("CONCAT(tipo_documento, documento_identidad) like ?", ["{$keyword}%"]);
                    })
                    ->orWhereHas('cargo', fn($c) => $c->where('nombre', 'like', "{$keyword}%"))
                    ->orWhereHas('departamento', fn($d) => $d->where('nombre', 'like', "{$keyword}%"));
                });
            }, true)
            ->addColumn('nombre_completo', function ($emp) {
                return $emp->persona ? $emp->persona->nombre_completo : 'N/A';
            })
            ->addColumn('cargo', function ($emp) {
                return $emp->cargo ? $emp->cargo->nombre : 'N/A';
            })
            ->addColumn('departamento', function ($emp) {
                return $emp->departamento ? $emp->departamento->nombre : 'N/A';
            })
            ->addColumn('documento', function ($emp) {
                return $emp->documento ?? 'N/A';
            })
            ->addColumn('email', function ($emp) {
                return $emp->email ?? 'N/A';
            })
            ->addColumn('telefono', function ($emp) {
                return $emp->telefono ?? 'N/A';
            })
            ->addColumn('actions', function ($emp) {
                return '
                    <div class="d-flex gap-2 justify-content-center">
                        <button class="btn btn-sm btn-soft-info view-btn" data-id="' . $emp->id . '" title="Ver"><i class="ri-eye-fill"></i></button>
                        <button class="btn btn-sm btn-soft-success edit-btn" data-id="' . $emp->id . '" title="Editar"><i class="ri-pencil-fill"></i></button>
                        <button class="btn btn-sm btn-soft-danger remove-btn" data-id="' . $emp->id . '" title="Eliminar"><i class="ri-delete-bin-fill"></i></button>
                    </div>
                ';
            })
            ->addColumn('trashed', fn($emp) => $emp->trashed())
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'              => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apellido'            => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'documento_identidad' => 'required|string|min:6|max:15|regex:/^[0-9]+$/',
            'tipo_documento'      => 'required|in:V-,E-,J-,G-',
            'email'               => 'nullable|email:rfc|max:255',
            'telefonos'           => 'required|array|min:1|max:3',
            'telefonos.*.numero'  => ['required', 'string', 'regex:/^[0-9]{4}-[0-9]{7}$/'],
            'telefonos.*.tipo'    => 'required|in:movil,casa,trabajo',
            'telefonos.*.es_principal' => 'required|boolean',
            'direccion'           => 'nullable|string|max:500',
            'ciudad'              => 'nullable|string|max:100',
            'estado_geografico'   => 'nullable|string|max:100',
            'fecha_nacimiento'    => 'nullable|date|before:-18 years',
            'genero'              => 'nullable|in:M,F',
            'codigo_empleado'     => 'nullable|string|max:50|unique:empleado,codigo_empleado',
            'fecha_ingreso'       => 'required|date|before_or_equal:today',
            'departamento_id'     => 'required|exists:departamento,id',
            'cargo_id'            => ['required', 'exists:cargo,id', function ($attr, $value, $fail) use ($request) {
                $cargo = Cargo::find($value);
                if ($cargo && (int) $cargo->departamento_id !== (int) $request->departamento_id) {
                    $fail('El cargo seleccionado no pertenece al departamento elegido.');
                }
            }],
        ], [
            'nombre.required'              => 'El nombre es obligatorio',
            'nombre.min'                   => 'El nombre debe tener al menos 2 caracteres',
            'nombre.regex'                 => 'El nombre solo puede contener letras y espacios',
            'apellido.required'            => 'El apellido es obligatorio',
            'apellido.min'                 => 'El apellido debe tener al menos 2 caracteres',
            'apellido.regex'               => 'El apellido solo puede contener letras y espacios',
            'documento_identidad.required' => 'El documento de identidad es obligatorio',
            'documento_identidad.min'      => 'El documento debe tener al menos 6 dígitos',
            'documento_identidad.regex'    => 'El documento solo puede contener números',
            'tipo_documento.required'      => 'Debe seleccionar el tipo de documento',
            'email.email'                  => 'El email debe ser una dirección válida',
            'telefonos.required'           => 'Agrega al menos un teléfono.',
            'telefonos.max'                => 'Máximo 3 teléfonos por persona.',
            'telefonos.*.numero.required'  => 'El número de teléfono es obligatorio.',
            'telefonos.*.numero.regex'     => 'El teléfono debe tener el formato 0424-1234567.',
            'fecha_nacimiento.before'      => 'El empleado debe ser mayor de 18 años',
            'fecha_ingreso.required'       => 'La fecha de ingreso es obligatoria',
            'fecha_ingreso.before_or_equal' => 'La fecha de ingreso no puede ser futura',
            'departamento_id.required'     => 'El departamento es obligatorio',
            'departamento_id.exists'       => 'El departamento seleccionado no es válido',
            'cargo_id.required'            => 'El cargo es obligatorio',
            'cargo_id.exists'              => 'El cargo seleccionado no es válido',
        ]);

        $this->empleadoService->crear($request->all());

        return response()->json(['message' => 'Empleado creado exitosamente.']);
    }

    public function show($id)
    {
        // withTrashed: también se ven detalles de empleados inhabilitados (desde el historial)
        $empleado = Empleado::withTrashed()
            ->with(['persona.telefonos', 'persona.direcciones', 'cargo', 'departamento'])
            ->findOrFail($id);

        $data                = $empleado->toArray();
        $data['telefono']    = $empleado->telefono;
        $data['telefonos']   = $empleado->persona ? $empleado->persona->telefonos : [];
        $data['direccion']   = $empleado->direccion;
        $data['ciudad']      = $empleado->ciudad;
        $data['cargo']       = $empleado->cargo ? $empleado->cargo->nombre : null;
        $data['departamento'] = $empleado->departamento ? $empleado->departamento->nombre : null;
        $data['trashed']     = $empleado->trashed();

        return response()->json($data);
    }

    public function edit($id)
    {
        $empleado = Empleado::with(['persona.telefonos', 'persona.direcciones', 'cargo', 'departamento'])->findOrFail($id);

        $data = $empleado->toArray();
        // fecha_nacimiento y genero ahora viven en empleado (no en persona)
        $data['fecha_nacimiento'] = $empleado->fecha_nacimiento
            ? $empleado->fecha_nacimiento->format('Y-m-d')
            : null;
        $data['genero'] = $empleado->genero;
        $data['fecha_ingreso'] = $empleado->fecha_ingreso
            ? \Carbon\Carbon::parse($empleado->fecha_ingreso)->format('Y-m-d')
            : null;

        $data['telefono']         = $empleado->telefono;
        $data['telefonos']        = $empleado->persona ? $empleado->persona->telefonos : [];
        $data['direccion']        = $empleado->direccion;
        $data['ciudad']           = $empleado->ciudad;
        // El estado de ubicación vive ahora en la dirección (no en persona)
        $dirPrincipal = $empleado->persona?->direccionPrincipal;
        $data['persona']['estado_geografico'] = $dirPrincipal?->estado;
        $data['cargo']            = $empleado->cargo ? $empleado->cargo->nombre : null;
        $data['departamento']     = $empleado->departamento ? $empleado->departamento->nombre : null;
        $data['cargo_id']         = $empleado->cargo_id;
        $data['departamento_id']  = $empleado->departamento_id;

        $data['other_role'] = Cliente::where('persona_id', $empleado->persona_id)->exists()
            ? 'cliente'
            : null;

        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);
        $persona  = $empleado->persona;

        // El documento de identidad y su tipo son inmutables en edición (igual que en
        // Clientes): la cédula no se puede editar, por eso el campo va deshabilitado en
        // el modal y no se envía. NO se valida ni se reescribe aquí.
        $request->validate([
            'nombre'              => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apellido'            => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'email'               => 'nullable|email:rfc|max:255|unique:persona,email,' . $persona->id,
            'telefonos'           => 'required|array|min:1|max:3',
            'telefonos.*.numero'  => ['required', 'string', 'regex:/^[0-9]{4}-[0-9]{7}$/'],
            'telefonos.*.tipo'    => 'required|in:movil,casa,trabajo',
            'telefonos.*.es_principal' => 'required|boolean',
            'direccion'           => 'nullable|string|max:500',
            'ciudad'              => 'nullable|string|max:100',
            'estado_geografico'   => 'nullable|string|max:100',
            'fecha_nacimiento'    => 'nullable|date|before:-18 years',
            'genero'              => 'nullable|in:M,F',
            'codigo_empleado'     => 'required|string|max:50|unique:empleado,codigo_empleado,' . $id,
            'fecha_ingreso'       => 'required|date|before_or_equal:today',
            'departamento_id'     => 'required|exists:departamento,id',
            'cargo_id'            => ['required', 'exists:cargo,id', function ($attr, $value, $fail) use ($request) {
                $cargo = Cargo::find($value);
                if ($cargo && (int) $cargo->departamento_id !== (int) $request->departamento_id) {
                    $fail('El cargo seleccionado no pertenece al departamento elegido.');
                }
            }],
        ], [
            'nombre.required'               => 'El nombre es obligatorio',
            'nombre.min'                    => 'El nombre debe tener al menos 2 caracteres',
            'nombre.regex'                  => 'El nombre solo puede contener letras y espacios',
            'apellido.required'             => 'El apellido es obligatorio',
            'apellido.min'                  => 'El apellido debe tener al menos 2 caracteres',
            'apellido.regex'                => 'El apellido solo puede contener letras y espacios',
            'email.email'                   => 'El email debe ser una dirección válida',
            'email.unique'                  => 'Este email ya está registrado',
            'telefonos.required'            => 'Agrega al menos un teléfono.',
            'telefonos.max'                 => 'Máximo 3 teléfonos por persona.',
            'telefonos.*.numero.required'   => 'El número de teléfono es obligatorio.',
            'telefonos.*.numero.regex'      => 'El teléfono debe tener el formato 0424-1234567.',
            'fecha_nacimiento.before'       => 'El empleado debe ser mayor de 18 años',
            'fecha_ingreso.required'        => 'La fecha de ingreso es obligatoria',
            'fecha_ingreso.before_or_equal' => 'La fecha de ingreso no puede ser futura',
            'departamento_id.required'      => 'El departamento es obligatorio',
            'departamento_id.exists'        => 'El departamento seleccionado no es válido',
            'cargo_id.required'             => 'El cargo es obligatorio',
            'cargo_id.exists'               => 'El cargo seleccionado no es válido',
        ]);

        $this->empleadoService->actualizar($empleado, $request->all());

        return response()->json(['message' => 'Empleado actualizado exitosamente.']);
    }

    public function destroy($id)
    {
        $empleado = Empleado::findOrFail($id);
        $empleado->delete(); // SoftDelete: marca deleted_at (va al historial)
        return response()->json(['message' => 'Empleado inhabilitado exitosamente.']);
    }

    /**
     * Restaurar un empleado inhabilitado (soft-deleted). Estándar Clientes/Proveedores.
     */
    public function restore($id)
    {
        $empleado = Empleado::onlyTrashed()->findOrFail($id);
        $empleado->restore();
        return response()->json(['message' => 'Empleado restaurado exitosamente.']);
    }

    public function checkDocumento(Request $request)
    {
        $numero = $request->input('numero');
        if (!$numero) {
            return response()->json(['exists' => false]);
        }

        $persona = Persona::with(['telefonos', 'direcciones'])->where('documento_identidad', $numero)->first();
        $exists  = $persona && Empleado::where('persona_id', $persona->id)->exists();

        $otherRole  = null;
        $personaData = null;
        if ($persona && !$exists) {
            if (Cliente::where('persona_id', $persona->id)->exists()) {
                $otherRole = 'cliente';
                $dir = $persona->direccionPrincipal;
                $personaData = [
                    'nombre'            => $persona->nombre,
                    'apellido'          => '',
                    'tipo_documento'    => $persona->tipo_documento,
                    'email'             => $persona->email ?? '',
                    'telefono'          => $persona->telefonoPrincipal ?? '',
                    'telefonos'         => $persona->telefonos,
                    // fecha_nacimiento/genero ya no viven en persona; al promover un
                    // cliente a empleado estos campos se capturan en el form de empleado.
                    'genero'            => '',
                    'fecha_nacimiento'  => '',
                    'estado_geografico' => $dir?->estado ?? '',
                    'ciudad'            => $dir?->ciudad ?? '',
                    'direccion'         => $dir?->direccion ?? '',
                ];
            }
        }

        return response()->json(['exists' => $exists, 'other_role' => $otherRole, 'persona' => $personaData]);
    }

    public function reportePdf(Request $request)
    {
        $query = Empleado::with(['persona', 'cargo', 'departamento'])->orderBy('codigo_empleado', 'asc');
        if ($request->filled('departamento_id')) {
            $query->where('departamento_id', $request->departamento_id);
        }
        if ($request->filled('cargo_id')) {
            $query->where('cargo_id', $request->cargo_id);
        }
        // Estatus: 1 = activos (default), 0 = inhabilitados (trashed) — estándar Clientes/Proveedores
        if ($request->input('estatus') === '0') {
            $query->onlyTrashed();
        }
        // Rango por fecha de ingreso
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_ingreso', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_ingreso', '<=', $request->fecha_hasta);
        }
        $empleados = $query->get();

        $filtros = [];
        if ($request->filled('departamento_id')) {
            $filtros['Departamento'] = optional(Departamento::find($request->departamento_id))->nombre
                ?? ('#' . $request->departamento_id);
        }
        if ($request->filled('cargo_id')) {
            $filtros['Cargo'] = optional(Cargo::find($request->cargo_id))->nombre
                ?? ('#' . $request->cargo_id);
        }
        if ($request->input('estatus') === '0') {
            $filtros['Estatus'] = 'Inhabilitados';
        }
        if ($rango = \App\Support\ReporteFiltros::rango($request->fecha_desde, $request->fecha_hasta)) {
            $filtros['Fecha de ingreso'] = $rango;
        }

        $pdf = \PDF::loadView('admin.empleados.reporte_pdf', compact('empleados', 'filtros'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('reporte_empleados_' . now()->format('Ymd_His') . '.pdf');
    }

    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        if (!$email)
            return response()->json(['exists' => false]);

        $query = Persona::where('email', $email);

        $excludeEmpleadoId = $request->input('exclude_id');
        if ($excludeEmpleadoId) {
            $empleado = Empleado::find($excludeEmpleadoId);
            if ($empleado && $empleado->persona_id) {
                $query->where('id', '!=', $empleado->persona_id);
            }
        }

        return response()->json(['exists' => $query->exists()]);
    }

    public function checkCodigo(Request $request)
    {
        $codigo = $request->input('codigo');
        if (!$codigo)
            return response()->json(['exists' => false]);
        $exists = Empleado::where('codigo_empleado', $codigo)->exists();
        return response()->json(['exists' => $exists]);
    }

    /**
     * AJAX: Retorna cargos activos filtrados por departamento_id
     */
    public function getCargos(Request $request)
    {
        $departamentoId = $request->input('departamento_id');
        if (!$departamentoId) {
            return response()->json([]);
        }

        $cargos = Cargo::where('departamento_id', $departamentoId)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json($cargos);
    }

}
