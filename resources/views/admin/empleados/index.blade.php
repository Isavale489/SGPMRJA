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

        #empleados-table {
            width: 100% !important;
            font-size: 13px;
            min-width: 1200px;
        }

        #empleados-table th,
        #empleados-table td {
            padding: 0.35rem 0.5rem;
            vertical-align: middle;
        }

        #empleados-table th:last-child,
        #empleados-table td:last-child {
            width: 48px;
            min-width: 40px;
            max-width: 60px;
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
    </style>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Listado de Empleados</h5>
                        <div class="flex-shrink-0 d-flex gap-2">
                            <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn"
                                data-bs-target="#showModal">
                                <i class="ri-add-line align-bottom me-1"></i> Agregar Empleado
                            </button>
                            <a href="{{ route('empleados.reporte.pdf') }}" class="btn btn-danger" target="_blank">
                                <i class="ri-file-pdf-fill align-bottom me-1"></i> Exportar PDF
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="empleados-table" class="table table-bordered table-striped table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre Completo</th>
                                <th>Documento</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Cargo</th>
                                <th>Departamento</th>
                                <th>Fecha Ingreso</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles -->
    <!-- Modal para ver detalles del Empleado -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header con gradiente marca Atlantico -->
                <div class="modal-header py-3"
                    style="background: linear-gradient(135deg, #1e3c72 0%, #2ecc71 50%, #00d9a5 100%);">
                    <h5 class="modal-title text-white d-flex align-items-center">
                        <i class="ri-user-settings-line me-2 fs-4"></i>Detalles del Empleado
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
                                        <i class="ri-user-line me-2"></i>Datos Personales
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-user-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Nombre Completo</small>
                                                    <span class="fw-semibold" id="view-nombre-completo">-</span>
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
                                                    style="width: 32px; height: 32px; background: rgba(0, 217, 165, 0.1);">
                                                    <i class="ri-user-heart-line" style="color: #00d9a5;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Género</small>
                                                    <span class="fw-semibold" id="view-genero">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-cake-2-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Fecha Nacimiento</small>
                                                    <span class="fw-semibold" id="view-fecha-nacimiento">-</span>
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
                                        <i class="ri-contacts-line me-2"></i>Contacto y Ubicación
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-6">
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
                                        <div class="col-6">
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
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-building-line" style="color: #1e3c72;"></i>
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
                        </div>

                        <!-- Columna Derecha: Datos Laborales -->
                        <div class="col-lg-6">
                            <!-- Card Datos Laborales -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header border-0" style="background: rgba(30, 60, 114, 0.1);">
                                    <h6 class="mb-0" style="color: #1e3c72;">
                                        <i class="ri-briefcase-line me-2"></i>Datos Laborales
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(46, 204, 113, 0.1);">
                                                    <i class="ri-hashtag" style="color: #2ecc71;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Código</small>
                                                    <span class="fw-semibold" id="view-codigo">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(0, 217, 165, 0.1);">
                                                    <i class="ri-user-star-line" style="color: #00d9a5;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Cargo</small>
                                                    <span class="fw-semibold" id="view-cargo">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-building-2-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Departamento</small>
                                                    <span class="fw-semibold" id="view-departamento">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Estado -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header border-0" style="background: rgba(0, 217, 165, 0.1);">
                                    <h6 class="mb-0" style="color: #00d9a5;">
                                        <i class="ri-information-line me-2"></i>Estado del Empleado
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(46, 204, 113, 0.1);">
                                                    <i class="ri-calendar-check-line" style="color: #2ecc71;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Fecha Ingreso</small>
                                                    <span class="fw-semibold" id="view-fecha-ingreso">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                                    <i class="ri-checkbox-circle-line" style="color: #1e3c72;"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Estado</small>
                                                    <span class="fw-semibold" id="view-estado">-</span>
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
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="modalTitle">Agregar Empleado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="empleadoForm">
                    <div class="modal-body">
                        <input type="hidden" id="id-field" />

                        <h6 class="mb-3 text-primary">Datos Personales</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="documento-field" class="form-label required">Documento de Identidad</label>
                                    <div class="input-group">
                                        <select class="form-select" id="tipo-documento-field" name="tipo_documento"
                                            style="max-width: 80px;">
                                            <option value="V-">V-</option>
                                            <option value="E-">E-</option>
                                            <option value="J-">J-</option>
                                            <option value="G-">G-</option>
                                        </select>
                                        <input type="text" id="documento-identidad-field" name="documento_identidad"
                                            class="form-control" placeholder="Número" required />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="nombre-field" class="form-label required">Nombre</label>
                                    <input type="text" id="nombre-field" name="nombre" class="form-control"
                                        placeholder="Nombre" required />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="apellido-field" class="form-label required">Apellido</label>
                                    <input type="text" id="apellido-field" name="apellido" class="form-control"
                                        placeholder="Apellido" required />
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="fecha-nacimiento-field" class="form-label">Fecha de Nacimiento</label>
                                    <input type="date" id="fecha-nacimiento-field" name="fecha_nacimiento"
                                        class="form-control" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="genero-field" class="form-label">Género</label>
                                    <select id="genero-field" name="genero" class="form-control">
                                        <option value="">Seleccione...</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="email-field" class="form-label">Email</label>
                                    <input type="email" id="email-field" name="email" class="form-control"
                                        placeholder="Email" />
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="telefono-field" class="form-label">Teléfono</label>
                                    <input type="text" id="telefono-field" name="telefono" class="form-control"
                                        placeholder="0424-1234567" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="estado-persona-field" class="form-label">Estado/Provincia</label>
                                    <input type="text" id="estado-persona-field" name="estado_persona" class="form-control"
                                        placeholder="Estado" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="ciudad-field" class="form-label">Ciudad</label>
                                    <input type="text" id="ciudad-field" name="ciudad" class="form-control"
                                        placeholder="Ciudad" />
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="direccion-field" class="form-label">Dirección</label>
                                    <textarea id="direccion-field" name="direccion" class="form-control"
                                        placeholder="Dirección completa" rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="mb-3 text-success">Datos Laborales</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="codigo-empleado-field" class="form-label">Código Empleado</label>
                                    <input type="text" id="codigo-empleado-field" name="codigo_empleado"
                                        class="form-control" placeholder="Auto-generado" readonly />
                                    <small class="text-muted">Se generará automáticamente</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="cargo-field" class="form-label required">Cargo</label>
                                    <input type="text" id="cargo-field" name="cargo" class="form-control"
                                        placeholder="Ej: Operario" required />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="departamento-field" class="form-label required">Departamento</label>
                                    <select id="departamento-field" name="departamento" class="form-control form-select"
                                        required>
                                        <option value="">Seleccione...</option>
                                        <option value="Administracion">Administracion</option>
                                        <option value="Produccion">Produccion</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha-ingreso-field" class="form-label required">Fecha de Ingreso</label>
                                    <input type="date" id="fecha-ingreso-field" name="fecha_ingreso" class="form-control"
                                        required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="estado-field" class="form-label required">Estado</label>
                                    <select name="estado" id="estado-field" class="form-control form-select" required>
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
    <script>
        $(function () {
            // Tooltips
            $(document).on('mouseenter', '[title]', function () {
                $(this).tooltip({ container: 'body' }).tooltip('show');
            });
            $(document).on('mouseleave', '[title]', function () {
                $(this).tooltip('dispose');
            });
        });

        // Validación para teléfono
        $(document).on('input', '#telefono-field', function () {
            let value = this.value.replace(/[^0-9]/g, '');
            if (value.length > 4) {
                value = value.slice(0, 4) + '-' + value.slice(4, 11);
            }
            this.value = value.slice(0, 12);
        });
    </script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
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

            function generateButtons(empleadoId) {
                return `
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ri-more-fill align-middle"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <button class="dropdown-item view-item-btn" data-id="${empleadoId}">
                                                            <i class="ri-eye-fill align-bottom me-2 text-muted"></i> Ver
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item edit-item-btn" data-id="${empleadoId}">
                                                            <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Editar
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item remove-item-btn" data-id="${empleadoId}">
                                                            <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Eliminar
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                            `;
            }

            var table = $('#empleados-table').DataTable({
                ajax: { url: "{{ route('empleados.data') }}", dataSrc: 'data' },
                columns: [
                    { data: 'codigo_empleado' },
                    { data: 'nombre_completo' },
                    {
                        data: 'documento', render: function (data, type, row) {
                            return row.persona ? row.persona.tipo_documento + row.persona.documento_identidad : 'N/A';
                        }
                    },
                    {
                        data: 'email', render: function (data, type, row) {
                            return row.persona && row.persona.email ? row.persona.email : 'N/A';
                        }
                    },
                    { data: 'telefono', defaultContent: 'N/A' },
                    { data: 'cargo' },
                    { data: 'departamento' },
                    {
                        data: 'fecha_ingreso', render: function (data) {
                            if (!data) return 'N/A';
                            // Asumiendo formato ISO YYYY-MM-DD...
                            const datePart = data.split('T')[0];
                            const [year, month, day] = datePart.split('-');
                            return `${day}/${month}/${year}`;
                        }
                    },
                    {
                        data: 'estado', render: function (data) {
                            return data == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
                        }
                    },
                    {
                        data: null, render: function (data, type, row) {
                            return generateButtons(row.id);
                        }
                    }
                ],
                order: [[0, 'desc']],
                dom: 'frtip',
                language: {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sZeroRecords": "No se encontraron resultados",
                    "sEmptyTable": "Ningún empleado disponible",
                    "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando 0 registros",
                    "sInfoFiltered": "(filtrado de _MAX_ registros)",
                    "sSearch": "Buscar:",
                    "oPaginate": {
                        "sFirst": "Primero",
                        "sLast": "Último",
                        "sNext": "Siguiente",
                        "sPrevious": "Anterior"
                    }
                }
            });

            function resetForm() {
                $("#empleadoForm").trigger("reset");
                $("#id-field").val("");
                $("#modalTitle").text("Agregar Empleado");
                $("#add-btn").show();
                $("#edit-btn").hide();
                $("#codigo-empleado-field").val("");
                $("#tipo-documento-field").val("V-");
            }

            function setEditMode() {
                $("#modalTitle").text("Actualizar Empleado");
                $("#add-btn").hide();
                $("#edit-btn").show();
            }

            $("#create-btn").click(function () { resetForm(); });
            $("#showModal").on('hidden.bs.modal', function () { resetForm(); });
            $("#add-btn, #edit-btn").click(function (e) { e.preventDefault(); $("#empleadoForm").submit(); });

            $("#empleadoForm").on("submit", function (e) {
                e.preventDefault();
                var id = $("#id-field").val();
                var url = id ? "{{ route('empleados.update', ':id') }}".replace(':id', id) : "{{ route('empleados.store') }}";
                var method = id ? "PUT" : "POST";
                var formData = $(this).serialize();
                if (method === 'PUT') { formData += '&_method=PUT'; }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        $("#showModal").modal("hide");
                        $("#empleadoForm").trigger("reset");
                        table.ajax.reload();
                        Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.message, showConfirmButton: false, timer: 2000 });
                    },
                    error: function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON.error || xhr.responseJSON.message || 'Error al procesar' });
                    }
                });
            });

            $(document).on("click", ".view-item-btn", function () {
                var id = $(this).data("id");
                $.get("{{ route('empleados.show', '') }}/" + id, function (data) {
                    $("#viewModal").modal("show");
                    $("#view-nombre-completo").text(data.persona.nombre + ' ' + data.persona.apellido);
                    $("#view-documento").text(data.persona.tipo_documento + data.persona.documento_identidad);
                    $("#view-email").text(data.persona.email || 'N/A');
                    $("#view-telefono").text(data.telefono || 'N/A');
                    $("#view-direccion").text(data.direccion || 'N/A');
                    $("#view-ciudad").text(data.ciudad || 'N/A');
                    $("#view-fecha-nacimiento").text(data.persona.fecha_nacimiento || 'N/A');
                    $("#view-genero").text(data.persona.genero || 'N/A');
                    $("#view-codigo").text(data.codigo_empleado);
                    $("#view-cargo").text(data.cargo);
                    $("#view-departamento").text(data.departamento);
                    $("#view-fecha-ingreso").text(data.fecha_ingreso);
                    $("#view-estado").html(data.estado == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>');
                });
            });

            $(document).on("click", ".edit-item-btn", function () {
                var id = $(this).data("id");
                $.get("{{ route('empleados.edit', ':id') }}".replace(':id', id), function (data) {
                    setEditMode();
                    $("#id-field").val(data.id);
                    $("#nombre-field").val(data.persona.nombre);
                    $("#apellido-field").val(data.persona.apellido);
                    $("#tipo-documento-field").val(data.persona.tipo_documento);
                    $("#documento-identidad-field").val(data.persona.documento_identidad);
                    $("#email-field").val(data.persona.email);
                    $("#telefono-field").val(data.telefono || '');
                    $("#direccion-field").val(data.direccion || '');
                    $("#ciudad-field").val(data.ciudad || '');
                    $("#estado-persona-field").val(data.persona.estado_persona);
                    $("#fecha-nacimiento-field").val(data.persona.fecha_nacimiento);
                    $("#genero-field").val(data.persona.genero);
                    $("#codigo-empleado-field").val(data.codigo_empleado);
                    $("#cargo-field").val(data.cargo);
                    $("#departamento-field").val(data.departamento);
                    $("#fecha-ingreso-field").val(data.fecha_ingreso);
                    $("#estado-field").val(data.estado);
                    $("#showModal").modal("show");
                });
            });

            $(document).on("click", ".remove-item-btn", function () {
                var id = $(this).data("id");
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
                    buttonsStyling: false
                }).then(function (result) {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('empleados.destroy', ':id') }}".replace(':id', id),
                            method: "DELETE",
                            success: function (response) {
                                table.ajax.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Eliminado',
                                    text: response.message
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON.message || 'Error al eliminar'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush