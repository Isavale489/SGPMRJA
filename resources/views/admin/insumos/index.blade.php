@extends('admin.layouts.app')

@push('styles')
    <!-- Sweet Alert css-->
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" rel="stylesheet"
        type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <style>
        .stock-bajo {
            color: #dc3545;
        }

        .stock-medio {
            color: #ffc107;
        }

        .stock-normal {
            color: #198754;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Listado de Insumos</h5>
                        <div class="flex-shrink-0 d-flex gap-2">
                            <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn"
                                data-bs-target="#showModal">
                                <i class="ri-add-line align-bottom me-1"></i> Agregar Insumo
                            </button>
                            <a href="{{ route('insumos.reporte.pdf') }}" class="btn btn-danger" target="_blank">
                                <i class="ri-file-pdf-fill align-bottom me-1"></i> Exportar PDF
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="insumos-table" class="table table-bordered table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Nro. Insumo</th>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Unidad</th>
                                    <th>Stock Actual</th>
                                    <th>Stock Mínimo</th>
                                    <th>Costo Unit.</th>
                                    <th>Proveedor</th>
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
    </div>

    <!-- Modal para ver detalles -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title">Detalles del Insumo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Nombre:</strong>
                        <p id="view-nombre" class="text-muted mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Tipo:</strong>
                        <p id="view-tipo" class="text-muted mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Unidad de Medida:</strong>
                        <p id="view-unidad-medida" class="text-muted mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Stock Actual:</strong>
                        <p id="view-stock-actual" class="text-muted mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Stock Mínimo:</strong>
                        <p id="view-stock-minimo" class="text-muted mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Costo Unitario:</strong>
                        <p id="view-costo-unitario" class="text-muted mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Proveedor:</strong>
                        <p id="view-proveedor" class="text-muted mb-0"></p>
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
                    <h5 class="modal-title" id="modalTitle">Agregar Insumo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="insumoForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="id-field" />
                        <div class="mb-3">
                            <label for="nombre-field" class="form-label required">Nombre</label><input type="text"
                                id="nombre-field" name="nombre" class="form-control" required />
                        </div>
                        <div class="mb-3">
                            <label for="tipo-field" class="form-label required">Tipo</label><select id="tipo-field"
                                name="tipo" class="form-control" required>
                                <option value="">Seleccione...</option>
                                <option value="Tela">Tela</option>
                                <option value="Hilo">Hilo</option>
                                <option value="Botón">Botón</option>
                                <option value="Cierre">Cierre</option>
                                <option value="Etiqueta">Etiqueta</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="unidad-medida-field" class="form-label required">Unidad de Medida</label><input
                                type="text" id="unidad-medida-field" name="unidad_medida" class="form-control" required />
                        </div>
                        <div class="mb-3">
                            <label for="stock-actual-field" class="form-label">Stock Actual</label>
                            <input type="number" id="stock-actual-field" name="stock_actual" class="form-control"
                                step="0.01" min="0" value="0" />
                        </div>
                        <div class="mb-3">
                            <label for="stock-minimo-field" class="form-label required">Stock Mínimo</label><input
                                type="number" id="stock-minimo-field" name="stock_minimo" class="form-control" step="0.01"
                                min="0" required />
                        </div>
                        <div class="mb-3">
                            <label for="costo-unitario-field" class="form-label required">Costo Unitario</label><input
                                type="number" id="costo-unitario-field" name="costo_unitario" class="form-control"
                                step="0.01" min="0" required />
                        </div>
                        <div class="mb-3">
                            <label for="proveedor-id-field" class="form-label">Proveedor</label>
                            <select id="proveedor-id-field" name="proveedor_id" class="form-control">
                                <option value="">Seleccione...</option>
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}">{{ $proveedor->razon_social }}</option>
                                @endforeach
                            </select>
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
            var table = $('#insumos-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                ajax: "{{ route('insumos.data') }}",
                dom: 'frtip',
                buttons: [
                    {
                        extend: 'copy',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                    },
                    {
                        extend: 'csv',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                    },
                    {
                        extend: 'excel',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                    },
                    {
                        extend: 'print',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                    }
                ],
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'nombre', name: 'nombre' },
                    { data: 'tipo', name: 'tipo' },
                    { data: 'unidad_medida', name: 'unidad_medida' },
                    {
                        data: 'stock_actual',
                        name: 'stock_actual',
                        render: function (data, type, row) {
                            var stockClass = 'stock-' + row.stock_status;
                            return `<span class="${stockClass}">${data}</span>`;
                        }
                    },
                    { data: 'stock_minimo', name: 'stock_minimo' },
                    {
                        data: 'costo_unitario',
                        name: 'costo_unitario',
                        render: function (data) {
                            return '$/ ' + parseFloat(data).toFixed(2);
                        }
                    },
                    { data: 'proveedor_nombre', name: 'proveedor.razon_social' },
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
                                                <li>
                                                    <button class="dropdown-item edit-item-btn" data-id="${data}">
                                                        <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Editar
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item remove-item-btn" data-id="${data}">
                                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Eliminar
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    `;
                        }
                    }
                ],
                order: [[0, 'desc']],
                language: lenguajeData
            });

            // Ver detalles
            $(document).on('click', '.view-item-btn', function () {
                var id = $(this).data('id');
                $.get("{{ route('insumos.show', ':id') }}".replace(':id', id), function (data) {
                    $("#view-nombre").text(data.nombre);
                    $("#view-tipo").text(data.tipo);
                    $("#view-unidad-medida").text(data.unidad_medida);
                    $("#view-stock-actual").text(data.stock_actual);
                    $("#view-stock-minimo").text(data.stock_minimo);
                    $("#view-costo-unitario").text('$/ ' + parseFloat(data.costo_unitario).toFixed(2));
                    $("#view-proveedor").text(data.proveedor ? data.proveedor.razon_social : 'Sin proveedor');
                    $("#view-created").text(data.created_at);
                    $("#viewModal").modal('show');
                });
            });

            // Editar
            $(document).on('click', '.edit-item-btn', function () {
                var id = $(this).data('id');
                $.get("{{ route('insumos.show', ':id') }}".replace(':id', id), function (data) {
                    $("#modalTitle").text("Editar Insumo");
                    $("#id-field").val(data.id);
                    $("#nombre-field").val(data.nombre);
                    $("#tipo-field").val(data.tipo);
                    $("#unidad-medida-field").val(data.unidad_medida);
                    $("#stock-actual-field").val(data.stock_actual);
                    $("#stock-minimo-field").val(data.stock_minimo);
                    $("#costo-unitario-field").val(data.costo_unitario);
                    $("#proveedor-id-field").val(data.proveedor_id);
                    $("#estado-field").val(data.estado ? '1' : '0');

                    $("#add-btn").hide();
                    $("#edit-btn").show();
                    $("#showModal").modal('show');
                });
            });

            // Enviar formulario
            $("#insumoForm").on("submit", function (e) {
                e.preventDefault();
                var id = $("#id-field").val();
                var url = id ? "{{ route('insumos.update', ':id') }}".replace(':id', id) : "{{ route('insumos.store') }}";
                var method = id ? "PUT" : "POST";

                var formData = new FormData(this);
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
                            url: "{{ route('insumos.destroy', ':id') }}".replace(':id', id),
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
                                    text: 'No se pudo eliminar el insumo'
                                });
                            }
                        });
                    }
                });
            });

            // Limpiar modal al cerrar
            $("#showModal").on("hidden.bs.modal", function () {
                $("#modalTitle").text("Agregar Insumo");
                $("#insumoForm")[0].reset();
                $("#id-field").val("");
                $("#add-btn").show();
                $("#edit-btn").hide();
            });
        });
    </script>
@endpush