<script>
    $(document).ready(function () {
        // Función para inicializar Select2
        function initializeSelect2(selector) {
            $(selector).select2({
                theme: 'bootstrap-5',
                placeholder: 'Seleccione insumo...',
                width: '100%',
                dropdownParent: $('#showModal')
            });
        }
        
        // Inicializar Select2 para los selectores existentes
        initializeSelect2('.insumo-select');

        // Manejar selección de pedido
        $('#pedido-id-field').on('change', function() {
            const pedidoId = $(this).val();
            
            if (pedidoId) {
                // Establecer el pedido_id en el campo oculto
                $('#pedido-id-hidden-field').val(pedidoId);
                
                // Mostrar información básica del pedido
                const selectedOption = $(this).find('option:selected');
                $('#info-cliente').text(selectedOption.data('cliente'));
                $('#info-fecha-pedido').text(selectedOption.data('fecha'));
                $('#info-fecha-entrega').text(selectedOption.data('entrega'));
                $('#pedido-info').show();
                
                // Obtener datos completos del pedido
                $.ajax({
                    url: `/pedidos/${pedidoId}/data-for-orden`,
                    method: 'GET',
                    success: function(pedido) {
                        
                        // Mostrar productos del pedido
                        $('#info-total-productos').text(pedido.productos.length);
                        
                        let productosHtml = '';
                        let totalCosto = 0;
                        
                        pedido.productos.forEach(function(detalle, index) {
                            const subtotal = detalle.cantidad * detalle.precio_unitario;
                            totalCosto += subtotal;
                            
                            productosHtml += `
                                <div class="card mb-2">
                                    <div class="card-body p-3">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <h6 class="mb-1">${detalle.producto && detalle.producto.nombre ? detalle.producto.nombre : 'Sin nombre'}</h6>
                                                <small class="text-muted">
                                                    Cantidad: ${detalle.cantidad} | 
                                                    Precio: ${detalle.precio_unitario}
                                                </small>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="text-end">
                                                    <strong>Subtotal: ${subtotal.toFixed(2)}</strong>
                                                </div>
                                                ${detalle.lleva_bordado ? `<small class="text-info"><i class="ri-brush-line"></i> Logo: ${detalle.nombre_logo || 'N/A'}<br>Ubicación: ${detalle.ubicacion_logo || 'N/A'}<br>Cantidad: ${detalle.cantidad_logo || 'N/A'}</small>` : ''}
                                                ${detalle.color ? `<br><small><i class="ri-palette-line"></i> Color: ${detalle.color}</small>` : ''}
                                                ${detalle.talla ? `<small> | <i class="ri-t-shirt-line"></i> Talla: ${detalle.talla}</small>` : ''}
                                                ${detalle.descripcion ? `<br><small class="text-muted">${detalle.descripcion}</small>` : ''}
                                                ${detalle.insumos && detalle.insumos.length > 0 ? `<br><small class="text-success"><i class="ri-tools-line"></i> ${detalle.insumos.length} insumo(s)</small>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        
                        $('#productos-container').html(productosHtml);
                        $('#productos-pedido').show();
                        
                        // Llenar campos automáticamente
                        // Usar el primer producto como referencia (o se puede modificar para manejar múltiples productos)
                        if (pedido.productos.length > 0) {
                            const primerProducto = pedido.productos[0];
                            $('#producto-id-field').val(primerProducto.producto_id);
                            
                            // Sumar todas las cantidades si hay múltiples productos del mismo tipo
                            const cantidadTotal = pedido.productos.reduce((sum, p) => sum + p.cantidad, 0);
                            $('#cantidad-solicitada-field').val(cantidadTotal);
                        }
                        
                        // Establecer fechas sugeridas
                        const hoy = new Date();
                        $('#fecha-inicio-field').val(hoy.toISOString().split('T')[0]);
                        
                        if (pedido.fecha_entrega_estimada) {
                            const fechaEntrega = new Date(pedido.fecha_entrega_estimada);
                            // Sugerir fecha fin 2 días antes de la entrega
                            fechaEntrega.setDate(fechaEntrega.getDate() - 2);
                            $('#fecha-fin-estimada-field').val(fechaEntrega.toISOString().split('T')[0]);
                        }
                        
                        // Establecer costo estimado basado en el total del pedido
                        $('#costo-estimado-field').val(totalCosto.toFixed(2));
                        
                        // Llenar insumos si están disponibles
                        $('#insumos-container').empty();
                        let insumoIndex = 0;
                        let insumosAgregados = new Map(); // Para evitar duplicados
                        
                        pedido.productos.forEach(function(detalle) {
                            if (detalle.insumos && detalle.insumos.length > 0) {
                                detalle.insumos.forEach(function(insumo) {
                                    
                                    const cantidadEstimada = insumo.pivot ? insumo.pivot.cantidad_estimada : 1;
                                    const cantidadTotal = cantidadEstimada * detalle.cantidad;
                                    
                                    // Verificar si ya agregamos este insumo
                                    if (insumosAgregados.has(insumo.id)) {
                                        // Si ya existe, sumar la cantidad
                                        const existingIndex = insumosAgregados.get(insumo.id);
                                        const currentValue = parseFloat($(`input[name="insumos[${existingIndex}][cantidad_estimada]"]`).val()) || 0;
                                        $(`input[name="insumos[${existingIndex}][cantidad_estimada]"]`).val(currentValue + cantidadTotal);
                                    } else {
                                        // Agregar nuevo insumo
                                        let newRow = `
                                            <div class="row insumo-row mt-2">
                                                <div class="col-md-6">
                                                    <select name="insumos[${insumoIndex}][id]" class="form-control insumo-select" required>
                                                        <option value="">Seleccione insumo...</option>
                                                        @foreach($insumos as $insumoOption)
                                                            <option value="{{ $insumoOption->id }}" ${insumo.id == '{{ $insumoOption->id }}' ? 'selected' : ''}>{{ $insumoOption->nombre }} ({{ $insumoOption->unidad_medida }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="number" name="insumos[${insumoIndex}][cantidad_estimada]" class="form-control" placeholder="Cantidad" step="0.01" min="0.01" value="${cantidadTotal.toFixed(2)}" required />
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-danger remove-insumo"><i class="ri-delete-bin-line"></i></button>
                                                </div>
                                            </div>
                                        `;
                                        $('#insumos-container').append(newRow);
                                        
                                        // Establecer el valor del select después de un pequeño delay
                                        setTimeout(() => {
                                            $(`select[name="insumos[${insumoIndex}][id]"]`).val(insumo.id);
                                            initializeSelect2(`select[name="insumos[${insumoIndex}][id]"]`);
                                        }, 100);
                                        
                                        insumosAgregados.set(insumo.id, insumoIndex);
                                        insumoIndex++;
                                    }
                                });
                            }
                        });
                        
                        // Si no hay insumos, agregar una fila vacía
                        if (insumoIndex === 0) {
                            $('#add-insumo-btn').click();
                        }
                        
                        // Llenar logo si está disponible
                        const productosConLogo = pedido.productos.filter(p => p.lleva_bordado && p.nombre_logo);
                        
                        if (productosConLogo.length > 0) {
                            // Si hay múltiples logos, combinarlos
                            const logos = productosConLogo.map(p => p.nombre_logo).filter(logo => logo);
                            const logosUnicos = [...new Set(logos)]; // Eliminar duplicados
                            const logoFinal = logosUnicos.join(', ');
                            
                            $('#logo-field').val(logoFinal);
                        } else {
                            $('#logo-field').val('');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al obtener datos del pedido:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudieron cargar los datos del pedido'
                        });
                    }
                });
            } else {
                // Limpiar el pedido_id
                $('#pedido-id-hidden-field').val('');
                
                // Ocultar información si no hay pedido seleccionado
                $('#pedido-info').hide();
                $('#productos-pedido').hide();
                
                // Limpiar campos
                $('#producto-id-field').val('');
                $('#cantidad-solicitada-field').val('');
                $('#fecha-inicio-field').val('');
                $('#fecha-fin-estimada-field').val('');
                $('#costo-estimado-field').val('');
                $('#logo-field').val('');
                
                // Resetear insumos
                $('#insumos-container').html(`
                    <div class="row insumo-row">
                        <div class="col-md-6">
                            <select name="insumos[0][id]" class="form-control insumo-select" required>
                                <option value="">Seleccione insumo...</option>
                                @foreach($insumos as $insumo)
                                    <option value="{{ $insumo->id }}">{{ $insumo->nombre }} ({{ $insumo->unidad_medida }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="number" name="insumos[0][cantidad_estimada]" class="form-control" placeholder="Cantidad" step="0.01" min="0.01" required />
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger remove-insumo"><i class="ri-delete-bin-line"></i></button>
                        </div>
                    </div>
                `);
                initializeSelect2('.insumo-select');
            }
        });

        // DataTable
        var table = $('#ordenes-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('ordenes.data') }}",
            dom: 'rtip',
            columns: [
                { 
                    data: 'id', 
                    name: 'id',
                    className: 'align-middle'
                },
                { 
                    data: 'pedido_info', 
                    name: 'pedido.id',
                    className: 'align-middle text-center',
                    orderable: false
                },
                { 
                    data: 'cantidad_solicitada', 
                    name: 'cantidad_solicitada',
                    className: 'align-middle text-center'
                },
                { 
                    data: null,
                    className: 'align-middle',
                    render: function(data) {
                        let porcentaje = (data.cantidad_producida / data.cantidad_solicitada * 100).toFixed(2);
                        return `<div class="progress" style="height: 15px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: ${porcentaje}%"
                                aria-valuenow="${porcentaje}" aria-valuemin="0" aria-valuemax="100">
                                ${porcentaje}%
                            </div>
                        </div>`;
                    }
                },
                { 
                    data: 'fecha_inicio', 
                    name: 'fecha_inicio',
                    className: 'align-middle',
                    render: function(data) {
                        let fecha = new Date(data);
                        return `<div class="text-nowrap">
                            <div class="fw-medium">${fecha.toLocaleDateString('es-ES')}</div>
                            <small class="text-muted">${fecha.toLocaleTimeString('es-ES')}</small>
                        </div>`;
                    }
                },
                { 
                    data: 'fecha_fin_estimada', 
                    name: 'fecha_fin_estimada',
                    className: 'align-middle',
                    render: function(data) {
                        let fecha = new Date(data);
                        return `<div class="text-nowrap">
                            <div class="fw-medium">${fecha.toLocaleDateString('es-ES')}</div>
                            <small class="text-muted">${fecha.toLocaleTimeString('es-ES')}</small>
                        </div>`;
                    }
                },
                { 
                    data: 'estado',
                    className: 'align-middle text-center',
                    render: function(data) {
                        let clases = {
                            'Pendiente': 'status-pendiente badge-soft-warning',
                            'En Proceso': 'status-procesando badge-soft-info',
                            'Finalizado': 'status-finalizado badge-soft-success',
                            'Cancelado': 'status-cancelado badge-soft-danger'
                        };
                        let badgeClass = clases[data] || 'badge-soft-secondary';
                        return `<span class="badge badge-status ${badgeClass} rounded-pill">${data}</span>`;
                    }
                },
                { 
                    data: 'costo_estimado',
                    className: 'align-middle text-end',
                    render: function(data) {
                        return '$/ ' + Number(data).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    }
                },
                {
                    data: 'logo',
                    name: 'logo',
                    render: function(data) {
                        return data || 'N/A';
                    }
                },
                { 
                    data: 'creado_por', 
                    name: 'creado_por',
                    className: 'align-middle'
                },
                {
                data: 'id',
                name: 'actions',
                orderable: false,
                searchable: false,
                className: 'align-middle text-center',
                render: function(data) {
                    return `
                        <div class="d-flex gap-2 justify-content-center">
                            <button class="btn btn-sm btn-soft-info view-btn" data-id="${data}" title="Ver">
                                <i class="ri-eye-fill"></i>
                            </button>
                            <button class="btn btn-sm btn-soft-success edit-btn" data-id="${data}" title="Editar">
                                <i class="ri-pencil-fill"></i>
                            </button>
                            <button class="btn btn-sm btn-soft-danger remove-btn" data-id="${data}" title="Eliminar">
                                <i class="ri-delete-bin-fill"></i>
                            </button>
                        </div>
                    `;
                }
                }
            ],
            order: [[0, 'desc']],
            responsive: false,
            buttons: [
                {
                    extend: 'copy',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 9] }
                },
                {
                    extend: 'csv',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 9] }
                },
                {
                    extend: 'excel',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 9] }
                },
                {
                    extend: 'pdf',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 9] }
                },
                {
                    extend: 'print',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 9] }
                }
            ],
            language: lenguajeData
        });

        // Buscador personalizado
        $('#custom-search-input').on('keyup', function () {
            table.search(this.value).draw();
        });

        // Agregar fila de insumo
        $('#add-insumo-btn').click(function() {
            let index = $('.insumo-row').length;
            let newRow = `
                <div class="row insumo-row mt-2">
                    <div class="col-md-6">
                        <select name="insumos[${index}][id]" class="form-control insumo-select" required>
                            <option value="">Seleccione insumo...</option>
                            @foreach($insumos as $insumo)
                                <option value="{{ $insumo->id }}">{{ $insumo->nombre }} ({{ $insumo->unidad_medida }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="insumos[${index}][cantidad_estimada]" class="form-control" placeholder="Cantidad" step="0.01" min="0.01" required />
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-insumo"><i class="ri-delete-bin-line"></i></button>
                    </div>
                </div>
            `;
            $('#insumos-container').append(newRow);
            // Inicializar Select2 para el nuevo selector
            initializeSelect2('.insumo-select:last');
        });

        // Remover fila de insumo
        $(document).on('click', '.remove-insumo', function() {
            $(this).closest('.insumo-row').remove();
        });

        // Crear orden
        $('#ordenForm').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            let url = $('#id-field').val() ? 
                "{{ route('ordenes.update', ':id') }}".replace(':id', $('#id-field').val()) :
                "{{ route('ordenes.store') }}";
            
            // Siempre usar POST y agregar _method para actualización
            if ($('#id-field').val()) {
                formData.append('_method', 'PUT');
            }

            $.ajax({
                url: url,
                method: 'POST', // Siempre usar POST
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#showModal').modal('hide');
                    table.ajax.reload();
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON.message || 'Ocurrió un error al procesar la solicitud'
                    });
                }
            });
        });

        // Editar orden
        $(document).on('click', '.edit-btn', function() {
            let id = $(this).data('id');
            $.get("{{ route('ordenes.edit', ':id') }}".replace(':id', id), function(data) {
                $('#modalTitle').text('Editar Orden de Producción');
                $('#id-field').val(data.id);
                $('#producto-id-field').val(data.producto_id).trigger('change');
                $('#cantidad-solicitada-field').val(data.cantidad_solicitada);
                
                const formatDateForInput = (dateString) => {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    return date.toISOString().split('T')[0];
                };
                
                $('#fecha-inicio-field').val(formatDateForInput(data.fecha_inicio));
                $('#fecha-fin-estimada-field').val(formatDateForInput(data.fecha_fin_estimada));
                $('#costo-estimado-field').val(data.costo_estimado);
                $('#estado-field').val(data.estado);
                $('#logo-field').val(data.logo);
                $('#notas-field').val(data.notas);
                
                // Limpiar y agregar insumos
                $('#insumos-container').empty();
                data.insumos.forEach((insumo, index) => {
                    $('#add-insumo-btn').click();
                    // Pequeña pausa para asegurar que Select2 se inicialice correctamente
                    setTimeout(() => {
                        $(`select[name="insumos[${index}][id]"]`).val(insumo.id).trigger('change');
                        $(`input[name="insumos[${index}][cantidad_estimada]"]`).val(insumo.pivot.cantidad_estimada);
                    }, 100);
                });

                $('#estado-container').show();
                $('#add-btn').hide();
                $('#edit-btn').show();
                $('#showModal').modal('show');
            });
        });

        // Ver orden
        $(document).on('click', '.view-btn', function() {
            let id = $(this).data('id');
            $.get("{{ route('ordenes.show', ':id') }}".replace(':id', id), function(data) {
                $('#view-producto').text(data.producto.nombre);
                $('#view-cantidad-solicitada').text(data.cantidad_solicitada);
                $('#view-cantidad-producida').text(data.cantidad_producida);
                
                let porcentaje = (data.cantidad_producida / data.cantidad_solicitada * 100).toFixed(2);
                $('#view-progreso')
                    .css('width', porcentaje + '%')
                    .attr('aria-valuenow', porcentaje)
                    .text(porcentaje + '%');

                $('#view-fecha-inicio').text(data.fecha_inicio);
                $('#view-fecha-fin-estimada').text(data.fecha_fin_estimada);
                $('#view-estado').html(`<span class="badge estado-${data.estado}">${data.estado}</span>`);
                $('#view-costo-estimado').text(
                    '$/ ' + Number(data.costo_estimado).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                );
                $('#view-creado-por').text(data.creadoPor ? data.creadoPor.name : 'N/A');
                
                $('#view-logo').html(data.logo || 'N/A');
                if(data.pedido && data.pedido.productos) {
                    let detallesLogo = '';
                    data.pedido.productos.forEach(function(detalle) {
                        if(detalle.lleva_bordado) {
                            detallesLogo += `<div><b>Logo:</b> ${detalle.nombre_logo || 'N/A'}<br><b>Ubicación:</b> ${detalle.ubicacion_logo || 'N/A'}<br><b>Cantidad:</b> ${detalle.cantidad_logo || 'N/A'}</div><hr>`;
                        }
                    });
                    if(detallesLogo) {
                        $('#view-logo').append('<br>' + detallesLogo);
                    }
                }

                // Insumos
                $('#view-insumos').empty();
                data.insumos.forEach(insumo => {
                    let porcentajeInsumo = (insumo.pivot.cantidad_utilizada / insumo.pivot.cantidad_estimada * 100).toFixed(2);
                    $('#view-insumos').append(`
                        <tr>
                            <td>${insumo.nombre}</td>
                            <td>${insumo.pivot.cantidad_estimada} ${insumo.unidad_medida}</td>
                            <td>${insumo.pivot.cantidad_utilizada} ${insumo.unidad_medida}</td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: ${porcentajeInsumo}%"
                                        aria-valuenow="${porcentajeInsumo}" aria-valuemin="0" aria-valuemax="100">
                                        ${porcentajeInsumo}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                $('#view-notas').text(data.notas || 'Sin notas');
                $('#view-created').text(new Date(data.created_at).toLocaleString());
                
                $('#viewModal').modal('show');
            });
        });

        // Eliminar orden
        $(document).on('click', '.remove-btn', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                backdrop: true,
                allowOutsideClick: true,
                customClass: {
                    confirmButton: 'btn btn-primary w-xs me-2',
                    cancelButton: 'btn btn-danger w-xs',
                    container: 'swal2-container'
                },
                buttonsStyling: false,
                showCloseButton: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('ordenes.destroy', ':id') }}".replace(':id', id),
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            table.ajax.reload();
                            Swal.fire('Eliminado', response.message, 'success');
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON.message || 'Ocurrió un error', 'error');
                        }
                    });
                }
            });
        });

        // Reset form on modal close
        $('#showModal').on('hidden.bs.modal', function () {
            $('#ordenForm')[0].reset();
            $('#id-field').val('');
            $('#pedido-id-hidden-field').val('');
            $('#modalTitle').text('Nueva Orden de Producción');
            $('#estado-container').hide();
            $('#add-btn').show();
            $('#edit-btn').hide();
            
            // Ocultar secciones de pedido
            $('#pedido-info').hide();
            $('#productos-pedido').hide();
            
            // Resetear insumos
            $('#insumos-container').html(`
                <div class="row insumo-row">
                    <div class="col-md-6">
                        <select name="insumos[0][id]" class="form-control insumo-select" required>
                            <option value="">Seleccione insumo...</option>
                            @foreach($insumos as $insumo)
                                <option value="{{ $insumo->id }}">{{ $insumo->nombre }} ({{ $insumo->unidad_medida }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="insumos[0][cantidad_estimada]" class="form-control" placeholder="Cantidad" step="0.01" min="0.01" required />
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-insumo"><i class="ri-delete-bin-line"></i></button>
                    </div>
                </div>
            `);
            // Reinicializar Select2 después de resetear el formulario
            initializeSelect2('.insumo-select');
        });
    });
</script>
