<!-- Modal para registrar producción -->
<div class="modal fade" id="showModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="modalTitle">Registrar Producción Diaria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="produccionForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="orden_id" class="form-label required">Orden de Producción</label><select id="orden_id" name="orden_id" class="form-select" required>
                            <option value="">Seleccione una orden...</option>
                            @foreach($ordenes as $orden)
                                <option value="{{ $orden->id }}">
                                    #{{ $orden->id }} - {{ $orden->producto->nombre ?? 'N/A' }}
                                    ({{ $orden->cantidad_producida }}/{{ $orden->cantidad_solicitada }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="operario_id" class="form-label required">Empleado</label><select id="operario_id" name="operario_id" class="form-select" required>
                            <option value="">Seleccione un operario...</option>
                            @foreach($operarios as $operario)
                                <option value="{{ $operario->id }}">{{ $operario->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cantidad_producida" class="form-label required">Cantidad Producida</label><input type="number" class="form-control" id="cantidad_producida"
                                    name="cantidad_producida" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cantidad_defectuosa" class="form-label required">Cantidad Defectuosa</label><input type="number" class="form-control" id="cantidad_defectuosa"
                                    name="cantidad_defectuosa" min="0" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3" maxlength="500"
                            placeholder="Ingrese observaciones sobre el proceso..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-success" id="add-btn">Registrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>