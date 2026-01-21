@extends('admin.layouts.app')

@push('styles')
    <!-- Sweet Alert css-->
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" rel="stylesheet"
        type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Listado de Proveedores</h5>
                        <div class="flex-shrink-0 d-flex gap-2">
                            @if(Auth::user()->isAdmin())
                                <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn"
                                    data-bs-target="#showModal">
                                    <i class="ri-add-line align-bottom me-1"></i> Agregar Proveedor
                                </button>
                            @endif
                            <a href="{{ route('proveedores.reporte.pdf') }}" class="btn btn-danger" target="_blank">
                                <i class="ri-file-pdf-fill align-bottom me-1"></i> Exportar PDF
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="proveedores-table" class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Razón Social</th>
                                <th>RIF</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Contacto</th>
                                <th>Estado</th>
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

    <!-- Modal para ver detalles -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title">Detalles del Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Razón Social:</strong>
                        <p id="view-razon-social" class="text-muted mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <strong>RIF:</strong>
                        <p id="view-rif" class="text-muted mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Dirección:</strong>
                        <p id="view-direccion" class="text-muted mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Teléfono:</strong>
                        <p id="view-telefono" class="text-muted mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong>
                        <p id="view-email" class="text-muted mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Contacto:</strong>
                        <p id="view-contacto" class="text-muted mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Teléfono de Contacto:</strong>
                        <p id="view-telefono-contacto" class="text-muted mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Fecha de Registro:</strong>
                        <p id="view-created" class="text-muted mb-0"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar/editar -->
    <div class="modal fade" id="showModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="modalTitle">Agregar Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="proveedorForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="id-field" />
                        <div class="mb-3">
                            <label for="rif-field" class="form-label required">RIF</label>
                            <div class="input-group">
                                <select class="form-select" id="rif-prefix-field" style="max-width: 80px;">
                                    <option value="J-">J-</option>
                                    <option value="V-">V-</option>
                                    <option value="G-">G-</option>
                                    <option value="E-">E-</option>
                                </select>
                                <input type="text" id="rif-number-field" class="form-control"
                                    placeholder="Ej: 123456789" maxlength="10" required />
                            </div>
                            <input type="hidden" id="rif-field" name="rif" />
                            <small class="text-muted">Máximo 10 dígitos</small>
                            <div id="rif-error" class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="razon-social-field" class="form-label required">Razón Social</label>
                            <input type="text" id="razon-social-field" name="razon_social" class="form-control" 
                                maxlength="200" placeholder="Nombre de la empresa" required />
                            <div id="razon-social-error" class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="direccion-field" class="form-label">Dirección</label>
                            <input type="text" id="direccion-field" name="direccion" class="form-control" 
                                maxlength="500" placeholder="Dirección de la empresa" />
                        </div>
                        <div class="mb-3">
                            <label for="telefono-field" class="form-label required">Teléfono</label>
                            <input type="text" id="telefono-field" name="telefono" class="form-control" 
                                placeholder="0424-1234567" maxlength="12" required />
                            <div id="telefono-error" class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="email-field" class="form-label">Email</label>
                            <input type="email" id="email-field" name="email" class="form-control" 
                                placeholder="correo@empresa.com" />
                            <div id="email-error" class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="contacto-field" class="form-label">Persona de Contacto</label>
                            <input type="text" id="contacto-field" name="contacto" class="form-control" 
                                maxlength="100" placeholder="Nombre del contacto" />
                        </div>
                        <div class="mb-3">
                            <label for="telefono-contacto-field" class="form-label">Teléfono de Contacto</label>
                            <input type="text" id="telefono-contacto-field" name="telefono_contacto" class="form-control" 
                                placeholder="0424-1234567" maxlength="12" />
                        </div>
                        <div class="mb-3">
                            <label for="estado-field" class="form-label">Estado</label>
                            <select id="estado-field" name="estado" class="form-control">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
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
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.colVis.min.js"></script>
    <script src="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        $(document).ready(function () {
            var table = $('#proveedores-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('proveedores.data') }}",
                dom: 'frtip',
                buttons: [
                    {
                        extend: 'copy',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5] } // Excluir la columna de acciones (índice 6)
                    },
                    {
                        extend: 'csv',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5] } // Excluir la columna de acciones (índice 6)
                    },
                    {
                        extend: 'excel',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5] } // Excluir la columna de acciones (índice 6)
                    },
                    {
                        extend: 'pdf',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5] } // Excluir la columna de acciones (índice 6)
                    },
                    {
                        extend: 'print',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5] } // Excluir la columna de acciones (índice 6)
                    }
                ],
                columns: [
                    { data: 'razon_social', name: 'razon_social' },
                    { data: 'rif', name: 'rif' },
                    { data: 'telefono', name: 'telefono' },
                    { data: 'email', name: 'email' },
                    { data: 'contacto', name: 'contacto' },
                    {
                        data: 'estado',
                        name: 'estado',
                        render: function (data) {
                            return data ? `<span class="badge bg-success">Activo</span>` : `<span class="badge bg-danger">Inactivo</span>`;
                        }
                    },
                    {
                        data: 'id',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            var isAdmin = {{ Auth::user()->isAdmin() ? 'true' : 'false' }};
                            var editDelete = isAdmin ? `
                                    <li>
                                        <button class="dropdown-item edit-item-btn" data-id="${data}">
                                            <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Editar
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item remove-item-btn" data-id="${data}">
                                            <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Eliminar
                                        </button>
                                    </li>` : '';
                            return `
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-more-fill align-middle"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <button class="dropdown-item view-item-btn" data-id="${data}">
                                                    <i class="ri-eye-fill align-bottom me-2 text-muted"></i> Ver
                                                </button>
                                            </li>
                                            ${editDelete}
                                        </ul>
                                    </div>
                                `;
                        }
                    }
                ],
                order: [[0, 'desc']], // Cambiar el índice de ordenamiento (ahora la columna "Razón Social" es la índice 0)
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

            // Ver detalles
            $(document).on('click', '.view-item-btn', function () {
                var id = $(this).data('id');
                $.get("{{ route('proveedores.show', ':id') }}".replace(':id', id), function (data) {
                    $("#view-razon-social").text(data.razon_social);
                    $("#view-rif").text(data.rif);
                    $("#view-direccion").text(data.direccion);
                    $("#view-telefono").text(data.telefono);
                    $("#view-email").text(data.email);
                    $("#view-contacto").text(data.contacto);
                    $("#view-telefono-contacto").text(data.telefono_contacto);
                    $("#view-created").text(data.created_at);
                    $("#viewModal").modal('show');
                });
            });

            // Editar
            $(document).on('click', '.edit-item-btn', function () {
                var id = $(this).data('id');
                $.get("{{ route('proveedores.show', ':id') }}".replace(':id', id), function (data) {
                    $("#modalTitle").text("Editar Proveedor");
                    $("#id-field").val(data.id);
                    $("#razon-social-field").val(data.razon_social);
                    
                    // Separar prefijo y número del RIF
                    var rif = data.rif || '';
                    var rifMatch = rif.match(/^(V-|J-|E-|G-)(.+)$/);
                    if (rifMatch) {
                        $("#rif-prefix-field").val(rifMatch[1]);
                        $("#rif-number-field").val(rifMatch[2]);
                    } else {
                        $("#rif-prefix-field").val('J-');
                        $("#rif-number-field").val(rif);
                    }
                    
                    $("#direccion-field").val(data.direccion);
                    $("#telefono-field").val(data.telefono);
                    $("#email-field").val(data.email);
                    $("#contacto-field").val(data.contacto);
                    $("#telefono-contacto-field").val(data.telefono_contacto);
                    $("#estado-field").val(data.estado ? '1' : '0');

                    $("#add-btn").hide();
                    $("#edit-btn").show();
                    $("#showModal").modal('show');
                });
            });

            // Enviar formulario
            $("#proveedorForm").on("submit", function (e) {
                e.preventDefault();
                var id = $("#id-field").val();
                var url = id ? "{{ route('proveedores.update', ':id') }}".replace(':id', id) : "{{ route('proveedores.store') }}";
                var method = id ? "PUT" : "POST";

                var formData = new FormData(this);
                
                // Combinar prefijo y número del RIF
                var rifPrefix = $('#rif-prefix-field').val();
                var rifNumber = $('#rif-number-field').val();
                formData.set('rif', rifPrefix + rifNumber);
                
                if (method === "PUT") {
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $("#showModal").modal('hide');
                        table.draw();
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.success,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    },
                    error: function (xhr) {
                        var errors = xhr.responseJSON.errors;
                        var errorMessage = '';
                        $.each(errors, function (key, value) {
                            errorMessage += value[0] + '<br>';
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errorMessage
                        });
                    }
                });
            });

            // Eliminar
            $(document).on("click", ".remove-item-btn", function () {
                var id = $(this).data("id");
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡No podrás revertir esto!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                    cancelButtonClass: 'btn btn-danger w-xs mt-2',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    buttonsStyling: false,
                    showCloseButton: true
                }).then(function (result) {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('proveedores.destroy', ':id') }}".replace(':id', id),
                            method: "DELETE",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                table.draw();
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Eliminado!',
                                    text: response.success,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'No se pudo eliminar el proveedor'
                                });
                            }
                        });
                    }
                });
            });

            // Limpiar modal al cerrar
            $("#showModal").on("hidden.bs.modal", function () {
                $("#modalTitle").text("Agregar Proveedor");
                $("#proveedorForm")[0].reset();
                $("#id-field").val("");
                $("#add-btn").show();
                $("#edit-btn").hide();
            });
        });
    </script>
@endpush