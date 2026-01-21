@extends('admin.layouts.app')

@section('title', 'Historial de Movimientos')

@push('styles')
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <style>
        .info-icon-circle {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Historial de Movimientos</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('inventario.reporte') }}">Inventario</a></li>
                            <li class="breadcrumb-item active">Historial</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header del Insumo con gradiente -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm"
                    style="background: linear-gradient(135deg, #1e3c72 0%, #2ecc71 50%, #00d9a5 100%);">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-white bg-opacity-25 me-3 d-flex align-items-center justify-content-center"
                                    style="width: 50px; height: 50px;">
                                    <i class="ri-archive-line text-white fs-3"></i>
                                </div>
                                <div>
                                    <h4 class="text-white mb-0">{{ $insumo->nombre }}</h4>
                                    <span class="text-white-50">{{ $insumo->tipo }} | {{ $insumo->unidad_medida }}</span>
                                </div>
                            </div>
                            <a href="{{ route('inventario.reporte') }}" class="btn btn-light">
                                <i class="ri-arrow-go-back-line me-1"></i>Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información y Resumen en Cards -->
        <div class="row mb-3">
            <!-- Card de Información del Insumo -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header border-0" style="background: rgba(30, 60, 114, 0.1);">
                        <h6 class="mb-0" style="color: #1e3c72;">
                            <i class="ri-information-line me-2"></i>Información del Insumo
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <div class="info-icon-circle me-2" style="background: rgba(30, 60, 114, 0.1);">
                                        <i class="ri-price-tag-3-line" style="color: #1e3c72;"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Proveedor</small>
                                        <span
                                            class="fw-semibold">{{ $insumo->proveedor ? $insumo->proveedor->razon_social : 'No asignado' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <div class="info-icon-circle me-2" style="background: rgba(46, 204, 113, 0.1);">
                                        <i class="ri-money-dollar-circle-line" style="color: #2ecc71;"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Costo Unitario</small>
                                        <span class="fw-semibold">${{ number_format($insumo->costo_unitario, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <div class="info-icon-circle me-2" style="background: rgba(220, 53, 69, 0.1);">
                                        <i class="ri-alert-line" style="color: #dc3545;"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Stock Mínimo</small>
                                        <span class="fw-semibold">{{ number_format($insumo->stock_minimo, 2) }}
                                            {{ $insumo->unidad_medida }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-center p-3 rounded"
                                    style="background: linear-gradient(135deg, rgba(30, 60, 114, 0.1) 0%, rgba(0, 217, 165, 0.1) 100%);">
                                    <div class="info-icon-circle me-3" style="background: rgba(0, 217, 165, 0.2);">
                                        <i class="ri-archive-fill fs-4" style="color: #00d9a5;"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">STOCK ACTUAL</small>
                                        <h3 class="mb-0 fw-bold" style="color: #1e3c72;">
                                            {{ number_format($insumo->stock_actual, 2) }} <small
                                                class="fs-6">{{ $insumo->unidad_medida }}</small></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cards de Resumen -->
            <div class="col-lg-5">
                <div class="row h-100">
                    <div class="col-12 mb-3">
                        <div class="card border-0 shadow-sm h-100"
                            style="background: linear-gradient(135deg, rgba(25, 135, 84, 0.1) 0%, rgba(25, 135, 84, 0.05) 100%);">
                            <div class="card-body d-flex align-items-center">
                                <div class="rounded-circle me-3 d-flex align-items-center justify-content-center"
                                    style="width: 60px; height: 60px; background: #198754;">
                                    <i class="ri-arrow-down-line text-white fs-2"></i>
                                </div>
                                <div>
                                    <p class="text-uppercase fw-semibold text-muted mb-1 fs-6">Total Entradas</p>
                                    <h3 class="mb-0 text-success fw-bold">
                                        {{ number_format($movimientos->where('tipo_movimiento', 'Entrada')->sum('cantidad'), 2) }}
                                    </h3>
                                    <small class="text-muted">{{ $insumo->unidad_medida }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm h-100"
                            style="background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%);">
                            <div class="card-body d-flex align-items-center">
                                <div class="rounded-circle me-3 d-flex align-items-center justify-content-center"
                                    style="width: 60px; height: 60px; background: #dc3545;">
                                    <i class="ri-arrow-up-line text-white fs-2"></i>
                                </div>
                                <div>
                                    <p class="text-uppercase fw-semibold text-muted mb-1 fs-6">Total Salidas</p>
                                    <h3 class="mb-0 text-danger fw-bold">
                                        {{ number_format($movimientos->where('tipo_movimiento', 'Salida')->sum('cantidad'), 2) }}
                                    </h3>
                                    <small class="text-muted">{{ $insumo->unidad_medida }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Historial -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0" style="background: rgba(0, 217, 165, 0.1);">
                        <h6 class="mb-0" style="color: #00d9a5;">
                            <i class="ri-history-line me-2"></i>Historial de Movimientos ({{ $movimientos->count() }}
                            registros)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="historial-table" class="table table-hover align-middle" style="width:100%">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nro.</th>
                                        <th>Tipo</th>
                                        <th>Cantidad</th>
                                        <th>Stock Anterior</th>
                                        <th>Stock Nuevo</th>
                                        <th>Motivo</th>
                                        <th>Usuario</th>
                                        <th>Fecha y Hora</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($movimientos as $movimiento)
                                        <tr>
                                            <td><span class="badge bg-secondary">{{ $movimiento->id }}</span></td>
                                            <td>
                                                @if($movimiento->tipo_movimiento == 'Entrada')
                                                    <span class="badge badge-status status-aprobada"><i
                                                            class="ri-arrow-down-line me-1"></i>Entrada</span>
                                                @else
                                                    <span class="badge badge-status status-rechazada"><i
                                                            class="ri-arrow-up-line me-1"></i>Salida</span>
                                                @endif
                                            </td>
                                            <td class="fw-semibold">{{ number_format($movimiento->cantidad, 2) }}</td>
                                            <td class="text-muted">{{ number_format($movimiento->stock_anterior, 2) }}</td>
                                            <td class="fw-semibold">{{ number_format($movimiento->stock_nuevo, 2) }}</td>
                                            <td><span class="text-truncate d-inline-block" style="max-width: 200px;"
                                                    title="{{ $movimiento->motivo }}">{{ $movimiento->motivo }}</span></td>
                                            <td>{{ $movimiento->creadoPor->name }}</td>
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
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#historial-table').DataTable({
                language: lenguajeData,
                responsive: true,
                order: [[0, 'desc']]
            });
        });
    </script>
@endpush