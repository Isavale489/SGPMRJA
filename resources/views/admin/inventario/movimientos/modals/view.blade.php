<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="viewModalLabel">Detalles del Movimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0" scope="row">Insumo:</th>
                                <td class="text-muted" id="view-insumo"></td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">Tipo de Movimiento:</th>
                                <td id="view-tipo-movimiento"></td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">Cantidad:</th>
                                <td class="text-muted" id="view-cantidad"></td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">Stock Anterior:</th>
                                <td class="text-muted" id="view-stock-anterior"></td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">Stock Nuevo:</th>
                                <td class="text-muted" id="view-stock-nuevo"></td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">Motivo:</th>
                                <td class="text-muted" id="view-motivo"></td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">Registrado por:</th>
                                <td class="text-muted" id="view-usuario"></td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">Fecha y Hora:</th>
                                <td class="text-muted" id="view-fecha"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
