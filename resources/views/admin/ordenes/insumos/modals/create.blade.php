<!-- Modal para agregar insumo -->
<div class="modal fade" id="showModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="modalTitle">Agregar Insumo a la Orden</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="insumoForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="insumo_id" class="form-label">Insumo</label>
                        <select id="insumo_id" name="insumo_id" class="form-select" required>
                            <option value="">Seleccione un insumo...</option>
                            @foreach($insumos as $insumo)
                                <option value="{{ $insumo->id }}" 
                                    data-unidad="{{ $insumo->unidad_medida }}"
                                    data-stock="{{ $insumo->stock_actual }}">
                                    {{ $insumo->nombre }} ({{ $insumo->tipo }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Stock disponible: <span id="stock-disponible">-</span></div>
                    </div>

                    <div class="mb-3">
                        <label for="cantidad_estimada" class="form-label">Cantidad Estimada</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="cantidad_estimada" 
                                name="cantidad_estimada" step="0.01" min="0.01" required>
                            <span class="input-group-text" id="unidad-medida">-</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-success" id="add-btn">Agregar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
