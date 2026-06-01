<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\OrdenProduccion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportesController extends Controller
{
    public function produccion()
    {
        $ordenesPorEstado = OrdenProduccion::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();

        // Producción mensual agregada desde las órdenes (por mes de inicio)
        $produccionMensual = OrdenProduccion::select(
            DB::raw('YEAR(fecha_inicio) as año'),
            DB::raw('MONTH(fecha_inicio) as mes'),
            DB::raw('SUM(cantidad_producida) as total_producido'),
            DB::raw('SUM(cantidad_defectuosa) as total_defectuoso')
        )
            ->whereNotNull('fecha_inicio')
            ->groupBy('año', 'mes')
            ->orderBy('año', 'desc')
            ->orderBy('mes', 'desc')
            ->limit(12)
            ->get();

        return view('admin.reportes.produccion', compact('ordenesPorEstado', 'produccionMensual'));
    }

    public function eficiencia()
    {
        // Eficiencia por orden: producido vs defectuoso (1 fila por orden)
        // `producto.nombre` es un accessor, no columna → eager load completo
        $eficienciaPorOrden = OrdenProduccion::with('producto')
            ->get()
            ->map(function ($orden) {
                $eficiencia = $orden->cantidad_producida > 0
                    ? ($orden->cantidad_producida - $orden->cantidad_defectuosa) / $orden->cantidad_producida * 100
                    : 0;
                return [
                    'orden_id' => $orden->id,
                    'producto' => $orden->producto->nombre ?? 'N/A',
                    'cantidad_solicitada' => $orden->cantidad_solicitada ?? 0,
                    'total_producido' => $orden->cantidad_producida,
                    'total_defectuoso' => $orden->cantidad_defectuosa,
                    'eficiencia' => round($eficiencia, 2)
                ];
            });

        return view('admin.reportes.eficiencia', compact('eficienciaPorOrden'));
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
        // Rendimiento por empleado: agregado desde las órdenes asignadas
        $rendimientoEmpleados = OrdenProduccion::select(
            'empleado_id',
            DB::raw('COUNT(*) as total_ordenes'),
            DB::raw('SUM(cantidad_producida) as total_producido'),
            DB::raw('SUM(cantidad_defectuosa) as total_defectuoso')
        )
            ->whereNotNull('empleado_id')
            ->with('empleado.persona')
            ->groupBy('empleado_id')
            ->get()
            ->map(function ($item) {
                $eficiencia = $item->total_producido > 0 ?
                    ($item->total_producido - $item->total_defectuoso) / $item->total_producido * 100 : 0;
                return [
                    'empleado_id' => $item->empleado_id,
                    'nombre' => $item->empleado && $item->empleado->persona
                        ? $item->empleado->persona->nombre_completo
                        : 'N/A',
                    'total_ordenes' => $item->total_ordenes,
                    'total_producido' => $item->total_producido,
                    'total_defectuoso' => $item->total_defectuoso,
                    'eficiencia' => round($eficiencia, 2)
                ];
            });

        return view('admin.reportes.empleados', compact('rendimientoEmpleados'));
    }
}
