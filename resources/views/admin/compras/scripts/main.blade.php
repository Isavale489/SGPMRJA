<script>
$(document).ready(function () {

    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    function updateFilterBadge() {
        let count = 0;
        $('#advanced-filters .navy-filter-select').each(function () {
            if ($(this).val() && $(this).val() !== '') count++;
        });
        $('#active-filter-count').text(count).toggleClass('d-none', count === 0);
    }

    $('#filters-collapse-body')
        .on('show.bs.collapse', function () {
            $('#advanced-filters .navy-filter-header').removeClass('is-collapsed');
        })
        .on('hidden.bs.collapse', function () {
            $('#advanced-filters .navy-filter-header').addClass('is-collapsed');
        });

    // ── DataTable ───────────────────────────────────────────────────────────
    window.comprasTable = $('#compras-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('compras.data') }}",
            data: function (d) {
                d.ver_anuladas       = window.VER_ANULADAS ? 1 : 0;
                d.filter_estado      = $('#filter-estado').val();
                d.filter_fecha_desde = $('#filter-fecha-desde').val();
                d.filter_fecha_hasta = $('#filter-fecha-hasta').val();
            }
        },
        autoWidth: false,
        columns: [
            {
                data: 'id', name: 'id', width: '5%',
                render: function (data) {
                    return '<span class="text-muted">#' + data + '</span>';
                }
            },
            { data: 'proveedor_nombre', name: 'proveedor_nombre', width: '25%', orderable: false },
            {
                data: 'numero_factura', name: 'numero_factura', width: '12%',
                render: function (data) {
                    return data
                        ? '<span style="font-family:monospace;font-size:.82rem;">' + data + '</span>'
                        : '<span class="text-muted fst-italic">S/N</span>';
                }
            },
            { data: 'fecha_formateada', name: 'fecha_compra', width: '12%' },
            {
                data: 'total', name: 'total', width: '14%', className: 'text-end',
                render: function (data) {
                    return parseFloat(data).toFixed(2);
                }
            },
            {
                data: 'estado_badge', name: 'estado', width: '10%',
                orderable: false, searchable: false
            },
            {
                data: 'actions', name: 'actions', width: '16%',
                orderable: false, searchable: false
            }
        ],
        order: [[0, 'desc']],
        dom: 'rtip',
        language: lenguajeData,
        responsive: true
    });

    // ── Filtros y búsqueda ──────────────────────────────────────────────────
    $('#custom-search-input').on('input', debounce(function () {
        window.comprasTable.search(this.value).draw();
    }, 300));

    $('#advanced-filters .navy-filter-select').on('change', function () {
        window.comprasTable.ajax.reload(null, true);
        updateFilterBadge();
    });

    $('#btn-clear-filters').on('click', function () {
        $('#filter-estado').val('');   // 'Activas (todas)'
        $('#filter-fecha-desde').val('');
        $('#filter-fecha-hasta').val('');
        $('#custom-search-input').val('');
        window.comprasTable.search('').ajax.reload(null, true);
        updateFilterBadge();
    });

    updateFilterBadge();

    // ── Ver detalle ─────────────────────────────────────────────────────────
    window.verDetalleCompra = function verDetalleCompra(compraId) {
        var badgeMap = { recibida: 'success', borrador: 'warning', anulada: 'danger' };

        $.ajax({
            url: '/compras/' + compraId + '/detalle',
            method: 'GET',
            success: function (d) {
                // Header y hero
                $('#cv-titulo').html('<i class="ri-shopping-bag-3-line me-1"></i>Compra #' + d.id);
                $('#cv-hero-titulo').text('Compra #' + d.id);
                var badgeColor = badgeMap[d.estado] || 'secondary';
                $('#cv-estado-badge').attr('class', 'badge bg-' + badgeColor).text(
                    d.estado.charAt(0).toUpperCase() + d.estado.slice(1)
                );
                $('#cv-hero-proveedor').text(d.proveedor.nombre);
                $('#cv-hero-fecha').text(d.fecha_compra);
                $('#cv-total').text(d.total);

                // Proveedor card
                $('#cv-prov-ini').text(d.proveedor.ini);
                $('#cv-prov-nombre').text(d.proveedor.nombre);
                $('#cv-prov-tipo').text(d.proveedor.tipo);
                $('#cv-prov-doc').text(d.proveedor.doc || 'Sin documento');
                if (d.proveedor.tel) {
                    $('#cv-prov-tel').text(d.proveedor.tel);
                    $('#cv-prov-tel-wrap').show();
                } else {
                    $('#cv-prov-tel-wrap').hide();
                }
                if (d.proveedor.email) {
                    $('#cv-prov-email').text(d.proveedor.email);
                    $('#cv-prov-email-wrap').show();
                } else {
                    $('#cv-prov-email-wrap').hide();
                }

                // Comprobante
                $('#cv-factura').text(d.numero_factura);
                $('#cv-fecha').text(d.fecha_compra);
                $('#cv-tasa').text(d.tasa_cambio ? 'Bs ' + d.tasa_cambio + ' / USD' : '—');

                if (d.observaciones) {
                    $('#cv-observaciones').text(d.observaciones);
                    $('#cv-obs-wrap').removeClass('d-none');
                } else {
                    $('#cv-obs-wrap').addClass('d-none');
                }

                // Items
                var itemsHtml = '';
                (d.items || []).forEach(function (item, i) {
                    var ivaBadge = item.aplica_iva
                        ? '<span class="badge bg-soft-success text-success">' + d.iva_porcentaje + '%</span>'
                        : '<span class="badge bg-soft-secondary text-muted">Exento</span>';
                    itemsHtml += '<tr>'
                        + '<td class="text-center cot-col-num">' + (i + 1) + '</td>'
                        + '<td class="fw-semibold">' + item.nombre + '</td>'
                        + '<td class="text-center"><span class="cot-tipo-pill">' + item.tipo + '</span></td>'
                        + '<td class="text-center text-muted">' + item.unidad + '</td>'
                        + '<td class="text-end">' + item.cantidad + '</td>'
                        + '<td class="text-end">' + item.costo_unitario_bs + '</td>'
                        + '<td class="text-center">' + ivaBadge + '</td>'
                        + '<td class="text-end fw-semibold">' + item.subtotal_bs + '</td>'
                        + '</tr>';
                });
                $('#cv-items-tbody').html(itemsHtml);
                $('#cv-items-count').text('(' + (d.items || []).length + ')');

                // Registro
                $('#cv-reg-avatar').attr('src', d.registrado_por.avatar_url);
                $('#cv-reg-nombre').text(d.registrado_por.name);
                $('#cv-reg-fecha').text(d.created_at);

                // Totales en bolívares (lo pagado) + equivalente USD y tasa
                $('#cv-subtotal').text(d.subtotal_bs);
                $('#cv-iva').text(d.iva_bs);
                $('#cv-iva-pct').text(d.iva_porcentaje);
                $('#cv-total-ticket').text(d.total_bs);
                $('#cv-tasa-ticket').text(d.tasa_cambio || '0.0000');
                $('#cv-total-usd').text(d.total);
                // Base exenta = suma de subtotales en Bs de líneas no gravadas
                var exento = (d.items || []).reduce(function (acc, it) {
                    return acc + (it.aplica_iva ? 0 : parseFloat(String(it.subtotal_bs).replace(/,/g, '')) || 0);
                }, 0);
                if (exento > 0.0001) {
                    $('#cv-exento').text(exento.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    $('#cv-exento-wrap').removeAttr('hidden');
                } else {
                    $('#cv-exento-wrap').attr('hidden', true);
                }

                // PDF
                $('#cv-pdf-btn').attr('href', '/compras/' + d.id + '/pdf');

                $('#viewCompraModal').modal('show');
            },
            error: function () {
                Swal.fire({ title: 'Error', text: 'No se pudo cargar el detalle de la compra.', icon: 'error', confirmButtonText: 'Entendido' });
            }
        });
    }

    $(document).on('click', '.ver-btn', function () {
        verDetalleCompra($(this).data('id'));
    });

    // ── Detalle de compra: navegación del wizard de solo lectura ─────────────
    (function () {
        var TOTAL = 3, step = 1;
        window.viewCompraShowStep = function (n) {
            step = n;
            $('#viewCompraModal .wiz-step-content').removeClass('is-active').attr('hidden', true);
            $('#viewCompraModal .wiz-step-content[data-step="' + n + '"]').removeAttr('hidden').addClass('is-active');
            $('#viewCompraModal .wiz-step-marker').each(function () {
                var s = parseInt($(this).data('step'), 10);
                $(this).toggleClass('is-active', s === n).toggleClass('is-complete', s < n);
            });
            $('#viewCompraModal .wiz-step-line-fill').each(function () {
                $(this).css('width', parseInt($(this).data('line'), 10) < n ? '100%' : '0%');
            });
            $('#cv-prev').toggle(n > 1);
            $('#cv-next').toggle(n < TOTAL);
            $('#cv-close').toggle(n === TOTAL);
        };
        $(document).on('click', '#cv-next', function () { if (step < TOTAL) window.viewCompraShowStep(step + 1); });
        $(document).on('click', '#cv-prev', function () { if (step > 1) window.viewCompraShowStep(step - 1); });
        $('#viewCompraModal').on('click', '.wiz-step-marker', function () { window.viewCompraShowStep(parseInt($(this).data('step'), 10)); });
        $('#viewCompraModal').on('show.bs.modal', function () { window.viewCompraShowStep(1); });
    }());

    // ── Procesar borrador ────────────────────────────────────────────────────
    $(document).on('click', '.procesar-btn', function () {
        var compraId = $(this).data('id');

        Swal.fire({
            title: '¿Procesar esta compra?',
            text: 'Se registrará la entrada de stock para todos los insumos incluidos.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, procesar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '/compras/' + compraId + '/procesar',
                method: 'POST',
                data: { _method: 'PATCH', _token: '{{ csrf_token() }}' },
                success: function (response) {
                    window.comprasTable.ajax.reload(null, false);
                    Swal.fire({
                        title: 'Procesada',
                        text: response.message,
                        icon: 'success',
                        timer: 2500,
                        showConfirmButton: false
                    });
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON?.message ?? 'Error al procesar la compra.';
                    Swal.fire({ title: 'Error', text: msg, icon: 'error', confirmButtonText: 'Entendido' });
                }
            });
        });
    });

    // ── Clonar compra anulada ────────────────────────────────────────────────
    $(document).on('click', '.clonar-btn', function () {
        var compraId = $(this).data('id');

        Swal.fire({
            title: '¿Clonar esta compra?',
            text: 'Se creará un nuevo borrador con los mismos datos. Podrás editarlo y procesarlo cuando estés listo.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, clonar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '/compras/' + compraId + '/clonar',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function (response) {
                    window.comprasTable.ajax.reload(null, false);
                    Swal.fire({
                        title: '¡Clonada!',
                        text: response.message,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Ver borrador',
                        cancelButtonText: 'Continuar'
                    }).then(function (r) {
                        if (r.isConfirmed) {
                            verDetalleCompra(response.compra_id);
                        }
                    });
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON?.message ?? 'Error al clonar la compra.';
                    Swal.fire({ title: 'Error', text: msg, icon: 'error', confirmButtonText: 'Entendido' });
                }
            });
        });
    });

    // ── Editar borrador ──────────────────────────────────────────────────────
    $(document).on('click', '.editar-btn', function () {
        var compraId = $(this).data('id');

        $.ajax({
            url: '/compras/' + compraId + '/editar-datos',
            method: 'GET',
            success: function (data) {
                $('#createCompraModal').one('shown.bs.modal', function () {
                    window.compraWizard.populate(data);
                });
                $('#createCompraModal').modal('show');
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Error al cargar los datos de la compra.';
                Swal.fire({ title: 'Error', text: msg, icon: 'error', confirmButtonText: 'Entendido' });
            }
        });
    });

    // ── Eliminar borrador (borrado físico) ───────────────────────────────────
    $(document).on('click', '.eliminar-compra-btn', function () {
        var compraId = $(this).data('id');

        Swal.fire({
            title: '¿Eliminar este borrador?',
            text: 'El borrador se eliminará definitivamente. Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '/compras/' + compraId,
                method: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function (response) {
                    window.comprasTable.ajax.reload(null, false);
                    Swal.fire({
                        title: 'Eliminado',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON?.message ?? 'Error al eliminar el borrador.';
                    Swal.fire({ title: 'Error', text: msg, icon: 'error', confirmButtonText: 'Entendido' });
                }
            });
        });
    });

    // ── Anular desde el listado ─────────────────────────────────────────────
    $(document).on('click', '.anular-btn', function () {
        var compraId = $(this).data('id');

        Swal.fire({
            title: '¿Anular esta compra?',
            text: 'Se revertirán todos los movimientos de stock generados.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '/compras/' + compraId + '/anular',
                method: 'POST',
                data: { _method: 'PATCH', _token: '{{ csrf_token() }}' },
                success: function (response) {
                    window.comprasTable.ajax.reload(null, false);
                    Swal.fire({
                        title: 'Anulada',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON?.message ?? 'Error al anular la compra.';
                    Swal.fire({ title: 'Error', text: msg, icon: 'error', confirmButtonText: 'Entendido' });
                }
            });
        });
    });
});
</script>
