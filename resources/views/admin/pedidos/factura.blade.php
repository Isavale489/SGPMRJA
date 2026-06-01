@extends('layouts.pdf')

@section('page-title', 'Pedido #' . $pedido->id)
@section('report-title', 'Pedido N° ' . str_pad($pedido->id, 5, '0', STR_PAD_LEFT))

@section('extra-styles')
    /* ── Bloque de datos (cliente / pedido) ── */
    .info-block {
        width: 100%;
        border: none;
        margin-bottom: 12px;
    }

    .info-block td {
        border: none;
        padding: 0 14px 0 0;
        font-size: 9.5px;
        line-height: 1.7;
        vertical-align: top;
        width: 50%;
        word-break: break-word;
    }

    .info-block .label {
        font-weight: bold;
        color: #1e3c72;
    }

    /* ── Anchos de columnas de la tabla de productos ── */
    .col-cant   { width: 6%;    text-align: center; }
    .col-concep { width: 34%; }
    .col-color  { width: 9%;    text-align: center; }
    .col-talla  { width: 8%;    text-align: center; }
    .col-logo   { width: 20%; }
    .col-cunit  { width: 11.5%; text-align: right; }
    .col-monto  { width: 11.5%; text-align: right; }

    .data-table tbody td.text-center { text-align: center; }
    .data-table tbody td.text-right  { text-align: right; }
    .data-table tbody td small { color: #6b7280; font-size: 8px; }

    /* ── Bloque de totales ── */
    .totals-block {
        width: 100%;
        border: none;
        margin-top: 10px;
        margin-bottom: 14px;
    }

    .totals-block > tbody > tr > td {
        border: none;
        padding: 0;
    }

    .totals-inner {
        width: 230px;
        border: none;
    }

    .totals-inner td {
        border: none;
        padding: 3px 8px;
        font-size: 10px;
    }

    .totals-inner .t-label {
        text-align: right;
        color: #3d4852;
        font-weight: 600;
    }

    .totals-inner .t-value {
        text-align: right;
        width: 90px;
    }

    .totals-inner .t-grand {
        border-top: 2px solid #1e3c72;
        color: #1e3c72;
        font-weight: bold;
        font-size: 11px;
    }

    /* ── Nota especial ── */
    .nota-especial {
        clear: both;
        background-color: #fff8e1;
        color: #5d4037;
        padding: 10px 12px;
        border: 1px solid #ffe082;
        border-left: 3px solid #ffb300;
        margin-top: 16px;
        font-size: 9.5px;
        line-height: 1.6;
    }
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

    {{-- ═══════ Tabla de productos ═══════ --}}
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-cant">Cant.</th>
                <th class="col-concep">Concepto o Descripción del Producto</th>
                <th class="col-color">Color</th>
                <th class="col-talla">Talla</th>
                <th class="col-logo">Logo</th>
                <th class="col-cunit">Costo Unit.</th>
                <th class="col-monto">Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->productos as $index => $detalle)
                @php
                    $ubicacionesTexto = $detalle->bordados->map(function ($b) {
                        $cantidad = (int) ($b->cantidad ?? 1);
                        return $b->nombre_aplicado . ($cantidad > 1 ? ' x' . $cantidad : '');
                    })->implode(', ');
                    $logosTexto = $detalle->bordados
                        ->map(function ($b) {
                            return trim((string) ($b->nombre_logo_aplicado ?? ''));
                        })
                        ->filter()
                        ->unique()
                        ->implode(', ');
                    $cantidadBordados = $detalle->bordados->sum(function ($b) {
                        return (int) ($b->cantidad ?? 1);
                    });
                    // Snapshot inmutable: estado del catálogo al momento del pedido
                    $telaSnap = $detalle->tela_snapshot;
                    $atrSnap  = $detalle->atributos_snapshot;
                    $variantPartes = [];
                    if (is_array($telaSnap) && !empty($telaSnap['nombre'])) {
                        $variantPartes[] = 'Tela: ' . $telaSnap['nombre'];
                    }
                    if (is_array($atrSnap)) {
                        foreach ($atrSnap as $atrNombre => $valNombre) {
                            $variantPartes[] = $atrNombre . ': ' . $valNombre;
                        }
                    }
                @endphp
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-cant text-center">{{ $detalle->cantidad }}</td>
                    <td class="col-concep">
                        {{ $detalle->producto->nombre }}
                        @if(!empty($variantPartes))
                            <br><small>{{ implode(' · ', $variantPartes) }}</small>
                        @endif
                        @if($detalle->descripcion)
                            <br><small>{{ $detalle->descripcion }}</small>
                        @endif
                    </td>
                    <td class="col-color text-center">{{ $detalle->color?->nombre ?? '-' }}</td>
                    <td class="col-talla text-center">{{ $detalle->talla?->etiqueta ?? '-' }}</td>
                    <td class="col-logo">
                        @if($detalle->lleva_bordado)
                            {{ $logosTexto ?: ($detalle->nombre_logo ?: '-') }}
                            <br><small>Ubicación: {{ $ubicacionesTexto ?: '-' }}</small>
                            <br><small>Cantidad: {{ $cantidadBordados ?: '-' }}</small>
                        @else
                            -
                        @endif
                    </td>
                    <td class="col-cunit text-right">{{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td class="col-monto text-right">{{ number_format($detalle->precio_unitario * $detalle->cantidad, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ═══════ Totales ═══════ --}}
    <table class="totals-block">
        <tr>
            <td>&nbsp;</td>
            <td style="width: 230px;">
                <table class="totals-inner">
                    <tr>
                        <td class="t-label">Total:</td>
                        <td class="t-value">{{ number_format($subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="t-label">Descuento:</td>
                        <td class="t-value">{{ number_format($descuento, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="t-label">IVA (16%):</td>
                        <td class="t-value">{{ number_format($iva, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="t-label t-grand">Total a Pagar:</td>
                        <td class="t-value t-grand">{{ number_format($totalPagar, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ═══════ Nota especial ═══════ --}}
    <div class="nota-especial">
        <b>Tiempo de Ejecución del Trabajo:</b> 30 días hábiles.<br>
        70% del costo total para la formalización del pedido, 30% a la entrega.<br>
        El plazo de entrega comienza a transcurrir una vez realizado el pago.<br>
        <b>No se modifican pedidos ya formalizados (ni tallas ni cantidades).</b>
    </div>
@endsection
