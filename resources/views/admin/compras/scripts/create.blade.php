<script>
$(document).ready(function () {

    var rowCount = 0;
    var INSUMOS  = window.INSUMOS_DATA || [];

    // ── Select2 en proveedor ────────────────────────────────────────────────
    $('#c-proveedor').select2({
        theme: 'bootstrap-5',
        placeholder: 'Seleccione un proveedor...',
        width: '100%',
        dropdownParent: $('#createCompraModal')
    });

    // ── Mostrar / ocultar fecha de vencimiento (requerida en crédito) ─────────
    $('#c-tipo-pago').on('change', function () {
        if ($(this).val() === 'credito') {
            $('#c-vencimiento-wrap').slideDown(150);
            $('#c-vencimiento').attr('required', true);
        } else {
            $('#c-vencimiento-wrap').slideUp(150);
            $('#c-vencimiento').val('').removeAttr('required');
        }
    });

    // ── Control segmentado de Tipo de Pago (el <select> oculto es la verdad) ──
    function syncPagoSeg(value) {
        $('#c-pago-seg .seg-option').each(function () {
            var active = $(this).data('value') === value;
            $(this).toggleClass('is-active', active).attr('aria-checked', active ? 'true' : 'false');
        });
    }
    $('#c-pago-seg').on('click', '.seg-option', function () {
        var value = $(this).data('value');
        syncPagoSeg(value);
        $('#c-tipo-pago').val(value).trigger('change');
    });

    // ── Utilidades ──────────────────────────────────────────────────────────
    function fmt(n) {
        return parseFloat(n || 0).toFixed(2);
    }

    function buildInsumoOptions(selectedId) {
        var html = '<option value="">Seleccione insumo...</option>';
        INSUMOS.forEach(function (ins) {
            var sel = (ins.id == selectedId) ? ' selected' : '';
            html += '<option value="' + ins.id + '"'
                + ' data-costo="' + ins.costo_unitario + '"'
                + ' data-unidad="' + ins.unidad_medida + '"'
                + sel + '>'
                + ins.nombre + ' (' + ins.tipo + ')'
                + '</option>';
        });
        return html;
    }

    function recalcular() {
        var subtotal = 0;
        $('#c-items-tbody tr').each(function () {
            var cantidad = parseFloat($(this).find('.c-cantidad').val()) || 0;
            var costo    = parseFloat($(this).find('.c-costo').val())    || 0;
            var sub      = cantidad * costo;
            $(this).find('.c-subtotal').text(fmt(sub));
            subtotal += sub;
        });
        var ivaPct = parseFloat($('#c-iva').val()) || 0;
        var iva    = subtotal * ivaPct / 100;
        $('#c-resumen-subtotal').text(fmt(subtotal));
        $('#c-resumen-iva').text(fmt(iva));
        $('#c-resumen-total').text(fmt(subtotal + iva));
        $('#c-resumen-iva-pct').text(ivaPct);
        // Pie en vivo del paso 2
        $('#c-items-footer-subtotal').text(fmt(subtotal));
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
            $('#c-items-footer').attr('hidden', true);
        } else {
            $('#c-items-empty').hide();
            $('#c-items-table-wrap').removeAttr('hidden');
            $('#c-items-footer').removeAttr('hidden');
        }
    }

    // ── Agregar fila ────────────────────────────────────────────────────────
    function addItemRow() {
        var idx = rowCount++;
        var num = $('#c-items-tbody tr').length + 1;

        var row = '<tr id="c-row-' + idx + '">'
            + '<td class="c-row-num text-center text-muted cot-col-num">' + num + '</td>'
            + '<td style="min-width:160px;">'
            +   '<select class="form-select form-select-sm c-insumo" id="c-ins-' + idx + '">'
            +     buildInsumoOptions('')
            +   '</select>'
            + '</td>'
            + '<td class="text-center">'
            +   '<div class="input-group input-group-sm" style="max-width:128px;margin:0 auto;">'
            +     '<input type="number" class="form-control form-control-sm c-cantidad text-center"'
            +     ' min="0.01" step="0.01" placeholder="0.00">'
            +     '<span class="input-group-text c-unit-addon">—</span>'
            +   '</div>'
            + '</td>'
            + '<td class="text-center">'
            +   '<input type="number" class="form-control form-control-sm c-costo text-end"'
            +   ' min="0.01" step="0.01" placeholder="0.00" style="max-width:105px;margin:0 auto;">'
            + '</td>'
            + '<td class="text-end fw-semibold c-subtotal pe-2">0.00</td>'
            + '<td class="text-center">'
            +   '<button type="button" class="btn btn-sm btn-soft-danger py-0 px-1 c-remove-btn"'
            +   ' data-row="' + idx + '" title="Quitar"><i class="ri-delete-bin-6-line"></i></button>'
            + '</td>'
            + '</tr>';

        $('#c-items-tbody').append(row);
        updateEmpty();

        $('#c-ins-' + idx).on('change', function () {
            var opt   = $(this).find('option:selected');
            var costo = opt.data('costo');
            var unid  = opt.data('unidad');
            var $tr   = $(this).closest('tr');
            if (costo) { $tr.find('.c-costo').val(fmt(costo)); }
            $tr.find('.c-unit-addon').text(unid ? String(unid).substring(0, 4) : '—');
            recalcular();
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

    // ── Recalcular al editar ────────────────────────────────────────────────
    $(document).on('input', '.c-cantidad, .c-costo', recalcular);
    $('#c-iva').on('input', recalcular);

    // ══════════════════════════════════════════════════════════════════════
    //  WIZARD — navegación 3 pasos (scaffold .wiz-*)
    // ══════════════════════════════════════════════════════════════════════
    (function () {
        'use strict';
        var TOTAL_STEPS = 3;
        var currentStep = 1;

        // ── Render del proveedor: card de datos (paso 1) + chip persistente ──
        var PROVEEDORES = window.PROVEEDORES_DATA || {};

        function iniciales(name) {
            return name.split(/\s+/).slice(0, 2).map(function (w) { return w.charAt(0); }).join('').toUpperCase();
        }

        function renderProveedorSeleccionado() {
            var id = $('#c-proveedor').val();

            // Sin proveedor → empty state, sin card, sin chip
            if (!id) {
                $('#c-prov-empty').show();
                $('#c-prov-card').attr('hidden', true).hide();
                $('#c-prov-banner').attr('hidden', true).attr('aria-hidden', 'true');
                return;
            }

            var data = PROVEEDORES[id] || {};
            var name = data.nombre || $('#c-proveedor').find('option:selected').text().trim();
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
            var provName = $('#c-proveedor').find('option:selected').text().trim();
            $('#c-recap-proveedor').text($('#c-proveedor').val() ? provName : '—');
            $('#c-recap-factura').text($('#c-factura').val() || 'S/N');
            var fecha = $('#c-fecha').val();
            if (fecha) {
                var p = fecha.split('-');
                $('#c-recap-fecha').text(p[2] + '/' + p[1] + '/' + p[0]);
            } else {
                $('#c-recap-fecha').text('—');
            }
            var pago = $('#c-tipo-pago').val() === 'credito' ? 'Crédito' : 'Contado';
            if ($('#c-tipo-pago').val() === 'credito' && $('#c-vencimiento').val()) {
                var v = $('#c-vencimiento').val().split('-');
                pago += ' · vence ' + v[2] + '/' + v[1] + '/' + v[0];
            }
            $('#c-recap-pago').text(pago);
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
                    Swal.fire({ title: 'Campo requerido', text: 'Seleccione un proveedor.', icon: 'warning', confirmButtonText: 'Entendido' });
                    return false;
                }
                if (!$('#c-fecha').val()) {
                    Swal.fire({ title: 'Campo requerido', text: 'Ingrese la fecha de compra.', icon: 'warning', confirmButtonText: 'Entendido' });
                    return false;
                }
                if ($('#c-tipo-pago').val() === 'credito' && !$('#c-vencimiento').val()) {
                    Swal.fire({ title: 'Campo requerido', text: 'Ingrese la fecha de vencimiento para pagos a crédito.', icon: 'warning', confirmButtonText: 'Entendido' });
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

            $('#c-items-tbody tr').each(function () {
                var insumoId = $(this).find('.c-insumo').val();
                var cantidad = $(this).find('.c-cantidad').val();
                var costo    = $(this).find('.c-costo').val();

                if (!insumoId || !cantidad || parseFloat(cantidad) <= 0 || !costo || parseFloat(costo) <= 0) {
                    err = 'incompleto';
                    return false;
                }
                if (insumoIds.indexOf(insumoId) !== -1) {
                    err = 'dup';
                    return false;
                }
                insumoIds.push(insumoId);
                items.push({ insumo_id: insumoId, cantidad: cantidad, costo_unitario: costo });
            });

            if (items.length === 0) {
                Swal.fire({ title: 'Sin ítems', text: 'Agregue al menos un insumo a la compra.', icon: 'warning', confirmButtonText: 'Entendido' });
                return false;
            }
            if (err === 'dup') {
                Swal.fire({ title: 'Insumo duplicado', text: 'Hay insumos repetidos en la lista. Cada insumo puede aparecer una sola vez.', icon: 'warning', confirmButtonText: 'Entendido' });
                return false;
            }
            if (err === 'incompleto') {
                Swal.fire({ title: 'Datos incompletos', text: 'Complete todos los campos de cada ítem (insumo, cantidad y costo).', icon: 'warning', confirmButtonText: 'Entendido' });
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

        // Mantener card, chip y recap sincronizados con el proveedor
        $('#c-proveedor').on('change', renderProveedorSeleccionado);

        // ── Reset al cerrar el modal ─────────────────────────────────────────
        function resetModal() {
            rowCount = 0;
            $('#compraForm')[0].reset();
            $('#c-proveedor').val('').trigger('change.select2');
            syncPagoSeg('contado');
            $('#c-tipo-pago').val('contado').trigger('change');
            $('#c-fecha').val('{{ date("Y-m-d") }}');
            $('#c-iva').val(16);
            $('#c-items-tbody').empty();
            updateEmpty();
            recalcular();
            renderProveedorSeleccionado();
            $('#c-submit-btn').removeAttr('disabled').html('<i class="ri-save-line me-1"></i>Registrar Compra');
        }

        // Estado inicial: fija display:none inline en la card (vence al display:flex)
        renderProveedorSeleccionado();

        // ── Lifecycle del modal ──────────────────────────────────────────────
        $('#createCompraModal')
            .on('show.bs.modal', function () { showStep(1); })
            .on('hidden.bs.modal', function () { resetModal(); currentStep = 1; });

        // ── Envío (solo en el último paso) ───────────────────────────────────
        $('#compraForm').on('submit', function (e) {
            e.preventDefault();

            if (!validateStep(1)) { showStep(1); return; }

            var items = collectItems();
            if (items === false) { showStep(2); return; }

            var payload = {
                proveedor_id:      $('#c-proveedor').val(),
                numero_factura:    $('#c-factura').val() || null,
                fecha_compra:      $('#c-fecha').val(),
                fecha_vencimiento: $('#c-vencimiento').val() || null,
                tipo_pago:         $('#c-tipo-pago').val(),
                iva_porcentaje:    $('#c-iva').val(),
                observaciones:     $('#c-observaciones').val() || null,
                items:             items
            };

            var $btn = $('#c-submit-btn');
            $btn.attr('disabled', true).html('<i class="ri-loader-4-line me-1"></i>Registrando...');

            $.ajax({
                url:         "{{ route('compras.store') }}",
                method:      'POST',
                contentType: 'application/json',
                headers:     { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data:        JSON.stringify(payload),
                success: function (response) {
                    if (response.success) {
                        $('#createCompraModal').modal('hide');
                        if (window.comprasTable) window.comprasTable.ajax.reload(null, false);

                        Swal.fire({
                            title: '¡Registrada!',
                            text:  response.message,
                            icon:  'success',
                            showCancelButton: true,
                            confirmButtonText: 'Ver detalle',
                            cancelButtonText:  'Continuar'
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                window.location.href = "{{ url('compras') }}/" + response.compra_id;
                            }
                        });
                    }
                },
                error: function (xhr) {
                    $btn.removeAttr('disabled').html('<i class="ri-save-line me-1"></i>Registrar Compra');
                    var json = xhr.responseJSON;
                    var msg  = 'Ocurrió un error al registrar la compra.';
                    if (json && json.errors) {
                        msg = Object.values(json.errors).flat().join('<br>');
                    } else if (json && json.message) {
                        msg = json.message;
                    }
                    Swal.fire({ title: 'Error', html: msg, icon: 'error', confirmButtonText: 'Entendido' });
                }
            });
        });

        // Exponer API mínima por consistencia con otros wizards
        window.compraWizard = { show: showStep, next: nextStep, prev: prevStep };
    })();
});
</script>
