<!-- Modal — Órdenes de producción de un pedido (drill-down desde la tabla principal) -->
<div class="modal fade atlantico-modal atlantico-modal--op" id="pedidoOrdenesModal" tabindex="-1" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0" id="pedido-ordenes-title">Órdenes del pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Contexto del pedido: cliente + resumen agregado --}}
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3" id="pedido-ordenes-meta"></div>

                <table id="pedido-ordenes-table" class="table table-bordered table-striped align-middle dt-transactional table-operativa w-100">
                    <thead>
                        <tr>
                            <th class="text-center">Nro. Orden</th>
                            <th>Producto</th>
                            <th class="text-center">Cant. Solicitada</th>
                            <th class="text-center">Progreso</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
