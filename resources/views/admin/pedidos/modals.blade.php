{{-- ═══════════════════════════════════════════════════════════════════
     WIZARD PEDIDO — showModal
     Modos: crear nuevo · completar desde cotización · editar
     Lógica JS en: pedidos/scripts/main.blade.php (TASK-011+)
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade atlantico-modal atlantico-modal--op wiz-modal" id="showModal" tabindex="-1"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-guard-id-field="ped-wiz-id-field" data-guard-save-btn="ped-wiz-edit-btn">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-sm-down">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Pedido</h5>
                <div class="wiz-dt-slot me-2">
                    @include('admin.partials.wizard-datetime-bar', ['prefix' => 'ped', 'modalId' => 'showModal', 'sm' => true])
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            {{-- Stepper visual — 4 pasos --}}
            <div class="wiz-stepper-wrapper">
                {{-- Chip cliente persistente — ocupa el gutter izquierdo del stepper (pasos 2+) --}}
                <div class="wiz-stepper-side wiz-stepper-side--left">
                    <div class="wiz-client-banner" id="ped-cliente-banner" hidden aria-hidden="true"
                        title="Cliente del pedido">
                        <span class="wiz-client-banner-label">Para:</span>
                        <div class="wiz-client-banner-avatar" id="ped-banner-avatar">—</div>
                        <div class="wiz-client-banner-main">
                            <span class="wiz-client-banner-name" id="ped-banner-name">—</span>
                            <div class="wiz-client-banner-sub">
                                <span class="wiz-client-banner-doc" id="ped-banner-doc">—</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="wiz-stepper" role="tablist" aria-label="Pasos del pedido">
                    <button type="button" class="wiz-step-marker is-active" data-step="1"
                        role="tab" aria-selected="true" aria-controls="ped-wiz-step-1">
                        <span class="wiz-step-dot">1</span>
                        <span class="wiz-step-label">Cliente</span>
                    </button>
                    <span class="wiz-step-line"><span class="wiz-step-line-fill" data-line="1"></span></span>
                    <button type="button" class="wiz-step-marker" data-step="2"
                        role="tab" aria-selected="false" aria-controls="ped-wiz-step-2">
                        <span class="wiz-step-dot">2</span>
                        <span class="wiz-step-label">Productos</span>
                    </button>
                    <span class="wiz-step-line"><span class="wiz-step-line-fill" data-line="2"></span></span>
                    <button type="button" class="wiz-step-marker" data-step="3"
                        role="tab" aria-selected="false" aria-controls="ped-wiz-step-3">
                        <span class="wiz-step-dot">3</span>
                        <span class="wiz-step-label">Pago</span>
                    </button>
                    <span class="wiz-step-line"><span class="wiz-step-line-fill" data-line="3"></span></span>
                    <button type="button" class="wiz-step-marker" data-step="4"
                        role="tab" aria-selected="false" aria-controls="ped-wiz-step-4">
                        <span class="wiz-step-dot">4</span>
                        <span class="wiz-step-label">Resumen</span>
                    </button>
                </div>
                {{-- Chip "Creado por" — usuario logueado, gutter derecho (todos los pasos, solo al crear) --}}
                <div class="wiz-stepper-side wiz-stepper-side--right">
                    <div class="wiz-client-banner wiz-client-banner--creator" id="ped-creador-banner" hidden
                        aria-hidden="true" title="Creado por">
                        <span class="wiz-client-banner-label">Creado por:</span>
                        <img class="wiz-client-banner-avatar wiz-client-banner-avatar--img"
                            id="ped-creador-avatar" src="{{ Auth::user()->avatar_url }}" alt="" />
                        <div class="wiz-client-banner-main">
                            <span class="wiz-client-banner-name" id="ped-creador-name">{{ Auth::user()->name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <form id="pedidoForm" novalidate>
                @csrf
                <input type="hidden" id="ped-wiz-id-field" />
                <input type="hidden" id="ped-wiz-cotizacion-id-field" name="cotizacion_id" />
                <input type="hidden" id="ped-wiz-cliente-id-field" name="cliente_id" />

                <div class="modal-body p-0 wiz-wizard-body">

                    {{-- ════════════════════════ PASO 1 — CLIENTE ════════════════════════ --}}
                    <section class="wiz-step-content is-active" id="ped-wiz-step-1" data-step="1">
                        <div class="wiz-step-header wiz-step-header--with-aside">
                            <div class="wiz-step-header-main">
                                <h4 class="wiz-step-title">Cliente y datos del pedido</h4>
                                <p class="wiz-step-desc">Busca el cliente por su documento, define las fechas y la prioridad del pedido.</p>
                            </div>
                            {{-- Chip: datos heredados de cotización (oculto por defecto) --}}
                            <div class="ped-banner-heredado d-none" id="ped-banner-heredado-p1"
                                title="Datos heredados de la cotización — puedes revisar y ajustar antes de guardar.">
                                <i class="ri-file-transfer-line"></i>
                                <span>Heredado de cotización <strong class="ped-banner-cot-num">#—</strong></span>
                            </div>
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
                                        {{-- Buscador de documento --}}
                                        <label for="ped-ci-rif-number-field" class="form-label small fw-semibold mb-1">
                                            Documento de identidad <span class="text-danger">*</span>
                                        </label>
                                        <div class="position-relative cot-search-doc-wrap">
                                            <div class="input-group cot-search-doc-group">
                                                <span class="input-group-text cot-search-doc-icon">
                                                    <i class="ri-search-2-line"></i>
                                                </span>
                                                <select class="form-select" id="ped-ci-rif-prefix-field"
                                                    name="rif_prefix" style="max-width: 70px;">
                                                    <option value="V-">V-</option>
                                                    <option value="J-">J-</option>
                                                    <option value="E-">E-</option>
                                                    <option value="G-">G-</option>
                                                </select>
                                                <input type="text" id="ped-ci-rif-number-field" name="rif_number"
                                                    class="form-control"
                                                    placeholder="Escribí el documento para buscar..."
                                                    autocomplete="off" />
                                                <input type="hidden" id="ped-ci-rif-full-field" name="ci_rif" />
                                            </div>
                                            <div id="ped-cliente-autocomplete-list"
                                                class="list-group position-absolute w-100"
                                                style="z-index: 1050; top: 100%;"></div>
                                        </div>

                                        {{-- Empty state --}}
                                        <div class="cot-cliente-empty" id="ped-cliente-empty">
                                            <div class="cot-cliente-empty-icon">
                                                <i class="ri-user-search-line"></i>
                                            </div>
                                            <p class="cot-cliente-empty-title">Buscá el cliente o creá uno nuevo</p>
                                            <p class="cot-cliente-empty-desc">
                                                Escribí el documento arriba para buscar entre clientes, empleados y proveedores existentes.
                                            </p>
                                            <button type="button" class="btn btn-outline-success cot-btn-create-cliente"
                                                id="ped-open-add-cliente-modal">
                                                <i class="ri-user-add-line me-1"></i>Crear cliente nuevo
                                            </button>
                                        </div>

                                        {{-- Loading state (skeleton) --}}
                                        <div class="cot-cliente-loading" id="ped-cliente-loading" hidden>
                                            <div class="cot-skeleton cot-skeleton-circle"></div>
                                            <div class="flex-grow-1">
                                                <div class="cot-skeleton cot-skeleton-line cot-skeleton-line-md"></div>
                                                <div class="cot-skeleton cot-skeleton-line cot-skeleton-line-sm"></div>
                                            </div>
                                        </div>

                                        {{-- Cliente card (seleccionado) --}}
                                        <div class="cot-cliente-card" id="ped-cliente-card" hidden>
                                            <div class="cot-cliente-avatar" id="ped-cliente-avatar"
                                                data-color-key="default">—</div>
                                            <div class="cot-cliente-info flex-grow-1">
                                                <div class="cot-cliente-name-row">
                                                    <h5 class="cot-cliente-name" id="ped-cliente-name-display">—</h5>
                                                    <span class="cot-cliente-roles" id="ped-cliente-roles"></span>
                                                </div>
                                                <p class="cot-cliente-doc">
                                                    <i class="ri-bank-card-line"></i>
                                                    <span id="ped-cliente-doc-display">—</span>
                                                </p>
                                                <div class="cot-cliente-contact-row">
                                                    <span class="cot-cliente-contact-item" id="ped-cliente-tel-wrap">
                                                        <i class="ri-phone-line"></i>
                                                        <span id="ped-cliente-tel-display">—</span>
                                                    </span>
                                                    <span class="cot-cliente-contact-item" id="ped-cliente-email-wrap">
                                                        <i class="ri-mail-line"></i>
                                                        <span id="ped-cliente-email-display">—</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <button type="button"
                                                class="btn btn-link btn-sm cot-cliente-change-btn"
                                                id="ped-cliente-change-btn" title="Cambiar cliente">
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
                                            <i class="ri-calendar-event-line me-2"></i>Detalles del pedido
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <label for="ped-fecha-pedido-field"
                                                    class="form-label small fw-semibold mb-1">
                                                    Fecha del pedido <span class="text-danger">*</span>
                                                </label>
                                                <input type="date" id="ped-fecha-pedido-field" name="fecha_pedido"
                                                    class="form-control form-control-sm" required />
                                            </div>
                                            <div class="col-md-6">
                                                <label for="ped-fecha-entrega-field"
                                                    class="form-label small fw-semibold mb-1">
                                                    Entrega estimada
                                                </label>
                                                <input type="date" id="ped-fecha-entrega-field"
                                                    name="fecha_entrega_estimada"
                                                    class="form-control form-control-sm" />
                                            </div>
                                            <div class="col-12">
                                                <div class="cot-date-shortcuts" id="ped-date-shortcuts">
                                                    <span class="cot-date-shortcuts-label">Entrega:</span>
                                                    <button type="button" class="cot-date-chip ped-date-chip" data-days="0">Hoy</button>
                                                    <button type="button" class="cot-date-chip ped-date-chip" data-days="15">+15 días</button>
                                                    <button type="button" class="cot-date-chip ped-date-chip" data-days="30">+30 días</button>
                                                    <button type="button" class="cot-date-chip ped-date-chip" data-days="60">+60 días</button>
                                                </div>
                                            </div>
                                            <div class="col-12 mt-2">
                                                <label class="form-label small fw-semibold mb-1">Prioridad</label>
                                                <div class="cot-priority-chips" role="radiogroup"
                                                    aria-label="Prioridad">
                                                    <button type="button"
                                                        class="cot-priority-chip ped-priority-chip cot-priority-chip--normal is-active"
                                                        data-value="Normal" role="radio" aria-checked="true">
                                                        <span class="cot-priority-dot"></span>
                                                        <span>Normal</span>
                                                    </button>
                                                    <button type="button"
                                                        class="cot-priority-chip ped-priority-chip cot-priority-chip--alta"
                                                        data-value="Alta" role="radio" aria-checked="false">
                                                        <span class="cot-priority-dot"></span>
                                                        <span>Alta</span>
                                                    </button>
                                                    <button type="button"
                                                        class="cot-priority-chip ped-priority-chip cot-priority-chip--urgente"
                                                        data-value="Urgente" role="radio" aria-checked="false">
                                                        <span class="cot-priority-dot"></span>
                                                        <span>Urgente</span>
                                                    </button>
                                                </div>
                                                <select id="ped-prioridad-field" name="prioridad"
                                                    class="d-none" tabindex="-1">
                                                    <option value="Normal" selected>Normal</option>
                                                    <option value="Alta">Alta</option>
                                                    <option value="Urgente">Urgente</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Card Estado (solo en modo edición) --}}
                                <div class="card border-0 shadow-sm" id="ped-estado-field-wrapper"
                                    style="display: none;">
                                    <div class="card-header border-0 bg-soft-secondary">
                                        <h6 class="mb-0 text-atlantico-green">
                                            <i class="ri-flag-line me-2"></i>Estado del pedido
                                        </h6>
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="ped-estado-readonly">
                                            <span class="badge" id="ped-estado-badge">—</span>
                                            <span class="ped-estado-auto-hint">
                                                <i class="ri-flashlight-line"></i>
                                                Se actualiza automáticamente según el avance de producción.
                                            </span>
                                        </div>
                                        {{-- Estado gobernado por producción; el campo solo refleja el actual --}}
                                        <input type="hidden" id="ped-estado-field" name="estado" value="Pendiente" />
                                        <small class="text-muted d-block mt-2">
                                            <i class="ri-information-line me-1"></i>Para anular el pedido usá el botón
                                            <strong>Cancelar</strong> del listado.
                                        </small>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </section>

                    {{-- ════════════════════════ PASO 2 — PRODUCTOS ════════════════════════ --}}
                    <section class="wiz-step-content" id="ped-wiz-step-2" data-step="2" hidden>
                        <div class="wiz-step-header wiz-step-header--with-aside">
                            <div class="wiz-step-header-main">
                                <h4 class="wiz-step-title">Productos del pedido</h4>
                                <p class="wiz-step-desc">Definidos en la cotización de origen — solo lectura. Para cambiarlos, edita la cotización.</p>
                            </div>
                            {{-- Chip: productos heredados de cotización (solo lectura) --}}
                            <div class="ped-banner-heredado d-none" id="ped-banner-heredado-p2"
                                title="Productos definidos en la cotización de origen — solo lectura.">
                                <i class="ri-file-transfer-line"></i>
                                <span>Heredado de cotización <strong class="ped-banner-cot-num">#—</strong></span>
                            </div>
                        </div>

                        {{-- KPI bar — líneas + total --}}
                        <div class="cot-kpi-grid mb-3">
                            <div class="cot-kpi">
                                <span class="cot-kpi-label">Líneas</span>
                                <span class="cot-kpi-value" id="ped-kpi-lineas">0</span>
                            </div>
                            <div class="cot-kpi cot-kpi--total">
                                <span class="cot-kpi-label">Total del pedido</span>
                                <span class="cot-kpi-value" id="ped-kpi-total">$0.00</span>
                            </div>
                        </div>

                        {{-- Empty state --}}
                        <div class="cot-empty-state" id="ped-productos-empty">
                            <div class="cot-empty-icon">
                                <i class="ri-shopping-bag-3-line"></i>
                            </div>
                            <h6 class="cot-empty-title">Sin productos</h6>
                            <p class="cot-empty-desc">La cotización de origen no tiene productos.</p>
                        </div>

                        {{-- Grilla de productos agrupada (solo lectura — espejo del wizard de cotización) --}}
                        <div id="ped-productos-list"></div>

                    </section>

                    {{-- ════════════════════════ PASO 3 — PAGO ════════════════════════ --}}
                    <section class="wiz-step-content" id="ped-wiz-step-3" data-step="3" hidden>
                        <div class="wiz-step-header">
                            <h4 class="wiz-step-title">Pago</h4>
                            <p class="wiz-step-desc">Registra uno o varios pagos para abonar al pedido. Podés combinar métodos y registrar pagos parciales; también podés continuar sin abono.</p>
                        </div>
                        {{-- Resumen / progreso de pago --}}
                        <div class="ped-pay-summary is-empty" id="ped-pay-summary">
                            <div class="ped-pay-figs">
                                <div class="ped-pay-fig">
                                    <span class="ped-pay-fig-label">Total del pedido</span>
                                    <span class="ped-pay-fig-value" id="ped-pay-total">$0,00</span>
                                </div>
                                <div class="ped-pay-fig ped-pay-fig--abono">
                                    <span class="ped-pay-fig-label">Abonado</span>
                                    <span class="ped-pay-fig-value" id="ped-pay-abono">$0,00</span>
                                </div>
                                <div class="ped-pay-fig ped-pay-fig--rest">
                                    <span class="ped-pay-fig-label">Restante</span>
                                    <span class="ped-pay-fig-value" id="ped-pay-restante">$0,00</span>
                                </div>
                            </div>
                            <div class="ped-pay-progress">
                                <div class="ped-pay-progress-bar" id="ped-pay-progress-bar"></div>
                            </div>
                            <span class="ped-pay-badge" id="ped-pay-badge">Sin abono</span>
                        </div>

                        {{-- Compat: campos leídos por validación/submit legacy --}}
                        <input type="hidden" id="ped-pago-total-display" />
                        <input type="hidden" id="ped-pago-abono-field" />

                        {{-- Card: Métodos de pago (agregar + lista) --}}
                        <div class="card border-0 shadow-sm">
                            <div class="card-header border-0 bg-soft-primary ped-pay-card-head">
                                <h6 class="mb-0 text-atlantico-dark">
                                    <i class="ri-bank-card-line me-2"></i>Métodos de pago
                                </h6>
                                <div class="ped-pay-add-btns">
                                    <button type="button" class="ped-pay-add-btn" data-metodo="efectivo" id="ped-pay-add-efectivo">
                                        <i class="ri-add-line"></i><span>Efectivo</span>
                                    </button>
                                    <button type="button" class="ped-pay-add-btn" data-metodo="transferencia">
                                        <i class="ri-add-line"></i><span>Transferencia</span>
                                    </button>
                                    <button type="button" class="ped-pay-add-btn" data-metodo="pago_movil">
                                        <i class="ri-add-line"></i><span>Pago móvil</span>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                {{-- Lista de pagos --}}
                                <div class="ped-pay-list" id="ped-pay-list"></div>

                                {{-- Estado vacío --}}
                                <div class="ped-pay-empty" id="ped-pay-empty">
                                    <i class="ri-wallet-3-line"></i>
                                    <p>Aún no registras pagos. Usá los botones de arriba para agregar uno, o continuá sin abono.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Template de fila de pago compacta (una línea, clonada por JS) --}}
                        <template id="ped-pay-row-tpl">
                            <div class="ped-pay-row">
                                <span class="ped-pay-row-icon"><i></i></span>
                                <span class="ped-pay-row-method"></span>
                                <div class="ped-pay-row-fields">
                                    <select class="form-select form-select-sm ped-pay-banco" title="Banco">
                                        <option value="">Banco…</option>
                                        @foreach($bancos as $banco)
                                            <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" class="form-control form-control-sm ped-pay-ref" placeholder="Nro. referencia" title="Referencia" />
                                </div>
                                <div class="ped-pay-amount input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control ped-pay-monto" min="0" step="0.01" placeholder="0,00" />
                                </div>
                                <button type="button" class="ped-pay-fill" title="Saldar el restante en este pago">Resto</button>
                                <button type="button" class="ped-pay-row-del" title="Quitar pago">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </template>
                    </section>

                    {{-- ════════════════════════ PASO 4 — RESUMEN ════════════════════════ --}}
                    <section class="wiz-step-content" id="ped-wiz-step-4" data-step="4" hidden>
                        <div class="wiz-step-header">
                            <h4 class="wiz-step-title">Resumen del pedido</h4>
                            <p class="wiz-step-desc">Revisa toda la información antes de guardar el pedido.</p>
                        </div>
                        <div class="row g-3">

                            {{-- Card: Cliente --}}
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header border-0 bg-soft-primary py-2 px-3">
                                        <h6 class="mb-0 text-atlantico-dark fs-13">
                                            <i class="ri-user-star-line me-1"></i>Cliente
                                        </h6>
                                    </div>
                                    <div class="card-body p-3" id="ped-res-cliente-bloque">
                                        <p class="text-muted small mb-0">—</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Card: Datos del pedido --}}
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header border-0 bg-soft-primary py-2 px-3">
                                        <h6 class="mb-0 text-atlantico-dark fs-13">
                                            <i class="ri-calendar-todo-line me-1"></i>Datos del pedido
                                        </h6>
                                    </div>
                                    <div class="card-body p-3" id="ped-res-datos-bloque">
                                        <p class="text-muted small mb-0">—</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Card: Productos --}}
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header border-0 bg-soft-primary py-2 px-3">
                                        <h6 class="mb-0 text-atlantico-dark fs-13">
                                            <i class="ri-shopping-bag-3-line me-1"></i>Productos
                                            (<span id="ped-res-lineas">0</span>&nbsp;línea(s))
                                        </h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="ps-3">Producto</th>
                                                        <th class="text-center">Cant.</th>
                                                        <th>Talla</th>
                                                        <th>Color</th>
                                                        <th class="text-end">P. Unit.</th>
                                                        <th class="text-end pe-3">Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="ped-res-productos-tbody">
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-3 small">
                                                            Sin productos
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr class="table-primary">
                                                        <td colspan="5" class="text-end fw-bold small ps-3">Total estimado</td>
                                                        <td class="text-end fw-bold pe-3" id="ped-res-total">$0.00</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Card: Pago --}}
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header border-0 bg-soft-primary py-2 px-3">
                                        <h6 class="mb-0 text-atlantico-dark fs-13">
                                            <i class="ri-wallet-line me-1"></i>Pago
                                        </h6>
                                    </div>
                                    <div class="card-body p-3" id="ped-res-pago-bloque">
                                        <p class="text-muted small mb-0">—</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Hero: total pedido --}}
                            <div class="col-lg-4 d-flex">
                                <div class="cot-resumen-card cot-resumen-card--compact w-100">
                                    <div class="cot-resumen-card-header">
                                        <i class="ri-money-dollar-circle-line"></i>
                                        <span>Total Pedido</span>
                                    </div>
                                    <div class="cot-resumen-card-body">
                                        <div class="cot-resumen-row cot-resumen-row--total">
                                            <span class="cot-resumen-row-label">Estimado</span>
                                            <span class="cot-resumen-row-value" id="ped-res-total-hero">$0.00</span>
                                        </div>
                                        <div class="cot-resumen-divider"></div>
                                        <div class="cot-resumen-row">
                                            <span class="cot-resumen-row-label">Abonado</span>
                                            <span class="cot-resumen-row-value" id="ped-res-abono-hero">$0.00</span>
                                        </div>
                                        <div class="cot-resumen-row">
                                            <span class="cot-resumen-row-label">Restante</span>
                                            <span class="cot-resumen-row-value" id="ped-res-restante-hero">$0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Card: Términos y Condiciones (informativo, se incluyen en el PDF) --}}
                            <div class="col-12">
                                @include('admin.partials.terminos-accordion', ['prefix' => 'ped'])
                            </div>

                        </div>
                    </section>

                </div>{{-- /modal-body --}}

                <div class="modal-footer wiz-wizard-footer">
                    <div class="wiz-wizard-footer-info">
                        <span class="wiz-wizard-step-info">
                            Paso <span id="ped-step-current">1</span> de 4
                        </span>
                    </div>
                    <div class="wiz-wizard-footer-actions">
                        <button type="button" class="btn btn-light wiz-wizard-btn-prev" id="btn-ped-prev"
                            style="display:none;">
                            <i class="ri-arrow-left-line me-1"></i>Anterior
                        </button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Cerrar
                        </button>
                        <button type="button" class="btn btn-atlantico-brand wiz-wizard-btn-next" id="btn-ped-next">
                            Continuar<i class="ri-arrow-right-line ms-1"></i>
                        </button>
                        <button type="submit" class="btn btn-success wiz-wizard-btn-submit" id="ped-wiz-add-btn"
                            style="display:none;">
                            <i class="ri-check-line me-1"></i>Guardar Pedido
                        </button>
                        <button type="submit" class="btn btn-success wiz-wizard-btn-submit" id="ped-wiz-edit-btn"
                            style="display:none;">
                            <i class="ri-save-line me-1"></i>Actualizar Pedido
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     VIEW MODAL — Detalles del Pedido (read-only)
     Estándares: emp-icon-box utilities + bg-soft-primary + cot-resumen-card
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade atlantico-modal atlantico-modal--op" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title mb-0">Detalles del Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="row g-2">
                    {{-- Columna Izquierda --}}
                    <div class="col-lg-6">
                        {{-- ── Información del Cliente ──────────────────────────── --}}
                        <div class="card border-0 shadow-sm mb-2">
                            <div class="card-header border-0 bg-soft-primary py-2 px-3">
                                <h6 class="mb-0 text-atlantico-dark fs-13">
                                    <i class="ri-user-star-line me-1"></i>Información del Cliente
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <i class="ri-user-line emp-icon--navy"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Cliente</small>
                                                <span class="fw-semibold fs-13" id="view-cliente-nombre">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="emp-icon-box emp-icon-box--green rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <i class="ri-bank-card-line emp-icon--green"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Documento</small>
                                                <span class="fw-semibold fs-13" id="view-ci-rif">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="emp-icon-box emp-icon-box--teal rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <i class="ri-mail-line emp-icon--teal"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Email</small>
                                                <span class="fw-semibold fs-13" id="view-cliente-email">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <i class="ri-phone-line emp-icon--navy"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Teléfono</small>
                                                <span class="fw-semibold fs-13" id="view-cliente-telefono">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Datos del Pedido ─────────────────────────────────── --}}
                        <div class="card border-0 shadow-sm mb-2">
                            <div class="card-header border-0 bg-soft-primary py-2 px-3">
                                <h6 class="mb-0 text-atlantico-dark fs-13">
                                    <i class="ri-calendar-todo-line me-1"></i>Datos del Pedido
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <i class="ri-calendar-line emp-icon--navy"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Fecha Pedido</small>
                                                <span class="fw-semibold fs-13" id="view-fecha-pedido">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <i class="ri-calendar-check-line emp-icon--navy"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Entrega Estimada</small>
                                                <span class="fw-semibold fs-13" id="view-fecha-entrega-estimada">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <i class="ri-flag-line emp-icon--navy"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Prioridad</small>
                                                <span class="fw-semibold fs-13" id="view-prioridad">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <i class="ri-checkbox-circle-line emp-icon--navy"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Estado</small>
                                                <span class="fw-semibold fs-13" id="view-estado">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex align-items-center">
                                            <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <i class="ri-user-settings-line emp-icon--navy"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Creado por</small>
                                                <span class="fw-semibold fs-13" id="view-usuario-creador">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Datos del Pago ───────────────────────────────────── --}}
                        <div class="card border-0 shadow-sm mb-2">
                            <div class="card-header border-0 bg-soft-primary py-2 px-3">
                                <h6 class="mb-0 text-atlantico-dark fs-13">
                                    <i class="ri-wallet-line me-1"></i>Datos del Pago
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    <div class="col-12 col-lg-6">
                                        <div class="d-flex align-items-center">
                                            <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <i class="ri-bank-card-2-line emp-icon--navy"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Método Pago</small>
                                                <div class="d-flex flex-wrap gap-1 mt-1" id="view-metodo-pago">-</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-lg-3">
                                        <div class="d-flex align-items-center">
                                            <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <i class="ri-hand-coin-line emp-icon--navy"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Abono</small>
                                                <span class="fw-semibold fs-13" id="view-abono">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-lg-3">
                                        <div class="d-flex align-items-center">
                                            <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <i class="ri-wallet-2-line emp-icon--navy"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Restante</small>
                                                <span class="fw-semibold fs-13" id="view-restante">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mt-1">
                                    <div class="col-12 col-md-6" id="view-bloque-transferencia-container" style="display:none;">
                                        <div class="rounded p-2 bg-soft-primary border">
                                            <span class="fw-bold text-atlantico-dark d-block mb-1 fs-12">
                                                <i class="ri-bank-card-line me-1"></i>Transferencia
                                            </span>
                                            <div class="mb-1">
                                                <small class="text-muted d-block fs-12">Banco</small>
                                                <span class="fw-semibold fs-13" id="view-banco-transferencia">-</span>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Referencia</small>
                                                <span class="fw-semibold fs-13" id="view-referencia-transferencia">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6" id="view-bloque-pago-movil-container" style="display:none;">
                                        <div class="rounded p-2 bg-soft-primary border">
                                            <span class="fw-bold text-atlantico-dark d-block mb-1 fs-12">
                                                <i class="ri-smartphone-line me-1"></i>Pago Móvil
                                            </span>
                                            <div class="mb-1">
                                                <small class="text-muted d-block fs-12">Banco</small>
                                                <span class="fw-semibold fs-13" id="view-banco-pago-movil">-</span>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Referencia</small>
                                                <span class="fw-semibold fs-13" id="view-referencia-pago-movil">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Total (hero gradiente del proyecto) ─────────────── --}}
                        <div class="cot-resumen-card mb-0">
                            <div class="cot-resumen-card-header py-2 px-3">
                                <i class="ri-money-dollar-circle-line"></i>
                                <span>Total Pedido</span>
                                <small class="ms-auto text-white-50 fw-normal text-lowercase" style="letter-spacing: 0;">
                                    <i class="ri-refresh-line me-1"></i>auto
                                </small>
                            </div>
                            <div class="cot-resumen-card-body p-3 text-end">
                                <span class="fw-bold text-white fs-3 lh-1" id="view-total-resumen">$0.00</span>
                                <small class="text-white-50 d-block mt-1 fs-12">
                                    <i class="ri-exchange-dollar-line me-1"></i><span id="view-total-resumen-bs">Bs 0,00</span>
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Columna Derecha: Productos --}}
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header border-0 bg-soft-primary py-2 px-3">
                                <h6 class="mb-0 text-atlantico-dark fs-13">
                                    <i class="ri-shopping-bag-3-line me-1"></i>Productos del Pedido
                                </h6>
                            </div>
                            <div class="card-body p-3" style="max-height: 500px; overflow-y: auto;">
                                <div id="view-productos-container">
                                    {{-- Productos se cargan dinámicamente --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     Modal Agregar Cliente — inline desde paso 1 del wizard
     IDs idénticos al modal de cotizaciones; callbacks distintos en scripts/main.blade.php
     ═══════════════════════════════════════════════════════════════════ --}}
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
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     Modal Agregar / Editar Producto — paso 2 del wizard de pedidos
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade atlantico-modal" id="ped-agregar-producto-modal" tabindex="-1"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="ped-agregar-prod-title">Agregar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="ped-prod-edit-idx" value="" />

                {{-- Buscar producto del catálogo --}}
                <div class="mb-3">
                    <label class="form-label small fw-semibold mb-1">
                        Producto <span class="text-danger">*</span>
                    </label>
                    <div class="position-relative">
                        <input type="text" id="ped-prod-search-input" class="form-control"
                            placeholder="Buscar por nombre o código..." autocomplete="off" />
                        <div id="ped-prod-search-list" class="list-group position-absolute w-100"
                            style="z-index:1200; top:100%; max-height:200px; overflow-y:auto;"></div>
                    </div>
                    <input type="hidden" id="ped-prod-id-field" />
                    <input type="hidden" id="ped-prod-nombre-field" />
                </div>

                <div class="row g-2">
                    <div class="col-md-4">
                        <label for="ped-prod-cantidad-field" class="form-label small fw-semibold mb-1">
                            Cantidad <span class="text-danger">*</span>
                        </label>
                        <input type="number" id="ped-prod-cantidad-field" class="form-control"
                            min="1" step="1" value="1" />
                    </div>
                    <div class="col-md-4">
                        <label for="ped-prod-talla-field" class="form-label small fw-semibold mb-1">Talla</label>
                        <select id="ped-prod-talla-field" class="form-select">
                            <option value="">Sin talla</option>
                            @foreach($tallas as $talla)
                                <option value="{{ $talla->id }}">{{ $talla->etiqueta ?: $talla->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="ped-prod-color-field" class="form-label small fw-semibold mb-1">Color</label>
                        <select id="ped-prod-color-field" class="form-select">
                            <option value="">Sin color</option>
                            @foreach($colores as $color)
                                <option value="{{ $color->id }}">{{ $color->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="ped-prod-precio-field" class="form-label small fw-semibold mb-1">
                            Precio unitario ($) <span class="text-danger">*</span>
                        </label>
                        <input type="number" id="ped-prod-precio-field" class="form-control"
                            min="0" step="0.01" placeholder="0.00" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold mb-1">Subtotal</label>
                        <div class="form-control bg-light fw-semibold" id="ped-prod-subtotal-display">
                            $0.00
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <div class="hstack gap-2 justify-content-end">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-atlantico-brand" id="ped-prod-guardar-btn">
                        <i class="ri-check-line me-1"></i>Guardar producto
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     Modal Seleccionar Cotización
     Abre desde el paso 2 del wizard para importar productos
     ═══════════════════════════════════════════════════════════════════ --}}
@include('admin.pedidos.modals.seleccionar_cotizacion')
