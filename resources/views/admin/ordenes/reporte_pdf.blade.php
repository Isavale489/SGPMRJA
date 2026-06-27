@extends('layouts.pdf')

@section('page-title', 'Reporte de Órdenes de Producción')
@section('report-title', 'Reporte de Órdenes de Producción')

@section('extra-styles')
    .col-producto { width: 24%; font-weight: 600; }
    .col-pedido { width: 9%; text-align: center; }
    .col-cant { width: 11%; text-align: center; }
    .col-estado { width: 13%; text-align: center; }
    .col-fecha { width: 13%; text-align: center; }
    .op-estado {
        display: inline-block;
        vertical-align: middle;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 600;
        line-height: 1.3;
    }
    .op-Pendiente { background: #fff4e0; color: #9a6700; }
    .op-EnProceso { background: #e7f0ff; color: #0d6efd; }
    .op-Finalizado { background: #e7f7ef; color: #1a7f5a; }
    .op-Cancelado { background: #fdecea; color: #b42318; }
@endsection

@section('summary-bar')
    <td>
        <span class="label">Total Órdenes:</span>
        <span class="value">{{ $ordenes->count() }}</span>
    </td>
    <td>
        <span class="label">En Proceso:</span>
        <span class="value">{{ $ordenes->where('estado', 'En Proceso')->count() }}</span>
    </td>
    <td>
        <span class="label">Finalizadas:</span>
        <span class="value">{{ $ordenes->where('estado', 'Finalizado')->count() }}</span>
    </td>
    <td>
        <span class="label">Canceladas:</span>
        <span class="value">{{ $ordenes->where('estado', 'Cancelado')->count() }}</span>
    </td>
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-producto">Producto</th>
                <th class="col-pedido">Pedido</th>
                <th class="col-cant">Cant. Solic.</th>
                <th class="col-cant">Cant. Prod.</th>
                <th class="col-estado">Estado</th>
                <th class="col-fecha">Inicio</th>
                <th class="col-fecha">Fin Estimada</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ordenes as $index => $orden)
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-producto">{{ $orden->nombre_producto }}</td>
                    <td class="col-pedido">{{ $orden->pedido_id ? ('#' . $orden->pedido_id) : '—' }}</td>
                    <td class="col-cant">{{ $orden->cantidad_solicitada }}</td>
                    <td class="col-cant">{{ $orden->cantidad_producida }}</td>
                    <td class="col-estado">
                        <span class="op-estado op-{{ str_replace(' ', '', $orden->estado) }}">{{ $orden->estado }}</span>
                    </td>
                    <td class="col-fecha">{{ optional($orden->fecha_inicio)->format('d/m/Y') ?: '—' }}</td>
                    <td class="col-fecha">{{ optional($orden->fecha_fin_estimada)->format('d/m/Y') ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
