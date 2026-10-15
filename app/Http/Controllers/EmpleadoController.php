<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Http\Requests\GuardarEmpleadoRequest;
use App\Models\Cargo;
use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Persona;
use App\Services\EmpleadoService;
use App\Support\CatalogoGeografico;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmpleadoController extends Controller
{
    use RespondeSegunCliente;

    public function __construct(
        private EmpleadoService $empleadoService
    ) {
    }

    /** Filtros de la tabla (query string): la URL es el estado de la vista. */
    private const FILTROS = ['buscar', 'departamento', 'cargo', 'orden', 'historial'];

    public function index(Request $request): Response
    {
        $filtros = array_filter($request->only(self::FILTROS), fn ($v) => $v !== null && $v !== '');

        return Inertia::render('Empleados/Index', [
            'empleados' => $this->consulta($filtros)
                ->paginate(15)
                ->withQueryString()
                ->through(fn (Empleado $e) => $this->fila($e)),
            'filtros' => (object) $filtros,
            'departamentos' => fn () => Departamento::orderBy('nombre')->get(['id', 'nombre']),
            'cargos' => fn () => Cargo::orderBy('nombre')->get(['id', 'nombre', 'departamento_id']),
            'estados' => CatalogoGeografico::mapa(),
            'urls' => [
                'index' => route('empleados.index', absolute: false),
                'reportePdf' => route('empleados.reporte.pdf', absolute: false),
                'checkDocumento' => route('empleados.check-documento', absolute: false),
                'checkEmail' => route('empleados.check-email', absolute: false),
                // Alta rápida de departamento y cargo desde el formulario.
                'departamentos' => route('departamentos.store', absolute: false),
                'cargos' => route('cargos.store', absolute: false),
            ],
        ]);
    }

    /**
     * Consulta de la tabla. Mismas reglas que tenía el endpoint DataTables:
     * búsqueda por nombre, documento, correo, cargo o departamento; filtros por
     * departamento y cargo; historial = solo inhabilitados.
     */
    private function consulta(array $filtros): Builder
    {
        $query = Empleado::with(['persona.telefonos', 'persona.direccion', 'persona.cliente:id,persona_id', 'persona.proveedor:id,persona_id', 'cargo', 'departamento']);

        if (! empty($filtros['historial'])) {
            $query->onlyTrashed();
        }
        if (! empty($filtros['departamento'])) {
            $query->where('departamento_id', $filtros['departamento']);
        }
        if (! empty($filtros['cargo'])) {
            $query->where('cargo_id', $filtros['cargo']);
        }
        if (! empty($filtros['buscar'])) {
            $buscar = trim($filtros['buscar']);
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('persona', function ($p) use ($buscar) {
                    // `nombre` ya contiene el nombre completo del empleado.
                    $p->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "{$buscar}%")
                        ->orWhereRaw('CONCAT(tipo_documento, documento_identidad) like ?', ["{$buscar}%"])
                        ->orWhere('documento_identidad', 'like', "{$buscar}%");
                })
                    ->orWhere('codigo_empleado', 'like', "{$buscar}%")
                    ->orWhereHas('cargo', fn ($c) => $c->where('nombre', 'like', "{$buscar}%"))
                    ->orWhereHas('departamento', fn ($d) => $d->where('nombre', 'like', "{$buscar}%"));
            });
        }

        return match ($filtros['orden'] ?? 'recientes') {
            'codigo' => $query->orderBy('empleado.codigo_empleado'),
            'nombre_asc', 'nombre_desc' => $query
                ->join('persona', 'empleado.persona_id', '=', 'persona.id')
                ->orderBy('persona.nombre', $filtros['orden'] === 'nombre_asc' ? 'asc' : 'desc')
                ->select('empleado.*'),
            default => $query->orderByDesc('empleado.created_at')->orderByDesc('empleado.id'),
        };
    }

    /**
     * Fila de la tabla (todo lo que usan Ver y Editar). Espejo de `EmpleadoFila`
     * en resources/js/pages/Empleados/tipos.ts (lo verifica EmpleadoPaginaTest).
     */
    private function fila(Empleado $e): array
    {
        $persona = $e->persona;
        $direccion = $persona?->direccion;

        return [
            'id' => $e->id,
            'codigo' => $e->codigo_empleado,
            'tipo_documento' => $persona?->tipo_documento,
            'numero_documento' => $persona?->documento_identidad,
            'documento' => $e->documento,
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
            'fecha_nacimiento' => $e->fecha_nacimiento?->format('Y-m-d'),
            'genero' => $e->genero === 'Otro' ? null : $e->genero,
            'fecha_ingreso' => $e->fecha_ingreso ? Carbon::parse($e->fecha_ingreso)->format('Y-m-d') : null,
            'departamento_id' => $e->departamento_id,
            'departamento' => $e->departamento?->nombre,
            'cargo_id' => $e->cargo_id,
            'cargo' => $e->cargo?->nombre,
            // La persona es compartida: editar sus datos también cambia estos registros.
            'otros_roles' => array_values(array_filter([
                $persona?->cliente ? 'cliente' : null,
                $persona?->proveedor ? 'proveedor' : null,
            ])),
            'inhabilitado' => $e->trashed(),
            'creado' => $e->created_at?->format('Y-m-d H:i'),
        ];
    }

    public function store(GuardarEmpleadoRequest $request)
    {
        $this->empleadoService->crear($request->validated());

        return $this->responder($request, 'Empleado creado exitosamente.', ['message' => 'Empleado creado exitosamente.']);
    }

    public function update(GuardarEmpleadoRequest $request, $id)
    {
        $this->empleadoService->actualizar(Empleado::findOrFail($id), $request->validated());

        return $this->responder($request, 'Empleado actualizado exitosamente.', ['message' => 'Empleado actualizado exitosamente.']);
    }

    public function destroy(Request $request, $id)
    {
        Empleado::findOrFail($id)->delete(); // SoftDelete: marca deleted_at (va al historial)

        return $this->responder($request, 'Empleado inhabilitado exitosamente.', ['message' => 'Empleado inhabilitado exitosamente.']);
    }

    /**
     * Restaurar un empleado inhabilitado (soft-deleted). Estándar Clientes/Proveedores.
     */
    public function restore(Request $request, $id)
    {
        Empleado::onlyTrashed()->findOrFail($id)->restore();

        return $this->responder($request, 'Empleado restaurado exitosamente.', ['message' => 'Empleado restaurado exitosamente.']);
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
        return $pdf->stream('reporte_empleados_' . now()->format('Ymd_His') . '.pdf');
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
}
