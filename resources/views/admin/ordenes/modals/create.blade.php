<!-- Modal para agregar/editar -->
<div class="modal fade" id="showModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="modalTitle">Nueva Orden de Producción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="ordenForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="id-field" />
                    <input type="hidden" id="pedido-id-hidden-field" name="pedido_id" />
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="pedido-id-field" class="form-label required">Seleccionar Pedido</label><select id="pedido-id-field" name="pedido_id" class="form-control" required>
                                <option value="">Seleccione un pedido...</option>
                                @foreach($pedidos as $pedido)
                                    <option value="{{ $pedido->id }}" 
                                            data-cliente="{{ $pedido->cliente_nombre }}"
                                            data-fecha="{{ $pedido->fecha_pedido }}"
                                            data-entrega="{{ $pedido->fecha_entrega_estimada }}">
                                        Pedido #{{ $pedido->id }} - {{ $pedido->cliente_nombre }} 
                                        ({{ $pedido->productos->count() }} productos) - 
                                        Entrega: {{ $pedido->fecha_entrega_estimada ? $pedido->fecha_entrega_estimada->format('d/m/Y') : 'No definida' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Información del pedido seleccionado -->
                    <div id="pedido-info" style="display: none;">
                        <div class="alert alert-info">
                            <h6><i class="ri-information-line"></i> Información del Pedido</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Cliente:</strong> <span id="info-cliente"></span><br>
                                    <strong>Fecha Pedido:</strong> <span id="info-fecha-pedido"></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Fecha Entrega:</strong> <span id="info-fecha-entrega"></span><br>
                                    <strong>Total Productos:</strong> <span id="info-total-productos"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Productos del pedido -->
                    <div id="productos-pedido" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Productos a Producir</label>
                            <div id="productos-container">
                                <!-- Se llenarán dinámicamente -->
                            </div>
                        </div>
                    </div>

                    <!-- Campos ocultos para mantener compatibilidad -->
                    <input type="hidden" id="producto-id-field" name="producto_id" />
                    <input type="hidden" id="cantidad-solicitada-field" name="cantidad_solicitada" />

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fecha-inicio-field" class="form-label required">Fecha de Inicio</label><input type="date" id="fecha-inicio-field" name="fecha_inicio" class="form-control" required />
                        </div>
                        <div class="col-md-6">
                            <label for="fecha-fin-estimada-field" class="form-label required">Fecha Fin Estimada</label><input type="date" id="fecha-fin-estimada-field" name="fecha_fin_estimada" class="form-control" required />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="costo-estimado-field" class="form-label required">Costo Estimado</label><input type="number" id="costo-estimado-field" name="costo_estimado" class="form-control" step="0.01" min="0" required />
                        </div>
                        <div class="col-md-6">
                            <label for="logo-field" class="form-label">Logo</label>
                            <input type="text" id="logo-field" name="logo" class="form-control"/>
                        </div>
                        <div class="col-md-6" id="estado-container" style="display: none;">
                            <label for="estado-field" class="form-label">Estado</label>
                            <select id="estado-field" name="estado" class="form-control">
                                <option value="Pendiente">Pendiente</option>
                                <option value="En Proceso">En Proceso</option>
                                <option value="Finalizado">Finalizado</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Insumos Requeridos</label>
                        <div id="insumos-container">
                            <div class="row insumo-row">
                                <div class="col-md-6">
                                    <select name="insumos[0][id]" class="form-control insumo-select" required>
                                        <option value="">Seleccione insumo...</option>
                                        @foreach($insumos as $insumo)
                                            <option value="{{ $insumo->id }}">{{ $insumo->nombre }} ({{ $insumo->unidad_medida }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" name="insumos[0][cantidad_estimada]" class="form-control" placeholder="Cantidad" step="0.01" min="0.01" required />
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remove-insumo"><i class="ri-delete-bin-line"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-info" id="add-insumo-btn">
                                <i class="ri-add-line"></i> Agregar Insumo
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notas-field" class="form-label">Notas</label>
                        <textarea id="notas-field" name="notas" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-success" id="add-btn">Crear Orden</button>
                        <button type="submit" class="btn btn-success" id="edit-btn" style="display: none;">Actualizar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
