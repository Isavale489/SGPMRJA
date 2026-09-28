<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Http\Requests\GuardarInsumoRequest;
use App\Http\Requests\StoreCompraRequest;
use App\Models\Compra;
use App\Models\Impuesto;
use App\Models\Insumo;
use App\Models\Proveedor;
use App\Models\TasaCambio;
use App\Models\TipoInsumo;
use App\Services\CompraService;
use App\Support\CatalogoGeografico;
use App\Support\ExistenciasInsumo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use PDF;

class CompraController extends Controller
{
    use RespondeSegunCliente;

    public function __construct(private CompraService $service) {}

    /**
     * Página Inertia con tres vistas (?vista=activas|anuladas|existencias).
     * Solo se consulta la vista activa; el detalle de una compra llega por
     * recarga parcial (?ver=ID).
     */
    public function index(Request $request): Response|RedirectResponse
    {
        // Entrada vieja de «Crear compra con faltantes» (proyeccion-insumos.js).
        if ($request->query('prefill') === '1') {
            return redirect()->route('compras.create', ['prefill' => 1]);
        }

        $filtros = array_filter($request->only(['vista', 'buscar', 'estado', 'proveedor', 'desde', 'hasta', 'tipo_insumo', 'alerta', 'ver']), fn ($v) => $v !== null && $v !== '');
        if ($request->boolean('anuladas')) {
            $filtros['vista'] = 'anuladas'; // enlace viejo ?anuladas=1
        }
        $vista = in_array($filtros['vista'] ?? null, ['anuladas', 'existencias'], true) ? $filtros['vista'] : 'activas';

        return Inertia::render('Compras/Index', [
            'vista' => $vista,
            'filtros' => (object) $filtros,
            'compras' => fn () => $vista !== 'existencias' ? $this->compras($filtros, $vista) : null,
            'existencias' => fn () => $vista === 'existencias' ? ExistenciasInsumo::paginar($filtros) : null,
            'detalle' => fn () => ($c = isset($filtros['ver']) ? Compra::find($filtros['ver']) : null) ? $this->detalle($c) : null,
            'proveedores' => Proveedor::with('persona')->where('estado', 1)->get()
                ->map(fn (Proveedor $p) => ['id' => $p->id, 'nombre' => $p->nombre_completo ?? '—'])
                ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)->values()->all(),
            'tiposInsumo' => ExistenciasInsumo::tipos(),
            'urls' => [
                'index' => route('compras.index', absolute: false),
                'crear' => route('compras.create', absolute: false),
                'reportePdf' => route('compras.reporte.pdf', absolute: false),
            ],
        ]);
    }

    private function compras(array $f, string $vista)
    {
        $q = Compra::with(['proveedor.persona', 'registradoPor:id,name', 'anuladoPor:id,name', 'detalles'])
            ->orderByDesc('fecha_compra')->orderByDesc('id');

        if ($vista === 'anuladas') {
            $q->where('estado', 'anulada');
        } elseif (in_array($f['estado'] ?? null, ['borrador', 'recibida'], true)) {
            $q->where('estado', $f['estado']);
        } else {
            $q->whereIn('estado', ['borrador', 'recibida']);
        }
        if (! empty($f['proveedor'])) {
            $q->where('proveedor_id', $f['proveedor']);
        }
        if (! empty($f['desde'])) {
            $q->whereDate('fecha_compra', '>=', $f['desde']);
        }
        if (! empty($f['hasta'])) {
            $q->whereDate('fecha_compra', '<=', $f['hasta']);
        }
        // «Contiene» sobre lo que se ve en la fila: factura, número, proveedor (nombre o documento).
        if (! empty($f['buscar'])) {
            $kw = trim($f['buscar']);
            $q->where(fn ($w) => $w->where('numero_factura', 'like', "%{$kw}%")
                ->orWhere('compra.id', ltrim($kw, '#'))
                ->orWhereHas('proveedor.persona', fn ($p) => $p->where('nombre', 'like', "%{$kw}%")
                    ->orWhereRaw('CONCAT(tipo_documento, documento_identidad) like ?', ["%{$kw}%"])));
        }

        return $q->paginate(15)->withQueryString()->through(fn (Compra $c) => [
            'id' => $c->id,
            'numero_factura' => $c->numero_factura,
            'proveedor' => $c->proveedor?->nombre_completo,
            'proveedor_doc' => $c->proveedor?->documento,
            'fecha' => $c->fecha_compra?->toDateString(),
            'total' => (float) $c->total,
            'total_bs' => $this->montosBs($c)['total'],
            'estado' => $c->estado,
            'clonada' => (bool) $c->clonada,
            'registrado_por' => $c->registradoPor?->name,
            'anulado_por' => $c->anuladoPor?->name,
            'fecha_anulacion' => $c->fecha_anulacion?->format('Y-m-d H:i'),
        ]);
    }

    /**
     * Montos en bolívares (lo efectivamente pagado), del costo en Bs tecleado
     * por línea. El IVA en Bs se aplica solo a la base gravada.
     */
    private function montosBs(Compra $c): array
    {
        $subtotal = $c->detalles->sum(fn ($d) => (float) $d->cantidad * (float) $d->costo_unitario_bs);
        $gravado = $c->detalles->where('aplica_iva', true)->sum(fn ($d) => (float) $d->cantidad * (float) $d->costo_unitario_bs);
        $iva = round($gravado * (float) $c->iva_porcentaje / 100, 2);

        return ['subtotal' => round($subtotal, 2), 'iva' => $iva, 'total' => round($subtotal + $iva, 2)];
    }

    private function detalle(Compra $c): array
    {
        $c->load(['proveedor.persona', 'detalles.insumo', 'registradoPor', 'anuladoPor:id,name']);
        $bs = $this->montosBs($c);

        return [
            'id' => $c->id,
            'estado' => $c->estado,
            'clonada' => (bool) $c->clonada,
            'numero_factura' => $c->numero_factura,
            'fecha' => $c->fecha_compra?->toDateString(),
            'observaciones' => $c->observaciones,
            'subtotal' => (float) $c->subtotal,
            'iva' => (float) $c->iva,
            'iva_porcentaje' => (float) $c->iva_porcentaje,
            'total' => (float) $c->total,
            'subtotal_bs' => $bs['subtotal'],
            'iva_bs' => $bs['iva'],
            'total_bs' => $bs['total'],
            'tasa' => $c->tasa_cambio ? (float) $c->tasa_cambio : null,
            // Fecha de la tasa BCV aplicada; null si el valor no coincide (tasa manual).
            'tasa_fecha' => TasaCambio::fechaParaValor($c->tasa_cambio, $c->fecha_compra?->toDateString())?->toDateString(),
            'creado' => $c->created_at?->format('Y-m-d H:i'),
            'proveedor' => $c->proveedor ? $this->proveedorResumen($c->proveedor) : null,
            'registrado_por' => ['nombre' => $c->registradoPor?->name ?? 'Sistema', 'avatar' => $c->registradoPor?->avatar_url],
            'anulado_por' => $c->anuladoPor?->name,
            'fecha_anulacion' => $c->fecha_anulacion?->format('Y-m-d H:i'),
            'items' => $c->detalles->map(fn ($d) => [
                'id' => $d->id,
                'insumo' => $d->insumo?->nombre ?? 'N/A',
                'codigo' => $d->insumo?->codigo,
                'unidad' => $d->insumo?->unidad_medida,
                'cantidad' => (float) $d->cantidad,
                'costo' => (float) $d->costo_unitario,
                'costo_bs' => (float) $d->costo_unitario_bs,
                'subtotal' => (float) $d->subtotal,
                'subtotal_bs' => round((float) $d->cantidad * (float) $d->costo_unitario_bs, 2),
                'aplica_iva' => (bool) $d->aplica_iva,
            ])->all(),
        ];
    }

    /** Mismo formato que proveedores.search y el alta rápida de proveedores. */
    private function proveedorResumen(Proveedor $p): array
    {
        return [
            'id' => $p->id,
            'nombre' => $p->nombre_completo ?? '—',
            'doc' => $p->documento ?? '',
            'tel' => $p->telefono_unificado ?? '',
            'email' => $p->email_unificado ?? '',
            'tipo' => $p->tipo_proveedor ?? '',
        ];
    }

    public function create(): Response
    {
        return $this->formulario(null);
    }

    public function edit(Compra $compra): Response|RedirectResponse
    {
        if ($compra->estado !== 'borrador') {
            return redirect()->route('compras.index')->with('error', 'Solo se pueden editar borradores.');
        }

        return $this->formulario($compra->load(['detalles', 'proveedor.persona']));
    }

    /** Formulario de compra (alta o edición de un borrador), en su propia página. */
    private function formulario(?Compra $compra): Response
    {
        return Inertia::render('Compras/Formulario', [
            'compra' => $compra ? [
                'id' => $compra->id,
                'proveedor' => $compra->proveedor ? $this->proveedorResumen($compra->proveedor) : null,
                'numero_factura' => $compra->numero_factura,
                'fecha_compra' => $compra->fecha_compra?->toDateString(),
                'tasa_cambio' => (float) $compra->tasa_cambio,
                'observaciones' => $compra->observaciones,
                'items' => $compra->detalles->map(fn ($d) => [
                    'insumo_id' => $d->insumo_id,
                    'cantidad' => (float) $d->cantidad,
                    'costo_unitario_bs' => (float) $d->costo_unitario_bs,
                    'aplica_iva' => (bool) $d->aplica_iva,
                ])->all(),
            ] : null,
            'insumos' => fn () => Insumo::where('estado', 1)->where('is_inventoriable', 1)->orderBy('nombre')
                ->get(['id', 'nombre', 'codigo', 'tipo', 'unidad_medida', 'costo_unitario', 'aplica_iva', 'stock_actual'])
                ->map(fn (Insumo $i) => [
                    'id' => $i->id,
                    'nombre' => $i->nombre,
                    'codigo' => $i->codigo,
                    'tipo' => $i->tipo,
                    'unidad' => $i->unidad_medida,
                    'costo' => (float) $i->costo_unitario,
                    'aplica_iva' => (bool) $i->aplica_iva,
                    'stock' => (float) $i->stock_actual,
                ])->all(),
            'iva' => Impuesto::tasaIva(),
            // Alta rápida de insumos y proveedores sin salir de la compra.
            'tiposInsumo' => TipoInsumo::where('activo', true)->orderBy('nombre')->pluck('nombre')->all(),
            'unidades' => GuardarInsumoRequest::UNIDADES,
            'estados' => CatalogoGeografico::mapa(),
            'urls' => [
                'index' => route('compras.index', absolute: false),
                'guardar' => $compra ? route('compras.update', $compra, absolute: false) : route('compras.store', absolute: false),
                'tasa' => route('compras.tasa', absolute: false),
                'buscarProveedor' => route('proveedores.search', absolute: false),
                'buscarPersona' => route('personas.search', absolute: false),
                'proveedores' => route('proveedores.index', absolute: false),
                'desdePersona' => url('/proveedores/from-persona'),
                'checkDocumento' => route('proveedores.check-documento', absolute: false),
                'checkRif' => route('proveedores.check-rif', absolute: false),
                'checkEmail' => route('proveedores.check-email', absolute: false),
                'insumos' => route('insumos.index', absolute: false),
                'checkNombre' => route('insumos.check-nombre', absolute: false),
            ],
        ]);
    }

    /**
     * Devuelve la tasa BCV (USD/VES) vigente para una fecha, para que el wizard
     * la precargue al elegir la fecha de compra. Si no hay tasa del día exacto,
     * cae a la última publicada antes de esa fecha (que es la que rige).
     */
    public function getTasa(Request $request)
    {
        $fecha = $request->input('fecha', now()->toDateString());

        // Validación liviana del formato de fecha; si no parsea, usamos hoy.
        try {
            $fecha = \Carbon\Carbon::parse($fecha)->toDateString();
        } catch (\Exception $e) {
            $fecha = now()->toDateString();
        }

        $tasa = TasaCambio::tasaVigente($fecha);

        if (!$tasa) {
            return response()->json([
                'encontrada' => false,
                'message'    => 'No hay tasa BCV registrada para esa fecha. Ingrésala manualmente.',
            ]);
        }

        return response()->json([
            'encontrada'    => true,
            'exacta'        => $tasa->fecha_bcv->toDateString() === $fecha,
            'valor'         => (float) $tasa->valor,
            'fecha_bcv'     => $tasa->fecha_bcv->format('Y-m-d'),
            'fecha_bcv_fmt' => $tasa->fecha_bcv->format('d/m/Y'),
        ]);
    }

    public function store(StoreCompraRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $compra = $this->service->registrar($request->validated(), Auth::id());
        } catch (\Exception $e) {
            Log::error('CompraController@store: '.$e->getMessage(), ['user' => Auth::id()]);

            return $this->rechazar($request, 'Ocurrió un error interno al registrar la compra. Intente nuevamente.', 500);
        }

        $mensaje = "Borrador de compra #{$compra->id} guardado. Procésalo cuando estés listo.";

        return $this->esInertia($request)
            ? redirect()->route('compras.index')->with('success', $mensaje)
            : response()->json(['success' => true, 'message' => $mensaje, 'compra_id' => $compra->id]);
    }

    public function update(StoreCompraRequest $request, Compra $compra): JsonResponse|RedirectResponse
    {
        try {
            $this->service->actualizar($compra, $request->validated());
        } catch (\RuntimeException $e) {
            return $this->rechazar($request, $e->getMessage());
        } catch (\Exception $e) {
            Log::error('CompraController@update: '.$e->getMessage(), ['user' => Auth::id()]);

            return $this->rechazar($request, 'Error al actualizar la compra.', 500);
        }

        $mensaje = "Compra #{$compra->id} actualizada correctamente.";

        return $this->esInertia($request)
            ? redirect()->route('compras.index')->with('success', $mensaje)
            : response()->json(['success' => true, 'message' => $mensaje]);
    }

    public function procesar(Request $request, Compra $compra): JsonResponse|RedirectResponse
    {
        return $this->ejecutar($request, 'procesar', fn () => $this->service->procesar($compra, Auth::id()),
            "Compra #{$compra->id} procesada. Stock de insumos actualizado.");
    }

    public function anular(Request $request, Compra $compra): JsonResponse|RedirectResponse
    {
        return $this->ejecutar($request, 'anular', fn () => $this->service->anular($compra, Auth::id()),
            "Compra #{$compra->id} anulada. El stock ha sido revertido.");
    }

    public function clonar(Request $request, Compra $compra): JsonResponse|RedirectResponse
    {
        $nueva = null;

        return $this->ejecutar($request, 'clonar', function () use ($compra, &$nueva) {
            $nueva = $this->service->clonar($compra, Auth::id());
        }, function () use (&$nueva) { // por referencia: $nueva existe recién tras clonar
            return "Compra clonada como borrador #{$nueva->id}. Revísala y procésala.";
        }, function () use (&$nueva) {
            return ['compra_id' => $nueva->id];
        });
    }

    public function destroy(Request $request, Compra $compra): JsonResponse|RedirectResponse
    {
        return $this->ejecutar($request, 'eliminar', fn () => $this->service->eliminar($compra),
            "Borrador #{$compra->id} eliminado correctamente.");
    }

    /**
     * Acción de estado: las reglas del servicio (RuntimeException) llegan como
     * aviso de error; un fallo inesperado se registra y se informa genérico.
     */
    private function ejecutar(Request $request, string $accion, callable $hacer, string|\Closure $mensaje, ?\Closure $extra = null): JsonResponse|RedirectResponse
    {
        try {
            $hacer();
        } catch (\RuntimeException $e) {
            return $this->rechazar($request, $e->getMessage());
        } catch (\Exception $e) {
            Log::error("CompraController@{$accion}: ".$e->getMessage(), ['user' => Auth::id()]);

            return $this->rechazar($request, "Error al {$accion} la compra.", 500);
        }

        $texto = $mensaje instanceof \Closure ? $mensaje() : $mensaje;

        return $this->responder($request, $texto, ['success' => true, 'message' => $texto, ...($extra ? $extra() : [])]);
    }

    public function reportePdf(Request $request)
    {
        $query = Compra::with(['proveedor.persona', 'registradoPor:id,name']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('proveedor_id')) {
            $query->where('proveedor_id', $request->proveedor_id);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_compra', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_compra', '<=', $request->fecha_hasta);
        }

        // Orden del reporte.
        $orden = $request->input('orden', 'recientes');
        switch ($orden) {
            case 'monto_desc':
                $query->orderByDesc('total')->orderByDesc('id');
                break;
            case 'monto_asc':
                $query->orderBy('total')->orderByDesc('id');
                break;
            default:
                $orden = 'recientes';
                $query->orderByDesc('fecha_compra')->orderByDesc('id');
                break;
        }

        $compras = $query->get();

        $filtros = [];
        if ($request->filled('estado')) {
            $filtros['Estado'] = ucfirst($request->estado);
        }
        if ($request->filled('proveedor_id')) {
            $filtros['Proveedor'] = optional(\App\Models\Proveedor::find($request->proveedor_id))->nombre
                ?? ('#' . $request->proveedor_id);
        }
        if ($rango = \App\Support\ReporteFiltros::rango($request->fecha_desde, $request->fecha_hasta)) {
            $filtros['Fecha de compra'] = $rango;
        }
        $filtros['Orden'] = ['recientes' => 'Fecha reciente', 'monto_desc' => 'Mayor monto', 'monto_asc' => 'Menor monto'][$orden];

        $pdf = PDF::loadView('admin.compras.reporte_pdf', compact('compras', 'filtros'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('reporte_compras_' . now()->format('Ymd_His') . '.pdf');
    }

    public function compraPdf(Compra $compra)
    {
        $compra->load(['proveedor.persona', 'detalles.insumo', 'registradoPor:id,name']);

        // Fecha de la tasa BCV aplicada; null si el snapshot no coincide con la
        // tasa vigente a la fecha de la compra (tasa manual) — no se muestra.
        $tasaFecha = TasaCambio::fechaParaValor($compra->tasa_cambio, $compra->fecha_compra?->toDateString());

        $pdf = PDF::loadView('admin.compras.comprobante', compact('compra', 'tasaFecha'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('compra_' . str_pad($compra->id, 5, '0', STR_PAD_LEFT) . '.pdf');
    }
}
