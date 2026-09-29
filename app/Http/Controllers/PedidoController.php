<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Http\Requests\GuardarPedidoRequest;
use App\Models\Banco;
use App\Models\Cotizacion;
use App\Models\Impuesto;
use App\Models\PagoPedido;
use App\Models\Pedido;
use App\Models\TasaCambio;
use App\Services\PedidoService;
use App\Support\FiltrosUrl;
use App\Support\GruposCotizacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use PDF;

/**
 * Pedidos (Inertia): listado con «Ver» por recarga parcial y el asistente
 * Cliente → Productos → Pago → Resumen en su propia página. Un pedido solo nace
 * de una cotización Aprobada (sus líneas se copian en el servidor); después se
 * editan pagos, entrega y prioridad.
 */
class PedidoController extends Controller
{
    use RespondeSegunCliente;

    public const ESTADOS = ['Pendiente', 'Procesando', 'Completado', 'Cancelado'];

    public function __construct(
        private PedidoService $pedidoService
    ) {
    }

    public function index(Request $request): Response|RedirectResponse
    {
        // Enlace viejo desde Cotizaciones: /pedidos?convertir=ID → el asistente.
        if ($request->filled('convertir') && ctype_digit((string) $request->input('convertir'))) {
            return redirect()->route('pedidos.create', ['cotizacion' => (int) $request->input('convertir')]);
        }

        $filtros = FiltrosUrl::de($request, ['buscar', 'estado', 'desde', 'hasta', 'orden', 'ver'], ['desde', 'hasta']);
        if (isset($filtros['estado']) && ! in_array($filtros['estado'], self::ESTADOS, true)) {
            unset($filtros['estado']);
        }

        return Inertia::render('Pedidos/Index', [
            'registros' => fn () => $this->listado($filtros),
            'filtros' => (object) $filtros,
            'detalle' => fn () => isset($filtros['ver']) && ($p = Pedido::find((int) $filtros['ver'])) ? $this->detalle($p) : null,
            'estados' => self::ESTADOS,
            'terminos' => $this->terminos(),
            'urls' => [
                'index' => route('pedidos.index', absolute: false),
                'crear' => route('pedidos.create', absolute: false),
                'reportePdf' => route('pedidos.reporte.pdf', absolute: false),
            ],
        ]);
    }

    private function listado(array $f)
    {
        $q = Pedido::query()
            ->with(['cliente' => fn ($c) => $c->withTrashed()->with('persona'), 'cotizacion:id,tasa_cambio_valor,fecha_cotizacion,created_at'])
            ->withCount(['ordenes as ordenes_activas' => fn ($o) => $o->where('estado', '!=', 'Cancelado')])
            ->select('pedido.*');

        if (isset($f['estado'])) {
            $q->where('pedido.estado', $f['estado']);
        }
        // El rango filtra la fecha de entrega (lo que se planifica).
        if (isset($f['desde'])) {
            $q->whereDate('pedido.fecha_entrega_estimada', '>=', $f['desde']);
        }
        if (isset($f['hasta'])) {
            $q->whereDate('pedido.fecha_entrega_estimada', '<=', $f['hasta']);
        }
        if (isset($f['buscar'])) {
            $kw = '%'.FiltrosUrl::like($f['buscar']).'%';
            $numero = ltrim(trim($f['buscar']), '#');
            $q->where(fn ($w) => $w->where('pedido.estado', 'like', $kw)
                ->orWhere('pedido.prioridad', 'like', $kw)
                ->when(ctype_digit($numero), fn ($w) => $w->orWhere('pedido.id', (int) $numero)->orWhere('pedido.cotizacion_id', (int) $numero))
                ->orWhereHas('cliente', fn ($c) => $c->withTrashed()->whereHas('persona', fn ($p) => $p
                    ->where('nombre', 'like', $kw)
                    ->orWhereRaw('CONCAT(tipo_documento, documento_identidad) like ?', [$kw]))));
        }

        match ($f['orden'] ?? 'recientes') {
            'monto_desc' => $q->orderByDesc('pedido.total')->orderByDesc('pedido.id'),
            'entrega_asc' => $q->orderByRaw('pedido.fecha_entrega_estimada IS NULL')->orderBy('pedido.fecha_entrega_estimada')->orderByDesc('pedido.id'),
            default => $q->orderByDesc('pedido.created_at')->orderByDesc('pedido.id'),
        };

        return $q->paginate(15)->appends(Arr::except($f, ['ver']))->through(fn (Pedido $p) => $this->fila($p));
    }

    /** Espejo de `PedidoFila` en resources/js/pages/Pedidos/tipos.ts (lo verifica PedidosPaginaTest). */
    private function fila(Pedido $p): array
    {
        return [
            'id' => $p->id,
            'cotizacion_id' => $p->cotizacion_id,
            'cliente' => $p->cliente?->nombre ?? 'Cliente no encontrado',
            'cliente_doc' => $p->cliente?->documento,
            'cliente_inhabilitado' => (bool) $p->cliente?->trashed(),
            'fecha' => $p->fecha_pedido?->toDateString(),
            'entrega' => $p->fecha_entrega_estimada?->toDateString(),
            'total' => (float) $p->total,
            'abono' => (float) $p->abono,
            'porcentaje_abonado' => round($p->porcentajeAbonado(), 1),
            'tasa' => $this->tasa($p),
            'estado' => $p->estado,
            'prioridad' => $p->prioridad ?? 'Normal',
            'formalizado' => $p->estaFormalizado(),
            'con_produccion' => (int) ($p->ordenes_activas ?? $p->ordenes()->where('estado', '!=', 'Cancelado')->count()) > 0,
        ];
    }

    /** Tasa del pedido: la de su cotización, con su fecha BCV si el valor coincide. */
    private function tasa(Pedido $p): ?array
    {
        $c = $p->cotizacion;
        if (! $c || (float) $c->tasa_cambio_valor <= 0) {
            return null;
        }
        $fecha = TasaCambio::fechaParaValor($c->tasa_cambio_valor, ($c->fecha_cotizacion ?? $c->created_at)?->toDateString());

        return ['valor' => (float) $c->tasa_cambio_valor, 'fecha' => $fecha?->toDateString()];
    }

    /** Espejo de `PedidoDetalle` (tipos.ts): lo que muestra «Ver». */
    private function detalle(Pedido $p): array
    {
        GruposCotizacion::cargar($p)->load(['cliente' => fn ($q) => $q->withTrashed()->with('persona'), 'user:id,name,avatar', 'pagos.banco' => fn ($q) => $q->withTrashed(), 'cotizacion']);

        return [
            ...$this->fila($p),
            'formalizacion' => $p->fecha_formalizacion?->toDateString(),
            'cliente_datos' => $p->cliente?->resumenParaCotizacion(),
            'creador' => $p->user ? ['nombre' => $p->user->name, 'avatar' => $p->user->avatar ? $p->user->avatar_url : null, 'fecha' => $p->created_at?->format('Y-m-d H:i')] : null,
            'grupos' => GruposCotizacion::desde($p->productos),
            'pagos' => $this->pagos($p),
        ];
    }

    private function pagos(Pedido $p): array
    {
        return $p->pagos->map(fn (PagoPedido $x) => [
            'metodo' => $x->metodo,
            'monto' => (float) $x->monto,
            'banco_id' => $x->banco_id,
            'banco' => $x->banco?->nombre,
            'referencia' => $x->referencia,
        ])->values()->all();
    }

    private function terminos(): array
    {
        return ['abono' => Pedido::porcentajeAbonoMinimo(), 'dias' => Pedido::diasHabilesEntrega()];
    }

    /** Cotizaciones que se pueden convertir: Aprobadas, vigentes y sin pedido. */
    private function cotizacionesDisponibles(): array
    {
        Cotizacion::actualizarCotizacionesVencidas();

        return Cotizacion::with(['cliente' => fn ($q) => $q->withTrashed()->with('persona')])
            ->withCount('productos')
            ->where('estado', 'Aprobada')
            ->doesntHave('pedido')
            ->orderByDesc('fecha_cotizacion')->orderByDesc('id')
            ->get()
            ->map(fn (Cotizacion $c) => [
                'id' => $c->id,
                'cliente' => $c->cliente?->nombre ?? 'Cliente no encontrado',
                'cliente_doc' => $c->cliente?->documento,
                'fecha' => $c->fecha_cotizacion?->toDateString(),
                'validez' => $c->fechaLimiteVigencia()?->toDateString(),
                'total' => (float) $c->total,
                'tasa' => (float) $c->tasa_cambio_valor > 0 ? (float) $c->tasa_cambio_valor : null,
                'prioridad' => $c->prioridad ?? 'Normal',
                'lineas' => (int) $c->productos_count,
            ])->all();
    }

    /** La cotización elegida en el asistente: cliente, productos (solo lectura) y total. */
    private function cotizacionElegida(Cotizacion $c): array
    {
        GruposCotizacion::cargar($c)->load(['cliente' => fn ($q) => $q->withTrashed()->with('persona')]);
        $tasa = (float) $c->tasa_cambio_valor > 0
            ? ['valor' => (float) $c->tasa_cambio_valor, 'fecha' => TasaCambio::fechaParaValor($c->tasa_cambio_valor, ($c->fecha_cotizacion ?? $c->created_at)?->toDateString())?->toDateString()]
            : null;

        return [
            'id' => $c->id,
            'estado' => $c->estado,
            'fecha' => $c->fecha_cotizacion?->toDateString(),
            'validez' => $c->fechaLimiteVigencia()?->toDateString(),
            'total' => (float) $c->total,
            'tasa' => $tasa,
            'prioridad' => $c->prioridad ?? 'Normal',
            'cliente' => $c->cliente?->resumenParaCotizacion(),
            'grupos' => GruposCotizacion::desde($c->productos),
        ];
    }

    private function propsFormulario(?Pedido $pedido, ?int $cotizacionId): array
    {
        return [
            'pedido' => $pedido ? [
                ...$this->detalle($pedido),
                // Lo mínimo que puede quedar abonado al editar (pedidos legacy con abono bajo).
                'abono_minimo' => min(round((float) $pedido->total * Pedido::porcentajeAbonoMinimo() / 100, 2), (float) $pedido->abono),
            ] : null,
            'cotizaciones' => fn () => $pedido ? [] : $this->cotizacionesDisponibles(),
            'cotizacion' => fn () => ! $pedido && $cotizacionId && ($c = Cotizacion::find($cotizacionId)) ? $this->cotizacionElegida($c) : null,
            'cotizacionPedida' => $pedido ? null : $cotizacionId,
            // Activos, más los inhabilitados que ya usan los pagos de este pedido.
            'bancos' => Banco::withTrashed()
                ->where(fn ($q) => $q->whereNull('deleted_at')->when($pedido, fn ($w) => $w->orWhereIn('id', $pedido->pagos()->whereNotNull('banco_id')->pluck('banco_id'))))
                ->orderBy('nombre')->get(['id', 'nombre', 'deleted_at'])
                ->map(fn ($b) => ['id' => $b->id, 'nombre' => $b->nombre.($b->deleted_at ? ' (inhabilitado)' : '')])->all(),
            'metodos' => PagoPedido::METODOS,
            'terminos' => $this->terminos(),
            'hoy' => now()->toDateString(),
            'entregaPropuesta' => now()->addWeekdays(Pedido::diasHabilesEntrega())->toDateString(),
            'urls' => [
                'index' => route('pedidos.index', absolute: false),
                'crear' => route('pedidos.create', absolute: false),
                'guardar' => $pedido ? route('pedidos.update', $pedido, absolute: false) : route('pedidos.store', absolute: false),
                'proyeccion' => route('pedidos.proyeccionInsumos', absolute: false),
                'crearCompra' => tienePermiso('compras.gestionar') ? route('compras.create', absolute: false) : null,
                'cotizaciones' => route('cotizaciones.index', absolute: false),
            ],
        ];
    }

    /** Enlace viejo a la ficha (antes devolvía JSON): abre el «Ver» del listado. */
    public function show(int $pedido): RedirectResponse
    {
        return redirect()->route('pedidos.index', ['ver' => $pedido]);
    }

    public function create(Request $request): Response
    {
        $cotizacion = ctype_digit((string) $request->input('cotizacion')) ? (int) $request->input('cotizacion') : null;

        return Inertia::render('Pedidos/Formulario', $this->propsFormulario(null, $cotizacion));
    }

    public function edit(Pedido $pedido): Response|RedirectResponse
    {
        if ($pedido->estado === 'Cancelado') {
            return redirect()->route('pedidos.index', ['ver' => $pedido->id])->with('error', 'Un pedido cancelado no se edita: reactívalo primero.');
        }

        return Inertia::render('Pedidos/Formulario', $this->propsFormulario($pedido, null));
    }

    /** Regla de negocio que impide guardar: error del formulario (Inertia) o 422 {error} (JSON). */
    private function fallar(Request $request, string $mensaje): JsonResponse
    {
        if ($this->esInertia($request)) {
            throw ValidationException::withMessages(['general' => $mensaje]);
        }

        return response()->json(['error' => $mensaje], 422);
    }

    public function store(GuardarPedidoRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $pedido = $this->pedidoService->crearDesdeCotizacion($request->validated());
        } catch (\InvalidArgumentException $e) {
            return $this->fallar($request, $e->getMessage());
        }

        if ($this->esInertia($request)) {
            return redirect()->route('pedidos.index', ['ver' => $pedido->id])->with('success', "Pedido #{$pedido->id} creado.");
        }

        return response()->json(['success' => 'Pedido creado exitosamente.', 'pedido_id' => $pedido->id]);
    }

    public function update(GuardarPedidoRequest $request, Pedido $pedido): JsonResponse|RedirectResponse
    {
        try {
            $this->pedidoService->actualizar($pedido, $request->validated());
        } catch (\InvalidArgumentException $e) {
            return $this->fallar($request, $e->getMessage());
        }

        if ($this->esInertia($request)) {
            return redirect()->route('pedidos.index', ['ver' => $pedido->id])->with('success', "Pedido #{$pedido->id} actualizado.");
        }

        return response()->json(['success' => 'Pedido actualizado exitosamente.']);
    }

    public function destroy(Request $request, Pedido $pedido): JsonResponse|RedirectResponse
    {
        if (in_array($pedido->estado, ['Completado', 'Cancelado'], true)) {
            return $this->rechazar($request, 'No se puede eliminar un pedido completado o cancelado.', 403);
        }

        // El servicio hace el soft delete y revierte la cotización de origen
        // ('Convertida' → 'Aprobada') liberando su cotizacion_id para poder
        // re-convertirla más adelante.
        try {
            $this->pedidoService->eliminar($pedido);
        } catch (\DomainException $e) {
            return $this->rechazar($request, $e->getMessage(), 403);
        }

        Log::warning('Pedido eliminado', ['pedido_id' => $pedido->id, 'cliente_id' => $pedido->cliente_id, 'total' => $pedido->total, 'user_id' => auth()->id()]);

        return $this->responder($request, "Pedido #{$pedido->id} eliminado.", ['success' => 'Pedido eliminado exitosamente.']);
    }

    /**
     * Cancelar un pedido (acción manual). Cancelado es terminal: el estado deja
     * de auto-recalcularse desde producción hasta que se reactive.
     */
    public function cancelar(Request $request, Pedido $pedido): JsonResponse|RedirectResponse
    {
        if ($pedido->estado === 'Completado') {
            return $this->rechazar($request, 'No se puede cancelar un pedido completado.');
        }
        if ($pedido->estado === 'Cancelado') {
            return $this->responder($request, 'El pedido ya estaba cancelado.', ['success' => 'El pedido ya estaba cancelado.']);
        }
        // Con el pedido bloqueado: crear una orden toma el mismo bloqueo, así que
        // una orden creada a la vez no queda colgando de un pedido cancelado.
        $cancelado = DB::transaction(function () use ($pedido) {
            Pedido::whereKey($pedido->id)->lockForUpdate()->first();
            if ($pedido->tieneProduccionEnCurso()) {
                return false;
            }
            $pedido->update(['estado' => 'Cancelado']);

            return true;
        });
        if (! $cancelado) {
            return $this->rechazar($request, 'No se puede cancelar el pedido: tiene órdenes de producción activas o en proceso. Cancela primero esas órdenes desde el módulo de Producción.');
        }

        Log::warning('Pedido cancelado', ['pedido_id' => $pedido->id, 'user_id' => auth()->id()]);

        return $this->responder($request, "Pedido #{$pedido->id} cancelado.", ['success' => 'Pedido cancelado.']);
    }

    /** Reactivar un pedido cancelado: vuelve a quedar gobernado por producción. */
    public function reactivar(Request $request, Pedido $pedido): JsonResponse|RedirectResponse
    {
        if ($pedido->estado !== 'Cancelado') {
            return $this->rechazar($request, 'Solo se puede reactivar un pedido cancelado.');
        }

        $pedido->update(['estado' => 'Pendiente']);
        $pedido->recalcularEstado();

        return $this->responder($request, "Pedido #{$pedido->id} reactivado.", ['success' => 'Pedido reactivado.']);
    }

    public function reportePdf(Request $request)
    {
        $query = Pedido::with(['user:id,name', 'cliente', 'cliente.persona']);
        if ($request->filled('estado') && is_string($request->estado)) {
            $query->where('estado', $request->estado);
        }
        // Cliente: coincidencia parcial por nombre/razón social o documento.
        if ($request->filled('cliente') && is_string($request->cliente)) {
            $term = '%'.FiltrosUrl::like(trim($request->cliente)).'%';
            $query->whereHas('cliente', function ($c) use ($term) {
                $c->withTrashed()->whereHas('persona', function ($p) use ($term) {
                    $p->where('nombre', 'like', $term)->orWhere('documento_identidad', 'like', $term);
                });
            });
        }
        $desde = FiltrosUrl::fecha($request->fecha_desde);
        $hasta = FiltrosUrl::fecha($request->fecha_hasta);
        if ($desde) {
            $query->whereDate('fecha_entrega_estimada', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('fecha_entrega_estimada', '<=', $hasta);
        }

        // Orden (paridad con el "Ordenar por" del listado en pantalla).
        $orden = $request->input('orden', 'recientes');
        switch ($orden) {
            case 'monto_desc':
                $query->orderBy('total', 'desc');
                break;
            case 'entrega_asc':
                $query->orderBy('fecha_entrega_estimada', 'asc');
                break;
            default:
                $orden = 'recientes';
                $query->orderBy('created_at', 'desc');
                break;
        }

        $pedidos = $query->get();

        $filtros = [];
        if ($request->filled('estado') && is_string($request->estado)) {
            $filtros['Estado'] = $request->estado;
        }
        if ($request->filled('cliente') && is_string($request->cliente)) {
            $filtros['Cliente'] = trim($request->cliente);
        }
        if ($rango = \App\Support\ReporteFiltros::rango($desde, $hasta)) {
            $filtros['Fecha de entrega'] = $rango;
        }
        $filtros['Orden'] = ['recientes' => 'Más recientes', 'monto_desc' => 'Mayor monto', 'entrega_asc' => 'Entrega más próxima'][$orden];

        $pdf = PDF::loadView('admin.pedidos.reporte_pdf', compact('pedidos', 'filtros'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('reporte_pedidos_'.now()->format('Ymd_His').'.pdf');
    }

    public function pedidoPdf(Pedido $pedido)
    {
        $pedido->load(['user:id,name', 'productos.producto', 'productos.tipoProducto', 'productos.genero', 'productos.color', 'productos.talla', 'productos.bordados.logo:id,name', 'cliente', 'cliente.persona', 'cotizacion:id,tasa_cambio_valor,fecha_cotizacion,created_at']);

        // IVA del impuesto configurado (antes 16 % fijo).
        $ivaTasa = Impuesto::tasaIva() / 100;
        $subtotal = $pedido->total;
        $descuento = 0;
        $iva = round(($subtotal - $descuento) * $ivaTasa, 2);
        $totalPagar = round($subtotal - $descuento + $iva, 2);

        // Tasa: la de la cotización de origen, con su fecha solo si el valor
        // coincide con una BCV publicada (fechaParaValor); si no hay, la vigente.
        $tasaValor = optional($pedido->cotizacion)->tasa_cambio_valor;
        if ($tasaValor) {
            $ref = optional(optional($pedido->cotizacion)->fecha_cotizacion ?? optional($pedido->cotizacion)->created_at)->toDateString();
            $tasaFecha = TasaCambio::fechaParaValor($tasaValor, $ref);
        } else {
            $row = TasaCambio::obtenerTasaActual('USD');
            $tasaValor = optional($row)->valor;
            $tasaFecha = optional($row)->fecha_bcv;
        }

        $pdf = PDF::loadView('admin.pedidos.factura', [
            'pedido' => $pedido,
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'iva' => $iva,
            'ivaPorcentaje' => Impuesto::tasaIva(),
            'totalPagar' => $totalPagar,
            'tasaValor' => $tasaValor,
            'tasaFecha' => $tasaFecha,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('pedido_'.$pedido->id.'.pdf');
    }
}
