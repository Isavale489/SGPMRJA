<!-- Modal — Crear órdenes en lote desde un pedido -->
<div class="modal fade atlantico-modal atlantico-modal--op" id="batchOrdenModal" tabindex="-1" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title mb-0">
                    Crear órdenes — <span id="batch-pedido-label">Pedido #—</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-3">

                {{-- Defaults compartidos --}}
                <div class="card border-0 shadow-sm mb-2 bg-soft-primary">
                    <div class="card-body p-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label for="batch-default-empleado" class="form-label form-label-sm mb-1">
                                    Empleado <span class="text-muted fw-normal">(default)</span>
                                </label>
                                <select id="batch-default-empleado" class="form-select form-select-sm">
                                    <option value="">— elegir —</option>
                                    @foreach($empleados as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="batch-default-inicio" class="form-label form-label-sm mb-1">Inicio (default)</label>
                                <input type="date" id="batch-default-inicio" class="form-control form-control-sm" />
                            </div>
                            <div class="col-md-3">
                                <label for="batch-default-fin" class="form-label form-label-sm mb-1">Fin estimado (default)</label>
                                <input type="date" id="batch-default-fin" class="form-control form-control-sm" />
                            </div>
                            <div class="col-md-2 d-grid">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="batch-apply-defaults"
                                    title="Copia los defaults a todas las filas">
                                    <i class="ri-magic-line me-1"></i>Aplicar a todas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabla de órdenes (1 fila por línea seleccionada) --}}
                <div class="cot-grouped-tablewrap">
                    <table class="cot-grouped-table">
                        <thead>
                            <tr>
                                <th class="cot-col-num">#</th>
                                <th class="cot-col-prod">Producto</th>
                                <th class="cot-col-num text-center">Cant.</th>
                                <th>Empleado</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th class="text-center" title="Insumos prellenados">Ins.</th>
                            </tr>
                        </thead>
                        <tbody id="batch-ordenes-tbody"></tbody>
                    </table>
                </div>
                <p class="text-muted fs-12 mt-2 mb-0">
                    <i class="ri-information-line me-1"></i>Los insumos se prellenan desde los templates del tipo de producto (Feature D).
                    Para personalizarlos en una orden, créala individualmente.
                </p>
            </div>
            <div class="modal-footer bg-light border-0 py-2">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-sm btn-success" id="batch-submit-btn">
                    <i class="ri-check-double-line me-1"></i>Crear <span id="batch-submit-count">0</span> órdenes
                </button>
            </div>
        </div>
    </div>
</div>
