@extends('layouts.pdf')

@section('page-title', 'Reporte de Pedidos')
@section('report-title', 'Reporte General de Pedidos')

@section('extra-styles')
    .col-nro {
        width: 8%;
        text-align: center;
    }

    .col-cliente {
        width: 25%;
        font-weight: 600;
    }

    .col-fecha {
        width: 12%;
        text-align: center;
    }

    .col-total {
        width: 14%;
        text-align: right;
    }

    .col-estado {
        width: 12%;
        text-align: center;
    }

    .col-elaborado {
        width: 18%;
    }
@endsection

@section('summary-bar')
    <td>
        <span class="label">Total Registros:</span>
        <span class="value">{{ $pedidos->count() }}</span>
    </td>
    <td>
        <span class="label">Monto Total:</span>
        <span class="value">${{ number_format($pedidos->sum('total'), 2) }}</span>
    </td>
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-nro">Nro. Pedido</th>
                <th class="col-cliente">Cliente</th>
                <th class="col-fecha">Fecha</th>
                <th class="col-total">Total ($)</th>
                <th class="col-estado">Estado</th>
                <th class="col-elaborado">Elaborado por</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedidos as $index => $pedido)
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-nro">{{ $pedido->id }}</td>
                    <td class="col-cliente">{{ $pedido->cliente_nombre_completo }}</td>
                    <td class="col-fecha">{{ \Carbon\Carbon::parse($pedido->fecha_pedido)->format('d/m/Y') }}</td>
                    <td class="col-total">{{ number_format($pedido->total, 2) }}</td>
                    <td class="col-estado">{{ $pedido->estado }}</td>
                    <td class="col-elaborado">{{ $pedido->user->name ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection