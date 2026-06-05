<script>
$(document).ready(function () {

    // ╔══════════════════════════════════════════════════════════════════
    // ║ PEDIDO WIZARD — Scaffold de navegación (4 pasos)
    // ║ showStep, validarStep, siguiente, anterior, stepper visual
    // ╚══════════════════════════════════════════════════════════════════
    (function () {
        'use strict';

        var TOTAL_STEPS = 4;
        var currentStep = 1;

        function isEditMode() { return !!$('#ped-wiz-id-field').val(); }

        // Datos del usuario logueado para el chip "Creado por" en modo crear
        window.pedCreadorDefault = { name: @json(Auth::user()->name), avatar: @json(Auth::user()->avatar_url) };

        function showStep(n) {
            n = Math.max(1, Math.min(TOTAL_STEPS, n));
            currentStep = n;

            // Mostrar sección activa, ocultar el resto
            document.querySelectorAll('#ped-wiz-step-1, #ped-wiz-step-2, #ped-wiz-step-3, #ped-wiz-step-4').forEach(function (sec) {
                var step = parseInt(sec.dataset.step, 10);
                var active = step === n;
                sec.classList.toggle('is-active', active);
                sec.hidden = !active;
            });

            // Markers del stepper (dentro del wizard de pedidos)
            document.querySelectorAll('#pedidoForm').forEach(function () {
                document.querySelectorAll('.wiz-step-marker[data-step]').forEach(function (mk) {
                    var step = parseInt(mk.dataset.step, 10);
                    mk.classList.toggle('is-active', step === n);
                    mk.classList.toggle('is-complete', step < n);
                    mk.setAttribute('aria-selected', step === n ? 'true' : 'false');
                });
            });

            // Líneas de progreso
            document.querySelectorAll('.wiz-step-line-fill[data-line]').forEach(function (lf) {
                var line = parseInt(lf.dataset.line, 10);
                lf.style.width = (line < n) ? '100%' : '0%';
            });

            // Contador de paso
            var ind = document.getElementById('ped-step-current');
            if (ind) ind.textContent = String(n);

            // Visibilidad de botones de footer
            var $prev  = $('#btn-ped-prev');
            var $next  = $('#btn-ped-next');
            var $add   = $('#ped-wiz-add-btn');
            var $edit  = $('#ped-wiz-edit-btn');

            $prev.toggle(n > 1);

            if (n === TOTAL_STEPS) {
                $next.hide();
                if (isEditMode()) { $add.hide(); $edit.show(); }
                else              { $edit.hide(); $add.show(); }
            } else {
                $next.show();
                $add.hide(); $edit.hide();
            }

            // Hook: sincronizar total al entrar en paso 3
            if (n === 3 && typeof window.pedSincronizarTotalPago === 'function') {
                window.pedSincronizarTotalPago();
            }
            // Hook: renderizar resumen al entrar en paso 4
            if (n === 4 && typeof window.pedRenderResumen === 'function') {
                window.pedRenderResumen();
            }

            // Banner cliente persistente: visible en pasos 2+ si hay cliente
            var hasClient = !!$('#ped-wiz-cliente-id-field').val();
            var showBanner = hasClient && n > 1;
            $('#ped-cliente-banner').attr('hidden', !showBanner).attr('aria-hidden', showBanner ? 'false' : 'true');

            // Chip "Creado por": siempre visible. Crear → usuario logueado; editar → creador real (hidratado).
            if (!isEditMode() && window.pedCreadorDefault) {
                $('#ped-creador-name').text(window.pedCreadorDefault.name);
                $('#ped-creador-avatar').attr('src', window.pedCreadorDefault.avatar);
            }
            $('#ped-creador-banner').removeAttr('hidden').attr('aria-hidden', 'false');
        }

        function validateStep(n) {
            if (n === 1) {
                var clienteId = $('#ped-wiz-cliente-id-field').val();
                var fecha     = $('#ped-fecha-pedido-field').val();

                if (!clienteId) {
                    Swal.fire({
                        icon: 'warning', title: 'Cliente requerido',
                        text: 'Debes seleccionar o crear un cliente antes de continuar.',
                        timer: 2200, showConfirmButton: false
                    });
                    $('#ped-ci-rif-number-field').trigger('focus');
                    return false;
                }
                if (!fecha) {
                    Swal.fire({
                        icon: 'warning', title: 'Fecha requerida',
                        text: 'La fecha del pedido es obligatoria.',
                        timer: 2200, showConfirmButton: false
                    });
                    $('#ped-fecha-pedido-field').trigger('focus');
                    return false;
                }
                var entrega = $('#ped-fecha-entrega-field').val();
                if (entrega && entrega < fecha) {
                    Swal.fire({
                        icon: 'warning', title: 'Fechas inconsistentes',
                        text: 'La fecha de entrega no puede ser anterior a la del pedido.',
                        timer: 2400, showConfirmButton: false
                    });
                    $('#ped-fecha-entrega-field').trigger('focus');
                    return false;
                }
                var $invalid = $('#ped-wiz-step-1 .is-invalid:visible');
                if ($invalid.length) {
                    Swal.fire({
                        icon: 'warning', title: 'Corrige los errores',
                        text: 'Revisa los campos marcados en rojo antes de continuar.',
                        timer: 2200, showConfirmButton: false
                    });
                    $invalid.first().trigger('focus');
                    return false;
                }
                return true;
            }
            if (n === 2) {
                var items = (window.pedProdState && window.pedProdState.items) || [];
                if (!items.length) {
                    Swal.fire({
                        icon: 'warning', title: 'Sin productos',
                        text: 'Debes agregar al menos un producto antes de continuar.',
                        timer: 2200, showConfirmButton: false
                    });
                    return false;
                }
                return true;
            }
            if (n === 3) {
                var PED_METODO_LABELS = { efectivo: 'Efectivo', transferencia: 'Transferencia', pago_movil: 'Pago Móvil' };
                var total3 = parseFloat($('#ped-pago-total-display').val()) || 0;
                var pagos3 = (window.pedPagoState && window.pedPagoState.pagos) || [];
                var abono3 = pagos3.reduce(function (a, p) { return a + (p.monto || 0); }, 0);

                for (var i = 0; i < pagos3.length; i++) {
                    var p3 = pagos3[i];
                    var label3 = PED_METODO_LABELS[p3.metodo] || p3.metodo;
                    if (!(p3.monto > 0)) {
                        Swal.fire({
                            icon: 'warning', title: 'Monto requerido',
                            text: 'Ingresá un monto mayor a 0 para ' + label3 + ', o quitá ese pago.',
                            timer: 2600, showConfirmButton: false
                        });
                        $('#ped-pay-list .ped-pay-monto').eq(i).trigger('focus');
                        return false;
                    }
                    if (p3.metodo === 'transferencia' || p3.metodo === 'pago_movil') {
                        if (!p3.banco_id) {
                            Swal.fire({
                                icon: 'warning', title: 'Banco requerido',
                                text: 'Seleccioná el banco para ' + label3 + '.',
                                timer: 2400, showConfirmButton: false
                            });
                            return false;
                        }
                        if (!p3.referencia) {
                            Swal.fire({
                                icon: 'warning', title: 'Referencia requerida',
                                text: 'Ingresá la referencia para ' + label3 + '.',
                                timer: 2400, showConfirmButton: false
                            });
                            return false;
                        }
                    }
                }
                if (abono3 > total3 + 0.001) {
                    Swal.fire({
                        icon: 'warning', title: 'Abono inválido',
                        text: 'La suma de los métodos ($' + abono3.toFixed(2) + ') no puede superar el total ($' + total3.toFixed(2) + ').',
                        timer: 2800, showConfirmButton: false
                    });
                    return false;
                }
                return true;
            }
            // Paso 4 se implementa en TASK-014
            return true;
        }

        function nextStep() {
            if (currentStep < TOTAL_STEPS) {
                if (!validateStep(currentStep)) return;
                showStep(currentStep + 1);
            }
        }
        function prevStep() {
            if (currentStep > 1) showStep(currentStep - 1);
        }

        // Botones de footer
        $('#btn-ped-next').on('click', nextStep);
        $('#btn-ped-prev').on('click', prevStep);

        // El form se envía por AJAX (botón submit) — evitar submit nativo por Enter
        $('#pedidoForm').on('submit', function (e) { e.preventDefault(); });

        // Click en marker del stepper: retroceder libre, avanzar con validación
        $(document).on('click', '.wiz-step-marker[data-step]', function () {
            // Actuar solo cuando el wizard de pedidos está visible
            if (!$('#ped-wiz-step-1').length) return;
            var target = parseInt(this.dataset.step, 10);
            if (target === currentStep) return;
            if (target < currentStep) { showStep(target); return; }
            for (var s = currentStep; s < target; s++) {
                if (!validateStep(s)) return;
            }
            showStep(target);
        });

        // Lifecycle del modal
        var $wizModal = $(document).find('#pedidoForm').closest('.modal');
        if (!$wizModal.length) $wizModal = $('#showModal');

        $wizModal.on('show.bs.modal', function () { showStep(1); });
        $wizModal.on('hidden.bs.modal', function () { currentStep = 1; });

        // API global
        window.pedWizard = { show: showStep, next: nextStep, prev: prevStep };

        // Modo protegido: cliente + fecha del pedido de solo lectura, oculta acciones de
        // producto y "cambiar cliente" (vía clase .ped-wiz-locked en CSS). Lo usan tanto
        // "completar desde cotización" como "editar" — un pedido nunca crea cliente/productos.
        window.pedAplicarModoProtegido = function () {
            $('#showModal').addClass('ped-wiz-locked');
            $('#ped-fecha-pedido-field').prop('readonly', true).addClass('campo-protegido');
            $('#ped-ci-rif-number-field').prop('readonly', true).addClass('campo-protegido');
            $('#ped-ci-rif-prefix-field').prop('disabled', true).addClass('campo-protegido');
        };
        window.pedQuitarModoProtegido = function () {
            $('#showModal').removeClass('ped-wiz-locked');
            $('#ped-fecha-pedido-field').prop('readonly', false).removeClass('campo-protegido');
            $('#ped-ci-rif-number-field').prop('readonly', false).removeClass('campo-protegido');
            $('#ped-ci-rif-prefix-field').prop('disabled', false).removeClass('campo-protegido');
        };
    })();


    // ╔══════════════════════════════════════════════════════════════════
    // ║ PEDIDO WIZARD — PASO 1: Cliente y datos
    // ║ Autocomplete personas-search, tarjeta cliente, chips prioridad/estado
    // ╚══════════════════════════════════════════════════════════════════
    (function () {
        'use strict';

        // Estado de autocompletado
        var pedClienteSeleccionado = false;
        var pedAutocompleteTimeout = null;

        // === Avatar: iniciales + color ===================================
        var AVATAR_COLORS = [
            { bg: '#1e3c72', fg: '#ffffff' },
            { bg: '#00b88c', fg: '#ffffff' },
            { bg: '#0c4a6e', fg: '#ffffff' },
            { bg: '#6b21a8', fg: '#ffffff' },
            { bg: '#b45309', fg: '#ffffff' },
            { bg: '#be123c', fg: '#ffffff' },
            { bg: '#0e7490', fg: '#ffffff' },
            { bg: '#365314', fg: '#ffffff' }
        ];
        function hashStr(s) {
            var h = 0;
            for (var i = 0; i < s.length; i++) { h = ((h << 5) - h) + s.charCodeAt(i); h |= 0; }
            return Math.abs(h);
        }
        function buildIniciales(persona) {
            if (!persona) return '—';
            var docPrefix = String(persona.tipo_documento || '').toUpperCase();
            var esJuridico = docPrefix === 'J-' || docPrefix === 'G-';
            if (esJuridico && persona.razon_social) {
                var rs = persona.razon_social.trim();
                var parts = rs.split(/\s+/).filter(Boolean);
                return parts.length >= 2 ? (parts[0][0] + parts[1][0]).toUpperCase() : rs.substring(0, 2).toUpperCase();
            }
            var n = (persona.nombre || '').trim();
            var a = (persona.apellido || '').trim();
            if (n && a) return (n[0] + a[0]).toUpperCase();
            if (n) return n.substring(0, 2).toUpperCase();
            return '—';
        }
        function pickAvatarColor(key) {
            return AVATAR_COLORS[hashStr(String(key || 'default')) % AVATAR_COLORS.length];
        }

        // === Roles → badges ==============================================
        var ROLE_LABELS = {
            cliente:            { label: 'Cliente',   cls: 'cot-role-pill cot-role-cliente' },
            empleado:           { label: 'Empleado',  cls: 'cot-role-pill cot-role-empleado' },
            proveedor_natural:  { label: 'Proveedor', cls: 'cot-role-pill cot-role-proveedor' },
            proveedor_juridico: { label: 'Proveedor', cls: 'cot-role-pill cot-role-proveedor' }
        };
        function buildRolesBadges(roles) {
            if (!Array.isArray(roles) || !roles.length) return '';
            var seen = {};
            return roles.map(function (r) {
                var meta = ROLE_LABELS[r];
                if (!meta || seen[meta.label]) return '';
                seen[meta.label] = true;
                return '<span class="' + meta.cls + '">' + meta.label + '</span>';
            }).filter(Boolean).join('');
        }

        // Badges para la lista de resultados del autocomplete
        var AC_ROLE_BADGES = {
            cliente:            { label: 'Cliente',   cls: 'bg-success-subtle text-success' },
            empleado:           { label: 'Empleado',  cls: 'bg-info-subtle text-info' },
            proveedor_natural:  { label: 'Proveedor', cls: 'bg-warning-subtle text-warning' },
            proveedor_juridico: { label: 'Proveedor', cls: 'bg-warning-subtle text-warning' }
        };
        function renderAcRoleBadges(roles) {
            var seen = new Set();
            return (roles || []).map(function (r) {
                var meta = AC_ROLE_BADGES[r];
                if (!meta || seen.has(meta.label)) return '';
                seen.add(meta.label);
                return '<span class="badge ' + meta.cls + ' ms-1">' + meta.label + '</span>';
            }).join('');
        }
        function rolesLabelLegible(roles) {
            var labels = [], seen = new Set();
            (roles || []).forEach(function (r) {
                var meta = AC_ROLE_BADGES[r];
                if (meta && !seen.has(meta.label)) { seen.add(meta.label); labels.push(meta.label); }
            });
            if (!labels.length) return '';
            if (labels.length === 1) return labels[0];
            return labels.slice(0, -1).join(', ') + ' y ' + labels[labels.length - 1];
        }

        // === Tarjeta del cliente seleccionado ============================
        window.pedMostrarTarjetaCliente = function (persona, clienteId) {
            if (!persona) return;
            var docPrefix  = String(persona.tipo_documento || '').toUpperCase();
            var esJuridico = docPrefix === 'J-' || docPrefix === 'G-';
            var nombre     = esJuridico && persona.razon_social
                ? persona.razon_social
                : (persona.apellido ? persona.nombre + ' ' + persona.apellido : (persona.nombre || ''));

            var iniciales = buildIniciales(persona);
            var color     = pickAvatarColor(persona.documento || persona.persona_id || nombre);

            $('#ped-cliente-empty').hide();
            $('#ped-cliente-loading').attr('hidden', true);
            $('#ped-cliente-card').removeAttr('hidden').show();

            var $av = $('#ped-cliente-avatar');
            $av.text(iniciales).css({ background: color.bg, color: color.fg });

            $('#ped-cliente-name-display').text(nombre || '—');
            $('#ped-cliente-doc-display').text(persona.documento || '—');

            var tel   = persona.telefono || '';
            var email = persona.email || '';
            $('#ped-cliente-tel-wrap').toggle(!!tel);
            $('#ped-cliente-tel-display').text(tel || '—');
            $('#ped-cliente-email-wrap').toggle(!!email);
            $('#ped-cliente-email-display').text(email || '—');

            $('#ped-cliente-roles').html(buildRolesBadges(persona.roles));

            // Banner persistente (pasos 2+): misma fuente de datos que la card
            $('#ped-banner-avatar').text(iniciales).css({ background: color.bg, color: color.fg });
            $('#ped-banner-name').text(nombre || '—');
            $('#ped-banner-doc').text(persona.documento || '—');
            if (typeof window.pedActualizarBannerCliente === 'function') window.pedActualizarBannerCliente();
        };

        window.pedResetearCliente = function () {
            $('#ped-cliente-card').attr('hidden', true).hide();
            $('#ped-cliente-loading').attr('hidden', true).hide();
            $('#ped-cliente-empty').show();
            $('#ped-wiz-cliente-id-field').val('');
            pedClienteSeleccionado = false;
            // Banner persistente: ocultar al limpiar el cliente
            $('#ped-cliente-banner').attr('hidden', true).attr('aria-hidden', 'true');
        };

        // Muestra/oculta el banner según paso y cliente seleccionado.
        // Visible solo en pasos 2+ (en el paso 1 ya está la card grande).
        window.pedActualizarBannerCliente = function () {
            var hasClient = !!$('#ped-wiz-cliente-id-field').val();
            var step = (typeof currentStep !== 'undefined') ? currentStep : 1;
            var show = hasClient && step > 1;
            $('#ped-cliente-banner').attr('hidden', !show).attr('aria-hidden', show ? 'false' : 'true');
        };

        window.pedShowLoading = function (show) {
            if (show) {
                if ($('#ped-wiz-cliente-id-field').val()) return;
                $('#ped-cliente-empty').hide();
                $('#ped-cliente-loading').removeAttr('hidden').show();
            } else {
                $('#ped-cliente-loading').attr('hidden', true).hide();
                if (!$('#ped-wiz-cliente-id-field').val()) $('#ped-cliente-empty').show();
            }
        };

        // === Aplicar persona al formulario de pedido =====================
        function pedAplicarPersonaAPedido(persona, clienteId) {
            $('#ped-wiz-cliente-id-field').val(clienteId || '');

            var docString = String(persona.documento || '').trim();
            var prefix = 'V-', number = '';
            if (docString) {
                if (/^[VJEG]-/.test(docString)) {
                    prefix = docString.substring(0, 2);
                    number = docString.substring(2);
                } else {
                    number = docString;
                    prefix = (docString.length >= 8 && /^[2-9]/.test(docString)) ? 'J-' : 'V-';
                }
            }
            $('#ped-ci-rif-prefix-field').val(prefix);
            $('#ped-ci-rif-number-field').val(number);
            $('#ped-ci-rif-full-field').val(prefix + number);
            $('#ped-cliente-autocomplete-list').empty().hide();
            pedClienteSeleccionado = true;

            if (typeof window.pedMostrarTarjetaCliente === 'function') {
                window.pedMostrarTarjetaCliente(persona, clienteId);
            }
        }
        // Exponer para el handler del modal de agregar cliente
        window.pedAplicarPersonaAPedido = pedAplicarPersonaAPedido;

        // === Autocomplete por documento ==================================
        $('#ped-ci-rif-number-field').on('input', function () {
            var query = $(this).val();
            clearTimeout(pedAutocompleteTimeout);
            if (query.length < 6) {
                $('#ped-cliente-autocomplete-list').empty().hide();
                window.pedShowLoading(false);
                return;
            }
            if (!$('#ped-wiz-cliente-id-field').val()) window.pedShowLoading(true);

            pedAutocompleteTimeout = setTimeout(function () {
                $.ajax({
                    url: '/personas-search',
                    data: { q: query },
                    complete: function () { window.pedShowLoading(false); },
                    success: function (personas) {
                        var html = '';
                        if (personas.length) {
                            personas.forEach(function (p, idx) {
                                var isJuridico     = p.tipo_documento === 'J-' || p.tipo_documento === 'G-';
                                var nombreCompleto = isJuridico && p.razon_social
                                    ? p.razon_social
                                    : (p.apellido ? p.nombre + ' ' + p.apellido : p.nombre);
                                var badges = renderAcRoleBadges(p.roles);
                                html += '<button type="button" class="list-group-item list-group-item-action ped-persona-ac-item" data-idx="' + idx + '">' +
                                    '<div class="d-flex justify-content-between align-items-center flex-wrap gap-1">' +
                                    '<div><span class="fw-semibold">' + (p.documento || 'Sin documento') + '</span>' +
                                    '<span class="text-muted"> — ' + nombreCompleto + '</span>' +
                                    '<small class="text-muted d-block">' + (p.email || 'Sin email') + '</small></div>' +
                                    '<div>' + badges + '</div></div></button>';
                            });
                            $('#ped-cliente-autocomplete-list').data('personas', personas);
                        } else {
                            html = '<div class="list-group-item disabled">No se encontraron registros</div>';
                            $('#ped-cliente-autocomplete-list').removeData('personas');
                        }
                        $('#ped-cliente-autocomplete-list').html(html).show();
                    }
                });
            }, 300);
        });

        // Selección de persona de la lista
        $(document).on('click', '.ped-persona-ac-item', function () {
            var idx     = $(this).data('idx');
            var personas = $('#ped-cliente-autocomplete-list').data('personas') || [];
            var persona = personas[idx];
            if (!persona) return;

            // Caso 1: ya es cliente
            if (persona.cliente_id) {
                pedAplicarPersonaAPedido(persona, persona.cliente_id);
                return;
            }

            // Caso 2: existe en sistema pero no es cliente → confirmar
            var rolesTexto     = rolesLabelLegible(persona.roles);
            var nombreMostrar  = (persona.tipo_documento === 'J-' || persona.tipo_documento === 'G-') && persona.razon_social
                ? persona.razon_social
                : (persona.nombre + ' ' + (persona.apellido || '')).trim();

            Swal.fire({
                title: '¿Crear cliente con datos existentes?',
                html: '<strong>' + nombreMostrar + '</strong> está registrado como <strong>' + rolesTexto +
                      '</strong> pero aún no es cliente.<br><br>¿Deseas crear el cliente reutilizando estos datos?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="ri-check-line me-1"></i>Sí, crear cliente',
                cancelButtonText: 'Cancelar',
                customClass: { confirmButton: 'btn btn-success w-xs me-2', cancelButton: 'btn btn-light w-xs' },
                buttonsStyling: false
            }).then(function (r) {
                if (!r.isConfirmed) return;
                $.ajax({
                    url: '/clientes/from-persona/' + persona.persona_id,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (resp) {
                        if (resp.success && resp.cliente_id) {
                            pedAplicarPersonaAPedido(persona, resp.cliente_id);
                            Swal.fire({
                                icon: 'success',
                                title: resp.reused ? '¡Listo!' : 'Cliente creado',
                                text: resp.message,
                                showConfirmButton: false,
                                timer: 1600
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo crear el cliente.' });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error al crear el cliente.' });
                    }
                });
            });
        });

        // Ocultar lista al hacer click fuera
        $(document).on('click', function (e) {
            if (!$(e.target).closest('#ped-ci-rif-number-field, #ped-cliente-autocomplete-list').length) {
                $('#ped-cliente-autocomplete-list').empty().hide();
            }
        });

        // Botón "Cambiar cliente"
        $(document).on('click', '#ped-cliente-change-btn', function (e) {
            e.preventDefault();
            window.pedResetearCliente();
            $('#ped-ci-rif-number-field').val('').trigger('focus');
        });

        // === Atajos de fecha (entrega estimada) =========================
        $(document).on('click', '.ped-date-chip', function () {
            var days = parseInt($(this).data('days'), 10) || 0;
            var pedidoVal = $('#ped-fecha-pedido-field').val();
            var base = pedidoVal ? new Date(pedidoVal + 'T00:00:00') : new Date();
            if (isNaN(base.getTime())) base = new Date();
            base.setDate(base.getDate() + days);
            var iso = base.toISOString().split('T')[0];
            $('#ped-fecha-entrega-field').val(iso).trigger('change').trigger('blur');
            $('.ped-date-chip').removeClass('is-active');
            $(this).addClass('is-active');
        });

        // === Chips de prioridad ==========================================
        $(document).on('click', '.ped-priority-chip', function () {
            var $b = $(this), val = $b.data('value');
            $('.ped-priority-chip').removeClass('is-active').attr('aria-checked', 'false');
            $b.addClass('is-active').attr('aria-checked', 'true');
            $('#ped-prioridad-field').val(val).trigger('change');
        });
        $(document).on('change', '#ped-prioridad-field', function () {
            var val = $(this).val() || 'Normal';
            $('.ped-priority-chip').removeClass('is-active').attr('aria-checked', 'false');
            $('.ped-priority-chip[data-value="' + val + '"]').addClass('is-active').attr('aria-checked', 'true');
        });

        // === Estado del pedido (solo lectura — lo gobierna producción) ===
        // El estado ya no se edita a mano; se muestra como badge. Cancelar/Reactivar
        // viven en el listado. Aquí solo renderizamos el badge actual.
        window.pedRenderEstadoBadge = function (estado) {
            var clases = {
                Pendiente:  'status-pendiente',
                Procesando: 'status-procesando',
                Completado: 'status-completado',
                Cancelado:  'status-cancelado'
            };
            var iconos = {
                Pendiente:  'ri-time-line',
                Procesando: 'ri-loader-4-line',
                Completado: 'ri-check-double-line',
                Cancelado:  'ri-close-circle-line'
            };
            var e = estado || 'Pendiente';
            $('#ped-estado-field').val(e);
            $('#ped-estado-badge')
                .attr('class', 'badge ' + (clases[e] || ''))
                .html('<i class="' + (iconos[e] || 'ri-question-line') + ' me-1"></i>' + e);
        };

        // === Reset al abrir en modo crear ================================
        var $wizModal = $(document).find('#pedidoForm').closest('.modal');
        if (!$wizModal.length) $wizModal = $('#showModal');

        $wizModal.on('show.bs.modal', function () {
            if (!$('#ped-wiz-id-field').val()) {
                window.pedResetearCliente();
                var todayVal = new Date().toISOString().slice(0, 10);
                $('#ped-fecha-pedido-field').val(todayVal);
                $('#ped-fecha-entrega-field').val('');
                $('.ped-priority-chip').removeClass('is-active').attr('aria-checked', 'false');
                $('.ped-priority-chip[data-value="Normal"]').addClass('is-active').attr('aria-checked', 'true');
                $('#ped-prioridad-field').val('Normal');
                $('.ped-date-chip').removeClass('is-active');
            }
        });

        // === Abrir modal crear cliente ====================================
        $(document).on('click', '#ped-open-add-cliente-modal', function () {
            document.getElementById('clienteFormCotizacion').reset();
            $('#id-field-cliente').val('');
            $('#modalClienteTitle').text('Crear Cliente');
            $('#add-btn-cliente').show();
            $('#documento-prefix-field-cliente').val('V-');
            $('#telefono-prefix-field-cliente').val('0424');
            $('#estatus-field-cliente').prop('checked', true);
            $('#estatus-label-cliente').text('Activo');
            $('#ciudad-field-cliente').html('<option value="">Primero seleccione un estado</option>');
            $('#tipo_cliente-field-cliente').val('');
            pedToggleClienteFields();
            $('#modalAddCliente').modal('show');
        });

        // === Lógica Natural vs Jurídico/Gubernamental ====================
        function pedToggleClienteFields() {
            var tipo        = $('#tipo_cliente-field-cliente').val();
            var $prefix     = $('#documento-prefix-field-cliente');
            var $docInput   = $('#documento-number-field-cliente');

            if (tipo === 'juridico') {
                $('#campos-persona-natural-cliente').addClass('d-none');
                $('#nombre-field-cliente').prop('required', false).prop('disabled', true).val('');
                $('#apellido-field-cliente').prop('required', false).prop('disabled', true).val('');
                $('#campos-razon-social-cliente').removeClass('d-none');
                $('#razon-social-field-cliente').prop('required', true).prop('disabled', false);
                $prefix.html('<option value="J-">J-</option>').prop('disabled', true);
                $docInput.attr('maxlength', '9');
            } else if (tipo === 'gubernamental') {
                $('#campos-persona-natural-cliente').addClass('d-none');
                $('#nombre-field-cliente').prop('required', false).prop('disabled', true).val('');
                $('#apellido-field-cliente').prop('required', false).prop('disabled', true).val('');
                $('#campos-razon-social-cliente').removeClass('d-none');
                $('#razon-social-field-cliente').prop('required', true).prop('disabled', false);
                $prefix.html('<option value="G-">G-</option>').prop('disabled', true);
                $docInput.attr('maxlength', '9');
            } else {
                $('#campos-persona-natural-cliente').removeClass('d-none');
                $('#nombre-field-cliente').prop('required', true).prop('disabled', false);
                $('#apellido-field-cliente').prop('required', true).prop('disabled', false);
                $('#campos-razon-social-cliente').addClass('d-none');
                $('#razon-social-field-cliente').prop('required', false).prop('disabled', true).val('');
                $prefix.html('<option value="V-">V-</option><option value="E-">E-</option>').prop('disabled', false);
                $docInput.attr('maxlength', '8');
            }
        }
        $(document).on('change', '#tipo_cliente-field-cliente', pedToggleClienteFields);

        // Dropdown municipios
        $(document).on('change', '#estado_territorial-field-cliente', function () {
            var estado      = $(this).val();
            var municipios  = typeof getMunicipios === 'function' ? getMunicipios(estado) : [];
            var $ciudad     = $('#ciudad-field-cliente');
            $ciudad.empty();
            if (!estado) {
                $ciudad.append('<option value="">Primero seleccione un estado</option>');
            } else {
                $ciudad.append('<option value="">Seleccione municipio</option>');
                municipios.forEach(function (m) {
                    $ciudad.append('<option value="' + m + '">' + m + '</option>');
                });
            }
        });

        // Limpiar validaciones al abrir el modal de cliente
        $('#modalAddCliente').on('show.bs.modal', function () {
            $(this).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            $(this).find('.invalid-feedback').hide();
        });

        // Validaciones en tiempo real del formulario de cliente
        $(document).on('input', '#nombre-field-cliente', function () {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        });
        $(document).on('input', '#apellido-field-cliente', function () {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        });
        $(document).on('input', '#documento-number-field-cliente', function () {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        });
        $(document).on('input', '#telefono-number-field-cliente', function () {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 7);
        });
        $(document).on('change', '#estatus-field-cliente', function () {
            $('#estatus-label-cliente').text($(this).is(':checked') ? 'Activo' : 'Inactivo');
        });

        // === Guardar cliente nuevo desde el wizard de pedidos ============
        $(document).on('click', '#add-btn-cliente', function (e) {
            e.preventDefault();

            // Concatenar campos compuestos antes de validar
            var documentoCompleto = $('#documento-prefix-field-cliente').val() + $('#documento-number-field-cliente').val();
            var telefonoCompleto  = $('#telefono-prefix-field-cliente').val() + '-' + $('#telefono-number-field-cliente').val();
            $('#documento-field-cliente').val(documentoCompleto);
            $('#telefono-field-cliente').val(telefonoCompleto);

            // Validar apellido explícitamente
            var tipo     = $('#tipo_cliente-field-cliente').val();
            var esNatural = tipo === 'natural' || tipo === '';
            if (esNatural) {
                var apellido = $('#apellido-field-cliente').val().trim();
                if (apellido.length < 2) {
                    $('#apellido-field-cliente').addClass('is-invalid');
                    Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El apellido es obligatorio (mínimo 2 caracteres).' });
                    return;
                }
                $('#apellido-field-cliente').removeClass('is-invalid');
            }

            // Validar dirección
            var direccion = $('#direccion-field-cliente').val().trim();
            if (direccion.length < 5) {
                $('#direccion-field-cliente').addClass('is-invalid');
                Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'La dirección es obligatoria (mínimo 5 caracteres).' });
                return;
            }
            $('#direccion-field-cliente').removeClass('is-invalid');

            // Validar usando checkValidity nativo
            var form = document.getElementById('clienteFormCotizacion');
            if (!form.checkValidity()) { form.reportValidity(); return; }

            $(this).prop('disabled', true);

            var formData = $('#clienteFormCotizacion').serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: '/clientes',
                type: 'POST',
                data: formData,
                success: function (response) {
                    Swal.fire({
                        icon: 'success', title: '¡Éxito!',
                        text: response.message || 'Cliente creado exitosamente.',
                        showConfirmButton: false, timer: 1800
                    });
                    $('#modalAddCliente').modal('hide');
                    $('#add-btn-cliente').prop('disabled', false);

                    // Construir objeto persona a partir de los campos del formulario
                    var esJuridico = tipo === 'juridico' || tipo === 'gubernamental';
                    var personaCreada = {
                        documento:       documentoCompleto,
                        tipo_documento:  $('#documento-prefix-field-cliente').val(),
                        nombre:          esJuridico ? '' : $('#nombre-field-cliente').val(),
                        apellido:        esJuridico ? '' : $('#apellido-field-cliente').val(),
                        razon_social:    esJuridico ? ($('#razon-social-field-cliente').val()) : '',
                        email:           $('#email-field-cliente').val().trim(),
                        telefono:        telefonoCompleto,
                        roles:           ['cliente'],
                        persona_id:      response.persona_id || null
                    };
                    var clienteId = response.cliente_id || response.id || null;
                    pedAplicarPersonaAPedido(personaCreada, clienteId);
                },
                error: function (xhr) {
                    $('#add-btn-cliente').prop('disabled', false);
                    var msg = '';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errs = xhr.responseJSON.errors;
                        msg = Object.values(errs).map(function (v) { return v[0]; }).join('\n');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    } else {
                        msg = 'Ocurrió un error al crear el cliente.';
                    }
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
            });
        });

    })();


    // ╔══════════════════════════════════════════════════════════════════
    // ║ PEDIDO WIZARD — PASO 2: Productos
    // ║ Estado en memoria, CRUD de productos, importar desde cotización
    // ╚══════════════════════════════════════════════════════════════════
    (function () {
        'use strict';

        var pedProdItems = [];
        var pedProdSearchTimeout = null;
        var pedProdCatalogo = window.products || [];

        var pedTallasCatalogo = @json($tallas->mapWithKeys(function ($t) {
            return [$t->id => ($t->etiqueta ?: $t->nombre)];
        }));
        var pedColoresCatalogo = @json($colores->mapWithKeys(function ($c) {
            return [$c->id => $c->nombre];
        }));
        var pedColoresHex = @json($colores->mapWithKeys(function ($c) {
            return [$c->id => $c->hex_referencial];
        }));

        function pedFmt(n) {
            return '$' + parseFloat(n || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        function pedRecalcularSubtotal() {
            var cant   = parseFloat($('#ped-prod-cantidad-field').val()) || 0;
            var precio = parseFloat($('#ped-prod-precio-field').val()) || 0;
            $('#ped-prod-subtotal-display').text(pedFmt(cant * precio));
        }

        function pedRecalcularTotal() {
            var total = pedProdItems.reduce(function (acc, it) { return acc + (it.subtotal || 0); }, 0);
            $('#ped-kpi-lineas').text(pedProdItems.length);
            $('#ped-kpi-total').text(pedFmt(total));
            window.pedProdState = { items: pedProdItems, total: total };
        }

        function pedEscHtml(s) {
            return String(s == null ? '' : s)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // Buscar código + tipo del producto en el catálogo (window.products)
        function pedProdInfo(productoId) {
            var p = pedProdCatalogo.find(function (x) { return String(x.id) === String(productoId); });
            if (!p) return { codigo: '', tipo: '', nombre: '' };
            var tipo = (p.tipo_producto && p.tipo_producto.nombre) ? p.tipo_producto.nombre
                     : (p.tipoProducto && p.tipoProducto.nombre) ? p.tipoProducto.nombre : '';
            return { codigo: p.codigo || '', tipo: tipo, nombre: p.nombre || '' };
        }

        // Grilla de productos agrupada por producto+color (espejo del wizard de cotización, solo lectura)
        function pedRenderProductos() {
            var $list  = $('#ped-productos-list');
            var $empty = $('#ped-productos-empty');

            pedRecalcularTotal();

            if (!pedProdItems.length) {
                $list.empty();
                $empty.show();
                return;
            }
            $empty.hide();

            // Agrupar por producto_id + color_id
            var groups = [];
            var byKey  = {};
            pedProdItems.forEach(function (it) {
                var key = (it.producto_id || '0') + '::' + (it.color_id || '0');
                if (!byKey[key]) {
                    byKey[key] = {
                        producto_id: it.producto_id, nombre: it.nombre,
                        color_id: it.color_id, color_label: it.color_label || '',
                        precio_unitario: it.precio_unitario,
                        totalQty: 0, totalSubtotal: 0, tallas: [],
                        llevaBordado: false, bordadosCount: 0
                    };
                    groups.push(byKey[key]);
                }
                var g = byKey[key];
                g.totalQty      += (it.cantidad || 0);
                g.totalSubtotal += (it.subtotal || 0);
                g.tallas.push({ label: it.talla_label || 'Única', qty: it.cantidad || 0 });
                if (it.bordados && it.bordados.length) {
                    g.llevaBordado = true;
                    // Bordados por unidad (las tallas del mismo producto+color comparten config)
                    if (!g.bordadosUnit) {
                        g.bordadosUnit = it.bordados;
                        g.bordadosCount = it.bordados.length;
                    }
                }
            });

            var rows = groups.map(function (g, idx) {
                var info = pedProdInfo(g.producto_id);
                // Si hay nombre propio: pill de familia + nombre como título. Si no: la familia es el título (sin pill redundante).
                var hasNombre = !!(info.nombre && String(info.nombre).trim());
                var titulo = hasNombre ? info.nombre : (info.tipo || g.nombre || '(producto sin definir)');
                var tipoPill = (hasNombre && info.tipo) ? '<span class="cot-tipo-pill">' + pedEscHtml(info.tipo) + '</span>' : '';
                var nombreLine = '<div class="cot-prod-modelo">' + pedEscHtml(titulo) + '</div>';
                var codigoLine = info.codigo ? '<div class="cot-prod-codigo"><i class="ri-barcode-line"></i>' + pedEscHtml(info.codigo) + '</div>' : '';
                var hex = g.color_id ? (pedColoresHex[g.color_id] || '') : '';
                var colorDot = '<span class="cot-color-dot" style="background:' + (hex || '#e2e8f0') + ';border-color:#cbd5e1;"></span>';
                var tallasChips = g.tallas.map(function (t) {
                    return '<span class="cot-chip cot-chip-talla">' + pedEscHtml(t.label) +
                           '<span class="cot-chip-x">×</span><strong>' + t.qty + '</strong></span>';
                }).join('');
                var bordadoLine;
                if (g.bordadosCount > 0) {
                    var recargoUnit = (typeof pedRecargoBordado === 'function') ? pedRecargoBordado(g.bordadosUnit || []) : 0;
                    bordadoLine = '<div class="cot-grouped-bordado-line cot-grouped-bordado-line--ok">' +
                        '<i class="ri-scissors-cut-line"></i>' +
                        g.bordadosCount + ' bordado' + (g.bordadosCount === 1 ? '' : 's') +
                        (recargoUnit > 0 ? ' · +' + pedFmt(recargoUnit) + '/u' : '') + '</div>';
                } else {
                    bordadoLine = '<div class="cot-grouped-bordado-line cot-grouped-bordado-line--none">' +
                        '<i class="ri-subtract-line"></i>Sin bordado</div>';
                }
                return '<tr class="cot-grouped-row">' +
                    '<td class="cot-col-num">' + (idx + 1) + '</td>' +
                    '<td class="cot-col-prod">' + tipoPill + nombreLine + codigoLine + '</td>' +
                    '<td class="cot-col-color"><span class="cot-color-cell">' + colorDot + pedEscHtml(g.color_label || 'Sin color') + '</span></td>' +
                    '<td class="cot-col-tallas"><div class="cot-tallas-wrap">' + tallasChips + '</div>' + bordadoLine + '</td>' +
                    '<td class="cot-col-num cot-cell-num"><strong>' + g.totalQty + '</strong></td>' +
                    '<td class="cot-cell-num">' + pedFmt(g.precio_unitario) + '</td>' +
                    '<td class="cot-cell-num cot-cell-subtotal">' + pedFmt(g.totalSubtotal) + '</td>' +
                    '</tr>';
            }).join('');

            var total = pedProdItems.reduce(function (a, it) { return a + (it.subtotal || 0); }, 0);
            $list.html(
                '<div class="cot-grouped-tablewrap">' +
                    '<table class="cot-grouped-table">' +
                        '<thead><tr>' +
                            '<th class="cot-col-num">#</th>' +
                            '<th class="cot-col-prod">Producto</th>' +
                            '<th class="cot-col-color">Color</th>' +
                            '<th class="cot-col-tallas">Tallas y cantidades</th>' +
                            '<th class="cot-col-num">Unid.</th>' +
                            '<th class="cot-cell-num">Precio U.</th>' +
                            '<th class="cot-cell-num">Subtotal</th>' +
                        '</tr></thead>' +
                        '<tbody>' + rows + '</tbody>' +
                        '<tfoot><tr><td colspan="6" class="text-end fw-bold">Total</td>' +
                            '<td class="cot-cell-num cot-cell-subtotal fw-bold">' + pedFmt(total) + '</td></tr></tfoot>' +
                    '</table>' +
                '</div>'
            );
        }

        function pedAbrirModalProducto(idx) {
            var item = (idx !== null && idx !== undefined && pedProdItems[idx]) ? pedProdItems[idx] : null;
            $('#ped-prod-edit-idx').val(idx !== null && idx !== undefined ? idx : '');
            if (item) {
                $('#ped-agregar-prod-title').text('Editar Producto');
                $('#ped-prod-search-input').val(item.nombre);
                $('#ped-prod-id-field').val(item.producto_id || '');
                $('#ped-prod-nombre-field').val(item.nombre);
                $('#ped-prod-cantidad-field').val(item.cantidad);
                $('#ped-prod-talla-field').val(item.talla_id || '');
                $('#ped-prod-color-field').val(item.color_id || '');
                $('#ped-prod-precio-field').val(item.precio_unitario);
                pedRecalcularSubtotal();
            } else {
                $('#ped-agregar-prod-title').text('Agregar Producto');
                $('#ped-prod-search-input').val('');
                $('#ped-prod-id-field').val('');
                $('#ped-prod-nombre-field').val('');
                $('#ped-prod-cantidad-field').val(1);
                $('#ped-prod-talla-field').val('');
                $('#ped-prod-color-field').val('');
                $('#ped-prod-precio-field').val('');
                $('#ped-prod-subtotal-display').text('$0.00');
            }
            $('#ped-prod-search-list').empty().hide();
            $('#ped-agregar-producto-modal').modal('show');
        }

        // Autocomplete de productos del catálogo (window.products)
        $('#ped-prod-search-input').on('input', function () {
            var q = $(this).val().toLowerCase().trim();
            $('#ped-prod-id-field').val('');
            $('#ped-prod-nombre-field').val('');
            clearTimeout(pedProdSearchTimeout);
            if (!q) { $('#ped-prod-search-list').empty().hide(); return; }
            pedProdSearchTimeout = setTimeout(function () {
                var matches = pedProdCatalogo.filter(function (p) {
                    return (p.nombre || '').toLowerCase().indexOf(q) !== -1 ||
                           (p.codigo || '').toLowerCase().indexOf(q) !== -1;
                }).slice(0, 10);
                var html = '';
                if (matches.length) {
                    matches.forEach(function (p) {
                        html += '<button type="button" class="list-group-item list-group-item-action ped-prod-ac-item"' +
                            ' data-id="' + p.id + '"' +
                            ' data-nombre="' + String(p.nombre || '').replace(/"/g, '&quot;') + '"' +
                            ' data-precio="' + (p.precio_base || 0) + '">' +
                            '<span class="fw-semibold">' + (p.nombre || '') + '</span>' +
                            (p.codigo ? '<small class="text-muted ms-2">' + p.codigo + '</small>' : '') +
                            '</button>';
                    });
                } else {
                    html = '<div class="list-group-item disabled text-muted small">Sin resultados</div>';
                }
                $('#ped-prod-search-list').html(html).show();
            }, 200);
        });

        $(document).on('click', '.ped-prod-ac-item', function () {
            var nombre = $(this).data('nombre');
            var precio = $(this).data('precio');
            $('#ped-prod-id-field').val($(this).data('id'));
            $('#ped-prod-nombre-field').val(nombre);
            $('#ped-prod-search-input').val(nombre);
            if (!$('#ped-prod-precio-field').val()) {
                $('#ped-prod-precio-field').val(parseFloat(precio).toFixed(2));
            }
            $('#ped-prod-search-list').empty().hide();
            pedRecalcularSubtotal();
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('#ped-prod-search-input, #ped-prod-search-list').length) {
                $('#ped-prod-search-list').empty().hide();
            }
        });

        // Recalcular subtotal al cambiar cantidad o precio
        $(document).on('input', '#ped-prod-cantidad-field, #ped-prod-precio-field', pedRecalcularSubtotal);

        // Guardar producto (agregar o editar)
        $(document).on('click', '#ped-prod-guardar-btn', function () {
            var nombre   = $('#ped-prod-nombre-field').val() || $('#ped-prod-search-input').val().trim();
            var cantidad = parseInt($('#ped-prod-cantidad-field').val(), 10);
            var precio   = parseFloat($('#ped-prod-precio-field').val());
            var tallaId  = $('#ped-prod-talla-field').val() || null;
            var colorId  = $('#ped-prod-color-field').val() || null;

            if (!nombre) {
                Swal.fire({ icon: 'warning', title: 'Producto requerido',
                    text: 'Selecciona un producto del catálogo.', timer: 2000, showConfirmButton: false });
                $('#ped-prod-search-input').trigger('focus');
                return;
            }
            if (!cantidad || cantidad < 1) {
                Swal.fire({ icon: 'warning', title: 'Cantidad inválida',
                    text: 'La cantidad debe ser al menos 1.', timer: 2000, showConfirmButton: false });
                return;
            }
            if (isNaN(precio) || precio < 0) {
                Swal.fire({ icon: 'warning', title: 'Precio inválido',
                    text: 'Ingresa un precio unitario válido.', timer: 2000, showConfirmButton: false });
                return;
            }

            var item = {
                producto_id:  $('#ped-prod-id-field').val() || null,
                nombre:       nombre,
                cantidad:     cantidad,
                talla_id:     tallaId ? parseInt(tallaId, 10) : null,
                talla_label:  tallaId ? (pedTallasCatalogo[tallaId] || '') : '',
                color_id:     colorId ? parseInt(colorId, 10) : null,
                color_label:  colorId ? (pedColoresCatalogo[colorId] || '') : '',
                precio_unitario: precio,
                subtotal:     cantidad * precio,
                heredado_cotizacion_id: null
            };

            var idxStr = $('#ped-prod-edit-idx').val();
            var idx    = (idxStr !== '') ? parseInt(idxStr, 10) : null;
            if (idx !== null && pedProdItems[idx]) {
                item.heredado_cotizacion_id = pedProdItems[idx].heredado_cotizacion_id;
                pedProdItems[idx] = item;
            } else {
                pedProdItems.push(item);
            }

            pedRenderProductos();
            $('#ped-agregar-producto-modal').modal('hide');
        });

        // Editar / eliminar producto desde la lista
        $(document).on('click', '.ped-prod-edit-btn', function () {
            pedAbrirModalProducto(parseInt($(this).data('idx'), 10));
        });
        $(document).on('click', '.ped-prod-del-btn', function () {
            var idx = parseInt($(this).data('idx'), 10);
            pedProdItems.splice(idx, 1);
            pedRenderProductos();
        });

        // Abrir modal agregar
        $(document).on('click', '#ped-btn-agregar-prod, #ped-btn-agregar-prod-empty', function () {
            pedAbrirModalProducto(null);
        });

        // Importar: activar flag y abrir modal de selección de cotización
        $(document).on('click', '#ped-btn-importar-cotizacion', function () {
            window.pedWizardImportMode = true;
            $('#seleccionarCotizacionModal').modal('show');
        });

        // Recargo de bordado por unidad: Σ(precio_aplicado × cantidad)
        // Coincide con BordadoPricingService::calcularRecargoBordadoUnitario del backend.
        function pedRecargoBordado(bordados) {
            return (bordados || []).reduce(function (acc, b) {
                var precio = parseFloat(b.precio_aplicado) || 0;
                var cant   = Math.max(1, parseInt(b.cantidad || 1, 10));
                return acc + precio * cant;
            }, 0);
        }

        // Hidratar productos desde cotización seleccionada
        window.pedHidratarDesde = function (cotizacionId) {
            window.pedWizardImportMode = false;
            $.ajax({
                url: '{{ route("cotizaciones.datosParaPedido", ":id") }}'.replace(':id', cotizacionId),
                method: 'GET',
                success: function (data) {
                    if (!data || !data.productos || !data.productos.length) {
                        Swal.fire({ icon: 'info', title: 'Sin productos',
                            text: 'La cotización no tiene productos para importar.',
                            timer: 2400, showConfirmButton: false });
                        return;
                    }
                    data.productos.forEach(function (p) {
                        var cant    = parseInt(p.cantidad, 10);
                        var precio  = parseFloat(p.precio_unitario);
                        pedProdItems.push({
                            producto_id:  p.producto_id,
                            nombre:       p.producto_nombre,
                            cantidad:     cant,
                            talla_id:     p.talla_id || null,
                            talla_label:  p.talla_id ? (pedTallasCatalogo[p.talla_id] || '') : '',
                            color_id:     p.color_id || null,
                            color_label:  p.color_id ? (pedColoresCatalogo[p.color_id] || '') : '',
                            precio_unitario: precio,
                            subtotal:     cant * precio,
                            heredado_cotizacion_id: cotizacionId
                        });
                    });
                    pedRenderProductos();
                    Swal.fire({
                        icon: 'success', title: 'Productos importados',
                        text: data.productos.length + ' producto(s) importados desde cotización #' + cotizacionId + '.',
                        timer: 2200, showConfirmButton: false
                    });
                },
                error: function (xhr) {
                    window.pedWizardImportMode = false;
                    var msg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message))
                        || 'No se pudo obtener los datos de la cotización.';
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
            });
        };

        // Hidratar array de productos directamente (llamado desde convert y edit)
        window.pedHidratarProductosDesde = function (productos, cotizacionId) {
            pedProdItems = [];
            productos.forEach(function (p) {
                var cant    = parseInt(p.cantidad, 10);
                var precio  = parseFloat(p.precio_unitario);                 // precio FINAL (incluye bordado)
                var bordados = Array.isArray(p.bordados) ? p.bordados : [];
                var recargo = pedRecargoBordado(bordados);                   // recargo bordado por unidad
                pedProdItems.push({
                    producto_id:  p.producto_id,
                    nombre:       p.producto_nombre,
                    cantidad:     cant,
                    talla_id:     p.talla_id || null,
                    talla_label:  p.talla_id ? (pedTallasCatalogo[p.talla_id] || '') : '',
                    color_id:     p.color_id || null,
                    color_label:  p.color_id ? (pedColoresCatalogo[p.color_id] || '') : '',
                    precio_unitario: precio,                                 // final → display/subtotal
                    precio_base:  +(precio - recargo).toFixed(2),            // base → payload (el backend re-suma el bordado)
                    lleva_bordado: bordados.length > 0,
                    bordados:     bordados,
                    subtotal:     cant * precio,
                    heredado_cotizacion_id: cotizacionId
                });
            });
            pedRenderProductos();
        };

        // Reset al abrir el wizard en modo crear
        var $wizModal = $(document).find('#pedidoForm').closest('.modal');
        if (!$wizModal.length) $wizModal = $('#showModal');
        $wizModal.on('show.bs.modal', function () {
            if (!$('#ped-wiz-id-field').val()) {
                pedProdItems = [];
                pedRenderProductos();
            }
        });

        // Estado global expuesto para paso 3 y validateStep
        window.pedProdState = { items: pedProdItems, total: 0 };

    })();


    // ╔══════════════════════════════════════════════════════════════════
    // ║ PEDIDO WIZARD — PASO 3: Pago (multi-método)
    // ║ Total readonly, abono = suma de métodos activos, restante.
    // ║ Cada método tiene su monto; transferencia/pago móvil piden banco+ref.
    // ╚══════════════════════════════════════════════════════════════════
    (function () {
        'use strict';

        // Metadatos por método: label, ícono y si pide banco+referencia
        var PED_METODOS = {
            efectivo:      { label: 'Efectivo',      icon: 'ri-money-dollar-circle-line', banco: false },
            transferencia: { label: 'Transferencia', icon: 'ri-bank-line',                banco: true  },
            pago_movil:    { label: 'Pago móvil',     icon: 'ri-smartphone-line',          banco: true  }
        };

        function pedFmtPago(n) { return parseFloat(n || 0).toFixed(2); }
        function pedMoney(n) {
            return '$' + parseFloat(n || 0).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        function pedPagoTotal() { return (window.pedProdState && window.pedProdState.total) || 0; }
        function pedHayEfectivo() { return $('#ped-pay-list .ped-pay-row[data-metodo="efectivo"]').length > 0; }

        // Lee las filas del DOM → [{metodo, monto, banco_id, referencia, banco_nombre}]
        function pedLeerPagos() {
            var pagos = [];
            $('#ped-pay-list .ped-pay-row').each(function () {
                var $row = $(this);
                var m = $row.attr('data-metodo');
                var meta = PED_METODOS[m];
                if (!meta) return;
                var pago = {
                    metodo:       m,
                    monto:        parseFloat($row.find('.ped-pay-monto').val()) || 0,
                    banco_id:     null,
                    referencia:   null,
                    banco_nombre: null
                };
                if (meta.banco) {
                    var $banco = $row.find('.ped-pay-banco');
                    pago.banco_id     = $banco.val() || null;
                    pago.banco_nombre = ($banco.find('option:selected').text() || '').trim() || null;
                    pago.referencia   = ($row.find('.ped-pay-ref').val() || '').trim() || null;
                }
                pagos.push(pago);
            });
            return pagos;
        }

        function pedAbonoTotal() {
            return pedLeerPagos().reduce(function (a, p) { return a + (p.monto || 0); }, 0);
        }

        // Recalcular resumen, barra de progreso, estado, empty state y botón efectivo
        function pedRecalcularPago() {
            var total = pedPagoTotal();
            var abono = pedAbonoTotal();
            var rest  = total - abono;

            $('#ped-pay-total').text(pedMoney(total));
            $('#ped-pay-abono').text(pedMoney(abono));
            $('#ped-pay-restante').text(pedMoney(rest < 0 ? 0 : rest));

            // Compat con validación/submit legacy
            $('#ped-pago-total-display').val(pedFmtPago(total));
            $('#ped-pago-abono-field').val(pedFmtPago(abono));

            var pct = total > 0 ? Math.min(100, (abono / total) * 100) : (abono > 0 ? 100 : 0);
            $('#ped-pay-progress-bar').css('width', pct.toFixed(1) + '%');

            var $sum = $('#ped-pay-summary').removeClass('is-empty is-partial is-complete is-over');
            var $badge = $('#ped-pay-badge');
            if (abono <= 0) {
                $sum.addClass('is-empty');           $badge.text('Sin abono');
            } else if (abono > total + 0.001) {
                $sum.addClass('is-over');             $badge.text('Excede el total por ' + pedMoney(abono - total));
            } else if (rest <= 0.001) {
                $sum.addClass('is-complete');         $badge.text('Pagado completo');
            } else {
                $sum.addClass('is-partial');          $badge.text('Resta ' + pedMoney(rest));
            }

            $('#ped-pay-empty').toggle($('#ped-pay-list .ped-pay-row').length === 0);
            $('#ped-pay-add-efectivo').prop('disabled', pedHayEfectivo());
        }

        // Crear una fila de pago (opcionalmente con datos para hidratar)
        function pedAgregarFila(metodo, data) {
            var meta = PED_METODOS[metodo];
            if (!meta) return;
            if (metodo === 'efectivo' && pedHayEfectivo()) return; // solo un efectivo

            var tpl = document.getElementById('ped-pay-row-tpl');
            var $row = $(tpl.content.firstElementChild.cloneNode(true));
            $row.attr('data-metodo', metodo);
            $row.find('.ped-pay-row-icon i').attr('class', meta.icon);
            $row.find('.ped-pay-row-method').text(meta.label);
            if (!meta.banco) $row.find('.ped-pay-row-fields').remove();

            if (data) {
                if (data.monto != null) $row.find('.ped-pay-monto').val(parseFloat(data.monto).toFixed(2));
                if (meta.banco) {
                    if (data.banco_id)   $row.find('.ped-pay-banco').val(String(data.banco_id));
                    if (data.referencia) $row.find('.ped-pay-ref').val(data.referencia);
                }
            }

            $('#ped-pay-list').append($row);
            pedRecalcularPago();
            if (!data) $row.find('.ped-pay-monto').trigger('focus');
            return $row;
        }

        function pedResetPaso3() {
            $('#ped-pay-list').empty();
            pedRecalcularPago();
        }

        // Sincronizar total desde paso 2 al navegar al paso 3
        window.pedSincronizarTotalPago = function () { pedRecalcularPago(); };

        // === Eventos ===
        $(document).on('click', '.ped-pay-add-btn', function () {
            pedAgregarFila($(this).data('metodo'));
        });
        $(document).on('click', '.ped-pay-row-del', function () {
            $(this).closest('.ped-pay-row').remove();
            pedRecalcularPago();
        });
        $(document).on('input', '#ped-pay-list .ped-pay-monto', pedRecalcularPago);
        // "Saldar restante": rellena esta fila con lo que falta (sin contarse a sí misma)
        $(document).on('click', '.ped-pay-fill', function () {
            var $row = $(this).closest('.ped-pay-row');
            var propio = parseFloat($row.find('.ped-pay-monto').val()) || 0;
            var otros = pedAbonoTotal() - propio;
            var rest = pedPagoTotal() - otros;
            if (rest < 0) rest = 0;
            $row.find('.ped-pay-monto').val(rest.toFixed(2));
            pedRecalcularPago();
        });

        // Reset al abrir el wizard en modo crear
        var $wizModal = $('#showModal');
        $wizModal.on('show.bs.modal', function () {
            if (!$('#ped-wiz-id-field').val()) pedResetPaso3();
        });

        // Hidratar pagos existentes (edit) — soporta múltiples por método
        window.pedHidratarPagos = function (pagos) {
            $('#ped-pay-list').empty();
            (pagos || []).forEach(function (p) {
                if (!PED_METODOS[p.metodo]) return;
                pedAgregarFila(p.metodo, { monto: p.monto, banco_id: p.banco_id, referencia: p.referencia });
            });
            pedRecalcularPago();
        };

        // Exponer estado para validación, resumen y submit (mismo shape que antes)
        window.pedPagoState = {
            get pagos() { return pedLeerPagos(); },
            get abono() { return pedAbonoTotal(); }
        };

    })();


    // ╔══════════════════════════════════════════════════════════════════
    // ║ PEDIDO WIZARD — PASO 4: Resumen + submit final (crear)
    // ╚══════════════════════════════════════════════════════════════════
    (function () {
        'use strict';

        var METODO_LABELS = {
            efectivo:     'Efectivo',
            transferencia: 'Transferencia',
            pago_movil:   'Pago Móvil'
        };

        function pedFmtRes(n) {
            return '$' + parseFloat(n || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        // Renderizar bloque cliente
        function pedRenderResCliente() {
            var nombre = $('#ped-cliente-name-display').text().trim() || '—';
            var doc    = $('#ped-cliente-doc-display').text().trim() || '—';
            var tel    = $('#ped-cliente-tel-wrap').is(':visible') ? $('#ped-cliente-tel-display').text().trim() : '';
            var email  = $('#ped-cliente-email-wrap').is(':visible') ? $('#ped-cliente-email-display').text().trim() : '';
            function icoItem(ico, bg, col, label, val) {
                return '<div class="col-6 d-flex align-items-start mb-2">' +
                    '<div class="emp-icon-box emp-icon-box--' + bg + ' rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">' +
                    '<i class="' + ico + ' emp-icon--' + col + '"></i></div>' +
                    '<div class="min-w-0"><small class="text-muted d-block fs-12">' + label + '</small>' +
                    '<span class="fw-semibold fs-13 text-break">' + val + '</span></div></div>';
            }
            var html = '<div class="row g-0">' +
                icoItem('ri-user-line',      'navy',  'navy',  'Nombre',    nombre) +
                icoItem('ri-bank-card-line', 'green', 'green', 'Documento', doc) +
                (tel   ? icoItem('ri-phone-line', 'teal', 'teal', 'Teléfono', tel)   : '') +
                (email ? icoItem('ri-mail-line',  'navy', 'navy', 'Email',    email) : '') +
                '</div>';
            $('#ped-res-cliente-bloque').html(html);
        }

        // Renderizar bloque datos del pedido
        function pedRenderResDatos() {
            var fecha     = $('#ped-fecha-pedido-field').val() || '—';
            var entrega   = $('#ped-fecha-entrega-field').val() || 'No especificada';
            var prioridad = $('#ped-prioridad-field').val() || 'Normal';
            var prioBg    = { Normal: 'bg-success', Alta: 'bg-warning', Urgente: 'bg-danger' };
            var prioBadge = '<span class="badge ' + (prioBg[prioridad] || 'bg-secondary') + '">' + prioridad + '</span>';
            function icoItem(ico, bg, col, label, val) {
                return '<div class="col-6 d-flex align-items-start mb-2">' +
                    '<div class="emp-icon-box emp-icon-box--' + bg + ' rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">' +
                    '<i class="' + ico + ' emp-icon--' + col + '"></i></div>' +
                    '<div><small class="text-muted d-block fs-12">' + label + '</small>' +
                    '<span class="fw-semibold fs-13">' + val + '</span></div></div>';
            }
            var html = '<div class="row g-0">' +
                icoItem('ri-calendar-line',       'navy', 'navy', 'Fecha pedido',     fecha) +
                icoItem('ri-calendar-check-line', 'navy', 'navy', 'Entrega estimada', entrega) +
                '<div class="col-6 d-flex align-items-start mb-2">' +
                '<div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">' +
                '<i class="ri-flag-line emp-icon--navy"></i></div>' +
                '<div><small class="text-muted d-block fs-12">Prioridad</small>' + prioBadge + '</div></div>' +
                '</div>';
            $('#ped-res-datos-bloque').html(html);
        }

        // Renderizar tabla de productos
        function pedRenderResProductos() {
            var items = (window.pedProdState && window.pedProdState.items) || [];
            var total = (window.pedProdState && window.pedProdState.total) || 0;
            $('#ped-res-lineas').text(items.length);
            $('#ped-res-total').text(pedFmtRes(total));
            if (!items.length) {
                $('#ped-res-productos-tbody').html(
                    '<tr><td colspan="6" class="text-center text-muted py-3 small">Sin productos</td></tr>'
                );
                return;
            }
            var rows = items.map(function (it) {
                var badge = it.heredado_cotizacion_id
                    ? ' <span class="ped-inherited-badge">Heredado #' + it.heredado_cotizacion_id + '</span>'
                    : '';
                return '<tr>' +
                    '<td class="small">' + it.nombre + badge + '</td>' +
                    '<td class="text-center small">' + it.cantidad + '</td>' +
                    '<td class="small">' + (it.talla_label || '—') + '</td>' +
                    '<td class="small">' + (it.color_label || '—') + '</td>' +
                    '<td class="text-end small">' + pedFmtRes(it.precio_unitario) + '</td>' +
                    '<td class="text-end small fw-semibold">' + pedFmtRes(it.subtotal) + '</td>' +
                    '</tr>';
            }).join('');
            $('#ped-res-productos-tbody').html(rows);
        }

        // Renderizar bloque pago (multi-método)
        function pedRenderResPago() {
            var METODO_ICONS = {
                efectivo:      'ri-money-dollar-circle-line',
                transferencia: 'ri-bank-card-2-line',
                pago_movil:    'ri-smartphone-line'
            };
            var pagos    = ((window.pedPagoState && window.pedPagoState.pagos) || [])
                .filter(function (p) { return p.monto > 0; });
            var abono    = pagos.reduce(function (a, p) { return a + p.monto; }, 0);
            var total    = (window.pedProdState && window.pedProdState.total) || 0;
            var restante = total - abono;
            var pct      = total > 0 ? Math.min(100, (abono / total) * 100) : 0;
            var pctInt   = Math.round(pct);

            // Actualiza hero card
            $('#ped-res-total-hero').text(pedFmtRes(total));
            $('#ped-res-abono-hero').text(pedFmtRes(abono));
            $('#ped-res-restante-hero').text(pedFmtRes(restante));

            // Tasa BCV y equivalente en Bs
            if (window.tasaBcv && window.tasaBcv.valor) {
                $('#ped-res-tasa-hero').text('Bs ' + parseFloat(window.tasaBcv.valor)
                    .toLocaleString('es-VE', { minimumFractionDigits: 4, maximumFractionDigits: 4 }));
            }
            var bsLbl = (typeof window.bsEquivalente === 'function') ? window.bsEquivalente(total) : null;
            $('#ped-res-total-bs-hero').text(bsLbl || 'Sin tasa BCV');

            // KPI figures
            var kpis = '<div class="d-flex gap-2 mb-3 flex-wrap">' +
                '<div class="flex-fill text-center px-2 py-2 rounded bg-light">' +
                '<small class="text-muted d-block fs-12 mb-1">Total</small>' +
                '<span class="fw-bold text-dark fs-13">' + pedFmtRes(total) + '</span></div>' +
                '<div class="flex-fill text-center px-2 py-2 rounded" style="background:rgba(22,163,74,.08)">' +
                '<small class="text-muted d-block fs-12 mb-1">Abonado</small>' +
                '<span class="fw-bold text-success fs-13">' + pedFmtRes(abono) + '</span></div>' +
                '<div class="flex-fill text-center px-2 py-2 rounded" style="background:rgba(30,60,114,.07)">' +
                '<small class="text-muted d-block fs-12 mb-1">Restante</small>' +
                '<span class="fw-bold text-atlantico-dark fs-13">' + pedFmtRes(restante) + '</span></div>' +
                '</div>';

            // Progress bar
            var barColor = pct >= 100 ? '#16a34a' : pct > 0 ? '#1e3c72' : '#e2e8f0';
            var progress = '<div class="mb-3">' +
                '<div class="d-flex justify-content-between mb-1">' +
                '<small class="text-muted fs-12">Progreso del pago</small>' +
                '<small class="fw-semibold fs-12">' + pctInt + '%</small></div>' +
                '<div class="progress" style="height:6px;border-radius:3px;">' +
                '<div class="progress-bar" style="width:' + pctInt + '%;background:' + barColor + ';border-radius:3px;" role="progressbar"></div>' +
                '</div></div>';

            // Methods
            var metodosHtml;
            if (!pagos.length) {
                metodosHtml = '<div class="text-center py-2">' +
                    '<i class="ri-wallet-3-line text-muted fs-5 d-block mb-1"></i>' +
                    '<p class="text-muted small mb-0">Sin pagos registrados — se salda después.</p></div>';
            } else {
                metodosHtml = '<div class="vstack gap-1">' +
                pagos.map(function (p) {
                    var label = METODO_LABELS[p.metodo] || p.metodo;
                    var icon  = METODO_ICONS[p.metodo] || 'ri-money-dollar-circle-line';
                    var extra = '';
                    if ((p.metodo === 'transferencia' || p.metodo === 'pago_movil') && (p.banco_nombre || p.referencia)) {
                        extra = '<small class="text-muted d-block fs-12">' + (p.banco_nombre || '—') + ' · Ref: ' + (p.referencia || '—') + '</small>';
                    }
                    return '<div class="d-flex align-items-center justify-content-between py-2 px-2 rounded" style="background:rgba(30,60,114,.05)">' +
                        '<div class="d-flex align-items-center gap-2">' +
                        '<i class="' + icon + ' text-atlantico-dark fs-5"></i>' +
                        '<div><span class="fw-semibold fs-13">' + label + '</span>' + extra + '</div></div>' +
                        '<span class="fw-bold fs-13 text-atlantico-dark">' + pedFmtRes(p.monto) + '</span>' +
                        '</div>';
                }).join('') + '</div>';
            }
            $('#ped-res-pago-bloque').html(kpis + progress + metodosHtml);
        }

        // Render completo del resumen
        window.pedRenderResumen = function () {
            pedRenderResCliente();
            pedRenderResDatos();
            pedRenderResProductos();
            pedRenderResPago();
        };

        // Construir payload para el store
        function pedConstruirPayload() {
            var items = (window.pedProdState && window.pedProdState.items) || [];
            // Pagos activos con monto > 0 (multi-método)
            var pagos = ((window.pedPagoState && window.pedPagoState.pagos) || [])
                .filter(function (p) { return p.monto > 0; });

            return {
                _token:                 $('meta[name="csrf-token"]').attr('content'),
                cliente_id:             $('#ped-wiz-cliente-id-field').val(),
                cotizacion_id:          $('#ped-wiz-cotizacion-id-field').val() || null,
                fecha_pedido:           $('#ped-fecha-pedido-field').val(),
                fecha_entrega_estimada: $('#ped-fecha-entrega-field').val() || null,
                prioridad:              $('#ped-prioridad-field').val(),
                pagos:                  pagos,
                productos:              items.map(function (it) {
                    var bordados = Array.isArray(it.bordados) ? it.bordados : [];
                    var prod = {
                        producto_id: it.producto_id,
                        cantidad:    it.cantidad,
                        talla_id:    it.talla_id || null,
                        color_id:    it.color_id || null,
                        // precio_unitario = BASE (sin bordado); el backend re-suma el recargo.
                        // Si por algún motivo no hay precio_base, cae al precio mostrado.
                        precio_unitario: (it.precio_base != null ? it.precio_base : it.precio_unitario),
                        // Entero 1/0: jQuery serializa los booleanos JS como "true"/"false",
                        // que la regla `boolean` de Laravel rechaza (solo acepta 1/0/"1"/"0").
                        lleva_bordado:   bordados.length > 0 ? 1 : 0
                    };
                    if (bordados.length) {
                        prod.bordados = bordados.map(function (b) {
                            return {
                                ubicacion_bordado_id: b.ubicacion_bordado_id || null,
                                logo_id:              b.logo_id || null,
                                nombre_aplicado:      b.nombre_aplicado || '',
                                es_personalizada:     b.es_personalizada ? 1 : 0,
                                cantidad:             Math.max(1, parseInt(b.cantidad || 1, 10)),
                                precio_aplicado:      parseFloat(b.precio_aplicado) || 0
                            };
                        });
                    }
                    return prod;
                })
            };
        }

        // Mapear errores 422 al paso que los originó
        function pedMapErrorsToStep(errors) {
            var keys = Object.keys(errors);
            if (keys.some(function (k) { return /^(cliente_id|fecha_pedido|prioridad)/.test(k); })) return 1;
            if (keys.some(function (k) { return /^productos/.test(k); })) return 2;
            if (keys.some(function (k) { return /^pagos/.test(k); })) return 3;
            return 4;
        }

        // Submit: crear (POST /pedidos) o editar (PUT /pedidos/{id}).
        // Ambos botones disparan; el modo se detecta por #ped-wiz-id-field.
        $(document).on('click', '#ped-wiz-add-btn, #ped-wiz-edit-btn', function (e) {
            e.preventDefault(); // los botones son type=submit dentro del form → evitar submit nativo
            var $btn = $(this).prop('disabled', true);
            var payload = pedConstruirPayload();

            // Modo edición: id presente → PUT a pedidos.update con estado
            var editId = $('#ped-wiz-id-field').val();
            var esEdit = !!editId;
            var url    = '{{ route("pedidos.store") }}';
            if (esEdit) {
                payload._method = 'PUT';
                payload.estado  = $('#ped-estado-field').val() || 'Pendiente';
                url = '{{ url("pedidos") }}/' + editId;
            }

            $.ajax({
                url: url,
                method: 'POST',
                data: payload,
                success: function (response) {
                    $btn.prop('disabled', false);
                    Swal.fire({
                        icon: 'success', title: esEdit ? '¡Pedido actualizado!' : '¡Pedido creado!',
                        text: response.success,
                        showConfirmButton: false, timer: 1800
                    });
                    var $wizModal = $('#pedidoForm').closest('.modal');
                    if (!$wizModal.length) $wizModal = $('#showModal');
                    $wizModal.modal('hide');
                    if ($.fn.DataTable.isDataTable('#pedidos-table')) {
                        $('#pedidos-table').DataTable().ajax.reload(null, false);
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false);
                    var msg = '';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errs = xhr.responseJSON.errors;
                        var step = pedMapErrorsToStep(errs);
                        msg = Object.values(errs).map(function (v) {
                            return Array.isArray(v) ? v[0] : v;
                        }).join('\n');
                        if (typeof window.pedWizard !== 'undefined') {
                            window.pedWizard.show(step);
                        }
                    } else if (xhr.responseJSON) {
                        msg = xhr.responseJSON.error || xhr.responseJSON.message || 'Error desconocido.';
                    } else {
                        msg = 'Ocurrió un error al crear el pedido.';
                    }
                    Swal.fire({ icon: 'error', title: 'Error al guardar', text: msg });
                }
            });
        });

    })();


    // ╔══════════════════════════════════════════════════════════════════
    // ║ PEDIDO WIZARD — TASK-015: Modo "completar desde cotización"
    // ║ Fetch datosParaPedido, hidrata pasos 1+2, abre en paso 3
    // ╚══════════════════════════════════════════════════════════════════
    (function () {
        'use strict';

        var pedPendingHydration = null;

        function pedMostrarBannerHeredado(cotizacionId) {
            $('.ped-banner-cot-num').text('#' + cotizacionId);
            $('#ped-banner-heredado-p1, #ped-banner-heredado-p2').removeClass('d-none');
        }

        function pedOcultarBannerHeredado() {
            $('#ped-banner-heredado-p1, #ped-banner-heredado-p2').addClass('d-none');
        }

        // Abrir wizard hidratado con datos de una cotización
        window.pedAbrirDesdeCotizacion = function (cotizacionId) {
            Swal.fire({
                title: 'Cargando cotización...',
                text: 'Preparando datos para el pedido',
                allowOutsideClick: false,
                didOpen: function () { Swal.showLoading(); }
            });

            $.ajax({
                url: '{{ route("cotizaciones.datosParaPedido", ":id") }}'.replace(':id', cotizacionId),
                method: 'GET',
                success: function (data) {
                    Swal.close();
                    if (!data || !data.cliente) {
                        Swal.fire({ icon: 'error', title: 'Error',
                            text: 'No se pudieron cargar los datos de la cotización.' });
                        return;
                    }
                    // Guardar antes de que show.bs.modal dispare los resets
                    pedPendingHydration = { data: data };
                    $('#showModal').modal('show');
                },
                error: function (xhr) {
                    Swal.close();
                    var msg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message))
                        || 'No se pudieron cargar los datos de la cotización.';
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
            });
        };

        // Aplicar hydration después de que los resets de show.bs.modal corran
        $('#showModal').on('shown.bs.modal', function () {
            if (!pedPendingHydration) return;

            var data  = pedPendingHydration.data;
            var cotId = data.cotizacion_id;
            pedPendingHydration = null;

            // Marcar origen de cotización
            $('#ped-wiz-cotizacion-id-field').val(cotId);

            // Paso 1 — cliente
            if (data.cliente && typeof window.pedAplicarPersonaAPedido === 'function') {
                window.pedAplicarPersonaAPedido(data.cliente, data.cliente_id);
            }

            // Paso 2 — productos
            if (data.productos && data.productos.length && typeof window.pedHidratarProductosDesde === 'function') {
                window.pedHidratarProductosDesde(data.productos, cotId);
            }

            // Cliente y productos vienen de la cotización → solo lectura
            if (typeof window.pedAplicarModoProtegido === 'function') {
                window.pedAplicarModoProtegido();
            }

            // Banners amber en paso 1 y paso 2
            pedMostrarBannerHeredado(cotId);

            // Título identificador
            $('#modalTitle').text('Pedido desde Cotización #' + cotId);

            // Arrancar en el paso 1: revisar cliente y productos (solo lectura), luego el pago
            if (typeof window.pedWizard !== 'undefined') {
                window.pedWizard.show(1);
            }
        });

        // Limpiar al cerrar
        $('#showModal').on('hidden.bs.modal', function () {
            pedOcultarBannerHeredado();
            if (!$('#ped-wiz-id-field').val()) {
                $('#ped-wiz-cotizacion-id-field').val('');
                if (typeof window.pedQuitarModoProtegido === 'function') {
                    window.pedQuitarModoProtegido();
                }
                $('#modalTitle').text('Nuevo Pedido');
            }
        });

        // Detectar ?convertir={cotizacionId} en la URL al cargar la página
        var params     = new URLSearchParams(window.location.search);
        var convertirId = params.get('convertir');
        if (convertirId) {
            window.history.replaceState(null, '', window.location.pathname);
            setTimeout(function () {
                window.pedAbrirDesdeCotizacion(convertirId);
            }, 450);
        }

    })();


    // ╔══════════════════════════════════════════════════════════════════
    // ║ PEDIDO WIZARD — TASK-016: Modo edición (campos protegidos)
    // ║ GET pedidos.show, hidrata los 4 pasos, paso 1 y 2 read-only,
    // ║ estado editable, submit PUT. Reabre cualquier estado.
    // ╚══════════════════════════════════════════════════════════════════
    (function () {
        'use strict';

        var pedPendingEdit = null;

        // Abrir el wizard en modo edición con un pedido existente
        window.pedAbrirEnEdit = function (pedidoId) {
            Swal.fire({
                title: 'Cargando pedido...',
                text: 'Preparando datos para edición',
                allowOutsideClick: false,
                didOpen: function () { Swal.showLoading(); }
            });

            $.ajax({
                url: '{{ url("pedidos") }}/' + pedidoId,
                method: 'GET',
                success: function (data) {
                    Swal.close();
                    if (!data || !data.id) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el pedido.' });
                        return;
                    }
                    pedPendingEdit = data;
                    // Marca edit ANTES de abrir → los resets de modo crear (show.bs.modal) se saltan
                    $('#ped-wiz-id-field').val(data.id);
                    // Creador real: mostrar quién creó el pedido (no el editor)
                    if (data.creador) {
                        $('#ped-creador-name').text(data.creador.name || '—');
                        if (data.creador.avatar_url) $('#ped-creador-avatar').attr('src', data.creador.avatar_url);
                    }
                    $('#showModal').modal('show');
                },
                error: function (xhr) {
                    Swal.close();
                    var msg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message))
                        || 'No se pudo cargar el pedido.';
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
            });
        };

        // Hidratar después de que se abra el modal (los resets de crear ya se saltaron)
        $('#showModal').on('shown.bs.modal', function () {
            if (!pedPendingEdit) return;
            var data = pedPendingEdit;
            pedPendingEdit = null;

            // El modo protegido (solo lectura de cliente/productos) se aplica más abajo,
            // tras hidratar todos los pasos.

            // ── Paso 1: cliente (construir "persona" desde campos normalizados) ──
            var persona = {
                nombre:         data.cliente_nombre_completo || '',
                apellido:       '',
                documento:      data.cliente_documento || '',
                telefono:       data.cliente_telefono_normalizado || '',
                email:          data.cliente_email_normalizado || '',
                tipo_documento: String(data.cliente_documento || '').substring(0, 2),
                roles:          ['cliente']
            };
            if (typeof window.pedAplicarPersonaAPedido === 'function') {
                window.pedAplicarPersonaAPedido(persona, data.cliente_id);
            }

            // Fechas + prioridad
            $('#ped-fecha-pedido-field').val(data.fecha_pedido || '');
            $('#ped-fecha-entrega-field').val(data.fecha_entrega_estimada || '');
            if (data.prioridad) {
                $('#ped-prioridad-field').val(data.prioridad);
                $('.ped-priority-chip').removeClass('is-active').attr('aria-checked', 'false');
                $('.ped-priority-chip[data-value="' + data.prioridad + '"]').addClass('is-active').attr('aria-checked', 'true');
            }

            // ── Estado: mostrar card (solo edit) y marcar chip ──
            $('#ped-estado-field-wrapper').show();
            window.pedRenderEstadoBadge(data.estado || 'Pendiente');

            // ── Paso 2: productos (mapear formato show() → el esperado por hidratar) ──
            if (data.productos && data.productos.length && typeof window.pedHidratarProductosDesde === 'function') {
                var prods = data.productos.map(function (d) {
                    return {
                        producto_id:     d.producto_id,
                        producto_nombre: (d.producto && d.producto.nombre) ? d.producto.nombre : ('Producto #' + d.producto_id),
                        cantidad:        d.cantidad,
                        talla_id:        d.talla_id || null,
                        color_id:        d.color_id || null,
                        precio_unitario: d.precio_unitario,
                        bordados:        Array.isArray(d.bordados) ? d.bordados : []
                    };
                });
                window.pedHidratarProductosDesde(prods, null); // null = sin badge "heredado"
            }

            // ── Paso 3: pago (hidrata todos los métodos registrados) ──
            if (typeof window.pedHidratarPagos === 'function') {
                window.pedHidratarPagos(data.pagos || []);
            }
            if (typeof window.pedSincronizarTotalPago === 'function') {
                window.pedSincronizarTotalPago();
            }

            // ── Modo protegido: cliente y productos de solo lectura ──
            if (typeof window.pedAplicarModoProtegido === 'function') {
                window.pedAplicarModoProtegido();
            }

            // Título según estado
            var titulo = (data.estado === 'Pendiente')
                ? ('Completar Pedido #' + data.id)
                : ('Editar Pedido #' + data.id);
            $('#modalTitle').text(titulo);

            // Texto del botón de submit
            $('#ped-wiz-add-btn').html('<i class="ri-save-line me-1"></i>Actualizar pedido');

            // Empezar en el paso 1
            if (typeof window.pedWizard !== 'undefined') {
                window.pedWizard.show(1);
            }
        });

        // Limpiar estado de edición al cerrar (deja el wizard listo para la próxima apertura)
        $('#showModal').on('hidden.bs.modal', function () {
            $('#ped-wiz-id-field').val('');
            $('#ped-estado-field-wrapper').hide();
            if (typeof window.pedQuitarModoProtegido === 'function') {
                window.pedQuitarModoProtegido();
            }
            $('#ped-wiz-add-btn').html('<i class="ri-check-line me-1"></i>Crear pedido');
            $('#modalTitle').text('Nuevo Pedido');
        });

    })();

});
</script>
