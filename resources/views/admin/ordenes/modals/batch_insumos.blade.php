<!-- Modal nested — Editar insumos de una línea del batch (estilo atlántico navy) -->
<div class="modal fade atlantico-modal" id="batchInsumosModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title mb-0">Insumos — <span id="batch-ins-prod">—</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-3">
                <input type="hidden" id="batch-ins-row-idx" value="" />

                {{-- Agregar / editar un insumo --}}
                <div class="row g-2 align-items-end mb-2">
                    <div class="col-7">
                        <label for="batch-ins-select" class="form-label form-label-sm mb-1">Insumo</label>
                        <select id="batch-ins-select" class="form-select form-select-sm">
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
                    <div class="col-3">
                        <label for="batch-ins-cant" class="form-label form-label-sm mb-1">Cantidad</label>
                        <input type="number" id="batch-ins-cant" class="form-control form-control-sm"
                            step="0.01" min="0.01" placeholder="0.00" />
                    </div>
                    <div class="col-2 d-grid">
                        <button type="button" class="btn btn-sm btn-soft-primary" id="batch-ins-add" title="Agregar">
                            <i class="ri-add-line"></i>
                        </button>
                    </div>
                </div>

                {{-- Lista --}}
                <div id="batch-ins-empty" class="text-center py-3 text-muted">
                    <i class="ri-tools-line d-block opacity-50 mb-1" style="font-size: 1.5rem;"></i>
                    <span class="fs-12">Aún no agregaste insumos a esta línea.</span>
                </div>
                <div class="cot-grouped-tablewrap" id="batch-ins-tablewrap" hidden>
                    <table class="cot-grouped-table">
                        <thead>
                            <tr>
                                <th class="cot-col-prod">Insumo</th>
                                <th class="cot-col-num text-end">Cantidad</th>
                                <th class="cot-col-acc text-center">—</th>
                            </tr>
                        </thead>
                        <tbody id="batch-ins-tbody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-2">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-sm btn-success" id="batch-ins-save">
                    <i class="ri-save-line me-1"></i>Guardar insumos
                </button>
            </div>
        </div>
    </div>
</div>
