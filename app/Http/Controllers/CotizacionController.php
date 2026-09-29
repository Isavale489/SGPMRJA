<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Http\Requests\GuardarCotizacionRequest;
use App\Models\BordadoUbicacion;
use App\Models\Cliente;
use App\Models\Color;
use App\Models\Cotizacion;
use App\Models\Genero;
use App\Models\Impuesto;
use App\Models\Logo;
use App\Models\Pedido;
use App\Models\Talla;
use App\Models\TasaCambio;
use App\Models\TipoProducto;
use App\Services\CotizacionService;
use App\Support\CatalogoGeografico;
use App\Support\FiltrosUrl;
use App\Support\GruposCotizacion;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use PDF;

/**
 * Cotizaciones (Inertia). Listado con «Ver» por pasos (?ver=ID) y asistente de
 * 3 pasos (Cliente → Productos → Resumen) en páginas propias para crear y
 * editar. Pedidos sigue en Blade: consume datos-para-pedido y convertir-a-pedido
 * (JSON), cuyos contratos fija CotizacionPedidoFlujoTest.
 */
class CotizacionController extends Controller
{
    use RespondeSegunCliente;

    public function __construct(
        private CotizacionService $cotizacionService
    ) {
    }

    public function index(Request $request): Response
    {
        Cotizacion::actualizarCotizacionesVencidas();

        $filtros = FiltrosUrl::de($request, ['buscar', 'estado', 'desde', 'hasta', 'orden', 'ver'], ['desde', 'hasta']);
        if (isset($filtros['estado']) && ! in_array($filtros['estado'], self::ESTADOS, true)) {
            unset($filtros['estado']);
        }

        return Inertia::render('Cotizaciones/Index', [
            'registros' => fn () => $this->listado($filtros),
            'filtros' => (object) $filtros,
            'detalle' => fn () => isset($filtros['ver']) && ($c = Cotizacion::find((int) $filtros['ver'])) ? $this->detalle($c) : null,
            'estados' => self::ESTADOS,
            'diasVigencia' => Cotizacion::diasVigencia(),
            'iva' => Impuesto::tasaIva(),
            'terminos' => $this->terminos(),
            'urls' => [
                'index' => route('cotizaciones.index', absolute: false),
                'crear' => route('cotizaciones.create', absolute: false),
                'reportePdf' => route('cotizaciones.reporte.pdf', absolute: false),
                'buscarCliente' => route('clientes.search', absolute: false),
                'pedidos' => url('/pedidos'),
            ],
        ]);
    }

    public const ESTADOS = ['Pendiente', 'Aprobada', 'Vencida', 'Convertida', 'Cancelada'];

    private function listado(array $f)
    {
        $q = Cotizacion::query()
            ->with(['cliente' => fn ($c) => $c->withTrashed()->with('persona')])
            ->select('cotizacion.*');

        if (isset($f['estado'])) {
            $q->where('cotizacion.estado', $f['estado']);
        }
        if (isset($f['desde'])) {
            $q->whereDate('cotizacion.fecha_cotizacion', '>=', $f['desde']);
        }
        if (isset($f['hasta'])) {
            $q->whereDate('cotizacion.fecha_cotizacion', '<=', $f['hasta']);
        }
        // Lo que se ve en la fila: n.º (con o sin #), cliente (nombre o documento) y estado.
        if (isset($f['buscar'])) {
            $kw = '%'.FiltrosUrl::like($f['buscar']).'%';
            $numero = ltrim(trim($f['buscar']), '#');
            $q->where(fn ($w) => $w->where('cotizacion.estado', 'like', $kw)
                ->when(ctype_digit($numero), fn ($w) => $w->orWhere('cotizacion.id', (int) $numero))
                ->orWhereHas('cliente', fn ($c) => $c->withTrashed()->whereHas('persona', fn ($p) => $p
                    ->where('nombre', 'like', $kw)
                    ->orWhereRaw('CONCAT(tipo_documento, documento_identidad) like ?', [$kw]))));
        }

        match ($f['orden'] ?? 'recientes') {
            'total_desc' => $q->orderByDesc('cotizacion.total')->orderByDesc('cotizacion.id'),
            'total_asc' => $q->orderBy('cotizacion.total')->orderByDesc('cotizacion.id'),
            default => $q->orderByDesc('cotizacion.created_at')->orderByDesc('cotizacion.id'),
        };

        return $q->paginate(15)->appends(Arr::except($f, ['ver']))->through(fn (Cotizacion $c) => $this->fila($c));
    }

    /** Espejo de `CotizacionFila` en resources/js/pages/Cotizaciones/tipos.ts (lo verifica CotizacionesPaginaTest). */
    private function fila(Cotizacion $c): array
    {
        return [
            'id' => $c->id,
            'cliente' => $c->cliente?->nombre ?? 'Cliente no encontrado',
            'cliente_doc' => $c->cliente?->documento,
            'cliente_inhabilitado' => (bool) $c->cliente?->trashed(),
            'fecha' => $c->fecha_cotizacion?->toDateString(),
            'validez' => $c->fechaLimiteVigencia()?->toDateString(),
            'total' => (float) $c->total,
            'tasa' => $this->tasa($c),
            'estado' => $c->estado,
            'prioridad' => $c->prioridad ?? 'Normal',
        ];
    }

    /** Tasa guardada en la cotización, con su fecha BCV si el valor coincide (null si no la tiene). */
    private function tasa(Cotizacion $c): ?array
    {
        if ((float) $c->tasa_cambio_valor <= 0) {
            return null;
        }
        $fecha = TasaCambio::fechaParaValor($c->tasa_cambio_valor, ($c->fecha_cotizacion ?? $c->created_at)?->toDateString());

        return ['valor' => (float) $c->tasa_cambio_valor, 'fecha' => $fecha?->toDateString()];
    }

    /** Espejo de `CotizacionDetalle` (tipos.ts): lo que muestra «Ver», sin otra petición. */
    private function detalle(Cotizacion $c): array
    {
        GruposCotizacion::cargar($c)->load(['cliente' => fn ($q) => $q->withTrashed()->with('persona'), 'user:id,name,avatar']);

        return [
            ...$this->fila($c),
            'notas' => $c->notas,
            'condiciones' => $c->condiciones_terminos,
            'cliente_datos' => $c->cliente?->resumenParaCotizacion(),
            'creador' => $this->creador($c),
            'grupos' => GruposCotizacion::desde($c->productos),
        ];
    }

    private function creador(Cotizacion $c): ?array
    {
        return $c->user ? [
            'nombre' => $c->user->name,
            'avatar' => $c->user->avatar ? $c->user->avatar_url : null,
            'fecha' => $c->created_at?->format('Y-m-d H:i'),
        ] : null;
    }

    /** Términos del PDF: el abono mínimo y los días hábiles vienen de la configuración. */
    private function terminos(): array
    {
        return ['abono' => Pedido::porcentajeAbonoMinimo(), 'dias' => Pedido::diasHabilesEntrega()];
    }

    public function create(): Response
    {
        return Inertia::render('Cotizaciones/Formulario', $this->propsFormulario(null));
    }

    public function edit(Request $request, Cotizacion $cotizacion)
    {
        if (! $cotizacion->esEditable()) {
            return redirect()->route('cotizaciones.index')->with('error', "La cotización #{$cotizacion->id} está {$cotizacion->estado}: ya no se puede editar.");
        }

        return Inertia::render('Cotizaciones/Formulario', $this->propsFormulario($cotizacion));
    }

    /**
     * Todo lo que usa el asistente viaja como props: el catálogo (tipos con sus
     * telas y atributos), colores, tallas, géneros, ubicaciones de bordado y
     * logos. Antes se pedían por AJAX a endpoints de otros módulos, que exigían
     * sus propios permisos (Colores, Productos).
     */
    private function propsFormulario(?Cotizacion $c): array
    {
        if ($c) {
            GruposCotizacion::cargar($c)->load(['cliente' => fn ($q) => $q->withTrashed()->with('persona'), 'user:id,name,avatar']);
        }

        return [
            'cotizacion' => $c ? [
                'id' => $c->id,
                'estado' => $c->estado,
                'cliente' => $c->cliente?->resumenParaCotizacion(),
                'fecha' => $c->fecha_cotizacion?->toDateString(),
                'validez' => $c->fechaLimiteVigencia()?->toDateString(),
                'prioridad' => $c->prioridad ?? 'Normal',
                'notas' => $c->notas,
                'creador' => $this->creador($c),
                'grupos' => GruposCotizacion::desde($c->productos),
            ] : null,
            'catalogo' => TipoProducto::with([
                'telas' => fn ($q) => $q->where('estado', true)->orderBy('nombre'),
                'atributos' => fn ($q) => $q->orderBy('tipo_producto_atributo.orden'),
                'atributos.valores',
            ])->orderBy('nombre')->get()->map(fn (TipoProducto $t) => [
                'id' => $t->id,
                'nombre' => $t->nombre,
                'prefijo' => $t->prefijo,
                'imagen' => $t->imagen_url,
                'precio' => (float) $t->precio_confeccion,
                'requiere_tela' => (bool) $t->requiere_tela,
                'telas' => $t->telas->map(fn ($i) => ['id' => $i->id, 'nombre' => $i->nombre, 'codigo' => $i->codigo])->values()->all(),
                'atributos' => $t->atributos->map(fn ($a) => [
                    'id' => $a->id,
                    'nombre' => $a->nombre,
                    'valores' => $a->valores->map(fn ($v) => ['id' => $v->id, 'nombre' => $v->nombre, 'codigo' => $v->codigo])->values()->all(),
                ])->values()->all(),
            ])->all(),
            'colores' => Color::activo()->orderBy('grupo')->orderBy('nombre')->get()
                ->map(fn ($x) => ['id' => $x->id, 'nombre' => $x->nombre, 'grupo' => $x->grupo, 'hex' => $x->hex_referencial])->all(),
            'tallas' => Talla::activo()->orderBy('orden')->orderBy('nombre')->get()
                ->map(fn ($x) => ['id' => $x->id, 'nombre' => $x->etiqueta ?: $x->nombre, 'grupo' => $x->grupo ?: 'Otras'])->all(),
            'generos' => Genero::activo()->orderBy('orden')->get()
                ->map(fn ($x) => ['id' => $x->id, 'nombre' => $x->etiqueta ?: $x->nombre])->all(),
            'ubicaciones' => BordadoUbicacion::activo()->orderBy('grupo')->orderBy('orden')->orderBy('nombre')->get()
                ->map(fn ($x) => ['id' => $x->id, 'nombre' => $x->nombre, 'grupo' => $x->grupo ?: 'General', 'precio' => (float) $x->precio_base])->all(),
            'logos' => Logo::orderBy('name')->get()->map(fn ($x) => ['id' => $x->id, 'nombre' => $x->name, 'archivo' => $x->original_filename])->all(),
            'maxBordados' => (int) parametro('cotizaciones.max_bordados_producto'),
            'diasVigencia' => Cotizacion::diasVigencia(),
            'iva' => Impuesto::tasaIva(),
            'terminos' => $this->terminos(),
            'estadosVe' => CatalogoGeografico::mapa(),
            'urls' => [
                'index' => route('cotizaciones.index', absolute: false),
                'guardar' => $c ? route('cotizaciones.update', $c, absolute: false) : route('cotizaciones.store', absolute: false),
                'resolverVariante' => route('cotizaciones.resolverVariante', absolute: false),
                'proyeccion' => route('cotizaciones.proyeccionInsumos', absolute: false),
                'crearCompra' => route('compras.create', absolute: false),
                'buscarCliente' => route('clientes.search', absolute: false),
                'buscarPersona' => route('personas.search', absolute: false),
                'desdePersona' => url('/clientes/from-persona'),
                'clientes' => route('clientes.index', absolute: false),
                'checkDocumento' => route('clientes.check-documento', absolute: false),
                'checkEmail' => route('clientes.check-email', absolute: false),
                'colores' => route('colores.store', absolute: false),
                'logos' => route('logos.store', absolute: false),
                'telas' => url('/tipo-productos'),
            ],
        ];
    }

    public function getUbicacionesBordado()
    {
        $catalogo = BordadoUbicacion::activo()
            ->orderBy('grupo')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'grupo', 'precio_base', 'orden']);

        return response()->json($catalogo);
    }

    public function store(GuardarCotizacionRequest $request)
    {
        $cotizacion = $this->cotizacionService->crear($request->validated());

        if ($this->esInertia($request)) {
            return redirect()->route('cotizaciones.index', ['ver' => $cotizacion->id])->with('success', "Cotización #{$cotizacion->id} creada.");
        }

        return response()->json(['success' => 'Cotización creada exitosamente.']);
    }

    /** JSON de la cotización (lo usaba la vista Blade; se conserva para clientes JSON). */
    public function show($id)
    {
        $cotizacion = Cotizacion::with(['user:id,name,avatar', 'productos.producto.tipoProducto', 'productos.bordados.logo:id,name'])
            ->with([
                'cliente' => function ($query) {
                    $query->withTrashed()->with('persona');
                }
            ])
            ->findOrFail($id);

        $clienteData = null;
        if ($cotizacion->cliente) {
            $clienteData = [
                'id' => $cotizacion->cliente->id,
                'nombre' => $cotizacion->cliente->nombre,
                'apellido' => '',
                'email' => $cotizacion->cliente->email,
                'telefono' => $cotizacion->cliente->telefono,
                'documento' => $cotizacion->cliente->documento,
                'tipo_documento' => optional($cotizacion->cliente->persona)->tipo_documento,
                'razon_social' => optional($cotizacion->cliente->persona)->razon_social,
                'direccion' => $cotizacion->cliente->direccion,
                'ciudad' => $cotizacion->cliente->ciudad,
                'eliminado' => $cotizacion->cliente->deleted_at ? true : false,
            ];
        }

        $response = $cotizacion->toArray();
        $response['cliente'] = $clienteData;
        $response['tasa_fecha_fmt'] = optional(TasaCambio::fechaParaValor(
            $cotizacion->tasa_cambio_valor,
            optional($cotizacion->fecha_cotizacion)->toDateString() ?? optional($cotizacion->created_at)->toDateString()
        ))->format('d/m/Y');
        $response['creador'] = $cotizacion->user ? [
            'name' => $cotizacion->user->name,
            'avatar_url' => $cotizacion->user->avatar_url,
            'fecha' => optional($cotizacion->created_at)->format('d/m/Y H:i'),
        ] : null;

        return response()->json($response);
    }

    public function update(GuardarCotizacionRequest $request, $id)
    {
        $cotizacion = Cotizacion::findOrFail($id);
        if (! $cotizacion->esEditable()) {
            return $this->rechazar($request, "La cotización está {$cotizacion->estado}: ya no se puede editar.");
        }

        $this->cotizacionService->actualizar($cotizacion, $request->validated());

        if ($this->esInertia($request)) {
            return redirect()->route('cotizaciones.index', ['ver' => $cotizacion->id])->with('success', "Cotización #{$cotizacion->id} actualizada.");
        }

        return response()->json(['success' => 'Cotización actualizada exitosamente.']);
    }

    public function reactivar(Request $request, $id)
    {
        $cotizacion = Cotizacion::findOrFail($id);
        try {
            $this->cotizacionService->reactivar($cotizacion);
        } catch (\InvalidArgumentException $e) {
            return $this->rechazar($request, $e->getMessage());
        }
        $dias = Cotizacion::diasVigencia();
        $mensaje = "Cotización reactivada correctamente. Nueva validez: {$dias} días.";

        return $this->responder($request, $mensaje, ['success' => $mensaje]);
    }

    public function destroy(Request $request, $id)
    {
        $cotizacion = Cotizacion::findOrFail($id);
        if (! $cotizacion->esEditable()) {
            return $this->rechazar($request, "La cotización está {$cotizacion->estado}: no se puede eliminar.");
        }
        $cotizacion->delete();

        Log::warning('Cotización eliminada', [
            'cotizacion_id' => $id,
            'cliente_id' => $cotizacion->cliente_id,
            'total' => $cotizacion->total,
            'user_id' => auth()->id(),
        ]);

        return $this->responder($request, "Cotización #{$cotizacion->id} eliminada.", ['success' => 'Cotización eliminada exitosamente.']);
    }

    public function reportePdf(Request $request)
    {
        $f = FiltrosUrl::de($request, ['estado', 'cliente_id', 'cliente', 'fecha_desde', 'fecha_hasta', 'orden'], ['fecha_desde', 'fecha_hasta']);

        $query = Cotizacion::with(['user:id,name', 'cliente.persona']);
        if (isset($f['estado'])) {
            $query->where('estado', $f['estado']);
        }
        // Cliente: el id exacto (buscador del diálogo); si no viene, búsqueda parcial por nombre o documento.
        if (isset($f['cliente_id'])) {
            $query->where('cliente_id', (int) $f['cliente_id']);
        } elseif (isset($f['cliente'])) {
            $term = '%'.FiltrosUrl::like($f['cliente']).'%';
            $query->whereHas('cliente', function ($c) use ($term) {
                $c->withTrashed()->whereHas('persona', function ($p) use ($term) {
                    $p->where('nombre', 'like', $term)
                      ->orWhere('documento_identidad', 'like', $term);
                });
            });
        }
        if (isset($f['fecha_desde'])) {
            $query->whereDate('fecha_cotizacion', '>=', $f['fecha_desde']);
        }
        if (isset($f['fecha_hasta'])) {
            $query->whereDate('fecha_cotizacion', '<=', $f['fecha_hasta']);
        }

        $orden = $f['orden'] ?? 'recientes';
        switch ($orden) {
            case 'total_desc':
                $query->orderBy('total', 'desc');
                break;
            case 'total_asc':
                $query->orderBy('total', 'asc');
                break;
            default:
                $orden = 'recientes';
                $query->orderBy('created_at', 'desc');
                break;
        }

        $cotizaciones = $query->get();

        $filtros = [];
        if (isset($f['estado'])) {
            $filtros['Estado'] = $f['estado'];
        }
        if (isset($f['cliente_id'])) {
            $cli = Cliente::withTrashed()->with('persona')->find((int) $f['cliente_id']);
            $filtros['Cliente'] = $cli ? ($cli->nombre ?: '#'.$f['cliente_id']) : '#'.$f['cliente_id'];
        } elseif (isset($f['cliente'])) {
            $filtros['Cliente'] = trim($f['cliente']);
        }
        if ($rango = \App\Support\ReporteFiltros::rango($f['fecha_desde'] ?? null, $f['fecha_hasta'] ?? null)) {
            $filtros['Fecha de emisión'] = $rango;
        }
        $filtros['Orden'] = ['recientes' => 'Más recientes', 'total_desc' => 'Mayor total', 'total_asc' => 'Menor total'][$orden];

        $pdf = PDF::loadView('admin.cotizaciones.reporte_pdf', compact('cotizaciones', 'filtros'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('reporte_cotizaciones_'.now()->format('Ymd_His').'.pdf');
    }

    public function reporteGeneral()
    {
        $cotizaciones = Cotizacion::with('user:id,name')->get();

        return view('admin.cotizaciones.reporte_general', compact('cotizaciones'));
    }

    public function cotizacionPdf(Cotizacion $cotizacion)
    {
        $cotizacion->load(['user:id,name']);

        $cotizacion->load([
            'cliente' => function ($query) {
                $query->withTrashed()->with('persona');
            },
            'productos.producto' => function ($query) {
                $query->withTrashed()->with('tipoProducto');
            },
            'productos.tipoProducto',
            'productos.genero',
            'productos.color',
            'productos.talla',
            'productos.bordados.logo:id,name',
        ]);

        // IVA del catálogo de impuestos (la misma tasa que muestra la pantalla).
        $ivaPorcentaje = Impuesto::tasaIva();
        $subtotal = $cotizacion->total;
        $descuento = 0; // Ajustable en el futuro si se implementa
        $iva = round(($subtotal - $descuento) * $ivaPorcentaje / 100, 2);
        $totalPagar = round($subtotal - $descuento + $iva, 2);

        // Tasa de cambio aplicada (snapshot de la cotización) + su fecha BCV exacta.
        $tasaValor = $cotizacion->tasa_cambio_valor;
        $tasaFecha = $tasaValor
            ? optional(TasaCambio::tasaVigente(
                \Illuminate\Support\Carbon::parse($cotizacion->fecha_cotizacion ?? $cotizacion->created_at)->toDateString(), 'USD'
            ))->fecha_bcv
            : null;

        $pdf = PDF::loadView('admin.cotizaciones.factura', [
            'cotizacion' => $cotizacion,
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'iva' => $iva,
            'ivaPorcentaje' => $ivaPorcentaje,
            'totalPagar' => $totalPagar,
            'tasaValor' => $tasaValor,
            'tasaFecha' => $tasaFecha,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('cotizacion_'.$cotizacion->id.'.pdf');
    }

    /**
     * Cambio de estado manual (menú del listado): solo las transiciones de
     * Cotizacion::TRANSICIONES. JSON: `{success, estado}` / 422 `{error}` como
     * siempre.
     */
    public function updateEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:Pendiente,Aprobada,Cancelada,Convertida,Vencida',
        ]);

        $cotizacion = Cotizacion::findOrFail($id);

        try {
            $this->cotizacionService->cambiarEstado($cotizacion, $request->estado);
        } catch (\InvalidArgumentException $e) {
            return $this->esInertia($request)
                ? back()->with('error', $e->getMessage())
                : response()->json(['error' => $e->getMessage()], 422);
        }

        $textos = ['Aprobada' => 'aprobada', 'Cancelada' => 'cancelada', 'Pendiente' => 'de nuevo en Pendiente'];

        return $this->responder($request, "Cotización #{$cotizacion->id} {$textos[$request->estado]}.", [
            'success' => 'Estado actualizado a: '.$request->estado,
            'estado' => $request->estado,
        ]);
    }

    /**
     * Obtener datos de cotización para pre-llenar formulario de pedido
     */
    public function getDatosParaPedido($id)
    {
        $cotizacion = Cotizacion::with([
            'cliente.persona',
            'productos.producto.tipoProducto',
            'productos.tipoProducto.atributos.valores',
            'productos.bordados.logo:id,name',
        ])->findOrFail($id);

        // Verificar que esté aprobada
        if ($cotizacion->estado !== 'Aprobada') {
            return response()->json([
                'error' => 'Solo se pueden convertir cotizaciones con estado Aprobada.'
            ], 422);
        }

        // Preparar datos para el formulario de pedido
        $datosParaPedido = [
            'cotizacion_id' => $cotizacion->id,
            'cliente_id' => $cotizacion->cliente_id,
            // Clave nueva (no rompe el contrato): el pedido hereda la prioridad.
            'prioridad' => $cotizacion->prioridad ?? 'Normal',
            'cliente' => $cotizacion->cliente ? [
                'id' => $cotizacion->cliente->id,
                'nombre' => $cotizacion->cliente->nombre,
                'apellido' => '',
                'email' => $cotizacion->cliente->email,
                'telefono' => $cotizacion->cliente->telefono,
                'documento' => $cotizacion->cliente->documento,
            ] : null,
            'total' => $cotizacion->total,
            'productos' => $cotizacion->productos->map(function ($detalle) {
                $recargoUnitario = $detalle->bordados->sum(function ($bordado) {
                    return ((float) $bordado->precio_aplicado) * ((int) ($bordado->cantidad ?: 1));
                });

                $ubicacionLegacy = $detalle->bordados->pluck('nombre_aplicado')->implode(', ');
                $cantidadLegacy = $detalle->bordados->sum(function ($bordado) {
                    return (int) ($bordado->cantidad ?: 1);
                });

                // Línea dinámica (sin producto_id): arrastrar la variante (tipo + tela + atributos)
                // para que el pedido la persista, y construir un nombre legible desde el snapshot.
                $esDinamica = empty($detalle->producto_id) && !empty($detalle->tipo_producto_id);
                $telaSnap   = $detalle->tela_snapshot ?? null;
                $nombreLinea = $detalle->producto
                    ? $detalle->producto->nombre_completo
                    : trim(($detalle->tipoProducto?->nombre ?? 'Variante')
                        . (is_array($telaSnap) && !empty($telaSnap['nombre']) ? ' · ' . $telaSnap['nombre'] : ''));

                return [
                    'producto_id' => $detalle->producto_id,
                    'tipo_producto_id' => $detalle->tipo_producto_id,
                    'insumo_tela_id' => is_array($telaSnap) ? ($telaSnap['id'] ?? null) : null,
                    'atributo_valor_ids' => $esDinamica && $detalle->tipoProducto
                        ? $detalle->tipoProducto->valorIdsDesdeSnapshot($detalle->atributos_snapshot)
                        : [],
                    'sku' => $detalle->producto ? $detalle->producto->codigo : $detalle->sku_snapshot,
                    'imagen_url' => ($detalle->producto && $detalle->producto->imagen)
                        ? asset($detalle->producto->imagen)
                        : ($detalle->tipoProducto?->imagen_url),
                    'producto_nombre' => $nombreLinea ?: 'N/A',
                    'cantidad' => $detalle->cantidad,
                    'descripcion' => $detalle->descripcion,
                    'lleva_bordado' => $detalle->lleva_bordado,
                    'nombre_logo' => $detalle->nombre_logo,
                    'bordados' => $detalle->bordados->map(function ($bordado) {
                        return [
                            'ubicacion_bordado_id' => $bordado->ubicacion_bordado_id,
                            'logo_id' => $bordado->logo_id,
                            'nombre_aplicado' => $bordado->nombre_aplicado,
                            'nombre_logo' => $bordado->logo ? $bordado->logo->name : $bordado->nombre_logo_aplicado,
                            'nombre_logo_aplicado' => $bordado->nombre_logo_aplicado,
                            'es_personalizada' => (bool) $bordado->es_personalizada,
                            'cantidad' => (int) $bordado->cantidad,
                            'precio_aplicado' => (float) $bordado->precio_aplicado,
                        ];
                    })->values(),
                    'recargo_bordado_unitario' => $recargoUnitario,
                    'ubicacion_logo' => $ubicacionLegacy ?: null,
                    'cantidad_logo' => $cantidadLegacy ?: null,
                    'talla_id' => $detalle->talla_id,
                    'color_id' => $detalle->color_id,
                    'genero_id' => $detalle->genero_id,
                    'precio_unitario' => $detalle->precio_unitario,
                ];
            }),
        ];

        return response()->json($datosParaPedido);
    }

    /**
     * Convertir cotización a pedido directamente (endpoint atómico, JSON).
     */
    public function convertirAPedido($id)
    {
        $cotizacion = Cotizacion::with(['cliente', 'productos'])->findOrFail($id);

        try {
            $pedido = $this->cotizacionService->convertirAPedido($cotizacion);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('Error al convertir cotización a pedido', [
                'cotizacion_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Error interno al convertir la cotización. Intente de nuevo.'], 500);
        }

        return response()->json([
            'success' => 'Cotización convertida a pedido exitosamente.',
            'pedido_id' => $pedido->id,
            'message' => 'Se ha creado el pedido #' . $pedido->id . '. Puede editar el pedido para agregar fechas de entrega, abonos y método de pago.'
        ]);
    }
}
