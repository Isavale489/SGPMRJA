<!-- Modal para ver detalles -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title">Detalles de la Orden de Producción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>Producto:</strong>
                            <p id="view-producto" class="text-muted mb-0"></p>
                        </div>
                        <div class="mb-3">
                            <strong>Cantidad Solicitada:</strong>
                            <p id="view-cantidad-solicitada" class="text-muted mb-0"></p>
                        </div>
                        <div class="mb-3">
                            <strong>Cantidad Producida:</strong>
                            <p id="view-cantidad-producida" class="text-muted mb-0"></p>
                        </div>
                        <div class="mb-3">
                            <strong>Progreso:</strong>
                            <div class="progress">
                                <div id="view-progreso" class="progress-bar" role="progressbar"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>Fecha de Inicio:</strong>
                            <p id="view-fecha-inicio" class="text-muted mb-0"></p>
                        </div>
                        <div class="mb-3">
                            <strong>Fecha Fin Estimada:</strong>
                            <p id="view-fecha-fin-estimada" class="text-muted mb-0"></p>
                        </div>
                        <div class="mb-3">
                            <strong>Estado:</strong>
                            <p id="view-estado" class="text-muted mb-0"></p>
                        </div>
                        <div class="mb-3">
                            <strong>Costo Estimado:</strong>
                            <p id="view-costo-estimado" class="text-muted mb-0"></p>
                        </div>
                        <div class="mb-3">
                            <strong>Logo:</strong>
                            <p id="view-logo" class="text-muted mb-0"></p>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Insumos Requeridos:</strong>
                    <div class="table-responsive mt-2">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Insumo</th>
                                    <th>Cantidad Estimada</th>
                                    <th>Cantidad Utilizada</th>
                                    <th>Progreso</th>
                                </tr>
                            </thead>
                            <tbody id="view-insumos">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Notas:</strong>
                    <p id="view-notas" class="text-muted mb-0"></p>
                </div>

                <div class="mb-3">
                    <strong>Creado por:</strong>
                    <p id="view-creado-por" class="text-muted mb-0"></p>
                </div>

                <div class="mb-3">
                    <strong>Fecha de Creación:</strong>
                    <p id="view-created" class="text-muted mb-0"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
