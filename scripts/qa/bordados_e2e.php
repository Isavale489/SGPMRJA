<?php

use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Logo;
use App\Models\Producto;
use App\Models\User;
use App\Services\CotizacionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cleanup = in_array('--cleanup', $argv, true);

function out(string $message): void
{
    echo $message . PHP_EOL;
}

function fail(string $message, int $code = 1): void
{
    out('QA_FAIL: ' . $message);
    exit($code);
}

try {
    $user = User::first();
    $cliente = Cliente::first();
    $producto = Producto::where('estado', 1)->first();
    $logos = Logo::take(3)->get();

    if (!$user || !$cliente || !$producto || $logos->count() < 3) {
        fail('Precondiciones insuficientes (usuario/cliente/producto/logos).');
    }

    Auth::loginUsingId($user->id);
    $service = app(CotizacionService::class);
    $base = 20.00;

    $crearData = [
        'cliente_id' => $cliente->id,
        'fecha_cotizacion' => now()->toDateString(),
        'fecha_validez' => now()->addDays(7)->toDateString(),
        'notas' => 'QA E2E bordados script',
        'productos' => [[
            'producto_id' => $producto->id,
            'cantidad' => 4,
            'descripcion' => 'QA producto',
            'lleva_bordado' => 1,
            'precio_unitario' => $base,
            'bordados' => [
                [
                    'ubicacion_bordado_id' => 1,
                    'nombre_aplicado' => 'Frontal Izquierdo',
                    'logo_id' => $logos[0]->id,
                    'es_personalizada' => false,
                    'precio_aplicado' => 3,
                    'cantidad' => 1,
                ],
                [
                    'ubicacion_bordado_id' => 5,
                    'nombre_aplicado' => 'Espaldar',
                    'logo_id' => $logos[1]->id,
                    'es_personalizada' => false,
                    'precio_aplicado' => 5,
                    'cantidad' => 1,
                ],
                [
                    'ubicacion_bordado_id' => null,
                    'nombre_aplicado' => 'Cuello Especial',
                    'logo_id' => $logos[2]->id,
                    'es_personalizada' => true,
                    'precio_aplicado' => 2,
                    'cantidad' => 2,
                ],
            ],
        ]],
    ];

    $cot = $service->crear($crearData);
    $cot->load('productos.bordados');

    $expectedCreateTotal = ($base + 3 + 5 + 4) * 4;
    if ((float) $cot->total !== (float) number_format($expectedCreateTotal, 2, '.', '')) {
        fail('Total de creación no coincide. got=' . $cot->total . ' expected=' . $expectedCreateTotal);
    }

    $updateData = [
        'cliente_id' => $cliente->id,
        'fecha_cotizacion' => now()->toDateString(),
        'fecha_validez' => now()->addDays(10)->toDateString(),
        'estado' => 'Aprobada',
        'notas' => 'QA E2E bordados script update',
        'productos' => [[
            'producto_id' => $producto->id,
            'cantidad' => 3,
            'descripcion' => 'QA producto update',
            'lleva_bordado' => 1,
            'precio_unitario' => $base,
            'bordados' => [
                [
                    'ubicacion_bordado_id' => 2,
                    'nombre_aplicado' => 'Frontal Derecho',
                    'logo_id' => $logos[0]->id,
                    'es_personalizada' => false,
                    'precio_aplicado' => 2.5,
                    'cantidad' => 1,
                ],
                [
                    'ubicacion_bordado_id' => 5,
                    'nombre_aplicado' => 'Espaldar',
                    'logo_id' => $logos[1]->id,
                    'es_personalizada' => false,
                    'precio_aplicado' => 4.5,
                    'cantidad' => 2,
                ],
            ],
        ]],
    ];

    $service->actualizar($cot, $updateData);
    $cot->refresh()->load('productos.bordados');

    $expectedUpdateTotal = ($base + 2.5 + (4.5 * 2)) * 3;
    if ((float) $cot->total !== (float) number_format($expectedUpdateTotal, 2, '.', '')) {
        fail('Total de actualización no coincide. got=' . $cot->total . ' expected=' . $expectedUpdateTotal);
    }

    // La conversión va por el asistente de Pedidos (abono mínimo + entrega).
    $cot->update(['estado' => 'Aprobada']);
    $pedido = app(\App\Services\PedidoService::class)->crearDesdeCotizacion([
        'cotizacion_id' => $cot->id,
        'fecha_entrega_estimada' => now()->addWeekdays(30)->toDateString(),
        'prioridad' => 'Normal',
        'pagos' => [['metodo' => 'efectivo', 'monto' => (float) $cot->total]],
    ]);
    $pedido->load('productos.bordados');

    if ((float) $pedido->total !== (float) $cot->total) {
        fail('Total de pedido no coincide con cotización convertida.');
    }

    $subtotal = $cot->total;
    $descuento = 0;
    $iva = round(($subtotal - $descuento) * 0.16, 2);
    $totalPagar = round($subtotal - $descuento + $iva, 2);

    $cotPdf = PDF::loadView('admin.cotizaciones.factura', [
        'cotizacion' => $cot->fresh()->load(['user:id,name', 'productos.producto', 'productos.bordados.logo:id,name', 'cliente', 'cliente.persona']),
        'subtotal' => $subtotal,
        'descuento' => $descuento,
        'iva' => $iva,
        'totalPagar' => $totalPagar,
    ])->setPaper('a4', 'portrait')->output();

    $pedPdf = PDF::loadView('admin.pedidos.factura', [
        'pedido' => $pedido->fresh()->load(['user:id,name', 'productos.producto', 'productos.bordados.logo:id,name', 'cliente', 'cliente.persona']),
        'subtotal' => $pedido->total,
        'descuento' => 0,
        'iva' => round($pedido->total * 0.16, 2),
        'totalPagar' => round($pedido->total * 1.16, 2),
    ])->setPaper('a4', 'portrait')->output();

    if (strlen($cotPdf) <= 0 || strlen($pedPdf) <= 0) {
        fail('Render de PDFs inválido.');
    }

    out('QA_OK');
    out('cotizacion_id=' . $cot->id);
    out('pedido_id=' . $pedido->id);
    out('cot_total=' . $cot->total);
    out('ped_total=' . $pedido->total);
    out('cot_pdf_bytes=' . strlen($cotPdf));
    out('ped_pdf_bytes=' . strlen($pedPdf));

    if ($cleanup) {
        DB::transaction(function () use ($cot) {
            $record = Cotizacion::with('pedido')->find($cot->id);
            if ($record?->pedido) {
                $record->pedido->delete();
            }
            $record?->delete();
        });
        out('CLEANUP_OK');
    } else {
        out('CLEANUP_SKIPPED');
    }
} catch (Throwable $e) {
    fail($e->getMessage());
}
