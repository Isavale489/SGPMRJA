<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\Persona;
use App\Models\Telefono;
use App\Models\Direccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ProveedorController extends Controller
{
    public function index()
    {
        return view('admin.proveedores.index');
    }

    public function getProveedores()
    {
        $proveedores = Proveedor::with('persona.telefonos', 'persona.direcciones')->get();

        $data = $proveedores->map(function ($proveedor) {
            return [
                'id' => $proveedor->id,
                'tipo_proveedor' => $proveedor->tipo_proveedor ?? 'juridico',
                'tipo_display' => ($proveedor->tipo_proveedor ?? 'juridico') === 'natural' ? 'Natural' : 'Jurídico',
                'nombre_display' => $proveedor->nombre_completo,
                'documento_display' => $proveedor->documento,
                'telefono_display' => $proveedor->telefono_unificado,
                'email_display' => $proveedor->email_unificado,
                'estado' => $proveedor->estado,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $tipoProveedor = $request->input('tipo_proveedor', 'juridico');

        if ($tipoProveedor === 'natural') {
            // Validación para proveedor natural (persona)
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

            DB::beginTransaction();
            try {
                // Crear persona
                $persona = Persona::create([
                    'nombre' => $request->nombre,
                    'apellido' => $request->apellido,
                    'tipo_documento' => $request->tipo_documento,
                    'documento_identidad' => $request->documento_identidad,
                    'email' => $request->email,
                ]);

                // Crear teléfono principal
                Telefono::create([
                    'persona_id' => $persona->id,
                    'numero' => $request->telefono,
                    'tipo' => 'movil',
                    'es_principal' => true,
                ]);

                // Crear dirección si se proporcionó
                if ($request->filled('direccion')) {
                    Direccion::create([
                        'persona_id' => $persona->id,
                        'direccion' => $request->direccion,
                        'ciudad' => $request->ciudad,
                        'estado' => $request->estado_territorial,
                        'tipo' => 'trabajo',
                        'es_principal' => true,
                    ]);
                }

                // Crear proveedor natural
                Proveedor::create([
                    'tipo_proveedor' => 'natural',
                    'persona_id' => $persona->id,
                    'estado' => $request->input('estado', true),
                ]);

                DB::commit();
                return response()->json(['success' => 'Proveedor natural creado exitosamente.']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Error al crear el proveedor: ' . $e->getMessage()], 500);
            }
        } else {
            // Validación para proveedor jurídico (empresa)
            $request->validate([
                'tipo_proveedor' => 'required|in:natural,juridico',
                'razon_social' => 'required|string|max:100',
                'rif' => 'required|string|max:15|unique:proveedor',
                'direccion' => 'required|string|max:200',
                'telefono' => 'required|string|max:20',
                'email' => 'required|email|max:100',
                'contacto' => 'nullable|string|max:100',
                'telefono_contacto' => 'nullable|string|max:20',
                'estado' => 'nullable|boolean',
            ]);

            Proveedor::create([
                'tipo_proveedor' => 'juridico',
                'razon_social' => $request->razon_social,
                'rif' => $request->rif,
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'contacto' => $request->contacto,
                'telefono_contacto' => $request->telefono_contacto,
                'estado' => $request->input('estado', true),
            ]);

            return response()->json(['success' => 'Proveedor jurídico creado exitosamente.']);
        }
    }

    public function show($id)
    {
        $proveedor = Proveedor::with('persona.telefonos', 'persona.direcciones')->findOrFail($id);

        if ($proveedor->esNatural() && $proveedor->persona) {
            $persona = $proveedor->persona;
            $telefonoPrincipal = $persona->telefonos->where('es_principal', true)->first();
            $direccionPrincipal = $persona->direcciones->where('es_principal', true)->first();

            return response()->json([
                'id' => $proveedor->id,
                'tipo_proveedor' => $proveedor->tipo_proveedor,
                'persona_id' => $proveedor->persona_id,
                // Datos de persona
                'nombre' => $persona->nombre,
                'apellido' => $persona->apellido,
                'tipo_documento' => $persona->tipo_documento,
                'documento_identidad' => $persona->documento_identidad,
                'email' => $persona->email,
                'telefono' => $telefonoPrincipal ? $telefonoPrincipal->numero : null,
                'direccion' => $direccionPrincipal ? $direccionPrincipal->direccion : null,
                'ciudad' => $direccionPrincipal ? $direccionPrincipal->ciudad : null,
                'estado_territorial' => $direccionPrincipal ? $direccionPrincipal->estado : null,
                // Unificados para display
                'nombre_display' => $proveedor->nombre_completo,
                'documento_display' => $proveedor->documento,
                'estado' => $proveedor->estado,
                'created_at' => $proveedor->created_at->format('d/m/Y H:i:s'),
                'updated_at' => $proveedor->updated_at->format('d/m/Y H:i:s'),
            ]);
        } else {
            return response()->json([
                'id' => $proveedor->id,
                'tipo_proveedor' => $proveedor->tipo_proveedor ?? 'juridico',
                'razon_social' => $proveedor->razon_social,
                'rif' => $proveedor->rif,
                'direccion' => $proveedor->direccion,
                'telefono' => $proveedor->telefono,
                'email' => $proveedor->email,
                'contacto' => $proveedor->contacto,
                'telefono_contacto' => $proveedor->telefono_contacto,
                // Unificados para display
                'nombre_display' => $proveedor->nombre_completo,
                'documento_display' => $proveedor->documento,
                'estado' => $proveedor->estado,
                'created_at' => $proveedor->created_at->format('d/m/Y H:i:s'),
                'updated_at' => $proveedor->updated_at->format('d/m/Y H:i:s'),
            ]);
        }
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
            ]);

            DB::beginTransaction();
            try {
                if ($proveedor->persona_id && $proveedor->persona) {
                    // Actualizar persona existente
                    $proveedor->persona->update([
                        'nombre' => $request->nombre,
                        'apellido' => $request->apellido,
                        'tipo_documento' => $request->tipo_documento,
                        'documento_identidad' => $request->documento_identidad,
                        'email' => $request->email,
                    ]);

                    // Actualizar teléfono principal
                    $telefonoPrincipal = $proveedor->persona->telefonos()->where('es_principal', true)->first();
                    if ($telefonoPrincipal) {
                        $telefonoPrincipal->update(['numero' => $request->telefono]);
                    } else {
                        Telefono::create([
                            'persona_id' => $proveedor->persona_id,
                            'numero' => $request->telefono,
                            'tipo' => 'movil',
                            'es_principal' => true,
                        ]);
                    }

                    // Actualizar dirección principal
                    $direccionPrincipal = $proveedor->persona->direcciones()->where('es_principal', true)->first();
                    if ($request->filled('direccion')) {
                        if ($direccionPrincipal) {
                            $direccionPrincipal->update([
                                'direccion' => $request->direccion,
                                'ciudad' => $request->ciudad,
                                'estado' => $request->estado_territorial,
                            ]);
                        } else {
                            Direccion::create([
                                'persona_id' => $proveedor->persona_id,
                                'direccion' => $request->direccion,
                                'ciudad' => $request->ciudad,
                                'estado' => $request->estado_territorial,
                                'tipo' => 'trabajo',
                                'es_principal' => true,
                            ]);
                        }
                    }
                } else {
                    // Convertir de jurídico a natural: crear persona
                    $persona = Persona::create([
                        'nombre' => $request->nombre,
                        'apellido' => $request->apellido,
                        'tipo_documento' => $request->tipo_documento,
                        'documento_identidad' => $request->documento_identidad,
                        'email' => $request->email,
                    ]);

                    Telefono::create([
                        'persona_id' => $persona->id,
                        'numero' => $request->telefono,
                        'tipo' => 'movil',
                        'es_principal' => true,
                    ]);

                    if ($request->filled('direccion')) {
                        Direccion::create([
                            'persona_id' => $persona->id,
                            'direccion' => $request->direccion,
                            'ciudad' => $request->ciudad,
                            'estado' => $request->estado_territorial,
                            'tipo' => 'trabajo',
                            'es_principal' => true,
                        ]);
                    }

                    $proveedor->persona_id = $persona->id;
                }

                $proveedor->tipo_proveedor = 'natural';
                $proveedor->estado = $request->input('estado', true);
                $proveedor->save();

                DB::commit();
                return response()->json(['success' => 'Proveedor actualizado exitosamente.']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Error al actualizar: ' . $e->getMessage()], 500);
            }
        } else {
            $request->validate([
                'razon_social' => 'required|string|max:100',
                'rif' => 'required|string|max:15|unique:proveedor,rif,' . $id,
                'direccion' => 'required|string|max:200',
                'telefono' => 'required|string|max:20',
                'email' => 'required|email|max:100',
                'contacto' => 'nullable|string|max:100',
                'telefono_contacto' => 'nullable|string|max:20',
                'estado' => 'nullable|boolean',
            ]);

            $proveedor->update([
                'tipo_proveedor' => 'juridico',
                'razon_social' => $request->razon_social,
                'rif' => $request->rif,
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'contacto' => $request->contacto,
                'telefono_contacto' => $request->telefono_contacto,
                'estado' => $request->input('estado', true),
            ]);

            return response()->json(['success' => 'Proveedor actualizado exitosamente.']);
        }
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->delete();
        return response()->json(['success' => 'Proveedor eliminado exitosamente.']);
    }

    public function reportePdf()
    {
        $proveedores = Proveedor::with('persona')->get();
        $pdf = \PDF::loadView('admin.proveedores.reporte_pdf', compact('proveedores'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('proveedores_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }

    public function checkRif(Request $request)
    {
        $rif = $request->input('rif');
        if (!$rif)
            return response()->json(['exists' => false]);
        $exists = Proveedor::where('rif', $rif)->exists();
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
        if (!$email)
            return response()->json(['exists' => false]);
        // Verificar en proveedores (juridicos) y personas (naturales)
        $existsProveedor = Proveedor::where('email', $email)->exists();
        $existsPersona = Persona::where('email', $email)->exists();

        return response()->json(['exists' => $existsProveedor || $existsPersona]);
    }
}
