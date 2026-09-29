<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Models\PermisoRol;
use App\Models\Rol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

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
    use RespondeSegunCliente;

    public function index(): Response
    {
        $roles = Rol::with('permisos:rol_id,permiso')
            ->withCount(['usuarios', 'permisos'])
            ->orderByDesc('es_sistema')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Seguridad/Index', [
            'roles' => $roles->map(fn (Rol $r) => $this->serializarRol($r))->all(),
            // Permisos otorgados por rol (el Administrador no: tiene acceso total).
            'permisos' => $roles->reject(fn (Rol $r) => $this->esAdministrador($r))
                ->mapWithKeys(fn (Rol $r) => [$r->id => $r->permisos->pluck('permiso')->values()->all()])->all(),
            'secciones' => collect($this->modulosMatriz())->map(fn ($sec, $nombre) => [
                'nombre' => $nombre,
                'tema' => $sec['tema'],
                'icono' => $sec['icono'],
                'modulos' => array_map(fn ($m) => [
                    ...$m,
                    'acciones' => collect($m['acciones'])->map(fn ($descripcion, $accion) => ['accion' => $accion, 'descripcion' => $descripcion])->values()->all(),
                ], $sec['modulos']),
            ])->values()->all(),
            'urls' => [
                'roles' => url('/configuracion/seguridad/roles'),
                'permisos' => url('/configuracion/seguridad/permisos'),
                'configuracion' => route('configuracion.index', absolute: false),
            ],
        ]);
    }

    /**
     * Crea un rol nuevo (no-sistema). Los permisos se asignan luego en la matriz.
     */
    public function storeRol(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->validarRol($request);

        $rol = Rol::create([
            'nombre'      => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'es_sistema'  => false,
        ]);

        return $this->responder($request, 'Rol creado correctamente.', [
            'success' => true,
            'message' => 'Rol creado correctamente.',
            'rol'     => $this->serializarRol($rol->loadCount(['usuarios', 'permisos'])),
        ], ['rol' => $rol->id]);
    }

    /**
     * Edita nombre/descripción de un rol. Los roles de sistema conservan su
     * nombre (identidad protegida) pero SÍ admiten descripción — es texto
     * informativo, no de autorización.
     */
    public function updateRol(Request $request, Rol $rol): JsonResponse|RedirectResponse
    {
        if ($rol->es_sistema) {
            $data = $request->validate(
                ['descripcion' => ['nullable', 'string', 'max:255']],
                [],
                ['descripcion' => 'descripción'],
            );

            $rol->update(['descripcion' => $data['descripcion'] ?? null]);

            return $this->responder($request, 'Descripción actualizada correctamente.', [
                'success' => true,
                'message' => 'Descripción actualizada correctamente.',
                'rol'     => $this->serializarRol($rol->loadCount(['usuarios', 'permisos'])),
            ]);
        }

        $data = $this->validarRol($request, $rol);

        $rol->update([
            'nombre'      => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
        ]);

        // El nombre del rol se cachea (esUsuarioAdministrador); invalidar por si acaso.
        Cache::forget("rol_nombre_{$rol->id}");

        return $this->responder($request, 'Rol actualizado correctamente.', [
            'success' => true,
            'message' => 'Rol actualizado correctamente.',
            'rol'     => $this->serializarRol($rol->loadCount(['usuarios', 'permisos'])),
        ]);
    }

    /**
     * Elimina un rol. Bloqueado para roles de sistema y roles con usuarios.
     */
    public function destroyRol(Request $request, Rol $rol): JsonResponse|RedirectResponse
    {
        if ($rol->es_sistema) {
            return $this->rechazar($request, 'No se puede eliminar un rol de sistema.', 403);
        }

        if ($rol->usuarios()->count() > 0) {
            return $this->rechazar($request, 'El rol tiene usuarios asignados. Reasígnalos a otro rol antes de eliminarlo.');
        }

        $rolId = $rol->id;

        DB::transaction(function () use ($rol) {
            $rol->permisos()->delete();
            $rol->delete();
        });

        Cache::forget("permisos.rol_{$rolId}");
        Cache::forget("rol_nombre_{$rolId}");

        return $this->responder($request, 'Rol eliminado correctamente.');
    }

    /**
     * Guarda la matriz de permisos de un rol: reemplaza sus filas en permiso_rol
     * y purga la caché del rol para que el cambio aplique sin re-login.
     */
    public function guardarMatriz(Request $request, Rol $rol): JsonResponse|RedirectResponse
    {
        if ($this->esAdministrador($rol)) {
            return $this->rechazar($request, 'El Administrador tiene acceso total y no es editable.', 403);
        }

        // Lista plana de claves (un arreglo anidado daba 500 en array_intersect).
        $request->validate(['permisos' => 'nullable|array', 'permisos.*' => 'string']);

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

        return $this->responder($request, 'Permisos actualizados correctamente.', [
            'success'  => true,
            'message'  => 'Permisos actualizados correctamente.',
            'permisos' => $permisos,
        ]);
    }

    // ===================================================================
    // Helpers
    // ===================================================================

    /**
     * Metadatos de PRESENTACIÓN de la matriz (no de autorización): sección,
     * ícono y color por módulo, espejo de la organización del sidebar. Un módulo
     * nuevo del registry sin entrada aquí cae en "Otros módulos" con ícono
     * genérico. 'tema' = clase de color de la sección (CSS .seg-sec-*): los
     * mismos de identidad del sidebar/cards (maestros navy, operativa emerald,
     * reportes sky); 'admin' (azul vivo #3b82f6) es propio de este panel:
     * Administración no existe como sección del sidebar.
     */
    private const SECCIONES_MATRIZ = [
        'Gestión General'      => ['tema' => 'maestros',  'icono' => 'Database',  'modulos' => ['clientes', 'empleados', 'departamentos', 'cargos', 'proveedores', 'productos', 'tipo-productos', 'atributos', 'colores', 'tallas', 'logos', 'insumos', 'tipo-insumos']],
        'Gestión Operativa'    => ['tema' => 'operativa', 'icono' => 'ArrowLeftRight',   'modulos' => ['cotizaciones', 'pedidos', 'ordenes', 'calidad', 'compras', 'movimiento-insumo']],
        'Consultas y Reportes' => ['tema' => 'reportes',  'icono' => 'ChartColumn',   'modulos' => ['reportes']],
        'Administración'       => ['tema' => 'admin',     'icono' => 'ShieldCheck', 'modulos' => ['configuracion', 'users']],
    ];

    private const ICONOS_MATRIZ = [
        'configuracion'     => 'Settings',
        'users'             => 'ShieldUser',
        'clientes'          => 'UserRound',
        'empleados'         => 'UserCog',
        'departamentos'     => 'Building',
        'cargos'            => 'Briefcase',
        'pedidos'           => 'ShoppingCart',
        'cotizaciones'      => 'FileText',
        'proveedores'       => 'Truck',
        'productos'         => 'Shirt',
        'tipo-productos'    => 'Shapes',
        'atributos'         => 'SlidersHorizontal',
        'colores'           => 'Palette',
        'tallas'            => 'Ruler',
        'logos'             => 'Image',
        'insumos'           => 'Archive',
        'tipo-insumos'      => 'Layers',
        'ordenes'           => 'CalendarCheck',
        'calidad'           => 'ShieldCheck',
        'compras'           => 'ShoppingBag',
        'movimiento-insumo' => 'Boxes',
        'reportes'          => 'ChartColumn',
    ];

    /**
     * Módulos del registry para la matriz (excluye 'comunes' y entradas sin
     * acciones), agrupados por sección. Íconos: nombres de lucide (ICONOS en
     * resources/js/components/app/icono.tsx).
     */
    private function modulosMatriz(): array
    {
        $disponibles = [];
        foreach (config('modulos', []) as $slug => $config) {
            if ($slug === 'comunes' || empty($config['acciones'])) {
                continue;
            }
            $disponibles[$slug] = [
                'slug'     => $slug,
                'nombre'   => $config['nombre'] ?? $slug,
                'icono'    => self::ICONOS_MATRIZ[$slug] ?? 'LayoutGrid',
                'acciones' => $config['acciones'],
            ];
        }

        $secciones = [];
        foreach (self::SECCIONES_MATRIZ as $nombre => $def) {
            $modulos = [];
            foreach ($def['modulos'] as $slug) {
                if (isset($disponibles[$slug])) {
                    $modulos[] = $disponibles[$slug];
                    unset($disponibles[$slug]);
                }
            }
            if ($modulos) {
                $secciones[$nombre] = ['tema' => $def['tema'], 'icono' => $def['icono'], 'modulos' => $modulos];
            }
        }

        // Módulos del registry sin sección asignada: visibles igual (no se pierden).
        if ($disponibles) {
            $secciones['Otros módulos'] = ['tema' => 'maestros', 'icono' => 'LayoutGrid', 'modulos' => array_values($disponibles)];
        }

        return $secciones;
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
            'es_admin'       => $this->esAdministrador($rol),
            'usuarios_count' => $rol->usuarios_count ?? 0,
            'permisos_count' => $rol->permisos_count ?? 0,
        ];
    }
}
