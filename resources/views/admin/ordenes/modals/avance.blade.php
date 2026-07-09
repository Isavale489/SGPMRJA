<!-- Modal — Registrar Avance de Producción -->
<div class="modal fade atlantico-modal atlantico-modal--op" id="avanceModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0">Registrar Avance de Producción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="am-orden-id">
                <input type="hidden" id="am-restante">

                {{-- Contexto de la orden: producto + piezas por producir (fuera del header) --}}
                <div class="am-ctx">
                    <span class="am-ctx-prod"><i class="ri-t-shirt-2-line"></i><span id="am-ctx-nombre">—</span></span>
                    <span class="am-ctx-pill" id="am-ctx-restante"></span>
                </div>

                {{-- Solo lectura: no queda nada por registrar (todos completaron su parte) --}}
                <div class="alert alert-success d-none py-2 px-3 small mb-0 d-flex align-items-center gap-2" id="am-nada">
                    <i class="ri-checkbox-circle-line fs-5 lh-1"></i>
                    <span>Esta orden ya tiene toda su producción registrada. No hay nada más que registrar.</span>
                </div>

                {{-- Selector de empleado: solo visible cuando la orden tiene equipo (2+).
                     Con un solo empleado el avance se le atribuye automáticamente. --}}
                <div class="mb-3 d-none" id="am-empleado-wrap">
                    <label class="form-label fw-medium mb-1">
                        <i class="ri-team-line me-1"></i>¿Quién produjo este avance? <span class="text-danger">*</span>
                    </label>
                    <select id="am-empleado" class="form-select"></select>
                </div>

                <p class="text-muted fs-12 mb-3" id="am-empleado-solo">
                    <i class="ri-user-star-line me-1"></i>Registrado para el empleado asignado a esta orden.
                </p>

                {{-- Las unidades defectuosas se registran en Control de Calidad
                     (inspección post-producción), no en el avance. --}}
                <div class="row g-3 mb-0" id="am-cantidades">
                    <div class="col-12">
                        <label class="form-label fw-medium">
                            Cantidad Producida <span class="text-danger">*</span>
                            <span id="am-restante-hint" class="text-muted fw-normal" style="font-size:11px;"></span>
                        </label>
                        <input type="number" id="am-cantidad-producida" class="form-control" min="1" placeholder="0">
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
