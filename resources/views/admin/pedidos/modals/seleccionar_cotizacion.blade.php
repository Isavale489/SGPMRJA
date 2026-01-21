<div class="modal fade" id="seleccionarCotizacionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <!-- Header con gradiente marca Atlantico -->
            <div class="modal-header py-3"
                style="background: linear-gradient(135deg, #1e3c72 0%, #2ecc71 50%, #00d9a5 100%);">
                <h5 class="modal-title text-white d-flex align-items-center">
                    <i class="ri-file-list-3-line me-2 fs-4"></i>Seleccionar Cotización Aprobada
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Buscador -->
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text" style="background: rgba(30, 60, 114, 0.1); border-color: #1e3c72;">
                            <i class="ri-search-line" style="color: #1e3c72;"></i>
                        </span>
                        <input type="text" id="buscarCotizacion" class="form-control"
                            placeholder="Buscar por cliente, documento o número de cotización..."
                            style="border-color: #1e3c72;">
                    </div>
                </div>

                <!-- Contenedor de cotizaciones -->
                <div id="cotizaciones-container" style="max-height: 450px; overflow-y: auto;">
                    <!-- Las cotizaciones se cargarán aquí dinámicamente como cards -->
                </div>

                <!-- Estado vacío -->
                <div id="empty-state" class="text-center py-5" style="display: none;">
                    <i class="ri-file-forbid-line" style="font-size: 4rem; color: #cbd5e1;"></i>
                    <p class="text-muted mt-3 mb-0">No hay cotizaciones aprobadas disponibles</p>
                    <small class="text-muted">Las cotizaciones ya convertidas a pedidos no aparecen aquí</small>
                </div>

                <!-- Loading state -->
                <div id="loading-state" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="text-muted mt-3 mb-0">Cargando cotizaciones...</p>
                </div>
            </div>
            <div class="modal-footer" style="background: rgba(0, 217, 165, 0.05);">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos para las cards de cotizaciones */
    .cotizacion-card {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #ffffff;
    }

    .cotizacion-card:hover {
        border-color: #00d9a5;
        box-shadow: 0 4px 12px rgba(0, 217, 165, 0.15);
        transform: translateY(-2px);
    }

    .cotizacion-card.selected {
        border-color: #2ecc71;
        background: rgba(46, 204, 113, 0.05);
    }

    .cotizacion-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .cotizacion-numero {
        font-weight: 600;
        color: #1e3c72;
        font-size: 1.1rem;
    }

    .cotizacion-total {
        font-weight: 700;
        color: #2ecc71;
        font-size: 1.2rem;
    }

    .cotizacion-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        font-size: 0.9rem;
    }

    .cotizacion-info-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .cotizacion-info-item i {
        color: #64748b;
    }

    .cotizacion-footer {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #e2e8f0;
        text-align: right;
    }
</style>
