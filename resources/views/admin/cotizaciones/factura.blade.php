@extends('layouts.pdf')

@section('page-title', 'Cotización #' . $cotizacion->id)
@section('report-title', 'Cotización N° ' . str_pad($cotizacion->id, 5, '0', STR_PAD_LEFT))

@section('extra-styles')
    /* ── Bloque de datos (cliente / cotización) ── */
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
    }

    .info-block .label {
        font-weight: bold;
        color: #1e3c72;
    }

    /* ── Anchos de columnas de la tabla de productos ── */
    .col-cant     { width: 6%;  text-align: center; }
    .col-prod     { width: 22%; }
    .col-desc     { width: 16%; }
    .col-talla    { width: 7%;  text-align: center; }
    .col-logo     { width: 13%; }
    .col-ubic     { width: 12%; }
    .col-bord     { width: 6%;  text-align: center; }
    .col-punit    { width: 9%;  text-align: right; }
    .col-monto    { width: 9%;  text-align: right; }

    .data-table tbody td.text-center { text-align: center; }
    .data-table tbody td.text-right  { text-align: right; }

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
    {{-- ═══════ Datos del cliente y de la cotización ═══════ --}}
    <table class="info-block">
        <tr>
            <td>
                <span class="label">Cliente:</span> {{ $cotizacion->cliente->nombre ?? '-' }}
                {{ $cotizacion->cliente->apellido ?? '' }}<br>
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

    {{-- ═══════ Tabla de productos ═══════ --}}
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-cant">Cant.</th>
                <th class="col-prod">Producto</th>
                <th class="col-desc">Descripción</th>
                <th class="col-talla">Talla</th>
                <th class="col-logo">Logo</th>
                <th class="col-ubic">Ubicación</th>
                <th class="col-bord">N° Bord.</th>
                <th class="col-punit">P. Unit.</th>
                <th class="col-monto">Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cotizacion->productos as $index => $detalle)
                @php
                    $ubicacionesTexto = $detalle->bordados->pluck('nombre_aplicado')->implode(', ');
                    $logosTexto = $detalle->bordados
                        ->map(function ($item) {
                            return trim((string) ($item->nombre_logo_aplicado ?? ''));
                        })
                        ->filter()
                        ->unique()
                        ->implode(', ');
                    $cantidadBordados = $detalle->bordados->sum(function ($item) {
                        return (int) ($item->cantidad ?? 1);
                    });
                    // Snapshot inmutable: lo que se cotizó al momento, no lo que está vivo en catálogo
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
                    $variantTexto = !empty($variantPartes) ? ' (' . implode(' · ', $variantPartes) . ')' : '';
                @endphp
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-cant text-center">{{ $detalle->cantidad }}</td>
                    <td class="col-prod">{{ ($detalle->producto->nombre ?? '-') }}{{ $variantTexto }}</td>
                    <td class="col-desc">{{ $detalle->descripcion ?? '-' }}</td>
                    <td class="col-talla text-center">{{ $detalle->talla?->etiqueta ?? '-' }}</td>
                    <td class="col-logo">{{ $detalle->lleva_bordado ? ($logosTexto ?: ($detalle->nombre_logo ?: '-')) : '-' }}</td>
                    <td class="col-ubic">{{ $detalle->lleva_bordado ? ($ubicacionesTexto ?: '-') : '-' }}</td>
                    <td class="col-bord text-center">{{ $detalle->lleva_bordado ? ($cantidadBordados ?: '-') : '-' }}</td>
                    <td class="col-punit text-right">{{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td class="col-monto text-right">{{ number_format($detalle->cantidad * $detalle->precio_unitario, 2) }}</td>
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
                        <td class="t-label">Subtotal:</td>
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
