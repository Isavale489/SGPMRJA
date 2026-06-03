@extends('admin.layouts.app')

@push('styles')
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Gestión de Departamentos</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Gestión General</a></li>
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Recursos Humanos</a></li>
                        <li class="breadcrumb-item active">Departamentos</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-maestros">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Listado de Departamentos</h5>
                        <div class="flex-shrink-0 d-flex align-items-center gap-3">
                            @if($historial)
                                <a href="{{ route('departamentos.index') }}" class="btn-historial btn-historial-volver">
                                    <i class="ri-arrow-left-line"></i> Solo Activos
                                </a>
                            @else
                                <a href="{{ route('departamentos.index', ['historial' => true]) }}" class="btn-historial btn-historial-ver">
                                    <i class="ri-time-line"></i> Ver Historial
                                </a>
                                <button type="button" class="btn btn-success add-btn" id="create-btn">
                                    <i class="ri-add-line align-bottom me-1"></i> Agregar Departamento
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="advanced-filters-wrapper navy-theme" id="advanced-filters">
                        <div class="navy-filter-header is-collapsed">
                            <div class="navy-header-search w-100">
                                <i class="ri-search-line"></i>
                                <input type="text" id="custom-search-input"
                                    class="navy-search-input"
                                    placeholder="Buscar departamento..."
                                    autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <table id="departamentos-table" class="table table-bordered table-striped table-sm align-middle table-maestro">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th class="text-center">Cargos</th>
                                <th class="text-center">Empleados</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Formulario Crear / Editar --}}
    <div class="modal fade atlantico-modal" id="formModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-guard-id-field="form-depto-id">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="formModalTitle">Nuevo Departamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="departamentoForm" autocomplete="off" novalidate>
                    @csrf
                    <input type="hidden" id="form-depto-id" />
                    <div class="modal-body p-4">
                        <div class="mb-0">
                            <label for="form-depto-nombre" class="form-label">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="form-depto-nombre" class="form-control"
                                placeholder="Ej: Producción, Logística, Administración" maxlength="100" required />
                            <div id="form-depto-error" class="invalid-feedback"></div>
                            <small class="text-muted d-block mt-1">Mínimo 3 caracteres, máximo 100.</small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Cerrar
                        </button>
                        <button type="button" class="btn btn-success" id="add-btn">
                            <i class="ri-add-line me-1"></i> Agregar
                        </button>
                        <button type="button" class="btn btn-success" id="edit-btn" style="display:none;">
                            <i class="ri-save-line me-1"></i> Actualizar
                        </button>
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
    <script>
        $(function () {
            // Tooltips
            $(document).on('mouseenter', '[title]', function () { $(this).tooltip({ container: 'body' }).tooltip('show'); });
            $(document).on('mouseleave', '[title]', function () { $(this).tooltip('dispose'); });

            const HISTORIAL = @json($historial);

            // ──────────────────────────────────────────────
            // DataTable
            // ──────────────────────────────────────────────
            const table = $('#departamentos-table').DataTable({
                ajax: {
                    url: "{{ route('departamentos.index') }}" + (HISTORIAL ? '?historial=true' : ''),
                    dataSrc: ''
                },
                columns: [
                    { data: 'nombre' },
                    {
                        data: 'cargos_count',
                        className: 'text-center',
                        render: function (d) {
                            return '<span class="badge bg-info-subtle text-info">' + (d ?? 0) + '</span>';
                        }
                    },
                    {
                        data: 'empleados_count',
                        className: 'text-center',
                        render: function (d) {
                            return '<span class="badge bg-success-subtle text-success">' + (d ?? 0) + '</span>';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        className: 'text-center',
                        render: function (data, type, row) {
                            return generateButtons(row);
                        }
                    }
                ],
                order: [[0, 'asc']],
                dom: 'rtip',
                language: lenguajeData,
                autoWidth: false
            });

            function generateButtons(row) {
                const id     = row.id;
                const nombre = $('<div>').text(row.nombre).html();

                var items;
                if (HISTORIAL) {
                    items = `<li><button type="button" class="dropdown-item act-item act-restore restore-btn" data-id="${id}"><span class="act-ic"><i class="ri-arrow-go-back-line"></i></span>Restaurar</button></li>`;
                } else {
                    items =
                        `<li><button type="button" class="dropdown-item act-item act-edit edit-btn" data-id="${id}" data-nombre="${nombre}"><span class="act-ic"><i class="ri-pencil-fill"></i></span>Editar</button></li>` +
                        `<li><button type="button" class="dropdown-item act-item act-del delete-btn" data-id="${id}"><span class="act-ic"><i class="ri-forbid-line"></i></span>Inhabilitar</button></li>`;
                }
                return `<div class="d-flex gap-1 justify-content-center align-items-center">` +
                    `<div class="dropdown d-inline-block">` +
                        `<button class="btn btn-sm btn-soft-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Acciones"><i class="ri-more-2-fill"></i></button>` +
                        `<ul class="dropdown-menu dropdown-menu-end actions-menu">${items}</ul>` +
                    `</div>` +
                `</div>`;
            }

            // ──────────────────────────────────────────────
            // Búsqueda con debounce
            // ──────────────────────────────────────────────
            let searchTimeout = null;
            $('#custom-search-input').on('keyup', function () {
                clearTimeout(searchTimeout);
                const val = this.value;
                searchTimeout = setTimeout(function () { table.search(val).draw(); }, 300);
            });

            $(window).on('resize', function () { table.columns.adjust(); });
            setTimeout(function () { table.columns.adjust(); }, 100);

            // ──────────────────────────────────────────────
            // Modal: Crear
            // ──────────────────────────────────────────────
            $('#create-btn').on('click', function () {
                resetForm();
                $('#formModalTitle').text('Nuevo Departamento');
                $('#add-btn').show();
                $('#edit-btn').hide();
                $('#formModal').modal('show');
            });

            $('#formModal').on('shown.bs.modal', function () {
                $('#form-depto-nombre').focus();
            });

            // ──────────────────────────────────────────────
            // Validaciones
            // ──────────────────────────────────────────────
            $('#form-depto-nombre').on('blur input', function () {
                validarNombre();
            });

            function validarNombre() {
                const $input = $('#form-depto-nombre');
                const $err   = $('#form-depto-error');
                const val    = $input.val().trim();

                if (val.length === 0) {
                    $input.removeClass('is-valid').addClass('is-invalid');
                    $err.text('El nombre es obligatorio.').show();
                    return false;
                }
                if (val.length < 3) {
                    $input.removeClass('is-valid').addClass('is-invalid');
                    $err.text('Mínimo 3 caracteres.').show();
                    return false;
                }
                $input.removeClass('is-invalid').addClass('is-valid');
                $err.hide();
                return true;
            }

            function resetForm() {
                $('#departamentoForm')[0].reset();
                $('#form-depto-id').val('');
                $('#form-depto-nombre').removeClass('is-invalid is-valid');
                $('#form-depto-error').hide();
            }

            // ──────────────────────────────────────────────
            // Submit: Crear
            // ──────────────────────────────────────────────
            $('#add-btn').on('click', function () {
                if (!validarNombre()) return;

                const $btn   = $(this);
                const nombre = $('#form-depto-nombre').val().trim();
                const original = $btn.html();
                $btn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i>Agregando...');

                $.ajax({
                    url: "{{ route('departamentos.store') }}",
                    method: 'POST',
                    data: { nombre: nombre, _token: '{{ csrf_token() }}' },
                    success: function (resp) {
                        $('#formModal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: '¡Listo!', text: resp.message, showConfirmButton: false, timer: 1500 });
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.errors?.nombre?.[0] || xhr.responseJSON?.message || 'Error al guardar.';
                        $('#form-depto-nombre').removeClass('is-valid').addClass('is-invalid');
                        $('#form-depto-error').text(msg).show();
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html(original);
                    }
                });
            });

            // ──────────────────────────────────────────────
            // Editar
            // ──────────────────────────────────────────────
            $(document).on('click', '.edit-btn', function () {
                resetForm();
                $('#form-depto-id').val($(this).data('id'));
                $('#form-depto-nombre').val($(this).data('nombre'));
                $('#formModalTitle').text('Editar Departamento');
                $('#add-btn').hide();
                $('#edit-btn').show();
                $('#formModal').modal('show');
            });

            $('#edit-btn').on('click', function () {
                if (!validarNombre()) return;

                const $btn   = $(this);
                const id     = $('#form-depto-id').val();
                const nombre = $('#form-depto-nombre').val().trim();
                const original = $btn.html();
                $btn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i>Actualizando...');

                $.ajax({
                    url: "{{ url('departamentos') }}/" + id,
                    method: 'PUT',
                    data: { nombre: nombre, _token: '{{ csrf_token() }}' },
                    success: function (resp) {
                        $('#formModal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: '¡Actualizado!', text: resp.message, showConfirmButton: false, timer: 1500 });
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.errors?.nombre?.[0] || xhr.responseJSON?.message || 'Error al guardar.';
                        $('#form-depto-nombre').removeClass('is-valid').addClass('is-invalid');
                        $('#form-depto-error').text(msg).show();
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html(original);
                    }
                });
            });

            // ──────────────────────────────────────────────
            // Inhabilitar
            // ──────────────────────────────────────────────
            $(document).on('click', '.delete-btn', function () {
                const id = $(this).data('id');
                Swal.fire({
                    title: '¿Inhabilitar departamento?',
                    text: 'Solo se puede inhabilitar si no tiene cargos ni empleados asociados.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, inhabilitar',
                    cancelButtonText: 'Cancelar',
                    customClass: { confirmButton: 'btn btn-danger w-xs me-2', cancelButton: 'btn btn-light w-xs' },
                    buttonsStyling: false
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    $.ajax({
                        url: "{{ url('departamentos') }}/" + id,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (resp) {
                            table.ajax.reload(null, false);
                            Swal.fire({ icon: 'success', title: '¡Listo!', text: resp.message, showConfirmButton: false, timer: 1500 });
                        },
                        error: function (xhr) {
                            Swal.fire({ icon: 'error', title: 'No se puede inhabilitar', text: xhr.responseJSON?.message || 'Error al procesar' });
                        }
                    });
                });
            });

            // ──────────────────────────────────────────────
            // Restaurar
            // ──────────────────────────────────────────────
            $(document).on('click', '.restore-btn', function () {
                const id = $(this).data('id');
                Swal.fire({
                    title: '¿Restaurar departamento?',
                    text: 'Volverá a estar disponible para ser asignado.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, restaurar',
                    cancelButtonText: 'Cancelar',
                    customClass: { confirmButton: 'btn btn-success w-xs me-2', cancelButton: 'btn btn-light w-xs' },
                    buttonsStyling: false
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    $.ajax({
                        url: "{{ url('departamentos') }}/" + id + "/restore",
                        method: 'PATCH',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (resp) {
                            table.ajax.reload(null, false);
                            Swal.fire({ icon: 'success', title: '¡Restaurado!', text: resp.message, showConfirmButton: false, timer: 1500 });
                        },
                        error: function (xhr) {
                            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Error al restaurar' });
                        }
                    });
                });
            });
        });
    </script>
@endpush
