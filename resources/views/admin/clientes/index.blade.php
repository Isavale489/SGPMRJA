@extends('admin.layouts.app')

@push('styles')
    <!-- Sweet Alert css-->
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet"
        type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <style>
        .card-body {
            overflow-x: auto;
        }

        /* Estilo para buscador personalizado */
        .search-box {
            position: relative;
        }

        .search-box .search-icon {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            color: #878a99;
        }

        .search-box input {
            padding-left: 30px;
        }

        #clientes-table {
            width: auto !important;
            /* El ancho justo del contenido */
            font-size: 13px;
            min-width: 0 !important;
            /* No forzar ancho mínimo */
        }

        #clientes-table th,
        #clientes-table td {
            padding: 0.35rem 0.5rem;
            vertical-align: middle;
        }

        /* Acciones: Ancho mínimo fijo */
        #clientes-table th:last-child,
        #clientes-table td:last-child {
            width: 80px;
            /* Suficiente para 3 botones */
            text-align: center;
        }

        .btn-purple {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: #fff;
        }

        .btn-purple:hover {
            background-color: #5e35b1;
            border-color: #5e35b1;
            color: #fff;
        }

        /* Revertido: sin reglas personalizadas para SweetAlert2 */
    </style>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Listado de Clientes</h5>
                        <div class="flex-shrink-0 d-flex gap-2">
                            <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn"
                                data-bs-target="#showModal">
                                <i class="ri-add-line align-bottom me-1"></i> Agregar Cliente
                            </button>
                            <a href="{{ route('clientes.reporte.pdf') }}" class="btn btn-danger" target="_blank">
                                <i class="ri-file-pdf-fill align-bottom me-1"></i> Exportar PDF
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="clientes-table" class="table table-bordered table-striped table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles del Cliente -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header con gradiente marca Atlantico -->
                <div class="modal-header py-3"
                    style="background: linear-gradient(135deg, #1e3c72 0%, #2ecc71 50%, #00d9a5 100%);">
                    <h5 class="modal-title text-white d-flex align-items-center">
                        <i class="ri-user-star-line me-2 fs-4"></i>Detalles del Cliente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Columna Izquierda: Datos Personales -->
                        <div class="col-lg-6">
                            <!-- Card Datos Personales -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header border-0" style="background: rgba(0, 217, 165, 0.1);">
                                    <h6 class="mb-0" style="color: #00d9a5;">
                                        <i class="ri-user-line me-2"></i>Información Personal
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
                                                    <span class="fw-semibold" id="view-nombre">-</span>
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
                                                    <span class="fw-semibold" id="view-apellido">-</span>
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
                                                    <span class="fw-semibold" id="view-documento">-</span>
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
                                                    <small class="text-muted d-block">Tipo Cliente</small>
                                                    <span class="fw-semibold" id="view-tipo_cliente">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Contacto -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header border-0" style="background: rgba(46, 204, 113, 0.1);">
                                    <h6 class="mb-0" style="color: #2ecc71;">
                                        <i class="ri-contacts-line me-2"></i>Información de Contacto
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-mail-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Email</small>
                                                    <span class="fw-semibold" id="view-email">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(0, 217, 165, 0.1);">
                                                    <i class="ri-phone-line" style="color: #00d9a5;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Teléfono</small>
                                                    <span class="fw-semibold" id="view-telefono">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Ubicación y Estado -->
                        <div class="col-lg-6">
                            <!-- Card Ubicación -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header border-0" style="background: rgba(30, 60, 114, 0.1);">
                                    <h6 class="mb-0" style="color: #1e3c72;">
                                        <i class="ri-map-pin-line me-2"></i>Ubicación
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(46, 204, 113, 0.1);">
                                                    <i class="ri-home-4-line" style="color: #2ecc71;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Dirección</small>
                                                    <span class="fw-semibold" id="view-direccion">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-government-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Estado</small>
                                                    <span class="fw-semibold" id="view-estado-territorial">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(0, 217, 165, 0.1);">
                                                    <i class="ri-building-line" style="color: #00d9a5;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Ciudad</small>
                                                    <span class="fw-semibold" id="view-ciudad">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Estatus -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header border-0" style="background: rgba(0, 217, 165, 0.1);">
                                    <h6 class="mb-0" style="color: #00d9a5;">
                                        <i class="ri-information-line me-2"></i>Estatus del Cliente
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-checkbox-circle-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Estatus</small>
                                                    <span class="fw-semibold" id="view-estatus">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(46, 204, 113, 0.1);">
                                                    <i class="ri-calendar-line" style="color: #2ecc71;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Fecha Registro</small>
                                                    <span class="fw-semibold" id="view-created">-</span>
                                                </div>
                                            </div>
                                        </div>
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
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar/editar -->
    <div class="modal fade" id="showModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="modalTitle">Agregar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="clienteForm">
                    <div class="modal-body">
                        <input type="hidden" id="id-field" />

                        <!-- Fila 1: Documento + Tipo Cliente + Estatus -->
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <label for="documento-field" class="form-label required">Documento (Cédula o RIF)</label>
                                <div class="input-group">
                                    <select class="form-select" id="documento-prefix-field" style="max-width: 70px;">
                                        <option value="V-">V-</option>
                                        <option value="J-">J-</option>
                                        <option value="E-">E-</option>
                                        <option value="G-">G-</option>
                                    </select>
                                    <input type="text" id="documento-number-field" class="form-control"
                                        placeholder="Nro. documento" maxlength="10" required />
                                </div>
                                <input type="hidden" id="documento-field" name="documento" />
                                <small class="text-muted">Máximo 10 dígitos</small>
                                <div id="documento-error" class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="tipo_cliente-field" class="form-label required">Tipo de Cliente</label>
                                <select id="tipo_cliente-field" name="tipo_cliente" class="form-select" required>
                                    <option value="">Seleccione</option>
                                    <option value="natural">Natural</option>
                                    <option value="juridico">Jurídico</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label d-block">Estatus</label>
                                <div class="form-check form-switch form-switch-success mt-2">
                                    <input type="hidden" name="estatus" value="0" />
                                    <input class="form-check-input" type="checkbox" role="switch" id="estatus-field"
                                        name="estatus" value="1" checked />
                                    <label class="form-check-label" for="estatus-field" id="estatus-label">Activo</label>
                                </div>
                            </div>
                        </div>

                        <!-- Fila 2: Nombre + Apellido -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nombre-field" class="form-label required">Nombre</label>
                                <input type="text" id="nombre-field" name="nombre" class="form-control" placeholder="Nombre"
                                    maxlength="100" required />
                                <div id="nombre-error" class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="apellido-field" class="form-label required">Apellido</label>
                                <input type="text" id="apellido-field" name="apellido" class="form-control"
                                    placeholder="Apellido" maxlength="100" required />
                                <div id="apellido-error" class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Fila 3: Email + Teléfono -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email-field" class="form-label">Email</label>
                                <input type="email" id="email-field" name="email" class="form-control"
                                    placeholder="correo@ejemplo.com" />
                                <div id="email-error" class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="telefono-field" class="form-label required">Teléfono</label>
                                <div class="input-group">
                                    <select class="form-select" id="telefono-prefix-field"
                                        style="max-width: 100px; min-width: 100px;">
                                        <option value="0412">0412</option>
                                        <option value="0422">0422</option>
                                        <option value="0414">0414</option>
                                        <option value="0424" selected>0424</option>
                                        <option value="0416">0416</option>
                                        <option value="0426">0426</option>
                                    </select>
                                    <input type="text" id="telefono-number-field" class="form-control" placeholder="1234567"
                                        maxlength="7" required />
                                </div>
                                <input type="hidden" id="telefono-field" name="telefono" />
                                <div id="telefono-error" class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Fila 4: Dirección -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="direccion-field" class="form-label required">Dirección</label>
                                <input type="text" id="direccion-field" name="direccion" class="form-control"
                                    placeholder="Dirección completa" maxlength="500" required />
                            </div>
                        </div>

                        <!-- Fila 5: Estado (Territorio) + Ciudad -->
                        <div class="row">
                            <div class="col-md-6">
                                <label for="estado_territorial-field" class="form-label required">Estado</label>
                                <select name="estado_territorial" id="estado_territorial-field" class="form-select"
                                    required>
                                    <option value="">Seleccione estado</option>
                                    <option value="Amazonas">Amazonas</option>
                                    <option value="Anzoátegui">Anzoátegui</option>
                                    <option value="Apure">Apure</option>
                                    <option value="Aragua">Aragua</option>
                                    <option value="Barinas">Barinas</option>
                                    <option value="Bolívar">Bolívar</option>
                                    <option value="Carabobo">Carabobo</option>
                                    <option value="Cojedes">Cojedes</option>
                                    <option value="Delta Amacuro">Delta Amacuro</option>
                                    <option value="Distrito Capital">Distrito Capital</option>
                                    <option value="Falcón">Falcón</option>
                                    <option value="Guárico">Guárico</option>
                                    <option value="La Guaira">La Guaira</option>
                                    <option value="Lara">Lara</option>
                                    <option value="Mérida">Mérida</option>
                                    <option value="Miranda">Miranda</option>
                                    <option value="Monagas">Monagas</option>
                                    <option value="Nueva Esparta">Nueva Esparta</option>
                                    <option value="Portuguesa">Portuguesa</option>
                                    <option value="Sucre">Sucre</option>
                                    <option value="Táchira">Táchira</option>
                                    <option value="Trujillo">Trujillo</option>
                                    <option value="Yaracuy">Yaracuy</option>
                                    <option value="Zulia">Zulia</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="ciudad-field" class="form-label required">Municipio</label>
                                <select name="ciudad" id="ciudad-field" class="form-select" required>
                                    <option value="">Primero seleccione un estado</option>
                                </select>
                            </div>
                        </div>


                    </div>
                    <div class="modal-footer">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                            <button type="button" class="btn btn-success" id="add-btn">Agregar</button>
                            <button type="button" class="btn btn-success" id="edit-btn"
                                style="display: none;">Actualizar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ URL::asset('/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/municipios-venezuela.js') }}"></script>
    <script>
        $(function () {
            // Activa tooltips para todos los elementos con atributo title
            $(document).on('mouseenter', '[title]', function () {
                $(this).tooltip({ container: 'body' }).tooltip('show');
            });
            $(document).on('mouseleave', '[title]', function () {
                $(this).tooltip('dispose');
            });
        });

        // Validación y formato para el campo de teléfono
        $(document).on('input', '#telefono-field', function () {
            let value = this.value.replace(/[^0-9]/g, '');
            if (value.length > 4) {
                value = value.slice(0, 4) + '-' + value.slice(4, 11);
            }
            this.value = value.slice(0, 12);
        });

        // === Capitalizar solo la primera letra del campo dirección ===
        $(document).on('blur', '#direccion-field', function () {
            var val = $(this).val();
            if (val && val.length > 0) {
                $(this).val(val.charAt(0).toUpperCase() + val.slice(1));
            }
        });

        // Validación en tiempo real para nombre (solo letras y espacios)
        $(document).on('input', '#nombre-field', function () {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        });

        // Validación en tiempo real para apellido (solo letras y espacios)
        $(document).on('input', '#apellido-field', function () {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        });

        // Validación en tiempo real para documento (solo números)
        $(document).on('input', '#documento-number-field', function () {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        });

        // Validación onblur para nombre
        $(document).on('blur', '#nombre-field', function () {
            let value = $(this).val().trim();
            if (value.length < 2) {
                $(this).addClass('is-invalid');
                $('#nombre-error').text('El nombre debe tener al menos 2 caracteres.').show();
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $('#nombre-error').hide();
            }
        });

        // Validación onblur para apellido
        $(document).on('blur', '#apellido-field', function () {
            let value = $(this).val().trim();
            if (value.length > 0 && value.length < 2) {
                $(this).addClass('is-invalid');
                $('#apellido-error').text('El apellido debe tener al menos 2 caracteres.').show();
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $('#apellido-error').hide();
            }
        });

        // Validación onblur para documento
        // Validación onblur para documento
        $(document).on('blur', '#documento-number-field', function () {
            let value = $(this).val().trim();
            let $input = $(this);
            let $error = $('#documento-error');
            let isEditMode = $('#id-field').val() !== ''; // Comprobar si estamos en edición

            if (value.length < 6) {
                $input.addClass('is-invalid');
                $error.text('El documento debe tener al menos 6 dígitos.').show();
            } else {
                // Si la longitud es válida y NO estamos en edición, verificamos duplicados
                if (!isEditMode) {
                    $.ajax({
                        url: "{{ route('clientes.check-documento') }}",
                        method: 'GET',
                        data: { numero: value },
                        success: function (response) {
                            if (response.exists) {
                                $input.addClass('is-invalid');
                                $error.text('Este cliente ya se encuentra registrado.').show();
                                // Opcional: Deshabilitar el botón de agregar
                                $('#add-btn').prop('disabled', true);
                            } else {
                                $input.removeClass('is-invalid').addClass('is-valid');
                                $error.hide();
                                $('#add-btn').prop('disabled', false);
                            }
                        },
                        error: function () {
                            console.error('Error al verificar documento');
                        }
                    });
                } else {
                    $input.removeClass('is-invalid').addClass('is-valid');
                    $error.hide();
                }
            }
        });

        // Validación onblur para teléfono
        $(document).on('blur', '#telefono-field', function () {
            let value = $(this).val().trim();
            let regex = /^[0-9]{4}-[0-9]{7}$/;
            if (!regex.test(value)) {
                $(this).addClass('is-invalid');
                $('#telefono-error').text('El teléfono debe tener el formato 0424-1234567.').show();
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $('#telefono-error').hide();
            }
        });

        // Validación onblur para email
        $(document).on('blur', '#email-field', function () {
            let value = $(this).val().trim();
            let $input = $(this);
            let $error = $('#email-error');
            let isEditMode = $('#id-field').val() !== '';
            let regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (value.length > 0) {
                if (!regex.test(value)) {
                    $input.addClass('is-invalid');
                    $error.text('Ingrese un email válido.').show();
                } else {
                    // Si formato es válido y NO es edición, verificar duplicado
                    if (!isEditMode) {
                        $.ajax({
                            url: "{{ route('clientes.check-email') }}",
                            method: 'GET',
                            data: { email: value },
                            success: function (response) {
                                if (response.exists) {
                                    $input.addClass('is-invalid');
                                    $error.text('Este correo ya está registrado.').show();
                                    $('#add-btn').prop('disabled', true);
                                } else {
                                    $input.removeClass('is-invalid').addClass('is-valid');
                                    $error.hide();
                                    $('#add-btn').prop('disabled', false);
                                }
                            },
                            error: function () {
                                console.error('Error al verificar email');
                            }
                        });
                    } else {
                        // En modo edición no validamos duplicado (limitación por now)
                        $input.removeClass('is-invalid').addClass('is-valid');
                        $error.hide();
                    }
                }
            } else {
                // Si está vacío, quitar clases (o mostrar error si required)
                // Es opcional en el html? No tiene "required" en el html form, pero tiene validator?
                // En el HTML no tiene 'required'.
                $input.removeClass('is-invalid').removeClass('is-valid');
                $error.hide();
            }
        });

        // Limpiar validaciones al abrir modal
        $('#showModal').on('show.bs.modal', function () {
            $('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            $('.invalid-feedback').hide();
        });
    </script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="{{ asset('assets/js/form-validation.js') }}"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });
            function generateButtons(clienteId) {
                return `
                                                                                    <div class="d-flex gap-2 justify-content-center">
                                                                                        <button class="btn btn-sm btn-soft-info view-item-btn" data-id="${clienteId}" title="Ver">
                                                                                            <i class="ri-eye-fill"></i>
                                                                                        </button>
                                                                                        <button class="btn btn-sm btn-soft-success edit-item-btn" data-id="${clienteId}" title="Editar">
                                                                                            <i class="ri-pencil-fill"></i>
                                                                                        </button>
                                                                                        <button class="btn btn-sm btn-soft-danger remove-item-btn" data-id="${clienteId}" title="Eliminar">
                                                                                            <i class="ri-delete-bin-fill"></i>
                                                                                        </button>
                                                                                    </div>
                                                                                `;
            }
            var table = $('#clientes-table').DataTable({
                ajax: { url: "{{ route('clientes.data') }}", dataSrc: 'data' },
                columns: [
                    { data: 'documento' },
                    {
                        data: null,
                        render: function (data, type, row) {
                            return row.nombre + ' ' + row.apellido;
                        }
                    },
                    { data: 'tipo_cliente', render: function (data) { return data === 'natural' ? 'Natural' : 'Jurídico'; } },
                    { data: 'email' },
                    { data: 'telefono' },
                    { data: null, render: function (data, type, row) { return generateButtons(row.id); } }
                ],
                order: [[0, 'asc']], // Ordenar por documento (primera columna)
                dom: 'frtip',
                buttons: [
                    {
                        extend: 'copy',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4]
                        }
                    },
                    {
                        extend: 'excel',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4]
                        }
                    }
                ],
                language: {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sZeroRecords": "No se encontraron resultados",
                    "sEmptyTable": "Ningún dato disponible en esta tabla",
                    "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix": "",
                    "sSearch": "Buscar:",
                    "sUrl": "",
                    "sInfoThousands": ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst": "Primero",
                        "sLast": "Último",
                        "sNext": "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    },
                    "buttons": {
                        "copy": "Copiar",
                        "csv": "CSV",
                        "excel": "Excel",
                        "pdf": "PDF",
                        "print": "Imprimir",
                        "colvis": "Visibilidad de Columna"
                    }
                }
            });


            // Ajustar columnas cuando se redimensiona la ventana
            $(window).on('resize', function () {
                table.columns.adjust();
            });
            // Ajustar después de carga inicial
            setTimeout(function () {
                table.columns.adjust();
            }, 100);
            function resetForm() {
                $("#clienteForm").trigger("reset");
                $("#id-field").val("");
                $("#modalTitle").text("Agregar Cliente");
                $("#add-btn").show();
                $("#edit-btn").hide();
                $("#documento-prefix-field").val("V-");
                $("#documento-prefix-field").prop('disabled', false); // Habilitar
                $("#documento-number-field").val("");
                $("#documento-number-field").prop('disabled', false); // Habilitar
                // Reset teléfono
                $("#telefono-prefix-field").val("0424");
                $("#telefono-number-field").val("");
            }
            function setEditMode() {
                $("#modalTitle").text("Actualizar Cliente");
                $("#add-btn").hide();
                $("#edit-btn").show();
                // Bloquear edición de documento
                $("#documento-prefix-field").prop('disabled', true);
                $("#documento-number-field").prop('disabled', true);
            }
            $("#create-btn").click(function () { resetForm(); });
            $("#showModal").on('hidden.bs.modal', function () { resetForm(); });

            // Listener para actualizar label del checkbox de estatus
            $("#estatus-field").on('change', function () {
                if ($(this).is(':checked')) {
                    $("#estatus-label").text('Activo');
                } else {
                    $("#estatus-label").text('Inactivo');
                }
            });

            // Dropdown dependiente: Poblar municipios cuando cambia el estado
            $("#estado_territorial-field").on('change', function () {
                const estado = $(this).val();
                const municipios = getMunicipios(estado);
                const ciudadSelect = $("#ciudad-field");

                // Limpiar opciones anteriores
                ciudadSelect.empty();

                if (estado === '') {
                    ciudadSelect.append('<option value="">Primero seleccione un estado</option>');
                } else {
                    ciudadSelect.append('<option value="">Seleccione municipio</option>');
                    municipios.forEach(function (municipio) {
                        ciudadSelect.append('<option value="' + municipio + '">' + municipio + '</option>');
                    });
                }
            });

            const validator = new FormValidator('clienteForm');

            $('#add-btn').click(function (e) {
                e.preventDefault();

                // Validar formulario antes de enviar
                if (!validator.validateAll()) {
                    return;
                }

                // Se deshabilita el botón para evitar múltiples envíos
                $(this).prop('disabled', true);
                $("#clienteForm").submit();
            });

            $("#clienteForm").on("submit", function (e) {
                e.preventDefault();
                var id = $("#id-field").val();
                var url = id ? "{{ route('clientes.update', ':id') }}".replace(':id', id) : "{{ route('clientes.store') }}";
                var method = id ? "PUT" : "POST";
                var documentoCompleto = $("#documento-prefix-field").val() + $("#documento-number-field").val();
                $("#documento-field").val(documentoCompleto);
                // Concatenar teléfono: prefijo-número
                var telefonoCompleto = $("#telefono-prefix-field").val() + "-" + $("#telefono-number-field").val();
                $("#telefono-field").val(telefonoCompleto);
                var formData = $(this).serialize();
                if (method === 'PUT') { formData += '&_method=PUT'; }
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        $("#showModal").modal("hide");
                        $("#clienteForm").trigger("reset");
                        table.ajax.reload();
                        Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.message, showConfirmButton: false, timer: 2000 });
                        $('#add-btn').prop('disabled', false); // Re-enable button on success
                    },
                    error: function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON.message });
                        $('#add-btn').prop('disabled', false); // Re-enable button on error
                    }
                });
            });
            $(document).on("click", ".view-item-btn", function () {
                var id = $(this).data("id");
                $.get("{{ route('clientes.show', '') }}/" + id, function (data) {
                    $("#viewModal").modal("show");
                    $("#view-nombre").text(data.nombre || 'N/A');
                    $("#view-apellido").text(data.apellido || 'N/A');
                    $("#view-tipo_cliente").text(data.tipo_cliente === 'natural' ? 'Natural' : 'Jurídico');
                    $("#view-email").text(data.email || 'N/A');
                    $("#view-telefono").text(data.telefono || 'N/A');
                    $("#view-documento").text(data.documento || 'N/A');
                    $("#view-direccion").text(data.direccion || 'N/A');
                    $("#view-estado-territorial").text(data.estado_territorial || 'N/A');
                    $("#view-ciudad").text(data.ciudad || 'N/A');
                    $("#view-estatus").html(data.estatus == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>');
                    $("#view-created").text(data.created_at || 'N/A');
                });
            });
            $(document).on("click", ".edit-item-btn", function () {
                var id = $(this).data("id");
                $.get("{{ route('clientes.edit', ':id') }}".replace(':id', id), function (data) {
                    setEditMode();
                    $("#id-field").val(data.id);
                    $("#nombre-field").val(data.nombre || '');
                    $("#apellido-field").val(data.apellido || '');
                    $("#tipo_cliente-field").val(data.tipo_cliente);
                    $("#email-field").val(data.email || '');
                    // Separar teléfono en prefijo y número
                    if (data.telefono && data.telefono.includes('-')) {
                        var telParts = data.telefono.split('-');
                        $("#telefono-prefix-field").val(telParts[0]);
                        $("#telefono-number-field").val(telParts[1]);
                    } else if (data.telefono) {
                        // Si no tiene guión, asumir formato 0424XXXXXXX
                        $("#telefono-prefix-field").val(data.telefono.slice(0, 4));
                        $("#telefono-number-field").val(data.telefono.slice(4));
                    }
                    if (data.documento) {
                        $("#documento-prefix-field").val(data.documento.slice(0, 2));
                        $("#documento-number-field").val(data.documento.slice(2));
                    }
                    $("#direccion-field").val(data.direccion || '');

                    // Primero establecer el estado
                    $("#estado_territorial-field").val(data.estado_territorial || '');

                    // Poblar los municipios del estado seleccionado
                    const estado = data.estado_territorial || '';
                    const municipios = getMunicipios(estado);
                    const ciudadSelect = $("#ciudad-field");
                    ciudadSelect.empty();
                    if (estado === '') {
                        ciudadSelect.append('<option value="">Primero seleccione un estado</option>');
                    } else {
                        ciudadSelect.append('<option value="">Seleccione municipio</option>');
                        municipios.forEach(function (municipio) {
                            ciudadSelect.append('<option value="' + municipio + '">' + municipio + '</option>');
                        });
                    }

                    // Ahora seleccionar el municipio guardado
                    $("#ciudad-field").val(data.ciudad || '');
                    // Manejar checkbox de estatus
                    if (data.estatus == 1) {
                        $("#estatus-field").prop('checked', true);
                        $("#estatus-label").text('Activo');
                    } else {
                        $("#estatus-field").prop('checked', false);
                        $("#estatus-label").text('Inactivo');
                    }
                    $("#showModal").modal("show");
                });
            });
            $(document).on("click", ".remove-item-btn", function () {
                var id = $(this).data("id");
                window.scrollTo(0, 0);
                document.activeElement.blur();
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡No podrás revertir esto!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-primary w-xs me-2',
                        cancelButton: 'btn btn-danger w-xs'
                    },
                    buttonsStyling: false,
                    showCloseButton: true
                }).then(function (result) {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('clientes.destroy', ':id') }}".replace(':id', id),
                            method: "DELETE",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name=\'csrf-token\']').attr('content')
                            },
                            success: function (response) {
                                table.ajax.reload();
                                // Mostrar mensaje con warning si el cliente tenía relaciones
                                if (response.warning) {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Cliente Eliminado',
                                        html: '<p>' + response.message + '</p><p class="text-muted small">' + response.warning + '</p>'
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Eliminado',
                                        text: response.message
                                    });
                                }
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON.message || 'Ocurrió un error al eliminar el cliente'
                                });
                            }
                        });
                    }
                });
            });
            $("#create-btn").click(function () {
                $('#id-field').val('');
                $('#clienteForm')[0].reset();
                $('#modalTitle').text('Agregar Cliente');
                $('#add-btn').show();
                $('#edit-btn').hide();
                validator.resetValidation();
            });
            $("#edit-btn").on("click", function () { $("#clienteForm").submit(); });
        });
    </script>
@endpush