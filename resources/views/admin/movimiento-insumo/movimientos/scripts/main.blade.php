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
                if ($(this).val() && $(this).val() !== '') {
                    count++;
                }
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

        // Inicializar DataTable
        var table = $('#movimientos-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('movimiento-insumo.data') }}",
                data: function (d) {
                    d.filter_tipo_movimiento = $('#filter-tipo').val();
                    d.filter_insumo_id = $('#filter-insumo').val();
                    d.filter_stock = $('#filter-stock').val();
                    d.filter_fecha_desde = $('#filter-fecha-desde').val();
                    d.filter_fecha_hasta = $('#filter-fecha-hasta').val();
                }
            },
            autoWidth: false,
            columns: [
                { data: 'insumo_nombre', name: 'insumo_nombre', width: '25%' },
                {
                    data: 'tipo_movimiento',
                    name: 'tipo_movimiento',
                    width: '15%',
                    render: function (data) {
                        if (data === 'Entrada') {
                            return '<span class="badge badge-status status-aprobada"><i class="ri-arrow-right-down-line me-1"></i>Entrada</span>';
                        } else {
                            return '<span class="badge badge-status status-rechazada"><i class="ri-arrow-right-up-line me-1"></i>Salida</span>';
                        }
                    }
                },
                {
                    data: 'cantidad',
                    name: 'cantidad',
                    width: '12%',
                    render: function (data, type, row) {
                        return parseFloat(data).toFixed(2);
                    }
                },
                {
                    data: 'stock_nuevo',
                    name: 'stock_nuevo',
                    width: '12%',
                    render: function (data, type, row) {
                        return parseFloat(data).toFixed(2);
                    }
                },
                {
                    data: 'fecha',
                    name: 'fecha',
                    width: '16%',
                    render: function (data) {
                        if (!data) return 'N/A';
                        return data.split(' ')[0];
                    }
                },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, width: '20%' },
            ],
            order: [[4, 'desc']],
            dom: 'rtip',
            language: lenguajeData,
            responsive: true
        });

        // ══════════════════════════════════════════════════
        // PANEL DE EXISTENCIAS — stock Mín./Actual/Máx. por insumo
        // ══════════════════════════════════════════════════
        function badgeEstadoStock(status) {
            if (status === 'bajo')  return '<span class="badge bg-danger">Bajo</span>';
            if (status === 'medio') return '<span class="badge bg-warning text-dark">Medio</span>';
            return '<span class="badge bg-success">Normal</span>';
        }

        var existenciasTable = $('#existencias-table').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: "{{ route('movimiento-insumo.existencias.data') }}",
                data: function (d) {
                    d.filter_tipo   = $('#exist-filter-tipo').val();
                    d.filter_estado = $('#exist-filter-alerta').val();
                }
            },
            columns: [
                {
                    data: 'nombre', name: 'nombre', width: '32%',
                    render: function (data, type, row) {
                        var pill = row.codigo
                            ? '<span style="font-family:monospace;padding:.1rem .45rem;background:rgba(12,74,110,.10);color:#0c4a6e;border-radius:4px;font-size:.72rem;font-weight:600;margin-right:.4rem;">' + row.codigo + '</span>'
                            : '';
                        return pill + (data || '');
                    }
                },
                { data: 'tipo', name: 'tipo', width: '12%' },
                {
                    data: 'stock_minimo', name: 'stock_minimo', width: '14%',
                    render: function (data) { return parseFloat(data).toFixed(2); }
                },
                {
                    data: 'stock_actual', name: 'stock_actual', width: '14%',
                    render: function (data, type, row) {
                        return '<span class="stock-' + row.stock_status + '">' + parseFloat(data).toFixed(2) + '</span>';
                    }
                },
                {
                    data: 'stock_maximo', name: 'stock_maximo', width: '14%',
                    render: function (data) { return parseFloat(data).toFixed(2); }
                },
                {
                    data: 'stock_status', name: 'stock_status', width: '14%',
                    orderable: false, searchable: false,
                    render: function (data) { return badgeEstadoStock(data); }
                }
            ],
            order: [],
            pageLength: 5,
            dom: 'rtip',
            language: lenguajeData,
            responsive: true
        });

        // ── Búsqueda + filtros unificados (estándar navy-filter) para Existencias ──
        function existUpdateBadge() {
            let count = 0;
            $('#exist-advanced-filters .navy-filter-select').each(function () {
                if ($(this).val() && $(this).val() !== '') count++;
            });
            $('#exist-active-filter-count').text(count).toggleClass('d-none', count === 0);
        }

        $('#exist-filters-collapse')
            .on('show.bs.collapse', function () {
                $('#exist-advanced-filters .navy-filter-header').removeClass('is-collapsed');
            })
            .on('hidden.bs.collapse', function () {
                $('#exist-advanced-filters .navy-filter-header').addClass('is-collapsed');
            });

        $('#exist-search-input').on('input', debounce(function () {
            existenciasTable.search(this.value).draw();
        }, 300));

        $('#exist-advanced-filters .navy-filter-select').on('change', function () {
            existenciasTable.ajax.reload();
            existUpdateBadge();
        });

        $('#exist-btn-clear-filters').on('click', function () {
            $('#exist-advanced-filters .navy-filter-select').val('');
            $('#exist-search-input').val('');
            existenciasTable.search('').ajax.reload();
            existUpdateBadge();
        });

        existUpdateBadge();

        // Buscador personalizado
        $('#custom-search-input').on('input', debounce(function () {
            table.search(this.value).draw();
        }, 300));

        $('#advanced-filters .navy-filter-select').on('change', function () {
            table.ajax.reload(null, true);
            updateFilterBadge();
        });

        $('#btn-clear-filters').on('click', function () {
            $('#advanced-filters .navy-filter-select').val('');
            $('#custom-search-input').val('');
            table.search('').draw();
            table.ajax.reload(null, true);
            updateFilterBadge();
        });

        updateFilterBadge();

        // Manejar cambio en el select de insumo
        $('#insumo_id').on('change', function () {
            var option = $(this).find('option:selected');
            var stock = option.data('stock');
            var unidad = option.data('unidad');

            if (stock) {
                $('#stock-info').text('Stock actual: ' + stock + ' ' + unidad);
                $('#unidad-medida').text('(' + unidad + ')');
            } else {
                $('#stock-info').text('Seleccione un insumo para ver información de stock.');
                $('#unidad-medida').text('');
            }
        });

        // Validar cantidad según tipo de movimiento
        $('#field-cantidad, #field-tipo_movimiento, #insumo_id').on('change input', function () {
            var cantidad = parseFloat($('#field-cantidad').val()) || 0;
            var tipoMovimiento = $('#field-tipo_movimiento').val();
            var option = $('#insumo_id').find('option:selected');
            var stock = parseFloat(option.data('stock')) || 0;

            if (tipoMovimiento === 'Salida' && cantidad > stock) {
                $('#stock-warning').removeClass('d-none');
            } else {
                $('#stock-warning').addClass('d-none');
            }
        });

        // Manejar envío del formulario de creación
        $('#createForm').on('submit', function (e) {
            e.preventDefault();

            // Validar formulario
            if (!this.checkValidity()) {
                e.stopPropagation();
                $(this).addClass('was-validated');
                return;
            }

            // Validar stock para salidas
            var cantidad = parseFloat($('#field-cantidad').val());
            var tipoMovimiento = $('#field-tipo_movimiento').val();
            var option = $('#insumo_id').find('option:selected');
            var stock = parseFloat(option.data('stock')) || 0;

            if (tipoMovimiento === 'Salida' && cantidad > stock) {
                Swal.fire({
                    title: 'Error',
                    text: 'La cantidad excede el stock disponible',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            // Enviar datos
            $.ajax({
                url: "{{ route('movimiento-insumo.store') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    insumo_id: $('#insumo_id').val(),
                    tipo_movimiento: $('#field-tipo_movimiento').val(),
                    cantidad: $('#field-cantidad').val(),
                    motivo: $('#field-motivo').val()
                },
                success: function (response) {
                    $('#createModal').modal('hide');
                    $('#createForm').trigger('reset');
                    $('#createForm').removeClass('was-validated');
                    table.ajax.reload();
                    existenciasTable.ajax.reload(null, false);

                    Swal.fire({
                        title: 'Éxito',
                        text: response.success,
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    });
                },
                error: function (xhr) {
                    var r = xhr.responseJSON || {};
                    var errorMessage = r.error || r.message || 'Ocurrió un error al procesar la solicitud';
                    if (r.errors) {
                        errorMessage = Object.values(r.errors).map(function (v) { return Array.isArray(v) ? v[0] : v; }).join('\n');
                    }

                    Swal.fire({
                        title: 'Error',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                }
            });
        });

        // Manejar clic en botón de ver
        $(document).on('click', '.view-btn', function () {
            var id = $(this).data('id');

            $.ajax({
                url: "/movimiento-insumo/" + id,
                method: 'GET',
                success: function (response) {
                    try {
                        $('#view-insumo').text((response.insumo ? response.insumo.nombre : 'N/A') + ' (' + (response.insumo ? response.insumo.tipo : 'N/A') + ')');

                        if (response.tipo_movimiento === 'Entrada') {
                            $('#view-tipo-movimiento').html('<span class="badge badge-status status-aprobada"><i class="ri-arrow-right-down-line me-1"></i>Entrada</span>');
                        } else {
                            $('#view-tipo-movimiento').html('<span class="badge badge-status status-rechazada"><i class="ri-arrow-right-up-line me-1"></i>Salida</span>');
                        }

                        var unidadMedida = response.insumo ? response.insumo.unidad_medida : '';
                        $('#view-cantidad').text(parseFloat(response.cantidad).toFixed(2) + ' ' + unidadMedida);
                        $('#view-stock-anterior').text(parseFloat(response.stock_anterior).toFixed(2) + ' ' + unidadMedida);
                        $('#view-stock-nuevo').text(parseFloat(response.stock_nuevo).toFixed(2) + ' ' + unidadMedida);
                        $('#view-motivo').text(response.motivo || 'N/A');
                        $('#view-usuario').text(response.creado_por ? response.creado_por.name : 'Sistema');

                        // Formatear fecha
                        if (response.created_at) {
                            var fecha = new Date(response.created_at);
                            var options = {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            };
                            $('#view-fecha').text(fecha.toLocaleDateString('es-ES', options));
                        } else {
                            $('#view-fecha').text('N/A');
                        }

                        $('#viewModal').modal('show');
                    } catch (e) {
                        console.error('Error al mostrar detalles:', e);
                        // Intentar mostrar el modal de todos modos si falla el renderizado de datos
                        $('#viewModal').modal('show');
                    }
                },
                error: function () {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo cargar la información del movimiento',
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                }
            });
        });

        // ── Detalle de movimiento: navegación del wizard de solo lectura ─────
        (function () {
            var TOTAL = 2, step = 1;
            window.viewMovShowStep = function (n) {
                step = n;
                $('#viewModal .wiz-step-content').removeClass('is-active').attr('hidden', true);
                $('#viewModal .wiz-step-content[data-step="' + n + '"]').removeAttr('hidden').addClass('is-active');
                $('#viewModal .wiz-step-marker').each(function () {
                    var s = parseInt($(this).data('step'), 10);
                    $(this).toggleClass('is-active', s === n).toggleClass('is-complete', s < n);
                });
                $('#viewModal .wiz-step-line-fill').each(function () {
                    $(this).css('width', parseInt($(this).data('line'), 10) < n ? '100%' : '0%');
                });
                $('#mv-prev').toggle(n > 1);
                $('#mv-next').toggle(n < TOTAL);
            };
            $(document).on('click', '#mv-next', function () { if (step < TOTAL) window.viewMovShowStep(step + 1); });
            $(document).on('click', '#mv-prev', function () { if (step > 1) window.viewMovShowStep(step - 1); });
            $('#viewModal').on('click', '.wiz-step-marker', function () { window.viewMovShowStep(parseInt($(this).data('step'), 10)); });
            $('#viewModal').on('show.bs.modal', function () { window.viewMovShowStep(1); });
        }());

        // --- NUEVO: Abrir modal y seleccionar insumo si viene insumo_id en la URL, esperando si es necesario ---
        function getUrlParameter(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            var results = regex.exec(window.location.search);
            return results === null ? null : decodeURIComponent(results[1].replace(/\+/g, ' '));
        }
        function selectInsumoAndOpenModal(insumoId, intentos) {
            intentos = intentos || 0;
            var $select = $('#insumo_id');
            if ($select.length && $select.find('option[value="' + insumoId + '"]').length) {
                $select.val(insumoId).trigger('change');
                $('#createModal').modal('show');
            } else if (intentos < 10) {
                setTimeout(function () { selectInsumoAndOpenModal(insumoId, intentos + 1); }, 200);
            }
        }
        var insumoId = getUrlParameter('insumo_id');
        if (insumoId) {
            selectInsumoAndOpenModal(insumoId);
        }

        // --- Manejar botón de agregar insumo rápido ---
        $('#open-add-insumo-modal').on('click', function () {
            // Ocultar temporalmente el modal principal para que no se superponga
            $('#createModal').addClass('modal-hidden-temp');
            $('#modalAddInsumo').modal('show');
        });

        // Cuando se cierra el modal de insumo, mostrar nuevamente el principal
        $('#modalAddInsumo').on('hidden.bs.modal', function () {
            $('#createModal').removeClass('modal-hidden-temp');
        });

        // Manejar envío del formulario de crear insumo
        $('#add-btn-insumo').on('click', function () {
            var form = $('#insumoFormMovimiento');

            // Validar campos requeridos
            var isValid = true;
            form.find('[required]').each(function () {
                if (!$(this).val()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (!isValid) {
                Swal.fire({
                    title: 'Error',
                    text: 'Por favor complete todos los campos requeridos',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            // Enviar datos
            $.ajax({
                url: "{{ route('insumos.store') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    nombre: $('#nombre-field-insumo').val(),
                    tipo: $('#tipo-field-insumo').val(),
                    unidad_medida: $('#unidad-medida-field-insumo').val(),
                    stock_actual: $('#stock-actual-field-insumo').val(),
                    stock_minimo: $('#stock-minimo-field-insumo').val(),
                    costo_unitario: $('#costo-unitario-field-insumo').val(),
                    estado: 1
                },
                success: function (response) {
                    // Cerrar modal
                    $('#modalAddInsumo').modal('hide');
                    $('#insumoFormMovimiento')[0].reset();

                    // Agregar el nuevo insumo al select
                    var nuevoInsumo = response.insumo;
                    if (nuevoInsumo) {
                        var newOption = new Option(
                            nuevoInsumo.nombre + ' (' + nuevoInsumo.tipo + ') - Stock: ' + nuevoInsumo.stock_actual + ' ' + nuevoInsumo.unidad_medida,
                            nuevoInsumo.id,
                            true,
                            true
                        );
                        $(newOption).attr('data-stock', nuevoInsumo.stock_actual);
                        $(newOption).attr('data-unidad', nuevoInsumo.unidad_medida);
                        $('#insumo_id').append(newOption).trigger('change');
                    }
                    existenciasTable.ajax.reload(null, false);

                    Swal.fire({
                        title: '¡Éxito!',
                        text: 'Insumo creado correctamente',
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        timer: 1500
                    });
                },
                error: function (xhr) {
                    var errors = xhr.responseJSON;
                    var errorMessage = 'Ocurrió un error al crear el insumo';
                    if (errors && errors.errors) {
                        errorMessage = Object.values(errors.errors).flat().join('<br>');
                    } else if (errors && errors.message) {
                        errorMessage = errors.message;
                    }

                    Swal.fire({
                        title: 'Error',
                        html: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                }
            });
        });

        // Limpiar modal de insumo al cerrar
        $('#modalAddInsumo').on('hidden.bs.modal', function () {
            $('#insumoFormMovimiento')[0].reset();
            $('#insumoFormMovimiento').find('.is-invalid').removeClass('is-invalid');
        });
    });
</script>