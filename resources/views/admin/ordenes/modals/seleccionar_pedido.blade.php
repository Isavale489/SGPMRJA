<!-- Modal — Seleccionar pedido / línea para producir -->
<div class="modal fade atlantico-modal atlantico-modal--op" id="seleccionarPedidoModal" tabindex="-1" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Pedido para Producir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Búsqueda + filtros avanzados — Patrón Maestro S-07 --}}
                <div class="advanced-filters-wrapper emerald-theme mb-3" id="pedord-advanced-filters">
                    <div class="navy-filter-header is-collapsed">
                        <div class="navy-header-search">
                            <i class="ri-search-line"></i>
                            <input type="text" class="navy-search-input" id="pedord-search"
                                placeholder="Buscar por cliente, documento o N° de pedido..." autocomplete="off">
                        </div>
                        <div class="navy-header-divider"></div>
                        <button class="navy-filter-btn collapsed" type="button"
                            data-bs-toggle="collapse" data-bs-target="#pedord-filters-collapse"
                            aria-expanded="false" aria-controls="pedord-filters-collapse">
                            <i class="ri-filter-3-line"></i>
                            <span>Filtros</span>
                            <span class="navy-filter-badge d-none" id="pedord-filter-count"></span>
                            <i class="ri-arrow-down-s-line navy-filter-chevron"></i>
                        </button>
                    </div>
                    <div class="collapse" id="pedord-filters-collapse">
                        <div class="navy-filter-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="navy-filter-label" for="pedord-filter-estado">
                                        <i class="ri-flag-line"></i> Estado del pedido
                                    </label>
                                    <select class="form-select navy-filter-select" id="pedord-filter-estado">
                                        <option value="">Todos</option>
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="Procesando">Procesando</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="navy-filter-label" for="pedord-filter-orden">
                                        <i class="ri-sort-asc"></i> Ordenar por
                                    </label>
                                    <select class="form-select navy-filter-select" id="pedord-filter-orden">
                                        <option value="recientes">Más recientes</option>
                                        <option value="entrega">Entrega más próxima</option>
                                        <option value="pendientes">Más líneas sin orden</option>
                                    </select>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <label class="navy-filter-label" for="pedord-filter-desde">
                                        <i class="ri-calendar-line"></i> Pedido desde
                                    </label>
                                    <input type="date" class="form-control navy-filter-select" id="pedord-filter-desde">
                                </div>
                                <div class="col-6 col-lg-3">
                                    <label class="navy-filter-label" for="pedord-filter-hasta">
                                        <i class="ri-calendar-2-line"></i> Pedido hasta
                                    </label>
                                    <input type="date" class="form-control navy-filter-select" id="pedord-filter-hasta">
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="navy-filter-label" for="pedord-filter-cobertura">
                                        <i class="ri-list-check-2"></i> Cobertura de órdenes
                                    </label>
                                    <select class="form-select navy-filter-select" id="pedord-filter-cobertura">
                                        <option value="">Todos</option>
                                        <option value="pendientes">Con líneas sin orden</option>
                                        <option value="cubiertos">Completamente cubiertos</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-2">
                                <button type="button" class="btn btn-link" id="pedord-clear-filters">Limpiar filtros</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contenedor de pedidos --}}
                <div id="pedidos-orden-container" style="max-height: 460px; overflow-y: auto;"></div>

                {{-- Estado vacío --}}
                <div id="pedidos-orden-empty" class="text-center py-5" style="display: none;">
                    <i class="ri-inbox-line" style="font-size: 4rem; color: #cbd5e1;"></i>
                    <p class="text-muted mt-3 mb-0">No hay pedidos disponibles para producir</p>
                    <small class="text-muted">Los pedidos cancelados o completados no aparecen aquí</small>
                </div>

                {{-- Loading --}}
                <div id="pedidos-orden-loading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="text-muted mt-3 mb-0">Cargando pedidos...</p>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancelar
                </button>
            </div>
        </div>
    </div>
</div>
