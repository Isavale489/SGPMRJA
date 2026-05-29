<!-- Modal — Detalles de Orden de Producción -->
{{-- Estilos en public/assets/css/custom.css — sección "MÓDULO ÓRDENES — Modal Detalles" --}}

<div class="modal fade atlantico-modal atlantico-modal--op" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- ══ CAPA 1: Encabezado estático ════════════════════════ -->
            <div class="modal-header">
                <h6 class="modal-title fw-semibold mb-0 orden-modal-title">
                    <i class="ri-list-check-2 me-2 opacity-75"></i>Detalles de la Orden de Producción
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- ══ CAPA 1: Zona de KPIs (placa blanca) ════════════════ -->
            <div class="view-plate px-4 pt-3 pb-3">

                <!-- Identidad: Producto + Estado + Metadata -->
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h5 class="fw-bold mb-0" id="view-producto"></h5>
                            <div id="view-estado"></div>
                        </div>
                        <p class="text-muted mb-0 fs-12 d-flex align-items-center flex-wrap gap-1">
                            <span id="view-pedido-info"></span>
                            <span class="px-1 opacity-50">·</span>
                            <i class="ri-user-star-line opacity-50"></i>
                            <span id="view-empleado"></span>
                            <span class="px-1 opacity-50">·</span>
                            <i class="ri-user-line opacity-50"></i>
                            <span id="view-creado-por"></span>
                            <span class="px-1 opacity-50">·</span>
                            <i class="ri-time-line opacity-50"></i>
                            <span id="view-created"></span>
                        </p>
                    </div>
                </div>

                <!-- KPI Strip — jerarquía tipográfica pura -->
                <div class="row g-0 mb-3">
                    <div class="col-6 kpi-sep pe-4">
                        <p class="text-uppercase text-muted mb-1 kpi-label">Solicitada</p>
                        <p class="mb-0 fw-bold kpi-number" id="view-cantidad-solicitada"></p>
                        <p class="text-muted mb-0 kpi-unit">unidades</p>
                    </div>
                    <div class="col-6 kpi-sep ps-4">
                        <p class="text-uppercase text-muted mb-1 kpi-label">Producida</p>
                        <p class="mb-0 fw-bold kpi-number" id="view-cantidad-producida"></p>
                        <p class="text-muted mb-0 kpi-unit">unidades</p>
                    </div>
                </div>

                <!-- Barra de progreso — único acento teal -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-uppercase text-muted kpi-label"
                           >Progreso de producción</span>
                        <span class="fw-semibold progress-pct-label">
                            <span id="view-progreso-pct">0</span>% completado
                        </span>
                    </div>
                    <div class="progress progress-orden">
                        <div id="view-progreso" class="progress-bar progress-bar-orden" role="progressbar"
                            aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

            </div>

            <!-- ══ CAPA 0: Lienzo gris — Tab Nav + Content ═══════════ -->
            <div class="modal-body p-3">

                <!-- Tabs nav — pertenece al lienzo, no a la placa -->
                <ul class="nav mb-3" id="viewModalTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-detalles-btn"
                            data-bs-toggle="pill" data-bs-target="#tab-detalles" type="button" role="tab"
                            aria-selected="true">
                            <i class="ri-calendar-2-line me-1"></i>Cronograma & Detalles
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-insumos-btn"
                            data-bs-toggle="pill" data-bs-target="#tab-insumos" type="button" role="tab"
                            aria-selected="false">
                            <i class="ri-box-3-line me-1"></i>Insumos
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="viewModalTabContent">

                    <!-- ─ TAB 1: Cronograma & Detalles ─────────────── -->
                    <div class="tab-pane fade show active" id="tab-detalles" role="tabpanel"
                        aria-labelledby="tab-detalles-btn">
                        <div class="row g-3">

                            <!-- Timeline -->
                            <div class="col-md-5">
                                <div class="card border-0 shadow-sm h-100 mb-0">
                                    <div class="card-body p-3">
                                        <p class="text-uppercase text-muted mb-3 kpi-label"
                                           >
                                            <i class="ri-calendar-2-line me-1 text-op-accent"></i>Cronograma
                                        </p>
                                        <!-- Inicio -->
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
                                        <!-- Fin estimado -->
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
                                        <!-- Fin real -->
                                        <div class="d-flex gap-3">
                                            <div class="flex-shrink-0">
                                                <div class="rounded-circle flex-shrink-0 timeline-dot timeline-dot-end"></div>
                                            </div>
                                            <div>
                                                <p class="text-muted mb-0 fst-italic timeline-date-label">
                                                    Fin de producción</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Diseño + Notas -->
                            <div class="col-md-7 d-flex flex-column gap-3">
                                <div class="card border-0 shadow-sm mb-0">
                                    <div class="card-body p-3">
                                        <p class="text-uppercase text-muted mb-2 kpi-label"
                                           >
                                            <i class="ri-paint-brush-line me-1 text-op-accent"></i>Diseño / Bordado
                                        </p>
                                        <div class="fs-13 view-content-area" id="view-logo"></div>
                                    </div>
                                </div>
                                <div class="card border-0 shadow-sm mb-0">
                                    <div class="card-body p-3">
                                        <p class="text-uppercase text-muted mb-2 kpi-label"
                                           >
                                            <i class="ri-sticky-note-line me-1 text-op-accent"></i>Notas
                                        </p>
                                        <p class="text-muted mb-0 fs-13 view-content-area" id="view-notas"
                                           ></p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ─ TAB 2: Insumos ────────────────────────────── -->
                    <div class="tab-pane fade" id="tab-insumos" role="tabpanel"
                        aria-labelledby="tab-insumos-btn">
                        <div class="card border-0 shadow-sm mb-0">
                            <div class="card-body p-0">
                                <div class="table-responsive">
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
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ══ Footer ════════════════════════════════════════════ -->
            <div class="modal-footer view-plate py-2 px-4">
                <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cerrar
                </button>
            </div>

        </div>
    </div>
</div>
