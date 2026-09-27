@extends('layouts.pdf')

@section('page-title', 'Cotización #' . $cotizacion->id)
@section('report-title', 'Cotización N° ' . str_pad($cotizacion->id, 5, '0', STR_PAD_LEFT))

@section('extra-styles')
    @include('admin.partials.factura.styles')
@endsection

@section('content')
    {{-- ═══════ Datos del cliente y de la cotización ═══════ --}}
    <table class="info-block">
        <tr>
            <td>
                <span class="label">Cliente:</span> {{ $cotizacion->cliente->nombre ?? '-' }}<br>
                <span class="label">Email:</span> {{ $cotizacion->cliente->email ?? '-' }}<br>
                <span class="label">Teléfono:</span> {{ $cotizacion->cliente->telefono ?? '-' }}<br>
                <span class="label">Documento:</span> {{ $cotizacion->cliente->documento ?? '-' }}
            </td>
            <td>
                <span class="label">Fecha Cotización:</span>
                {{ \Carbon\Carbon::parse($cotizacion->fecha_cotizacion)->format('d/m/Y') }}<br>
                <span class="label">Fecha Validez:</span>
                {{ $cotizacion->fecha_validez ? \Carbon\Carbon::parse($cotizacion->fecha_validez)->format('d/m/Y') : '-' }}<br>
                <span class="label">Estado:</span> {{ $cotizacion->estado }}<br>
                <span class="label">Elaborado por:</span> {{ $cotizacion->user->name ?? '-' }}
            </td>
        </tr>
    </table>

    {{-- ═══════ Ítems ═══════ --}}
    @include('admin.partials.factura.items', ['detalles' => $cotizacion->productos, 'tasaValor' => $tasaValor])

    {{-- ═══════ Pie anclado: montos + condiciones ═══════ --}}
    @php
        $totalBordadoUsd = $cotizacion->productos->sum(function ($d) {
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

        @if($cotizacion->condiciones_terminos)
            <div class="nota-especial" style="background:#f0f4ff; border-color:#3b5bdb; border-left-color:#1d3557; color:#1d3557;">
                <b>Condiciones y Términos:</b><br>
                {!! nl2br(e($cotizacion->condiciones_terminos)) !!}
            </div>
        @else
            @include('admin.partials.factura.condiciones')
        @endif
    </div>
@endsection
