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
                <h4 class="mb-sm-0">Gestión de Colores</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Gestión General</a></li>
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Productos</a></li>
                        <li class="breadcrumb-item active">Colores</li>
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
                        <h5 class="card-title mb-0 flex-grow-1">Catálogo de Colores</h5>
                        <div class="flex-shrink-0 d-flex align-items-center gap-3">
                            @if($historial)
                                <a href="{{ route('colores.index') }}" class="btn-historial btn-historial-volver">
                                    <i class="ri-arrow-left-line"></i> Solo Activos
                                </a>
                            @else
                                <a href="{{ route('colores.index', ['historial' => true]) }}" class="btn-historial btn-historial-ver">
                                    <i class="ri-archive-line"></i> Inhabilitados
                                </a>
                                <button type="button" class="btn btn-success add-btn" id="create-btn">
                                    <i class="ri-add-line align-bottom me-1"></i> Agregar Color
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
                                    placeholder="Buscar color por nombre, grupo o HEX..."
                                    autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <table id="colores-table" class="table table-bordered table-striped table-sm align-middle table-maestro">
                        <thead>
                            <tr>
                                <th>Color</th>
                                <th>Grupo</th>
                                <th class="text-center">HEX</th>
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
    <div class="modal fade atlantico-modal" id="formModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-guard-id-field="form-color-id">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="formModalTitle">Nuevo Color</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="colorForm" autocomplete="off" novalidate>
                    @csrf
                    <input type="hidden" id="form-color-id" />
                    <div class="modal-body p-4">

                        {{-- Preview en vivo de la pastilla tal como se verá en el catálogo --}}
                        <div class="d-flex align-items-center gap-2 p-3 mb-4 rounded-3 bg-light">
                            <span id="form-color-preview-dot"
                                style="display:inline-block;width:26px;height:26px;border-radius:50%;background:#1B3A5C;border:1px solid rgba(0,0,0,.15);"></span>
                            <span class="fw-semibold" id="form-color-preview-name">Nombre del color</span>
                        </div>

                        <div class="mb-3">
                            <label for="form-color-nombre" class="form-label">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="form-color-nombre" class="form-control"
                                placeholder="Ej: Azul Marino, Verde Botella" maxlength="100" required />
                            <div id="form-color-nombre-error" class="invalid-feedback"></div>
                            <small class="text-muted d-block mt-1">Nombre comercial del color. Mínimo 2 caracteres.</small>
                        </div>

                        <div class="mb-3">
                            <label for="form-color-grupo" class="form-label">Grupo</label>
                            <select id="form-color-grupo" class="form-select">
                                <option value="">Sin grupo</option>
                                @foreach($grupos as $g)
                                    <option value="{{ $g }}">{{ $g }}</option>
                                @endforeach
                                <option value="__new__">➕ Nuevo grupo…</option>
                            </select>
                            <input type="text" id="form-color-grupo-nuevo" class="form-control mt-2 d-none"
                                placeholder="Nombre del nuevo grupo" maxlength="100" />
                            <small class="text-muted d-block mt-1">Elegí un grupo existente para mantener el catálogo ordenado, o creá uno nuevo solo si hace falta.</small>
                        </div>

                        <div class="mb-0">
                            <label for="form-color-hex" class="form-label">
                                Color HEX referencial <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" id="form-color-hex" value="#1B3A5C"
                                    class="form-control form-control-color" style="max-width:56px;" title="Elegí una tonalidad" />
                                <input type="text" id="form-color-hex-text" class="form-control" maxlength="7"
                                    placeholder="#1B3A5C" style="max-width:140px;text-transform:uppercase;font-family:monospace;" />
                            </div>
                            <div id="form-color-hex-error" class="invalid-feedback d-block" style="display:none;"></div>
                            <small class="text-muted d-block mt-1">
                                Referencia visual del color (no es exacto al hilo/tela). Elegí con la paleta o escribí el HEX.
                            </small>
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
            const HEX_RE = /^#[0-9A-Fa-f]{6}$/;

            function esc(v) { return $('<div>').text(v == null ? '' : v).html(); }

            // ──────────────────────────────────────────────
            // DataTable
            // ──────────────────────────────────────────────
            const table = $('#colores-table').DataTable({
                ajax: {
                    url: "{{ route('colores.index') }}" + (HISTORIAL ? '?historial=true' : ''),
                    dataSrc: ''
                },
                columns: [
                    {
                        data: 'nombre',
                        render: function (data, type, row) {
                            if (type !== 'display') return data;
                            var hex = HEX_RE.test(row.hex_referencial || '') ? row.hex_referencial : '#CCCCCC';
                            return '<span style="display:inline-block;width:18px;height:18px;border-radius:50%;background:' + hex +
                                ';border:1px solid rgba(0,0,0,.15);vertical-align:middle;margin-right:.5rem;"></span>' +
                                '<span class="align-middle">' + esc(data) + '</span>';
                        }
                    },
                    {
                        data: 'grupo',
                        render: function (d, type) {
                            if (type !== 'display') return d || '';
                            return d ? '<span class="badge bg-info-subtle text-info">' + esc(d) + '</span>'
                                     : '<span class="text-muted">—</span>';
                        }
                    },
                    {
                        data: 'hex_referencial',
                        className: 'text-center',
                        render: function (d, type) {
                            var hex = (d || '').toUpperCase();
                            return type !== 'display' ? hex : '<code>' + esc(hex) + '</code>';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        className: 'text-center',
                        render: function (data, type, row) { return generateButtons(row); }
                    }
                ],
                order: [[1, 'asc'], [0, 'asc']],
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
                    items =
                        `<li><button type="button" class="dropdown-item act-item act-edit edit-btn" data-id="${id}"><span class="act-ic"><i class="ri-pencil-fill"></i></span>Editar</button></li>` +
                        `<li><button type="button" class="dropdown-item act-item act-del delete-btn" data-id="${id}"><span class="act-ic"><i class="ri-forbid-line"></i></span>Inhabilitar</button></li>`;
                }
                return `<div class="d-flex gap-1 justify-content-center align-items-center">` +
                    `<div class="dropdown d-inline-block">` +
                        `<button class="btn btn-sm btn-soft-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Acciones"><i class="ri-more-2-fill"></i></button>` +
                        `<ul class="dropdown-menu dropdown-menu-end actions-menu">${items}</ul>` +
                    `</div>` +
                `</div>`;
            }

            // Búsqueda con debounce
            let searchTimeout = null;
            $('#custom-search-input').on('keyup', function () {
                clearTimeout(searchTimeout);
                const val = this.value;
                searchTimeout = setTimeout(function () { table.search(val).draw(); }, 300);
            });
            $(window).on('resize', function () { table.columns.adjust(); });
            setTimeout(function () { table.columns.adjust(); }, 100);

            // ──────────────────────────────────────────────
            // Sincronización picker ↔ texto ↔ preview
            // ──────────────────────────────────────────────
            function setHex(hex) {
                hex = (hex || '').toUpperCase();
                $('#form-color-hex-text').val(hex);
                if (HEX_RE.test(hex)) {
                    $('#form-color-hex').val(hex);
                    $('#form-color-preview-dot').css('background', hex);
                }
            }
            function updatePreviewName() {
                var n = $('#form-color-nombre').val().trim();
                $('#form-color-preview-name').text(n || 'Nombre del color');
            }
            $('#form-color-hex').on('input', function () { setHex(this.value); });
            $('#form-color-hex-text').on('input', function () { setHex(this.value); });
            $('#form-color-nombre').on('input', updatePreviewName);

            // ── Grupo: select de existentes + "Nuevo grupo…" revela el input ──
            $('#form-color-grupo').on('change', function () {
                const nuevo = this.value === '__new__';
                $('#form-color-grupo-nuevo').toggleClass('d-none', !nuevo);
                if (nuevo) $('#form-color-grupo-nuevo').val('').focus();
            });

            // Devuelve el grupo elegido (existente, nuevo, o '' si "Sin grupo")
            function getGrupo() {
                const sel = $('#form-color-grupo').val();
                if (sel === '__new__') return $('#form-color-grupo-nuevo').val().trim();
                return sel || '';
            }

            // Coloca un grupo en el control: si existe como opción lo selecciona,
            // si no, lo agrega como opción nueva y la selecciona (caso edición).
            function setGrupo(g) {
                g = (g || '').trim();
                $('#form-color-grupo-nuevo').addClass('d-none').val('');
                if (g === '') { $('#form-color-grupo').val(''); return; }
                const exists = $('#form-color-grupo option').filter(function () { return this.value === g; }).length > 0;
                if (!exists) {
                    $('<option>').val(g).text(g).insertBefore('#form-color-grupo option[value="__new__"]');
                }
                $('#form-color-grupo').val(g);
            }

            // ──────────────────────────────────────────────
            // Validaciones
            // ──────────────────────────────────────────────
            function validarNombre() {
                const $i = $('#form-color-nombre'), $e = $('#form-color-nombre-error');
                const v = $i.val().trim();
                if (v.length < 2) {
                    $i.removeClass('is-valid').addClass('is-invalid');
                    $e.text('El nombre debe tener al menos 2 caracteres.').show();
                    return false;
                }
                $i.removeClass('is-invalid').addClass('is-valid'); $e.hide();
                return true;
            }
            function validarHex() {
                const v = $('#form-color-hex-text').val().trim().toUpperCase();
                const $e = $('#form-color-hex-error');
                if (!HEX_RE.test(v)) {
                    $('#form-color-hex-text').removeClass('is-valid').addClass('is-invalid');
                    $e.text('El HEX debe tener el formato #RRGGBB.').show();
                    return false;
                }
                $('#form-color-hex-text').removeClass('is-invalid').addClass('is-valid'); $e.hide();
                return true;
            }
            $('#form-color-nombre').on('blur', validarNombre);
            $('#form-color-hex-text').on('blur', validarHex);

            function resetForm() {
                $('#colorForm')[0].reset();
                $('#form-color-id').val('');
                $('#form-color-nombre, #form-color-hex-text').removeClass('is-invalid is-valid');
                $('#form-color-nombre-error, #form-color-hex-error').hide();
                setHex('#1B3A5C');
                setGrupo('');
                updatePreviewName();
            }

            function payload() {
                return {
                    nombre: $('#form-color-nombre').val().trim(),
                    grupo: getGrupo(),
                    hex_referencial: $('#form-color-hex-text').val().trim().toUpperCase(),
                    _token: '{{ csrf_token() }}'
                };
            }

            function showServerError(xhr) {
                const errs = xhr.responseJSON?.errors || {};
                if (errs.nombre) { $('#form-color-nombre').removeClass('is-valid').addClass('is-invalid'); $('#form-color-nombre-error').text(errs.nombre[0]).show(); }
                if (errs.hex_referencial) { $('#form-color-hex-text').removeClass('is-valid').addClass('is-invalid'); $('#form-color-hex-error').text(errs.hex_referencial[0]).show(); }
                if (!errs.nombre && !errs.hex_referencial) {
                    Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Error al guardar.' });
                }
            }

            // ──────────────────────────────────────────────
            // Crear
            // ──────────────────────────────────────────────
            $('#create-btn').on('click', function () {
                resetForm();
                $('#formModalTitle').text('Nuevo Color');
                $('#add-btn').show(); $('#edit-btn').hide();
                $('#formModal').modal('show');
            });
            $('#formModal').on('shown.bs.modal', function () { $('#form-color-nombre').focus(); });

            $('#add-btn').on('click', function () {
                if (!validarNombre() || !validarHex()) return;
                const $btn = $(this), original = $btn.html();
                $btn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i>Agregando...');
                $.ajax({
                    url: "{{ route('colores.store') }}", method: 'POST', data: payload(),
                    success: function (resp) {
                        $('#formModal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: '¡Listo!', text: resp.message, showConfirmButton: false, timer: 1500 });
                    },
                    error: showServerError,
                    complete: function () { $btn.prop('disabled', false).html(original); }
                });
            });

            // ──────────────────────────────────────────────
            // Editar
            // ──────────────────────────────────────────────
            $(document).on('click', '.edit-btn', function () {
                const id = $(this).data('id');
                resetForm();
                $.ajax({
                    url: "{{ url('colores') }}/" + id, method: 'GET',
                    success: function (c) {
                        $('#form-color-id').val(c.id);
                        $('#form-color-nombre').val(c.nombre);
                        setGrupo(c.grupo || '');
                        setHex(c.hex_referencial);
                        updatePreviewName();
                        $('#formModalTitle').text('Editar Color');
                        $('#add-btn').hide(); $('#edit-btn').show();
                        $('#formModal').modal('show');
                    },
                    error: function () { Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el color.' }); }
                });
            });

            $('#edit-btn').on('click', function () {
                if (!validarNombre() || !validarHex()) return;
                const $btn = $(this), id = $('#form-color-id').val(), original = $btn.html();
                $btn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i>Actualizando...');
                $.ajax({
                    url: "{{ url('colores') }}/" + id, method: 'PUT', data: payload(),
                    success: function (resp) {
                        $('#formModal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: '¡Actualizado!', text: resp.message, showConfirmButton: false, timer: 1500 });
                    },
                    error: showServerError,
                    complete: function () { $btn.prop('disabled', false).html(original); }
                });
            });

            // ──────────────────────────────────────────────
            // Inhabilitar / Restaurar
            // ──────────────────────────────────────────────
            $(document).on('click', '.delete-btn', function () {
                const id = $(this).data('id');
                Swal.fire({
                    title: '¿Inhabilitar color?',
                    text: 'Dejará de aparecer en el selector de colores de cotizaciones y pedidos.',
                    icon: 'warning', showCancelButton: true,
                    confirmButtonText: 'Sí, inhabilitar', cancelButtonText: 'Cancelar',
                    customClass: { confirmButton: 'btn btn-danger w-xs me-2', cancelButton: 'btn btn-light w-xs' },
                    buttonsStyling: false
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    $.ajax({
                        url: "{{ url('colores') }}/" + id, method: 'DELETE', data: { _token: '{{ csrf_token() }}' },
                        success: function (resp) {
                            table.ajax.reload(null, false);
                            Swal.fire({ icon: 'success', title: '¡Listo!', text: resp.message, showConfirmButton: false, timer: 1500 });
                        },
                        error: function (xhr) { Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Error al procesar' }); }
                    });
                });
            });

            $(document).on('click', '.restore-btn', function () {
                const id = $(this).data('id');
                Swal.fire({
                    title: '¿Restaurar color?',
                    text: 'Volverá a estar disponible en el selector de colores.',
                    icon: 'question', showCancelButton: true,
                    confirmButtonText: 'Sí, restaurar', cancelButtonText: 'Cancelar',
                    customClass: { confirmButton: 'btn btn-success w-xs me-2', cancelButton: 'btn btn-light w-xs' },
                    buttonsStyling: false
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    $.ajax({
                        url: "{{ url('colores') }}/" + id + "/restore", method: 'PATCH', data: { _token: '{{ csrf_token() }}' },
                        success: function (resp) {
                            table.ajax.reload(null, false);
                            Swal.fire({ icon: 'success', title: '¡Restaurado!', text: resp.message, showConfirmButton: false, timer: 1500 });
                        },
                        error: function (xhr) { Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Error al restaurar' }); }
                    });
                });
            });
        });
    </script>
@endpush
