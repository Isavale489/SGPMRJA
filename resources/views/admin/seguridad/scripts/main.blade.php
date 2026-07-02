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
            $('#seg-rol-nombre').prop('disabled', false).removeClass('campo-protegido');
            $('#seg-rol-nota-sistema').addClass('d-none');
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

        function rolCardHtml(rol) {
            const usuarios = rol.usuarios_count || 0;
            const permisos = rol.permisos_count || 0;
            const motivo = usuarios > 0 ? 'usuarios' : '';   // los roles creados nunca son de sistema
            const delTitle = motivo === 'usuarios' ? 'Tiene usuarios asignados' : 'Eliminar';
            return '' +
                '<div class="seg-rol-card" data-rol-id="' + rol.id + '" data-rol-nombre="' + esc(rol.nombre) + '"' +
                ' data-rol-descripcion="' + esc(rol.descripcion || '') + '" data-es-sistema="0"' +
                ' data-usuarios="' + usuarios + '">' +
                '<div class="seg-rol-card-head">' +
                    '<span class="seg-rol-card-ic"><i class="ri-user-settings-line"></i></span>' +
                    '<div class="seg-rol-card-id"><span class="seg-rol-card-nombre seg-rol-nombre">' + esc(rol.nombre) + '</span></div>' +
                '</div>' +
                '<p class="seg-rol-card-desc seg-rol-desc' + (rol.descripcion ? '' : ' is-empty') + '">' +
                    (rol.descripcion ? esc(rol.descripcion) : 'Sin descripción.') + '</p>' +
                '<div class="seg-rol-card-meta">' +
                    '<span class="seg-rol-meta-chip"><i class="ri-group-line"></i><span class="seg-rol-usuarios">' + usuarios + '</span> usuario' + (usuarios === 1 ? '' : 's') + '</span>' +
                    '<span class="seg-rol-meta-chip"><i class="ri-key-2-line"></i><span class="seg-rol-permisos">' + permisos + ' permiso' + (permisos === 1 ? '' : 's') + '</span></span>' +
                '</div>' +
                '<div class="seg-rol-card-foot">' +
                    '<button type="button" class="btn btn-sm btn-soft-success seg-config-rol"><i class="ri-lock-2-line align-bottom me-1"></i>Configurar permisos</button>' +
                    '<div class="d-inline-flex gap-1 ms-auto">' +
                        '<button type="button" class="btn btn-sm btn-soft-primary seg-edit-rol" title="Editar"><i class="ri-pencil-fill"></i></button>' +
                        '<button type="button" class="btn btn-sm btn-soft-danger seg-del-rol' + (motivo ? ' is-blocked' : '') + '"' +
                            ' data-motivo="' + motivo + '" title="' + delTitle + '"><i class="ri-delete-bin-fill"></i></button>' +
                    '</div>' +
                '</div>' +
                '</div>';
        }

        function upsertRolCard(rol) {
            const $card = $('#seg-roles-grid .seg-rol-card[data-rol-id="' + rol.id + '"]');
            if ($card.length) {
                $card.attr('data-rol-nombre', rol.nombre).attr('data-rol-descripcion', rol.descripcion || '');
                $card.find('.seg-rol-nombre').text(rol.nombre);
                $card.find('.seg-rol-desc').text(rol.descripcion || 'Sin descripción.')
                    .toggleClass('is-empty', !rol.descripcion);
            } else {
                $('#seg-roles-grid').append(rolCardHtml(rol));
            }
        }

        function upsertRolOption(rol) {
            let $opt = $('#seg-rol-select option[value="' + rol.id + '"]');
            if (!$opt.length) {
                $opt = $('<option>').val(rol.id).appendTo('#seg-rol-select');
            }
            $opt.text(rol.nombre)
                .attr('data-descripcion', rol.descripcion || '')
                .attr('data-usuarios', rol.usuarios_count || 0)
                .attr('data-sistema', rol.es_sistema ? 1 : 0);
        }

        $('#seg-nuevo-rol-btn').on('click', function () {
            resetRolForm();
            $('#rolModalTitle').text('Nuevo rol');
            rolModal.show();
        });

        $(document).on('click', '.seg-edit-rol', function () {
            const $card = $(this).closest('.seg-rol-card');
            const esSistema = $card.attr('data-es-sistema') === '1';
            resetRolForm();
            // Roles de sistema: el nombre es identidad protegida; solo se edita la descripción.
            $('#rolModalTitle').text(esSistema ? 'Editar descripción' : 'Editar rol');
            $('#id-field').val($card.attr('data-rol-id'));
            $('#seg-rol-nombre').val($card.attr('data-rol-nombre'))
                .prop('disabled', esSistema).toggleClass('campo-protegido', esSistema);
            $('#seg-rol-nota-sistema').toggleClass('d-none', !esSistema);
            $('#seg-rol-descripcion').val($card.attr('data-rol-descripcion')).trigger('focus');
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
                    upsertRolCard(res.rol);
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
            const $row = $btn.closest('.seg-rol-card');
            const id = $row.attr('data-rol-id');
            const nombre = $row.attr('data-rol-nombre');
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
                const n = parseInt($row.attr('data-usuarios'), 10) || 0;
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

        // "Configurar permisos" desde la tarjeta del rol: salta a la pestaña
        // Permisos con el rol ya seleccionado (el guard de cambios sin guardar
        // del select aplica igual, porque se dispara su change normal).
        $(document).on('click', '.seg-config-rol', function () {
            const id = $(this).closest('.seg-rol-card').attr('data-rol-id');
            const tabEl = document.querySelector('a[href="#seg-tab-permisos"]');
            bootstrap.Tab.getOrCreateInstance(tabEl).show();
            $('#seg-rol-select').val(id).trigger('change');
        });

        // =====================================================================
        // TAB PERMISOS — matriz por secciones (módulo × acción)
        // =====================================================================

        const TOTAL_PERMISOS = $('.seg-perm').length;
        let rolCargado = '';                 // id del rol actualmente en edición
        let permisosOriginales = new Set();  // snapshot del último load/save (para el estado sucio)
        let revirtiendoSelect = false;       // evita re-entrar al guard al revertir el select

        function $verDe($card) {
            return $card.find('.seg-perm[data-accion="ver"]');
        }

        function permisosActuales() {
            return $('.seg-perm:checked').map(function () { return this.value; }).get();
        }

        // Diff contra el snapshot: cuántas acciones se agregaron o quitaron.
        function contarCambios() {
            const actuales = new Set(permisosActuales());
            let cambios = 0;
            actuales.forEach(function (p) { if (!permisosOriginales.has(p)) cambios++; });
            permisosOriginales.forEach(function (p) { if (!actuales.has(p)) cambios++; });
            return cambios;
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

        // Contadores por sección + global + estado sucio (chip y botón Guardar).
        function actualizarContadores() {
            $('.seg-seccion').each(function () {
                const $sec = $(this);
                const $perms = $sec.find('.seg-perm');
                $sec.find('[data-seccion-count]').text($perms.filter(':checked').length + '/' + $perms.length);
            });

            const marcados = $('.seg-perm:checked').length;
            $('#seg-global-count').text(marcados + ' de ' + TOTAL_PERMISOS + ' permisos');

            const cambios = contarCambios();
            $('#seg-cambios-chip')
                .toggleClass('d-none', cambios === 0)
                .html('<i class="ri-circle-fill"></i>' + cambios + (cambios === 1 ? ' cambio sin guardar' : ' cambios sin guardar'));
            $('#seg-save-hint').toggleClass('d-none', cambios > 0);
            $('#seg-guardar-permisos').prop('disabled', cambios === 0);
        }

        function hayCambios() {
            return rolCargado !== '' && contarCambios() > 0;
        }

        // Marca exactamente el set indicado SIN tocar el snapshot: el resultado
        // queda como cambio pendiente (lo usan "Copiar de…" y el guard de dirty).
        function aplicarPermisos(permisos) {
            $('.seg-perm').prop('checked', false);
            (permisos || []).forEach(function (p) {
                $('.seg-perm').filter(function () { return this.value === p; }).prop('checked', true);
            });
            $('.seg-mod-card').each(function () { syncFila($(this)); });
            actualizarContadores();
        }

        function pintarMatriz(permisos) {
            permisosOriginales = new Set(permisos || []);
            aplicarPermisos(permisos);
        }

        // Resumen del rol en edición (datos desde el <option> seleccionado).
        // Lectura con .attr() (no .data()) para ver actualizaciones del CRUD de roles.
        function pintarResumenRol() {
            const $opt = $('#seg-rol-select option:selected');
            const usuarios = parseInt($opt.attr('data-usuarios'), 10) || 0;
            $('#seg-res-nombre').text(($opt.text() || '').replace(' (sistema)', '').trim());
            $('#seg-res-sistema').toggleClass('d-none', String($opt.attr('data-sistema')) !== '1');
            $('#seg-res-usuarios').html('<i class="ri-group-line"></i>' + usuarios + (usuarios === 1 ? ' usuario' : ' usuarios'));
            const desc = $opt.attr('data-descripcion') || '';
            $('#seg-res-desc').text(desc).toggleClass('d-none', !desc);
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
            actualizarContadores();
        });

        $(document).on('change', '.seg-all', function () {
            const $card = $(this).closest('.seg-mod-card');
            $card.find('.seg-perm').prop('checked', $(this).is(':checked'));
            syncFila($card);
            actualizarContadores();
        });

        // Buscar módulo: filtra tarjetas por nombre o slug; oculta secciones vacías.
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
            $('.seg-seccion').each(function () {
                const $sec = $(this);
                $sec.toggleClass('d-none', $sec.find('.seg-mod-card:not(.d-none)').length === 0);
            });
            $('#seg-mod-search-empty').toggleClass('d-none', visibles > 0);
        });

        // Marca/limpia un conjunto de tarjetas (solo las visibles: respeta el filtro).
        function setCards($cards, marcar) {
            $cards.each(function () {
                const $card = $(this);
                $card.find('.seg-perm').prop('checked', marcar);
                syncFila($card);
            });
            actualizarContadores();
        }
        $('#seg-marcar-todo').on('click', function () { setCards($('.seg-mod-card:not(.d-none)'), true); });
        $('#seg-limpiar-todo').on('click', function () { setCards($('.seg-mod-card:not(.d-none)'), false); });

        // Preset "Solo Ver": rol de consulta — únicamente la acción 'ver' de los
        // módulos visibles (respeta el filtro de búsqueda).
        $('#seg-solo-ver').on('click', function () {
            $('.seg-mod-card:not(.d-none)').each(function () {
                const $card = $(this);
                $card.find('.seg-perm').prop('checked', false);
                $verDe($card).prop('checked', true);
                syncFila($card);
            });
            actualizarContadores();
        });

        // "Copiar de…": el menú se arma al abrirse con los demás roles del select
        // (siempre al día con el CRUD de roles; excluye el rol en edición).
        $('#seg-copiar-wrap').on('show.bs.dropdown', function () {
            const items = [];
            $('#seg-rol-select option').each(function () {
                const v = $(this).val();
                if (!v || v === rolCargado) return;
                items.push('<li><button type="button" class="dropdown-item seg-copiar-item" data-rol="' + v + '">'
                    + esc($(this).text()) + '</button></li>');
            });
            $('#seg-copiar-menu').html(items.length
                ? items.join('')
                : '<li><span class="dropdown-item disabled">No hay otros roles para copiar</span></li>');
        });

        $(document).on('click', '.seg-copiar-item', function () {
            const srcId = $(this).data('rol');
            const srcNombre = $(this).text();
            $.ajax({
                url: URLS.permisosBase + '/' + srcId,
                type: 'GET',
                success: function (res) {
                    aplicarPermisos(res.permisos || []);
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: 'Permisos de "' + srcNombre + '" precargados',
                        text: 'Revisa y presiona Guardar para aplicarlos.',
                        timer: 2600,
                        showConfirmButton: false,
                    });
                },
                error: function (xhr) {
                    const res = xhr.responseJSON || {};
                    Swal.fire('Error', res.message || 'No se pudieron copiar los permisos.', 'error');
                },
            });
        });
        $(document).on('click', '.seg-sec-marcar', function () {
            setCards($(this).closest('.seg-seccion').find('.seg-mod-card:not(.d-none)'), true);
        });
        $(document).on('click', '.seg-sec-limpiar', function () {
            setCards($(this).closest('.seg-seccion').find('.seg-mod-card:not(.d-none)'), false);
        });

        function cargarRol(rolId) {
            const $wrapper = $('#seg-matriz-wrapper');
            const $placeholder = $('#seg-matriz-placeholder');

            if (!rolId) {
                rolCargado = '';
                permisosOriginales = new Set();
                $wrapper.addClass('d-none');
                $placeholder.removeClass('d-none');
                $('#seg-guardar-permisos').prop('disabled', true);
                return;
            }

            $.ajax({
                url: URLS.permisosBase + '/' + rolId,
                type: 'GET',
                success: function (res) {
                    rolCargado = String(rolId);
                    // Reinicia el buscador para mostrar todos los módulos del nuevo rol.
                    $('#seg-mod-search').val('').trigger('input');
                    pintarMatriz(res.permisos || []);
                    pintarResumenRol();
                    $placeholder.addClass('d-none');
                    $wrapper.removeClass('d-none');
                },
                error: function (xhr) {
                    const res = xhr.responseJSON || {};
                    Swal.fire('Error', res.message || 'No se pudieron cargar los permisos.', 'error');
                },
            });
        }

        function guardarPermisos(rolId, done) {
            $.ajax({
                url: URLS.permisosBase + '/' + rolId,
                type: 'POST',
                data: { _token: CSRF, _method: 'PUT', permisos: permisosActuales() },
                success: function (res) {
                    // Refresca el chip "N permisos" de la tarjeta del rol (tab Roles)
                    const n = (res.permisos || []).length;
                    $('.seg-rol-card[data-rol-id="' + rolId + '"] .seg-rol-permisos')
                        .text(n + (n === 1 ? ' permiso' : ' permisos'));
                    if (done) { done(res); return; }
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
        }

        // Cambio de rol: si hay cambios sin guardar, guard Guardar/Descartar/Seguir
        // (mismo patrón que AtlanticoGuard en los modales de edición).
        $('#seg-rol-select').on('change', function () {
            if (revirtiendoSelect) { revirtiendoSelect = false; return; }

            const nuevoRol = $(this).val();
            if (!hayCambios()) { cargarRol(nuevoRol); return; }

            const rolAnterior = rolCargado;
            Swal.fire({
                icon: 'warning',
                title: 'Cambios sin guardar',
                text: 'Modificaste permisos de este rol y aún no los guardas.',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: '<i class="ri-save-3-line me-1"></i>Guardar',
                denyButtonText: 'Descartar',
                cancelButtonText: 'Seguir editando',
                confirmButtonColor: '#0ab39c',
                denyButtonColor: '#f06548',
                cancelButtonColor: '#74788d',
            }).then(function (r) {
                if (r.isConfirmed) {
                    guardarPermisos(rolAnterior, function () { cargarRol(nuevoRol); });
                } else if (r.isDenied) {
                    cargarRol(nuevoRol);
                } else {
                    revirtiendoSelect = true;
                    $('#seg-rol-select').val(rolAnterior).trigger('change');
                }
            });
        });

        $('#seg-guardar-permisos').on('click', function () {
            if (rolCargado) guardarPermisos(rolCargado);
        });

        // Salir de la página con cambios sin guardar → confirmación nativa.
        window.addEventListener('beforeunload', function (e) {
            if (hayCambios()) { e.preventDefault(); e.returnValue = ''; }
        });
    })();
</script>
