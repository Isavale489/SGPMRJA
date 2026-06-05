{{-- ═══════════════════════════════════════════════════════════════════
     VIEW MODAL — Detalles de la Cotización (solo lectura, wizard 3 pasos)
     Pasos: Cliente → Productos → Resumen
     Lógica de navegación y render en: cotizaciones/scripts/main.blade.php
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade atlantico-modal atlantico-modal--op" id="viewModal" tabindex="-1" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-sm-down">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Detalles de la Cotización</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            {{-- Stepper visual — 3 pasos --}}
            <div class="wiz-stepper-wrapper">
                <div class="wiz-stepper-side wiz-stepper-side--left"></div>
                <div class="wiz-stepper" role="tablist" aria-label="Secciones del detalle">
                    <button type="button" class="wiz-step-marker is-active" data-step="1"
                        role="tab" aria-selected="true" aria-controls="view-wiz-step-1">
                        <span class="wiz-step-dot">1</span>
                        <span class="wiz-step-label">Cliente</span>
                    </button>
                    <span class="wiz-step-line"><span class="wiz-step-line-fill" data-line="1"></span></span>
                    <button type="button" class="wiz-step-marker" data-step="2"
                        role="tab" aria-selected="false" aria-controls="view-wiz-step-2">
                        <span class="wiz-step-dot">2</span>
                        <span class="wiz-step-label">Productos</span>
                    </button>
                    <span class="wiz-step-line"><span class="wiz-step-line-fill" data-line="2"></span></span>
                    <button type="button" class="wiz-step-marker" data-step="3"
                        role="tab" aria-selected="false" aria-controls="view-wiz-step-3">
                        <span class="wiz-step-dot">3</span>
                        <span class="wiz-step-label">Resumen</span>
                    </button>
                </div>
                <div class="wiz-stepper-side wiz-stepper-side--right"></div>
            </div>

            <div class="modal-body p-0 wiz-wizard-body">

                {{-- ════════════════════ PASO 1 — CLIENTE ════════════════════ --}}
                <section class="wiz-step-content is-active" id="view-wiz-step-1" data-step="1">
                    <div class="wiz-step-header">
                        <h4 class="wiz-step-title">Información del cliente</h4>
                        <p class="wiz-step-desc">Datos del cliente y de la cotización.</p>
                    </div>
                    <div class="row g-3">

                        {{-- Card: Cliente --}}
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header border-0 bg-soft-primary py-2 px-3">
                                    <h6 class="mb-0 text-atlantico-dark fs-13">
                                        <i class="ri-user-star-line me-1"></i>Información del Cliente
                                    </h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-2">
                                        <div class="col-6 d-flex align-items-start">
                                            <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                                <i class="ri-user-line emp-icon--navy"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Nombre</small>
                                                <span class="fw-semibold fs-13" id="view-cliente-nombre">-</span>
                                            </div>
                                        </div>
                                        <div class="col-6 d-flex align-items-start">
                                            <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                                <i class="ri-user-follow-line emp-icon--navy"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Apellido</small>
                                                <span class="fw-semibold fs-13" id="view-cliente-apellido">-</span>
                                            </div>
                                        </div>
                                        <div class="col-6 d-flex align-items-start">
                                            <div class="emp-icon-box emp-icon-box--green rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                                <i class="ri-bank-card-line emp-icon--green"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Documento</small>
                                                <span class="fw-semibold fs-13" id="view-ci-rif">-</span>
                                            </div>
                                        </div>
                                        <div class="col-6 d-flex align-items-start">
                                            <div class="emp-icon-box emp-icon-box--teal rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                                <i class="ri-phone-line emp-icon--teal"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Teléfono</small>
                                                <span class="fw-semibold fs-13" id="view-cliente-telefono">-</span>
                                            </div>
                                        </div>
                                        <div class="col-12 d-flex align-items-start">
                                            <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                                <i class="ri-mail-line emp-icon--navy"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Email</small>
                                                <span class="fw-semibold fs-13" id="view-cliente-email">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card: Datos de la Cotización --}}
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header border-0 bg-soft-primary py-2 px-3">
                                    <h6 class="mb-0 text-atlantico-dark fs-13">
                                        <i class="ri-calendar-todo-line me-1"></i>Datos de la Cotización
                                    </h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-2">
                                        <div class="col-6 d-flex align-items-start">
                                            <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                                <i class="ri-calendar-line emp-icon--navy"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Fecha Cotización</small>
                                                <span class="fw-semibold fs-13" id="view-fecha-cotizacion">-</span>
                                            </div>
                                        </div>
                                        <div class="col-6 d-flex align-items-start">
                                            <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                                <i class="ri-calendar-check-line emp-icon--navy"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Fecha Validez</small>
                                                <span class="fw-semibold fs-13" id="view-fecha-validez">-</span>
                                            </div>
                                        </div>
                                        <div class="col-6 d-flex align-items-start">
                                            <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                                <i class="ri-flag-line emp-icon--navy"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block fs-12">Estado</small>
                                                <span class="fs-13" id="view-estado">-</span>
                                            </div>
                                        </div>
                                        <div class="col-6 d-flex align-items-start">
                                            <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
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

                    </div>
                </section>

                {{-- ════════════════════ PASO 2 — PRODUCTOS ════════════════════ --}}
                <section class="wiz-step-content" id="view-wiz-step-2" data-step="2" hidden>
                    <div class="wiz-step-header">
                        <h4 class="wiz-step-title">Productos de la cotización</h4>
                        <p class="wiz-step-desc">Lista de productos, tallas, colores y bordados incluidos.</p>
                    </div>

                    {{-- KPI bar --}}
                    <div class="cot-kpi-grid mb-3">
                        <div class="cot-kpi">
                            <span class="cot-kpi-label">Líneas</span>
                            <span class="cot-kpi-value" id="view-kpi-lineas">0</span>
                        </div>
                        <div class="cot-kpi cot-kpi--total">
                            <span class="cot-kpi-label">Total</span>
                            <span class="cot-kpi-value" id="view-kpi-total">$0.00</span>
                        </div>
                    </div>

                    {{-- Estado vacío --}}
                    <div class="cot-empty-state" id="view-productos-empty" hidden>
                        <div class="cot-empty-icon"><i class="ri-shopping-bag-3-line"></i></div>
                        <h6 class="cot-empty-title">Sin productos</h6>
                        <p class="cot-empty-desc">Esta cotización no tiene productos registrados.</p>
                    </div>

                    {{-- Grilla de productos (solo lectura) --}}
                    <div id="view-productos-container"></div>
                </section>

                {{-- ════════════════════ PASO 3 — RESUMEN ════════════════════ --}}
                <section class="wiz-step-content" id="view-wiz-step-3" data-step="3" hidden>
                    <div class="wiz-step-header">
                        <h4 class="wiz-step-title">Resumen final</h4>
                        <p class="wiz-step-desc">Totales, equivalencia en Bs y términos de la cotización.</p>
                    </div>
                    <div class="row g-3">

                        {{-- Card: Totales --}}
                        <div class="col-lg-5">
                            <div class="cot-resumen-card h-100">
                                <div class="cot-resumen-card-header">
                                    <i class="ri-money-dollar-circle-line"></i>
                                    <span>Resumen Final</span>
                                </div>
                                <div class="cot-resumen-card-body">
                                    <div class="cot-resumen-row">
                                        <span class="cot-resumen-row-label">
                                            <i class="ri-bank-line me-1" style="font-size:.9rem;opacity:.7"></i>Tasa BCV (USD/VES)
                                        </span>
                                        <span class="cot-resumen-row-value fs-13" id="view-resumen-tasa">—</span>
                                    </div>
                                    <div class="cot-resumen-divider"></div>
                                    <div class="cot-resumen-row">
                                        <span class="cot-resumen-row-label">Subtotal</span>
                                        <span class="cot-resumen-row-value" id="view-resumen-subtotal">$0.00</span>
                                    </div>
                                    <div class="cot-resumen-row">
                                        <span class="cot-resumen-row-label">IVA (16%)</span>
                                        <span class="cot-resumen-row-value" id="view-resumen-iva">$0.00</span>
                                    </div>
                                    <div class="cot-resumen-divider"></div>
                                    <div class="cot-resumen-row cot-resumen-row--total">
                                        <span class="cot-resumen-row-label">TOTAL</span>
                                        <span class="cot-resumen-row-value" id="view-total">$0.00</span>
                                    </div>
                                    <div class="cot-resumen-row">
                                        <span class="cot-resumen-row-label">
                                            <i class="ri-exchange-dollar-line me-1" style="font-size:.9rem;opacity:.7"></i>Equivalente Bs
                                        </span>
                                        <span class="cot-resumen-row-value fs-13" id="view-total-bs">—</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card: Términos y condiciones --}}
                        <div class="col-lg-7">
                            @include('admin.partials.terminos-accordion', ['prefix' => 'view-cot'])
                        </div>

                    </div>
                </section>

            </div>{{-- /modal-body --}}

            <div class="modal-footer wiz-wizard-footer">
                <div class="wiz-wizard-footer-info">
                    <a href="#" id="view-pdf-btn" class="btn btn-sm btn-warning" target="_blank">
                        <i class="ri-file-pdf-line me-1"></i>PDF
                    </a>
                </div>
                <div class="wiz-wizard-footer-actions">
                    <button type="button" class="btn btn-light wiz-wizard-btn-prev" id="btn-view-prev" style="display:none;">
                        <i class="ri-arrow-left-line me-1"></i>Anterior
                    </button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cerrar
                    </button>
                    <button type="button" class="btn btn-atlantico-brand wiz-wizard-btn-next" id="btn-view-next">
                        Continuar<i class="ri-arrow-right-line ms-1"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal para agregar/editar Cotización -->