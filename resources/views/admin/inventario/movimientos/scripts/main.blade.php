<script>
    $(document).ready(function () {
        // Inicializar DataTable
        var table = $('#movimientos-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('inventario.movimientos.data') }}",
            columns: [
                { data: 'insumo_nombre', name: 'insumo_nombre' },
                { data: 'insumo_tipo', name: 'insumo_tipo' },
                {
                    data: 'tipo_movimiento',
                    name: 'tipo_movimiento',
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
                    render: function (data, type, row) {
                        return parseFloat(data).toFixed(2);
                    }
                },
                {
                    data: 'stock_anterior',
                    name: 'stock_anterior',
                    render: function (data, type, row) {
                        return parseFloat(data).toFixed(2);
                    }
                },
                {
                    data: 'stock_nuevo',
                    name: 'stock_nuevo',
                    render: function (data, type, row) {
                        return parseFloat(data).toFixed(2);
                    }
                },
                { data: 'usuario', name: 'usuario' },
                { data: 'fecha', name: 'fecha' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
            order: [[7, 'desc']],
            language: lenguajeData,
            responsive: true
        });

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
        $('#cantidad, #tipo_movimiento, #insumo_id').on('change input', function () {
            var cantidad = parseFloat($('#cantidad').val()) || 0;
            var tipoMovimiento = $('#tipo_movimiento').val();
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
            var cantidad = parseFloat($('#cantidad').val());
            var tipoMovimiento = $('#tipo_movimiento').val();
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
                url: "{{ route('inventario.movimientos.store') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    insumo_id: $('#insumo_id').val(),
                    tipo_movimiento: $('#tipo_movimiento').val(),
                    cantidad: $('#cantidad').val(),
                    motivo: $('#motivo').val()
                },
                success: function (response) {
                    $('#createModal').modal('hide');
                    $('#createForm').trigger('reset');
                    $('#createForm').removeClass('was-validated');
                    table.ajax.reload();

                    Swal.fire({
                        title: 'Éxito',
                        text: response.success,
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    });
                },
                error: function (xhr) {
                    var errors = xhr.responseJSON;
                    var errorMessage = errors.error || 'Ocurrió un error al procesar la solicitud';

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
                url: "/inventario/movimientos/" + id,
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
        $('#open-add-insumo-modal').on('click', function() {
            // Ocultar temporalmente el modal principal para que no se superponga
            $('#createModal').addClass('modal-hidden-temp');
            $('#modalAddInsumo').modal('show');
        });

        // Cuando se cierra el modal de insumo, mostrar nuevamente el principal
        $('#modalAddInsumo').on('hidden.bs.modal', function() {
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
                    proveedor_id: $('#proveedor-id-field-insumo').val(),
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