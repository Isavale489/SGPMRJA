<!-- Modal Agregar Insumo Rápido -->
<div class="modal fade" id="modalAddInsumo" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3"
                style="background: linear-gradient(135deg, #1e3c72 0%, #2ecc71 50%, #00d9a5 100%);">
                <h5 class="modal-title text-white d-flex align-items-center">
                    <i class="ri-add-circle-line me-2 fs-4"></i>Agregar Nuevo Insumo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="insumoFormMovimiento">
                <div class="modal-body">
                    <!-- Nombre y Tipo -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nombre-field-insumo" class="form-label required">Nombre del Insumo</label>
                            <input type="text" id="nombre-field-insumo" name="nombre" class="form-control"
                                placeholder="Ej: Tela Oxford" required />
                        </div>
                        <div class="col-md-6">
                            <label for="tipo-field-insumo" class="form-label required">Tipo</label>
                            <select id="tipo-field-insumo" name="tipo" class="form-select" required>
                                <option value="">Seleccione tipo...</option>
                                <option value="Tela">Tela</option>
                                <option value="Hilo">Hilo</option>
                                <option value="Botón">Botón</option>
                                <option value="Cierre">Cierre</option>
                                <option value="Etiqueta">Etiqueta</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                    </div>

                    <!-- Unidad de Medida y Costo -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="unidad-medida-field-insumo" class="form-label required">Unidad de Medida</label>
                            <input type="text" id="unidad-medida-field-insumo" name="unidad_medida" class="form-control"
                                placeholder="Ej: Metros, Unidades, Kg" required />
                        </div>
                        <div class="col-md-6">
                            <label for="costo-unitario-field-insumo" class="form-label required">Costo Unitario
                                ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" id="costo-unitario-field-insumo" name="costo_unitario"
                                    class="form-control" step="0.01" min="0" placeholder="0.00" required />
                            </div>
                        </div>
                    </div>

                    <!-- Stock Actual y Mínimo -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="stock-actual-field-insumo" class="form-label required">Stock Inicial</label>
                            <input type="number" id="stock-actual-field-insumo" name="stock_actual" class="form-control"
                                step="0.01" min="0" value="0" placeholder="0" required />
                            <small class="text-muted">Cantidad inicial en inventario</small>
                        </div>
                        <div class="col-md-6">
                            <label for="stock-minimo-field-insumo" class="form-label required">Stock Mínimo</label>
                            <input type="number" id="stock-minimo-field-insumo" name="stock_minimo" class="form-control"
                                step="0.01" min="0" placeholder="0" required />
                            <small class="text-muted">Nivel para alertas de reabastecimiento</small>
                        </div>
                    </div>

                    <!-- Proveedor -->
                    <div class="row">
                        <div class="col-12">
                            <label for="proveedor-id-field-insumo" class="form-label required">Proveedor</label>
                            <select id="proveedor-id-field-insumo" name="proveedor_id" class="form-select" required>
                                <option value="">Seleccione proveedor...</option>
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}">{{ $proveedor->razon_social }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-success" id="add-btn-insumo">
                        <i class="ri-add-line me-1"></i>Agregar Insumo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>