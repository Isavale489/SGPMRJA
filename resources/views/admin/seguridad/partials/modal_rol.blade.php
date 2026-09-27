{{-- Modal de alta/edición de rol. Clase atlantico-modal + #id-field => AtlanticoGuard
     se activa solo en modo edición (CLAUDE.md). --}}
<div class="modal fade atlantico-modal" id="rolModal" tabindex="-1" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false" data-guard-save-btn="seg-guardar-rol">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="rolModalTitle">Nuevo rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="seg-rol-form" novalidate>
                <div class="modal-body">
                    {{-- Campo oculto con el ID del registro (convención universal del proyecto) --}}
                    <input type="hidden" id="id-field" name="id" value="">

                    <div class="mb-3">
                        <label for="seg-rol-nombre" class="form-label">Nombre del rol <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="seg-rol-nombre" name="nombre" maxlength="60"
                            placeholder="Ej. Vendedor, Almacén, Producción" autocomplete="off">
                        <div class="invalid-feedback" id="seg-rol-nombre-error"></div>
                        <div class="form-text d-none" id="seg-rol-nota-sistema">
                            <i class="ri-lock-line me-1"></i>El nombre de los roles de sistema no se puede cambiar.
                        </div>
                    </div>

                    <div class="mb-1">
                        <label for="seg-rol-descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="seg-rol-descripcion" name="descripcion" rows="2"
                            maxlength="255" placeholder="Para qué sirve este rol (opcional)"></textarea>
                        <div class="invalid-feedback" id="seg-rol-descripcion-error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="seg-guardar-rol">
                        <i class="ri-save-3-line align-bottom me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
