@extends('admin.layouts.app')

@section('title', 'Reporte de Rendimiento de Empleados')
@push('styles')
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" rel="stylesheet"
        type="text/css" />
    {{-- Estilos en public/assets/css/custom.css — sección "MÓDULO REPORTES — Panel interactivo" --}}
@endpush
@section('content')
    @php
        $conProduccion = $rendimientoEmpleados->whereNotNull('eficiencia')->values();
    @endphp
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Reporte de Rendimiento de Empleados</h4>
            </div>
        </div>
    </div>

    {{-- ══ PANEL INTERACTIVO — widgets arrastrables (orden persistente) ══ --}}
    <div class="row" id="emp-panel">
        <div class="col-xl-6 rep-widget" data-widget="produccion">
            <div class="card card-reportes">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">Producción por Empleado</h4>
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="rep-chart-dl" data-chart="produccion" title="Guardar como imagen"><i class="ri-download-2-line"></i></button>
                        <span class="rep-drag-handle" title="Arrastra para reordenar el panel"><i class="ri-drag-move-2-line"></i></span>
                    </div>
                </div>
                <div class="card-body">
                    @if ($conProduccion->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">
                            <i class="ri-bar-chart-line fs-3 d-block mb-2"></i>
                            Aún no hay producción registrada por empleado.
                        </p>
                    @else
                        <div id="chartProduccion" class="rep-chart"></div>
                        <p class="rep-chart-hint mb-0"><i class="ri-cursor-line me-1"></i>Haz clic en una barra para filtrar la tabla por ese empleado.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-6 rep-widget" data-widget="eficiencia">
            <div class="card card-reportes">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">Eficiencia por Empleado</h4>
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="rep-chart-dl" data-chart="eficiencia" title="Guardar como imagen"><i class="ri-download-2-line"></i></button>
                        <span class="rep-drag-handle" title="Arrastra para reordenar el panel"><i class="ri-drag-move-2-line"></i></span>
                    </div>
                </div>
                <div class="card-body">
                    @if ($conProduccion->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">
                            <i class="ri-speed-line fs-3 d-block mb-2"></i>
                            Aún no hay producción registrada por empleado.
                        </p>
                    @else
                        <div id="chartEficiencia" class="rep-chart"></div>
                        <p class="rep-chart-hint mb-0"><i class="ri-cursor-line me-1"></i>Haz clic en una barra para filtrar la tabla por ese empleado.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card card-reportes">
                <div class="card-header">
                    <h4 class="card-title mb-0">Detalle de Rendimiento por Empleado</h4>
                </div>
                <div class="card-body">
                        {{-- Barra unificada de búsqueda (sección Reportes: tema sky; sin filtros —
                             con pocos empleados un filtro extra sería ruido) --}}
                        <div class="advanced-filters-wrapper sky-theme" id="advanced-filters">
                            <div class="navy-filter-header is-collapsed">
                                <div class="navy-header-search">
                                    <i class="ri-search-line"></i>
                                    <input type="text" class="navy-search-input" id="emp-search"
                                        placeholder="Buscar por nombre de empleado..." autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <table class="table table-bordered table-striped table-sm align-middle dt-reportes" id="empleadosTable">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Órdenes</th>
                                    <th>Asignado</th>
                                    <th>Producido</th>
                                    <th>Defectuoso</th>
                                    <th>Eficiencia</th>
                                    <th>Promedio por Orden</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rendimientoEmpleados as $empleado)
                                    <tr>
                                        <td>{{ $empleado['nombre'] }}</td>
                                        <td>{{ $empleado['total_ordenes'] }}</td>
                                        <td>{{ $empleado['total_asignado'] }}</td>
                                        <td>{{ $empleado['total_producido'] }}</td>
                                        <td>{{ $empleado['total_defectuoso'] }}</td>
                                        @if (is_null($empleado['eficiencia']))
                                            <td data-order="-1">
                                                <span class="text-muted fst-italic">Sin producción</span>
                                            </td>
                                        @else
                                            <td data-order="{{ $empleado['eficiencia'] }}">
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar {{ $empleado['eficiencia'] >= 90 ? 'bg-success' : ($empleado['eficiencia'] >= 70 ? 'bg-warning' : 'bg-danger') }}"
                                                        role="progressbar" style="width: {{ $empleado['eficiencia'] }}%;"
                                                        aria-valuenow="{{ $empleado['eficiencia'] }}" aria-valuemin="0"
                                                        aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <span>{{ $empleado['eficiencia'] }}%</span>
                                            </td>
                                        @endif
                                        <td data-order="{{ $empleado['total_ordenes'] > 0 ? $empleado['total_producido'] / $empleado['total_ordenes'] : 0 }}">
                                            {{ $empleado['total_ordenes'] > 0 ? round($empleado['total_producido'] / $empleado['total_ordenes'], 1) : 0 }}
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
    (function () {
        'use strict';

        @php
            $datosChart = $conProduccion->map(fn ($e) => [
                'nombre'     => $e['nombre'],
                'producido'  => $e['total_producido'],
                'defectuoso' => $e['total_defectuoso'],
                'eficiencia' => $e['eficiencia'],
                'ordenes'    => $e['total_ordenes'],
            ])->values();
        @endphp
        var datos = @json($datosChart);

        var STORAGE_KEY = 'sgpmrja-rep-empleados-layout';
        var tabla, chartProd = null, chartEfi = null;

        function debounce(fn, wait) {
            var t;
            return function () {
                var ctx = this, args = arguments;
                clearTimeout(t);
                t = setTimeout(function () { fn.apply(ctx, args); }, wait);
            };
        }

        function colorEficiencia(v) {
            return v >= 90 ? '#0ab39c' : (v >= 70 ? '#f7b84b' : '#f06548');
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

        // Filtrar la tabla al hacer clic en una barra (mismo empleado en ambos charts).
        function filtrarPorEmpleado(nombre) {
            var input = document.getElementById('emp-search');
            var nuevo = (input.value === nombre) ? '' : nombre; // segundo clic = quitar filtro
            input.value = nuevo;
            tabla.search(nuevo).draw();
            document.getElementById('empleadosTable').closest('.card').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function alClickBarra(event) {
            if (event.datum && event.datum.nombre) filtrarPorEmpleado(event.datum.nombre);
        }

        // Parte nombres largos en líneas de hasta ~16 caracteres (por palabra):
        // el motor de texto de AG respeta \n, así el corte es determinista y no
        // depende de la heurística de wrapping (que no dispara en el apilado).
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

        function ejeNombres() {
            return {
                type: 'category', position: 'left', thickness: 150,
                label: { formatter: function (p) { return partirEtiqueta(p.value); } }
            };
        }

        function crearCharts() {
            var elProd = document.getElementById('chartProduccion');
            var elEfi  = document.getElementById('chartEficiencia');
            if (!elProd || !elEfi || !datos.length) return;

            var filas = Math.max(300, datos.length * 52);
            // La leyenda y el título del eje roban ~64px de alto al área de
            // dibujo: sin compensarlo, las filas quedan sin espacio vertical
            // y el wrapping de nombres largos no se activa (colisión).
            elProd.style.height = (filas + 64) + 'px';
            elEfi.style.height  = filas + 'px';

            if (chartProd) { chartProd.destroy(); chartProd = null; }
            if (chartEfi)  { chartEfi.destroy();  chartEfi = null; }

            // ── Producción: barras horizontales apiladas conformes + defectuosas ──
            chartProd = agCharts.AgCharts.create({
                container: elProd,
                theme: temaAg(),
                data: datos,
                // Tooltip por defecto: en apilado AG arma un bloque por serie;
                // un renderer compartido duplicaba toda la información.
                series: [
                    {
                        type: 'bar', direction: 'horizontal', stacked: true,
                        xKey: 'nombre', yKey: 'producido', yName: 'Conformes',
                        fill: '#0ab39c'
                    },
                    {
                        type: 'bar', direction: 'horizontal', stacked: true,
                        xKey: 'nombre', yKey: 'defectuoso', yName: 'Defectuosas',
                        fill: '#f06548', cornerRadius: 4
                    }
                ],
                // axes es DICCIONARIO desde AG Charts 13 (x = eje del xKey).
                axes: {
                    x: ejeNombres(),
                    y: { type: 'number', position: 'bottom', title: { text: 'Unidades' } }
                },
                legend: { position: 'bottom' },
                listeners: { seriesNodeClick: alClickBarra }
            });

            // ── Eficiencia: barras 0-100 coloreadas por umbral + etiqueta % ──
            chartEfi = agCharts.AgCharts.create({
                container: elEfi,
                theme: temaAg(),
                data: datos,
                series: [{
                    type: 'bar', direction: 'horizontal',
                    xKey: 'nombre', yKey: 'eficiencia', yName: 'Eficiencia',
                    cornerRadius: 4,
                    itemStyler: function (params) {
                        return { fill: colorEficiencia(params.datum.eficiencia) };
                    },
                    // inside-end: con la barra al 100% un label externo cae fuera
                    // del área de dibujo y se corta; adentro siempre es visible.
                    label: {
                        placement: 'inside-end', color: '#ffffff', fontWeight: 'bold', fontSize: 11,
                        formatter: function (params) { return params.value + '%'; }
                    },
                    tooltip: {
                        // heading (no title): AG ya pone la categoría como encabezado
                        // automático; declarar además title duplicaba el nombre.
                        renderer: function (params) {
                            var d = params.datum;
                            return {
                                heading: d.nombre,
                                data: [
                                    { label: 'Eficiencia', value: d.eficiencia + '%' },
                                    { label: 'Conformes', value: String(d.producido) },
                                    { label: 'Defectuosas', value: String(d.defectuoso) }
                                ]
                            };
                        }
                    }
                }],
                axes: {
                    x: ejeNombres(),
                    y: {
                        type: 'number', position: 'bottom', max: 100,
                        label: { formatter: function (params) { return params.value + '%'; } }
                    }
                },
                legend: { enabled: false },
                listeners: { seriesNodeClick: alClickBarra }
            });
        }

        // ── Panel arrastrable: restaurar orden guardado y activar SortableJS ──
        function initPanel() {
            var panel = document.getElementById('emp-panel');
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
                    // AG Charts observa su contenedor: se reajusta solo tras el drop.
                }
            });
        }

        // Exportar cada gráfico como PNG desde el botón del header de su card.
        function initDescargas() {
            document.querySelectorAll('.rep-chart-dl').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var esProd = btn.dataset.chart === 'produccion';
                    var chart = esProd ? chartProd : chartEfi;
                    if (chart) chart.download({ fileName: esProd ? 'produccion-por-empleado' : 'eficiencia-por-empleado' });
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            tabla = $('#empleadosTable').DataTable({
                autoWidth: false,
                language: lenguajeData,
                dom: 'rtip', // la búsqueda vive en la barra unificada
                order: [[5, 'desc']]
            });

            $('#emp-search').on('input', debounce(function () {
                tabla.search(this.value).draw();
            }, 300));

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
