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
            <a href="{{ route('inventario.alertas') }}" class="text-decoration-none">
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
                    <div id="estadoPedidosChart"></div>
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
                    <div id="tendenciaPedidosChart"></div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ==========================================
            // DATOS DEL BACKEND
            // ==========================================
            const pedidosLabels = @json($pedidosLabels);
            const pedidosValues = @json($pedidosValues);
            const totalPedidos = {{ $totalPedidos }};

            const tendenciaLabels = @json($tendenciaLabels);
            const tendenciaPedidos = @json($tendenciaPedidos);
            const tendenciaMontos = @json($tendenciaMontos);

            // ==========================================
            // GRÁFICO 1: ESTADO DE PEDIDOS (DONUT)
            // ==========================================
            const pedidosContainer = document.querySelector("#estadoPedidosChart");

            if (totalPedidos > 0 && pedidosContainer) {
                var pedidosOptions = {
                    series: pedidosValues,
                    chart: { type: 'donut', height: 350 },
                    labels: pedidosLabels,
                    colors: ['#3577f1', '#f7b84b', '#0ab39c', '#f06548'],
                    legend: { position: 'bottom' },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '65%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total Pedidos',
                                        fontSize: '14px',
                                        fontWeight: 600,
                                        color: '#878a99'
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            return opts.w.config.series[opts.seriesIndex];
                        }
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: { chart: { height: 280 }, legend: { position: 'bottom' } }
                    }]
                };
                new ApexCharts(pedidosContainer, pedidosOptions).render();
            } else if (pedidosContainer) {
                pedidosContainer.innerHTML = `
                    <div class="text-center py-5">
                        <div class="avatar-md mx-auto mb-3">
                            <div class="avatar-title bg-soft-light rounded-circle text-muted fs-1">
                                <i class="ri-folder-info-line"></i>
                            </div>
                        </div>
                        <h5 class="text-muted">No hay datos suficientes</h5>
                        <p class="text-muted mb-0">Registre nuevos pedidos para ver estadísticas.</p>
                    </div>
                `;
            }

            // ==========================================
            // GRÁFICO 2: TENDENCIA — PEDIDOS POR MES (AREA)
            // ==========================================
            const tendenciaContainer = document.querySelector("#tendenciaPedidosChart");
            const totalTendencia = tendenciaPedidos.reduce((a, b) => a + b, 0);

            if (totalTendencia > 0 && tendenciaContainer) {
                var tendenciaOptions = {
                    series: [{ name: 'Pedidos', data: tendenciaPedidos }],
                    chart: {
                        type: 'area',
                        height: 350,
                        toolbar: { show: false }
                    },
                    colors: ['#0ab39c'],
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 3 },
                    fill: {
                        type: 'gradient',
                        gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 90, 100] }
                    },
                    markers: { size: 4, hover: { size: 6 } },
                    xaxis: {
                        categories: tendenciaLabels,
                        labels: { style: { fontSize: '12px' } }
                    },
                    yaxis: {
                        labels: { formatter: function(val) { return Math.round(val); } }
                    },
                    grid: { borderColor: '#e9ebec', strokeDashArray: 4 },
                    tooltip: {
                        custom: function({ series, seriesIndex, dataPointIndex, w }) {
                            const pedidos = series[seriesIndex][dataPointIndex];
                            const monto = tendenciaMontos[dataPointIndex] ?? 0;
                            const mes = w.globals.labels[dataPointIndex];
                            return '<div class="px-2 py-1">' +
                                   '<div class="fw-semibold mb-1">' + mes + '</div>' +
                                   '<div>Pedidos: <b>' + pedidos + '</b></div>' +
                                   '<div>Monto: <b>$ ' + monto.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</b></div>' +
                                   '</div>';
                        }
                    }
                };
                new ApexCharts(tendenciaContainer, tendenciaOptions).render();
            } else if (tendenciaContainer) {
                tendenciaContainer.innerHTML = `
                    <div class="text-center py-5">
                        <div class="avatar-md mx-auto mb-3">
                            <div class="avatar-title bg-soft-light rounded-circle text-muted fs-1">
                                <i class="ri-line-chart-line"></i>
                            </div>
                        </div>
                        <h5 class="text-muted">Sin pedidos recientes</h5>
                        <p class="text-muted mb-0">La tendencia aparecerá cuando se registren pedidos.</p>
                    </div>
                `;
            }
        });
    </script>
@endpush
