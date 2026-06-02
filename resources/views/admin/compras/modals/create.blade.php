<div class="modal fade atlantico-modal atlantico-modal--op" id="createCompraModal" tabindex="-1" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title mb-0">
                    <i class="ri-shopping-bag-line me-1"></i>Nueva Compra
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="compraForm" novalidate>
                @csrf
                {{-- modal-body con scroll propio — evita conflicto con modal-footer --}}
                <div class="modal-body p-3" style="max-height: 72vh; overflow-y: auto;">

                    {{-- ── Proveedor y Factura ──────────────────────────── --}}
                    <div class="card border-0 shadow-sm mb-2">
                        <div class="card-header border-0 bg-soft-primary py-2 px-3">
                            <h6 class="mb-0 text-atlantico-dark fs-13">
                                <i class="ri-store-2-line me-1"></i>Proveedor y Datos de Factura
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label form-label-sm required mb-1" for="c-proveedor">Proveedor</label>
                                    <select id="c-proveedor" name="proveedor_id" class="form-select form-select-sm" required>
                                        <option value="">Seleccione un proveedor...</option>
                                        @foreach ($proveedores as $proveedor)
                                            <option value="{{ $proveedor->id }}">{{ $proveedor->nombre_completo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label form-label-sm mb-1" for="c-factura">N° de Factura</label>
                                    <input type="text" id="c-factura" name="numero_factura"
                                        class="form-control form-control-sm" maxlength="30"
                                        placeholder="Ej: 0001-000456">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm required mb-1" for="c-fecha">Fecha de Compra</label>
                                    <input type="date" id="c-fecha" name="fecha_compra"
                                        class="form-control form-control-sm" required
                                        value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm mb-1" for="c-tipo-pago">Tipo de Pago</label>
                                    <select id="c-tipo-pago" name="tipo_pago" class="form-select form-select-sm">
                                        <option value="contado">Contado</option>
                                        <option value="credito">Crédito</option>
                                    </select>
                                </div>
                                <div class="col-md-4" id="c-vencimiento-wrap" style="display:none;">
                                    <label class="form-label form-label-sm mb-1 required" id="c-vencimiento-label" for="c-vencimiento">Vencimiento</label>
                                    <input type="date" id="c-vencimiento" name="fecha_vencimiento"
                                        class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Ítems ────────────────────────────────────────── --}}
                    <div class="card border-0 shadow-sm mb-2">
                        <div class="card-header border-0 bg-soft-primary py-2 px-3 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 text-atlantico-dark fs-13">
                                <i class="ri-archive-line me-1"></i>Ítems de la Compra
                                <span class="text-muted fw-normal ms-1" id="c-items-count">(0)</span>
                            </h6>
                            <button type="button" class="btn btn-sm btn-soft-primary py-0 px-2" id="c-add-item-btn">
                                <i class="ri-add-line"></i> Agregar ítem
                            </button>
                        </div>
                        <div class="card-body p-0">
                            {{-- Empty state --}}
                            <div id="c-items-empty" class="text-center py-3 text-muted">
                                <i class="ri-archive-line d-block opacity-50 mb-1" style="font-size:1.6rem;"></i>
                                <p class="fs-12 mb-0">Aún no agregaste ítems. Haz click en <strong>"Agregar ítem"</strong>.</p>
                            </div>
                            {{-- Tabla dinámica --}}
                            <div id="c-items-table-wrap" class="cot-grouped-tablewrap" hidden>
                                <table class="cot-grouped-table">
                                    <thead>
                                        <tr>
                                            <th class="cot-col-num text-center" style="width:32px;">#</th>
                                            <th>Insumo</th>
                                            <th class="text-center" style="width:100px;">Cantidad</th>
                                            <th class="text-center" style="width:115px;">Costo Unit.</th>
                                            <th class="text-end" style="width:95px;">Subtotal</th>
                                            <th class="text-center" style="width:44px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="c-items-tbody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ── Observaciones + Resumen ──────────────────────── --}}
                    <div class="row g-2">
                        <div class="col-md-7">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header border-0 bg-soft-primary py-2 px-3">
                                    <h6 class="mb-0 text-atlantico-dark fs-13">
                                        <i class="ri-sticky-note-line me-1"></i>Observaciones
                                    </h6>
                                </div>
                                <div class="card-body p-3">
                                    <textarea id="c-observaciones" name="observaciones"
                                        class="form-control form-control-sm" rows="3" maxlength="500"
                                        placeholder="Notas o comentarios (opcional)..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header border-0 bg-soft-primary py-2 px-3">
                                    <h6 class="mb-0 text-atlantico-dark fs-13">
                                        <i class="ri-calculator-line me-1"></i>Resumen
                                    </h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="mb-2">
                                        <label class="form-label form-label-sm mb-1" for="c-iva">% IVA</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" id="c-iva" name="iva_porcentaje"
                                                class="form-control form-control-sm" value="16" min="0" max="100" step="0.01">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted fs-12">Subtotal</span>
                                        <span class="fw-semibold fs-12" id="c-resumen-subtotal">0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted fs-12">IVA (<span id="c-resumen-iva-pct">16</span>%)</span>
                                        <span class="fw-semibold fs-12" id="c-resumen-iva">0.00</span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold fs-13">Total</span>
                                        <span class="fw-bold fs-15" id="c-resumen-total">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>{{-- /modal-body --}}

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-sm btn-success" id="c-submit-btn">
                        <i class="ri-save-line me-1"></i>Registrar Compra
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
