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
                d.filter_estado     = $('#filter-estado').val();
                d.filter_tipo_pago  = $('#filter-tipo-pago').val();
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
            { data: 'proveedor_nombre', name: 'proveedor_nombre', width: '25%' },
            {
                data: 'numero_factura', name: 'numero_factura', width: '12%',
                render: function (data) {
                    return data
                        ? '<span style="font-family:monospace;font-size:.82rem;">' + data + '</span>'
                        : '<span class="text-muted fst-italic">S/N</span>';
                }
            },
            { data: 'fecha_formateada', name: 'fecha_compra', width: '10%' },
            {
                data: 'tipo_pago', name: 'tipo_pago', width: '10%',
                render: function (data) {
                    if (data === 'credito') {
                        return '<span class="badge bg-secondary">Crédito</span>';
                    }
                    return '<span class="badge bg-info text-dark">Contado</span>';
                }
            },
            {
                data: 'total', name: 'total', width: '12%', className: 'text-end',
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
        $('#advanced-filters .navy-filter-select').val('');
        $('#custom-search-input').val('');
        window.comprasTable.search('').ajax.reload(null, true);
        updateFilterBadge();
    });

    updateFilterBadge();

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
                            window.location.href = '{{ url("compras") }}/' + response.compra_id;
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
