{{-- ════════════════════════════════════════════════════════════════════
     Wizard "Nueva Compra" — 3 pasos (Proveedor · Ítems · Resumen)
     Reutiliza el scaffold .wiz-* compartido (ver docs/conventions/wizard-pattern.md)
     Prefijo de IDs: c-  ·  shell atlantico-modal--op (transaccional, emerald)
     ════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade atlantico-modal atlantico-modal--op wiz-modal" id="createCompraModal" tabindex="-1"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"
    data-guard-id-field="c-edit-id">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="compraModalTitle">
                    <i class="ri-shopping-bag-3-line me-1"></i>Nueva Compra
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- ── Stepper visual ──────────────────────────────────────────── --}}
            <div class="wiz-stepper-wrapper">
                {{-- Chip proveedor persistente — gutter izquierdo (pasos 2+) --}}
                <div class="wiz-stepper-side wiz-stepper-side--left">
                    <div class="wiz-client-banner" id="c-prov-banner" hidden aria-hidden="true"
                        title="Proveedor de la compra">
                        <span class="wiz-client-banner-label">Proveedor:</span>
                        <div class="wiz-client-banner-avatar" id="c-prov-banner-avatar">—</div>
                        <div class="wiz-client-banner-main">
                            <span class="wiz-client-banner-name" id="c-prov-banner-name">—</span>
                            <div class="wiz-client-banner-sub">
                                <span class="wiz-client-banner-doc" id="c-prov-banner-doc">—</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wiz-stepper" role="tablist" aria-label="Pasos de la compra">
                    <button type="button" class="wiz-step-marker is-active" data-step="1" role="tab"
                        aria-selected="true" aria-controls="c-step-1">
                        <span class="wiz-step-dot">1</span>
                        <span class="wiz-step-label">Proveedor</span>
                    </button>
                    <span class="wiz-step-line"><span class="wiz-step-line-fill" data-line="1"></span></span>
                    <button type="button" class="wiz-step-marker" data-step="2" role="tab"
                        aria-selected="false" aria-controls="c-step-2">
                        <span class="wiz-step-dot">2</span>
                        <span class="wiz-step-label">Ítems</span>
                    </button>
                    <span class="wiz-step-line"><span class="wiz-step-line-fill" data-line="2"></span></span>
                    <button type="button" class="wiz-step-marker" data-step="3" role="tab"
                        aria-selected="false" aria-controls="c-step-3">
                        <span class="wiz-step-dot">3</span>
                        <span class="wiz-step-label">Resumen</span>
                    </button>
                </div>

                {{-- Chip "Registrada por" — usuario logueado, gutter derecho (todos los pasos) --}}
                <div class="wiz-stepper-side wiz-stepper-side--right">
                    <div class="wiz-client-banner wiz-client-banner--creator" id="c-creador-banner"
                        aria-hidden="true" title="Registrada por">
                        <span class="wiz-client-banner-label">Registra:</span>
                        <img class="wiz-client-banner-avatar wiz-client-banner-avatar--img"
                            src="{{ Auth::user()->avatar_url }}" alt="" />
                        <div class="wiz-client-banner-main">
                            <span class="wiz-client-banner-name">{{ Auth::user()->name }}</span>
                            <div class="wiz-client-banner-sub">
                                <span style="font-size:0.7rem; opacity:.8;">
                                    <i class="ri-calendar-line me-1"></i>{{ date('d/m/Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form id="compraForm" novalidate>
                @csrf
                <input type="hidden" id="c-edit-id" value="">

                <div class="modal-body p-0 wiz-wizard-body">

                    {{-- ═══════════════════ PASO 1 — PROVEEDOR Y FACTURA ═══════════════════ --}}
                    <section class="wiz-step-content is-active" id="c-step-1" data-step="1">
                        <div class="wiz-step-header">
                            <span class="wiz-step-tag">Paso 1 de 3</span>
                            <h4 class="wiz-step-title">Proveedor y datos de factura</h4>
                            <p class="wiz-step-desc">Selecciona el proveedor y registra los datos del comprobante de compra.</p>
                        </div>

                        <div class="row g-3">
                            {{-- Card: Proveedor --}}
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header border-0 bg-soft-primary">
                                        <h6 class="mb-0 text-atlantico-dark">
                                            <i class="ri-store-2-line me-2"></i>Proveedor
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <label class="form-label small fw-semibold mb-1" for="c-prov-doc-number">
                                            <i class="ri-building-line me-1 text-muted"></i>Documento del proveedor <span class="text-danger">*</span>
                                        </label>
                                        {{-- Buscador de documento con icono de lupa --}}
                                        <div class="position-relative cot-search-doc-wrap">
                                            <div class="input-group cot-search-doc-group">
                                                <button type="button" class="input-group-text cot-search-doc-icon"
                                                    id="c-prov-browse-btn" title="Buscar en listado de proveedores">
                                                    <i class="ri-search-2-line"></i>
                                                </button>
                                                <select class="form-select" id="c-prov-doc-prefix" style="max-width: 70px;">
                                                    <option value="V-">V-</option>
                                                    <option value="J-" selected>J-</option>
                                                    <option value="E-">E-</option>
                                                    <option value="G-">G-</option>
                                                </select>
                                                <input type="text" id="c-prov-doc-number" class="form-control"
                                                    placeholder="Escribí el documento para buscar..." autocomplete="off">
                                            </div>
                                            <div id="c-prov-autocomplete" class="list-group position-absolute w-100"
                                                style="z-index: 1090; top: 100%;"></div>
                                        </div>

                                        {{-- Campo real enviado al backend --}}
                                        <input type="hidden" id="c-proveedor" name="proveedor_id" required>

                                        {{-- Empty state — sin proveedor seleccionado --}}
                                        <div class="cot-cliente-empty" id="c-prov-empty">
                                            <div class="cot-cliente-empty-icon"><i class="ri-store-2-line"></i></div>
                                            <p class="cot-cliente-empty-title">Buscá el proveedor o creá uno nuevo</p>
                                            <p class="cot-cliente-empty-desc">
                                                Escribí el documento arriba para buscar entre clientes, empleados y proveedores existentes.
                                            </p>
                                            <button type="button" class="btn btn-outline-success cot-btn-create-cliente"
                                                id="c-prov-create-btn">
                                                <i class="ri-add-line me-1"></i>Crear proveedor nuevo
                                            </button>
                                        </div>

                                        {{-- Loading state — skeleton mientras busca --}}
                                        <div class="cot-cliente-loading" id="c-prov-loading" hidden>
                                            <div class="cot-skeleton cot-skeleton-circle"></div>
                                            <div class="flex-grow-1">
                                                <div class="cot-skeleton cot-skeleton-line cot-skeleton-line-md"></div>
                                                <div class="cot-skeleton cot-skeleton-line cot-skeleton-line-sm"></div>
                                            </div>
                                        </div>

                                        {{-- Card del proveedor seleccionado --}}
                                        <div class="cot-cliente-card" id="c-prov-card" hidden>
                                            <div class="cot-cliente-avatar" id="c-prov-card-avatar">—</div>
                                            <div class="cot-cliente-info flex-grow-1">
                                                <div class="cot-cliente-name-row">
                                                    <h5 class="cot-cliente-name" id="c-prov-card-name">—</h5>
                                                    <span class="cot-cliente-roles">
                                                        <span class="cot-role-pill cot-role-proveedor" id="c-prov-card-tipo">Proveedor</span>
                                                    </span>
                                                </div>
                                                <p class="cot-cliente-doc">
                                                    <i class="ri-bank-card-line"></i>
                                                    <span id="c-prov-card-doc">—</span>
                                                </p>
                                                <div class="cot-cliente-contact-row">
                                                    <span class="cot-cliente-contact-item" id="c-prov-card-tel-wrap">
                                                        <i class="ri-phone-line"></i>
                                                        <span id="c-prov-card-tel">—</span>
                                                    </span>
                                                    <span class="cot-cliente-contact-item" id="c-prov-card-email-wrap">
                                                        <i class="ri-mail-line"></i>
                                                        <span id="c-prov-card-email">—</span>
                                                    </span>
                                                </div>
                                                <div class="cot-cliente-stats" id="c-prov-card-stats">
                                                    <span class="cot-cliente-stat">
                                                        <i class="ri-shopping-bag-3-line"></i>
                                                        <strong id="c-prov-card-count">0</strong>&nbsp;compra(s) previa(s)
                                                    </span>
                                                    <span class="cot-cliente-stat-sep" id="c-prov-card-last-sep" hidden>·</span>
                                                    <span class="cot-cliente-stat" id="c-prov-card-last-wrap" hidden>
                                                        <i class="ri-time-line"></i>
                                                        Última:&nbsp;<strong id="c-prov-card-last">—</strong>
                                                    </span>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-link btn-sm cot-cliente-change-btn"
                                                id="c-prov-change-btn" title="Cambiar proveedor">
                                                <i class="ri-refresh-line me-1"></i>Cambiar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Card: Comprobante --}}
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header border-0 bg-soft-primary">
                                        <h6 class="mb-0 text-atlantico-dark">
                                            <i class="ri-file-list-3-line me-2"></i>Datos del Comprobante
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label small fw-semibold mb-1" for="c-factura">N° de Factura</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="ri-receipt-line"></i></span>
                                                    <input type="text" id="c-factura" name="numero_factura"
                                                        class="form-control" maxlength="30" placeholder="Ej: 0001-000456">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label small fw-semibold mb-1" for="c-fecha">Fecha de Compra <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="ri-calendar-event-line"></i></span>
                                                    <input type="date" id="c-fecha" name="fecha_compra"
                                                        class="form-control" required value="{{ date('Y-m-d') }}">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label small fw-semibold mb-1">Tipo de Pago</label>
                                                <div class="seg-control" id="c-pago-seg" role="radiogroup" aria-label="Tipo de pago">
                                                    <button type="button" class="seg-option is-active" data-value="contado"
                                                        role="radio" aria-checked="true">
                                                        <i class="ri-money-dollar-circle-line"></i>Contado
                                                    </button>
                                                    <button type="button" class="seg-option" data-value="credito"
                                                        role="radio" aria-checked="false">
                                                        <i class="ri-calendar-schedule-line"></i>Crédito
                                                    </button>
                                                </div>
                                                <select id="c-tipo-pago" name="tipo_pago" class="d-none" tabindex="-1">
                                                    <option value="contado" selected>Contado</option>
                                                    <option value="credito">Crédito</option>
                                                </select>
                                            </div>
                                            <div class="col-12" id="c-vencimiento-wrap" style="display:none;">
                                                <label class="form-label small fw-semibold mb-1" id="c-vencimiento-label" for="c-vencimiento">Fecha de Vencimiento <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="ri-calendar-close-line"></i></span>
                                                    <input type="date" id="c-vencimiento" name="fecha_vencimiento"
                                                        class="form-control">
                                                </div>
                                                <small class="c-field-hint">
                                                    <i class="ri-alarm-warning-line me-1"></i>Fecha límite de pago acordada con el proveedor.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- ═══════════════════════ PASO 2 — ÍTEMS ════════════════════════════ --}}
                    <section class="wiz-step-content" id="c-step-2" data-step="2" hidden>
                        <div class="wiz-step-header d-flex align-items-end justify-content-between flex-wrap gap-2">
                            <div>
                                <span class="wiz-step-tag">Paso 2 de 3</span>
                                <h4 class="wiz-step-title">Ítems de la compra</h4>
                                <p class="wiz-step-desc mb-0">Agrega los insumos adquiridos con su cantidad y costo unitario.</p>
                            </div>
                            <button type="button" class="btn btn-success" id="c-add-item-btn">
                                <i class="ri-add-line me-1"></i>Agregar ítem
                            </button>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-header border-0 bg-soft-primary d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 text-atlantico-dark">
                                    <i class="ri-archive-line me-2"></i>Insumos
                                    <span class="text-muted fw-normal ms-1" id="c-items-count">(0)</span>
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                {{-- Empty state --}}
                                <div id="c-items-empty" class="text-center py-5 text-muted">
                                    <i class="ri-archive-2-line d-block opacity-50 mb-2" style="font-size:2.4rem;"></i>
                                    <p class="mb-2">Aún no agregaste ítems a esta compra.</p>
                                    <button type="button" class="btn btn-sm btn-soft-success" id="c-add-item-empty-btn">
                                        <i class="ri-add-line me-1"></i>Agregar el primer ítem
                                    </button>
                                </div>
                                {{-- Tabla dinámica --}}
                                <div id="c-items-table-wrap" class="cot-grouped-tablewrap" hidden>
                                    <table class="cot-grouped-table">
                                        <thead>
                                            <tr>
                                                <th class="cot-col-num text-center" style="width:36px;">#</th>
                                                <th>Insumo</th>
                                                <th class="text-center" style="width:140px;">Cantidad</th>
                                                <th class="text-center" style="width:125px;">Costo Unit.</th>
                                                <th class="text-end" style="width:110px;">Subtotal</th>
                                                <th class="text-center" style="width:48px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="c-items-tbody"></tbody>
                                    </table>
                                </div>
                            </div>
                            {{-- Pie: subtotal en vivo --}}
                            <div class="card-footer border-0 bg-transparent d-flex align-items-center justify-content-between py-2 px-3"
                                id="c-items-footer" hidden>
                                <span class="text-muted small">
                                    <i class="ri-stack-line me-1"></i><span id="c-items-footer-count">0</span> ítem(s)
                                </span>
                                <span class="small">
                                    Subtotal sin IVA:
                                    <strong class="text-atlantico-emerald ms-1" id="c-items-footer-subtotal">0.00</strong>
                                </span>
                            </div>
                        </div>
                    </section>

                    {{-- ═══════════════════════ PASO 3 — RESUMEN ══════════════════════════ --}}
                    <section class="wiz-step-content" id="c-step-3" data-step="3" hidden>
                        <div class="wiz-step-header">
                            <span class="wiz-step-tag">Paso 3 de 3</span>
                            <h4 class="wiz-step-title">Resumen y observaciones</h4>
                            <p class="wiz-step-desc">Revisa el desglose, ajusta el IVA y agrega notas antes de registrar.</p>
                        </div>

                        <div class="row g-3">
                            {{-- Observaciones + recap --}}
                            <div class="col-lg-7">
                                <div class="card border-0 shadow-sm mb-3">
                                    <div class="card-header border-0 bg-soft-primary">
                                        <h6 class="mb-0 text-atlantico-dark">
                                            <i class="ri-store-2-line me-2"></i>Detalle de la compra
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-sm-6">
                                                <small class="text-muted d-block mb-1"><i class="ri-store-2-line me-1"></i>Proveedor</small>
                                                <span class="fw-semibold" id="c-recap-proveedor">—</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <small class="text-muted d-block mb-1"><i class="ri-receipt-line me-1"></i>N° de Factura</small>
                                                <span class="fw-semibold" id="c-recap-factura">—</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <small class="text-muted d-block mb-1"><i class="ri-calendar-line me-1"></i>Fecha</small>
                                                <span id="c-recap-fecha">—</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <small class="text-muted d-block mb-1"><i class="ri-bank-card-line me-1"></i>Tipo de Pago</small>
                                                <span id="c-recap-pago">—</span>
                                            </div>
                                            <div class="col-12">
                                                <small class="text-muted d-block mb-1"><i class="ri-archive-line me-1"></i>Ítems</small>
                                                <span id="c-recap-items">0 insumo(s)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm">
                                    <div class="card-header border-0 bg-soft-primary">
                                        <h6 class="mb-0 text-atlantico-dark">
                                            <i class="ri-sticky-note-line me-2"></i>Observaciones
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea id="c-observaciones" name="observaciones"
                                            class="form-control" rows="3" maxlength="500"
                                            placeholder="Notas o comentarios (opcional)..."></textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Ticket de totales --}}
                            <div class="col-lg-5">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header border-0 bg-soft-primary">
                                        <h6 class="mb-0 text-atlantico-dark">
                                            <i class="ri-calculator-line me-2"></i>Resumen
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold mb-1" for="c-iva">% IVA</label>
                                            <div class="input-group">
                                                <input type="number" id="c-iva" name="iva_porcentaje"
                                                    class="form-control" value="16" min="0" max="100" step="0.01">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                        <div class="c-ticket">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted">Subtotal</span>
                                                <span class="fw-semibold" id="c-resumen-subtotal">0.00</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted">IVA (<span id="c-resumen-iva-pct">16</span>%)</span>
                                                <span class="fw-semibold" id="c-resumen-iva">0.00</span>
                                            </div>
                                            <hr class="my-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold fs-15">Total</span>
                                                <span class="fw-bold fs-18 text-atlantico-emerald" id="c-resumen-total">0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>{{-- /wiz-wizard-body --}}

                {{-- ── Footer del wizard ───────────────────────────────────────── --}}
                <div class="modal-footer wiz-wizard-footer">
                    <div class="wiz-wizard-footer-info">
                        <span class="wiz-wizard-step-info">
                            Paso <span id="c-step-current">1</span> de 3
                        </span>
                    </div>
                    <div class="wiz-wizard-footer-actions">
                        <button type="button" class="btn btn-light wiz-wizard-btn-prev" id="btn-c-prev"
                            style="display:none;">
                            <i class="ri-arrow-left-line me-1"></i>Anterior
                        </button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Cancelar
                        </button>
                        <button type="button" class="btn btn-atlantico-brand wiz-wizard-btn-next" id="btn-c-next">
                            Continuar<i class="ri-arrow-right-line ms-1"></i>
                        </button>
                        <button type="submit" class="btn btn-success wiz-wizard-btn-submit" id="c-submit-btn"
                            style="display:none;">
                            <i class="ri-save-line me-1"></i>Registrar Compra
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

@include('admin.compras.modals.buscar-proveedor')

{{-- ═══════════════════════════════════════════════════════════════════════════
     MINI-MODAL: Crear proveedor nuevo (inline, "extensión" del maestro Proveedores)
     Réplica fiel del modal #showModal de admin/proveedores/index.blade.php.
     Prefijo de IDs: cpr-  ·  navy (atlantico-modal) porque pertenece al maestro.
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade atlantico-modal" id="crearProveedorRapidoModal" tabindex="-1" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title">Agregar Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cprForm" novalidate>
                <div class="modal-body">

                    <div class="modal-form-section">
                        <div class="modal-form-section-title"><i class="ri-fingerprint-line"></i>Identificación</div>
                        <div class="row mb-0">
                            <div class="col-md-6 cpr-tipo-juridico">
                                <x-forms.input name="rif_number" label="RIF" id="cpr-rif-number-field"
                                    placeholder="Ej: 123456789" maxlength="9" required prependRaw="true">
                                    <x-slot:prepend>
                                        <select class="form-select" id="cpr-rif-prefix-field" style="max-width: 80px;">
                                            <option value="J-">J-</option>
                                            <option value="G-">G-</option>
                                        </select>
                                    </x-slot:prepend>
                                </x-forms.input>
                            </div>
                            <div class="col-md-6 cpr-tipo-natural" style="display: none;">
                                <x-forms.input name="documento_identidad_number" label="Documento de Identidad"
                                    id="cpr-documento-identidad-field" maxlength="8" placeholder="Ej: 12345678" required
                                    prependRaw="true">
                                    <x-slot:prepend>
                                        <select class="form-select" id="cpr-tipo-documento-field" style="max-width: 80px;">
                                            <option value="V-">V-</option>
                                            <option value="E-">E-</option>
                                        </select>
                                    </x-slot:prepend>
                                </x-forms.input>
                            </div>
                            <div class="col-md-6">
                                <x-forms.select name="tipo_proveedor" label="Tipo de Proveedor" required
                                    id="cpr-tipo-proveedor-field"
                                    :options="['juridico' => 'Jurídico (Empresa)', 'natural' => 'Natural (Persona)']"
                                    placeholder="" />
                            </div>
                        </div>
                    </div>

                    {{-- ── CAMPOS JURÍDICO ── --}}
                    <div id="cpr-campos-juridico">
                        <div class="modal-form-section">
                            <div class="modal-form-section-title"><i class="ri-building-line"></i>Datos Empresariales</div>
                            <div class="row mb-0">
                                <div class="col-md-6 mb-3">
                                    <x-forms.input name="razon_social" label="Razón Social" maxlength="200"
                                        placeholder="Nombre de la empresa" id="cpr-razon-social-field" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <x-forms.input name="direccion_jur" label="Dirección" maxlength="500"
                                        placeholder="Dirección de la empresa" id="cpr-direccion-jur-field" />
                                </div>
                            </div>
                        </div>

                        <div class="modal-form-section">
                            <div class="modal-form-section-title"><i class="ri-contacts-book-line"></i>Contacto</div>
                            <div class="row mb-0">
                                <div class="col-md-6 mb-3">
                                    <x-forms.input name="telefono_jur_number" label="Teléfono"
                                        id="cpr-telefono-jur-number-field" maxlength="7" placeholder="1234567" required
                                        prependRaw="true">
                                        <x-slot:prepend>
                                            <select class="form-select" id="cpr-telefono-jur-prefix-field"
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
                                </div>
                                <div class="col-md-6 mb-3">
                                    <x-forms.input name="email_jur" label="Email" type="email"
                                        placeholder="correo@empresa.com" id="cpr-email-jur-field" />
                                </div>
                            </div>
                        </div>

                        <div class="modal-form-section">
                            <div class="modal-form-section-title"><i class="ri-user-follow-line"></i>Contacto Secundario</div>
                            <div class="row mb-0">
                                <div class="col-md-6 mb-3">
                                    <x-forms.input name="contacto" label="Persona de Contacto" maxlength="100"
                                        placeholder="Nombre del contacto" id="cpr-contacto-field" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <x-forms.input name="telefono_contacto_number" label="Teléfono de Contacto"
                                        id="cpr-telefono-contacto-number-field" maxlength="7" placeholder="1234567"
                                        prependRaw="true">
                                        <x-slot:prepend>
                                            <select class="form-select" id="cpr-telefono-contacto-prefix-field"
                                                style="max-width: 100px; min-width: 100px;">
                                                <option value="0412">0412</option>
                                                <option value="0414">0414</option>
                                                <option value="0424" selected>0424</option>
                                                <option value="0416">0416</option>
                                                <option value="0426">0426</option>
                                            </select>
                                        </x-slot:prepend>
                                    </x-forms.input>
                                </div>
                            </div>
                        </div>

                        <div class="modal-form-section">
                            <div class="modal-form-section-title"><i class="ri-map-pin-2-line"></i>Ubicación</div>
                            <div class="row mb-0">
                                <div class="col-md-6 mb-3">
                                    <label for="cpr-estado-territorial-jur-field" class="form-label">Estado</label>
                                    <select id="cpr-estado-territorial-jur-field" class="form-select cpr-estado-select"
                                        data-ciudad-target="#cpr-ciudad-jur-field">
                                        <option value="">Seleccione estado</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cpr-ciudad-jur-field" class="form-label">Municipio</label>
                                    <select id="cpr-ciudad-jur-field" class="form-select">
                                        <option value="">Primero seleccione un estado</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── CAMPOS NATURAL ── --}}
                    <div id="cpr-campos-natural" style="display: none;">
                        <div class="modal-form-section">
                            <div class="modal-form-section-title"><i class="ri-user-3-line"></i>Datos Personales</div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <x-forms.input name="nombre" label="Nombre" maxlength="100" placeholder="Nombre"
                                        id="cpr-nombre-field" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <x-forms.input name="apellido" label="Apellido" maxlength="100"
                                        placeholder="Apellido" id="cpr-apellido-field" />
                                </div>
                            </div>
                            <div class="mb-0">
                                <x-forms.input name="direccion_nat" label="Dirección" maxlength="255"
                                    placeholder="Dirección completa" id="cpr-direccion-nat-field" />
                            </div>
                        </div>

                        <div class="modal-form-section">
                            <div class="modal-form-section-title"><i class="ri-contacts-book-line"></i>Contacto</div>
                            <div class="row mb-0">
                                <div class="col-md-6 mb-3">
                                    <x-forms.input name="telefono_nat_number" label="Teléfono"
                                        id="cpr-telefono-nat-number-field" maxlength="7" placeholder="1234567" required
                                        prependRaw="true">
                                        <x-slot:prepend>
                                            <select class="form-select" id="cpr-telefono-nat-prefix-field"
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
                                </div>
                                <div class="col-md-6 mb-3">
                                    <x-forms.input name="email_nat" label="Email" type="email"
                                        placeholder="correo@email.com" id="cpr-email-nat-field" />
                                </div>
                            </div>
                        </div>

                        <div class="modal-form-section mb-0">
                            <div class="modal-form-section-title"><i class="ri-map-pin-2-line"></i>Ubicación</div>
                            <div class="row mb-0">
                                <div class="col-md-6 mb-3">
                                    <label for="cpr-estado-territorial-field" class="form-label">Estado</label>
                                    <select id="cpr-estado-territorial-field" class="form-select cpr-estado-select"
                                        data-ciudad-target="#cpr-ciudad-field">
                                        <option value="">Seleccione estado</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cpr-ciudad-field" class="form-label">Municipio</label>
                                    <select id="cpr-ciudad-field" class="form-select">
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
                        <button type="submit" class="btn btn-success" id="cpr-submit-btn">
                            <i class="ri-save-line me-1"></i>Guardar y seleccionar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
