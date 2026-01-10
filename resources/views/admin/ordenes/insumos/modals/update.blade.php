<!-- Modal para actualizar uso de insumo -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title">Actualizar Uso de Insumo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="update_detalle_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Insumo</label>
                        <p id="update_insumo_nombre" class="form-control-static"></p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Cantidad Estimada</label>
                            <p id="update_cantidad_estimada" class="form-control-static"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unidad de Medida</label>
                            <p id="update_unidad_medida" class="form-control-static"></p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="cantidad_utilizada" class="form-label">Cantidad Utilizada</label>
                        <input type="number" class="form-control" id="cantidad_utilizada" 
                            name="cantidad_utilizada" step="0.01" min="0" required>
                        <div class="form-text">No puede exceder la cantidad estimada</div>
                    </div>

                    <div class="progress mb-3">
                        <div class="progress-bar" id="update_progress" role="progressbar"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-success" id="update-btn">Actualizar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
