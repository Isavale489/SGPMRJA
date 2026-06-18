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
                grid-template-columns: repeat(3, 1fr) !important;
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
                                    <i class="ri-archive-line"></i> Inhabilitados
                                </a>
                            @endif
                            <div class="d-flex gap-2">
                                @if(tienePermiso('proveedores.gestionar') && !$historial)
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
                <div class="modal-body p-0">

                    {{-- Hero strip --}}
                    <div class="cli-view-hero">
                        <div class="cli-view-hero-avatar" id="view-hero-avatar">—</div>
                        <div class="cli-view-hero-info">
                            <div class="cli-view-hero-name" id="view-hero-name">—</div>
                            <div class="cli-view-hero-doc" id="view-hero-doc">—</div>
                        </div>
                        <div class="cli-view-hero-badge text-end">
                            <div><span id="view-estatus">—</span></div>
                            <div class="cli-view-hero-date mt-1"><i class="ri-calendar-line me-1"></i><span id="view-hero-date">—</span></div>
                        </div>
                    </div>

                    {{-- Secciones --}}
                    <div class="px-4 py-3 cli-view-sections">

                        {{-- Identificación --}}
                        <div class="cli-view-card">
                        <div class="cli-view-card-header"><i class="ri-store-2-line"></i>Identificación</div>
                        <div class="cli-view-card-body">
                        <div class="row g-3">
                            {{-- Documento SIEMPRE primero (label dinámico) --}}
                            <div class="col-sm-6">
                                <div class="d-flex align-items-start">
                                    <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                        <i class="ri-bank-card-line emp-icon--navy"></i>
                                    </div>
                                    <div><small class="text-muted d-block fs-12" id="view-label-documento">Documento</small>
                                    <span class="fw-semibold fs-13" id="view-documento">-</span></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-start">
                                    <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                        <i class="ri-user-settings-line emp-icon--navy"></i>
                                    </div>
                                    <div><small class="text-muted d-block fs-12">Tipo</small>
                                    <span class="fw-semibold fs-13" id="view-tipo">-</span></div>
                                </div>
                            </div>
                            {{-- Natural: Nombre + Apellido --}}
                            <div class="col-sm-6" id="view-block-prov-nombre">
                                <div class="d-flex align-items-start">
                                    <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                        <i class="ri-user-line emp-icon--navy"></i>
                                    </div>
                                    <div><small class="text-muted d-block fs-12">Nombre</small>
                                    <span class="fw-semibold fs-13" id="view-nombre">-</span></div>
                                </div>
                            </div>
                            <div class="col-sm-6" id="view-block-prov-apellido">
                                <div class="d-flex align-items-start">
                                    <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                        <i class="ri-user-follow-line emp-icon--navy"></i>
                                    </div>
                                    <div><small class="text-muted d-block fs-12">Apellido</small>
                                    <span class="fw-semibold fs-13" id="view-apellido">-</span></div>
                                </div>
                            </div>
                            {{-- Jurídico: Razón Social --}}
                            <div class="col-12 d-none" id="view-block-prov-razon-social">
                                <div class="d-flex align-items-start">
                                    <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                        <i class="ri-building-line emp-icon--navy"></i>
                                    </div>
                                    <div><small class="text-muted d-block fs-12">Razón Social</small>
                                    <span class="fw-semibold fs-13" id="view-razon-social">-</span></div>
                                </div>
                            </div>
                        </div>
                        </div></div>

                        {{-- Contacto --}}
                        <div class="cli-view-card">
                        <div class="cli-view-card-header"><i class="ri-contacts-line"></i>Contacto</div>
                        <div class="cli-view-card-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-start">
                                    <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                        <i class="ri-mail-line emp-icon--navy"></i>
                                    </div>
                                    <div><small class="text-muted d-block fs-12">Correo electrónico</small>
                                    <span class="fw-semibold fs-13" id="view-email">-</span></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-start">
                                    <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                        <i class="ri-phone-line emp-icon--navy"></i>
                                    </div>
                                    <div><small class="text-muted d-block fs-12">Teléfono</small>
                                    <span class="fw-semibold fs-13" id="view-telefono">-</span></div>
                                </div>
                            </div>
                            <div class="col-sm-6" id="view-contacto-section">
                                <div class="d-flex align-items-start">
                                    <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                        <i class="ri-user-follow-line emp-icon--navy"></i>
                                    </div>
                                    <div><small class="text-muted d-block fs-12">Persona de Contacto</small>
                                    <span class="fw-semibold fs-13" id="view-contacto">-</span></div>
                                </div>
                            </div>
                            <div class="col-sm-6" id="view-telefono-contacto-section">
                                <div class="d-flex align-items-start">
                                    <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                        <i class="ri-smartphone-line emp-icon--navy"></i>
                                    </div>
                                    <div><small class="text-muted d-block fs-12">Teléfono de Contacto</small>
                                    <span class="fw-semibold fs-13" id="view-telefono-contacto">-</span></div>
                                </div>
                            </div>
                        </div>
                        </div></div>

                        {{-- Ubicación --}}
                        <div class="cli-view-card">
                        <div class="cli-view-card-header"><i class="ri-map-pin-line"></i>Ubicación</div>
                        <div class="cli-view-card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex align-items-start">
                                    <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                        <i class="ri-home-4-line emp-icon--navy"></i>
                                    </div>
                                    <div><small class="text-muted d-block fs-12">Dirección</small>
                                    <span class="fw-semibold fs-13" id="view-direccion">-</span></div>
                                </div>
                            </div>
                        </div>
                        </div></div>

                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
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
                                {{-- Documento unificado: el prefijo (V/E/J/G) determina el tipo de proveedor.
                                     V/E → Natural (cédula), J/G → Jurídico (RIF). --}}
                                <div class="col-md-6">
                                    <x-forms.input name="documento_identidad_number" label="Documento (Cédula o RIF)"
                                        id="documento-identidad-field" maxlength="9" placeholder="Nro. de documento"
                                        required prependRaw="true">
                                        <x-slot:prepend>
                                            <select class="form-select" id="tipo-documento-field" name="tipo_documento"
                                                style="max-width: 80px;">
                                                <option value="V-">V-</option>
                                                <option value="E-">E-</option>
                                                <option value="J-">J-</option>
                                                <option value="G-">G-</option>
                                            </select>
                                        </x-slot:prepend>
                                    </x-forms.input>
                                    <input type="hidden" id="rif-field" name="rif" />
                                </div>
                                <div class="col-md-6">
                                    <x-forms.select name="tipo_proveedor" label="Tipo de Proveedor" required
                                        id="tipo-proveedor-field" :options="['juridico' => 'Jurídico (Empresa)', 'natural' => 'Natural (Persona)']" placeholder=""
                                        class="js-readonly" disabled title="Se determina por el prefijo del documento"
                                        hint="Se define por el prefijo del documento (V/E → Natural, J/G → Jurídico)." />
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
                    <div class="row g-2 mt-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="pdf-fecha-desde">Registro desde</label>
                            <input type="date" class="form-control" id="pdf-fecha-desde">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="pdf-fecha-hasta">Registro hasta</label>
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
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
    <script src="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/js/municipios-venezuela.js') }}"></script>

    <script>
        // Formatea a dd/mm/aaaa (sin hora) — igual que en los demás módulos del estándar "Ver".
        function formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            if (typeof dateStr === 'string') {
                var datePart = dateStr.trim().split(' ')[0] || '';
                if (/^\d{2}\/\d{2}\/\d{4}$/.test(datePart)) return datePart;
            }
            var date = new Date(dateStr);
            if (isNaN(date.getTime())) return dateStr;
            var day = String(date.getDate()).padStart(2, '0');
            var month = String(date.getMonth() + 1).padStart(2, '0');
            return day + '/' + month + '/' + date.getFullYear();
        }

        $(document).ready(function () {

            function generateButtons(proveedorId, isTrashed) {
                var sVer = '<button class="btn btn-sm btn-soft-info view-item-btn" data-id="' + proveedorId + '" title="Ver"><i class="ri-eye-fill"></i></button>';
                var items = '';
                if (isTrashed) {
                    items = '<li><button type="button" class="dropdown-item act-item act-restore restore-item-btn" data-id="' + proveedorId + '"><span class="act-ic"><i class="ri-arrow-go-back-line"></i></span>Restaurar</button></li>';
                } else {
                    var puedeGestionar = {{ tienePermiso('proveedores.gestionar') ? 'true' : 'false' }};
                    if (puedeGestionar) {
                        items =
                            '<li><button type="button" class="dropdown-item act-item act-edit edit-item-btn" data-id="' + proveedorId + '"><span class="act-ic"><i class="ri-pencil-fill"></i></span>Editar</button></li>' +
                            '<li><button type="button" class="dropdown-item act-item act-del remove-item-btn" data-id="' + proveedorId + '"><span class="act-ic"><i class="ri-forbid-line"></i></span>Inhabilitar</button></li>';
                    }
                }
                var menu = items
                    ? '<div class="dropdown d-inline-block">' +
                        '<button class="btn btn-sm btn-soft-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Más acciones"><i class="ri-more-2-fill"></i></button>' +
                        '<ul class="dropdown-menu dropdown-menu-end actions-menu">' + items + '</ul>' +
                    '</div>'
                    : '';
                return '<div class="d-flex gap-1 justify-content-center align-items-center">' + sVer + menu + '</div>';
            }

            // Toggle de los bloques de datos (#campos-juridico/#campos-natural) según el tipo.
            // El TIPO se DERIVA del prefijo del documento (select de tipo en solo lectura).
            function tipoDesdePrefijo(prefix) {
                return (prefix === 'J-' || prefix === 'G-') ? 'juridico' : 'natural';
            }

            function toggleCampos() {
                var prefix = $('#tipo-documento-field').val() || 'V-';
                var tipo = tipoDesdePrefijo(prefix);
                var $jur = $('#campos-juridico');
                var $nat = $('#campos-natural');
                var $doc = $('#documento-identidad-field');

                // Reflejar el tipo en el select de solo lectura (trigger change para que
                // AtlanticoSelect resincronice la etiqueta del widget realzado).
                $('#tipo-proveedor-field').val(tipo).trigger('change');

                // Maxlength dinámico: RIF (J/G) 9 dígitos, cédula (V/E) 8.
                var maxLen = (tipo === 'juridico') ? 9 : 8;
                $doc.attr('maxlength', String(maxLen));
                if (($doc.val() || '').length > maxLen) $doc.val($doc.val().slice(0, maxLen));

                if (tipo === 'natural') {
                    $jur.hide();
                    $nat.show();
                    $jur.find('[required]').each(function () {
                        $(this).removeAttr('required').attr('data-required', 'true');
                    });
                    $nat.find('[data-required]').each(function () {
                        $(this).attr('required', 'required').removeAttr('data-required');
                    });
                    // Limpiar campos jurídicos (NO el documento, que es compartido)
                    $('#razon-social-field, #direccion-jur-field, #telefono-jur-field, #email-jur-field, #contacto-field, #telefono-contacto-field, #estado-territorial-jur-field').val('');
                    $('#ciudad-jur-field').empty().append('<option value="">Primero seleccione un estado</option>');
                } else {
                    $jur.show();
                    $nat.hide();
                    $nat.find('[required]').each(function () {
                        $(this).removeAttr('required').attr('data-required', 'true');
                    });
                    $jur.find('[data-required]').each(function () {
                        $(this).attr('required', 'required').removeAttr('data-required');
                    });
                    // Limpiar campos naturales (NO el documento, que es compartido)
                    $('#nombre-field, #apellido-field, #telefono-nat-field, #email-nat-field, #direccion-nat-field, #ciudad-field, #estado-territorial-field').val('');
                }
            }

            // El tipo se deriva del prefijo del documento.
            $('#tipo-documento-field').on('change', toggleCampos);
            toggleCampos(); // Inicializar: tipo + visibilidad + required según el prefijo

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
                        d.historial                  = @json($historial);
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

            // ── Botón limpiar: resetea búsqueda + filtros + orden ──
            $('#btn-clear-filters').on('click', function () {
                $('#filter-tipo-proveedor').val('');
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

                    var heroName, heroInitials, heroDoc;
                    // Layout dinámico según tipo de proveedor
                    if (data.tipo_proveedor === 'natural') {
                        $("#view-block-prov-nombre").removeClass('d-none');
                        $("#view-block-prov-apellido").removeClass('d-none');
                        $("#view-block-prov-razon-social").addClass('d-none');
                        $("#view-nombre").text(data.nombre || 'N/A');
                        $("#view-apellido").text(data.apellido || 'N/A');
                        $("#view-label-documento").text('Documento de Identidad');
                        heroDoc = data.documento_display || data.documento_identidad || 'N/A';
                        $("#view-documento").text(heroDoc);
                        heroName = (data.nombre || '') + (data.apellido ? ' ' + data.apellido : '');
                        heroInitials = (data.nombre ? data.nombre.charAt(0) : '') + (data.apellido ? data.apellido.charAt(0) : '');
                    } else {
                        $("#view-block-prov-nombre").addClass('d-none');
                        $("#view-block-prov-apellido").addClass('d-none');
                        $("#view-block-prov-razon-social").removeClass('d-none');
                        $("#view-razon-social").text(data.razon_social || 'N/A');
                        $("#view-label-documento").text('RIF');
                        heroDoc = data.rif || 'N/A';
                        $("#view-documento").text(heroDoc);
                        heroName = data.razon_social || 'N/A';
                        var words = (data.razon_social || '').trim().split(/\s+/);
                        heroInitials = words.length >= 2 ? words[0].charAt(0) + words[1].charAt(0) : (words[0] ? words[0].charAt(0) : '?');
                    }

                    $("#view-hero-avatar").text((heroInitials || '?').toUpperCase());
                    $("#view-hero-name").text(heroName.trim() || 'N/A');
                    $("#view-hero-doc").text(heroDoc);
                    $("#view-hero-date").text(formatDate(data.created_at));

                    $("#view-telefono").text(data.telefono || 'No especificado');
                    $("#view-email").text(data.email || 'No especificado');
                    $("#view-direccion").text(data.direccion || 'No especificada');
                    $("#view-estatus").html(data.trashed ?
                        '<span class="badge rounded-pill bg-danger">Inhabilitado</span>' :
                        '<span class="badge rounded-pill bg-success">Activo</span>');

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

                    $("#viewModal").modal('show');
                });
            });

            // Editar
            $(document).on('click', '.edit-item-btn', function () {
                var id = $(this).data('id');
                $.get("{{ route('proveedores.show', ':id') }}".replace(':id', id), function (data) {
                    $("#modalTitle").text("Editar Proveedor");
                    $("#id-field").val(data.id);

                    // Documento unificado: fijar prefijo+número primero; el tipo se deriva del prefijo.
                    if (data.tipo_proveedor === 'natural') {
                        $("#tipo-documento-field").val(data.tipo_documento || 'V-');
                        $("#documento-identidad-field").val(data.documento_identidad || '');
                    } else {
                        var rifFull = data.rif || '';
                        var rifM = rifFull.match(/^(V-|J-|E-|G-)(.+)$/);
                        if (rifM) {
                            $("#tipo-documento-field").val(rifM[1]);
                            $("#documento-identidad-field").val(rifM[2]);
                        } else {
                            $("#tipo-documento-field").val('J-');
                            $("#documento-identidad-field").val(rifFull);
                        }
                    }
                    toggleCampos();

                    if (data.tipo_proveedor === 'natural') {
                        // Cargar datos de persona natural
                        $("#nombre-field").val(data.nombre);
                        $("#apellido-field").val(data.apellido);

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
                        // Cargar datos de empresa jurídica (el documento ya se fijó arriba)
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

                    // Bloquear edición del documento (el tipo ya es de solo lectura)
                    $("#tipo-documento-field").prop('disabled', true).addClass('campo-protegido');
                    $("#documento-identidad-field").prop('disabled', true).addClass('campo-protegido');

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
                var tipo = tipoDesdePrefijo($('#tipo-documento-field').val());

                var formData = new FormData(this);
                formData.set('tipo_proveedor', tipo);

                // Preparar datos según tipo y LIMPIAR campos del tipo opuesto
                if (tipo === 'juridico') {
                    var rifPrefix = $('#tipo-documento-field').val();
                    var rifNumber = $('#documento-identidad-field').val();
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
                $("#tipo-documento-field").val("V-");
                toggleCampos();
                $("#add-btn").show().prop('disabled', false);
                $("#edit-btn").hide();
                // Desbloquear el documento unificado
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
                var max = parseInt($(this).attr('maxlength'), 10) || 9;
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, max);
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

            // 5. Documento unificado — longitud + AJAX duplicado (según el prefijo)
            $(document).on('blur', '#documento-identidad-field', function () {
                var $input = $(this);
                var val = $input.val().trim();
                var isEdit = $('#id-field').val() !== '';
                var tipo = tipoDesdePrefijo($('#tipo-documento-field').val());
                var minLen = (tipo === 'juridico') ? 5 : 6;
                if (val.length === 0) {
                    marcarInvalido($input, 'El documento es obligatorio.');
                    return;
                }
                if (val.length < minLen) {
                    marcarInvalido($input, 'El documento debe tener al menos ' + minLen + ' dígitos.');
                    return;
                }
                if (isEdit) { marcarValido($input); return; }

                if (tipo === 'juridico') {
                    var fullRif = $('#tipo-documento-field').val() + val;
                    $.get("{{ route('proveedores.check-rif') }}", { rif: fullRif }, function (res) {
                        if (res.exists) {
                            marcarInvalido($input, 'Este RIF ya está registrado.');
                            $('#add-btn').prop('disabled', true);
                        } else {
                            marcarValido($input);
                            $('#add-btn').prop('disabled', false);
                        }
                    });
                } else {
                    $.get("{{ route('proveedores.check-documento') }}", { numero: val }, function (res) {
                        if (res.exists) {
                            marcarInvalido($input, 'Este documento ya está registrado.');
                            $('#add-btn').prop('disabled', true);
                        } else {
                            marcarValido($input);
                            $('#add-btn').prop('disabled', false);
                        }
                    });
                }
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
                    var $rif = $('#documento-identidad-field');
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
            var fdesde = $('#pdf-fecha-desde').val();
            var fhasta = $('#pdf-fecha-hasta').val();
            if (fdesde) params.push('fecha_desde=' + encodeURIComponent(fdesde));
            if (fhasta) params.push('fecha_hasta=' + encodeURIComponent(fhasta));
            window.open(baseUrl + (params.length ? '?' + params.join('&') : ''), '_blank');
            bootstrap.Modal.getInstance(document.getElementById('pdfExportModal'))?.hide();
        });
        $('#pdfExportModal').on('show.bs.modal', function () {
            $('#pdf-filter-tipo').val('');
            $('#pdf-filter-estatus').val('');
            $('#pdf-fecha-desde, #pdf-fecha-hasta').val('');
        });
    </script>
@endpush