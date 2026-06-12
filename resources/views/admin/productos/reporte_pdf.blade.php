@extends('layouts.pdf')

@section('page-title', 'Catálogo de Tipos de Producto')
@section('report-title', $historial ? 'Catálogo de Tipos (Historial / Inhabilitados)' : 'Catálogo de Tipos de Producto')

@section('extra-styles')
    .col-nombre   { width: 26%; font-weight: 600; }
    .col-prefijo  { width: 12%; text-align: center; }
    .col-precio   { width: 18%; text-align: right; }
    .col-telas    { width: 12%; text-align: center; }
    .col-atrib    { width: 12%; text-align: center; }
    .col-tela-req { width: 14%; text-align: center; }
@endsection

@section('summary-bar')
    <td>
        <span class="label">Total Tipos:</span>
        <span class="value">{{ $tipos->count() }}</span>
    </td>
    <td>
        <span class="label">Con tela:</span>
        <span class="value">{{ $tipos->where('requiere_tela', true)->count() }}</span>
    </td>
    <td>
        <span class="label">Sin tela:</span>
        <span class="value">{{ $tipos->where('requiere_tela', false)->count() }}</span>
    </td>
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-nombre">Tipo</th>
                <th class="col-prefijo">Prefijo</th>
                <th class="col-precio">Precio Confección ($)</th>
                <th class="col-telas">Telas</th>
                <th class="col-atrib">Atributos</th>
                <th class="col-tela-req">¿Requiere tela?</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tipos as $index => $tipo)
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-nombre">{{ $tipo->nombre }}</td>
                    <td class="col-prefijo">{{ $tipo->prefijo }}</td>
                    <td class="col-precio">{{ number_format($tipo->precio_confeccion, 2) }}</td>
                    <td class="col-telas">{{ $tipo->requiere_tela ? $tipo->telas_count : '—' }}</td>
                    <td class="col-atrib">{{ $tipo->atributos_count }}</td>
                    <td class="col-tela-req">
                        <span class="{{ $tipo->requiere_tela ? 'badge-activo' : 'badge-inactivo' }}">
                            {{ $tipo->requiere_tela ? 'Sí' : 'No' }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
