@extends('layouts.pdf')

@section('page-title', 'Reporte de Movimientos de Insumos')
@section('report-title', 'Historial de Movimientos de Insumos')

@section('extra-styles')
    .col-fecha { width: 11%; }
    .col-insumo { width: 15%; font-weight: 600; }
    .col-ant { width: 9%; text-align: right; }
    .col-ent { width: 9%; text-align: right; }
    .col-sal { width: 9%; text-align: right; }
    .col-saldo { width: 9%; text-align: right; font-weight: 700; }
    .col-actual { width: 9%; text-align: right; }
    .col-estado { width: 8%; text-align: center; }
    .col-motivo { width: 10%; }
    .col-user { width: 8%; }
    .kx-entrada { color: #1a7f5a; font-weight: 600; }
    .kx-salida { color: #b42318; font-weight: 600; }
    .kx-muted { color: #9ca3af; }
    .est-badge { display: inline-block; padding: 1px 6px; border-radius: 8px; font-size: 8px; font-weight: 700; }
    .est-critico { background: #fde2e1; color: #b42318; }
    .est-optimo { background: #d7f5e6; color: #1a7f5a; }
    .est-exceso { background: #dbeafe; color: #1e40af; }
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
                <th class="col-ant">Stock Ant.</th>
                <th class="col-ent">Entrada (+)</th>
                <th class="col-sal">Salida (−)</th>
                <th class="col-saldo">Saldo Result.</th>
                <th class="col-actual">Stock Actual</th>
                <th class="col-estado">Estado</th>
                <th class="col-motivo">Motivo</th>
                <th class="col-user">Usuario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimientos as $index => $mov)
                @php
                    $esEntrada = $mov->tipo_movimiento === 'Entrada';
                    $ins = $mov->insumo;
                    $estado = $ins ? $ins->estadoStock() : 'optimo';
                    $estadoMap = [
                        'critico' => ['est-critico', 'Crítico'],
                        'optimo'  => ['est-optimo', 'Óptimo'],
                        'exceso'  => ['est-exceso', 'Exceso'],
                    ][$estado];
                @endphp
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-fecha">{{ optional($mov->created_at)->format('d/m/Y H:i') }}</td>
                    <td class="col-insumo">{{ $ins->nombre ?? '—' }}</td>
                    <td class="col-ant">{{ number_format($mov->stock_anterior, 2) }}</td>
                    <td class="col-ent kx-entrada">{!! $esEntrada ? '+' . number_format($mov->cantidad, 2) : '<span class="kx-muted">–</span>' !!}</td>
                    <td class="col-sal kx-salida">{!! !$esEntrada ? '−' . number_format($mov->cantidad, 2) : '<span class="kx-muted">–</span>' !!}</td>
                    <td class="col-saldo">{{ number_format($mov->stock_nuevo, 2) }}</td>
                    <td class="col-actual">{{ $ins ? number_format($ins->stock_actual, 2) : '—' }}</td>
                    <td class="col-estado"><span class="est-badge {{ $estadoMap[0] }}">{{ $estadoMap[1] }}</span></td>
                    <td class="col-motivo">{{ $mov->motivo ?: '—' }}</td>
                    <td class="col-user">{{ $mov->creadoPor->name ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
