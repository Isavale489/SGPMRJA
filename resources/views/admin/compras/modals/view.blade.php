{{-- Modal de detalle de compra — documento comercial de solo lectura.
     Anatomía de factura sobre una sola superficie:
       membrete (proveedor + metadatos con dot leaders)
       → líneas (tabla de ítems a sangre completa)
       → cierre (firma "Registrado por" + observaciones | ticket de totales)
     Clase: atlantico-modal--op (transaccional) · Prefijo de IDs: cv-
--}}
<div class="modal fade atlantico-modal atlantico-modal--op" id="viewCompraModal" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="cv-titulo">
                    <i class="ri-shopping-bag-3-line me-1"></i>Detalle de Compra
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3">
                <div class="cv-doc">

                    {{-- ── Membrete: proveedor + metadatos del comprobante ── --}}
                    <div class="cv-doc-head">
                        <div class="cv-letterhead">
                            <div class="cot-cliente-avatar flex-shrink-0" id="cv-prov-ini">—</div>
                            <div class="min-w-0">
                                <span class="cv-letterhead-eyebrow"><i class="ri-store-2-line me-1"></i>Proveedor</span>
                                <div class="cv-letterhead-name-row">
                                    <h5 id="cv-prov-nombre">—</h5>
                                    <span class="cot-role-pill cot-role-proveedor" id="cv-prov-tipo">—</span>
                                </div>
                                <p class="cv-letterhead-doc">
                                    <i class="ri-bank-card-line"></i>
                                    <span class="cli-copyable" id="cv-prov-doc">—</span>
                                </p>
                                <div class="cv-letterhead-contact">
                                    <span id="cv-prov-tel-wrap">
                                        <i class="ri-phone-line"></i>
                                        <span class="cli-copyable" id="cv-prov-tel">—</span>
                                    </span>
                                    <span id="cv-prov-email-wrap">
                                        <i class="ri-mail-line"></i>
                                        <span class="cli-copyable" id="cv-prov-email">—</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="cv-meta">
                            <div class="cv-meta-row">
                                <span class="cv-meta-label">Estado</span>
                                <span class="cv-meta-fill"></span>
                                <span class="badge" id="cv-estado-badge">—</span>
                            </div>
                            <div class="cv-meta-row">
                                <span class="cv-meta-label">N° de factura</span>
                                <span class="cv-meta-fill"></span>
                                <span class="cv-meta-value cli-copyable" id="cv-factura">—</span>
                            </div>
                            <div class="cv-meta-row">
                                <span class="cv-meta-label">Fecha de compra</span>
                                <span class="cv-meta-fill"></span>
                                <span class="cv-meta-value" id="cv-fecha">—</span>
                            </div>
                            <div class="cv-meta-row">
                                <span class="cv-meta-label" id="cv-tasa-label">Tasa BCV</span>
                                <span class="cv-meta-fill"></span>
                                <span class="cv-meta-value" id="cv-tasa">—</span>
                            </div>
                            <div class="cv-meta-row">
                                <span class="cv-meta-label">Registrado por</span>
                                <span class="cv-meta-fill"></span>
                                <span class="cv-meta-value">
                                    <span id="cv-reg-nombre">—</span>
                                    <span class="cv-meta-sub" id="cv-reg-fecha">—</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- ── Cinta de sección: líneas del documento ── --}}
                    <div class="cv-doc-strip">
                        <i class="ri-archive-line"></i>Ítems de la compra
                        <span class="fw-normal" id="cv-items-count">(0)</span>
                    </div>

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
                        </table>
                    </div>

                    {{-- ── Cierre del documento: banda de totales a todo lo ancho ── --}}
                    <div class="cv-totals-bar">
                        <div class="cv-total-cell">
                            <span class="cv-total-label">Subtotal</span>
                            <span class="cv-total-value">Bs <span id="cv-subtotal">0,00</span></span>
                        </div>
                        <div class="cv-total-cell d-none" id="cv-exento-wrap">
                            <span class="cv-total-label">Base exenta</span>
                            <span class="cv-total-value">Bs <span id="cv-exento">0,00</span></span>
                        </div>
                        <div class="cv-total-cell">
                            <span class="cv-total-label">IVA (<span id="cv-iva-pct">—</span>%)</span>
                            <span class="cv-total-value">Bs <span id="cv-iva">0,00</span></span>
                        </div>
                        <div class="cv-total-cell cv-total-cell--pay">
                            <span class="cv-total-label">Total a pagar</span>
                            <span class="cv-total-value">Bs <span id="cv-total-ticket">0,00</span></span>
                            <span class="cv-total-conv">
                                ≈ $ <span id="cv-total-usd">0,00</span>
                                <span class="cv-total-tasa"><i class="ri-exchange-dollar-line"></i>Bs <span id="cv-tasa-ticket">0,0000</span> / USD<span id="cv-tasa-ticket-fecha"></span></span>
                            </span>
                        </div>
                    </div>

                    {{-- Observaciones del documento (solo si existen) --}}
                    <div class="cv-obs-note cv-obs-note--doc d-none" id="cv-obs-wrap">
                        <i class="ri-sticky-note-line"></i>
                        <div>
                            <span class="cv-obs-eyebrow">Observaciones</span>
                            <span class="fst-italic" id="cv-observaciones">—</span>
                        </div>
                    </div>

                </div>{{-- /cv-doc --}}
            </div>{{-- /modal-body --}}

            <div class="modal-footer wiz-wizard-footer">
                <div class="wiz-wizard-footer-info">
                    <a id="cv-pdf-btn" href="#" target="_blank" class="btn btn-soft-danger">
                        <i class="ri-file-pdf-fill align-bottom me-1"></i> Exportar PDF
                    </a>
                </div>
                <div class="wiz-wizard-footer-actions">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cerrar
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
