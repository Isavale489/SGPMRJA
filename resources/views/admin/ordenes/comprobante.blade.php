@extends('layouts.pdf')

@php
    $nro = str_pad($orden->id, 5, '0', STR_PAD_LEFT);
    $det = $orden->detallePedido;
    $color  = $det && $det->color ? $det->color->nombre : null;
    $talla  = $det && $det->talla ? ($det->talla->etiqueta ?: $det->talla->nombre) : null;
    $genero = $det && $det->genero ? $det->genero->nombre : null;
    $variante = collect([$color, $talla, $genero])->filter()->implode(' · ') ?: '—';
    $bordados = $det ? $det->bordados : collect();

    // Empleados asignados (responsable principal primero si existe).
    $empleados = $orden->empleadosAsignados
        ->map(fn($e) => optional($e->persona)->nombre_completo ?? ('Empleado #' . $e->id))
        ->filter()->values();
    if ($empleados->isEmpty() && $orden->empleado) {
        $empleados = collect([optional($orden->empleado->persona)->nombre_completo ?? ('Empleado #' . $orden->empleado->id)]);
    }

    $progresoPct = $orden->cantidad_solicitada
        ? round($orden->cantidad_producida / $orden->cantidad_solicitada * 100)
        : 0;
@endphp

@section('page-title', 'Orden de Producción N° ' . $nro)
@section('report-title', 'Orden de Producción N° ' . $nro)

@section('extra-styles')
    /* ── Bloque de datos (orden / pedido) ── */
    .info-block { width: 100%; border: none; margin-bottom: 12px; }
    .info-block td {
        border: none;
        padding: 0 14px 0 0;
        font-size: 9.5px;
        line-height: 1.8;
        vertical-align: top;
        width: 50%;
    }
    .info-block .label { font-weight: bold; color: #1e3c72; }

    /* ── Estado pill ── */
    .op-estado { display: inline-block; vertical-align: middle; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 600; line-height: 1.3; }
    .op-Pendiente  { background: #fff4e0; color: #9a6700; }
    .op-EnProceso  { background: #e7f0ff; color: #0d6efd; }
    .op-Finalizado { background: #e7f7ef; color: #1a7f5a; }
    .op-Cancelado  { background: #fdecea; color: #b42318; }

    /* ── Barra de progreso ── */
    .prog-wrap { background: #eef2f9; border-radius: 6px; height: 12px; width: 100%; overflow: hidden; }
    .prog-fill { background: #1a7f5a; height: 12px; }

    /* ── Título de sección ── */
    .sec-title {
        font-size: 10px;
        font-weight: bold;
        color: #1e3c72;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1.5px solid #1e3c72;
        padding-bottom: 3px;
        margin: 14px 0 8px 0;
    }

    /* ── Anchos de columnas ── */
    .col-ins  { width: 46%; }
    .col-und  { width: 14%; text-align: center; }
    .col-est  { width: 20%; text-align: right; }
    .col-uti  { width: 20%; text-align: right; }

    .col-etapa { width: 28%; }
    .col-emps  { width: 42%; }
    .col-casig { width: 15%; text-align: center; }
    .col-sest  { width: 15%; text-align: center; }

    .data-table tbody td.text-center { text-align: center; }
    .data-table tbody td.text-right  { text-align: right; }

    /* ── Pill / tipo ── */
    .tipo-pill {
        background-color: #eef2fb;
        color: #1e3c72;
        padding: 2px 7px;
        font-size: 8px;
        font-weight: 600;
    }

    /* ── Diseño / Bordado ── */
    .bordado-item {
        border: 1px solid #dde2e8;
        border-left: 3px solid #1e3c72;
        background: #f8f9fc;
        padding: 6px 10px;
        margin-bottom: 6px;
        font-size: 9px;
    }
    .bordado-item .b-label { color: #3d4852; font-weight: 600; }

    /* ── Nota ── */
    .nota-obs {
        background: #f8f9fc;
        border: 1px solid #dde2e8;
        border-left: 3px solid #1e3c72;
        padding: 8px 12px;
        font-size: 9px;
        color: #2d3436;
        margin-top: 4px;
    }
    .nota-cancel {
        background: #fdecea;
        border: 1px solid #f5c6cb;
        border-left: 3px solid #b42318;
        padding: 8px 12px;
        font-size: 9px;
        color: #721c24;
        margin-top: 6px;
    }

    .empty-row { text-align: center; color: #9aa3af; font-style: italic; padding: 8px; }
@endsection

@section('summary-bar')
    <td><span class="label">Estado:</span>
        <span class="op-estado op-{{ str_replace(' ', '', $orden->estado) }}">{{ $orden->estado }}</span>
    </td>
    <td><span class="label">Pedido:</span> <span class="value">{{ $orden->pedido_id ? ('#' . $orden->pedido_id) : '—' }}</span></td>
    <td><span class="label">Cliente:</span> <span class="value">{{ $orden->pedido?->cliente_nombre_completo ?? '—' }}</span></td>
    <td style="text-align:right;"><span class="label">Progreso:</span> <span class="value">{{ $progresoPct }}%</span></td>
@endsection

@section('content')
    {{-- ═══════ Datos de la orden ═══════ --}}
    <table class="info-block">
        <tr>
            <td>
                <span class="label">Producto:</span> {{ $orden->nombre_producto }}<br>
                <span class="label">Variante:</span> {{ $variante }}<br>
                <span class="label">Cant. solicitada:</span> {{ $orden->cantidad_solicitada }} u<br>
                <span class="label">Cant. producida:</span> {{ $orden->cantidad_producida }} u<br>
                <span class="label">Cant. defectuosa:</span> {{ $orden->cantidad_defectuosa }} u
            </td>
            <td>
                <span class="label">Pedido N°:</span> {{ $orden->pedido_id ? ('#' . $orden->pedido_id) : '—' }}<br>
                <span class="label">Cliente:</span> {{ $orden->pedido?->cliente_nombre_completo ?? '—' }}<br>
                <span class="label">{{ $empleados->count() > 1 ? 'Empleados' : 'Empleado' }}:</span>
                    {{ $empleados->isNotEmpty() ? $empleados->implode(', ') : 'Sin asignar' }}<br>
                <span class="label">Creado por:</span> {{ $orden->creadoPor?->name ?? 'Sistema' }}
            </td>
        </tr>
    </table>

    {{-- ═══════ Progreso ═══════ --}}
    <table style="width:100%; border:none; margin-bottom:6px;">
        <tr>
            <td style="border:none; padding:0; font-size:9.5px; width:70%; vertical-align:middle;">
                <div class="prog-wrap"><div class="prog-fill" style="width:{{ $progresoPct }}%;"></div></div>
            </td>
            <td style="border:none; padding:0 0 0 12px; font-size:9.5px; vertical-align:middle;">
                <span class="label" style="color:#1e3c72; font-weight:bold;">{{ $progresoPct }}%</span>
                ({{ $orden->cantidad_producida }} / {{ $orden->cantidad_solicitada }} u)
            </td>
        </tr>
    </table>

    {{-- ═══════ Cronograma ═══════ --}}
    <table class="info-block" style="margin-top:8px; margin-bottom:6px;">
        <tr>
            <td>
                <span class="label">Fecha de inicio:</span> {{ optional($orden->fecha_inicio)->format('d/m/Y') ?: '—' }}<br>
                <span class="label">Fin estimada:</span> {{ optional($orden->fecha_fin_estimada)->format('d/m/Y') ?: '—' }}
            </td>
            <td>
                <span class="label">Fin real:</span>
                {{ $orden->fecha_fin_real ? $orden->fecha_fin_real->format('d/m/Y') : 'Aún en curso' }}
            </td>
        </tr>
    </table>

    {{-- ═══════ Diseño / Bordado ═══════ --}}
    <div class="sec-title">Diseño / Bordado</div>
    @if($bordados->isEmpty())
        <p style="font-size:9px; color:#9aa3af; font-style:italic;">Producto sin bordado / diseño.</p>
    @else
        @foreach($bordados as $b)
            <div class="bordado-item">
                <span class="b-label">Aplicación:</span> {{ $b->nombre_aplicado ?: '—' }} &nbsp;&middot;&nbsp;
                <span class="b-label">Logo:</span> {{ $b->logo->name ?? ($b->nombre_logo_aplicado ?? 'Logo') }} &nbsp;&middot;&nbsp;
                <span class="b-label">Cantidad:</span> {{ $b->cantidad ?: 1 }}
            </div>
        @endforeach
    @endif

    {{-- ═══════ Insumos ═══════ --}}
    <div class="sec-title">Insumos</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-ins">Insumo</th>
                <th class="col-und">Unidad</th>
                <th class="col-est">Estimado</th>
                <th class="col-uti">Utilizado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orden->insumos as $index => $insumo)
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-ins">{{ $insumo->nombre }}</td>
                    <td class="col-und text-center">{{ $insumo->unidad_medida ?: '—' }}</td>
                    <td class="col-est text-right">{{ number_format($insumo->pivot->cantidad_estimada, 2, ',', '.') }}</td>
                    <td class="col-uti text-right">{{ number_format($insumo->pivot->cantidad_utilizada, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty-row">Sin insumos registrados para esta orden.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- ═══════ Sub-órdenes (etapas) ═══════ --}}
    <div class="sec-title">Sub-órdenes / Etapas</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-etapa">Etapa / Tarea</th>
                <th class="col-emps">Empleados asignados</th>
                <th class="col-casig">Cant.</th>
                <th class="col-sest">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orden->subordenes as $index => $sub)
                @php
                    $subEmps = $sub->empleados
                        ->map(fn($e) => optional($e->persona)->nombre_completo ?? ('Emp. #' . $e->id))
                        ->filter()->implode(', ');
                @endphp
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-etapa">{{ $sub->nombre }}</td>
                    <td class="col-emps">{{ $subEmps ?: '—' }}</td>
                    <td class="col-casig text-center">{{ $sub->cantidad_asignada ?: '—' }}</td>
                    <td class="col-sest text-center">
                        <span class="op-estado op-{{ str_replace(' ', '', $sub->estado) }}">{{ $sub->estado }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty-row">Sin sub-órdenes registradas para esta orden.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- ═══════ Notas ═══════ --}}
    @if($orden->notas)
        <div class="sec-title">Notas</div>
        <div class="nota-obs">{!! nl2br(e($orden->notas)) !!}</div>
    @endif

    @if($orden->estado === 'Cancelado' && $orden->motivo_cancelacion)
        <div class="nota-cancel">
            <b>Motivo de cancelación:</b><br>
            {!! nl2br(e($orden->motivo_cancelacion)) !!}
        </div>
    @endif
@endsection
