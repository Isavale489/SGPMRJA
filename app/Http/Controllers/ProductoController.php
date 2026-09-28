<?php

namespace App\Http\Controllers;

use App\Models\Atributo;
use App\Models\Insumo;
use App\Models\Producto;
use App\Models\TipoProducto;
use App\Services\ProductoService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use PDF;

class ProductoController extends Controller
{
    public function __construct(private ProductoService $productoService)
    {
    }

    /**
     * Catálogo de productos = Tipos de producto (FEAT-003): la página lista los
     * tipos y los gestiona (atributos, telas permitidas, insumos por unidad).
     * Las escrituras van a TipoProductoController.
     */
    public function index(Request $request): Response
    {
        $filtros = array_filter($request->only(['buscar', 'historial']), fn ($v) => $v !== null && $v !== '');
        $query = TipoProducto::with([
            'atributos' => fn ($q) => $q->orderBy('tipo_producto_atributo.orden'),
            'telas:id,nombre,codigo',
            'insumosDefault:id,nombre,unidad_medida',
        ])->withCount('productos')->orderBy('nombre');
        if (! empty($filtros['historial'])) {
            $query->onlyTrashed();
        }
        if (! empty($filtros['buscar'])) {
            $buscar = trim($filtros['buscar']);
            $query->where(fn ($q) => $q->where('nombre', 'like', "%{$buscar}%")->orWhere('prefijo', 'like', "{$buscar}%"));
        }

        return Inertia::render('Productos/Index', [
            'registros' => $query->paginate(15)->withQueryString()->through(fn (TipoProducto $t) => [
                'id' => $t->id,
                'nombre' => $t->nombre,
                'prefijo' => $t->prefijo,
                'descripcion' => $t->descripcion,
                'imagen' => $t->imagen ? asset($t->imagen) : null,
                'precio_confeccion' => (float) $t->precio_confeccion,
                'requiere_tela' => (bool) $t->requiere_tela,
                'requiere_produccion' => (bool) $t->requiere_produccion,
                'consumo_tela_por_unidad' => (float) $t->consumo_tela_por_unidad,
                'atributos' => $t->atributos->map(fn ($a) => ['id' => $a->id, 'nombre' => $a->nombre, 'codigo' => $a->codigo, 'orden' => (int) $a->pivot->orden])->values()->all(),
                'telas' => $t->telas->map(fn ($i) => ['id' => $i->id, 'nombre' => $i->nombre, 'codigo' => $i->codigo])->values()->all(),
                'insumos' => $t->insumosDefault->map(fn ($i) => ['id' => $i->id, 'nombre' => $i->nombre, 'unidad' => $i->unidad_medida, 'cantidad' => (float) $i->pivot->cantidad_estimada])->values()->all(),
                'productos' => $t->productos_count,
                'inhabilitado' => $t->trashed(),
            ]),
            'filtros' => (object) $filtros,
            // Catálogos del formulario.
            'catalogo' => [
                'atributos' => Atributo::withCount('valores')->orderBy('nombre')->get(['id', 'nombre', 'codigo'])
                    ->map(fn ($a) => ['id' => $a->id, 'nombre' => $a->nombre, 'codigo' => $a->codigo, 'valores' => $a->valores_count])->all(),
                'telas' => Insumo::telas()->orderBy('nombre')->get(['id', 'nombre', 'codigo'])->all(),
                // La tela es por variante; al tipo solo se asocia lo constante (hilo, botón, etiqueta…).
                'insumos' => Insumo::where('estado', true)->where('tipo', '!=', 'Tela')->orderBy('nombre')->get(['id', 'nombre', 'unidad_medida'])
                    ->map(fn ($i) => ['id' => $i->id, 'nombre' => $i->nombre, 'unidad' => $i->unidad_medida])->all(),
            ],
            'urls' => [
                'index' => route('productos.index', absolute: false),
                'tipos' => url('/tipo-productos'),
                'reportePdf' => route('productos.reporte.pdf', absolute: false),
            ],
        ]);
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
            'filtros'   => ['Vista' => $historial ? 'Historial (inhabilitados)' : 'Activos'],
        ];
        $pdf = PDF::loadView('admin.productos.reporte_pdf', $data);
        return $pdf->stream('catalogo-tipos-' . time() . '.pdf');
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

}
