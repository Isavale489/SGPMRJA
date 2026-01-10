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
        // Cargar relaciones incluyendo cliente normalizado
        $pedidos = Pedido::with(['user:id,name', 'cliente.persona'])->select('pedido.*');
        return DataTables::of($pedidos)
            ->addColumn('usuario_creador', function ($pedido) {
                return $pedido->user ? $pedido->user->name : 'N/A';
            })
            // Usar accessors para mostrar datos normalizados del cliente
            ->addColumn('cliente_nombre_display', function ($pedido) {
                return $pedido->cliente_nombre_completo ?? 'N/A';
            })
            ->addColumn('cliente_email_display', function ($pedido) {
                return $pedido->cliente_email_normalizado ?? 'N/A';
            })
            ->addColumn('cliente_telefono_display', function ($pedido) {
                return $pedido->cliente_telefono_normalizado ?? 'N/A';
            })
            ->addColumn('cliente_documento_display', function ($pedido) {
                return $pedido->cliente_documento ?? 'N/A';
            })
            ->addColumn('actions', function ($pedido) {
                $isAdmin = auth()->user()->isAdmin();
                $actions = '<div class="d-flex gap-2">';
                $actions .= '<button type="button" class="btn btn-sm btn-info view-btn" data-id="' . $pedido->id . '" title="Ver detalles"><i class="ri-eye-fill"></i></button>';
                if ($isAdmin) {
                    $actions .= '<button type="button" class="btn btn-sm btn-success edit-btn" data-id="' . $pedido->id . '" title="Editar pedido"><i class="ri-pencil-fill"></i></button>';
                    $actions .= '<button type="button" class="btn btn-sm btn-danger remove-btn" data-id="' . $pedido->id . '" title="Eliminar pedido"><i class="ri-delete-bin-fill"></i></button>';
                }
                $actions .= '<a href="' . route('pedidos.pdf', $pedido->id) . '" class="btn btn-sm btn-warning" title="Descargar PDF"><i class="ri-file-pdf-fill"></i></a>';
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cotizacion_id' => 'nullable|exists:cotizacion,id', // FK a cotización (opcional)
            'cliente_id' => 'required|exists:cliente,id', // FK al cliente (ahora requerido)
            'fecha_pedido' => 'required|date',
            'fecha_entrega_estimada' => 'nullable|date|after_or_equal:fecha_pedido',
            'abono' => 'required|numeric|min:0',
            'efectivo_pagado' => 'boolean',
            'transferencia_pagado' => 'boolean',
            'pago_movil_pagado' => 'boolean',
            'referencia_transferencia' => 'nullable|string|max:255|required_if:transferencia_pagado,true',
            'referencia_pago_movil' => 'nullable|string|max:255|required_if:pago_movil_pagado,true',
            'banco_id' => 'nullable|exists:banco,id|required_if:transferencia_pagado,true|required_if:pago_movil_pagado,true',
            'prioridad' => 'required|in:Normal,Alta,Urgente',
            'productos' => 'required|array',
            'productos.*.producto_id' => 'required|exists:producto,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.descripcion' => 'nullable|string',
            'productos.*.lleva_bordado' => 'nullable|boolean',
            'productos.*.nombre_logo' => 'nullable|string|required_if:productos.*.lleva_bordado,true',
            'productos.*.color' => 'nullable|string|max:50',
            'productos.*.talla' => 'nullable|in:Talla Unica,XS,S,M,L,XL,XXL,2,4,6,8,10,12,14,16',
            'productos.*.insumos' => 'nullable|array',
            'productos.*.insumos.*.id' => 'required|exists:insumo,id',
            'productos.*.insumos.*.cantidad_estimada' => 'required|numeric|min:0.01',
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

                // Procesar insumos para cada producto del pedido
                if (isset($item['insumos']) && is_array($item['insumos'])) {
                    foreach ($item['insumos'] as $insumoData) {
                        $insumo = Insumo::find($insumoData['id']);

                        if ($insumo) {
                            $cantidadARestar = $insumoData['cantidad_estimada'];

                            if ($insumo->stock_actual >= $cantidadARestar) {
                                $stockAnterior = $insumo->stock_actual;
                                $insumo->stock_actual -= $cantidadARestar;
                                $insumo->save();

                                // Registrar movimiento de insumo (Salida)
                                MovimientoInsumo::create([
                                    'insumo_id' => $insumo->id,
                                    'tipo_movimiento' => 'Salida',
                                    'cantidad' => $cantidadARestar,
                                    'stock_anterior' => $stockAnterior,
                                    'stock_nuevo' => $insumo->stock_actual,
                                    'motivo' => 'Consumo por Pedido #' . $pedido->id . ' - Producto: ' . $producto->nombre,
                                    'created_by' => Auth::id(),
                                ]);

                                // IMPORTANTE: Asociar insumo al detalle de pedido
                                $detallePedido->insumos()->attach($insumo->id, ['cantidad_estimada' => $cantidadARestar]);

                            } else {
                                // Si el stock es insuficiente, revertir la transacción y retornar un error
                                throw new \Exception('Stock insuficiente para el insumo: ' . $insumo->nombre . '. Stock actual: ' . $insumo->stock_actual . ', Cantidad requerida: ' . $cantidadARestar);
                            }
                        }
                    }
                }
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
            'productos.insumos',
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
        $request->validate([
            'cliente_id' => 'required|exists:cliente,id', // FK al cliente (ahora requerido)
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
            'productos' => 'required|array',
            'productos.*.producto_id' => 'required|exists:producto,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.descripcion' => 'nullable|string',
            'productos.*.lleva_bordado' => 'nullable|boolean',
            'productos.*.nombre_logo' => 'nullable|string|required_if:productos.*.lleva_bordado,true',
            'productos.*.color' => 'nullable|string|max:50',
            'productos.*.talla' => 'nullable|in:Talla Unica,XS,S,M,L,XL,XXL,2,4,6,8,10,12,14,16',
            'productos.*.insumos' => 'nullable|array',
            'productos.*.insumos.*.id' => 'required|exists:insumo,id',
            'productos.*.insumos.*.cantidad_estimada' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($request, $id) {
            $pedido = Pedido::findOrFail($id);

            // Revertir el stock de insumos de los productos existentes antes de eliminarlos
            foreach ($pedido->productos as $detallePedidoExistente) {
                if ($detallePedidoExistente->insumos) { // Asumiendo que existe una relación 'insumos' en DetallePedido
                    foreach ($detallePedidoExistente->insumos as $insumoExistente) {
                        $insumo = Insumo::find($insumoExistente->id);
                        if ($insumo) {
                            $stockAnterior = $insumo->stock_actual;
                            $cantidadADevolver = $insumoExistente->pivot->cantidad_estimada; // Asumiendo tabla pivote
                            $insumo->stock_actual += $cantidadADevolver;
                            $insumo->save();

                            MovimientoInsumo::create([
                                'insumo_id' => $insumo->id,
                                'tipo_movimiento' => 'Entrada',
                                'cantidad' => $cantidadADevolver,
                                'stock_anterior' => $stockAnterior,
                                'stock_nuevo' => $insumo->stock_actual,
                                'motivo' => 'Reversión por actualización de Pedido #' . $pedido->id . ' - Producto: ' . $detallePedidoExistente->producto->nombre,
                                'created_by' => Auth::id(),
                            ]);
                        }
                    }
                }
            }

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

                // Procesar insumos para cada producto del pedido
                if (isset($item['insumos']) && is_array($item['insumos'])) {
                    foreach ($item['insumos'] as $insumoData) {
                        $insumo = Insumo::find($insumoData['id']);

                        if ($insumo) {
                            $cantidadARestar = $insumoData['cantidad_estimada'];

                            if ($insumo->stock_actual >= $cantidadARestar) {
                                $stockAnterior = $insumo->stock_actual;
                                $insumo->stock_actual -= $cantidadARestar;
                                $insumo->save();

                                // Registrar movimiento de insumo (Salida)
                                MovimientoInsumo::create([
                                    'insumo_id' => $insumo->id,
                                    'tipo_movimiento' => 'Salida',
                                    'cantidad' => $cantidadARestar,
                                    'stock_anterior' => $stockAnterior,
                                    'stock_nuevo' => $insumo->stock_actual,
                                    'motivo' => 'Consumo por actualización de Pedido #' . $pedido->id . ' - Producto: ' . $producto->nombre,
                                    'created_by' => Auth::id(),
                                ]);

                                // Asociar insumo al detalle de pedido (si usas tabla pivote para DetallePedido e Insumo)
                                // Esto es crucial si quieres cargar los insumos al editar el pedido
                                $detallePedido->insumos()->attach($insumo->id, ['cantidad_estimada' => $cantidadARestar]);

                            } else {
                                // Si el stock es insuficiente, revertir la transacción y retornar un error
                                throw new \Exception('Stock insuficiente para el insumo: ' . $insumo->nombre . '. Stock actual: ' . $insumo->stock_actual . ', Cantidad requerida: ' . $cantidadARestar);
                            }
                        }
                    }
                }
            }
        });

        return response()->json(['success' => 'Pedido actualizado exitosamente.']);
    }

    public function destroy($id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->delete();
        return response()->json(['success' => 'Pedido eliminado exitosamente.']);
    }

    public function reportePdf()
    {
        // Obtener todos los pedidos con su usuario asociado
        $pedidos = Pedido::with('user:id,name')->get();

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
        $pedido->load(['user:id,name', 'productos.producto']);

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
