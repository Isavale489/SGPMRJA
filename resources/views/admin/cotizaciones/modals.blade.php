<!-- Modal para ver detalles de Cotización -->
<div class="modal fade atlantico-modal atlantico-modal--op" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de la Cotización</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <!-- Columna Izquierda -->
                    <div class="col-lg-6">
                        <!-- Card Datos del Cliente -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header border-0" style="background: rgba(30, 60, 114, 0.1);">
                                <h6 class="mb-0" style="color: #1e3c72;">
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
                                                <small class="text-muted d-block">Nombre</small>
                                                <span class="fw-semibold" id="view-cliente-nombre">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                <i class="ri-user-follow-line" style="color: #1e3c72;"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Apellido</small>
                                                <span class="fw-semibold" id="view-cliente-apellido">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3 mt-1">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                <i class="ri-bank-card-line" style="color: #1e3c72;"></i>
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
                                                style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                <i class="ri-phone-line" style="color: #1e3c72;"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Teléfono</small>
                                                <span class="fw-semibold" id="view-cliente-telefono">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                <i class="ri-mail-line" style="color: #1e3c72;"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Email</small>
                                                <span class="fw-semibold" id="view-cliente-email">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Datos de la Cotización -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header border-0" style="background: rgba(30, 60, 114, 0.1);">
                                <h6 class="mb-0" style="color: #1e3c72;">
                                    <i class="ri-calendar-todo-line me-2"></i>Datos de la Cotización
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
                                                <small class="text-muted d-block">Fecha Cotización</small>
                                                <span class="fw-semibold" id="view-fecha-cotizacion">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                <i class="ri-calendar-check-line" style="color: #1e3c72;"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Fecha Validez</small>
                                                <span class="fw-semibold" id="view-fecha-validez">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                <i class="ri-flag-line" style="color: #1e3c72;"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Estado</small>
                                                <span class="fw-semibold" id="view-estado">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                <i class="ri-user-settings-line" style="color: #1e3c72;"></i>
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

                        <!-- Card Total -->
                        <div class="card border-0"
                            style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                            <div class="card-body py-3 px-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                                            style="width:38px;height:38px;background:rgba(255,255,255,0.15);">
                                            <i class="ri-money-dollar-circle-line text-white fs-5"></i>
                                        </div>
                                        <div>
                                            <span class="text-white-50 d-block"
                                                style="font-size:0.7rem;letter-spacing:0.06em;text-transform:uppercase;">Total
                                                Cotización</span>
                                            <small class="text-white-50" style="font-size:0.68rem;">
                                                <i class="ri-refresh-line me-1"></i>Se actualiza automáticamente
                                            </small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold text-white" style="font-size:1.8rem;line-height:1;"
                                            id="view-total">$0.00</span>
                                        <small class="text-white-50 d-block mt-1" style="font-size:0.72rem;">
                                            <i class="ri-exchange-dollar-line me-1"></i><span id="view-total-bs">Bs 0,00</span>
                                        </small>
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
                                    <i class="ri-shopping-bag-3-line me-2"></i>Productos de la Cotización
                                </h6>
                            </div>
                            <div class="card-body" style="max-height: 450px; overflow-y: auto;">
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
                <a href="#" id="view-pdf-btn" class="btn btn-warning" target="_blank">
                    <i class="ri-file-pdf-line me-1"></i>Descargar PDF
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar/editar Cotización -->
<!-- ════════════════════════════════════════════════════════════════════
     Modal Cotización — Wizard 3 pasos (Cliente · Productos · Resumen)
     IDs de campos preservados para compatibilidad con scripts/main.blade.php
     ════════════════════════════════════════════════════════════════════ -->
<div class="modal fade atlantico-modal atlantico-modal--op wiz-modal" id="showModal" tabindex="-1"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nueva Cotización</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Stepper visual --}}
            <div class="wiz-stepper-wrapper">
                {{-- Chip cliente persistente — ocupa el gutter izquierdo del stepper (pasos 2+) --}}
                <div class="wiz-stepper-side wiz-stepper-side--left">
                    <div class="wiz-client-banner" id="cot-cliente-banner" hidden aria-hidden="true"
                        title="Cliente de la cotización">
                        <span class="wiz-client-banner-label">Para:</span>
                        <div class="wiz-client-banner-avatar" id="cot-banner-avatar">—</div>
                        <div class="wiz-client-banner-main">
                            <span class="wiz-client-banner-name" id="cot-banner-name">—</span>
                            <div class="wiz-client-banner-sub">
                                <span class="wiz-client-banner-doc" id="cot-banner-doc">—</span>
                                <span class="wiz-client-banner-doc ms-2" id="cot-banner-fecha" style="opacity:.8;">
                                    <i class="ri-calendar-line me-1"></i><span id="cot-banner-fecha-val">—</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="wiz-stepper" role="tablist" aria-label="Pasos de la cotización">
                    <button type="button" class="wiz-step-marker is-active" data-step="1" role="tab" aria-selected="true">
                        <span class="wiz-step-dot">1</span>
                        <span class="wiz-step-label">Cliente</span>
                    </button>
                    <span class="wiz-step-line"><span class="wiz-step-line-fill" data-line="1"></span></span>
                    <button type="button" class="wiz-step-marker" data-step="2" role="tab" aria-selected="false">
                        <span class="wiz-step-dot">2</span>
                        <span class="wiz-step-label">Productos</span>
                    </button>
                    <span class="wiz-step-line"><span class="wiz-step-line-fill" data-line="2"></span></span>
                    <button type="button" class="wiz-step-marker" data-step="3" role="tab" aria-selected="false">
                        <span class="wiz-step-dot">3</span>
                        <span class="wiz-step-label">Resumen</span>
                    </button>
                </div>
                {{-- Chip "Creada por" — usuario logueado, gutter derecho (todos los pasos, solo al crear) --}}
                <div class="wiz-stepper-side wiz-stepper-side--right">
                    <div class="wiz-client-banner wiz-client-banner--creator" id="cot-creador-banner" hidden
                        aria-hidden="true" title="Creada por">
                        <span class="wiz-client-banner-label">Creada por:</span>
                        <img class="wiz-client-banner-avatar wiz-client-banner-avatar--img"
                            id="cot-creador-avatar" src="{{ Auth::user()->avatar_url }}" alt="" />
                        <div class="wiz-client-banner-main">
                            <span class="wiz-client-banner-name" id="cot-creador-name">{{ Auth::user()->name }}</span>
                            <div class="wiz-client-banner-sub">
                                <span style="font-size:0.7rem; opacity:.8;">
                                    <i class="ri-time-line me-1"></i><span id="cot-banner-hora">—</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form id="cotizacionForm" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" id="id-field" />
                <input type="hidden" id="cliente-id-field" name="cliente_id" />

                <div class="modal-body p-0 wiz-wizard-body">

                    {{-- ═════════════════════════ PASO 1 — CLIENTE ═════════════════════════ --}}
                    <section class="wiz-step-content is-active" id="cot-step-1" data-step="1">
                        <div class="wiz-step-header">
                            <span class="wiz-step-tag">Paso 1 de 3</span>
                            <h4 class="wiz-step-title">Cliente y datos generales</h4>
                            <p class="wiz-step-desc">Busca el cliente por su documento o créalo en línea, luego define las fechas y la prioridad.</p>
                        </div>

                        <div class="row g-3">
                            {{-- Card Cliente --}}
                            <div class="col-lg-7">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header border-0 bg-soft-primary">
                                        <h6 class="mb-0 text-atlantico-dark">
                                            <i class="ri-user-3-line me-2"></i>Datos del Cliente
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        {{-- Buscador de documento con icono de lupa --}}
                                        <label for="ci-rif-number-field" class="form-label small fw-semibold mb-1">
                                            Documento de identidad <span class="text-danger">*</span>
                                        </label>
                                        <div class="position-relative cot-search-doc-wrap">
                                            <div class="input-group cot-search-doc-group">
                                                <span class="input-group-text cot-search-doc-icon">
                                                    <i class="ri-search-2-line"></i>
                                                </span>
                                                <select class="form-select" id="ci-rif-prefix-field"
                                                    name="rif_prefix" style="max-width: 70px;">
                                                    <option value="V-">V-</option>
                                                    <option value="J-">J-</option>
                                                    <option value="E-">E-</option>
                                                    <option value="G-">G-</option>
                                                </select>
                                                <input type="text" id="ci-rif-number-field" name="rif_number"
                                                    class="form-control"
                                                    placeholder="Escribí el documento para buscar..."
                                                    autocomplete="off" required />
                                                <input type="hidden" id="ci-rif-full-field" name="ci_rif" />
                                            </div>
                                            <div id="cliente-autocomplete-list"
                                                class="list-group position-absolute w-100"
                                                style="z-index: 1050; top: 100%;"></div>
                                        </div>

                                        {{-- Hidden inputs (preservados para compat con scripts y submit) --}}
                                        <input type="hidden" id="cliente-nombre-field" name="cliente_nombre" />
                                        <input type="hidden" id="cliente-apellido-field" name="cliente_apellido" />
                                        <input type="hidden" id="cliente-razon-social-display" />
                                        <input type="hidden" id="cliente-email-field" name="cliente_email" />
                                        <input type="hidden" id="cliente-telefono-field" name="cliente_telefono" />
                                        {{-- Wrappers legacy preservados (controlan visibilidad razón social) --}}
                                        <span id="block-cot-nombre" hidden></span>
                                        <span id="block-cot-apellido" hidden></span>
                                        <span id="block-cot-razon-social" class="d-none" hidden></span>

                                        {{-- Empty state — sin cliente seleccionado --}}
                                        <div class="cot-cliente-empty" id="cot-cliente-empty">
                                            <div class="cot-cliente-empty-icon">
                                                <i class="ri-user-search-line"></i>
                                            </div>
                                            <p class="cot-cliente-empty-title">Buscá el cliente o creá uno nuevo</p>
                                            <p class="cot-cliente-empty-desc">
                                                Escribí el documento arriba para buscar entre clientes, empleados y proveedores existentes.
                                            </p>
                                            <button type="button" class="btn btn-outline-success cot-btn-create-cliente"
                                                id="open-add-cliente-modal">
                                                <i class="ri-user-add-line me-1"></i>Crear cliente nuevo
                                            </button>
                                        </div>

                                        {{-- Loading state — skeleton mientras busca --}}
                                        <div class="cot-cliente-loading" id="cot-cliente-loading" hidden>
                                            <div class="cot-skeleton cot-skeleton-circle"></div>
                                            <div class="flex-grow-1">
                                                <div class="cot-skeleton cot-skeleton-line cot-skeleton-line-md"></div>
                                                <div class="cot-skeleton cot-skeleton-line cot-skeleton-line-sm"></div>
                                            </div>
                                        </div>

                                        {{-- Selected client card --}}
                                        <div class="cot-cliente-card" id="cot-cliente-card" hidden>
                                            <div class="cot-cliente-avatar" id="cot-cliente-avatar"
                                                data-color-key="default">—</div>
                                            <div class="cot-cliente-info flex-grow-1">
                                                <div class="cot-cliente-name-row">
                                                    <h5 class="cot-cliente-name" id="cot-cliente-name-display">—</h5>
                                                    <span class="cot-cliente-roles" id="cot-cliente-roles"></span>
                                                </div>
                                                <p class="cot-cliente-doc">
                                                    <i class="ri-bank-card-line"></i>
                                                    <span id="cot-cliente-doc-display">—</span>
                                                </p>
                                                <div class="cot-cliente-contact-row">
                                                    <span class="cot-cliente-contact-item" id="cot-cliente-tel-wrap">
                                                        <i class="ri-phone-line"></i>
                                                        <span id="cot-cliente-tel-display">—</span>
                                                    </span>
                                                    <span class="cot-cliente-contact-item" id="cot-cliente-email-wrap">
                                                        <i class="ri-mail-line"></i>
                                                        <span id="cot-cliente-email-display">—</span>
                                                    </span>
                                                </div>
                                                <div class="cot-cliente-stats" id="cot-cliente-stats" hidden>
                                                    <span class="cot-cliente-stat">
                                                        <i class="ri-file-list-3-line"></i>
                                                        <strong id="cot-cliente-stat-count">0</strong> cotización(es) previas
                                                    </span>
                                                    <span class="cot-cliente-stat-sep">·</span>
                                                    <span class="cot-cliente-stat" id="cot-cliente-stat-last-wrap" hidden>
                                                        <i class="ri-time-line"></i>
                                                        Última: <strong id="cot-cliente-stat-last">—</strong>
                                                    </span>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-link btn-sm cot-cliente-change-btn"
                                                id="cot-cliente-change-btn" title="Cambiar cliente">
                                                <i class="ri-refresh-line me-1"></i>Cambiar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Card Detalles + Estado --}}
                            <div class="col-lg-5">
                                <div class="card border-0 shadow-sm mb-3">
                                    <div class="card-header border-0 bg-soft-primary">
                                        <h6 class="mb-0 text-atlantico-dark">
                                            <i class="ri-calendar-event-line me-2"></i>Detalles de la cotización
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <label for="fecha-cotizacion-field"
                                                    class="form-label small fw-semibold mb-1">
                                                    Fecha emisión <span class="text-danger">*</span>
                                                </label>
                                                <input type="date" id="fecha-cotizacion-field" name="fecha_cotizacion"
                                                    class="form-control form-control-sm" required />
                                            </div>
                                            <div class="col-md-6">
                                                <label for="fecha-validez-field"
                                                    class="form-label small fw-semibold mb-1">
                                                    Fecha validez
                                                </label>
                                                <input type="date" id="fecha-validez-field" name="fecha_validez"
                                                    class="form-control form-control-sm" />
                                            </div>
                                            <div class="col-12">
                                                <div class="cot-date-shortcuts" id="cot-date-shortcuts">
                                                    <span class="cot-date-shortcuts-label">Validez rápida:</span>
                                                    <button type="button" class="cot-date-chip" data-days="0">Hoy</button>
                                                    <button type="button" class="cot-date-chip" data-days="15">+15 días</button>
                                                    <button type="button" class="cot-date-chip" data-days="30">+30 días</button>
                                                    <button type="button" class="cot-date-chip" data-days="60">+60 días</button>
                                                </div>
                                            </div>
                                            <div class="col-12 mt-2">
                                                <label class="form-label small fw-semibold mb-1">Prioridad</label>
                                                <div class="cot-priority-chips" role="radiogroup" aria-label="Prioridad">
                                                    <button type="button" class="cot-priority-chip cot-priority-chip--normal is-active"
                                                        data-value="Normal" role="radio" aria-checked="true">
                                                        <span class="cot-priority-dot"></span>
                                                        <span>Normal</span>
                                                    </button>
                                                    <button type="button" class="cot-priority-chip cot-priority-chip--alta"
                                                        data-value="Alta" role="radio" aria-checked="false">
                                                        <span class="cot-priority-dot"></span>
                                                        <span>Alta</span>
                                                    </button>
                                                    <button type="button" class="cot-priority-chip cot-priority-chip--urgente"
                                                        data-value="Urgente" role="radio" aria-checked="false">
                                                        <span class="cot-priority-dot"></span>
                                                        <span>Urgente</span>
                                                    </button>
                                                </div>
                                                <select id="prioridad-field" name="prioridad" class="d-none" tabindex="-1">
                                                    <option value="Normal" selected>Normal</option>
                                                    <option value="Alta">Alta</option>
                                                    <option value="Urgente">Urgente</option>
                                                </select>
                                                <small class="text-muted d-block mt-1" style="font-size:0.7rem;">
                                                    <i class="ri-information-line me-1"></i>Se hereda al pedido al convertir.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Card Estado (solo edición) --}}
                                <div class="card border-0 shadow-sm" id="estado-field-wrapper" style="display: none;">
                                    <div class="card-header border-0 bg-soft-secondary">
                                        <h6 class="mb-0 text-atlantico-green">
                                            <i class="ri-flag-line me-2"></i>Estado de la cotización
                                        </h6>
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="cot-estado-chips" role="radiogroup" aria-label="Estado">
                                            <button type="button" class="cot-estado-chip cot-estado-chip--pendiente is-active"
                                                data-value="Pendiente" role="radio" aria-checked="true">Pendiente</button>
                                            <button type="button" class="cot-estado-chip cot-estado-chip--aprobada"
                                                data-value="Aprobada" role="radio" aria-checked="false">Aprobada</button>
                                            <button type="button" class="cot-estado-chip cot-estado-chip--cancelada"
                                                data-value="Cancelada" role="radio" aria-checked="false">Cancelada</button>
                                        </div>
                                        <select id="estado-field" name="estado" class="d-none" tabindex="-1">
                                            <option value="Pendiente">Pendiente</option>
                                            <option value="Aprobada">Aprobada</option>
                                            <option value="Cancelada">Cancelada</option>
                                        </select>
                                        <small class="text-muted d-block mt-2">
                                            <i class="ri-information-line me-1"></i>"Vencida" se asigna automáticamente al pasar la fecha.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- ═════════════════════════ PASO 2 — PRODUCTOS ═════════════════════════ --}}
                    <section class="wiz-step-content" id="cot-step-2" data-step="2" hidden>
                        <div class="wiz-step-header">
                            <div class="d-flex align-items-end justify-content-between flex-wrap gap-3">
                                <div>
                                    <span class="wiz-step-tag">Paso 2 de 3</span>
                                    <h4 class="wiz-step-title">Productos de la cotización</h4>
                                    <p class="wiz-step-desc">Explora el catálogo y configura cada prenda con sus colores, tallas y bordados.</p>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn btn-atlantico-brand" id="btn-explorar-catalogo">
                                        <i class="ri-layout-grid-line me-1"></i>Explorar catálogo
                                    </button>
                                    {{-- Botón legacy "Agregar manual" eliminado: el catálogo + configurador
                                         es el único punto de entrada. Se preserva el ID #add-producto-item
                                         oculto para no romper handlers existentes que aún lo referencian. --}}
                                    <button type="button" id="add-producto-item" hidden tabindex="-1"></button>
                                </div>
                            </div>
                        </div>

                        {{-- KPIs --}}
                        <div class="cot-kpi-grid mb-3">
                            <div class="cot-kpi">
                                <span class="cot-kpi-label">Líneas</span>
                                <span class="cot-kpi-value" id="cot-kpi-items">0</span>
                            </div>
                            <div class="cot-kpi">
                                <span class="cot-kpi-label">Subtotal</span>
                                <span class="cot-kpi-value" id="cot-kpi-subtotal">$0,00</span>
                            </div>
                            <div class="cot-kpi cot-kpi--total">
                                <span class="cot-kpi-label">Total</span>
                                <span class="cot-kpi-value" id="cot-kpi-total">$0,00</span>
                                <input type="hidden" id="total-display-field" />
                                <span id="total-display-value" hidden>$0,00</span>
                            </div>
                        </div>

                        {{-- Empty state --}}
                        <div class="cot-empty-state" id="cot-empty-state">
                            <div class="cot-empty-icon">
                                <i class="ri-shopping-bag-3-line"></i>
                            </div>
                            <h5 class="cot-empty-title">Aún no hay productos</h5>
                            <p class="cot-empty-desc">Explora el catálogo para seleccionar prendas y configurar sus colores, tallas y bordados.</p>
                            <button type="button" class="btn btn-atlantico-brand" onclick="document.getElementById('btn-explorar-catalogo').click()">
                                <i class="ri-layout-grid-line me-1"></i>Abrir catálogo
                            </button>
                        </div>

                        {{-- Tabla agrupada — bloques por (producto + color + bordados) --}}
                        <div id="cot-grouped-list" class="cot-grouped-list"></div>

                        {{-- Container de productos (gestionado por scripts/main.blade.php).
                             Permanentemente oculto: es la fuente de verdad para FormData
                             pero el usuario nunca interactúa directamente con las cards.
                             Toda la edición pasa por el catálogo / configurador / acciones
                             en cada fila de la tabla agrupada. --}}
                        <div id="productos-container" class="cot-productos-container" hidden></div>
                    </section>

                    {{-- ═════════════════════════ PASO 3 — RESUMEN ═════════════════════════ --}}
                    <section class="wiz-step-content" id="cot-step-3" data-step="3" hidden>
                        <div class="wiz-step-header">
                            <span class="wiz-step-tag">Paso 3 de 3</span>
                            <h4 class="wiz-step-title">Resumen y notas</h4>
                            <p class="wiz-step-desc">Revisa el desglose, agrega condiciones y finaliza.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-lg-7">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header border-0 bg-soft-primary">
                                        <h6 class="mb-0 text-atlantico-dark">
                                            <i class="ri-file-text-line me-2"></i>Notas y condiciones
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <label class="form-label fw-semibold small text-muted mb-1">
                                            <i class="ri-sticky-note-line me-1"></i>Notas internas
                                        </label>
                                        <textarea id="notas-field" name="notas" class="form-control"
                                            rows="3"
                                            placeholder="Observaciones internas sobre la cotización..."
                                            style="resize: vertical; min-height: 70px;"></textarea>
                                        <small class="text-muted d-block mt-1 mb-3">
                                            <i class="ri-information-line me-1"></i>Opcional · máx. 2000 caracteres
                                        </small>

                                        <label class="form-label fw-semibold small text-muted mb-1">
                                            <i class="ri-file-shield-2-line me-1"></i>Condiciones y Términos
                                        </label>
                                        <textarea id="condiciones-field" name="condiciones_terminos" class="form-control"
                                            rows="4"
                                            placeholder="Cláusulas legales, condiciones de pago, tiempo de entrega, garantías..."
                                            style="resize: vertical; min-height: 90px;"></textarea>
                                        <small class="text-muted d-block mt-1">
                                            <i class="ri-information-line me-1"></i>Se incluirá en el PDF de la cotización
                                        </small>

                                        {{-- Wrapper legacy preservado para compatibilidad de IDs --}}
                                        <div id="notasCollapseBody" class="d-none"></div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm mt-3">
                                    <div class="card-header border-0 bg-soft-primary">
                                        <h6 class="mb-0 text-atlantico-dark">
                                            <i class="ri-list-check-2 me-2"></i>Líneas incluidas
                                        </h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive cot-resumen-tabla-wrap">
                                            <table class="table table-sm mb-0 cot-resumen-tabla">
                                                <thead>
                                                    <tr>
                                                        <th style="width:46%">Producto</th>
                                                        <th class="text-center" style="width:14%">Cant.</th>
                                                        <th class="text-end" style="width:18%">Unit.</th>
                                                        <th class="text-end" style="width:22%">Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="cot-resumen-lineas">
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-3 small">
                                                            Sin productos agregados
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="cot-resumen-card">
                                    <div class="cot-resumen-card-header">
                                        <i class="ri-money-dollar-circle-line"></i>
                                        <span>Resumen final</span>
                                    </div>
                                    <div class="cot-resumen-card-body">
                                        <div class="cot-resumen-row" style="opacity: 0.8; font-size: 0.75rem; margin-bottom: 6px;">
                                            <span class="cot-resumen-row-label">
                                                <i class="ri-bank-line me-1"></i>Tasa BCV (USD/VES)
                                            </span>
                                            <span class="cot-resumen-row-value" id="cot-resumen-tasa">Bs 0,0000</span>
                                        </div>
                                        <div class="cot-resumen-row">
                                            <span class="cot-resumen-row-label">Subtotal</span>
                                            <span class="cot-resumen-row-value" id="cot-resumen-subtotal">$0,00</span>
                                        </div>
                                        <div class="cot-resumen-row">
                                            <span class="cot-resumen-row-label">IVA (16%)</span>
                                            <span class="cot-resumen-row-value" id="cot-resumen-iva">$0,00</span>
                                        </div>
                                        <div class="cot-resumen-divider"></div>
                                        <div class="cot-resumen-row cot-resumen-row--total">
                                            <span class="cot-resumen-row-label">Total</span>
                                            <span class="cot-resumen-row-value" id="cot-resumen-total">$0,00</span>
                                        </div>
                                        <div class="cot-resumen-row" style="opacity: 0.85;">
                                            <span class="cot-resumen-row-label" style="font-size: 0.75rem;">
                                                <i class="ri-exchange-dollar-line me-1"></i>Equivalente Bs
                                            </span>
                                            <span class="cot-resumen-row-value" id="cot-resumen-total-bs" style="font-size: 0.85rem; color: rgba(255,255,255,0.85);">Bs 0,00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>{{-- /modal-body --}}

                <div class="modal-footer wiz-wizard-footer">
                    <div class="wiz-wizard-footer-info">
                        <span class="wiz-wizard-step-info">
                            <span id="cot-step-current">1</span> de 3
                        </span>
                    </div>
                    <div class="wiz-wizard-footer-actions">
                        <button type="button" class="btn btn-light wiz-wizard-btn-prev" id="btn-cot-prev"
                            style="display:none;">
                            <i class="ri-arrow-left-line me-1"></i>Anterior
                        </button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Cerrar
                        </button>
                        <button type="button" class="btn btn-atlantico-brand wiz-wizard-btn-next" id="btn-cot-next">
                            Continuar<i class="ri-arrow-right-line ms-1"></i>
                        </button>
                        <button type="submit" class="btn btn-success wiz-wizard-btn-submit" id="add-btn"
                            style="display:none;">
                            <i class="ri-check-line me-1"></i>Crear cotización
                        </button>
                        <button type="submit" class="btn btn-success wiz-wizard-btn-submit" id="edit-btn"
                            style="display:none;">
                            <i class="ri-save-line me-1"></i>Actualizar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════
     Modal Catálogo de Productos — anidado sobre el wizard
     Fase 2: grilla con filtros + carrito lateral.
     Fase 3 conectará el click en card → modal Configurador.
     ════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade atlantico-modal atlantico-modal--op cot-catalog-modal" id="catalogoProductosModal"
    tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="cat-icon-square">
                        <i class="ri-layout-grid-line"></i>
                    </div>
                    <div>
                        <p class="cat-eyebrow mb-0" id="cat-eyebrow">Catálogo de productos</p>
                        <h5 class="modal-title m-0" id="catalogoProductosLabel">Selecciona productos</h5>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-0 cat-body">

                {{-- Sidebar filtros --}}
                <aside class="cat-filters" id="cat-filters">
                    <div class="cat-filters-header">
                        <span>Filtros</span>
                        <button type="button" class="cat-clear-btn" id="cat-clear-filters">Limpiar</button>
                    </div>

                    <div class="cat-filter-group">
                        <label class="cat-filter-label" for="cat-search">Buscar</label>
                        <div class="position-relative">
                            <i class="ri-search-line cat-search-icon"></i>
                            <input type="text" id="cat-search" class="cat-search-input"
                                placeholder="Código, modelo o tipo...">
                        </div>
                    </div>

                    <div class="cat-filter-group">
                        <label class="cat-filter-label">Tipo de producto</label>
                        <div id="cat-filter-tipos" class="cat-filter-list">
                            {{-- Renderizado por JS --}}
                        </div>
                    </div>

                    <div class="cat-filter-group">
                        <label class="cat-filter-label">Rango de precio</label>
                        <div class="cat-price-range">
                            <input type="number" id="cat-price-min" class="form-control form-control-sm"
                                placeholder="Min" min="0" step="0.01">
                            <span class="cat-price-sep">—</span>
                            <input type="number" id="cat-price-max" class="form-control form-control-sm"
                                placeholder="Max" min="0" step="0.01">
                        </div>
                    </div>
                </aside>

                {{-- Grilla central --}}
                <main class="cat-grid-wrap">
                    <div class="cat-grid-toolbar">
                        <span class="cat-results-count">
                            <strong id="cat-results-count">0</strong> resultados
                        </span>
                        <select id="cat-sort" class="form-select form-select-sm cat-sort-select">
                            <option value="relevance">Más relevantes</option>
                            <option value="price-asc">Precio: menor a mayor</option>
                            <option value="price-desc">Precio: mayor a menor</option>
                            <option value="name">Nombre A–Z</option>
                        </select>
                    </div>

                    <div class="cat-grid" id="cat-grid">
                        {{-- Renderizado por JS --}}
                    </div>

                    <div class="cat-grid-empty d-none" id="cat-grid-empty">
                        <div class="cat-grid-empty-icon"><i class="ri-search-2-line"></i></div>
                        <p class="cat-grid-empty-title">Sin resultados</p>
                        <p class="cat-grid-empty-desc">Ajusta los filtros o cambia la búsqueda.</p>
                    </div>
                </main>

                {{-- Carrito lateral --}}
                <aside class="cat-cart">
                    <div class="cat-cart-header">
                        <p class="cat-cart-eyebrow">Selección actual</p>
                        <h6 class="cat-cart-title">
                            <span id="cat-cart-count">0</span> productos
                        </h6>
                    </div>

                    <div class="cat-cart-list" id="cat-cart-list">
                        {{-- Items configurados por el usuario (Fase 3+) --}}
                    </div>

                    <div class="cat-cart-empty" id="cat-cart-empty">
                        <i class="ri-shopping-cart-2-line"></i>
                        <p>Configura productos del catálogo<br>para agregarlos a la cotización.</p>
                    </div>

                    <div class="cat-cart-footer">
                        <div class="cat-cart-total-row">
                            <span class="cat-cart-total-label">Estimado</span>
                            <span class="cat-cart-total-value" id="cat-cart-total">$0,00</span>
                        </div>
                        <button type="button" class="btn btn-atlantico-brand w-100" id="btn-cat-confirmar" disabled>
                            <i class="ri-check-line me-1"></i>Agregar a la cotización
                        </button>
                    </div>
                </aside>

            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════
     Modal Selector de Variante — Fase 4 (variantes/atributos)
     Se abre al clickear una card de TIPO en el catálogo. Permite elegir
     tela y valores de atributos para resolver el producto exacto antes
     de configurar color/tallas/bordados.
     ════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade atlantico-modal atlantico-modal--op cot-variant-modal" id="varianteSelectorModal"
    tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="cat-icon-square">
                        <i class="ri-shape-2-line"></i>
                    </div>
                    <div>
                        <p class="cat-eyebrow mb-0">Selecciona la variante</p>
                        <h5 class="modal-title m-0" id="vs-tipo-nombre">—</h5>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">
                    Elige la combinación que vas a cotizar. El SKU se resuelve automáticamente.
                </p>

                {{-- Sección Tela --}}
                <div class="mb-3" id="vs-tela-section">
                    <label class="form-label small fw-semibold mb-2">
                        <i class="ri-shirt-line me-1"></i>Tela
                    </label>
                    <div id="vs-tela-options" class="d-flex flex-wrap gap-2">
                        {{-- Render dinámico --}}
                    </div>
                </div>

                {{-- Sección Atributos (chips por atributo) --}}
                <div id="vs-atributos-section">
                    <div class="text-muted small text-center py-3">
                        <span class="spinner-border spinner-border-sm me-2"></span>Cargando variantes…
                    </div>
                </div>

                {{-- Resultado de resolución --}}
                <div id="vs-result-found" class="alert alert-success py-2 small mt-3 mb-0" style="display:none;">
                    <i class="ri-check-line me-1"></i>
                    <strong id="vs-result-codigo">—</strong> · <span id="vs-result-precio">—</span>
                </div>
                <div id="vs-result-missing" class="alert alert-warning py-2 small mt-3 mb-0" style="display:none;">
                    <i class="ri-error-warning-line me-1"></i>
                    Esta combinación no existe en el catálogo. Crea el producto en
                    <a href="{{ url('productos') }}" target="_blank">/productos</a> primero.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-atlantico-brand" id="vs-confirm" disabled>
                    <i class="ri-arrow-right-line me-1"></i>Configurar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════
     Modal Configurador de Producto — Fase 3
     Se abre al clickear una card del catálogo. Color + tallas matrix.
     Los bordados se configuran por línea desde el paso 2 (Fase 4).
     ════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade atlantico-modal atlantico-modal--op cot-config-modal" id="cotConfiguradorModal"
    tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-sm cfg-back-btn" id="cfg-back-to-catalog"
                        title="Volver al catálogo">
                        <i class="ri-arrow-left-line"></i>
                    </button>
                    <div>
                        <p class="cfg-eyebrow mb-0" id="cfg-eyebrow">Configurar producto</p>
                        <h5 class="modal-title m-0" id="cfg-title">—</h5>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-0 cfg-body">
                <div class="cfg-grid">

                    {{-- Columna izquierda: ficha del producto --}}
                    <aside class="cfg-info">
                        <div class="cfg-media">
                            <div class="cfg-media-inner" id="cfg-media-inner">
                                <i class="ri-t-shirt-2-line"></i>
                            </div>
                        </div>
                        <div class="cfg-info-body">
                            <p class="cfg-info-codigo" id="cfg-info-codigo">—</p>
                            <h6 class="cfg-info-modelo" id="cfg-info-modelo">—</h6>
                            <p class="cfg-info-tipo" id="cfg-info-tipo">—</p>
                            <div class="cfg-info-price-block">
                                <span class="cfg-info-price-label">Precio base</span>
                                <span class="cfg-info-price-value" id="cfg-info-precio">$0,00</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100 mt-2" id="cfg-cambiar-variante" style="display:none;">
                                <i class="ri-shape-2-line me-1"></i>Cambiar variante
                            </button>
                            <p class="cfg-info-help">
                                <i class="ri-information-line me-1"></i>El bordado se configura por
                                línea en el paso 2 después de confirmar el carrito.
                            </p>
                        </div>
                    </aside>

                    {{-- Columna derecha: configuración --}}
                    <main class="cfg-form">

                        {{-- 1. Color --}}
                        <section class="cfg-section">
                            <header class="cfg-section-header">
                                <span class="cfg-section-num">1</span>
                                <div>
                                    <h6 class="cfg-section-title">Color</h6>
                                    <p class="cfg-section-desc">Selecciona el color para esta configuración.</p>
                                </div>
                                <span class="cfg-color-selected" id="cfg-color-selected">Sin seleccionar</span>
                            </header>
                            <div class="cfg-color-grid" id="cfg-color-grid">
                                {{-- Renderizado por JS --}}
                            </div>
                        </section>

                        {{-- 2. Tallas y cantidades --}}
                        <section class="cfg-section">
                            <header class="cfg-section-header">
                                <span class="cfg-section-num">2</span>
                                <div>
                                    <h6 class="cfg-section-title">Tallas y cantidades</h6>
                                    <p class="cfg-section-desc">Indica cuántas unidades de cada talla.</p>
                                </div>
                                <button type="button" class="btn btn-sm btn-link cfg-distribute-btn"
                                    id="cfg-distribute-btn">
                                    <i class="ri-equalizer-line me-1"></i>Distribuir uniforme
                                </button>
                            </header>
                            <div class="cfg-tallas-grid" id="cfg-tallas-grid">
                                {{-- Renderizado por JS --}}
                            </div>
                            <div class="cfg-tallas-summary" id="cfg-tallas-summary">
                                <span>Total unidades</span>
                                <strong id="cfg-tallas-total">0</strong>
                            </div>
                        </section>

                        {{-- 3. Precio unitario --}}
                        <section class="cfg-section">
                            <header class="cfg-section-header">
                                <span class="cfg-section-num">3</span>
                                <div>
                                    <h6 class="cfg-section-title">Precio unitario</h6>
                                    <p class="cfg-section-desc">Por defecto usa el precio base del producto. Modifícalo si negociaste otro precio con el cliente.</p>
                                </div>
                                <button type="button" class="btn btn-sm btn-link cfg-precio-reset"
                                    id="cfg-precio-reset" title="Restaurar al precio base">
                                    <i class="ri-refresh-line me-1"></i>Restaurar
                                </button>
                            </header>
                            <div class="cfg-precio-row">
                                <div class="cfg-precio-input-wrap">
                                    <span class="cfg-precio-prefix">$</span>
                                    <input type="number" id="cfg-precio-input" class="cfg-precio-input"
                                        min="0" step="0.01" placeholder="0.00" inputmode="decimal">
                                </div>
                                <span class="cfg-precio-base-hint">
                                    Precio base: <strong id="cfg-precio-base-hint-value">$0,00</strong>
                                </span>
                            </div>
                        </section>

                    </main>
                </div>

                {{-- Footer sticky con resumen y acciones --}}
                <div class="cfg-actions">
                    <div class="cfg-actions-summary">
                        <div class="cfg-summary-item">
                            <span class="cfg-summary-label">Unidades</span>
                            <span class="cfg-summary-value" id="cfg-summary-qty">0</span>
                        </div>
                        <div class="cfg-summary-item">
                            <span class="cfg-summary-label">Precio unitario</span>
                            <span class="cfg-summary-value" id="cfg-summary-unit">$0,00</span>
                        </div>
                        <div class="cfg-summary-item cfg-summary-item--total">
                            <span class="cfg-summary-label">Subtotal</span>
                            <span class="cfg-summary-value" id="cfg-summary-subtotal">$0,00</span>
                        </div>
                    </div>
                    <div class="cfg-actions-buttons">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Cancelar
                        </button>
                        <button type="button" class="btn btn-atlantico-brand" id="cfg-save-btn" disabled>
                            <i class="ri-shopping-cart-2-line me-1"></i>
                            <span id="cfg-save-btn-label">Agregar al carrito</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════
     Modal Agregar/Editar Cliente — réplica visual del estándar /clientes
     IDs preservan sufijo "-cliente" para no chocar con el modal padre.
     ════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade atlantico-modal" id="modalAddCliente" tabindex="-1" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="clienteFormCotizacion" class="modal-content" novalidate>
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="modalClienteTitle">Agregar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="id-field-cliente" />

                {{-- 1. Identificación: Documento + Tipo Cliente --}}
                <div class="modal-form-section">
                    <div class="section-header-compact">
                        <div class="modal-form-section-title">
                            <i class="ri-fingerprint-line"></i>Identificación
                        </div>
                    </div>
                    <div class="row g-2 mb-0">
                        <div class="col-md-6">
                            <label for="documento-number-field-cliente" class="form-label required">
                                Documento (Cédula o RIF)
                            </label>
                            <div class="input-group">
                                <select class="form-select" id="documento-prefix-field-cliente"
                                    style="max-width: 70px;">
                                    <option value="V-">V-</option>
                                    <option value="J-">J-</option>
                                    <option value="E-">E-</option>
                                    <option value="G-">G-</option>
                                </select>
                                <input type="text" id="documento-number-field-cliente" class="form-control"
                                    placeholder="Nro. documento" maxlength="10" required />
                            </div>
                            <input type="hidden" id="documento-field-cliente" name="documento" />
                            <small class="text-muted d-block mt-1 mb-1">Máximo 10 dígitos</small>
                            <div id="documento-error-cliente" class="invalid-feedback" style="display: none;"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="tipo_cliente-field-cliente" class="form-label required">Tipo de Cliente</label>
                            <select id="tipo_cliente-field-cliente" name="tipo_cliente" class="form-select" required>
                                <option value="">Seleccione</option>
                                <option value="natural">Natural</option>
                                <option value="juridico">Jurídico</option>
                                <option value="gubernamental">Gubernamental</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 2. Datos del Cliente: Nombre+Apellido (natural) / Razón Social (jurídico/gubernamental) --}}
                <div class="modal-form-section">
                    <div class="modal-form-section-title">
                        <i class="ri-user-3-line"></i>Datos del Cliente
                    </div>

                    <div id="campos-persona-natural-cliente" class="row g-2 mb-0">
                        <div class="col-md-6">
                            <label for="nombre-field-cliente" class="form-label required">Nombre</label>
                            <input type="text" id="nombre-field-cliente" name="nombre" class="form-control"
                                placeholder="Nombre" maxlength="100" required />
                            <div id="nombre-error-cliente" class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="apellido-field-cliente" class="form-label required">Apellido</label>
                            <input type="text" id="apellido-field-cliente" name="apellido" class="form-control"
                                placeholder="Apellido" maxlength="100" required />
                            <div id="apellido-error-cliente" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div id="campos-razon-social-cliente" class="row g-2 mb-0 d-none">
                        <div class="col-12">
                            <label for="razon-social-field-cliente" class="form-label">Razón Social</label>
                            <input type="text" id="razon-social-field-cliente" name="nombre" class="form-control"
                                placeholder="Razón Social de la empresa" maxlength="200" />
                        </div>
                    </div>
                </div>

                {{-- 3. Contacto: Email + Teléfono + Dirección --}}
                <div class="modal-form-section">
                    <div class="modal-form-section-title">
                        <i class="ri-contacts-book-2-line"></i>Contacto
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label for="email-field-cliente" class="form-label">Email</label>
                            <input type="email" id="email-field-cliente" name="email" class="form-control"
                                placeholder="correo@ejemplo.com" />
                            <div id="email-error-cliente" class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="telefono-number-field-cliente" class="form-label required">Teléfono</label>
                            <div class="input-group">
                                <select class="form-select" id="telefono-prefix-field-cliente"
                                    style="max-width: 100px; min-width: 100px;">
                                    <option value="0412">0412</option>
                                    <option value="0422">0422</option>
                                    <option value="0414">0414</option>
                                    <option value="0424" selected>0424</option>
                                    <option value="0416">0416</option>
                                    <option value="0426">0426</option>
                                </select>
                                <input type="text" id="telefono-number-field-cliente" class="form-control"
                                    placeholder="1234567" maxlength="7" required />
                            </div>
                            <input type="hidden" id="telefono-field-cliente" name="telefono" />
                            <div id="telefono-error-cliente" class="invalid-feedback" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="row g-2 mb-0">
                        <div class="col-12">
                            <label for="direccion-field-cliente" class="form-label required">Dirección</label>
                            <textarea id="direccion-field-cliente" name="direccion" class="form-control"
                                placeholder="Dirección completa" maxlength="500" rows="2" required></textarea>
                            <div id="direccion-error-cliente" class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>

                {{-- 4. Ubicación: Estado + Municipio --}}
                <div class="modal-form-section">
                    <div class="modal-form-section-title">
                        <i class="ri-map-pin-2-line"></i>Ubicación
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label for="estado_territorial-field-cliente" class="form-label required">Estado</label>
                            <select name="estado_territorial" id="estado_territorial-field-cliente"
                                class="form-select" required>
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
                            <label for="ciudad-field-cliente" class="form-label required">Municipio</label>
                            <select name="ciudad" id="ciudad-field-cliente" class="form-select" required>
                                <option value="">Primero seleccione un estado</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 5. Estatus --}}
                <div class="modal-form-section mb-0">
                    <div class="modal-form-section-title">
                        <i class="ri-shield-check-line"></i>Estatus
                    </div>
                    <div class="form-check form-switch form-switch-success">
                        <input type="hidden" name="estatus" value="0" />
                        <input class="form-check-input" type="checkbox" role="switch" id="estatus-field-cliente"
                            name="estatus" value="1" checked />
                        <label class="form-check-label" for="estatus-field-cliente"
                            id="estatus-label-cliente">Activo</label>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light border-0">
                <div class="hstack gap-2 justify-content-end">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cerrar
                    </button>
                    <button type="button" class="btn btn-success" id="add-btn-cliente">
                        <i class="ri-add-line me-1"></i>Agregar
                    </button>
                    <button type="button" class="btn btn-success" id="edit-btn-cliente" style="display: none;">
                        <i class="ri-save-line me-1"></i>Actualizar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal para seleccionar producto (Movido al final para z-index) -->
<div class="modal fade" id="productosModalCotizacion" tabindex="-1" aria-labelledby="productosModalCotizacionLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <!-- Header — Nivel 2: Modal utilitario de búsqueda -->
            <div class="modal-header utility-modal-header p-3" id="productosModalCotizacion-header">
                <h5 class="modal-title" id="productosModalCotizacionLabel">
                    <i class="ri-search-line me-2" style="opacity: 0.7;"></i>Buscar y Seleccionar Producto
                </h5>
                <button type="button" class="btn-close utility-modal-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Estilos en public/assets/css/custom.css — sección "MÓDULO COTIZACIONES" --}}

                <!-- Card de Filtros -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-soft-success">
                        <h6 class="mb-0 text-atlantico-cyan">
                            <i class="ri-filter-3-line me-2"></i>Filtros de búsqueda
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <div class="input-group">
                                    <span class="input-group-text bg-soft-primary border-primary text-primary">
                                        <i class="ri-search-line"></i>
                                    </span>
                                    <input type="text" id="buscarProductoModalCotizacion"
                                        class="form-control border-primary"
                                        placeholder="Buscar por código, tipo o modelo...">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <select id="filtroTipoProductoCotizacion"
                                    class="form-select border-success text-success">
                                    <option value="">📁 Todos los tipos</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card de Tabla de Productos -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 bg-soft-primary">
                        <h6 class="mb-0 text-atlantico-dark">
                            <i class="ri-store-2-line me-2"></i>Catálogo de Productos
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 350px;">
                            <table class="table table-hover mb-0 table-seleccionable" id="productosModalCotizacionTable">
                                <thead style="position: sticky; top: 0; z-index: 10;">
                                    <tr>
                                        <th style="width:8%;  border:none;" class="text-center"><i
                                                class="ri-image-line"></i></th>
                                        <th style="width:20%; border:none;"><i class="ri-barcode-line me-1"></i>Código
                                        </th>
                                        <th style="width:20%; border:none;"><i class="ri-folder-line me-1"></i>Tipo</th>
                                        <th style="width:28%; border:none;"><i class="ri-t-shirt-line me-1"></i>Modelo
                                        </th>
                                        <th style="width:16%; border:none;"><i
                                                class="ri-money-dollar-circle-line me-1"></i>Precio</th>
                                        <th style="width:8%;  border:none;" class="text-center"><i
                                                class="ri-check-line"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="productosModalCotizacionBody">
                                    <!-- Se llena con JavaScript -->
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Cargando productos...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

@include('admin.partials.catalog_modals')