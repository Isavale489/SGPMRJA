@extends('admin.layouts.app')

@section('title', 'Control de Inventario')
@push('styles')
    <!-- Sweet Alert css-->
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet"
        type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
    {{-- Estilos en public/assets/css/custom.css — sección "MÓDULO INVENTARIO — MOVIMIENTOS" --}}
@endpush
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Control de Inventario</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Gestión Operativa</a></li>
                            <li class="breadcrumb-item active">Movimientos</li>
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
                            <h5 class="card-title mb-0 flex-grow-1">Movimientos de Inventario</h5>
                            <div class="flex-shrink-0 d-flex align-items-center gap-3">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                        data-bs-target="#createModal">
                                        <i class="ri-add-line align-bottom me-1"></i> Registrar Movimiento
                                    </button>
                                    <a href="{{ route('inventario.alertas') }}" class="btn btn-warning">
                                        <i class="ri-alert-line align-bottom me-1"></i> Alertas de Stock
                                    </a>
                                    <a href="{{ route('inventario.reporte') }}" class="btn btn-danger">
                                        <i class="ri-file-list-3-line align-bottom me-1"></i> Reporte de Inventario
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="advanced-filters-wrapper emerald-theme" id="advanced-filters">
                            <div class="navy-filter-header is-collapsed">
                                <div class="navy-header-search">
                                    <i class="ri-search-line"></i>
                                    <input type="text" class="navy-search-input" id="custom-search-input"
                                        placeholder="Buscar movimiento..." autocomplete="off">
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
                                            <label class="navy-filter-label" for="filter-tipo">
                                                <i class="ri-exchange-line"></i> Tipo
                                            </label>
                                            <select class="form-select navy-filter-select" id="filter-tipo">
                                                <option value="">Todos</option>
                                                <option value="Entrada">Entrada</option>
                                                <option value="Salida">Salida</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="navy-filter-label" for="filter-insumo">
                                                <i class="ri-archive-line"></i> Insumo
                                            </label>
                                            <select class="form-select navy-filter-select" id="filter-insumo">
                                                <option value="">Todos</option>
                                                @foreach ($insumos as $insumo)
                                                    <option value="{{ $insumo->id }}">{{ $insumo->nombre }}</option>
                                                @endforeach
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
                        <table id="movimientos-table" class="table table-bordered table-striped align-middle dt-transactional table-operativa">
                            <thead>
                                <tr>
                                    <th>Insumo</th>
                                    <th>Tipo Movimiento</th>
                                    <th>Cantidad</th>
                                    <th>Stock Nuevo</th>
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

        {{-- ============================================================
             EXISTENCIAS DE INSUMOS — consulta de stock sin salir de aquí
             Mín. / Actual / Máx. de cada insumo inventariable.
             ============================================================ --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-reportes">
                    <div class="card-header">
                        <h5 class="card-title mb-1">
                            <i class="ri-stack-line align-bottom me-1"></i>Existencias de Insumos
                        </h5>
                        <small class="text-muted">Stock mínimo, actual y máximo de cada insumo. Se actualiza al registrar un movimiento.</small>
                    </div>
                    <div class="card-body">
                        {{-- Búsqueda + filtros unificados — Patrón Maestro S-07 --}}
                        <div class="advanced-filters-wrapper navy-theme" id="exist-advanced-filters">
                            <div class="navy-filter-header is-collapsed">
                                <div class="navy-header-search">
                                    <i class="ri-search-line"></i>
                                    <input type="text" class="navy-search-input" id="exist-search-input"
                                        placeholder="Buscar insumo..." autocomplete="off">
                                </div>
                                <div class="navy-header-divider"></div>
                                <button class="navy-filter-btn collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#exist-filters-collapse"
                                    aria-expanded="false" aria-controls="exist-filters-collapse">
                                    <i class="ri-filter-3-line"></i>
                                    <span>Filtros</span>
                                    <span class="navy-filter-badge d-none" id="exist-active-filter-count"></span>
                                    <i class="ri-arrow-down-s-line navy-filter-chevron"></i>
                                </button>
                            </div>
                            <div class="collapse" id="exist-filters-collapse">
                                <div class="navy-filter-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-4">
                                            <label class="navy-filter-label" for="exist-filter-tipo">
                                                <i class="ri-price-tag-3-line"></i> Tipo de Insumo
                                            </label>
                                            <select class="form-select navy-filter-select" id="exist-filter-tipo">
                                                <option value="">Todos</option>
                                                <option value="Tela">Tela</option>
                                                <option value="Hilo">Hilo</option>
                                                <option value="Boton">Botón</option>
                                                <option value="Cierre">Cierre</option>
                                                <option value="Etiqueta">Etiqueta</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="navy-filter-label" for="exist-filter-alerta">
                                                <i class="ri-alarm-warning-line"></i> Disponibilidad
                                            </label>
                                            <select class="form-select navy-filter-select" id="exist-filter-alerta">
                                                <option value="">Todas</option>
                                                <option value="alerta">Solo en alerta (stock bajo)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-2">
                                        <button type="button" class="btn btn-link" id="exist-btn-clear-filters">Limpiar filtros</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- FIN FILTROS --}}

                        <div class="table-responsive">
                            <table id="existencias-table" class="table table-bordered table-striped table-sm align-middle dt-reportes table-operativa">
                                <thead>
                                    <tr>
                                        <th>Insumo</th>
                                        <th>Tipo</th>
                                        <th>Existencia Mín.</th>
                                        <th>Existencia Actual</th>
                                        <th>Existencia Máx.</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.inventario.movimientos.modals.create')
    @include('admin.inventario.movimientos.modals.view')
    @include('admin.inventario.movimientos.modals.create_insumo')

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