@extends('admin.layouts.app')

@push('styles')
    <!-- Sweet Alert css-->
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet"
        type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
    {{-- Grid responsivo para filtros: 1 col mobile → 4 cols desktop --}}
    <style>
        @media (min-width: 768px) {
            .navy-filter-grid {
                grid-template-columns: repeat(4, 1fr) !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Gestión de Productos</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Gestión General</a></li>
                        <li class="breadcrumb-item active">Productos</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>

    {{-- Estilos en public/assets/css/custom.css — sección "MÓDULO MAESTROS — Productos" --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-maestros">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Listado de Productos</h5>
                        <div class="flex-shrink-0 d-flex align-items-center gap-3">
                            <!-- Toggle Historial -->
                            @if($historial)
                                <a href="{{ route('productos.index') }}" class="btn-historial btn-historial-volver">
                                    <i class="ri-arrow-left-line"></i> Solo Activos
                                </a>
                            @else
                                <a href="{{ route('productos.index', ['historial' => true]) }}" class="btn-historial btn-historial-ver">
                                    <i class="ri-time-line"></i> Ver Historial
                                </a>
                            @endif
                            @if(!$historial)
                            <div class="d-flex gap-2 align-items-center">
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#tiposModal">
                                    <i class="ri-settings-3-line align-bottom me-1"></i> Gestionar Tipos
                                </button>
                                <button type="button" class="btn btn-success add-btn ms-2" data-bs-toggle="modal"
                                    id="create-btn" data-bs-target="#showModal">
                                    <i class="ri-add-line align-bottom me-1"></i> Agregar Producto
                                </button>
                                <button type="button" class="btn btn-danger ms-2" data-bs-toggle="modal" data-bs-target="#pdfExportModal">
                                    <i class="ri-file-pdf-fill align-bottom me-1"></i> Exportar PDF
                                </button>
                            </div>
                            @else
                            <div class="d-flex gap-2 align-items-center">
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#pdfExportModal">
                                    <i class="ri-file-pdf-fill align-bottom me-1"></i> Exportar PDF
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {{-- ============================================================
                         FILTROS — Patrón Maestro S-07 (Colapsable)
                         Filtros server-side: ajax.reload() con param filter_tipo_producto_id.
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
                                    placeholder="Buscar producto..."
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
                                    {{-- Filtro 1: Tipo de Producto (dinámico desde $tiposProducto) --}}
                                    <div>
                                        <label class="navy-filter-label" for="filter-tipo-producto">
                                            <i class="ri-price-tag-3-line"></i> Tipo de Producto
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-tipo-producto">
                                            <option value="">Todos</option>
                                            @foreach($tiposProducto as $tipo)
                                                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- Filtro 2: Tela (dinámico desde $telasDisponibles) --}}
                                    <div>
                                        <label class="navy-filter-label" for="filter-tela">
                                            <i class="ri-shirt-line"></i> Tela
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-tela">
                                            <option value="">Todas</option>
                                            @foreach($telasDisponibles as $tela)
                                                <option value="{{ $tela->id }}">{{ $tela->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- Filtro 3: Estatus --}}
                                    <div>
                                        <label class="navy-filter-label" for="filter-estatus">
                                            <i class="ri-shield-check-line"></i> Estatus
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-estatus">
                                            <option value="">Todos</option>
                                            <option value="1" selected>Activo</option>
                                            <option value="0">Inactivo</option>
                                        </select>
                                    </div>
                                    {{-- Filtro 4: Ordenar por --}}
                                    <div>
                                        <label class="navy-filter-label" for="filter-orden">
                                            <i class="ri-sort-asc"></i> Ordenar por
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-orden">
                                            <option value="recientes">Más recientes primero</option>
                                            <option value="codigo_asc">Código (A-Z)</option>
                                            <option value="codigo_desc">Código (Z-A)</option>
                                            <option value="precio_mayor">Mayor Precio Base</option>
                                            <option value="precio_menor">Menor Precio Base</option>
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

                    <table id="productos-table" class="table table-bordered table-striped table-sm align-middle table-operativa table-maestro">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Imagen</th>
                                <th>Tipo</th>
                                <th>Tela</th>
                                <th>Atributos</th>
                                <th>Precio Base</th>
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

    <!-- Modal para ver detalles del Producto -->
    <div class="modal fade atlantico-modal" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title">Detalles del Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Imagen del Producto centrada -->
                    <div class="card border-0 shadow-sm mb-4" id="producto-imagen-container" style="display: none;">
                        <div class="card-header border-0" style="background: rgba(30, 60, 114, 0.1);">
                            <h6 class="mb-0" style="color: #1e3c72;">
                                <i class="ri-image-line me-2"></i>Vista del Producto
                            </h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="rounded mx-auto d-inline-block p-2" style="background: rgba(30, 60, 114, 0.05);">
                                <img id="producto-imagen" src="" alt="Imagen del producto" class="rounded"
                                    style="max-width: 200px; max-height: 200px; object-fit: cover;">
                            </div>
                        </div>
                    </div>

                    <!-- Card Información del Producto -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-0" style="background: rgba(30, 60, 114, 0.1);">
                            <h6 class="mb-0" style="color: #1e3c72;">
                                <i class="ri-information-line me-2"></i>Información del Producto
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center producto-info-item">
                                        <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                            <i class="ri-price-tag-3-line" style="color: #1e3c72;"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Nombre</small>
                                            <span class="fw-semibold" id="view-nombre">-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center producto-info-item">
                                        <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                            <i class="ri-money-dollar-circle-line" style="color: #1e3c72;"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Precio Base</small>
                                            <span class="fw-semibold" id="view-precio">-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center producto-info-item">
                                        <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                            <i class="ri-calendar-line" style="color: #1e3c72;"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Fecha de Creación</small>
                                            <span class="fw-semibold" id="view-created">-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-start producto-info-item">
                                        <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                            <i class="ri-file-text-line" style="color: #1e3c72;"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Descripción</small>
                                            <span class="fw-semibold" id="view-descripcion">-</span>
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
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="modalTitle">Agregar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="productoForm" enctype="multipart/form-data" novalidate>
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="id-field" />
                        <div class="row">
                            <div class="col-md-6">
                                <div class="modal-form-section">
                                    <div class="modal-form-section-title"><i class="ri-price-tag-3-line"></i>Identificación
                                        del Producto</div>

                                    {{-- Tipo de Producto — mantiene HTML custom por data-prefijo + botón "+" --}}
                                    <div class="mb-3">
                                        <label for="tipo-producto-field" class="form-label">Tipo de Producto <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <select id="tipo-producto-field" name="tipo_producto_id" class="form-select"
                                                required>
                                                <option value="">Seleccione un tipo...</option>
                                                @foreach($tiposProducto as $tipo)
                                                    <option value="{{ $tipo->id }}" data-prefijo="{{ $tipo->prefijo }}">
                                                        {{ $tipo->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-outline-primary" id="btn-add-tipo-inline"
                                                title="Agregar nuevo tipo">
                                                <i class="ri-add-line"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Selector de Tela (insumo tipo='Tela') --}}
                                    <div class="mb-3" id="tela-field-wrapper">
                                        <label for="tela-field" class="form-label">
                                            Tela <span class="text-danger" id="tela-required-star">*</span>
                                        </label>
                                        <select id="tela-field" name="insumo_tela_id" class="form-select">
                                            <option value="">Seleccione una tela...</option>
                                            @foreach($telasDisponibles as $tela)
                                                <option value="{{ $tela->id }}"
                                                    data-codigo="{{ $tela->codigo }}"
                                                    data-costo="{{ $tela->costo_unitario }}"
                                                    data-unidad="{{ $tela->unidad_medida }}">
                                                    {{ $tela->nombre }}
                                                    @if($tela->codigo) [{{ $tela->codigo }}] @endif
                                                    — ${{ number_format($tela->costo_unitario, 2) }}/{{ $tela->unidad_medida }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted" id="tela-hint">
                                            Materia prima base. Define la sugerencia de precio.
                                        </small>
                                    </div>

                                    <x-forms.input name="codigo" label="Código (SKU)" readonly class="bg-light fw-bold"
                                        placeholder="Se genera al seleccionar tipo, tela y atributos"
                                        hint="Determinístico: prefijo-tela-atributos-secuencial." id="codigo-field" />

                                    <div class="mb-0">
                                        <label for="descripcion-field" class="form-label">Descripción <span
                                                class="text-danger">*</span></label>
                                        <textarea id="descripcion-field" name="descripcion" class="form-control" rows="3"
                                            placeholder="Descripción adicional del producto" required></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modal-form-section">
                                    <div class="modal-form-section-title">
                                        <i class="ri-list-check-2"></i>Variaciones (atributos de confección)
                                    </div>
                                    <div id="atributos-chips-container">
                                        <div class="text-muted small text-center py-3" id="atributos-empty">
                                            <i class="ri-information-line"></i>
                                            Selecciona un tipo de producto para ver sus variaciones.
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-form-section mb-0">
                                    <div class="modal-form-section-title"><i class="ri-money-dollar-circle-line"></i>Precio,
                                        Imagen y Estado</div>

                                    <div class="mb-3">
                                        <label for="precio-base-field" class="form-label">
                                            Precio Base ($) <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" name="precio_base"
                                                step="0.01" min="0" placeholder="0.00" required id="precio-base-field" />
                                            <button type="button" class="btn btn-outline-secondary"
                                                id="btn-aplicar-sugerido" title="Aplicar precio sugerido"
                                                style="display:none;">
                                                <i class="ri-magic-line me-1"></i>
                                                <span id="sugerido-label">Sugerido: $0.00</span>
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-1" id="precio-breakdown" style="display:none;">
                                            <span id="precio-breakdown-text"></span>
                                        </small>
                                    </div>

                                    {{-- Imagen — mantiene HTML nativo por preview --}}
                                    <div class="mb-3">
                                        <label for="imagen-field" class="form-label">Imagen <span
                                                class="text-danger" id="imagen-required-star">*</span></label>
                                        <input type="file" id="imagen-field" name="imagen" class="form-control"
                                            accept="image/*" required />
                                        <div id="imagen-preview" class="mt-2 text-center" style="display: none;">
                                            <img src="" alt="Vista previa de la imagen" class="img-fluid"
                                                style="max-width: 200px;">
                                        </div>
                                    </div>

                                    {{-- Switch de Estado sincronizado con hidden input --}}
                                    <div class="mb-3">
                                        <label class="form-label mb-2">Estado <span class="text-danger">*</span></label>
                                        <div class="form-check form-switch form-switch-success form-switch-md" dir="ltr">
                                            <input type="checkbox" class="form-check-input" id="estado-switch" checked>
                                            <label class="form-check-label fw-medium" for="estado-switch" id="estado-label">Activo</label>
                                        </div>
                                        <input type="hidden" name="estado" id="estado-hidden-field" value="1">
                                    </div>
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

    <!-- Modal para gestionar Tipos de Producto -->
    <div class="modal fade atlantico-modal" id="tiposModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title">Gestionar Tipos de Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button type="button" class="btn btn-success" id="add-tipo-btn">
                            <i class="ri-add-line me-1"></i>Agregar Tipo
                        </button>
                        <div class="btn-group" role="group" aria-label="Vista de tipos de producto">
                            <button type="button" class="btn btn-outline-primary active" id="btn-tipos-activos">Activos</button>
                            <button type="button" class="btn btn-outline-secondary" id="btn-tipos-historial">Historial (Inhabilitados)</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover w-100" id="tipos-table" width="100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Prefijo</th>
                                    <th>Productos</th>
                                    <th width="100">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tipos-tbody">
                                <!-- Se llena con JavaScript -->
                            </tbody>
                        </table>
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

    <!-- Modal para agregar/editar Tipo de Producto -->
    <div class="modal fade atlantico-modal" id="addTipoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="tipoModalTitle">Agregar Tipo de Producto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="tipoForm" novalidate>
                    <div class="modal-body">
                        <input type="hidden" id="tipo-id-field" />
                        <div class="modal-form-section mb-0">
                            <div class="modal-form-section-title"><i class="ri-list-settings-line"></i>Datos del Tipo de
                                Producto</div>

                            <div class="mb-3">
                                <label for="tipo-nombre-field" class="form-label required">Nombre del Tipo</label>
                                <input type="text" id="tipo-nombre-field" name="nombre" class="form-control"
                                    placeholder="Ej: Chemise, Franela, Pantalón" required />
                                <div id="tipo-nombre-error" class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="tipo-prefijo-field" class="form-label required">Prefijo de Código</label>
                                <input type="text" id="tipo-prefijo-field" name="prefijo" class="form-control"
                                    placeholder="Ej: CHM, FRN, PNT (máx 5 letras)" maxlength="5" required
                                    style="text-transform: uppercase;" />
                                <div id="tipo-prefijo-error" class="invalid-feedback"></div>
                                <small class="text-muted">Se usará para generar códigos como CHM-001</small>
                            </div>
                            <div class="mb-3">
                                <label for="tipo-descripcion-field" class="form-label required">Descripción</label>
                                <textarea id="tipo-descripcion-field" name="descripcion" class="form-control" rows="2"
                                    placeholder="Descripción opcional" required></textarea>
                                <div id="tipo-descripcion-error" class="invalid-feedback"></div>
                            </div>

                            <div class="row g-2 mb-0">
                                <div class="col-md-7">
                                    <label for="tipo-precio-confeccion" class="form-label">Precio de Confección</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" id="tipo-precio-confeccion" name="precio_confeccion"
                                            class="form-control" step="0.01" min="0" max="99999.99" placeholder="0.00" />
                                    </div>
                                    <small class="text-muted">Mano de obra + insumos secundarios. Se suma al precio de la tela para sugerir el precio final del producto.</small>
                                </div>
                                <div class="col-md-5 d-flex align-items-end">
                                    <div class="form-check form-switch w-100">
                                        <input class="form-check-input" type="checkbox" id="tipo-requiere-tela" name="requiere_tela" checked>
                                        <label class="form-check-label" for="tipo-requiere-tela">
                                            Requiere tela
                                        </label>
                                        <div class="text-muted small">Si está activo, todo producto de este tipo debe tener una tela asignada.</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Consumo de tela por unidad (visible solo si requiere tela) --}}
                            <div class="row g-2 mt-2 mb-0" id="tipo-consumo-tela-row">
                                <div class="col-md-7">
                                    <label for="tipo-consumo-tela" class="form-label">Consumo de tela por unidad</label>
                                    <div class="input-group">
                                        <input type="number" id="tipo-consumo-tela" name="consumo_tela_por_unidad"
                                            class="form-control" step="0.01" min="0" max="9999.99" placeholder="0.00" />
                                        <span class="input-group-text">por unidad</span>
                                    </div>
                                    <small class="text-muted">
                                        Cantidad de tela por cada unidad producida (ej: 2 m para una camisa).
                                        Al crear la orden se prellena con (consumo × cantidad), usando la tela específica del producto.
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Sección: Atributos de confección asociados --}}
                        <div class="modal-form-section mb-0 mt-3">
                            <div class="modal-form-section-title">
                                <i class="ri-list-check-2"></i>Atributos de confección
                            </div>
                            <p class="text-muted small mb-2">
                                Selecciona qué variaciones definen una variante de este tipo (ej: Manga, Cuello).
                                El <strong>orden</strong> define cómo se concatenan en el código del producto.
                            </p>
                            <div id="tipo-atributos-list" class="d-flex flex-column gap-2">
                                {{-- Render dinámico vía JS --}}
                                <div class="text-center text-muted py-2" id="tipo-atributos-empty">
                                    <span class="spinner-border spinner-border-sm me-2"></span>Cargando atributos…
                                </div>
                            </div>
                        </div>

                        {{-- Sección: Insumos por defecto (template para órdenes de producción) --}}
                        <div class="modal-form-section mb-0 mt-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="modal-form-section-title mb-0">
                                    <i class="ri-tools-line"></i>Insumos por defecto
                                </div>
                                <button type="button" class="btn btn-sm btn-soft-primary py-0 px-2" id="tipo-insumo-add-btn">
                                    <i class="ri-add-line"></i> Agregar insumo
                                </button>
                            </div>
                            <p class="text-muted small mb-2">
                                Insumos constantes al tipo y su consumo <strong>por unidad producida</strong>
                                (ej: 8 botones por camisa, 1 etiqueta por chemise). Al crear una orden se prellenan
                                multiplicados por la cantidad a producir.
                                <br><span class="text-muted"><i class="ri-information-line me-1"></i>Las telas no aparecen aquí — varían por variante (cada producto tiene su tela).</span>
                            </p>
                            <div id="tipo-insumos-list" class="d-flex flex-column gap-1">
                                {{-- Render dinámico vía JS --}}
                            </div>
                            <div id="tipo-insumos-empty" class="text-center text-muted py-2 small" style="display:none;">
                                <i class="ri-inbox-line me-1"></i>Sin insumos por defecto. Agrega los que se usan al producir.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-success" id="save-tipo-btn">
                            <i class="ri-save-line me-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Exportar PDF con filtros --}}
    <div class="modal fade atlantico-modal" id="pdfExportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-file-pdf-line me-2"></i>Exportar PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Filtra qué productos incluir en el reporte.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="pdf-filter-tipo">Tipo de Producto</label>
                        <select class="form-select" id="pdf-filter-tipo">
                            <option value="">Todos los tipos</option>
                            @foreach($tiposProducto as $tp)
                                <option value="{{ $tp->id }}">{{ $tp->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold" for="pdf-filter-estatus">Estatus</label>
                        <select class="form-select" id="pdf-filter-estatus">
                            <option value="">Todos</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
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
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        // Configurar pdfMake para evitar errores de fuentes
        if (typeof pdfMake !== 'undefined' && typeof pdfFonts !== 'undefined') {
            pdfMake.vfs = pdfFonts.pdfMake.vfs;
        }

        // Configuración alternativa para evitar errores de fuentes
        if (typeof pdfMake !== 'undefined') {
            pdfMake.fonts = {
                Roboto: {
                    normal: 'Roboto-Regular.ttf',
                    bold: 'Roboto-Medium.ttf',
                    italics: 'Roboto-Italic.ttf',
                    bolditalics: 'Roboto-MediumItalic.ttf'
                }
            };
        }

        $(document).ready(function () {
            var esHistorial = {{ $historial ? 'true' : 'false' }};

            function generateButtons(productoId, isTrashed) {
                var sVer = '<button class="btn btn-sm btn-soft-info view-item-btn" data-id="' + productoId + '" title="Ver"><i class="ri-eye-fill"></i></button>';
                var items;
                if (isTrashed) {
                    items = '<li><button type="button" class="dropdown-item act-item act-restore restore-item-btn" data-id="' + productoId + '"><span class="act-ic"><i class="ri-arrow-go-back-line"></i></span>Restaurar</button></li>';
                } else {
                    items =
                        '<li><button type="button" class="dropdown-item act-item act-edit edit-item-btn" data-id="' + productoId + '"><span class="act-ic"><i class="ri-pencil-fill"></i></span>Editar</button></li>' +
                        '<li><button type="button" class="dropdown-item act-item act-del remove-item-btn" data-id="' + productoId + '"><span class="act-ic"><i class="ri-forbid-line"></i></span>Inhabilitar</button></li>';
                }
                var menu =
                    '<div class="dropdown d-inline-block">' +
                        '<button class="btn btn-sm btn-soft-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Más acciones"><i class="ri-more-2-fill"></i></button>' +
                        '<ul class="dropdown-menu dropdown-menu-end actions-menu">' + items + '</ul>' +
                    '</div>';
                return '<div class="d-flex gap-1 justify-content-center align-items-center">' + sVer + menu + '</div>';
            }

            function renderEllipsis(value) {
                if (!value) return '<span class="text-muted">—</span>';
                return '<span title="' + value + '" style="cursor:default;">' + value + '</span>';
            }

            var table = $('#productos-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('productos.data') }}",
                    data: function (d) {
                        // ── Filtros avanzados: enviar valores al server ──
                        d.filter_tipo_producto_id    = $('#filter-tipo-producto').val();
                        d.filter_insumo_tela_id      = $('#filter-tela').val();
                        d.filter_estatus             = $('#filter-estatus').val();
                        d.filter_orden               = $('#filter-orden').val();
                    }
                },
                columns: [
                    {
                        data: 'codigo',
                        name: 'codigo',
                        render: function (data) {
                            return data ? '<span class="badge bg-dark">' + data + '</span>' : '-';
                        }
                    },
                    {
                        data: 'imagen',
                        name: 'imagen',
                        render: function (data) {
                            return data ? '<img src="' + data + '" alt="Imagen del producto" class="img-thumbnail" width="44" style="height:44px; object-fit:cover;">' : '<span class="text-muted">Sin imagen</span>';
                        }
                    },
                    {
                        data: 'tipo_nombre',
                        name: 'tipo_nombre',
                        render: function (data) {
                            if (!data) return '<span class="text-muted">—</span>';
                            return '<span class="badge badge-tipo badge-tipo-producto" title="' + data + '"><i class="ri-price-tag-3-line"></i> ' + data + '</span>';
                        }
                    },
                    {
                        data: 'tela_nombre',
                        name: 'tela_nombre',
                        orderable: false,
                        render: function (data) {
                            if (!data || data === '—') return '<span class="text-muted">—</span>';
                            return '<span class="badge bg-light text-dark border"><i class="ri-shirt-line me-1"></i>' + data + '</span>';
                        }
                    },
                    {
                        data: 'atributos_resumen',
                        name: 'atributos_resumen',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            if (!data || data === '—') return '<span class="text-muted small">—</span>';
                            return '<span class="text-muted small">' + data + '</span>';
                        }
                    },
                    {
                        data: 'precio_base',
                        name: 'precio_base',
                        render: function (data) {
                            return '$ ' + parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'estado',
                        name: 'estado',
                        render: function (data, type, row) {
                            // Si está en historial (trashed), mostrar badge "Inactivo"
                            if (row.trashed) {
                                return '<span class="badge badge-status status-inactivo"><i class="ri-close-circle-line"></i> Inactivo</span>';
                            }
                            return data ? '<span class="badge badge-status status-activo"><i class="ri-checkbox-circle-line"></i> Activo</span>' : '<span class="badge badge-status status-inactivo"><i class="ri-close-circle-line"></i> Inactivo</span>';
                        }
                    },
                    {
                        data: 'id',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return generateButtons(data, row.trashed);
                        }
                    }
                ],
                order: [],
                ordering: false,
                dom: 'rtip',
                buttons: [
                    {
                        extend: 'copy',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    },
                    {
                        extend: 'excel',
                        exportOptions: {
                            columns: [2, 3, 4, 5]
                        }
                    }
                ],
                language: lenguajeData
            });

            // ══════════════════════════════════════════════════════
            // BÚSQUEDA + FILTROS — Patrón Maestro S-07
            // Header unificado: búsqueda global + panel colapsable
            // Filtros: server-side (ajax.reload con filter_tipo_producto_id)
            // ══════════════════════════════════════════════════════

            // ── Badge: actualizar contador de filtros activos + punto rojo ──
            function updateFilterBadge() {
                var count = 0;
                if ($('#filter-tipo-producto').val() !== '')                         count++;
                if ($('#filter-tela').val() !== '')                                  count++;
                if ($('#filter-estatus').val() !== '1')                              count++;
                if ($('#filter-orden').val() !== 'recientes')                        count++;
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

            // ── Sincronizar clase is-collapsed con el collapse ──
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

            // ── Si se llegó por toggle historial (?historial=true) ──
            @if($historial)
                $('#filter-estatus').val('0');
                table.ajax.reload();
                updateFilterBadge();
            @endif

            // ── Botón limpiar: resetea búsqueda + filtros + orden ──
            $('#btn-clear-filters').on('click', function () {
                $('#filter-tipo-producto').val('');
                $('#filter-tela').val('');
                $('#filter-estatus').val('1');
                $('#filter-orden').val('recientes');
                $('#custom-search-input').val('');
                updateFilterBadge();
                table.search('').ajax.reload(function () {
                    updateFilterBadge();
                });
            });

            // Sincronizar switch de estado con hidden input
            $("#estado-switch").on('change', function() {
                var isChecked = $(this).is(':checked');
                $("#estado-hidden-field").val(isChecked ? '1' : '0');
                $("#estado-label").text(isChecked ? 'Activo' : 'Inactivo');
            });

            // Vista previa de imagen
            $("#imagen-field").change(function () {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $("#imagen-preview img").attr('src', e.target.result);
                        $("#imagen-preview").show();
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });

            // Ver detalles
            $(document).on('click', '.view-item-btn', function () {
                var id = $(this).data('id');
                $.get("{{ route('productos.show', ':id') }}".replace(':id', id), function (data) {
                    // Mostrar imagen solo si existe
                    if (data.imagen) {
                        $("#producto-imagen").attr('src', data.imagen);
                        $("#producto-imagen-container").show();
                    } else {
                        $("#producto-imagen-container").hide();
                    }

                    $("#view-nombre").text(data.nombre);
                    $("#view-descripcion").text(data.descripcion || 'Sin descripción');
                    $("#view-precio").text('$ ' + parseFloat(data.precio_base).toFixed(2));
                    $("#view-created").text(data.created_at);
                    $("#viewModal").modal('show');
                });
            });

            // Editar
            $(document).on('click', '.edit-item-btn', function () {
                var id = $(this).data('id');
                $.get("{{ route('productos.show', ':id') }}".replace(':id', id), function (data) {
                    $("#modalTitle").text("Editar Producto");
                    $("#id-field").val(data.id);
                    $("#tipo-producto-field").val(data.tipo_producto_id);
                    $("#tela-field").val(data.insumo_tela_id || '');
                    $("#codigo-field").val(data.codigo);
                    $("#descripcion-field").val(data.descripcion);
                    $("#precio-base-field").val(data.precio_base);
                    var isActivo = data.estado ? true : false;
                    $("#estado-switch").prop('checked', isActivo);
                    $("#estado-hidden-field").val(isActivo ? '1' : '0');
                    $("#estado-label").text(isActivo ? 'Activo' : 'Inactivo');

                    if (data.imagen) {
                        $("#imagen-preview img").attr('src', data.imagen);
                        $("#imagen-preview").show();
                        $("#imagen-field").prop('required', false);
                        $("#imagen-required-star").addClass('d-none');
                    } else {
                        $("#imagen-field").prop('required', true);
                        $("#imagen-required-star").removeClass('d-none');
                    }

                    $("#add-btn").hide();
                    $("#edit-btn").show();
                    $("#showModal").modal('show');

                    // Cargar atributos del tipo y marcar los del producto
                    cargarAtributosDeTipo(data.tipo_producto_id).then(function () {
                        renderAtributosChips(data.atributo_valor_ids || []);
                        // En edición no recalculamos SKU; mostramos sugerencia y breakdown sí
                        actualizarSugerenciaPrecioOnly();
                    });
                });
            });

            // Variante de actualizarCodigoYPrecio que NO toca el SKU (para modo edición)
            function actualizarSugerenciaPrecioOnly() {
                var tipoId = $('#tipo-producto-field').val();
                var telaId = $('#tela-field').val();
                if (!tipoId) return;
                $.getJSON("{{ route('productos.sugerir-precio') }}", {
                    tipo_producto_id: tipoId,
                    insumo_tela_id: telaId || null
                }).done(function (resp) {
                    if (resp.precio_sugerido > 0) {
                        $('#sugerido-label').text('Sugerido: $' + parseFloat(resp.precio_sugerido).toFixed(2));
                        $('#btn-aplicar-sugerido').show().data('valor', resp.precio_sugerido);
                        $('#precio-breakdown-text').html(
                            'Tela: $' + parseFloat(resp.costo_tela).toFixed(2) +
                            ' + Confección: $' + parseFloat(resp.precio_confeccion).toFixed(2) +
                            ' = <strong>$' + parseFloat(resp.precio_sugerido).toFixed(2) + '</strong>'
                        );
                        $('#precio-breakdown').show();
                    }
                });
            }

            // ========= NUEVO FLUJO: Variantes (tipo + tela + atributos) =========

            var atributosDelTipoActual = []; // [{id, nombre, codigo, valores: [...]}, ...] en orden del tipo

            function cargarAtributosDeTipo(tipoId) {
                if (!tipoId) {
                    atributosDelTipoActual = [];
                    renderAtributosChips();
                    actualizarCodigoYPrecio();
                    return $.Deferred().resolve();
                }
                return $.getJSON("{{ url('tipo-productos') }}/" + tipoId).done(function (tipo) {
                    atributosDelTipoActual = (tipo.atributos || []).map(function (a) {
                        return {
                            id: a.id,
                            nombre: a.nombre,
                            codigo: a.codigo,
                            orden: a.pivot ? a.pivot.orden : 999,
                            valores: a.valores || []
                        };
                    });
                    // Toggle requerimiento de tela
                    var requiereTela = !!tipo.requiere_tela;
                    $('#tela-field').prop('required', requiereTela);
                    $('#tela-required-star').toggle(requiereTela);
                    renderAtributosChips();
                });
            }

            function renderAtributosChips(seleccionadosIds) {
                var preseleccionados = seleccionadosIds || [];
                if (!atributosDelTipoActual.length) {
                    $('#atributos-empty').show().text('Este tipo no tiene atributos asociados.');
                    $('#atributos-chips-container').find('.atributo-grupo').remove();
                    return;
                }
                $('#atributos-empty').hide();

                var html = atributosDelTipoActual.map(function (atr) {
                    if (!atr.valores.length) {
                        return '' +
                            '<div class="atributo-grupo mb-3" data-atr-id="' + atr.id + '">' +
                                '<label class="form-label small text-muted mb-1">' +
                                    escapeHtml(atr.nombre) +
                                '</label>' +
                                '<div class="text-muted small fst-italic">Sin valores definidos. ' +
                                'Agregar en <a href="{{ url('atributos') }}" target="_blank">/atributos</a>.</div>' +
                            '</div>';
                    }
                    var chips = atr.valores.map(function (v) {
                        var checked = preseleccionados.indexOf(v.id) !== -1;
                        return '' +
                            '<input type="radio" class="btn-check atributo-radio" ' +
                                'name="atr_' + atr.id + '" id="val-' + v.id + '" ' +
                                'value="' + v.id + '" data-atr-id="' + atr.id + '"' +
                                (checked ? ' checked' : '') + '>' +
                            '<label class="btn btn-outline-primary btn-sm" for="val-' + v.id + '">' +
                                escapeHtml(v.nombre) +
                                ' <small class="text-muted">' + escapeHtml(v.codigo) + '</small>' +
                            '</label>';
                    }).join('');
                    return '' +
                        '<div class="atributo-grupo mb-3" data-atr-id="' + atr.id + '">' +
                            '<label class="form-label small fw-semibold mb-2">' +
                                escapeHtml(atr.nombre) +
                                ' <span class="text-muted small">(' + escapeHtml(atr.codigo) + ')</span>' +
                            '</label>' +
                            '<div class="d-flex flex-wrap gap-2">' + chips + '</div>' +
                        '</div>';
                }).join('');

                $('#atributos-chips-container').find('.atributo-grupo').remove();
                $('#atributos-chips-container').append(html);
            }

            function recolectarValoresSeleccionados() {
                return $('.atributo-radio:checked').map(function () {
                    return parseInt($(this).val());
                }).get();
            }

            function actualizarCodigoYPrecio() {
                var tipoId = $('#tipo-producto-field').val();
                var telaId = $('#tela-field').val();

                if (!tipoId) {
                    $('#codigo-field').val('');
                    $('#btn-aplicar-sugerido').hide();
                    $('#precio-breakdown').hide();
                    return;
                }

                // Sugerencia de precio (depende de tipo + tela)
                $.getJSON("{{ route('productos.sugerir-precio') }}", {
                    tipo_producto_id: tipoId,
                    insumo_tela_id: telaId || null
                }).done(function (resp) {
                    if (resp.precio_sugerido > 0) {
                        $('#sugerido-label').text('Sugerido: $' + parseFloat(resp.precio_sugerido).toFixed(2));
                        $('#btn-aplicar-sugerido').show().data('valor', resp.precio_sugerido);
                        $('#precio-breakdown-text').html(
                            'Tela: $' + parseFloat(resp.costo_tela).toFixed(2) +
                            ' + Confección: $' + parseFloat(resp.precio_confeccion).toFixed(2) +
                            ' = <strong>$' + parseFloat(resp.precio_sugerido).toFixed(2) + '</strong>'
                        );
                        $('#precio-breakdown').show();
                    } else {
                        $('#btn-aplicar-sugerido').hide();
                        $('#precio-breakdown').hide();
                    }
                });

                // Vista previa SKU (depende de tipo + tela + atributos seleccionados)
                if ($('#id-field').val()) return; // En edición no recalculamos automáticamente
                var valoresIds = recolectarValoresSeleccionados();
                $.getJSON("{{ route('productos.preview-codigo') }}", {
                    tipo_producto_id: tipoId,
                    insumo_tela_id: telaId || null,
                    'atributo_valor_ids[]': valoresIds
                }).done(function (resp) {
                    $('#codigo-field').val(resp.codigo);
                });
            }

            function escapeHtml(s) {
                return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            }

            // Listeners
            $('#tipo-producto-field').on('change', function () {
                cargarAtributosDeTipo($(this).val()).then(actualizarCodigoYPrecio);
            });
            $('#tela-field').on('change', actualizarCodigoYPrecio);
            $(document).on('change', '.atributo-radio', actualizarCodigoYPrecio);

            // Aplicar precio sugerido
            $('#btn-aplicar-sugerido').on('click', function () {
                var v = $(this).data('valor');
                if (v) $('#precio-base-field').val(parseFloat(v).toFixed(2)).trigger('blur');
            });

            // Enviar formulario
            $("#productoForm").on("submit", function (e) {
                e.preventDefault();

                if (!validarFormularioProducto()) {
                    return;
                }
                var id = $("#id-field").val();
                var url = id ? "{{ route('productos.update', ':id') }}".replace(':id', id) : "{{ route('productos.store') }}";
                var method = id ? "PUT" : "POST";

                var formData = new FormData(this);
                if (method === "PUT") {
                    formData.append('_method', 'PUT');
                }

                // Adjuntar valores de atributos seleccionados (uno por radio group)
                recolectarValoresSeleccionados().forEach(function (vid) {
                    formData.append('atributo_valor_ids[]', vid);
                });

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
                    text: "El producto será inhabilitado y moverá al historial.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, inhabilitar',
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
                            url: "{{ route('productos.destroy', ':id') }}".replace(':id', id),
                            method: "DELETE",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                table.draw();
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Inhabilitado!',
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
                                    text: 'No se pudo inhabilitar el producto',
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

            // ══════════════════════════════════════════════════════
            // RESTAURAR — SoftDelete Restore (Patrón Maestro S-08)
            // ══════════════════════════════════════════════════════
            $(document).on("click", ".restore-item-btn", function () {
                var id = $(this).data("id");
                Swal.fire({
                    title: '¿Restaurar registro?',
                    text: "¿Estás seguro de que deseas restaurar este producto?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, restaurar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-success w-xs me-2',
                        cancelButton: 'btn btn-light w-xs'
                    },
                    buttonsStyling: false,
                    showCloseButton: true
                }).then(function (result) {
                    if (result.value) {
                        $.ajax({
                            url: "{{ url('productos') }}/" + id + "/restore",
                            method: "POST",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                table.draw();
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Restaurado!',
                                    text: response.success,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'No se pudo restaurar el producto'
                                });
                            }
                        });
                    }
                });
            });

            // Limpiar modal al cerrar
            $("#showModal").on("hidden.bs.modal", function () {
                $("#modalTitle").text("Agregar Producto");
                $("#productoForm")[0].reset();
                $("#id-field").val("");
                $("#codigo-field").val("");
                $("#tela-field").val("");
                $("#imagen-preview").hide();
                $("#imagen-field").prop('required', true);
                $("#imagen-required-star").removeClass('d-none');
                $("#estado-switch").prop('checked', true);
                $("#estado-hidden-field").val("1");
                $("#estado-label").text("Activo");
                $("#add-btn").show();
                $("#edit-btn").hide();
                $('#productoForm').find('input, select, textarea').removeClass('is-invalid is-valid');
                $('#productoForm').find('.invalid-feedback').hide();
                // Reset variantes
                atributosDelTipoActual = [];
                $('#atributos-chips-container').find('.atributo-grupo').remove();
                $('#atributos-empty').show().text('Selecciona un tipo de producto para ver sus variaciones.');
                $('#btn-aplicar-sugerido').hide();
                $('#precio-breakdown').hide();
            });

            function validarFormularioProducto() {
                let esValido = true;

                let $tipo = $('#tipo-producto-field');
                if (!$tipo.val()) {
                    marcarInvalido($tipo, 'El tipo de producto es obligatorio.');
                    esValido = false;
                } else { marcarValido($tipo); }

                let $desc = $('#descripcion-field');
                let desc = $desc.val().trim();
                if (!desc) {
                    marcarInvalido($desc, 'La descripción es obligatoria.');
                    esValido = false;
                } else if (desc.length < 10) {
                    marcarInvalido($desc, 'La descripción debe tener al menos 10 caracteres.');
                    esValido = false;
                } else { marcarValido($desc); }

                let $precio = $('#precio-base-field');
                let precio = parseFloat($precio.val());
                if (isNaN(precio) || precio <= 0) {
                    marcarInvalido($precio, 'El precio base debe ser mayor a cero.');
                    esValido = false;
                } else { marcarValido($precio); }

                let esCreacion = $('#id-field').val() === '';
                if (esCreacion) {
                    let $imagen = $('#imagen-field');
                    if (!$imagen[0].files || $imagen[0].files.length === 0) {
                        marcarInvalido($imagen, 'La imagen es obligatoria al crear un producto.');
                        esValido = false;
                    } else { marcarValido($imagen); }
                }

                return esValido;
            }

            function validarFormularioTipo() {
                let esValido = true;

                let $nombre = $('#tipo-nombre-field');
                let nombre = $nombre.val().trim();
                if (!nombre) {
                    marcarInvalido($nombre, 'El nombre del tipo es obligatorio.');
                    esValido = false;
                } else if (nombre.length < 2) {
                    marcarInvalido($nombre, 'El nombre debe tener al menos 2 caracteres.');
                    esValido = false;
                } else { marcarValido($nombre); }

                let $prefijo = $('#tipo-prefijo-field');
                let prefijo = $prefijo.val().trim();
                if (!prefijo) {
                    marcarInvalido($prefijo, 'El código prefijo es obligatorio.');
                    esValido = false;
                } else if (!/^[a-zA-Z]+$/.test(prefijo)) {
                    marcarInvalido($prefijo, 'El código prefijo solo puede contener letras.');
                    esValido = false;
                } else { marcarValido($prefijo); }

                return esValido;
            }
            let tiposHistorial = false;
            let tiposTable = null;

            // ===============================
            // Funciones para Tipos de Producto
            // ===============================

            // Cargar tipos al abrir modal de gestión
            $("#tiposModal").on("show.bs.modal", function () {
                actualizarVistaTipos();
                recargarTipos();
            });

            $("#tiposModal").on("shown.bs.modal", function () {
                if (tiposTable) {
                    tiposTable.columns.adjust().draw(false);
                }
            });

            function tiposUrl() {
                return "{{ route('tipo-productos.index') }}" + (tiposHistorial ? '?historial=true' : '');
            }

            function actualizarVistaTipos() {
                $("#btn-tipos-activos").toggleClass('active', !tiposHistorial);
                $("#btn-tipos-historial").toggleClass('active', tiposHistorial);
                $("#add-tipo-btn").toggle(!tiposHistorial);
            }

            function inicializarTiposTable() {
                tiposTable = $("#tipos-table").DataTable({
                    processing: true,
                    autoWidth: false,
                    scrollX: false,
                    ajax: {
                        url: tiposUrl(),
                        dataSrc: ''
                    },
                    columns: [
                        { data: 'nombre' },
                        {
                            data: 'prefijo',
                            render: function (data) {
                                return `<span class="badge bg-secondary">${data}</span>`;
                            }
                        },
                        {
                            data: 'productos_count',
                            render: function (data) {
                                return `<span class="badge bg-info">${data}</span>`;
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: function (data, type, row) {
                                if (tiposHistorial) {
                                    return `
                                        <button class="btn btn-sm btn-outline-success restore-tipo-btn" data-id="${row.id}" title="Restaurar">
                                            <i class="ri-refresh-line"></i>
                                        </button>
                                    `;
                                }

                                return `
                                    <button class="btn btn-sm btn-outline-primary edit-tipo-btn" 
                                        data-id="${row.id}" 
                                        data-nombre="${row.nombre}" 
                                        data-prefijo="${row.prefijo}"
                                        data-descripcion="${row.descripcion || ''}">
                                        <i class="ri-pencil-line"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-tipo-btn" data-id="${row.id}" title="Inhabilitar">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                `;
                            }
                        }
                    ],
                    order: [[0, 'asc']],
                    dom: 'rtip',
                    language: lenguajeData
                });
            }

            function recargarTipos() {
                if (!tiposTable) {
                    inicializarTiposTable();
                    return;
                }

                tiposTable.ajax.url(tiposUrl()).load(function () {
                    tiposTable.columns.adjust().draw(false);
                });
            }

            $("#btn-tipos-activos").on('click', function () {
                tiposHistorial = false;
                actualizarVistaTipos();
                recargarTipos();
            });

            $("#btn-tipos-historial").on('click', function () {
                tiposHistorial = true;
                actualizarVistaTipos();
                recargarTipos();
            });

            // ══════════════════════════════════════════════════════
            // VALIDACIONES ONBLUR — Formulario Producto principal
            // ══════════════════════════════════════════════════════

            // Tipo de Producto — select obligatorio
            $(document).on('blur', '#tipo-producto-field', function () {
                if (!$(this).val()) {
                    marcarInvalido($(this), 'El tipo de producto es obligatorio.');
                } else {
                    marcarValido($(this));
                }
            });

            // Descripción — mín. 10 chars
            $(document).on('blur', '#descripcion-field', function () {
                let val = $(this).val().trim();
                if (!val) {
                    marcarInvalido($(this), 'La descripción es obligatoria.');
                } else if (val.length < 10) {
                    marcarInvalido($(this), 'Mínimo 10 caracteres.');
                } else {
                    marcarValido($(this));
                }
            });

            // Precio Base — mayor a cero
            $(document).on('blur', '#precio-base-field', function () {
                let val = parseFloat($(this).val());
                if (isNaN(val) || val <= 0) {
                    marcarInvalido($(this), 'El precio base debe ser mayor a cero.');
                } else {
                    marcarValido($(this));
                }
            });

            // Imagen — formato y tamaño (solo en creación)
            $(document).on('change', '#imagen-field', function () {
                let esCreacion = $('#id-field').val() === '';
                let file = this.files[0];
                if (!file) {
                    if (esCreacion) marcarInvalido($(this), 'La imagen es obligatoria.');
                    return;
                }
                let tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!tiposPermitidos.includes(file.type)) {
                    marcarInvalido($(this), 'Formato no permitido. Use JPG, PNG o GIF.');
                    return;
                }
                if (file.size > 2048 * 1024) {
                    marcarInvalido($(this), 'La imagen no puede superar 2MB.');
                    return;
                }
                marcarValido($(this));
            });

            // ══════════════════════════════════════════════════════
            // VALIDACIONES AJAX onblur — Tipos de Producto

            // 1. Nombre
            $('#tipo-nombre-field').on('blur', function () {
                let value = $(this).val().trim();
                let $input = $(this);
                let isEdit = $('#tipo-id-field').val() !== '';

                if (value.length === 0) {
                    marcarInvalido($input, 'El nombre del tipo es obligatorio.');
                    return;
                }
                if (value.length < 2) {
                    marcarInvalido($input, 'El nombre debe tener al menos 2 caracteres.');
                    return;
                }
                if (isEdit) {
                    marcarValido($input);
                    return;
                }
                $.get("{{ route('tipo-productos.check-nombre') }}", { nombre: value }, function (res) {
                    if (res.exists) {
                        marcarInvalido($input, 'Este nombre ya está registrado.');
                        $('#save-tipo-btn').prop('disabled', true);
                    } else {
                        marcarValido($input);
                        $('#save-tipo-btn').prop('disabled', false);
                    }
                });
            });

            // 2. Prefijo — sanitizar a mayúsculas en tiempo real
            $(document).on('input', '#tipo-prefijo-field', function () {
                this.value = this.value.replace(/[^a-zA-Z]/g, '').toUpperCase();
            });

            $('#tipo-prefijo-field').on('blur', function () {
                let $input = $(this);
                let value = $input.val().trim();
                let isEdit = $('#tipo-id-field').val() !== '';

                if (!value) {
                    marcarInvalido($input, 'El código prefijo es obligatorio.');
                    return;
                }
                if (!/^[a-zA-Z]+$/.test(value)) {
                    marcarInvalido($input, 'El código prefijo solo puede contener letras.');
                    return;
                }
                if (isEdit) { marcarValido($input); return; }

                $.get("{{ route('tipo-productos.check-codigo') }}", { codigo: value }, function (res) {
                    if (res.exists) {
                        marcarInvalido($input, 'Este prefijo ya está registrado.');
                        $('#save-tipo-btn').prop('disabled', true);
                    } else {
                        marcarValido($input);
                        $('#save-tipo-btn').prop('disabled', false);
                    }
                });
            });

            // Abrir addTipoModal sin data-bs-toggle para no cerrar el padre
            $('#btn-add-tipo-inline, #add-tipo-btn').on('click', function () {
                $('#addTipoModal').modal('show');
            });

            // ==== Atributos disponibles (cache para el modal de Tipo) ====
            var atributosDisponibles = [];

            function cargarAtributosDisponibles() {
                return $.getJSON("{{ route('atributos.index') }}").done(function (rows) {
                    atributosDisponibles = rows;
                });
            }

            function renderAtributosLista(seleccionados) {
                // seleccionados: array [{id, orden}] | undefined
                var seleccionadosMap = {};
                (seleccionados || []).forEach(function (s) {
                    seleccionadosMap[s.id] = s.orden;
                });

                if (!atributosDisponibles.length) {
                    $('#tipo-atributos-list').html(
                        '<div class="text-muted small text-center py-2">No hay atributos definidos. ' +
                        'Crea atributos en <a href="{{ url('atributos') }}" target="_blank">/atributos</a> antes de asociarlos.</div>'
                    );
                    return;
                }

                var html = atributosDisponibles.map(function (a) {
                    var checked = seleccionadosMap.hasOwnProperty(a.id);
                    var orden = checked ? seleccionadosMap[a.id] : '';
                    return '' +
                        '<div class="d-flex align-items-center gap-2 p-2 border rounded tipo-atr-row" data-atr-id="' + a.id + '">' +
                            '<div class="form-check flex-grow-1 mb-0">' +
                                '<input class="form-check-input tipo-atr-check" type="checkbox" id="tipo-atr-' + a.id + '"' + (checked ? ' checked' : '') + '>' +
                                '<label class="form-check-label" for="tipo-atr-' + a.id + '">' +
                                    '<strong>' + escapeHtml(a.nombre) + '</strong> ' +
                                    '<span class="badge bg-light text-muted ms-1">' + escapeHtml(a.codigo) + '</span>' +
                                '</label>' +
                            '</div>' +
                            '<div style="width: 90px;">' +
                                '<input type="number" class="form-control form-control-sm tipo-atr-orden" min="1" max="99" placeholder="orden" value="' + orden + '"' + (checked ? '' : ' disabled') + '>' +
                            '</div>' +
                        '</div>';
                }).join('');

                $('#tipo-atributos-list').html(html);
            }

            // Toggle del input de orden cuando se marca/desmarca
            $(document).on('change', '.tipo-atr-check', function () {
                var $row = $(this).closest('.tipo-atr-row');
                var $orden = $row.find('.tipo-atr-orden');
                if (this.checked) {
                    $orden.prop('disabled', false);
                    if (!$orden.val()) {
                        // Auto-asignar siguiente orden disponible
                        var ordenes = $('.tipo-atr-check:checked').map(function () {
                            return parseInt($(this).closest('.tipo-atr-row').find('.tipo-atr-orden').val()) || 0;
                        }).get();
                        var max = Math.max.apply(null, [0].concat(ordenes));
                        $orden.val(max + 1);
                    }
                } else {
                    $orden.prop('disabled', true).val('');
                }
            });

            function recolectarAtributosSeleccionados() {
                var sel = [];
                $('.tipo-atr-check:checked').each(function () {
                    var $row = $(this).closest('.tipo-atr-row');
                    var id = parseInt($row.data('atr-id'));
                    var orden = parseInt($row.find('.tipo-atr-orden').val()) || 1;
                    sel.push({ id: id, orden: orden });
                });
                return sel;
            }

            function escapeHtml(s) {
                return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            }

            // ════════════════════════════════════════════════════════════
            // Insumos por defecto (template para órdenes de producción)
            // ════════════════════════════════════════════════════════════
            // Catálogo de insumos disponibles (passed from controller)
            var insumosCatalogo = @json($insumosDisponibles ?? []);
            var tipoInsumosState = []; // [{ id, nombre, unidad, cantidad }]

            function renderTipoInsumos() {
                var $list  = $('#tipo-insumos-list');
                var $empty = $('#tipo-insumos-empty');
                if (!tipoInsumosState.length) {
                    $list.empty();
                    $empty.show();
                    return;
                }
                $empty.hide();
                var optionsHtml = insumosCatalogo.map(function (i) {
                    return '<option value="' + i.id + '" data-nombre="' + escapeHtml(i.nombre) + '" data-unidad="' + escapeHtml(i.unidad_medida || '') + '">' +
                           escapeHtml(i.nombre) + ' (' + escapeHtml(i.unidad_medida || '') + ')</option>';
                }).join('');
                $list.html(tipoInsumosState.map(function (it, idx) {
                    return '<div class="row g-2 align-items-center tipo-insumo-row" data-idx="' + idx + '">' +
                        '<div class="col-md-7">' +
                            '<select class="form-select form-select-sm tipo-insumo-select">' +
                                '<option value="">Seleccione insumo...</option>' + optionsHtml +
                            '</select>' +
                        '</div>' +
                        '<div class="col-md-3">' +
                            '<input type="number" class="form-control form-control-sm tipo-insumo-cantidad" step="0.01" min="0.01" placeholder="Por unidad" title="Cantidad por unidad producida" value="' + (it.cantidad || '') + '">' +
                        '</div>' +
                        '<div class="col-md-2 d-grid">' +
                            '<button type="button" class="btn btn-sm btn-soft-danger tipo-insumo-remove" title="Quitar"><i class="ri-delete-bin-line"></i></button>' +
                        '</div>' +
                    '</div>';
                }).join(''));
                // Setear los valores seleccionados después de render
                tipoInsumosState.forEach(function (it, idx) {
                    $('.tipo-insumo-row[data-idx="' + idx + '"] .tipo-insumo-select').val(it.id || '');
                });
            }

            // Sincroniza el estado desde los inputs visibles (antes de submit/cambios)
            function sincronizarTipoInsumosState() {
                tipoInsumosState = [];
                $('#tipo-insumos-list .tipo-insumo-row').each(function () {
                    var $row = $(this);
                    var $sel = $row.find('.tipo-insumo-select');
                    var id   = parseInt($sel.val(), 10);
                    var cant = parseFloat($row.find('.tipo-insumo-cantidad').val());
                    if (!id || isNaN(cant) || cant <= 0) return; // descartar filas vacías
                    var $opt = $sel.find('option:selected');
                    tipoInsumosState.push({
                        id: id,
                        nombre: $opt.data('nombre') || '',
                        unidad: $opt.data('unidad') || '',
                        cantidad: cant
                    });
                });
            }

            // Agregar fila vacía
            $(document).on('click', '#tipo-insumo-add-btn', function () {
                tipoInsumosState.push({ id: null, nombre: '', unidad: '', cantidad: '' });
                renderTipoInsumos();
                // foco en el último select
                $('#tipo-insumos-list .tipo-insumo-row:last .tipo-insumo-select').trigger('focus');
            });

            // Quitar fila
            $(document).on('click', '.tipo-insumo-remove', function () {
                var idx = parseInt($(this).closest('.tipo-insumo-row').data('idx'), 10);
                tipoInsumosState.splice(idx, 1);
                renderTipoInsumos();
            });

            // Cambios in-place (no rerenderear para no perder foco)
            $(document).on('change', '.tipo-insumo-select', function () {
                var idx = parseInt($(this).closest('.tipo-insumo-row').data('idx'), 10);
                var $opt = $(this).find('option:selected');
                tipoInsumosState[idx].id     = parseInt($(this).val(), 10) || null;
                tipoInsumosState[idx].nombre = $opt.data('nombre') || '';
                tipoInsumosState[idx].unidad = $opt.data('unidad') || '';
            });
            $(document).on('input', '.tipo-insumo-cantidad', function () {
                var idx = parseInt($(this).closest('.tipo-insumo-row').data('idx'), 10);
                tipoInsumosState[idx].cantidad = parseFloat($(this).val()) || '';
            });

            // Toggle visibilidad del campo consumo_tela según requiere_tela
            function aplicarTipoConsumoTelaVisibility() {
                $('#tipo-consumo-tela-row').toggle($('#tipo-requiere-tela').is(':checked'));
            }
            $(document).on('change', '#tipo-requiere-tela', aplicarTipoConsumoTelaVisibility);

            // Cargar atributos al abrir el modal (siempre refresca por si se agregaron en otra pestaña)
            $("#addTipoModal").on("show.bs.modal", function () {
                $('#tipo-atributos-empty').show();
                aplicarTipoConsumoTelaVisibility();
                cargarAtributosDisponibles().then(function () {
                    var idEdit = $("#tipo-id-field").val();
                    // En modo crear: render con cero seleccionados; en edición se rellena en el handler.
                    if (!idEdit) {
                        renderAtributosLista([]);
                        tipoInsumosState = [];
                        renderTipoInsumos();
                    }
                });
            });

            // Limpiar validaciones al cerrar modal de tipo
            $("#addTipoModal").on("hidden.bs.modal", function () {
                $('#tipoForm')[0].reset();
                $('#tipo-id-field').val('');
                $('#tipo-precio-confeccion').val('');
                $('#tipo-requiere-tela').prop('checked', true);
                $('#tipo-consumo-tela').val('');
                aplicarTipoConsumoTelaVisibility();
                $('#tipo-atributos-list').html('');
                tipoInsumosState = [];
                renderTipoInsumos();
                $('#tipoModalTitle').html('<i class="ri-add-line me-2"></i>Agregar Tipo de Producto');
                $('.is-invalid').removeClass('is-invalid');
                $('.is-valid').removeClass('is-valid');
                $('.invalid-feedback').hide();
                $('#save-tipo-btn').prop('disabled', false);
            });

            // Editar tipo: cargar datos completos vía GET (incluye atributos asociados)
            $(document).on("click", ".edit-tipo-btn", function () {
                var id = $(this).data("id");

                $.getJSON("{{ url('tipo-productos') }}/" + id, function (tipo) {
                    $("#tipo-id-field").val(tipo.id);
                    $("#tipo-nombre-field").val(tipo.nombre);
                    $("#tipo-prefijo-field").val(tipo.prefijo);
                    $("#tipo-descripcion-field").val(tipo.descripcion || '');
                    $("#tipo-precio-confeccion").val(tipo.precio_confeccion || 0);
                    $("#tipo-requiere-tela").prop('checked', !!tipo.requiere_tela);
                    $("#tipo-consumo-tela").val(tipo.consumo_tela_por_unidad || 0);
                    aplicarTipoConsumoTelaVisibility();
                    $("#tipoModalTitle").html('<i class="ri-pencil-line me-2"></i>Editar Tipo de Producto');

                    var asociados = (tipo.atributos || []).map(function (a) {
                        return { id: a.id, orden: a.pivot ? a.pivot.orden : 1 };
                    });

                    // Hidratar insumos por defecto desde el tipo
                    tipoInsumosState = (tipo.insumos_default || []).map(function (i) {
                        return {
                            id: i.id,
                            nombre: i.nombre,
                            unidad: i.unidad_medida || '',
                            cantidad: parseFloat(i.pivot && i.pivot.cantidad_estimada) || ''
                        };
                    });

                    $("#tiposModal").modal('hide');
                    $("#addTipoModal").modal('show');

                    // Esperar a que termine la carga de atributos disponibles antes de renderizar
                    cargarAtributosDisponibles().then(function () {
                        renderAtributosLista(asociados);
                        renderTipoInsumos();
                    });
                });
            });

            // Eliminar tipo
            $(document).on("click", ".delete-tipo-btn", function () {
                var id = $(this).data("id");

                Swal.fire({
                    title: '¿Seguro que desea inhabilitar?',
                    text: "Solo se puede inhabilitar si no tiene productos asociados",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, inhabilitar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-primary w-xs me-2',
                        cancelButton: 'btn btn-danger w-xs'
                    },
                    buttonsStyling: false,
                    showCloseButton: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('tipo-productos') }}/" + id,
                            method: "DELETE",
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            success: function (response) {
                                if (tiposTable) {
                                    tiposTable.ajax.reload(null, false);
                                }
                                actualizarSelectTipos();
                                Swal.fire({
                                    title: 'Inhabilitado',
                                    text: response.message,
                                    icon: 'success',
                                    customClass: {
                                        confirmButton: 'btn btn-primary w-xs me-2',
                                        cancelButton: 'btn btn-danger w-xs'
                                    },
                                    buttonsStyling: false,
                                    showCloseButton: true
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    title: 'Error',
                                    text: xhr.responseJSON.message,
                                    icon: 'error',
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

            // Restaurar tipo
            $(document).on("click", ".restore-tipo-btn", function () {
                var id = $(this).data("id");

                Swal.fire({
                    title: '¿Seguro que desea restaurar este registro?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, restaurar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-primary w-xs me-2',
                        cancelButton: 'btn btn-danger w-xs'
                    },
                    buttonsStyling: false,
                    showCloseButton: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('tipo-productos') }}/" + id + "/restore",
                            method: "PATCH",
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            success: function (response) {
                                if (tiposTable) {
                                    tiposTable.ajax.reload();
                                }
                                actualizarSelectTipos();
                                Swal.fire({
                                    title: 'Restaurado',
                                    text: response.message,
                                    icon: 'success',
                                    customClass: {
                                        confirmButton: 'btn btn-primary w-xs me-2',
                                        cancelButton: 'btn btn-danger w-xs'
                                    },
                                    buttonsStyling: false,
                                    showCloseButton: true
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    title: 'Error',
                                    text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'No se pudo restaurar el tipo de producto',
                                    icon: 'error',
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

            // Guardar tipo
            $("#tipoForm").on("submit", function (e) {
                e.preventDefault();

                if (!validarFormularioTipo()) {
                    return;
                }

                var id = $("#tipo-id-field").val();
                var url = id ? "{{ url('tipo-productos') }}/" + id : "{{ route('tipo-productos.store') }}";
                var method = id ? "PUT" : "POST";

                // Sincronizar estado de insumos desde los inputs visibles antes de enviar
                sincronizarTipoInsumosState();

                $.ajax({
                    url: url,
                    method: method,
                    data: {
                        nombre: $("#tipo-nombre-field").val(),
                        prefijo: $("#tipo-prefijo-field").val().toUpperCase(),
                        descripcion: $("#tipo-descripcion-field").val(),
                        precio_confeccion: parseFloat($("#tipo-precio-confeccion").val()) || 0,
                        requiere_tela: $("#tipo-requiere-tela").is(':checked') ? 1 : 0,
                        consumo_tela_por_unidad: parseFloat($("#tipo-consumo-tela").val()) || 0,
                        atributos: recolectarAtributosSeleccionados(),
                        insumos_default: tipoInsumosState.map(function (it) {
                            return { id: it.id, cantidad_estimada: it.cantidad };
                        })
                    },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        $("#addTipoModal").modal('hide');

                        if (tiposTable) {
                            tiposTable.ajax.reload(null, false);
                        }

                        // Actualizar select de tipos
                        actualizarSelectTipos();

                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
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
                        var errors = xhr.responseJSON.errors || {};
                        var message = xhr.responseJSON.message || 'Error al guardar';
                        Swal.fire({
                            title: 'Error',
                            text: message,
                            icon: 'error',
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

            // Actualizar select de tipos después de agregar uno nuevo
            function actualizarSelectTipos() {
                $.get("{{ route('tipo-productos.index') }}", function (tipos) {
                    var select = $("#tipo-producto-field");
                    select.find("option:not(:first)").remove();

                    tipos.forEach(function (tipo) {
                        select.append(`<option value="${tipo.id}" data-prefijo="${tipo.prefijo}">${tipo.nombre}</option>`);
                    });
                });
            }

            // Limpiar modal de tipo al cerrar
            $("#addTipoModal").on("hidden.bs.modal", function () {
                $("#tipoForm")[0].reset();
                $("#tipo-id-field").val("");
                $("#tipoModalTitle").html('<i class="ri-add-line me-2"></i>Agregar Tipo de Producto');
            });
        });

        // PDF Export Modal — Productos
        $('#btn-generar-pdf').on('click', function () {
            var baseUrl  = '{{ route('productos.reporte.pdf') }}';
            var params   = [];
            var tipo     = $('#pdf-filter-tipo').val();
            var estatus  = $('#pdf-filter-estatus').val();
            if (tipo)    params.push('tipo_producto=' + encodeURIComponent(tipo));
            if (estatus !== '') params.push('estatus=' + encodeURIComponent(estatus));
            window.open(baseUrl + (params.length ? '?' + params.join('&') : ''), '_blank');
            bootstrap.Modal.getInstance(document.getElementById('pdfExportModal'))?.hide();
        });
        $('#pdfExportModal').on('show.bs.modal', function () {
            $('#pdf-filter-tipo').val('');
            $('#pdf-filter-estatus').val('');
        });
    </script>
@endpush