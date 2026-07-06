@extends('admin.layouts.app')

@section('title', 'Reporte de Eficiencia')
@push('styles')
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    {{-- Estilos en public/assets/css/custom.css — sección "MÓDULO REPORTES — Eficiencia (pulso de producción)" --}}
@endpush
@section('content')
@php
    // Umbral compartido barra/chip: ≥90 sano, ≥70 en observación, <70 crítico.
    $nivelEficiencia = fn (?float $ef) => is_null($ef) ? 'na' : ($ef >= 90 ? 'ok' : ($ef >= 70 ? 'warn' : 'bad'));

    $estadoBadge = [
        'Pendiente'  => 'badge-soft-warning',
        'En Proceso' => 'badge-soft-info',
        'Finalizado' => 'badge-soft-success',
        'Cancelado'  => 'badge-soft-danger',
    ];

    $globalNivel = $nivelEficiencia($kpis['eficiencia_global']);
    $intentosGlobal = $kpis['producido'] + $kpis['defectuoso'];
@endphp
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Reporte de Eficiencia</h4>
        </div>
    </div>
</div>

{{-- ══ PULSO DE PRODUCCIÓN — eficiencia global ponderada de todo lo fabricado ══ --}}
<div class="row">
    <div class="col-12">
        <div class="card efi-pulso">
            <div class="card-body">
                <div class="row align-items-center g-4">
                    <div class="col-lg-3 text-center text-lg-start">
                        <p class="efi-pulso-eyebrow mb-1">Pulso de producción</p>
                        @if (is_null($kpis['eficiencia_global']))
                            <h1 class="efi-pulso-valor efi-pulso-valor--na mb-1">—</h1>
                            <p class="efi-pulso-sub mb-0">Aún no hay unidades fabricadas.</p>
                        @else
                            <h1 class="efi-pulso-valor efi-pulso-valor--{{ $globalNivel }} mb-1">{{ $kpis['eficiencia_global'] }}<small>%</small></h1>
                            <p class="efi-pulso-sub mb-0">Eficiencia global ponderada</p>
                        @endif
                    </div>
                    <div class="col-lg-9">
                        @if ($intentosGlobal > 0)
                            <div class="efi-bar efi-bar--hero" role="img"
                                aria-label="{{ $kpis['producido'] }} unidades conformes y {{ $kpis['defectuoso'] }} defectuosas">
                                <span class="efi-bar-ok" style="width: {{ round($kpis['producido'] / $intentosGlobal * 100, 2) }}%"></span>
                                <span class="efi-bar-def" style="width: {{ round($kpis['defectuoso'] / $intentosGlobal * 100, 2) }}%"></span>
                            </div>
                        @else
                            <div class="efi-bar efi-bar--hero efi-bar--vacia" role="img" aria-label="Sin producción registrada"></div>
                        @endif
                        <div class="d-flex flex-wrap align-items-center gap-3 mt-2">
                            <span class="efi-leyenda"><span class="efi-leyenda-dot efi-leyenda-dot--ok"></span>{{ $kpis['producido'] }} conformes</span>
                            <span class="efi-leyenda"><span class="efi-leyenda-dot efi-leyenda-dot--def"></span>{{ $kpis['defectuoso'] }} defectuosas</span>
                            <span class="efi-leyenda ms-auto">{{ $kpis['pedidos_produccion'] }} de {{ $kpis['pedidos_total'] }} pedidos con producción</span>
                        </div>
                        <p class="efi-formula mb-0 mt-2">
                            <i class="ri-information-line me-1"></i>Eficiencia = conformes ÷ (conformes + defectuosas), ponderada por unidades reales — no es promedio de porcentajes.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ TABLA POR PEDIDO — lo crítico primero, drill-down a órdenes ══ --}}
<div class="row">
    <div class="col-xl-12">
        <div class="card card-reportes">
            <div class="card-header">
                <h4 class="card-title mb-0">Eficiencia por Pedido</h4>
            </div>
            <div class="card-body">
                    {{-- Barra unificada de búsqueda + filtros (sección Reportes: tema sky) --}}
                    <div class="advanced-filters-wrapper sky-theme" id="advanced-filters">
                        <div class="navy-filter-header is-collapsed">
                            <div class="navy-header-search">
                                <i class="ri-search-line"></i>
                                <input type="text" class="navy-search-input" id="efi-search"
                                    placeholder="Buscar por pedido o cliente..." autocomplete="off">
                            </div>
                            <div class="navy-header-divider"></div>
                            <button class="navy-filter-btn collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#filters-collapse-body"
                                aria-expanded="false" aria-controls="filters-collapse-body">
                                <i class="ri-filter-3-line"></i>
                                <span>Filtros</span>
                                <span class="navy-filter-badge d-none" id="active-filter-count"></span>
                                <i class="ri-arrow-down-s-line navy-filter-chevron"></i>
                            </button>
                        </div>
                        <div class="collapse" id="filters-collapse-body">
                            <div class="navy-filter-body">
                                {{-- align-items-end: el botón Limpiar comparte fila con el select
                                     (una fila sola debajo agregaba altura muerta al panel) --}}
                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-md-6">
                                        <label class="navy-filter-label" for="efi-filter-nivel">
                                            <i class="ri-speed-line"></i> Nivel de eficiencia
                                        </label>
                                        <select class="form-select navy-filter-select" id="efi-filter-nivel">
                                            <option value="">Todos</option>
                                            <option value="bad">Crítico (menos de 70%)</option>
                                            <option value="warn">En observación (70% a 89%)</option>
                                            <option value="ok">Sano (90% o más)</option>
                                            <option value="na">Sin producción</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6 text-md-end">
                                        <button type="button" class="btn btn-link" id="btn-clear-filters">Limpiar filtros</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered table-striped table-sm align-middle dt-reportes" id="efiPedidosTable">
                        <thead>
                            <tr>
                                <th>Pedido</th>
                                <th>Órdenes</th>
                                <th>Solicitado</th>
                                <th>Producido</th>
                                <th>Defectuoso</th>
                                <th>Eficiencia</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($eficienciaPorPedido as $pedido)
                            @php
                                $nivel = $nivelEficiencia($pedido['eficiencia']);
                                $intentos = $pedido['producido'] + $pedido['defectuoso'];
                            @endphp
                            <tr data-nivel="{{ $nivel }}">
                                <td data-order="{{ $pedido['pedido_id'] }}">
                                    <div class="efi-ped">
                                        <span class="efi-ped-num">Pedido #{{ $pedido['pedido_id'] }}</span>
                                        <span class="efi-ped-cliente">{{ $pedido['cliente'] }}</span>
                                    </div>
                                </td>
                                <td>{{ $pedido['total_ordenes'] }}</td>
                                <td>{{ $pedido['solicitado'] }}</td>
                                <td>{{ $pedido['producido'] }}</td>
                                <td>{{ $pedido['defectuoso'] }}</td>
                                <td data-order="{{ $pedido['eficiencia'] ?? 101 }}">
                                    @if (is_null($pedido['eficiencia']))
                                        <div class="efi-celda">
                                            <div class="efi-bar efi-bar--vacia"></div>
                                            <span class="efi-chip efi-chip--na">Sin producción</span>
                                        </div>
                                    @else
                                        <div class="efi-celda">
                                            <div class="efi-bar">
                                                <span class="efi-bar-ok" style="width: {{ round($pedido['producido'] / $intentos * 100, 2) }}%"></span>
                                                <span class="efi-bar-def" style="width: {{ round($pedido['defectuoso'] / $intentos * 100, 2) }}%"></span>
                                            </div>
                                            <span class="efi-chip efi-chip--{{ $nivel }}">{{ $pedido['eficiencia'] }}%</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-soft-info efi-ver-btn" data-pedido="{{ $pedido['pedido_id'] }}"
                                        title="Ver eficiencia por orden">
                                        <i class="ri-search-eye-line"></i> Ver órdenes
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
            </div>
        </div>
    </div>
</div>

{{-- ══ MODAL DRILL-DOWN — las órdenes del pedido, misma barra a escala de orden ══ --}}
<div class="modal fade atlantico-modal atlantico-modal--reportes" id="efiPedidoModal" tabindex="-1" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0" id="efi-modal-title">Eficiencia del pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Resumen del pedido: cliente + eficiencia ponderada + totales --}}
                <div id="efi-modal-resumen"></div>
                <p class="efi-lista-titulo" id="efi-modal-lista-titulo"></p>
                <div class="efi-modal-lista" id="efi-modal-lista"></div>
                <p class="efi-formula mb-0 mt-3">
                    <i class="ri-information-line me-1"></i>La eficiencia del pedido pondera todas sus órdenes por unidades; cada orden muestra su propio rendimiento.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
<script>
(function () {
    'use strict';

    var pedidos = @json($eficienciaPorPedido->keyBy('pedido_id'));

    var estadoBadge = {
        'Pendiente':  'badge-soft-warning',
        'En Proceso': 'badge-soft-info',
        'Finalizado': 'badge-soft-success',
        'Cancelado':  'badge-soft-danger'
    };

    function escHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function nivel(ef) {
        if (ef === null || ef === undefined) return 'na';
        return ef >= 90 ? 'ok' : (ef >= 70 ? 'warn' : 'bad');
    }

    // Misma barra segmentada del hero y la tabla, a escala de orden.
    function barraHtml(producido, defectuoso) {
        var intentos = producido + defectuoso;
        if (!intentos) {
            return '<div class="efi-bar efi-bar--vacia"></div>';
        }
        var okPct = Math.round(producido / intentos * 10000) / 100;
        return '<div class="efi-bar">'
            + '<span class="efi-bar-ok" style="width:' + okPct + '%"></span>'
            + '<span class="efi-bar-def" style="width:' + (100 - okPct) + '%"></span>'
            + '</div>';
    }

    function chipHtml(ef) {
        if (ef === null || ef === undefined) {
            return '<span class="efi-chip efi-chip--na">Sin producción</span>';
        }
        return '<span class="efi-chip efi-chip--' + nivel(ef) + '">' + ef + '%</span>';
    }

    function debounce(fn, wait) {
        var t;
        return function () {
            var ctx = this, args = arguments;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(ctx, args); }, wait);
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Filtro por nivel: lee el data-nivel de cada fila (ok/warn/bad/na).
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (settings.nTable.id !== 'efiPedidosTable') return true;
            var nivel = $('#efi-filter-nivel').val();
            return !nivel || settings.aoData[dataIndex].nTr.dataset.nivel === nivel;
        });

        var tabla = $('#efiPedidosTable').DataTable({
            autoWidth: false,
            language: lenguajeData,
            dom: 'rtip', // búsqueda y filtros viven en la barra unificada
            // Lo crítico primero: eficiencia ascendente ("Sin producción" = 101, al final).
            order: [[5, 'asc']],
            columnDefs: [{ targets: 6, orderable: false, searchable: false }]
        });

        // ── Barra unificada: búsqueda + filtros ──
        $('#efi-search').on('input', debounce(function () {
            tabla.search(this.value).draw();
        }, 300));
        $('#efi-filter-nivel').on('change', function () {
            var n = this.value ? 1 : 0;
            $('#active-filter-count').text(n).toggleClass('d-none', n === 0);
            tabla.draw();
        });
        $('#btn-clear-filters').on('click', function () {
            $('#efi-filter-nivel').val('');
            $('#efi-search').val('');
            $('#active-filter-count').addClass('d-none');
            tabla.search('').draw();
        });
        $('#filters-collapse-body')
            .on('show.bs.collapse', function () { $('#advanced-filters .navy-filter-header').removeClass('is-collapsed'); })
            .on('hidden.bs.collapse', function () { $('#advanced-filters .navy-filter-header').addClass('is-collapsed'); });

        var modalEl = document.getElementById('efiPedidoModal');
        var modal = new bootstrap.Modal(modalEl);

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.efi-ver-btn');
            if (!btn) return;

            var p = pedidos[btn.dataset.pedido];
            if (!p) return;

            document.getElementById('efi-modal-title').textContent = 'Eficiencia del Pedido #' + p.pedido_id;

            // ── Resumen del pedido: cliente (izq) + % ponderado con su barra (der)
            //    + tiles de totales — la misma metáfora del hero, a escala de pedido.
            var intentos = p.producido + p.defectuoso;
            var nivelPedido = nivel(p.eficiencia);
            var valorHtml = (p.eficiencia === null || p.eficiencia === undefined)
                ? '<div class="efi-resumen-valor efi-resumen-valor--na">—</div>'
                  + '<span class="efi-resumen-leyenda">Aún sin unidades fabricadas</span>'
                : '<div class="efi-resumen-valor efi-resumen-valor--' + nivelPedido + '">' + p.eficiencia + '<small>%</small></div>'
                  + barraHtml(p.producido, p.defectuoso)
                  + '<span class="efi-resumen-leyenda">' + p.producido + ' conformes · ' + p.defectuoso + ' defectuosas de ' + intentos + ' fabricadas</span>';

            document.getElementById('efi-modal-resumen').innerHTML =
                '<div class="efi-resumen">'
                + '<div class="efi-resumen-cliente">'
                + '<span class="efi-eyebrow">Cliente</span>'
                + '<span class="efi-resumen-nombre"><i class="ri-user-3-line me-1"></i>' + escHtml(p.cliente) + '</span>'
                + '<div class="efi-stats">'
                + '<div class="efi-stat"><span>Solicitado</span><b>' + p.solicitado + '</b></div>'
                + '<div class="efi-stat"><span>Producido</span><b>' + p.producido + '</b></div>'
                + '<div class="efi-stat efi-stat--def"><span>Defectuoso</span><b>' + p.defectuoso + '</b></div>'
                + '</div>'
                + '</div>'
                + '<div class="efi-resumen-yield">' + valorHtml + '</div>'
                + '</div>';

            document.getElementById('efi-modal-lista-titulo').textContent =
                p.ordenes.length === 1 ? 'Orden de producción del pedido' : 'Órdenes de producción del pedido (' + p.ordenes.length + ')';

            document.getElementById('efi-modal-lista').innerHTML = p.ordenes.map(function (o) {
                return '<div class="efi-orden">'
                    + '<div class="efi-orden-cab">'
                    + '<div class="efi-orden-titulo">'
                    + '<span class="efi-eyebrow">Orden #' + o.orden_id + '</span>'
                    + '<span class="efi-orden-producto">' + escHtml(o.producto) + '</span>'
                    + '</div>'
                    + '<span class="badge badge-status ' + (estadoBadge[o.estado] || 'badge-soft-secondary') + ' rounded-pill">' + escHtml(o.estado) + '</span>'
                    + '</div>'
                    + '<div class="efi-orden-datos">'
                    + '<span class="efi-orden-cifra">Solicitado <b>' + o.solicitado + '</b></span>'
                    + '<span class="efi-orden-sep"></span>'
                    + '<span class="efi-orden-cifra">Producido <b>' + o.producido + '</b></span>'
                    + '<span class="efi-orden-sep"></span>'
                    + '<span class="efi-orden-cifra">Defectuoso <b>' + o.defectuoso + '</b></span>'
                    + '</div>'
                    + '<div class="efi-celda">' + barraHtml(o.producido, o.defectuoso) + chipHtml(o.eficiencia) + '</div>'
                    + '</div>';
            }).join('');

            modal.show();
        });
    });
})();
</script>
@endpush
