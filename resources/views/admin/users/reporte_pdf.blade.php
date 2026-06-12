@extends('layouts.pdf')

@section('page-title', 'Reporte de Usuarios')
@section('report-title', 'Reporte General de Usuarios')

@section('extra-styles')
    .col-nombre-user {
        width: 30%;
        font-weight: 600;
    }

    .col-email {
        width: 34%;
    }

    .col-rol {
        width: 20%;
        text-align: center;
    }

    .col-estatus {
        width: 12%;
        text-align: center;
    }
@endsection

@section('summary-bar')
    <td>
        <span class="label">Total Registros:</span>
        <span class="value">{{ $users->count() }}</span>
    </td>
    <td>
        <span class="label">Activos:</span>
        <span class="value">{{ $users->where('estado', 1)->count() }}</span>
    </td>
    <td>
        <span class="label">Inactivos:</span>
        <span class="value">{{ $users->where('estado', 0)->count() }}</span>
    </td>
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-nombre-user">Nombre</th>
                <th class="col-email">Email</th>
                <th class="col-rol">Rol</th>
                <th class="col-estatus">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $index => $user)
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-nombre-user">{{ $user->name }}</td>
                    <td class="col-email">{{ $user->email }}</td>
                    <td class="col-rol">{{ $user->role }}</td>
                    <td class="col-estatus">
                        <span class="{{ $user->estado ? 'badge-activo' : 'badge-inactivo' }}">
                            {{ $user->estado ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
