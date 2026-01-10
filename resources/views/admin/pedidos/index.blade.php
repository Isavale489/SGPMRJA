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
    <!-- Autocorrección y Autocompletado -->
    <link href="https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css" rel="stylesheet" />
    <style>
        /* Estilos para sugerencias de autocorrección */
        .autocorrect-suggestion {
            position: absolute;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 12px;
            z-index: 1060;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            max-width: 300px;
        }

        .autocorrect-suggestion .suggestion-text {
            color: #856404;
            margin-bottom: 5px;
        }

        .autocorrect-suggestion .suggestion-buttons {
            display: flex;
            gap: 5px;
        }

        .autocorrect-suggestion .btn-suggestion {
            font-size: 11px;
            padding: 2px 8px;
        }

        /* Estilos para campos con errores detectados */
        .field-with-suggestion {
            border-color: #ffc107 !important;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25) !important;
        }

        /* Estilos para autocompletado mejorado */
        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            z-index: 1060 !important;
        }

        .ui-menu-item {
            font-size: 14px;
        }

        .ui-menu-item .ui-menu-item-wrapper {
            padding: 8px 12px;
        }

        .ui-menu-item .suggestion-meta {
            color: #6c757d;
            font-size: 12px;
        }
    </style>
@endpush

@section('content')
    <style>
        .card-body {
            overflow-x: auto;
        }

        /* === RESPONSIVIDAD: Ocultar columnas en pantallas pequeñas === */
        /* Pantallas menores a 1400px: ocultar Email */
        @media (max-width: 1399px) {

            #pedidos-table th:nth-child(3),
            #pedidos-table td:nth-child(3) {
                display: none;
            }
        }

        /* Pantallas menores a 1200px: ocultar también Teléfono y Usuario Creador */
        @media (max-width: 1199px) {

            #pedidos-table th:nth-child(4),
            #pedidos-table td:nth-child(4),
            #pedidos-table th:nth-child(10),
            #pedidos-table td:nth-child(10) {
                display: none;
            }
        }

        /* Pantallas menores a 992px: ocultar también Documento y Fecha Entrega */
        @media (max-width: 991px) {

            #pedidos-table th:nth-child(5),
            #pedidos-table td:nth-child(5),
            #pedidos-table th:nth-child(7),
            #pedidos-table td:nth-child(7) {
                display: none;
            }
        }

        #pedidos-table {
            width: 100% !important;
            font-size: 13px;
        }

        #pedidos-table th,
        #pedidos-table td {
            padding: 0.35rem 0.5rem;
            vertical-align: middle;
        }

        #pedidos-table th:last-child,
        #pedidos-table td:last-child {
            width: 48px;
            min-width: 40px;
            max-width: 60px;
            text-align: center;
        }

        #pedidos-table th:first-child,
        #pedidos-table td:first-child {
            width: 70px;
            min-width: 50px;
            max-width: 90px;
            text-align: center;
        }

        /* Solución para el z-index de Select2 en modales */
        .select2-container--open {
            z-index: 99999;
        }

        /* Solución para el recorte de Select2 en tarjetas de producto */
        #productos-container .card {
            overflow: visible !important;
        }

        .insumo-btn-height {
            height: 39px !important;
            /* Altura fijada a 39px */
            line-height: 1.5;
            padding: 0.375rem 0.75rem;
            /* Ajustar padding si es necesario */
            display: flex;
            /* Usar flexbox para centrar contenido */
            align-items: center;
            /* Centrar verticalmente */
            justify-content: center;
            /* Centrar horizontalmente */
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

        .producto-selector-btn .producto-text {
            flex-grow: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .producto-selector-btn .placeholder-text {
            color: #6c757d;
        }

        #productosModalTable tbody tr {
            cursor: pointer;
            transition: background-color 0.15s;
        }

        #productosModalTable tbody tr:hover {
            background-color: #e3f2fd !important;
        }

        #productosModalTable tbody tr.selected {
            background-color: #bbdefb !important;
        }

        .producto-img-thumb {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
        }

        /* Z-index para que el modal de productos aparezca sobre el modal principal */
        #productosModal {
            z-index: 1060 !important;
        }

        #productosModal .modal-backdrop {
            z-index: 1055 !important;
        }
    </style>

    <!-- Modal para seleccionar producto -->
    <div class="modal fade" id="productosModal" tabindex="-1" aria-labelledby="productosModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header con gradiente marca Atlantico -->
                <div class="modal-header py-3"
                    style="background: linear-gradient(135deg, #1e3c72 0%, #2ecc71 50%, #00d9a5 100%);">
                    <h5 class="modal-title text-white d-flex align-items-center" id="productosModalLabel">
                        <i class="ri-search-line me-2 fs-4"></i>Buscar y Seleccionar Producto
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Card de Filtros -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header border-0" style="background: rgba(0, 217, 165, 0.1);">
                            <h6 class="mb-0" style="color: #00d9a5;">
                                <i class="ri-filter-3-line me-2"></i>Filtros de búsqueda
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-7">
                                    <div class="input-group">
                                        <span class="input-group-text"
                                            style="background: rgba(30, 60, 114, 0.1); border-color: #1e3c72;">
                                            <i class="ri-search-line" style="color: #1e3c72;"></i>
                                        </span>
                                        <input type="text" id="buscarProductoModal" class="form-control"
                                            placeholder="Buscar por código, tipo o modelo..."
                                            style="border-color: #1e3c72;">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <select id="filtroTipoProducto" class="form-select" style="border-color: #00d9a5;">
                                        <option value="">📁 Todos los tipos</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card de Tabla de Productos -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-0" style="background: rgba(30, 60, 114, 0.1);">
                            <h6 class="mb-0" style="color: #1e3c72;">
                                <i class="ri-store-2-line me-2"></i>Catálogo de Productos
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 350px;">
                                <table class="table table-hover mb-0" id="productosModalTable">
                                    <thead
                                        style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); position: sticky; top: 0;">
                                        <tr>
                                            <th class="text-white" width="60"><i class="ri-image-line"></i></th>
                                            <th class="text-white"><i class="ri-barcode-line me-1"></i>Código</th>
                                            <th class="text-white"><i class="ri-folder-line me-1"></i>Tipo</th>
                                            <th class="text-white"><i class="ri-t-shirt-line me-1"></i>Modelo</th>
                                            <th class="text-white"><i class="ri-money-dollar-circle-line me-1"></i>Precio
                                            </th>
                                            <th class="text-white text-center" width="80"><i class="ri-check-line"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody id="productosModalBody">
                                        <!-- Se llena con JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: rgba(0, 217, 165, 0.05);">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Listado de Pedidos</h5>
                        <div class="flex-shrink-0">
                            @if(Auth::user()->isAdmin())
                                <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn"
                                    data-bs-target="#showModal">
                                    <i class="ri-add-line align-bottom me-1"></i> Agregar Pedido
                                </button>
                            @endif
                            <a href="{{ route('pedidos.reporte.pdf') }}" target="_blank" class="btn btn-danger ms-2">
                                <i class="ri-file-pdf-line align-bottom me-1"></i> Exportar PDF
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="pedidos-table" class="table table-bordered table-striped table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Nro. de pedido</th>
                                    <th>Cliente</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Documento de identidad</th>
                                    <th>Fecha Pedido</th>
                                    <th>Fecha Entrega Estimada</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                    <th>Usuario Creador</th>
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
    </div>

    <!-- Modal para ver detalles del Pedido -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header con gradiente marca Atlantico -->
                <div class="modal-header py-3"
                    style="background: linear-gradient(135deg, #1e3c72 0%, #2ecc71 50%, #00d9a5 100%);">
                    <h5 class="modal-title text-white d-flex align-items-center">
                        <i class="ri-shopping-cart-line me-2 fs-4"></i>Detalles del Pedido
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Columna Izquierda -->
                        <div class="col-lg-6">
                            <!-- Card Datos del Cliente -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header border-0" style="background: rgba(0, 217, 165, 0.1);">
                                    <h6 class="mb-0" style="color: #00d9a5;">
                                        <i class="ri-user-star-line me-2"></i>Información del Cliente
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-user-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Cliente</small>
                                                    <span class="fw-semibold" id="view-cliente-nombre">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(46, 204, 113, 0.1);">
                                                    <i class="ri-bank-card-line" style="color: #2ecc71;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Documento</small>
                                                    <span class="fw-semibold" id="view-ci-rif">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(0, 217, 165, 0.1);">
                                                    <i class="ri-mail-line" style="color: #00d9a5;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Email</small>
                                                    <span class="fw-semibold" id="view-cliente-email">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-phone-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Teléfono</small>
                                                    <span class="fw-semibold" id="view-cliente-telefono">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Datos del Pedido -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header border-0" style="background: rgba(46, 204, 113, 0.1);">
                                    <h6 class="mb-0" style="color: #2ecc71;">
                                        <i class="ri-calendar-todo-line me-2"></i>Datos del Pedido
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-calendar-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Fecha Pedido</small>
                                                    <span class="fw-semibold" id="view-fecha-pedido">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(0, 217, 165, 0.1);">
                                                    <i class="ri-calendar-check-line" style="color: #00d9a5;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Entrega Estimada</small>
                                                    <span class="fw-semibold" id="view-fecha-entrega-estimada">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(46, 204, 113, 0.1);">
                                                    <i class="ri-flag-line" style="color: #2ecc71;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Prioridad</small>
                                                    <span class="fw-semibold" id="view-prioridad">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-checkbox-circle-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Estado</small>
                                                    <span class="fw-semibold" id="view-estado">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(0, 217, 165, 0.1);">
                                                    <i class="ri-user-settings-line" style="color: #00d9a5;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Creado por</small>
                                                    <span class="fw-semibold" id="view-usuario-creador">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Datos del Pago -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header border-0" style="background: rgba(30, 60, 114, 0.1);">
                                    <h6 class="mb-0" style="color: #1e3c72;">
                                        <i class="ri-wallet-line me-2"></i>Datos del Pago
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-4">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(46, 204, 113, 0.1);">
                                                    <i class="ri-hand-coin-line" style="color: #2ecc71;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Abono</small>
                                                    <span class="fw-semibold" id="view-abono">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(0, 217, 165, 0.1);">
                                                    <i class="ri-money-dollar-circle-line" style="color: #00d9a5;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Total</small>
                                                    <span class="fw-semibold" id="view-total">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-wallet-2-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Restante</small>
                                                    <span class="fw-semibold" id="view-restante">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(46, 204, 113, 0.1);">
                                                    <i class="ri-bank-card-2-line" style="color: #2ecc71;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Método Pago</small>
                                                    <span class="fw-semibold" id="view-metodo-pago">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4" id="view-referencia-transferencia-container">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(0, 217, 165, 0.1);">
                                                    <i class="ri-hashtag" style="color: #00d9a5;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Referencia</small>
                                                    <span class="fw-semibold" id="view-referencia-transferencia">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4" id="view-referencia-pago-movil-container" style="display:none;">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(0, 217, 165, 0.1);">
                                                    <i class="ri-hashtag" style="color: #00d9a5;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Ref. Pago Móvil</small>
                                                    <span class="fw-semibold" id="view-referencia-pago-movil">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4" id="view-banco-container">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-bank-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Banco</small>
                                                    <span class="fw-semibold" id="view-banco">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Productos -->
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header border-0" style="background: rgba(30, 60, 114, 0.1);">
                                    <h6 class="mb-0" style="color: #1e3c72;">
                                        <i class="ri-shopping-bag-3-line me-2"></i>Productos del Pedido
                                    </h6>
                                </div>
                                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                    <div id="view-productos-container">
                                        <!-- Productos se cargan dinámicamente -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar/editar -->
    <div class="modal fade" id="showModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="modalTitle">Agregar Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="pedidoForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="id-field" />
                        <input type="hidden" id="cliente-id-field" name="cliente_id" />
                        <div class="row">
                            <!-- Columna Izquierda: Datos del Cliente -->
                            <div class="col-lg-6">
                                <div class="card shadow-lg border-dark mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Datos del Cliente</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3 position-relative">
                                                    <label for="cliente-nombre-field" class="form-label">Nombre
                                                        Cliente</label>
                                                    <div class="input-group">
                                                        <input type="text" id="cliente-nombre-field" name="cliente_nombre"
                                                            class="form-control"
                                                            placeholder="Buscar o escribir nombre del cliente"
                                                            autocomplete="off" required />
                                                        <button type="button" class="btn btn-outline-success"
                                                            id="open-add-cliente-modal" title="Agregar nuevo cliente">
                                                            <i class="ri-user-add-line"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div id="cliente-autocomplete-list"
                                                    class="list-group position-absolute w-100" style="z-index: 1050;"></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="ci-rif-field" class="form-label">Documento de
                                                        identidad</label>
                                                    <div class="input-group">
                                                        <select class="form-select" id="ci-rif-prefix-field"
                                                            name="ci_rif_prefix" style="max-width: 80px;">
                                                            <option value="V-">V-</option>
                                                            <option value="J-">J-</option>
                                                            <option value="E-">E-</option>
                                                            <option value="G-">G-</option>
                                                        </select>
                                                        <input type="text" id="ci-rif-number-field" name="ci_rif_number"
                                                            class="form-control" placeholder="Número de documento"
                                                            required />
                                                    </div>
                                                    <!-- Este campo oculto se actualizará con el valor completo de CI/RIF -->
                                                    <input type="hidden" id="ci-rif-full-field" name="ci_rif" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="cliente-email-field" class="form-label">Email
                                                        Cliente</label>
                                                    <input type="email" id="cliente-email-field" name="cliente_email"
                                                        class="form-control" placeholder="Email del cliente" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="cliente-telefono-field" class="form-label">Teléfono
                                                        Cliente</label>
                                                    <input type="text" id="cliente-telefono-field" name="cliente_telefono"
                                                        class="form-control" placeholder="Teléfono del cliente" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="fecha-pedido-field" class="form-label">Fecha del
                                                        Pedido</label>
                                                    <input type="date" id="fecha-pedido-field" name="fecha_pedido"
                                                        class="form-control" required />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="fecha-entrega-estimada-field" class="form-label">Fecha de
                                                        Entrega Estimada</label>
                                                    <input type="date" id="fecha-entrega-estimada-field"
                                                        name="fecha_entrega_estimada" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="prioridad-field" class="form-label">Prioridad</label>
                                            <select id="prioridad-field" name="prioridad" class="form-control" required>
                                                <option value="Normal">Normal</option>
                                                <option value="Alta">Alta</option>
                                                <option value="Urgente">Urgente</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="card shadow-lg border-dark mb-3" id="estado-field-wrapper"
                                    style="display: none;">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Estado del Pedido</h5>
                                    </div>
                                    <div class="card-body">
                                        <label for="estado-field" class="form-label">Estado</label>
                                        <select id="estado-field" name="estado" class="form-control">
                                            <option value="Pendiente">Pendiente</option>
                                            <option value="Procesando">Procesando</option>
                                            <option value="Completado">Completado</option>
                                            <option value="Cancelado">Cancelado</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Sección de Pago (Movida y Agrupada) -->
                                <div class="card shadow-lg border-dark mb-2">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><i class="ri-wallet-line align-bottom me-1"></i> Datos
                                            del Pago</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-2">
                                                    <label for="abono-field" class="form-label"><i
                                                            class="ri-wallet-line align-bottom me-1"></i> Abono ($)</label>
                                                    <input type="number" id="abono-field" name="abono" class="form-control"
                                                        step="0.01" min="0" value="0" required />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-2">
                                                    <label for="total-display-field" class="form-label"><i
                                                            class="ri-currency-line align-bottom me-1"></i> Total del Pedido
                                                        ($)</label>
                                                    <input type="text" id="total-display-field" class="form-control"
                                                        readonly />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-2">
                                                    <label for="restante-display-field" class="form-label"><i
                                                            class="ri-wallet-2-line align-bottom me-1"></i> Restante
                                                        ($)</label>
                                                    <input type="text" id="restante-display-field" class="form-control"
                                                        readonly />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label"><i class="ri-bank-card-line align-bottom me-1"></i>
                                                Método de Pago</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="efectivo-pagado-field"
                                                    name="efectivo_pagado" value="1">
                                                <label class="form-check-label" for="efectivo-pagado-field">Efectivo</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox"
                                                    id="transferencia-pagado-field" name="transferencia_pagado" value="1">
                                                <label class="form-check-label"
                                                    for="transferencia-pagado-field">Transferencia</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="pago-movil-pagado-field"
                                                    name="pago_movil_pagado" value="1">
                                                <label class="form-check-label" for="pago-movil-pagado-field">Pago
                                                    Móvil</label>
                                            </div>
                                        </div>
                                        <div class="mb-2" id="referencia-transferencia-container">
                                            <label for="referencia-transferencia-field" class="form-label"><i
                                                    class="ri-numbers-line align-bottom me-1"></i> Referencia
                                                Transferencia</label>
                                            <input type="text" id="referencia-transferencia-field"
                                                name="referencia_transferencia" class="form-control"
                                                placeholder="Número de referencia" />
                                        </div>
                                        <div class="mb-2" id="referencia-pago-movil-container">
                                            <label for="referencia-pago-movil-field" class="form-label"><i
                                                    class="ri-smartphone-line align-bottom me-1"></i> Referencia Pago
                                                Móvil</label>
                                            <input type="text" id="referencia-pago-movil-field" name="referencia_pago_movil"
                                                class="form-control" placeholder="Número de referencia" />
                                        </div>
                                        <div class="mb-2" id="banco-container">
                                            <label for="banco-id-field" class="form-label"><i
                                                    class="ri-bank-line align-bottom me-1"></i> Banco</label>
                                            <select id="banco-id-field" name="banco_id" class="form-control">
                                                <option value="">Seleccione un banco</option>
                                                @foreach($bancos as $banco)
                                                    <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna Derecha: Productos del Pedido y Estado -->
                            <div class="col-lg-6">
                                <div class="card shadow-lg border-dark mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Productos del Pedido</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="productos-container">
                                            <!-- Aquí se agregarán dinámicamente los campos de producto -->
                                        </div>
                                        <button type="button" class="btn btn-sm btn-info mt-2"
                                            id="add-producto-item">Agregar Producto</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-success" id="add-btn">Agregar</button>
                            <button type="submit" class="btn btn-success" id="edit-btn"
                                style="display: none;">Actualizar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Agregar/Editar Cliente (reutilizado) -->
    <div class="modal fade" id="modalAddCliente" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="modalClienteTitle">Agregar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="clienteFormPedido">
                    <div class="modal-body">
                        <input type="hidden" id="id-field-cliente" />
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre-field-cliente" class="form-label">Nombre</label>
                                    <input type="text" id="nombre-field-cliente" name="nombre" class="form-control"
                                        placeholder="Nombre" required />
                                </div>
                                <div class="mb-3">
                                    <label for="tipo_cliente-field-cliente" class="form-label">Tipo de Cliente</label>
                                    <select id="tipo_cliente-field-cliente" name="tipo_cliente" class="form-control"
                                        required>
                                        <option value="">Seleccione tipo</option>
                                        <option value="natural">Natural</option>
                                        <option value="juridico">Jurídico</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="email-field-cliente" class="form-label">Email</label>
                                    <input type="email" id="email-field-cliente" name="email" class="form-control"
                                        placeholder="Email" required />
                                </div>
                                <div class="mb-3">
                                    <label for="telefono-field-cliente" class="form-label">Teléfono</label>
                                    <input type="text" id="telefono-field-cliente" name="telefono" class="form-control"
                                        placeholder="Teléfono" required />
                                </div>
                                <div class="mb-3">
                                    <label for="documento-field-cliente" class="form-label">Documento (Cédula o RIF)</label>
                                    <input type="text" id="documento-field-cliente" name="documento" class="form-control"
                                        placeholder="Cédula o RIF" required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="direccion-field-cliente" class="form-label">Dirección</label>
                                    <input type="text" id="direccion-field-cliente" name="direccion" class="form-control"
                                        placeholder="Dirección" required />
                                </div>
                                <div class="mb-3">
                                    <label for="ciudad-field-cliente" class="form-label">Ciudad</label>
                                    <input type="text" id="ciudad-field-cliente" name="ciudad" class="form-control"
                                        placeholder="Ciudad" required />
                                </div>
                                <div class="mb-3">
                                    <label for="estado-field-cliente" class="form-label">Estado</label>
                                    <select name="estado" id="estado-field-cliente" class="form-control form-select">
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                            <button type="button" class="btn btn-success" id="add-btn-cliente">Agregar</button>
                            <button type="button" class="btn btn-success" id="edit-btn-cliente"
                                style="display: none;">Actualizar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Librerías para Autocorrección y Autocompletado -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fuse.js@6.6.2/dist/fuse.min.js"></script>

    <script>
            // === SISTEMA DE AUT    OCOR      RECCIÓN Y AUTOCOMPLETADO ===

            // Diccionarios de datos comunes para autocorrección
            const commonNames = [
                'María', 'José', 'Ana', 'Carlos', 'Luis', 'Carmen', 'Antonio', 'Francisco', 'Manuel', 'Jesús',
                'David', 'Daniel', 'Miguel', 'Rafael', 'Pedro', 'Ángel', 'Alejandro', 'Diego', 'Andrés', 'Pablo',
                'Juan', 'Javier', 'Fernando', 'Sergio', 'Adrián', 'Alberto', 'Eduardo', 'Iván', 'Roberto', 'Rubén',
                'Ramón', 'Raúl', 'Enrique', 'Víctor', 'Jorge', 'Mario', 'Arturo', 'Ricardo', 'Emilio', 'Gonzalo',
                'Laura', 'Marta', 'Elena', 'Cristina', 'Pilar', 'Dolores', 'Isabel', 'Antonia', 'Teresa', 'Rosa',
                'Lucía', 'Sofía', 'Paula', 'Natalia', 'Silvia', 'Beatriz', 'Mónica', 'Raquel', 'Susana', 'Patricia'
            ];

            const commonCities = [
                'Caracas', 'Maracaibo', 'Valencia', 'Barquisimeto', 'Maracay', 'Ciudad Guayana', 'San Cristóbal',
                'Maturín', 'Ciudad Bolívar', 'Cumaná', 'Mérida', 'Barcelona', 'Turmero', 'Cabimas', 'Coro',
                'Punto Fijo', 'Los Teques', 'Guanare', 'Acarigua', 'Araure', 'El Tigre', 'Anaco', 'Puerto La Cruz',
                'Porlamar', 'La Guaira', 'Catia La Mar', 'Guarenas', 'Guatire', 'Charallave', 'Cúa', 'Ocumare del Tuy'
            ];

            const commonMaterials = [
                'Algodón', 'Poliéster', 'Seda', 'Lino', 'Denim', 'Lycra', 'Spandex', 'Nylon', 'Rayón', 'Viscosa',
                'Lana', 'Cashmere', 'Mohair', 'Alpaca', 'Bambú', 'Modal', 'Tencel', 'Microfibra', 'Polar', 'Franela',
                'Gabardina', 'Drill', 'Popelina', 'Oxford', 'Chambray', 'Crepe', 'Chiffon', 'Tafetán', 'Organza', 'Tul'
            ];

            const commonColors = [
                'Blanco', 'Negro', 'Azul', 'Rojo', 'Verde', 'Amarillo', 'Rosa', 'Morado', 'Naranja', 'Gris',
                'Marrón', 'Beige', 'Crema', 'Dorado', 'Plateado', 'Turquesa', 'Coral', 'Lavanda', 'Menta', 'Salmón',
                'Azul Marino', 'Verde Oliva', 'Rojo Vino', 'Rosa Pálido', 'Gris Claro', 'Gris Oscuro', 'Azul Cielo',
                'Verde Esmeralda', 'Púrpura', 'Magenta', 'Cian', 'Lima', 'Índigo', 'Violeta', 'Carmesí', 'Escarlata'
            ];

            // Función para calcular distancia de Levenshtein (similitud entre strings)
            function levenshteinDistance(str1, str2) {
                const matrix = [];
                for (let i = 0; i <= str2.length; i++) {
                    matrix[i] = [i];
                }
                for (let j = 0; j <= str1.length; j++) {
                    matrix[0][j] = j;
                }
                for (let i = 1; i <= str2.length; i++) {
                    for (let j = 1; j <= str1.length; j++) {
                        if (str2.charAt(i - 1) === str1.charAt(j - 1)) {
                            matrix[i][j] = matrix[i - 1][j - 1];
                        } else {
                            matrix[i][j] = Math.min(
                                matrix[i - 1][j - 1] + 1,
                                matrix[i][j - 1] + 1,
                                matrix[i - 1][j] + 1
                            );
                        }
                    }
                }
                return matrix[str2.length][str1.length];
            }

            // Función para encontrar sugerencias similares
            function findSimilarSuggestions(input, dictionary, threshold = 2) {
                if (!input || input.length < 2) return [];

                const suggestions = [];
                const inputLower = input.toLowerCase().trim();

                dictionary.forEach(word => {
                    const wordLower = word.toLowerCase();
                    const distance = levenshteinDistance(inputLower, wordLower);
                    const similarity = 1 - (distance / Math.max(inputLower.length, wordLower.length));

                    if (distance <= threshold && similarity > 0.6 && wordLower !== inputLower) {
                        suggestions.push({
                            word: word,
                            distance: distance,
                            similarity: similarity
                        });
                    }
                });

                return suggestions.sort((a, b) => b.similarity - a.similarity).slice(0, 3);
            }

            // Función para mostrar sugerencia de autocorrección
            function showAutocorrectSuggestion(field, suggestions) {
                hideAutocorrectSuggestion(field);

                if (suggestions.length === 0) return;

                const $field = $(field);
                const fieldOffset = $field.offset();
                const suggestion = suggestions[0]; // Tomar la mejor sugerencia

                const suggestionHtml = `
                                                                                                                                                <div class="autocorrect-suggestion" data-field-id="${field.id}">
                                                                                                                                                    <div class="suggestion-text">
                                                                                                                                                        ¿Quisiste decir "<strong>${suggestion.word}</strong>"?
                                                                                                                                                    </div>
                                                                                                                                                    <div class="suggestion-buttons">
                                                                                                                                                        <button type="button" class="btn btn-sm btn-warning btn-suggestion accept-suggestion" data-suggestion="${suggestion.word}">
                                                                                                                                                            Sí
                                                                                                                                                        </button>
                                                                                                                                                        <button type="button" class="btn btn-sm btn-secondary btn-suggestion ignore-suggestion">
                                                                                                                                                            No
                                                                                                                                                        </button>
                                                                                                                                                    </div>
                                                                                                                                                                                                           </div>
                                                                                                                                            `;

                $('body').append(suggestionHtml);

                const $suggestionBox = $('.autocorrect-suggestion[data-field-id="' + field.id + '"]');
                $suggestionBox.css({
                    top: fieldOffset.top + $field.outerHeight() + 5,
                    left: fieldOffset.left
                });

                $field.addClass('field-with-suggestion');

                // Auto-hide después de 10 segundos
                setTimeout(() => {
                    hideAutocorrectSuggestion(field);
                }, 10000);
            }

            // Función para ocultar sugerencia de autocorrección
            function hideAutocorrectSuggestion(field) {
                $(field).removeClass('field-with-suggestion');
                $('.autocorrect-suggestion[data-field-id="' + field.id + '"]').remove();
            }

            // Eventos para manejar sugerencias
            $(document).on('click', '.accept-suggestion', function () {
                const suggestion = $(this).data('suggestion');
                const fieldId = $(this).closest('.autocorrect-suggestion').data('field-id');
                const field = document.getElementById(fieldId);

                $(field).val(suggestion).trigger('change');
                hideAutocorrectSuggestion(field);
            });

            $(document).on('click', '.ignore-suggestion', function () {
                const fieldId = $(this).closest('.autocorrect-suggestion').data('field-id');
                const field = document.getElementById(fieldId);
                hideAutocorrectSuggestion(field);
            });

            // Función para aplicar autocorrección a un campo
            function applyAutocorrection(field, dictionary) {
                $(field).on('blur', function () {
                    const value = $(this).val().trim();
                    if (value.length >= 2) {
                        const suggestions = findSimilarSuggestions(value, dictionary);
                        if (suggestions.length > 0) {
                            showAutocorrectSuggestion(this, suggestions);
                        }
                    }
                });
            }

            // Función para aplicar autocompletado avanzado
            function applyAdvancedAutocomplete(field, dictionary, options = {}) {
                $(field).autocomplete({
                    source: function (request, response) {
                        const fuse = new Fuse(dictionary, {
                            threshold: 0.4,
                            includeScore: true,
                            minMatchCharLength: 2
                        });

                        const results = fuse.search(request.term);
                        const suggestions = results.slice(0, 8).map(result => ({
                            label: result.item,
                            value: result.item,
                            score: result.score
                        }));

                        response(suggestions);
                    },
                    minLength: 2,
                    delay: 300,
                    appendTo: options.appendTo || 'body',
                    select: function (event, ui) {
                        $(this).val(ui.item.value);
                        return false;
                    }
                });
            }

            // Función para normalizar texto (capitalizar nombres)
            function normalizeText(text, type = 'name') {
                if (!text) return text;

                switch (type) {
                    case 'name':
                        return text.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
                    case 'email':
                        return text.toLowerCase().trim();
                    case 'city':
                        return text.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
                    default:
                        return text.trim();
                }
            }

            // Función para validar formato de email
            function validateEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            // Función para validar formato de teléfono venezolano
            function validateVenezuelanPhone(phone) {
                const phoneRegex = /^(0414|0424|0412|0416|0426|0212|0241|0243|0244|0245|0246|0247|0248|0249|0251|0252|0253|0254|0255|0256|0257|0258|0259|0261|0262|0263|0264|0265|0266|0267|0268|0269|0271|0272|0273|0274|0275|0276|0277|0278|0279|0281|0282|0283|0284|0285|0286|0287|0288|0289|0291|0292|0293|0294|0295)\d{7}$/;
                return phoneRegex.test(phone.replace(/\D/g, ''));
            }

            // Función para formatear teléfono venezolano
            function formatVenezuelanPhone(phone) {
                const cleaned = phone.replace(/\D/g, '');
                if (cleaned.length === 11) {
                    return cleaned.replace(/(\d{4})(\d{3})(\d{4})/, '$1-$2-$3');
                }
                return phone;
            }

            // Función para sugerir corrección de email
            function suggestEmailCorrection(email) {
                const commonDomains = ['gmail.com', 'hotmail.com', 'yahoo.com', 'outlook.com', 'cantv.net'];
                const parts = email.split('@');

                if (parts.length !== 2) return null;

                const [username, domain] = parts;
                const suggestions = findSimilarSuggestions(domain, commonDomains, 1);

                if (suggestions.length > 0) {
                    return `${username}@${suggestions[0].word}`;
                }

                return null;
            }

            $(document).ready(function () {
                // === INICIALIZACIÓN DEL SISTEMA DE AUTOCORRECCIÓN ===
                // Autocorrector desactivado por solicitud del usuario. Todas las funciones de autocorrección han sido eliminadas/comentadas.
                // Puedes reactivar la autocorrección restaurando las llamadas a applyAutocorrection y showAutocorrectSuggestion si lo deseas en el futuro.
                // Función para mostrar sugerencia de email
                function showEmailSuggestion(field, suggestion) {
                    hideAutocorrectSuggestion(field);

                    const $field = $(field);
                    const fieldOffset = $field.offset();

                    const suggestionHtml = `
                                                                                                                                                    <div class="autocorrect-suggestion" data-field-id="${field.id}">
                                                                                                                                                        <div class="suggestion-text">
                                                                                                                                                            ¿Quisiste decir "<strong>${suggestion}</strong>"?
                                                                                                                                                        </div>
                                                                                                                                                        <div class="suggestion-buttons">
                                                                                                                                                            <button type="button" class="btn btn-sm btn-warning btn-suggestion accept-suggestion" data-suggestion="${suggestion}">
                                                                                                                                                                Sí
                                                                                                                                                            </button>
                                                                                                                                                            <button type="button" class="btn btn-sm btn-secondary btn-suggestion ignore-suggestion">
                                                                                                                                                                No
                                                                                                                                                            </button>
                                                                                                                                                        </div>
                                                                                                                                                    </div>
                                                                                                                                                `;

                    $('body').append(suggestionHtml);

                    const $suggestionBox = $('.autocorrect-suggestion[data-field-id="' + field.id + '"]');
                    $suggestionBox.css({
                        top: fieldOffset.top + $field.outerHeight() + 5,
                        left: fieldOffset.left
                    });

                    $field.addClass('field-with-suggestion');

                    setTimeout(() => {
                        hideAutocorrectSuggestion(field);
                    }, 10000);
                }

                // Función para mostrar error de formato de email
                function showEmailFormatError(field) {
                    $(field).addClass('is-invalid');
                    let feedback = $(field).siblings('.invalid-feedback');
                    if (feedback.length === 0) {
                        $(field).after('<div class="invalid-feedback">Por favor ingrese un email válido</div>');
                    } else {
                        feedback.text('Por favor ingrese un email válido');
                    }
                }

                // Función para mostrar error de formato de teléfono
                function showPhoneFormatError(field) {
                    $(field).addClass('is-invalid');
                    let feedback = $(field).siblings('.invalid-feedback');
                    if (feedback.length === 0) {
                        $(field).after('<div class="invalid-feedback">Formato de teléfono inválido. Ejemplo: 0414-123-4567</div>');
                    } else {
                        feedback.text('Formato de teléfono inválido. Ejemplo: 0414-123-4567');
                    }
                }

                // Autocorrector desactivado por solicitud del usuario. No se inicializa autocorrección.
                // Limpiar sugerencias al cerrar modales
                $('#showModal, #modalAddCliente').on('hidden.bs.modal', function () {
                    $('.autocorrect-suggestion').remove();
                    $('.field-with-suggestion').removeClass('field-with-suggestion');
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();
                });

                var table = $('#pedidos-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('pedidos.data') }}",
                    columns: [
                        { data: 'id', name: 'id', title: 'Nro. de pedido' },
                        { data: 'cliente_nombre_display', name: 'cliente_nombre_display', defaultContent: 'N/A' },
                        { data: 'cliente_email_display', name: 'cliente_email_display', defaultContent: 'N/A' },
                        { data: 'cliente_telefono_display', name: 'cliente_telefono_display', defaultContent: 'N/A' },
                        { data: 'cliente_documento_display', name: 'cliente_documento_display', defaultContent: 'N/A' },
                        { data: 'fecha_pedido', name: 'fecha_pedido' },
                        { data: 'fecha_entrega_estimada', name: 'fecha_entrega_estimada' },
                        { data: 'estado', name: 'estado' },
                        { data: 'total', name: 'total' },
                        { data: 'user.name', name: 'user.name', defaultContent: '' },
                        {
                            data: 'id',
                            name: 'actions',
                            orderable: false,
                            searchable: false,
                            render: function (data) {
                                var isAdmin = {{ Auth::user()->isAdmin() ? 'true' : 'false' }};
                                var editDelete = isAdmin ? `
                                                                    <li>
                                                                        <button class="dropdown-item edit-btn" data-id="${data}">
                                                                            <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Editar
                                                                        </button>
                                                                    </li>
                                                                    <li>
                                                                        <button class="dropdown-item remove-btn" data-id="${data}">
                                                                            <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Eliminar
                                                                        </button>
                                                                    </li>` : '';
                                return `
                                                                    <div class="dropdown d-inline-block">
                                                                        <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                            <i class="ri-more-fill align-middle"></i>
                                                                        </button>
                                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                                            <li>
                                                                                <button class="dropdown-item view-btn" data-id="${data}">
                                                                                    <i class="ri-eye-fill align-bottom me-2 text-muted"></i> Ver
                                                                                </button>
                                                                            </li>
                                                                            ${editDelete}
                                                                            <li>
                                                                                <a class="dropdown-item" href="/pedidos/${data}/pdf" target="_blank">
                                                                                    <i class="ri-file-pdf-line align-bottom me-2 text-danger"></i> PDF
                                                                                </a>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                `;
                            }
                        }
                    ],
                    order: [[0, 'desc']],
                    dom: 'frtip',
                    buttons: [
                        {
                            extend: 'copy',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9] // Adjust columns as needed
                            }
                        },
                        {
                            extend: 'excel',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9] // Adjust columns as needed
                            }
                        }
                    ],
                    language: {
                        "sProcessing": "Procesando...",
                        "sLengthMenu": "Mostrar _MENU_ registros",
                        "sZeroRecords": "No se encontraron resultados",
                        "sEmptyTable": "Ningún dato disponible en esta tabla",
                        "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                        "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                        "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                        "sInfoPostFix": "",
                        "sSearch": "Buscar:",
                        "sUrl": "",
                        "sInfoThousands": ",",
                        "sLoadingRecords": "Cargando...",
                        "oPaginate": {
                            "sFirst": "Primero",
                            "sLast": "Último",
                            "sNext": "Siguiente",
                            "sPrevious": "Anterior"
                        },
                        "oAria": {
                            "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                        },
                        "buttons": {
                            "copy": "Copiar",
                            "copyTitle": "Copiado al portapapeles",
                            "copySuccess": {
                                "1": "1 fila copiada al portapapeles",
                                "_": "%d filas copiadas al portapapeles"
                            },
                            "csv": "CSV",
                            "excel": "Excel",
                            "print": "Imprimir",
                            "colvis": "Visibilidad de Columna"
                        }
                    }
                });

                window.products = @json($productos);
                var insumos = @json($insumos);
                window.productItemIndex = 0;
                var productItemIndex = window.productItemIndex;

                window.addProductItem = function addProductItem(productoId = '', cantidad = '', precioUnitario = '', descripcion = '', llevaBordado = false, nombreLogo = '', color = '', talla = '', productInsumos = [], ubicacionLogo = '', cantidadLogo = '') {
                    // Obtener nombre del producto seleccionado (si hay)
                    var productoText = '🔍 Clic para buscar producto...';
                    if (productoId) {
                        var productoEncontrado = products.find(p => p.id == productoId);
                        if (productoEncontrado) {
                            var tipoNombre = productoEncontrado.tipo_producto ? productoEncontrado.tipo_producto.nombre : 'Sin tipo';
                            productoText = (productoEncontrado.codigo || '') + ' - ' + tipoNombre + ' ' + productoEncontrado.modelo;
                        }
                    }

                    var insumoOptions = '<option value="">Seleccione insumo...</option>';
                    insumos.forEach(function (insumo) {
                        insumoOptions += `<option value="${insumo.id}">${insumo.nombre} (${insumo.unidad_medida})</option>`;
                    });

                    var itemHtml = `
                                                                                                                                                    <div class="card mb-3 shadow-lg border-dark" data-product-index="${productItemIndex}">
                                                                                                                                                        <div class="card-body">
                                                                                                                                                            <h5 class="card-title">Nuevo Producto</h5>
                                                                                                                                                            <div class="row product-item align-items-center">
                                                                                                                                                                <div class="col-md-9 d-flex align-items-center">
                                                                                                                                                                    <input type="hidden" name="productos[${productItemIndex}][producto_id]" class="producto-id-input" value="${productoId}" required>
                                                                                                                                                                    <div class="producto-selector-btn flex-grow-1" data-product-index="${productItemIndex}">
                                                                                                                                                                        <span class="producto-text ${!productoId ? 'placeholder-text' : ''}">${productoText}</span>
                                                                                                                                                                        <i class="ri-arrow-down-s-line"></i>
                                                                                                                                                                    </div>
                                                                                                                                                                    <span class="ms-1 fw-bold text-success precio-producto-span">${precioUnitario ? '$ ' + parseFloat(precioUnitario).toFixed(2) : ''}</span>
                                                                                                                                                                </div>
                                                                                                                                                                <div class="col-md-3">
                                                                                                                                                                    <input type="number" name="productos[${productItemIndex}][cantidad]" class="form-control cantidad-input" placeholder="Cant." min="1" value="${cantidad}" required />
                                                                                                                                                                </div>
                                                                                                                                                                <div class="col-md-6 mt-2">
                                                                                                                                                                    <input type="text" name="productos[${productItemIndex}][color]" class="form-control" placeholder="Seleccione un color" value="${color}" required />
                                                                                                                                                                </div>
                                                                                                                                                                <div class="col-md-6 mt-2">
                                                                                                                                                                    <select name="productos[${productItemIndex}][talla]" class="form-control" required>
                                                                                                                                                                        <option value="">Seleccione una talla</option>
                                                                                                                                                                        <option value="Talla Unica" ${talla == 'Talla Unica' ? 'selected' : ''}>Talla Unica</option>
                                                                                                                                                                        <option value="2" ${talla == '2' ? 'selected' : ''}>2</option>
                                                                                                                                                                        <option value="4" ${talla == '4' ? 'selected' : ''}>4</option>
                                                                                                                                                                        <option value="6" ${talla == '6' ? 'selected' : ''}>6</option>
                                                                                                                                                                        <option value="8" ${talla == '8' ? 'selected' : ''}>8</option>
                                                                                                                                                                        <option value="10" ${talla == '10' ? 'selected' : ''}>10</option>
                                                                                                                                                                        <option value="12" ${talla == '12' ? 'selected' : ''}>12</option>
                                                                                                                                                                        <option value="14" ${talla == '14' ? 'selected' : ''}>14</option>
                                                                                                                                                                        <option value="16" ${talla == '16' ? 'selected' : ''}>16</option>
                                                                                                                                                                        <option value="XS" ${talla == 'XS' ? 'selected' : ''}>XS</option>
                                                                                                                                                                        <option value="S" ${talla == 'S' ? 'selected' : ''}>S</option>
                                                                                                                                                                        <option value="M" ${talla == 'M' ? 'selected' : ''}>M</option>
                                                                                                                                                                        <option value="L" ${talla == 'L' ? 'selected' : ''}>L</option>
                                                                                                                                                                        <option value="XL" ${talla == 'XL' ? 'selected' : ''}>XL</option>
                                                                                                                                                                        <option value="XXL" ${talla == 'XXL' ? 'selected' : ''}>XXL</option>
                                                                                                                                                                    </select>
                                                                                                                                                                </div>
                                                                                                                                                                <div class="col-md-12 mt-2">
                                                                                                                                                                    <textarea name="productos[${productItemIndex}][descripcion]" class="form-control" placeholder="Descripción del producto (opcional)" rows="2">${descripcion}</textarea>
                                                                                                                                                                </div>
                                                                                                                                                                <div class="col-md-12 mt-2">
                                                                                                                                                                    <div class="form-check form-switch">
                                                                                                                                                                        <input type="hidden" name="productos[${productItemIndex}][lleva_bordado]" value="0">
                                                                                                                                                                        <input class="form-check-input lleva-bordado-checkbox" type="checkbox" id="lleva-bordado-${productItemIndex}" name="productos[${productItemIndex}][lleva_bordado]" value="1" ${llevaBordado ? 'checked' : ''}>
                                                                                                                                                                        <label class="form-check-label" for="lleva-bordado-${productItemIndex}">Lleva Bordado/Logo</label>
                                                                                                                                                                    </div>
                                                                                                                                                                </div>
                                                                                                                                                                <div class="col-md-12 mt-2 nombre-logo-container" style="display: ${llevaBordado ? 'block' : 'none'}">
                                                                                                                                                                    <input type="text" name="productos[${productItemIndex}][nombre_logo]" class="form-control nombre-logo-input" placeholder="Nombre del logo a bordar" value="${nombreLogo}" />
                                                                                                                                                                    <input type="text" name="productos[${productItemIndex}][ubicacion_logo]" class="form-control mt-2 ubicacion-logo-input" placeholder="Ubicación del bordado/logo (ej: Pecho izquierdo)" value="${ubicacionLogo || ''}" />
                                                                                                                                                                    <input type="number" name="productos[${productItemIndex}][cantidad_logo]" class="form-control mt-2 cantidad-logo-input" placeholder="Cantidad de logos/bordados" min="1" value="${cantidadLogo || 1}" />
                                                                                                                                                                </div>
                                                                                                                                                                <div class="col-md-12 mt-4">
                                                                                                                                                                    <label class="form-label">Insumos y Telas Requeridas</label>
                                                                                                                                                                    <div id="insumos-container-${productItemIndex}">
                                                                                                                                                                        <!-- Los insumos se agregarán aquí dinámicamente -->
                                                                                                                                                                    </div>
                                                                                                                                                                    <div class="mt-3">
                                                                                                                                                                        <button type="button" class="btn btn-sm btn-info add-insumo-btn" data-product-item-index="${productItemIndex}">
                                                                                                                                                                            <i class="ri-add-line"></i> Agregar Insumo
                                                                                                                                                                        </button>
                                                                                                                                                                    </div>
                                                                                                                                                                    <div class="text-end mt-5">
                                                                                                                                                                        <button type="button" class="btn btn-danger btn-sm remove-producto-item">Eliminar Producto</button>
                                                                                                                                                                    </div>
                                                                                                                                                                </div>
                                                                                                                                                                <input type="hidden" name="productos[${productItemIndex}][precio_unitario]" class="precio-unitario-input" value="${precioUnitario}" />
                                                                                                                                                            </div>
                                                                                                                                                        </div>
                                                                                                                                                    </div>
                                                                                                                                                `;
                    $('#productos-container').append(itemHtml);

                    // Inicializar Select2 con búsqueda para el select de productos
                    $('#productos-container').find('.product-select').last().select2({
                        theme: 'bootstrap-5',
                        placeholder: '🔍 Buscar producto...',
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#showModal').length ? $('#showModal') : $('body'),
                        language: {
                            noResults: function () {
                                return 'No se encontraron productos';
                            },
                            searching: function () {
                                return 'Buscando...';
                            }
                        }
                    });
                    // Añadir los insumos preexistentes si los hay al editar el pedido
                    if (productInsumos.length > 0) {
                        productInsumos.forEach(function (insumo) {
                            addInsumoItem(productItemIndex, insumo.insumo_id, insumo.cantidad_estimada);
                        });
                    }

                    // Si no hay insumos preexistentes, agregar una fila vacía por defecto
                    if (productInsumos.length === 0) {
                        addInsumoItem(productItemIndex);
                    }

                    window.productItemIndex++;

                    // Inicializar el estado del campo nombre_logo y nuevos campos
                    toggleNombreLogo($(`#lleva-bordado-${productItemIndex - 1}`));
                }

                // Nueva función para agregar un insumo individualmente
                function addInsumoItem(currentProductItemIndex, insumoId = '', cantidadEstimada = '') {
                    let insumoIndex = $(`#insumos-container-${currentProductItemIndex}`).find('.insumo-row').length;
                    let newInsumoRow = `
                                                                                                                                                    <div class="row insumo-row mt-2">
                                                                                                                                                        <div class="col-md-5">
                                                                                                                                                            <select name="productos[${currentProductItemIndex}][insumos][${insumoIndex}][id]" class="form-control insumo-select" required data-insumo-index="${currentProductItemIndex}-${insumoIndex}">
                                                                                                                                                                <option value="">Seleccione insumo...</option>
                                                                                                                                                                ${insumos.map(insumo => {
                        let color = parseFloat(insumo.stock_actual) <= parseFloat(insumo.stock_minimo) ? 'red' : 'green';
                        let stockText = `<span style=\"color:${color};font-weight:bold;\">Stock: ${parseFloat(insumo.stock_actual).toFixed(2)}</span>`;
                        return `<option value="${insumo.id}"${insumoId == insumo.id ? ' selected' : ''} data-stock="${insumo.stock_actual}" data-stock-minimo="${insumo.stock_minimo}" data-nombre="${insumo.nombre}" data-unidad="${insumo.unidad_medida}">${insumo.nombre} (${insumo.unidad_medida}) - Stock: ${parseFloat(insumo.stock_actual).toFixed(2)}</option>`;
                    }).join('')}
                                                                                                                                                            </select>
                                                                                                                                                            <div class="stock-actual-info mt-1" id="stock-info-${currentProductItemIndex}-${insumoIndex}"></div>
                                                                                                                                                        </div>
                                                                                                                                                        <div class="col-md-3">
                                                                                                                                                            <input type="number" name="productos[${currentProductItemIndex}][insumos][${insumoIndex}][cantidad_estimada]" class="form-control" placeholder="Cantidad" step="0.01" min="0.01" value="${cantidadEstimada}" required />
                                                                                                                                                        </div>
                                                                                                                                                        <div class="col-md-4">
                                                                                                                                                            <button type="button" class="btn btn-danger btn-sm form-control insumo-btn-height remove-insumo">Eliminar Insumo</button>
                                                                                                                                                        </div>
                                                                                                                                                    </div>
                                                                                                                                                `;
                    $(`#insumos-container-${currentProductItemIndex}`).append(newInsumoRow);
                    initializeSelect2($(`#insumos-container-${currentProductItemIndex} .insumo-select`).last());

                    // Aplicar formato HTML a las opciones usando Select2
                    $(`#insumos-container-${currentProductItemIndex} .insumo-select`).last().select2({
                        theme: 'bootstrap-5',
                        placeholder: 'Seleccione insumo...',
                        width: '100%',
                        dropdownParent: $('#showModal .modal-body'),
                        templateResult: function (data) {
                            if (!data.id) return data.text;
                            let option = $(data.element);
                            let html = option.data('html');
                            return html ? $(`<span>${html}</span>`) : data.text;
                        },
                        templateSelection: function (data) {
                            if (!data.id) return data.text;
                            let option = $(data.element);
                            let html = option.data('html');
                            return html ? $(`<span>${html}</span>`) : data.text;
                        },
                        escapeMarkup: function (m) { return m; }
                    });
                }

                // Evento para agregar más insumos (delegado)
                $('#productos-container').on('click', '.add-insumo-btn', function () {
                    let currentProductItemIndex = $(this).data('product-item-index');
                    addInsumoItem(currentProductItemIndex);
                });

                // Remover fila de insumo (delegado)
                $('#productos-container').on('click', '.remove-insumo', function () {
                    $(this).closest('.insumo-row').remove();
                });

                // Función para alternar la visibilidad del campo nombre_logo y los nuevos campos
                function toggleNombreLogo(checkbox) {
                    var container = $(checkbox).closest('.product-item').find('.nombre-logo-container');
                    if ($(checkbox).is(':checked')) {
                        container.show();
                        container.find('.nombre-logo-input').prop('required', true);
                        container.find('.ubicacion-logo-input').prop('required', true);
                        container.find('.cantidad-logo-input').prop('required', true);
                    } else {
                        container.hide();
                        container.find('.nombre-logo-input').val('').prop('required', false);
                        container.find('.ubicacion-logo-input').val('').prop('required', false);
                        container.find('.cantidad-logo-input').val('').prop('required', false);
                    }
                }

                // Evento para el checkbox lleva_bordado
                $('#productos-container').on('change', '.lleva-bordado-checkbox', function () {
                    toggleNombreLogo(this);
                });

                // Funciones para manejar los nuevos campos de pago y prioridad
                let currentPedidoTotal = 0; // Para almacenar el total del pedido, ya sea del backend o calculado

                function calculateProductTotals() {
                    let sum = 0;
                    $('#productos-container .product-item').each(function () {
                        let quantity = parseFloat($(this).find('.cantidad-input').val()) || 0;
                        let price = parseFloat($(this).find('.precio-unitario-input').val()) || 0;
                        sum += (quantity * price);
                    });
                    currentPedidoTotal = sum;
                    $('#total-display-field').val(currentPedidoTotal.toFixed(2));
                    updateRemaining();
                }

                function updateRemaining() {
                    let abono = parseFloat($('#abono-field').val()) || 0;
                    let restante = currentPedidoTotal - abono;
                    $('#restante-display-field').val(restante.toFixed(2));
                }

                function togglePaymentFieldsVisibility() {
                    let transferenciaChecked = $('#transferencia-pagado-field').is(':checked');
                    let pagoMovilChecked = $('#pago-movil-pagado-field').is(':checked');
                    let requiresBank = transferenciaChecked || pagoMovilChecked;

                    // Referencia Transferencia
                    if (transferenciaChecked) {
                        $('#referencia-transferencia-container').show();
                        $('#referencia-transferencia-field').prop('required', true);
                    } else {
                        $('#referencia-transferencia-container').hide();
                        $('#referencia-transferencia-field').val('').prop('required', false);
                    }

                    // Referencia Pago Móvil
                    if (pagoMovilChecked) {
                        $('#referencia-pago-movil-container').show();
                        $('#referencia-pago-movil-field').prop('required', true);
                    } else {
                        $('#referencia-pago-movil-container').hide();
                        $('#referencia-pago-movil-field').val('').prop('required', false);
                    }

                    // Banco
                    if (requiresBank) {
                        $('#banco-container').show();
                        $('#banco-id-field').prop('required', true);
                    } else {
                        $('#banco-container').hide();
                        $('#banco-id-field').val('').prop('required', false);
                    }
                }

                // Eventos para los nuevos campos de pago y prioridad
                $('#abono-field').on('change keyup', updateRemaining);
                $('#efectivo-pagado-field, #transferencia-pagado-field, #pago-movil-pagado-field').on('change', togglePaymentFieldsVisibility);
                $('#productos-container').on('change', '.cantidad-input', calculateProductTotals); // Recalcular total cuando cambia la cantidad

                $('#create-btn').on('click', function () {
                    $('#modalTitle').text('Agregar Pedido');
                    $('#pedidoForm')[0].reset();
                    $('#id-field').val('');
                    $('#cliente-id-field').val(''); // Limpiar cliente_id para nuevo pedido
                    $('#add-btn').show();
                    $('#edit-btn').hide();
                    $('#estado-field-wrapper').hide(); // Ocultar estado en agregar
                    $('#productos-container').empty(); // Limpiar productos existentes
                    addProductItem(); // Añadir un item de producto vacío

                    // Resetear y ocultar nuevos campos de pago/prioridad
                    $('#abono-field').val(0);
                    $('#efectivo-pagado-field').prop('checked', false);
                    $('#transferencia-pagado-field').prop('checked', false);
                    $('#pago-movil-pagado-field').prop('checked', false);
                    $('#referencia-transferencia-field').val('');
                    $('#referencia-pago-movil-field').val('');
                    $('#banco-id-field').val('');
                    $('#prioridad-field').val('Normal'); // Valor por defecto
                    currentPedidoTotal = 0; // Resetear total para un nuevo pedido
                    calculateProductTotals(); // Calcular el total inicial (que será 0)
                    togglePaymentFieldsVisibility(); // Ocultar referencias y banco inicialmente
                });

                // Evento para agregar más productos
                $('#add-producto-item').on('click', function () {
                    addProductItem();
                });

                // Evento para remover producto
                $('#productos-container').on('click', '.remove-producto-item', function () {
                    $(this).closest('.card').remove();
                });

                // Función para combinar el prefijo y el número del RIF/CI
                function updateRifFullField() {
                    let prefix = $('#ci-rif-prefix-field').val();
                    let number = $('#ci-rif-number-field').val();
                    $('#ci-rif-full-field').val(prefix + number);
                }

                // Escuchar cambios en el prefijo y el número del RIF/CI
                $('#ci-rif-prefix-field, #ci-rif-number-field').on('change keyup', updateRifFullField);

                // Actualizar precio_unitario oculto cuando se selecciona un producto
                $('#productos-container').on('change', '.product-select', function () {
                    var selectedOption = $(this).find('option:selected');
                    var precio = selectedOption.data('precio');
                    $(this).closest('.card').find('.precio-unitario-input').val(precio);
                    // Mostrar el precio en el span a la derecha
                    $(this).siblings('.precio-producto-span').text(precio ? '$' + parseFloat(precio).toFixed(2) : '');
                    calculateProductTotals(); // Recalcular total cuando cambia el producto
                });

                $('#pedidoForm').on('submit', function (e) {
                    e.preventDefault();
                    let formData = new FormData(this);
                    var id = $('#id-field').val();
                    var url = id ? '/pedidos/' + id : '/pedidos';


                    // Añadir el token CSRF y el método HTTP manualmente para PUT/DELETE
                    if (id) {
                        formData.append('_method', 'PUT');
                    }
                    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                    $.ajax({
                        url: url,
                        method: 'POST', // Siempre POST para Laravel con _method para PUT
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: response.success,
                                icon: 'success',
                                confirmButtonClass: 'btn btn-primary w-xs me-2',
                                buttonsStyling: false,
                                showCloseButton: true
                            })
                            $('#showModal').modal('hide');
                            table.ajax.reload();
                        },
                        error: function (xhr) {
                            var errorMessage = '';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                                var errors = xhr.responseJSON.errors;
                                for (var key in errors) {
                                    errorMessage += errors[key][0] + '\n';
                                }
                            } else {
                                errorMessage = 'Ocurrió un error inesperado.';
                            }
                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
                                icon: 'error',
                                confirmButtonClass: 'btn btn-primary w-xs me-2',
                                buttonsStyling: false,
                                showCloseButton: true
                            })
                        }
                    });
                });

                $('#pedidos-table').on('click', '.edit-btn', function () {
                    var id = $(this).data('id');
                    $('#modalTitle').text('Editar Pedido');
                    $('#id-field').val(id);
                    $('#add-btn').hide();
                    $('#edit-btn').show();
                    $('#estado-field-wrapper').show(); // Mostrar estado en editar
                    $('#productos-container').empty(); // Limpiar productos existentes

                    $.ajax({
                        url: '/pedidos/' + id,
                        method: 'GET',
                        success: function (data) {
                            // Cargar cliente_id normalizado si existe
                            $('#cliente-id-field').val(data.cliente_id || '');
                            $('#cliente-nombre-field').val(data.cliente_nombre_completo || data.cliente_nombre);
                            $('#cliente-email-field').val(data.cliente_email_normalizado || data.cliente_email);
                            $('#cliente-telefono-field').val(data.cliente_telefono_normalizado || data.cliente_telefono);
                            // Detectar prefijo del documento (V-, J-, E-, G-)
                            var ciRif = data.ci_rif || '';
                            var prefix = ciRif.match(/^(V-|J-|E-|G-)/);
                            $('#ci-rif-prefix-field').val(prefix ? prefix[0] : 'V-');
                            $('#ci-rif-number-field').val(ciRif.replace(/^(V-|J-|E-|G-)/, ''));
                            $('#ci-rif-full-field').val(data.ci_rif);
                            $('#fecha-pedido-field').val(data.fecha_pedido || '');
                            $('#fecha-entrega-estimada-field').val(data.fecha_entrega_estimada || '');
                            $('#estado-field').val(data.estado);

                            // Cargar nuevos campos de pago y prioridad
                            $('#abono-field').val(data.abono);
                            $('#efectivo-pagado-field').prop('checked', data.efectivo_pagado);
                            $('#transferencia-pagado-field').prop('checked', data.transferencia_pagado);
                            $('#pago-movil-pagado-field').prop('checked', data.pago_movil_pagado);
                            $('#referencia-transferencia-field').val(data.referencia_transferencia || '');
                            $('#referencia-pago-movil-field').val(data.referencia_pago_movil || '');
                            $('#banco-id-field').val(data.banco_id).trigger('change'); // Usar .val() y trigger('change') para Select2
                            $('#prioridad-field').val(data.prioridad);

                            currentPedidoTotal = parseFloat(data.total); // Cargar el total del backend
                            $('#total-display-field').val(currentPedidoTotal.toFixed(2));

                            // Llenar productos del pedido
                            if (data.productos && data.productos.length > 0) {
                                data.productos.forEach(function (item) {
                                    // Transformar insumos a la forma esperada por addProductItem
                                    let insumosTransformados = [];
                                    if (item.insumos && item.insumos.length > 0) {
                                        insumosTransformados = item.insumos.map(function (insumo) {
                                            return {
                                                insumo_id: insumo.id,
                                                cantidad_estimada: insumo.pivot.cantidad_estimada
                                            };
                                        });
                                    }
                                    addProductItem(
                                        item.producto_id,
                                        item.cantidad,
                                        item.precio_unitario,
                                        item.descripcion,
                                        item.lleva_bordado,
                                        item.nombre_logo,
                                        item.color,
                                        item.talla,
                                        insumosTransformados,
                                        item.ubicacion_logo || '',
                                        item.cantidad_logo || 1
                                    );
                                });
                                calculateProductTotals();
                            } else {
                                addProductItem();
                                calculateProductTotals();
                            }

                            togglePaymentFieldsVisibility(); // Aplicar visibilidad basada en los datos cargados
                            updateRemaining(); // Calcular el restante

                            $('#showModal').modal('show');
                        },
                        error: function (xhr) {
                            Swal.fire({
                                title: 'Error!',
                                text: 'No se pudo cargar los datos del pedido.',
                                icon: 'error',
                                confirmButtonClass: 'btn btn-primary w-xs me-2',
                                buttonsStyling: false,
                                showCloseButton: true
                            })
                        }
                    });
                });

                $('#pedidos-table').on('click', '.view-btn', function () {
                    var id = $(this).data('id');
                    $.ajax({
                        url: '/pedidos/' + id,
                        method: 'GET',
                        success: function (data) {
                            $('#view-cliente-nombre').text(data.cliente_nombre);
                            $('#view-cliente-email').text(data.cliente_email || 'N/A');
                            $('#view-cliente-telefono').text(data.cliente_telefono || 'N/A');
                            $('#view-ci-rif').text(data.ci_rif || 'N/A');
                            $('#view-fecha-pedido').text(data.fecha_pedido);
                            $('#view-fecha-entrega-estimada').text(data.fecha_entrega_estimada || 'N/A');

                            // Mostrar estado con badge
                            let estadoBadgeClass = '';
                            switch (data.estado) {
                                case 'Pendiente':
                                    estadoBadgeClass = 'bg-info';
                                    break;
                                case 'Procesando':
                                    estadoBadgeClass = 'bg-warning';
                                    break;
                                case 'Completado':
                                    estadoBadgeClass = 'bg-success';
                                    break;
                                case 'Cancelado':
                                    estadoBadgeClass = 'bg-danger';
                                    break;
                                default:
                                    estadoBadgeClass = 'bg-secondary';
                            }
                            $('#view-estado').html(`<span class="badge ${estadoBadgeClass}">${data.estado}</span>`);

                            $('#view-total').text(parseFloat(data.total).toFixed(2));
                            $('#view-usuario-creador').text(data.user ? data.user.name : 'N/A');

                            // Cargar y mostrar nuevos campos de pago y prioridad
                            $('#view-abono').text(parseFloat(data.abono).toFixed(2));
                            let restante = parseFloat(data.total) - parseFloat(data.abono);
                            $('#view-restante').text(restante.toFixed(2));

                            let metodosPago = [];
                            if (data.efectivo_pagado) metodosPago.push('<span class="badge bg-success">Efectivo</span>');
                            if (data.transferencia_pagado) metodosPago.push('<span class="badge bg-primary">Transferencia</span>');
                            if (data.pago_movil_pagado) metodosPago.push('<span class="badge bg-info">Pago Móvil</span>');
                            $('#view-metodo-pago').html(metodosPago.join(' ') || 'N/A');

                            if (data.referencia_transferencia) {
                                $('#view-referencia-transferencia').text(data.referencia_transferencia);
                                $('#view-referencia-transferencia-container').show();
                            } else {
                                $('#view-referencia-transferencia').text('');
                                $('#view-referencia-transferencia-container').hide();
                            }

                            if (data.referencia_pago_movil) {
                                $('#view-referencia-pago-movil').text(data.referencia_pago_movil);
                                $('#view-referencia-pago-movil-container').show();
                            } else {
                                $('#view-referencia-pago-movil').text('');
                                $('#view-referencia-pago-movil-container').hide();
                            }

                            if (data.banco && data.banco.nombre) {
                                $('#view-banco').text(data.banco.nombre);
                                $('#view-banco-container').show();
                            } else {
                                $('#view-banco').text('');
                                $('#view-banco-container').hide();
                            }

                            // Mostrar prioridad con badge
                            let prioridadBadgeClass = '';
                            switch (data.prioridad) {
                                case 'Normal':
                                    prioridadBadgeClass = 'bg-primary';
                                    break;
                                case 'Alta':
                                    prioridadBadgeClass = 'bg-warning';
                                    break;
                                case 'Urgente':
                                    prioridadBadgeClass = 'bg-danger';
                                    break;
                                default:
                                    prioridadBadgeClass = 'bg-secondary';
                            }
                            $('#view-prioridad').html(`<span class="badge ${prioridadBadgeClass}">${data.prioridad || 'N/A'}</span>`);

                            // Llenar productos del pedido en la vista
                            var productosBody = $('#view-productos-container');
                            productosBody.empty();
                            if (data.productos && data.productos.length > 0) {
                                data.productos.forEach(function (item, index) {
                                    var subtotal = item.cantidad * item.precio_unitario;
                                    productosBody.append(`
                                                                                                                                    <div class="card border-0 shadow-sm mb-3" style="border-left: 4px solid #00d9a5 !important;">
                                                                                                                                        <div class="card-body p-3">
                                                                                                                                            <!-- Header del Producto -->
                                                                                                                                            <div class="d-flex align-items-center mb-3">
                                                                                                                                                <div class="rounded-circle me-3 d-flex align-items-center justify-content-center"
                                                                                                                                                    style="width: 45px; height: 45px; background: #1e3c72;">
                                                                                                                                                    <i class="ri-shopping-bag-line text-white fs-5"></i>
                                                                                                                                                </div>
                                                                                                                                                <div>
                                                                                                                                                    <h6 class="mb-0 fw-bold" style="color: #1e3c72;">${item.producto.codigo || ''} - ${item.producto.tipo_producto ? item.producto.tipo_producto.nombre : 'Sin tipo'} ${item.producto.modelo}</h6>
                                                                                                                                                    <small class="text-muted">Producto #${index + 1}</small>
                                                                                                                                                </div>
                                                                                                                                                <div class="ms-auto">
                                                                                                                                                    <span class="badge" style="background: #00d9a5; font-size: 0.9rem;">
                                                                                                                                                        $${subtotal.toFixed(2)}
                                                                                                                                                    </span>
                                                                                                                                                </div>
                                                                                                                                            </div>

                                                                                                                                            <!-- Detalles del Producto -->
                                                                                                                                            <div class="row g-2 mb-3">
                                                                                                                                                <div class="col-6 col-md-3">
                                                                                                                                                    <div class="d-flex align-items-center">
                                                                                                                                                        <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                                                                                                                            style="width: 28px; height: 28px; background: rgba(46, 204, 113, 0.15);">
                                                                                                                                                            <i class="ri-stack-line" style="color: #2ecc71; font-size: 0.85rem;"></i>
                                                                                                                                                        </div>
                                                                                                                                                        <div>
                                                                                                                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Cantidad</small>
                                                                                                                                                            <span class="fw-semibold" style="font-size: 0.85rem;">${item.cantidad}</span>
                                                                                                                                                        </div>
                                                                                                                                                    </div>
                                                                                                                                                </div>
                                                                                                                                                <div class="col-6 col-md-3">
                                                                                                                                                    <div class="d-flex align-items-center">
                                                                                                                                                        <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                                                                                                                            style="width: 28px; height: 28px; background: rgba(0, 217, 165, 0.15);">
                                                                                                                                                            <i class="ri-palette-line" style="color: #00d9a5; font-size: 0.85rem;"></i>
                                                                                                                                                        </div>
                                                                                                                                                        <div>
                                                                                                                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Color</small>
                                                                                                                                                            <span class="fw-semibold" style="font-size: 0.85rem;">${item.color || 'N/A'}</span>
                                                                                                                                                        </div>
                                                                                                                                                    </div>
                                                                                                                                                </div>
                                                                                                                                                <div class="col-6 col-md-3">
                                                                                                                                                    <div class="d-flex align-items-center">
                                                                                                                                                        <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                                                                                                                            style="width: 28px; height: 28px; background: rgba(30, 60, 114, 0.15);">
                                                                                                                                                            <i class="ri-t-shirt-line" style="color: #1e3c72; font-size: 0.85rem;"></i>
                                                                                                                                                        </div>
                                                                                                                                                        <div>
                                                                                                                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Talla</small>
                                                                                                                                                            <span class="fw-semibold" style="font-size: 0.85rem;">${item.talla || 'N/A'}</span>
                                                                                                                                                        </div>
                                                                                                                                                    </div>
                                                                                                                                                </div>
                                                                                                                                                <div class="col-6 col-md-3">
                                                                                                                                                    <div class="d-flex align-items-center">
                                                                                                                                                        <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                                                                                                                            style="width: 28px; height: 28px; background: rgba(46, 204, 113, 0.15);">
                                                                                                                                                            <i class="ri-money-dollar-circle-line" style="color: #2ecc71; font-size: 0.85rem;"></i>
                                                                                                                                                        </div>
                                                                                                                                                        <div>
                                                                                                                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">P. Unitario</small>
                                                                                                                                                            <span class="fw-semibold" style="font-size: 0.85rem;">$${parseFloat(item.precio_unitario).toFixed(2)}</span>
                                                                                                                                                        </div>
                                                                                                                                                    </div>
                                                                                                                                                </div>
                                                                                                                                            </div>

                                                                                                                                            <!-- Bordado/Logo si aplica -->
                                                                                                                                            ${item.lleva_bordado ? `
                                                                                                                                            <div class="rounded p-2 mb-3" style="background: rgba(0, 217, 165, 0.08);">
                                                                                                                                                <div class="d-flex align-items-center mb-2">
                                                                                                                                                    <i class="ri-scissors-cut-line me-2" style="color: #00d9a5;"></i>
                                                                                                                                                    <span class="fw-semibold" style="color: #00d9a5; font-size: 0.85rem;">Bordado / Logo</span>
                                                                                                                                                </div>
                                                                                                                                                <div class="row g-2">
                                                                                                                                                    <div class="col-6">
                                                                                                                                                        <small class="text-muted">Logo:</small>
                                                                                                                                                        <span class="fw-semibold ms-1" style="font-size: 0.85rem;">${item.nombre_logo || 'N/A'}</span>
                                                                                                                                                    </div>
                                                                                                                                                    <div class="col-6">
                                                                                                                                                        <small class="text-muted">Ubicación:</small>
                                                                                                                                                        <span class="fw-semibold ms-1" style="font-size: 0.85rem;">${item.ubicacion_logo || 'N/A'}</span>
                                                                                                                                                    </div>
                                                                                                                                                    <div class="col-12">
                                                                                                                                                        <small class="text-muted">Cantidad:</small>
                                                                                                                                                        <span class="badge bg-primary ms-1">${item.cantidad_logo || '0'}</span>
                                                                                                                                                    </div>
                                                                                                                                                </div>
                                                                                                                                            </div>
                                                                                                                                            ` : ''}

                                                                                                                                            <!-- Descripción -->
                                                                                                                                            ${item.descripcion ? `
                                                                                                                                            <div class="rounded p-2 mb-3" style="background: rgba(30, 60, 114, 0.05);">
                                                                                                                                                <div class="d-flex align-items-start">
                                                                                                                                                    <i class="ri-file-text-line me-2 mt-1" style="color: #1e3c72;"></i>
                                                                                                                                                    <div>
                                                                                                                                                        <small class="text-muted d-block">Descripción</small>
                                                                                                                                                        <span style="font-size: 0.85rem;">${item.descripcion}</span>
                                                                                                                                                    </div>
                                                                                                                                                </div>
                                                                                                                                            </div>
                                                                                                                                            ` : ''}

                                                                                                                                            <!-- Insumos -->
                                                                                                                                            ${item.insumos && item.insumos.length > 0 ? `
                                                                                                                                            <div class="rounded p-2" style="background: rgba(46, 204, 113, 0.08);">
                                                                                                                                                <div class="d-flex align-items-center mb-2">
                                                                                                                                                    <i class="ri-tools-line me-2" style="color: #2ecc71;"></i>
                                                                                                                                                    <span class="fw-semibold" style="color: #2ecc71; font-size: 0.85rem;">Insumos Requeridos</span>
                                                                                                                                                </div>
                                                                                                                                                <div class="d-flex flex-wrap gap-2">
                                                                                                                                                    ${item.insumos.map(insumo => `
                                                                                                                                                        <span class="badge" style="background: rgba(46, 204, 113, 0.2); color: #1e3c72;">
                                                                                                                                                            ${insumo.nombre}
                                                                                                                                                            <span class="badge bg-primary ms-1">${parseFloat(insumo.pivot.cantidad_estimada).toFixed(2)} ${insumo.unidad_medida}</span>
                                                                                                                                                        </span>
                                                                                                                                                    `).join('')}
                                                                                                                                                </div>
                                                                                                                                            </div>
                                                                                                                                            ` : ''}
                                                                                                                                        </div>
                                                                                                                                    </div>
                                                                                                                                `);
                                });
                            } else {
                                productosBody.append('<p class="text-muted text-center py-4"><i class="ri-shopping-bag-line fs-1 d-block mb-2"></i>No hay productos en este pedido.</p>');
                            }

                            $('#viewModal').modal('show');
                        },
                        error: function (xhr) {
                            Swal.fire({
                                title: 'Error!',
                                text: 'No se pudo cargar los detalles del pedido.',
                                icon: 'error',
                                confirmButtonClass: 'btn btn-primary w-xs me-2',
                                buttonsStyling: false,
                                showCloseButton: true
                            })
                        }
                    });
                });

                $('#pedidos-table').on('click', '.remove-btn', function () {
                    var id = $(this).data('id');
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "¡No podrás revertir esto!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonClass: 'btn btn-primary w-xs me-2',
                        cancelButtonClass: 'btn btn-danger w-xs',
                        confirmButtonText: 'Sí, eliminarlo!',
                        cancelButtonText: 'Cancelar',
                        buttonsStyling: false,
                        showCloseButton: true
                    }).then(function (result) {
                        if (result.value) {
                            $.ajax({
                                url: '/pedidos/' + id,
                                method: 'POST', // Usamos POST y _method para simular DELETE
                                data: {
                                    _method: 'DELETE',
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function (response) {
                                    Swal.fire({
                                        title: '¡Eliminado!',
                                        text: response.success,
                                        icon: 'success',
                                        confirmButtonClass: 'btn btn-primary w-xs me-2',
                                        buttonsStyling: false,
                                        showCloseButton: true
                                    })
                                    table.ajax.reload();
                                },
                                error: function (xhr) {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: 'No se pudo eliminar el pedido.',
                                        icon: 'error',
                                        confirmButtonClass: 'btn btn-primary w-xs me-2',
                                        buttonsStyling: false,
                                        showCloseButton: true
                                    })
                                }
                            });
                        }
                    });
                });

                // Función para inicializar Select2
                function initializeSelect2(selector) {
                    $(selector).select2({
                        theme: 'bootstrap-5',
                        placeholder: 'Seleccione insumo...',
                        width: '100%',
                        dropdownParent: $('#showModal .modal-body')
                    });
                }

                // Reset form on modal close
                $('#showModal').on('hidden.bs.modal', function () {
                    $('#pedidoForm')[0].reset();
                    $('#id-field').val('');
                    $('#modalTitle').text('Agregar Pedido');
                    $('#estado-field-wrapper').hide(); // Ocultar estado en agregar
                    $('#productos-container').empty(); // Limpiar productos existentes
                    addProductItem(); // Añadir un item de producto vacío

                    // Resetear y ocultar nuevos campos de pago/prioridad
                    $('#abono-field').val(0);
                    $('#efectivo-pagado-field').prop('checked', false);
                    $('#transferencia-pagado-field').prop('checked', false);
                    $('#pago-movil-pagado-field').prop('checked', false);
                    $('#referencia-transferencia-field').val('');
                    $('#referencia-pago-movil-field').val('');
                    $('#banco-id-field').val('');
                    $('#prioridad-field').val('Normal'); // Valor por defecto
                    currentPedidoTotal = 0; // Resetear total
                    calculateProductTotals(); // Recalcular el total inicial (que será 0)
                    togglePaymentFieldsVisibility(); // Ocultar referencias y banco

                    // Reiniciar los campos del CI/RIF a sus valores por defecto al resetear el formulario
                    $('#ci-rif-prefix-field').val('V-');
                    $('#ci-rif-number-field').val('');
                    updateRifFullField();
                });

                // --- AUTOCOMPLETADO DE CLIENTE ---
                let clienteSeleccionado = null;
                let autocompleteTimeout = null;

                $('#cliente-nombre-field').on('input', function () {
                    const query = $(this).val();
                    clearTimeout(autocompleteTimeout);
                    if (query.length < 2) {
                        $('#cliente-autocomplete-list').empty().hide();
                        return;
                    }
                    autocompleteTimeout = setTimeout(function () {
                        $.ajax({
                            url: '/clientes-search',
                            data: { q: query },
                            success: function (clientes) {
                                let html = '';
                                if (clientes.length > 0) {
                                    clientes.forEach(function (cliente) {
                                        html += `<button type="button" class="list-group-item list-group-item-action cliente-autocomplete-item" data-id="${cliente.id}" data-nombre="${cliente.nombre}" data-email="${cliente.email || ''}" data-telefono="${cliente.telefono || ''}" data-documento="${cliente.documento || ''}">${cliente.nombre} <small class='text-muted'>${cliente.documento || 'Sin documento'} - ${cliente.email || 'Sin email'}</small></button>`;
                                    });
                                } else {
                                    html = '<div class="list-group-item disabled">No se encontraron clientes</div>';
                                }
                                $('#cliente-autocomplete-list').html(html).show();
                            }
                        });
                    }, 300);
                });

                // Selección de cliente de la lista
                $(document).on('click', '.cliente-autocomplete-item', function () {
                    const $this = $(this);
                    const clienteId = $this.data('id'); // ID del cliente seleccionado
                    const nombre = $this.data('nombre') || '';
                    const email = $this.data('email') || '';
                    const telefono = $this.data('telefono') || '';
                    const documento = $this.data('documento');

                    // Llenar cliente_id (FK normalizada)
                    $('#cliente-id-field').val(clienteId);

                    // Llenar campos básicos
                    $('#cliente-nombre-field').val(nombre);
                    $('#cliente-email-field').val(email);
                    $('#cliente-telefono-field').val(telefono);

                    // Procesar documento - Convertir siempre a string primero
                    let prefix = 'V-';
                    let number = '';

                    // Convertir documento a string de forma segura
                    let docString = '';
                    if (documento !== undefined && documento !== null && documento !== '') {
                        docString = String(documento).trim();
                    }

                    if (docString.length > 0) {
                        // Si el documento ya tiene formato V- o J-
                        if (docString.startsWith('V-') || docString.startsWith('J-') || docString.startsWith('E-') || docString.startsWith('G-')) {
                            prefix = docString.substring(0, 2);
                            number = docString.substring(2);
                        } else {
                            // Si no tiene prefijo, determinar automáticamente
                            number = docString;
                            // Lógica para determinar si es V- o J-
                            if (docString.length >= 8 && /^[2-9]/.test(docString)) {
                                prefix = 'J-';
                            } else {
                                prefix = 'V-';
                            }
                        }
                    }

                    // Establecer valores en los campos
                    $('#ci-rif-prefix-field').val(prefix);
                    $('#ci-rif-number-field').val(number);
                    updateRifFullField();

                    $('#cliente-autocomplete-list').empty().hide();
                    clienteSeleccionado = true;
                });

                // Ocultar lista al hacer click fuera
                $(document).on('click', function (e) {
                    if (!$(e.target).closest('#cliente-nombre-field, #cliente-autocomplete-list').length) {
                        $('#cliente-autocomplete-list').empty().hide();
                    }
                });

                // --- MODAL AGREGAR CLIENTE DESDE PEDIDO ---
                $('#open-add-cliente-modal').on('click', function () {
                    $('#clienteFormPedido')[0].reset();
                    $('#id-field-cliente').val('');
                    $('#modalClienteTitle').text('Agregar Cliente');
                    $('#add-btn-cliente').show();
                    $('#edit-btn-cliente').hide();
                    $('#modalAddCliente').modal('show');
                });

                // Guardar nuevo cliente desde el modal
                $('#add-btn-cliente').on('click', function () {
                    $('#clienteFormPedido').submit();
                });
                $('#clienteFormPedido').on('submit', function (e) {
                    e.preventDefault();
                    var formData = $(this).serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: '/clientes',
                        type: 'POST',
                        data: formData,
                        success: function (response) {
                            Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.message, showConfirmButton: false, timer: 2000 });
                            $('#modalAddCliente').modal('hide');

                            // Capturar el cliente_id del cliente recién creado
                            if (response.cliente_id) {
                                $('#cliente-id-field').val(response.cliente_id);
                            }

                            // Rellenar los campos del pedido con el nuevo cliente
                            const nombre = $('#nombre-field-cliente').val();
                            const email = $('#email-field-cliente').val();
                            const telefono = $('#telefono-field-cliente').val();
                            const documento = $('#documento-field-cliente').val();

                            $('#cliente-nombre-field').val(nombre);
                            $('#cliente-email-field').val(email || '');
                            $('#cliente-telefono-field').val(telefono || '');

                            // Separar prefijo y número del documento
                            let prefix = 'V-'; // Valor por defecto
                            let number = '';

                            if (documento) {
                                // Si el documento ya tiene formato V- o J-
                                if (documento.startsWith('V-') || documento.startsWith('J-')) {
                                    prefix = documento.substring(0, 2);
                                    number = documento.substring(2);
                                } else {
                                    // Si el documento no tiene prefijo, asumimos que es solo el número
                                    number = documento;
                                    // Determinar el prefijo basado en la longitud o primer dígito
                                    if (documento.length >= 8 && ['2', '3', '4', '5', '6', '7', '8', '9'].includes(documento.charAt(0))) {
                                        prefix = 'J-';
                                    } else {
                                        prefix = 'V-';
                                    }
                                }
                            }

                            $('#ci-rif-prefix-field').val(prefix);
                            $('#ci-rif-number-field').val(number);
                            $('#ci-rif-full-field').val(prefix + number);
                            $('#cliente-autocomplete-list').empty().hide();
                        },
                        error: function (xhr) {
                            let errorMsg = 'Ocurrió un error al agregar el cliente.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                                errorMsg = Object.values(xhr.responseJSON.errors).join('\n');
                            }
                            Swal.fire({ icon: 'error', title: 'Error', text: errorMsg });
                        }
                    });
                });

                $(`#insumos-container-${currentProductItemIndex} .insumo-select`).last().on('change', function () {
                    const selected = $(this).find('option:selected');
                    const stock = parseFloat(selected.data('stock'));
                    const stockMin = parseFloat(selected.data('stock-minimo'));
                    const unidad = selected.data('unidad') || '';
                    let color = stock <= stockMin ? 'red' : 'green';
                    let text = '';
                    if (!isNaN(stock)) {
                        text = `<span style="color:${color};font-weight:bold;">Stock actual: ${stock.toFixed(2)} ${unidad}</span>`;
                    }
                    const infoId = $(this).data('insumo-index');
                    $(`#stock-info-${infoId}`).html(text);
                }).trigger('change');
            });

            // ================================================
            // MODAL DE SELECCIÓN DE PRODUCTOS
            // ================================================
            var currentProductIndex = null;
            var productosModal = null;

            $(document).ready(function () {
                productosModal = new bootstrap.Modal(document.getElementById('productosModal'));
                cargarTiposEnFiltro();

                $('#buscarProductoModal').on('keyup', function () {
                    renderizarProductosModal($(this).val(), $('#filtroTipoProducto').val());
                });
                $('#filtroTipoProducto').on('change', function () {
                    renderizarProductosModal($('#buscarProductoModal').val(), $(this).val());
                });
            });

            function cargarTiposEnFiltro() {
                var tipos = [];
                if (typeof products !== 'undefined') {
                    products.forEach(function (p) {
                        if (p.tipo_producto && !tipos.find(t => t.id === p.tipo_producto.id)) {
                            tipos.push(p.tipo_producto);
                        }
                    });
                }
                var select = $('#filtroTipoProducto');
                select.find('option:not(:first)').remove();
                tipos.forEach(function (tipo) {
                    select.append('<option value="' + tipo.id + '">' + tipo.nombre + '</option>');
                });
            }

            function renderizarProductosModal(filtro, tipoId) {
                filtro = filtro || '';
                tipoId = tipoId || '';
                var tbody = $('#productosModalBody');
                tbody.empty();

                if (typeof products === 'undefined') return;

                var productosFiltrados = products.filter(function (p) {
                    var matchFiltro = true;
                    var matchTipo = true;
                    if (filtro) {
                        var busqueda = filtro.toLowerCase();
                        var codigo = (p.codigo || '').toLowerCase();
                        var modelo = (p.modelo || '').toLowerCase();
                        var tipo = p.tipo_producto ? p.tipo_producto.nombre.toLowerCase() : '';
                        matchFiltro = codigo.includes(busqueda) || modelo.includes(busqueda) || tipo.includes(busqueda);
                    }
                    if (tipoId) {
                        matchTipo = p.tipo_producto && p.tipo_producto.id == tipoId;
                    }
                    return matchFiltro && matchTipo;
                });

                if (productosFiltrados.length === 0) {
                    tbody.append('<tr><td colspan="6" class="text-center text-muted">No se encontraron productos</td></tr>');
                    return;
                }

                productosFiltrados.forEach(function (p) {
                    var tipoNombre = p.tipo_producto ? p.tipo_producto.nombre : 'Sin tipo';
                    var imgHtml = p.imagen ? '<img src="' + p.imagen + '" class="producto-img-thumb" alt="">' : '<span class="text-muted">-</span>';
                    var row = '<tr data-producto-id="' + p.id + '" data-precio="' + p.precio_base + '">' +
                        '<td>' + imgHtml + '</td>' +
                        '<td><span class="badge bg-dark">' + (p.codigo || '-') + '</span></td>' +
                        '<td><span class="badge bg-primary">' + tipoNombre + '</span></td>' +
                        '<td>' + p.modelo + '</td>' +
                        '<td class="text-success fw-bold">$ ' + parseFloat(p.precio_base).toFixed(2) + '</td>' +
                        '<td><button type="button" class="btn btn-sm btn-success select-producto-btn" data-id="' + p.id + '"><i class="ri-check-line"></i></button></td>' +
                        '</tr>';
                    tbody.append(row);
                });
            }

            $(document).on('click', '.producto-selector-btn', function () {
                currentProductIndex = $(this).data('product-index');
                $('#buscarProductoModal').val('');
                $('#filtroTipoProducto').val('');
                renderizarProductosModal('', '');
                if (productosModal) productosModal.show();
            });

            $(document).on('click', '.select-producto-btn', function () {
                seleccionarProducto($(this).data('id'));
            });

            $(document).on('dblclick', '#productosModalTable tbody tr', function () {
                var productoId = $(this).data('producto-id');
                if (productoId) seleccionarProducto(productoId);
            });

            function seleccionarProducto(productoId) {
                if (typeof products === 'undefined') return;
                var producto = products.find(function (p) { return p.id == productoId; });
                if (!producto || currentProductIndex === null) return;

                var card = $('.card[data-product-index="' + currentProductIndex + '"]');
                var tipoNombre = producto.tipo_producto ? producto.tipo_producto.nombre : 'Sin tipo';
                var displayName = (producto.codigo || '') + ' - ' + tipoNombre + ' ' + producto.modelo;

                card.find('.producto-id-input').val(productoId);
                card.find('.producto-text').text(displayName).removeClass('placeholder-text');
                card.find('.precio-producto-span').text('$ ' + parseFloat(producto.precio_base).toFixed(2));
                card.find('.precio-unitario-input').val(producto.precio_base);

                if (productosModal) productosModal.hide();
                currentProductIndex = null;
            }
        </script>

        <script>
            // === CARGA DE PEDIDO DESDE COTIZACIÓN ===
            $(document).ready(function () {
                // Verificar si venimos desde una cotización
                var urlParams = new URLSearchParams(window.location.search);
                var cotizacionId = urlParams.get('desde_cotizacion');

                if (cotizacionId) {
                    // Limpiar el parámetro de la URL sin recargar
                    window.history.replaceState({}, document.title, window.location.pathname);

                    // Cargar datos de la cotización
                    $.ajax({
                        url: '/cotizaciones/' + cotizacionId + '/datos-para-pedido',
                        method: 'GET',
                        success: function (data) {
                            // Mostrar mensaje de carga exitosa
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'info',
                                title: 'Cargando datos de cotización #' + cotizacionId,
                                showConfirmButton: false,
                                timer: 2000
                            });

                            // Almacenar ID de cotización para marcarla como convertida después
                            $('#showModal').data('cotizacion-id', cotizacionId);

                            // Agregar campo hidden para enviar cotizacion_id con el formulario
                            // Primero remover si ya existe para evitar duplicados
                            $('input[name="cotizacion_id"]').remove();
                            // Agregarlo al formulario principal (buscar el form dentro del modal)
                            var $form = $('#showModal form').first();
                            if ($form.length === 0) {
                                // Si no hay form, buscarlo por ID común
                                $form = $('form[id*="pedido"], form[id*="Pedido"]').first();
                            }
                            if ($form.length > 0) {
                                $form.prepend('<input type="hidden" name="cotizacion_id" value="' + cotizacionId + '">');
                            }

                            // Llenar datos del cliente
                            if (data.cliente) {
                                $('#cliente-id-field').val(data.cliente.id);
                                $('#cliente-nombre-field').val(data.cliente.nombre || '');
                                $('#cliente-apellido-field').val(data.cliente.apellido || '');
                                $('#cliente-email-field').val(data.cliente.email || '');
                                $('#cliente-telefono-field').val(data.cliente.telefono || '');

                                // Procesar documento
                                if (data.cliente.documento) {
                                    var doc = data.cliente.documento;
                                    var prefix = doc.substring(0, 2);
                                    var number = doc.substring(2);
                                    $('#ci-rif-prefix-field').val(prefix);
                                    $('#ci-rif-number-field').val(number);
                                    $('#ci-rif-full-field').val(doc);
                                }
                            }

                            // Limpiar contenedor de productos y resetear índice
                            $('#productos-container').empty();
                            window.productItemIndex = 0;

                            // Agregar cada producto de la cotización usando addProductItem
                            if (data.productos && data.productos.length > 0) {
                                data.productos.forEach(function (prod) {
                                    // Parámetros: productoId, cantidad, precioUnitario, descripcion, 
                                    //             llevaBordado, nombreLogo, color, talla, productInsumos, 
                                    //             ubicacionLogo, cantidadLogo
                                    window.addProductItem(
                                        prod.producto_id,
                                        prod.cantidad,
                                        prod.precio_unitario,
                                        prod.descripcion || '',
                                        prod.lleva_bordado || false,
                                        prod.nombre_logo || '',
                                        '', // color (cotización no tiene color, se puede agregar después)
                                        prod.talla || '',
                                        [], // insumos vacíos, se pueden agregar después
                                        prod.ubicacion_logo || '',
                                        prod.cantidad_logo || 1
                                    );
                                });
                            }

                            // Recalcular totales
                            setTimeout(function () {
                                if (typeof calculateTotals === 'function') {
                                    calculateTotals();
                                }
                            }, 500);

                            // Abrir el modal de creación
                            setTimeout(function () {
                                $('#showModal').modal('show');
                                $('#modalTitle').text('Nuevo Pedido desde Cotización #' + cotizacionId);
                            }, 600);
                        },
                        error: function (xhr) {
                            var errorMsg = xhr.responseJSON?.error || 'No se pudo cargar la cotización.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMsg
                            });
                        }
                    });
                }
            });
        </script>
@endpush