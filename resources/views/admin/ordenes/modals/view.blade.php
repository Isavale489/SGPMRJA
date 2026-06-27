<!-- Modal — Detalles de Orden de Producción (wizard) -->
{{-- Estilos en public/assets/css/custom.css — sección "MÓDULO ÓRDENES — Modal Detalles" --}}

<div class="modal fade atlantico-modal atlantico-modal--op" id="viewModal" tabindex="-1" aria-hidden="true">
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
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                {{-- Chip "Creado por" — gutter derecho del stepper (estándar de las transacciones) --}}
                <div class="wiz-stepper-side wiz-stepper-side--right">
                    <div class="wiz-client-banner wiz-client-banner--creator" title="Creado por">
                        <span class="wiz-client-banner-label">Creado por:</span>
                        <img class="wiz-client-banner-avatar wiz-client-banner-avatar--img"
                            id="view-ord-creador-avatar" src="" alt="" />
                        <div class="wiz-client-banner-main">
                            <span class="wiz-client-banner-name" id="view-creado-por">—</span>
                            <span class="wiz-client-banner-sub">
                                <span class="wiz-client-banner-doc">
                                    <i class="ri-time-line me-1"></i><span id="view-created">—</span>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ Body del wizard ════════════════════════════════════ -->
            <div class="modal-body wiz-wizard-body p-3">

                <!-- ─ Paso 1: Datos de la Orden ─────────────────────── -->
                <section class="wiz-step-content is-active" data-step="1">
                    <div class="row g-3">

                        <!-- Metadata en emp-icon-box -->
                        <div class="col-12">
                            <div class="cli-view-card">
                                <div class="cli-view-card-header">
                                    <i class="ri-clipboard-line"></i>Datos de la Orden
                                </div>
                                <div class="cli-view-card-body">
                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="emp-icon-box emp-icon-box--navy rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center">
                                            <i class="ri-user-star-line emp-icon--navy"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0 fs-11 text-uppercase" id="view-empleado-label">Empleado</p>
                                            <p class="fw-semibold fs-13 mb-0" id="view-empleado"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="col-md-5">
                            <div class="cli-view-card h-100 mb-0">
                                <div class="cli-view-card-header">
                                    <i class="ri-calendar-2-line"></i>Cronograma
                                </div>
                                <div class="cli-view-card-body">
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
                                            <p class="text-muted mb-0 timeline-date-label">Fin de producción</p>
                                            <span id="view-fecha-fin-real" class="fw-semibold timeline-date-value fst-italic text-muted">Aún en curso</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Diseño / Bordado -->
                        <div class="col-md-7">
                            <div class="cli-view-card h-100 mb-0">
                                <div class="cli-view-card-header">
                                    <i class="ri-paint-brush-line"></i>Diseño / Bordado
                                </div>
                                <div class="cli-view-card-body">
                                    <div class="fs-13 view-content-area" id="view-logo"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>

                <!-- ─ Paso 2: Insumos ────────────────────────────────── -->
                <section class="wiz-step-content" data-step="2">
                    <div class="cot-grouped-tablewrap" id="view-insumos-tablewrap">
                        <table class="cot-grouped-table">
                            <thead>
                                <tr>
                                    <th class="cot-col-num text-center" style="width:38px;">#</th>
                                    <th style="min-width:170px;">Insumo</th>
                                    <th class="cot-cell-num">Estimado</th>
                                    <th class="cot-cell-num">Utilizado</th>
                                    <th style="min-width:170px;">Progreso</th>
                                </tr>
                            </thead>
                            <tbody id="view-insumos"></tbody>
                        </table>
                    </div>
                    <div id="view-insumos-empty" class="cot-empty-state" style="display:none;">
                        <div class="cot-empty-icon"><i class="ri-box-3-line"></i></div>
                        <h6 class="cot-empty-title">Sin insumos</h6>
                        <p class="cot-empty-desc">Sin insumos registrados para esta orden.</p>
                    </div>
                </section>

                <!-- ─ Paso 3: Progreso ───────────────────────────────── -->
                <section class="wiz-step-content" data-step="3">
                    <div class="row g-3">

                        <!-- Card de progreso de producción -->
                        <div class="col-12">
                            <div class="cot-resumen-card">
                                <div class="ord-prog-body">
                                    <div class="ord-prog-head">
                                        <span class="ord-prog-title">Progreso de producción</span>
                                        <span class="ord-prog-pct"><span id="view-progreso-pct">0</span>%</span>
                                    </div>
                                    <div class="progress ord-prog-bar">
                                        <div id="view-progreso" class="progress-bar" role="progressbar"
                                            style="width:0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="ord-prog-stats">
                                        <div class="ord-prog-stat">
                                            <span class="ord-prog-stat-label">Solicitada</span>
                                            <span class="ord-prog-stat-num"><span id="view-cantidad-solicitada">0</span><small>u</small></span>
                                        </div>
                                        <div class="ord-prog-stat">
                                            <span class="ord-prog-stat-label">Producida</span>
                                            <span class="ord-prog-stat-num"><span id="view-cantidad-producida">0</span><small>u</small></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div class="col-12">
                            <div class="cli-view-card mb-0">
                                <div class="cli-view-card-header">
                                    <i class="ri-sticky-note-line"></i>Notas
                                </div>
                                <div class="cli-view-card-body">
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
            <div class="modal-footer wiz-wizard-footer">
                <div class="wiz-wizard-footer-info"></div>
                <div class="wiz-wizard-footer-actions">
                    <button type="button" class="btn btn-light wiz-wizard-btn-prev" id="btn-view-ord-prev" style="display:none;">
                        <i class="ri-arrow-left-line me-1"></i>Anterior
                    </button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cerrar
                    </button>
                    <button type="button" class="btn btn-atlantico-brand wiz-wizard-btn-next" id="btn-view-ord-next">
                        Continuar<i class="ri-arrow-right-line ms-1"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
