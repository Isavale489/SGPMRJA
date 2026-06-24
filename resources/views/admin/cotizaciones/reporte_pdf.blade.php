@extends('layouts.pdf')

@section('page-title', 'Reporte de Cotizaciones')
@section('report-title', 'Reporte General de Cotizaciones')

@section('extra-styles')
    .col-nro {
        width: 10%;
        text-align: center;
    }

    .col-cliente {
        width: 23%;
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
        <span class="value">{{ $cotizaciones->count() }}</span>
    </td>
    <td>
        <span class="label">Monto Total:</span>
        <span class="value">${{ number_format($cotizaciones->sum('total'), 2) }}</span>
    </td>
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-nro">Nro. Cotización</th>
                <th class="col-cliente">Cliente</th>
                <th class="col-fecha">Fecha Cotización</th>
                <th class="col-total">Total ($)</th>
                <th class="col-estado">Estado</th>
                <th class="col-elaborado">Elaborado por</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cotizaciones as $index => $cotizacion)
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-nro">{{ $cotizacion->id }}</td>
                    <td class="col-cliente">{{ $cotizacion->cliente ? $cotizacion->cliente->nombre : 'Sin cliente' }}</td>
                    <td class="col-fecha">{{ \Carbon\Carbon::parse($cotizacion->fecha_cotizacion)->format('d/m/Y') }}</td>
                    <td class="col-total">{{ number_format($cotizacion->total, 2) }}</td>
                    <td class="col-estado">{{ $cotizacion->estado }}</td>
                    <td class="col-elaborado">{{ $cotizacion->user->name ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection