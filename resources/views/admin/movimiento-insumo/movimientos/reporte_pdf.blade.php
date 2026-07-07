@extends('layouts.pdf')

@section('page-title', 'Reporte de Movimientos de Insumos')
@section('report-title', 'Historial de Movimientos de Insumos')

@section('extra-styles')
    /* Anchos + alineación por columna (mismo patrón que compras/insumos).
       Texto a la izquierda, fechas centradas, columnas numéricas a la derecha. */
    .col-fecha  { width: 10%; text-align: center; }
    .col-insumo { width: 20%; font-weight: 600; }
    .col-ant    { width: 9%;  text-align: right; }
    .col-ent    { width: 9%;  text-align: right; }
    .col-sal    { width: 9%;  text-align: right; }
    .col-saldo  { width: 10%; text-align: right; }
    .col-estado { width: 9%;  text-align: center; }
    .col-motivo { width: 13%; }
    .col-user   { width: 8%; }

    /* Encabezados alineados a SU dato: el th del layout base fuerza left con más
       especificidad, así que se fija explícitamente (evita header izq / dato der). */
    .data-table thead th.col-fecha,
    .data-table thead th.col-estado { text-align: center; }
    .data-table thead th.col-ant,
    .data-table thead th.col-ent,
    .data-table thead th.col-sal,
    .data-table thead th.col-saldo { text-align: right; }

    /* Datos +/- con color sutil pero MISMO peso que el resto (tipografía uniforme). */
    .kx-entrada { color: #1a7f5a; }
    .kx-salida  { color: #b42318; }
    .kx-muted   { color: #b0b7c0; }

    /* Badge de estado con el mismo lenguaje visual que los badges del layout base. */
    .est-badge { display: inline-block; padding: 2px 6px; font-size: 8px; font-weight: 600; }
    .est-critico { background-color: #f8d7da; color: #721c24; }
    .est-optimo  { background-color: #d4edda; color: #155724; }
    .est-exceso  { background-color: #cce5ff; color: #004085; }
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
                    // Estado del SALDO RESULTANTE de esta fila (no del stock vivo):
                    // así el badge califica el saldo que muestra la propia fila.
                    $estado = $ins ? $ins->estadoStockPara($mov->stock_nuevo) : 'optimo';
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
                    <td class="col-estado"><span class="est-badge {{ $estadoMap[0] }}">{{ $estadoMap[1] }}</span></td>
                    <td class="col-motivo">{{ $mov->motivo ?: '—' }}</td>
                    <td class="col-user">{{ $mov->creadoPor->name ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
