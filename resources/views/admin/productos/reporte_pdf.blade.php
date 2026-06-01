@extends('layouts.pdf')

@section('page-title', 'Reporte de Productos')
@section('report-title', 'Reporte General de Productos')

@section('extra-styles')
    .col-imagen {
        width: 8%;
        text-align: center;
    }

    .col-nombre {
        width: 18%;
        font-weight: 600;
    }

    .col-modelo {
        width: 12%;
    }

    .col-descripcion {
        width: 25%;
    }

    .col-precio {
        width: 12%;
        text-align: right;
    }

    .col-estatus {
        width: 8%;
        text-align: center;
    }

    .col-creado {
        width: 10%;
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
        <span class="value">{{ $productos->count() }}</span>
    </td>
    <td>
        <span class="label">Activos:</span>
        <span class="value">{{ $productos->where('estado', 1)->count() }}</span>
    </td>
    <td>
        <span class="label">Inactivos:</span>
        <span class="value">{{ $productos->where('estado', 0)->count() }}</span>
    </td>
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-imagen">Imagen</th>
                <th class="col-nombre">Nombre</th>
                <th class="col-descripcion">Descripción</th>
                <th class="col-precio">Precio Base ($)</th>
                <th class="col-estatus">Estado</th>
                <th class="col-creado">Fecha Creación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $index => $producto)
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-imagen">
                        @if($producto->imagen)
                            <img src="{{ public_path($producto->imagen) }}" alt="Imagen Producto" class="product-image">
                        @else
                            Sin imagen
                        @endif
                    </td>
                    <td class="col-nombre">{{ $producto->nombre }}</td>
                    <td class="col-descripcion">{{ $producto->descripcion }}</td>
                    <td class="col-precio">{{ number_format($producto->precio_base, 2) }}</td>
                    <td class="col-estatus">
                        <span class="{{ $producto->estado ? 'badge-activo' : 'badge-inactivo' }}">
                            {{ $producto->estado ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="col-creado">{{ \Carbon\Carbon::parse($producto->created_at)->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection