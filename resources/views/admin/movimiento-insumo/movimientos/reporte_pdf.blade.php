@extends('layouts.pdf')

@section('page-title', 'Reporte de Movimientos de Insumos')
@section('report-title', 'Historial de Movimientos de Insumos')

@section('extra-styles')
    .col-fecha { width: 13%; }
    .col-insumo { width: 20%; font-weight: 600; }
    .col-tipo { width: 9%; text-align: center; }
    .col-cant { width: 9%; text-align: right; }
    .col-stock { width: 10%; text-align: center; }
    .col-motivo { width: 18%; }
    .col-user { width: 11%; }
    .mov-entrada { color: #1a7f5a; font-weight: 600; }
    .mov-salida { color: #b42318; font-weight: 600; }
@endsection

@section('summary-bar')
    <td>
        <span class="label">Total Movimientos:</span>
        <span class="value">{{ $movimientos->count() }}</span>
    </td>
    <td>
        <span class="label">Entradas:</span>
        <span class="value">{{ $movimientos->where('tipo_movimiento', 'Entrada')->count() }}</span>
    </td>
    <td>
        <span class="label">Salidas:</span>
        <span class="value">{{ $movimientos->where('tipo_movimiento', 'Salida')->count() }}</span>
    </td>
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-fecha">Fecha</th>
                <th class="col-insumo">Insumo</th>
                <th class="col-tipo">Tipo</th>
                <th class="col-cant">Cantidad</th>
                <th class="col-stock">Stock Ant.</th>
                <th class="col-stock">Stock Nuevo</th>
                <th class="col-motivo">Motivo</th>
                <th class="col-user">Usuario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimientos as $index => $mov)
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-fecha">{{ optional($mov->created_at)->format('d/m/Y H:i') }}</td>
                    <td class="col-insumo">{{ $mov->insumo->nombre ?? '—' }}</td>
                    <td class="col-tipo">
                        <span class="{{ $mov->tipo_movimiento === 'Entrada' ? 'mov-entrada' : 'mov-salida' }}">
                            {{ $mov->tipo_movimiento }}
                        </span>
                    </td>
                    <td class="col-cant">{{ number_format($mov->cantidad, 2) }}</td>
                    <td class="col-stock">{{ number_format($mov->stock_anterior, 2) }}</td>
                    <td class="col-stock">{{ number_format($mov->stock_nuevo, 2) }}</td>
                    <td class="col-motivo">{{ $mov->motivo ?: '—' }}</td>
                    <td class="col-user">{{ $mov->creadoPor->name ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
