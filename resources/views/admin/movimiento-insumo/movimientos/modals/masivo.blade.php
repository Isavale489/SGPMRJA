{{-- Modal: Movimiento masivo — aplica una misma Entrada/Salida a todos los
     insumos inventariables activos. Atajo para hidratar inventario. --}}
<div class="modal fade atlantico-modal" id="masivoModal" tabindex="-1" aria-labelledby="masivoModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-3">
                <h5 class="modal-title" id="masivoModalLabel">
                    <i class="ri-stack-line me-1"></i>Movimiento Masivo de Insumos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="masivoForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="ri-information-line fs-18 align-middle me-2"></i>
                            </div>
                            <div class="flex-grow-1">
                                Se registrará el mismo movimiento a los
                                <strong>{{ $insumosInventariables->count() }} insumos inventariables activos</strong>.
                                En salidas, los insumos sin stock suficiente se omiten.
                            </div>
                        </div>
                    </div>

                    <x-forms.select name="masivo_tipo_movimiento" label="Tipo de Movimiento" required
                        :options="['Entrada' => 'Entrada', 'Salida' => 'Salida']"
                        placeholder="Seleccione el tipo" />

                    <div class="mb-3">
                        <label for="masivo-cantidad" class="form-label">Cantidad por insumo <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="masivo-cantidad" name="cantidad" step="0.01" min="0.01"
                            required>
                        <div class="form-text">Cada insumo recibe esta misma cantidad, en su propia unidad de medida.</div>
                    </div>

                    <div class="mb-0">
                        <label for="masivo-motivo" class="form-label">Motivo <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="masivo-motivo" name="motivo" rows="3" maxlength="500"
                            placeholder="Ej.: Carga inicial de inventario" required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success" id="masivo-submit-btn">
                        <i class="ri-stack-line me-1"></i>Aplicar a todos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
