<?php

namespace App\Http\Controllers;

use App\Models\PermisoRol;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Panel de "Configuración de seguridad" (FEAT-005 / TASK-039).
 *
 * Dos pestañas: Roles (CRUD) y Permisos (matriz módulo × acción por rol).
 * La definición de módulos/acciones vive en config/modulos.php (registry,
 * TASK-037); aquí solo se gobierna el VALOR (qué rol tiene qué permiso) en la
 * tabla permiso_rol.
 *
 * Acceso SOLO Administrador (gate 'acceso-seguridad' en las rutas). El panel NO
 * forma parte del registry ni de la matriz: nadie puede otorgárselo a sí mismo.
 *
 * El Administrador (rol de sistema con acceso total por Gate::before) se muestra
 * como "acceso total" y NO es editable en la matriz. El Supervisor sí es editable
 * en permisos, pero no renombrable ni eliminable (es_sistema).
 */
class SeguridadController extends Controller
{
    public function index()
    {
        $roles = Rol::withCount('usuarios')
            ->orderByDesc('es_sistema')
            ->orderBy('nombre')
            ->get();

        return view('admin.seguridad.index', [
            'roles'   => $roles,
            'modulos' => $this->modulosMatriz(),
        ]);
    }

    /**
     * Crea un rol nuevo (no-sistema). Los permisos se asignan luego en la matriz.
     */
    public function storeRol(Request $request)
    {
        $data = $this->validarRol($request);

        $rol = Rol::create([
            'nombre'      => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'es_sistema'  => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rol creado correctamente.',
            'rol'     => $this->serializarRol($rol->loadCount('usuarios')),
        ]);
    }

    /**
     * Edita nombre/descripción de un rol. Los roles de sistema no se editan.
     */
    public function updateRol(Request $request, Rol $rol)
    {
        if ($rol->es_sistema) {
            return response()->json([
                'success' => false,
                'message' => 'Los roles de sistema no se pueden editar.',
            ], 403);
        }

        $data = $this->validarRol($request, $rol);

        $rol->update([
            'nombre'      => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
        ]);

        // El nombre del rol se cachea (esUsuarioAdministrador); invalidar por si acaso.
        Cache::forget("rol_nombre_{$rol->id}");

        return response()->json([
            'success' => true,
            'message' => 'Rol actualizado correctamente.',
            'rol'     => $this->serializarRol($rol->loadCount('usuarios')),
        ]);
    }

    /**
     * Elimina un rol. Bloqueado para roles de sistema y roles con usuarios.
     */
    public function destroyRol(Rol $rol)
    {
        if ($rol->es_sistema) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un rol de sistema.',
            ], 403);
        }

        if ($rol->usuarios()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'El rol tiene usuarios asignados. Reasígnalos a otro rol antes de eliminarlo.',
            ], 422);
        }

        $rolId = $rol->id;

        DB::transaction(function () use ($rol) {
            $rol->permisos()->delete();
            $rol->delete();
        });

        Cache::forget("permisos.rol_{$rolId}");
        Cache::forget("rol_nombre_{$rolId}");

        return response()->json([
            'success' => true,
            'message' => 'Rol eliminado correctamente.',
        ]);
    }

    /**
     * JSON con los permisos otorgados de un rol (para pintar la matriz).
     */
    public function getPermisos(Rol $rol)
    {
        if ($this->esAdministrador($rol)) {
            return response()->json([
                'success' => false,
                'message' => 'El Administrador tiene acceso total y no es editable.',
            ], 403);
        }

        return response()->json([
            'success'  => true,
            'permisos' => $rol->permisosArray(),
        ]);
    }

    /**
     * Guarda la matriz de permisos de un rol: reemplaza sus filas en permiso_rol
     * y purga la caché del rol para que el cambio aplique sin re-login.
     */
    public function guardarMatriz(Request $request, Rol $rol)
    {
        if ($this->esAdministrador($rol)) {
            return response()->json([
                'success' => false,
                'message' => 'El Administrador tiene acceso total y no es editable.',
            ], 403);
        }

        // Solo se aceptan claves que existan en el registry (descarta basura).
        $validos   = $this->permisosValidos();
        $solicitados = (array) $request->input('permisos', []);
        $permisos  = array_values(array_intersect($validos, $solicitados));

        // Invariante: 'ver' es prerrequisito. Si un módulo tiene cualquier acción
        // otorgada, se fuerza también su 'ver' (no se puede actuar sin ver).
        $permisos = $this->forzarVerPrerrequisito($permisos);

        DB::transaction(function () use ($rol, $permisos) {
            $rol->permisos()->delete();

            if (!empty($permisos)) {
                $now  = now();
                $filas = array_map(fn ($p) => [
                    'rol_id'     => $rol->id,
                    'permiso'    => $p,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $permisos);

                PermisoRol::insert($filas);
            }
        });

        Cache::forget("permisos.rol_{$rol->id}");

        return response()->json([
            'success'  => true,
            'message'  => 'Permisos actualizados correctamente.',
            'permisos' => $permisos,
        ]);
    }

    // ===================================================================
    // Helpers
    // ===================================================================

    /**
     * Módulos del registry para la matriz (excluye 'comunes' y entradas sin acciones).
     * Cada entrada: ['slug', 'nombre', 'acciones' => ['accion' => 'descripcion']].
     */
    private function modulosMatriz(): array
    {
        $modulos = [];

        foreach (config('modulos', []) as $slug => $config) {
            if ($slug === 'comunes' || empty($config['acciones'])) {
                continue;
            }

            $modulos[] = [
                'slug'     => $slug,
                'nombre'   => $config['nombre'] ?? $slug,
                'acciones' => $config['acciones'],
            ];
        }

        return $modulos;
    }

    /**
     * Todas las claves 'modulo.accion' válidas según el registry.
     */
    private function permisosValidos(): array
    {
        $claves = [];

        foreach (config('modulos', []) as $slug => $config) {
            if ($slug === 'comunes' || empty($config['acciones'])) {
                continue;
            }

            foreach (array_keys($config['acciones']) as $accion) {
                $claves[] = "{$slug}.{$accion}";
            }
        }

        return $claves;
    }

    /**
     * Asegura que si un módulo tiene ≥1 acción otorgada, su 'ver' esté incluido.
     */
    private function forzarVerPrerrequisito(array $permisos): array
    {
        $modulosConAlgo = [];
        foreach ($permisos as $permiso) {
            $modulosConAlgo[explode('.', $permiso, 2)[0]] = true;
        }

        $validos = $this->permisosValidos();
        foreach (array_keys($modulosConAlgo) as $modulo) {
            $ver = "{$modulo}.ver";
            if (in_array($ver, $validos, true) && !in_array($ver, $permisos, true)) {
                $permisos[] = $ver;
            }
        }

        return array_values(array_unique($permisos));
    }

    private function esAdministrador(Rol $rol): bool
    {
        return $rol->es_sistema && $rol->nombre === 'Administrador';
    }

    private function validarRol(Request $request, ?Rol $rol = null): array
    {
        return $request->validate([
            'nombre' => [
                'required', 'string', 'max:60',
                Rule::unique('rol', 'nombre')
                    ->ignore($rol?->id)
                    ->whereNull('deleted_at'),
            ],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ], [], [
            'nombre'      => 'nombre del rol',
            'descripcion' => 'descripción',
        ]);
    }

    private function serializarRol(Rol $rol): array
    {
        return [
            'id'             => $rol->id,
            'nombre'         => $rol->nombre,
            'descripcion'    => $rol->descripcion,
            'es_sistema'     => (bool) $rol->es_sistema,
            'usuarios_count' => $rol->usuarios_count ?? 0,
        ];
    }
}
