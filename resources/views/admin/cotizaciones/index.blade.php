@extends('admin.layouts.app')

@push('styles')
    <!-- Sweet Alert css-->
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet"
        type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <link href="https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Gestión de Cotizaciones</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Gestión Operativa</a></li>
                        <li class="breadcrumb-item active">Cotizaciones</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>

    {{-- Estilos en public/assets/css/custom.css — sección "MÓDULO COTIZACIONES" --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-transactional">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Listado de Cotizaciones</h5>
                        <div class="flex-shrink-0 d-flex align-items-center gap-3">
                            @if(Auth::user()->isAdmin())
                                <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn"
                                    data-bs-target="#showModal">
                                    <i class="ri-add-line align-bottom me-1"></i> Agregar Cotización
                                </button>
                            @endif
                            <button type="button" class="btn btn-danger ms-2" data-bs-toggle="modal" data-bs-target="#pdfExportModal">
                                <i class="ri-file-pdf-line align-bottom me-1"></i> Exportar PDF
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
                                    placeholder="Buscar cotización..." autocomplete="off">
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
                                    <div class="col-12 col-md-4">
                                        <label class="navy-filter-label" for="filter-estado">
                                            <i class="ri-shield-check-line"></i> Estado
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-estado">
                                            <option value="">Todos los estados</option>
                                            <option value="Pendiente">Pendiente</option>
                                            <option value="Aprobada">Aprobada</option>
                                            <option value="Vencida">Vencida</option>
                                            <option value="Convertida">Convertida</option>
                                            <option value="Cancelada">Cancelada</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="navy-filter-label" for="filter-fecha">
                                            <i class="ri-calendar-event-line"></i> Fecha
                                        </label>
                                        <input type="date" class="form-control navy-filter-select" id="filter-fecha">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="navy-filter-label" for="filter-orden">
                                            <i class="ri-sort-desc"></i> Ordenar Por
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-orden">
                                            <option value="recientes">Recientes</option>
                                            <option value="total_desc">Mayor total</option>
                                            <option value="total_asc">Menor total</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-2">
                                    <button type="button" class="btn btn-link" id="btn-clear-filters">Limpiar filtros</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <table id="cotizaciones-table" class="table table-bordered table-striped table-sm align-middle dt-transactional table-operativa">
                        <thead>
                            <tr>
                                <th>Nro.</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('admin.cotizaciones.modals')

    {{-- Modal: Exportar PDF con filtros --}}
    <div class="modal fade atlantico-modal" id="pdfExportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-file-pdf-line me-2"></i>Exportar PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Filtra qué cotizaciones incluir en el reporte.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="pdf-filter-estado">Estado</label>
                        <select class="form-select" id="pdf-filter-estado">
                            <option value="">Todos los estados</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="Aprobada">Aprobada</option>
                            <option value="Vencida">Vencida</option>
                            <option value="Convertida">Convertida</option>
                            <option value="Cancelada">Cancelada</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-0">
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="pdf-fecha-desde">Fecha Desde</label>
                            <input type="date" class="form-control" id="pdf-fecha-desde">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="pdf-fecha-hasta">Fecha Hasta</label>
                            <input type="date" class="form-control" id="pdf-fecha-hasta">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="btn-generar-pdf">
                        <i class="ri-file-pdf-fill me-1"></i>Generar PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- DataTables desde CDN, después de jQuery -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ URL::asset('/assets/js/municipios-venezuela.js') }}"></script>
    @include('admin.cotizaciones.scripts.main')
    <script>
        // PDF Export Modal — Cotizaciones
        $('#btn-generar-pdf').on('click', function () {
            var baseUrl = '{{ route('cotizaciones.reporte.pdf') }}';
            var params  = [];
            var estado  = $('#pdf-filter-estado').val();
            var desde   = $('#pdf-fecha-desde').val();
            var hasta   = $('#pdf-fecha-hasta').val();
            if (estado) params.push('estado='       + encodeURIComponent(estado));
            if (desde)  params.push('fecha_desde='  + encodeURIComponent(desde));
            if (hasta)  params.push('fecha_hasta='  + encodeURIComponent(hasta));
            window.open(baseUrl + (params.length ? '?' + params.join('&') : ''), '_blank');
            bootstrap.Modal.getInstance(document.getElementById('pdfExportModal'))?.hide();
        });
        $('#pdfExportModal').on('show.bs.modal', function () {
            $('#pdf-filter-estado').val('');
            $('#pdf-fecha-desde').val('');
            $('#pdf-fecha-hasta').val('');
        });
    </script>
@endpush