@extends('layouts.pdf')

@section('page-title', 'Reporte de Proveedores')
@section('report-title', 'Reporte General de Proveedores')

@section('extra-styles')
    .col-tipo {
        width: 7%;
        text-align: center;
    }

    .col-documento {
        width: 11%;
    }

    .col-nombre {
        width: 17%;
        font-weight: 600;
    }

    .col-direccion {
        width: 17%;
    }

    .col-telefono {
        width: 10%;
    }

    .col-email {
        width: 14%;
        font-size: 8px;
    }

    .col-contacto {
        width: 10%;
    }

    .col-estatus {
        width: 7%;
        text-align: center;
    }
@endsection

@section('summary-bar')
    <td>
        <span class="label">Total Registros:</span>
        <span class="value">{{ $proveedores->count() }}</span>
    </td>
    <td>
        <span class="label">Activos:</span>
        <span class="value">{{ $proveedores->filter(fn($p) => !$p->trashed())->count() }}</span>
    </td>
    <td>
        <span class="label">Inhabilitados:</span>
        <span class="value">{{ $proveedores->filter(fn($p) => $p->trashed())->count() }}</span>
    </td>
    <td>
        <span class="label">Naturales:</span>
        <span class="value">{{ $proveedores->where('tipo_proveedor', 'natural')->count() }}</span>
    </td>
    <td>
        <span class="label">Jurídicos:</span>
        <span class="value">{{ $proveedores->where('tipo_proveedor', 'juridico')->count() }}</span>
    </td>
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-tipo">Tipo</th>
                <th class="col-documento">Documento</th>
                <th class="col-nombre">Nombre/Razón Social</th>
                <th class="col-direccion">Dirección</th>
                <th class="col-telefono">Teléfono</th>
                <th class="col-email">Email</th>
                <th class="col-contacto">Contacto</th>
                <th class="col-estatus">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($proveedores as $index => $proveedor)
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-tipo">
                        @if($proveedor->tipo_proveedor === 'natural')
                            <span class="badge-natural">Natural</span>
                        @else
                            <span class="badge-juridico">Jurídico</span>
                        @endif
                    </td>
                    <td class="col-documento">{{ $proveedor->documento }}</td>
                    <td class="col-nombre">{{ $proveedor->nombre_completo }}</td>
                    <td class="col-direccion">{{ $proveedor->direccion_unificada }}</td>
                    <td class="col-telefono">{{ $proveedor->telefono_unificado }}</td>
                    <td class="col-email">{{ $proveedor->email_unificado }}</td>
                    <td class="col-contacto">{{ $proveedor->tipo_proveedor === 'natural' ? '-' : $proveedor->contacto }}</td>
                    <td class="col-estatus">
                        <span class="{{ $proveedor->trashed() ? 'badge-inactivo' : 'badge-activo' }}">
                            {{ $proveedor->trashed() ? 'Inhabilitado' : 'Activo' }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection