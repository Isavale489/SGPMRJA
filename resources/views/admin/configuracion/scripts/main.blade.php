<script>
    (function () {
        'use strict';

        // ============================================================
        // Panel de Configuración del Sistema (FEAT-004)
        // Dirigido por el registry: el JS no conoce parámetros
        // concretos — lee claves, tipos y límites del DOM.
        // ============================================================

        const CSRF = '{{ csrf_token() }}';

        // ---------- Validación (patrón blur + submit del proyecto) ----------

        function validarCampo($input) {
            const $campo = $input.closest('.config-campo');
            const tipo = $campo.data('tipo');

            if (tipo === 'booleano') {
                return true; // un switch siempre es válido
            }

            const valor = String($input.val() ?? '').trim();

            if ($input.data('requerido') && valor === '') {
                marcarInvalido($input, 'Este campo es obligatorio.');
                return false;
            }

            if (tipo === 'decimal' || tipo === 'entero') {
                const num = Number(valor);
                if (valor === '' || isNaN(num)) {
                    marcarInvalido($input, 'Ingresa un valor numérico.');
                    return false;
                }
                const min = $input.attr('min');
                const max = $input.attr('max');
                if (min !== undefined && num < Number(min)) {
                    marcarInvalido($input, 'El valor mínimo permitido es ' + min + '.');
                    return false;
                }
                if (max !== undefined && num > Number(max)) {
                    marcarInvalido($input, 'El valor máximo permitido es ' + max + '.');
                    return false;
                }
                if (tipo === 'entero' && !Number.isInteger(num)) {
                    marcarInvalido($input, 'Ingresa un número entero.');
                    return false;
                }
            }

            marcarValido($input);
            return true;
        }

        function validarFormularioConfig($form) {
            let valido = true;
            $form.find('.config-input').each(function () {
                if (!validarCampo($(this))) valido = false;
            });
            return valido;
        }

        $(document).on('blur', '.config-input', function () {
            validarCampo($(this));
        });

        // ---------- Recolección de valores ----------

        function valorDeCampo($campo) {
            const $input = $campo.find('.config-input');
            if ($campo.data('tipo') === 'booleano') {
                return $input.is(':checked') ? '1' : '0';
            }
            return String($input.val() ?? '').trim();
        }

        function valoresDeForm($form) {
            const valores = {};
            $form.find('.config-campo').each(function () {
                valores[$(this).data('clave')] = valorDeCampo($(this));
            });
            return valores;
        }

        // ---------- Guardar módulo ----------

        function guardarModulo($form) {
            $.ajax({
                url: $form.data('update-url'),
                type: 'POST',
                data: { _token: CSRF, _method: 'PUT', valores: valoresDeForm($form) },
                success: function (res) {
                    // Tras guardar, todos los campos del módulo pasan a tener
                    // override: badge fuera, botón Restablecer disponible.
                    $form.find('.config-campo').each(function () {
                        $(this).find('.config-badge-default').addClass('d-none');
                        $(this).find('.config-reset-btn').removeClass('d-none');
                        limpiarValidacion($(this).find('.config-input'));
                    });
                    Swal.fire({
                        icon: 'success',
                        title: 'Guardado',
                        text: res.message,
                        timer: 1800,
                        showConfirmButton: false
                    });
                },
                error: function (xhr) {
                    const res = xhr.responseJSON || {};
                    if (xhr.status === 422 && res.errors) {
                        // Errores keyed por clave del registry (sin escapar)
                        Object.keys(res.errors).forEach(function (clave) {
                            const $campo = $form.find('.config-campo').filter(function () {
                                return $(this).data('clave') === clave;
                            });
                            marcarInvalido($campo.find('.config-input'), res.errors[clave][0]);
                        });
                        return;
                    }
                    Swal.fire('Error', res.message || 'No se pudo guardar la configuración.', 'error');
                }
            });
        }

        $(document).on('submit', '.config-form', function (e) {
            e.preventDefault();
            const $form = $(this);

            if (!validarFormularioConfig($form)) return;

            // Confirmación previa declarada en el registry (data-confirmar)
            if ($form.data('confirmar')) {
                Swal.fire({
                    icon: 'warning',
                    title: '¿Guardar cambios?',
                    text: $form.data('confirmar-mensaje'),
                    showCancelButton: true,
                    confirmButtonText: 'Sí, guardar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#0ab39c',
                    cancelButtonColor: '#74788d'
                }).then(function (result) {
                    if (result.isConfirmed) guardarModulo($form);
                });
                return;
            }

            guardarModulo($form);
        });

        // ---------- Restablecer parámetro ----------

        $(document).on('click', '.config-reset-btn', function () {
            const $btn = $(this);
            const $campo = $btn.closest('.config-campo');

            Swal.fire({
                icon: 'question',
                title: '¿Restablecer?',
                text: 'El parámetro volverá a su valor por defecto.',
                showCancelButton: true,
                confirmButtonText: 'Sí, restablecer',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#0ab39c',
                cancelButtonColor: '#74788d'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: $btn.data('reset-url'),
                    type: 'POST',
                    data: { _token: CSRF, _method: 'DELETE' },
                    success: function (res) {
                        const $input = $campo.find('.config-input');
                        if ($campo.data('tipo') === 'booleano') {
                            $input.prop('checked', Boolean(res.default));
                        } else {
                            $input.val(res.default);
                        }
                        limpiarValidacion($input);
                        $campo.find('.config-badge-default').removeClass('d-none');
                        $btn.addClass('d-none');
                        Swal.fire({
                            icon: 'success',
                            title: 'Restablecido',
                            text: res.message,
                            timer: 1800,
                            showConfirmButton: false
                        });
                    },
                    error: function (xhr) {
                        const res = xhr.responseJSON || {};
                        Swal.fire('Error', res.message || 'No se pudo restablecer el parámetro.', 'error');
                    }
                });
            });
        });

        // ============================================================
        // Impuestos (tabla `impuesto`) — crear / editar / eliminar
        // ============================================================

        const $impModal = $('#impuestoModal');
        const impModal = $impModal.length
            ? bootstrap.Modal.getOrCreateInstance($impModal[0])
            : null;

        function limpiarErroresImpuesto() {
            $impModal.find('.form-control, .form-select').removeClass('is-invalid');
            $impModal.find('.invalid-feedback').text('');
        }

        function abrirModalImpuesto() {
            limpiarErroresImpuesto();
            $('#form-impuesto')[0].reset();
            $('#id-field').val('');
            $('#impuestoModalTitle').text('Nuevo impuesto');
            $('#form-impuesto').data('save-url', $('#imp-store-url').val()).data('method', 'POST');
            $('#imp-codigo').prop('readonly', false);
            $('#imp-estado').prop('disabled', false);
            $('#imp-estado-wrapper').show();
            $('#imp-codigo-help').text('Identificador corto en mayúsculas.');
            impModal.show();
        }

        $(document).on('click', '#btn-nuevo-impuesto', abrirModalImpuesto);

        $(document).on('click', '.btn-editar-impuesto', function () {
            const d = $(this).data();
            limpiarErroresImpuesto();
            $('#id-field').val(d.id);
            $('#imp-codigo').val(d.codigo);
            $('#imp-nombre').val(d.nombre);
            $('#imp-porcentaje').val(d.porcentaje);
            $('#imp-descripcion').val(d.descripcion || '');
            $('#imp-estado').val(d.estado);
            $('#impuestoModalTitle').text('Editar impuesto');
            $('#form-impuesto').data('save-url', d.updateUrl).data('method', 'PUT');

            // El IVA es el impuesto base de compras: código inmutable y siempre activo.
            const esIva = String(d.esIva) === '1';
            $('#imp-codigo').prop('readonly', esIva);
            $('#imp-estado').prop('disabled', esIva);
            $('#imp-estado-wrapper').toggle(!esIva);
            $('#imp-codigo-help').text(esIva
                ? 'El código del IVA no se puede cambiar.'
                : 'Identificador corto en mayúsculas.');

            impModal.show();
        });

        $(document).on('submit', '#form-impuesto', function (e) {
            e.preventDefault();
            limpiarErroresImpuesto();

            const $form = $(this);
            const data = {
                _token: CSRF,
                _method: $form.data('method'),
                codigo: $('#imp-codigo').val().trim().toUpperCase(),
                nombre: $('#imp-nombre').val().trim(),
                porcentaje: $('#imp-porcentaje').val(),
                descripcion: $('#imp-descripcion').val().trim(),
                estado: $('#imp-estado').val()
            };

            $('#btn-guardar-impuesto').prop('disabled', true);

            $.ajax({
                url: $form.data('save-url'),
                type: 'POST',
                data: data,
                success: function (res) {
                    impModal.hide();
                    Swal.fire({
                        icon: 'success', title: 'Listo', text: res.message,
                        timer: 1600, showConfirmButton: false
                    }).then(function () { window.location.reload(); });
                },
                error: function (xhr) {
                    const res = xhr.responseJSON || {};
                    if (xhr.status === 422 && res.errors) {
                        Object.keys(res.errors).forEach(function (campo) {
                            $('#imp-' + campo).addClass('is-invalid');
                            $('#imp-' + campo + '-error').text(res.errors[campo][0]);
                        });
                        return;
                    }
                    Swal.fire('Error', res.message || 'No se pudo guardar el impuesto.', 'error');
                },
                complete: function () { $('#btn-guardar-impuesto').prop('disabled', false); }
            });
        });

        $(document).on('click', '.btn-eliminar-impuesto', function () {
            const $btn = $(this);
            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar impuesto?',
                text: 'Se eliminará "' + $btn.data('nombre') + '".',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#f06548',
                cancelButtonColor: '#74788d'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: $btn.data('delete-url'),
                    type: 'POST',
                    data: { _token: CSRF, _method: 'DELETE' },
                    success: function (res) {
                        Swal.fire({
                            icon: 'success', title: 'Eliminado', text: res.message,
                            timer: 1500, showConfirmButton: false
                        }).then(function () { window.location.reload(); });
                    },
                    error: function (xhr) {
                        const res = xhr.responseJSON || {};
                        Swal.fire('Error', res.message || 'No se pudo eliminar el impuesto.', 'error');
                    }
                });
            });
        });

        // ---------- Persistencia del módulo activo en el hash ----------

        $('#config-pills [data-bs-toggle="pill"]').on('shown.bs.tab', function () {
            history.replaceState(null, '', '#' + $(this).data('config-slug'));
        });

        const hash = window.location.hash.replace('#', '');
        if (hash) {
            const pill = document.querySelector('#pill-' + CSS.escape(hash));
            if (pill) bootstrap.Tab.getOrCreateInstance(pill).show();
        }
    })();
</script>
