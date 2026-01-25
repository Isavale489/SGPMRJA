<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Persona;
use App\Models\Telefono;
use App\Models\Direccion;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;


class ClienteController extends Controller
{
    public function index()
    {
        return view('admin.clientes.index');
    }

    public function getClientes()
    {
        // Cargar persona con telefonos y direcciones normalizadas
        $clientes = Cliente::with(['persona.telefonos', 'persona.direcciones'])->get();

        // Formatear para el DataTable usando los accessors del modelo Cliente
        $clientesFormateados = $clientes->map(function ($cliente) {
            return [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre ?? 'N/A',
                'apellido' => $cliente->apellido ?? '',
                'tipo_cliente' => $cliente->tipo_cliente,
                'email' => $cliente->email,
                'telefono' => $cliente->telefono,
                'documento' => $cliente->documento,
                'direccion' => $cliente->direccion,
                'estado_territorial' => $cliente->estado_territorial, // Estado/territorio geográfico
                'ciudad' => $cliente->ciudad,
                'estatus' => $cliente->estatus, // Activo/Inactivo
                'created_at' => $cliente->created_at ? $cliente->created_at->format('d/m/Y H:i') : null,
            ];
        });

        return DataTables::of($clientesFormateados)->make(true);
    }

    public function store(Request $request)
    {
        // Extraer prefijo y número del documento para validar unicidad
        $documento = $request->documento;
        $tipoDocumento = 'V-';
        $numeroDocumento = $documento;

        if (preg_match('/^(V-|J-|E-|G-)(.+)$/', $documento, $matches)) {
            $tipoDocumento = $matches[1];
            $numeroDocumento = $matches[2];
        }

        $request->validate([
            'nombre' => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apellido' => 'nullable|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/',
            'tipo_cliente' => 'required|in:natural,juridico',
            'email' => 'nullable|string|email:rfc,dns|max:255|unique:persona,email',
            'telefono' => 'required|string|regex:/^[0-9]{4}-[0-9]{7}$/',
            'documento' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) use ($numeroDocumento) {
                    // Validar que el número de documento solo contenga números
                    if (!preg_match('/^[0-9]+$/', $numeroDocumento)) {
                        $fail('El número de documento solo puede contener números.');
                    }
                    // Validar longitud mínima
                    if (strlen($numeroDocumento) < 6) {
                        $fail('El documento debe tener al menos 6 dígitos.');
                    }
                    // Verificar unicidad en la tabla persona
                    $exists = \App\Models\Persona::where('documento_identidad', $numeroDocumento)->exists();
                    if ($exists) {
                        $fail('Este documento ya está registrado en el sistema.');
                    }
                },
            ],
            'direccion' => 'nullable|string|max:500',
            'estado_territorial' => 'nullable|string|max:50',
            'ciudad' => 'nullable|string|max:100',
            'estatus' => 'required|in:0,1',
        ], [
            // Mensajes personalizados
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede exceder los 100 caracteres.',
            'nombre.regex' => 'El nombre solo puede contener letras y espacios.',
            'apellido.max' => 'El apellido no puede exceder los 100 caracteres.',
            'apellido.regex' => 'El apellido solo puede contener letras y espacios.',
            'tipo_cliente.required' => 'Debe seleccionar el tipo de cliente.',
            'tipo_cliente.in' => 'El tipo de cliente debe ser Natural o Jurídico.',
            'email.email' => 'El email debe ser una dirección de correo válida.',
            'email.unique' => 'Este email ya está registrado en el sistema.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.regex' => 'El teléfono debe tener el formato 0424-1234567.',
            'documento.required' => 'El documento de identidad es obligatorio.',
            'direccion.max' => 'La dirección no puede exceder los 500 caracteres.',
            'estado_territorial.max' => 'El estado territorial no puede exceder los 50 caracteres.',
            'ciudad.max' => 'La ciudad no puede exceder los 100 caracteres.',
            'estatus.required' => 'Debe seleccionar el estatus del cliente.',
            'estatus.in' => 'El estatus debe ser Activo o Inactivo.',
        ]);

        $clienteId = null;

        DB::transaction(function () use ($request, &$clienteId) {
            // Extraer prefijo y número del documento
            $documento = $request->documento;
            $tipoDocumento = 'V-';
            $numeroDocumento = $documento;

            if (preg_match('/^(V-|J-|E-|G-)(.+)$/', $documento, $matches)) {
                $tipoDocumento = $matches[1];
                $numeroDocumento = $matches[2];
            }

            // Crear la persona (sin telefono/direccion en la tabla persona)
            $persona = Persona::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido ?? '',
                'documento_identidad' => $numeroDocumento,
                'tipo_documento' => $tipoDocumento,
                'email' => $request->email,
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
            if (!empty($request->direccion) || !empty($request->ciudad) || !empty($request->estado_territorial)) {
                Direccion::create([
                    'persona_id' => $persona->id,
                    'direccion' => $request->direccion ?? '',
                    'estado' => $request->estado_territorial,
                    'ciudad' => $request->ciudad,
                    'tipo' => 'casa',
                    'es_principal' => true,
                ]);
            }

            // Crear el cliente asociado a la persona
            $cliente = Cliente::create([
                'persona_id' => $persona->id,
                'tipo_cliente' => $request->tipo_cliente,
                'estatus' => $request->estatus,
            ]);

            $clienteId = $cliente->id;
        });

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
            // Datos adicionales para UI de múltiples teléfonos/direcciones
            'telefonos' => $cliente->persona ? $cliente->persona->telefonos : [],
            'direcciones' => $cliente->persona ? $cliente->persona->direcciones : [],
        ]);
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::with(['persona.telefonos', 'persona.direcciones'])->findOrFail($id);

        // Ya no actualizamos documento, así que no es necesario extraerlo ni validarlo para update

        $request->validate([
            'nombre' => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apellido' => 'nullable|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/',
            'tipo_cliente' => 'required|in:natural,juridico',
            'email' => 'nullable|string|email:rfc,dns|max:255|unique:persona,email,' . ($cliente->persona_id ?? 'NULL'),
            'telefono' => 'required|string|regex:/^[0-9]{4}-[0-9]{7}$/',
            // Eliminada validación de documento para update
            'direccion' => 'nullable|string|max:500',
            'estado_territorial' => 'nullable|string|max:50',
            'ciudad' => 'nullable|string|max:100',
            'estatus' => 'required|in:0,1',
        ], [
            // Mensajes personalizados
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede exceder los 100 caracteres.',
            'nombre.regex' => 'El nombre solo puede contener letras y espacios.',
            'apellido.max' => 'El apellido no puede exceder los 100 caracteres.',
            'apellido.regex' => 'El apellido solo puede contener letras y espacios.',
            'tipo_cliente.required' => 'Debe seleccionar el tipo de cliente.',
            'tipo_cliente.in' => 'El tipo de cliente debe ser Natural o Jurídico.',
            'email.email' => 'El email debe ser una dirección de correo válida.',
            'email.unique' => 'Este email ya está registrado en el sistema.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.regex' => 'El teléfono debe tener el formato 0424-1234567.',
            // Eliminados mensajes de documento
            'direccion.max' => 'La dirección no puede exceder los 500 caracteres.',
            'estado_territorial.max' => 'El estado territorial no puede exceder los 50 caracteres.',
            'ciudad.max' => 'La ciudad no puede exceder los 100 caracteres.',
            'estatus.required' => 'Debe seleccionar el estatus del cliente.',
            'estatus.in' => 'El estatus debe ser Activo o Inactivo.',
        ]);

        DB::transaction(function () use ($request, $cliente) {
            // Actualizar la persona asociada (sin telefono/direccion y SIN DOCUMENTO)
            if ($cliente->persona) {
                $cliente->persona->update([
                    'nombre' => $request->nombre,
                    'apellido' => $request->apellido ?? '',
                    // 'documento_identidad' => ... SE MANTIENE INTACTO
                    // 'tipo_documento' => ... SE MANTIENE INTACTO
                    'email' => $request->email,
                ]);

                // Actualizar o crear teléfono principal
                if (!empty($request->telefono)) {
                    $telefonoPrincipal = $cliente->persona->telefonos()->where('es_principal', true)->first();
                    if ($telefonoPrincipal) {
                        $telefonoPrincipal->update(['numero' => $request->telefono]);
                    } else {
                        Telefono::create([
                            'persona_id' => $cliente->persona->id,
                            'numero' => $request->telefono,
                            'tipo' => 'movil',
                            'es_principal' => true,
                        ]);
                    }
                }

                // Actualizar o crear dirección principal
                if (!empty($request->direccion) || !empty($request->ciudad) || !empty($request->estado_territorial)) {
                    $direccionPrincipal = $cliente->persona->direcciones()->where('es_principal', true)->first();
                    if ($direccionPrincipal) {
                        $direccionPrincipal->update([
                            'direccion' => $request->direccion ?? '',
                            'estado' => $request->estado_territorial,
                            'ciudad' => $request->ciudad,
                        ]);
                    } else {
                        Direccion::create([
                            'persona_id' => $cliente->persona->id,
                            'direccion' => $request->direccion ?? '',
                            'estado' => $request->estado_territorial,
                            'ciudad' => $request->ciudad,
                            'tipo' => 'casa',
                            'es_principal' => true,
                        ]);
                    }
                }
            }

            // Actualizar el cliente
            $cliente->update([
                'tipo_cliente' => $request->tipo_cliente,
                'estatus' => $request->estatus,
            ]);
        });

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

        $cliente->delete();

        if ($cotizacionesCount > 0) {
            return response()->json([
                'message' => 'Cliente eliminado exitosamente.',
                'warning' => 'Este cliente tenía ' . $cotizacionesCount . ' cotización(es). Los registros históricos se mantienen.'
            ]);
        }

        return response()->json(['message' => 'Cliente eliminado exitosamente.']);
    }

    public function show($id)
    {
        $cliente = Cliente::with(['persona.telefonos', 'persona.direcciones'])->findOrFail($id);
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
            'created_at' => $cliente->created_at ? $cliente->created_at->format('d/m/Y H:i:s') : null,
            'updated_at' => $cliente->updated_at ? $cliente->updated_at->format('d/m/Y H:i:s') : null
        ]);
    }

    /**
     * Buscar clientes por documento de identidad (para autocompletado AJAX)
     */
    public function searchAjax(Request $request)
    {
        $query = $request->input('q');
        $clientes = Cliente::with(['persona.telefonos', 'persona.direcciones'])
            ->whereHas('persona', function ($q) use ($query) {
                // Buscar por documento_identidad (solo el número, sin prefijo)
                $q->where('documento_identidad', 'LIKE', "%{$query}%");
            })
            ->where('estatus', 1)
            ->limit(10)
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

        // Buscar coincidencia exacta en la tabla 'persona'
        $exists = \App\Models\Persona::where('documento_identidad', $numero)->exists();

        return response()->json(['exists' => $exists]);
    }

    /**
     * Exportar reporte de clientes en PDF
     */
    public function exportarPDF()
    {
        $clientes = Cliente::with('persona')->get();
        $pdf = Pdf::loadView('admin.clientes.reporte_pdf', compact('clientes'))->setPaper('a4', 'portrait');
        return $pdf->download('reporte_clientes_' . now()->format('Ymd_His') . '.pdf');
    }

    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        if (!$email)
            return response()->json(['exists' => false]);
        $exists = \App\Models\Persona::where('email', $email)->exists();
        return response()->json(['exists' => $exists]);
    }
}
