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
    <style>
        .card-body {
            overflow-x: auto;
        }

        #cotizaciones-table {
            width: 100%;
        }

        #cotizaciones-table th:last-child,
        #cotizaciones-table td:last-child {
            width: 260px;
            min-width: 260px;
            text-align: center;
        }

        /* Prevent wrapping in phone/document columns */
        #cotizaciones-table td {
            white-space: nowrap;
        }

        /* Backdrop más oscuro y difuminado para modal de cliente */
        #modalAddCliente ~ .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.7) !important;
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
        }

        /* Mejorar visibilidad del modal de cliente */
        #modalAddCliente .modal-content {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
        }

        /* Estilos para modal de selección de productos */
        .producto-selector-btn {
            cursor: pointer;
            background: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding: 0.375rem 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 38px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .producto-selector-btn:hover {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        #productosModalCotizacionTable tbody tr {
            cursor: pointer;
            transition: background-color 0.15s;
        }

        #productosModalCotizacionTable tbody tr:hover {
            background-color: #e3f2fd !important;
        }

        #productosModalCotizacionTable tbody tr.selected {
            background-color: #bbdefb !important;
        }

        .producto-img-thumb {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Listado de Cotizaciones</h5>
                        <div class="flex-shrink-0 d-flex align-items-center gap-3">
                            <!-- Buscador Personalizado -->
                            <div class="search-box">
                                <input type="text" class="form-control form-control-sm" id="custom-search-input" placeholder="Buscar cotización...">
                                <i class="ri-search-line search-icon"></i>
                            </div>

                            @if(Auth::user()->isAdmin())
                                <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn"
                                    data-bs-target="#showModal">
                                    <i class="ri-add-line align-bottom me-1"></i> Agregar Cotización
                                </button>
                            @endif
                            <a href="{{ route('cotizaciones.reporte.pdf') }}" target="_blank" class="btn btn-danger ms-2">
                                <i class="ri-file-pdf-line align-bottom me-1"></i> Exportar PDF
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="cotizaciones-table" class="table table-bordered table-striped table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Nro. de cotización</th>
                                <th>Documento de identidad</th>
                                <th>Cliente</th>
                                <th>Teléfono</th>
                                <th>Fecha Cotización</th>
                                <th>Fecha de Validez</th>
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
@endsection

@push('scripts')
    <!-- DataTables desde CDN, después de jQuery -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ URL::asset('/assets/js/municipios-venezuela.js') }}"></script>
    @include('admin.cotizaciones.scripts.main')
@endpush