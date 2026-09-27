<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Empleado;
use App\Models\Insumo;
use App\Models\OrdenProduccion;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class ReportesController extends Controller
{
    /** Nombres de mes en español (la BD/PHP devuelven inglés con date('F')). */
    private const MESES = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    /**
     * Eficiencia (first-pass yield): unidades buenas sobre el total realmente
     * fabricado. `cantidad_defectuosa` ACUMULA los rechazos de calidad y el
     * reproceso vuelve a sumar a `cantidad_producida` (FEAT-006), por lo que
     * producido + defectuoso = intentos totales. Devuelve null cuando aún no
     * hay producción (distinto de 0%, que significa "todo salió defectuoso").
     */
    private static function calcularEficiencia(int $producido, int $defectuoso): ?float
    {
        $intentos = $producido + $defectuoso;

        return $intentos > 0 ? round($producido / $intentos * 100, 2) : null;
    }

    public function produccion()
    {
        $ordenesPorEstado = OrdenProduccion::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();

        // Producción mensual agregada desde las órdenes (por mes de inicio)
        $produccionMensual = OrdenProduccion::select(
            DB::raw('YEAR(fecha_inicio) as anio'),
            DB::raw('MONTH(fecha_inicio) as mes'),
            DB::raw('SUM(cantidad_producida) as total_producido'),
            DB::raw('SUM(cantidad_defectuosa) as total_defectuoso')
        )
            ->whereNotNull('fecha_inicio')
            ->groupBy('anio', 'mes')
            ->orderBy('anio', 'desc')
            ->orderBy('mes', 'desc')
            ->limit(12)
            ->get()
            ->map(function ($fila) {
                $fila->mes_nombre = self::MESES[$fila->mes];
                $fila->eficiencia = self::calcularEficiencia((int) $fila->total_producido, (int) $fila->total_defectuoso);

                return $fila;
            });

        return view('admin.reportes.produccion', compact('ordenesPorEstado', 'produccionMensual'));
    }

    public function eficiencia()
    {
        // Eficiencia agregada POR PEDIDO (unidad de negocio) con drill-down a
        // sus órdenes. La eficiencia del pedido es PONDERADA por unidades
        // (sum/sum), nunca promedio de porcentajes. Las órdenes de variantes
        // dinámicas (FEAT-003) tienen producto_id NULL: el nombre legible sale
        // del accessor nombre_producto (snapshot de la línea del pedido).
        $ordenes = OrdenProduccion::with([
            'producto',
            'detallePedido.tipoProducto',
            'detallePedido.genero',
            'pedido.cliente' => fn ($q) => $q->withTrashed()->with('persona'),
        ])->get();

        $eficienciaPorPedido = $ordenes
            ->groupBy('pedido_id')
            ->map(function ($grupo, $pedidoId) {
                $producido  = (int) $grupo->sum('cantidad_producida');
                $defectuoso = (int) $grupo->sum('cantidad_defectuosa');

                return [
                    'pedido_id'     => $pedidoId,
                    'cliente'       => $grupo->first()->pedido?->cliente?->persona?->nombre_completo ?? 'Sin cliente',
                    'total_ordenes' => $grupo->count(),
                    'solicitado'    => (int) $grupo->sum('cantidad_solicitada'),
                    'producido'     => $producido,
                    'defectuoso'    => $defectuoso,
                    'eficiencia'    => self::calcularEficiencia($producido, $defectuoso),
                    'ordenes'       => $grupo->sortByDesc('id')->values()->map(fn ($o) => [
                        'orden_id'   => $o->id,
                        'producto'   => $o->nombre_producto,
                        'estado'     => $o->estado,
                        'solicitado' => $o->cantidad_solicitada ?? 0,
                        'producido'  => $o->cantidad_producida,
                        'defectuoso' => $o->cantidad_defectuosa,
                        'eficiencia' => self::calcularEficiencia($o->cantidad_producida, $o->cantidad_defectuosa),
                    ]),
                ];
            })
            ->sortByDesc('pedido_id')
            ->values();

        $totalProducido  = (int) $ordenes->sum('cantidad_producida');
        $totalDefectuoso = (int) $ordenes->sum('cantidad_defectuosa');

        $kpis = [
            'eficiencia_global'  => self::calcularEficiencia($totalProducido, $totalDefectuoso),
            'producido'          => $totalProducido,
            'defectuoso'         => $totalDefectuoso,
            'pedidos_total'      => $eficienciaPorPedido->count(),
            'pedidos_produccion' => $eficienciaPorPedido->whereNotNull('eficiencia')->count(),
        ];

        return view('admin.reportes.eficiencia', compact('eficienciaPorPedido', 'kpis'));
    }

    public function insumos()
    {
        $consumoInsumos = DB::table('detalle_orden_insumo')
            ->join('insumo', 'detalle_orden_insumo.insumo_id', '=', 'insumo.id')
            ->join('orden_produccion', 'detalle_orden_insumo.orden_produccion_id', '=', 'orden_produccion.id')
            ->select(
                'insumo.id',
                'insumo.nombre',
                'insumo.tipo',
                'insumo.unidad_medida',
                DB::raw('SUM(detalle_orden_insumo.cantidad_utilizada) as total_utilizado'),
                DB::raw('COUNT(DISTINCT detalle_orden_insumo.orden_produccion_id) as total_ordenes')
            )
            ->groupBy('insumo.id', 'insumo.nombre', 'insumo.tipo', 'insumo.unidad_medida')
            ->orderBy('total_utilizado', 'desc')
            ->get();

        return view('admin.reportes.insumos', compact('consumoInsumos'));
    }

    public function empleados()
    {
        // Reparto real por persona (Brecha B): el pivot orden_produccion_empleado
        // guarda cantidad asignada/producida/defectuosa POR EMPLEADO. Las órdenes
        // previas al reparto no tienen filas pivot → fallback al responsable
        // principal (empleado_id) con los totales de la orden.
        $filasPivot = DB::table('orden_produccion_empleado as ope')
            ->join('orden_produccion as op', 'op.id', '=', 'ope.orden_produccion_id')
            ->whereNull('op.deleted_at')
            ->select(
                'ope.empleado_id',
                'ope.orden_produccion_id as orden_id',
                DB::raw('COALESCE(ope.cantidad, 0) as asignado'),
                'ope.cantidad_producida',
                'ope.cantidad_defectuosa'
            )
            ->get();

        $ordenesConPivot = $filasPivot->pluck('orden_id')->unique();

        $filasLegacy = OrdenProduccion::whereNotNull('empleado_id')
            ->whereNotIn('id', $ordenesConPivot)
            ->get(['id', 'empleado_id', 'cantidad_solicitada', 'cantidad_producida', 'cantidad_defectuosa'])
            ->map(fn ($op) => (object) [
                'empleado_id'         => $op->empleado_id,
                'orden_id'            => $op->id,
                'asignado'            => $op->cantidad_solicitada ?? 0,
                'cantidad_producida'  => $op->cantidad_producida,
                'cantidad_defectuosa' => $op->cantidad_defectuosa,
            ]);

        $filas = $filasPivot->concat($filasLegacy);

        // withTrashed: un empleado inhabilitado conserva su historial productivo.
        $nombres = Empleado::withTrashed()
            ->with('persona')
            ->whereIn('id', $filas->pluck('empleado_id')->unique())
            ->get()
            ->mapWithKeys(fn ($e) => [$e->id => $e->persona->nombre_completo ?? 'Empleado #' . $e->id]);

        $rendimientoEmpleados = $filas
            ->groupBy('empleado_id')
            ->map(function ($grupo, $empleadoId) use ($nombres) {
                $producido  = (int) $grupo->sum('cantidad_producida');
                $defectuoso = (int) $grupo->sum('cantidad_defectuosa');

                return [
                    'empleado_id'      => $empleadoId,
                    'nombre'           => $nombres[$empleadoId] ?? 'Empleado #' . $empleadoId,
                    'total_ordenes'    => $grupo->pluck('orden_id')->unique()->count(),
                    'total_asignado'   => (int) $grupo->sum('asignado'),
                    'total_producido'  => $producido,
                    'total_defectuoso' => $defectuoso,
                    'eficiencia'       => self::calcularEficiencia($producido, $defectuoso),
                ];
            })
            ->sortByDesc('total_producido')
            ->values();

        return view('admin.reportes.empleados', compact('rendimientoEmpleados'));
    }

    /**
     * Reportes Generales: hub central de todos los reportes del sistema.
     * El catálogo vive en config/reportes.php (registry, misma filosofía que
     * config/modulos.php); aquí solo se filtra por existencia de la ruta y
     * por el permiso que esa ruta exige según el registry de módulos.
     */
    public function general()
    {
        $grupos = collect(config('reportes.grupos', []))
            ->map(function ($grupo) {
                $grupo['reportes'] = array_values(array_filter(
                    $grupo['reportes'],
                    function ($reporte) {
                        if (!Route::has($reporte['ruta'])) {
                            return false;
                        }

                        // Sin mapeo en config/modulos.php => denegar por defecto,
                        // igual que el middleware CheckPermiso.
                        $permiso = permisoDeRuta($reporte['ruta']);

                        return $permiso !== null && tienePermiso($permiso);
                    }
                ));

                return $grupo;
            })
            ->filter(fn ($grupo) => count($grupo['reportes']) > 0)
            ->values();

        $inicioMes = now()->startOfMonth();

        $kpis = [
            'pedidos_mes'       => Pedido::where('created_at', '>=', $inicioMes)->count(),
            'cotizaciones_mes'  => Cotizacion::where('created_at', '>=', $inicioMes)->count(),
            'ordenes_activas'   => OrdenProduccion::whereIn('estado', ['Pendiente', 'En Proceso'])->count(),
            'insumos_criticos'  => Insumo::where('is_inventoriable', true)
                ->whereColumn('stock_actual', '<=', 'stock_minimo')
                ->count(),
        ];

        return view('admin.reportes.general', compact('grupos', 'kpis'));
    }
}
