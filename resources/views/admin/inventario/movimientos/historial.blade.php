@extends('admin.layouts.app')

@section('title', 'Historial de Movimientos')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Historial de Movimientos</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('existencia.reporte') }}">Existencia</a></li>
                        <li class="breadcrumb-item active">Historial</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Historial de Movimientos: {{ $insumo->nombre }}</h5>
                        <div class="flex-shrink-0">
                            <a href="{{ route('existencia.reporte') }}" class="btn btn-secondary ms-2">
                                <i class="ri-arrow-go-back-line align-bottom me-1"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Información del Insumo</h5>
                                    <div class="table-responsive">
                                        <table class="table table-borderless mb-0">
                                            <tbody>
                                                <tr>
                                                    <th class="ps-0" scope="row">Nombre:</th>
                                                    <td class="text-muted">{{ $insumo->nombre }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0" scope="row">Tipo:</th>
                                                    <td class="text-muted">{{ $insumo->tipo }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0" scope="row">Unidad de Medida:</th>
                                                    <td class="text-muted">{{ $insumo->unidad_medida }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0" scope="row">Stock Actual:</th>
                                                    <td class="text-muted">{{ number_format($insumo->stock_actual, 2) }} {{ $insumo->unidad_medida }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0" scope="row">Stock Mínimo:</th>
                                                    <td class="text-muted">{{ number_format($insumo->stock_minimo, 2) }} {{ $insumo->unidad_medida }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0" scope="row">Costo Unitario:</th>
                                                    <td class="text-muted">$/ {{ number_format($insumo->costo_unitario, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0" scope="row">Proveedor:</th>
                                                    <td class="text-muted">{{ $insumo->proveedor ? $insumo->proveedor->razon_social : 'No asignado' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Resumen de Movimientos</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card bg-success-subtle border-0">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm flex-shrink-0">
                                                            <span class="avatar-title bg-success text-white rounded-2 fs-2">
                                                                <i class="ri-arrow-down-line"></i>
                                                            </span>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <p class="text-uppercase fw-medium text-muted mb-1">Total Entradas</p>
                                                            <h4 class="mb-0">{{ number_format($movimientos->where('tipo_movimiento', 'Entrada')->sum('cantidad'), 2) }} {{ $insumo->unidad_medida }}</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card bg-danger-subtle border-0">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm flex-shrink-0">
                                                            <span class="avatar-title bg-danger text-white rounded-2 fs-2">
                                                                <i class="ri-arrow-up-line"></i>
                                                            </span>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <p class="text-uppercase fw-medium text-muted mb-1">Total Salidas</p>
                                                            <h4 class="mb-0">{{ number_format($movimientos->where('tipo_movimiento', 'Salida')->sum('cantidad'), 2) }} {{ $insumo->unidad_medida }}</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="historial-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Nro. Movimiento</th>
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
                                    <td>{{ $movimiento->id }}</td>
                                    <td>
                                        @if($movimiento->tipo_movimiento == 'Entrada')
                                            <span class="badge bg-success">Entrada</span>
                                        @else
                                            <span class="badge bg-danger">Salida</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($movimiento->cantidad, 2) }} {{ $insumo->unidad_medida }}</td>
                                    <td>{{ number_format($movimiento->stock_anterior, 2) }} {{ $insumo->unidad_medida }}</td>
                                    <td>{{ number_format($movimiento->stock_nuevo, 2) }} {{ $insumo->unidad_medida }}</td>
                                    <td>{{ $movimiento->motivo }}</td>
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

@section('scripts')
<script>
    $(document).ready(function() {
        // Inicializar DataTable
        var table = $('#historial-table').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
            },
            responsive: true,
            order: [[0, 'desc']],
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf'
            ]
        });
    });
</script>
@endsection
