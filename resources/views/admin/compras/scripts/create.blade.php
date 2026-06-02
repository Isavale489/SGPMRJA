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

    // ── Mostrar / ocultar fecha de vencimiento ──────────────────────────────
    $('#c-tipo-pago').on('change', function () {
        if ($(this).val() === 'credito') {
            $('#c-vencimiento-wrap').show();
        } else {
            $('#c-vencimiento-wrap').hide();
            $('#c-vencimiento').val('');
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
    }

    function renumber() {
        $('#c-items-tbody tr').each(function (i) {
            $(this).find('.c-row-num').text(i + 1);
        });
    }

    function updateEmpty() {
        var count = $('#c-items-tbody tr').length;
        $('#c-items-count').text('(' + count + ')');
        if (count === 0) {
            $('#c-items-empty').show();
            $('#c-items-table-wrap').attr('hidden', true);
        } else {
            $('#c-items-empty').hide();
            $('#c-items-table-wrap').removeAttr('hidden');
        }
    }

    function resetModal() {
        rowCount = 0;
        $('#compraForm')[0].reset();
        $('#c-proveedor').val('').trigger('change');
        $('#c-tipo-pago').val('contado').trigger('change');
        $('#c-fecha').val('{{ date("Y-m-d") }}');
        $('#c-iva').val(16);
        $('#c-items-tbody').empty();
        updateEmpty();
        recalcular();
        $('#c-submit-btn').removeAttr('disabled').html('<i class="ri-save-line me-1"></i>Registrar Compra');
    }

    // ── Limpiar modal al cerrar ─────────────────────────────────────────────
    $('#createCompraModal').on('hidden.bs.modal', function () {
        resetModal();
    });

    // ── Agregar fila ────────────────────────────────────────────────────────
    $('#c-add-item-btn').on('click', function () {
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
            +   '<input type="number" class="form-control form-control-sm c-cantidad text-center"'
            +   ' min="0.01" step="0.01" placeholder="0.00" style="max-width:86px;margin:0 auto;">'
            + '</td>'
            + '<td class="text-center">'
            +   '<input type="number" class="form-control form-control-sm c-costo text-end"'
            +   ' min="0.01" step="0.01" placeholder="0.00" style="max-width:100px;margin:0 auto;">'
            + '</td>'
            + '<td class="text-end fw-semibold c-subtotal pe-2">0.00</td>'
            + '<td class="text-center">'
            +   '<button type="button" class="btn btn-sm btn-soft-danger py-0 px-1 c-remove-btn"'
            +   ' data-row="' + idx + '"><i class="ri-delete-bin-6-line"></i></button>'
            + '</td>'
            + '</tr>';

        $('#c-items-tbody').append(row);
        updateEmpty();

        $('#c-ins-' + idx).on('change', function () {
            var opt = $(this).find('option:selected');
            var costo = opt.data('costo');
            if (costo) {
                $(this).closest('tr').find('.c-costo').val(fmt(costo));
            }
            recalcular();
        });
    });

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

    // ── Envío ───────────────────────────────────────────────────────────────
    $('#compraForm').on('submit', function (e) {
        e.preventDefault();

        if (!$('#c-proveedor').val()) {
            Swal.fire({ title: 'Campo requerido', text: 'Seleccione un proveedor.', icon: 'warning', confirmButtonText: 'Entendido' });
            return;
        }
        if (!$('#c-fecha').val()) {
            Swal.fire({ title: 'Campo requerido', text: 'Ingrese la fecha de compra.', icon: 'warning', confirmButtonText: 'Entendido' });
            return;
        }

        var items  = [];
        var hasErr = false;

        $('#c-items-tbody tr').each(function () {
            var insumoId = $(this).find('.c-insumo').val();
            var cantidad = $(this).find('.c-cantidad').val();
            var costo    = $(this).find('.c-costo').val();

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
            Swal.fire({ title: 'Datos incompletos', text: 'Complete todos los campos de cada ítem.', icon: 'warning', confirmButtonText: 'Entendido' });
            return;
        }

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
                    // Recarga la tabla antes de mostrar el alert para que aparezca el nuevo registro
                    if (typeof table !== 'undefined') table.ajax.reload(null, false);

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
});
</script>
