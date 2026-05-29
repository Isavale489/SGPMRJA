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
                <h4 class="mb-sm-0">Gestión de Proveedores</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Gestión General</a></li>
                        <li class="breadcrumb-item active">Proveedores</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>

    {{-- Estilos en public/assets/css/custom.css — sección "MÓDULO MAESTROS — Proveedores" --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-maestros">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Listado de Proveedores</h5>
                        <div class="flex-shrink-0 d-flex align-items-center gap-3">
                            <!-- Toggle Historial -->
                            @if($historial)
                                <a href="{{ route('proveedores.index') }}" class="btn-historial btn-historial-volver">
                                    <i class="ri-arrow-left-line"></i> Solo Activos
                                </a>
                            @else
                                <a href="{{ route('proveedores.index', ['historial' => true]) }}"
                                    class="btn-historial btn-historial-ver">
                                    <i class="ri-time-line"></i> Ver Historial
                                </a>
                            @endif
                            <div class="d-flex gap-2">
                                @if(Auth::user()->isAdmin() && !$historial)
                                    <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn"
                                        data-bs-target="#showModal">
                                        <i class="ri-add-line align-bottom me-1"></i> Agregar Proveedor
                                    </button>
                                @endif
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
                    Réplica exacta del patrón de Clientes.
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
                                    placeholder="Buscar proveedor..."
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
                                    {{-- Filtro 1: Tipo de Proveedor --}}
                                    <div>
                                        <label class="navy-filter-label" for="filter-tipo-proveedor">
                                            <i class="ri-user-settings-line"></i> Tipo de Proveedor
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-tipo-proveedor">
                                            <option value="">Todos</option>
                                            <option value="natural">Natural</option>
                                            <option value="juridico">Jurídico</option>
                                        </select>
                                    </div>
                                    {{-- Filtro 2: Estatus --}}
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
                                    {{-- Filtro 3: Estado Territorial (Venezuela) --}}
                                    <div>
                                        <label class="navy-filter-label" for="filter-estado-territorial">
                                            <i class="ri-map-pin-line"></i> Estado
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-estado-territorial">
                                            <option value="">Todos</option>
                                            <option value="Amazonas">Amazonas</option>
                                            <option value="Anzoátegui">Anzoátegui</option>
                                            <option value="Apure">Apure</option>
                                            <option value="Aragua">Aragua</option>
                                            <option value="Barinas">Barinas</option>
                                            <option value="Bolívar">Bolívar</option>
                                            <option value="Carabobo">Carabobo</option>
                                            <option value="Cojedes">Cojedes</option>
                                            <option value="Delta Amacuro">Delta Amacuro</option>
                                            <option value="Distrito Capital">Distrito Capital</option>
                                            <option value="Falcón">Falcón</option>
                                            <option value="Guárico">Guárico</option>
                                            <option value="La Guaira">La Guaira</option>
                                            <option value="Lara">Lara</option>
                                            <option value="Mérida">Mérida</option>
                                            <option value="Miranda">Miranda</option>
                                            <option value="Monagas">Monagas</option>
                                            <option value="Nueva Esparta">Nueva Esparta</option>
                                            <option value="Portuguesa">Portuguesa</option>
                                            <option value="Sucre">Sucre</option>
                                            <option value="Táchira">Táchira</option>
                                            <option value="Trujillo">Trujillo</option>
                                            <option value="Yaracuy">Yaracuy</option>
                                            <option value="Zulia">Zulia</option>
                                        </select>
                                    </div>
                                    {{-- Filtro 4: Ordenar por --}}
                                    <div>
                                        <label class="navy-filter-label" for="filter-orden">
                                            <i class="ri-sort-asc"></i> Ordenar por
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-orden">
                                            <option value="recientes">Más recientes primero</option>
                                            <option value="antiguos">Más antiguos primero</option>
                                            <option value="nombre_asc">Nombre (A-Z)</option>
                                            <option value="nombre_desc">Nombre (Z-A)</option>
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

                    <table id="proveedores-table"
                        class="table table-bordered table-striped table-sm align-middle table-operativa table-maestro">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Nombre/Razón Social</th>
                                <th>Tipo</th>
                                <th>Teléfono</th>
                                <th>Email</th>
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

    <!-- Modal para ver detalles -->
    <div class="modal fade atlantico-modal" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title">Detalles del Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Columna Izquierda: Datos del Proveedor -->
                        <div class="col-lg-6">
                            <!-- Card Datos del Proveedor -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header border-0" style="background: rgba(30, 60, 114, 0.1);">
                                    <h6 class="mb-0" style="color: #1e3c72;">
                                        <i class="ri-store-2-line me-2"></i>Datos del Proveedor
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        {{-- Proveedor Natural: Nombre + Apellido en 2 columnas --}}
                                        <div class="col-6" id="view-block-prov-nombre">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-user-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Nombre</small>
                                                    <span class="fw-semibold" id="view-nombre">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6" id="view-block-prov-apellido">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-user-follow-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Apellido</small>
                                                    <span class="fw-semibold" id="view-apellido">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Proveedor Jurídico: Razón Social en ancho completo --}}
                                        <div class="col-12 d-none" id="view-block-prov-razon-social">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-building-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Razón Social</small>
                                                    <span class="fw-semibold" id="view-razon-social">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Tipo: siempre visible --}}
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-user-settings-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Tipo</small>
                                                    <span class="fw-semibold" id="view-tipo">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Documento: label dinámico (Cédula / RIF) --}}
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-bank-card-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block"
                                                        id="view-label-documento">Documento</small>
                                                    <span class="fw-semibold" id="view-documento">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Ubicación -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header border-0" style="background: rgba(30, 60, 114, 0.1);">
                                    <h6 class="mb-0" style="color: #1e3c72;">
                                        <i class="ri-map-pin-line me-2"></i>Ubicación
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-home-4-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Dirección</small>
                                                    <span class="fw-semibold" id="view-direccion">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Contacto y Estado -->
                        <div class="col-lg-6">
                            <!-- Card Contacto -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header border-0" style="background: rgba(30, 60, 114, 0.1);">
                                    <h6 class="mb-0" style="color: #1e3c72;">
                                        <i class="ri-contacts-line me-2"></i>Información de Contacto
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-mail-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Email</small>
                                                    <span class="fw-semibold" id="view-email">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-phone-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Teléfono</small>
                                                    <span class="fw-semibold" id="view-telefono">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12" id="view-contacto-section">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-user-follow-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Persona de Contacto</small>
                                                    <span class="fw-semibold" id="view-contacto">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12" id="view-telefono-contacto-section">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-smartphone-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Teléfono de Contacto</small>
                                                    <span class="fw-semibold" id="view-telefono-contacto">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Estado -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header border-0" style="background: rgba(30, 60, 114, 0.1);">
                                    <h6 class="mb-0" style="color: #1e3c72;">
                                        <i class="ri-information-line me-2"></i>Registro
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
                                                    <small class="text-muted d-block">Fecha de Registro</small>
                                                    <span class="fw-semibold" id="view-created">-</span>
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
                                                    <small class="text-muted d-block">Estatus</small>
                                                    <span class="fw-semibold" id="view-estatus">-</span>
                                                </div>
                                            </div>
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
                    <h5 class="modal-title" id="modalTitle">Agregar Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="proveedorForm" novalidate>
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="id-field" />

                        <div class="modal-form-section">
                            <div class="modal-form-section-title"><i class="ri-fingerprint-line"></i>Identificación</div>

                            <div class="row mb-0">
                                {{-- Documento del proveedor: RIF si jurídico, Cédula si natural. Se togglea por JS según
                                tipo. --}}
                                <div class="col-md-6 js-tipo-juridico">
                                    <x-forms.input name="rif_number" label="RIF" id="rif-number-field"
                                        placeholder="Ej: 123456789" maxlength="9" required prependRaw="true">
                                        <x-slot:prepend>
                                            <select class="form-select" id="rif-prefix-field" style="max-width: 80px;">
                                                <option value="J-">J-</option>
                                                <option value="G-">G-</option>
                                            </select>
                                        </x-slot:prepend>
                                    </x-forms.input>
                                    <input type="hidden" id="rif-field" name="rif" />
                                </div>
                                <div class="col-md-6 js-tipo-natural" style="display: none;">
                                    <x-forms.input name="documento_identidad_number" label="Documento de Identidad"
                                        id="documento-identidad-field" maxlength="8" placeholder="Ej: 12345678" required
                                        prependRaw="true">
                                        <x-slot:prepend>
                                            <select class="form-select" id="tipo-documento-field" name="tipo_documento"
                                                style="max-width: 80px;">
                                                <option value="V-">V-</option>
                                                <option value="E-">E-</option>
                                            </select>
                                        </x-slot:prepend>
                                    </x-forms.input>
                                </div>
                                <div class="col-md-6">
                                    <x-forms.select name="tipo_proveedor" label="Tipo de Proveedor" required
                                        id="tipo-proveedor-field" :options="['juridico' => 'Jurídico (Empresa)', 'natural' => 'Natural (Persona)']" placeholder="" />
                                </div>
                            </div>
                        </div>

                        <!-- CAMPOS PARA PROVEEDOR JURÍDICO (EMPRESA) -->
                        <div id="campos-juridico">
                            <div class="modal-form-section">
                                <div class="modal-form-section-title"><i class="ri-building-line"></i>Datos Empresariales
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 mb-3">
                                        <x-forms.input name="razon_social" label="Razón Social" maxlength="200"
                                            placeholder="Nombre de la empresa" id="razon-social-field" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <x-forms.input name="direccion" label="Dirección" maxlength="500"
                                            placeholder="Dirección de la empresa" id="direccion-jur-field" />
                                    </div>
                                </div>
                            </div>

                            <div class="modal-form-section">
                                <div class="modal-form-section-title"><i class="ri-contacts-book-line"></i>Contacto</div>

                                <div class="row mb-0">
                                    <div class="col-md-6 mb-3">
                                        <x-forms.input name="telefono_jur_number" label="Teléfono"
                                            id="telefono-jur-number-field" maxlength="7" placeholder="1234567" required
                                            prependRaw="true">
                                            <x-slot:prepend>
                                                <select class="form-select" id="telefono-jur-prefix-field"
                                                    style="max-width: 100px; min-width: 100px;">
                                                    <option value="0212">0212</option>
                                                    <option value="0251">0251</option>
                                                    <option value="0241">0241</option>
                                                    <option value="0255">0255</option>
                                                    <option value="0412">0412</option>
                                                    <option value="0414">0414</option>
                                                    <option value="0424" selected>0424</option>
                                                    <option value="0416">0416</option>
                                                    <option value="0426">0426</option>
                                                </select>
                                            </x-slot:prepend>
                                        </x-forms.input>
                                        <input type="hidden" id="telefono-jur-field" name="telefono" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <x-forms.input name="email" label="Email" type="email"
                                            placeholder="correo@empresa.com" id="email-jur-field" />
                                    </div>
                                </div>
                            </div>

                            <div class="modal-form-section">
                                <div class="modal-form-section-title"><i class="ri-user-follow-line"></i>Contacto Secundario
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 mb-3">
                                        <x-forms.input name="contacto" label="Persona de Contacto" maxlength="100"
                                            placeholder="Nombre del contacto" id="contacto-field" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <x-forms.input name="telefono_contacto_number" label="Teléfono de Contacto"
                                            id="telefono-contacto-number-field" maxlength="7" placeholder="1234567"
                                            prependRaw="true">
                                            <x-slot:prepend>
                                                <select class="form-select" id="telefono-contacto-prefix-field"
                                                    style="max-width: 100px; min-width: 100px;">
                                                    <option value="0412">0412</option>
                                                    <option value="0414">0414</option>
                                                    <option value="0424" selected>0424</option>
                                                    <option value="0416">0416</option>
                                                    <option value="0426">0426</option>
                                                </select>
                                            </x-slot:prepend>
                                        </x-forms.input>
                                        <input type="hidden" id="telefono-contacto-field" name="telefono_contacto" />
                                    </div>
                                </div>
                            </div>

                            <div class="modal-form-section">
                                <div class="modal-form-section-title"><i class="ri-map-pin-2-line"></i>Ubicación</div>

                                <div class="row mb-0">
                                    <div class="col-md-6 mb-3">
                                        <label for="estado-territorial-jur-field" class="form-label">Estado</label>
                                        <select id="estado-territorial-jur-field" name="estado_territorial"
                                            class="form-select">
                                            <option value="">Seleccione estado</option>
                                            <option value="Amazonas">Amazonas</option>
                                            <option value="Anzoátegui">Anzoátegui</option>
                                            <option value="Apure">Apure</option>
                                            <option value="Aragua">Aragua</option>
                                            <option value="Barinas">Barinas</option>
                                            <option value="Bolívar">Bolívar</option>
                                            <option value="Carabobo">Carabobo</option>
                                            <option value="Cojedes">Cojedes</option>
                                            <option value="Delta Amacuro">Delta Amacuro</option>
                                            <option value="Distrito Capital">Distrito Capital</option>
                                            <option value="Falcón">Falcón</option>
                                            <option value="Guárico">Guárico</option>
                                            <option value="La Guaira">La Guaira</option>
                                            <option value="Lara">Lara</option>
                                            <option value="Mérida">Mérida</option>
                                            <option value="Miranda">Miranda</option>
                                            <option value="Monagas">Monagas</option>
                                            <option value="Nueva Esparta">Nueva Esparta</option>
                                            <option value="Portuguesa">Portuguesa</option>
                                            <option value="Sucre">Sucre</option>
                                            <option value="Táchira">Táchira</option>
                                            <option value="Trujillo">Trujillo</option>
                                            <option value="Yaracuy">Yaracuy</option>
                                            <option value="Zulia">Zulia</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="ciudad-jur-field" class="form-label">Municipio</label>
                                        <select id="ciudad-jur-field" name="ciudad" class="form-select">
                                            <option value="">Primero seleccione un estado</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CAMPOS PARA PROVEEDOR NATURAL (PERSONA) -->
                        <div id="campos-natural" style="display: none;">
                            <div class="modal-form-section">
                                <div class="modal-form-section-title"><i class="ri-user-3-line"></i>Datos Personales</div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <x-forms.input name="nombre" label="Nombre" maxlength="100" placeholder="Nombre"
                                            id="nombre-field" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <x-forms.input name="apellido" label="Apellido" maxlength="100"
                                            placeholder="Apellido" id="apellido-field" />
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <x-forms.input name="direccion" label="Dirección" maxlength="255"
                                        placeholder="Dirección completa" id="direccion-nat-field" />
                                </div>
                            </div>

                            <div class="modal-form-section">
                                <div class="modal-form-section-title"><i class="ri-contacts-book-line"></i>Contacto</div>

                                <div class="row mb-0">
                                    <div class="col-md-6 mb-3">
                                        <x-forms.input name="telefono_nat_number" label="Teléfono"
                                            id="telefono-nat-number-field" maxlength="7" placeholder="1234567" required
                                            prependRaw="true">
                                            <x-slot:prepend>
                                                <select class="form-select" id="telefono-nat-prefix-field"
                                                    style="max-width: 100px; min-width: 100px;">
                                                    <option value="0412">0412</option>
                                                    <option value="0422">0422</option>
                                                    <option value="0414">0414</option>
                                                    <option value="0424" selected>0424</option>
                                                    <option value="0416">0416</option>
                                                    <option value="0426">0426</option>
                                                </select>
                                            </x-slot:prepend>
                                        </x-forms.input>
                                        <input type="hidden" id="telefono-nat-field" name="telefono" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <x-forms.input name="email" label="Email" type="email"
                                            placeholder="correo@email.com" id="email-nat-field" />
                                    </div>
                                </div>
                            </div>

                            <div class="modal-form-section mb-0">
                                <div class="modal-form-section-title"><i class="ri-map-pin-2-line"></i>Ubicación</div>

                                <div class="row mb-0">
                                    <div class="col-md-6 mb-3">
                                        <label for="estado-territorial-field" class="form-label">Estado</label>
                                        <select id="estado-territorial-field" name="estado_territorial" class="form-select">
                                            <option value="">Seleccione estado</option>
                                            <option value="Amazonas">Amazonas</option>
                                            <option value="Anzoátegui">Anzoátegui</option>
                                            <option value="Apure">Apure</option>
                                            <option value="Aragua">Aragua</option>
                                            <option value="Barinas">Barinas</option>
                                            <option value="Bolívar">Bolívar</option>
                                            <option value="Carabobo">Carabobo</option>
                                            <option value="Cojedes">Cojedes</option>
                                            <option value="Delta Amacuro">Delta Amacuro</option>
                                            <option value="Distrito Capital">Distrito Capital</option>
                                            <option value="Falcón">Falcón</option>
                                            <option value="Guárico">Guárico</option>
                                            <option value="La Guaira">La Guaira</option>
                                            <option value="Lara">Lara</option>
                                            <option value="Mérida">Mérida</option>
                                            <option value="Miranda">Miranda</option>
                                            <option value="Monagas">Monagas</option>
                                            <option value="Nueva Esparta">Nueva Esparta</option>
                                            <option value="Portuguesa">Portuguesa</option>
                                            <option value="Sucre">Sucre</option>
                                            <option value="Táchira">Táchira</option>
                                            <option value="Trujillo">Trujillo</option>
                                            <option value="Yaracuy">Yaracuy</option>
                                            <option value="Zulia">Zulia</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="ciudad-field" class="form-label">Municipio</label>
                                        <select id="ciudad-field" name="ciudad" class="form-select">
                                            <option value="">Primero seleccione un estado</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-form-section mb-0">
                            <div class="modal-form-section-title"><i class="ri-shield-check-line"></i>Estatus</div>
                            <div class="form-check form-switch form-switch-success">
                                <input type="hidden" name="estado" value="0" />
                                <input class="form-check-input" type="checkbox" role="switch" id="estado-field"
                                    name="estado" value="1" checked />
                                <label class="form-check-label" for="estado-field" id="estado-label">Activo</label>
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
        <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-file-pdf-line me-2"></i>Exportar PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Filtra qué proveedores incluir en el reporte.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="pdf-filter-tipo">Tipo de Proveedor</label>
                        <select class="form-select" id="pdf-filter-tipo">
                            <option value="">Todos los tipos</option>
                            <option value="natural">Natural</option>
                            <option value="juridico">Jurídico</option>
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
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
    <script src="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/js/municipios-venezuela.js') }}"></script>

    <script>
        $(document).ready(function () {

            function generateButtons(proveedorId, isTrashed) {
                // Si el registro está inhabilitado (trashed), mostrar botón "Ver" + "Restaurar"
                if (isTrashed) {
                    return '<div class="d-flex gap-1 justify-content-center">' +
                        '<button class="btn btn-sm btn-soft-secondary view-item-btn" data-id="' + proveedorId + '" title="Ver" style="padding:0.2rem 0.45rem;">' +
                        '<i class="ri-eye-fill" style="font-size:13px;"></i>' +
                        '</button>' +
                        '<button class="btn btn-sm btn-soft-success restore-item-btn" data-id="' + proveedorId + '" title="Restaurar" style="padding:0.2rem 0.45rem;">' +
                        '<i class="ri-arrow-go-back-line" style="font-size:13px;"></i>' +
                        '</button>' +
                        '</div>';
                }

                var isAdmin = {{ Auth::user()->isAdmin() ? 'true' : 'false' }};
                var editDeleteBtns = isAdmin
                    ? '<button class="btn btn-sm btn-soft-success edit-item-btn" data-id="' + proveedorId + '" title="Editar" style="padding:0.2rem 0.45rem;">' +
                    '<i class="ri-pencil-fill" style="font-size:13px;"></i>' +
                    '</button>' +
                    '<button class="btn btn-sm btn-soft-danger remove-item-btn" data-id="' + proveedorId + '" title="Inhabilitar" style="padding:0.2rem 0.45rem;">' +
                    '<i class="ri-forbid-line" style="font-size:13px;"></i>' +
                    '</button>'
                    : '';

                return '<div class="d-flex gap-1 justify-content-center">' +
                    '<button class="btn btn-sm btn-soft-secondary view-item-btn" data-id="' + proveedorId + '" title="Ver" style="padding:0.2rem 0.45rem;">' +
                    '<i class="ri-eye-fill" style="font-size:13px;"></i>' +
                    '</button>' +
                    editDeleteBtns +
                    '</div>';
            }

            // Toggle campos según tipo de proveedor.
            // Los selectores incluyen tanto los bloques grandes (#campos-juridico/#campos-natural)
            // como los wrappers del RIF/Documento dentro de la sección Identificación
            // (.js-tipo-juridico/.js-tipo-natural).
            function toggleCampos() {
                var tipo = $('#tipo-proveedor-field').val();
                var $jur = $('#campos-juridico, .js-tipo-juridico');
                var $nat = $('#campos-natural, .js-tipo-natural');

                if (tipo === 'natural') {
                    $jur.hide();
                    $nat.show();
                    // Desactivar validaciones del bloque JURÍDICO oculto (incluye RIF en Identificación)
                    $jur.find('[required]').each(function () {
                        $(this).removeAttr('required').attr('data-required', 'true');
                    });
                    // Restaurar validaciones del bloque NATURAL visible (incluye Documento en Identificación)
                    $nat.find('[data-required]').each(function () {
                        $(this).attr('required', 'required').removeAttr('data-required');
                    });
                    // Limpiar campos jurídicos
                    $('#rif-number-field, #razon-social-field, #direccion-jur-field, #telefono-jur-field, #email-jur-field, #contacto-field, #telefono-contacto-field, #estado-territorial-jur-field').val('');
                    $('#ciudad-jur-field').empty().append('<option value="">Primero seleccione un estado</option>');
                } else {
                    $jur.show();
                    $nat.hide();
                    // Desactivar validaciones del bloque NATURAL oculto (incluye Documento en Identificación)
                    $nat.find('[required]').each(function () {
                        $(this).removeAttr('required').attr('data-required', 'true');
                    });
                    // Restaurar validaciones del bloque JURÍDICO visible (incluye RIF en Identificación)
                    $jur.find('[data-required]').each(function () {
                        $(this).attr('required', 'required').removeAttr('data-required');
                    });
                    // Limpiar campos naturales
                    $('#nombre-field, #apellido-field, #documento-identidad-field, #telefono-nat-field, #email-nat-field, #direccion-nat-field, #ciudad-field, #estado-territorial-field').val('');
                }
            }

            $('#tipo-proveedor-field').on('change', toggleCampos);
            toggleCampos(); // Inicializar: quitar required de los campos ocultos al cargar

            // Listener para actualizar label del checkbox de estatus
            $("#estado-field").on('change', function () {
                if ($(this).is(':checked')) {
                    $("#estado-label").text('Activo');
                } else {
                    $("#estado-label").text('Inactivo');
                }
            });

            // Dropdown dependiente: Poblar municipios cuando cambia el estado (Natural)
            $("#estado-territorial-field").on('change', function () {
                const estado = $(this).val();
                const municipios = getMunicipios(estado);
                const ciudadSelect = $("#ciudad-field");

                ciudadSelect.empty();

                if (estado === '') {
                    ciudadSelect.append('<option value="">Primero seleccione un estado</option>');
                } else {
                    ciudadSelect.append('<option value="">Seleccione municipio</option>');
                    municipios.forEach(function (municipio) {
                        ciudadSelect.append('<option value="' + municipio + '">' + municipio + '</option>');
                    });
                }
            });

            // Dropdown dependiente: Poblar municipios cuando cambia el estado (Jurídico)
            $("#estado-territorial-jur-field").on('change', function () {
                const estado = $(this).val();
                const municipios = getMunicipios(estado);
                const ciudadSelect = $("#ciudad-jur-field");

                ciudadSelect.empty();

                if (estado === '') {
                    ciudadSelect.append('<option value="">Primero seleccione un estado</option>');
                } else {
                    ciudadSelect.append('<option value="">Seleccione municipio</option>');
                    municipios.forEach(function (municipio) {
                        ciudadSelect.append('<option value="' + municipio + '">' + municipio + '</option>');
                    });
                }
            });

            var table = $('#proveedores-table').DataTable({
                ajax: {
                    url: "{{ route('proveedores.data') }}",
                    dataSrc: 'data',
                    data: function (d) {
                        // ── Filtros avanzados: enviar valores al server ──
                        d.filter_tipo_proveedor      = $('#filter-tipo-proveedor').val();
                        d.filter_estatus             = $('#filter-estatus').val();
                        d.filter_estado_territorial  = $('#filter-estado-territorial').val();
                        d.filter_orden               = $('#filter-orden').val();
                    }
                },
                columns: [
                    { data: 'documento_display', name: 'rif' },
                    { data: 'nombre_display', name: 'razon_social' },
                    {
                        data: 'tipo_display',
                        name: 'tipo_proveedor',
                        render: function (data, type, row) {
                            if (row.tipo_proveedor === 'natural') {
                                return '<span class="badge-tipo badge-tipo-natural"><i class="ri-user-line"></i> Natural</span>';
                            }
                            return '<span class="badge-tipo badge-tipo-juridico"><i class="ri-building-line"></i> Jurídico</span>';
                        }
                    },
                    { data: 'telefono_display', name: 'telefono' },
                    {
                        data: 'email_display',
                        name: 'email',
                        render: function (data) {
                            if (!data) return '<span class="text-muted">—</span>';
                            return '<span title="' + data + '" style="cursor:default;">' + data + '</span>';
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
                dom: 'rtip',
                language: lenguajeData
            });

            // ══════════════════════════════════════════════════════
            // BÚSQUEDA + FILTROS AVANZADOS — Patrón Maestro S-07
            // Header unificado: búsqueda global + panel colapsable
            // ══════════════════════════════════════════════════════

            // ── Badge: actualizar contador de filtros activos + punto rojo ──
            function updateFilterBadge() {
                var count = 0;
                if ($('#filter-tipo-proveedor').val() !== '')                        count++;
                if ($('#filter-estatus').val() !== '1')                              count++;
                if ($('#filter-estado-territorial').val() !== '')                    count++;
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

            // ── Si se llegó por toggle historial (?historial=true) ──
            @if($historial)
                $('#filter-estatus').val('0');
                table.ajax.reload();
                updateFilterBadge();
            @endif

            // ── Botón limpiar: resetea búsqueda + filtros + orden ──
            $('#btn-clear-filters').on('click', function () {
                $('#filter-tipo-proveedor').val('');
                $('#filter-estatus').val('1');
                $('#filter-estado-territorial').val('');
                $('#filter-orden').val('recientes');
                $('#custom-search-input').val('');
                updateFilterBadge();
                table.search('').ajax.reload(function () {
                    updateFilterBadge();
                });
            });

            // Ver detalles
            $(document).on('click', '.view-item-btn', function () {
                var id = $(this).data('id');
                $.get("{{ route('proveedores.show', ':id') }}".replace(':id', id), function (data) {
                    var tipoText = data.tipo_proveedor === 'natural' ? 'Natural (Persona)' : 'Jurídico (Empresa)';
                    $("#view-tipo").text(tipoText);

                    // Layout dinámico según tipo de proveedor
                    if (data.tipo_proveedor === 'natural') {
                        $("#view-block-prov-nombre").removeClass('d-none');
                        $("#view-block-prov-apellido").removeClass('d-none');
                        $("#view-block-prov-razon-social").addClass('d-none');
                        $("#view-nombre").text(data.nombre || 'N/A');
                        $("#view-apellido").text(data.apellido || 'N/A');
                        $("#view-label-documento").text('Documento de Identidad');
                        $("#view-documento").text(data.documento_display || data.documento_identidad || 'N/A');
                    } else {
                        $("#view-block-prov-nombre").addClass('d-none');
                        $("#view-block-prov-apellido").addClass('d-none');
                        $("#view-block-prov-razon-social").removeClass('d-none');
                        $("#view-razon-social").text(data.razon_social || 'N/A');
                        $("#view-label-documento").text('RIF');
                        $("#view-documento").text(data.rif || 'N/A');
                    }

                    $("#view-telefono").text(data.telefono || 'No especificado');
                    $("#view-email").text(data.email || 'No especificado');
                    $("#view-direccion").text(data.direccion || 'No especificada');
                    $("#view-estatus").html(data.estado == 1 ?
                        '<span class="badge bg-success">Activo</span>' :
                        '<span class="badge bg-danger">Inactivo</span>');

                    // Mostrar/ocultar campos de contacto según tipo
                    if (data.tipo_proveedor === 'juridico') {
                        $("#view-contacto-section").show();
                        $("#view-telefono-contacto-section").show();
                        $("#view-contacto").text(data.contacto || 'No especificado');
                        $("#view-telefono-contacto").text(data.telefono_contacto || 'No especificado');
                    } else {
                        $("#view-contacto-section").hide();
                        $("#view-telefono-contacto-section").hide();
                    }

                    $("#view-created").text(data.created_at);
                    $("#viewModal").modal('show');
                });
            });

            // Editar
            $(document).on('click', '.edit-item-btn', function () {
                var id = $(this).data('id');
                $.get("{{ route('proveedores.show', ':id') }}".replace(':id', id), function (data) {
                    $("#modalTitle").text("Editar Proveedor");
                    $("#id-field").val(data.id);
                    $("#tipo-proveedor-field").val(data.tipo_proveedor || 'juridico');
                    $("#estado-field").val(data.estado ? '1' : '0');

                    toggleCampos();

                    if (data.tipo_proveedor === 'natural') {
                        // Cargar datos de persona natural
                        $("#nombre-field").val(data.nombre);
                        $("#apellido-field").val(data.apellido);
                        $("#tipo-documento-field").val(data.tipo_documento || 'V-');
                        $("#documento-identidad-field").val(data.documento_identidad);

                        // Separar teléfono en prefijo y número
                        var telefono = data.telefono || '';
                        var telMatch = telefono.match(/^(0412|0422|0414|0424|0416|0426)-(.+)$/);
                        if (telMatch) {
                            $("#telefono-nat-prefix-field").val(telMatch[1]);
                            $("#telefono-nat-number-field").val(telMatch[2]);
                        } else {
                            $("#telefono-nat-prefix-field").val('0424');
                            $("#telefono-nat-number-field").val(telefono.replace(/^0\d{3}-?/, ''));
                        }

                        $("#email-nat-field").val(data.email);
                        $("#direccion-nat-field").val(data.direccion);

                        // Cargar estado y municipio de forma síncrona (Obs. #4 resuelta)
                        if (data.estado_territorial) {
                            $("#estado-territorial-field").val(data.estado_territorial);
                            var municipios = getMunicipios(data.estado_territorial);
                            var select = $("#ciudad-field");
                            select.empty().append('<option value="">Seleccione municipio</option>');
                            municipios.forEach(function (m) {
                                select.append('<option value="' + m + '">' + m + '</option>');
                            });
                            select.val(data.ciudad);
                        }
                    } else {
                        // Cargar datos de empresa jurídica
                        var rif = data.rif || '';
                        var rifMatch = rif.match(/^(V-|J-|E-|G-)(.+)$/);
                        if (rifMatch) {
                            $("#rif-prefix-field").val(rifMatch[1]);
                            $("#rif-number-field").val(rifMatch[2]);
                        } else {
                            $("#rif-prefix-field").val('J-');
                            $("#rif-number-field").val(rif);
                        }
                        $("#razon-social-field").val(data.razon_social);
                        $("#direccion-jur-field").val(data.direccion);

                        // Cargar estado y municipio jurídico de forma síncrona (Obs. #4 resuelta)
                        if (data.estado_territorial) {
                            $("#estado-territorial-jur-field").val(data.estado_territorial);
                            var municipios = getMunicipios(data.estado_territorial);
                            var select = $("#ciudad-jur-field");
                            select.empty().append('<option value="">Seleccione municipio</option>');
                            municipios.forEach(function (m) {
                                select.append('<option value="' + m + '">' + m + '</option>');
                            });
                            select.val(data.ciudad);
                        }

                        // Separar teléfono principal en prefijo y número
                        var telJur = data.telefono || '';
                        var telJurMatch = telJur.match(/^(0212|0251|0241|0255|0412|0414|0424|0416|0426)-(.+)$/);
                        if (telJurMatch) {
                            $("#telefono-jur-prefix-field").val(telJurMatch[1]);
                            $("#telefono-jur-number-field").val(telJurMatch[2]);
                        } else {
                            $("#telefono-jur-prefix-field").val('0424');
                            $("#telefono-jur-number-field").val(telJur.replace(/^0\d{3}-?/, ''));
                        }

                        $("#email-jur-field").val(data.email);
                        $("#contacto-field").val(data.contacto);

                        // Separar teléfono de contacto en prefijo y número
                        var telContacto = data.telefono_contacto || '';
                        var telContactoMatch = telContacto.match(/^(0412|0414|0424|0416|0426)-(.+)$/);
                        if (telContactoMatch) {
                            $("#telefono-contacto-prefix-field").val(telContactoMatch[1]);
                            $("#telefono-contacto-number-field").val(telContactoMatch[2]);
                        } else {
                            $("#telefono-contacto-prefix-field").val('0424');
                            $("#telefono-contacto-number-field").val(telContacto.replace(/^0\d{3}-?/, ''));
                        }
                    }

                    $("#add-btn").hide();
                    $("#edit-btn").show();

                    // Bloquear edición de documento y tipo de proveedor
                    $("#tipo-proveedor-field").prop('disabled', true).addClass('campo-protegido');
                    if (data.tipo_proveedor === 'natural') {
                        $("#tipo-documento-field").prop('disabled', true).addClass('campo-protegido');
                        $("#documento-identidad-field").prop('disabled', true).addClass('campo-protegido');
                    } else {
                        $("#rif-prefix-field").prop('disabled', true).addClass('campo-protegido');
                        $("#rif-number-field").prop('disabled', true).addClass('campo-protegido');
                    }

                    $("#showModal").modal('show');
                });
            });

            // Enviar formulario
            $("#proveedorForm").on("submit", function (e) {
                e.preventDefault();

                if (!validarFormularioProveedor()) return;

                var id = $("#id-field").val();
                var url = id ? "{{ route('proveedores.update', ':id') }}".replace(':id', id) : "{{ route('proveedores.store') }}";
                var method = id ? "PUT" : "POST";
                var tipo = $('#tipo-proveedor-field').val();

                var formData = new FormData(this);
                formData.set('tipo_proveedor', tipo);

                // Preparar datos según tipo y LIMPIAR campos del tipo opuesto
                if (tipo === 'juridico') {
                    var rifPrefix = $('#rif-prefix-field').val();
                    var rifNumber = $('#rif-number-field').val();
                    formData.set('rif', rifPrefix + rifNumber);

                    // Concatenar teléfono principal: prefijo-número
                    var telefonoJurCompleto = $('#telefono-jur-prefix-field').val() + '-' + $('#telefono-jur-number-field').val();
                    formData.set('telefono', telefonoJurCompleto);

                    formData.set('email', $('#email-jur-field').val());
                    formData.set('direccion', $('#direccion-jur-field').val());
                    formData.set('estado_territorial', $('#estado-territorial-jur-field').val());
                    formData.set('ciudad', $('#ciudad-jur-field').val());

                    // Concatenar teléfono de contacto
                    var telefonoContactoCompleto = $('#telefono-contacto-prefix-field').val() + '-' + $('#telefono-contacto-number-field').val();
                    formData.set('telefono_contacto', telefonoContactoCompleto);

                    // Eliminar campos huérfanos del bloque Natural que FormData serializó
                    formData.delete('documento_identidad_number');
                    formData.delete('telefono_nat_number');
                    formData.delete('nombre');
                    formData.delete('apellido');
                } else {
                    // Concatenar teléfono: prefijo-número
                    var telefonoCompleto = $('#telefono-nat-prefix-field').val() + '-' + $('#telefono-nat-number-field').val();
                    formData.set('telefono', telefonoCompleto);
                    formData.set('email', $('#email-nat-field').val());
                    formData.set('direccion', $('#direccion-nat-field').val());

                    // Concatenar documento de identidad: prefijo + número
                    var tipoDoc = $('#tipo-documento-field').val();
                    var docNum = $('#documento-identidad-field').val();
                    formData.set('tipo_documento', tipoDoc);
                    formData.set('documento_identidad', docNum);

                    // Eliminar campos huérfanos del bloque Jurídico que FormData serializó
                    formData.delete('documento_identidad_number');
                    formData.delete('telefono_nat_number');
                    formData.delete('rif_number');
                    formData.delete('telefono_jur_number');
                    formData.delete('telefono_contacto_number');
                    formData.delete('razon_social');
                    formData.delete('contacto');
                }

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
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.success,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    },
                    error: function (xhr) {
                        var errors = xhr.responseJSON?.errors || {};
                        var errorMessage = xhr.responseJSON?.error || '';
                        if (Object.keys(errors).length > 0) {
                            errorMessage = '';
                            $.each(errors, function (key, value) {
                                errorMessage += value[0] + '<br>';
                            });
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errorMessage || 'Ocurrió un error al procesar la solicitud'
                        });
                    }
                });
            });

            // Eliminar
            $(document).on("click", ".remove-item-btn", function () {
                var id = $(this).data("id");
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "El proveedor será inhabilitado y moverá al historial.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, inhabilitar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-primary w-xs me-2',
                        cancelButton: 'btn btn-danger w-xs'
                    },
                    buttonsStyling: false
                }).then(function (result) {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('proveedores.destroy', ':id') }}".replace(':id', id),
                            method: "DELETE",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                table.ajax.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Inhabilitado!',
                                    text: response.success,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'No se pudo inhabilitar el proveedor'
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
                    text: "¿Estás seguro de que deseas restaurar este proveedor?",
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
                            url: "{{ url('proveedores') }}/" + id + "/restore",
                            method: "POST",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                table.ajax.reload();
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
                                    text: 'No se pudo restaurar el proveedor'
                                });
                            }
                        });
                    }
                });
            });

            // Limpiar modal al cerrar
            $("#showModal").on("hidden.bs.modal", function () {
                $("#modalTitle").text("Agregar Proveedor");
                $("#proveedorForm")[0].reset();
                $("#id-field").val("");
                $("#tipo-proveedor-field").val("juridico");
                toggleCampos();
                $("#add-btn").show().prop('disabled', false);
                $("#edit-btn").hide();
                // Desbloquear campos de documento
                $("#tipo-proveedor-field").prop('disabled', false).removeClass('campo-protegido');
                $("#rif-prefix-field").prop('disabled', false).removeClass('campo-protegido');
                $("#rif-number-field").prop('disabled', false).removeClass('campo-protegido');
                $("#tipo-documento-field").prop('disabled', false).removeClass('campo-protegido');
                $("#documento-identidad-field").prop('disabled', false).removeClass('campo-protegido');
                $('.is-invalid').removeClass('is-invalid');
                $('.is-valid').removeClass('is-valid');
                $('.invalid-feedback').hide();
                $('#add-btn').prop('disabled', false);
            });

            // ══════════════════════════════════════════════════════
            // VALIDACIONES ONBLUR — Patrón marcarInvalido/marcarValido
            // ══════════════════════════════════════════════════════

            var emailRegex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;

            // Sanitización en tiempo real — solo letras y espacios
            $(document).on('input', '#nombre-field, #apellido-field, #contacto-field', function () {
                this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
            });
            // Solo dígitos en campos numéricos de teléfono y documento
            $(document).on('input', '#telefono-jur-number-field, #telefono-nat-number-field, #telefono-contacto-number-field', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 7);
            });
            $(document).on('input', '#documento-identidad-field', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);
            });
            $(document).on('input', '#rif-number-field', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);
            });

            // 1. RIF (Jurídico) — longitud mínima + AJAX duplicado
            $(document).on('blur', '#rif-number-field', function () {
                var $input = $(this);
                var val = $input.val().trim();
                var isEdit = $('#id-field').val() !== '';
                if (val.length === 0) {
                    marcarInvalido($input, 'El RIF es obligatorio.');
                    return;
                }
                if (val.length < 5) {
                    marcarInvalido($input, 'El RIF debe tener al menos 5 dígitos.');
                    return;
                }
                if (isEdit) { marcarValido($input); return; }
                var fullRif = $('#rif-prefix-field').val() + val;
                $.get("{{ route('proveedores.check-rif') }}", { rif: fullRif }, function (res) {
                    if (res.exists) {
                        marcarInvalido($input, 'Este RIF ya está registrado.');
                        $('#add-btn').prop('disabled', true);
                    } else {
                        marcarValido($input);
                        $('#add-btn').prop('disabled', false);
                    }
                });
            });

            // 2. Razón Social (Jurídico)
            $(document).on('blur', '#razon-social-field', function () {
                var val = $(this).val().trim();
                if (val.length === 0) {
                    marcarInvalido($(this), 'La Razón Social es obligatoria.');
                } else if (val.length < 2) {
                    marcarInvalido($(this), 'Mínimo 2 caracteres.');
                } else {
                    marcarValido($(this));
                }
            });

            // 3. Nombre (Natural)
            $(document).on('blur', '#nombre-field', function () {
                var val = $(this).val().trim();
                if (val.length === 0) {
                    marcarInvalido($(this), 'El nombre es obligatorio.');
                } else if (val.length < 2) {
                    marcarInvalido($(this), 'Mínimo 2 caracteres.');
                } else {
                    marcarValido($(this));
                }
            });

            // 4. Apellido (Natural)
            $(document).on('blur', '#apellido-field', function () {
                var val = $(this).val().trim();
                if (val.length === 0) {
                    marcarInvalido($(this), 'El apellido es obligatorio.');
                } else if (val.length < 2) {
                    marcarInvalido($(this), 'Mínimo 2 caracteres.');
                } else {
                    marcarValido($(this));
                }
            });

            // 5. Documento de Identidad (Natural) — longitud + AJAX duplicado
            $(document).on('blur', '#documento-identidad-field', function () {
                var $input = $(this);
                var val = $input.val().trim();
                var isEdit = $('#id-field').val() !== '';
                if (val.length === 0) {
                    marcarInvalido($input, 'El documento es obligatorio.');
                    return;
                }
                if (val.length < 6) {
                    marcarInvalido($input, 'El documento debe tener al menos 6 dígitos.');
                    return;
                }
                if (isEdit) { marcarValido($input); return; }
                $.get("{{ route('proveedores.check-documento') }}", { numero: val }, function (res) {
                    if (res.exists) {
                        marcarInvalido($input, 'Este documento ya está registrado.');
                        $('#add-btn').prop('disabled', true);
                    } else {
                        marcarValido($input);
                        $('#add-btn').prop('disabled', false);
                    }
                });
            });

            // 6. Teléfono principal — Jurídico
            $(document).on('blur', '#telefono-jur-number-field', function () {
                var val = $(this).val().trim();
                if (val.length === 0) {
                    marcarInvalido($(this), 'El teléfono es obligatorio.');
                } else if (!/^[0-9]{7}$/.test(val)) {
                    marcarInvalido($(this), 'Debe tener exactamente 7 dígitos.');
                } else {
                    marcarValido($(this));
                }
            });

            // 7. Teléfono principal — Natural
            $(document).on('blur', '#telefono-nat-number-field', function () {
                var val = $(this).val().trim();
                if (val.length === 0) {
                    marcarInvalido($(this), 'El teléfono es obligatorio.');
                } else if (!/^[0-9]{7}$/.test(val)) {
                    marcarInvalido($(this), 'Debe tener exactamente 7 dígitos.');
                } else {
                    marcarValido($(this));
                }
            });

            // 8. Teléfono de Contacto (Jurídico, opcional)
            $(document).on('blur', '#telefono-contacto-number-field', function () {
                var val = $(this).val().trim();
                if (val.length === 0) { limpiarValidacion($(this)); return; }
                if (!/^[0-9]{7}$/.test(val)) {
                    marcarInvalido($(this), 'Debe tener exactamente 7 dígitos.');
                } else {
                    marcarValido($(this));
                }
            });

            // 9. Email — Jurídico y Natural (formato + AJAX duplicado con exclude_id en edición)
            $(document).on('blur', '#email-jur-field, #email-nat-field', function () {
                var $input = $(this);
                var val = $input.val().trim();
                var excludeId = $('#id-field').val();
                if (val.length === 0) { limpiarValidacion($input); return; }
                if (!emailRegex.test(val)) {
                    marcarInvalido($input, 'Ingrese un email válido (ej: correo@dominio.com).');
                    return;
                }
                $.get("{{ route('proveedores.check-email') }}", { email: val, exclude_id: excludeId }, function (res) {
                    if (res.exists) {
                        marcarInvalido($input, 'Este correo ya está registrado.');
                        $('#add-btn').prop('disabled', true);
                    } else {
                        marcarValido($input);
                        $('#add-btn').prop('disabled', false);
                    }
                });
            });

            // 10. Dirección — Jurídico
            $(document).on('blur', '#direccion-jur-field', function () {
                var val = $(this).val().trim();
                if (val.length === 0) {
                    marcarInvalido($(this), 'La dirección es obligatoria.');
                } else if (val.length < 5) {
                    marcarInvalido($(this), 'Mínimo 5 caracteres.');
                } else {
                    marcarValido($(this));
                }
            });

            // 11. Dirección — Natural
            $(document).on('blur', '#direccion-nat-field', function () {
                var val = $(this).val().trim();
                if (val.length === 0) {
                    marcarInvalido($(this), 'La dirección es obligatoria.');
                } else if (val.length < 5) {
                    marcarInvalido($(this), 'Mínimo 5 caracteres.');
                } else {
                    marcarValido($(this));
                }
            });

            // 12. Persona de Contacto (Jurídico, opcional)
            $(document).on('blur', '#contacto-field', function () {
                var val = $(this).val().trim();
                if (val.length === 0) { limpiarValidacion($(this)); return; }
                if (val.length < 2) {
                    marcarInvalido($(this), 'Mínimo 2 caracteres.');
                } else {
                    marcarValido($(this));
                }
            });

            // ══════════════════════════════════════════════════════
            // VALIDACIÓN AL SUBMIT
            // ══════════════════════════════════════════════════════
            function validarFormularioProveedor() {
                var esValido = true;
                var tipo = $('#tipo-proveedor-field').val();

                if (tipo === 'juridico') {
                    var $rif = $('#rif-number-field');
                    if ($rif.val().trim().length < 5) {
                        marcarInvalido($rif, 'El RIF debe tener al menos 5 dígitos.');
                        esValido = false;
                    }
                    var $razon = $('#razon-social-field');
                    if ($razon.val().trim().length < 2) {
                        marcarInvalido($razon, 'La Razón Social es obligatoria.');
                        esValido = false;
                    }
                    var $telJur = $('#telefono-jur-number-field');
                    if (!/^[0-9]{7}$/.test($telJur.val().trim())) {
                        marcarInvalido($telJur, 'El teléfono debe tener 7 dígitos.');
                        esValido = false;
                    }
                    var $dirJur = $('#direccion-jur-field');
                    if ($dirJur.val().trim().length < 5) {
                        marcarInvalido($dirJur, 'La dirección es obligatoria (mín. 5 caracteres).');
                        esValido = false;
                    }
                } else {
                    var $nombre = $('#nombre-field');
                    if ($nombre.val().trim().length < 2) {
                        marcarInvalido($nombre, 'El nombre es obligatorio.');
                        esValido = false;
                    }
                    var $apellido = $('#apellido-field');
                    if ($apellido.val().trim().length < 2) {
                        marcarInvalido($apellido, 'El apellido es obligatorio.');
                        esValido = false;
                    }
                    var $doc = $('#documento-identidad-field');
                    if ($doc.val().trim().length < 6) {
                        marcarInvalido($doc, 'El documento debe tener al menos 6 dígitos.');
                        esValido = false;
                    }
                    var $telNat = $('#telefono-nat-number-field');
                    if (!/^[0-9]{7}$/.test($telNat.val().trim())) {
                        marcarInvalido($telNat, 'El teléfono debe tener 7 dígitos.');
                        esValido = false;
                    }
                    var $dirNat = $('#direccion-nat-field');
                    if ($dirNat.val().trim().length < 5) {
                        marcarInvalido($dirNat, 'La dirección es obligatoria (mín. 5 caracteres).');
                        esValido = false;
                    }
                }

                // Email opcional — si tiene valor debe tener formato válido
                var emailActivo = tipo === 'juridico' ? $('#email-jur-field') : $('#email-nat-field');
                var emailVal = emailActivo.val().trim();
                if (emailVal.length > 0 && !emailRegex.test(emailVal)) {
                    marcarInvalido(emailActivo, 'Ingrese un email válido.');
                    esValido = false;
                }

                return esValido;
            }

        });
    </script>
    <script>
        // PDF Export Modal — Proveedores
        $('#btn-generar-pdf').on('click', function () {
            var baseUrl = '{{ route('proveedores.reporte.pdf') }}';
            var params  = [];
            var tipo    = $('#pdf-filter-tipo').val();
            var estatus = $('#pdf-filter-estatus').val();
            if (tipo)    params.push('tipo_proveedor=' + encodeURIComponent(tipo));
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