@extends('admin.layouts.app')

@section('title', 'Control de Existencia')
@push('styles')
    <!-- Sweet Alert css-->
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
@endpush
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Control de Existencia</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Existencia</a></li>
                        <li class="breadcrumb-item active">Movimientos</li>
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
                        <h5 class="card-title mb-0 flex-grow-1">Movimientos de Insumos</h5>
                        <div class="flex-shrink-0">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal">
                                <i class="ri-add-line align-bottom me-1"></i> Registrar Movimiento
                            </button>
                            <a href="{{ route('existencia.alertas') }}" class="btn btn-warning ms-2">
                                <i class="ri-alert-line align-bottom me-1"></i> Alertas de Stock
                            </a>
                            <a href="{{ route('existencia.reporte') }}" class="btn btn-danger ms-2">
                                <i class="ri-file-list-3-line align-bottom me-1"></i> Reporte de Existencia
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="movimientos-table" class="table table-bordered table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Insumo</th>
                                    <th>Tipo</th>
                                    <th>Tipo Movimiento</th>
                                    <th>Cantidad</th>
                                    <th>Stock Anterior</th>
                                    <th>Stock Nuevo</th>
                                    <th>Usuario</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables llenará esto -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.inventario.movimientos.modals.create')
@include('admin.inventario.movimientos.modals.view')

@endsection

@push('scripts') 
    <script src="{{ URL::asset('/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
@include('admin.inventario.movimientos.scripts.main')
@endpush
