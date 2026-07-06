@extends('admin.layouts.app')

@section('title', 'Reporte de Consumo de Insumos')
@push('styles')
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    {{-- Estilos en public/assets/css/custom.css — sección "MÓDULO REPORTES — Insumos" --}}
@endpush
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Reporte de Consumo de Insumos</h4>
        </div>
    </div>
</div>

{{-- ══ PANEL INTERACTIVO — widgets arrastrables (orden persistente) ══ --}}
<div class="row" id="ins-panel">
    <div class="col-xl-6 rep-widget" data-widget="tipo">
        <div class="card card-reportes">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Consumo por Tipo de Insumo</h4>
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="rep-chart-dl" data-chart="tipo" title="Guardar como imagen"><i class="ri-download-2-line"></i></button>
                    <span class="rep-drag-handle" title="Arrastra para reordenar el panel"><i class="ri-drag-move-2-line"></i></span>
                </div>
            </div>
            <div class="card-body">
                @if ($consumoInsumos->isEmpty())
                    <p class="text-muted text-center py-4 mb-0">
                        <i class="ri-donut-chart-line fs-3 d-block mb-2"></i>
                        Aún no hay consumo de insumos registrado.
                    </p>
                @else
                    <div id="chartConsumoTipo" class="ag-chart-box"></div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-6 rep-widget" data-widget="top">
        <div class="card card-reportes">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Top 10 Insumos Más Utilizados</h4>
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="rep-chart-dl" data-chart="top" title="Guardar como imagen"><i class="ri-download-2-line"></i></button>
                    <span class="rep-drag-handle" title="Arrastra para reordenar el panel"><i class="ri-drag-move-2-line"></i></span>
                </div>
            </div>
            <div class="card-body">
                @if ($consumoInsumos->isEmpty())
                    <p class="text-muted text-center py-4 mb-0">
                        <i class="ri-bar-chart-horizontal-line fs-3 d-block mb-2"></i>
                        Aún no hay consumo de insumos registrado.
                    </p>
                @else
                    <div id="chartTopInsumos" class="ag-chart-box"></div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card card-reportes">
            <div class="card-header">
                <h4 class="card-title mb-0">Detalle de Consumo de Insumos</h4>
            </div>
            <div class="card-body">
                    {{-- Barra unificada de búsqueda + filtros (sección Reportes: tema sky) --}}
                    <div class="advanced-filters-wrapper sky-theme" id="advanced-filters">
                        <div class="navy-filter-header is-collapsed">
                            <div class="navy-header-search">
                                <i class="ri-search-line"></i>
                                <input type="text" class="navy-search-input" id="ins-search"
                                    placeholder="Buscar por nombre de insumo..." autocomplete="off">
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
                                        <label class="navy-filter-label" for="ins-filter-tipo">
                                            <i class="ri-stack-line"></i> Tipo de insumo
                                        </label>
                                        <select class="form-select navy-filter-select" id="ins-filter-tipo">
                                            <option value="">Todos</option>
                                            @foreach($consumoInsumos->pluck('tipo')->unique()->sort() as $tipo)
                                                <option value="{{ $tipo }}">{{ $tipo }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6 text-md-end">
                                        <button type="button" class="btn btn-link" id="btn-clear-filters">Limpiar filtros</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered table-striped table-sm align-middle dt-reportes" id="insumosTable">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Unidad de Medida</th>
                                <th>Total Utilizado</th>
                                <th>Total Órdenes</th>
                                <th>Promedio por Orden</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($consumoInsumos as $insumo)
                            <tr>
                                <td>{{ $insumo->nombre }}</td>
                                <td>{{ $insumo->tipo }}</td>
                                <td>{{ $insumo->unidad_medida }}</td>
                                <td data-order="{{ $insumo->total_utilizado }}">{{ number_format($insumo->total_utilizado, 2) }}</td>
                                <td>{{ $insumo->total_ordenes }}</td>
                                <td data-order="{{ $insumo->total_ordenes > 0 ? $insumo->total_utilizado / $insumo->total_ordenes : 0 }}">
                                    {{ $insumo->total_ordenes > 0 ? number_format($insumo->total_utilizado / $insumo->total_ordenes, 2) : 0 }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
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
<script src="{{ URL::asset('assets/libs/ag-charts/ag-charts-community.min.js') }}"></script>
<script src="{{ URL::asset('assets/libs/sortablejs/Sortable.min.js') }}"></script>
<script>
    function debounce(fn, wait) {
        var t;
        return function () {
            var ctx = this, args = arguments;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(ctx, args); }, wait);
        };
    }

    function escapeRegex(s) {
        return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    document.addEventListener('DOMContentLoaded', function () {
        var tabla = $('#insumosTable').DataTable({
            autoWidth: false,
            language: lenguajeData,
            dom: 'rtip', // búsqueda y filtros viven en la barra unificada
            order: [[3, 'desc']]
        });

        initPanel();
        crearCharts();
        initDescargas();

        // Cambio de tema (luna del header): re-crear con el tema AG correspondiente.
        new MutationObserver(crearCharts)
            .observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });

        // ── Barra unificada: búsqueda + filtros ──
        $('#ins-search').on('input', debounce(function () {
            tabla.search(this.value).draw();
        }, 300));
        $('#ins-filter-tipo').on('change', function () {
            var v = this.value;
            var n = v ? 1 : 0;
            $('#active-filter-count').text(n).toggleClass('d-none', n === 0);
            tabla.column(1).search(v ? '^' + escapeRegex(v) + '$' : '', true, false).draw();
        });
        $('#btn-clear-filters').on('click', function () {
            $('#ins-filter-tipo').val('');
            $('#ins-search').val('');
            $('#active-filter-count').addClass('d-none');
            tabla.search('').column(1).search('').draw();
        });
        $('#filters-collapse-body')
            .on('show.bs.collapse', function () { $('#advanced-filters .navy-filter-header').removeClass('is-collapsed'); })
            .on('hidden.bs.collapse', function () { $('#advanced-filters .navy-filter-header').addClass('is-collapsed'); });

    });

    @php
        $insumosChart = $consumoInsumos->map(fn ($i) => [
            'nombre' => $i->nombre,
            'tipo'   => $i->tipo,
            'total'  => (float) $i->total_utilizado,
        ])->values();
    @endphp
    var insumos = @json($insumosChart);
    var chartTipo = null, chartTop = null;
    var STORAGE_KEY = 'sgpmrja-rep-insumos-layout';

    // ── Panel arrastrable: restaurar orden guardado y activar SortableJS ──
    function initPanel() {
        var panel = document.getElementById('ins-panel');
        if (!panel || typeof Sortable === 'undefined') return;

        try {
            var guardado = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
            guardado.forEach(function (id) {
                var w = panel.querySelector('.rep-widget[data-widget="' + id + '"]');
                if (w) panel.appendChild(w);
            });
        } catch (e) { /* layout corrupto: se ignora y queda el orden por defecto */ }

        new Sortable(panel, {
            animation: 180,
            handle: '.rep-drag-handle',
            ghostClass: 'rep-widget-ghost',
            chosenClass: 'rep-widget-chosen',
            onEnd: function () {
                var orden = Array.prototype.map.call(panel.querySelectorAll('.rep-widget'), function (w) {
                    return w.dataset.widget;
                });
                localStorage.setItem(STORAGE_KEY, JSON.stringify(orden));
            }
        });
    }

    // Exportar cada gráfico como PNG desde el botón del header de su card.
    function initDescargas() {
        document.querySelectorAll('.rep-chart-dl').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var esTipo = btn.dataset.chart === 'tipo';
                var chart = esTipo ? chartTipo : chartTop;
                if (chart) chart.download({ fileName: esTipo ? 'consumo-por-tipo-insumo' : 'top-insumos-utilizados' });
            });
        });
    }

    // Parte nombres largos en líneas de hasta ~16 caracteres (por palabra):
    // el motor de texto de AG respeta \n en las etiquetas del eje.
    function partirEtiqueta(texto) {
        var max = 18;
        var lineas = [], actual = '';
        String(texto).split(' ').forEach(function (palabra) {
            if (actual && (actual + ' ' + palabra).length > max) {
                lineas.push(actual);
                actual = palabra;
            } else {
                actual = actual ? actual + ' ' + palabra : palabra;
            }
        });
        if (actual) lineas.push(actual);
        return lineas.join('\n');
    }

    // Tema AG Charts según el modo activo; fondo transparente para
    // que el gráfico se integre a la card.
    function temaAg() {
        var dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        return {
            baseTheme: dark ? 'ag-default-dark' : 'ag-default',
            overrides: { common: { background: { visible: false } } }
        };
    }

    function crearCharts() {
        var elTipo = document.getElementById('chartConsumoTipo');
        var elTop  = document.getElementById('chartTopInsumos');
        if (!insumos.length) return;

        if (chartTipo) { chartTipo.destroy(); chartTipo = null; }
        if (chartTop)  { chartTop.destroy();  chartTop = null; }

        if (elTipo) {
            var acumulado = {};
            insumos.forEach(function (i) {
                acumulado[i.tipo] = (acumulado[i.tipo] || 0) + i.total;
            });
            var tipos = Object.keys(acumulado).map(function (t) {
                return { tipo: t, total: Math.round(acumulado[t] * 100) / 100 };
            }).sort(function (a, b) { return b.total - a.total; });

            chartTipo = agCharts.AgCharts.create({
                container: elTipo,
                theme: temaAg(),
                data: tipos,
                series: [{
                    type: 'donut',
                    angleKey: 'total',
                    calloutLabelKey: 'tipo',
                    innerRadiusRatio: 0.62,
                    cornerRadius: 4,
                    fills: ['#0ab39c', '#299cdb', '#f7b84b', '#f06548', '#6559cc', '#74788d'],
                    innerLabels: [
                        { text: String(tipos.length), fontSize: 26, fontWeight: 'bold' },
                        { text: tipos.length === 1 ? 'tipo' : 'tipos', fontSize: 12, spacing: 4 }
                    ],
                    tooltip: {
                        // heading (no title): evita que AG duplique la categoría
                        renderer: function (params) {
                            return {
                                heading: params.datum.tipo,
                                data: [{ label: 'Consumo', value: params.datum.total.toFixed(2) }]
                            };
                        }
                    }
                }],
                legend: { position: 'bottom' }
            });
        }

        if (elTop) {
            chartTop = agCharts.AgCharts.create({
                container: elTop,
                theme: temaAg(),
                data: insumos.slice(0, 10),
                series: [{
                    type: 'bar', direction: 'horizontal',
                    xKey: 'nombre', yKey: 'total', yName: 'Cantidad utilizada',
                    fill: '#0ab39c', cornerRadius: 4,
                    tooltip: {
                        // heading (no title): evita que AG duplique la categoría
                        renderer: function (params) {
                            return {
                                heading: params.datum.nombre,
                                data: [{ label: 'Utilizado', value: params.datum.total.toFixed(2) }]
                            };
                        }
                    }
                }],
                // axes es DICCIONARIO desde AG Charts 13 (x = eje del xKey).
                axes: {
                    x: {
                        type: 'category', position: 'left', thickness: 150,
                        label: { formatter: function (p) { return partirEtiqueta(p.value); } }
                    },
                    y: { type: 'number', position: 'bottom' }
                },
                legend: { enabled: false }
            });
        }
    }
</script>
@endpush
