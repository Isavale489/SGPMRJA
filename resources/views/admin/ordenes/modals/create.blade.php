<!-- Modal para crear/editar orden de producción -->
<div class="modal fade atlantico-modal atlantico-modal--op" id="showModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title mb-0" id="modalTitle">Nueva Orden de Producción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="ordenForm" novalidate>
                @csrf
                <div class="modal-body p-3">
                    {{-- id de la orden (modo edición) --}}
                    <input type="hidden" id="id-field" />
                    {{-- línea del pedido que produce la orden (de aquí salen producto + cantidad en el backend) --}}
                    <input type="hidden" id="detalle-pedido-id-field" name="detalle_pedido_id" />
                    {{-- referencias de solo lectura (sin name → no se envían) --}}
                    <input type="hidden" id="pedido-id-hidden-field" />
                    <input type="hidden" id="producto-id-field" />

                    {{-- ── Hero: línea seleccionada (solo lectura, compacto) ────────── --}}
                    <div class="cot-resumen-card mb-2" id="orden-linea-panel">
                        <div class="cot-resumen-card-header py-2 px-3">
                            <i class="ri-file-list-3-line"></i>
                            <span>Línea seleccionada</span>
                            <span class="ms-auto badge bg-light text-dark fw-normal" id="orden-linea-pedido">Pedido #—</span>
                        </div>
                        <div class="cot-resumen-card-body p-3">
                            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="text-white fw-semibold mb-1" id="orden-linea-producto">—</div>
                                    <div class="text-white-50 fs-12 mb-2" id="orden-linea-cliente"></div>
                                    <div class="d-flex flex-wrap gap-1" id="orden-linea-meta"></div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-white fs-4 lh-1" id="orden-linea-cantidad">0</div>
                                    <small class="text-white-50" style="font-size: 10.5px;">unidades</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Asignación: empleado + estado ───────────────────────── --}}
                    <div class="card border-0 shadow-sm mb-2">
                        <div class="card-header border-0 bg-soft-primary py-2 px-3">
                            <h6 class="mb-0 text-atlantico-dark fs-13">
                                <i class="ri-user-star-line me-1"></i>Asignación
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label for="empleado-id-field" class="form-label form-label-sm required mb-1">Empleado asignado</label>
                                    <select id="empleado-id-field" name="empleado_id" class="form-select form-select-sm" required>
                                        <option value="">Seleccione empleado...</option>
                                        @foreach($empleados as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6" id="estado-container" style="display: none;">
                                    <label for="estado-field" class="form-label form-label-sm mb-1">Estado</label>
                                    <select id="estado-field" name="estado" class="form-select form-select-sm">
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="En Proceso">En Proceso</option>
                                        <option value="Finalizado">Finalizado</option>
                                        <option value="Cancelado">Cancelado</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Cronograma ─────────────────────────────────────────── --}}
                    <div class="card border-0 shadow-sm mb-2">
                        <div class="card-header border-0 bg-soft-primary py-2 px-3">
                            <h6 class="mb-0 text-atlantico-dark fs-13">
                                <i class="ri-calendar-2-line me-1"></i>Cronograma
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label for="fecha-inicio-field" class="form-label form-label-sm required mb-1">Inicio</label>
                                    <input type="date" id="fecha-inicio-field" name="fecha_inicio" class="form-control form-control-sm" required />
                                </div>
                                <div class="col-md-6">
                                    <label for="fecha-fin-estimada-field" class="form-label form-label-sm required mb-1">Fin estimado</label>
                                    <input type="date" id="fecha-fin-estimada-field" name="fecha_fin_estimada" class="form-control form-control-sm" required />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Insumos requeridos (grilla, como wizard productos) ─── --}}
                    <div class="card border-0 shadow-sm mb-2">
                        <div class="card-header border-0 bg-soft-primary py-2 px-3 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 text-atlantico-dark fs-13">
                                <i class="ri-tools-line me-1"></i>Insumos requeridos
                                <span class="text-muted fw-normal ms-1" id="orden-insumos-count">(0)</span>
                            </h6>
                            <button type="button" class="btn btn-sm btn-soft-primary py-0 px-2" id="add-insumo-btn">
                                <i class="ri-add-line"></i> Agregar insumo
                            </button>
                        </div>
                        <div class="card-body p-0">
                            {{-- Empty state --}}
                            <div id="orden-insumos-empty" class="text-center py-3 text-muted">
                                <i class="ri-tools-line d-block opacity-50 mb-1" style="font-size: 1.75rem;"></i>
                                <p class="fs-12 mb-0">Aún no agregaste insumos. Haz click en <strong>“Agregar insumo”</strong>.</p>
                            </div>

                            {{-- Tabla agrupada (mismo estilo que el wizard de cotización) --}}
                            <div id="orden-insumos-table-wrap" class="cot-grouped-tablewrap" hidden>
                                <table class="cot-grouped-table">
                                    <thead>
                                        <tr>
                                            <th class="cot-col-num">#</th>
                                            <th class="cot-col-prod">Insumo</th>
                                            <th class="cot-col-num text-end">Cantidad</th>
                                            <th class="cot-col-acc text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="orden-insumos-tbody"></tbody>
                                </table>
                            </div>

                            {{-- Fuente de verdad para FormData: hidden inputs sincronizados con el estado --}}
                            <div id="insumos-container" hidden></div>
                        </div>
                    </div>

                    {{-- ── Notas ─────────────────────────────────────────────── --}}
                    <div class="card border-0 shadow-sm mb-0">
                        <div class="card-header border-0 bg-soft-primary py-2 px-3">
                            <h6 class="mb-0 text-atlantico-dark fs-13">
                                <i class="ri-sticky-note-line me-1"></i>Notas
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <textarea id="notas-field" name="notas" class="form-control form-control-sm" rows="2" placeholder="Observaciones sobre la orden (opcional)..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-2">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Cerrar
                        </button>
                        <button type="submit" class="btn btn-sm btn-success" id="add-btn">
                            <i class="ri-add-line me-1"></i>Crear Orden
                        </button>
                        <button type="submit" class="btn btn-sm btn-success" id="edit-btn" style="display: none;">
                            <i class="ri-save-line me-1"></i>Actualizar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
