<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Persona;
use App\Models\Telefono;
use App\Models\Direccion;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use PDF;

class EmpleadoController extends Controller
{
    public function index()
    {
        return view('admin.empleados.index');
    }

    public function getEmpleados()
    {
        // Cargar persona con telefonos y direcciones normalizadas
        $empleados = Empleado::with(['persona.telefonos', 'persona.direcciones'])->get();

        return DataTables::of($empleados)
            ->addColumn('nombre_completo', function ($empleado) {
                return $empleado->persona ? $empleado->persona->nombre_completo : 'N/A';
            })
            ->addColumn('documento', function ($empleado) {
                return $empleado->documento ?? 'N/A'; // Usa accessor
            })
            ->addColumn('email', function ($empleado) {
                return $empleado->email ?? 'N/A'; // Usa accessor
            })
            ->addColumn('telefono', function ($empleado) {
                return $empleado->telefono ?? 'N/A'; // Usa accessor que obtiene telefono_principal
            })
            ->addColumn('actions', function ($empleado) {
                return '
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-info view-btn" data-id="' . $empleado->id . '"><i class="ri-eye-fill"></i></button>
                        <button class="btn btn-sm btn-success edit-btn" data-id="' . $empleado->id . '"><i class="ri-pencil-fill"></i></button>
                        <button class="btn btn-sm btn-danger remove-btn" data-id="' . $empleado->id . '"><i class="ri-delete-bin-fill"></i></button>
                    </div>
                ';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apellido' => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'documento_identidad' => 'required|string|min:6|max:15|regex:/^[0-9]+$/|unique:persona,documento_identidad',
            'tipo_documento' => 'required|in:V-,E-,J-,G-',
            'email' => 'nullable|email:rfc,dns|max:255|unique:persona,email',
            'telefono' => 'nullable|string|regex:/^[0-9]{4}-[0-9]{7}$/',
            'direccion' => 'nullable|string|max:500',
            'ciudad' => 'nullable|string|max:100',
            'estado_persona' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'nullable|date|before:-18 years',
            'genero' => 'nullable|in:M,F,Otro',
            'codigo_empleado' => 'nullable|string|max:50|unique:empleado,codigo_empleado',
            'fecha_ingreso' => 'required|date|before_or_equal:today',
            'cargo' => 'required|string|min:3|max:100',
            'departamento' => 'required|string|in:Administracion,Produccion',
            'estado' => 'required|boolean',
        ], [
            // Mensajes personalizados
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres',
            'nombre.regex' => 'El nombre solo puede contener letras y espacios',
            'apellido.required' => 'El apellido es obligatorio',
            'apellido.min' => 'El apellido debe tener al menos 2 caracteres',
            'apellido.regex' => 'El apellido solo puede contener letras y espacios',
            'documento_identidad.required' => 'El documento de identidad es obligatorio',
            'documento_identidad.min' => 'El documento debe tener al menos 6 dígitos',
            'documento_identidad.regex' => 'El documento solo puede contener números',
            'documento_identidad.unique' => 'Este documento ya está registrado',
            'tipo_documento.required' => 'Debe seleccionar el tipo de documento',
            'email.email' => 'El email debe ser una dirección válida',
            'email.unique' => 'Este email ya está registrado',
            'telefono.regex' => 'El teléfono debe tener el formato 0424-1234567',
            'fecha_nacimiento.before' => 'El empleado debe ser mayor de 18 años',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria',
            'fecha_ingreso.before_or_equal' => 'La fecha de ingreso no puede ser futura',
            'cargo.required' => 'El cargo es obligatorio',
            'cargo.min' => 'El cargo debe tener al menos 3 caracteres',
            'departamento.required' => 'El departamento es obligatorio',
            'departamento.in' => 'Debe seleccionar un departamento válido (Administracion o Produccion)',
            'estado.required' => 'Debe seleccionar el estado del empleado',
        ]);

        try {
            DB::beginTransaction();

            // Crear persona (sin telefono/direccion en la tabla persona)
            $persona = Persona::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'documento_identidad' => $request->documento_identidad,
                'tipo_documento' => $request->tipo_documento,
                'email' => $request->email,
                'estado_persona' => $request->estado_persona,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'genero' => $request->genero,
            ]);

            // Crear teléfono en tabla normalizada
            if (!empty($request->telefono)) {
                Telefono::create([
                    'persona_id' => $persona->id,
                    'numero' => $request->telefono,
                    'tipo' => 'movil',
                    'es_principal' => true,
                ]);
            }

            // Crear dirección en tabla normalizada
            if (!empty($request->direccion) || !empty($request->ciudad)) {
                Direccion::create([
                    'persona_id' => $persona->id,
                    'direccion' => $request->direccion ?? '',
                    'ciudad' => $request->ciudad,
                    'tipo' => 'casa',
                    'es_principal' => true,
                ]);
            }

            // Generar código de empleado si no se proporciona
            $codigoEmpleado = $request->codigo_empleado;
            if (!$codigoEmpleado) {
                $ultimoCodigo = Empleado::max('codigo_empleado');
                $numero = $ultimoCodigo ? ((int) substr($ultimoCodigo, 4) + 1) : 1;
                $codigoEmpleado = 'EMP-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
            }

            // Crear empleado
            Empleado::create([
                'persona_id' => $persona->id,
                'codigo_empleado' => $codigoEmpleado,
                'fecha_ingreso' => $request->fecha_ingreso,
                'cargo' => $request->cargo,
                'departamento' => $request->departamento,
                'estado' => $request->estado,
            ]);

            DB::commit();

            return response()->json(['message' => 'Empleado creado exitosamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear empleado: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $empleado = Empleado::with(['persona.telefonos', 'persona.direcciones'])->findOrFail($id);

        // Convertir a array y agregar campos normalizados
        $data = $empleado->toArray();
        $data['telefono'] = $empleado->telefono;
        $data['direccion'] = $empleado->direccion;
        $data['ciudad'] = $empleado->ciudad;

        return response()->json($data);
    }

    public function edit($id)
    {
        $empleado = Empleado::with(['persona.telefonos', 'persona.direcciones'])->findOrFail($id);

        // Formatear fechas para los campos input type="date"
        $data = $empleado->toArray();
        $data['persona']['fecha_nacimiento'] = $empleado->persona->fecha_nacimiento
            ? $empleado->persona->fecha_nacimiento->format('Y-m-d')
            : null;
        $data['fecha_ingreso'] = $empleado->fecha_ingreso
            ? \Carbon\Carbon::parse($empleado->fecha_ingreso)->format('Y-m-d')
            : null;

        // Agregar datos de telefono/direccion usando accessors (tablas normalizadas)
        $data['telefono'] = $empleado->telefono;
        $data['direccion'] = $empleado->direccion;
        $data['ciudad'] = $empleado->ciudad;

        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);
        $persona = $empleado->persona;

        $request->validate([
            'nombre' => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apellido' => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'documento_identidad' => 'required|string|min:6|max:15|regex:/^[0-9]+$/|unique:persona,documento_identidad,' . $persona->id,
            'tipo_documento' => 'required|in:V-,E-,J-,G-',
            'email' => 'nullable|email:rfc,dns|max:255|unique:persona,email,' . $persona->id,
            'telefono' => 'nullable|string|regex:/^[0-9]{4}-[0-9]{7}$/',
            'direccion' => 'nullable|string|max:500',
            'ciudad' => 'nullable|string|max:100',
            'estado_persona' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'nullable|date|before:-18 years',
            'genero' => 'nullable|in:M,F,Otro',
            'codigo_empleado' => 'required|string|max:50|unique:empleado,codigo_empleado,' . $id,
            'fecha_ingreso' => 'required|date|before_or_equal:today',
            'cargo' => 'required|string|min:3|max:100',
            'departamento' => 'required|string|in:Administracion,Produccion',
            'estado' => 'required|boolean',
        ], [
            // Mensajes personalizados
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres',
            'nombre.regex' => 'El nombre solo puede contener letras y espacios',
            'apellido.required' => 'El apellido es obligatorio',
            'apellido.min' => 'El apellido debe tener al menos 2 caracteres',
            'apellido.regex' => 'El apellido solo puede contener letras y espacios',
            'documento_identidad.required' => 'El documento de identidad es obligatorio',
            'documento_identidad.min' => 'El documento debe tener al menos 6 dígitos',
            'documento_identidad.regex' => 'El documento solo puede contener números',
            'documento_identidad.unique' => 'Este documento ya está registrado',
            'tipo_documento.required' => 'Debe seleccionar el tipo de documento',
            'email.email' => 'El email debe ser una dirección válida',
            'email.unique' => 'Este email ya está registrado',
            'telefono.regex' => 'El teléfono debe tener el formato 0424-1234567',
            'fecha_nacimiento.before' => 'El empleado debe ser mayor de 18 años',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria',
            'fecha_ingreso.before_or_equal' => 'La fecha de ingreso no puede ser futura',
            'cargo.required' => 'El cargo es obligatorio',
            'cargo.min' => 'El cargo debe tener al menos 3 caracteres',
            'departamento.required' => 'El departamento es obligatorio',
            'departamento.in' => 'Debe seleccionar un departamento válido (Administracion o Produccion)',
            'estado.required' => 'Debe seleccionar el estado del empleado',
        ]);

        try {
            DB::beginTransaction();

            // Actualizar persona (sin telefono/direccion)
            $persona->update([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'documento_identidad' => $request->documento_identidad,
                'tipo_documento' => $request->tipo_documento,
                'email' => $request->email,
                'estado_persona' => $request->estado_persona,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'genero' => $request->genero,
            ]);

            // Actualizar o crear teléfono principal
            if (!empty($request->telefono)) {
                $telefonoPrincipal = $persona->telefonos()->where('es_principal', true)->first();
                if ($telefonoPrincipal) {
                    $telefonoPrincipal->update(['numero' => $request->telefono]);
                } else {
                    Telefono::create([
                        'persona_id' => $persona->id,
                        'numero' => $request->telefono,
                        'tipo' => 'movil',
                        'es_principal' => true,
                    ]);
                }
            }

            // Actualizar o crear dirección principal
            if (!empty($request->direccion) || !empty($request->ciudad)) {
                $direccionPrincipal = $persona->direcciones()->where('es_principal', true)->first();
                if ($direccionPrincipal) {
                    $direccionPrincipal->update([
                        'direccion' => $request->direccion ?? '',
                        'ciudad' => $request->ciudad,
                    ]);
                } else {
                    Direccion::create([
                        'persona_id' => $persona->id,
                        'direccion' => $request->direccion ?? '',
                        'ciudad' => $request->ciudad,
                        'tipo' => 'casa',
                        'es_principal' => true,
                    ]);
                }
            }

            // Actualizar empleado
            $empleado->update([
                'codigo_empleado' => $request->codigo_empleado,
                'fecha_ingreso' => $request->fecha_ingreso,
                'cargo' => $request->cargo,
                'departamento' => $request->departamento,
                'estado' => $request->estado,
            ]);

            DB::commit();

            return response()->json(['message' => 'Empleado actualizado exitosamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar empleado: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $empleado = Empleado::findOrFail($id);
        $empleado->delete();
        return response()->json(['message' => 'Empleado eliminado exitosamente.']);
    }

    public function reportePdf()
    {
        // Obtener todos los empleados con sus datos de persona
        $empleados = Empleado::with('persona')->orderBy('codigo_empleado', 'asc')->get();

        // Cargar la vista y generar el PDF (A4 horizontal para más columnas)
        $pdf = \PDF::loadView('admin.empleados.reporte_pdf', compact('empleados'))
            ->setPaper('a4', 'landscape');

        // Descargar el archivo con una marca de tiempo
        return $pdf->download('reporte_empleados_' . now()->format('Ymd_His') . '.pdf');
    }
}
