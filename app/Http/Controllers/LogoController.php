<?php

namespace App\Http\Controllers;

use App\Models\Logo;
use Illuminate\Http\Request;

class LogoController extends Controller
{
    /**
     * Retorna la lista completa de logos como JSON (para uso AJAX o futuras integraciones).
     */
    public function getLogos(Request $request)
    {
        $logos = Logo::orderBy('name')->get(['id', 'name', 'original_filename']);
        return response()->json($logos);
    }

    /**
     * Alta rápida de un logo desde el selector del configurador de bordados.
     * Solo se registra la referencia (nombre + archivo del ponchado); si no se
     * indica archivo, se asume "<nombre>.emb" como en el catálogo sembrado.
     * Los UNIQUE de la tabla incluyen filas soft-deleted, por eso la validación
     * unique NO excluye trashed (igual que la restricción física).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:120', 'unique:logo,name'],
            'original_filename' => ['nullable', 'string', 'max:150', 'unique:logo,original_filename'],
        ], [
            'name.required'            => 'Indica el nombre del logo.',
            'name.unique'              => 'Ya existe un logo con ese nombre.',
            'original_filename.unique' => 'Ya existe un logo con ese archivo.',
        ]);

        $data['name'] = trim($data['name']);
        $data['original_filename'] = trim($data['original_filename'] ?? '') ?: $data['name'] . '.emb';

        // El default "<nombre>.emb" también debe respetar el UNIQUE físico.
        if (Logo::withTrashed()->where('original_filename', $data['original_filename'])->exists()) {
            return response()->json([
                'message' => 'Ya existe un logo con ese archivo (' . $data['original_filename'] . ').',
                'errors'  => ['original_filename' => ['Ya existe un logo con ese archivo.']],
            ], 422);
        }

        $logo = Logo::create($data);

        return response()->json([
            'success' => true,
            'logo'    => $logo->only(['id', 'name', 'original_filename']),
        ], 201);
    }
}
