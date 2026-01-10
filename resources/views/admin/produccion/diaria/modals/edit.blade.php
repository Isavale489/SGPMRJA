<!-- Modal para editar registro -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title">Editar Registro de Producción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <h6>Información de la Orden</h6>
                        <p class="mb-1"><strong>Orden:</strong> #<span id="edit_orden_id"></span></p>
                        <p class="mb-1"><strong>Producto:</strong> <span id="edit_producto"></span></p>
                        <p class="mb-1"><strong>Empleado:</strong> <span id="edit_operario"></span></p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_cantidad_producida" class="form-label required">Cantidad Producida</label><input type="number" class="form-control" id="edit_cantidad_producida"
                                    name="cantidad_producida" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_cantidad_defectuosa" class="form-label required">Cantidad Defectuosa</label><input type="number" class="form-control" id="edit_cantidad_defectuosa"
                                    name="cantidad_defectuosa" min="0" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="edit_observaciones" name="observaciones" rows="3"
                            maxlength="500" placeholder="Ingrese observaciones sobre el proceso..."></textarea>
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