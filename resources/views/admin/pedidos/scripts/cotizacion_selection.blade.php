<script>
    // ============================================
    // FUNCIONALIDAD DE SELECCIÓN DE COTIZACIONES
    // ============================================

    let cotizacionesDisponibles = [];
    let cotizacionSeleccionada = null;

    // Cargar cotizaciones cuando se abre el modal
    $(document).on('shown.bs.modal', '#seleccionarCotizacionModal', function() {
        cargarCotizacionesDisponibles();
    });

    // Función para cargar cotizaciones disponibles
    function cargarCotizacionesDisponibles() {
        const container = $('#cotizaciones-container');
        const emptyState = $('#empty-state');
        const loadingState = $('#loading-state');

        // Mostrar loading
        container.hide();
        emptyState.hide();
        loadingState.show();

        $.ajax({
            url: '{{ route("pedidos.cotizacionesDisponibles") }}',
            method: 'GET',
            success: function(data) {
                loadingState.hide();
                
                if (data.length === 0) {
                    emptyState.show();
                    return;
                }

                cotizacionesDisponibles = data;
                renderCotizaciones(data);
                container.show();
            },
            error: function(xhr) {
                loadingState.hide();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar las cotizaciones disponibles',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            }
        });
    }

    // Renderizar cotizaciones como cards
    function renderCotizaciones(cotizaciones) {
        const container = $('#cotizaciones-container');
        container.empty();

        cotizaciones.forEach(function(cotizacion) {
            const card = `
                <div class="cotizacion-card" data-cotizacion-id="${cotizacion.id}">
                    <div class="cotizacion-header">
                        <span class="cotizacion-numero">
                            <i class="ri-file-text-line"></i> Cotización #${cotizacion.id}
                        </span>
                        <span class="cotizacion-total">$${cotizacion.total}</span>
                    </div>
                    <div class="cotizacion-info">
                        <div class="cotizacion-info-item">
                            <i class="ri-user-line"></i>
                            <span>${cotizacion.cliente_nombre}</span>
                        </div>
                        <div class="cotizacion-info-item">
                            <i class="ri-calendar-line"></i>
                            <span>${cotizacion.fecha_cotizacion}</span>
                        </div>
                        <div class="cotizacion-info-item">
                            <i class="ri-bank-card-line"></i>
                            <span>${cotizacion.cliente_documento}</span>
                        </div>
                        <div class="cotizacion-info-item">
                            <i class="ri-shopping-bag-line"></i>
                            <span>${cotizacion.cantidad_productos} producto(s)</span>
                        </div>
                    </div>
                    <div class="cotizacion-footer">
                        <button type="button" class="btn btn-sm btn-success seleccionar-cotizacion-btn">
                            <i class="ri-check-line"></i> Seleccionar Cotización
                        </button>
                    </div>
                </div>
            `;
            container.append(card);
        });
    }

    // Búsqueda de cotizaciones
    $('#buscarCotizacion').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        if (searchTerm === '') {
            renderCotizaciones(cotizacionesDisponibles);
            return;
        }

        const filtradas = cotizacionesDisponibles.filter(function(cot) {
            return cot.cliente_nombre.toLowerCase().includes(searchTerm) ||
                   cot.cliente_documento.toLowerCase().includes(searchTerm) ||
                   cot.id.toString().includes(searchTerm);
        });

        renderCotizaciones(filtradas);
    });

    // Handler para seleccionar cotización
    $(document).on('click', '.seleccionar-cotizacion-btn', function() {
        const card = $(this).closest('.cotizacion-card');
        const cotizacionId = card.data('cotizacion-id');
        
        // Cargar datos de la cotizacion y abrir el formulario
        cargarDatosCotizacion(cotizacionId);
    });

    // Función para cargar datos de cotización seleccionada
    function cargarDatosCotizacion(cotizacionId) {
        $.ajax({
            url: `/cotizaciones/${cotizacionId}/datos-para-pedido`,
            method: 'GET',
            success: function(data) {
                // Cerrar modal de selección
                $('#seleccionarCotizacionModal').modal('hide');
                
                // Guardar cotización seleccionada
                cotizacionSeleccionada = data;
                
                // Abrir modal de pedido y pre-llenar datos
                setTimeout(function() {
                    $('#showModal').modal('show');
                    setTimeout(function() {
                        preLlenarFormularioPedido(data);
                    }, 300);
                }, 300);
            },
            error: function(xhr) {
                var errorMsg = xhr.responseJSON?.error || 'No se pudieron cargar los datos de la cotización';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg,
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            }
        });
    }

    // Función para pre-llenar el formulario de pedido con datos de cotización
    function preLlenarFormularioPedido(data) {
        console.log('Pre-llenando formulario con data:', data);
        
        // Título del modal
        $('#modalTitle').text('Crear Pedido desde Cotización #' + data.cotizacion_id);
        
        // Guardar cotización_id en campo oculto
        if ($('#cotizacion-id-field').length === 0) {
            $('#pedidoForm').prepend('<input type="hidden" id="cotizacion-id-field" name="cotizacion_id">');
        }
        $('#cotizacion-id-field').val(data.cotizacion_id);
        
        // Llenar campos ocultos con datos del cliente (para el backend)
        if (data.cliente) {
            $('#cliente-id-field').val(data.cliente.id);
            
            // Llenar campos pero OCULTARLOS porque son redundantes con el banner
            $('#cliente-nombre-field').val(`${data.cliente.nombre} ${data.cliente.apellido || ''}`);
            $('#ci-rif-full-field').val(data.cliente.documento);
            
            const docParts = data.cliente.documento.match(/^([VJEG])-(.+)$/);
            if (docParts) {
                $('#ci-rif-prefix-field').val(docParts[1] + '-');
                $('#ci-rif-number-field').val(docParts[2]);
            }
            
            $('#cliente-email-field').val(data.cliente.email || '');
            $('#cliente-telefono-field').val(data.cliente.telefono || '');
            
            // OCULTAR solo los campos específicos del cliente (no toda la card)
            // Ocultar los div.mb-3 contenedores de cada campo individual
            $('#cliente-nombre-field').closest('.mb-3').hide();
            $('#ci-rif-number-field').closest('.mb-3').hide();
            $('#cliente-email-field').closest('.mb-3').hide();
            $('#cliente-telefono-field').closest('.mb-3').hide();
        }
        
        // Mostrar banner informativo PRIMERO
        mostrarBannerCotizacion(data);
        
        // Pre-cargar productos de la cotización
        preCargarProductosDesdeCotizacion(data.productos, data.total);
        
        // Las fechas y datos de pago quedan editables (valores por defecto)
        $('#fecha-pedido-field').val(new Date().toISOString().split('T')[0]);
        $('#prioridad-field').val('Normal');
        
        // Establecer PRIMERO el total, LUEGO el abono, LUEGO calcular restante
        const totalNum = parseFloat(data.total) || 0;
        $('#total-display-field').val(totalNum.toFixed(2));
        $('#abono-field').val(0);
        calcularRestante();
    }

    // Mostrar banner con información de la cotización
    function mostrarBannerCotizacion(data) {
        // Remover banner anterior si existe
        $('#cotizacion-info-banner').remove();
        
        const totalNum = parseFloat(data.total) || 0;
        const banner = `
            <div class="alert mb-3" id="cotizacion-info-banner" 
                style="background: linear-gradient(135deg, #e3f2fd, #bbdefb); border-left: 4px solid #2196F3; border-radius: 8px;">
                <div class="d-flex align-items-start">
                    <i class="ri-information-line me-2" style="font-size: 1.5rem; color: #1976D2;"></i>
                    <div class="flex-grow-1">
                        <h6 class="mb-2" style="color: #1565C0; font-weight: 600;">
                            Información de la Cotización #${data.cotizacion_id}
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="ri-user-line me-2" style="color: #1976D2;"></i>
                                    <span><strong>Cliente:</strong> ${data.cliente.nombre} ${data.cliente.apellido || ''}</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="ri-bank-card-line me-2" style="color: #1976D2;"></i>
                                    <span><strong>Documento:</strong> ${data.cliente.documento}</span>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="mb-1">
                                    <strong>Total Cotizado:</strong> 
                                    <span style="color: #2ecc71; font-size: 1.2rem; font-weight: 700;">$${totalNum.toFixed(2)}</span>
                                </div>
                                <div>
                                    <strong>Productos:</strong> <span class="badge bg-primary">${data.productos.length}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Insertar banner al inicio del modal-body
        $('#showModal .modal-body').prepend(banner);
    }

    // Pre-cargar productos desde cotización (SIMPLIFICADO)
    function preCargarProductosDesdeCotizacion(productos, totalCotizacion) {
        console.log('Cargando productos:', productos);
        
        // Generar inputs hidden para cada producto (laravel espera un array)
        let hiddenInputs = '';
        productos.forEach(function(detalle, index) {
            hiddenInputs += `<input type="hidden" name="productos[${index}][producto_id]" value="${detalle.producto_id || detalle.id}">`;
            hiddenInputs += `<input type="hidden" name="productos[${index}][cantidad]" value="${detalle.cantidad}">`;
            hiddenInputs += `<input type="hidden" name="productos[${index}][precio_unitario]" value="${detalle.precio_unitario}">`;
            hiddenInputs += `<input type="hidden" name="productos[${index}][descripcion]" value="${detalle.descripcion || ''}">`;
            hiddenInputs += `<input type="hidden" name="productos[${index}][lleva_bordado]" value="${detalle.lleva_bordado ? 1 : 0}">`;
            
            if (detalle.lleva_bordado) {
                 hiddenInputs += `<input type="hidden" name="productos[${index}][nombre_logo]" value="${detalle.nombre_logo || ''}">`;
                 hiddenInputs += `<input type="hidden" name="productos[${index}][ubicacion_logo]" value="${detalle.ubicacion_logo || ''}">`;
                 hiddenInputs += `<input type="hidden" name="productos[${index}][cantidad_logo]" value="${detalle.cantidad_logo || 1}">`;
            }
            
            hiddenInputs += `<input type="hidden" name="productos[${index}][talla]" value="${detalle.talla || ''}">`;
            hiddenInputs += `<input type="hidden" name="productos[${index}][color]" value="${detalle.color || ''}">`;

            // Insumos
            if (detalle.insumos && Array.isArray(detalle.insumos)) {
                detalle.insumos.forEach(function(insumo, insumoIndex) {
                     hiddenInputs += `<input type="hidden" name="productos[${index}][insumos][${insumoIndex}][id]" value="${insumo.insumo_id || insumo.id}">`;
                     hiddenInputs += `<input type="hidden" name="productos[${index}][insumos][${insumoIndex}][cantidad_estimada]" value="${insumo.cantidad_estimada || insumo.cantidad}">`;
                });
            }
        });
        
        // Agregar al formulario usando un contenedor especial
        if ($('#hidden-products-container').length === 0) {
            $('#pedidoForm').append('<div id="hidden-products-container"></div>');
        }
        $('#hidden-products-container').html(hiddenInputs);
        
        // Mostrar productos en el contenedor de forma visual (read-only)
        const $container = $('#productos-container');
        $container.empty();
        
        // Título
        $container.append(`
            <div class="alert alert-info mb-2" style="background: rgba(33, 150, 243, 0.1); border-left: 3px solid #2196F3;">
                <i class="ri-shopping-bag-line me-2"></i><strong>Productos de la Cotización</strong> <small class="text-muted">(Solo lectura)</small>
            </div>
        `);
        
        // Renderizar cada producto como una card
        productos.forEach(function(detalle, index) {
            const cantidad = parseInt(detalle.cantidad) || 0;
            const precioUnit = parseFloat(detalle.precio_unitario) || 0;
            const subtotal = cantidad * precioUnit;
            
            const cardHTML = `
                <div class="card mb-2 border-start border-3 border-primary" style="background: #f8f9fa;">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <h6 class="mb-1 text-primary">${detalle.producto_nombre || 'Producto'}</h6>
                                ${detalle.descripcion ? `<small class="text-muted d-block mb-1">${detalle.descripcion}</small>` : ''}
                                <div class="d-flex gap-2 flex-wrap">
                                    ${detalle.talla ? `<span class="badge bg-secondary">Talla: ${detalle.talla}</span>` : ''}
                                    ${detalle.lleva_bordado ? '<span class="badge bg-info">Con Bordado</span>' : ''}
                                    ${detalle.nombre_logo ? `<span class="badge bg-warning text-dark">Logo: ${detalle.nombre_logo}</span>` : ''}
                                </div>
                            </div>
                            <div class="col-md-5 text-end">
                                <div class="mb-1"><small class="text-muted">Cantidad:</small> <strong>${cantidad}</strong></div>
                                <div class="mb-1"><small class="text-muted">Precio Unit.:</small> <strong>$${precioUnit.toFixed(2)}</strong></div>
                                <div class="text-primary fs-5"><strong>$${subtotal.toFixed(2)}</strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $container.append(cardHTML);
        });
        
        // Actualizar total
        const totalNum = parseFloat(totalCotizacion) || 0;
        $('#total-display-field').val(totalNum.toFixed(2));
    }

    // Limpiar formulario al cerrar modal
    $(document).on('hidden.bs.modal', '#showModal', function() {
        if (cotizacionSeleccionada) {
            console.log('Limpiando formulario');
            
            // Reset formulario
            $('#pedidoForm')[0].reset();
            $('#cotizacion-info-banner').remove();
            $('#cotizacion-id-field').remove();
            $('#hidden-products-container').remove();
            $('#productos-container').empty();
            
            // Mostrar de nuevo los campos de cliente específicos que ocultamos
            $('#cliente-nombre-field').closest('.mb-3').show();
            $('#ci-rif-number-field').closest('.mb-3').show();
            $('#cliente-email-field').closest('.mb-3').show();
            $('#cliente-telefono-field').closest('.mb-3').show();
            
            // Reset título
            $('#modalTitle').text('Agregar Pedido');
            
            cotizacionSeleccionada = null;
        }
    });

    // Calcular restante automáticamente
    $('#abono-field').on('input', function() {
        calcularRestante();
    });

    function calcularRestante() {
        const total = parseFloat($('#total-display-field').val()) || 0;
        const abono = parseFloat($('#abono-field').val()) || 0;
        const restante = total - abono;
        $('#restante-display-field').val(restante.toFixed(2));
    }
</script>
