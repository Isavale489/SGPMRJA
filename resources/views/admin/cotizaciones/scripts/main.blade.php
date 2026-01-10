<style>
    /* Paleta de colores marca Atlántico */
    :root {
        --atlantico-dark-blue: #1e3c72;
        --atlantico-green: #2ecc71;
        --atlantico-cyan: #00d9a5;
        --atlantico-light-cyan: #38ef7d;
    }

    /* Soft background colors for icons - Paleta Atlántico */
    .bg-soft-primary {
        background-color: rgba(30, 60, 114, 0.1) !important;
        /* azul oscuro */
    }

    .bg-soft-secondary {
        background-color: rgba(46, 204, 113, 0.15) !important;
        /* verde */
    }

    .bg-soft-success {
        background-color: rgba(0, 217, 165, 0.1) !important;
        /* cyan */
    }

    .bg-soft-info {
        background-color: rgba(56, 239, 125, 0.1) !important;
        /* verde claro */
    }

    .bg-soft-warning {
        background-color: rgba(46, 204, 113, 0.2) !important;
        /* verde más intenso */
    }

    .bg-soft-danger {
        background-color: rgba(30, 60, 114, 0.15) !important;
        /* azul oscuro más intenso */
    }

    /* Colores de texto personalizados */
    .text-atlantico-dark {
        color: #1e3c72 !important;
    }

    .text-atlantico-green {
        color: #2ecc71 !important;
    }

    .text-atlantico-cyan {
        color: #00d9a5 !important;
    }
</style>
<script>
    $(document).ready(function () {
        var table = $('#cotizaciones-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('cotizaciones.data') }}",
            columns: [
                { data: 'id', name: 'id', title: 'Nro. de cotización' },
                { data: 'cliente_nombre', name: 'cliente_nombre' },
                { data: 'cliente_email', name: 'cliente_email' },
                { data: 'cliente_telefono', name: 'cliente_telefono' },
                { data: 'ci_rif', name: 'ci_rif' },
                { data: 'fecha_cotizacion', name: 'fecha_cotizacion' },
                { data: 'fecha_validez', name: 'fecha_validez' },
                {
                    data: 'estado',
                    name: 'estado',
                    render: function (data, type, row) {
                        // Si ya fue convertida o está vencida, solo mostrar badge
                        if (data === 'Convertida') {
                            return '<span class="badge bg-primary"><i class="ri-check-double-line me-1"></i>Convertida</span>';
                        }
                        if (data === 'Vencida') {
                            return '<span class="badge bg-dark"><i class="ri-time-line me-1"></i>Vencida</span>';
                        }
                        // Dropdown para cambiar estado
                        var options = ['Pendiente', 'Aprobada', 'Rechazada'];
                        var badgeClass = {
                            'Pendiente': 'bg-warning text-dark',
                            'Aprobada': 'bg-success',
                            'Rechazada': 'bg-danger'
                        };
                        return `
                            <select class="form-select form-select-sm estado-dropdown ${badgeClass[data] || ''}" 
                                    data-id="${row.id}" 
                                    data-original="${data}"
                                    style="min-width: 120px; font-weight: 500;">
                                ${options.map(opt => `<option value="${opt}" ${data === opt ? 'selected' : ''}>${opt}</option>`).join('')}
                            </select>
                        `;
                    }
                },
                { data: 'total', name: 'total' },
                { data: 'user.name', name: 'user.name', defaultContent: '' },
                {
                    data: 'id',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        var isAdmin = {{ Auth::user()->isAdmin() ? 'true' : 'false' }};

                        // Botón Convertir a Pedido (solo si está Aprobada)
                        var convertBtn = '';
                        if (row.estado === 'Aprobada' && isAdmin) {
                            convertBtn = `
                                <li>
                                    <button class="dropdown-item convert-to-pedido-btn" data-id="${data}">
                                        <i class="ri-exchange-line align-bottom me-2 text-success"></i> Convertir a Pedido
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            `;
                        }

                        var editDelete = isAdmin ? `
                            <li>
                                <button class="dropdown-item edit-btn" data-id="${data}">
                                    <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Editar
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item remove-btn" data-id="${data}">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Eliminar
                                </button>
                            </li>` : '';
                        return `
            <div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <button class="dropdown-item view-btn" data-id="${data}">
                            <i class="ri-eye-fill align-bottom me-2 text-muted"></i> Ver
                        </button>
                    </li>
                    ${convertBtn}
                    ${editDelete}
                    <li>
                        <a class="dropdown-item" href="/cotizaciones/${data}/pdf" target="_blank">
                            <i class="ri-file-pdf-line align-bottom me-2 text-danger"></i> PDF
                        </a>
                    </li>
                </ul>
            </div>
        `;
                    }
                }
            ],
            order: [[0, 'desc']],
            language: {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix": "",
                "sSearch": "Buscar:",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                },
                "buttons": {
                    "copy": "Copiar",
                    "csv": "CSV",
                    "excel": "Excel",
                    "pdf": "PDF",
                    "print": "Imprimir",
                    "colvis": "Visibilidad de Columna"
                }
            }
        });
        // Ajustar columnas cuando se redimensiona la ventana
        $(window).on('resize', function () {
            table.columns.adjust();
        });
        // Ajustar después de carga inicial
        setTimeout(function () {
            table.columns.adjust();
        }, 100);

        // === Lógica de productos, insumos y pagos igual que en pedidos ===
        var products = @json($productos);
        var insumos = @json($insumos);
        var productItemIndex = 0;

        function addProductItem(productoId = '', cantidad = '', precioUnitario = '', descripcion = '', llevaBordado = false, nombreLogo = '', talla = '', ubicacionLogo = '', cantidadLogo = 1) {
            var productOptions = '<option value="">Seleccione un producto</option>';
            products.forEach(function (product) {
                var tipoNombre = product.tipo_producto ? product.tipo_producto.nombre : 'Sin tipo';
                var displayName = product.codigo ? product.codigo + ' - ' + tipoNombre + ' ' + product.modelo : tipoNombre + ' ' + product.modelo;
                productOptions += `<option value="${product.id}" data-precio="${product.precio_base}" data-modelo="${product.modelo}"${productoId == product.id ? ' selected' : ''}>${displayName}</option>`;
            });

            var itemHtml = `
            <div class="card mb-3 shadow-lg border-dark">
                <div class="card-body">
                    <h5 class="card-title">Nuevo Producto</h5>
                    <div class="row product-item">
                        <div class="col-md-8">
                            <select name="productos[${productItemIndex}][producto_id]" class="form-control product-select" required>
                                ${productOptions}
                            </select>
                            <span class="ms-1 fw-bold text-success precio-producto-span">${precioUnitario ? '$' + parseFloat(precioUnitario).toFixed(2) : ''}</span>
                        </div>
                        <div class="col-md-4">
                            <input type="number" name="productos[${productItemIndex}][cantidad]" class="form-control cantidad-input" placeholder="Cantidad" min="1" value="${cantidad}" required />
                        </div>
                        <div class="col-md-6 mt-2">
                            <select name="productos[${productItemIndex}][talla]" class="form-control">
                                <option value="">Talla (opcional)</option>
                                <option value="Talla Unica" ${talla == 'Talla Unica' ? 'selected' : ''}>Talla Unica</option>
                                <option value="2" ${talla == '2' ? 'selected' : ''}>2</option>
                                <option value="4" ${talla == '4' ? 'selected' : ''}>4</option>
                                <option value="6" ${talla == '6' ? 'selected' : ''}>6</option>
                                <option value="8" ${talla == '8' ? 'selected' : ''}>8</option>
                                <option value="10" ${talla == '10' ? 'selected' : ''}>10</option>
                                <option value="12" ${talla == '12' ? 'selected' : ''}>12</option>
                                <option value="14" ${talla == '14' ? 'selected' : ''}>14</option>
                                <option value="16" ${talla == '16' ? 'selected' : ''}>16</option>
                                <option value="XS" ${talla == 'XS' ? 'selected' : ''}>XS</option>
                                <option value="S" ${talla == 'S' ? 'selected' : ''}>S</option>
                                <option value="M" ${talla == 'M' ? 'selected' : ''}>M</option>
                                <option value="L" ${talla == 'L' ? 'selected' : ''}>L</option>
                                <option value="XL" ${talla == 'XL' ? 'selected' : ''}>XL</option>
                                <option value="XXL" ${talla == 'XXL' ? 'selected' : ''}>XXL</option>
                            </select>
                        </div>
                        <div class="col-md-12 mt-2">
                            <textarea name="productos[${productItemIndex}][descripcion]" class="form-control" placeholder="Descripción del producto (opcional)" rows="2">${descripcion}</textarea>
                        </div>
                        <div class="col-md-12 mt-2">
                            <div class="form-check form-switch">
                                <input type="hidden" name="productos[${productItemIndex}][lleva_bordado]" value="0">
                                <input class="form-check-input lleva-bordado-checkbox" type="checkbox" id="lleva-bordado-${productItemIndex}" name="productos[${productItemIndex}][lleva_bordado]" value="1" ${llevaBordado ? 'checked' : ''}>
                                <label class="form-check-label" for="lleva-bordado-${productItemIndex}">Lleva Bordado/Logo</label>
                            </div>
                        </div>
                        <div class="col-md-12 mt-2 nombre-logo-container" style="display: ${llevaBordado ? 'block' : 'none'}">
                            <input type="text" name="productos[${productItemIndex}][nombre_logo]" class="form-control nombre-logo-input" placeholder="Nombre del logo a bordar" value="${nombreLogo}" />
                            <input type="text" name="productos[${productItemIndex}][ubicacion_logo]" class="form-control mt-2 ubicacion-logo-input" placeholder="Ubicación del bordado/logo (ej: Pecho izquierdo)" value="${ubicacionLogo || ''}" />
                            <input type="number" name="productos[${productItemIndex}][cantidad_logo]" class="form-control mt-2 cantidad-logo-input" placeholder="Cantidad de logos/bordados" min="1" value="${cantidadLogo || 1}" />
                        </div>
                        <input type="hidden" name="productos[${productItemIndex}][precio_unitario]" class="precio-unitario-input" value="${precioUnitario}" />
                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-danger btn-sm remove-producto-item">Eliminar Producto</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
            $('#productos-container').append(itemHtml);

            // Inicializar Select2 con búsqueda para el select de productos
            $('#productos-container').find('.product-select').last().select2({
                theme: 'bootstrap-5',
                placeholder: '🔍 Buscar producto...',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#showModal').length ? $('#showModal') : $('body'),
                language: {
                    noResults: function () {
                        return 'No se encontraron productos';
                    },
                    searching: function () {
                        return 'Buscando...';
                    }
                }
            });

            productItemIndex++;
        }

        function addInsumoItem(currentProductItemIndex, insumoId = '', cantidadEstimada = '') {
            let insumoIndex = $(`#insumos-container-${currentProductItemIndex}`).find('.insumo-row').length;
            let newInsumoRow = `
            <div class="row insumo-row mt-2">
                <div class="col-md-5">
                    <select name="productos[${currentProductItemIndex}][insumos][${insumoIndex}][id]" class="form-control insumo-select" required>
                        <option value="">Seleccione insumo...</option>
                        ${insumos.map(insumo => `<option value="${insumo.id}"${insumoId == insumo.id ? ' selected' : ''}>${insumo.nombre} (${insumo.unidad_medida})</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" name="productos[${currentProductItemIndex}][insumos][${insumoIndex}][cantidad_estimada]" class="form-control" placeholder="Cantidad" step="0.01" min="0.01" value="${cantidadEstimada}" required />
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-danger btn-sm form-control insumo-btn-height remove-insumo">Eliminar Insumo</button>
                </div>
            </div>
        `;
            $(`#insumos-container-${currentProductItemIndex}`).append(newInsumoRow);
        }

        // Evento para agregar más productos
        $('#add-producto-item').on('click', function () {
            addProductItem();
        });

        // Evento para remover producto
        $('#productos-container').on('click', '.remove-producto-item', function () {
            $(this).closest('.card').remove();
        });

        // Evento para agregar más insumos (delegado)
        $('#productos-container').on('click', '.add-insumo-btn', function () {
            let currentProductItemIndex = $(this).data('product-item-index');
            addInsumoItem(currentProductItemIndex);
        });

        // Remover fila de insumo (delegado)
        $('#productos-container').on('click', '.remove-insumo', function () {
            $(this).closest('.insumo-row').remove();
        });

        // Mostrar/ocultar campo nombre_logo
        $('#productos-container').on('change', '.lleva-bordado-checkbox', function () {
            var container = $(this).closest('.product-item').find('.nombre-logo-container');
            if ($(this).is(':checked')) {
                container.show();
                container.find('.nombre-logo-input, .ubicacion-logo-input, .cantidad-logo-input').prop('required', true);
            } else {
                container.hide();
                container.find('.nombre-logo-input, .ubicacion-logo-input, .cantidad-logo-input').val('').prop('required', false);
            }
        });

        // Calcular el total de la cotización y el restante
        function calculateCotizacionTotals() {
            let sum = 0;
            $('#productos-container .product-item').each(function () {
                let quantity = parseFloat($(this).find('.cantidad-input').val()) || 0;
                let price = parseFloat($(this).find('.precio-unitario-input').val()) || 0;
                sum += (quantity * price);
            });
            $('#total-display-field').val(sum.toFixed(2));
            updateCotizacionRemaining();
        }
        function updateCotizacionRemaining() {
            let abono = parseFloat($('#abono-field').val()) || 0;
            let total = parseFloat($('#total-display-field').val()) || 0;
            let restante = total - abono;
            $('#restante-display-field').val(restante.toFixed(2));
        }
        // Recalcular total cuando cambia la cantidad o el producto
        $('#productos-container').on('change', '.cantidad-input', calculateCotizacionTotals);
        $('#productos-container').on('change', '.product-select', function () {
            var selectedOption = $(this).find('option:selected');
            var precio = selectedOption.data('precio');
            var spanPrecio = $(this).closest('.product-item').find('.precio-producto-span');

            $(this).closest('.card').find('.precio-unitario-input').val(precio);

            if (precio) {
                spanPrecio.text('$' + parseFloat(precio).toFixed(2));
            } else {
                spanPrecio.text('');
            }

            calculateCotizacionTotals();
        });
        $('#abono-field').on('change keyup', updateCotizacionRemaining);
        // Inicializar con un producto vacío al abrir el modal de creación
        $('#create-btn').on('click', function () {
            $('#modalTitle').text('Agregar Cotización');
            $('#cotizacionForm')[0].reset();
            $('#id-field').val('');
            $('#cliente-id-field').val('');
            $('#add-btn').show();
            $('#edit-btn').hide();
            $('#estado-field-wrapper').hide();
            $('#productos-container').empty();
            addProductItem();
            calculateCotizacionTotals();
        });

        // Botón para abrir modal de creación
        $('#create-btn').on('click', function () {
            $('#modalTitle').text('Agregar Cotización');
            $('#cotizacionForm')[0].reset();
            $('#id-field').val('');
            $('#cliente-id-field').val('');
            $('#add-btn').show();
            $('#edit-btn').hide();
            $('#estado-field-wrapper').hide();
            $('#productos-container').empty();
            // Aquí puedes agregar la lógica para añadir un producto vacío si lo deseas
        });

        // Envío del formulario de cotización (crear/editar)
        $('#cotizacionForm').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            var id = $('#id-field').val();
            var url = id ? '/cotizaciones/' + id : '/cotizaciones';
            if (id) {
                formData.append('_method', 'PUT');
            }
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: response.success,
                        icon: 'success',
                        confirmButtonClass: 'btn btn-primary w-xs me-2',
                        buttonsStyling: false,
                        showCloseButton: true
                    })
                    $('#showModal').modal('hide');
                    table.ajax.reload();
                },
                error: function (xhr) {
                    var errorMessage = '';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        for (var key in errors) {
                            errorMessage += errors[key][0] + '\n';
                        }
                    } else {
                        errorMessage = 'Ocurrió un error inesperado.';
                    }
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonClass: 'btn btn-primary w-xs me-2',
                        buttonsStyling: false,
                        showCloseButton: true
                    })
                }
            });
        });

        // Botón de editar
        $('#cotizaciones-table').on('click', '.edit-btn', function () {
            var id = $(this).data('id');
            $('#modalTitle').text('Editar Cotización');
            $('#id-field').val(id);
            $('#add-btn').hide();
            $('#edit-btn').show();
            $('#estado-field-wrapper').show();
            $('#productos-container').empty();
            $.ajax({
                url: '/cotizaciones/' + id,
                method: 'GET',
                success: function (data) {
                    $('#cliente-id-field').val(data.cliente_id || '');
                    // Obtener datos del cliente desde la relación
                    if (data.cliente) {
                        $('#cliente-nombre-field').val(data.cliente.nombre);
                        $('#cliente-apellido-field').val(data.cliente.apellido || '');
                        $('#cliente-email-field').val(data.cliente.email || '');
                        $('#cliente-telefono-field').val(data.cliente.telefono || '');
                        var documento = data.cliente.documento || '';
                        if (documento) {
                            var prefix = documento.substring(0, 2);
                            var number = documento.substring(2);
                            $('#ci-rif-prefix-field').val(prefix);
                            $('#ci-rif-number-field').val(number);
                            $('#ci-rif-full-field').val(documento);
                        }
                    }
                    $('#fecha-cotizacion-field').val(data.fecha_cotizacion);
                    $('#fecha-validez-field').val(data.fecha_validez);
                    $('#estado-field').val(data.estado);
                    // Cargar productos existentes
                    $('#productos-container').empty();
                    if (data.productos && data.productos.length > 0) {
                        productItemIndex = 0;
                        data.productos.forEach(function (detalle) {
                            addProductItem(
                                detalle.producto_id,
                                detalle.cantidad,
                                detalle.precio_unitario,
                                detalle.descripcion,
                                detalle.lleva_bordado,
                                detalle.nombre_logo,
                                detalle.talla,
                                detalle.ubicacion_logo,
                                detalle.cantidad_logo
                            );
                        });
                    }
                    calculateCotizacionTotals();
                    $('#showModal').modal('show');
                }
            });
        });

        // Botón de ver detalles
        $('#cotizaciones-table').on('click', '.view-btn', function () {
            var id = $(this).data('id');
            $.ajax({
                url: '/cotizaciones/' + id,
                method: 'GET',
                success: function (data) {
                    // Obtener datos del cliente desde la relación
                    if (data.cliente) {
                        // Mostrar nombre con badge si cliente fue eliminado
                        var nombreHtml = data.cliente.nombre || 'N/A';
                        if (data.cliente.eliminado) {
                            nombreHtml += ' <span class="badge bg-danger ms-1" title="Este cliente fue eliminado">Eliminado</span>';
                        }
                        $('#view-cliente-nombre').html(nombreHtml);

                        // Si cliente eliminado, mostrar datos con estilo atenuado
                        var mutedClass = data.cliente.eliminado ? 'text-muted' : '';
                        $('#view-cliente-apellido').html(data.cliente.eliminado
                            ? '<span class="text-muted">' + (data.cliente.apellido || '') + '</span>'
                            : (data.cliente.apellido || ''));
                        $('#view-cliente-email').html(data.cliente.eliminado
                            ? '<span class="text-muted">' + (data.cliente.email || 'N/A') + '</span>'
                            : (data.cliente.email || 'N/A'));
                        $('#view-cliente-telefono').html(data.cliente.eliminado
                            ? '<span class="text-muted">' + (data.cliente.telefono || 'N/A') + '</span>'
                            : (data.cliente.telefono || 'N/A'));
                        $('#view-ci-rif').html(data.cliente.eliminado
                            ? '<span class="text-muted">' + (data.cliente.documento || 'N/A') + '</span>'
                            : (data.cliente.documento || 'N/A'));
                    } else {
                        $('#view-cliente-nombre').html('<span class="text-danger">Cliente no encontrado</span>');
                        $('#view-cliente-apellido').text('');
                        $('#view-cliente-email').text('N/A');
                        $('#view-cliente-telefono').text('N/A');
                        $('#view-ci-rif').text('N/A');
                    }
                    $('#view-fecha-cotizacion').text(data.fecha_cotizacion);
                    $('#view-fecha-validez').text(data.fecha_validez);
                    $('#view-estado').text(data.estado);
                    $('#view-usuario-creador').text(data.user ? data.user.name : '');
                    // Mostrar productos de la cotización con el mismo diseño que pedidos
                    var productosBody = $('#view-productos-container');
                    productosBody.empty();
                    if (data.productos && data.productos.length > 0) {
                        data.productos.forEach(function (item, index) {
                            var subtotal = item.cantidad * item.precio_unitario;
                            productosBody.append(`
                                <div class="card border-0 shadow-sm mb-3" style="border-left: 4px solid #00d9a5 !important;">
                                    <div class="card-body p-3">
                                        <!-- Header del Producto -->
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="rounded-circle me-3 d-flex align-items-center justify-content-center"
                                                style="width: 45px; height: 45px; background: #1e3c72;">
                                                <i class="ri-t-shirt-2-line text-white fs-5"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold" style="color: #1e3c72;">${item.producto ? (item.producto.nombre_completo || item.producto.modelo || 'Producto') : 'Sin producto'}</h6>
                                                <small class="text-muted">Producto #${index + 1}</small>
                                            </div>
                                            <div class="ms-auto">
                                                <span class="badge" style="background: #00d9a5; font-size: 0.9rem;">
                                                    $${subtotal.toFixed(2)}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Detalles del Producto -->
                                        <div class="row g-2 mb-3">
                                            <div class="col-6 col-md-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                        style="width: 28px; height: 28px; background: rgba(46, 204, 113, 0.15);">
                                                        <i class="ri-stack-line" style="color: #2ecc71; font-size: 0.85rem;"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size: 0.7rem;">Cantidad</small>
                                                        <span class="fw-semibold" style="font-size: 0.85rem;">${item.cantidad}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                        style="width: 28px; height: 28px; background: rgba(30, 60, 114, 0.15);">
                                                        <i class="ri-t-shirt-line" style="color: #1e3c72; font-size: 0.85rem;"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size: 0.7rem;">Talla</small>
                                                        <span class="fw-semibold" style="font-size: 0.85rem;">${item.talla || 'N/A'}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                        style="width: 28px; height: 28px; background: rgba(46, 204, 113, 0.15);">
                                                        <i class="ri-money-dollar-circle-line" style="color: #2ecc71; font-size: 0.85rem;"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size: 0.7rem;">P. Unitario</small>
                                                        <span class="fw-semibold" style="font-size: 0.85rem;">$${parseFloat(item.precio_unitario).toFixed(2)}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                        style="width: 28px; height: 28px; background: rgba(0, 217, 165, 0.15);">
                                                        <i class="ri-scissors-cut-line" style="color: #00d9a5; font-size: 0.85rem;"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size: 0.7rem;">Bordado</small>
                                                        <span class="badge ${item.lleva_bordado ? 'bg-success' : 'bg-secondary'}" style="font-size: 0.75rem;">${item.lleva_bordado ? 'Sí' : 'No'}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Bordado/Logo si aplica -->
                                        ${item.lleva_bordado ? `
                                        <div class="rounded p-2 mb-3" style="background: rgba(0, 217, 165, 0.08);">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="ri-scissors-cut-line me-2" style="color: #00d9a5;"></i>
                                                <span class="fw-semibold" style="color: #00d9a5; font-size: 0.85rem;">Bordado / Logo</span>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <small class="text-muted">Logo:</small>
                                                    <span class="fw-semibold ms-1" style="font-size: 0.85rem;">${item.nombre_logo || 'N/A'}</span>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Ubicación:</small>
                                                    <span class="fw-semibold ms-1" style="font-size: 0.85rem;">${item.ubicacion_logo || 'N/A'}</span>
                                                </div>
                                            </div>
                                        </div>
                                        ` : ''}
                                        
                                        <!-- Descripción -->
                                        ${item.descripcion ? `
                                        <div class="rounded p-2" style="background: rgba(30, 60, 114, 0.05);">
                                            <div class="d-flex align-items-start">
                                                <i class="ri-file-text-line me-2 mt-1" style="color: #1e3c72;"></i>
                                                <div>
                                                    <small class="text-muted d-block">Descripción</small>
                                                    <span style="font-size: 0.85rem;">${item.descripcion}</span>
                                                </div>
                                            </div>
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                            `);
                        });
                    } else {
                        productosBody.append('<p class="text-muted text-center py-4"><i class="ri-file-list-3-line fs-1 d-block mb-2"></i>No hay productos en esta cotización.</p>');
                    }
                    // Formatear el total
                    $('#view-total').text('$' + parseFloat(data.total).toFixed(2));
                    // Establecer enlace PDF
                    $('#view-pdf-btn').attr('href', '/cotizaciones/' + id + '/pdf');
                    $('#viewModal').modal('show');
                }
            });
        });

        // Botón de eliminar
        $('#cotizaciones-table').on('click', '.remove-btn', function () {
            var id = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonClass: 'btn btn-danger w-xs me-2',
                cancelButtonClass: 'btn btn-light w-xs',
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/cotizaciones/' + id,
                        method: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            Swal.fire({
                                title: '¡Eliminado!',
                                text: response.success,
                                icon: 'success',
                                confirmButtonClass: 'btn btn-primary w-xs me-2',
                                buttonsStyling: false,
                                showCloseButton: true
                            })
                            table.ajax.reload();
                        },
                        error: function (xhr) {
                            Swal.fire({
                                title: 'Error!',
                                text: 'No se pudo eliminar la cotización.',
                                icon: 'error',
                                confirmButtonClass: 'btn btn-primary w-xs me-2',
                                buttonsStyling: false,
                                showCloseButton: true
                            })
                        }
                    });
                }
            });
        });

        // === CAMBIO DE ESTADO VIA DROPDOWN ===
        $('#cotizaciones-table').on('change', '.estado-dropdown', function () {
            var $select = $(this);
            var cotizacionId = $select.data('id');
            var nuevoEstado = $select.val();
            var estadoOriginal = $select.data('original');

            $.ajax({
                url: '/cotizaciones/' + cotizacionId + '/estado',
                method: 'PUT',
                data: {
                    estado: nuevoEstado,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response.success,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    $select.data('original', nuevoEstado);
                    table.ajax.reload(null, false);
                },
                error: function (xhr) {
                    $select.val(estadoOriginal);
                    var errorMsg = xhr.responseJSON?.error || 'Error al cambiar el estado.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                }
            });
        });

        // === CONVERTIR COTIZACIÓN A PEDIDO ===
        $('#cotizaciones-table').on('click', '.convert-to-pedido-btn', function () {
            var cotizacionId = $(this).data('id');

            Swal.fire({
                title: '¿Convertir a Pedido?',
                text: 'Se abrirá el formulario de nuevo pedido con los datos de esta cotización.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="ri-exchange-line me-1"></i> Sí, convertir',
                cancelButtonText: 'Cancelar',
                confirmButtonClass: 'btn btn-success me-2',
                cancelButtonClass: 'btn btn-light',
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    localStorage.setItem('cotizacionParaConvertir', cotizacionId);
                    window.location.href = '/pedidos?desde_cotizacion=' + cotizacionId;
                }
            });
        });

        // === AUTOCOMPLETADO DE CLIENTE ===
        let clienteSeleccionado = null;
        let autocompleteTimeout = null;

        $('#cliente-nombre-field').on('input', function () {
            const query = $(this).val();
            clearTimeout(autocompleteTimeout);
            if (query.length < 2) {
                $('#cliente-autocomplete-list').empty().hide();
                return;
            }
            autocompleteTimeout = setTimeout(function () {
                $.ajax({
                    url: '/clientes-search',
                    data: { q: query },
                    success: function (clientes) {
                        let html = '';
                        if (clientes.length > 0) {
                            clientes.forEach(function (cliente) {
                                const nombreCompleto = cliente.apellido ? `${cliente.nombre} ${cliente.apellido}` : cliente.nombre;
                                html += `<button type="button" class="list-group-item list-group-item-action cliente-autocomplete-item" data-id="${cliente.id}" data-nombre="${cliente.nombre}" data-apellido="${cliente.apellido || ''}" data-email="${cliente.email || ''}" data-telefono="${cliente.telefono || ''}" data-documento="${cliente.documento || ''}">${nombreCompleto} <small class='text-muted'>${cliente.documento || 'Sin documento'} - ${cliente.email || 'Sin email'}</small></button>`;
                            });
                        } else {
                            html = '<div class="list-group-item disabled">No se encontraron clientes</div>';
                        }
                        $('#cliente-autocomplete-list').html(html).show();
                    }
                });
            }, 300);
        });

        // Selección de cliente de la lista
        $(document).on('click', '.cliente-autocomplete-item', function () {
            const $this = $(this);
            const clienteId = $this.data('id');
            const nombre = $this.data('nombre') || '';
            const apellido = $this.data('apellido') || '';
            const email = $this.data('email') || '';
            const telefono = $this.data('telefono') || '';
            const documento = $this.data('documento');

            // Guardar cliente_id
            $('#cliente-id-field').val(clienteId);

            // Llenar campos básicos
            $('#cliente-nombre-field').val(nombre);
            $('#cliente-apellido-field').val(apellido);
            $('#cliente-email-field').val(email);
            $('#cliente-telefono-field').val(telefono);

            // Procesar documento - Convertir siempre a string primero
            let prefix = 'V-';
            let number = '';
            let docString = '';
            if (documento !== undefined && documento !== null && documento !== '') {
                docString = String(documento).trim();
            }
            if (docString.length > 0) {
                if (docString.startsWith('V-') || docString.startsWith('J-') || docString.startsWith('E-') || docString.startsWith('G-')) {
                    prefix = docString.substring(0, 2);
                    number = docString.substring(2);
                } else {
                    number = docString;
                    if (docString.length >= 8 && /^[2-9]/.test(docString)) {
                        prefix = 'J-';
                    } else {
                        prefix = 'V-';
                    }
                }
            }
            $('#ci-rif-prefix-field').val(prefix);
            $('#ci-rif-number-field').val(number);
            $('#ci-rif-full-field').val(prefix + number);
            $('#cliente-autocomplete-list').empty().hide();
            clienteSeleccionado = true;
        });

        // Ocultar lista al hacer click fuera
        $(document).on('click', function (e) {
            if (!$(e.target).closest('#cliente-nombre-field, #cliente-autocomplete-list').length) {
                $('#cliente-autocomplete-list').empty().hide();
            }
        });

        // --- MODAL AGREGAR CLIENTE DESDE COTIZACIÓN ---
        $('#open-add-cliente-modal').on('click', function () {
            $('#clienteFormCotizacion')[0].reset();
            $('#id-field-cliente').val('');
            $('#modalClienteTitle').text('Agregar Cliente');
            $('#add-btn-cliente').show();
            $('#edit-btn-cliente').hide();
            $('#modalAddCliente').modal('show');
        });

        // Restaurar el submit por AJAX para el formulario de cliente y mostrar errores personalizados en el modal
        $('#add-btn-cliente').off('click').on('click', function (e) {
            // Unir prefijo y número antes de enviar
            var documentoCompleto = $('#documento-prefix-field-cliente').val() + $('#documento-number-field-cliente').val();
            $('#documento-field-cliente').val(documentoCompleto);
            var form = document.getElementById('clienteFormCotizacion');
            if (!form.checkValidity()) {
                // Si el formulario no es válido, el navegador mostrará la alerta nativa y no se envía
                form.reportValidity();
                return;
            }
            // Si es válido, enviar por AJAX
            var formData = $('#clienteFormCotizacion').serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '/clientes',
                type: 'POST',
                data: formData,
                success: function (response) {
                    Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.message, showConfirmButton: false, timer: 2000 });
                    $('#modalAddCliente').modal('hide');
                    // Rellenar los campos de la cotización con el nuevo cliente
                    const nombre = $('#nombre-field-cliente').val();
                    const email = $('#email-field-cliente').val();
                    const telefono = $('#telefono-field-cliente').val();
                    const documento = documentoCompleto;
                    $('#cliente-nombre-field').val(nombre);
                    $('#cliente-email-field').val(email || '');
                    $('#cliente-telefono-field').val(telefono || '');
                    // Separar prefijo y número del documento
                    let prefix = 'V-';
                    let number = '';
                    if (documento) {
                        if (documento.startsWith('V-') || documento.startsWith('J-') || documento.startsWith('E-') || documento.startsWith('G-')) {
                            prefix = documento.substring(0, 2);
                            number = documento.substring(2);
                        } else {
                            number = documento;
                            if (documento.length >= 8 && ['2', '3', '4', '5', '6', '7', '8', '9'].includes(documento.charAt(0))) {
                                prefix = 'J-';
                            } else {
                                prefix = 'V-';
                            }
                        }
                    }
                    $('#ci-rif-prefix-field').val(prefix);
                    $('#ci-rif-number-field').val(number);
                    $('#ci-rif-full-field').val(prefix + number);
                }
            });
        });
    });
</script>