@extends('admin.layouts.app')

@push('styles')
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" rel="stylesheet"
        type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    {{-- Estilos en public/assets/css/custom.css — sección "MÓDULO ÓRDENES DE PRODUCCIÓN" --}}
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Órdenes de Producción</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Gestión Operativa</a></li>
                        <li class="breadcrumb-item active">Órdenes</li>
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
                        <h5 class="card-title mb-0 flex-grow-1">Órdenes de Producción</h5>
                        <div class="flex-shrink-0 d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-soft-info" id="mis-ordenes-btn"
                                data-bs-toggle="modal" data-bs-target="#misOrdenesModal">
                                <i class="ri-user-follow-line align-bottom me-1"></i> Mis Órdenes
                            </button>
                            <button type="button" class="btn btn-success add-btn" id="create-btn">
                                <i class="ri-add-line align-bottom me-1"></i> Nueva Orden
                            </button>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#pdfExportModal">
                                <i class="ri-file-pdf-fill align-bottom me-1"></i> Exportar PDF
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
                                    placeholder="Buscar por pedido o cliente..." autocomplete="off">
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
                                            <option value="">Todos los estados</option>
                                            <option value="Pendiente">Pendiente</option>
                                            <option value="En Proceso">En Proceso</option>
                                            <option value="Finalizado">Finalizado</option>
                                            <option value="Cancelado">Cancelado</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="navy-filter-label" for="filter-fecha-desde">
                                            <i class="ri-calendar-event-line"></i> Fecha Estimada Desde
                                        </label>
                                        <input type="date" class="form-control navy-filter-select" id="filter-fecha-desde">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="navy-filter-label" for="filter-fecha-hasta">
                                            <i class="ri-calendar-check-line"></i> Fecha Estimada Hasta
                                        </label>
                                        <input type="date" class="form-control navy-filter-select" id="filter-fecha-hasta">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="navy-filter-label" for="filter-orden">
                                            <i class="ri-sort-desc"></i> Ordenar Por
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-orden">
                                            <option value="recientes">Recientes</option>
                                            <option value="progreso_desc">Mayor progreso</option>
                                            <option value="progreso_asc">Menor progreso</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-2">
                                    <button type="button" class="btn btn-link" id="btn-clear-filters">Limpiar filtros</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Una fila por pedido; el detalle de sus órdenes vive en el
                         modal "Ver órdenes" (pedido_ordenes.blade.php) --}}
                    <table id="ordenes-table" class="table table-bordered table-striped align-middle dt-transactional table-operativa">
                        <thead>
                            <tr>
                                <th>Pedido</th>
                                <th>Cliente</th>
                                <th class="text-center">Órdenes</th>
                                <th class="text-center">Progreso Global</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.ordenes.modals.mis_ordenes')
    @include('admin.ordenes.modals.pedido_ordenes')
    @include('admin.ordenes.modals.create')
    @include('admin.ordenes.modals.insumo_add')
    @include('admin.ordenes.modals.view')
    @include('admin.ordenes.modals.avance')
    @include('admin.ordenes.modals.subordenes')

    {{-- Modal: Exportar PDF --}}
    <div class="modal fade atlantico-modal" id="pdfExportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-file-pdf-line me-2"></i>Exportar PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Filtra qué órdenes incluir en el reporte.</p>
                    <div class="mb-0">
                        <label class="form-label fw-semibold" for="pdf-filter-estado">Estado</label>
                        <select class="form-select" id="pdf-filter-estado">
                            <option value="">Todos los estados</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="En Proceso">En Proceso</option>
                            <option value="Finalizado">Finalizado</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="row g-2 mt-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="pdf-fecha-desde">Fecha est. desde</label>
                            <input type="date" class="form-control" id="pdf-fecha-desde">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="pdf-fecha-hasta">Fecha est. hasta</label>
                            <input type="date" class="form-control" id="pdf-fecha-hasta">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-semibold" for="pdf-filter-orden">Ordenar por</label>
                        <select class="form-select" id="pdf-filter-orden">
                            <option value="recientes">Más recientes</option>
                            <option value="progreso_desc">Mayor progreso</option>
                            <option value="progreso_asc">Menor progreso</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="btn-generar-pdf" data-allow-future="1">
                        <i class="ri-file-pdf-fill me-1"></i>Generar PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Empleados de Producción asignables a las sub-órdenes.
        window.OP_EMPLEADOS = @json($empleados);
    </script>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.colVis.min.js"></script>
    <script src="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('assets/js/proyeccion-insumos.js') }}"></script>

    @include('admin.ordenes.scripts.main')
    @include('admin.ordenes.scripts.subordenes')
    <script>
        // Exportar PDF — Órdenes de Producción
        $('#btn-generar-pdf').on('click', function () {
            var baseUrl = '{{ route('ordenes.reporte.pdf') }}';
            var params = [];
            var estado = $('#pdf-filter-estado').val();
            var fdesde = $('#pdf-fecha-desde').val();
            var fhasta = $('#pdf-fecha-hasta').val();
            var orden  = $('#pdf-filter-orden').val();
            if (estado) params.push('estado=' + encodeURIComponent(estado));
            if (fdesde) params.push('fecha_desde=' + encodeURIComponent(fdesde));
            if (fhasta) params.push('fecha_hasta=' + encodeURIComponent(fhasta));
            if (orden && orden !== 'recientes') params.push('orden=' + encodeURIComponent(orden));
            window.open(baseUrl + (params.length ? '?' + params.join('&') : ''), '_blank');
            bootstrap.Modal.getInstance(document.getElementById('pdfExportModal'))?.hide();
        });
        $('#pdfExportModal').on('show.bs.modal', function () {
            $('#pdf-filter-estado, #pdf-fecha-desde, #pdf-fecha-hasta').val('');
            $('#pdf-filter-orden').val('recientes');
        });
    </script>
@endpush