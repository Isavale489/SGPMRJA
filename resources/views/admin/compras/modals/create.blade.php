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
                        <img class="wiz-client-banner-avatar wiz-client-banner-avatar--img"
                            src="{{ Auth::user()->avatar_url }}" alt="" onerror="this.onerror=null;this.src=window.AMS_AVATAR_FALLBACK" />
                        <div class="wiz-client-banner-main">
                            <span class="wiz-client-banner-eyebrow">Registrada por</span>
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
                                                <label class="form-label small fw-semibold mb-1" for="c-factura">N° de Factura <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="ri-receipt-line"></i></span>
                                                    <input type="text" id="c-factura" name="numero_factura"
                                                        class="form-control" maxlength="10" inputmode="numeric"
                                                        placeholder="Ej: 0001-0456">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-semibold mb-1" for="c-fecha">Fecha de Compra <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="ri-calendar-event-line"></i></span>
                                                    <input type="date" id="c-fecha" name="fecha_compra"
                                                        class="form-control" required value="{{ date('Y-m-d') }}"
                                                        max="{{ date('Y-m-d') }}">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-semibold mb-1" for="c-tasa">
                                                    Tasa de cambio (Bs por USD) <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="ri-exchange-dollar-line"></i></span>
                                                    <input type="number" id="c-tasa" name="tasa_cambio"
                                                        class="form-control bg-light" min="0.0001" step="0.0001"
                                                        placeholder="0.0000" readonly>
                                                    <span class="input-group-text">Bs/USD</span>
                                                </div>
                                                <small class="text-muted d-block mt-1" id="c-tasa-hint">
                                                    <i class="ri-information-line me-1"></i>Se autocompleta con la tasa BCV de la fecha de compra.
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
                                                <th class="text-center" style="width:130px;">Cantidad</th>
                                                <th class="text-center" style="width:130px;">Costo Unit. (Bs)</th>
                                                <th class="text-center" style="width:140px;">Total (Bs)</th>
                                                <th class="text-center" style="width:60px;" title="Marcá si la línea es gravable con IVA">IVA</th>
                                                <th class="text-center" style="width:48px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="c-items-tbody"></tbody>
                                    </table>
                                </div>
                            </div>
                            {{-- Pie: subtotal en vivo --}}
                            <div class="card-footer border-0 bg-transparent align-items-center justify-content-between py-2 px-3 d-none"
                                id="c-items-footer">
                                <span class="text-muted small">
                                    <i class="ri-stack-line me-1"></i><span id="c-items-footer-count">0</span> ítem(s)
                                </span>
                                <span class="small">
                                    Subtotal sin IVA:
                                    <strong class="text-atlantico-emerald ms-1">Bs <span id="c-items-footer-subtotal">0.00</span></strong>
                                    <span class="text-muted ms-1">(≈ $<span id="c-items-footer-subtotal-usd">0.00</span>)</span>
                                </span>
                            </div>
                        </div>
                    </section>

                    {{-- ═══════════════════════ PASO 3 — RESUMEN ══════════════════════════ --}}
                    <section class="wiz-step-content" id="c-step-3" data-step="3" hidden>
                        <div class="wiz-step-header">
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
                                            <div class="col-12">
                                                <small class="text-muted d-block mb-2">
                                                    <i class="ri-archive-line me-1"></i>Ítems
                                                    <span class="ms-1" id="c-recap-items">0 insumo(s)</span>
                                                </small>
                                                {{-- Detalle de los insumos cargados (espejo de la grilla del paso 2) --}}
                                                <div class="cot-grouped-tablewrap">
                                                    <table class="cot-grouped-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Insumo</th>
                                                                <th class="text-end" style="width:64px;">Cant.</th>
                                                                <th class="text-end" style="width:120px;">Costo Unit.</th>
                                                                <th class="text-center" style="width:56px;">IVA</th>
                                                                <th class="text-end" style="width:128px;">Subtotal</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="c-recap-items-tbody"></tbody>
                                                    </table>
                                                </div>
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
                                        <div class="c-ticket">
                                            <div class="c-ticket-row">
                                                <span class="c-ticket-label">Subtotal</span>
                                                <span class="c-ticket-val">Bs <span id="c-resumen-subtotal">0,00</span></span>
                                            </div>
                                            <div class="c-ticket-row d-none" id="c-resumen-exento-wrap">
                                                <span class="c-ticket-label">Base exenta</span>
                                                <span class="c-ticket-val text-muted">Bs <span id="c-resumen-exento">0,00</span></span>
                                            </div>
                                            <div class="c-ticket-row">
                                                <span class="c-ticket-label">IVA (<span id="c-resumen-iva-pct">16</span>%)</span>
                                                <span class="c-ticket-val">Bs <span id="c-resumen-iva">0,00</span></span>
                                            </div>

                                            <div class="c-ticket-total">
                                                <span class="c-ticket-total-label">Total a pagar</span>
                                                <span class="c-ticket-total-val">Bs <span id="c-resumen-total">0,00</span></span>
                                            </div>

                                            <div class="c-ticket-conv">
                                                <div class="c-ticket-conv-row">
                                                    <span><i class="ri-exchange-dollar-line me-1"></i>Tasa aplicada<span id="c-resumen-tasa-fecha"></span></span>
                                                    <span>Bs <span id="c-resumen-tasa">0,0000</span> / USD</span>
                                                </div>
                                                <div class="c-ticket-conv-row">
                                                    <span>Equivalente en USD</span>
                                                    <span class="c-conv-usd">$ <span id="c-resumen-total-usd">0,00</span></span>
                                                </div>
                                            </div>

                                            <p class="c-ticket-note">
                                                <i class="ri-information-line me-1"></i>Los costos se cargan en bolívares; el equivalente en USD usa la tasa del paso 1. El IVA ({{ rtrim(rtrim(number_format(\App\Models\Impuesto::tasaIva(), 2), '0'), '.') }}%) aplica solo a las líneas gravables.
                                            </p>
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
@include('admin.compras.modals.buscar-insumo')

{{-- ═══════════════════════════════════════════════════════════════════════════
     MINI-MODAL: Crear insumo nuevo (inline, "extensión" del maestro Insumos)
     Alta rápida vía AJAX → se auto-selecciona en la fila que disparó el "+".
     Prefijo de IDs: cir-  ·  navy (atlantico-modal) porque pertenece al maestro.
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade atlantico-modal" id="crearInsumoRapidoModal" tabindex="-1" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title"><i class="ri-archive-line me-2"></i>Agregar Insumo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cirForm" novalidate>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        <i class="ri-information-line me-1"></i>Alta rápida: el insumo se creará como inventariable
                        (stock inicial 0) y quedará seleccionado en la fila de la compra.
                    </p>

                    <div class="modal-form-section">
                        <div class="modal-form-section-title"><i class="ri-box-3-line"></i>Datos del Insumo</div>
                        <div class="row mb-0">
                            <div class="col-md-7 mb-3">
                                <label for="cir-nombre-field" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" id="cir-nombre-field" class="form-control" maxlength="100"
                                    placeholder="Ej: Tela Oxford" autocomplete="off">
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="cir-codigo-field" class="form-label">Código <span class="text-muted">(opcional)</span></label>
                                <input type="text" id="cir-codigo-field" class="form-control text-uppercase"
                                    maxlength="8" placeholder="Ej: OXF" style="font-family: monospace;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cir-tipo-field" class="form-label">Tipo <span class="text-danger">*</span></label>
                                <select id="cir-tipo-field" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @foreach ($tiposInsumo as $ti)
                                        <option value="{{ $ti->nombre }}">{{ $ti->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cir-unidad-field" class="form-label">Unidad de Medida <span class="text-danger">*</span></label>
                                <select id="cir-unidad-field" class="form-select">
                                    <option value="">Seleccione...</option>
                                    <option value="Metro">Metro (m)</option>
                                    <option value="Kg">Kilogramo (Kg)</option>
                                    <option value="Gramo">Gramo (g)</option>
                                    <option value="Unidad">Unidad (Und)</option>
                                    <option value="Rollo">Rollo</option>
                                    <option value="Cono">Cono</option>
                                    <option value="Docena">Docena</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-0">
                                <label for="cir-costo-field" class="form-label">Costo Unitario <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$/</span>
                                    <input type="number" id="cir-costo-field" class="form-control"
                                        step="0.01" min="0.01" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-6 mb-0 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="cir-aplica-iva-field" checked>
                                    <label class="form-check-label" for="cir-aplica-iva-field">
                                        Gravable con IVA
                                        <i class="ri-information-line text-muted" title="Desmarca si el insumo es exento de IVA"></i>
                                    </label>
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
                        <button type="submit" class="btn btn-success" id="cir-submit-btn">
                            <i class="ri-save-line me-1"></i>Guardar y seleccionar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

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
                            {{-- Documento unificado (igual al maestro de Proveedores): el prefijo
                                 V/E/J/G determina el tipo. V/E → Natural, J/G → Jurídico. --}}
                            <div class="col-md-6">
                                <x-forms.input name="documento_number" label="Documento (Cédula o RIF)"
                                    id="cpr-doc-number-field" maxlength="9" placeholder="Nro. de documento" required
                                    prependRaw="true">
                                    <x-slot:prepend>
                                        <select class="form-select" id="cpr-doc-prefix-field" style="max-width: 80px;">
                                            <option value="V-">V-</option>
                                            <option value="E-">E-</option>
                                            <option value="J-">J-</option>
                                            <option value="G-">G-</option>
                                        </select>
                                    </x-slot:prepend>
                                </x-forms.input>
                            </div>
                            <div class="col-md-6">
                                <x-forms.select name="tipo_proveedor" label="Tipo de Proveedor" required
                                    id="cpr-tipo-proveedor-field"
                                    :options="['juridico' => 'Jurídico (Empresa)', 'natural' => 'Natural (Persona)']"
                                    placeholder="" class="js-readonly" disabled
                                    title="Se determina por el prefijo del documento"
                                    hint="Se define por el prefijo del documento (V/E → Natural, J/G → Jurídico)." />
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
                                <div class="col-12 mb-3">
                                    <x-forms.input name="email_jur" label="Email" type="email"
                                        placeholder="correo@empresa.com" id="cpr-email-jur-field" />
                                </div>
                                <div class="col-12">
                                    {{-- Teléfonos múltiples de la empresa (componente reutilizable) --}}
                                    @include('admin.partials.telefonos-field', ['telId' => 'cpr-jur-tel'])
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
                                <div class="col-12 mb-3">
                                    <x-forms.input name="email_nat" label="Email" type="email"
                                        placeholder="correo@email.com" id="cpr-email-nat-field" />
                                </div>
                                <div class="col-12">
                                    {{-- Teléfonos múltiples (componente reutilizable) --}}
                                    @include('admin.partials.telefonos-field', ['telId' => 'cpr-nat-tel'])
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
