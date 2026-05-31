@extends('layouts.pdf')

@section('page-title', 'Reporte de Insumos')
@section('report-title', 'Reporte General de Insumos')

@section('extra-styles')
    .col-nombre-insumo {
        width: 22%;
        font-weight: 600;
    }

    .col-tipo {
        width: 11%;
    }

    .col-unidad {
        width: 10%;
        text-align: center;
    }

    .col-costo {
        width: 12%;
        text-align: right;
    }

    .col-stock-min {
        width: 11%;
        text-align: center;
    }

    .col-stock {
        width: 11%;
        text-align: center;
    }

    .col-stock-max {
        width: 11%;
        text-align: center;
    }

    .col-estatus {
        width: 7%;
        text-align: center;
    }

    .product-image {
        width: 50px;
        height: auto;
        display: block;
        margin: 0 auto;
    }
@endsection

@section('summary-bar')
    <td>
        <span class="label">Total Registros:</span>
        <span class="value">{{ $insumos->count() }}</span>
    </td>
    <td>
        <span class="label">Activos:</span>
        <span class="value">{{ $insumos->where('estado', 1)->count() }}</span>
    </td>
    <td>
        <span class="label">Inactivos:</span>
        <span class="value">{{ $insumos->where('estado', 0)->count() }}</span>
    </td>
    <td>
        <span class="label">Stock Bajo:</span>
        <span class="value">{{ $insumos->filter(fn($i) => $i->stock_actual <= $i->stock_minimo)->count() }}</span>
    </td>
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-nombre-insumo">Nombre</th>
                <th class="col-tipo">Tipo</th>
                <th class="col-unidad">Unidad</th>
                <th class="col-costo">Costo Unit.</th>
                <th class="col-stock-min">Existencia Mín.</th>
                <th class="col-stock">Existencia Actual</th>
                <th class="col-stock-max">Existencia Máx.</th>
                <th class="col-estatus">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($insumos as $index => $insumo)
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-nombre-insumo">{{ $insumo->nombre }}</td>
                    <td class="col-tipo">{{ $insumo->tipo }}</td>
                    <td class="col-unidad">{{ $insumo->unidad_medida }}</td>
                    <td class="col-costo">{{ number_format($insumo->costo_unitario, 2) }}</td>
                    <td class="col-stock-min">{{ $insumo->stock_minimo }}</td>
                    <td class="col-stock">
                        @if($insumo->stock_actual <= $insumo->stock_minimo)
                            <span class="stock-bajo">{{ $insumo->stock_actual }}</span>
                        @elseif($insumo->stock_actual <= $insumo->stock_minimo * 1.5)
                            <span class="stock-medio">{{ $insumo->stock_actual }}</span>
                        @else
                            <span class="stock-normal">{{ $insumo->stock_actual }}</span>
                        @endif
                    </td>
                    <td class="col-stock-max">{{ $insumo->stock_maximo }}</td>
                    <td class="col-estatus">
                        <span class="{{ $insumo->estado ? 'badge-activo' : 'badge-inactivo' }}">
                            {{ $insumo->estado ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection