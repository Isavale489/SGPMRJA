@extends('layouts.pdf')

@section('page-title', 'Compra #' . $compra->id)
@section('report-title', 'Comprobante de Compra N° ' . str_pad($compra->id, 5, '0', STR_PAD_LEFT))

@section('extra-styles')
    /* ── Bloque de datos (proveedor / compra) ── */
    .info-block { width: 100%; border: none; margin-bottom: 12px; }
    .info-block td {
        border: none;
        padding: 0 14px 0 0;
        font-size: 9.5px;
        line-height: 1.7;
        vertical-align: top;
        width: 50%;
    }
    .info-block .label { font-weight: bold; color: #1e3c72; }

    /* ── Anchos de columnas de la tabla de ítems ── */
    .col-ins   { width: 28%; }
    .col-tipo  { width: 13%; }
    .col-und   { width: 10%; text-align: center; }
    .col-cant  { width: 11%; text-align: right; }
    .col-punit { width: 13%; text-align: right; }
    .col-iva   { width: 11%; text-align: center; }
    .col-sub   { width: 14%; text-align: right; }

    /* ── Marca IVA por línea ── */
    .iva-grav { color: #155724; font-weight: 600; }
    .iva-exento { color: #6c757d; font-size: 8px; text-transform: uppercase; letter-spacing: 0.4px; }

    .data-table tbody td.text-center { text-align: center; }
    .data-table tbody td.text-right  { text-align: right; }

    /* ── Pill de tipo de insumo ── */
    .tipo-pill {
        background-color: #eef2fb;
        color: #1e3c72;
        padding: 2px 7px;
        font-size: 8px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    /* ── Bloque de totales ── */
    .totals-block { width: 100%; border: none; margin-top: 10px; margin-bottom: 14px; }
    .totals-block > tbody > tr > td { border: none; padding: 0; }
    .totals-inner { width: 240px; border: none; }
    .totals-inner td {
        border: none;
        padding: 3px 0;
        font-size: 10px;
    }
    .totals-inner .t-label { color: #3d4852; }
    .totals-inner .t-value { text-align: right; font-weight: bold; color: #1e3c72; }
    .totals-inner .t-grand {
        font-size: 12px;
        color: #064e3b;
        border-top: 1.5px solid #1e3c72;
        padding-top: 5px;
    }

    /* ── Estado (badge alineado verticalmente con la etiqueta) ── */
    .estado-recibida, .estado-borrador, .estado-anulada {
        display: inline-block;
        vertical-align: middle;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 8.5px;
        font-weight: 600;
        line-height: 1.3;
    }
    .estado-recibida { background-color: #d4edda; color: #155724; }
    .estado-borrador { background-color: #fff3cd; color: #856404; }
    .estado-anulada  { background-color: #f8d7da; color: #721c24; }

    /* ── Nota de observaciones ── */
    .nota-obs {
        background: #f8f9fc;
        border: 1px solid #dde2e8;
        border-left: 3px solid #1e3c72;
        padding: 8px 12px;
        font-size: 9px;
        color: #2d3436;
        margin-top: 4px;
    }

    /* ── Bloque anclado al pie de la página (montos + observaciones) ── */
    .doc-bottom {
        position: fixed;
        left: 30px;
        right: 30px;
        bottom: 42px;
    }
@endsection

@section('summary-bar')
    @php
        $totalBsBar = $compra->detalles->sum(fn($d) => $d->cantidad * $d->costo_unitario_bs)
            + round($compra->detalles->where('aplica_iva', true)->sum(fn($d) => $d->cantidad * $d->costo_unitario_bs) * (float) $compra->iva_porcentaje / 100, 2);
    @endphp
    <td><span class="label">Proveedor:</span> <span class="value">{{ $compra->proveedor?->nombre_completo ?? 'N/A' }}</span></td>
    <td><span class="label">N° Factura:</span> <span class="value">{{ $compra->numero_factura ?: 'S/N' }}</span></td>
    <td><span class="label">Fecha:</span> <span class="value">{{ $compra->fecha_compra?->format('d/m/Y') }}</span></td>
    <td style="text-align:right;"><span class="label">Total:</span> <span class="value">Bs {{ number_format($totalBsBar, 2, ',', '.') }}</span></td>
@endsection

@section('content')
    {{-- ═══════ Datos del proveedor y de la compra ═══════ --}}
    <table class="info-block">
        <tr>
            <td>
                <span class="label">Proveedor:</span> {{ $compra->proveedor?->nombre_completo ?? '-' }}<br>
                <span class="label">Documento:</span> {{ $compra->proveedor?->documento ?? '-' }}<br>
                <span class="label">Teléfono:</span> {{ $compra->proveedor?->telefono_unificado ?? '-' }}<br>
                <span class="label">Email:</span> {{ $compra->proveedor?->email_unificado ?? '-' }}
            </td>
            <td>
                <span class="label">N° de Factura:</span> {{ $compra->numero_factura ?: 'S/N' }}<br>
                <span class="label">Fecha de Compra:</span> {{ $compra->fecha_compra?->format('d/m/Y') }}<br>
                <span class="label">Tasa de cambio:</span> {{ $compra->tasa_cambio ? 'Bs ' . number_format($compra->tasa_cambio, 4, ',', '.') . ' / USD' : '—' }}<br>
                <span class="label">Estado:</span>
                <span class="estado-{{ $compra->estado }}">{{ ucfirst($compra->estado) }}</span><br>
                <span class="label">Registrado por:</span> {{ $compra->registradoPor?->name ?? 'Sistema' }}
            </td>
        </tr>
    </table>

    {{-- ═══════ Tabla de ítems ═══════ --}}
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-ins">Insumo</th>
                <th class="col-tipo">Tipo</th>
                <th class="col-und">Unidad</th>
                <th class="col-cant">Cantidad</th>
                <th class="col-punit">Costo Unit. (Bs)</th>
                <th class="col-iva">IVA</th>
                <th class="col-sub">Subtotal (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($compra->detalles as $index => $detalle)
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-ins">{{ $detalle->insumo?->nombre ?? 'N/A' }}</td>
                    <td class="col-tipo">
                        <span class="tipo-pill">{{ $detalle->insumo?->tipo ?? '—' }}</span>
                    </td>
                    <td class="col-und text-center">{{ $detalle->insumo?->unidad_medida ?? '—' }}</td>
                    <td class="col-cant text-right">{{ number_format($detalle->cantidad, 2, ',', '.') }}</td>
                    <td class="col-punit text-right">{{ number_format($detalle->costo_unitario_bs, 2, ',', '.') }}</td>
                    <td class="col-iva">
                        @if($detalle->aplica_iva)
                            <span class="iva-grav">{{ rtrim(rtrim(number_format($compra->iva_porcentaje, 2), '0'), '.') }}%</span>
                        @else
                            <span class="iva-exento">Exento</span>
                        @endif
                    </td>
                    <td class="col-sub text-right">{{ number_format($detalle->cantidad * $detalle->costo_unitario_bs, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ═══════ Pie anclado: totales + observaciones ═══════ --}}
    <div class="doc-bottom">
    {{-- ═══════ Totales ═══════ --}}
    <table class="totals-block">
        <tr>
            <td>&nbsp;</td>
            <td style="width: 260px;">
                @php
                    $ivaPctTxt   = rtrim(rtrim(number_format($compra->iva_porcentaje, 2), '0'), '.');
                    // Montos en Bs (lo pagado), derivados del costo en Bs por línea.
                    $subtotalBs  = $compra->detalles->sum(fn($d) => $d->cantidad * $d->costo_unitario_bs);
                    $baseGravBs  = $compra->detalles->where('aplica_iva', true)->sum(fn($d) => $d->cantidad * $d->costo_unitario_bs);
                    $baseExenBs  = $subtotalBs - $baseGravBs;
                    $ivaBs       = round($baseGravBs * (float) $compra->iva_porcentaje / 100, 2);
                    $totalBs     = $subtotalBs + $ivaBs;
                @endphp
                <table class="totals-inner">
                    <tr>
                        <td class="t-label">Subtotal:</td>
                        <td class="t-value">Bs {{ number_format($subtotalBs, 2, ',', '.') }}</td>
                    </tr>
                    @if($baseExenBs > 0)
                    <tr>
                        <td class="t-label">Base exenta:</td>
                        <td class="t-value">Bs {{ number_format($baseExenBs, 2, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="t-label">IVA ({{ $ivaPctTxt }}%):</td>
                        <td class="t-value">Bs {{ number_format($ivaBs, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="t-label t-grand">Total:</td>
                        <td class="t-value t-grand">Bs {{ number_format($totalBs, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="t-label" style="padding-top:5px;">Tasa aplicada:</td>
                        <td class="t-value" style="padding-top:5px; font-weight:normal;">{{ $compra->tasa_cambio ? 'Bs ' . number_format($compra->tasa_cambio, 4, ',', '.') : '—' }}</td>
                    </tr>
                    <tr>
                        <td class="t-label">Equivalente USD:</td>
                        <td class="t-value">$ {{ number_format($compra->total, 2, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ═══════ Observaciones ═══════ --}}
    @if($compra->observaciones)
        <div class="nota-obs">
            <b>Observaciones:</b><br>
            {!! nl2br(e($compra->observaciones)) !!}
        </div>
    @endif
    </div>{{-- /.doc-bottom --}}
@endsection
