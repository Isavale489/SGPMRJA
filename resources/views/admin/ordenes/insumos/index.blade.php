@extends('admin.layouts.app')

@push('styles')
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Control de Insumos - Orden #{{ $orden->id }}</h5>
                        <div class="flex-shrink-0">
                            <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn" data-bs-target="#showModal">
                                <i class="ri-add-line align-bottom me-1"></i> Agregar Insumo
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <h6 class="mb-2">Detalles de la Orden</h6>
                                <p class="mb-1"><strong>Producto:</strong> {{ $orden->producto->nombre }}</p>
                                <p class="mb-1"><strong>Cantidad Solicitada:</strong> {{ $orden->cantidad_solicitada }}</p>
                                <p class="mb-1"><strong>Fecha Inicio:</strong> {{ $orden->fecha_inicio->format('d/m/Y') }}</p>
                                <p class="mb-1"><strong>Estado:</strong> 
                                    <span class="badge bg-{{ $orden->estado == 'Pendiente' ? 'warning' : 
                                        ($orden->estado == 'En Proceso' ? 'info' : 
                                        ($orden->estado == 'Finalizado' ? 'success' : 'danger')) }}">
                                        {{ $orden->estado }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <h6 class="mb-2">Progreso de Producción</h6>
                                <div class="progress mb-3" style="height: 20px;">
                                    @php
                                        $porcentaje = ($orden->cantidad_producida / $orden->cantidad_solicitada) * 100;
                                    @endphp
                                    <div class="progress-bar bg-success" role="progressbar" 
                                        style="width: {{ $porcentaje }}%" 
                                        aria-valuenow="{{ $porcentaje }}" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100">
                                        {{ number_format($porcentaje, 2) }}%
                                    </div>
                                </div>
                                <p class="mb-1"><strong>Producido:</strong> {{ $orden->cantidad_producida }} de {{ $orden->cantidad_solicitada }}</p>
                            </div>
                        </div>
                    </div>

                    <table id="insumos-table" class="table table-bordered dt-responsive nowrap table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Insumo</th>
                                <th>Tipo</th>
                                <th>Unidad</th>
                                <th>Cant. Estimada</th>
                                <th>Cant. Utilizada</th>
                                <th>Progreso</th>
                                <th>Stock Disponible</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.ordenes.insumos.modals.create')
    @include('admin.ordenes.insumos.modals.update')
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
    <script src="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    @include('admin.ordenes.insumos.scripts.main')
@endpush
