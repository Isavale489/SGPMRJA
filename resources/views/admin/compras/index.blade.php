@extends('admin.layouts.app')

@section('title', 'Compras')

@push('styles')
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Gestión de Compras</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Gestión Operativa</a></li>
                            <li class="breadcrumb-item active">Compras</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card card-transactional">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">Registro de Compras</h5>
                            <div class="flex-shrink-0">
                                <button type="button" class="btn btn-success"
                                    data-bs-toggle="modal" data-bs-target="#createCompraModal">
                                    <i class="ri-add-line align-bottom me-1"></i> Nueva Compra
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="advanced-filters-wrapper emerald-theme" id="advanced-filters">
                            <div class="navy-filter-header is-collapsed">
                                <div class="navy-header-search">
                                    <i class="ri-search-line"></i>
                                    <input type="text" class="navy-search-input" id="custom-search-input"
                                        placeholder="Buscar por proveedor, factura..." autocomplete="off">
                                </div>
                                <div class="navy-header-divider"></div>
                                <button class="navy-filter-btn collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#filters-collapse-body"
                                    aria-expanded="false" aria-controls="filters-collapse-body">
                                    <i class="ri-filter-3-line"></i>
                                    <span>Filtros</span>
                                    <span class="navy-filter-badge d-none" id="active-filter-count"></span>
                                    <i class="ri-arrow-down-s-line navy-filter-chevron"></i>
                                </button>
                            </div>
                            <div class="collapse" id="filters-collapse-body">
                                <div class="navy-filter-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-3">
                                            <label class="navy-filter-label" for="filter-estado">
                                                <i class="ri-shield-check-line"></i> Estado
                                            </label>
                                            <select class="form-select navy-filter-select" id="filter-estado">
                                                <option value="">Todos</option>
                                                <option value="recibida">Recibida</option>
                                                <option value="borrador">Borrador</option>
                                                <option value="anulada">Anulada</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="navy-filter-label" for="filter-tipo-pago">
                                                <i class="ri-bank-card-line"></i> Tipo de Pago
                                            </label>
                                            <select class="form-select navy-filter-select" id="filter-tipo-pago">
                                                <option value="">Todos</option>
                                                <option value="contado">Contado</option>
                                                <option value="credito">Crédito</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="navy-filter-label" for="filter-fecha-desde">
                                                <i class="ri-calendar-line"></i> Desde
                                            </label>
                                            <input type="date" class="form-control navy-filter-select" id="filter-fecha-desde">
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="navy-filter-label" for="filter-fecha-hasta">
                                                <i class="ri-calendar-2-line"></i> Hasta
                                            </label>
                                            <input type="date" class="form-control navy-filter-select" id="filter-fecha-hasta">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-2">
                                        <button type="button" class="btn btn-link" id="btn-clear-filters">Limpiar filtros</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <table id="compras-table" class="table table-bordered table-striped align-middle dt-transactional table-operativa">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Proveedor</th>
                                    <th>N° Factura</th>
                                    <th>Fecha</th>
                                    <th>Tipo Pago</th>
                                    <th class="text-end">Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.compras.modals.create')

    {{-- Datos de insumos + proveedores para el JS del modal --}}
    <script>
        window.INSUMOS_DATA = @json($insumos);
        window.PROVEEDORES_DATA = {
            @foreach ($proveedores as $p)
                "{{ $p->id }}": {
                    nombre:  @json($p->nombre_completo),
                    doc:     @json($p->documento ?? ''),
                    tel:     @json($p->telefono_unificado ?? ''),
                    email:   @json($p->email_unificado ?? ''),
                    tipo:    @json($p->tipo_proveedor ?? ''),
                    compras: {{ (int) ($p->compras_count ?? 0) }},
                    ultima:  @json($p->ultima_compra ?? '')
                },
            @endforeach
        };
    </script>
@endsection

@push('scripts')
    <script src="{{ URL::asset('/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @include('admin.compras.scripts.main')
    @include('admin.compras.scripts.create')
@endpush
