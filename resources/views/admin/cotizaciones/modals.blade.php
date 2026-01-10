<!-- Modal para ver detalles de Cotización -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <!-- Header con gradiente marca Atlantico -->
            <div class="modal-header py-3"
                style="background: linear-gradient(135deg, #1e3c72 0%, #2ecc71 50%, #00d9a5 100%);">
                <h5 class="modal-title text-white d-flex align-items-center">
                    <i class="ri-file-list-3-line me-2 fs-4"></i>Detalles de la Cotización
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <!-- Columna Izquierda -->
                    <div class="col-lg-6">
                        <!-- Card Datos del Cliente -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header border-0" style="background: rgba(0, 217, 165, 0.1);">
                                <h6 class="mb-0" style="color: #00d9a5;">
                                    <i class="ri-user-star-line me-2"></i>Información del Cliente
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                <i class="ri-user-line" style="color: #1e3c72;"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Nombre</small>
                                                <span class="fw-semibold" id="view-cliente-nombre">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; background: rgba(0, 217, 165, 0.1);">
                                                <i class="ri-user-follow-line" style="color: #00d9a5;"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Apellido</small>
                                                <span class="fw-semibold" id="view-cliente-apellido">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; background: rgba(46, 204, 113, 0.1);">
                                                <i class="ri-bank-card-line" style="color: #2ecc71;"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Documento</small>
                                                <span class="fw-semibold" id="view-ci-rif">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; background: rgba(0, 217, 165, 0.1);">
                                                <i class="ri-phone-line" style="color: #00d9a5;"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Teléfono</small>
                                                <span class="fw-semibold" id="view-cliente-telefono">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                <i class="ri-mail-line" style="color: #1e3c72;"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Email</small>
                                                <span class="fw-semibold" id="view-cliente-email">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Datos de la Cotización -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header border-0" style="background: rgba(46, 204, 113, 0.1);">
                                <h6 class="mb-0" style="color: #2ecc71;">
                                    <i class="ri-calendar-todo-line me-2"></i>Datos de la Cotización
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                <i class="ri-calendar-line" style="color: #1e3c72;"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Fecha Cotización</small>
                                                <span class="fw-semibold" id="view-fecha-cotizacion">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; background: rgba(0, 217, 165, 0.1);">
                                                <i class="ri-calendar-check-line" style="color: #00d9a5;"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Fecha Validez</small>
                                                <span class="fw-semibold" id="view-fecha-validez">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; background: rgba(46, 204, 113, 0.1);">
                                                <i class="ri-flag-line" style="color: #2ecc71;"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Estado</small>
                                                <span class="fw-semibold" id="view-estado">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                <i class="ri-user-settings-line" style="color: #1e3c72;"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Creado por</small>
                                                <span class="fw-semibold" id="view-usuario-creador">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Total -->
                        <div class="card border-0 shadow-sm"
                            style="background: linear-gradient(135deg, #1e3c72 0%, #00d9a5 100%);">
                            <div class="card-body text-center py-4">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="ri-money-dollar-circle-line text-white me-3" style="font-size: 3rem;"></i>
                                    <div class="text-start">
                                        <small class="text-white-50 d-block text-uppercase">Total de la
                                            Cotización</small>
                                        <h2 class="text-white mb-0 fw-bold" id="view-total">$0.00</h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Productos -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header border-0" style="background: rgba(30, 60, 114, 0.1);">
                                <h6 class="mb-0" style="color: #1e3c72;">
                                    <i class="ri-shopping-bag-3-line me-2"></i>Productos de la Cotización
                                </h6>
                            </div>
                            <div class="card-body" style="max-height: 450px; overflow-y: auto;">
                                <div id="view-productos-container">
                                    <!-- Productos se cargan dinámicamente -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cerrar
                </button>
                <a href="#" id="view-pdf-btn" class="btn btn-warning" target="_blank">
                    <i class="ri-file-pdf-line me-1"></i>Descargar PDF
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar/editar Cotización -->
<div class="modal fade" id="showModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="modalTitle">Agregar Cotización</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cotizacionForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="id-field" />
                    <input type="hidden" id="cliente-id-field" name="cliente_id" />
                    <div class="row">
                        <!-- Columna Izquierda: Datos del Cliente -->
                        <div class="col-lg-6">
                            <div class="card shadow-lg border-dark mb-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Datos del Cliente</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3 position-relative">
                                                <label for="cliente-nombre-field" class="form-label">Nombre
                                                    Cliente</label>
                                                <div class="input-group">
                                                    <input type="text" id="cliente-nombre-field" name="cliente_nombre"
                                                        class="form-control"
                                                        placeholder="Buscar o escribir nombre del cliente"
                                                        autocomplete="off" required />
                                                    <button type="button" class="btn btn-outline-success"
                                                        id="open-add-cliente-modal" title="Agregar nuevo cliente">
                                                        <i class="ri-user-add-line"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div id="cliente-autocomplete-list"
                                                class="list-group position-absolute w-100" style="z-index: 1050;"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="cliente-apellido-field" class="form-label">Apellido
                                                    Cliente</label>
                                                <input type="text" id="cliente-apellido-field" name="cliente_apellido"
                                                    class="form-control" placeholder="Apellido del cliente" readonly />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="ci-rif-field" class="form-label">Documento de
                                                    identidad</label>
                                                <div class="input-group">
                                                    <select class="form-select" id="ci-rif-prefix-field"
                                                        name="rif_prefix" required>
                                                        <option value="V-">V-</option>
                                                        <option value="J-">J-</option>
                                                        <option value="E-">E-</option>
                                                        <option value="G-">G-</option>
                                                    </select>
                                                    <input type="text" id="ci-rif-number-field" name="rif_number"
                                                        class="form-control" placeholder="Número de identificación"
                                                        required />
                                                    <input type="hidden" id="ci-rif-full-field" name="ci_rif" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="cliente-telefono-field" class="form-label required">Teléfono
                                                    Cliente</label><input type="text" id="cliente-telefono-field"
                                                    name="cliente_telefono" class="form-control"
                                                    placeholder="Teléfono del cliente" required />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="cliente-email-field" class="form-label">Email
                                                    Cliente</label>
                                                <input type="email" id="cliente-email-field" name="cliente_email"
                                                    class="form-control" placeholder="correo@ejemplo.com" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="fecha-cotizacion-field" class="form-label required">Fecha de
                                                    Cotización</label><input type="date" id="fecha-cotizacion-field"
                                                    name="fecha_cotizacion" class="form-control" required />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="fecha-validez-field" class="form-label required">Fecha de
                                                    Validez</label><input type="date" id="fecha-validez-field"
                                                    name="fecha_validez" class="form-control" required />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card shadow-lg border-dark mb-3" id="estado-field-wrapper"
                                style="display: none;">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Estado de la Cotización</h5>
                                </div>
                                <div class="card-body">
                                    <label for="estado-field" class="form-label">Estado</label>
                                    <select id="estado-field" name="estado" class="form-control">
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="Procesando">Procesando</option>
                                        <option value="Completado">Completado</option>
                                        <option value="Cancelado">Cancelado</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Sección de Total de la Cotización -->
                            <div class="card shadow-lg border-dark mb-2">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="ri-currency-line align-bottom me-1"></i> Total
                                        de la Cotización</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-2">
                                                <label for="total-display-field" class="form-label"><i
                                                        class="ri-currency-line align-bottom me-1"></i> Total de la
                                                    Cotización ($)</label>
                                                <input type="text" id="total-display-field" class="form-control"
                                                    readonly />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Columna Derecha: Productos de la Cotización y Estado -->
                        <div class="col-lg-6">
                            <div class="card shadow-lg border-dark mb-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Productos de la Cotización</h5>
                                </div>
                                <div class="card-body">
                                    <div id="productos-container">
                                        <!-- Aquí se agregarán dinámicamente los campos de producto -->
                                    </div>
                                    <button type="button" class="btn btn-sm btn-info mt-2"
                                        id="add-producto-item">Agregar Producto</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-success" id="add-btn">Agregar</button>
                        <button type="submit" class="btn btn-success" id="edit-btn"
                            style="display: none;">Actualizar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Agregar/Editar Cliente (reutilizado) -->
<div class="modal fade" id="modalAddCliente" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="modalClienteTitle">Agregar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="cliente-modal-errors" class="alert alert-danger d-none" role="alert"></div>
            <form id="clienteFormCotizacion">
                <div class="modal-body">
                    <input type="hidden" id="id-field-cliente" />
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre-field-cliente" class="form-label required">Nombre</label><input
                                    type="text" id="nombre-field-cliente" name="nombre" class="form-control"
                                    placeholder="Nombre" required />
                            </div>
                            <div class="mb-3">
                                <label for="apellido-field-cliente" class="form-label">Apellido</label><input
                                    type="text" id="apellido-field-cliente" name="apellido" class="form-control"
                                    placeholder="Apellido" />
                            </div>
                            <div class="mb-3">
                                <label for="tipo_cliente-field-cliente" class="form-label required">Tipo de
                                    Cliente</label><select id="tipo_cliente-field-cliente" name="tipo_cliente"
                                    class="form-control" required>
                                    <option value="">Seleccione tipo</option>
                                    <option value="natural">Natural</option>
                                    <option value="juridico">Jurídico</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="email-field-cliente" class="form-label">Email</label>
                                <input type="email" id="email-field-cliente" name="email" class="form-control"
                                    placeholder="Email" />
                            </div>
                            <div class="mb-3">
                                <label for="telefono-field-cliente" class="form-label required">Teléfono</label><input
                                    type="text" id="telefono-field-cliente" name="telefono" class="form-control"
                                    placeholder="Teléfono" required />
                            </div>
                            <div class="mb-3">
                                <label for="documento-field-cliente" class="form-label">Documento (Cédula o RIF)</label>
                                <div class="input-group">
                                    <select class="form-select" id="documento-prefix-field-cliente"
                                        style="max-width: 80px;">
                                        <option value="V-">V-</option>
                                        <option value="J-">J-</option>
                                        <option value="E-">E-</option>
                                        <option value="G-">G-</option>
                                    </select>
                                    <input type="text" id="documento-number-field-cliente" class="form-control"
                                        placeholder="Número de documento" required />
                                </div>
                                <input type="hidden" id="documento-field-cliente" name="documento" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="direccion-field-cliente" class="form-label required">Dirección</label><input
                                    type="text" id="direccion-field-cliente" name="direccion" class="form-control"
                                    placeholder="Dirección" required />
                            </div>
                            <div class="mb-3">
                                <label for="ciudad-field-cliente" class="form-label required">Ciudad</label><input
                                    type="text" id="ciudad-field-cliente" name="ciudad" class="form-control"
                                    placeholder="Ciudad" required />
                            </div>
                            <div class="mb-3">
                                <label for="estado-field-cliente" class="form-label required">Estado</label><select
                                    name="estado" id="estado-field-cliente" class="form-control form-select" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-success" id="add-btn-cliente">Agregar</button>
                        <button type="button" class="btn btn-success" id="edit-btn-cliente"
                            style="display: none;">Actualizar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>