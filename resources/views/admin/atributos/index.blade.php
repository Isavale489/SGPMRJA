@extends('admin.layouts.app')

@push('styles')
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .atributo-row { cursor: pointer; }
        .atributo-row.is-selected td { background-color: rgba(30, 60, 114, .08) !important; }
        [data-bs-theme="dark"] .atributo-row.is-selected td { background-color: rgba(126, 165, 232, .14) !important; }
        .codigo-pill {
            display: inline-block;
            padding: .12rem .55rem;
            font-family: 'JetBrains Mono', 'Consolas', monospace;
            font-size: .78rem;
            font-weight: 600;
            background: rgba(30, 60, 114, .10);
            color: #1e3c72;
            border-radius: 4px;
            letter-spacing: .03em;
        }
        [data-bs-theme="dark"] .codigo-pill { background: rgba(126, 165, 232, .18); color: #cfe0f8; }
        .valor-empty {
            display: flex; align-items: center; justify-content: center;
            min-height: 240px; color: #6c757d; font-style: italic;
        }
        .badge-uso-prod {
            font-size: .72rem;
            padding: .25rem .5rem;
        }
        .badge-uso-prod.zero { background: rgba(108, 117, 125, .15); color: #6c757d; }
        .badge-uso-prod.has  { background: rgba(16, 185, 129, .15); color: #065f46; }
        [data-bs-theme="dark"] .badge-uso-prod.has { background: rgba(16, 185, 129, .22); color: #6ee7b7; }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Atributos de Confección</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Gestión General</a></li>
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Productos</a></li>
                        <li class="breadcrumb-item active">Atributos</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Panel izquierdo: ATRIBUTOS --}}
        <div class="col-lg-5">
            <div class="card card-maestros h-100">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Atributos</h5>
                        <button type="button" class="btn btn-success add-btn" id="btn-add-atributo">
                            <i class="ri-add-line align-bottom me-1"></i> Agregar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="atributos-table" class="table table-bordered table-striped table-sm align-middle table-maestro">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Código</th>
                                <th class="text-center">Valores</th>
                                <th class="text-center">Tipos</th>
                                <th class="text-center" style="width: 90px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Panel derecho: VALORES DEL ATRIBUTO SELECCIONADO --}}
        <div class="col-lg-7">
            <div class="card card-maestros h-100">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">
                            Valores de <span id="selected-atr-name" class="text-muted">—</span>
                        </h5>
                        <button type="button" class="btn btn-success add-btn" id="btn-add-valor" disabled>
                            <i class="ri-add-line align-bottom me-1"></i> Agregar valor
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="valores-empty" class="valor-empty">
                        <div class="text-center">
                            <i class="ri-arrow-left-line fs-1 d-block mb-2"></i>
                            Selecciona un atributo a la izquierda para ver sus valores.
                        </div>
                    </div>
                    <table id="valores-table" class="table table-bordered table-striped table-sm align-middle table-maestro" style="display:none;">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 60px;">#</th>
                                <th>Nombre</th>
                                <th>Código</th>
                                <th class="text-center">Productos</th>
                                <th class="text-center" style="width: 90px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Crear / Editar Atributo --}}
    <div class="modal fade atlantico-modal" id="atributoModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="atributoModalTitle">Nuevo Atributo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="atributoForm" autocomplete="off" novalidate>
                    @csrf
                    <input type="hidden" id="atr-id" />
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="atr-nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" id="atr-nombre" class="form-control" maxlength="80" placeholder="Ej: Manga, Cuello, Corte" required />
                            <div id="atr-nombre-error" class="invalid-feedback"></div>
                            <small class="text-muted">Mínimo 3 caracteres, máximo 80.</small>
                        </div>
                        <div class="mb-3">
                            <label for="atr-codigo" class="form-label">Código <span class="text-danger">*</span></label>
                            <input type="text" id="atr-codigo" class="form-control text-uppercase" maxlength="8" placeholder="Ej: MNG, CLL" required style="font-family: 'JetBrains Mono', 'Consolas', monospace;" />
                            <div id="atr-codigo-error" class="invalid-feedback"></div>
                            <small class="text-muted">2-8 caracteres, solo letras mayúsculas y números. <strong>Inmutable</strong> después de crear.</small>
                        </div>
                        <div class="mb-3">
                            <label for="atr-descripcion" class="form-label">Descripción</label>
                            <textarea id="atr-descripcion" class="form-control" rows="2" maxlength="191" placeholder="Opcional"></textarea>
                        </div>
                        <div class="mb-0">
                            <label for="atr-tipos-producto" class="form-label">Tipos de Producto</label>
                            <select id="atr-tipos-producto" class="form-select" multiple style="min-height: 90px;">
                                @foreach($tiposProducto as $tp)
                                    <option value="{{ $tp->id }}">{{ $tp->nombre }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Selecciona uno o varios. Mantén <kbd>Ctrl</kbd> para selección múltiple.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i> Cerrar
                        </button>
                        <button type="button" class="btn btn-success" id="btn-save-atributo">
                            <i class="ri-save-line me-1"></i> <span class="btn-label">Guardar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Crear / Editar Valor --}}
    <div class="modal fade atlantico-modal" id="valorModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="valorModalTitle">Nuevo Valor</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="valorForm" autocomplete="off" novalidate>
                    @csrf
                    <input type="hidden" id="val-id" />
                    <div class="modal-body p-4">
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="ri-information-line me-1"></i>
                            Para el atributo <strong id="val-atributo-label">—</strong>
                        </div>
                        <div class="mb-3">
                            <label for="val-nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" id="val-nombre" class="form-control" maxlength="80" placeholder="Ej: Larga, V, Mao" required />
                            <div id="val-nombre-error" class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="val-codigo" class="form-label">Código <span class="text-danger">*</span></label>
                            <input type="text" id="val-codigo" class="form-control text-uppercase" maxlength="8" placeholder="Ej: L, V, MAO" required style="font-family: 'JetBrains Mono', 'Consolas', monospace;" />
                            <div id="val-codigo-error" class="invalid-feedback"></div>
                            <small class="text-muted">1-8 caracteres, solo letras mayúsculas y números. <strong>Inmutable</strong>.</small>
                        </div>
                        <div class="mb-0">
                            <label for="val-orden" class="form-label">Orden</label>
                            <input type="number" id="val-orden" class="form-control" min="0" max="9999" placeholder="Auto" />
                            <small class="text-muted">Posición en la lista. Vacío asigna al final.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i> Cerrar
                        </button>
                        <button type="button" class="btn btn-success" id="btn-save-valor">
                            <i class="ri-save-line me-1"></i> <span class="btn-label">Guardar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ URL::asset('/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
    (function() {
        'use strict';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const $atributosBody = $('#atributos-table tbody');
        const $valoresTable  = $('#valores-table');
        const $valoresBody   = $('#valores-table tbody');
        const $valoresEmpty  = $('#valores-empty');
        const $btnAddValor   = $('#btn-add-valor');
        const $selectedName  = $('#selected-atr-name');

        let atributosCache = [];
        let selectedAtributo = null;

        // ----------- ATRIBUTOS -----------
        function loadAtributos() {
            return $.getJSON('{{ route('atributos.index') }}').done(rows => {
                atributosCache = rows;
                renderAtributos(rows);
                if (selectedAtributo) {
                    const refreshed = rows.find(r => r.id === selectedAtributo.id);
                    if (refreshed) selectAtributo(refreshed);
                    else clearSelection();
                }
            });
        }

        function renderAtributos(rows) {
            if (!rows.length) {
                $atributosBody.html('<tr><td colspan="5" class="text-center text-muted py-4">No hay atributos registrados.</td></tr>');
                return;
            }
            $atributosBody.html(rows.map(r => `
                <tr class="atributo-row ${selectedAtributo && selectedAtributo.id === r.id ? 'is-selected' : ''}" data-id="${r.id}">
                    <td><strong>${escapeHtml(r.nombre)}</strong></td>
                    <td><span class="codigo-pill">${escapeHtml(r.codigo)}</span></td>
                    <td class="text-center">${r.valores_count}</td>
                    <td class="text-center">${r.tipos_producto_count}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-soft-primary me-1 btn-edit-atr" title="Editar">
                            <i class="ri-pencil-line"></i>
                        </button>
                        <button class="btn btn-sm btn-soft-danger btn-del-atr" title="Eliminar">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </td>
                </tr>
            `).join(''));
        }

        function selectAtributo(atr) {
            selectedAtributo = atr;
            $('.atributo-row').removeClass('is-selected');
            $(`.atributo-row[data-id="${atr.id}"]`).addClass('is-selected');
            $selectedName.text(atr.nombre).removeClass('text-muted');
            $btnAddValor.prop('disabled', false);
            loadValores(atr.id);
        }

        function clearSelection() {
            selectedAtributo = null;
            $('.atributo-row').removeClass('is-selected');
            $selectedName.text('—').addClass('text-muted');
            $btnAddValor.prop('disabled', true);
            $valoresTable.hide();
            $valoresEmpty.show();
        }

        // Eventos atributos
        $atributosBody.on('click', '.atributo-row', function(e) {
            if ($(e.target).closest('button').length) return;
            const id = $(this).data('id');
            const atr = atributosCache.find(a => a.id === id);
            if (atr) selectAtributo(atr);
        });

        $atributosBody.on('click', '.btn-edit-atr', function() {
            const id = $(this).closest('tr').data('id');
            openAtributoModal(atributosCache.find(a => a.id === id));
        });

        $atributosBody.on('click', '.btn-del-atr', function() {
            const id = $(this).closest('tr').data('id');
            const atr = atributosCache.find(a => a.id === id);
            confirmDeleteAtributo(atr);
        });

        $('#btn-add-atributo').on('click', () => openAtributoModal(null));

        function openAtributoModal(atr) {
            const isEdit = !!atr;
            $('#atributoModalTitle').text(isEdit ? 'Editar Atributo' : 'Nuevo Atributo');
            $('#atr-id').val(atr?.id || '');
            $('#atr-nombre').val(atr?.nombre || '').removeClass('is-invalid');
            $('#atr-codigo').val(atr?.codigo || '').removeClass('is-invalid').prop('readonly', isEdit);
            $('#atr-descripcion').val(atr?.descripcion || '');
            // Pre-seleccionar tipos de producto asociados
            const ids = (atr?.tipos_producto_ids || []).map(String);
            $('#atr-tipos-producto option').each(function() {
                $(this).prop('selected', ids.includes($(this).val()));
            });
            $('#btn-save-atributo .btn-label').text(isEdit ? 'Actualizar' : 'Crear');
            new bootstrap.Modal('#atributoModal').show();
        }

        $('#btn-save-atributo').on('click', saveAtributo);

        function saveAtributo() {
            const id = $('#atr-id').val();
            const nombre = $('#atr-nombre').val().trim();
            const codigo = $('#atr-codigo').val().trim().toUpperCase();
            const descripcion = $('#atr-descripcion').val().trim();
            const tiposProducto = $('#atr-tipos-producto').val() || [];

            $('#atr-nombre, #atr-codigo').removeClass('is-invalid');

            if (nombre.length < 3) {
                $('#atr-nombre').addClass('is-invalid');
                $('#atr-nombre-error').text('Mínimo 3 caracteres.');
                return;
            }
            if (!id && !/^[A-Z0-9]{2,8}$/.test(codigo)) {
                $('#atr-codigo').addClass('is-invalid');
                $('#atr-codigo-error').text('2-8 caracteres, solo letras mayúsculas y números.');
                return;
            }

            const url = id ? `{{ url('atributos') }}/${id}` : '{{ route('atributos.store') }}';
            const method = id ? 'PUT' : 'POST';
            const payload = id ? { nombre, descripcion } : { nombre, codigo, descripcion };

            $.ajax({
                url, method,
                data: { ...payload, _token: csrfToken, 'tipos_producto': tiposProducto },
                traditional: false,
                success: (resp) => {
                    bootstrap.Modal.getInstance('#atributoModal')?.hide();
                    toast(resp.message);
                    loadAtributos();
                },
                error: handleAjaxError
            });
        }

        function confirmDeleteAtributo(atr) {
            Swal.fire({
                title: '¿Eliminar atributo?',
                html: `Vas a eliminar <strong>${escapeHtml(atr.nombre)}</strong>. Esto no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#d33',
            }).then(r => {
                if (!r.isConfirmed) return;
                $.ajax({
                    url: `{{ url('atributos') }}/${atr.id}`,
                    method: 'DELETE',
                    data: { _token: csrfToken },
                    success: (resp) => {
                        toast(resp.message);
                        if (selectedAtributo?.id === atr.id) clearSelection();
                        loadAtributos();
                    },
                    error: handleAjaxError
                });
            });
        }

        // ----------- VALORES -----------
        function loadValores(atributoId) {
            $valoresEmpty.hide();
            $valoresTable.show();
            $valoresBody.html('<tr><td colspan="5" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span></td></tr>');
            $.getJSON(`{{ url('atributos') }}/${atributoId}/valores`).done(rows => {
                if (!rows.length) {
                    $valoresBody.html('<tr><td colspan="5" class="text-center text-muted py-4">Sin valores. Agrega el primero.</td></tr>');
                    return;
                }
                $valoresBody.html(rows.map(v => `
                    <tr data-id="${v.id}">
                        <td class="text-center text-muted">${v.orden ?? '—'}</td>
                        <td><strong>${escapeHtml(v.nombre)}</strong></td>
                        <td><span class="codigo-pill">${escapeHtml(v.codigo)}</span></td>
                        <td class="text-center">
                            <span class="badge badge-uso-prod ${v.productos_count > 0 ? 'has' : 'zero'}">
                                ${v.productos_count}
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-soft-primary me-1 btn-edit-val" title="Editar">
                                <i class="ri-pencil-line"></i>
                            </button>
                            <button class="btn btn-sm btn-soft-danger btn-del-val" title="Eliminar">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </td>
                    </tr>
                `).join(''));
            });
        }

        $btnAddValor.on('click', () => openValorModal(null));

        $valoresBody.on('click', '.btn-edit-val', function() {
            const tr = $(this).closest('tr');
            const id = tr.data('id');
            const valor = {
                id,
                nombre: tr.find('td:eq(1) strong').text(),
                codigo: tr.find('td:eq(2) .codigo-pill').text(),
                orden:  tr.find('td:eq(0)').text().trim()
            };
            openValorModal(valor);
        });

        $valoresBody.on('click', '.btn-del-val', function() {
            const tr = $(this).closest('tr');
            const id = tr.data('id');
            const nombre = tr.find('td:eq(1) strong').text();
            confirmDeleteValor(id, nombre);
        });

        function openValorModal(val) {
            if (!selectedAtributo) return;
            const isEdit = !!val;
            $('#valorModalTitle').text(isEdit ? 'Editar Valor' : 'Nuevo Valor');
            $('#val-atributo-label').text(selectedAtributo.nombre);
            $('#val-id').val(val?.id || '');
            $('#val-nombre').val(val?.nombre || '').removeClass('is-invalid');
            $('#val-codigo').val(val?.codigo || '').removeClass('is-invalid').prop('readonly', isEdit);
            $('#val-orden').val(val?.orden && val.orden !== '—' ? val.orden : '');
            $('#btn-save-valor .btn-label').text(isEdit ? 'Actualizar' : 'Crear');
            new bootstrap.Modal('#valorModal').show();
        }

        $('#btn-save-valor').on('click', saveValor);

        function saveValor() {
            if (!selectedAtributo) return;
            const id = $('#val-id').val();
            const nombre = $('#val-nombre').val().trim();
            const codigo = $('#val-codigo').val().trim().toUpperCase();
            const orden = $('#val-orden').val();

            $('#val-nombre, #val-codigo').removeClass('is-invalid');
            if (!nombre) {
                $('#val-nombre').addClass('is-invalid');
                $('#val-nombre-error').text('El nombre es obligatorio.');
                return;
            }
            if (!id && !/^[A-Z0-9]{1,8}$/.test(codigo)) {
                $('#val-codigo').addClass('is-invalid');
                $('#val-codigo-error').text('1-8 caracteres, solo letras mayúsculas y números.');
                return;
            }

            const base = `{{ url('atributos') }}/${selectedAtributo.id}/valores`;
            const url = id ? `${base}/${id}` : base;
            const method = id ? 'PUT' : 'POST';
            const payload = id ? { nombre, orden } : { nombre, codigo, orden };

            $.ajax({
                url, method,
                data: { ...payload, _token: csrfToken },
                success: (resp) => {
                    bootstrap.Modal.getInstance('#valorModal')?.hide();
                    toast(resp.message);
                    loadValores(selectedAtributo.id);
                    loadAtributos();
                },
                error: handleAjaxError
            });
        }

        function confirmDeleteValor(id, nombre) {
            Swal.fire({
                title: '¿Eliminar valor?',
                html: `Vas a eliminar <strong>${escapeHtml(nombre)}</strong>.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#d33',
            }).then(r => {
                if (!r.isConfirmed) return;
                $.ajax({
                    url: `{{ url('atributos') }}/${selectedAtributo.id}/valores/${id}`,
                    method: 'DELETE',
                    data: { _token: csrfToken },
                    success: (resp) => {
                        toast(resp.message);
                        loadValores(selectedAtributo.id);
                        loadAtributos();
                    },
                    error: handleAjaxError
                });
            });
        }

        // ----------- HELPERS -----------
        function escapeHtml(s) {
            return String(s ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[c]));
        }

        function toast(msg) {
            Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: msg, showConfirmButton: false, timer: 2200, timerProgressBar: true
            });
        }

        function handleAjaxError(xhr) {
            const resp = xhr.responseJSON || {};
            if (resp.errors) {
                Object.entries(resp.errors).forEach(([field, msgs]) => {
                    const $input = $(`#atr-${field}, #val-${field}`);
                    $input.addClass('is-invalid');
                    $input.next('.invalid-feedback').text(Array.isArray(msgs) ? msgs[0] : msgs);
                });
            } else {
                Swal.fire('Error', resp.message || 'Error inesperado.', 'error');
            }
        }

        // Init
        loadAtributos();
    })();
    </script>
@endpush
