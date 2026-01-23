@extends('admin.layouts.app')

@push('styles')
    <!-- Sweet Alert css-->
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet"
        type="text/css" />
    <!-- Se eliminó la referencia a los estilos de botones -->
@endpush

@section('content')
    <style>
        .card-body {
            overflow-x: auto;
        }

        #users-table {
            width: 100% !important;
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

        #users-table th:last-child,
        #users-table td:last-child {
            width: 48px;
            min-width: 40px;
            max-width: 60px;
            text-align: center;
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
    </style>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Listado de Usuarios</h5>
                        <div class="flex-shrink-0 d-flex align-items-center gap-3">
                            <!-- Buscador Personalizado -->
                            <div class="search-box">
                                <input type="text" class="form-control form-control-sm" id="custom-search-input" placeholder="Buscar usuario...">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn"
                                    data-bs-target="#showModal">
                                    <i class="ri-add-line align-bottom me-1"></i> Agregar Usuario
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="users-table" class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Creado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles del Usuario -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header con gradiente marca Atlantico -->
                <div class="modal-header py-3"
                    style="background: linear-gradient(135deg, #1e3c72 0%, #2ecc71 50%, #00d9a5 100%);">
                    <h5 class="modal-title text-white d-flex align-items-center">
                        <i class="ri-user-3-line me-2 fs-4"></i>Detalles del Usuario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Avatar centrado -->
                    <div class="text-center mb-4" id="user-avatar-container">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center"
                            style="width: 100px; height: 100px; background: linear-gradient(135deg, #1e3c72 0%, #00d9a5 100%); padding: 3px;">
                            <img id="user-avatar" src="/assets/images/users/user-dummy-img.jpg" alt="Avatar del usuario"
                                class="rounded-circle bg-white" style="width: 94px; height: 94px; object-fit: cover;">
                        </div>
                    </div>

                    <!-- Card Información del Usuario -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-0" style="background: rgba(0, 217, 165, 0.1);">
                            <h6 class="mb-0" style="color: #00d9a5;">
                                <i class="ri-information-line me-2"></i>Información del Usuario
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
                                            <small class="text-muted d-block">Nombre</small>
                                            <span class="fw-semibold" id="view-name">-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px; background: rgba(46, 204, 113, 0.1);">
                                            <i class="ri-mail-line" style="color: #2ecc71;"></i>
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
                                            <i class="ri-shield-user-line" style="color: #00d9a5;"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Rol</small>
                                            <span class="fw-semibold" id="view-role">-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                            <i class="ri-calendar-line" style="color: #1e3c72;"></i>
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
                    <h5 class="modal-title" id="modalTitle">Agregar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="userForm">
                    <div class="modal-body">
                        <input type="hidden" id="id-field" />
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name-field" class="form-label required">Nombre</label><input type="text"
                                        id="name-field" name="name" class="form-control" placeholder="Nombre" required />
                                </div>
                                <div class="mb-3">
                                    <label for="email-field" class="form-label required">Email</label><input type="email"
                                        id="email-field" name="email" class="form-control" placeholder="Email" required />
                                </div>
                                <div class="mb-3" id="password-group">
                                    <label for="password-field" class="form-label">Contraseña</label>
                                    <input type="password" id="password-field" name="password" class="form-control"
                                        placeholder="Contraseña" />
                                    <small class="text-muted">Dejar en blanco para mantener la contraseña actual al
                                        editar</small>
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirmation-field" class="form-label required">Confirmar
                                        Contraseña</label><input type="password" id="password_confirmation-field"
                                        name="password_confirmation" class="form-control" placeholder="Confirmar Contraseña"
                                        required />
                                </div>
                                <div class="mb-3">
                                    <label for="role-field" class="form-label required">Rol</label><select id="role-field"
                                        name="role" class="form-control" required>
                                        <option value="">Seleccione un rol</option>
                                        <option value="Administrador">Administrador</option>
                                        <option value="Supervisor">Supervisor</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="avatar-field" class="form-label">Avatar</label>
                                    <input type="file" id="avatar-field" name="avatar" class="form-control"
                                        accept="image/*" />
                                    <div id="avatar-preview" class="mt-2 text-center" style="display: none;">
                                        <img src="" alt="Vista previa del avatar" class="img-fluid rounded-circle"
                                            style="max-width: 100px;">
                                    </div>
                                </div>

                                <div class="mb-4">
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
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="{{ asset('assets/js/form-validation.js') }}"></script>


    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            function generateButtons(userId) {
                return `
                    <div class="d-flex gap-2 justify-content-center">
                        <button class="btn btn-sm btn-soft-info view-item-btn" data-id="${userId}" title="Ver">
                            <i class="ri-eye-fill"></i>
                        </button>
                        <button class="btn btn-sm btn-soft-success edit-item-btn" data-id="${userId}" title="Editar">
                            <i class="ri-pencil-fill"></i>
                        </button>
                        <button class="btn btn-sm btn-soft-danger remove-item-btn" data-id="${userId}" title="Eliminar">
                            <i class="ri-delete-bin-fill"></i>
                        </button>
                    </div>
                `;
            }

            var table = $('#users-table').DataTable({
                ajax: {
                    url: "{{ route('users.data') }}",
                    dataSrc: 'data'
                },
                columns: [
                    {
                        data: 'name',
                        render: function (data, type, row) {
                            return `
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-2">
                                                            <div class="avatar-xs">
                                                                <img src="${row.avatar || '/assets/images/users/user-dummy-img.jpg'}" alt="Avatar" class="img-fluid rounded-circle">
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1">${data}</div>
                                                    </div>
                                                `;
                        }
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'role'
                    },
                    {
                        data: 'estado',
                        render: function (data, type, row) {
                            if (data == 1) {
                                return '<span class="badge bg-success">Activo</span>';
                            } else {
                                return '<span class="badge bg-danger">Inactivo</span>';
                            }
                        }
                    },
                    {
                        data: 'created_at',
                        render: function(data) {
                            if (!data) return '';
                            const date = new Date(data);
                            return date.toLocaleString('es-VE', {
                                year: 'numeric', 
                                month: '2-digit', 
                                day: '2-digit', 
                                hour: '2-digit', 
                                minute: '2-digit',
                                hour12: true
                            });
                        }
                    },
                    {
                        data: null,
                        render: function (data, type, row) {
                            return generateButtons(row.id);
                        }
                    }
                ],
                order: [
                    [4, 'desc']
                ],
                dom: 'rtip',
                responsive: true,
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

            // Buscador personalizado
            $('#custom-search-input').on('keyup', function () {
                table.search(this.value).draw();
            });

            function resetForm() {
                $('#modalTitle').text('Agregar Usuario');
                $('#userForm')[0].reset();
                $('#userForm input[type="hidden"]').val('');
                $('#avatar-preview').hide().find('img').attr('src', '');
                $('#add-btn').show();
                $('#edit-btn').hide();
                $('#password-group').show();
                $('#password-field').prop('required', true); // Requerir contraseña al crear
                $('#password_confirmation-field').prop('required', true);
                
                // Reiniciar validaciones
                validator.resetValidation();
            }

            function setEditMode() {
                $("#modalTitle").text("Actualizar Usuario");
                $("#add-btn").hide();
                $("#edit-btn").show();
                $('#password-group').hide(); // Ocultar campo de contraseña al editar
                $('#password-field').prop('required', false); // No requerir contraseña al editar
                $('#password_confirmation-field').prop('required', false);
            }

            // Función para mostrar vista previa de imágenes
            function readURL(input, previewId) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $(previewId).find('img').attr('src', e.target.result);
                        $(previewId).show();
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            // Vista previa de imágenes al seleccionarlas
            $('#avatar-field').change(function () {
                readURL(this, '#avatar-preview');
            });

            $("#create-btn").click(function () {
                resetForm();
                // Ocultar vista previa
                $('#avatar-preview').hide();
            });

            $("#showModal").on('hidden.bs.modal', function () {
                resetForm();
            });

            const validator = new FormValidator('userForm');

            $('#add-btn').click(function (e) {
                e.preventDefault();

                // Run validation
                if (!validator.validateAll()) {
                    return;
                }

                $("#userForm").submit();
            });

            $("#userForm").on("submit", function (e) {
                e.preventDefault();
                var id = $("#id-field").val();
                var url = id ? "{{ route('users.update', ':id') }}".replace(':id', id) : "{{ route('users.store') }}";
                var method = id ? "PUT" : "POST";

                var formData = new FormData(this);
                if (method === 'PUT') {
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $("#showModal").modal("hide");
                        $("#userForm").trigger("reset");
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            customClass: {
                                confirmButton: 'btn btn-primary w-xs me-2',
                                cancelButton: 'btn btn-danger w-xs'
                            },
                            buttonsStyling: false,
                            showCloseButton: true,
                            showConfirmButton: true,
                            timer: 2000
                        });
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON.message,
                            customClass: {
                                confirmButton: 'btn btn-primary w-xs me-2',
                                cancelButton: 'btn btn-danger w-xs'
                            },
                            buttonsStyling: false,
                            showCloseButton: true
                        });
                    }
                });
            });

            $(document).on("click", ".view-item-btn", function () {
                var id = $(this).data("id");
                $.get("{{ route('users.show', '') }}/" + id, function (data) {
                    $("#viewModal").modal("show");
                    $("#view-name").text(data.name);
                    $("#view-email").text(data.email);
                    $("#view-role").text(data.role || 'Sin rol');
                    $("#view-created").text(data.created_at);

                    // Mostrar avatar
                    if (data.avatar) {
                        $("#user-avatar").attr("src", data.avatar);
                    } else {
                        $("#user-avatar").attr("src", "/assets/images/users/user-dummy-img.jpg");
                    }


                });
            });

            $(document).on("click", ".edit-item-btn", function () {
                var id = $(this).data("id");

                $.get("{{ route('users.edit', ':id') }}".replace(':id', id), function (data) {
                    setEditMode();
                    $("#id-field").val(data.id);
                    $("#name-field").val(data.name);
                    $("#email-field").val(data.email);
                    $("#role-field").val(data.role);

                    // Mostrar las imágenes existentes si las hay
                    if (data.avatar) {
                        $("#avatar-preview img").attr('src', data.avatar);
                        $("#avatar-preview").show();
                    }


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
                    buttonsStyling: false,
                    showCloseButton: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('users.destroy', '') }}/" + id,
                            type: "DELETE",
                            success: function (response) {
                                table.ajax.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Eliminado!',
                                    text: response.message,
                                    customClass: {
                                        confirmButton: 'btn btn-primary w-xs me-2',
                                        cancelButton: 'btn btn-danger w-xs'
                                    },
                                    buttonsStyling: false,
                                    showCloseButton: true,
                                    timer: 2000
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON.message,
                                    customClass: {
                                        confirmButton: 'btn btn-primary w-xs me-2',
                                        cancelButton: 'btn btn-danger w-xs'
                                    },
                                    buttonsStyling: false,
                                    showCloseButton: true
                                });
                            }
                        });
                    }
                });
            });

            $("#create-btn").click(function () {
                $("#id-field").val("");
                $("#userForm").trigger("reset");
                $(".modal-title").text("Agregar Usuario");
                $("#add-btn").show();
                $("#edit-btn").hide();
            });

            $("#edit-btn").on("click", function () {
                $("#userForm").submit();
            });
        });
    </script>
@endpush