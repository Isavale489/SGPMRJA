<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\DetallePedido;
use App\Models\Insumo;
use App\Models\MovimientoInsumo;
use App\Models\Banco;
use App\Models\Cotizacion;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Rules\CiRifFormat;
use PDF;

class PedidoController extends Controller
{
    public function index()
    {
        $productos = Producto::with('tipoProducto')->where('estado', true)->get();
        $insumos = Insumo::all();
        $bancos = Banco::all();
        return view('admin.pedidos.index', compact('productos', 'insumos', 'bancos'));
    }

    public function getPedidos()
    {
        // Hacer JOINs para permitir ordenamiento y búsqueda por datos del cliente
        $pedidos = Pedido::select('pedido.*')
            ->join('cliente', 'pedido.cliente_id', '=', 'cliente.id')
            ->join('persona', 'cliente.persona_id', '=', 'persona.id')
            ->with(['user:id,name', 'cliente.persona']);

        return DataTables::of($pedidos)
            // Usar accessors para mostrar datos normalizados del cliente
            ->addColumn('cliente_nombre_display', function ($pedido) {
                return $pedido->cliente_nombre_completo ?? 'N/A';
            })
            ->filterColumn('cliente_nombre_display', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('persona.nombre', 'like', "%{$keyword}%")
                        ->orWhere('persona.apellido', 'like', "%{$keyword}%")
                        ->orWhereRaw("CONCAT(persona.nombre, ' ', persona.apellido) like ?", ["%{$keyword}%"]);
                });
            })

            ->addColumn('cliente_telefono_display', function ($pedido) {
                return $pedido->cliente_telefono_normalizado ?? 'N/A';
            })


            ->addColumn('fecha_pedido', function ($pedido) {
                return $pedido->fecha_pedido ? $pedido->fecha_pedido->format('d/m/Y') : 'N/A';
            })
            ->addColumn('fecha_entrega_estimada', function ($pedido) {
                return $pedido->fecha_entrega_estimada ? $pedido->fecha_entrega_estimada->format('d/m/Y') : 'N/A';
            })

            ->make(true);
    }

    /**
     * Obtener cotizaciones aprobadas disponibles para crear pedidos
     * (que no tengan un pedido asociado)
     */
    public function getCotizacionesDisponibles()
    {
        $cotizaciones = Cotizacion::with(['cliente', 'productos'])
            ->where('estado', 'Aprobada')
            ->doesntHave('pedido') // Solo cotizaciones sin pedido asociado
            ->orderBy('fecha_cotizacion', 'desc')
            ->get()
            ->map(function ($cotizacion) {
                return [
                    'id' => $cotizacion->id,
                    'cliente_nombre' => $cotizacion->cliente ?
                        trim(($cotizacion->cliente->nombre ?? '') . ' ' . ($cotizacion->cliente->apellido ?? '')) :
                        'N/A',
                    'cliente_documento' => $cotizacion->cliente->documento ?? 'N/A',
                    'fecha_cotizacion' => $cotizacion->fecha_cotizacion ?
                        $cotizacion->fecha_cotizacion->format('d/m/Y') :
                        'N/A',
                    'fecha_validez' => $cotizacion->fecha_validez ?
                        $cotizacion->fecha_validez->format('d/m/Y') :
                        'N/A',
                    'total' => number_format($cotizacion->total, 2),
                    'total_raw' => $cotizacion->total,
                    'cantidad_productos' => $cotizacion->productos->count(),
                ];
            });

        return response()->json($cotizaciones);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cotizacion_id' => 'nullable|exists:cotizacion,id',
            'cliente_id' => 'required|exists:cliente,id',
            'fecha_pedido' => 'required|date',
            'fecha_entrega_estimada' => 'nullable|date|after_or_equal:fecha_pedido',
            'abono' => 'required|numeric|min:0',
            'efectivo_pagado' => 'boolean',
            'transferencia_pagado' => 'boolean',
            'pago_movil_pagado' => 'boolean',
            'referencia_transferencia' => 'nullable|string|max:255|required_if:transferencia_pagado,true',
            'referencia_pago_movil' => 'nullable|string|max:255|required_if:pago_movil_pagado,true',
            'banco_transferencia_id' => 'nullable|exists:banco,id|required_if:transferencia_pagado,true',
            'banco_pago_movil_id' => 'nullable|exists:banco,id|required_if:pago_movil_pagado,true',
            'prioridad' => 'required|in:Normal,Alta,Urgente',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:producto,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.descripcion' => 'nullable|string|max:500',
            'productos.*.lleva_bordado' => 'nullable|boolean',
            'productos.*.nombre_logo' => 'nullable|string|max:100|required_if:productos.*.lleva_bordado,true',
            'productos.*.color' => 'nullable|string|max:50',
            'productos.*.talla' => 'nullable|in:Talla Unica,XS,S,M,L,XL,XXL,2,4,6,8,10,12,14,16',
        ]);


        DB::transaction(function () use ($request) {
            $total_pedido = 0;
            foreach ($request->productos as $item) {
                $producto = Producto::find($item['producto_id']);
                $total_pedido += $producto->precio_base * $item['cantidad'];
            }

            $pedido = Pedido::create([
                'cotizacion_id' => $request->cotizacion_id, // Vincula con cotización si existe
                'cliente_id' => $request->cliente_id,
                'fecha_pedido' => $request->fecha_pedido,
                'fecha_entrega_estimada' => $request->fecha_entrega_estimada,
                'estado' => 'Pendiente',
                'total' => $total_pedido,
                'user_id' => Auth::id(),
                'abono' => $request->abono,
                'efectivo_pagado' => $request->boolean('efectivo_pagado'),
                'transferencia_pagado' => $request->boolean('transferencia_pagado'),
                'pago_movil_pagado' => $request->boolean('pago_movil_pagado'),
                'referencia_transferencia' => $request->referencia_transferencia,
                'referencia_pago_movil' => $request->referencia_pago_movil,
                'banco_id' => $request->banco_id,
                'banco_transferencia_id' => $request->banco_transferencia_id,
                'banco_pago_movil_id' => $request->banco_pago_movil_id,
                'prioridad' => $request->prioridad,
            ]);

            foreach ($request->productos as $item) {
                $producto = Producto::find($item['producto_id']);
                $detallePedido = DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'descripcion' => $item['descripcion'] ?? null,
                    'lleva_bordado' => $item['lleva_bordado'] ?? false,
                    'nombre_logo' => $item['nombre_logo'] ?? null,
                    'ubicacion_logo' => $item['ubicacion_logo'] ?? null,
                    'cantidad_logo' => $item['cantidad_logo'] ?? null,
                    'color' => $item['color'] ?? null,
                    'talla' => $item['talla'] ?? null,
                    'precio_unitario' => $producto->precio_base,
                ]);


            }

            // Si el pedido viene desde una cotización, marcarla como Convertida
            if ($request->cotizacion_id) {
                $cotizacion = Cotizacion::find($request->cotizacion_id);
                if ($cotizacion && $cotizacion->estado === 'Aprobada') {
                    $cotizacion->update(['estado' => 'Convertida']);
                }
            }
        });

        return response()->json(['success' => 'Pedido creado exitosamente.']);
    }

    public function show($id)
    {
        // Cargar pedido con cliente y sus relaciones normalizadas
        $pedido = Pedido::with([
            'user:id,name',
            'productos.producto.tipoProducto',
            'productos.producto.tipoProducto',
            'banco:id,nombre',
            'cliente.persona.telefonos',
            'cliente.persona.direcciones'
        ])->findOrFail($id);

        // Agregar datos normalizados del cliente al response
        $data = $pedido->toArray();
        $data['cliente_nombre_completo'] = $pedido->cliente_nombre_completo;
        $data['cliente_email_normalizado'] = $pedido->cliente_email_normalizado;
        $data['cliente_telefono_normalizado'] = $pedido->cliente_telefono_normalizado;
        $data['cliente_documento'] = $pedido->cliente_documento;

        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        if (in_array($pedido->estado, ['Completado', 'Cancelado'])) {
            return response()->json(['error' => 'No se puede editar un pedido completado o cancelado.'], 403);
        }

        $request->validate([
            'cliente_id' => 'required|exists:cliente,id',
            'fecha_pedido' => 'required|date',
            'fecha_entrega_estimada' => 'nullable|date|after_or_equal:fecha_pedido',
            'estado' => 'required|in:Pendiente,Procesando,Completado,Cancelado',
            'abono' => 'required|numeric|min:0',
            'efectivo_pagado' => 'boolean',
            'transferencia_pagado' => 'boolean',
            'pago_movil_pagado' => 'boolean',
            'referencia_transferencia' => 'nullable|string|max:255|required_if:transferencia_pagado,true',
            'referencia_pago_movil' => 'nullable|string|max:255|required_if:pago_movil_pagado,true',
            'banco_id' => 'nullable|exists:banco,id|required_if:transferencia_pagado,true|required_if:pago_movil_pagado,true',
            'prioridad' => 'required|in:Normal,Alta,Urgente',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:producto,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.descripcion' => 'nullable|string|max:500',
            'productos.*.lleva_bordado' => 'nullable|boolean',
            'productos.*.nombre_logo' => 'nullable|string|max:100|required_if:productos.*.lleva_bordado,true',
            'productos.*.color' => 'nullable|string|max:50',
            'productos.*.talla' => 'nullable|in:Talla Unica,XS,S,M,L,XL,XXL,2,4,6,8,10,12,14,16',
        ]);


        DB::transaction(function () use ($request, $pedido) {
            // $pedido ya fue recuperado arriba



            // Eliminar los detalles de pedido existentes (y sus relaciones con insumos si usas attach/detach en la sincronización)
            $pedido->productos()->delete();

            $total_pedido = 0;
            foreach ($request->productos as $item) {
                $producto = Producto::find($item['producto_id']);
                $total_pedido += $producto->precio_base * $item['cantidad'];
            }

            $pedido->update([
                'cliente_id' => $request->cliente_id,
                'fecha_pedido' => $request->fecha_pedido,
                'fecha_entrega_estimada' => $request->fecha_entrega_estimada,
                'estado' => $request->estado,
                'total' => $total_pedido,
                'abono' => $request->abono,
                'efectivo_pagado' => $request->boolean('efectivo_pagado'),
                'transferencia_pagado' => $request->boolean('transferencia_pagado'),
                'pago_movil_pagado' => $request->boolean('pago_movil_pagado'),
                'referencia_transferencia' => $request->referencia_transferencia,
                'referencia_pago_movil' => $request->referencia_pago_movil,
                'banco_id' => $request->banco_id,
                'prioridad' => $request->prioridad,
            ]);

            // Sincronizar productos del pedido y deducir stock de insumos
            foreach ($request->productos as $item) {
                $producto = Producto::find($item['producto_id']);
                $detallePedido = DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'descripcion' => $item['descripcion'] ?? null,
                    'lleva_bordado' => $item['lleva_bordado'] ?? false,
                    'nombre_logo' => $item['nombre_logo'] ?? null,
                    'ubicacion_logo' => $item['ubicacion_logo'] ?? null,
                    'cantidad_logo' => $item['cantidad_logo'] ?? null,
                    'color' => $item['color'] ?? null,
                    'talla' => $item['talla'] ?? null,
                    'precio_unitario' => $producto->precio_base,
                ]);


            }
        });

        return response()->json(['success' => 'Pedido actualizado exitosamente.']);
    }

    public function destroy($id)
    {
        $pedido = Pedido::findOrFail($id);

        if (in_array($pedido->estado, ['Completado', 'Cancelado'])) {
            return response()->json(['error' => 'No se puede eliminar un pedido completado o cancelado.'], 403);
        }

        $pedido->delete();
        return response()->json(['success' => 'Pedido eliminado exitosamente.']);
    }

    public function reportePdf()
    {
        // Obtener todos los pedidos con su usuario asociado y cliente
        $pedidos = Pedido::with(['user:id,name', 'cliente', 'cliente.persona'])->get();

        // Cargar la vista y generar el PDF (A4 vertical)
        $pdf = PDF::loadView('admin.pedidos.reporte_pdf', compact('pedidos'))
            ->setPaper('a4', 'portrait');

        // Descargar el archivo con una marca de tiempo para evitar colisiones
        return $pdf->download('reporte_pedidos_' . now()->format('Ymd_His') . '.pdf');
    }

    public function reporteGeneral()
    {
        $pedidos = Pedido::with('user:id,name')->get();
        return view('admin.pedidos.reporte_general', compact('pedidos'));
    }

    public function pedidoPdf(Pedido $pedido)
    {
        // Cargar relaciones necesarias
        $pedido->load(['user:id,name', 'productos.producto', 'cliente', 'cliente.persona']);

        // Cálculos financieros
        $ivaTasa = 0.16; // 16 %
        $subtotal = $pedido->total;
        $descuento = 0; // Ajustable en el futuro si se implementa
        $iva = round(($subtotal - $descuento) * $ivaTasa, 2);
        $totalPagar = round($subtotal - $descuento + $iva, 2);

        $pdf = PDF::loadView('admin.pedidos.factura', [
            'pedido' => $pedido,
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'iva' => $iva,
            'totalPagar' => $totalPagar,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('pedido_' . $pedido->id . '.pdf');
    }
}
