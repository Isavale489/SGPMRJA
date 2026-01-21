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
                    <h4 class="card-title mb-0">Inventario de Insumos</h4>
                </div>
                <div class="card-body">
                    <canvas id="inventarioChart" class="chartjs-chart" height="300"></canvas>
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
                    <h4 class="card-title mb-0 flex-grow-1">Últimos Movimientos de Inventario</h4>
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
            // Datos para el gráfico de inventario
            const inventarioCtx = document.getElementById('inventarioChart').getContext('2d');
            const inventarioChart = new Chart(inventarioCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($insumos->pluck('nombre')->toArray()) !!},
                    datasets: [{
                        label: 'Stock Actual',
                        data: {!! json_encode($insumos->pluck('stock_actual')->toArray()) !!},
                        backgroundColor: 'rgba(30, 60, 114, 0.6)',
                        borderColor: 'rgba(30, 60, 114, 0.8)',
                        borderWidth: 2
                    }, {
                        label: 'Stock Mínimo',
                        data: {!! json_encode($insumos->pluck('stock_minimo')->toArray()) !!},
                        backgroundColor: 'rgba(220, 53, 69, 0.6)',
                        borderColor: 'rgba(220, 53, 69, 0.8)',
                        borderWidth: 2
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
                            'rgba(30, 60, 114, 0.6)',   // Pendiente - azul suave
                            'rgba(0, 217, 165, 0.6)',   // En Proceso - cyan suave
                            'rgba(46, 204, 113, 0.6)',  // Finalizado - verde suave
                            'rgba(220, 53, 69, 0.6)'    // Cancelado - rojo puro
                        ],
                        borderColor: [
                            'rgba(30, 60, 114, 0.8)',
                            'rgba(0, 217, 165, 0.8)',
                            'rgba(46, 204, 113, 0.8)',
                            'rgba(220, 53, 69, 0.8)'
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
