<!-- Modal — Detalles de Orden de Producción (wizard) -->
{{-- Estilos en public/assets/css/custom.css — sección "MÓDULO ÓRDENES — Modal Detalles" --}}

<div class="modal fade atlantico-modal atlantico-modal--op" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- ══ Encabezado dinámico ════════════════════════════════ -->
            <div class="modal-header border-0 pb-0">
                <div class="flex-grow-1 me-3">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h6 class="fw-bold mb-0 text-atlantico-dark" id="view-producto"></h6>
                        <div id="view-estado"></div>
                    </div>
                    <p class="text-muted mb-0 fs-12">
                        <i class="ri-file-list-2-line opacity-50 me-1"></i><span id="view-pedido-info"></span>
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- ══ Stepper ════════════════════════════════════════════ -->
            <div class="wiz-stepper-wrapper">
                <div class="wiz-stepper-side wiz-stepper-side--left"></div>
                <div class="wiz-stepper" role="tablist">
                    <button type="button" class="wiz-step-marker is-active" data-step="1" role="tab">
                        <span class="wiz-step-dot">1</span>
                        <span class="wiz-step-label">Orden</span>
                    </button>
                    <span class="wiz-step-line"><span class="wiz-step-line-fill" data-line="1"></span></span>
                    <button type="button" class="wiz-step-marker" data-step="2" role="tab">
                        <span class="wiz-step-dot">2</span>
                        <span class="wiz-step-label">Insumos</span>
                    </button>
                    <span class="wiz-step-line"><span class="wiz-step-line-fill" data-line="2"></span></span>
                    <button type="button" class="wiz-step-marker" data-step="3" role="tab">
                        <span class="wiz-step-dot">3</span>
                        <span class="wiz-step-label">Progreso</span>
                    </button>
                    <span class="wiz-step-line"><span class="wiz-step-line-fill" data-line="3"></span></span>
                    <button type="button" class="wiz-step-marker" data-step="4" role="tab">
                        <span class="wiz-step-dot"><i class="ri-layout-grid-line" style="font-size:11px;"></i></span>
                        <span class="wiz-step-label">Kanban</span>
                    </button>
                </div>
                <div class="wiz-stepper-side wiz-stepper-side--right"></div>
            </div>

            <!-- ══ Body del wizard ════════════════════════════════════ -->
            <div class="modal-body wiz-wizard-body p-3">

                <!-- ─ Paso 1: Datos de la Orden ─────────────────────── -->
                <section class="wiz-step-content is-active" data-step="1">
                    <div class="row g-3">

                        <!-- Metadata en emp-icon-box -->
                        <div class="col-12">
                            <div class="row g-2">
                                <div class="col-sm-4">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="emp-icon-box emp-icon-box--navy rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center">
                                            <i class="ri-user-star-line emp-icon--navy"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0 fs-11 text-uppercase">Empleado</p>
                                            <p class="fw-semibold fs-13 mb-0" id="view-empleado"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="emp-icon-box emp-icon-box--teal rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center">
                                            <i class="ri-user-line emp-icon--teal"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0 fs-11 text-uppercase">Creado por</p>
                                            <p class="fw-semibold fs-13 mb-0" id="view-creado-por"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="emp-icon-box emp-icon-box--green rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center">
                                            <i class="ri-time-line emp-icon--green"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0 fs-11 text-uppercase">Registrado</p>
                                            <p class="fw-semibold fs-13 mb-0" id="view-created"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="col-md-5">
                            <div class="card border-0 shadow-sm h-100 mb-0">
                                <div class="card-body p-3">
                                    <p class="text-uppercase text-muted mb-3 kpi-label">
                                        <i class="ri-calendar-2-line me-1 text-op-accent"></i>Cronograma
                                    </p>
                                    <div class="d-flex gap-3 mb-0">
                                        <div class="d-flex flex-column align-items-center flex-shrink-0">
                                            <div class="rounded-circle flex-shrink-0 timeline-dot timeline-dot-start"></div>
                                            <div class="timeline-line"></div>
                                        </div>
                                        <div class="pb-3">
                                            <p class="text-muted mb-0 timeline-date-label">Fecha de inicio</p>
                                            <span id="view-fecha-inicio" class="fw-semibold timeline-date-value"></span>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-3 mb-0">
                                        <div class="d-flex flex-column align-items-center flex-shrink-0">
                                            <div class="rounded-circle flex-shrink-0 timeline-dot timeline-dot-mid"></div>
                                            <div class="timeline-line"></div>
                                        </div>
                                        <div class="pb-3">
                                            <p class="text-muted mb-0 timeline-date-label">Fecha fin estimada</p>
                                            <span id="view-fecha-fin-estimada" class="fw-semibold timeline-date-value"></span>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="rounded-circle flex-shrink-0 timeline-dot timeline-dot-end"></div>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0 fst-italic timeline-date-label">Fin de producción</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Diseño / Bordado -->
                        <div class="col-md-7">
                            <div class="card border-0 shadow-sm h-100 mb-0">
                                <div class="card-body p-3">
                                    <p class="text-uppercase text-muted mb-2 kpi-label">
                                        <i class="ri-paint-brush-line me-1 text-op-accent"></i>Diseño / Bordado
                                    </p>
                                    <div class="fs-13 view-content-area" id="view-logo"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>

                <!-- ─ Paso 2: Insumos ────────────────────────────────── -->
                <section class="wiz-step-content" data-step="2">
                    <div class="card border-0 shadow-sm mb-0">
                        <div class="card-body p-0">
                            <div class="table-responsive" id="view-insumos-tablewrap">
                                <table class="table table-nowrap table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Insumo</th>
                                            <th class="text-center">Est.</th>
                                            <th class="text-center">Utilizado</th>
                                            <th>Progreso</th>
                                        </tr>
                                    </thead>
                                    <tbody id="view-insumos"></tbody>
                                </table>
                            </div>
                            <div id="view-insumos-empty" class="text-center py-4" style="display:none;">
                                <i class="ri-box-3-line text-muted" style="font-size:2rem;opacity:.4;"></i>
                                <p class="text-muted fs-13 mt-2 mb-0">Sin insumos registrados para esta orden.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ─ Paso 3: Progreso ───────────────────────────────── -->
                <section class="wiz-step-content" data-step="3">
                    <div class="row g-3">

                        <!-- Hero card de progreso -->
                        <div class="col-12">
                            <div class="cot-resumen-card">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="text-uppercase opacity-75 mb-0 fs-11" style="letter-spacing:.08em;">Progreso de producción</p>
                                    <span class="fw-bold" style="font-size:2rem;line-height:1;"><span id="view-progreso-pct">0</span>%</span>
                                </div>
                                <div class="progress mb-4" style="height:10px;background:rgba(255,255,255,.2);">
                                    <div id="view-progreso" class="progress-bar" role="progressbar"
                                        style="background:#00d9a5;transition:width .5s;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="row g-0 text-center">
                                    <div class="col-6 border-end border-white border-opacity-25">
                                        <p class="opacity-75 mb-1 fs-11 text-uppercase">Solicitada</p>
                                        <p class="fw-bold mb-0" style="font-size:2rem;" id="view-cantidad-solicitada"></p>
                                        <p class="opacity-60 fs-11 mb-0">unidades</p>
                                    </div>
                                    <div class="col-6">
                                        <p class="opacity-75 mb-1 fs-11 text-uppercase">Producida</p>
                                        <p class="fw-bold mb-0" style="font-size:2rem;" id="view-cantidad-producida"></p>
                                        <p class="opacity-60 fs-11 mb-0">unidades</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-0">
                                <div class="card-body p-3">
                                    <p class="text-uppercase text-muted mb-2 kpi-label">
                                        <i class="ri-sticky-note-line me-1 text-op-accent"></i>Notas
                                    </p>
                                    <p class="text-muted mb-0 fs-13 view-content-area" id="view-notas"></p>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>

                <!-- ─ Paso 4: Kanban ─────────────────────────────────── -->
                <section class="wiz-step-content" data-step="4">
                    <div id="kanban-loading" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Cargando…</span>
                        </div>
                        <p class="text-muted fs-13 mt-2 mb-0">Cargando tablero…</p>
                    </div>
                    <div id="kanban-empty" class="text-center py-4" style="display:none;">
                        <i class="ri-layout-grid-line text-muted" style="font-size:2rem;opacity:.4;"></i>
                        <p class="text-muted fs-13 mt-2 mb-0">No hay sub-órdenes para esta orden.</p>
                        <p class="text-muted fs-12 mb-0">Usa el botón <i class="ri-node-tree"></i> en la tabla para agregar etapas.</p>
                    </div>
                    <div id="kanban-board" class="kanban-board" style="display:none;"></div>
                </section>

            </div>{{-- /modal-body --}}

            <!-- ══ Footer del wizard ══════════════════════════════════ -->
            <div class="modal-footer wiz-wizard-footer py-2 px-3">
                <div class="wiz-wizard-footer-info"></div>
                <div class="wiz-wizard-footer-actions">
                    <button type="button" class="btn btn-sm btn-light border" id="btn-view-ord-prev" style="display:none;">
                        <i class="ri-arrow-left-line me-1"></i>Anterior
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" id="btn-view-ord-next">
                        Siguiente<i class="ri-arrow-right-line ms-1"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal" id="btn-view-ord-close" style="display:none;">
                        <i class="ri-close-line me-1"></i>Cerrar
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
