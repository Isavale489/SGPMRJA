@extends('layouts.pdf')

@section('page-title', 'Pedido #' . $pedido->id)
@section('report-title', 'Pedido N° ' . str_pad($pedido->id, 5, '0', STR_PAD_LEFT))

@section('extra-styles')
    @include('admin.partials.factura.styles')
@endsection

@section('content')
    {{-- ═══════ Datos del cliente y del pedido ═══════ --}}
    <table class="info-block">
        <tr>
            <td>
                <span class="label">Cliente:</span> {{ $pedido->cliente_nombre_completo }}<br>
                <span class="label">{{ str_starts_with($pedido->cliente_documento, 'V-') ? 'C.I.:' : 'RIF:' }}</span>
                {{ $pedido->cliente_documento }}<br>
                <span class="label">Teléfono:</span> {{ $pedido->cliente_telefono_normalizado }}<br>
                <span class="label">Email:</span> {{ $pedido->cliente_email_normalizado }}
            </td>
            <td>
                <span class="label">Fecha del Pedido:</span>
                {{ \Carbon\Carbon::parse($pedido->fecha_pedido)->format('d/m/Y') }}<br>
                <span class="label">Fecha Entrega Est.:</span>
                {{ $pedido->fecha_entrega_estimada ? \Carbon\Carbon::parse($pedido->fecha_entrega_estimada)->format('d/m/Y') : '-' }}<br>
                <span class="label">Elaborado por:</span> {{ $pedido->user->name ?? '-' }}
            </td>
        </tr>
    </table>

    {{-- ═══════ Ítems ═══════ --}}
    @include('admin.partials.factura.items', ['detalles' => $pedido->productos, 'tasaValor' => $tasaValor])

    {{-- ═══════ Pie anclado: montos + condiciones ═══════ --}}
    @php
        $totalBordadoUsd = $pedido->productos->sum(function ($d) {
            if (!$d->lleva_bordado) {
                return 0;
            }
            $recargoUnit = $d->bordados->sum(fn ($b) => (float) $b->precio_aplicado * (int) ($b->cantidad ?? 1));
            return $recargoUnit * (int) $d->cantidad;
        });
    @endphp
    <div class="doc-bottom">
        @include('admin.partials.factura.totales', [
            'subtotal' => $subtotal, 'descuento' => $descuento, 'iva' => $iva,
            'totalPagar' => $totalPagar, 'totalBordadoUsd' => $totalBordadoUsd,
            'tasaValor' => $tasaValor, 'tasaFecha' => $tasaFecha,
        ])

        @include('admin.partials.factura.condiciones')
    </div>
@endsection
