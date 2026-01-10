<?php

namespace App\Http\Controllers;
 
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\Insumo;
use App\Models\OrdenProduccion;
use App\Models\ProduccionDiaria;
use App\Models\MovimientoInsumo;

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
        // Obtener datos para el dashboard
        $totalInsumos = Insumo::count();
        $ordenesEnProceso = OrdenProduccion::where('estado', 'En Proceso')->count();
        $produccionTotal = ProduccionDiaria::sum('cantidad_producida');
        $alertasStock = Insumo::whereRaw('stock_actual <= stock_minimo')->count();
        
        // Datos para gráfico de inventario
        $insumos = Insumo::all();
        
        // Datos para gráfico de órdenes por estado
        $ordenesPendientes = OrdenProduccion::where('estado', 'Pendiente')->count();
        $ordenesEnProceso = OrdenProduccion::where('estado', 'En Proceso')->count();
        $ordenesFinalizadas = OrdenProduccion::where('estado', 'Finalizado')->count();
        $ordenesCanceladas = OrdenProduccion::where('estado', 'Cancelado')->count();
        
        // Últimos movimientos de inventario
        $ultimosMovimientos = MovimientoInsumo::with('insumo')->latest()->take(5)->get();
        
        return view('dashboard', compact(
            'totalInsumos', 
            'ordenesEnProceso', 
            'produccionTotal', 
            'alertasStock',
            'insumos',
            'ordenesPendientes',
            'ordenesEnProceso',
            'ordenesFinalizadas',
            'ordenesCanceladas',
            'ultimosMovimientos'
        ));
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
