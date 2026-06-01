<!-- Modal — Mis Órdenes por empleado (consulta + registrar avance) -->
<div class="modal fade atlantico-modal atlantico-modal--op" id="misOrdenesModal" tabindex="-1" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0">Mis Órdenes</h5>
                    <p class="mb-0 fs-12" style="color: rgba(255,255,255,.85);">Órdenes de producción asignadas a cada empleado</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Selector de empleado --}}
                <div class="mb-3">
                    <label class="form-label fw-medium" for="mis-ordenes-empleado">
                        <i class="ri-user-line me-1"></i>Empleado de Producción
                    </label>
                    <select class="form-select" id="mis-ordenes-empleado">
                        <option value="">Selecciona un empleado…</option>
                        @foreach ($empleados as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Resumen — pills por estado --}}
                <div id="mis-ordenes-resumen" class="d-flex flex-wrap gap-2 mb-3" style="display: none;">
                    <span class="badge rounded-pill d-inline-flex align-items-center px-3 py-2" style="background:rgba(71,85,105,.10); color:#475569;">
                        <i class="ri-stack-line me-1"></i>Total <strong class="ms-1 fs-13" id="mo-total">0</strong>
                    </span>
                    <span class="badge rounded-pill d-inline-flex align-items-center px-3 py-2" style="background:rgba(245,158,11,.14); color:#d97706;">
                        <i class="ri-time-line me-1"></i>Pendientes <strong class="ms-1 fs-13" id="mo-pendientes">0</strong>
                    </span>
                    <span class="badge rounded-pill d-inline-flex align-items-center px-3 py-2" style="background:rgba(37,99,235,.12); color:#2563eb;">
                        <i class="ri-loader-4-line me-1"></i>En proceso <strong class="ms-1 fs-13" id="mo-en-proceso">0</strong>
                    </span>
                    <span class="badge rounded-pill d-inline-flex align-items-center px-3 py-2" style="background:rgba(22,163,74,.12); color:#16a34a;">
                        <i class="ri-check-double-line me-1"></i>Finalizadas <strong class="ms-1 fs-13" id="mo-finalizadas">0</strong>
                    </span>
                </div>

                {{-- Lista de órdenes --}}
                <div id="mis-ordenes-container" style="max-height: 440px; overflow-y: auto;"></div>

                {{-- Estado inicial (sin empleado) --}}
                <div id="mis-ordenes-placeholder" class="text-center py-5">
                    <i class="ri-user-search-line" style="font-size: 3.5rem; color: #cbd5e1;"></i>
                    <p class="text-muted mt-3 mb-0">Selecciona un empleado para ver sus órdenes asignadas</p>
                </div>

                {{-- Estado vacío --}}
                <div id="mis-ordenes-empty" class="text-center py-5" style="display: none;">
                    <i class="ri-inbox-line" style="font-size: 3.5rem; color: #cbd5e1;"></i>
                    <p class="text-muted mt-3 mb-0">Este empleado no tiene órdenes asignadas</p>
                </div>

                {{-- Loading --}}
                <div id="mis-ordenes-loading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="text-muted mt-3 mb-0">Cargando órdenes...</p>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
