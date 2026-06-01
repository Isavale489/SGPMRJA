<!-- Modal — Registrar Avance de Producción -->
<div class="modal fade atlantico-modal atlantico-modal--op" id="avanceModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h6 class="modal-title fw-semibold mb-0">Registrar Avance de Producción</h6>
                    <p class="text-white-50 mb-0 fs-12" id="am-orden-info"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="am-orden-id">
                <input type="hidden" id="am-restante">

                <p class="text-muted fs-12 mb-3">
                    <i class="ri-user-star-line me-1"></i>Registrado para el empleado asignado a esta orden.
                </p>

                <div class="row g-3 mb-0">
                    <div class="col-6">
                        <label class="form-label fw-medium">
                            Cantidad Producida <span class="text-danger">*</span>
                            <span id="am-restante-hint" class="text-muted fw-normal" style="font-size:11px;"></span>
                        </label>
                        <input type="number" id="am-cantidad-producida" class="form-control" min="1" placeholder="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-medium">Cantidad Defectuosa</label>
                        <input type="number" id="am-cantidad-defectuosa" class="form-control" min="0" value="0">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-success" id="am-btn-save">
                    <i class="ri-save-line me-1"></i>Guardar Avance
                </button>
            </div>
        </div>
    </div>
</div>
