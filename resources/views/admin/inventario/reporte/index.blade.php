@extends('admin.layouts.app')

@section('title', 'Reporte de Existencia')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Reporte de Existencia</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Existencia</a></li>
                        <li class="breadcrumb-item active">Reporte</li>
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
                        <h5 class="card-title mb-0 flex-grow-1">Estado Actual de la Existencia</h5>
                        <div class="flex-shrink-0">
                            <a href="{{ route('existencia.movimientos.index') }}" class="btn btn-secondary">
                                <i class="ri-arrow-go-back-line align-bottom me-1"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="existencia-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Nro. Insumo</th>
                                    <th>Insumo</th>
                                    <th>Tipo</th>
                                    <th>Unidad</th>
                                    <th>Stock Actual</th>
                                    <th>Stock Mínimo</th>
                                    <th>Estado</th>
                                    <th>Costo Unitario</th>
                                    <th>Valor Total</th>
                                    <th>Proveedor</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($insumos as $insumo)
                                <tr>
                                    <td>{{ $insumo->id }}</td>
                                    <td>{{ $insumo->nombre }}</td>
                                    <td>{{ $insumo->tipo }}</td>
                                    <td>{{ $insumo->unidad_medida }}</td>
                                    <td>{{ number_format($insumo->stock_actual, 2) }}</td>
                                    <td>{{ number_format($insumo->stock_minimo, 2) }}</td>
                                    <td>
                                        @if($insumo->stock_actual <= $insumo->stock_minimo)
                                            <span class="badge bg-danger">Stock Bajo</span>
                                        @else
                                            <span class="badge bg-success">Stock Óptimo</span>
                                        @endif
                                    </td>
                                    <td>$/ {{ number_format($insumo->costo_unitario, 2) }}</td>
                                    <td>$/ {{ number_format($insumo->stock_actual * $insumo->costo_unitario, 2) }}</td>
                                    <td>{{ $insumo->proveedor ? $insumo->proveedor->razon_social : 'No asignado' }}</td>
                                    <td>
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-fill align-middle"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a href="{{ route('existencia.movimientos.historial', $insumo->id) }}" class="dropdown-item">
                                                        <i class="ri-history-line align-bottom me-2 text-muted"></i> Ver Historial
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" class="dropdown-item add-movement" data-id="{{ $insumo->id }}" data-nombre="{{ $insumo->nombre }}">
                                                        <i class="ri-add-circle-line align-bottom me-2 text-muted"></i> Registrar Movimiento
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="8" class="text-end">Valor Total de la Existencia:</th>
                                    <th>$/ {{ number_format($insumos->sum(function($insumo) { return $insumo->stock_actual * $insumo->costo_unitario; }), 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Inicializar DataTable
        var table = $('#existencia-table').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
            },
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf'
            ]
        });

        // Manejar clic en botón de imprimir
        $('#print-btn').on('click', function() {
            window.print();
        });

        // Delegación de eventos para Registrar Movimiento
        $(document).on('click', '.add-movement', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            window.location.href = "{{ route('existencia.movimientos.index') }}?insumo_id=" + id;
        });
    });
</script>
@endpush
