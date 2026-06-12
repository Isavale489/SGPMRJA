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
                <h4 class="mb-sm-0">Gestión de Cargos</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Gestión General</a></li>
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Recursos Humanos</a></li>
                        <li class="breadcrumb-item active">Cargos</li>
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
                        <h5 class="card-title mb-0 flex-grow-1">Listado de Cargos</h5>
                        <div class="flex-shrink-0 d-flex align-items-center gap-3">
                            @if($historial)
                                <a href="{{ route('cargos.index') }}" class="btn-historial btn-historial-volver">
                                    <i class="ri-arrow-left-line"></i> Solo Activos
                                </a>
                            @else
                                <a href="{{ route('cargos.index', ['historial' => true]) }}" class="btn-historial btn-historial-ver">
                                    <i class="ri-archive-line"></i> Inhabilitados
                                </a>
                                <button type="button" class="btn btn-success add-btn" id="create-btn">
                                    <i class="ri-add-line align-bottom me-1"></i> Agregar Cargo
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="advanced-filters-wrapper navy-theme" id="advanced-filters">
                        <div class="navy-filter-header is-collapsed">
                            <div class="navy-header-search">
                                <i class="ri-search-line"></i>
                                <input type="text" id="custom-search-input"
                                    class="navy-search-input"
                                    placeholder="Buscar cargo..."
                                    autocomplete="off">
                            </div>
                            <div class="navy-header-divider"></div>
                            <button class="navy-filter-btn collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#filters-collapse-body"
                                aria-expanded="false" aria-controls="filters-collapse-body">
                                <i class="ri-filter-3-line"></i>
                                <span>Filtros</span>
                                <span class="navy-filter-badge d-none" id="active-filter-count"></span>
                                <i class="ri-arrow-down-s-line navy-filter-chevron"></i>
                            </button>
                        </div>
                        <div class="collapse" id="filters-collapse-body">
                            <div class="navy-filter-body">
                                <div class="row g-2 align-items-end">
                                    <div class="col-lg-6 col-md-12">
                                        <label class="navy-filter-label" for="filter-departamento">
                                            <i class="ri-building-line"></i> Departamento
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-departamento">
                                            <option value="">Todos los departamentos</option>
                                            @foreach($departamentos as $id => $nombre)
                                                <option value="{{ $id }}">{{ $nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-2">
                                    <button type="button" class="btn btn-navy-outline btn-sm" id="btn-clear-filters">
                                        <i class="ri-refresh-line me-1"></i>Limpiar filtros
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table id="cargos-table" class="table table-bordered table-striped table-sm align-middle table-maestro">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Departamento</th>
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
    <div class="modal fade atlantico-modal" id="formModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-guard-id-field="form-cargo-id">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="formModalTitle">Nuevo Cargo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="cargoForm" autocomplete="off" novalidate>
                    @csrf
                    <input type="hidden" id="form-cargo-id" />
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="form-cargo-depto" class="form-label">
                                Departamento <span class="text-danger">*</span>
                            </label>
                            <select id="form-cargo-depto" class="form-select" required>
                                <option value="">Seleccione un departamento...</option>
                                @foreach($departamentos as $id => $nombre)
                                    <option value="{{ $id }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                            <div id="form-cargo-depto-error" class="invalid-feedback"></div>
                        </div>
                        <div class="mb-0">
                            <label for="form-cargo-nombre" class="form-label">
                                Nombre del Cargo <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="form-cargo-nombre" class="form-control"
                                placeholder="Ej: Costurera, Cortador, Jefe de turno" maxlength="100" required />
                            <div id="form-cargo-error" class="invalid-feedback"></div>
                            <small class="text-muted d-block mt-1">Mínimo 3 caracteres, máximo 100. Único por departamento.</small>
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
            $(document).on('mouseenter', '[title]', function () { $(this).tooltip({ container: 'body' }).tooltip('show'); });
            $(document).on('mouseleave', '[title]', function () { $(this).tooltip('dispose'); });

            const HISTORIAL = @json($historial);

            // ──────────────────────────────────────────────
            // DataTable
            // ──────────────────────────────────────────────
            const table = $('#cargos-table').DataTable({
                ajax: {
                    url: "{{ route('cargos.index') }}",
                    dataSrc: '',
                    data: function (d) {
                        if (HISTORIAL) d.historial = 'true';
                        const dep = $('#filter-departamento').val();
                        if (dep) d.departamento_id = dep;
                    }
                },
                columns: [
                    { data: 'nombre' },
                    {
                        data: 'departamento.nombre',
                        defaultContent: '<span class="text-muted">—</span>',
                        render: function (d) {
                            return d ? $('<div>').text(d).html() : '<span class="text-muted">—</span>';
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
                        render: function (data, type, row) { return generateButtons(row); }
                    }
                ],
                order: [[0, 'asc']],
                dom: 'rtip',
                language: lenguajeData,
                autoWidth: false
            });

            function generateButtons(row) {
                const id = row.id;
                var items;
                if (HISTORIAL) {
                    items = `<li><button type="button" class="dropdown-item act-item act-restore restore-btn" data-id="${id}"><span class="act-ic"><i class="ri-arrow-go-back-line"></i></span>Restaurar</button></li>`;
                } else {
                    const payload = JSON.stringify({ id: row.id, nombre: row.nombre, departamento_id: row.departamento_id }).replace(/'/g, '&#39;');
                    items =
                        `<li><button type="button" class="dropdown-item act-item act-edit edit-btn" data-cargo='${payload}'><span class="act-ic"><i class="ri-pencil-fill"></i></span>Editar</button></li>` +
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
            // Búsqueda + filtros
            // ──────────────────────────────────────────────
            let searchTimeout = null;
            $('#custom-search-input').on('keyup', function () {
                clearTimeout(searchTimeout);
                const val = this.value;
                searchTimeout = setTimeout(function () { table.search(val).draw(); }, 300);
            });

            $('#filters-collapse-body').on('show.bs.collapse', function () {
                $('.navy-filter-header').removeClass('is-collapsed');
            }).on('hidden.bs.collapse', function () {
                $('.navy-filter-header').addClass('is-collapsed');
            });

            function updateFilterBadge() {
                let count = 0;
                if ($('#filter-departamento').val() !== '') count++;
                const $badge = $('#active-filter-count');
                if (count > 0) {
                    $badge.text(count).removeClass('d-none');
                } else {
                    $badge.addClass('d-none');
                }
            }

            $('#filter-departamento').on('change', function () {
                table.ajax.reload();
                updateFilterBadge();
            });

            $('#btn-clear-filters').on('click', function () {
                $('#filter-departamento').val('');
                $('#custom-search-input').val('');
                table.search('').ajax.reload();
                updateFilterBadge();
            });

            $(window).on('resize', function () { table.columns.adjust(); });
            setTimeout(function () { table.columns.adjust(); }, 100);

            // ──────────────────────────────────────────────
            // Modal: Crear
            // ──────────────────────────────────────────────
            $('#create-btn').on('click', function () {
                resetForm();
                // Si hay filtro activo de departamento, preseleccionar en el form
                const filtroDepto = $('#filter-departamento').val();
                if (filtroDepto) $('#form-cargo-depto').val(filtroDepto);
                $('#formModalTitle').text('Nuevo Cargo');
                $('#add-btn').show();
                $('#edit-btn').hide();
                $('#formModal').modal('show');
            });

            $('#formModal').on('shown.bs.modal', function () {
                $('#form-cargo-depto').focus();
            });

            // ──────────────────────────────────────────────
            // Validaciones
            // ──────────────────────────────────────────────
            $('#form-cargo-depto').on('change blur', function () { validarDepartamento(); });
            $('#form-cargo-nombre').on('blur input', function () { validarNombre(); });

            function validarDepartamento() {
                const $sel = $('#form-cargo-depto');
                const $err = $('#form-cargo-depto-error');
                if (!$sel.val()) {
                    $sel.removeClass('is-valid').addClass('is-invalid');
                    $err.text('Debe seleccionar un departamento.').show();
                    return false;
                }
                $sel.removeClass('is-invalid').addClass('is-valid');
                $err.hide();
                return true;
            }

            function validarNombre() {
                const $input = $('#form-cargo-nombre');
                const $err   = $('#form-cargo-error');
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
                $('#cargoForm')[0].reset();
                $('#form-cargo-id').val('');
                $('#form-cargo-nombre, #form-cargo-depto').removeClass('is-invalid is-valid');
                $('#form-cargo-error, #form-cargo-depto-error').hide();
            }

            // ──────────────────────────────────────────────
            // Submit: Crear
            // ──────────────────────────────────────────────
            $('#add-btn').on('click', function () {
                const okDepto  = validarDepartamento();
                const okNombre = validarNombre();
                if (!okDepto || !okNombre) return;

                const $btn   = $(this);
                const nombre = $('#form-cargo-nombre').val().trim();
                const dep    = $('#form-cargo-depto').val();
                const original = $btn.html();
                $btn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i>Agregando...');

                $.ajax({
                    url: "{{ route('cargos.store') }}",
                    method: 'POST',
                    data: { nombre: nombre, departamento_id: dep, _token: '{{ csrf_token() }}' },
                    success: function (resp) {
                        $('#formModal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: '¡Listo!', text: resp.message, showConfirmButton: false, timer: 1500 });
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.errors?.nombre?.[0]
                            || xhr.responseJSON?.errors?.departamento_id?.[0]
                            || xhr.responseJSON?.message
                            || 'Error al guardar.';
                        $('#form-cargo-nombre').removeClass('is-valid').addClass('is-invalid');
                        $('#form-cargo-error').text(msg).show();
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
                const c = $(this).data('cargo');
                $('#form-cargo-id').val(c.id);
                $('#form-cargo-nombre').val(c.nombre);
                $('#form-cargo-depto').val(c.departamento_id);
                $('#formModalTitle').text('Editar Cargo');
                $('#add-btn').hide();
                $('#edit-btn').show();
                $('#formModal').modal('show');
            });

            $('#edit-btn').on('click', function () {
                const okDepto  = validarDepartamento();
                const okNombre = validarNombre();
                if (!okDepto || !okNombre) return;

                const $btn   = $(this);
                const id     = $('#form-cargo-id').val();
                const nombre = $('#form-cargo-nombre').val().trim();
                const dep    = $('#form-cargo-depto').val();
                const original = $btn.html();
                $btn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i>Actualizando...');

                $.ajax({
                    url: "{{ url('cargos') }}/" + id,
                    method: 'PUT',
                    data: { nombre: nombre, departamento_id: dep, _token: '{{ csrf_token() }}' },
                    success: function (resp) {
                        $('#formModal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: '¡Actualizado!', text: resp.message, showConfirmButton: false, timer: 1500 });
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.errors?.nombre?.[0]
                            || xhr.responseJSON?.errors?.departamento_id?.[0]
                            || xhr.responseJSON?.message
                            || 'Error al guardar.';
                        $('#form-cargo-nombre').removeClass('is-valid').addClass('is-invalid');
                        $('#form-cargo-error').text(msg).show();
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
                    title: '¿Inhabilitar cargo?',
                    text: 'Solo se puede inhabilitar si no tiene empleados asociados.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, inhabilitar',
                    cancelButtonText: 'Cancelar',
                    customClass: { confirmButton: 'btn btn-danger w-xs me-2', cancelButton: 'btn btn-light w-xs' },
                    buttonsStyling: false
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    $.ajax({
                        url: "{{ url('cargos') }}/" + id,
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
                    title: '¿Restaurar cargo?',
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
                        url: "{{ url('cargos') }}/" + id + "/restore",
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
