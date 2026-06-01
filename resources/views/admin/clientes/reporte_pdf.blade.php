@extends('layouts.pdf')

@section('page-title', 'Reporte General de Clientes')
@section('report-title', 'Reporte General de Clientes')

@section('extra-styles')
    .col-nombre {
        width: 12%;
        font-weight: 600;
    }

    .col-tipo {
        width: 8%;
    }

    .col-email {
        width: 16%;
    }

    .col-telefono {
        width: 9%;
    }

    .col-documento {
        width: 10%;
    }

    .col-direccion {
        width: 20%;
    }

    .col-ciudad {
        width: 8%;
    }

    .col-estatus {
        width: 6%;
        text-align: center;
    }

    .col-creado {
        width: 8%;
        text-align: center;
    }
@endsection

@section('summary-bar')
    <td>
        <span class="label">Total Registros:</span>
        <span class="value">{{ $clientes->count() }}</span>
    </td>
    <td>
        <span class="label">Activos:</span>
        <span class="value">{{ $clientes->filter(fn($c) => !$c->trashed())->count() }}</span>
    </td>
    <td>
        <span class="label">Inhabilitados:</span>
        <span class="value">{{ $clientes->filter(fn($c) => $c->trashed())->count() }}</span>
    </td>
    <td>
        <span class="label">Naturales:</span>
        <span class="value">{{ $clientes->where('tipo_cliente', 'natural')->count() }}</span>
    </td>
    <td>
        <span class="label">Jurídicos:</span>
        <span class="value">{{ $clientes->where('tipo_cliente', 'juridico')->count() }}</span>
    </td>
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-nombre">Nombre</th>
                <th class="col-tipo">Tipo</th>
                <th class="col-email">Email</th>
                <th class="col-telefono">Teléfono</th>
                <th class="col-documento">Documento</th>
                <th class="col-direccion">Dirección</th>
                <th class="col-ciudad">Municipio</th>
                <th class="col-estatus">Estatus</th>
                <th class="col-creado">Creado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $index => $cliente)
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-nombre">{{ $cliente->nombre }}{{ $cliente->apellido ? ' ' . $cliente->apellido : '' }}
                    </td>
                    <td class="col-tipo">{{ ucfirst($cliente->tipo_cliente) }}</td>
                    <td class="col-email">{{ $cliente->email ?? '—' }}</td>
                    <td class="col-telefono">{{ $cliente->telefono ?? '—' }}</td>
                    <td class="col-documento">{{ $cliente->documento ?? '—' }}</td>
                    <td class="col-direccion">{{ $cliente->direccion ?? '—' }}</td>
                    <td class="col-ciudad">{{ $cliente->ciudad ?? '—' }}</td>
                    <td class="col-estatus">
                        <span class="{{ $cliente->trashed() ? 'badge-inactivo' : 'badge-activo' }}">
                            {{ $cliente->trashed() ? 'Inhabilitado' : 'Activo' }}
                        </span>
                    </td>
                    <td class="col-creado">
                        {{ $cliente->created_at ? \Carbon\Carbon::parse($cliente->created_at)->format('d/m/Y') : '—' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection