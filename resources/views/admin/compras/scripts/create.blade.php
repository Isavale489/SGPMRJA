<script>
$(document).ready(function () {

    var rowCount = 0;
    var INSUMOS  = window.INSUMOS_DATA || [];
    var IVA_TASA = parseFloat(window.IVA_TASA) || 16;

    // (El proveedor ahora se elige por búsqueda de documento — ver IIFE abajo)

    // ── Utilidades ──────────────────────────────────────────────────────────
    function fmt(n) {
        return parseFloat(n || 0).toFixed(2);
    }

    // Formato venezolano: miles con '.', decimales con ','  (ej. 1.234.567,89)
    function formatBs(n, dec) {
        dec = (dec === undefined) ? 2 : dec;
        var v = parseFloat(n);
        if (isNaN(v)) v = 0;
        var parts = v.toFixed(dec).split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return dec > 0 ? parts.join(',') : parts[0];
    }

    // Parsea un monto en formato venezolano ("1.234,56") a número (1234.56).
    function parseBs(str) {
        if (str === null || str === undefined) return 0;
        var s = String(str).trim();
        if (s === '') return 0;
        s = s.replace(/\./g, '').replace(',', '.');
        var n = parseFloat(s);
        return isNaN(n) ? 0 : n;
    }

    // Mantiene en sincronía costo unitario ↔ total de la línea. La cantidad
    // (número simple) usa parseFloat; los montos en Bs usan parseBs.
    // source: 'total' → recalcula el unitario; cualquier otro → recalcula el total.
    function recomputeRow($tr, source) {
        var qty = parseFloat($tr.find('.c-cantidad').val()) || 0;
        if (source === 'total') {
            var total = parseBs($tr.find('.c-total').val());
            var unit  = qty > 0 ? total / qty : 0;
            $tr.find('.c-costo').val(unit ? formatBs(unit) : '');
        } else {
            var unit2  = parseBs($tr.find('.c-costo').val());
            var total2 = qty * unit2;
            $tr.find('.c-total').val(total2 ? formatBs(total2) : '');
        }
        recalcular();
    }

    function buildInsumoOptions(selectedId) {
        var html = '<option value="">Seleccione insumo...</option>';
        INSUMOS.forEach(function (ins) {
            var sel = (ins.id == selectedId) ? ' selected' : '';
            // aplica_iva puede venir como 1/0/true/false desde el backend
            var grav = (ins.aplica_iva === undefined || ins.aplica_iva === null)
                ? 1 : (Number(ins.aplica_iva) ? 1 : 0);
            html += '<option value="' + ins.id + '"'
                + ' data-costo="' + ins.costo_unitario + '"'
                + ' data-unidad="' + ins.unidad_medida + '"'
                + ' data-iva="' + grav + '"'
                + sel + '>'
                + ins.nombre + ' (' + ins.tipo + ')'
                + '</option>';
        });
        return html;
    }

    // Reconstruye TODOS los <select> de insumo (tras un alta rápida), preservando
    // la selección actual de cada fila para no perder ítems ya elegidos.
    function rebuildAllInsumoSelects() {
        $('#c-items-tbody tr').each(function () {
            var $sel = $(this).find('.c-insumo');
            $sel.html(buildInsumoOptions($sel.val()));
        });
    }

    // Los costos se cargan en bolívares; el equivalente en USD se deriva de la tasa.
    function recalcular() {
        var subtotal = 0, baseGravada = 0; // en Bs
        $('#c-items-tbody tr').each(function () {
            var cantidad = parseFloat($(this).find('.c-cantidad').val()) || 0;
            var costo    = parseBs($(this).find('.c-costo').val()); // Bs unitario
            var sub      = cantidad * costo;
            subtotal += sub;
            if ($(this).find('.c-iva-check').is(':checked')) baseGravada += sub;
        });
        var iva    = baseGravada * IVA_TASA / 100;
        var exento = subtotal - baseGravada;
        var total  = subtotal + iva;

        var tasa        = parseFloat($('#c-tasa').val()) || 0;
        var subtotalUsd = tasa > 0 ? subtotal / tasa : 0;
        var totalUsd    = tasa > 0 ? total / tasa : 0;

        $('#c-resumen-subtotal').text(formatBs(subtotal));
        $('#c-resumen-iva').text(formatBs(iva));
        $('#c-resumen-total').text(formatBs(total));
        $('#c-resumen-iva-pct').text(IVA_TASA);
        $('#c-resumen-tasa').text(tasa ? formatBs(tasa, 4) : '0,0000');
        $('#c-resumen-total-usd').text(formatBs(totalUsd));
        // Base exenta: solo se muestra si hay líneas exentas. Se alterna con
        // d-none (no con [hidden], que .d-flex !important anularía).
        if (exento > 0.0001) {
            $('#c-resumen-exento').text(formatBs(exento));
            $('#c-resumen-exento-wrap').removeClass('d-none');
        } else {
            $('#c-resumen-exento-wrap').addClass('d-none');
        }
        // Pie en vivo del paso 2 (Bs + equivalente USD)
        $('#c-items-footer-subtotal').text(formatBs(subtotal));
        $('#c-items-footer-subtotal-usd').text(formatBs(subtotalUsd));
    }

    function renumber() {
        $('#c-items-tbody tr').each(function (i) {
            $(this).find('.c-row-num').text(i + 1);
        });
    }

    function updateEmpty() {
        var count = $('#c-items-tbody tr').length;
        $('#c-items-count').text('(' + count + ')');
        $('#c-items-footer-count').text(count);
        if (count === 0) {
            $('#c-items-empty').show();
            $('#c-items-table-wrap').attr('hidden', true);
            $('#c-items-footer').removeClass('d-flex').addClass('d-none');
        } else {
            $('#c-items-empty').hide();
            $('#c-items-table-wrap').removeAttr('hidden');
            $('#c-items-footer').removeClass('d-none').addClass('d-flex');
        }
    }

    // ── Agregar fila ────────────────────────────────────────────────────────
    function addItemRow() {
        var idx = rowCount++;
        var num = $('#c-items-tbody tr').length + 1;

        var row = '<tr id="c-row-' + idx + '">'
            + '<td class="c-row-num text-center text-muted cot-col-num">' + num + '</td>'
            + '<td style="min-width:200px;">'
            +   '<div class="input-group input-group-sm">'
            +     '<select class="form-select form-select-sm c-insumo" id="c-ins-' + idx + '">'
            +       buildInsumoOptions('')
            +     '</select>'
            +     '<button type="button" class="btn btn-soft-success c-add-insumo-btn"'
            +     ' data-row="' + idx + '" title="Crear insumo nuevo"><i class="ri-add-line"></i></button>'
            +   '</div>'
            + '</td>'
            + '<td class="text-center">'
            +   '<div class="input-group input-group-sm" style="max-width:128px;margin:0 auto;">'
            +     '<input type="number" class="form-control form-control-sm c-cantidad text-center"'
            +     ' min="0.01" step="0.01" placeholder="0.00">'
            +     '<span class="input-group-text c-unit-addon">—</span>'
            +   '</div>'
            + '</td>'
            + '<td class="text-center">'
            +   '<input type="text" inputmode="decimal" class="form-control form-control-sm c-costo text-end"'
            +   ' placeholder="0,00" style="max-width:120px;margin:0 auto;">'
            + '</td>'
            + '<td class="text-center">'
            +   '<input type="text" inputmode="decimal" class="form-control form-control-sm c-total text-end"'
            +   ' placeholder="0,00" style="max-width:130px;margin:0 auto;">'
            + '</td>'
            + '<td class="text-center">'
            +   '<div class="form-check d-inline-block m-0">'
            +     '<input class="form-check-input c-iva-check" type="checkbox" checked'
            +     ' title="Gravable con IVA — desmarca si es exento">'
            +   '</div>'
            + '</td>'
            + '<td class="text-center">'
            +   '<button type="button" class="btn btn-sm btn-soft-danger py-0 px-1 c-remove-btn"'
            +   ' data-row="' + idx + '" title="Quitar"><i class="ri-delete-bin-6-line"></i></button>'
            + '</td>'
            + '</tr>';

        $('#c-items-tbody').append(row);
        updateEmpty();

        $('#c-ins-' + idx).on('change', function () {
            var opt   = $(this).find('option:selected');
            var costo = opt.data('costo'); // costo de referencia del insumo, en USD
            var unid  = opt.data('unidad');
            var $tr   = $(this).closest('tr');
            // El maestro guarda el costo en USD; lo precargamos convertido a Bs
            // con la tasa de la compra (editable por el usuario).
            if (costo) {
                var tasa = parseFloat($('#c-tasa').val()) || 0;
                var unitBs = tasa > 0 ? parseFloat(costo) * tasa : parseFloat(costo);
                $tr.find('.c-costo').val(formatBs(unitBs));
            }
            $tr.find('.c-unit-addon').text(unid ? String(unid).substring(0, 4) : '—');
            // Heredar gravable/exento del insumo elegido (data-iva = 1/0)
            if (opt.val()) {
                $tr.find('.c-iva-check').prop('checked', Number(opt.data('iva')) !== 0);
            }
            // Recalcular el total de la línea a partir del unitario precargado.
            recomputeRow($tr, 'unit');
        }).trigger('focus');
    }

    $('#c-add-item-btn, #c-add-item-empty-btn').on('click', addItemRow);

    // ── Eliminar fila ───────────────────────────────────────────────────────
    $(document).on('click', '.c-remove-btn', function () {
        $(this).closest('tr').remove();
        renumber();
        updateEmpty();
        recalcular();
    });

    // ── Sincronización costo unitario ↔ total al editar ──────────────────────
    // Escribís el unitario → calcula el total; escribís el total → calcula el
    // unitario; cambiás la cantidad → recalcula el total con el unitario actual.
    $(document).on('input', '.c-costo', function () { recomputeRow($(this).closest('tr'), 'unit'); });
    $(document).on('input', '.c-total', function () { recomputeRow($(this).closest('tr'), 'total'); });
    $(document).on('input', '.c-cantidad', function () { recomputeRow($(this).closest('tr'), 'unit'); });
    $(document).on('change', '.c-iva-check', recalcular);

    // Al salir del campo, reformatea el monto tecleado al formato venezolano.
    $(document).on('blur', '.c-costo, .c-total', function () {
        var v = parseBs($(this).val());
        $(this).val(v ? formatBs(v) : '');
    });

    // ── Tasa de cambio: autocompletar según la fecha de compra ───────────────
    // Busca la tasa BCV del día (o la vigente anterior) en la DB. Es editable:
    // si la factura del proveedor usó otra tasa, el usuario la corrige.
    function aplicarTasaPorFecha(fecha, opts) {
        opts = opts || {};
        if (!fecha) return;
        $.get('/compras/tasa', { fecha: fecha }, function (r) {
            // En modo edición ya se cargó la tasa guardada (snapshot): si la
            // petición "solo nuevo" llega tarde, no pisar ese valor.
            if (opts.soloNuevo && $('#c-edit-id').val()) return;

            var $hint = $('#c-tasa-hint');
            var $tasa = $('#c-tasa');
            if (r.encontrada) {
                // Tasa traída por el sistema: campo de solo lectura.
                $tasa.val(parseFloat(r.valor).toFixed(4)).prop('readonly', true).addClass('bg-light');
                if (r.exacta) {
                    $hint.html('<i class="ri-checkbox-circle-line text-success me-1"></i>Tasa BCV oficial del ' + r.fecha_bcv_fmt + '.');
                } else {
                    $hint.html('<i class="ri-information-line text-warning me-1"></i>Sin tasa de ese día; se aplicó la tasa BCV vigente del ' + r.fecha_bcv_fmt + '.');
                }
            } else {
                // El sistema no tiene tasa para esa fecha: se habilita la carga manual.
                $tasa.val('').prop('readonly', false).removeClass('bg-light');
                $hint.html('<i class="ri-error-warning-line text-danger me-1"></i>' + (r.message || 'Sin tasa BCV para esa fecha. Ingresala manualmente.'));
            }
            recalcular();
        });
    }

    $('#c-fecha').on('change', function () { aplicarTasaPorFecha($(this).val()); });
    $('#c-tasa').on('input', recalcular);

    // N° de factura: solo dígitos y guiones (formato 0001-000456).
    $('#c-factura').on('input', function () {
        this.value = this.value.replace(/[^0-9\-]/g, '');
    });

    // ══════════════════════════════════════════════════════════════════════
    //  WIZARD — navegación 3 pasos (scaffold .wiz-*)
    // ══════════════════════════════════════════════════════════════════════
    (function () {
        'use strict';
        var TOTAL_STEPS = 3;
        var currentStep = 1;

        // ── Render del proveedor: card de datos (paso 1) + chip persistente ──
        // selectedProv: objeto { id, nombre, doc, tel, email, tipo, compras, ultima }
        var selectedProv = null;

        function iniciales(name) {
            return String(name || '').split(/\s+/).slice(0, 2).map(function (w) { return w.charAt(0); }).join('').toUpperCase();
        }

        function renderProveedorSeleccionado() {
            var data = selectedProv;

            // Sin proveedor → empty state, sin card, sin chip
            if (!data || !data.id) {
                $('#c-prov-empty').show();
                $('#c-prov-card').attr('hidden', true).hide();
                $('#c-prov-banner').attr('hidden', true).attr('aria-hidden', 'true');
                return;
            }

            var name = data.nombre || '—';
            var doc  = data.doc || '';
            var tel  = data.tel || '';
            var mail = data.email || '';
            var ini  = iniciales(name) || '—';

            // Card del paso 1
            $('#c-prov-card-avatar').text(ini);
            $('#c-prov-card-name').text(name);
            $('#c-prov-card-doc').text(doc || 'Sin documento');
            $('#c-prov-card-tipo').text(data.tipo === 'natural' ? 'Natural'
                                      : data.tipo === 'juridico' ? 'Jurídico' : 'Proveedor');
            if (tel)  { $('#c-prov-card-tel').text(tel);  $('#c-prov-card-tel-wrap').show(); }
            else      { $('#c-prov-card-tel-wrap').hide(); }
            if (mail) { $('#c-prov-card-email').text(mail); $('#c-prov-card-email-wrap').show(); }
            else      { $('#c-prov-card-email-wrap').hide(); }

            // Stats: compras previas + última compra
            var count = parseInt(data.compras, 10) || 0;
            $('#c-prov-card-count').text(count);
            if (data.ultima) {
                var u = String(data.ultima).substring(0, 10).split('-');
                $('#c-prov-card-last').text(u[2] + '/' + u[1] + '/' + u[0]);
                $('#c-prov-card-last-sep').removeAttr('hidden').show();
                $('#c-prov-card-last-wrap').removeAttr('hidden').show();
            } else {
                $('#c-prov-card-last-sep').attr('hidden', true).hide();
                $('#c-prov-card-last-wrap').attr('hidden', true).hide();
            }

            $('#c-prov-empty').hide();
            $('#c-prov-card').removeAttr('hidden').show();

            // Chip persistente (gutter izquierdo)
            $('#c-prov-banner-avatar').text(ini);
            $('#c-prov-banner-name').text(name);
            $('#c-prov-banner-doc').text(doc || '—');
            bannerVisibilidad(currentStep);

            $('#c-prov-autocomplete').empty().hide();
        }

        // ── Aplica un proveedor seleccionado (búsqueda, from-persona o creación) ─
        function aplicarProveedor(prov) {
            selectedProv = {
                id:      prov.id,
                nombre:  prov.nombre || '—',
                doc:     prov.doc || '',
                tel:     prov.tel || '',
                email:   prov.email || '',
                tipo:    prov.tipo || '',
                compras: parseInt(prov.compras, 10) || 0,
                ultima:  prov.ultima || null
            };
            $('#c-proveedor').val(prov.id);
            $('#c-prov-doc-number').val('');
            renderProveedorSeleccionado();
        }

        // ── Limpia la selección y vuelve al estado de búsqueda ──────────────────
        function clearProveedor() {
            selectedProv = null;
            $('#c-proveedor').val('');
            $('#c-prov-autocomplete').empty().hide();
            renderProveedorSeleccionado();
        }

        function bannerVisibilidad(n) {
            // visible en pasos 2+ si hay proveedor; oculto en paso 1
            if (n > 1 && $('#c-proveedor').val()) {
                $('#c-prov-banner').removeAttr('hidden').attr('aria-hidden', 'false');
            } else {
                $('#c-prov-banner').attr('hidden', true).attr('aria-hidden', 'true');
            }
        }

        // ── Recap del paso 3 ─────────────────────────────────────────────────
        function renderRecap() {
            $('#c-recap-proveedor').text(selectedProv ? selectedProv.nombre : '—');
            $('#c-recap-factura').text($('#c-factura').val() || 'S/N');
            var fecha = $('#c-fecha').val();
            if (fecha) {
                var p = fecha.split('-');
                $('#c-recap-fecha').text(p[2] + '/' + p[1] + '/' + p[0]);
            } else {
                $('#c-recap-fecha').text('—');
            }
            var n = $('#c-items-tbody tr').length;
            $('#c-recap-items').text(n + ' insumo' + (n === 1 ? '' : 's'));
        }

        // ── Mostrar paso N ───────────────────────────────────────────────────
        function showStep(n) {
            n = Math.max(1, Math.min(TOTAL_STEPS, n));
            currentStep = n;

            $('.wiz-step-content', '#createCompraModal').each(function () {
                var s = parseInt(this.dataset.step, 10);
                $(this).toggleClass('is-active', s === n).attr('hidden', s !== n ? true : null);
            });

            $('.wiz-step-marker', '#createCompraModal').each(function () {
                var s = parseInt(this.dataset.step, 10);
                $(this).toggleClass('is-active', s === n)
                       .toggleClass('is-complete', s < n)
                       .attr('aria-selected', s === n ? 'true' : 'false');
            });

            $('.wiz-step-line-fill', '#createCompraModal').each(function () {
                var l = parseInt(this.dataset.line, 10);
                $(this).css('width', l < n ? '100%' : '0%');
            });

            $('#c-step-current').text(n);

            // Botones del footer
            $('#btn-c-prev').toggle(n > 1);
            $('#btn-c-next').toggle(n < TOTAL_STEPS);
            $('#c-submit-btn').toggle(n === TOTAL_STEPS);

            bannerVisibilidad(n);

            // Hooks por paso
            if (n === 3) { recalcular(); renderRecap(); }
        }

        // ── Validación por paso ──────────────────────────────────────────────
        function validateStep(n) {
            if (n === 1) {
                if (!$('#c-proveedor').val()) {
                    marcarInvalido($('#c-proveedor'), 'Seleccione un proveedor.');
                    $('#c-proveedor').parent().find('.select2-selection').trigger('focus');
                    return false;
                }
                marcarValido($('#c-proveedor'));

                if (!$('#c-factura').val().trim()) {
                    Swal.fire({ title: 'Campo requerido', text: 'Ingrese el número de factura.', icon: 'warning', confirmButtonText: 'Entendido' });
                    return false;
                }
                if (!/^[0-9\-]+$/.test($('#c-factura').val().trim())) {
                    Swal.fire({ title: 'Formato inválido', text: 'El número de factura solo puede contener dígitos y guiones.', icon: 'warning', confirmButtonText: 'Entendido' });
                    return false;
                }
                if (!$('#c-fecha').val()) {
                    marcarInvalido($('#c-fecha'), 'Ingrese la fecha de compra.');
                    $('#c-fecha').trigger('focus');
                    return false;
                }
                marcarValido($('#c-fecha'));

                // La fecha no puede ser futura (max = hoy según el servidor).
                var maxFecha = $('#c-fecha').attr('max');
                if (maxFecha && $('#c-fecha').val() > maxFecha) {
                    Swal.fire({ title: 'Fecha inválida', text: 'La fecha de compra no puede ser futura.', icon: 'warning', confirmButtonText: 'Entendido' });
                    return false;
                }
                if (!(parseFloat($('#c-tasa').val()) > 0)) {
                    Swal.fire({ title: 'Campo requerido', text: 'Ingrese la tasa de cambio (Bs por USD) de la compra.', icon: 'warning', confirmButtonText: 'Entendido' });
                    return false;
                }
                return true;
            }
            if (n === 2) {
                return collectItems() !== false;
            }
            return true;
        }

        // ── Recolecta y valida los ítems; devuelve array o false ─────────────
        function collectItems() {
            var items     = [];
            var insumoIds = [];
            var err       = null;
            var $errField = null;
            var errMsg    = null;

            $('#c-items-tbody tr').each(function () {
                if (err) return false;
                var $tr      = $(this);
                var insumoId = $tr.find('.c-insumo').val();
                var cantidad = $tr.find('.c-cantidad').val();
                var costo    = parseBs($tr.find('.c-costo').val()); // Bs unitario
                var aplicaIva = $tr.find('.c-iva-check').is(':checked');

                if (!insumoId || !cantidad || parseFloat(cantidad) <= 0 || !costo || costo <= 0) {
                    err = 'incompleto';
                    if (!insumoId)                                      { $errField = $tr.find('.c-insumo');   errMsg = 'Seleccione un insumo para esta fila.'; }
                    else if (!cantidad || parseFloat(cantidad) <= 0)    { $errField = $tr.find('.c-cantidad'); errMsg = 'Ingrese una cantidad válida (mayor a 0).'; }
                    else                                                 { $errField = $tr.find('.c-costo');    errMsg = 'Ingrese un costo válido (mayor a 0).'; }
                    return false;
                }
                if (insumoIds.indexOf(insumoId) !== -1) {
                    err = 'dup';
                    $errField = $tr.find('.c-insumo');
                    errMsg = 'Este insumo ya está en la lista. Cada insumo puede aparecer una sola vez.';
                    return false;
                }
                insumoIds.push(insumoId);
                // El costo se ingresa en bolívares; el USD lo deriva el backend.
                items.push({ insumo_id: insumoId, cantidad: cantidad, costo_unitario_bs: costo, aplica_iva: aplicaIva });
            });

            if (items.length === 0 && !err) {
                Swal.fire({ title: 'Sin ítems', text: 'Agregue al menos un insumo a la compra.', icon: 'warning', confirmButtonText: 'Entendido' });
                return false;
            }
            if (err) {
                marcarInvalido($errField, errMsg);
                $errField.trigger('focus');
                return false;
            }
            return items;
        }

        function nextStep() { if (currentStep < TOTAL_STEPS && validateStep(currentStep)) showStep(currentStep + 1); }
        function prevStep() { if (currentStep > 1) showStep(currentStep - 1); }

        $('#btn-c-next').on('click', nextStep);
        $('#btn-c-prev').on('click', prevStep);

        // Click en marker: retroceso libre, avance valida cada paso intermedio
        $(document).on('click', '#createCompraModal .wiz-step-marker[data-step]', function () {
            var target = parseInt(this.dataset.step, 10);
            if (target < currentStep) { showStep(target); return; }
            for (var s = currentStep; s < target; s++) if (!validateStep(s)) return;
            showStep(target);
        });

        // ── Feedback en tiempo real — paso 1 ────────────────────────────────
        $('#c-proveedor').on('select2:close', function () {
            if (!$(this).val()) marcarInvalido($(this), 'Seleccione un proveedor.');
            else marcarValido($(this));
        });

        $('#c-fecha').on('blur', function () {
            if (!$(this).val()) marcarInvalido($(this), 'Ingrese la fecha de compra.');
            else marcarValido($(this));
        });

        // ════════════════════════════════════════════════════════════════════
        //  BÚSQUEDA DE PROVEEDOR POR DOCUMENTO (patrón cliente de cotizaciones)
        // ════════════════════════════════════════════════════════════════════
        var provSearchTimeout = null;
        var CSRF = $('meta[name="csrf-token"]').attr('content');

        function provShowLoading(show) {
            if (show) {
                $('#c-prov-loading').removeAttr('hidden');
                $('#c-prov-empty').hide();
            } else {
                $('#c-prov-loading').attr('hidden', true);
                if (!selectedProv) $('#c-prov-empty').show();
            }
        }

        function renderProvAutocomplete(personas) {
            var html = '';
            if (personas && personas.length > 0) {
                personas.forEach(function (p, idx) {
                    var isJuridico = p.tipo_documento === 'J-' || p.tipo_documento === 'G-';
                    var nombre = isJuridico && p.razon_social
                        ? p.razon_social
                        : (p.apellido ? p.nombre + ' ' + p.apellido : p.nombre);
                    var badge = p.proveedor_id
                        ? '<span class="badge bg-success">Proveedor</span>'
                        : '<span class="badge bg-secondary">No es proveedor aún</span>';
                    html += '<button type="button" class="list-group-item list-group-item-action c-prov-ac-item" data-idx="' + idx + '">'
                        +   '<div class="d-flex justify-content-between align-items-center flex-wrap gap-1">'
                        +     '<div>'
                        +       '<span class="fw-semibold">' + (p.documento || 'Sin documento') + '</span>'
                        +       '<span class="text-muted"> — ' + nombre + '</span>'
                        +       '<small class="text-muted d-block">' + (p.email || 'Sin email') + '</small>'
                        +     '</div>'
                        +     '<div>' + badge + '</div>'
                        +   '</div>'
                        + '</button>';
                });
                $('#c-prov-autocomplete').data('personas', personas);
            } else {
                html = '<div class="list-group-item disabled">No se encontraron registros. Puedes crear un proveedor nuevo abajo.</div>';
                $('#c-prov-autocomplete').removeData('personas');
            }
            $('#c-prov-autocomplete').html(html).show();
        }

        $('#c-prov-doc-number').on('input', function () {
            var number = $(this).val().trim();
            clearTimeout(provSearchTimeout);
            if (number.length < 6) {
                $('#c-prov-autocomplete').empty().hide();
                provShowLoading(false);
                return;
            }
            if (!selectedProv) provShowLoading(true);
            provSearchTimeout = setTimeout(function () {
                $.ajax({
                    url: '/personas-search',
                    data: { q: number },
                    complete: function () { provShowLoading(false); },
                    success: function (personas) { renderProvAutocomplete(personas); }
                });
            }, 300);
        });

        // Selección de una persona de la lista
        $(document).on('click', '.c-prov-ac-item', function () {
            var idx = $(this).data('idx');
            var personas = $('#c-prov-autocomplete').data('personas') || [];
            var p = personas[idx];
            if (!p) return;

            var isJuridico = p.tipo_documento === 'J-' || p.tipo_documento === 'G-';
            var nombre = isJuridico && p.razon_social
                ? p.razon_social
                : (p.apellido ? p.nombre + ' ' + p.apellido : p.nombre);

            // Caso 1: ya es proveedor → aplicar directo
            if (p.proveedor_id) {
                aplicarProveedor({
                    id:      p.proveedor_id,
                    nombre:  nombre,
                    doc:     p.documento || '',
                    tel:     p.telefono || '',
                    email:   p.email || '',
                    tipo:    p.proveedor_tipo || '',
                    compras: p.compras_count || 0,
                    ultima:  p.compras_last_date || null
                });
                return;
            }

            // Caso 2: existe pero no es proveedor → confirmar conversión
            Swal.fire({
                title: '¿Crear proveedor con datos existentes?',
                html: '<strong>' + nombre.trim() + '</strong> ya está registrado en el sistema pero aún no es proveedor.<br><br>¿Deseás crear el proveedor reutilizando estos datos?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="ri-check-line me-1"></i>Sí, crear proveedor',
                cancelButtonText: 'Cancelar',
                customClass: { confirmButton: 'btn btn-success w-xs me-2', cancelButton: 'btn btn-light w-xs' },
                buttonsStyling: false
            }).then(function (r) {
                if (!r.isConfirmed) return;
                $.ajax({
                    url: '/proveedores/from-persona/' + p.persona_id,
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF },
                    success: function (resp) {
                        if (resp.success && resp.proveedor) {
                            aplicarProveedor(resp.proveedor);
                            Swal.fire({ icon: 'success', title: resp.reused ? '¡Listo!' : 'Proveedor creado', text: resp.message, showConfirmButton: false, timer: 1600 });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo crear el proveedor.' });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error al crear el proveedor.' });
                    }
                });
            });
        });

        // Ocultar lista al hacer click fuera
        $(document).on('click', function (e) {
            if (!$(e.target).closest('#c-prov-doc-number, #c-prov-doc-prefix, #c-prov-autocomplete').length) {
                $('#c-prov-autocomplete').empty().hide();
            }
        });

        // Botón "Cambiar" → volver a búsqueda
        $('#c-prov-change-btn').on('click', function () {
            clearProveedor();
            $('#c-prov-doc-number').val('').trigger('focus');
        });

        // ── Reset al cerrar el modal ─────────────────────────────────────────
        function resetModal() {
            rowCount = 0;
            $('#compraForm')[0].reset();
            selectedProv = null;
            $('#c-proveedor').val('');
            $('#c-edit-id').val('');
            $('#c-prov-doc-number').val('');
            $('#c-prov-doc-prefix').val('J-');
            $('#c-prov-autocomplete').empty().hide();
            $('#c-fecha').val('{{ date("Y-m-d") }}');
            $('#c-tasa').val('').prop('readonly', true).addClass('bg-light');
            $('#c-tasa-hint').html('<i class="ri-information-line me-1"></i>Se autocompleta con la tasa BCV de la fecha de compra.');
            $('#c-items-tbody').empty();
            updateEmpty();
            recalcular();
            renderProveedorSeleccionado();
            $('#compraModalTitle').html('<i class="ri-shopping-bag-3-line me-1"></i>Nueva Compra');
            $('#c-submit-btn').removeAttr('disabled').html('<i class="ri-save-line me-1"></i>Registrar Compra');
        }

        // ── Poblar wizard en modo edición ────────────────────────────────────
        function populate(data) {
            $('#c-edit-id').val(data.id);
            $('#compraModalTitle').html('<i class="ri-pencil-line me-1"></i>Editar Borrador #' + data.id);
            $('#c-submit-btn').html('<i class="ri-save-line me-1"></i>Actualizar Compra');

            if (data.proveedor_data && data.proveedor_data.id) {
                aplicarProveedor(data.proveedor_data);
            }

            $('#c-factura').val(data.numero_factura || '');
            $('#c-fecha').val(data.fecha_compra || '');
            // Tasa guardada en la compra (snapshot), no la del día actual. Solo
            // lectura si quedó registrada; editable si la compra no tenía tasa.
            if (data.tasa_cambio) {
                $('#c-tasa').val(parseFloat(data.tasa_cambio).toFixed(4)).prop('readonly', true).addClass('bg-light');
                $('#c-tasa-hint').html('<i class="ri-history-line me-1"></i>Tasa con la que se registró esta compra.');
            } else {
                $('#c-tasa').val('').prop('readonly', false).removeClass('bg-light');
                $('#c-tasa-hint').html('<i class="ri-error-warning-line text-danger me-1"></i>Esta compra no tiene tasa registrada. Ingresala.');
            }
            $('#c-observaciones').val(data.observaciones || '');

            $('#c-items-tbody').empty();
            rowCount = 0;
            if (data.items && data.items.length) {
                data.items.forEach(function (item) {
                    addItemRow();
                    var $row = $('#c-items-tbody tr:last');
                    var $sel = $row.find('.c-insumo');
                    $sel.val(item.insumo_id);
                    var unid = $sel.find('option:selected').data('unidad') || '';
                    $row.find('.c-unit-addon').text(unid ? String(unid).substring(0, 4) : '—');
                    $row.find('.c-cantidad').val(parseFloat(item.cantidad));
                    // Mostrar el costo en Bs (formato venezolano) tal como se tecleó.
                    $row.find('.c-costo').val(formatBs(item.costo_unitario_bs));
                    // Restaurar el flag IVA guardado en la línea (no el del insumo)
                    $row.find('.c-iva-check').prop('checked', item.aplica_iva !== false);
                    // Rellenar el total de la línea a partir del unitario.
                    recomputeRow($row, 'unit');
                });
                updateEmpty();
                recalcular();
            }
        }

        // Estado inicial: fija display:none inline en la card (vence al display:flex)
        renderProveedorSeleccionado();

        // ── Lifecycle del modal ──────────────────────────────────────────────
        $('#createCompraModal')
            .on('show.bs.modal', function () {
                showStep(1);
                // Compra nueva: precargar la tasa BCV de hoy. En edición, populate()
                // (shown.bs.modal) fija la tasa guardada y el guard soloNuevo la respeta.
                aplicarTasaPorFecha($('#c-fecha').val(), { soloNuevo: true });
            })
            .on('hidden.bs.modal', function () { resetModal(); currentStep = 1; });

        // ── Envío (solo en el último paso) ───────────────────────────────────
        $('#compraForm').on('submit', function (e) {
            e.preventDefault();

            if (!validateStep(1)) { showStep(1); return; }

            var items = collectItems();
            if (items === false) { showStep(2); return; }

            var editId = $('#c-edit-id').val();
            var isEdit = !!editId;

            var payload = {
                proveedor_id:      $('#c-proveedor').val(),
                numero_factura:    $('#c-factura').val() || null,
                fecha_compra:      $('#c-fecha').val(),
                tasa_cambio:       $('#c-tasa').val(),
                observaciones:     $('#c-observaciones').val() || null,
                items:             items
            };

            var $btn = $('#c-submit-btn');
            $btn.attr('disabled', true).html(
                '<i class="ri-loader-4-line me-1"></i>' + (isEdit ? 'Actualizando...' : 'Registrando...')
            );

            $.ajax({
                url:         isEdit ? '/compras/' + editId : "{{ route('compras.store') }}",
                method:      isEdit ? 'PUT' : 'POST',
                contentType: 'application/json',
                headers:     { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data:        JSON.stringify(payload),
                success: function (response) {
                    if (response.success) {
                        $('#createCompraModal').modal('hide');
                        if (window.comprasTable) window.comprasTable.ajax.reload(null, false);

                        if (isEdit) {
                            Swal.fire({ title: '¡Actualizada!', text: response.message, icon: 'success', timer: 2000, showConfirmButton: false });
                        } else {
                            Swal.fire({
                                title: '¡Registrada!',
                                text:  response.message,
                                icon:  'success',
                                showCancelButton: true,
                                confirmButtonText: 'Ver detalle',
                                cancelButtonText:  'Continuar'
                            }).then(function (result) {
                                if (result.isConfirmed && window.verDetalleCompra) {
                                    window.verDetalleCompra(response.compra_id);
                                }
                            });
                        }
                    }
                },
                error: function (xhr) {
                    $btn.removeAttr('disabled').html(
                        '<i class="ri-save-line me-1"></i>' + (isEdit ? 'Actualizar Compra' : 'Registrar Compra')
                    );
                    var json = xhr.responseJSON;
                    var msg  = 'Ocurrió un error al ' + (isEdit ? 'actualizar' : 'registrar') + ' la compra.';
                    if (json && json.errors) {
                        msg = Object.values(json.errors).flat().join('<br>');
                    } else if (json && json.message) {
                        msg = json.message;
                    }
                    Swal.fire({ title: 'Error', html: msg, icon: 'error', confirmButtonText: 'Entendido' });
                }
            });
        });

        // ════════════════════════════════════════════════════════════════════
        //  MINI-MODAL: crear proveedor nuevo inline (réplica del maestro)
        // ════════════════════════════════════════════════════════════════════

        // Poblar los <select> de Estado desde municipios-venezuela.js (una sola vez)
        (function poblarEstados() {
            if (typeof municipiosVenezuela === 'undefined') return;
            var estados = Object.keys(municipiosVenezuela).sort(function (a, b) {
                return a.localeCompare(b, 'es');
            });
            var opts = estados.map(function (e) { return '<option value="' + e + '">' + e + '</option>'; }).join('');
            $('.cpr-estado-select').each(function () {
                $(this).append(opts);
            });
        })();

        // Cascada Estado → Municipio (ambos bloques, vía data-ciudad-target)
        $('.cpr-estado-select').on('change', function () {
            var $ciudad = $($(this).data('ciudad-target'));
            $ciudad.empty();
            if (typeof getMunicipios !== 'function' || !this.value) {
                $ciudad.append('<option value="">Primero seleccione un estado</option>');
                return;
            }
            $ciudad.append('<option value="">Seleccione municipio</option>');
            getMunicipios(this.value).forEach(function (m) {
                $ciudad.append('<option value="' + m + '">' + m + '</option>');
            });
        });

        // Toggle Jurídico / Natural según el select de tipo
        function cprToggleCampos() {
            var tipo = $('#cpr-tipo-proveedor-field').val();
            var esJur = tipo === 'juridico';
            $('#cpr-campos-juridico, .cpr-tipo-juridico').toggle(esJur);
            $('#cpr-campos-natural, .cpr-tipo-natural').toggle(!esJur);
        }
        $('#cpr-tipo-proveedor-field').on('change', cprToggleCampos);

        function validarCprForm() {
            var valido = true;
            var tipo = $('#cpr-tipo-proveedor-field').val();
            if (!tipo) {
                marcarInvalido($('#cpr-tipo-proveedor-field'), 'Seleccione el tipo de proveedor.'); valido = false;
            } else { marcarValido($('#cpr-tipo-proveedor-field')); }
            if (tipo === 'juridico') {
                if (!$('#cpr-razon-social-field').val().trim()) {
                    marcarInvalido($('#cpr-razon-social-field'), 'La razón social es requerida.'); valido = false;
                } else { marcarValido($('#cpr-razon-social-field')); }
                if (!$('#cpr-telefono-jur-number-field').val().trim()) {
                    marcarInvalido($('#cpr-telefono-jur-number-field'), 'El teléfono es requerido.'); valido = false;
                } else { marcarValido($('#cpr-telefono-jur-number-field')); }
            } else if (tipo === 'natural') {
                if (!$('#cpr-nombre-field').val().trim()) {
                    marcarInvalido($('#cpr-nombre-field'), 'El nombre es requerido.'); valido = false;
                } else { marcarValido($('#cpr-nombre-field')); }
                if (!$('#cpr-apellido-field').val().trim()) {
                    marcarInvalido($('#cpr-apellido-field'), 'El apellido es requerido.'); valido = false;
                } else { marcarValido($('#cpr-apellido-field')); }
                if (!$('#cpr-telefono-nat-number-field').val().trim()) {
                    marcarInvalido($('#cpr-telefono-nat-number-field'), 'El teléfono es requerido.'); valido = false;
                } else { marcarValido($('#cpr-telefono-nat-number-field')); }
            }
            return valido;
        }

        // Blur/change en tiempo real — cprForm (solo valida campos del tipo activo)
        $('#cpr-tipo-proveedor-field').on('change', function () {
            if (!$(this).val()) marcarInvalido($(this), 'Seleccione el tipo de proveedor.');
            else marcarValido($(this));
        });
        $('#cpr-razon-social-field').on('blur', function () {
            if ($('#cpr-tipo-proveedor-field').val() !== 'juridico') return;
            if (!$(this).val().trim()) marcarInvalido($(this), 'La razón social es requerida.');
            else marcarValido($(this));
        });
        $('#cpr-telefono-jur-number-field').on('blur', function () {
            if ($('#cpr-tipo-proveedor-field').val() !== 'juridico') return;
            if (!$(this).val().trim()) marcarInvalido($(this), 'El teléfono es requerido.');
            else marcarValido($(this));
        });
        $('#cpr-nombre-field').on('blur', function () {
            if ($('#cpr-tipo-proveedor-field').val() !== 'natural') return;
            if (!$(this).val().trim()) marcarInvalido($(this), 'El nombre es requerido.');
            else marcarValido($(this));
        });
        $('#cpr-apellido-field').on('blur', function () {
            if ($('#cpr-tipo-proveedor-field').val() !== 'natural') return;
            if (!$(this).val().trim()) marcarInvalido($(this), 'El apellido es requerido.');
            else marcarValido($(this));
        });
        $('#cpr-telefono-nat-number-field').on('blur', function () {
            if ($('#cpr-tipo-proveedor-field').val() !== 'natural') return;
            if (!$(this).val().trim()) marcarInvalido($(this), 'El teléfono es requerido.');
            else marcarValido($(this));
        });

        $('#crearProveedorRapidoModal').on('hidden.bs.modal', function () {
            $('#cpr-tipo-proveedor-field, #cpr-razon-social-field, #cpr-telefono-jur-number-field, ' +
              '#cpr-nombre-field, #cpr-apellido-field, #cpr-telefono-nat-number-field').each(function () {
                limpiarValidacion($(this));
            });
        });

        // Abrir el mini-modal, prellenando el documento escrito en el buscador
        $('#c-prov-create-btn').on('click', function () {
            $('#cprForm')[0].reset();
            $('#cpr-ciudad-jur-field, #cpr-ciudad-field').empty()
                .append('<option value="">Primero seleccione un estado</option>');

            var prefix = $('#c-prov-doc-prefix').val();
            var number = $('#c-prov-doc-number').val().trim();
            if (prefix === 'V-' || prefix === 'E-') {
                $('#cpr-tipo-proveedor-field').val('natural');
                $('#cpr-tipo-documento-field').val(prefix);
                $('#cpr-documento-identidad-field').val(number);
            } else {
                $('#cpr-tipo-proveedor-field').val('juridico');
                $('#cpr-rif-prefix-field').val(prefix === 'G-' ? 'G-' : 'J-');
                $('#cpr-rif-number-field').val(number);
            }
            cprToggleCampos();
            $('#c-prov-autocomplete').empty().hide();
            $('#crearProveedorRapidoModal').modal('show');
        });

        $('#cprForm').on('submit', function (e) {
            e.preventDefault();
            if (!validarCprForm()) return;
            var tipo = $('#cpr-tipo-proveedor-field').val();
            var payload = { _token: CSRF, tipo_proveedor: tipo };

            if (tipo === 'juridico') {
                payload.rif               = $('#cpr-rif-prefix-field').val() + $('#cpr-rif-number-field').val().trim();
                payload.razon_social      = $('#cpr-razon-social-field').val().trim();
                payload.direccion         = $('#cpr-direccion-jur-field').val().trim();
                payload.telefono          = $('#cpr-telefono-jur-prefix-field').val() + '-' + $('#cpr-telefono-jur-number-field').val().trim();
                payload.email             = $('#cpr-email-jur-field').val().trim();
                payload.contacto          = $('#cpr-contacto-field').val().trim() || null;
                payload.telefono_contacto = $('#cpr-telefono-contacto-number-field').val().trim()
                    ? $('#cpr-telefono-contacto-prefix-field').val() + '-' + $('#cpr-telefono-contacto-number-field').val().trim()
                    : null;
                payload.estado_territorial = $('#cpr-estado-territorial-jur-field').val();
                payload.ciudad             = $('#cpr-ciudad-jur-field').val();
            } else {
                payload.tipo_documento      = $('#cpr-tipo-documento-field').val();
                payload.documento_identidad = $('#cpr-documento-identidad-field').val().trim();
                payload.nombre              = $('#cpr-nombre-field').val().trim();
                payload.apellido            = $('#cpr-apellido-field').val().trim();
                payload.direccion           = $('#cpr-direccion-nat-field').val().trim();
                payload.telefono            = $('#cpr-telefono-nat-prefix-field').val() + '-' + $('#cpr-telefono-nat-number-field').val().trim();
                payload.email               = $('#cpr-email-nat-field').val().trim();
                payload.estado_territorial  = $('#cpr-estado-territorial-field').val();
                payload.ciudad              = $('#cpr-ciudad-field').val();
            }

            var $btn = $('#cpr-submit-btn');
            $btn.attr('disabled', true).html('<i class="ri-loader-4-line me-1"></i>Guardando...');

            $.ajax({
                url:    "{{ route('proveedores.store') }}",
                method: 'POST',
                data:   payload,
                success: function (resp) {
                    $btn.removeAttr('disabled').html('<i class="ri-save-line me-1"></i>Guardar y seleccionar');
                    $('#crearProveedorRapidoModal').modal('hide');
                    if (resp.proveedor) {
                        aplicarProveedor(resp.proveedor);
                    }
                    Swal.fire({ icon: 'success', title: 'Proveedor creado', text: resp.success || 'Proveedor creado correctamente.', showConfirmButton: false, timer: 1600 });
                },
                error: function (xhr) {
                    $btn.removeAttr('disabled').html('<i class="ri-save-line me-1"></i>Guardar y seleccionar');
                    var json = xhr.responseJSON;
                    var msg = 'No se pudo crear el proveedor.';
                    if (json && json.errors) { msg = Object.values(json.errors).flat().join('<br>'); }
                    else if (json && json.message) { msg = json.message; }
                    Swal.fire({ title: 'Error', html: msg, icon: 'error', confirmButtonText: 'Entendido' });
                }
            });
        });

        // ════════════════════════════════════════════════════════════════════
        //  MINI-MODAL: crear insumo nuevo inline (alta rápida del maestro)
        // ════════════════════════════════════════════════════════════════════
        var cirTargetSelect = null;

        // Abrir desde el "+" de una fila — recuerda el <select> a auto-seleccionar
        $(document).on('click', '.c-add-insumo-btn', function () {
            cirTargetSelect = $('#c-ins-' + $(this).data('row'));
            $('#cirForm')[0].reset();
            $('#cir-nombre-field, #cir-tipo-field, #cir-unidad-field, #cir-costo-field').each(function () {
                limpiarValidacion($(this));
            });
            $('#crearInsumoRapidoModal').modal('show');
        });

        $('#crearInsumoRapidoModal').on('hidden.bs.modal', function () {
            $('#cir-nombre-field, #cir-tipo-field, #cir-unidad-field, #cir-costo-field').each(function () {
                limpiarValidacion($(this));
            });
        });

        // Código: solo MAYÚSCULAS y alfanumérico (text-uppercase es solo visual)
        $('#cir-codigo-field').on('input', function () {
            this.value = this.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        });

        // Blur/change en tiempo real — cirForm
        $('#cir-nombre-field').on('blur', function () {
            if ($(this).val().trim().length < 3) marcarInvalido($(this), 'El nombre debe tener al menos 3 caracteres.');
            else marcarValido($(this));
        });
        $('#cir-tipo-field').on('change', function () {
            if (!$(this).val()) marcarInvalido($(this), 'Seleccione el tipo de insumo.');
            else marcarValido($(this));
        });
        $('#cir-unidad-field').on('change', function () {
            if (!$(this).val()) marcarInvalido($(this), 'Seleccione la unidad de medida.');
            else marcarValido($(this));
        });
        $('#cir-costo-field').on('blur', function () {
            var v = parseFloat($(this).val());
            if (isNaN(v) || v <= 0) marcarInvalido($(this), 'Ingrese un costo válido (mayor a 0).');
            else marcarValido($(this));
        });

        function validarCirForm() {
            var valido = true;
            if ($('#cir-nombre-field').val().trim().length < 3) {
                marcarInvalido($('#cir-nombre-field'), 'El nombre debe tener al menos 3 caracteres.'); valido = false;
            } else { marcarValido($('#cir-nombre-field')); }
            if (!$('#cir-tipo-field').val()) {
                marcarInvalido($('#cir-tipo-field'), 'Seleccione el tipo de insumo.'); valido = false;
            } else { marcarValido($('#cir-tipo-field')); }
            if (!$('#cir-unidad-field').val()) {
                marcarInvalido($('#cir-unidad-field'), 'Seleccione la unidad de medida.'); valido = false;
            } else { marcarValido($('#cir-unidad-field')); }
            var costo = parseFloat($('#cir-costo-field').val());
            if (isNaN(costo) || costo <= 0) {
                marcarInvalido($('#cir-costo-field'), 'Ingrese un costo válido (mayor a 0).'); valido = false;
            } else { marcarValido($('#cir-costo-field')); }
            return valido;
        }

        $('#cirForm').on('submit', function (e) {
            e.preventDefault();

            if (!validarCirForm()) return;

            var nombre = $('#cir-nombre-field').val().trim();
            var codigo = $('#cir-codigo-field').val().trim();
            var tipo   = $('#cir-tipo-field').val();
            var unidad = $('#cir-unidad-field').val();
            var costo  = parseFloat($('#cir-costo-field').val());

            var payload = {
                _token:           CSRF,
                nombre:           nombre,
                tipo:             tipo,
                unidad_medida:    unidad,
                costo_unitario:   costo,
                aplica_iva:       $('#cir-aplica-iva-field').is(':checked') ? 1 : 0,
                is_inventoriable: 1,
                stock_actual:     0,
                stock_minimo:     0,
                stock_maximo:     0
            };
            if (codigo) payload.codigo = codigo;

            var $btn = $('#cir-submit-btn');
            $btn.attr('disabled', true).html('<i class="ri-loader-4-line me-1"></i>Guardando...');

            $.ajax({
                url:    "{{ route('insumos.store') }}",
                method: 'POST',
                data:   payload,
                success: function (resp) {
                    $btn.removeAttr('disabled').html('<i class="ri-save-line me-1"></i>Guardar y seleccionar');
                    var ins = resp.insumo;
                    if (ins) {
                        // Disponible para esta y futuras filas, sin recargar
                        INSUMOS.push({
                            id:             ins.id,
                            nombre:         ins.nombre,
                            codigo:         ins.codigo,
                            tipo:           ins.tipo,
                            unidad_medida:  ins.unidad_medida,
                            costo_unitario: ins.costo_unitario,
                            aplica_iva:     ins.aplica_iva
                        });
                        rebuildAllInsumoSelects();
                        if (cirTargetSelect && cirTargetSelect.length) {
                            cirTargetSelect.val(ins.id).trigger('change');
                        }
                    }
                    $('#crearInsumoRapidoModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Insumo creado',
                        text: 'Se agregó "' + (ins ? ins.nombre : '') + '" y quedó seleccionado.',
                        showConfirmButton: false, timer: 1600 });
                },
                error: function (xhr) {
                    $btn.removeAttr('disabled').html('<i class="ri-save-line me-1"></i>Guardar y seleccionar');
                    var json = xhr.responseJSON;
                    var msg  = 'No se pudo crear el insumo.';
                    if (json && json.errors) { msg = Object.values(json.errors).flat().join('<br>'); }
                    else if (json && json.message) { msg = json.message; }
                    Swal.fire({ title: 'Error', html: msg, icon: 'error', confirmButtonText: 'Entendido' });
                }
            });
        });

        // ════════════════════════════════════════════════════════════════════
        //  MODAL DE BÚSQUEDA DE PROVEEDOR (lupa del paso 1)
        // ════════════════════════════════════════════════════════════════════
        (function () {
            var bspTimeout = null;

            function bspRender(proveedores) {
                var $tbody = $('#bsp-tbody');
                var $empty = $('#bsp-empty');
                var $label = $('#bsp-count-label');
                $tbody.empty();

                if (!proveedores || proveedores.length === 0) {
                    $empty.removeClass('d-none');
                    $label.text('');
                    return;
                }

                $empty.addClass('d-none');
                $label.text(proveedores.length + ' proveedor(es) encontrado(s)');

                proveedores.forEach(function (p) {
                    var tipoBadge = p.tipo === 'juridico'
                        ? '<span class="badge bg-soft-primary text-primary">Jurídico</span>'
                        : '<span class="badge bg-soft-success text-success">Natural</span>';

                    var $tr = $('<tr style="cursor:pointer;">')
                        .html(
                            '<td class="fw-semibold">' + p.nombre + '</td>'
                            + '<td class="font-monospace small">' + (p.doc || '—') + '</td>'
                            + '<td>' + tipoBadge + '</td>'
                            + '<td class="text-muted small">' + (p.tel || '—') + '</td>'
                            + '<td class="text-center"><span class="badge bg-light text-dark">' + p.compras + '</span></td>'
                        )
                        .on('click', function () {
                            aplicarProveedor(p);
                            $('#buscarProveedorModal').modal('hide');
                            $('#bsp-input').val('');
                        });
                    $tbody.append($tr);
                });
            }

            function bspSearch(q) {
                $('#bsp-loading').removeClass('d-none');
                $('#bsp-empty').addClass('d-none');
                $.ajax({
                    url: '{{ route("proveedores.search") }}',
                    data: { q: q },
                    success: function (data) {
                        $('#bsp-loading').addClass('d-none');
                        bspRender(data);
                    },
                    error: function () {
                        $('#bsp-loading').addClass('d-none');
                    }
                });
            }

            // Abrir el modal hijo vía JS (no data-bs-toggle) para no cerrar el padre
            // — ver docs/conventions/nested-modals.md
            $('#c-prov-browse-btn').on('click', function () {
                $('#buscarProveedorModal').modal('show');
            });

            $('#buscarProveedorModal').on('shown.bs.modal', function () {
                $('#bsp-input').trigger('focus');
                if ($('#bsp-tbody tr').length === 0) {
                    bspSearch('');
                }
            }).on('hidden.bs.modal', function () {
                clearTimeout(bspTimeout);
            });

            $('#bsp-input').on('input', function () {
                clearTimeout(bspTimeout);
                bspTimeout = setTimeout(function () {
                    bspSearch($('#bsp-input').val().trim());
                }, 300);
            });

            $('#bsp-clear-btn').on('click', function () {
                $('#bsp-input').val('').trigger('focus');
                bspSearch('');
            });
        })();

        // Exponer API mínima por consistencia con otros wizards
        window.compraWizard = { show: showStep, next: nextStep, prev: prevStep, populate: populate };
    })();
});
</script>
