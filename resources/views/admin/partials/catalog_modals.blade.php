<!-- Modal para seleccionar Logo (Catálogo de Logos) -->
<div class="modal fade" id="logoSearchModal" tabindex="-1" aria-labelledby="logoSearchModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 1110;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header p-3" id="logoSearchModal-header" style="background-color: #132649 !important;">
                <h5 class="modal-title" style="color: #ffffff !important;" id="logoSearchModalLabel">
                    <i class="ri-image-line me-2" style="opacity: 0.7;"></i>Buscar y Seleccionar Logo
                </h5>
                <button type="button" class="btn-close utility-modal-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <style>
                    #logoSearchModalTable thead,
                    #logoSearchModalTable thead tr {
                        background: #1e3c72;
                        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
                    }

                    #logoSearchModalTable thead tr th {
                        background: transparent !important;
                        background-color: transparent !important;
                        --bs-table-bg: transparent;
                        --bs-table-accent-bg: transparent;
                        --bs-table-striped-bg: transparent;
                        --bs-table-hover-bg: transparent;
                        --bs-table-active-bg: transparent;
                        color: white !important;
                        border: none !important;
                        border-top: none !important;
                        border-bottom: none !important;
                        border-left: none !important;
                        border-right: none !important;
                        font-size: 0.78rem;
                        letter-spacing: 0.04em;
                        text-transform: uppercase;
                        font-weight: 600;
                    }

                    #logoSearchModalTable tbody td {
                        color: #333 !important;
                        vertical-align: middle;
                    }

                    #logoSearchModalTable .logo-filename-cell {
                        font-size: 0.74rem;
                        color: #9099a8 !important;
                        font-style: italic;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        white-space: nowrap;
                        max-width: 160px;
                    }
                </style>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-soft-primary d-flex align-items-center">
                        <h6 class="mb-0 text-atlantico-dark">
                            <i class="ri-filter-3-line me-2"></i>Búsqueda de logo
                        </h6>
                        <button type="button" class="btn btn-sm btn-atlantico-brand ms-auto" id="logoQuickCreateToggle">
                            <i class="ri-add-line me-1"></i>Nuevo logo
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="input-group">
                            <span class="input-group-text bg-soft-primary border-primary text-primary">
                                <i class="ri-search-line"></i>
                            </span>
                            <input type="text" id="buscarLogoModal" class="form-control border-primary"
                                placeholder="Buscar por nombre del logo...">
                        </div>
                        <small class="text-muted mt-1 d-block">
                            <i class="ri-information-line me-1"></i>Puede hacer doble clic en una fila para seleccionar
                            el logo
                        </small>

                        {{-- Alta rápida: registra el logo y lo deja seleccionado en la ubicación --}}
                        <div class="collapse mt-3" id="logoQuickCreateForm">
                            <div class="border rounded-3 p-3 bg-light-subtle">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold mb-1" for="logoQuickName">
                                            Nombre del logo <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-sm" id="logoQuickName"
                                            maxlength="120" placeholder="Ej. Ferretería La Torre">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold mb-1" for="logoQuickFile">
                                            Archivo del ponchado <span class="text-muted fw-normal">(opcional)</span>
                                        </label>
                                        <input type="text" class="form-control form-control-sm" id="logoQuickFile"
                                            maxlength="150" placeholder="Se asume «Nombre.emb» si se deja vacío">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end gap-2 mt-2">
                                    <button type="button" class="btn btn-sm btn-light" id="logoQuickCancel">Cancelar</button>
                                    <button type="button" class="btn btn-sm btn-atlantico-brand" id="logoQuickSave">
                                        <i class="ri-save-line me-1"></i>Guardar y seleccionar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 bg-soft-primary">
                        <h6 class="mb-0 text-atlantico-dark">
                            <i class="ri-folder-image-line me-2"></i>Catálogo de Logos
                            <span class="badge bg-secondary ms-2" id="logoModalCount">0 logos</span>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 350px;">
                            <table class="table table-hover mb-0" id="logoSearchModalTable">
                                <thead style="position: sticky; top: 0; z-index: 10;">
                                    <tr>
                                        <th style="width:65%; border:none;"><i class="ri-image-line me-1"></i>Nombre del
                                            Logo</th>
                                        <th style="width:25%; border:none;"><i class="ri-file-line me-1"></i>Archivo
                                            Original</th>
                                        <th style="width:10%; border:none;" class="text-center"><i
                                                class="ri-check-line"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="logoModalBody">
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">Cargando logos...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="colorCatalogoModal" tabindex="-1" aria-labelledby="colorCatalogoModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 1070;">
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content"
            style="box-shadow: 0 24px 60px rgba(0,0,0,0.55), 0 8px 24px rgba(0,0,0,0.35), 0 0 0 1px rgba(30,60,114,0.22); border-top: 3px solid #00d9a5;">
            <div class="modal-header p-3" style="background-color: #132649 !important;">
                <h5 class="modal-title" style="color: #ffffff !important;" id="colorCatalogoModalLabel">
                    <i class="ri-palette-line me-2" style="opacity:0.7;"></i>Catálogo de Colores
                </h5>
                <button type="button" class="btn-close utility-modal-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body p-3" style="overflow-x: hidden;">
                <div class="alert alert-warning py-2 px-3 mb-3 d-flex align-items-start"
                    style="font-size: 0.78rem; background: rgba(241, 196, 15, 0.12); border-color: rgba(241, 196, 15, 0.3); color: #7d6608;">
                    <i class="ri-information-line me-2 mt-1 flex-shrink-0" style="font-size: 1rem;"></i>
                    <span><strong>Nota:</strong> Los colores mostrados son meramente referenciales.
                        El tono físico del material o tela puede variar.</span>
                </div>

                <div class="input-group input-group-sm mb-3">
                    <span class="input-group-text" style="background: rgba(30,60,114,0.1); border-color: #1e3c72;">
                        <i class="ri-search-line" style="color: #1e3c72;"></i>
                    </span>
                    <input type="text" id="buscarColorModal" class="form-control" placeholder="Buscar color..."
                        style="border-color: #1e3c72;">
                </div>

                <div id="coloresSwatchGrid" style="max-height: 360px; overflow-y: auto; overflow-x: hidden;">
                </div>
            </div>

            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tallaCatalogoModal" tabindex="-1" aria-labelledby="tallaCatalogoModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 1070;">
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content"
            style="box-shadow: 0 24px 60px rgba(0,0,0,0.55), 0 8px 24px rgba(0,0,0,0.35), 0 0 0 1px rgba(30,60,114,0.22); border-top: 3px solid #00d9a5;">
            <div class="modal-header p-3" style="background-color: #132649 !important;">
                <h5 class="modal-title" style="color: #ffffff !important;" id="tallaCatalogoModalLabel">
                    <i class="ri-t-shirt-line me-2" style="opacity:0.7;"></i>Catálogo de Tallas
                </h5>
                <button type="button" class="btn-close utility-modal-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body p-3" style="overflow-x: hidden;">
                <div class="input-group input-group-sm mb-3">
                    <span class="input-group-text" style="background: rgba(30,60,114,0.1); border-color: #1e3c72;">
                        <i class="ri-search-line" style="color: #1e3c72;"></i>
                    </span>
                    <input type="text" id="buscarTallaModal" class="form-control" placeholder="Buscar talla..."
                        style="border-color: #1e3c72;">
                </div>

                <div id="tallasCatalogoGrid" style="max-height: 360px; overflow-y: auto; overflow-x: hidden;">
                </div>
            </div>

            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancelar
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════════════
     Modal Configurador de Bordados (sobre el modal de cotización #showModal).
     Antes era un offcanvas lateral; se convirtió a modal centrado para mejor
     UX. El z-index del modal y su backdrop se ajustan en main.blade.php
     (show.bs.modal) para quedar por encima del modal padre. El id histórico
     #bordadoOffcanvas se conserva para no tocar todo el JS dependiente.
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade atlantico-modal bordado-modal" id="bordadoOffcanvas" tabindex="-1"
    aria-labelledby="bordadoOffcanvasLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered bordado-modal-dialog">
    <div class="modal-content">

    {{-- Header: identidad del producto que se está configurando --}}
    <div class="bordado-oc-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3 flex-grow-1 overflow-hidden">
            <div class="bordado-oc-icon">
                <i class="ri-scissors-cut-line"></i>
            </div>
            <div class="overflow-hidden">
                <p class="bordado-oc-eyebrow mb-0">Servicio de bordado</p>
                <h6 class="offcanvas-title mb-0 text-truncate" id="bordadoOffcanvasLabel">
                    <span id="bordado-oc-producto">—</span>
                </h6>
                <div class="bordado-oc-color-badge">
                    <span class="bordado-oc-color-dot" id="bordado-oc-color-dot" style="background:#ccc;"></span>
                    <span id="bordado-oc-color-name">Sin color</span>
                </div>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
            aria-label="Cerrar"></button>
    </div>

    {{-- Cuerpo: flex column con zona scrollable interna --}}
    <div class="modal-body p-0 d-flex flex-column" style="overflow: hidden; min-height: 0;">

        {{-- Buscador fijo en la parte superior --}}
        <div class="bordado-oc-search px-3 pt-3 pb-2">
            <div class="input-group input-group-sm">
                <span class="input-group-text bordado-oc-search-icon">
                    <i class="ri-search-line"></i>
                </span>
                <input type="text" id="buscarUbicacionModal" class="form-control"
                    placeholder="Buscar ubicación por nombre...">
            </div>
            <p class="bordado-oc-hint mt-2 mb-0">
                <i class="ri-information-line me-1"></i>
                Activa cada ubicación, asígnale un logo y ajusta precio y cantidad de bordados por prenda.
            </p>
        </div>

        {{-- Zona scrollable: ubicaciones estándar + personalizadas --}}
        <div class="flex-grow-1 overflow-auto px-3 py-3" style="min-height: 0;">

            <p class="bordado-oc-section-label mb-2">
                <i class="ri-map-pin-2-line me-1"></i>Ubicaciones del catálogo
            </p>
            <div id="ubicacionesCatalogoGrid" class="d-flex flex-column gap-2"></div>

            <hr class="bordado-oc-divider my-3">

            <div class="d-flex align-items-center justify-content-between mb-2">
                <p class="bordado-oc-section-label mb-0">
                    <i class="ri-edit-2-line me-1"></i>Ubicaciones personalizadas
                </p>
                <button type="button" class="btn btn-sm btn-atlantico-brand" id="agregarUbicacionPersonalizadaBtn">
                    <i class="ri-add-line me-1"></i>Agregar
                </button>
            </div>
            <div id="ubicacionesPersonalizadasContainer" class="d-flex flex-column gap-2">
                <small class="text-muted">No hay ubicaciones personalizadas.</small>
            </div>

        </div>

        {{-- Footer sticky: resumen de recargo + botones de acción --}}
        <div class="bordado-oc-footer">
            <div class="bordado-oc-summary mb-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted">Ubicaciones activas</span>
                    <span class="fw-semibold small" id="bordado-oc-active-count">0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Recargo unitario</span>
                    <span class="fw-bold bordado-oc-recargo-value" id="resumenRecargoBordadoModal">$0.00</span>
                </div>
                {{-- Equivalente en Bolívares (VES) del servicio de bordado --}}
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <span class="text-muted" style="font-size:0.72rem;">
                        <i class="ri-exchange-dollar-line me-1"></i>Equivalente Bs
                        <span id="resumenRecargoBordadoTasa" class="ms-1" style="opacity:0.75;"></span>
                    </span>
                    <span class="fw-semibold" style="font-size:0.8rem;color:#1e3c72;" id="resumenRecargoBordadoModalBs">Bs 0,00</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-light flex-fill" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-sm btn-atlantico-brand flex-fill" id="aplicarUbicacionesBordadoBtn">
                    <i class="ri-check-line me-1"></i>Aplicar configuración
                </button>
            </div>
        </div>

    </div>{{-- /modal-body --}}
    </div>{{-- /modal-content --}}
  </div>{{-- /modal-dialog --}}
</div>{{-- /modal #bordadoOffcanvas --}}
