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
                'telefono' => $cliente->telefono, // Usa accessor que obtiene telefono_principal
                'documento' => $cliente->documento,
                'direccion' => $cliente->direccion, // Usa accessor que obtiene direccion_principal
                'ciudad' => $cliente->ciudad, // Usa accessor que obtiene ciudad de direccion_principal
                'estado' => $cliente->estado,
                'created_at' => $cliente->created_at ? $cliente->created_at->format('d/m/Y H:i') : null,
            ];
        });

        return DataTables::of($clientesFormateados)->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'tipo_cliente' => 'required|in:natural,juridico',
            'email' => 'nullable|string|email|max:255|unique:persona,email',
            'telefono' => 'required|string|max:30',
            'documento' => 'required|string|max:50',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:100',
            'estado' => 'required|in:0,1',
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
            if (!empty($request->direccion) || !empty($request->ciudad)) {
                Direccion::create([
                    'persona_id' => $persona->id,
                    'direccion' => $request->direccion ?? '',
                    'ciudad' => $request->ciudad,
                    'tipo' => 'casa',
                    'es_principal' => true,
                ]);
            }

            // Crear el cliente asociado a la persona
            $cliente = Cliente::create([
                'persona_id' => $persona->id,
                'tipo_cliente' => $request->tipo_cliente,
                'estado' => $request->estado,
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
        $cliente = Cliente::with(['persona.telefonos', 'persona.direcciones'])->find($id);

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
            'ciudad' => $direccionPrincipal ? $direccionPrincipal->ciudad : '',
            'estado' => $cliente->estado,
            // Datos adicionales para UI de múltiples teléfonos/direcciones
            'telefonos' => $cliente->persona ? $cliente->persona->telefonos : [],
            'direcciones' => $cliente->persona ? $cliente->persona->direcciones : [],
        ]);
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::with(['persona.telefonos', 'persona.direcciones'])->findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'tipo_cliente' => 'required|in:natural,juridico',
            'email' => 'nullable|string|email|max:255|unique:persona,email,' . ($cliente->persona_id ?? 'NULL'),
            'telefono' => 'required|string|max:30',
            'documento' => 'required|string|max:50',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:100',
            'estado' => 'required|in:0,1',
        ]);

        DB::transaction(function () use ($request, $cliente) {
            // Extraer prefijo y número del documento
            $documento = $request->documento;
            $tipoDocumento = 'V-';
            $numeroDocumento = $documento;

            if (preg_match('/^(V-|J-|E-|G-)(.+)$/', $documento, $matches)) {
                $tipoDocumento = $matches[1];
                $numeroDocumento = $matches[2];
            }

            // Actualizar la persona asociada (sin telefono/direccion)
            if ($cliente->persona) {
                $cliente->persona->update([
                    'nombre' => $request->nombre,
                    'apellido' => $request->apellido ?? '',
                    'documento_identidad' => $numeroDocumento,
                    'tipo_documento' => $tipoDocumento,
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
                if (!empty($request->direccion) || !empty($request->ciudad)) {
                    $direccionPrincipal = $cliente->persona->direcciones()->where('es_principal', true)->first();
                    if ($direccionPrincipal) {
                        $direccionPrincipal->update([
                            'direccion' => $request->direccion ?? '',
                            'ciudad' => $request->ciudad,
                        ]);
                    } else {
                        Direccion::create([
                            'persona_id' => $cliente->persona->id,
                            'direccion' => $request->direccion ?? '',
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
                'estado' => $request->estado,
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
            'telefono' => $cliente->telefono, // Usa accessor que obtiene telefono_principal
            'documento' => $cliente->documento,
            'direccion' => $cliente->direccion, // Usa accessor que obtiene direccion_principal
            'ciudad' => $cliente->ciudad, // Usa accessor que obtiene ciudad de direccion_principal
            'estado' => $cliente->estado,
            'created_at' => $cliente->created_at ? $cliente->created_at->format('d/m/Y H:i:s') : null,
            'updated_at' => $cliente->updated_at ? $cliente->updated_at->format('d/m/Y H:i:s') : null
        ]);
    }

    /**
     * Buscar clientes por nombre o apellido (para autocompletado AJAX)
     */
    public function searchAjax(Request $request)
    {
        $query = $request->input('q');
        $clientes = Cliente::with(['persona.telefonos', 'persona.direcciones'])
            ->whereHas('persona', function ($q) use ($query) {
                $q->where('nombre', 'LIKE', "%{$query}%")
                    ->orWhere('apellido', 'LIKE', "%{$query}%");
            })
            ->where('estado', 1)
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
     * Exportar reporte de clientes en PDF
     */
    public function exportarPDF()
    {
        $clientes = Cliente::with('persona')->get();
        $pdf = Pdf::loadView('admin.clientes.reporte_pdf', compact('clientes'))->setPaper('a4', 'portrait');
        return $pdf->download('reporte_clientes_' . now()->format('Ymd_His') . '.pdf');
    }
}
