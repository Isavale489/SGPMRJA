{{-- Modal de detalle de compra — solo lectura
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

            <div class="modal-body p-4">

                {{-- Hero: número + estado + total --}}
                <div class="card border-0 shadow-sm mb-3">
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

                <div class="row g-3">
                    {{-- Columna principal --}}
                    <div class="col-lg-8">

                        {{-- Card: Información del proveedor + comprobante --}}
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
                                            <span id="cv-prov-doc">—</span>
                                        </p>
                                        <div class="cot-cliente-contact-row">
                                            <span class="cot-cliente-contact-item" id="cv-prov-tel-wrap">
                                                <i class="ri-phone-line"></i>
                                                <span id="cv-prov-tel">—</span>
                                            </span>
                                            <span class="cot-cliente-contact-item" id="cv-prov-email-wrap">
                                                <i class="ri-mail-line"></i>
                                                <span id="cv-prov-email">—</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Datos del comprobante --}}
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <small class="text-muted d-block mb-1"><i class="ri-receipt-line me-1"></i>N° de Factura</small>
                                        <span class="fw-semibold" id="cv-factura">—</span>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block mb-1"><i class="ri-calendar-event-line me-1"></i>Fecha de Compra</small>
                                        <span id="cv-fecha">—</span>
                                    </div>
                                    <div class="col-12 d-none" id="cv-obs-wrap">
                                        <small class="text-muted d-block mb-1"><i class="ri-sticky-note-line me-1"></i>Observaciones</small>
                                        <span class="fst-italic" id="cv-observaciones">—</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card: Ítems --}}
                        <div class="card border-0 shadow-sm">
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
                                                <th class="cot-col-num text-center" style="width:40px;">#</th>
                                                <th>Insumo</th>
                                                <th class="text-center" style="width:110px;">Tipo</th>
                                                <th class="text-center" style="width:90px;">Unidad</th>
                                                <th class="text-end" style="width:100px;">Cantidad</th>
                                                <th class="text-end" style="width:110px;">Costo Unit.</th>
                                                <th class="text-end" style="width:110px;">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cv-items-tbody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Columna lateral --}}
                    <div class="col-lg-4">

                        {{-- Registro --}}
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header border-0 bg-soft-primary">
                                <h6 class="mb-0 text-atlantico-dark">
                                    <i class="ri-user-line me-2"></i>Registro
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <img id="cv-reg-avatar" src="" alt=""
                                        class="rounded-circle" width="40" height="40" style="object-fit:cover;">
                                    <div>
                                        <small class="text-muted d-block">Registrado por</small>
                                        <span class="fw-semibold" id="cv-reg-nombre">—</span>
                                    </div>
                                </div>
                                <div>
                                    <small class="text-muted d-block mb-1"><i class="ri-time-line me-1"></i>Fecha de registro</small>
                                    <span id="cv-reg-fecha">—</span>
                                </div>
                            </div>
                        </div>

                        {{-- Totales --}}
                        <div class="card border-0 shadow-sm">
                            <div class="card-header border-0 bg-soft-primary">
                                <h6 class="mb-0 text-atlantico-dark">
                                    <i class="ri-calculator-line me-2"></i>Totales
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="c-ticket">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Subtotal</span>
                                        <span class="fw-semibold" id="cv-subtotal">0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">IVA</span>
                                        <span class="fw-semibold" id="cv-iva">0.00</span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold fs-15">Total</span>
                                        <span class="fw-bold fs-18 text-atlantico-emerald" id="cv-total-ticket">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light border-0">
                <a id="cv-pdf-btn" href="#" target="_blank" class="btn btn-success btn-sm">
                    <i class="ri-file-pdf-2-line me-1"></i>Descargar PDF
                </a>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cerrar
                </button>
            </div>

        </div>
    </div>
</div>
