@extends('admin.layouts.app')

@section('title', 'Reporte de Producción')
@push('styles')
    {{-- Estilos en public/assets/css/custom.css — sección "MÓDULO REPORTES — AG Charts" --}}
@endpush
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Reporte de Producción</h4>
        </div>
    </div>
</div>

{{-- ══ PANEL INTERACTIVO — widgets arrastrables (orden persistente) ══ --}}
<div class="row" id="prod-panel">
    <div class="col-xl-6 rep-widget" data-widget="estados">
        <div class="card card-reportes">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Órdenes por Estado</h4>
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="rep-chart-dl" data-chart="estados" title="Guardar como imagen"><i class="ri-download-2-line"></i></button>
                    <span class="rep-drag-handle" title="Arrastra para reordenar el panel"><i class="ri-drag-move-2-line"></i></span>
                </div>
            </div>
            <div class="card-body">
                @if ($ordenesPorEstado->isEmpty())
                    <p class="text-muted text-center py-4 mb-0">
                        <i class="ri-donut-chart-line fs-3 d-block mb-2"></i>
                        Aún no hay órdenes de producción registradas.
                    </p>
                @else
                    <div id="chartEstados" class="ag-chart-box"></div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-6 rep-widget" data-widget="mensual">
        <div class="card card-reportes">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Producción Mensual</h4>
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="rep-chart-dl" data-chart="mensual" title="Guardar como imagen"><i class="ri-download-2-line"></i></button>
                    <span class="rep-drag-handle" title="Arrastra para reordenar el panel"><i class="ri-drag-move-2-line"></i></span>
                </div>
            </div>
            <div class="card-body">
                @if ($produccionMensual->isEmpty())
                    <p class="text-muted text-center py-4 mb-0">
                        <i class="ri-bar-chart-line fs-3 d-block mb-2"></i>
                        Aún no hay producción registrada.
                    </p>
                @else
                    <div id="chartMensual" class="ag-chart-box"></div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card card-reportes">
            <div class="card-header">
                <h4 class="card-title mb-0">Estadísticas de Producción</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-centered table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mes</th>
                                <th>Año</th>
                                <th>Total Producido</th>
                                <th>Total Defectuoso</th>
                                <th>Eficiencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($produccionMensual as $produccion)
                            <tr>
                                <td>{{ $produccion->mes_nombre }}</td>
                                <td>{{ $produccion->anio }}</td>
                                <td>{{ $produccion->total_producido }}</td>
                                <td>{{ $produccion->total_defectuoso }}</td>
                                <td>
                                    @if (is_null($produccion->eficiencia))
                                        <span class="text-muted fst-italic">Sin producción</span>
                                    @else
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar {{ $produccion->eficiencia >= 90 ? 'bg-success' : ($produccion->eficiencia >= 70 ? 'bg-warning' : 'bg-danger') }}" role="progressbar" style="width: {{ $produccion->eficiencia }}%;" aria-valuenow="{{ $produccion->eficiencia }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span>{{ number_format($produccion->eficiencia, 2) }}%</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Aún no hay producción registrada.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- AG Charts Community 14 (UMD local, global agCharts) — PILOTO en este
     reporte; el panel de Empleados usa ECharts (zoom/pan/export nativos). --}}
<script src="{{ URL::asset('assets/libs/ag-charts/ag-charts-community.min.js') }}"></script>
<script src="{{ URL::asset('assets/libs/sortablejs/Sortable.min.js') }}"></script>
<script>
(function () {
    'use strict';

    var STORAGE_KEY = 'sgpmrja-rep-produccion-layout';

    var estados = @json($ordenesPorEstado->map(fn ($o) => ['estado' => $o->estado, 'total' => (int) $o->total])->values());

    @php
        $mensualChart = $produccionMensual->reverse()->values()->map(fn ($p) => [
            'mes'        => mb_substr($p->mes_nombre, 0, 3) . '-' . $p->anio,
            'producido'  => (int) $p->total_producido,
            'defectuoso' => (int) $p->total_defectuoso,
        ]);
    @endphp
    var mensual = @json($mensualChart);

    var coloresEstado = {
        'Pendiente':  '#f7b84b',
        'En Proceso': '#299cdb',
        'Finalizado': '#0ab39c',
        'Cancelado':  '#f06548'
    };

    var chartEstados = null, chartMensual = null;

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
        var elEstados = document.getElementById('chartEstados');
        var elMensual = document.getElementById('chartMensual');

        if (chartEstados) { chartEstados.destroy(); chartEstados = null; }
        if (chartMensual) { chartMensual.destroy(); chartMensual = null; }

        if (elEstados && estados.length) {
            var orden = estados.slice().sort(function (a, b) { return b.total - a.total; });
            var totalOrdenes = orden.reduce(function (s, d) { return s + d.total; }, 0);

            chartEstados = agCharts.AgCharts.create({
                container: elEstados,
                theme: temaAg(),
                data: orden,
                series: [{
                    type: 'donut',
                    angleKey: 'total',
                    calloutLabelKey: 'estado',
                    sectorLabelKey: 'total',
                    innerRadiusRatio: 0.62,
                    cornerRadius: 4,
                    fills: orden.map(function (d) { return coloresEstado[d.estado] || '#74788d'; }),
                    innerLabels: [
                        { text: String(totalOrdenes), fontSize: 26, fontWeight: 'bold' },
                        { text: 'órdenes', fontSize: 12, spacing: 4 }
                    ]
                }],
                legend: { position: 'bottom' }
            });
        }

        if (elMensual && mensual.length) {
            chartMensual = agCharts.AgCharts.create({
                container: elMensual,
                theme: temaAg(),
                data: mensual,
                series: [
                    { type: 'bar', xKey: 'mes', yKey: 'producido',  yName: 'Producido',  fill: '#0ab39c', cornerRadius: 4 },
                    { type: 'bar', xKey: 'mes', yKey: 'defectuoso', yName: 'Defectuoso', fill: '#f06548', cornerRadius: 4 }
                ],
                // axes es DICCIONARIO desde AG Charts 13 (x = eje del xKey).
                axes: {
                    x: { type: 'category', position: 'bottom' },
                    y: { type: 'number', position: 'left', title: { text: 'Unidades' } }
                },
                legend: { position: 'bottom' }
            });
        }
    }

    // ── Panel arrastrable: restaurar orden guardado y activar SortableJS ──
    function initPanel() {
        var panel = document.getElementById('prod-panel');
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
                var esEstados = btn.dataset.chart === 'estados';
                var chart = esEstados ? chartEstados : chartMensual;
                if (chart) chart.download({ fileName: esEstados ? 'ordenes-por-estado' : 'produccion-mensual' });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initPanel();
        crearCharts();
        initDescargas();

        // Cambio de tema (luna del header): re-crear con el tema AG correspondiente.
        // El tamaño lo gestiona AG Charts solo (observa su contenedor).
        new MutationObserver(crearCharts)
            .observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
    });
})();
</script>
@endpush
