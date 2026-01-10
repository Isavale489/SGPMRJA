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

    <!-- Widgets de resumen -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted mb-0">Total Insumos</p>
                            <h4 class="fs-22 fw-semibold mb-0">{{ $totalInsumos }}</h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-primary rounded fs-3">
                                <i class="ri-stack-line text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted mb-0">Órdenes en Proceso</p>
                            <h4 class="fs-22 fw-semibold mb-0">{{ $ordenesEnProceso }}</h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-success rounded fs-3">
                                <i class="ri-shopping-bag-line text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted mb-0">Producción Total</p>
                            <h4 class="fs-22 fw-semibold mb-0">{{ $produccionTotal }}</h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-info rounded fs-3">
                                <i class="ri-calendar-check-line text-info"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted mb-0">Alertas Stock</p>
                            <h4 class="fs-22 fw-semibold mb-0">{{ $alertasStock }}</h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-warning rounded fs-3">
                                <i class="ri-alert-line text-warning"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Existencia de Insumos</h4>
                </div>
                <div class="card-body">
                    <canvas id="existenciaChart" class="chartjs-chart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Estado de Órdenes de Producción</h4>
                </div>
                <div class="card-body">
                    <canvas id="ordenesChart" class="chartjs-chart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de últimos movimientos -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Últimos Movimientos de Existencia</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Insumo</th>
                                    <th>Tipo</th>
                                    <th>Cantidad</th>
                                    <th>Stock Nuevo</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ultimosMovimientos as $movimiento)
                                <tr>
                                    <td>{{ $movimiento->insumo ? $movimiento->insumo->nombre : 'N/A' }}</td>
                                    <td>
                                        @if($movimiento->tipo_movimiento == 'Entrada')
                                            <span class="badge bg-success">Entrada</span>
                                        @else
                                            <span class="badge bg-danger">Salida</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($movimiento->cantidad, 2) }}</td>
                                    <td>{{ number_format($movimiento->stock_nuevo, 2) }}</td>
                                    <td>{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Datos para el gráfico de existencia
            const existenciaCtx = document.getElementById('existenciaChart').getContext('2d');
            const existenciaChart = new Chart(existenciaCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($insumos->pluck('nombre')->toArray()) !!},
                    datasets: [{
                        label: 'Stock Actual',
                        data: {!! json_encode($insumos->pluck('stock_actual')->toArray()) !!},
                        backgroundColor: 'rgba(94, 129, 244, 0.8)',
                        borderColor: 'rgba(94, 129, 244, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Stock Mínimo',
                        data: {!! json_encode($insumos->pluck('stock_minimo')->toArray()) !!},
                        backgroundColor: 'rgba(241, 85, 108, 0.8)',
                        borderColor: 'rgba(241, 85, 108, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Datos para el gráfico de órdenes
            const ordenesCtx = document.getElementById('ordenesChart').getContext('2d');
            
            // Contar órdenes por estado
            const pendientes = {{ $ordenesPendientes }};
            const enProceso = {{ $ordenesEnProceso }};
            const finalizadas = {{ $ordenesFinalizadas }};
            const canceladas = {{ $ordenesCanceladas }};
            
            const ordenesChart = new Chart(ordenesCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Pendiente', 'En Proceso', 'Finalizado', 'Cancelado'],
                    datasets: [{
                        data: [pendientes, enProceso, finalizadas, canceladas],
                        backgroundColor: [
                            'rgba(255, 184, 34, 0.8)',   // Amarillo para Pendiente
                            'rgba(10, 179, 156, 0.8)',   // Verde para En Proceso
                            'rgba(94, 129, 244, 0.8)',   // Azul para Finalizado
                            'rgba(241, 85, 108, 0.8)'    // Rojo para Cancelado
                        ],
                        borderColor: [
                            'rgba(255, 184, 34, 1)',
                            'rgba(10, 179, 156, 1)',
                            'rgba(94, 129, 244, 1)',
                            'rgba(241, 85, 108, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
@endpush
