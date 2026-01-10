<!-- Modal para ver detalles -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title">Detalles de Producción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6>Información de la Orden</h6>
                    <p class="mb-1"><strong>Orden:</strong> #<span id="view_orden_id"></span></p>
                    <p class="mb-1"><strong>Producto:</strong> <span id="view_producto"></span></p>
                    <p class="mb-1"><strong>Empleado:</strong> <span id="view_operario"></span></p>
                    <p class="mb-1"><strong>Fecha:</strong> <span id="view_fecha"></span></p>
                </div>

                <div class="mb-3">
                    <h6>Producción</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Cantidad Producida:</strong> <span
                                    id="view_cantidad_producida"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Cantidad Defectuosa:</strong> <span
                                    id="view_cantidad_defectuosa"></span></p>
                        </div>
                    </div>
                    <div class="mt-2">
                        <p class="mb-1"><strong>Eficiencia:</strong></p>
                        <div class="progress" style="height: 15px;">
                            <div class="progress-bar" id="view_eficiencia" role="progressbar"></div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <h6>Observaciones</h6>
                    <p id="view_observaciones" class="text-muted"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>