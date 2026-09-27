{{-- Modal de búsqueda de proveedor — nivel 2 (anidado dentro del wizard)
     Prefijo de IDs: bsp-  (buscar-proveedor)
--}}
<div class="modal fade" id="buscarProveedorModal" tabindex="-1" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header utility-modal-header">
                <h5 class="modal-title">
                    <i class="ri-search-line me-2"></i>Buscar Proveedor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3">
                {{-- Buscador --}}
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="ri-search-2-line"></i></span>
                    <input type="text" id="bsp-input" class="form-control"
                        placeholder="Buscar por nombre, razón social o documento..." autocomplete="off">
                    <button type="button" class="btn btn-light" id="bsp-clear-btn" title="Limpiar">
                        <i class="ri-close-line"></i>
                    </button>
                </div>

                {{-- Loading --}}
                <div id="bsp-loading" class="text-center py-4 d-none">
                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                    <span class="text-muted small">Buscando...</span>
                </div>

                {{-- Resultados --}}
                <div id="bsp-results-wrap" style="max-height: 420px; overflow-y: auto;">
                    <table class="table table-hover table-sm align-middle mb-0" id="bsp-table">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width:30%">Nombre</th>
                                <th style="width:22%">Documento</th>
                                <th style="width:12%">Tipo</th>
                                <th style="width:24%">Teléfono</th>
                                <th style="width:12%" class="text-center">Compras</th>
                            </tr>
                        </thead>
                        <tbody id="bsp-tbody"></tbody>
                    </table>
                    <div id="bsp-empty" class="text-center py-5 text-muted d-none">
                        <i class="ri-store-2-line d-block opacity-40 mb-2" style="font-size:2rem;"></i>
                        <p class="mb-0 small">No se encontraron proveedores.</p>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light border-0 py-2">
                <small class="text-muted me-auto" id="bsp-count-label"></small>
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancelar
                </button>
            </div>

        </div>
    </div>
</div>
