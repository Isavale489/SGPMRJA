<?php

namespace App\Http\Controllers;

use App\Models\RecoveryAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        // Valores por defecto seguros — el dashboard siempre renderiza
        $totalClientes = 0;
        $totalProductos = 0;
        $totalEmpleados = 0;
        $totalProveedores = 0;
        $pedidosLabels = ['Pendiente', 'Procesando', 'Completado', 'Cancelado'];
        $pedidosValues = [0, 0, 0, 0];
        $totalPedidos = 0;
        // KPIs operativos accionables
        $pedidosPorEntregar = 0;
        $insumosStockBajo = 0;
        $cotizacionesPorVencer = 0;
        // Tendencia mensual de pedidos
        $tendenciaLabels = [];
        $tendenciaPedidos = [];
        $tendenciaMontos = [];

        try {
            // 1 query: conteos de maestros usando subqueries
            $counts = DB::selectOne("
                SELECT
                    (SELECT COUNT(*) FROM cliente WHERE deleted_at IS NULL) as clientes,
                    (SELECT COUNT(*) FROM producto WHERE deleted_at IS NULL) as productos,
                    (SELECT COUNT(*) FROM empleado WHERE deleted_at IS NULL) as empleados,
                    (SELECT COUNT(*) FROM proveedor WHERE deleted_at IS NULL) as proveedores
            ");

            $totalClientes = $counts->clientes ?? 0;
            $totalProductos = $counts->productos ?? 0;
            $totalEmpleados = $counts->empleados ?? 0;
            $totalProveedores = $counts->proveedores ?? 0;
        } catch (\Exception $e) {
            \Log::warning('Dashboard: error al consultar conteos de maestros — ' . $e->getMessage());
        }

        try {
            // 1 query: estados de pedidos con SUM(CASE WHEN)
            // NOTA: el enum real es 'Procesando' (no 'En Proceso') — ver migración cr03
            $pedidoStats = DB::selectOne("
                SELECT
                    SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) as pendiente,
                    SUM(CASE WHEN estado = 'Procesando' THEN 1 ELSE 0 END) as procesando,
                    SUM(CASE WHEN estado = 'Completado' THEN 1 ELSE 0 END) as completado,
                    SUM(CASE WHEN estado = 'Cancelado' THEN 1 ELSE 0 END) as cancelado
                FROM pedido WHERE deleted_at IS NULL
            ");

            $pedidosLabels = ['Pendiente', 'Procesando', 'Completado', 'Cancelado'];
            $pedidosValues = [
                (int) ($pedidoStats->pendiente ?? 0),
                (int) ($pedidoStats->procesando ?? 0),
                (int) ($pedidoStats->completado ?? 0),
                (int) ($pedidoStats->cancelado ?? 0),
            ];
            $totalPedidos = array_sum($pedidosValues);

            // 1 query: KPIs operativos accionables (entregas, saldo, stock, cotizaciones)
            $kpis = DB::selectOne("
                SELECT
                    (SELECT COUNT(*) FROM pedido
                        WHERE deleted_at IS NULL AND estado NOT IN ('Completado','Cancelado')
                          AND fecha_entrega_estimada BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                    ) as por_entregar,
                    (SELECT COUNT(*) FROM insumo
                        WHERE deleted_at IS NULL AND estado = 1 AND stock_actual <= stock_minimo
                    ) as stock_bajo,
                    (SELECT COUNT(*) FROM cotizacion
                        WHERE deleted_at IS NULL AND estado = 'Pendiente'
                          AND fecha_validez BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                    ) as cot_vencer
            ");
            $pedidosPorEntregar    = (int) ($kpis->por_entregar ?? 0);
            $insumosStockBajo      = (int) ($kpis->stock_bajo ?? 0);
            $cotizacionesPorVencer = (int) ($kpis->cot_vencer ?? 0);

            // 1 query: tendencia de pedidos por mes (últimos 6 meses)
            $tendencia = DB::select("
                SELECT DATE_FORMAT(fecha_pedido, '%Y-%m') as mes,
                       COUNT(*) as total,
                       COALESCE(SUM(total), 0) as monto
                FROM pedido
                WHERE deleted_at IS NULL
                  AND fecha_pedido >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 5 MONTH)
                GROUP BY mes ORDER BY mes
            ");
            $mesesEs = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            $skeleton = [];
            for ($i = 5; $i >= 0; $i--) {
                $m = now()->subMonths($i);
                $skeleton[$m->format('Y-m')] = [
                    'label' => $mesesEs[(int) $m->format('n')] . ' ' . $m->format('y'),
                    'total' => 0,
                    'monto' => 0,
                ];
            }
            foreach ($tendencia as $row) {
                if (isset($skeleton[$row->mes])) {
                    $skeleton[$row->mes]['total'] = (int) $row->total;
                    $skeleton[$row->mes]['monto'] = round((float) $row->monto, 2);
                }
            }
            $tendenciaLabels  = array_values(array_column($skeleton, 'label'));
            $tendenciaPedidos = array_values(array_column($skeleton, 'total'));
            $tendenciaMontos  = array_values(array_column($skeleton, 'monto'));

        } catch (\Exception $e) {
            \Log::warning('Dashboard: error al consultar datos operativos — ' . $e->getMessage());
        }

        // Notificación: intento de recuperación reciente para el usuario actual
        $recoveryAlert = $this->getRecoveryAlert();

        return view('dashboard', compact(
            'totalClientes',
            'totalProductos',
            'totalEmpleados',
            'totalProveedores',
            'pedidosLabels',
            'pedidosValues',
            'totalPedidos',
            'pedidosPorEntregar',
            'insumosStockBajo',
            'cotizacionesPorVencer',
            'tendenciaLabels',
            'tendenciaPedidos',
            'tendenciaMontos',
            'recoveryAlert'
        ));
    }

    /**
     * Devuelve el último intento de recuperación posterior al login del usuario,
     * para mostrarlo como banner informativo. Solo se muestra una vez por sesión.
     */
    private function getRecoveryAlert(): ?array
    {
        $user = Auth::user();
        if (!$user) return null;

        if (Session::get('recovery_alert_shown')) {
            return null;
        }

        $attempt = RecoveryAttempt::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHours(24))
            ->orderByDesc('created_at')
            ->first();

        if (!$attempt) return null;

        Session::put('recovery_alert_shown', true);

        return [
            'fecha'     => $attempt->created_at->format('d/m/Y H:i'),
            'ip'        => $attempt->ip,
            'resultado' => $attempt->resultado,
            'tipo'      => $attempt->tipo,
        ];
    }


    /*Language Translation*/
    public function lang($locale)
    {
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();
            return redirect()->back()->with('locale', $locale);
        } else {
            return redirect()->back();
        }
    }


}

