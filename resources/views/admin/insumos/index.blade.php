@extends('admin.layouts.app')

@push('styles')
    <!-- Sweet Alert css-->
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" rel="stylesheet"
        type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    {{-- Grid responsivo para filtros: 1 col mobile → 3 cols desktop --}}
    <style>
        @media (min-width: 768px) {
            .navy-filter-grid {
                grid-template-columns: repeat(3, 1fr) !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Gestión de Insumos</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Gestión General</a></li>
                        <li class="breadcrumb-item active">Insumos</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-maestros">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Listado de Insumos</h5>
                        <div class="flex-shrink-0 d-flex align-items-center gap-3">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn"
                                    data-bs-target="#showModal">
                                    <i class="ri-add-line align-bottom me-1"></i> Agregar Insumo
                                </button>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#pdfExportModal">
                                    <i class="ri-file-pdf-fill align-bottom me-1"></i> Exportar PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {{-- ============================================================
                         FILTROS — Patrón Maestro S-07 (Colapsable)
                         CSS genérico en custom.css: .navy-filter-*
                         ============================================================ --}}
                    <div class="advanced-filters-wrapper navy-theme" id="advanced-filters">
                        {{-- Header unificado: búsqueda global + trigger de filtros --}}
                        <div class="navy-filter-header is-collapsed">
                            {{-- Búsqueda global (siempre visible) --}}
                            <div class="navy-header-search">
                                <i class="ri-search-line"></i>
                                <input type="text" id="custom-search-input"
                                    class="navy-search-input"
                                    placeholder="Buscar insumo..."
                                    autocomplete="off">
                            </div>
                            {{-- Divisor vertical --}}
                            <div class="navy-header-divider"></div>
                            {{-- Trigger del collapse de filtros --}}
                            <button class="navy-filter-btn collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#filters-collapse-body"
                                aria-expanded="false" aria-controls="filters-collapse-body">
                                <i class="ri-filter-3-line"></i>
                                <span class="position-relative">
                                    Filtros
                                    <span class="d-none position-absolute" id="filter-dot-indicator"
                                        style="top: -3px; right: -10px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%; border: 2px solid #1b2e4b; display: inline-block;"></span>
                                </span>
                                <span class="navy-filter-badge d-none" id="active-filter-count"></span>
                                <i class="ri-arrow-down-s-line navy-filter-chevron"></i>
                            </button>
                        </div>
                        {{-- Body: colapsable, oculto por defecto --}}
                        <div class="collapse" id="filters-collapse-body">
                            <div class="navy-filter-body">
                                <div style="display: grid; grid-template-columns: 1fr; gap: 0.75rem;" class="navy-filter-grid">
                                    {{-- Filtro 1: Tipo de Insumo --}}
                                    <div>
                                        <label class="navy-filter-label" for="filter-tipo">
                                            <i class="ri-price-tag-3-line"></i> Tipo de Insumo
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-tipo">
                                            <option value="">Todos</option>
                                            <option value="Tela">Tela</option>
                                            <option value="Hilo">Hilo</option>
                                            <option value="Boton">Botón</option>
                                            <option value="Cierre">Cierre</option>
                                            <option value="Etiqueta">Etiqueta</option>
                                        </select>
                                    </div>
                                    {{-- Filtro 2: Disponibilidad --}}
                                    <div>
                                        <label class="navy-filter-label" for="filter-stock">
                                            <i class="ri-store-3-line"></i> Disponibilidad
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-stock">
                                            <option value="">Todos</option>
                                            <option value="con_stock">Con Stock</option>
                                            <option value="agotado">Agotados</option>
                                        </select>
                                    </div>
                                    {{-- Filtro 3: Ordenar por --}}
                                    <div>
                                        <label class="navy-filter-label" for="filter-orden">
                                            <i class="ri-sort-asc"></i> Ordenar por
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-orden">
                                            <option value="recientes">Más recientes primero</option>
                                            <option value="mayor_costo">Mayor Costo</option>
                                            <option value="menor_costo">Menor Costo</option>
                                            <option value="mayor_stock">Mayor Stock</option>
                                            <option value="menor_stock">Menor Stock</option>
                                        </select>
                                    </div>
                                </div>
                                {{-- Botón limpiar: estilo ghost con icono de escoba --}}
                                <div class="d-flex justify-content-end mt-2">
                                    <button type="button" class="btn btn-sm" id="btn-clear-filters"
                                        style="background: transparent; color: #8a9bb5; border: none; font-size: 0.8rem; transition: all 0.2s ease;"
                                        onmouseover="this.style.color='#ef4444'; this.style.textDecoration='underline';"
                                        onmouseout="this.style.color='#8a9bb5'; this.style.textDecoration='none';">
                                        <i class='bx bx-broom' style="margin-right: 4px; font-size: 1rem; vertical-align: middle;"></i>Limpiar filtros
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- FIN FILTROS --}}

                    <div class="table-responsive">
                        <table id="insumos-table" class="table table-bordered table-striped table-sm align-middle table-operativa table-maestro">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Código</th>
                                    <th>Tipo</th>
                                    <th>Mín.</th>
                                    <th>Actual</th>
                                    <th>Máx.</th>
                                    <th>Costo Unit.</th>
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

    <!-- Modal para ver detalles -->
    <div class="modal fade atlantico-modal" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title"><i class="ri-archive-line me-2"></i>Detalles del Insumo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">

                        {{-- Columna izquierda: Información General --}}
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header border-0" style="background: rgba(30, 60, 114, 0.1);">
                                    <h6 class="mb-0" style="color: #1e3c72;">
                                        <i class="ri-information-line me-2"></i>Información General
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                                    style="width:32px;height:32px;background:rgba(30,60,114,0.1);">
                                                    <i class="ri-box-3-line" style="color:#1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Nombre</small>
                                                    <span class="fw-semibold" id="view-nombre">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                                    style="width:32px;height:32px;background:rgba(30,60,114,0.1);">
                                                    <i class="ri-price-tag-3-line" style="color:#1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Tipo</small>
                                                    <span id="view-tipo">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                                    style="width:32px;height:32px;background:rgba(30,60,114,0.1);">
                                                    <i class="ri-scales-line" style="color:#1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Unidad de Medida</small>
                                                    <span class="fw-semibold" id="view-unidad-medida">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Columna derecha: Inventario y Costo --}}
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header border-0" style="background: rgba(30, 60, 114, 0.1);">
                                    <h6 class="mb-0" style="color: #1e3c72;">
                                        <i class="ri-bar-chart-grouped-line me-2"></i>Inventario y Costo
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-4">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                                    style="width:32px;height:32px;background:rgba(30,60,114,0.1);">
                                                    <i class="ri-arrow-down-line" style="color:#1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Existencia Mínima</small>
                                                    <span class="fw-semibold" id="view-stock-minimo">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                                    style="width:32px;height:32px;background:rgba(30,60,114,0.1);">
                                                    <i class="ri-store-3-line" style="color:#1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Existencia Actual</small>
                                                    <span class="fw-semibold" id="view-stock-actual">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                                    style="width:32px;height:32px;background:rgba(30,60,114,0.1);">
                                                    <i class="ri-arrow-up-line" style="color:#1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Existencia Máxima</small>
                                                    <span class="fw-semibold" id="view-stock-maximo">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                                    style="width:32px;height:32px;background:rgba(30,60,114,0.1);">
                                                    <i class="ri-money-dollar-circle-line" style="color:#1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Costo Unitario</small>
                                                    <span class="fw-semibold" id="view-costo-unitario">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tarjeta de Registro --}}
                            <div class="card border-0 shadow-sm">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle me-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width:28px;height:28px;background:rgba(30,60,114,0.08);">
                                            <i class="ri-calendar-line" style="color:#1e3c72;font-size:12px;"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block" style="font-size:11px;">Fecha de Registro</small>
                                            <small class="fw-semibold" id="view-created">-</small>
                                        </div>
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
    <div class="modal fade atlantico-modal" id="showModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="modalTitle">Agregar Insumo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="insumoForm" novalidate>
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="id-field" />

                        <div class="modal-form-section">
                            <div class="modal-form-section-title"><i class="ri-box-3-line"></i>Datos del Insumo</div>

                            <div class="row mb-0">
                                <div class="col-md-5">
                                    <x-forms.input name="nombre" label="Nombre" required />
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="codigo-field" class="form-label">
                                            Código
                                            <i class="ri-information-line text-muted" data-bs-toggle="tooltip"
                                               title="2-8 caracteres (mayúsculas/números). No se puede modificar después de guardar. Recomendado para Telas: forma parte del código del producto."
                                               style="cursor: help;"></i>
                                        </label>
                                        <input type="text" id="codigo-field" name="codigo" class="form-control text-uppercase"
                                            maxlength="8" placeholder="Ej: OXF"
                                            style="font-family: monospace;" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <x-forms.select name="tipo" label="Tipo" required
                                        :options="['Tela' => 'Tela', 'Hilo' => 'Hilo', 'Boton' => 'Botón', 'Cierre' => 'Cierre', 'Etiqueta' => 'Etiqueta']" />
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-12">
                                    <x-forms.select name="unidad_medida" label="Unidad de Medida" required
                                        :options="['Metro' => 'Metro (m)', 'Kg' => 'Kilogramo (Kg)', 'Gramo' => 'Gramo (g)', 'Unidad' => 'Unidad (Und)', 'Rollo' => 'Rollo', 'Cono' => 'Cono', 'Docena' => 'Docena']" />
                                </div>
                            </div>
                        </div>

                        <div class="modal-form-section mb-0">
                            <div class="modal-form-section-title"><i class="ri-bar-chart-grouped-line"></i>Control de Inventario y Costo</div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is-inventoriable-switch"
                                        name="is_inventoriable" value="1" checked>
                                    <label class="form-check-label" for="is-inventoriable-switch">
                                        Inventariable <small class="text-muted">(gestionar stock)</small>
                                    </label>
                                </div>
                            </div>

                            <div id="stock-fields-wrapper">
                                <div class="row mb-0">
                                    <div class="col-md-4">
                                        <x-forms.input name="stock_minimo" label="Existencia Mínima" type="number" step="0.01" min="0" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-forms.input name="stock_actual" label="Existencia Actual" type="number" step="0.01" min="0" value="0" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-forms.input name="stock_maximo" label="Existencia Máxima" type="number" step="0.01" min="0" />
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6">
                                    <x-forms.input name="costo_unitario" label="Costo Unitario" type="number" step="0.01" min="0"
                                        required />
                                </div>
                                <div class="col-md-6">
                                    <x-forms.select name="estado" label="Estado" required
                                        :options="['1' => 'Activo', '0' => 'Inactivo']" placeholder="" value="1" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                <i class="ri-close-line me-1"></i>Cerrar
                            </button>
                            <x-ui.button-save id="add-btn" text="Agregar" icon="ri-add-line" loading-text="Agregando..." />
                            <x-ui.button-save id="edit-btn" text="Actualizar" icon="ri-save-line"
                                loading-text="Actualizando..." style="display: none;" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Exportar PDF con filtros --}}
    <div class="modal fade atlantico-modal" id="pdfExportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 360px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-file-pdf-line me-2"></i>Exportar PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Filtra qué insumos incluir en el reporte.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="pdf-filter-tipo">Tipo de Insumo</label>
                        <select class="form-select" id="pdf-filter-tipo">
                            <option value="">Todos los tipos</option>
                            <option value="Tela">Tela</option>
                            <option value="Hilo">Hilo</option>
                            <option value="Boton">Botón</option>
                            <option value="Cierre">Cierre</option>
                            <option value="Etiqueta">Etiqueta</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold" for="pdf-filter-stock">Disponibilidad</label>
                        <select class="form-select" id="pdf-filter-stock">
                            <option value="">Todos</option>
                            <option value="con_stock">Con Stock</option>
                            <option value="agotado">Agotados</option>
                        </select>
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
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.colVis.min.js"></script>
    <script src="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        $(document).ready(function () {
            function generateButtons(insumoId) {
                var sVer = `<button class="btn btn-sm btn-soft-info view-item-btn" data-id="${insumoId}" title="Ver"><i class="ri-eye-fill"></i></button>`;
                var items =
                    `<li><button type="button" class="dropdown-item act-item act-edit edit-item-btn" data-id="${insumoId}"><span class="act-ic"><i class="ri-pencil-fill"></i></span>Editar</button></li>` +
                    `<li><button type="button" class="dropdown-item act-item act-del remove-item-btn" data-id="${insumoId}"><span class="act-ic"><i class="ri-delete-bin-fill"></i></span>Eliminar</button></li>`;
                var menu =
                    `<div class="dropdown d-inline-block">` +
                        `<button class="btn btn-sm btn-soft-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Más acciones"><i class="ri-more-2-fill"></i></button>` +
                        `<ul class="dropdown-menu dropdown-menu-end actions-menu">${items}</ul>` +
                    `</div>`;
                return `<div class="d-flex gap-1 justify-content-center align-items-center">${sVer}${menu}</div>`;
            }

            function renderEllipsis(value) {
                if (!value) return '<span class="text-muted">—</span>';
                return '<span title="' + value + '" style="cursor:default;">' + value + '</span>';
            }

            var table = $('#insumos-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                responsive: false,
                ajax: {
                    url: "{{ route('insumos.data') }}",
                    data: function (d) {
                        d.filter_tipo   = $('#filter-tipo').val();
                        d.filter_stock  = $('#filter-stock').val();
                        d.filter_orden  = $('#filter-orden').val();
                    }
                },
                dom: 'rtip',
                buttons: [
                    {
                        extend: 'copy',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] }
                    },
                    {
                        extend: 'csv',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] }
                    },
                    {
                        extend: 'excel',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] }
                    },
                    {
                        extend: 'print',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] }
                    }
                ],
                columns: [
                    {
                        data: 'nombre',
                        name: 'nombre',
                        width: '20%',
                        render: function (data) {
                            return renderEllipsis(data);
                        }
                    },
                    {
                        data: 'codigo',
                        name: 'codigo',
                        width: '8%',
                        render: function (data) {
                            return data
                                ? '<span class="codigo-pill" style="font-family: monospace; padding: .12rem .55rem; background: rgba(30,60,114,.10); color: #1e3c72; border-radius: 4px; font-size: .78rem; font-weight: 600;">' + data + '</span>'
                                : '<span class="text-muted">—</span>';
                        }
                    },
                    {
                        data: 'tipo',
                        name: 'tipo',
                        width: '12%',
                        render: function (data) {
                            var tipos = {
                                'Tela': '<span class="badge-tipo badge-tipo-tela"><i class="ri-t-shirt-line"></i> Tela</span>',
                                'Hilo': '<span class="badge-tipo badge-tipo-hilo"><i class="ri-links-line"></i> Hilo</span>',
                                'Boton': '<span class="badge-tipo badge-tipo-boton"><i class="ri-radio-button-line"></i> Botón</span>',
                                'Cierre': '<span class="badge-tipo badge-tipo-cierre"><i class="ri-lock-line"></i> Cierre</span>',
                                'Etiqueta': '<span class="badge-tipo badge-tipo-etiqueta"><i class="ri-price-tag-3-line"></i> Etiqueta</span>'
                            };
                            return tipos[data] || '<span class="badge-tipo"><i class="ri-more-line"></i> ' + data + '</span>';
                        }
                    },
                    {
                        data: 'stock_minimo',
                        name: 'stock_minimo',
                        width: '9%',
                        render: function (data) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'stock_actual',
                        name: 'stock_actual',
                        width: '9%',
                        render: function (data, type, row) {
                            var stockClass = 'stock-' + row.stock_status;
                            return `<span class="${stockClass}">${parseFloat(data).toFixed(2)}</span>`;
                        }
                    },
                    {
                        data: 'stock_maximo',
                        name: 'stock_maximo',
                        width: '9%',
                        render: function (data) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'costo_unitario',
                        name: 'costo_unitario',
                        width: '14%',
                        render: function (data) {
                            return '$/ ' + parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'id',
                        name: 'actions',
                        width: '19%',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            return generateButtons(data);
                        }
                    }
                ],
                order: [],
                ordering: false,
                language: lenguajeData
            });

            // ══════════════════════════════════════════════════
            // BÚSQUEDA + FILTROS AVANZADOS — Patrón Maestro S-07
            // Header unificado: búsqueda global + panel colapsable
            // ══════════════════════════════════════════════════

            // ── Badge: actualizar contador de filtros activos + punto rojo ──
            function updateFilterBadge() {
                var count = 0;
                if ($('#filter-tipo').val() !== '')           count++;
                if ($('#filter-stock').val() !== '')          count++;
                if ($('#filter-orden').val() !== 'recientes') count++;
                var $badge = $('#active-filter-count');
                var $dot   = $('#filter-dot-indicator');
                if (count > 0) {
                    $badge.text(count).removeClass('d-none');
                    $dot.removeClass('d-none');
                } else {
                    $badge.addClass('d-none');
                    $dot.addClass('d-none');
                }
            }

            // ── Sincronizar clase is-collapsed con el estado del collapse ──
            $('#filters-collapse-body').on('show.bs.collapse', function () {
                $('.navy-filter-header').removeClass('is-collapsed');
            }).on('hidden.bs.collapse', function () {
                $('.navy-filter-header').addClass('is-collapsed');
            });

            // ── Búsqueda global (debounce 300ms) ──
            var searchTimeout = null;
            $('#custom-search-input').on('keyup', function () {
                clearTimeout(searchTimeout);
                var val = this.value;
                searchTimeout = setTimeout(function () {
                    table.search(val).draw();
                }, 300);
            });

            // ── Filtros de select: recargar al cambiar ──
            $('.navy-filter-select').on('change', function () {
                table.ajax.reload();
                updateFilterBadge();
            });

            // ── Botón limpiar: resetea búsqueda + filtros + orden ──
            $('#btn-clear-filters').on('click', function () {
                $('#filter-tipo').val('');
                $('#filter-stock').val('');
                $('#filter-orden').val('recientes');
                $('#custom-search-input').val('');
                updateFilterBadge();
                table.search('').ajax.reload(function () {
                    updateFilterBadge();
                });
            });

            function formatDate(dateStr) {
                if (!dateStr) return 'N/A';
                if (typeof dateStr === 'string') {
                    var parts = dateStr.trim().split(' ');
                    var datePart = parts[0] || '';
                    if (/^\d{2}\/\d{2}\/\d{4}$/.test(datePart)) return datePart;
                }
                var date = new Date(dateStr);
                if (isNaN(date.getTime())) return dateStr;
                var day   = String(date.getDate()).padStart(2, '0');
                var month = String(date.getMonth() + 1).padStart(2, '0');
                var year  = date.getFullYear();
                return day + '/' + month + '/' + year;
            }

            // Ver detalles
            var tipoBadges = {
                'Tela':     '<span class="badge-tipo badge-tipo-tela"><i class="ri-t-shirt-line"></i> Tela</span>',
                'Hilo':     '<span class="badge-tipo badge-tipo-hilo"><i class="ri-links-line"></i> Hilo</span>',
                'Boton':    '<span class="badge-tipo badge-tipo-boton"><i class="ri-radio-button-line"></i> Botón</span>',
                'Cierre':   '<span class="badge-tipo badge-tipo-cierre"><i class="ri-lock-line"></i> Cierre</span>',
                'Etiqueta': '<span class="badge-tipo badge-tipo-etiqueta"><i class="ri-price-tag-3-line"></i> Etiqueta</span>'
            };

            $(document).on('click', '.view-item-btn', function () {
                var id = $(this).data('id');
                $.get("{{ route('insumos.show', ':id') }}".replace(':id', id), function (data) {
                    $("#view-nombre").text(data.nombre);
                    $("#view-tipo").html(tipoBadges[data.tipo] || data.tipo);
                    $("#view-unidad-medida").text(data.unidad_medida);
                    $("#view-stock-actual").text(parseFloat(data.stock_actual).toFixed(2));
                    $("#view-stock-minimo").text(parseFloat(data.stock_minimo).toFixed(2));
                    $("#view-stock-maximo").text(parseFloat(data.stock_maximo).toFixed(2));
                    $("#view-costo-unitario").text('$/ ' + parseFloat(data.costo_unitario).toFixed(2));
                    $("#view-created").text(formatDate(data.created_at));
                    $("#viewModal").modal('show');
                });
            });

            // Editar
            $(document).on('click', '.edit-item-btn', function () {
                var id = $(this).data('id');
                $.get("{{ route('insumos.show', ':id') }}".replace(':id', id), function (data) {
                    $("#modalTitle").text("Editar Insumo");
                    $("#id-field").val(data.id);
                    $("#field-nombre").val(data.nombre);
                    $("#codigo-field").val(data.codigo || '');
                    $("#codigo-field").prop('readonly', !!data.codigo);
                    $("#field-tipo").val(data.tipo);
                    $("#field-unidad_medida").val(data.unidad_medida);
                    var inventoriable = data.is_inventoriable !== false && data.is_inventoriable !== 0;
                    $("#is-inventoriable-switch").prop('checked', inventoriable);
                    $("#stock-fields-wrapper").toggle(inventoriable);
                    $("#field-stock_actual").val(data.stock_actual);
                    $("#field-stock_minimo").val(data.stock_minimo);
                    $("#field-stock_maximo").val(data.stock_maximo);
                    $("#field-costo_unitario").val(data.costo_unitario);
                    $("#field-estado").val(data.estado ? '1' : '0');

                    $("#add-btn").hide();
                    $("#edit-btn").show();
                    $("#showModal").modal('show');
                });
            });

            // Enviar formulario
            $("#insumoForm").on("submit", function (e) {
                e.preventDefault();

                if (!validarFormularioInsumo()) {
                    return;
                }
                var id = $("#id-field").val();
                var url = id ? "{{ route('insumos.update', ':id') }}".replace(':id', id) : "{{ route('insumos.store') }}";
                var method = id ? "PUT" : "POST";

                var formData = new FormData(this);
                if (method === "PUT") {
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $("#showModal").modal('hide');
                        table.draw();
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.success,
                            showConfirmButton: false,
                            customClass: {
                                confirmButton: 'btn btn-primary w-xs me-2',
                                cancelButton: 'btn btn-danger w-xs'
                            },
                            buttonsStyling: false,
                            showCloseButton: true,
                            timer: 1500
                        });
                    },
                    error: function (xhr) {
                        var errors = xhr.responseJSON.errors;
                        var errorMessage = '';
                        $.each(errors, function (key, value) {
                            errorMessage += value[0] + '<br>';
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errorMessage,
                            customClass: {
                                confirmButton: 'btn btn-primary w-xs me-2',
                                cancelButton: 'btn btn-danger w-xs'
                            },
                            buttonsStyling: false,
                            showCloseButton: true
                        });
                    }
                });
            });

            // Eliminar
            $(document).on("click", ".remove-item-btn", function () {
                var id = $(this).data("id");
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡No podrás revertir esto!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-primary w-xs me-2',
                        cancelButton: 'btn btn-danger w-xs'
                    },
                    buttonsStyling: false,
                    showCloseButton: true
                }).then(function (result) {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('insumos.destroy', ':id') }}".replace(':id', id),
                            method: "DELETE",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                table.draw();
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Eliminado!',
                                    text: response.success,
                                    showConfirmButton: false,
                                    customClass: {
                                        confirmButton: 'btn btn-primary w-xs me-2',
                                        cancelButton: 'btn btn-danger w-xs'
                                    },
                                    buttonsStyling: false,
                                    showCloseButton: true,
                                    timer: 1500
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'No se pudo eliminar el insumo',
                                    customClass: {
                                        confirmButton: 'btn btn-primary w-xs me-2',
                                        cancelButton: 'btn btn-danger w-xs'
                                    },
                                    buttonsStyling: false,
                                    showCloseButton: true
                                });
                            }
                        });
                    }
                });
            });

            // Toggle inventariable: muestra/oculta campos de stock
            $(document).on('change', '#is-inventoriable-switch', function () {
                $('#stock-fields-wrapper').toggle($(this).is(':checked'));
            });

            // Limpiar modal al cerrar
            $("#showModal").on("hidden.bs.modal", function () {
                $("#modalTitle").text("Agregar Insumo");
                $("#insumoForm")[0].reset();
                $("#id-field").val("");
                $("#codigo-field").prop('readonly', false);
                $("#is-inventoriable-switch").prop('checked', true);
                $("#stock-fields-wrapper").show();
                $("#add-btn").show();
                $("#edit-btn").hide();
                $('#insumoForm').find('input, select, textarea').removeClass('is-invalid is-valid');
                $('#insumoForm').find('.invalid-feedback').hide();
            });

            // Inicializar tooltips dentro del modal cuando se abre
            $("#showModal").on("shown.bs.modal", function () {
                $('#showModal [data-bs-toggle="tooltip"]').each(function () {
                    if (!bootstrap.Tooltip.getInstance(this)) {
                        new bootstrap.Tooltip(this);
                    }
                });
            });

            // ══════════════════════════════════════════════════════
            // VALIDACIONES ONBLUR
            // ══════════════════════════════════════════════════════

            // Nombre — mín. 3 chars + AJAX duplicado
            $(document).on('blur', '#field-nombre', function () {
                var $input = $(this);
                var val = $input.val().trim();
                var excludeId = $('#id-field').val();
                if (val.length === 0) {
                    marcarInvalido($input, 'El nombre es obligatorio.');
                    return;
                }
                if (val.length < 3) {
                    marcarInvalido($input, 'Mínimo 3 caracteres.');
                    return;
                }
                $.get("{{ route('insumos.check-nombre') }}", { nombre: val, exclude_id: excludeId }, function (res) {
                    if (res.exists) {
                        marcarInvalido($input, 'Ya existe un insumo con ese nombre.');
                    } else {
                        marcarValido($input);
                    }
                });
            });

            // Tipo — obligatorio
            $(document).on('blur', '#field-tipo', function () {
                if (!$(this).val()) {
                    marcarInvalido($(this), 'El tipo es obligatorio.');
                } else {
                    marcarValido($(this));
                }
            });

            // Unidad de Medida — obligatoria
            $(document).on('blur', '#field-unidad_medida', function () {
                if (!$(this).val()) {
                    marcarInvalido($(this), 'La unidad de medida es obligatoria.');
                } else {
                    marcarValido($(this));
                }
            });

            // Stock Actual — no negativo (solo si inventariable)
            $(document).on('blur', '#field-stock_actual', function () {
                if (!$('#is-inventoriable-switch').is(':checked')) return;
                var val = parseFloat($(this).val());
                if (isNaN(val) || val < 0) {
                    marcarInvalido($(this), 'El stock no puede ser negativo.');
                } else {
                    marcarValido($(this));
                    var $min = $('#field-stock_minimo');
                    if ($min.val() !== '') {
                        var minVal = parseFloat($min.val());
                        if (!isNaN(minVal) && minVal > val) {
                            marcarInvalido($min, 'El stock mínimo no puede superar el stock actual.');
                        } else if (!isNaN(minVal) && minVal >= 0) {
                            marcarValido($min);
                        }
                    }
                }
            });

            // Stock Mínimo — no negativo + no mayor al stock actual
            $(document).on('blur', '#field-stock_minimo', function () {
                if (!$('#is-inventoriable-switch').is(':checked')) return;
                var val = parseFloat($(this).val());
                var stockActual = parseFloat($('#field-stock_actual').val());
                if (isNaN(val) || val < 0) {
                    marcarInvalido($(this), 'El stock mínimo no puede ser negativo.');
                } else if (!isNaN(stockActual) && val > stockActual) {
                    marcarInvalido($(this), 'El stock mínimo no puede superar el stock actual.');
                } else {
                    marcarValido($(this));
                }
            });

            // Existencia Máxima — no negativa + no menor que la mínima
            $(document).on('blur', '#field-stock_maximo', function () {
                if (!$('#is-inventoriable-switch').is(':checked')) return;
                var val = parseFloat($(this).val());
                if ($(this).val() === '') { marcarValido($(this)); return; }
                var stockMin = parseFloat($('#field-stock_minimo').val());
                if (isNaN(val) || val < 0) {
                    marcarInvalido($(this), 'La existencia máxima no puede ser negativa.');
                } else if (!isNaN(stockMin) && val < stockMin) {
                    marcarInvalido($(this), 'La existencia máxima no puede ser menor que la mínima.');
                } else {
                    marcarValido($(this));
                }
            });

            // Costo Unitario — mayor a 0
            $(document).on('blur', '#field-costo_unitario', function () {
                var val = parseFloat($(this).val());
                if (isNaN(val) || val <= 0) {
                    marcarInvalido($(this), 'El costo unitario debe ser mayor a cero.');
                } else {
                    marcarValido($(this));
                }
            });

            // ══════════════════════════════════════════════════════
            // VALIDACIÓN AL SUBMIT
            // ══════════════════════════════════════════════════════
            function validarFormularioInsumo() {
                let esValido = true;
                let inventoriable = $('#is-inventoriable-switch').is(':checked');

                let $nombre = $('#field-nombre');
                let nombre = $nombre.val().trim();
                if (!nombre) {
                    marcarInvalido($nombre, 'El nombre es obligatorio.');
                    esValido = false;
                } else if (nombre.length < 3) {
                    marcarInvalido($nombre, 'El nombre debe tener al menos 3 caracteres.');
                    esValido = false;
                } else { marcarValido($nombre); }

                let $tipo = $('#field-tipo');
                if (!$tipo.val()) {
                    marcarInvalido($tipo, 'El tipo es obligatorio.');
                    esValido = false;
                } else { marcarValido($tipo); }

                let $unidad = $('#field-unidad_medida');
                if (!$unidad.val().trim()) {
                    marcarInvalido($unidad, 'La unidad de medida es obligatoria.');
                    esValido = false;
                } else { marcarValido($unidad); }

                if (inventoriable) {
                    let $stockActual = $('#field-stock_actual');
                    let stockActual = parseFloat($stockActual.val());
                    if (isNaN(stockActual) || stockActual < 0) {
                        marcarInvalido($stockActual, 'El stock actual no puede ser negativo.');
                        esValido = false;
                    } else { marcarValido($stockActual); }

                    let $stockMin = $('#field-stock_minimo');
                    let stockMin = parseFloat($stockMin.val());
                    if (isNaN(stockMin) || stockMin < 0) {
                        marcarInvalido($stockMin, 'El stock mínimo no puede ser negativo.');
                        esValido = false;
                    } else if (!isNaN(stockActual) && stockActual >= 0 && stockMin > stockActual) {
                        marcarInvalido($stockMin, 'El stock mínimo no puede superar el stock actual.');
                        esValido = false;
                    } else { marcarValido($stockMin); }

                    let $stockMax = $('#field-stock_maximo');
                    if ($stockMax.val() !== '') {
                        let stockMax = parseFloat($stockMax.val());
                        if (isNaN(stockMax) || stockMax < 0) {
                            marcarInvalido($stockMax, 'La existencia máxima no puede ser negativa.');
                            esValido = false;
                        } else if (!isNaN(stockMin) && stockMin >= 0 && stockMax < stockMin) {
                            marcarInvalido($stockMax, 'La existencia máxima no puede ser menor que la mínima.');
                            esValido = false;
                        } else { marcarValido($stockMax); }
                    }
                }

                let $costo = $('#field-costo_unitario');
                let costo = parseFloat($costo.val());
                if (isNaN(costo) || costo <= 0) {
                    marcarInvalido($costo, 'El costo unitario debe ser mayor a cero.');
                    esValido = false;
                } else { marcarValido($costo); }

                return esValido;
            }
        });

        // PDF Export Modal
        $('#btn-generar-pdf').on('click', function () {
            var baseUrl = '{{ route('insumos.reporte.pdf') }}';
            var params = [];
            var tipo  = $('#pdf-filter-tipo').val();
            var stock = $('#pdf-filter-stock').val();
            if (tipo)  params.push('tipo='  + encodeURIComponent(tipo));
            if (stock) params.push('stock=' + encodeURIComponent(stock));
            window.open(baseUrl + (params.length ? '?' + params.join('&') : ''), '_blank');
            bootstrap.Modal.getInstance(document.getElementById('pdfExportModal'))?.hide();
        });
        $('#pdfExportModal').on('show.bs.modal', function () {
            $('#pdf-filter-tipo').val('');
            $('#pdf-filter-stock').val('');
        });
    </script>
@endpush