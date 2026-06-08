{{-- Modal dinámico — Sub-órdenes de Producción de la OP principal
     Lista las sub-órdenes (etapas) y los empleados asignados a cada una,
     y permite crear nuevas asignando varios empleados.
     Prefijo de IDs: so- (sub-orden) --}}
<div class="modal fade atlantico-modal atlantico-modal--op" id="subordenesModal" tabindex="-1" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-node-tree me-2"></i>Sub-órdenes de Producción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <p class="text-muted small mb-3">
                    <i class="ri-information-line me-1"></i>Orden principal:
                    <strong id="so-orden-nombre">—</strong><span class="text-muted" id="so-orden-meta"></span>
                </p>

                {{-- Alta de sub-orden + asignación de empleados --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-soft-primary">
                        <h6 class="mb-0 text-atlantico-dark"><i class="ri-add-line me-1"></i>Nueva sub-orden</h6>
                    </div>
                    <div class="card-body">
                        <form id="soForm" novalidate>
                            <input type="hidden" id="so-orden-id">
                            <div class="row g-2">
                                <div class="col-md-7">
                                    <label class="form-label small fw-semibold mb-1" for="so-nombre">Etapa / Tarea <span class="text-danger">*</span></label>
                                    <input type="text" id="so-nombre" class="form-control form-control-sm" maxlength="120"
                                        placeholder="Ej: Corte, Costura, Bordado" autocomplete="off">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small fw-semibold mb-1" for="so-cantidad">Cantidad asignada</label>
                                    <input type="number" id="so-cantidad" class="form-control form-control-sm" min="1" placeholder="Opcional">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold mb-1">Empleados asignados <span class="text-danger">*</span></label>
                                    <div id="so-empleados" class="border rounded p-2" style="max-height:150px; overflow:auto;"></div>
                                </div>
                                <div class="col-12">
                                    <textarea id="so-notas" class="form-control form-control-sm" rows="1" maxlength="500"
                                        placeholder="Notas (opcional)..."></textarea>
                                </div>
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success btn-sm" id="so-add-btn">
                                        <i class="ri-save-line me-1"></i>Agregar sub-orden
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Listado de sub-órdenes --}}
                <h6 class="text-muted small text-uppercase mb-2">
                    Sub-órdenes <span id="so-count" class="text-muted">(0)</span>
                </h6>
                <div id="so-lista"></div>
                <div id="so-empty" class="text-center text-muted py-4 d-none">
                    <i class="ri-node-tree d-block opacity-50 mb-2" style="font-size:2rem;"></i>
                    Aún no hay sub-órdenes para esta orden.
                </div>
            </div>

            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
