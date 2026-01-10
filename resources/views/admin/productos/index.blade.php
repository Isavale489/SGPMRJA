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
            /* overflow-x: auto; */
        }

        #productos-table {
            width: 100% !important;
            font-size: 13px;
        }

        #productos-table th,
        #productos-table td {
            padding: 0.35rem 0.5rem;
            vertical-align: middle;
        }

        #productos-table th:last-child,
        #productos-table td:last-child {
            width: 48px;
            min-width: 40px;
            max-width: 60px;
            text-align: center;
        }

        #productos-table th:first-child,
        #productos-table td:first-child {
            width: 60px;
            min-width: 40px;
            max-width: 80px;
            text-align: center;
        }
    </style>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Listado de Productos</h5>
                        <div class="flex-shrink-0">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#tiposModal">
                                <i class="ri-settings-3-line align-bottom me-1"></i> Gestionar Tipos
                            </button>
                            <button type="button" class="btn btn-success add-btn ms-2" data-bs-toggle="modal"
                                id="create-btn" data-bs-target="#showModal">
                                <i class="ri-add-line align-bottom me-1"></i> Agregar Producto
                            </button>
                            <a href="{{ route('productos.reporte.pdf') }}" target="_blank" class="btn btn-danger ms-2">
                                <i class="ri-file-pdf-fill align-bottom me-1"></i> Exportar PDF
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="productos-table" class="table table-bordered table-striped table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Código</th>
                                <th>Tipo</th>
                                <th>Modelo</th>
                                <th>Precio Base</th>
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

    <!-- Modal para ver detalles del Producto -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header con gradiente marca Atlantico -->
                <div class="modal-header py-3"
                    style="background: linear-gradient(135deg, #1e3c72 0%, #2ecc71 50%, #00d9a5 100%);">
                    <h5 class="modal-title text-white d-flex align-items-center">
                        <i class="ri-t-shirt-2-line me-2 fs-4"></i>Detalles del Producto
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Imagen del Producto centrada -->
                    <div class="text-center mb-4" id="producto-imagen-container" style="display: none;">
                        <div class="rounded mx-auto d-inline-block p-2" style="background: rgba(30, 60, 114, 0.05);">
                            <img id="producto-imagen" src="" alt="Imagen del producto" class="rounded"
                                style="max-width: 200px; max-height: 200px; object-fit: cover;">
                        </div>
                    </div>

                    <!-- Card Información del Producto -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-0" style="background: rgba(0, 217, 165, 0.1);">
                            <h6 class="mb-0" style="color: #00d9a5;">
                                <i class="ri-information-line me-2"></i>Información del Producto
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                            <i class="ri-price-tag-3-line" style="color: #1e3c72;"></i>
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
                                            style="width: 32px; height: 32px; background: rgba(46, 204, 113, 0.1);">
                                            <i class="ri-hashtag" style="color: #2ecc71;"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Modelo</small>
                                            <span class="fw-semibold" id="view-modelo">-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px; background: rgba(0, 217, 165, 0.1);">
                                            <i class="ri-money-dollar-circle-line" style="color: #00d9a5;"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Precio Base</small>
                                            <span class="fw-semibold" id="view-precio">-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-start">
                                        <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px; background: rgba(30, 60, 114, 0.1);">
                                            <i class="ri-file-text-line" style="color: #1e3c72;"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Descripción</small>
                                            <span class="fw-semibold" id="view-descripcion">-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px; background: rgba(46, 204, 113, 0.1);">
                                            <i class="ri-calendar-line" style="color: #2ecc71;"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Fecha de Creación</small>
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
                    <h5 class="modal-title" id="modalTitle">Agregar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="productoForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="id-field" />
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Tipo de Producto -->
                                <div class="mb-3">
                                    <label for="tipo-producto-field" class="form-label required">Tipo de Producto</label>
                                    <div class="input-group">
                                        <select id="tipo-producto-field" name="tipo_producto_id" class="form-select"
                                            required>
                                            <option value="">Seleccione un tipo...</option>
                                            @foreach($tiposProducto as $tipo)
                                                <option value="{{ $tipo->id }}" data-prefijo="{{ $tipo->codigo_prefijo }}">
                                                    {{ $tipo->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#addTipoModal" title="Agregar nuevo tipo">
                                            <i class="ri-add-line"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- Modelo -->
                                <div class="mb-3">
                                    <label for="modelo-field" class="form-label required">Modelo</label>
                                    <input type="text" id="modelo-field" name="modelo" class="form-control"
                                        placeholder="Ej: Polo Clásica, Cuello V, Drill Industrial" required />
                                </div>
                                <!-- Código (auto-generado) -->
                                <div class="mb-3">
                                    <label for="codigo-field" class="form-label">Código</label>
                                    <input type="text" id="codigo-field" name="codigo" class="form-control bg-light"
                                        readonly placeholder="Se genera automáticamente" />
                                    <small class="text-muted">El código se genera al seleccionar el tipo de producto</small>
                                </div>
                                <!-- Descripción -->
                                <div class="mb-3">
                                    <label for="descripcion-field" class="form-label">Descripción</label>
                                    <textarea id="descripcion-field" name="descripcion" class="form-control" rows="3"
                                        placeholder="Descripción adicional del producto"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- Precio Base -->
                                <div class="mb-3">
                                    <label for="precio-base-field" class="form-label required">Precio Base ($)</label>
                                    <input type="number" id="precio-base-field" name="precio_base" class="form-control"
                                        step="0.01" min="0" required placeholder="0.00" />
                                </div>
                                <!-- Imagen -->
                                <div class="mb-3">
                                    <label for="imagen-field" class="form-label">Imagen</label>
                                    <input type="file" id="imagen-field" name="imagen" class="form-control"
                                        accept="image/*" />
                                    <div id="imagen-preview" class="mt-2 text-center" style="display: none;">
                                        <img src="" alt="Vista previa de la imagen" class="img-fluid"
                                            style="max-width: 200px;">
                                    </div>
                                </div>
                                <!-- Estado -->
                                <div class="mb-3">
                                    <label for="estado-field" class="form-label">Estado</label>
                                    <select id="estado-field" name="estado" class="form-control">
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
                            <button type="submit" class="btn btn-success" id="add-btn">Agregar</button>
                            <button type="submit" class="btn btn-success" id="edit-btn"
                                style="display: none;">Actualizar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para gestionar Tipos de Producto -->
    <div class="modal fade" id="tiposModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="ri-settings-3-line me-2"></i>Gestionar Tipos de Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tipos-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Prefijo</th>
                                    <th>Productos</th>
                                    <th width="100">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tipos-tbody">
                                <!-- Se llena con JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTipoModal">
                        <i class="ri-add-line me-1"></i>Agregar Tipo
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar/editar Tipo de Producto -->
    <div class="modal fade" id="addTipoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="tipoModalTitle"><i class="ri-add-line me-2"></i>Agregar Tipo de Producto
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="tipoForm">
                    <div class="modal-body">
                        <input type="hidden" id="tipo-id-field" />
                        <div class="mb-3">
                            <label for="tipo-nombre-field" class="form-label required">Nombre del Tipo</label>
                            <input type="text" id="tipo-nombre-field" name="nombre" class="form-control"
                                placeholder="Ej: Chemise, Franela, Pantalón" required />
                        </div>
                        <div class="mb-3">
                            <label for="tipo-prefijo-field" class="form-label required">Prefijo de Código</label>
                            <input type="text" id="tipo-prefijo-field" name="codigo_prefijo" class="form-control"
                                placeholder="Ej: CHM, FRN, PNT (máx 5 letras)" maxlength="5" required
                                style="text-transform: uppercase;" />
                            <small class="text-muted">Se usará para generar códigos como CHM-001</small>
                        </div>
                        <div class="mb-3">
                            <label for="tipo-descripcion-field" class="form-label">Descripción</label>
                            <textarea id="tipo-descripcion-field" name="descripcion" class="form-control" rows="2"
                                placeholder="Descripción opcional"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" id="save-tipo-btn">Guardar</button>
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
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        // Configurar pdfMake para evitar errores de fuentes
        if (typeof pdfMake !== 'undefined' && typeof pdfFonts !== 'undefined') {
            pdfMake.vfs = pdfFonts.pdfMake.vfs;
        }

        // Configuración alternativa para evitar errores de fuentes
        if (typeof pdfMake !== 'undefined') {
            pdfMake.fonts = {
                Roboto: {
                    normal: 'Roboto-Regular.ttf',
                    bold: 'Roboto-Medium.ttf',
                    italics: 'Roboto-Italic.ttf',
                    bolditalics: 'Roboto-MediumItalic.ttf'
                }
            };
        }

        $(document).ready(function () {
            var table = $('#productos-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('productos.data') }}",
                columns: [
                    {
                        data: 'imagen',
                        name: 'imagen',
                        render: function (data) {
                            return data ? '<img src="' + data + '" alt="Imagen del producto" class="img-thumbnail" width="50">' : '<span class="text-muted">Sin imagen</span>';
                        }
                    },
                    {
                        data: 'codigo',
                        name: 'codigo',
                        render: function (data) {
                            return data ? '<span class="badge bg-dark">' + data + '</span>' : '-';
                        }
                    },
                    {
                        data: 'tipo_nombre',
                        name: 'tipo_nombre',
                        render: function (data) {
                            return '<span class="badge bg-primary">' + data + '</span>';
                        }
                    },
                    { data: 'modelo', name: 'modelo' },
                    {
                        data: 'precio_base',
                        name: 'precio_base',
                        render: function (data) {
                            return '$ ' + parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'estado',
                        name: 'estado',
                        render: function (data) {
                            return data ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
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
                order: [[1, 'desc']], // Cambiar el índice de ordenamiento (ahora la columna "Nombre" es la índice 1)
                dom: 'frtip',
                buttons: [
                    {
                        extend: 'copy',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4] // Excluir la columna de acciones (índice 5)
                        }
                    },
                    {
                        extend: 'excel',
                        exportOptions: {
                            columns: [1, 2, 3, 4] // Excluir la columna de imagen (índice 0) y de acciones (índice 5)
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
                        "copyTitle": "Copiado al portapapeles",
                        "copySuccess": {
                            "_": "%d filas copiadas al portapapeles",
                            "1": "1 fila copiada al portapapeles"
                        },
                        "csv": "CSV",
                        "excel": "Excel",
                        "pdf": "PDF",
                        "print": "Imprimir",
                        "colvis": "Visibilidad de Columna"
                    }
                }
            });

            // Vista previa de imagen
            $("#imagen-field").change(function () {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $("#imagen-preview img").attr('src', e.target.result);
                        $("#imagen-preview").show();
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });

            // Ver detalles
            $(document).on('click', '.view-item-btn', function () {
                var id = $(this).data('id');
                $.get("{{ route('productos.show', ':id') }}".replace(':id', id), function (data) {
                    // Mostrar imagen solo si existe
                    if (data.imagen) {
                        $("#producto-imagen").attr('src', data.imagen);
                        $("#producto-imagen-container").show();
                    } else {
                        $("#producto-imagen-container").hide();
                    }

                    $("#view-nombre").text(data.nombre);
                    $("#view-descripcion").text(data.descripcion || 'Sin descripción');
                    $("#view-modelo").text(data.modelo);
                    $("#view-precio").text('$ ' + parseFloat(data.precio_base).toFixed(2));
                    $("#view-created").text(data.created_at);
                    $("#viewModal").modal('show');
                });
            });

            // Editar
            $(document).on('click', '.edit-item-btn', function () {
                var id = $(this).data('id');
                $.get("{{ route('productos.show', ':id') }}".replace(':id', id), function (data) {
                    $("#modalTitle").text("Editar Producto");
                    $("#id-field").val(data.id);
                    $("#tipo-producto-field").val(data.tipo_producto_id);
                    $("#codigo-field").val(data.codigo);
                    $("#descripcion-field").val(data.descripcion);
                    $("#modelo-field").val(data.modelo);
                    $("#precio-base-field").val(data.precio_base);
                    $("#estado-field").val(data.estado ? '1' : '0');

                    if (data.imagen) {
                        $("#imagen-preview img").attr('src', data.imagen);
                        $("#imagen-preview").show();
                    }

                    $("#add-btn").hide();
                    $("#edit-btn").show();
                    $("#showModal").modal('show');
                });
            });

            // Generar código automático al seleccionar tipo o escribir modelo
            function actualizarCodigoPreview() {
                var tipoId = $("#tipo-producto-field").val();
                var modelo = $("#modelo-field").val();
                var isEditing = $("#id-field").val() !== "";
                
                if (tipoId && !isEditing) {
                    $.get("{{ url('tipo-productos') }}/" + tipoId + "/proximo-codigo", {modelo: modelo}, function(response) {
                        $("#codigo-field").val(response.codigo);
                    });
                } else if (!tipoId) {
                    $("#codigo-field").val("");
                }
            }
            
            $("#tipo-producto-field").on("change", actualizarCodigoPreview);
            
            // Actualizar código cuando el usuario escribe el modelo (con delay)
            var modeloTimer;
            $("#modelo-field").on("keyup", function() {
                clearTimeout(modeloTimer);
                modeloTimer = setTimeout(actualizarCodigoPreview, 500);
            });

            // Enviar formulario
            $("#productoForm").on("submit", function (e) {
                e.preventDefault();
                var id = $("#id-field").val();
                var url = id ? "{{ route('productos.update', ':id') }}".replace(':id', id) : "{{ route('productos.store') }}";
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
                            url: "{{ route('productos.destroy', ':id') }}".replace(':id', id),
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
                                    text: 'No se pudo eliminar el producto'
                                });
                            }
                        });
                    }
                });
            });

            // Limpiar modal al cerrar
            $("#showModal").on("hidden.bs.modal", function () {
                $("#modalTitle").text("Agregar Producto");
                $("#productoForm")[0].reset();
                $("#id-field").val("");
                $("#codigo-field").val("");
                $("#imagen-preview").hide();
                $("#add-btn").show();
                $("#edit-btn").hide();
            });

            // ===============================
            // Funciones para Tipos de Producto
            // ===============================

            // Cargar tipos al abrir modal de gestión
            $("#tiposModal").on("show.bs.modal", function () {
                cargarTipos();
            });

            function cargarTipos() {
                $.get("{{ route('tipo-productos.index') }}", function (tipos) {
                    var tbody = $("#tipos-tbody");
                    tbody.empty();

                    tipos.forEach(function (tipo) {
                        tbody.append(`
                                <tr>
                                    <td>${tipo.nombre}</td>
                                    <td><span class="badge bg-secondary">${tipo.codigo_prefijo}</span></td>
                                    <td><span class="badge bg-info">${tipo.contador}</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary edit-tipo-btn" 
                                            data-id="${tipo.id}" 
                                            data-nombre="${tipo.nombre}" 
                                            data-prefijo="${tipo.codigo_prefijo}"
                                            data-descripcion="${tipo.descripcion || ''}">
                                            <i class="ri-pencil-line"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-tipo-btn" data-id="${tipo.id}">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </td>
                                </tr>
                            `);
                    });
                });
            }

            // Editar tipo
            $(document).on("click", ".edit-tipo-btn", function () {
                var id = $(this).data("id");
                var nombre = $(this).data("nombre");
                var prefijo = $(this).data("prefijo");
                var descripcion = $(this).data("descripcion");

                $("#tipo-id-field").val(id);
                $("#tipo-nombre-field").val(nombre);
                $("#tipo-prefijo-field").val(prefijo);
                $("#tipo-descripcion-field").val(descripcion);
                $("#tipoModalTitle").html('<i class="ri-pencil-line me-2"></i>Editar Tipo de Producto');

                $("#tiposModal").modal('hide');
                $("#addTipoModal").modal('show');
            });

            // Eliminar tipo
            $(document).on("click", ".delete-tipo-btn", function () {
                var id = $(this).data("id");

                Swal.fire({
                    title: '¿Eliminar tipo?',
                    text: "Solo se puede eliminar si no tiene productos asociados",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('tipo-productos') }}/" + id,
                            method: "DELETE",
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            success: function (response) {
                                cargarTipos();
                                Swal.fire('Eliminado', response.message, 'success');
                            },
                            error: function (xhr) {
                                Swal.fire('Error', xhr.responseJSON.message, 'error');
                            }
                        });
                    }
                });
            });

            // Guardar tipo
            $("#tipoForm").on("submit", function (e) {
                e.preventDefault();

                var id = $("#tipo-id-field").val();
                var url = id ? "{{ url('tipo-productos') }}/" + id : "{{ route('tipo-productos.store') }}";
                var method = id ? "PUT" : "POST";

                $.ajax({
                    url: url,
                    method: method,
                    data: {
                        nombre: $("#tipo-nombre-field").val(),
                        codigo_prefijo: $("#tipo-prefijo-field").val().toUpperCase(),
                        descripcion: $("#tipo-descripcion-field").val()
                    },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        $("#addTipoModal").modal('hide');

                        // Actualizar select de tipos
                        actualizarSelectTipos();

                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    },
                    error: function (xhr) {
                        var errors = xhr.responseJSON.errors || {};
                        var message = xhr.responseJSON.message || 'Error al guardar';
                        Swal.fire('Error', message, 'error');
                    }
                });
            });

            // Actualizar select de tipos después de agregar uno nuevo
            function actualizarSelectTipos() {
                $.get("{{ route('tipo-productos.index') }}", function (tipos) {
                    var select = $("#tipo-producto-field");
                    select.find("option:not(:first)").remove();

                    tipos.forEach(function (tipo) {
                        select.append(`<option value="${tipo.id}" data-prefijo="${tipo.codigo_prefijo}">${tipo.nombre}</option>`);
                    });
                });
            }

            // Limpiar modal de tipo al cerrar
            $("#addTipoModal").on("hidden.bs.modal", function () {
                $("#tipoForm")[0].reset();
                $("#tipo-id-field").val("");
                $("#tipoModalTitle").html('<i class="ri-add-line me-2"></i>Agregar Tipo de Producto');
            });
        });
    </script>
@endpush