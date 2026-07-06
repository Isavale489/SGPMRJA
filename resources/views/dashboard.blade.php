@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Dashboard</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if (!empty($recoveryAlert))
        @php
            $isExito  = $recoveryAlert['resultado'] === 'exito';
            $isFallo  = in_array($recoveryAlert['resultado'], ['fallo', 'bloqueado']);
            $alertCls = $isExito ? 'alert-success' : ($isFallo ? 'alert-warning' : 'alert-info');
            $icon     = $isExito ? 'ri-shield-check-line' : 'ri-shield-keyhole-line';
            $titulo   = $isExito
                ? 'Recuperación de contraseña exitosa'
                : 'Intento de recuperación detectado';
        @endphp
        <div class="alert {{ $alertCls }} alert-dismissible fade show d-flex align-items-start gap-2" role="alert">
            <i class="{{ $icon }} fs-4"></i>
            <div class="flex-grow-1">
                <strong>{{ $titulo }}</strong><br>
                <small>
                    Fecha: <strong>{{ $recoveryAlert['fecha'] }}</strong> ·
                    Método: <strong>{{ $recoveryAlert['tipo'] === 'preguntas' ? 'Preguntas de seguridad' : 'Correo electrónico' }}</strong>
                    @if ($recoveryAlert['ip'])
                        · IP: <code>{{ $recoveryAlert['ip'] }}</code>
                    @endif
                    <br>
                    Si no fuiste tú, contacta al administrador de inmediato.
                </small>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    {{-- ═══════ KPIs OPERATIVOS (accionables) ═══════ --}}
    <div class="row">
        <!-- Pedidos por entregar (7 días) -->
        <div class="col-xl-4 col-md-6">
            <a href="{{ url('pedidos') }}" class="text-decoration-none">
                <div class="card card-animate dash-kpi dash-kpi--warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-2">Entregas esta semana</p>
                                <h3 class="fs-22 fw-bold mb-0">{{ $pedidosPorEntregar }}</h3>
                                <span class="text-muted fs-12">pedidos por entregar (7 días)</span>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title rounded fs-3 bg-warning-subtle text-warning">
                                    <i class="ri-truck-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <!-- Insumos con stock bajo -->
        <div class="col-xl-4 col-md-6">
            <a href="{{ route('movimiento-insumo.alertas') }}" class="text-decoration-none">
                <div class="card card-animate dash-kpi dash-kpi--danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-2">Insumos en alerta</p>
                                <h3 class="fs-22 fw-bold mb-0">{{ $insumosStockBajo }}</h3>
                                <span class="text-muted fs-12">stock bajo o agotado</span>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title rounded fs-3 bg-danger-subtle text-danger">
                                    <i class="ri-alert-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <!-- Cotizaciones por vencer -->
        <div class="col-xl-4 col-md-6">
            <a href="{{ url('cotizaciones') }}" class="text-decoration-none">
                <div class="card card-animate dash-kpi dash-kpi--sky">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-2">Cotizaciones por vencer</p>
                                <h3 class="fs-22 fw-bold mb-0">{{ $cotizacionesPorVencer }}</h3>
                                <span class="text-muted fs-12">validez en ≤ 7 días</span>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title rounded fs-3 bg-info-subtle text-info">
                                    <i class="ri-file-list-3-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- ═══════ GRÁFICOS ═══════ --}}
    <div class="row">
        <!-- Estado de Pedidos -->
        <div class="col-xl-5">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">Estado de Pedidos</h4>
                    <a href="{{ url('pedidos') }}" class="text-muted fs-13">Ver todos <i class="ri-arrow-right-line"></i></a>
                </div>
                <div class="card-body">
                    <div id="estadoPedidosChart" class="ag-chart-box"></div>
                </div>
            </div>
        </div>
        <!-- Tendencia: Pedidos por Mes -->
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">Pedidos por Mes</h4>
                    <span class="text-muted fs-13">Últimos 6 meses</span>
                </div>
                <div class="card-body">
                    <div id="tendenciaPedidosChart" class="ag-chart-box"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════ MAESTROS (resumen secundario) ═══════ --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-3">
                    <div class="row text-center">
                        <div class="col-6 col-md-3 dash-mini">
                            <a href="{{ url('clientes') }}" class="text-decoration-none d-block">
                                <i class="ri-user-star-line text-primary fs-4"></i>
                                <div class="fs-20 fw-bold text-body">{{ $totalClientes }}</div>
                                <small class="text-muted text-uppercase">Clientes</small>
                            </a>
                        </div>
                        <div class="col-6 col-md-3 dash-mini">
                            <a href="{{ url('productos') }}" class="text-decoration-none d-block">
                                <i class="ri-t-shirt-line text-success fs-4"></i>
                                <div class="fs-20 fw-bold text-body">{{ $totalProductos }}</div>
                                <small class="text-muted text-uppercase">Productos</small>
                            </a>
                        </div>
                        <div class="col-6 col-md-3 dash-mini">
                            <a href="{{ url('empleados') }}" class="text-decoration-none d-block">
                                <i class="ri-user-settings-line text-info fs-4"></i>
                                <div class="fs-20 fw-bold text-body">{{ $totalEmpleados }}</div>
                                <small class="text-muted text-uppercase">Empleados</small>
                            </a>
                        </div>
                        <div class="col-6 col-md-3 dash-mini">
                            <a href="{{ url('proveedores') }}" class="text-decoration-none d-block">
                                <i class="ri-truck-line text-warning fs-4"></i>
                                <div class="fs-20 fw-bold text-body">{{ $totalProveedores }}</div>
                                <small class="text-muted text-uppercase">Proveedores</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/libs/ag-charts/ag-charts-community.min.js') }}"></script>
    <script>
    (function () {
        'use strict';

        // ==========================================
        // DATOS DEL BACKEND
        // ==========================================
        var pedidosLabels = @json($pedidosLabels);
        var pedidosValues = @json($pedidosValues);
        var totalPedidos = {{ $totalPedidos }};

        var tendenciaLabels = @json($tendenciaLabels);
        var tendenciaPedidos = @json($tendenciaPedidos);
        var tendenciaMontos = @json($tendenciaMontos);

        // Color fijo por estado del pedido (no posicional)
        var coloresEstado = {
            'Pendiente':  '#f7b84b',
            'Procesando': '#3577f1',
            'Completado': '#0ab39c',
            'Cancelado':  '#f06548'
        };

        var chartEstados = null, chartTendencia = null;

        // Tema AG Charts según el modo activo; fondo transparente para
        // que el gráfico se integre a la card.
        function temaAg() {
            var dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            return {
                baseTheme: dark ? 'ag-default-dark' : 'ag-default',
                overrides: { common: { background: { visible: false } } }
            };
        }

        function vacio(contenedor, icono, titulo, texto) {
            contenedor.innerHTML = '<div class="text-center py-5">'
                + '<div class="avatar-md mx-auto mb-3">'
                + '<div class="avatar-title bg-soft-light rounded-circle text-muted fs-1"><i class="' + icono + '"></i></div>'
                + '</div>'
                + '<h5 class="text-muted">' + titulo + '</h5>'
                + '<p class="text-muted mb-0">' + texto + '</p>'
                + '</div>';
        }

        function crearCharts() {
            var pedidosContainer = document.getElementById('estadoPedidosChart');
            var tendenciaContainer = document.getElementById('tendenciaPedidosChart');

            if (chartEstados) { chartEstados.destroy(); chartEstados = null; }
            if (chartTendencia) { chartTendencia.destroy(); chartTendencia = null; }

            // ==========================================
            // GRÁFICO 1: ESTADO DE PEDIDOS (DONUT)
            // ==========================================
            if (pedidosContainer && totalPedidos > 0) {
                var datosEstados = pedidosLabels
                    .map(function (estado, i) { return { estado: estado, total: pedidosValues[i] }; })
                    .filter(function (d) { return d.total > 0; });

                chartEstados = agCharts.AgCharts.create({
                    container: pedidosContainer,
                    theme: temaAg(),
                    data: datosEstados,
                    series: [{
                        type: 'donut',
                        angleKey: 'total',
                        calloutLabelKey: 'estado',
                        sectorLabelKey: 'total',
                        innerRadiusRatio: 0.62,
                        cornerRadius: 4,
                        fills: datosEstados.map(function (d) { return coloresEstado[d.estado] || '#74788d'; }),
                        innerLabels: [
                            { text: String(totalPedidos), fontSize: 26, fontWeight: 'bold' },
                            { text: totalPedidos === 1 ? 'pedido' : 'pedidos', fontSize: 12, spacing: 4 }
                        ]
                    }],
                    legend: { position: 'bottom' }
                });
            } else if (pedidosContainer) {
                pedidosContainer.classList.remove('ag-chart-box');
                vacio(pedidosContainer, 'ri-folder-info-line', 'No hay datos suficientes', 'Registra nuevos pedidos para ver estadísticas.');
            }

            // ==========================================
            // GRÁFICO 2: TENDENCIA — PEDIDOS POR MES (ÁREA)
            // ==========================================
            var totalTendencia = tendenciaPedidos.reduce(function (a, b) { return a + b; }, 0);

            if (tendenciaContainer && totalTendencia > 0) {
                var datosTendencia = tendenciaLabels.map(function (mes, i) {
                    return { mes: mes, pedidos: tendenciaPedidos[i], monto: tendenciaMontos[i] || 0 };
                });

                chartTendencia = agCharts.AgCharts.create({
                    container: tendenciaContainer,
                    theme: temaAg(),
                    data: datosTendencia,
                    series: [{
                        type: 'area',
                        xKey: 'mes', yKey: 'pedidos', yName: 'Pedidos',
                        stroke: '#0ab39c', strokeWidth: 3,
                        fill: '#0ab39c', fillOpacity: 0.18,
                        interpolation: { type: 'smooth' },
                        marker: { enabled: true, size: 7, fill: '#0ab39c' },
                        tooltip: {
                            // heading (no title): AG ya pone la categoría como encabezado
                            // automático; declarar además title duplicaba el mes.
                            renderer: function (params) {
                                var d = params.datum;
                                return {
                                    heading: d.mes,
                                    data: [
                                        { label: 'Pedidos', value: String(d.pedidos) },
                                        { label: 'Monto', value: '$ ' + d.monto.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }
                                    ]
                                };
                            }
                        }
                    }],
                    axes: {
                        x: { type: 'category', position: 'bottom' },
                        y: {
                            type: 'number', position: 'left',
                            label: { formatter: function (p) { return Math.round(p.value); } }
                        }
                    },
                    legend: { enabled: false }
                });
            } else if (tendenciaContainer) {
                tendenciaContainer.classList.remove('ag-chart-box');
                vacio(tendenciaContainer, 'ri-line-chart-line', 'Sin pedidos recientes', 'La tendencia aparecerá cuando se registren pedidos.');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            crearCharts();

            // Cambio de tema (luna del header): re-crear con el tema AG correspondiente.
            new MutationObserver(crearCharts)
                .observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
        });
    })();
    </script>
@endpush
