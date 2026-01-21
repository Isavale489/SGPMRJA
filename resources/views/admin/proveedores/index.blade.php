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
                        <h5 class="card-title mb-0 flex-grow-1">Listado de Proveedores</h5>
                        <h5 class="card-title mb-0 flex-grow-1">Listado de Proveedores</h5>
                        <div class="flex-shrink-0 d-flex align-items-center gap-3">
                            <!-- Buscador Personalizado -->
                            <div class="search-box">
                                <input type="text" class="form-control form-control-sm" id="custom-search-input" placeholder="Buscar proveedor...">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                            <div class="d-flex gap-2">
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
                </div>
                <div class="card-body">
                    <table id="proveedores-table" class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Documento</th>
                                <th>Nombre/Razón Social</th>
                                <th>Teléfono</th>
                                <th>Email</th>
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
                        <strong>Tipo de Proveedor:</strong>
                        <p id="view-tipo" class="text-muted mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Nombre/Razón Social:</strong>
                        <p id="view-nombre" class="text-muted mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Documento/RIF:</strong>
                        <p id="view-documento" class="text-muted mb-0"></p>
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
                    <div class="mb-3" id="view-contacto-section">
                        <strong>Persona de Contacto:</strong>
                        <p id="view-contacto" class="text-muted mb-0"></p>
                    </div>
                    <div class="mb-3" id="view-telefono-contacto-section">
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
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="modalTitle">Agregar Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="proveedorForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="id-field" />

                        <!-- Fila 1: Tipo de Proveedor + Estatus -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tipo-proveedor-field" class="form-label required">Tipo de Proveedor</label>
                                <select id="tipo-proveedor-field" name="tipo_proveedor" class="form-select" required>
                                    <option value="juridico">Jurídico (Empresa)</option>
                                    <option value="natural">Natural (Persona)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label d-block">Estatus</label>
                                <div class="form-check form-switch form-switch-success mt-2">
                                    <input type="hidden" name="estado" value="0" />
                                    <input class="form-check-input" type="checkbox" role="switch" id="estado-field"
                                        name="estado" value="1" checked />
                                    <label class="form-check-label" for="estado-field" id="estado-label">Activo</label>
                                </div>
                            </div>
                        </div>

                        <!-- CAMPOS PARA PROVEEDOR JURÍDICO (EMPRESA) -->
                        <div id="campos-juridico">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="rif-field" class="form-label required">RIF</label>
                                    <div class="input-group">
                                        <select class="form-select" id="rif-prefix-field" style="max-width: 80px;">
                                            <option value="J-">J-</option>
                                            <option value="V-">V-</option>
                                            <option value="G-">G-</option>
                                            <option value="E-">E-</option>
                                        </select>
                                        <input type="text" id="rif-number-field" class="form-control"
                                            placeholder="Ej: 123456789" maxlength="10" />
                                    </div>
                                    <input type="hidden" id="rif-field" name="rif" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="razon-social-field" class="form-label required">Razón Social</label>
                                    <input type="text" id="razon-social-field" name="razon_social" class="form-control"
                                        maxlength="200" placeholder="Nombre de la empresa" />
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="direccion-jur-field" class="form-label required">Dirección</label>
                                <input type="text" id="direccion-jur-field" name="direccion" class="form-control"
                                    maxlength="500" placeholder="Dirección de la empresa" />
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="telefono-jur-field" class="form-label required">Teléfono</label>
                                    <div class="input-group">
                                        <select class="form-select" id="telefono-jur-prefix-field"
                                            style="max-width: 100px; min-width: 100px;">
                                            <option value="0212">0212</option>
                                            <option value="0251">0251</option>
                                            <option value="0241">0241</option>
                                            <option value="0255">0255</option>
                                            <option value="0412">0412</option>
                                            <option value="0414">0414</option>
                                            <option value="0424" selected>0424</option>
                                            <option value="0416">0416</option>
                                            <option value="0426">0426</option>
                                        </select>
                                        <input type="text" id="telefono-jur-number-field" class="form-control"
                                            placeholder="1234567" maxlength="7" />
                                    </div>
                                    <input type="hidden" id="telefono-jur-field" name="telefono" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email-jur-field" class="form-label required">Email</label>
                                    <input type="email" id="email-jur-field" name="email" class="form-control"
                                        placeholder="correo@empresa.com" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="contacto-field" class="form-label">Persona de Contacto</label>
                                    <input type="text" id="contacto-field" name="contacto" class="form-control"
                                        maxlength="100" placeholder="Nombre del contacto" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="telefono-contacto-field" class="form-label">Teléfono de Contacto</label>
                                    <div class="input-group">
                                        <select class="form-select" id="telefono-contacto-prefix-field"
                                            style="max-width: 100px; min-width: 100px;">
                                            <option value="0412">0412</option>
                                            <option value="0414">0414</option>
                                            <option value="0424" selected>0424</option>
                                            <option value="0416">0416</option>
                                            <option value="0426">0426</option>
                                        </select>
                                        <input type="text" id="telefono-contacto-number-field" class="form-control"
                                            placeholder="1234567" maxlength="7" />
                                    </div>
                                    <input type="hidden" id="telefono-contacto-field" name="telefono_contacto" />
                                </div>
                            </div>
                        </div>

                        <!-- CAMPOS PARA PROVEEDOR NATURAL (PERSONA) -->
                        <div id="campos-natural" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre-field" class="form-label required">Nombre</label>
                                    <input type="text" id="nombre-field" name="nombre" class="form-control" maxlength="100"
                                        placeholder="Nombre" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="apellido-field" class="form-label required">Apellido</label>
                                    <input type="text" id="apellido-field" name="apellido" class="form-control"
                                        maxlength="100" placeholder="Apellido" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
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
                                            class="form-control" placeholder="Ej: 12345678" maxlength="15" />
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="telefono-nat-field" class="form-label required">Teléfono</label>
                                    <div class="input-group">
                                        <select class="form-select" id="telefono-nat-prefix-field"
                                            style="max-width: 100px; min-width: 100px;">
                                            <option value="0412">0412</option>
                                            <option value="0422">0422</option>
                                            <option value="0414">0414</option>
                                            <option value="0424" selected>0424</option>
                                            <option value="0416">0416</option>
                                            <option value="0426">0426</option>
                                        </select>
                                        <input type="text" id="telefono-nat-number-field" class="form-control"
                                            placeholder="1234567" maxlength="7" />
                                    </div>
                                    <input type="hidden" id="telefono-nat-field" name="telefono" />
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email-nat-field" class="form-label required">Email</label>
                                <input type="email" id="email-nat-field" name="email" class="form-control"
                                    placeholder="correo@email.com" />
                            </div>
                            <div class="mb-3">
                                <label for="direccion-nat-field" class="form-label required">Dirección</label>
                                <input type="text" id="direccion-nat-field" name="direccion" class="form-control"
                                    maxlength="255" placeholder="Dirección completa" />
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="estado-territorial-field" class="form-label">Estado</label>
                                    <select id="estado-territorial-field" name="estado_territorial" class="form-select">
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
                                <div class="col-md-6 mb-3">
                                    <label for="ciudad-field" class="form-label">Municipio</label>
                                    <select id="ciudad-field" name="ciudad" class="form-select">
                                        <option value="">Primero seleccione un estado</option>
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
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
    <script src="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/municipios-venezuela.js') }}"></script>

    <script>
        $(document).ready(function () {
            // Toggle campos según tipo de proveedor
            function toggleCampos() {
                var tipo = $('#tipo-proveedor-field').val();
                if (tipo === 'natural') {
                    $('#campos-juridico').hide();
                    $('#campos-natural').show();
                    // Limpiar campos jurídicos
                    $('#rif-number-field, #razon-social-field, #direccion-jur-field, #telefono-jur-field, #email-jur-field, #contacto-field, #telefono-contacto-field').val('');
                } else {
                    $('#campos-juridico').show();
                    $('#campos-natural').hide();
                    // Limpiar campos naturales
                    $('#nombre-field, #apellido-field, #documento-identidad-field, #telefono-nat-field, #email-nat-field, #direccion-nat-field, #ciudad-field, #estado-territorial-field').val('');
                }
            }

            $('#tipo-proveedor-field').on('change', toggleCampos);

            // Listener para actualizar label del checkbox de estatus
            $("#estado-field").on('change', function () {
                if ($(this).is(':checked')) {
                    $("#estado-label").text('Activo');
                } else {
                    $("#estado-label").text('Inactivo');
                }
            });

            // Dropdown dependiente: Poblar municipios cuando cambia el estado
            $("#estado-territorial-field").on('change', function () {
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

            var table = $('#proveedores-table').DataTable({
                ajax: { url: "{{ route('proveedores.data') }}", dataSrc: 'data' },
                columns: [
                    {
                        data: 'tipo_display',
                        name: 'tipo_proveedor',
                        render: function (data, type, row) {
                            if (row.tipo_proveedor === 'natural') {
                                return '<span class="badge bg-info">Natural</span>';
                            }
                            return '<span class="badge bg-primary">Jurídico</span>';
                        }
                    },
                    { data: 'documento_display', name: 'rif' },
                    { data: 'nombre_display', name: 'razon_social' },
                    { data: 'telefono_display', name: 'telefono' },
                    { data: 'email_display', name: 'email' },
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
                            var isAdmin = {{ Auth::user()->isAdmin() ? 'true' : 'false' }};
                            var editDeleteBtns = isAdmin ? `
                                                    <button class="btn btn-sm btn-soft-success edit-item-btn" data-id="${data}" title="Editar">
                                                        <i class="ri-pencil-fill"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-soft-danger remove-item-btn" data-id="${data}" title="Eliminar">
                                                        <i class="ri-delete-bin-fill"></i>
                                                    </button>
                                                ` : '';
                            return `
                                                    <div class="d-flex gap-2 justify-content-center">
                                                        <button class="btn btn-sm btn-soft-info view-item-btn" data-id="${data}" title="Ver">
                                                            <i class="ri-eye-fill"></i>
                                                        </button>
                                                        ${editDeleteBtns}
                                                    </div>
                                                `;
                        }
                    }
                ],
                order: [[2, 'asc']],
                dom: 'rtip',
                language: {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sZeroRecords": "No se encontraron resultados",
                    "sEmptyTable": "Ningún dato disponible en esta tabla",
                    "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "sSearch": "Buscar:",
                    "oPaginate": {
                        "sFirst": "Primero",
                        "sLast": "Último",
                        "sNext": "Siguiente",
                        "sPrevious": "Anterior"
                    }
                }
            });

            // Buscador personalizado
            $('#custom-search-input').on('keyup', function () {
                table.search(this.value).draw();
            });

            // Ver detalles
            $(document).on('click', '.view-item-btn', function () {
                var id = $(this).data('id');
                $.get("{{ route('proveedores.show', ':id') }}".replace(':id', id), function (data) {
                    var tipoText = data.tipo_proveedor === 'natural' ? 'Natural (Persona)' : 'Jurídico (Empresa)';
                    $("#view-tipo").text(tipoText);
                    $("#view-nombre").text(data.nombre_display || data.razon_social);
                    $("#view-documento").text(data.documento_display || data.rif);
                    $("#view-telefono").text(data.telefono);
                    $("#view-email").text(data.email || 'No especificado');
                    $("#view-direccion").text(data.direccion || 'No especificada');

                    // Mostrar/ocultar campos de contacto según tipo
                    if (data.tipo_proveedor === 'juridico') {
                        $("#view-contacto-section").show();
                        $("#view-telefono-contacto-section").show();
                        $("#view-contacto").text(data.contacto || 'No especificado');
                        $("#view-telefono-contacto").text(data.telefono_contacto || 'No especificado');
                    } else {
                        $("#view-contacto-section").hide();
                        $("#view-telefono-contacto-section").hide();
                    }

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
                    $("#tipo-proveedor-field").val(data.tipo_proveedor || 'juridico');
                    $("#estado-field").val(data.estado ? '1' : '0');

                    toggleCampos();

                    if (data.tipo_proveedor === 'natural') {
                        // Cargar datos de persona natural
                        $("#nombre-field").val(data.nombre);
                        $("#apellido-field").val(data.apellido);
                        $("#tipo-documento-field").val(data.tipo_documento || 'V-');
                        $("#documento-identidad-field").val(data.documento_identidad);

                        // Separar teléfono en prefijo y número
                        var telefono = data.telefono || '';
                        var telMatch = telefono.match(/^(0412|0422|0414|0424|0416|0426)-(.+)$/);
                        if (telMatch) {
                            $("#telefono-nat-prefix-field").val(telMatch[1]);
                            $("#telefono-nat-number-field").val(telMatch[2]);
                        } else {
                            $("#telefono-nat-prefix-field").val('0424');
                            $("#telefono-nat-number-field").val(telefono.replace(/^0\d{3}-?/, ''));
                        }

                        $("#email-nat-field").val(data.email);
                        $("#direccion-nat-field").val(data.direccion);

                        // Cargar estado y luego municipio
                        if (data.estado_territorial) {
                            $("#estado-territorial-field").val(data.estado_territorial);
                            // Disparar evento change para cargar municipios
                            $("#estado-territorial-field").trigger('change');
                            // Esperar a que se carguen los municipios y seleccionar el correcto
                            setTimeout(function () {
                                $("#ciudad-field").val(data.ciudad);
                            }, 100);
                        }
                    } else {
                        // Cargar datos de empresa jurídica
                        var rif = data.rif || '';
                        var rifMatch = rif.match(/^(V-|J-|E-|G-)(.+)$/);
                        if (rifMatch) {
                            $("#rif-prefix-field").val(rifMatch[1]);
                            $("#rif-number-field").val(rifMatch[2]);
                        } else {
                            $("#rif-prefix-field").val('J-');
                            $("#rif-number-field").val(rif);
                        }
                        $("#razon-social-field").val(data.razon_social);
                        $("#direccion-jur-field").val(data.direccion);

                        // Separar teléfono principal en prefijo y número
                        var telJur = data.telefono || '';
                        var telJurMatch = telJur.match(/^(0212|0251|0241|0255|0412|0414|0424|0416|0426)-(.+)$/);
                        if (telJurMatch) {
                            $("#telefono-jur-prefix-field").val(telJurMatch[1]);
                            $("#telefono-jur-number-field").val(telJurMatch[2]);
                        } else {
                            $("#telefono-jur-prefix-field").val('0424');
                            $("#telefono-jur-number-field").val(telJur.replace(/^0\d{3}-?/, ''));
                        }

                        $("#email-jur-field").val(data.email);
                        $("#contacto-field").val(data.contacto);

                        // Separar teléfono de contacto en prefijo y número
                        var telContacto = data.telefono_contacto || '';
                        var telContactoMatch = telContacto.match(/^(0412|0414|0424|0416|0426)-(.+)$/);
                        if (telContactoMatch) {
                            $("#telefono-contacto-prefix-field").val(telContactoMatch[1]);
                            $("#telefono-contacto-number-field").val(telContactoMatch[2]);
                        } else {
                            $("#telefono-contacto-prefix-field").val('0424');
                            $("#telefono-contacto-number-field").val(telContacto.replace(/^0\d{3}-?/, ''));
                        }
                    }

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
                var tipo = $('#tipo-proveedor-field').val();

                var formData = new FormData(this);
                formData.set('tipo_proveedor', tipo);

                // Preparar datos según tipo
                if (tipo === 'juridico') {
                    var rifPrefix = $('#rif-prefix-field').val();
                    var rifNumber = $('#rif-number-field').val();
                    formData.set('rif', rifPrefix + rifNumber);

                    // Concatenar teléfono principal: prefijo-número
                    var telefonoJurCompleto = $('#telefono-jur-prefix-field').val() + '-' + $('#telefono-jur-number-field').val();
                    formData.set('telefono', telefonoJurCompleto);

                    formData.set('email', $('#email-jur-field').val());
                    formData.set('direccion', $('#direccion-jur-field').val());

                    // Concatenar teléfono de contacto
                    var telefonoContactoCompleto = $('#telefono-contacto-prefix-field').val() + '-' + $('#telefono-contacto-number-field').val();
                    formData.set('telefono_contacto', telefonoContactoCompleto);
                } else {
                    // Concatenar teléfono: prefijo-número
                    var telefonoCompleto = $('#telefono-nat-prefix-field').val() + '-' + $('#telefono-nat-number-field').val();
                    formData.set('telefono', telefonoCompleto);
                    formData.set('email', $('#email-nat-field').val());
                    formData.set('direccion', $('#direccion-nat-field').val());
                }

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
                        var errors = xhr.responseJSON?.errors || {};
                        var errorMessage = xhr.responseJSON?.error || '';
                        if (Object.keys(errors).length > 0) {
                            errorMessage = '';
                            $.each(errors, function (key, value) {
                                errorMessage += value[0] + '<br>';
                            });
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errorMessage || 'Ocurrió un error al procesar la solicitud'
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
                $("#tipo-proveedor-field").val("juridico");
                toggleCampos();
                $("#add-btn").show();
                $("#edit-btn").hide();
            });
        });
    </script>
@endpush