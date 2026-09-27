@extends('layouts.pdf')

@section('page-title', 'Reporte de Empleados')
@section('report-title', 'Reporte General de Empleados')

@section('extra-styles')
    .col-codigo {
        width: 8%;
        text-align: center;
    }

    .col-nombre {
        width: 20%;
        font-weight: 600;
    }

    .col-documento {
        width: 12%;
    }

    .col-cargo {
        width: 15%;
    }

    .col-depto {
        width: 17%;
    }

    .col-fecha {
        width: 10%;
        text-align: center;
    }

    .col-estatus {
        width: 7%;
        text-align: center;
    }
@endsection

@section('summary-bar')
    <td>
        <span class="label">Total Registros:</span>
        <span class="value">{{ $empleados->count() }}</span>
    </td>
    <td>
        <span class="label">Activos:</span>
        <span class="value">{{ $empleados->filter(fn($e) => !$e->trashed())->count() }}</span>
    </td>
    <td>
        <span class="label">Inhabilitados:</span>
        <span class="value">{{ $empleados->filter(fn($e) => $e->trashed())->count() }}</span>
    </td>
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-codigo">Código</th>
                <th class="col-nombre">Nombre Completo</th>
                <th class="col-documento">Documento</th>
                <th class="col-cargo">Cargo</th>
                <th class="col-depto">Departamento</th>
                <th class="col-fecha">Fecha Ingreso</th>
                <th class="col-estatus">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($empleados as $index => $empleado)
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-codigo">{{ $empleado->codigo_empleado }}</td>
                    <td class="col-nombre">{{ $empleado->persona->nombre }}</td>
                    <td class="col-documento">{{ $empleado->persona->tipo_documento }}{{ $empleado->persona->documento_identidad }}</td>
                    <td class="col-cargo">{{ $empleado->cargo?->nombre ?? '—' }}</td>
                    <td class="col-depto">{{ $empleado->departamento?->nombre ?? '—' }}</td>
                    <td class="col-fecha">{{ \Carbon\Carbon::parse($empleado->fecha_ingreso)->format('d/m/Y') }}</td>
                    <td class="col-estatus">
                        <span class="{{ $empleado->trashed() ? 'badge-inactivo' : 'badge-activo' }}">
                            {{ $empleado->trashed() ? 'Inhabilitado' : 'Activo' }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection