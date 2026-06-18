{{-- Modal crear/editar impuesto (tabla `impuesto`). Estándar atlantico-modal. --}}
<div class="modal fade atlantico-modal" id="impuestoModal" tabindex="-1" aria-labelledby="impuestoModalTitle"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form-impuesto" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="impuestoModalTitle">Nuevo impuesto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    {{-- ID del registro (convención universal del proyecto) --}}
                    <input type="hidden" id="id-field" name="id">
                    {{-- URL de guardado: store por defecto; el editar la sobreescribe --}}
                    <input type="hidden" id="imp-store-url" value="{{ route('impuestos.store') }}">

                    <div class="row g-3">
                        <div class="col-md-5">
                            <label for="imp-codigo" class="form-label">Código <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" id="imp-codigo" name="codigo"
                                maxlength="20" placeholder="Ej: IVA, ISLR">
                            <div class="form-text" id="imp-codigo-help">Identificador corto en mayúsculas.</div>
                            <div class="invalid-feedback" id="imp-codigo-error"></div>
                        </div>
                        <div class="col-md-7">
                            <label for="imp-porcentaje" class="form-label">Porcentaje (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control"
                                id="imp-porcentaje" name="porcentaje" placeholder="16">
                            <div class="invalid-feedback" id="imp-porcentaje-error"></div>
                        </div>
                        <div class="col-12">
                            <label for="imp-nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="imp-nombre" name="nombre" maxlength="100"
                                placeholder="Ej: IVA (Impuesto al Valor Agregado)">
                            <div class="invalid-feedback" id="imp-nombre-error"></div>
                        </div>
                        <div class="col-12">
                            <label for="imp-descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="imp-descripcion" name="descripcion" rows="2"
                                maxlength="255" placeholder="Opcional"></textarea>
                            <div class="invalid-feedback" id="imp-descripcion-error"></div>
                        </div>
                        <div class="col-md-6" id="imp-estado-wrapper">
                            <label for="imp-estado" class="form-label">Estado</label>
                            <select class="form-select" id="imp-estado" name="estado">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                            <div class="invalid-feedback" id="imp-estado-error"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btn-guardar-impuesto">
                        <i class="ri-save-3-line align-bottom me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
