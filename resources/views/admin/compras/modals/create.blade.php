{{-- ════════════════════════════════════════════════════════════════════
     Wizard "Nueva Compra" — 3 pasos (Proveedor · Ítems · Resumen)
     Reutiliza el scaffold .wiz-* compartido (ver docs/conventions/wizard-pattern.md)
     Prefijo de IDs: c-  ·  shell atlantico-modal--op (transaccional, emerald)
     ════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade atlantico-modal atlantico-modal--op wiz-modal" id="createCompraModal" tabindex="-1"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
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
                                        <label class="form-label small fw-semibold mb-1" for="c-proveedor">
                                            <i class="ri-building-line me-1 text-muted"></i>Proveedor <span class="text-danger">*</span>
                                        </label>
                                        <select id="c-proveedor" name="proveedor_id" class="form-select" required>
                                            <option value="">Seleccione un proveedor...</option>
                                            @foreach ($proveedores as $proveedor)
                                                <option value="{{ $proveedor->id }}"
                                                    data-doc="{{ $proveedor->documento ?? '' }}">
                                                    {{ $proveedor->nombre_completo }}
                                                </option>
                                            @endforeach
                                        </select>

                                        {{-- Empty state — sin proveedor seleccionado --}}
                                        <div class="cot-cliente-empty" id="c-prov-empty">
                                            <div class="cot-cliente-empty-icon"><i class="ri-store-2-line"></i></div>
                                            <p class="cot-cliente-empty-title">Sin proveedor seleccionado</p>
                                            <p class="cot-cliente-empty-desc">
                                                Elige un proveedor arriba para ver sus datos de contacto.
                                            </p>
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
