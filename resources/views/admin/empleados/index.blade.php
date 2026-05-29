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
                <h4 class="mb-sm-0">Gestión de Empleados</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Gestión General</a></li>
                        <li class="breadcrumb-item active">Empleados</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>

    {{-- Estilos en public/assets/css/custom.css — sección "MÓDULO MAESTROS — Empleados" --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-maestros">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Listado de Empleados</h5>
                        <div class="flex-shrink-0 d-flex align-items-center gap-3">
                            <!-- Toggle Historial -->
                            @if($historial)
                                <a href="{{ route('empleados.index') }}" class="btn-historial btn-historial-volver">
                                    <i class="ri-arrow-left-line"></i> Solo Activos
                                </a>
                            @else
                                <a href="{{ route('empleados.index', ['historial' => true]) }}"
                                    class="btn-historial btn-historial-ver">
                                    <i class="ri-time-line"></i> Ver Historial
                                </a>
                            @endif
                            <div class="d-flex gap-2">
                                <a href="{{ url('departamentos') }}" class="btn btn-link-depto" title="Ir al catálogo de departamentos">
                                    <i class="ri-building-line align-bottom me-1"></i> Departamentos
                                </a>
                                <a href="{{ url('cargos') }}" class="btn btn-link-cargo" title="Ir al catálogo de cargos">
                                    <i class="ri-briefcase-line align-bottom me-1"></i> Cargos
                                </a>
                                @if(!$historial)
                                    <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn"
                                        data-bs-target="#showModal">
                                        <i class="ri-add-line align-bottom me-1"></i> Agregar Empleado
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
                                    placeholder="Buscar empleado..."
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
                                    {{-- Filtro 1: Departamento --}}
                                    <div>
                                        <label class="navy-filter-label" for="filter-departamento">
                                            <i class="ri-building-2-line"></i> Departamento
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-departamento">
                                            <option value="">Todos</option>
                                            @foreach($departamentos as $id => $nombre)
                                                <option value="{{ $id }}">{{ $nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- Filtro 2: Cargo --}}
                                    <div>
                                        <label class="navy-filter-label" for="filter-cargo">
                                            <i class="ri-briefcase-line"></i> Cargo
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-cargo">
                                            <option value="">Todos</option>
                                            @foreach($cargos as $id => $nombre)
                                                <option value="{{ $id }}">{{ $nombre }}</option>
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

                    <table id="empleados-table" class="table table-bordered table-striped table-sm align-middle table-operativa table-maestro">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Nombre Completo</th>
                                <th>Teléfono</th>
                                <th>Cargo</th>
                                <th>Departamento</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles -->
    <!-- Modal para ver detalles del Empleado -->
    <div class="modal fade atlantico-modal" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title">Detalles del Empleado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Columna Izquierda: Datos Personales -->
                        <div class="col-lg-6">
                            <!-- Card Datos Personales -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header border-0">
                                    <h6 class="mb-0">
                                        <i class="ri-user-line me-2"></i>Datos Personales
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center emp-icon-box emp-icon-box--navy">
                                                    <i class="ri-user-line emp-icon--navy"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Nombre Completo</small>
                                                    <span class="fw-semibold" id="view-nombre-completo">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center emp-icon-box emp-icon-box--green">
                                                    <i class="ri-bank-card-line emp-icon--green"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Documento</small>
                                                    <span class="fw-semibold" id="view-documento">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center emp-icon-box emp-icon-box--teal">
                                                    <i class="ri-user-heart-line emp-icon--teal"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Género</small>
                                                    <span class="fw-semibold" id="view-genero">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center emp-icon-box emp-icon-box--navy">
                                                    <i class="ri-cake-2-line emp-icon--navy"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Fecha Nacimiento</small>
                                                    <span class="fw-semibold" id="view-fecha-nacimiento">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Contacto -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header border-0">
                                    <h6 class="mb-0">
                                        <i class="ri-contacts-line me-2"></i>Contacto y Ubicación
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center emp-icon-box emp-icon-box--navy">
                                                    <i class="ri-mail-line emp-icon--navy"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Email</small>
                                                    <span class="fw-semibold" id="view-email">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center emp-icon-box emp-icon-box--teal">
                                                    <i class="ri-phone-line emp-icon--teal"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Teléfono</small>
                                                    <span class="fw-semibold" id="view-telefono">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center emp-icon-box emp-icon-box--green">
                                                    <i class="ri-home-4-line emp-icon--green"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Dirección</small>
                                                    <span class="fw-semibold" id="view-direccion">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center emp-icon-box emp-icon-box--navy">
                                                    <i class="ri-building-line emp-icon--navy"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Municipio</small>
                                                    <span class="fw-semibold" id="view-ciudad">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Datos Laborales -->
                        <div class="col-lg-6">
                            <!-- Card Datos Laborales -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header border-0">
                                    <h6 class="mb-0">
                                        <i class="ri-briefcase-line me-2"></i>Datos Laborales
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center emp-icon-box emp-icon-box--green">
                                                    <i class="ri-hashtag emp-icon--green"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Código</small>
                                                    <span class="fw-semibold" id="view-codigo">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center emp-icon-box emp-icon-box--teal">
                                                    <i class="ri-user-star-line emp-icon--teal"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Cargo</small>
                                                    <span class="fw-semibold" id="view-cargo">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center emp-icon-box emp-icon-box--navy">
                                                    <i class="ri-building-2-line emp-icon--navy"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Departamento</small>
                                                    <span class="fw-semibold" id="view-departamento">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Estado -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header border-0">
                                    <h6 class="mb-0">
                                        <i class="ri-information-line me-2"></i>Estado del Empleado
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center emp-icon-box emp-icon-box--green">
                                                    <i class="ri-calendar-check-line emp-icon--green"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Fecha Ingreso</small>
                                                    <span class="fw-semibold" id="view-fecha-ingreso">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center emp-icon-box emp-icon-box--navy">
                                                    <i class="ri-checkbox-circle-line emp-icon--navy"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Estado</small>
                                                    <span class="fw-semibold" id="view-estado">-</span>
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

    <!-- Modal para agregar/editar Empleado (Estándar Clientes) -->
    <div class="modal fade atlantico-modal" id="showModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="modalTitle">Agregar Empleado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="empleadoForm" novalidate>
                    <div class="modal-body">
                        <input type="hidden" id="id-field" />
                        <div id="edit-shared-persona-notice" class="d-none alert mb-3 py-2 px-3 emp-shared-notice">
                            <i class="ri-user-shared-line me-1"></i>
                            Esta persona también está registrada como <strong id="edit-shared-role"></strong>.
                            Los cambios en datos personales afectarán ambos registros.
                        </div>

                        <div class="modal-form-section">
                            <div class="modal-form-section-title"><i class="ri-user-3-line"></i>Datos Personales</div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="field-documento_identidad" class="form-label required">Documento de Identidad</label>
                                    <div class="input-group">
                                        <select class="form-select tipo-doc-select" id="tipo-documento-field" name="tipo_documento">
                                            <option value="V-">V-</option>
                                            <option value="E-">E-</option>
                                            <option value="J-">J-</option>
                                            <option value="G-">G-</option>
                                        </select>
                                        <input type="text" id="field-documento_identidad" name="documento_identidad"
                                            class="form-control" placeholder="Nro. documento" required />
                                    </div>
                                    <div id="documento-persona-card" class="d-none mt-2 rounded emp-persona-card">
                                        <div class="emp-persona-card-title">
                                            <i class="ri-user-shared-line me-1"></i>
                                            Persona ya registrada como <span id="persona-card-role" class="text-capitalize"></span>
                                        </div>
                                        <div id="persona-card-data" class="emp-persona-card-data"></div>
                                        <button type="button" id="persona-vincular-btn" class="btn btn-sm emp-persona-vincular-btn">
                                            <i class="ri-link me-1"></i>Usar estos datos
                                        </button>
                                    </div>
                                    <div id="documento-vinculado-notice" class="d-none mt-1 emp-persona-notice-text">
                                        <i class="ri-link me-1"></i><span id="documento-vinculado-text"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <x-forms.input name="nombre" label="Nombre" placeholder="Nombre" required />
                                </div>
                                <div class="col-md-4">
                                    <x-forms.input name="apellido" label="Apellido" placeholder="Apellido" required />
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <x-forms.input name="email" label="Email" type="email" placeholder="correo@ejemplo.com" />
                                </div>
                                <div class="col-md-6">
                                    <x-forms.input name="telefono_number" label="Teléfono" id="telefono-number-field"
                                        maxlength="7" placeholder="1234567" prependRaw="true">
                                        <x-slot:prepend>
                                            <select class="form-select phone-prefix-select" id="telefono-prefix-field">
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
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6">
                                    <x-forms.input name="fecha_nacimiento" label="Fecha de Nacimiento" type="date" />
                                </div>
                                <div class="col-md-6">
                                    <x-forms.select name="genero" label="Género"
                                        :options="['M' => 'Masculino', 'F' => 'Femenino']" />
                                </div>
                            </div>
                        </div>

                        <div class="modal-form-section">
                            <div class="modal-form-section-title"><i class="ri-map-pin-2-line"></i>Ubicación</div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <x-forms.input name="direccion" label="Dirección" placeholder="Dirección completa" />
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6">
                                    <label for="estado_geografico-field" class="form-label">Estado</label>
                                    <select name="estado_geografico" id="estado_geografico-field" class="form-select">
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
                                    <label for="ciudad-field" class="form-label">Municipio</label>
                                    <select name="ciudad" id="ciudad-field" class="form-select">
                                        <option value="">Primero seleccione un estado</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="modal-form-section mb-0">
                            <div class="modal-form-section-title"><i class="ri-briefcase-4-line"></i>Información Laboral</div>

                            <div class="row mb-3">
                                {{-- Departamento --}}
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="field-departamento_id" class="form-label">Departamento <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <select id="field-departamento_id" name="departamento_id" class="form-select" required>
                                                <option value="">Seleccione...</option>
                                                @foreach($departamentos as $id => $nombre)
                                                    <option value="{{ $id }}">{{ $nombre }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-outline-success" id="add-departamento-btn"
                                                title="Agregar departamento">
                                                <i class="ri-add-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Cargo (cascading) --}}
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="field-cargo_id" class="form-label">Cargo <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <select id="field-cargo_id" name="cargo_id" class="form-select" required disabled>
                                                <option value="">Elija un departamento</option>
                                            </select>
                                            <button type="button" class="btn btn-outline-success" id="add-cargo-btn"
                                                title="Agregar cargo" disabled>
                                                <i class="ri-add-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <x-forms.input name="fecha_ingreso" label="Fecha de Ingreso" type="date" required />
                                </div>
                            </div>

                            <input type="hidden" id="field-codigo_empleado" name="codigo_empleado" />

                            {{-- Estado Laboral: solo lectura. Lo gobierna Inhabilitar/Restaurar, no es editable aquí. --}}
                            <div class="row mb-0">
                                <div class="col-md-6">
                                    <label class="form-label">Estado Laboral</label>
                                    <input type="text" class="form-control bg-light" id="field-estado-display"
                                        value="Activo" readonly tabindex="-1">
                                    <div class="d-flex align-items-start gap-2 mt-2 p-2 rounded-3 bg-info-subtle border border-info-subtle">
                                        <i class="ri-information-line text-info fs-5 lh-1 mt-1"></i>
                                        <small class="text-info-emphasis mb-0 lh-sm">
                                            Se asigna automáticamente: un empleado nuevo o restaurado queda <strong>Activo</strong>.
                                            Para darlo de baja usa <strong>Inhabilitar</strong> (pasa a Inactivo y al historial).
                                        </small>
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
                            <x-ui.button-save id="edit-btn" text="Actualizar" icon="ri-save-line" loading-text="Actualizando..."
                                style="display: none;" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Mini-Modal: Nuevo Departamento -->
    <div class="modal fade atlantico-modal" id="addDepartamentoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title">Nuevo Departamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-0">
                        <label class="form-label required">Nombre</label>
                        <input type="text" id="nuevo-departamento-field" class="form-control"
                            placeholder="Ej: Logística" maxlength="100" />
                        <div id="depto-error" class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="guardar-departamento-btn">
                        <i class="ri-check-line me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mini-Modal: Nuevo Cargo -->
    <div class="modal fade atlantico-modal" id="addCargoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title">Nuevo Cargo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2">
                        Departamento: <strong id="cargo-depto-label">—</strong>
                    </p>
                    <div class="mb-0">
                        <label class="form-label required">Nombre del Cargo</label>
                        <input type="text" id="nuevo-cargo-field" class="form-control"
                            placeholder="Ej: Costurera" maxlength="100" />
                        <div id="cargo-error" class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="guardar-cargo-btn">
                        <i class="ri-check-line me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- Modales de gestión completa (CRUD) de Departamentos y Cargos movidos a /departamentos y /cargos. Los mini-modales addDepartamentoModal y addCargoModal de arriba se mantienen para creación rápida desde el form de empleado. --}}

    {{-- Modal: Exportar PDF con filtros --}}
    <div class="modal fade atlantico-modal" id="pdfExportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-file-pdf-line me-2"></i>Exportar PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Filtra qué empleados incluir en el reporte.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="pdf-filter-departamento">Departamento</label>
                        <select class="form-select" id="pdf-filter-departamento">
                            <option value="">Todos los departamentos</option>
                            @foreach($departamentos as $id => $nombre)
                                <option value="{{ $id }}">{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="pdf-filter-cargo">Cargo</label>
                        <select class="form-select" id="pdf-filter-cargo">
                            <option value="">Todos los cargos</option>
                            @foreach($cargos as $id => $nombre)
                                <option value="{{ $id }}">{{ $nombre }}</option>
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
    <script src="{{ URL::asset('/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/municipios-venezuela.js') }}"></script>
    <script>
        $(function () {
            // Tooltips
            $(document).on('mouseenter', '[title]', function () {
                $(this).tooltip({ container: 'body' }).tooltip('show');
            });
            $(document).on('mouseleave', '[title]', function () {
                $(this).tooltip('dispose');
            });

            // Validación onblur para documento (duplicados)
            $(document).on('blur', '#field-documento_identidad', function () {
                let value = $(this).val().trim();
                let $input = $(this);
                let isEditMode = $('#id-field').val() !== '';

                if (value.length < 6) {
                    marcarInvalido($input, 'El documento debe tener al menos 6 dígitos.');
                    return;
                }
                if (isEditMode) {
                    marcarValido($input);
                    return;
                }
                $.ajax({
                    url: "{{ route('empleados.check-documento') }}",
                    method: 'GET',
                    data: { numero: value },
                    success: function (response) {
                        if (response.exists) {
                            marcarInvalido($input, 'Este documento ya pertenece a un empleado registrado.');
                            $('#add-btn').prop('disabled', true);
                            $('#documento-persona-card').addClass('d-none');
                            $('#documento-vinculado-notice').addClass('d-none');
                        } else {
                            marcarValido($input);
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
                        console.error('Error al verificar documento de empleado');
                    }
                });
            });

            // Vincular persona existente al formulario de empleado
            $(document).on('click', '#persona-vincular-btn', function () {
                var p = $(this).data('persona');
                var role = $(this).data('role');

                if (p.tipo_documento) $('#tipo-documento-field').val(p.tipo_documento);

                // Datos personales
                $('#field-nombre').val(p.nombre || '').prop('readonly', true).addClass('bg-light').css('cursor', 'not-allowed');
                $('#field-apellido').val(p.apellido || '').prop('readonly', true).addClass('bg-light').css('cursor', 'not-allowed');
                $('#field-email').val(p.email || '').prop('readonly', true).addClass('bg-light').css('cursor', 'not-allowed');

                // Teléfono
                if (p.telefono && p.telefono.includes('-')) {
                    var parts = p.telefono.split('-');
                    $('#telefono-prefix-field').val(parts[0]).prop('disabled', true);
                    $('#telefono-number-field').val(parts[1]).prop('readonly', true).addClass('bg-light').css('cursor', 'not-allowed');
                }

                // Opcionales
                if (p.fecha_nacimiento) $('#field-fecha_nacimiento').val(p.fecha_nacimiento).prop('readonly', true).addClass('bg-light').css('cursor', 'not-allowed');
                if (p.genero) $('#field-genero').val(p.genero).prop('disabled', true);
                if (p.direccion) $('#field-direccion').val(p.direccion).prop('readonly', true).addClass('bg-light').css('cursor', 'not-allowed');

                // Estado y Municipio
                if (p.estado_geografico) {
                    $('#estado_geografico-field').val(p.estado_geografico).trigger('change');
                    if (p.ciudad) $('#ciudad-field').val(p.ciudad);
                    $('#estado_geografico-field').prop('disabled', true);
                    $('#ciudad-field').prop('disabled', true);
                }

                // Mostrar aviso y habilitar guardar
                $('#documento-persona-card').addClass('d-none');
                $('#documento-vinculado-text').text('Datos vinculados de persona registrada como ' + role + '.');
                $('#documento-vinculado-notice').removeClass('d-none');
                $('#add-btn').prop('disabled', false);
            });

            // Validación onblur para Email
            $(document).on('blur', '#field-email', function () {
                let value = $(this).val().trim();
                let $input = $(this);
                let regex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;

                if (value.length === 0) {
                    limpiarValidacion($input);
                    return;
                }
                if (!regex.test(value)) {
                    marcarInvalido($input, 'Ingrese un email válido (ej: usuario@dominio.com).');
                    return;
                }

                let excludeId = $('#id-field').val();
                $.ajax({
                    url: "{{ route('empleados.check-email') }}",
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
                        console.error('Error al verificar email de empleado');
                    }
                });
            });
        });

        // Dropdown dependiente: poblar municipios cuando cambia el estado
        $(document).on('change', '#estado_geografico-field', function () {
            const estado = $(this).val();
            const municipios = getMunicipios(estado);
            const $ciudad = $("#ciudad-field");
            $ciudad.empty();
            if (estado === '') {
                $ciudad.append('<option value="">Primero seleccione un estado</option>');
            } else {
                $ciudad.append('<option value="">Seleccione municipio</option>');
                municipios.forEach(function (municipio) {
                    $ciudad.append('<option value="' + municipio + '">' + municipio + '</option>');
                });
            }
        });

        // Sanitización del número de teléfono (solo dígitos, máx 7)
        $(document).on('input', '#telefono-number-field', function () {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 7);
        });

        // Validación onblur para teléfono (opcional — solo valida si hay valor)
        $(document).on('blur', '#telefono-number-field', function () {
            let value = $(this).val().trim();
            if (value.length === 0) {
                limpiarValidacion($(this));
            } else if (!/^[0-9]{7}$/.test(value)) {
                marcarInvalido($(this), 'El número debe tener exactamente 7 dígitos.');
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

            function generateButtons(empleadoId, isTrashed) {
                // Inhabilitado (trashed) → Ver + Restaurar
                if (isTrashed) {
                    return '<div class="d-flex gap-1 justify-content-center">' +
                        '<button class="btn btn-sm btn-soft-secondary view-item-btn dt-action-btn" data-id="' + empleadoId + '" title="Ver">' +
                        '<i class="ri-eye-fill dt-action-icon"></i>' +
                        '</button>' +
                        '<button class="btn btn-sm btn-soft-success restore-item-btn dt-action-btn" data-id="' + empleadoId + '" title="Restaurar">' +
                        '<i class="ri-arrow-go-back-line dt-action-icon"></i>' +
                        '</button>' +
                        '</div>';
                }
                // Activo → Ver + Editar + Inhabilitar
                return '<div class="d-flex gap-1 justify-content-center">' +
                    '<button class="btn btn-sm btn-soft-secondary view-item-btn dt-action-btn" data-id="' + empleadoId + '" title="Ver">' +
                    '<i class="ri-eye-fill dt-action-icon"></i>' +
                    '</button>' +
                    '<button class="btn btn-sm btn-soft-success edit-item-btn dt-action-btn" data-id="' + empleadoId + '" title="Editar">' +
                    '<i class="ri-pencil-fill dt-action-icon"></i>' +
                    '</button>' +
                    '<button class="btn btn-sm btn-soft-danger remove-item-btn dt-action-btn" data-id="' + empleadoId + '" title="Inhabilitar">' +
                    '<i class="ri-forbid-line dt-action-icon"></i>' +
                    '</button>' +
                    '</div>';
            }

            function renderEllipsis(value) {
                if (!value) return '<span class="text-muted">—</span>';
                return '<span title="' + value + '" style="cursor:default;">' + value + '</span>';
            }

            function formatDate(dateStr) {
                if (!dateStr) return 'N/A';
                if (typeof dateStr === 'string') {
                    var parts = dateStr.trim().split(' ');
                    var datePart = parts[0] || '';
                    if (/^\d{2}\/\d{2}\/\d{4}$/.test(datePart)) return datePart;
                }
                var date = new Date(dateStr);
                if (isNaN(date.getTime())) return dateStr;
                var day = String(date.getDate()).padStart(2, '0');
                var month = String(date.getMonth() + 1).padStart(2, '0');
                var year = date.getFullYear();
                return day + '/' + month + '/' + year;
            }

            var table = $('#empleados-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('empleados.data') }}",
                    dataSrc: 'data',
                    data: function (d) {
                        // ── Filtros avanzados: enviar valores al server ──
                        d.filter_departamento   = $('#filter-departamento').val();
                        d.filter_cargo          = $('#filter-cargo').val();
                        d.filter_estatus        = $('#filter-estatus').val();
                        d.filter_orden          = $('#filter-orden').val();
                    }
                },
                columns: [
                    {
                        data: 'documento', render: function (data, type, row) {
                            return row.persona ? row.persona.tipo_documento + row.persona.documento_identidad : 'N/A';
                        }
                    },
                    {
                        data: 'nombre_completo',
                        render: function (data) {
                            return renderEllipsis(data);
                        }
                    },
                    { data: 'telefono', defaultContent: 'N/A' },
                    {
                        data: 'cargo',
                        render: function (data) {
                            return renderEllipsis(data);
                        }
                    },
                    {
                        data: 'departamento',
                        render: function (data) {
                            if (!data) return 'N/A';
                            var lower = data.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                            if (lower.includes('administra')) {
                                return '<span class="badge-tipo badge-depto-administracion"><i class="ri-building-2-line"></i> ' + data + '</span>';
                            } else if (lower.includes('producc')) {
                                return '<span class="badge-tipo badge-depto-produccion"><i class="ri-tools-line"></i> ' + data + '</span>';
                            }
                            return '<span class="badge-tipo badge-depto-otro"><i class="ri-briefcase-line"></i> ' + data + '</span>';
                        }
                    },
                    {
                        data: 'trashed', render: function (data) {
                            return data
                                ? '<span class="badge-status badge-status-inactivo"><i class="ri-close-circle-line"></i> Inhabilitado</span>'
                                : '<span class="badge-status badge-status-activo"><i class="ri-checkbox-circle-line"></i> Activo</span>';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return generateButtons(row.id, row.trashed);
                        }
                    }
                ],
                order: [],
                ordering: false,
                dom: 'rtip',
                language: lenguajeData
            });

            // ══════════════════════════════════════════════════
            // BÚSQUEDA + FILTROS AVANZADOS — Patrón Maestro S-07
            // ══════════════════════════════════════════════════

            // ── Badge: actualizar contador de filtros activos + punto rojo ──
            function updateFilterBadge() {
                var count = 0;
                if ($('#filter-departamento').val() !== '')                          count++;
                if ($('#filter-cargo').val() !== '')                                 count++;
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

            // ── Si se llegó por toggle historial (?historial=true) → mostrar inhabilitados ──
            @if($historial)
                $('#filter-estatus').val('0');
                table.ajax.reload();
                updateFilterBadge();
            @endif

            // ── Botón limpiar: resetea búsqueda + filtros + orden ──
            $('#btn-clear-filters').on('click', function () {
                $('#filter-departamento').val('');
                $('#filter-cargo').val('');
                $('#filter-estatus').val('1');
                $('#filter-orden').val('recientes');
                $('#custom-search-input').val('');
                updateFilterBadge();
                table.search('').ajax.reload(function () {
                    updateFilterBadge();
                });
            });

            // === Funciones del modal crear/editar (estándar Clientes) ===
            function resetForm() {
                $("#empleadoForm").trigger("reset");
                $("#id-field").val("");
                $("#modalTitle").text("Agregar Empleado");
                $("#add-btn").show().prop('disabled', false);
                $("#edit-btn").hide();
                $("#field-codigo_empleado").val("");
                $("#tipo-documento-field").val("V-").prop('disabled', false).removeClass('campo-protegido');
                $("#field-documento_identidad").prop('disabled', false).removeClass('campo-protegido');
                $("#field-estado-display").val("Activo");
                // Resetear teléfono
                $("#telefono-prefix-field").val("0424");
                $("#telefono-number-field").val("");
                // Resetear departamento y cargo
                $("#field-departamento_id").val("");
                $("#field-cargo_id").empty().append('<option value="">Elija un departamento</option>').prop('disabled', true);
                $("#add-cargo-btn").prop('disabled', true);
                // Resetear Estado y Municipio
                $("#estado_geografico-field").val("");
                $("#ciudad-field").empty().append('<option value="">Primero seleccione un estado</option>');
                // Limpiar validaciones
                $("#empleadoForm .is-invalid").removeClass("is-invalid");
                $("#empleadoForm .is-valid").removeClass("is-valid");
                // Desbloquear campos vinculados de persona existente
                $('#field-nombre, #field-apellido, #field-email, #field-fecha_nacimiento, #field-direccion, #telefono-number-field').prop('readonly', false).removeClass('bg-light').css('cursor', '');
                $('#telefono-prefix-field, #field-genero, #estado_geografico-field, #ciudad-field').prop('disabled', false);
                $('#documento-persona-card').addClass('d-none');
                $('#documento-vinculado-notice').addClass('d-none');
                $('#edit-shared-persona-notice').addClass('d-none');
            }

            function setEditMode() {
                $("#modalTitle").text("Actualizar Empleado");
                $("#add-btn").hide();
                $("#edit-btn").show();
                // Bloquear edición de documento
                $("#tipo-documento-field").prop('disabled', true).addClass('campo-protegido');
                $("#field-documento_identidad").prop('disabled', true).addClass('campo-protegido');
                // Limpiar card de vinculación (por si venía del flujo crear)
                $('#documento-persona-card').addClass('d-none');
                $('#documento-vinculado-notice').addClass('d-none');
                $('#edit-shared-persona-notice').addClass('d-none');
            }

            function validarFormularioEmpleado() {
                let esValido = true;
                let isEditMode = $('#id-field').val() !== '';

                // Documento (solo en creación — en edición está bloqueado)
                if (!isEditMode) {
                    let $doc = $('#field-documento_identidad');
                    if ($doc.val().trim().length < 6) {
                        marcarInvalido($doc, 'El documento debe tener al menos 6 dígitos.');
                        esValido = false;
                    } else { marcarValido($doc); }
                }

                // Nombre
                let $nombre = $('#field-nombre');
                if ($nombre.val().trim().length === 0) {
                    marcarInvalido($nombre, 'El nombre es obligatorio.');
                    esValido = false;
                } else if ($nombre.val().trim().length < 2) {
                    marcarInvalido($nombre, 'El nombre debe tener al menos 2 caracteres.');
                    esValido = false;
                } else { marcarValido($nombre); }

                // Apellido
                let $apellido = $('#field-apellido');
                if ($apellido.val().trim().length === 0) {
                    marcarInvalido($apellido, 'El apellido es obligatorio.');
                    esValido = false;
                } else if ($apellido.val().trim().length < 2) {
                    marcarInvalido($apellido, 'El apellido debe tener al menos 2 caracteres.');
                    esValido = false;
                } else { marcarValido($apellido); }

                // Departamento
                let $depto = $('#field-departamento_id');
                if (!$depto.val()) {
                    marcarInvalido($depto, 'El departamento es obligatorio.');
                    esValido = false;
                } else { marcarValido($depto); }

                // Cargo
                let $cargo = $('#field-cargo_id');
                if (!$cargo.val()) {
                    marcarInvalido($cargo, 'El cargo es obligatorio.');
                    esValido = false;
                } else { marcarValido($cargo); }

                // Fecha de Ingreso
                let $fechaIngreso = $('#field-fecha_ingreso');
                if (!$fechaIngreso.val()) {
                    marcarInvalido($fechaIngreso, 'La fecha de ingreso es obligatoria.');
                    esValido = false;
                } else {
                    let selected = new Date($fechaIngreso.val() + 'T00:00:00');
                    let today = new Date(); today.setHours(0, 0, 0, 0);
                    if (selected > today) {
                        marcarInvalido($fechaIngreso, 'La fecha de ingreso no puede ser futura.');
                        esValido = false;
                    } else { marcarValido($fechaIngreso); }
                }

                // Email (opcional — solo valida formato si hay valor)
                let $email = $('#field-email');
                let emailVal = $email.val().trim();
                if (emailVal.length > 0) {
                    let emailRegex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
                    if (!emailRegex.test(emailVal)) {
                        marcarInvalido($email, 'Ingrese un email válido (ej: usuario@dominio.com).');
                        esValido = false;
                    }
                }

                // Teléfono (opcional — solo valida si se ingresó número)
                let $telNum = $('#telefono-number-field');
                if ($telNum.val().trim().length > 0 && !/^[0-9]{7}$/.test($telNum.val().trim())) {
                    marcarInvalido($telNum, 'El número debe tener exactamente 7 dígitos.');
                    esValido = false;
                }

                return esValido;
            }

            $("#create-btn").click(function () { resetForm(); });
            $("#showModal").on('hidden.bs.modal', function () { resetForm(); });
            $("#add-btn, #edit-btn").click(function (e) { e.preventDefault(); $("#empleadoForm").submit(); });

            // Envío del formulario via AJAX (crear o actualizar)
            $("#empleadoForm").on("submit", function (e) {
                e.preventDefault();

                if (!validarFormularioEmpleado()) return;

                var id = $("#id-field").val();
                var url = id ? "{{ route('empleados.update', ':id') }}".replace(':id', id) : "{{ route('empleados.store') }}";
                var method = id ? "PUT" : "POST";
                // Concatenar teléfono: prefijo-número → campo oculto
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
                        $("#empleadoForm").trigger("reset");
                        table.ajax.reload();
                        Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.message, showConfirmButton: false, timer: 2000 });
                    },
                    error: function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON.error || xhr.responseJSON.message || 'Error al procesar' });
                    }
                });
            });

            // Ver detalles del empleado
            $(document).on("click", ".view-item-btn", function () {
                var id = $(this).data("id");
                $.get("{{ route('empleados.show', '') }}/" + id, function (data) {
                    $("#viewModal").modal("show");
                    $("#view-nombre-completo").text(data.persona.nombre + ' ' + data.persona.apellido);
                    $("#view-documento").text(data.persona.tipo_documento + data.persona.documento_identidad);
                    $("#view-email").text(data.persona.email || 'N/A');
                    $("#view-telefono").text(data.telefono || 'N/A');
                    $("#view-direccion").text(data.direccion || 'N/A');
                    $("#view-ciudad").text(data.ciudad || 'N/A');
                    $("#view-fecha-nacimiento").text(formatDate(data.persona.fecha_nacimiento));
                    $("#view-genero").text(data.persona.genero || 'N/A');
                    $("#view-codigo").text(data.codigo_empleado);
                    $("#view-cargo").text(data.cargo);
                    $("#view-departamento").text(data.departamento);
                    $("#view-fecha-ingreso").text(formatDate(data.fecha_ingreso));
                    $("#view-estado").html(data.trashed ? '<span class="badge bg-danger">Inhabilitado</span>' : '<span class="badge bg-success">Activo</span>');
                });
            });

            // Editar empleado: cargar datos en el modal
            $(document).on("click", ".edit-item-btn", function () {
                var id = $(this).data("id");
                $.get("{{ route('empleados.edit', ':id') }}".replace(':id', id), function (data) {
                    setEditMode();
                    if (data.other_role) {
                        $('#edit-shared-role').text(data.other_role);
                        $('#edit-shared-persona-notice').removeClass('d-none');
                    }
                    $("#id-field").val(data.id);
                    $("#field-nombre").val(data.persona.nombre);
                    $("#field-apellido").val(data.persona.apellido);
                    $("#tipo-documento-field").val(data.persona.tipo_documento);
                    $("#field-documento_identidad").val(data.persona.documento_identidad);
                    $("#field-email").val(data.persona.email);
                    // Separar teléfono en prefijo y número
                    if (data.telefono && data.telefono.includes('-')) {
                        var telParts = data.telefono.split('-');
                        $("#telefono-prefix-field").val(telParts[0]);
                        $("#telefono-number-field").val(telParts[1]);
                    } else if (data.telefono) {
                        $("#telefono-prefix-field").val(data.telefono.slice(0, 4));
                        $("#telefono-number-field").val(data.telefono.slice(4));
                    } else {
                        $("#telefono-prefix-field").val("0424");
                        $("#telefono-number-field").val("");
                    }
                    $("#field-direccion").val(data.direccion || '');
                    // Estado y municipio dependiente
                    const estadoEdit = data.persona.estado_geografico || '';
                    $("#estado_geografico-field").val(estadoEdit);
                    const $ciudadEdit = $("#ciudad-field");
                    $ciudadEdit.empty();
                    if (estadoEdit === '') {
                        $ciudadEdit.append('<option value="">Primero seleccione un estado</option>');
                    } else {
                        $ciudadEdit.append('<option value="">Seleccione municipio</option>');
                        getMunicipios(estadoEdit).forEach(function (m) {
                            $ciudadEdit.append('<option value="' + m + '">' + m + '</option>');
                        });
                    }
                    $ciudadEdit.val(data.ciudad || '');
                    $("#field-fecha_nacimiento").val(data.persona.fecha_nacimiento);
                    $("#field-genero").val(data.persona.genero);
                    $("#field-codigo_empleado").val(data.codigo_empleado);
                    $("#field-fecha_ingreso").val(data.fecha_ingreso);
                    $("#field-estado-display").val(data.trashed ? 'Inhabilitado' : 'Activo');

                    // Departamento → cargo en cascada
                    var $deptoSel = $('#field-departamento_id');
                    var $cargoSel = $('#field-cargo_id');
                    var $addCargo = $('#add-cargo-btn');
                    $deptoSel.val(data.departamento_id);

                    if (data.departamento_id) {
                        $cargoSel.empty().append('<option value="">Cargando...</option>').prop('disabled', true);
                        $.get("{{ route('empleados.get-cargos') }}", { departamento_id: data.departamento_id }, function (cargos) {
                            $cargoSel.empty().prop('disabled', false);
                            $addCargo.prop('disabled', false);
                            $cargoSel.append('<option value="">Seleccione...</option>');
                            cargos.forEach(function (c) {
                                $cargoSel.append('<option value="' + c.id + '">' + c.nombre + '</option>');
                            });
                            $cargoSel.val(data.cargo_id);
                            $("#showModal").modal("show");
                        }).fail(function () {
                            $cargoSel.empty().append('<option value="">Error al cargar cargos</option>');
                            $("#showModal").modal("show");
                        });
                    } else {
                        $cargoSel.empty().append('<option value="">Elija un departamento</option>').prop('disabled', true);
                        $addCargo.prop('disabled', true);
                        $("#showModal").modal("show");
                    }
                    return; // Modal se abre dentro del callback AJAX
                });
            });

            $(document).on("click", ".remove-item-btn", function () {
                var id = $(this).data("id");
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "El empleado será inhabilitado y moverá al historial.",
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
                            url: "{{ route('empleados.destroy', ':id') }}".replace(':id', id),
                            method: "DELETE",
                            success: function (response) {
                                table.ajax.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Inhabilitado!',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON.message || 'Error al inhabilitar'
                                });
                            }
                        });
                    }
                });
            });

            // ══════════════════════════════════════════════════════
            // RESTAURAR — SoftDelete Restore (estándar Clientes/Proveedores)
            // ══════════════════════════════════════════════════════
            $(document).on("click", ".restore-item-btn", function () {
                var id = $(this).data("id");
                Swal.fire({
                    title: '¿Restaurar empleado?',
                    text: "El empleado volverá a estar activo.",
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
                            url: "{{ url('empleados') }}/" + id + "/restore",
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
                                    text: 'No se pudo restaurar el empleado'
                                });
                            }
                        });
                    }
                });
            });
        });

        // ===== Cascading: departamento → cargo =====
        $(document).on('change', '#field-departamento_id', function () {
            var deptoId   = $(this).val();
            var $cargoSel = $('#field-cargo_id');
            var $addCargo = $('#add-cargo-btn');
            $cargoSel.empty().prop('disabled', true);
            $addCargo.prop('disabled', true);

            if (!deptoId) {
                $cargoSel.append('<option value="">Elija un departamento</option>');
                return;
            }
            $cargoSel.append('<option value="">Cargando...</option>');
            $.get("{{ route('empleados.get-cargos') }}", { departamento_id: deptoId }, function (cargos) {
                $cargoSel.empty().prop('disabled', false);
                $addCargo.prop('disabled', false);
                if (cargos.length === 0) {
                    $cargoSel.append('<option value="">Sin cargos — agregue uno con +</option>');
                } else {
                    $cargoSel.append('<option value="">Seleccione...</option>');
                    cargos.forEach(function (c) {
                        $cargoSel.append('<option value="' + c.id + '">' + c.nombre + '</option>');
                    });
                }
            }).fail(function () {
                $cargoSel.empty().append('<option value="">Error al cargar cargos</option>');
            });
        });

        // Abrir modales hijos sin data-bs-toggle para que Bootstrap
        // no cierre el padre (showModal) antes de abrir el hijo
        $('#add-departamento-btn').on('click', function () {
            $('#addDepartamentoModal').modal('show');
        });
        $('#add-cargo-btn').on('click', function () {
            $('#addCargoModal').modal('show');
        });

        // ===== Departamento On-the-fly =====
        $('#addDepartamentoModal').on('shown.bs.modal', function () {
            $('#nuevo-departamento-field').val('').removeClass('is-invalid').focus();
            $('#depto-error').hide();
        });

        $('#guardar-departamento-btn').click(function () {
            var nombre = $('#nuevo-departamento-field').val().trim();
            if (nombre.length < 3) {
                $('#nuevo-departamento-field').addClass('is-invalid');
                $('#depto-error').text('Mínimo 3 caracteres.').show();
                return;
            }
            var $btn = $(this).prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i>Guardando...');
            $.ajax({
                url: "{{ route('departamentos.store') }}",
                method: 'POST',
                data: { nombre: nombre, _token: '{{ csrf_token() }}' },
                success: function (resp) {
                    var dep       = resp.departamento;
                    var $deptoSel = $('#field-departamento_id');
                    $deptoSel.append('<option value="' + dep.id + '">' + dep.nombre + '</option>');
                    $deptoSel.val(dep.id).trigger('change');
                    $('#addDepartamentoModal').modal('hide');
                    Swal.fire({ icon: 'success', title: '¡Listo!', text: 'Departamento agregado.', showConfirmButton: false, timer: 1500 });
                },
                error: function (xhr) {
                    $('#nuevo-departamento-field').addClass('is-invalid');
                    $('#depto-error').text(xhr.responseJSON?.message || 'Error al guardar.').show();
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="ri-check-line me-1"></i>Guardar');
                }
            });
        });

        // ===== Cargo On-the-fly =====
        $('#addCargoModal').on('shown.bs.modal', function () {
            var deptoNombre = $('#field-departamento_id option:selected').text();
            $('#cargo-depto-label').text(deptoNombre);
            $('#nuevo-cargo-field').val('').removeClass('is-invalid').focus();
            $('#cargo-error').hide();
        });

        $('#guardar-cargo-btn').click(function () {
            var nombre  = $('#nuevo-cargo-field').val().trim();
            var deptoId = $('#field-departamento_id').val();
            if (nombre.length < 3) {
                $('#nuevo-cargo-field').addClass('is-invalid');
                $('#cargo-error').text('Mínimo 3 caracteres.').show();
                return;
            }
            var $btn = $(this).prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i>Guardando...');
            $.ajax({
                url: "{{ route('cargos.store') }}",
                method: 'POST',
                data: { nombre: nombre, departamento_id: deptoId, _token: '{{ csrf_token() }}' },
                success: function (resp) {
                    var cargo     = resp.cargo;
                    var $cargoSel = $('#field-cargo_id');
                    $cargoSel.append('<option value="' + cargo.id + '">' + cargo.nombre + '</option>');
                    $cargoSel.val(cargo.id);
                    $('#addCargoModal').modal('hide');
                    Swal.fire({ icon: 'success', title: '¡Listo!', text: 'Cargo agregado.', showConfirmButton: false, timer: 1500 });
                },
                error: function (xhr) {
                    $('#nuevo-cargo-field').addClass('is-invalid');
                    $('#cargo-error').text(xhr.responseJSON?.message || 'Error al guardar.').show();
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="ri-check-line me-1"></i>Guardar');
                }
            });
        });

        // ===== Lógica de Prefijo Documento (Empleados) =====
        function getEmpleadoDocMaxLength() {
            var prefix = $('#tipo-documento-field').val();
            return (prefix === 'J-' || prefix === 'G-') ? 9 : 8;
        }

        $(document).on('change', '#tipo-documento-field', function () {
            var maxLen = getEmpleadoDocMaxLength();
            var $docInput = $('#field-documento_identidad');
            $docInput.attr('maxlength', maxLen);
            if ($docInput.val().length > maxLen) {
                $docInput.val($docInput.val().slice(0, maxLen));
            }
        });

        $(document).on('input', '#field-documento_identidad', function () {
            var maxLen = getEmpleadoDocMaxLength();
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, maxLen);
        });


        // Refrescar select de departamento del form de empleado tras crear uno nuevo desde el mini-modal.
        function sincronizarSelectsDepartamento() {
            $.get("{{ route('departamentos.index') }}", function (data) {
                var $sel = $('#field-departamento_id');
                if (!$sel.length) return;
                var currentVal  = $sel.val();
                var placeholder = $sel.find('option:first').clone();
                $sel.empty().append(placeholder);
                data.forEach(function (d) {
                    $sel.append('<option value="' + d.id + '">' + $('<div>').text(d.nombre).html() + '</option>');
                });
                $sel.val(currentVal);
            });
        }

        // PDF Export Modal — Empleados
        $('#btn-generar-pdf').on('click', function () {
            var baseUrl = '{{ route('empleados.reporte.pdf') }}';
            var params  = [];
            var dep     = $('#pdf-filter-departamento').val();
            var cargo   = $('#pdf-filter-cargo').val();
            var estatus = $('#pdf-filter-estatus').val();
            if (dep)            params.push('departamento_id=' + encodeURIComponent(dep));
            if (cargo)          params.push('cargo_id='        + encodeURIComponent(cargo));
            if (estatus !== '') params.push('estatus='         + encodeURIComponent(estatus));
            window.open(baseUrl + (params.length ? '?' + params.join('&') : ''), '_blank');
            bootstrap.Modal.getInstance(document.getElementById('pdfExportModal'))?.hide();
        });
        $('#pdfExportModal').on('show.bs.modal', function () {
            $('#pdf-filter-departamento').val('');
            $('#pdf-filter-cargo').val('');
            $('#pdf-filter-estatus').val('');
        });
    </script>
@endpush