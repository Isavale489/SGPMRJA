<script>
    (function () {
        'use strict';

        // ============================================================
        // Panel de Configuración de seguridad (FEAT-005 / TASK-039).
        // Tab Roles (CRUD AJAX) + Tab Permisos (matriz módulo × acción).
        // ============================================================

        const CSRF = '{{ csrf_token() }}';
        const URLS = {
            rolesStore:   '{{ route('seguridad.roles.store') }}',
            rolBase:      '{{ url('configuracion/seguridad/roles') }}',     // + /{id}
            permisosBase: '{{ url('configuracion/seguridad/permisos') }}',  // + /{id}
        };

        const rolModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('rolModal'));

        // ---------- utilidades ----------

        function esc(s) {
            return $('<div>').text(s == null ? '' : s).html();
        }

        function marcarErr(input, errBox, msg) {
            $(input).addClass('is-invalid');
            $(errBox).text(msg);
        }

        function limpiarErr(input, errBox) {
            $(input).removeClass('is-invalid');
            $(errBox).text('');
        }

        // =====================================================================
        // TAB ROLES — CRUD
        // =====================================================================

        function resetRolForm() {
            document.getElementById('seg-rol-form').reset();
            $('#id-field').val('');
            limpiarErr('#seg-rol-nombre', '#seg-rol-nombre-error');
            limpiarErr('#seg-rol-descripcion', '#seg-rol-descripcion-error');
        }

        function validarRol() {
            const nombre = $('#seg-rol-nombre').val().trim();
            limpiarErr('#seg-rol-nombre', '#seg-rol-nombre-error');

            if (nombre === '') {
                marcarErr('#seg-rol-nombre', '#seg-rol-nombre-error', 'El nombre del rol es obligatorio.');
                return false;
            }
            if (nombre.length > 60) {
                marcarErr('#seg-rol-nombre', '#seg-rol-nombre-error', 'Máximo 60 caracteres.');
                return false;
            }
            return true;
        }

        function rolRowHtml(rol) {
            const usuarios = rol.usuarios_count || 0;
            const motivo = usuarios > 0 ? 'usuarios' : '';   // los roles creados nunca son de sistema
            const delTitle = motivo === 'usuarios' ? 'Tiene usuarios asignados' : 'Eliminar';
            return '' +
                '<tr data-rol-id="' + rol.id + '" data-rol-nombre="' + esc(rol.nombre) + '"' +
                ' data-rol-descripcion="' + esc(rol.descripcion || '') + '" data-es-sistema="0"' +
                ' data-usuarios="' + usuarios + '">' +
                '<td><span class="fw-medium seg-rol-nombre">' + esc(rol.nombre) + '</span></td>' +
                '<td class="text-muted seg-rol-desc">' + (rol.descripcion ? esc(rol.descripcion) : '—') + '</td>' +
                '<td class="text-center"><span class="badge bg-light text-body seg-rol-usuarios">' +
                    usuarios + '</span></td>' +
                '<td class="text-center"><div class="d-inline-flex gap-1">' +
                    '<button type="button" class="btn btn-sm btn-soft-primary seg-edit-rol" title="Editar"><i class="ri-pencil-fill"></i></button>' +
                    '<button type="button" class="btn btn-sm btn-soft-danger seg-del-rol' + (motivo ? ' is-blocked' : '') + '"' +
                        ' data-motivo="' + motivo + '" title="' + delTitle + '"><i class="ri-delete-bin-fill"></i></button>' +
                '</div></td>' +
                '</tr>';
        }

        function upsertRolRow(rol) {
            const $row = $('#seg-roles-tbody tr[data-rol-id="' + rol.id + '"]');
            if ($row.length) {
                $row.attr('data-rol-nombre', rol.nombre).attr('data-rol-descripcion', rol.descripcion || '');
                $row.find('.seg-rol-nombre').text(rol.nombre);
                $row.find('.seg-rol-desc').text(rol.descripcion || '—');
            } else {
                $('#seg-roles-tbody').append(rolRowHtml(rol));
            }
        }

        function upsertRolOption(rol) {
            const $opt = $('#seg-rol-select option[value="' + rol.id + '"]');
            if ($opt.length) {
                $opt.text(rol.nombre);
            } else {
                $('#seg-rol-select').append($('<option>').val(rol.id).text(rol.nombre));
            }
        }

        $('#seg-nuevo-rol-btn').on('click', function () {
            resetRolForm();
            $('#rolModalTitle').text('Nuevo rol');
            rolModal.show();
        });

        $(document).on('click', '.seg-edit-rol', function () {
            const $row = $(this).closest('tr');
            resetRolForm();
            $('#rolModalTitle').text('Editar rol');
            $('#id-field').val($row.data('rol-id'));
            $('#seg-rol-nombre').val($row.data('rol-nombre'));
            $('#seg-rol-descripcion').val($row.data('rol-descripcion'));
            rolModal.show();
        });

        $(document).on('blur', '#seg-rol-nombre', function () {
            if ($(this).val().trim() !== '') limpiarErr('#seg-rol-nombre', '#seg-rol-nombre-error');
        });

        $('#seg-rol-form').on('submit', function (e) {
            e.preventDefault();
            if (!validarRol()) return;

            const id = $('#id-field').val();
            const data = {
                _token: CSRF,
                nombre: $('#seg-rol-nombre').val().trim(),
                descripcion: $('#seg-rol-descripcion').val().trim(),
            };

            let url = URLS.rolesStore;
            if (id) {
                url = URLS.rolBase + '/' + id;
                data._method = 'PUT';
            }

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                success: function (res) {
                    rolModal.hide();
                    upsertRolRow(res.rol);
                    upsertRolOption(res.rol);
                    Swal.fire({
                        icon: 'success',
                        title: id ? 'Actualizado' : 'Creado',
                        text: res.message,
                        timer: 1700,
                        showConfirmButton: false,
                    });
                },
                error: function (xhr) {
                    const res = xhr.responseJSON || {};
                    if (xhr.status === 422 && res.errors) {
                        if (res.errors.nombre) {
                            marcarErr('#seg-rol-nombre', '#seg-rol-nombre-error', res.errors.nombre[0]);
                        }
                        if (res.errors.descripcion) {
                            marcarErr('#seg-rol-descripcion', '#seg-rol-descripcion-error', res.errors.descripcion[0]);
                        }
                        return;
                    }
                    Swal.fire('Error', res.message || 'No se pudo guardar el rol.', 'error');
                },
            });
        });

        $(document).on('click', '.seg-del-rol', function () {
            const $btn = $(this);
            const $row = $btn.closest('tr');
            const id = $row.data('rol-id');
            const nombre = $row.data('rol-nombre');
            const motivo = $btn.data('motivo');

            // Bloqueos: en vez de un botón muerto, explicamos por qué no se puede.
            if (motivo === 'sistema') {
                Swal.fire({
                    icon: 'info',
                    title: 'Rol de sistema',
                    html: 'El rol <strong>' + esc(nombre) + '</strong> es de sistema y no se puede eliminar.',
                });
                return;
            }
            if (motivo === 'usuarios') {
                const n = $row.data('usuarios') || 0;
                Swal.fire({
                    icon: 'warning',
                    title: 'No se puede eliminar',
                    html: 'El rol <strong>' + esc(nombre) + '</strong> tiene <strong>' + n + '</strong> usuario' +
                        (n === 1 ? '' : 's') + ' asignado' + (n === 1 ? '' : 's') +
                        '.<br>Reasígnalos a otro rol antes de eliminarlo.',
                });
                return;
            }

            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar rol?',
                html: 'Se eliminará el rol <strong>' + esc(nombre) + '</strong> y sus permisos. Esta acción no se puede deshacer.',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#f06548',
                cancelButtonColor: '#74788d',
            }).then(function (result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: URLS.rolBase + '/' + id,
                    type: 'POST',
                    data: { _token: CSRF, _method: 'DELETE' },
                    success: function (res) {
                        $row.remove();
                        $('#seg-rol-select option[value="' + id + '"]').remove();
                        if ($('#seg-rol-select').val() === String(id)) {
                            $('#seg-rol-select').val('').trigger('change');
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: res.message,
                            timer: 1700,
                            showConfirmButton: false,
                        });
                    },
                    error: function (xhr) {
                        const res = xhr.responseJSON || {};
                        Swal.fire('Error', res.message || 'No se pudo eliminar el rol.', 'error');
                    },
                });
            });
        });

        // =====================================================================
        // TAB PERMISOS — grilla de tarjetas (módulo × acción)
        // =====================================================================

        function $verDe($card) {
            return $card.find('.seg-perm[data-accion="ver"]');
        }

        // Sincroniza una tarjeta de módulo: master "Todo", indeterminado y contador.
        function syncFila($card) {
            const $perms = $card.find('.seg-perm');
            const total = $perms.length;
            const marcados = $perms.filter(':checked').length;
            const $all = $card.find('.seg-all');
            $all.prop('checked', total > 0 && marcados === total);
            $all.prop('indeterminate', marcados > 0 && marcados < total);
            $card.find('.seg-mod-count').text(marcados + '/' + total);
            $card.toggleClass('has-perms', marcados > 0);
        }

        // Contador global en la barra de herramientas.
        function actualizarContadorGlobal() {
            const marcados = $('.seg-perm:checked').length;
            $('#seg-global-count').text(marcados === 1 ? '1 permiso' : marcados + ' permisos');
        }

        function pintarMatriz(permisos) {
            $('.seg-perm').prop('checked', false);
            (permisos || []).forEach(function (p) {
                $('.seg-perm').filter(function () { return this.value === p; }).prop('checked', true);
            });
            $('.seg-mod-card').each(function () { syncFila($(this)); });
            actualizarContadorGlobal();
        }

        // 'ver' es prerrequisito: marcar cualquier acción activa 'ver';
        // desmarcar 'ver' desmarca todas las del módulo.
        $(document).on('change', '.seg-perm', function () {
            const $cb = $(this);
            const $card = $cb.closest('.seg-mod-card');
            if ($cb.data('accion') === 'ver') {
                if (!$cb.is(':checked')) $card.find('.seg-perm').prop('checked', false);
            } else if ($cb.is(':checked')) {
                $verDe($card).prop('checked', true);
            }
            syncFila($card);
            actualizarContadorGlobal();
        });

        $(document).on('change', '.seg-all', function () {
            const $card = $(this).closest('.seg-mod-card');
            $card.find('.seg-perm').prop('checked', $(this).is(':checked'));
            syncFila($card);
            actualizarContadorGlobal();
        });

        // Buscar módulo: filtra las tarjetas por nombre o slug.
        $('#seg-mod-search').on('input', function () {
            const q = $(this).val().trim().toLowerCase();
            let visibles = 0;
            $('.seg-mod-card').each(function () {
                const $card = $(this);
                const match = !q
                    || ($card.data('nombre') + '').indexOf(q) !== -1
                    || ($card.data('modulo') + '').indexOf(q) !== -1;
                $card.toggleClass('d-none', !match);
                if (match) visibles++;
            });
            $('#seg-mod-search-empty').toggleClass('d-none', visibles > 0);
        });

        // Acciones globales: operan sobre las tarjetas visibles (respeta el filtro).
        function setTodasVisibles(marcar) {
            $('.seg-mod-card:not(.d-none)').each(function () {
                const $card = $(this);
                $card.find('.seg-perm').prop('checked', marcar);
                syncFila($card);
            });
            actualizarContadorGlobal();
        }
        $('#seg-marcar-todo').on('click', function () { setTodasVisibles(true); });
        $('#seg-limpiar-todo').on('click', function () { setTodasVisibles(false); });

        $('#seg-rol-select').on('change', function () {
            const rolId = $(this).val();
            const $wrapper = $('#seg-matriz-wrapper');
            const $placeholder = $('#seg-matriz-placeholder');

            if (!rolId) {
                $wrapper.addClass('d-none');
                $placeholder.removeClass('d-none');
                $('#seg-guardar-permisos').prop('disabled', true);
                return;
            }

            $.ajax({
                url: URLS.permisosBase + '/' + rolId,
                type: 'GET',
                success: function (res) {
                    // Reinicia el buscador para mostrar todos los módulos del nuevo rol.
                    $('#seg-mod-search').val('').trigger('input');
                    pintarMatriz(res.permisos || []);
                    $placeholder.addClass('d-none');
                    $wrapper.removeClass('d-none');
                    $('#seg-guardar-permisos').prop('disabled', false);
                },
                error: function (xhr) {
                    const res = xhr.responseJSON || {};
                    Swal.fire('Error', res.message || 'No se pudieron cargar los permisos.', 'error');
                },
            });
        });

        $('#seg-guardar-permisos').on('click', function () {
            const rolId = $('#seg-rol-select').val();
            if (!rolId) return;

            const permisos = $('.seg-perm:checked').map(function () { return this.value; }).get();

            $.ajax({
                url: URLS.permisosBase + '/' + rolId,
                type: 'POST',
                data: { _token: CSRF, _method: 'PUT', permisos: permisos },
                success: function (res) {
                    pintarMatriz(res.permisos || []);
                    Swal.fire({
                        icon: 'success',
                        title: 'Guardado',
                        text: res.message,
                        timer: 1700,
                        showConfirmButton: false,
                    });
                },
                error: function (xhr) {
                    const res = xhr.responseJSON || {};
                    Swal.fire('Error', res.message || 'No se pudieron guardar los permisos.', 'error');
                },
            });
        });
    })();
</script>
