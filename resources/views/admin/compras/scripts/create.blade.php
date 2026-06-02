<script>
$(document).ready(function () {

    var rowCount  = 0;
    var INSUMOS   = window.INSUMOS_DATA || [];

    // ── Select2 en proveedor ────────────────────────────────────────────────
    $('#proveedor_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Seleccione un proveedor...',
        width: '100%'
    });

    // ── Mostrar / ocultar fecha de vencimiento ──────────────────────────────
    $('#tipo_pago').on('change', function () {
        if ($(this).val() === 'credito') {
            $('#vencimiento-wrapper').show();
        } else {
            $('#vencimiento-wrapper').hide();
            $('#fecha_vencimiento').val('');
        }
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

        $('#items-tbody tr').each(function () {
            var cantidad = parseFloat($(this).find('.cantidad-input').val()) || 0;
            var costo    = parseFloat($(this).find('.costo-input').val())    || 0;
            var sub      = cantidad * costo;
            $(this).find('.subtotal-cell').text(fmt(sub));
            subtotal += sub;
        });

        var ivaPct = parseFloat($('#iva_porcentaje').val()) || 0;
        var iva    = subtotal * ivaPct / 100;
        var total  = subtotal + iva;

        $('#resumen-subtotal').text(fmt(subtotal));
        $('#resumen-iva').text(fmt(iva));
        $('#resumen-total').text(fmt(total));
        $('#resumen-iva-pct').text(ivaPct);
    }

    function renumber() {
        $('#items-tbody tr').each(function (i) {
            $(this).find('.row-num').text(i + 1);
        });
    }

    function updateEmpty() {
        var count = $('#items-tbody tr').length;
        $('#items-count').text('(' + count + ')');
        if (count === 0) {
            $('#items-empty').show();
            $('#items-table-wrap').attr('hidden', true);
        } else {
            $('#items-empty').hide();
            $('#items-table-wrap').removeAttr('hidden');
        }
    }

    // ── Agregar fila ────────────────────────────────────────────────────────
    $('#add-item-btn').on('click', function () {
        var idx  = rowCount++;
        var num  = $('#items-tbody tr').length + 1;

        var row = '<tr id="item-row-' + idx + '">'
            + '<td class="row-num text-center text-muted cot-col-num">' + num + '</td>'
            + '<td style="min-width:180px;">'
            +   '<select class="form-select form-select-sm insumo-select" id="insumo-sel-' + idx + '">'
            +     buildInsumoOptions('')
            +   '</select>'
            + '</td>'
            + '<td class="text-center">'
            +   '<input type="number" class="form-control form-control-sm cantidad-input text-center"'
            +   ' min="0.01" step="0.01" placeholder="0.00" style="max-width:90px;margin:0 auto;">'
            + '</td>'
            + '<td class="text-center">'
            +   '<input type="number" class="form-control form-control-sm costo-input text-end"'
            +   ' min="0.01" step="0.01" placeholder="0.00" style="max-width:105px;margin:0 auto;">'
            + '</td>'
            + '<td class="text-end fw-semibold subtotal-cell pe-2">0.00</td>'
            + '<td class="text-center">'
            +   '<button type="button" class="btn btn-sm btn-soft-danger py-0 px-1 remove-item-btn"'
            +   ' data-row="' + idx + '"><i class="ri-delete-bin-6-line"></i></button>'
            + '</td>'
            + '</tr>';

        $('#items-tbody').append(row);
        updateEmpty();

        // Auto-rellenar costo al seleccionar insumo
        $('#insumo-sel-' + idx).on('change', function () {
            var opt   = $(this).find('option:selected');
            var costo = opt.data('costo');
            if (costo) {
                $(this).closest('tr').find('.costo-input').val(fmt(costo));
            }
            recalcular();
        });
    });

    // ── Eliminar fila ───────────────────────────────────────────────────────
    $(document).on('click', '.remove-item-btn', function () {
        $(this).closest('tr').remove();
        renumber();
        updateEmpty();
        recalcular();
    });

    // ── Recalcular al editar ────────────────────────────────────────────────
    $(document).on('input', '.cantidad-input, .costo-input', recalcular);
    $('#iva_porcentaje').on('input', recalcular);

    // ── Envío del formulario ────────────────────────────────────────────────
    $('#compraForm').on('submit', function (e) {
        e.preventDefault();

        // Validaciones básicas
        if (!$('#proveedor_id').val()) {
            Swal.fire({ title: 'Campo requerido', text: 'Seleccione un proveedor.', icon: 'warning', confirmButtonText: 'Entendido' });
            return;
        }
        if (!$('#fecha_compra').val()) {
            Swal.fire({ title: 'Campo requerido', text: 'Ingrese la fecha de compra.', icon: 'warning', confirmButtonText: 'Entendido' });
            return;
        }

        var items  = [];
        var hasErr = false;

        $('#items-tbody tr').each(function () {
            var insumoId = $(this).find('.insumo-select').val();
            var cantidad = $(this).find('.cantidad-input').val();
            var costo    = $(this).find('.costo-input').val();

            if (!insumoId || !cantidad || parseFloat(cantidad) <= 0 || !costo || parseFloat(costo) <= 0) {
                hasErr = true;
                return false;
            }
            items.push({ insumo_id: insumoId, cantidad: cantidad, costo_unitario: costo });
        });

        if (items.length === 0) {
            Swal.fire({ title: 'Sin ítems', text: 'Agregue al menos un insumo a la compra.', icon: 'warning', confirmButtonText: 'Entendido' });
            return;
        }
        if (hasErr) {
            Swal.fire({ title: 'Datos incompletos', text: 'Complete todos los campos de cada ítem (insumo, cantidad y costo).', icon: 'warning', confirmButtonText: 'Entendido' });
            return;
        }

        var payload = {
            proveedor_id:      $('#proveedor_id').val(),
            numero_factura:    $('#numero_factura').val() || null,
            fecha_compra:      $('#fecha_compra').val(),
            fecha_vencimiento: $('#fecha_vencimiento').val() || null,
            tipo_pago:         $('#tipo_pago').val(),
            iva_porcentaje:    $('#iva_porcentaje').val(),
            observaciones:     $('#observaciones').val() || null,
            items:             items
        };

        var $btn = $('#submit-btn');
        $btn.attr('disabled', true).html('<i class="ri-loader-4-line me-1"></i>Registrando...');

        $.ajax({
            url:         window.ROUTES.store,
            method:      'POST',
            contentType: 'application/json',
            headers:     { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data:        JSON.stringify(payload),
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: '¡Registrada!',
                        text:  response.message,
                        icon:  'success',
                        confirmButtonText: 'Ver compra'
                    }).then(function () {
                        window.location.href = window.ROUTES.show + response.compra_id;
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
});
</script>
