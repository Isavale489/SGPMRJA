@extends('layouts.pdf')

@section('page-title', 'Reporte de Compras')
@section('report-title', 'Reporte General de Compras')

@section('extra-styles')
    .col-nro    { width: 7%;  text-align: center; }
    .col-prov   { width: 25%; font-weight: 600; }
    .col-fact   { width: 12%; }
    .col-fecha  { width: 13%; text-align: center; }
    .col-total  { width: 18%; text-align: right; }
    .col-estado { width: 11%; text-align: center; }

    .data-table tbody td.text-center { text-align: center; }
    .data-table tbody td.text-right  { text-align: right; }

    .estado-recibida, .estado-borrador, .estado-anulada {
        display: inline-block;
        vertical-align: middle;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 8px;
        font-weight: 600;
        line-height: 1.3;
    }
    .estado-recibida { background-color: #d4edda; color: #155724; }
    .estado-borrador { background-color: #fff3cd; color: #856404; }
    .estado-anulada  { background-color: #f8d7da; color: #721c24; }
@endsection

@section('summary-bar')
    <td>
        <span class="label">Total Registros:</span>
        <span class="value">{{ $compras->count() }}</span>
    </td>
    <td>
        <span class="label">Monto Total:</span>
        <span class="value">{{ number_format($compras->where('estado', '!=', 'anulada')->sum('total'), 2) }}</span>
    </td>
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-nro">N° Compra</th>
                <th class="col-prov">Proveedor</th>
                <th class="col-fact">N° Factura</th>
                <th class="col-fecha">Fecha</th>
                <th class="col-total">Total</th>
                <th class="col-estado">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($compras as $index => $compra)
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-nro">{{ str_pad($compra->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td class="col-prov">{{ $compra->proveedor?->nombre_completo ?? 'N/A' }}</td>
                    <td class="col-fact">{{ $compra->numero_factura ?: 'S/N' }}</td>
                    <td class="col-fecha">{{ $compra->fecha_compra?->format('d/m/Y') }}</td>
                    <td class="col-total">{{ number_format($compra->total, 2) }}</td>
                    <td class="col-estado">
                        <span class="estado-{{ $compra->estado }}">{{ ucfirst($compra->estado) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:14px; color:#888;">
                        No se encontraron compras con los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
