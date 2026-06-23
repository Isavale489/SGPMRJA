{{-- Modal de búsqueda de insumo — nivel 2 (anidado dentro del wizard)
     Réplica del patrón de buscar-proveedor; filtra el catálogo INSUMOS en cliente.
     Prefijo de IDs: bsi-  (buscar-insumo)
--}}
<div class="modal fade" id="buscarInsumoModal" tabindex="-1" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header utility-modal-header">
                <h5 class="modal-title">
                    <i class="ri-search-line me-2"></i>Buscar Insumo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3">
                {{-- Buscador --}}
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="ri-search-2-line"></i></span>
                    <input type="text" id="bsi-input" class="form-control"
                        placeholder="Buscar por nombre, código o tipo..." autocomplete="off">
                    <button type="button" class="btn btn-light" id="bsi-clear-btn" title="Limpiar">
                        <i class="ri-close-line"></i>
                    </button>
                </div>

                {{-- Resultados --}}
                <div id="bsi-results-wrap" style="max-height: 420px; overflow-y: auto;">
                    <table class="table table-hover table-sm align-middle mb-0" id="bsi-table">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width:34%">Insumo</th>
                                <th style="width:16%">Código</th>
                                <th style="width:20%">Tipo</th>
                                <th style="width:12%" class="text-center">Unidad</th>
                                <th style="width:12%" class="text-end">Costo ref.</th>
                                <th style="width:6%" class="text-center">IVA</th>
                            </tr>
                        </thead>
                        <tbody id="bsi-tbody"></tbody>
                    </table>
                    <div id="bsi-empty" class="text-center py-5 text-muted d-none">
                        <i class="ri-archive-2-line d-block opacity-40 mb-2" style="font-size:2rem;"></i>
                        <p class="mb-0 small">No se encontraron insumos. Puedes crear uno nuevo con el botón “+”.</p>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light border-0 py-2">
                <small class="text-muted me-auto" id="bsi-count-label"></small>
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancelar
                </button>
            </div>

        </div>
    </div>
</div>
