<script>
    $(document).ready(function() {
        // Inicializar DataTable
        var table = $('#movimientos-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('existencia.movimientos.data') }}",
            columns: [
                { data: 'insumo_nombre', name: 'insumo_nombre' },
                { data: 'insumo_tipo', name: 'insumo_tipo' },
                { 
                    data: 'tipo_movimiento', 
                    name: 'tipo_movimiento',
                    render: function(data) {
                        if (data === 'Entrada') {
                            return '<span class="badge bg-success">Entrada</span>';
                        } else {
                            return '<span class="badge bg-danger">Salida</span>';
                        }
                    }
                },
                { 
                    data: 'cantidad', 
                    name: 'cantidad',
                    render: function(data, type, row) {
                        return parseFloat(data).toFixed(2);
                    }
                },
                { 
                    data: 'stock_anterior', 
                    name: 'stock_anterior',
                    render: function(data, type, row) {
                        return parseFloat(data).toFixed(2);
                    }
                },
                { 
                    data: 'stock_nuevo', 
                    name: 'stock_nuevo',
                    render: function(data, type, row) {
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
        $('#insumo_id').on('change', function() {
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
        $('#cantidad, #tipo_movimiento, #insumo_id').on('change input', function() {
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
        $('#createForm').on('submit', function(e) {
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
                url: "{{ route('existencia.movimientos.store') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    insumo_id: $('#insumo_id').val(),
                    tipo_movimiento: $('#tipo_movimiento').val(),
                    cantidad: $('#cantidad').val(),
                    motivo: $('#motivo').val()
                },
                success: function(response) {
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
                error: function(xhr) {
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
        $(document).on('click', '.view-btn', function() {
            var id = $(this).data('id');
            
            $.ajax({
                url: "/existencia/movimientos/" + id,
                method: 'GET',
                success: function(response) {
                    $('#view-insumo').text(response.insumo.nombre + ' (' + response.insumo.tipo + ')');
                    
                    if (response.tipo_movimiento === 'Entrada') {
                        $('#view-tipo-movimiento').html('<span class="badge bg-success">Entrada</span>');
                    } else {
                        $('#view-tipo-movimiento').html('<span class="badge bg-danger">Salida</span>');
                    }
                    
                    $('#view-cantidad').text(parseFloat(response.cantidad).toFixed(2) + ' ' + response.insumo.unidad_medida);
                    $('#view-stock-anterior').text(parseFloat(response.stock_anterior).toFixed(2) + ' ' + response.insumo.unidad_medida);
                    $('#view-stock-nuevo').text(parseFloat(response.stock_nuevo).toFixed(2) + ' ' + response.insumo.unidad_medida);
                    $('#view-motivo').text(response.motivo);
                    $('#view-usuario').text(response.creado_por.name);
                    
                    // Formatear fecha
                    var fecha = new Date(response.created_at);
                    var options = { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    };
                    $('#view-fecha').text(fecha.toLocaleDateString('es-ES', options));
                    
                    $('#viewModal').modal('show');
                },
                error: function() {
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
            if ($select.length && $select.find('option[value="'+insumoId+'"]').length) {
                $select.val(insumoId).trigger('change');
                $('#createModal').modal('show');
            } else if (intentos < 10) {
                setTimeout(function() { selectInsumoAndOpenModal(insumoId, intentos+1); }, 200);
            }
        }
        var insumoId = getUrlParameter('insumo_id');
        if (insumoId) {
            selectInsumoAndOpenModal(insumoId);
        }
    });
</script>
