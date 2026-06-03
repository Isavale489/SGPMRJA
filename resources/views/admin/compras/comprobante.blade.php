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
    .col-ins   { width: 30%; }
    .col-tipo  { width: 14%; }
    .col-und   { width: 12%; text-align: center; }
    .col-cant  { width: 12%; text-align: right; }
    .col-punit { width: 14%; text-align: right; }
    .col-sub   { width: 14%; text-align: right; }

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

    /* ── Estado ── */
    .estado-recibida { background-color: #d4edda; color: #155724; padding: 2px 8px; font-size: 8.5px; font-weight: 600; }
    .estado-borrador { background-color: #fff3cd; color: #856404; padding: 2px 8px; font-size: 8.5px; font-weight: 600; }
    .estado-anulada  { background-color: #f8d7da; color: #721c24; padding: 2px 8px; font-size: 8.5px; font-weight: 600; }

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
@endsection

@section('summary-bar')
    <td><span class="label">Proveedor:</span> <span class="value">{{ $compra->proveedor?->nombre_completo ?? 'N/A' }}</span></td>
    <td><span class="label">N° Factura:</span> <span class="value">{{ $compra->numero_factura ?: 'S/N' }}</span></td>
    <td><span class="label">Fecha:</span> <span class="value">{{ $compra->fecha_compra?->format('d/m/Y') }}</span></td>
    <td style="text-align:right;"><span class="label">Total:</span> <span class="value">{{ number_format($compra->total, 2) }}</span></td>
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
                <span class="label">Tipo de Pago:</span> {{ $compra->tipo_pago === 'credito' ? 'Crédito' : 'Contado' }}
                @if($compra->tipo_pago === 'credito' && $compra->fecha_vencimiento)
                    (vence {{ $compra->fecha_vencimiento->format('d/m/Y') }})
                @endif<br>
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
                <th class="col-punit">Costo Unit.</th>
                <th class="col-sub">Subtotal</th>
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
                    <td class="col-cant text-right">{{ number_format($detalle->cantidad, 2) }}</td>
                    <td class="col-punit text-right">{{ number_format($detalle->costo_unitario, 2) }}</td>
                    <td class="col-sub text-right">{{ number_format($detalle->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ═══════ Totales ═══════ --}}
    <table class="totals-block">
        <tr>
            <td>&nbsp;</td>
            <td style="width: 240px;">
                <table class="totals-inner">
                    <tr>
                        <td class="t-label">Subtotal:</td>
                        <td class="t-value">{{ number_format($compra->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="t-label">IVA:</td>
                        <td class="t-value">{{ number_format($compra->iva, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="t-label t-grand">Total:</td>
                        <td class="t-value t-grand">{{ number_format($compra->total, 2) }}</td>
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
@endsection
