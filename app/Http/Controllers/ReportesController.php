<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\OrdenProduccion;
use App\Models\ProduccionDiaria;
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

        $produccionMensual = ProduccionDiaria::select(
            DB::raw('YEAR(created_at) as año'),
            DB::raw('MONTH(created_at) as mes'),
            DB::raw('SUM(cantidad_producida) as total_producido'),
            DB::raw('SUM(cantidad_defectuosa) as total_defectuoso')
        )
            ->groupBy('año', 'mes')
            ->orderBy('año', 'desc')
            ->orderBy('mes', 'desc')
            ->limit(12)
            ->get();

        return view('admin.reportes.produccion', compact('ordenesPorEstado', 'produccionMensual'));
    }

    public function eficiencia()
    {
        $eficienciaPorOrden = ProduccionDiaria::select(
            'orden_id',
            DB::raw('SUM(cantidad_producida) as total_producido'),
            DB::raw('SUM(cantidad_defectuosa) as total_defectuoso')
        )
            ->with('orden:id,producto_id,cantidad_solicitada')
            ->groupBy('orden_id')
            ->get()
            ->map(function ($item) {
                $eficiencia = ($item->total_producido - $item->total_defectuoso) / $item->total_producido * 100;
                return [
                    'orden_id' => $item->orden_id,
                    'producto' => $item->orden->producto->nombre ?? 'N/A',
                    'cantidad_solicitada' => $item->orden->cantidad_solicitada ?? 0,
                    'total_producido' => $item->total_producido,
                    'total_defectuoso' => $item->total_defectuoso,
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
        $rendimientoOperarios = ProduccionDiaria::select(
            'operario_id',
            DB::raw('COUNT(DISTINCT orden_id) as total_ordenes'),
            DB::raw('SUM(cantidad_producida) as total_producido'),
            DB::raw('SUM(cantidad_defectuosa) as total_defectuoso')
        )
            ->with('operario:id,name')
            ->groupBy('operario_id')
            ->get()
            ->map(function ($item) {
                $eficiencia = $item->total_producido > 0 ?
                    ($item->total_producido - $item->total_defectuoso) / $item->total_producido * 100 : 0;
                return [
                    'operario_id' => $item->operario_id,
                    'nombre' => $item->operario->name ?? 'N/A',
                    'total_ordenes' => $item->total_ordenes,
                    'total_producido' => $item->total_producido,
                    'total_defectuoso' => $item->total_defectuoso,
                    'eficiencia' => round($eficiencia, 2)
                ];
            });

        return view('admin.reportes.empleados', compact('rendimientoOperarios'));
    }
}
