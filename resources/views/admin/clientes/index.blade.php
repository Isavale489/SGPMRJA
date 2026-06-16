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
                <h4 class="mb-sm-0">Gestión de Clientes</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Gestión General</a></li>
                        <li class="breadcrumb-item active">Clientes</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>

    {{-- Estilos en public/assets/css/custom.css — sección "MÓDULO MAESTROS — Clientes" --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-maestros">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Listado de Clientes</h5>
                        <div class="flex-shrink-0 d-flex align-items-center gap-3">
                            <!-- Toggle Historial -->
                            @if($historial)
                                <a href="{{ route('clientes.index') }}" class="btn-historial btn-historial-volver">
                                    <i class="ri-arrow-left-line"></i> Solo Activos
                                </a>
                            @else
                                <a href="{{ route('clientes.index', ['historial' => true]) }}" class="btn-historial btn-historial-ver">
                                    <i class="ri-archive-line"></i> Inhabilitados
                                </a>
                            @endif
                            @if(!$historial)
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn"
                                    data-bs-target="#showModal">
                                    <i class="ri-add-line align-bottom me-1"></i> Agregar Cliente
                                </button>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#pdfExportModal">
                                    <i class="ri-file-pdf-fill align-bottom me-1"></i> Exportar PDF
                                </button>
                            </div>
                            @else
                            <div class="d-flex gap-2">
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
                         Copiar este bloque completo a Proveedores, Productos, etc.
                         Solo ajustar las opciones de cada <select> y los data-col-index.
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
                                    placeholder="Buscar cliente..."
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
                                    {{-- Filtro 1: Tipo de Cliente --}}
                                    <div>
                                        <label class="navy-filter-label" for="filter-tipo-cliente">
                                            <i class="ri-user-settings-line"></i> Tipo de Cliente
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-tipo-cliente" data-col-index="6">
                                            <option value="">Todos</option>
                                            <option value="natural">Natural</option>
                                            <option value="juridico">Jurídico</option>
                                            <option value="gubernamental">Gubernamental</option>
                                        </select>
                                    </div>
                                    {{-- Filtro 3: Estado Territorial (Venezuela) --}}
                                    <div>
                                        <label class="navy-filter-label" for="filter-estado-territorial">
                                            <i class="ri-map-pin-line"></i> Estado
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-estado-territorial" data-col-index="8">
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

                    <table id="clientes-table" class="table table-bordered table-striped table-sm align-middle table-operativa table-maestro">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles del Cliente -->
    <div class="modal fade atlantico-modal" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title">Detalles del Cliente</h5>
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
                    <div class="px-4 py-3" style="background:#fbfcfe;">

                        {{-- Identificación --}}
                        <div class="cli-view-card">
                        <div class="cli-view-card-header"><i class="ri-fingerprint-line"></i>Identificación</div>
                        <div class="cli-view-card-body">
                        <div class="row g-3">
                            {{-- Documento SIEMPRE primero --}}
                            <div class="col-sm-6">
                                <div class="d-flex align-items-start">
                                    <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                        <i class="ri-bank-card-line emp-icon--navy"></i>
                                    </div>
                                    <div><small class="text-muted d-block fs-12">Documento</small>
                                    <span class="fw-semibold fs-13" id="view-documento">-</span></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-start">
                                    <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                        <i class="ri-user-settings-line emp-icon--navy"></i>
                                    </div>
                                    <div><small class="text-muted d-block fs-12">Tipo de Cliente</small>
                                    <span class="fw-semibold fs-13" id="view-tipo_cliente">-</span></div>
                                </div>
                            </div>
                            {{-- Natural: Nombre + Apellido (cada uno col-6) --}}
                            <div class="col-sm-6" id="view-block-nombre">
                                <div class="d-flex align-items-start">
                                    <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                        <i class="ri-user-line emp-icon--navy"></i>
                                    </div>
                                    <div><small class="text-muted d-block fs-12">Nombre</small>
                                    <span class="fw-semibold fs-13" id="view-nombre">-</span></div>
                                </div>
                            </div>
                            <div class="col-sm-6" id="view-block-apellido">
                                <div class="d-flex align-items-start">
                                    <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                        <i class="ri-user-follow-line emp-icon--navy"></i>
                                    </div>
                                    <div><small class="text-muted d-block fs-12">Apellido</small>
                                    <span class="fw-semibold fs-13" id="view-apellido">-</span></div>
                                </div>
                            </div>
                            {{-- Jurídico/Gubernamental: Razón Social (col-12 cuando visible) --}}
                            <div class="col-12 d-none" id="view-block-razon-social">
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
                            <div class="col-sm-6">
                                <div class="d-flex align-items-start">
                                    <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                        <i class="ri-government-line emp-icon--navy"></i>
                                    </div>
                                    <div><small class="text-muted d-block fs-12">Estado</small>
                                    <span class="fw-semibold fs-13" id="view-estado-territorial">-</span></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-start">
                                    <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                        <i class="ri-building-line emp-icon--navy"></i>
                                    </div>
                                    <div><small class="text-muted d-block fs-12">Municipio</small>
                                    <span class="fw-semibold fs-13" id="view-ciudad">-</span></div>
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
            <form id="clienteForm" class="modal-content" novalidate>
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="modalTitle">Agregar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id-field" />
                    <div id="edit-shared-persona-notice" class="d-none alert mb-3 py-2 px-3"
                        style="background:rgba(8,145,178,0.08); border:1px solid rgba(8,145,178,0.3); border-radius:8px; font-size:0.82rem; color:#0891b2;">
                        <i class="ri-user-shared-line me-1"></i>
                        Esta persona también está registrada como <strong id="edit-shared-role"></strong>.
                        Los cambios en datos personales afectarán ambos registros.
                    </div>

                    <div class="modal-form-section">
                        <div class="section-header-compact">
                            <div class="modal-form-section-title"><i class="ri-fingerprint-line"></i>Identificación</div>
                        </div>

                        <div class="row g-2 mb-0">
                            <div class="col-md-6">
                                <x-forms.input name="documento_number" label="Documento (Cédula o RIF)"
                                    id="documento-number-field" required maxlength="10" placeholder="Nro. documento"
                                    prependRaw="true">
                                    <x-slot:prepend>
                                        <select class="form-select" id="documento-prefix-field" style="max-width: 70px;">
                                            <option value="V-">V-</option>
                                            <option value="J-">J-</option>
                                            <option value="E-">E-</option>
                                            <option value="G-">G-</option>
                                        </select>
                                    </x-slot:prepend>
                                </x-forms.input>
                                <input type="hidden" id="documento-field" name="documento" />
                                <small class="text-muted"
                                    style="margin-top: -6px; display: block; margin-bottom: 6px;">Máximo
                                    10 dígitos</small>
                                <div id="documento-error" class="invalid-feedback" style="display: none;"></div>
                                <div id="documento-persona-card" class="d-none mt-2 rounded"
                                    style="border:1px solid rgba(8,145,178,0.35); background:rgba(8,145,178,0.06); padding:10px 12px;">
                                    <div style="font-size:0.78rem; font-weight:600; color:#0891b2; margin-bottom:4px;">
                                        <i class="ri-user-shared-line me-1"></i>
                                        Persona ya registrada como <span id="persona-card-role" style="text-transform:capitalize;"></span>
                                    </div>
                                    <div id="persona-card-data" style="font-size:0.8rem; line-height:1.8; margin-bottom:8px;"></div>
                                    <button type="button" id="persona-vincular-btn" class="btn btn-sm"
                                        style="background:#0891b2; color:white; font-size:0.75rem; padding:3px 12px; border-radius:20px;">
                                        <i class="ri-link me-1"></i>Usar estos datos
                                    </button>
                                </div>
                                <div id="documento-vinculado-notice" class="d-none mt-1" style="font-size:0.78rem; color:#0891b2;">
                                    <i class="ri-link me-1"></i><span id="documento-vinculado-text"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <x-forms.select name="tipo_cliente" label="Tipo de Cliente" required id="tipo_cliente-field"
                                    :options="['natural' => 'Natural', 'juridico' => 'Jurídico', 'gubernamental' => 'Gubernamental']" />
                            </div>
                        </div>
                    </div>

                    <div class="modal-form-section">
                        <div class="modal-form-section-title"><i class="ri-user-3-line"></i>Datos del Cliente</div>

                        <div id="campos-persona-natural" class="row g-2 mb-0">
                            <div class="col-md-6">
                                <x-forms.input name="nombre" label="Nombre" placeholder="Nombre" maxlength="100" required
                                    id="nombre-field" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.input name="apellido" label="Apellido" placeholder="Apellido" maxlength="100"
                                    required id="apellido-field" />
                            </div>
                        </div>

                        <div id="campos-razon-social" class="row g-2 mb-0 d-none">
                            <div class="col-12">
                                <x-forms.input name="nombre" label="Razón Social" placeholder="Razón Social de la empresa"
                                    maxlength="200" id="razon-social-field" />
                            </div>
                        </div>
                    </div>

                    <div class="modal-form-section">
                        <div class="modal-form-section-title"><i class="ri-contacts-book-2-line"></i>Contacto</div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <x-forms.input name="email" label="Email" type="email" placeholder="correo@ejemplo.com"
                                    id="email-field" />
                            </div>
                            <div class="col-md-6">
                                <x-forms.input name="telefono_number" label="Teléfono" id="telefono-number-field" required
                                    maxlength="7" placeholder="1234567" prependRaw="true">
                                    <x-slot:prepend>
                                        <select class="form-select" id="telefono-prefix-field"
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
                                <input type="hidden" id="telefono-field" name="telefono" />
                                <div id="telefono-error" class="invalid-feedback" style="display: none;"></div>
                            </div>
                        </div>

                        <div class="row g-2 mb-0">
                            <div class="col-12">
                                <x-forms.textarea name="direccion" label="Dirección" placeholder="Dirección completa"
                                    maxlength="500" required id="direccion-field" rows="2" />
                            </div>
                        </div>
                    </div>

                    <div class="modal-form-section">
                        <div class="modal-form-section-title"><i class="ri-map-pin-2-line"></i>Ubicación</div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label for="estado_territorial-field" class="form-label required">Estado</label>
                                <select name="estado_territorial" id="estado_territorial-field" class="form-select"
                                    required>
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
                            <div class="col-md-6">
                                <label for="ciudad-field" class="form-label required">Municipio</label>
                                <select name="ciudad" id="ciudad-field" class="form-select" required>
                                    <option value="">Primero seleccione un estado</option>
                                </select>
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
                        <x-ui.button-save id="edit-btn" text="Actualizar" icon="ri-save-line" loading-text="Actualizando..."
                            style="display: none;" />
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Exportar PDF con filtros --}}
    <div class="modal fade atlantico-modal" id="pdfExportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 360px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-file-pdf-line me-2"></i>Exportar PDF
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Filtra qué clientes incluir en el reporte.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="pdf-filter-estado">Estado</label>
                        <select class="form-select" id="pdf-filter-estado">
                            <option value="">Todos los estados</option>
                            <option value="1">Solo Activos</option>
                            <option value="0">Solo Inactivos</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold" for="pdf-filter-tipo">Tipo de Cliente</label>
                        <select class="form-select" id="pdf-filter-tipo">
                            <option value="">Todos los tipos</option>
                            <option value="natural">Natural</option>
                            <option value="juridico">Jurídico</option>
                            <option value="gubernamental">Gubernamental</option>
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
    <script src="{{ URL::asset('/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/municipios-venezuela.js') }}"></script>
    <script>
        $(function () {
            // Activa tooltips para todos los elementos con atributo title
            $(document).on('mouseenter', '[title]', function () {
                $(this).tooltip({ container: 'body' }).tooltip('show');
            });
            $(document).on('mouseleave', '[title]', function () {
                $(this).tooltip('dispose');
            });
        });

        // Sanitización del número de teléfono (campo visible, solo dígitos, máx 7)
        $(document).on('input', '#telefono-number-field', function () {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 7);
        });

        // === Capitalizar solo la primera letra del campo dirección ===
        $(document).on('blur', '#direccion-field', function () {
            var val = $(this).val();
            if (val && val.length > 0) {
                $(this).val(val.charAt(0).toUpperCase() + val.slice(1));
            }
        });

        // Validación en tiempo real para nombre (solo letras y espacios)
        $(document).on('input', '#nombre-field', function () {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        });

        // Validación en tiempo real para apellido (solo letras y espacios)
        $(document).on('input', '#apellido-field', function () {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        });

        // Validación en tiempo real para documento (solo números, maxlength dinámico)
        $(document).on('input', '#documento-number-field', function () {
            var maxLen = getDocMaxLength();
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, maxLen);
        });

        // Vincular persona existente al formulario de cliente
        $(document).on('click', '#persona-vincular-btn', function () {
            var p = $(this).data('persona');
            var role = $(this).data('role');

            if (p.tipo_documento) $('#documento-prefix-field').val(p.tipo_documento);

            // Datos personales
            $('#nombre-field').val(p.nombre || '').prop('readonly', true).addClass('bg-light').css('cursor', 'not-allowed');
            $('#apellido-field').val(p.apellido || '').prop('readonly', true).addClass('bg-light').css('cursor', 'not-allowed');
            $('#razon-social-field').val(p.nombre || '').prop('readonly', true).addClass('bg-light').css('cursor', 'not-allowed');
            $('#email-field').val(p.email || '').prop('readonly', true).addClass('bg-light').css('cursor', 'not-allowed');

            // Teléfono
            if (p.telefono && p.telefono.includes('-')) {
                var parts = p.telefono.split('-');
                $('#telefono-prefix-field').val(parts[0]).prop('disabled', true);
                $('#telefono-number-field').val(parts[1]).prop('readonly', true).addClass('bg-light').css('cursor', 'not-allowed');
            }

            // Dirección
            if (p.direccion) $('#direccion-field').val(p.direccion).prop('readonly', true).addClass('bg-light').css('cursor', 'not-allowed');

            // Estado y Municipio
            if (p.estado_geografico) {
                $('#estado_territorial-field').val(p.estado_geografico).trigger('change');
                if (p.ciudad) $('#ciudad-field').val(p.ciudad);
                $('#estado_territorial-field').prop('disabled', true);
                $('#ciudad-field').prop('disabled', true);
            }

            // Mostrar aviso y habilitar guardar
            $('#documento-persona-card').addClass('d-none');
            $('#documento-vinculado-text').text('Datos vinculados de persona registrada como ' + role + '.');
            $('#documento-vinculado-notice').removeClass('d-none');
            $('#add-btn').prop('disabled', false);
        });

        // Validación onblur para nombre
        $(document).on('blur', '#nombre-field', function () {
            let value = $(this).val().trim();
            if (value.length === 0) {
                marcarInvalido($(this), 'El nombre es obligatorio.');
            } else if (value.length < 2) {
                marcarInvalido($(this), 'El nombre debe tener al menos 2 caracteres.');
            } else {
                marcarValido($(this));
            }
        });

        // Validación onblur para apellido
        $(document).on('blur', '#apellido-field', function () {
            let value = $(this).val().trim();
            if (value.length > 0 && value.length < 2) {
                marcarInvalido($(this), 'El apellido debe tener al menos 2 caracteres.');
            } else if (value.length >= 2) {
                marcarValido($(this));
            } else {
                limpiarValidacion($(this));
            }
        });

        // Validación onblur para documento
        // Validación onblur para documento
        $(document).on('blur', '#documento-number-field', function () {
            let value = $(this).val().trim();
            let $input = $(this);
            let $error = $('#documento-error');
            let isEditMode = $('#id-field').val() !== ''; // Comprobar si estamos en edición

            if (value.length < 6) {
                $input.addClass('is-invalid');
                var maxLen = getDocMaxLength();
                $error.text('El documento debe tener entre 6 y ' + maxLen + ' dígitos.').show();
            } else {
                // Si la longitud es válida y NO estamos en edición, verificamos duplicados
                if (!isEditMode) {
                    $.ajax({
                        url: "{{ route('clientes.check-documento') }}",
                        method: 'GET',
                        data: { numero: value },
                        success: function (response) {
                            if (response.exists) {
                                $input.addClass('is-invalid');
                                $error.text('Este cliente ya se encuentra registrado.').show();
                                $('#add-btn').prop('disabled', true);
                                $('#documento-persona-card').addClass('d-none');
                                $('#documento-vinculado-notice').addClass('d-none');
                            } else {
                                $input.removeClass('is-invalid').addClass('is-valid');
                                $error.hide();
                                if (response.other_role && response.persona) {
                                    var p = response.persona;
                                    var nombreCompleto = p.nombre + (p.apellido ? ' ' + p.apellido : '');
                                    var detalles = '<strong>' + nombreCompleto + '</strong>';
                                    if (p.email) detalles += '<br>' + p.email;
                                    if (p.telefono) detalles += '<br>' + p.telefono;
                                    $('#persona-card-role').text(response.other_role);
                                    $('#persona-card-data').html(detalles);
                                    $('#persona-vincular-btn').data('persona', p).data('role', response.other_role);
                                    $('#documento-persona-card').removeClass('d-none');
                                    $('#add-btn').prop('disabled', true);
                                } else {
                                    $('#documento-persona-card').addClass('d-none');
                                    $('#add-btn').prop('disabled', false);
                                }
                            }
                        },
                        error: function () {
                            console.error('Error al verificar documento');
                        }
                    });
                } else {
                    $input.removeClass('is-invalid').addClass('is-valid');
                    $error.hide();
                }
            }
        });

        // Validación onblur para teléfono (campo visible: solo los 7 dígitos)
        $(document).on('blur', '#telefono-number-field', function () {
            let value = $(this).val().trim();
            if (value.length === 0) {
                marcarInvalido($(this), 'El teléfono es obligatorio.');
            } else if (!/^[0-9]{7}$/.test(value)) {
                marcarInvalido($(this), 'El número debe tener exactamente 7 dígitos.');
            } else {
                marcarValido($(this));
            }
        });

        // Validación onblur para email
        $(document).on('blur', '#email-field', function () {
            let value = $(this).val().trim();
            let $input = $(this);
            let excludeId = $('#id-field').val();
            let regex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;

            if (value.length === 0) {
                limpiarValidacion($input);
                return;
            }

            if (!regex.test(value)) {
                marcarInvalido($input, 'Ingrese un email válido (ej: usuario@dominio.com).');
                return;
            }

            $.ajax({
                url: "{{ route('clientes.check-email') }}",
                method: 'GET',
                data: { email: value, exclude_id: excludeId },
                success: function (response) {
                    if (response.exists) {
                        marcarInvalido($input, 'Este correo ya está registrado.');
                        $('#add-btn').prop('disabled', true);
                    } else {
                        marcarValido($input);
                        $('#add-btn').prop('disabled', false);
                    }
                },
                error: function () {
                    console.error('Error al verificar email');
                }
            });
        });

        // Limpiar validaciones al abrir modal
        $('#showModal').on('show.bs.modal', function () {
            $('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            $('.invalid-feedback').hide();
        });

        // === Lógica dinámica: Natural vs Jurídico/Gubernamental ===
        function toggleClienteFields() {
            var tipo = $('#tipo_cliente-field').val();
            var esNatural = (tipo === 'natural' || tipo === '');
            var $prefixSelect = $('#documento-prefix-field');
            var $docInput = $('#documento-number-field');

            if (esNatural) {
                // Mostrar Nombre + Apellido, ocultar Razón Social
                $('#campos-persona-natural').removeClass('d-none');
                $('#nombre-field').prop('required', true).prop('disabled', false);
                $('#apellido-field').prop('required', true).prop('disabled', false);

                $('#campos-razon-social').addClass('d-none');
                $('#razon-social-field').prop('required', false).prop('disabled', true).val('');

                // Prefijos: V- y E- para Natural
                $prefixSelect.html('<option value="V-">V-</option><option value="E-">E-</option>');
                $prefixSelect.prop('disabled', false);
                // Maxlength: 8 dígitos para cédula
                $docInput.attr('maxlength', '8');
                // Truncar si el valor actual excede el nuevo máximo
                if ($docInput.val().length > 8) {
                    $docInput.val($docInput.val().slice(0, 8));
                }

            } else if (tipo === 'juridico') {
                // Ocultar Nombre + Apellido, mostrar Razón Social
                $('#campos-persona-natural').addClass('d-none');
                $('#nombre-field').prop('required', false).prop('disabled', true).val('');
                $('#apellido-field').prop('required', false).prop('disabled', true).val('');

                $('#campos-razon-social').removeClass('d-none');
                $('#razon-social-field').prop('required', true).prop('disabled', false);

                // Prefijo: solo J- (bloqueado)
                $prefixSelect.html('<option value="J-">J-</option>');
                $prefixSelect.prop('disabled', true);
                // Maxlength: 9 dígitos para RIF
                $docInput.attr('maxlength', '9');
                if ($docInput.val().length > 9) {
                    $docInput.val($docInput.val().slice(0, 9));
                }

            } else if (tipo === 'gubernamental') {
                // Ocultar Nombre + Apellido, mostrar Razón Social
                $('#campos-persona-natural').addClass('d-none');
                $('#nombre-field').prop('required', false).prop('disabled', true).val('');
                $('#apellido-field').prop('required', false).prop('disabled', true).val('');

                $('#campos-razon-social').removeClass('d-none');
                $('#razon-social-field').prop('required', true).prop('disabled', false);

                // Prefijo: solo G- (bloqueado)
                $prefixSelect.html('<option value="G-">G-</option>');
                $prefixSelect.prop('disabled', true);
                // Maxlength: 9 dígitos para RIF gubernamental
                $docInput.attr('maxlength', '9');
                if ($docInput.val().length > 9) {
                    $docInput.val($docInput.val().slice(0, 9));
                }
            }
        }

        // Obtener maxlength dinámico según prefijo actual
        function getDocMaxLength() {
            var prefix = $('#documento-prefix-field').val();
            return (prefix === 'J-' || prefix === 'G-') ? 9 : 8;
        }

        // Disparar al cambiar el tipo de cliente
        $(document).on('change', '#tipo_cliente-field', function () {
            toggleClienteFields();
        });

        // Estado inicial al cargar la página
        toggleClienteFields();

        // Validación onblur para Razón Social (cuando visible)
        $(document).on('blur', '#razon-social-field', function () {
            let value = $(this).val().trim();
            if (value.length === 0) {
                marcarInvalido($(this), 'La razón social es obligatoria.');
            } else if (value.length < 3) {
                marcarInvalido($(this), 'La razón social debe tener al menos 3 caracteres.');
            } else {
                marcarValido($(this));
            }
        });

        // Validación onblur para Tipo de Cliente
        $(document).on('blur', '#tipo_cliente-field', function () {
            let value = $(this).val();
            if (!value) {
                marcarInvalido($(this), 'Seleccione el tipo de cliente.');
            } else {
                marcarValido($(this));
            }
        });
    </script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });
            function generateButtons(clienteId, isTrashed) {
                var sVer = '<button class="btn btn-sm btn-soft-info view-item-btn" data-id="' + clienteId + '" title="Ver"><i class="ri-eye-fill"></i></button>';
                var items;
                if (isTrashed) {
                    items = '<li><button type="button" class="dropdown-item act-item act-restore restore-item-btn" data-id="' + clienteId + '"><span class="act-ic"><i class="ri-arrow-go-back-line"></i></span>Restaurar</button></li>';
                } else {
                    items =
                        '<li><button type="button" class="dropdown-item act-item act-edit edit-item-btn" data-id="' + clienteId + '"><span class="act-ic"><i class="ri-pencil-fill"></i></span>Editar</button></li>' +
                        '<li><button type="button" class="dropdown-item act-item act-del remove-item-btn" data-id="' + clienteId + '"><span class="act-ic"><i class="ri-forbid-line"></i></span>Inhabilitar</button></li>';
                }
                var menu =
                    '<div class="dropdown d-inline-block">' +
                        '<button class="btn btn-sm btn-soft-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Más acciones"><i class="ri-more-2-fill"></i></button>' +
                        '<ul class="dropdown-menu dropdown-menu-end actions-menu">' + items + '</ul>' +
                    '</div>';
                return '<div class="d-flex gap-1 justify-content-center align-items-center">' + sVer + menu + '</div>';
            }

            function formatDate(dateStr) {
                if (!dateStr) return 'N/A';

                if (typeof dateStr === 'string') {
                    var parts = dateStr.trim().split(' ');
                    var datePart = parts[0] || '';
                    if (/^\d{2}\/\d{2}\/\d{4}$/.test(datePart)) {
                        return datePart;
                    }
                }

                var date = new Date(dateStr);
                if (isNaN(date.getTime())) return dateStr;

                var day = String(date.getDate()).padStart(2, '0');
                var month = String(date.getMonth() + 1).padStart(2, '0');
                var year = date.getFullYear();

                return day + '/' + month + '/' + year;
            }

            var table = $('#clientes-table').DataTable({
                ajax: {
                    url: "{{ route('clientes.data') }}",
                    dataSrc: 'data',
                    data: function (d) {
                        // ── Filtros avanzados: enviar valores al server ──
                        d.filter_tipo_cliente        = $('#filter-tipo-cliente').val();
                        d.historial                  = @json($historial);
                        d.filter_estado_territorial  = $('#filter-estado-territorial').val();
                        d.filter_orden               = $('#filter-orden').val();
                    }
                },
                columns: [
                    { data: 'documento' },                           // col 0
                    {                                                 // col 1
                        data: null,
                        render: function (data, type, row) {
                            if (row.tipo_cliente === 'juridico' || row.tipo_cliente === 'gubernamental') {
                                return row.nombre || 'N/A';
                            }
                            return (row.nombre || '') + ' ' + (row.apellido || '');
                        }
                    },
                    {                                                 // col 2
                        data: 'tipo_cliente', render: function (data) {
                            if (data === 'natural') return '<span class="badge-tipo badge-tipo-natural"><i class="ri-user-line"></i> Natural</span>';
                            if (data === 'juridico') return '<span class="badge-tipo badge-tipo-juridico"><i class="ri-building-line"></i> Jurídico</span>';
                            if (data === 'gubernamental') return '<span class="badge-tipo badge-tipo-gubernamental"><i class="ri-government-line"></i> Gubernamental</span>';
                            return data;
                        }
                    },
                    { data: 'telefono' },                             // col 3
                    {                                                 // col 4
                        data: 'email',
                        render: function (data) {
                            if (!data) return '<span class="text-muted">—</span>';
                            return '<span title="' + data + '" style="cursor:default;">' + data + '</span>';
                        }
                    },
                    { data: null, orderable: false, render: function (data, type, row) { return generateButtons(row.id, row.trashed); } }
                ],
                order: [],
                dom: 'rtip',
                buttons: [
                    { extend: 'copy', exportOptions: { columns: [0, 1, 2, 3, 4] } },
                    { extend: 'excel', exportOptions: { columns: [0, 1, 2, 3, 4] } }
                ],
                language: lenguajeData
            });

            // ══════════════════════════════════════════════════════
            // BÚSQUEDA + FILTROS AVANZADOS — Patrón Maestro S-07
            // Header unificado: búsqueda global + panel colapsable
            // ══════════════════════════════════════════════════════

            // ── Badge: actualizar contador de filtros activos + punto rojo ──
            function updateFilterBadge() {
                var count = 0;
                if ($('#filter-tipo-cliente').val() !== '')                          count++;
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
                $('#filter-tipo-cliente').val('');
                $('#filter-estado-territorial').val('');
                $('#filter-orden').val('recientes');
                $('#custom-search-input').val('');
                updateFilterBadge();
                table.search('').ajax.reload(function () {
                    updateFilterBadge();
                });
            });


            // Ajustar columnas cuando se redimensiona la ventana
            $(window).on('resize', function () {
                table.columns.adjust();
            });
            // Ajustar después de carga inicial
            setTimeout(function () {
                table.columns.adjust();
            }, 100);
            function resetForm() {
                $("#clienteForm").trigger("reset");
                $("#id-field").val("");
                $("#modalTitle").text("Agregar Cliente");
                $("#add-btn").show().prop('disabled', false);
                $("#edit-btn").hide();
                $("#documento-prefix-field").val("V-");
                $("#documento-prefix-field").prop('disabled', false).removeClass('campo-protegido');
                $("#documento-number-field").val("");
                $("#documento-number-field").prop('disabled', false).removeClass('campo-protegido');
                // Reset teléfono
                $("#telefono-prefix-field").val("0424");
                $("#telefono-number-field").val("");
                // Resetear tipo cliente a Natural y actualizar campos
                $("#tipo_cliente-field").val("");
                $("#razon-social-field").val("");
                toggleClienteFields();
                // Desbloquear campos vinculados de persona existente
                $('#nombre-field, #apellido-field, #razon-social-field, #email-field, #telefono-number-field, #direccion-field').prop('readonly', false).removeClass('bg-light').css('cursor', '');
                $('#telefono-prefix-field, #estado_territorial-field, #ciudad-field').prop('disabled', false);
                $('#documento-persona-card').addClass('d-none');
                $('#documento-vinculado-notice').addClass('d-none');
                $('#edit-shared-persona-notice').addClass('d-none');
            }
            function setEditMode() {
                $("#modalTitle").text("Actualizar Cliente");
                $("#add-btn").hide();
                $("#edit-btn").show();
                // Bloquear edición de documento
                $("#documento-prefix-field").prop('disabled', true).addClass('campo-protegido');
                $("#documento-number-field").prop('disabled', true).addClass('campo-protegido');
                // Limpiar card de vinculación (por si venía del flujo crear)
                $('#documento-persona-card').addClass('d-none');
                $('#documento-vinculado-notice').addClass('d-none');
                $('#edit-shared-persona-notice').addClass('d-none');
            }
            $("#create-btn").click(function () { resetForm(); });
            $("#showModal").on('hidden.bs.modal', function () { resetForm(); });

            // Dropdown dependiente: Poblar municipios cuando cambia el estado
            $("#estado_territorial-field").on('change', function () {
                const estado = $(this).val();
                const municipios = getMunicipios(estado);
                const ciudadSelect = $("#ciudad-field");

                // Limpiar opciones anteriores
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

            function validarFormularioCliente() {
                let esValido = true;
                let tipo = $('#tipo_cliente-field').val();
                let esNatural = (tipo === 'natural' || tipo === '');

                if (esNatural) {
                    let $nombre = $('#nombre-field');
                    let nombre = $nombre.val().trim();
                    if (nombre.length === 0) {
                        marcarInvalido($nombre, 'El nombre es obligatorio.');
                        esValido = false;
                    } else if (nombre.length < 2) {
                        marcarInvalido($nombre, 'El nombre debe tener al menos 2 caracteres.');
                        esValido = false;
                    } else { marcarValido($nombre); }

                    let $apellido = $('#apellido-field');
                    let apellido = $apellido.val().trim();
                    if (apellido.length === 0) {
                        marcarInvalido($apellido, 'El apellido es obligatorio.');
                        esValido = false;
                    } else if (apellido.length < 2) {
                        marcarInvalido($apellido, 'El apellido debe tener al menos 2 caracteres.');
                        esValido = false;
                    } else { marcarValido($apellido); }
                } else {
                    let $razon = $('#razon-social-field');
                    let razon = $razon.val().trim();
                    if (razon.length === 0) {
                        marcarInvalido($razon, 'La razón social es obligatoria.');
                        esValido = false;
                    } else if (razon.length < 3) {
                        marcarInvalido($razon, 'La razón social debe tener al menos 3 caracteres.');
                        esValido = false;
                    } else { marcarValido($razon); }
                }

                let $doc = $('#documento-number-field');
                let doc = $doc.val().trim();
                if (doc.length < 6) {
                    marcarInvalido($doc, 'El documento debe tener entre 6 y ' + getDocMaxLength() + ' dígitos.');
                    esValido = false;
                } else { marcarValido($doc); }

                let $tel = $('#telefono-number-field');
                let tel = $tel.val().trim();
                if (tel.length === 0) {
                    marcarInvalido($tel, 'El teléfono es obligatorio.');
                    esValido = false;
                } else if (!/^[0-9]{7}$/.test(tel)) {
                    marcarInvalido($tel, 'El número debe tener exactamente 7 dígitos.');
                    esValido = false;
                } else { marcarValido($tel); }

                let $email = $('#email-field');
                let emailVal = $email.val().trim();
                let emailRegex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
                if (emailVal.length > 0 && !emailRegex.test(emailVal)) {
                    marcarInvalido($email, 'Ingrese un email válido (ej: usuario@dominio.com).');
                    esValido = false;
                }

                let $dir = $('#direccion-field');
                if (!$dir.val().trim()) {
                    marcarInvalido($dir, 'La dirección es obligatoria.');
                    esValido = false;
                } else { marcarValido($dir); }

                let $estado = $('#estado_territorial-field');
                if (!$estado.val()) {
                    marcarInvalido($estado, 'El estado es obligatorio.');
                    esValido = false;
                } else { marcarValido($estado); }

                let $ciudad = $('#ciudad-field');
                if (!$ciudad.val()) {
                    marcarInvalido($ciudad, 'El municipio es obligatorio.');
                    esValido = false;
                } else { marcarValido($ciudad); }

                return esValido;
            }

            $('#add-btn').click(function (e) {
                e.preventDefault();

                if (!validarFormularioCliente()) {
                    return;
                }

                // Se deshabilita el botón para evitar múltiples envíos
                $(this).prop('disabled', true);
                $("#clienteForm").submit();
            });

            $("#clienteForm").on("submit", function (e) {
                e.preventDefault();
                var id = $("#id-field").val();
                var url = id ? "{{ route('clientes.update', ':id') }}".replace(':id', id) : "{{ route('clientes.store') }}";
                var method = id ? "PUT" : "POST";
                var documentoCompleto = $("#documento-prefix-field").val() + $("#documento-number-field").val();
                $("#documento-field").val(documentoCompleto);
                // Concatenar teléfono: prefijo-número
                var telefonoCompleto = $("#telefono-prefix-field").val() + "-" + $("#telefono-number-field").val();
                $("#telefono-field").val(telefonoCompleto);
                var formData = $(this).serialize();
                if (method === 'PUT') { formData += '&_method=PUT'; }
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        $("#showModal").modal("hide");
                        $("#clienteForm").trigger("reset");
                        table.ajax.reload();
                        Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.message, showConfirmButton: false, timer: 2000 });
                        $('#add-btn').prop('disabled', false); // Re-enable button on success
                    },
                    error: function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON.message });
                        $('#add-btn').prop('disabled', false); // Re-enable button on error
                    }
                });
            });
            $(document).on("click", ".view-item-btn", function () {
                var id = $(this).data("id");
                $.get("{{ route('clientes.show', '') }}/" + id, function (data) {
                    $("#viewModal").modal("show");
                    var tipoTexto = data.tipo_cliente === 'natural' ? 'Natural' : (data.tipo_cliente === 'juridico' ? 'Jurídico' : 'Gubernamental');
                    var fullName, initials;
                    if (data.tipo_cliente === 'natural') {
                        $("#view-block-nombre").removeClass('d-none');
                        $("#view-block-apellido").removeClass('d-none');
                        $("#view-block-razon-social").addClass('d-none');
                        $("#view-nombre").text(data.nombre || 'N/A');
                        $("#view-apellido").text(data.apellido || 'N/A');
                        fullName = (data.nombre || '') + (data.apellido ? ' ' + data.apellido : '');
                        initials = (data.nombre ? data.nombre.charAt(0) : '') + (data.apellido ? data.apellido.charAt(0) : '');
                    } else {
                        $("#view-block-nombre").addClass('d-none');
                        $("#view-block-apellido").addClass('d-none');
                        $("#view-block-razon-social").removeClass('d-none');
                        $("#view-razon-social").text(data.nombre || 'N/A');
                        fullName = data.nombre || 'N/A';
                        var words = (data.nombre || '').trim().split(/\s+/);
                        initials = words.length >= 2 ? words[0].charAt(0) + words[1].charAt(0) : (words[0] ? words[0].charAt(0) : '?');
                    }
                    $("#view-hero-avatar").text(initials.toUpperCase());
                    $("#view-hero-name").text(fullName.trim() || 'N/A');
                    $("#view-hero-doc").text(data.documento || 'N/A');
                    $("#view-hero-date").text(formatDate(data.created_at));
                    $("#view-tipo_cliente").text(tipoTexto);
                    $("#view-email").text(data.email || 'N/A');
                    $("#view-telefono").text(data.telefono || 'N/A');
                    $("#view-documento").text(data.documento || 'N/A');
                    $("#view-direccion").text(data.direccion || 'N/A');
                    $("#view-estado-territorial").text(data.estado_territorial || 'N/A');
                    $("#view-ciudad").text(data.ciudad || 'N/A');
                    $("#view-estatus").html(data.trashed ? '<span class="badge rounded-pill bg-danger">Inhabilitado</span>' : '<span class="badge rounded-pill bg-success">Activo</span>');
                });
            });
            $(document).on("click", ".edit-item-btn", function () {
                var id = $(this).data("id");
                $.get("{{ route('clientes.edit', ':id') }}".replace(':id', id), function (data) {
                    setEditMode();
                    if (data.other_role) {
                        $('#edit-shared-role').text(data.other_role);
                        $('#edit-shared-persona-notice').removeClass('d-none');
                    }
                    $("#id-field").val(data.id);
                    $("#nombre-field").val(data.nombre || '');
                    $("#apellido-field").val(data.apellido || '');
                    $("#tipo_cliente-field").val(data.tipo_cliente);
                    // Actualizar visibilidad de campos según tipo
                    toggleClienteFields();
                    // Si es Jurídico/Gubernamental, llenar Razón Social con nombre
                    if (data.tipo_cliente === 'juridico' || data.tipo_cliente === 'gubernamental') {
                        $("#razon-social-field").val(data.nombre || '');
                    }
                    $("#email-field").val(data.email || '');
                    // Separar teléfono en prefijo y número
                    if (data.telefono && data.telefono.includes('-')) {
                        var telParts = data.telefono.split('-');
                        $("#telefono-prefix-field").val(telParts[0]);
                        $("#telefono-number-field").val(telParts[1]);
                    } else if (data.telefono) {
                        // Si no tiene guión, asumir formato 0424XXXXXXX
                        $("#telefono-prefix-field").val(data.telefono.slice(0, 4));
                        $("#telefono-number-field").val(data.telefono.slice(4));
                    }
                    if (data.documento) {
                        $("#documento-prefix-field").val(data.documento.slice(0, 2));
                        $("#documento-number-field").val(data.documento.slice(2));
                    }
                    $("#direccion-field").val(data.direccion || '');

                    // Primero establecer el estado
                    $("#estado_territorial-field").val(data.estado_territorial || '');

                    // Poblar los municipios del estado seleccionado
                    const estado = data.estado_territorial || '';
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

                    // Ahora seleccionar el municipio guardado
                    $("#ciudad-field").val(data.ciudad || '');
                    $("#showModal").modal("show");
                });
            });
            $(document).on("click", ".remove-item-btn", function () {
                var id = $(this).data("id");
                window.scrollTo(0, 0);
                document.activeElement.blur();
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "El cliente será inhabilitado y moverá al historial.",
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
                            url: "{{ route('clientes.destroy', ':id') }}".replace(':id', id),
                            method: "DELETE",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name=\'csrf-token\']').attr('content')
                            },
                            success: function (response) {
                                table.ajax.reload();
                                // Mostrar mensaje con warning si el cliente tenía relaciones
                                if (response.warning) {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Cliente Inhabilitado',
                                        html: '<p>' + response.message + '</p><p class="text-muted small">' + response.warning + '</p>'
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Inhabilitado',
                                        text: response.message
                                    });
                                }
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON.message || 'Ocurrió un error al inhabilitar el cliente'
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
                    text: "¿Estás seguro de que deseas restaurar este cliente?",
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
                            url: "{{ url('clientes') }}/" + id + "/restore",
                            method: "POST",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                table.ajax.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Restaurado!',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON?.message || 'No se pudo restaurar el cliente'
                                });
                            }
                        });
                    }
                });
            });
            $("#create-btn").click(function () {
                $('#id-field').val('');
                $('#clienteForm')[0].reset();
                $('#modalTitle').text('Agregar Cliente');
                $('#add-btn').show();
                $('#edit-btn').hide();
                $('#clienteForm').find('input, select, textarea').removeClass('is-invalid is-valid');
                $('#clienteForm').find('.invalid-feedback').hide();
            });
            $("#edit-btn").on("click", function () { $("#clienteForm").submit(); });
        });

        // PDF Export Modal
        $('#btn-generar-pdf').on('click', function () {
            var baseUrl = '{{ route('clientes.reporte.pdf') }}';
            var params = [];
            var estado = $('#pdf-filter-estado').val();
            var tipo   = $('#pdf-filter-tipo').val();
            if (estado !== '') params.push('estado=' + encodeURIComponent(estado));
            if (tipo   !== '') params.push('tipo_cliente=' + encodeURIComponent(tipo));
            var fdesde = $('#pdf-fecha-desde').val();
            var fhasta = $('#pdf-fecha-hasta').val();
            if (fdesde) params.push('fecha_desde=' + encodeURIComponent(fdesde));
            if (fhasta) params.push('fecha_hasta=' + encodeURIComponent(fhasta));
            var url = baseUrl + (params.length ? '?' + params.join('&') : '');
            window.open(url, '_blank');
            bootstrap.Modal.getInstance(document.getElementById('pdfExportModal'))?.hide();
        });
        $('#pdfExportModal').on('show.bs.modal', function () {
            $('#pdf-filter-estado').val('');
            $('#pdf-filter-tipo').val('');
            $('#pdf-fecha-desde, #pdf-fecha-hasta').val('');
        });
    </script>
@endpush