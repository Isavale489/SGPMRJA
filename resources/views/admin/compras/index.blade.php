@extends('admin.layouts.app')

@section('title', 'Compras')

@push('styles')
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
                            <h5 class="card-title mb-0 flex-grow-1">
                                {{ $verAnuladas ? 'Compras Anuladas' : 'Registro de Compras' }}
                            </h5>
                            <div class="flex-shrink-0 d-flex align-items-center gap-3">
                                {{-- Píldora: alternar entre activas y anuladas --}}
                                @if ($verAnuladas)
                                    <a href="{{ route('compras.index') }}" class="btn-historial btn-historial-volver">
                                        <i class="ri-arrow-left-line"></i> Volver a activas
                                    </a>
                                @else
                                    <a href="{{ route('compras.index', ['anuladas' => true]) }}" class="btn-historial btn-historial-ver">
                                        <i class="ri-archive-line"></i> Anuladas
                                    </a>
                                @endif

                                @unless ($verAnuladas)
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-success"
                                            data-bs-toggle="modal" data-bs-target="#createCompraModal">
                                            <i class="ri-add-line align-bottom me-1"></i> Nueva Compra
                                        </button>
                                        <button type="button" class="btn btn-danger"
                                            data-bs-toggle="modal" data-bs-target="#pdfExportModal">
                                            <i class="ri-file-pdf-line align-bottom me-1"></i> Exportar PDF
                                        </button>
                                    </div>
                                @else
                                    <button type="button" class="btn btn-danger"
                                        data-bs-toggle="modal" data-bs-target="#pdfExportModal">
                                        <i class="ri-file-pdf-line align-bottom me-1"></i> Exportar PDF
                                    </button>
                                @endunless
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
                                        @unless ($verAnuladas)
                                        <div class="col-12 col-md-3">
                                            <label class="navy-filter-label" for="filter-estado">
                                                <i class="ri-shield-check-line"></i> Estado
                                            </label>
                                            <select class="form-select navy-filter-select" id="filter-estado">
                                                <option value="" selected>Activas (todas)</option>
                                                <option value="recibida">Recibida</option>
                                                <option value="borrador">Borrador</option>
                                            </select>
                                        </div>
                                        @endunless
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
                                    <th>Nro.</th>
                                    <th>Proveedor</th>
                                    <th>N° Factura</th>
                                    <th>Fecha</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Estado</th>
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
    @include('admin.compras.modals.view')

    {{-- Modal: Exportar PDF con filtros --}}
    <div class="modal fade atlantico-modal atlantico-modal--op" id="pdfExportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-file-pdf-line me-2"></i>Exportar PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Filtra qué compras incluir en el reporte. Deja los campos vacíos para incluir todas.</p>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="pdf-proveedor">Proveedor</label>
                            <select class="form-select" id="pdf-proveedor">
                                <option value="">Todos los proveedores</option>
                                @foreach ($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}">{{ $proveedor->nombre_completo }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="pdf-estado">Estado</label>
                            <select class="form-select" id="pdf-estado">
                                <option value="">Todos</option>
                                <option value="recibida">Recibida</option>
                                <option value="borrador">Borrador</option>
                                <option value="anulada">Anulada</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="pdf-fecha-desde">Fecha Desde</label>
                            <input type="date" class="form-control" id="pdf-fecha-desde">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="pdf-fecha-hasta">Fecha Hasta</label>
                            <input type="date" class="form-control" id="pdf-fecha-hasta">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="pdf-orden">Ordenar por</label>
                            <select class="form-select" id="pdf-orden">
                                <option value="recientes">Fecha reciente</option>
                                <option value="monto_desc">Mayor monto</option>
                                <option value="monto_asc">Menor monto</option>
                            </select>
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

    {{-- Datos de insumos para el JS del modal (el proveedor se elige por búsqueda) --}}
    <script>
        window.INSUMOS_DATA = @json($insumos);
        window.VER_ANULADAS = @json($verAnuladas);
        window.IVA_TASA     = @json(\App\Models\Impuesto::tasaIva());
    </script>
@endsection

@push('scripts')
    <script src="{{ URL::asset('/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/js/municipios-venezuela.js') }}"></script>
    <script src="{{ asset('assets/js/telefonos-repeater.js') }}"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    @include('admin.compras.scripts.main')
    @include('admin.compras.scripts.create')
    <script>
        // Exportar PDF con filtros — Compras
        $('#btn-generar-pdf').on('click', function () {
            var baseUrl = '{{ route('compras.reporte.pdf') }}';
            var params  = [];
            var prov    = $('#pdf-proveedor').val();
            var estado  = $('#pdf-estado').val();
            var desde   = $('#pdf-fecha-desde').val();
            var hasta   = $('#pdf-fecha-hasta').val();
            var orden   = $('#pdf-orden').val();
            if (prov)   params.push('proveedor_id=' + encodeURIComponent(prov));
            if (estado) params.push('estado='       + encodeURIComponent(estado));
            if (desde)  params.push('fecha_desde='   + encodeURIComponent(desde));
            if (hasta)  params.push('fecha_hasta='   + encodeURIComponent(hasta));
            if (orden && orden !== 'recientes') params.push('orden=' + encodeURIComponent(orden));
            window.open(baseUrl + (params.length ? '?' + params.join('&') : ''), '_blank');
            bootstrap.Modal.getInstance(document.getElementById('pdfExportModal'))?.hide();
        });
        $('#pdfExportModal').on('show.bs.modal', function () {
            $('#pdf-proveedor, #pdf-estado, #pdf-fecha-desde, #pdf-fecha-hasta').val('');
            $('#pdf-orden').val('recientes');
        });
    </script>
@endpush
