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

        #clientes-table {
            width: 100% !important;
            font-size: 13px;
            min-width: 1200px;
        }

        #clientes-table th,
        #clientes-table td {
            padding: 0.35rem 0.5rem;
            vertical-align: middle;
        }

        #clientes-table th:last-child,
        #clientes-table td:last-child {
            width: 48px;
            min-width: 40px;
            max-width: 60px;
            text-align: center;
        }

        #clientes-table th:nth-last-child(2),
        #clientes-table td:nth-last-child(2) {
            width: 100px;
            min-width: 80px;
            max-width: 120px;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #clientes-table th:nth-child(5),
        #clientes-table td:nth-child(5) {
            width: 100px;
            min-width: 80px;
            max-width: 120px;
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
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Tipo</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Documento</th>
                                <th>Dirección</th>
                                <th>Ciudad</th>
                                <th>Estado</th>
                                <th>Creado</th>
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
                                        <div class="col-12">
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

                            <!-- Card Estado -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header border-0" style="background: rgba(0, 217, 165, 0.1);">
                                    <h6 class="mb-0" style="color: #00d9a5;">
                                        <i class="ri-information-line me-2"></i>Estado del Cliente
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
                                                    <small class="text-muted d-block">Estado</small>
                                                    <span class="fw-semibold" id="view-estado">-</span>
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
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="modalTitle">Agregar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="clienteForm">
                    <div class="modal-body">
                        <input type="hidden" id="id-field" />
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre-field" class="form-label required">Nombre</label><input type="text"
                                        id="nombre-field" name="nombre" class="form-control" placeholder="Nombre"
                                        required />
                                </div>
                                <div class="mb-3">
                                    <label for="apellido-field" class="form-label">Apellido</label><input type="text"
                                        id="apellido-field" name="apellido" class="form-control" placeholder="Apellido" />
                                </div>
                                <div class="mb-3">
                                    <label for="tipo_cliente-field" class="form-label required">Tipo de
                                        Cliente</label><select id="tipo_cliente-field" name="tipo_cliente"
                                        class="form-control" required>
                                        <option value="">Seleccione tipo</option>
                                        <option value="natural">Natural</option>
                                        <option value="juridico">Jurídico</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="email-field" class="form-label">Email</label>
                                    <input type="email" id="email-field" name="email" class="form-control"
                                        placeholder="Email" />
                                </div>
                                <div class="mb-3">
                                    <label for="telefono-field" class="form-label required">Teléfono</label><input
                                        type="text" id="telefono-field" name="telefono" class="form-control"
                                        placeholder="Teléfono" required />
                                </div>
                                <div class="mb-3">
                                    <label for="documento-field" class="form-label">Documento (Cédula o RIF)</label>
                                    <div class="input-group">
                                        <select class="form-select" id="documento-prefix-field" style="max-width: 80px;">
                                            <option value="V-">V-</option>
                                            <option value="J-">J-</option>
                                            <option value="E-">E-</option>
                                            <option value="G-">G-</option>
                                        </select>
                                        <input type="text" id="documento-number-field" class="form-control"
                                            placeholder="Número de documento" required />
                                    </div>
                                    <input type="hidden" id="documento-field" name="documento" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="direccion-field" class="form-label">Dirección</label>
                                    <input type="text" id="direccion-field" name="direccion" class="form-control"
                                        placeholder="Dirección" />
                                </div>
                                <div class="mb-3">
                                    <label for="ciudad-field" class="form-label">Ciudad</label>
                                    <input type="text" id="ciudad-field" name="ciudad" class="form-control"
                                        placeholder="Ciudad" />
                                </div>
                                <div class="mb-3">
                                    <label for="estado-field" class="form-label">Estado</label>
                                    <select name="estado" id="estado-field" class="form-control form-select">
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
            this.value = value.slice(0, 12); // Máximo 12 caracteres (incluyendo el guion)
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
            function generateButtons(clienteId) {
                return `
                                                <div class="dropdown d-inline-block">
                                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-fill align-middle"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                <button class="dropdown-item view-item-btn" data-id="${clienteId}">
                                                <i class="ri-eye-fill align-bottom me-2 text-muted"></i> Ver
                                                </button>
                                                </li>
                                                <li>
                                                <button class="dropdown-item edit-item-btn" data-id="${clienteId}">
                                                <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Editar
                                                </button>
                                                </li>
                                                <li>
                                                <button class="dropdown-item remove-item-btn" data-id="${clienteId}">
                                                <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Eliminar
                                                </button>
                                                </li>
                                                </ul>
                                                </div>
                                                `;
            }
            var table = $('#clientes-table').DataTable({
                ajax: { url: "{{ route('clientes.data') }}", dataSrc: 'data' },
                columns: [
                    { data: 'nombre' },
                    { data: 'apellido' },
                    { data: 'tipo_cliente', render: function (data) { return data === 'natural' ? 'Natural' : 'Jurídico'; } },
                    { data: 'email' },
                    { data: 'telefono' },
                    { data: 'documento' },
                    { data: 'direccion' },
                    { data: 'ciudad' },
                    { data: 'estado', render: function (data) { return data == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>'; } },
                    {
                        data: 'created_at',
                        render: function (data) {
                            if (!data) return 'N/A';
                            return `<span class="text-truncate" style="max-width:60px; display:inline-block; cursor:pointer;" title="${data}">${data}</span>`;
                        }
                    },
                    { data: null, render: function (data, type, row) { return generateButtons(row.id); } }
                ],
                order: [[9, 'desc']], // Columna "Creado" es ahora índice 9
                dom: 'frtip',
                buttons: [
                    {
                        extend: 'copy',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
                        }
                    },
                    {
                        extend: 'excel',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
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
                $("#documento-number-field").val("");
            }
            function setEditMode() {
                $("#modalTitle").text("Actualizar Cliente");
                $("#add-btn").hide();
                $("#edit-btn").show();
            }
            $("#create-btn").click(function () { resetForm(); });
            $("#showModal").on('hidden.bs.modal', function () { resetForm(); });
            $("#add-btn, #edit-btn").click(function (e) { e.preventDefault(); $("#clienteForm").submit(); });
            $("#clienteForm").on("submit", function (e) {
                e.preventDefault();
                var id = $("#id-field").val();
                var url = id ? "{{ route('clientes.update', ':id') }}".replace(':id', id) : "{{ route('clientes.store') }}";
                var method = id ? "PUT" : "POST";
                var documentoCompleto = $("#documento-prefix-field").val() + $("#documento-number-field").val();
                $("#documento-field").val(documentoCompleto);
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
                    },
                    error: function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON.message });
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
                    $("#view-ciudad").text(data.ciudad || 'N/A');
                    $("#view-estado").html(data.estado == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>');
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
                    $("#telefono-field").val(data.telefono || '');
                    if (data.documento) {
                        $("#documento-prefix-field").val(data.documento.slice(0, 2));
                        $("#documento-number-field").val(data.documento.slice(2));
                    }
                    $("#direccion-field").val(data.direccion || '');
                    $("#ciudad-field").val(data.ciudad || '');
                    $("#estado-field").val(data.estado);
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
                $("#id-field").val("");
                $("#clienteForm").trigger("reset");
                $(".modal-title").text("Agregar Cliente");
                $("#add-btn").show();
                $("#edit-btn").hide();
            });
            $("#edit-btn").on("click", function () { $("#clienteForm").submit(); });
        });
    </script>
@endpush