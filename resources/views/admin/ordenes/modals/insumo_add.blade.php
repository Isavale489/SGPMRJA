<!-- Modal nested — Agregar insumo a la orden (estilo atlántico navy) -->
<div class="modal fade atlantico-modal" id="insumoAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title mb-0"><span id="insumoAddModal-title">Agregar insumo</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-3">
                <input type="hidden" id="insumo-add-edit-idx" value="" />
                <div class="mb-2">
                    <label for="insumo-add-select" class="form-label form-label-sm mb-1 required">Insumo</label>
                    <select id="insumo-add-select" class="form-select form-select-sm">
                        <option value="">Seleccione insumo...</option>
                        @foreach($insumos as $insumo)
                            <option value="{{ $insumo->id }}"
                                data-nombre="{{ $insumo->nombre }}"
                                data-unidad="{{ $insumo->unidad_medida }}">
                                {{ $insumo->nombre }} ({{ $insumo->unidad_medida }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-0">
                    <label for="insumo-add-cantidad" class="form-label form-label-sm mb-1 required">Cantidad</label>
                    <input type="number" id="insumo-add-cantidad" class="form-control form-control-sm"
                        step="0.01" min="0.01" placeholder="0.00" />
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-2">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-sm btn-success" id="insumo-add-confirm">
                    <i class="ri-check-line me-1"></i><span id="insumo-add-confirm-label">Agregar</span>
                </button>
            </div>
        </div>
    </div>
</div>
