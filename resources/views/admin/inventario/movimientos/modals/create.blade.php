<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="createModalLabel">Registrar Movimiento de Inventario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="insumo_id" class="form-label required">Insumo</label>
                        <div class="input-group">
                            <select class="form-select" id="insumo_id" name="insumo_id" required>
                                <option value="">Seleccione un insumo</option>
                                @foreach($insumos as $insumo)
                                    <option value="{{ $insumo->id }}" data-stock="{{ $insumo->stock_actual }}"
                                        data-unidad="{{ $insumo->unidad_medida }}">
                                        {{ $insumo->nombre }} ({{ $insumo->tipo }}) - Stock: {{ $insumo->stock_actual }}
                                        {{ $insumo->unidad_medida }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-success" id="open-add-insumo-modal"
                                title="Agregar nuevo insumo">
                                <i class="ri-add-line"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Por favor seleccione un insumo.</div>
                    </div>

                    <div class="mb-3">
                        <label for="tipo_movimiento" class="form-label required">Tipo de Movimiento</label><select
                            class="form-select" id="tipo_movimiento" name="tipo_movimiento" required>
                            <option value="">Seleccione el tipo</option>
                            <option value="Entrada">Entrada</option>
                            <option value="Salida">Salida</option>
                        </select>
                        <div class="invalid-feedback">Por favor seleccione el tipo de movimiento.</div>
                    </div>

                    <div class="mb-3">
                        <label for="cantidad" class="form-label">Cantidad <span id="unidad-medida"></span></label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad" step="0.01" min="0.01"
                            required>
                        <div class="invalid-feedback">Por favor ingrese una cantidad válida.</div>
                        <div id="stock-warning" class="text-danger mt-1 d-none">
                            ¡Atención! La cantidad excede el stock disponible.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo</label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="3" required></textarea>
                        <div class="invalid-feedback">Por favor ingrese el motivo del movimiento.</div>
                    </div>

                    <div class="alert alert-info">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="ri-information-line fs-18 align-middle me-2"></i>
                            </div>
                            <div class="flex-grow-1">
                                <span id="stock-info">Seleccione un insumo para ver información de stock.</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>