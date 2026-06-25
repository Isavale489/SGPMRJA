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
    .col-num      { width: 4%;    text-align: center; }
    .col-codigo   { width: 12%; }
    .col-prod     { width: 40%; }
    .col-talla    { width: 9%;    text-align: center; }
    .col-cant     { width: 8%;    text-align: center; }
    .col-punit    { width: 13.5%; text-align: right; }
    .col-monto    { width: 13.5%; text-align: right; }

    /* Código del producto (SKU): legible y sin cortarse */
    .col-codigo .prod-code {
        font-family: 'DejaVu Sans Mono', monospace;
        font-size: 8px;
        color: #1e3c72;
        font-weight: bold;
        word-break: break-all;
    }
    .col-prod strong { color: #2d3436; }
    .col-prod small  { color: #6b7280; }
    .col-prod .desc-line { color: #6b7280; font-style: italic; }
    .col-prod .bord-line { color: #1e3c72; }

    .data-table tbody td.text-center { text-align: center; }
    .data-table tbody td.text-right  { text-align: right; }
    .data-table tbody td .bs-eq { color: #777777; font-size: 8px; font-weight: normal; }

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
        width: 260px;
        border: none;
    }

    .totals-inner td {
        border: none;
        padding: 3px 0;
        font-size: 10px;
    }

    .totals-inner .t-label {
        color: #3d4852;
    }

    .totals-inner .t-value {
        text-align: right;
        font-weight: bold;
        color: #1e3c72;
    }

    .totals-inner .t-grand {
        font-size: 12px;
        color: #064e3b;
        border-top: 1.5px solid #1e3c72;
        padding-top: 5px;
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

    {{-- ═══════ Tabla de productos ═══════ --}}
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-codigo">Código</th>
                <th class="col-prod">Producto</th>
                <th class="col-talla">Talla</th>
                <th class="col-cant">Cant.</th>
                <th class="col-punit">P. Unit.</th>
                <th class="col-monto">Subtotal</th>
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
                    if ($detalle->genero) {
                        $variantPartes[] = 'Género: ' . $detalle->genero->nombre;
                    }
                    if (is_array($telaSnap) && !empty($telaSnap['nombre'])) {
                        $variantPartes[] = 'Tela: ' . $telaSnap['nombre'];
                    }
                    if (is_array($atrSnap)) {
                        foreach ($atrSnap as $atrNombre => $valNombre) {
                            $variantPartes[] = $atrNombre . ': ' . $valNombre;
                        }
                    }
                    // Código y nombre robustos: línea legacy (producto) o dinámica
                    // (sin producto_id → tipo + sku_snapshot). Evita el "-" y los
                    // warnings de leer ->nombre sobre null.
                    $prodCodigo = optional($detalle->producto)->codigo ?? ($detalle->sku_snapshot ?: '—');
                    $prodNombre = optional($detalle->producto)->nombre ?? (optional($detalle->tipoProducto)->nombre ?? 'Variante');
                @endphp
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-codigo"><span class="prod-code">{{ $prodCodigo }}</span></td>
                    <td class="col-prod">
                        <strong>{{ $prodNombre }}</strong>
                        @if(!empty($variantPartes))<br><small>{{ implode(' · ', $variantPartes) }}</small>@endif
                        @if($detalle->descripcion)<br><small class="desc-line">{{ $detalle->descripcion }}</small>@endif
                        @if($detalle->lleva_bordado)
                            <br><small class="bord-line"><b>Bordado:</b> {{ $logosTexto ?: ($detalle->nombre_logo ?: 's/logo') }}@if($ubicacionesTexto) — {{ $ubicacionesTexto }}@endif @if($cantidadBordados)(x{{ $cantidadBordados }})@endif</small>
                        @endif
                    </td>
                    <td class="col-talla text-center">{{ $detalle->talla?->etiqueta ?? '—' }}</td>
                    <td class="col-cant text-center">{{ $detalle->cantidad }}</td>
                    <td class="col-punit text-right">
                        $ {{ number_format($detalle->precio_unitario, 2) }}
                        @if($tasaValor)<br><span class="bs-eq">Bs {{ number_format($detalle->precio_unitario * $tasaValor, 2, ',', '.') }}</span>@endif
                    </td>
                    <td class="col-monto text-right">
                        $ {{ number_format($detalle->cantidad * $detalle->precio_unitario, 2) }}
                        @if($tasaValor)<br><span class="bs-eq">Bs {{ number_format($detalle->cantidad * $detalle->precio_unitario * $tasaValor, 2, ',', '.') }}</span>@endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Costo total del servicio de bordado (recargo unitario × cantidad de prendas).
         Ya está incluido en el precio unitario; se desglosa para reflejarlo en Bs. --}}
    @php
        $totalBordadoUsd = $cotizacion->productos->sum(function ($d) {
            if (!$d->lleva_bordado) {
                return 0;
            }
            $recargoUnit = $d->bordados->sum(function ($b) {
                return (float) $b->precio_aplicado * (int) ($b->cantidad ?? 1);
            });
            return $recargoUnit * (int) $d->cantidad;
        });
    @endphp

    {{-- ═══════ Totales ═══════ --}}
    <table class="totals-block">
        <tr>
            <td>&nbsp;</td>
            <td style="width: 260px;">
                <table class="totals-inner">
                    <tr>
                        <td class="t-label">Subtotal:</td>
                        <td class="t-value">$ {{ number_format($subtotal, 2) }}</td>
                    </tr>
                    @if($totalBordadoUsd > 0)
                    <tr>
                        <td class="t-label">Servicio de bordado:</td>
                        <td class="t-value">$ {{ number_format($totalBordadoUsd, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="t-label">Descuento:</td>
                        <td class="t-value">$ {{ number_format($descuento, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="t-label">IVA (16%):</td>
                        <td class="t-value">$ {{ number_format($iva, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="t-label t-grand">Total:</td>
                        <td class="t-value t-grand">$ {{ number_format($totalPagar, 2) }}</td>
                    </tr>
                    @if($tasaValor)
                    <tr>
                        <td class="t-label" style="padding-top:5px;">Tasa aplicada{{ $tasaFecha ? ' (' . \Carbon\Carbon::parse($tasaFecha)->format('d/m/Y') . ')' : '' }}:</td>
                        <td class="t-value" style="padding-top:5px; font-weight:normal;">Bs {{ number_format($tasaValor, 4, ',', '.') }} / USD</td>
                    </tr>
                    <tr>
                        <td class="t-label">Equivalente Bs:</td>
                        <td class="t-value">Bs {{ number_format($totalPagar * $tasaValor, 2, ',', '.') }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    {{-- ═══════ Condiciones y Términos ═══════ --}}
    @if($cotizacion->condiciones_terminos)
    <div class="nota-especial" style="background:#f0f4ff; border-color:#3b5bdb; border-left-color:#1d3557; color:#1d3557;">
        <b>Condiciones y Términos:</b><br>
        {!! nl2br(e($cotizacion->condiciones_terminos)) !!}
    </div>
    @else
    {{-- Términos y condiciones estándar (alineados con el wizard de cotización) --}}
    @php($abonoPct = \App\Models\Pedido::porcentajeAbonoMinimo())
    <div class="nota-especial">
        <b>Condiciones para Pedidos:</b><br>
        &bull; <b>Formalización:</b> abono del {{ $abonoPct }}% del costo total para iniciar la producción.<br>
        &bull; <b>Tiempo de ejecución:</b> 30 días hábiles desde la confirmación del pago inicial.<br>
        &bull; <b>Saldo restante:</b> el {{ 100 - $abonoPct }}% se cancela al momento de la entrega.<br>
        &bull; <b>Modificaciones:</b> una vez formalizado el pedido no se aceptan cambios en tallas, cantidades ni diseño.<br>
        &bull; <b>Entrega:</b> el plazo comienza a contarse desde el abono del {{ $abonoPct }}% inicial.<br>
        <br>
        <b>Servicio de bordado:</b><br>
        &bull; Prendas externas: deben estar limpias y en buen estado.<br>
        &bull; El cliente aprueba diseño, ubicación y tamaño antes de iniciar; luego no hay cambios.<br>
        &bull; Anticipo del 50% para programar y 50% a la entrega; tiempo estimado de 7 a 10 días hábiles.
    </div>
    @endif
@endsection
