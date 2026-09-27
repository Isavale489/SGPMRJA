@extends('layouts.pdf')

@section('page-title', 'Reporte de Inspecciones de Calidad')
@section('report-title', 'Reporte de Inspecciones de Calidad')

@section('extra-styles')
    .col-fecha    { width: 12%; }
    .col-orden    { width: 11%; text-align: center; }
    .col-producto { width: 23%; font-weight: 600; }
    .col-cant     { width: 9%;  text-align: center; }
    .col-result   { width: 14%; text-align: center; }
    .col-inspector{ width: 13%; }
    .qc-result {
        display: inline-block;
        vertical-align: middle;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 9px;
        font-weight: 600;
        line-height: 1.3;
    }
    .qc-aprobado  { background: #e7f7ef; color: #1a7f5a; }
    .qc-observado { background: #fff4e0; color: #9a6700; }
    .qc-rechazado { background: #fdecea; color: #b42318; }
    .qc-obs { color: #6b7280; font-style: italic; font-size: 8px; }
@endsection

@section('summary-bar')
    <td>
        <span class="label">Total Inspecciones:</span>
        <span class="value">{{ $inspecciones->count() }}</span>
    </td>
    <td>
        <span class="label">Aprobadas:</span>
        <span class="value">{{ $inspecciones->where('resultado', 'aprobado')->count() }}</span>
    </td>
    <td>
        <span class="label">Con observaciones:</span>
        <span class="value">{{ $inspecciones->where('resultado', 'observado')->count() }}</span>
    </td>
    <td>
        <span class="label">Rechazadas:</span>
        <span class="value">{{ $inspecciones->where('resultado', 'rechazado')->count() }}</span>
    </td>
    <td>
        <span class="label">Unidades conformes:</span>
        <span class="value">{{ number_format($inspecciones->sum('cantidad_aprobada')) }}</span>
    </td>
    <td>
        <span class="label">Unidades defectuosas:</span>
        <span class="value">{{ number_format($inspecciones->sum('cantidad_rechazada')) }}</span>
    </td>
@endsection

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-fecha">Fecha</th>
                <th class="col-orden">Orden / Pedido</th>
                <th class="col-producto">Producto</th>
                <th class="col-cant">Insp.</th>
                <th class="col-cant">Conf.</th>
                <th class="col-cant">Def.</th>
                <th class="col-result">Resultado</th>
                <th class="col-inspector">Inspector</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inspecciones as $index => $insp)
                @php
                    $orden = $insp->ordenProduccion;
                    $ref = $orden && $orden->pedido_id ? ('Pedido #' . $orden->pedido_id) : ($orden ? ('Orden #' . $orden->id) : '—');
                @endphp
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-fecha">{{ optional($insp->fecha_inspeccion)->format('d/m/Y H:i') ?: '—' }}</td>
                    <td class="col-orden">{{ $ref }}</td>
                    <td class="col-producto">
                        {{ $orden->nombre_producto ?? '—' }}
                        @if($insp->observaciones)
                            <br><span class="qc-obs">{{ $insp->observaciones }}</span>
                        @endif
                    </td>
                    <td class="col-cant">{{ $insp->cantidad_inspeccionada }}</td>
                    <td class="col-cant">{{ $insp->cantidad_aprobada }}</td>
                    <td class="col-cant">{{ $insp->cantidad_rechazada }}</td>
                    <td class="col-result">
                        <span class="qc-result qc-{{ $insp->resultado }}">
                            {{ \App\Models\ControlCalidad::RESULTADOS[$insp->resultado] ?? ucfirst($insp->resultado) }}
                        </span>
                    </td>
                    <td class="col-inspector">{{ $insp->inspector->name ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:18px; color:#6b7280;">
                        No hay inspecciones registradas para los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
