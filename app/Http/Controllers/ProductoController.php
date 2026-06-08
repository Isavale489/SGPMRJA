<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Producto;
use App\Models\TipoProducto;
use App\Services\ProductoService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use PDF;

class ProductoController extends Controller
{
    public function __construct(private ProductoService $productoService)
    {
    }

    public function index(Request $request)
    {
        $tiposProducto = TipoProducto::with(['atributos.valores'])->orderBy('nombre')->get();
        $telasDisponibles = Insumo::telas()->orderBy('nombre')->get(['id', 'nombre', 'codigo', 'costo_unitario', 'unidad_medida']);
        // Insumos disponibles para el template de orden de producción del tipo.
        // Excluimos telas: la tela es per-variante (cada producto tiene su insumo_tela_id).
        // Solo se cataloga al tipo lo constante (hilo, botón, cierre, etiqueta...).
        $insumosDisponibles = Insumo::where('estado', true)
            ->where('tipo', '!=', 'Tela')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'unidad_medida']);
        $historial = $request->has('historial');

        return view('admin.productos.index', compact('tiposProducto', 'telasDisponibles', 'insumosDisponibles', 'historial'));
    }

    public function getProductos(Request $request)
    {
        // ── Base query con relaciones ──
        $query = Producto::with(['tipoProducto', 'tela']);

        // ══════════════════════════════════════════════════════════
        // FILTROS AVANZADOS — Server-Side (Patrón Maestro S-07)
        // ══════════════════════════════════════════════════════════

        // Filtro: Tipo de Producto
        if ($request->filled('filter_tipo_producto_id')) {
            $query->where('tipo_producto_id', $request->filter_tipo_producto_id);
        }

        // Filtro: Tela
        if ($request->filled('filter_insumo_tela_id')) {
            $query->where('insumo_tela_id', $request->filter_insumo_tela_id);
        }

        // Filtro: Estatus (1 = activo, 0 = inactivo/trashed)
        if ($request->filled('filter_estatus')) {
            $estatus = $request->input('filter_estatus');
            if ($estatus === '0') {
                $query->onlyTrashed();
            }
            // Si estatus es '1', el query base ya excluye trashed
        }

        // ══════════════════════════════════════════════════════════
        // ORDENAMIENTO — Selector "Ordenar por" del frontend
        // Fallback: más recientes primero (created_at DESC)
        // ══════════════════════════════════════════════════════════
        $orden = $request->input('filter_orden', 'recientes');

        switch ($orden) {
            case 'codigo_asc':
                $query->orderBy('producto.codigo', 'asc');
                break;
            case 'codigo_desc':
                $query->orderBy('producto.codigo', 'desc');
                break;
            case 'precio_mayor':
                $query->orderBy('producto.precio_base', 'desc');
                break;
            case 'precio_menor':
                $query->orderBy('producto.precio_base', 'asc');
                break;
            case 'recientes':
            default:
                $query->orderBy('producto.created_at', 'desc');
                break;
        }

        return DataTables::of($query)
            // Búsqueda estricta: por las columnas que componen el producto (código,
            // descripción, atributos) y por su tipo y tela. Sobrescribe el buscador
            // global porque "nombre_completo" es un accessor, no una columna real.
            ->filter(function ($query) use ($request) {
                $keyword = trim((string) $request->input('search.value'));
                if ($keyword === '') {
                    return;
                }
                $query->where(function ($q) use ($keyword) {
                    $q->where('producto.codigo', 'like', "%{$keyword}%")
                      ->orWhere('producto.descripcion', 'like', "%{$keyword}%")
                      ->orWhere('producto.atributos_snapshot', 'like', "%{$keyword}%")
                      ->orWhereHas('tipoProducto', fn($t) => $t->where('nombre', 'like', "%{$keyword}%"))
                      ->orWhereHas('tela', fn($t) => $t->where('nombre', 'like', "%{$keyword}%"));
                });
            }, true)
            ->addColumn('tipo_nombre', function ($p) {
                return $p->tipoProducto ? $p->tipoProducto->nombre : 'Sin tipo';
            })
            ->addColumn('tela_nombre', function ($p) {
                return $p->tela ? $p->tela->nombre : '—';
            })
            ->addColumn('atributos_resumen', function ($p) {
                if (empty($p->atributos_snapshot)) {
                    return '—';
                }
                return collect($p->atributos_snapshot)
                    ->map(fn($val, $atr) => "{$atr}: {$val}")
                    ->implode(' · ');
            })
            ->addColumn('nombre_completo', function ($p) {
                return $p->nombre_completo;
            })
            ->addColumn('trashed', function ($p) {
                return $p->trashed();
            })
            ->make(true);
    }

    private function handleFileUpload($file, $oldPath, $directory)
    {
        if ($oldPath && file_exists(public_path($oldPath))) {
            unlink(public_path($oldPath));
        }
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($directory), $filename);
        return $directory . '/' . $filename;
    }

    public function store(Request $request)
    {
        $validated = $this->validarProducto($request);

        $tipo = TipoProducto::findOrFail($validated['tipo_producto_id']);
        $this->validarTelaYAtributos($request, $tipo);

        $imagenPath = null;
        if ($request->hasFile('imagen')) {
            $imagenPath = $this->handleFileUpload($request->file('imagen'), null, 'productoimg/imagenes');
        }

        $producto = $this->productoService->crear([
            'tipo_producto_id'    => $validated['tipo_producto_id'],
            'insumo_tela_id'      => $request->input('insumo_tela_id'),
            'atributo_valor_ids'  => array_map('intval', $request->input('atributo_valor_ids', [])),
            'descripcion'         => $request->input('descripcion'),
            'precio_base'         => $validated['precio_base'],
            'imagen'              => $imagenPath,
            'estado'              => $request->boolean('estado', true),
        ]);

        return response()->json([
            'success' => 'Producto creado exitosamente.',
            'codigo'  => $producto->codigo,
            'id'      => $producto->id,
        ]);
    }

    public function show($id)
    {
        $producto = Producto::with([
            'tipoProducto',
            'tela',
            'atributoValores.atributo',
        ])->findOrFail($id);

        return response()->json([
            'id'                 => $producto->id,
            'tipo_producto_id'   => $producto->tipo_producto_id,
            'tipo_nombre'        => $producto->tipoProducto?->nombre,
            'insumo_tela_id'     => $producto->insumo_tela_id,
            'tela_nombre'        => $producto->tela?->nombre,
            'tela_codigo'        => $producto->tela?->codigo,
            'atributo_valor_ids' => $producto->atributoValores->pluck('id'),
            'atributos_snapshot' => $producto->atributos_snapshot ?? new \stdClass(),
            'codigo'             => $producto->codigo,
            'nombre'             => $producto->nombre_completo,
            'descripcion'        => $producto->descripcion,
            'precio_base'        => $producto->precio_base,
            'imagen'             => $producto->imagen ? asset($producto->imagen) : null,
            'estado'             => $producto->estado,
            'created_at'         => $producto->created_at?->format('d/m/Y H:i:s'),
            'updated_at'         => $producto->updated_at?->format('d/m/Y H:i:s'),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validarProducto($request);

        $producto = Producto::findOrFail($id);
        $tipo = TipoProducto::findOrFail($validated['tipo_producto_id']);
        $this->validarTelaYAtributos($request, $tipo);

        $imagenPath = null;
        if ($request->hasFile('imagen')) {
            $imagenPath = $this->handleFileUpload($request->file('imagen'), $producto->imagen, 'productoimg/imagenes');
        }

        $this->productoService->actualizar($producto, [
            'tipo_producto_id'    => $validated['tipo_producto_id'],
            'insumo_tela_id'      => $request->input('insumo_tela_id'),
            'atributo_valor_ids'  => array_map('intval', $request->input('atributo_valor_ids', [])),
            'descripcion'         => $request->input('descripcion'),
            'precio_base'         => $validated['precio_base'],
            'imagen'              => $imagenPath,
            'estado'              => $request->has('estado') ? $request->boolean('estado') : $producto->estado,
        ]);

        return response()->json([
            'success' => 'Producto actualizado exitosamente.',
            'codigo'  => $producto->fresh()->codigo,
        ]);
    }

    public function destroy($id)
    {
        Producto::findOrFail($id)->delete();
        return response()->json(['success' => 'Producto inhabilitado exitosamente.']);
    }

    public function restore($id)
    {
        $producto = Producto::onlyTrashed()->findOrFail($id);
        $producto->restore();
        return response()->json(['success' => 'Producto restaurado exitosamente.']);
    }

    public function reportePdf(Request $request)
    {
        // Catálogo = Tipo de Producto. El reporte lista los Tipos (activos o historial).
        $historial = $request->boolean('historial');

        $query = TipoProducto::withCount(['atributos', 'telas'])->orderBy('nombre');
        if ($historial) {
            $query->onlyTrashed();
        }
        $tipos = $query->get();

        $data = [
            'title'     => 'Catálogo de Tipos de Producto',
            'date'      => date('m/d/Y'),
            'tipos'     => $tipos,
            'historial' => $historial,
        ];
        $pdf = PDF::loadView('admin.productos.reporte_pdf', $data);
        return $pdf->download('catalogo-tipos-' . time() . '.pdf');
    }

    /**
     * Sugerencia de precio en vivo: tela.costo_unitario + tipo.precio_confeccion.
     */
    public function sugerirPrecio(Request $request)
    {
        $request->validate([
            'tipo_producto_id' => 'required|exists:tipo_producto,id',
            'insumo_tela_id'   => 'nullable|exists:insumo,id',
        ]);

        $tipo = TipoProducto::find($request->tipo_producto_id);
        $tela = $request->insumo_tela_id ? Insumo::find($request->insumo_tela_id) : null;

        return response()->json([
            'precio_sugerido'   => $this->productoService->sugerirPrecio($tipo, $tela),
            'precio_confeccion' => (float) ($tipo->precio_confeccion ?? 0),
            'costo_tela'        => $tela ? (float) $tela->costo_unitario : 0,
        ]);
    }

    /**
     * Resuelve la variante exacta (producto) que matchea una combinación
     * tipo + tela + valores. Usado por el wizard de cotizaciones para que
     * el usuario seleccione la variante con chips antes de configurarla.
     */
    public function resolverVariante(Request $request)
    {
        $request->validate([
            'tipo_producto_id'      => 'required|exists:tipo_producto,id',
            'insumo_tela_id'        => 'nullable|exists:insumo,id',
            'atributo_valor_ids'    => 'nullable|array',
            'atributo_valor_ids.*'  => 'integer|exists:atributo_valor,id',
        ]);

        $tipoId    = (int) $request->tipo_producto_id;
        $telaId    = $request->insumo_tela_id ? (int) $request->insumo_tela_id : null;
        $valoresIds = array_map('intval', $request->input('atributo_valor_ids', []));
        sort($valoresIds);

        $candidatos = Producto::with(['tela', 'atributoValores', 'tipoProducto'])
            ->where('tipo_producto_id', $tipoId)
            ->where('estado', true)
            ->when($telaId, fn($q) => $q->where('insumo_tela_id', $telaId))
            ->when(!$telaId, fn($q) => $q->whereNull('insumo_tela_id'))
            ->get();

        $match = $candidatos->first(function ($p) use ($valoresIds) {
            $idsActuales = $p->atributoValores->pluck('id')->sort()->values()->all();
            return $idsActuales == $valoresIds;
        });

        // Caso compat: la combinación ya existe como Producto concreto (legacy).
        if ($match) {
            return response()->json([
                'found'   => true,
                'dynamic' => false,
                'producto' => [
                    'id'           => $match->id,
                    'codigo'       => $match->codigo,
                    'precio_base'  => (float) $match->precio_base,
                    'imagen'       => $match->imagen ? asset($match->imagen) : null,
                    'tipo_nombre'  => $match->tipoProducto?->nombre,
                    'tela_nombre'  => $match->tela?->nombre,
                ],
            ]);
        }

        // Caso dinámico (FEAT-003): no existe Producto → se calcula la variante
        // al vuelo (SKU + precio + snapshots) sin persistir nada.
        $tipo = TipoProducto::find($tipoId);
        $tela = $telaId ? Insumo::find($telaId) : null;

        if ($tipo->requiere_tela && !$tela) {
            return response()->json([
                'found'   => false,
                'message' => 'Este tipo de producto requiere una tela.',
            ]);
        }

        // La tela elegida debe estar permitida para el tipo (tipo_producto_tela).
        if ($tela && !$tipo->telas()->wherePivot('insumo_id', $tela->id)->exists()) {
            return response()->json([
                'found'   => false,
                'message' => 'La tela seleccionada no está habilitada para este tipo de producto.',
            ]);
        }

        $snap = $this->productoService->buildSnapshotsDesdeTipo($tipo, $tela, $valoresIds);

        return response()->json([
            'found'    => true,
            'dynamic'  => true,
            'producto' => [
                'id'           => null,
                'codigo'       => $snap['sku'],
                'precio_base'  => $snap['precio_sugerido'],
                'imagen'       => null,
                'tipo_nombre'  => $tipo->nombre,
                'tela_nombre'  => $tela?->nombre,
            ],
            'variante' => [
                'tipo_producto_id'   => $tipo->id,
                'insumo_tela_id'     => $tela?->id,
                'atributo_valor_ids' => $valoresIds,
                'tela_snapshot'      => $snap['tela_snapshot'],
                'atributos_snapshot' => $snap['atributos_snapshot'],
                'sku'                => $snap['sku'],
                'precio_sugerido'    => $snap['precio_sugerido'],
            ],
        ]);
    }

    /**
     * Vista previa del SKU sin persistir nada.
     */
    public function previewCodigo(Request $request)
    {
        $request->validate([
            'tipo_producto_id'      => 'required|exists:tipo_producto,id',
            'insumo_tela_id'        => 'nullable|exists:insumo,id',
            'atributo_valor_ids'    => 'nullable|array',
            'atributo_valor_ids.*'  => 'integer|exists:atributo_valor,id',
        ]);

        $tipo = TipoProducto::find($request->tipo_producto_id);
        $tela = $request->insumo_tela_id ? Insumo::find($request->insumo_tela_id) : null;

        return response()->json([
            'codigo' => $this->productoService->previsualizarCodigo(
                $tipo,
                $tela,
                array_map('intval', $request->input('atributo_valor_ids', []))
            ),
        ]);
    }

    private function validarProducto(Request $request): array
    {
        return $request->validate([
            'tipo_producto_id' => 'required|exists:tipo_producto,id',
            'precio_base'      => 'required|numeric|min:0.01',
            'imagen'           => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,bmp,avif|max:10240',
        ], [
            'tipo_producto_id.required' => 'Debe seleccionar un tipo de producto.',
            'tipo_producto_id.exists'   => 'El tipo de producto no existe.',
            'precio_base.required'      => 'El precio base es obligatorio.',
            'precio_base.min'           => 'El precio base debe ser mayor a cero.',
            'imagen.image'              => 'El archivo debe ser una imagen válida.',
            'imagen.mimes'              => 'Formato no permitido. Use JPG, PNG, GIF, WEBP, BMP o AVIF.',
            'imagen.max'                => 'La imagen no puede superar 10MB.',
        ]);
    }

    /**
     * Valida tela según requiere_tela del tipo, y atributos seleccionados según
     * los atributos asociados al tipo.
     */
    private function validarTelaYAtributos(Request $request, TipoProducto $tipo): void
    {
        // Tela
        if ($tipo->requiere_tela && !$request->filled('insumo_tela_id')) {
            abort(response()->json([
                'message' => 'Validación falló.',
                'errors'  => ['insumo_tela_id' => ['Este tipo de producto requiere una tela.']],
            ], 422));
        }

        // Un tipo que no requiere tela no debe recibir una (espejo de resolverVariante).
        if (!$tipo->requiere_tela && $request->filled('insumo_tela_id')) {
            abort(response()->json([
                'message' => 'Validación falló.',
                'errors'  => ['insumo_tela_id' => ['Este tipo de producto no usa tela.']],
            ], 422));
        }

        if ($request->filled('insumo_tela_id')) {
            $tela = Insumo::find($request->insumo_tela_id);
            if (!$tela || $tela->tipo !== 'Tela') {
                abort(response()->json([
                    'message' => 'Validación falló.',
                    'errors'  => ['insumo_tela_id' => ['El insumo seleccionado no es una tela.']],
                ], 422));
            }
        }

        // Atributos: el catálogo guarda el producto BASE (tipo + tela). Las variaciones
        // (manga, cuello, corte...) se configuran al cotizar (FEAT-003), por lo que aquí
        // NO se exigen — aunque el tipo las tenga marcadas como obligatorias.
        // Si por compatibilidad llegan valores, se validan que pertenezcan al tipo.
        $valoresIds = array_map('intval', $request->input('atributo_valor_ids', []));
        if (empty($valoresIds)) {
            return;
        }

        // Mapear cada valor a su atributo y verificar que el atributo esté asociado al tipo
        $atributosDelTipo = $tipo->atributos()->pluck('atributo.id')->all();
        $valores = \App\Models\AtributoValor::whereIn('id', $valoresIds)->get(['id', 'atributo_id']);

        foreach ($valores as $valor) {
            if (!in_array($valor->atributo_id, $atributosDelTipo)) {
                abort(response()->json([
                    'message' => 'Validación falló.',
                    'errors'  => ['atributo_valor_ids' => ['Un valor seleccionado no corresponde a los atributos del tipo.']],
                ], 422));
            }
        }

        // Solo un valor por atributo (no se puede tener Manga Larga Y Manga Corta)
        $atributosUsados = $valores->pluck('atributo_id')->toArray();
        if (count($atributosUsados) !== count(array_unique($atributosUsados))) {
            abort(response()->json([
                'message' => 'Validación falló.',
                'errors'  => ['atributo_valor_ids' => ['Solo se permite un valor por atributo.']],
            ], 422));
        }
    }
}
