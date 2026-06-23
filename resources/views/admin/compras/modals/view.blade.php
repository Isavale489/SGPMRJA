{{-- Modal de detalle de compra — wizard de solo lectura
     Clase: atlantico-modal--op (transaccional)
     Prefijo de IDs: cv- (compra-view)
--}}
<div class="modal fade atlantico-modal atlantico-modal--op" id="viewCompraModal" tabindex="-1"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="cv-titulo">
                    <i class="ri-shopping-bag-3-line me-1"></i>Detalle de Compra
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Stepper visual — 3 pasos (jerárquicamente, los pasos van arriba de todo) --}}
            <div class="wiz-stepper-wrapper">
                <div class="wiz-stepper-side wiz-stepper-side--left"></div>
                <div class="wiz-stepper" role="tablist">
                    <button type="button" class="wiz-step-marker is-active" data-step="1" role="tab">
                        <span class="wiz-step-dot">1</span>
                        <span class="wiz-step-label">Proveedor</span>
                    </button>
                    <span class="wiz-step-line"><span class="wiz-step-line-fill" data-line="1"></span></span>
                    <button type="button" class="wiz-step-marker" data-step="2" role="tab">
                        <span class="wiz-step-dot">2</span>
                        <span class="wiz-step-label">Ítems</span>
                    </button>
                    <span class="wiz-step-line"><span class="wiz-step-line-fill" data-line="2"></span></span>
                    <button type="button" class="wiz-step-marker" data-step="3" role="tab">
                        <span class="wiz-step-dot">3</span>
                        <span class="wiz-step-label">Totales</span>
                    </button>
                </div>
                <div class="wiz-stepper-side wiz-stepper-side--right"></div>
            </div>

            {{-- Hero persistente: número + estado + total (visible en todos los pasos) --}}
            <div class="px-3 pt-3">
                <div class="card border-0 shadow-sm mb-0">
                    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3 p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="c-show-hero-icon"><i class="ri-shopping-bag-3-line"></i></div>
                            <div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <h4 class="mb-0" id="cv-hero-titulo">Compra #—</h4>
                                    <span class="badge" id="cv-estado-badge">—</span>
                                </div>
                                <p class="text-muted mb-0 mt-1" id="cv-hero-meta">
                                    <i class="ri-store-2-line me-1"></i><span id="cv-hero-proveedor">—</span>
                                    <span class="mx-2 text-muted opacity-50">·</span>
                                    <i class="ri-calendar-line me-1"></i><span id="cv-hero-fecha">—</span>
                                </p>
                            </div>
                        </div>
                        <div class="text-lg-end">
                            <small class="text-muted d-block mb-1">Total de la compra</small>
                            <span class="c-show-hero-total text-atlantico-emerald" id="cv-total">0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-body wiz-wizard-body p-3">

                {{-- ─ Paso 1: Proveedor y registro ─────────────────────── --}}
                <section class="wiz-step-content is-active" data-step="1">

                    {{-- Información de la compra (proveedor + comprobante) --}}
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header border-0 bg-soft-primary">
                            <h6 class="mb-0 text-atlantico-dark">
                                <i class="ri-file-list-3-line me-2"></i>Información de la Compra
                            </h6>
                        </div>
                        <div class="card-body">
                            {{-- Card proveedor --}}
                            <div class="cot-cliente-card mb-3">
                                <div class="cot-cliente-avatar" id="cv-prov-ini">—</div>
                                <div class="cot-cliente-info flex-grow-1">
                                    <div class="cot-cliente-name-row">
                                        <h5 class="cot-cliente-name" id="cv-prov-nombre">—</h5>
                                        <span class="cot-cliente-roles">
                                            <span class="cot-role-pill cot-role-proveedor" id="cv-prov-tipo">Proveedor</span>
                                        </span>
                                    </div>
                                    <p class="cot-cliente-doc">
                                        <i class="ri-bank-card-line"></i>
                                        <span class="cli-copyable" id="cv-prov-doc">—</span>
                                    </p>
                                    <div class="cot-cliente-contact-row">
                                        <span class="cot-cliente-contact-item" id="cv-prov-tel-wrap">
                                            <i class="ri-phone-line"></i>
                                            <span class="cli-copyable" id="cv-prov-tel">—</span>
                                        </span>
                                        <span class="cot-cliente-contact-item" id="cv-prov-email-wrap">
                                            <i class="ri-mail-line"></i>
                                            <span class="cli-copyable" id="cv-prov-email">—</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Datos del comprobante --}}
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <small class="text-muted d-block mb-1"><i class="ri-receipt-line me-1"></i>N° de Factura</small>
                                    <span class="fw-semibold cli-copyable" id="cv-factura">—</span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block mb-1"><i class="ri-calendar-event-line me-1"></i>Fecha de Compra</small>
                                    <span id="cv-fecha">—</span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block mb-1"><i class="ri-exchange-dollar-line me-1"></i>Tasa de cambio</small>
                                    <span id="cv-tasa">—</span>
                                </div>
                                <div class="col-12 d-none" id="cv-obs-wrap">
                                    <small class="text-muted d-block mb-1"><i class="ri-sticky-note-line me-1"></i>Observaciones</small>
                                    <span class="fst-italic" id="cv-observaciones">—</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Registro --}}
                    <div class="card border-0 shadow-sm mb-0">
                        <div class="card-header border-0 bg-soft-primary">
                            <h6 class="mb-0 text-atlantico-dark">
                                <i class="ri-user-line me-2"></i>Registro
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2">
                                        <img id="cv-reg-avatar" src="" alt=""
                                            class="rounded-circle" width="40" height="40" style="object-fit:cover;">
                                        <div>
                                            <small class="text-muted d-block">Registrado por</small>
                                            <span class="fw-semibold" id="cv-reg-nombre">—</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block mb-1"><i class="ri-time-line me-1"></i>Fecha de registro</small>
                                    <span id="cv-reg-fecha">—</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </section>

                {{-- ─ Paso 2: Ítems ─────────────────────────────────────── --}}
                <section class="wiz-step-content" data-step="2" hidden>
                    <div class="card border-0 shadow-sm mb-0">
                        <div class="card-header border-0 bg-soft-primary">
                            <h6 class="mb-0 text-atlantico-dark">
                                <i class="ri-archive-line me-2"></i>Ítems de la Compra
                                <span class="text-muted fw-normal ms-1" id="cv-items-count">(0)</span>
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="cot-grouped-tablewrap">
                                <table class="cot-grouped-table">
                                    <thead>
                                        <tr>
                                            <th class="cot-col-num text-center" style="width:34px;">#</th>
                                            <th style="min-width:160px;">Insumo</th>
                                            <th class="text-center" style="width:85px;">Tipo</th>
                                            <th class="text-center" style="width:70px;">Unidad</th>
                                            <th class="text-end" style="width:70px;">Cant.</th>
                                            <th class="text-end" style="width:100px;">Costo Unit.<br><small class="text-muted fw-normal">Bs</small></th>
                                            <th class="text-end" style="width:95px;"><small class="text-muted fw-normal">≈ USD</small></th>
                                            <th class="text-center" style="width:60px;">IVA</th>
                                            <th class="text-end" style="width:110px;">Subtotal<br><small class="text-muted fw-normal">Bs</small></th>
                                            <th class="text-end" style="width:95px;"><small class="text-muted fw-normal">≈ USD</small></th>
                                        </tr>
                                    </thead>
                                    <tbody id="cv-items-tbody"></tbody>
                                    <tfoot>
                                        <tr class="cot-grouped-foot">
                                            <td colspan="8" class="text-end fw-semibold text-muted">Subtotal</td>
                                            <td class="text-end fw-bold">Bs <span id="cv-items-foot-bs">0,00</span></td>
                                            <td class="text-end text-muted">$ <span id="cv-items-foot-usd">0,00</span></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ─ Paso 3: Totales ───────────────────────────────────── --}}
                <section class="wiz-step-content" data-step="3" hidden>
                    <div class="row justify-content-center">
                        <div class="col-md-7 col-lg-6">
                            <div class="card border-0 shadow-sm mb-0">
                                <div class="card-header border-0 bg-soft-primary">
                                    <h6 class="mb-0 text-atlantico-dark">
                                        <i class="ri-calculator-line me-2"></i>Totales
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="c-ticket">
                                        <div class="c-ticket-row">
                                            <span class="c-ticket-label">Subtotal</span>
                                            <span class="c-ticket-val">Bs <span id="cv-subtotal">0,00</span></span>
                                        </div>
                                        <div class="c-ticket-row d-none" id="cv-exento-wrap">
                                            <span class="c-ticket-label">Base exenta</span>
                                            <span class="c-ticket-val text-muted">Bs <span id="cv-exento">0,00</span></span>
                                        </div>
                                        <div class="c-ticket-row">
                                            <span class="c-ticket-label">IVA (<span id="cv-iva-pct">—</span>%)</span>
                                            <span class="c-ticket-val">Bs <span id="cv-iva">0,00</span></span>
                                        </div>

                                        <div class="c-ticket-total">
                                            <span class="c-ticket-total-label">Total a pagar</span>
                                            <span class="c-ticket-total-val">Bs <span id="cv-total-ticket">0,00</span></span>
                                        </div>

                                        <div class="c-ticket-conv">
                                            <div class="c-ticket-conv-row">
                                                <span><i class="ri-exchange-dollar-line me-1"></i>Tasa aplicada</span>
                                                <span>Bs <span id="cv-tasa-ticket">0,0000</span> / USD</span>
                                            </div>
                                            <div class="c-ticket-conv-row">
                                                <span>Equivalente en USD</span>
                                                <span class="c-conv-usd">$ <span id="cv-total-usd">0,00</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

            </div>{{-- /modal-body --}}

            <div class="modal-footer wiz-wizard-footer py-2 px-3">
                <div class="wiz-wizard-footer-info">
                    <a id="cv-pdf-btn" href="#" target="_blank" class="btn btn-success btn-sm">
                        <i class="ri-file-pdf-2-line me-1"></i>Descargar PDF
                    </a>
                </div>
                <div class="wiz-wizard-footer-actions">
                    <button type="button" class="btn btn-sm btn-light border" id="cv-prev" style="display:none;">
                        <i class="ri-arrow-left-line me-1"></i>Anterior
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" id="cv-next">
                        Siguiente<i class="ri-arrow-right-line ms-1"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal" id="cv-close" style="display:none;">
                        <i class="ri-close-line me-1"></i>Cerrar
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
