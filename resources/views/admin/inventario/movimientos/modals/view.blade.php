<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <!-- Header con gradiente marca Atlantico -->
            <div class="modal-header py-3"
                style="background: linear-gradient(135deg, #1e3c72 0%, #2ecc71 50%, #00d9a5 100%);">
                <h5 class="modal-title text-white d-flex align-items-center" id="viewModalLabel">
                    <i class="ri-file-list-3-line me-2 fs-4"></i>Detalles del Movimiento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <!-- Columna Izquierda: Datos del Insumo -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header border-0" style="background: rgba(30, 60, 114, 0.1);">
                                <h6 class="mb-0" style="color: #1e3c72;">
                                    <i class="ri-stack-line me-2"></i>Información del Insumo
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle me-3 d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px; background: rgba(30, 60, 114, 0.1);">
                                        <i class="ri-archive-line" style="color: #1e3c72;"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Insumo</small>
                                        <span class="fw-semibold" id="view-insumo">-</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle me-3 d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px; background: rgba(46, 204, 113, 0.1);">
                                        <i class="ri-swap-line" style="color: #2ecc71;"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Tipo de Movimiento</small>
                                        <span id="view-tipo-movimiento">-</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle me-3 d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px; background: rgba(0, 217, 165, 0.1);">
                                        <i class="ri-hashtag" style="color: #00d9a5;"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Cantidad</small>
                                        <span class="fw-semibold fs-5" id="view-cantidad">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Stock y Detalles -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header border-0" style="background: rgba(0, 217, 165, 0.1);">
                                <h6 class="mb-0" style="color: #00d9a5;">
                                    <i class="ri-bar-chart-box-line me-2"></i>Cambio de Stock
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center mb-3">
                                    <div class="col-5">
                                        <div class="border rounded p-2" style="background: rgba(220, 53, 69, 0.1);">
                                            <small class="text-muted d-block">Stock Anterior</small>
                                            <span class="fw-bold fs-5 text-danger" id="view-stock-anterior">-</span>
                                        </div>
                                    </div>
                                    <div class="col-2 d-flex align-items-center justify-content-center">
                                        <i class="ri-arrow-right-line fs-4 text-muted"></i>
                                    </div>
                                    <div class="col-5">
                                        <div class="border rounded p-2" style="background: rgba(25, 135, 84, 0.1);">
                                            <small class="text-muted d-block">Stock Nuevo</small>
                                            <span class="fw-bold fs-5 text-success" id="view-stock-nuevo">-</span>
                                        </div>
                                    </div>
                                </div>
                                <hr class="my-3">
                                <div class="mb-2">
                                    <small class="text-muted d-block"><i
                                            class="ri-chat-quote-line me-1"></i>Motivo</small>
                                    <span id="view-motivo" class="fst-italic">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fila inferior: Usuario y Fecha -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm"
                            style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                            <div class="card-body py-3">
                                <div class="row">
                                    <div class="col-md-6 border-end">
                                        <div class="d-flex align-items-center h-100 ps-3">
                                            <div class="rounded-circle me-3 d-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px; background: rgba(13, 110, 253, 0.1);">
                                                <i class="ri-user-line text-primary fs-5"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Registrado por</small>
                                                <span class="fw-semibold" id="view-usuario">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center h-100 ps-3">
                                            <div class="rounded-circle me-3 d-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px; background: rgba(25, 135, 84, 0.1);">
                                                <i class="ri-calendar-line text-success fs-5"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Fecha y Hora</small>
                                                <span class="fw-semibold" id="view-fecha">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>