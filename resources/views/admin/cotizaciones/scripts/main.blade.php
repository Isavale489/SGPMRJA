<style>
    /* Paleta de colores marca Atlántico */
    :root {
        --atlantico-dark-blue: #1e3c72;
        --atlantico-green: #2ecc71;
        --atlantico-cyan: #00d9a5;
        --atlantico-light-cyan: #38ef7d;
    }

    /* Estilos para dropdowns de estado - Paleta Atlántico sobria */
    .estado-pendiente {
        background-color: rgba(30, 60, 114, 0.15) !important;
        border-color: #1e3c72 !important;
        color: #1e3c72 !important;
    }

    .estado-aprobada {
        background-color: rgba(0, 217, 165, 0.15) !important;
        border-color: #00d9a5 !important;
        color: #006b52 !important;
    }

    .estado-rechazada {
        background-color: rgba(139, 58, 58, 0.15) !important;
        border-color: #8b3a3a !important;
        color: #8b3a3a !important;
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

    /* Estilo para buscador personalizado */
    .search-box {
        position: relative;
    }

    .search-box .search-icon {
        position: absolute;
        top: 50%;
        left: 10px;
        transform: translateY(-50%);
        color: #878a99;
    }

    .search-box input {
        padding-left: 30px;
    }
</style>
<script>
    $(document).ready(function () {
        // === FUNCIÓN GLOBAL: Capitalizar solo la primera letra ===
        function capitalizeFirstLetter(str) {
            if (!str) return str;
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        // Aplicar a campos de dirección al perder foco
        $(document).on('blur', '#direccion-field, #direccion-field-cliente, [name="direccion"]', function () {
            var val = $(this).val();
            if (val && val.length > 0) {
                $(this).val(capitalizeFirstLetter(val));
            }
        });
        var table = $('#cotizaciones-table').DataTable({
            responsive: true,
            dom: 'rtip', /* Ocultar buscador (f) y selector de longitud (l) para máxima limpieza */
            processing: true,
            serverSide: true,
            ajax: "{{ route('cotizaciones.data') }}",
            columns: [
                { data: 'id', name: 'id', title: 'Nro.', width: '5%' },
                { data: 'ci_rif', name: 'ci_rif', title: 'Documento', width: '10%' },
                { data: 'cliente_nombre', name: 'cliente_nombre', width: '10%' },
                { data: 'cliente_telefono', name: 'cliente_telefono', width: '10%' },
                { data: 'fecha_cotizacion', name: 'fecha_cotizacion', width: '10%' },
                { data: 'fecha_validez', name: 'fecha_validez', width: '10%' },
                {
                    data: 'total',
                    name: 'total',
                    width: '10%',
                    render: $.fn.dataTable.render.number(',', '.', 2, '$')
                },
                {
                    data: 'estado',
                    name: 'estado',
                    width: '12%',
                    className: 'text-center',
                    render: function (data, type, row) {
                        // Estilos de estado con paleta Atlántico
                        var estadoStyles = {
                            'Pendiente': 'background-color: rgba(30, 60, 114, 0.15); border: 1px solid #1e3c72; color: #1e3c72;',
                            'Aprobada': 'background-color: rgba(46, 204, 113, 0.15); border: 1px solid #2ecc71; color: #1e8449;',
                            'Convertida': 'background-color: rgba(0, 217, 165, 0.15); border: 1px solid #00d9a5; color: #006b52;',
                            'Cancelado': 'background-color: rgba(139, 58, 58, 0.15); border: 1px solid #8b3a3a; color: #8b3a3a;',
                            'Vencida': 'background-color: rgba(139, 58, 58, 0.15); border: 1px solid #8b3a3a; color: #8b3a3a;'
                        };
                        var estadoIcons = {
                            'Pendiente': 'ri-time-line',
                            'Aprobada': 'ri-check-double-line',
                            'Convertida': 'ri-exchange-line',
                            'Cancelado': 'ri-close-circle-line',
                            'Vencida': 'ri-alarm-warning-line'
                        };
                        var style = estadoStyles[data] || 'background-color: #f8f9fa; color: #495057;';
                        var icon = estadoIcons[data] || 'ri-question-line';

                        var badge = '<span class="badge" style="' + style + ' padding: 5px 10px; border-radius: 4px; font-weight: 500;"><i class="' + icon + ' me-1"></i>' + data + '</span>';

                        var isAdmin = {{ Auth::user()->isAdmin() ? 'true' : 'false' }};

                        // Si no es admin o ya está convertida, solo mostrar badge
                        if (!isAdmin || data === 'Convertida') {
                            return badge;
                        }

                        // Construir opciones del dropdown
                        var opciones = '';

                        if (data === 'Pendiente') {
                            opciones += '<li><a class="dropdown-item change-status-btn" href="#" data-id="' + row.id + '" data-status="Aprobada"><i class="ri-check-double-line text-success me-2"></i>Aprobar</a></li>';
                            opciones += '<li><a class="dropdown-item change-status-btn" href="#" data-id="' + row.id + '" data-status="Cancelado"><i class="ri-close-circle-line text-danger me-2"></i>Cancelar</a></li>';
                        } else if (data === 'Aprobada') {
                            opciones += '<li><a class="dropdown-item change-status-btn" href="#" data-id="' + row.id + '" data-status="Pendiente"><i class="ri-time-line text-warning me-2"></i>Volver a Pendiente</a></li>';
                            opciones += '<li><a class="dropdown-item change-status-btn" href="#" data-id="' + row.id + '" data-status="Cancelado"><i class="ri-close-circle-line text-danger me-2"></i>Cancelar</a></li>';
                        } else if (data === 'Cancelado' || data === 'Vencida') {
                            opciones += '<li><a class="dropdown-item change-status-btn" href="#" data-id="' + row.id + '" data-status="Pendiente"><i class="ri-time-line text-warning me-2"></i>Reactivar (Pendiente)</a></li>';
                        }

                        if (opciones === '') return badge;

                        return `
                            <div class="dropdown">
                                <a href="#" role="button" id="dropdownMenuLink${row.id}" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none;">
                                    ${badge} <i class="ri-arrow-down-s-line ms-1 text-muted"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink${row.id}">
                                    ${opciones}
                                </ul>
                            </div>
                        `;
                    }
                },
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
                                <button class="btn btn-sm btn-soft-primary convert-to-pedido-btn" data-id="${data}" title="Convertir a Pedido">
                                    <i class="ri-exchange-line"></i>
                                </button>
                            `;
                        }

                        // Botones Editar y Eliminar (solo si NO está Convertida ni Cancelado)
                        var editDelete = '';
                        if (isAdmin && row.estado !== 'Convertida' && row.estado !== 'Cancelado' && row.estado !== 'Vencida') {
                            editDelete = `
                            <button class="btn btn-sm btn-soft-success edit-btn" data-id="${data}" title="Editar">
                                <i class="ri-pencil-fill"></i>
                            </button>
                            <button class="btn btn-sm btn-soft-danger remove-btn" data-id="${data}" title="Eliminar">
                                <i class="ri-delete-bin-fill"></i>
                            </button>`;
                        }

                        // Layout horizontal compacto con gap-1
                        return `
                            <div class="d-flex gap-1 justify-content-center align-items-center flex-wrap">
                                <button class="btn btn-sm btn-soft-info view-btn" data-id="${data}" title="Ver">
                                    <i class="ri-eye-fill"></i>
                                </button>
                                ${convertBtn}
                                ${editDelete}
                                <a class="btn btn-sm btn-soft-secondary" href="/cotizaciones/${data}/pdf" target="_blank" title="PDF">
                                    <i class="ri-file-pdf-line"></i>
                                </a>
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

        // Buscador personalizado
        $('#custom-search-input').on('keyup', function () {
            table.search(this.value).draw();
        });

        // Ajustar columnas cuando se redimensiona la ventana
        $(window).on('resize', function () {
            table.columns.adjust();
        });
        // Ajustar después de carga inicial
        setTimeout(function () {
            table.columns.adjust();
        }, 100);

        // === Lógica de productos (Adaptada de Pedidos) ===
        var products = @json($productos);
        var productItemIndex = 0;
        var productosModalCotizacion = null;
        var currentProductIndex = null; // Para saber qué items editar si fuera el caso

        // Inicializar Modal y cargar datos
        try {
            productosModalCotizacion = new bootstrap.Modal(document.getElementById('productosModalCotizacion'));
            cargarTiposEnFiltroCotizacion();
        } catch (e) {
            console.error('Error inicializando modal cotización:', e);
        }

        // Eventos del Modal
        $('#buscarProductoModalCotizacion').on('keyup', function () {
            renderizarProductosModalCotizacion($(this).val(), $('#filtroTipoProductoCotizacion').val());
        });
        $('#filtroTipoProductoCotizacion').on('change', function () {
            renderizarProductosModalCotizacion($('#buscarProductoModalCotizacion').val(), $(this).val());
        });

        // Botón "Agregar Otro Producto" (Añade tarjeta vacía)
        $('#add-producto-item').on('click', function () {
            addProductItem(); // Agrega tarjeta vacía
            // Opcional: Scrollear hacia el nuevo item
            // var newDetails = $('#productos-container').children().last();
            // if(newDetails.length) newDetails[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        });

        function cargarTiposEnFiltroCotizacion() {
            var tipos = [];
            if (typeof products !== 'undefined' && products) {
                products.forEach(function (p) {
                    if (p.tipo_producto && !tipos.find(t => t.id === p.tipo_producto.id)) {
                        tipos.push(p.tipo_producto);
                    }
                });
            }
            var select = $('#filtroTipoProductoCotizacion');
            select.find('option:not(:first)').remove();
            tipos.forEach(function (tipo) {
                select.append('<option value="' + tipo.id + '">' + tipo.nombre + '</option>');
            });
        }

        function renderizarProductosModalCotizacion(filtro, tipoId) {
            filtro = filtro || '';
            tipoId = tipoId || '';
            var tbody = $('#productosModalCotizacionBody');
            tbody.empty();

            if (typeof products === 'undefined' || !products) {
                tbody.append('<tr><td colspan="6" class="text-center text-danger py-4"><i class="ri-error-warning-line fs-2 d-block mb-2"></i>Error: No se pudieron cargar los productos.</td></tr>');
                return;
            }

            if (products.length === 0) {
                tbody.append('<tr><td colspan="6" class="text-center text-muted py-4"><i class="ri-inbox-line fs-2 d-block mb-2"></i>No hay productos registrados en el sistema.</td></tr>');
                return;
            }

            var productosFiltrados = products.filter(function (p) {
                var matchFiltro = true;
                var matchTipo = true;
                if (filtro) {
                    var busqueda = filtro.toLowerCase();
                    var codigo = (p.codigo || '').toLowerCase();
                    var modelo = (p.modelo || '').toLowerCase();
                    var tipo = p.tipo_producto ? p.tipo_producto.nombre.toLowerCase() : '';
                    matchFiltro = codigo.includes(busqueda) || modelo.includes(busqueda) || tipo.includes(busqueda);
                }
                if (tipoId) {
                    matchTipo = p.tipo_producto && p.tipo_producto.id == tipoId;
                }
                return matchFiltro && matchTipo;
            });

            if (productosFiltrados.length === 0) {
                tbody.append('<tr><td colspan="6" class="text-center text-muted py-4"><i class="ri-search-2-line fs-2 d-block mb-2"></i>No se encontraron coincidencias.</td></tr>');
                return;
            }

            productosFiltrados.forEach(function (p) {
                var tipoNombre = p.tipo_producto ? p.tipo_producto.nombre : 'Sin tipo';
                var imgHtml = p.imagen ? '<img src="' + p.imagen + '" class="producto-img-thumb" alt="">' : '<span class="text-muted text-center d-block" style="width:40px; font-size:10px;">Sin IMG</span>';
                var row = '<tr data-producto-id="' + p.id + '" data-precio="' + p.precio_base + '">' +
                    '<td>' + imgHtml + '</td>' +
                    '<td><span class="badge bg-dark">' + (p.codigo || '-') + '</span></td>' +
                    '<td><span class="badge bg-primary">' + tipoNombre + '</span></td>' +
                    '<td>' + p.modelo + '</td>' +
                    '<td class="text-success fw-bold text-end">$ ' + parseFloat(p.precio_base).toFixed(2) + '</td>' +
                    '<td class="text-center"><button type="button" class="btn btn-sm btn-success select-producto-btn-cotizacion" data-id="' + p.id + '"><i class="ri-check-line"></i></button></td>' +
                    '</tr>';
                tbody.append(row);
            });
        }

        // Asegurar renderizado al abrir el modal por cualquier vía
        document.getElementById('productosModalCotizacion').addEventListener('shown.bs.modal', function () {
            renderizarProductosModalCotizacion($('#buscarProductoModalCotizacion').val(), $('#filtroTipoProductoCotizacion').val());
        });

        // Ajuste inteligente de backdrop: Solo limpiar si no queda otro modal abierto
        document.getElementById('productosModalCotizacion').addEventListener('hidden.bs.modal', function () {
            // Si el modal principal de cotización está abierto, NO quitar la clase modal-open del body
            if ($('#showModal').hasClass('show')) {
                $('body').addClass('modal-open');
                // Bootstrap debería encargarse de quitar el backdrop del modal hijo automáticamente.
                // Si quedaran backdrops residuales, eliminamos solo los extra (manteniendo 1)
                var backdrops = $('.modal-backdrop');
                if (backdrops.length > 1) {
                    // Dejar solo uno
                    backdrops.not(backdrops.first()).remove();
                }
            } else {
                // Si no hay modal padre, limpiar todo
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                $('body').css('overflow', 'auto');
                $('body').css('padding-right', '');
            }
        });

        // Abrir modal desde un item existente (EDITAR/CAMBIAR PRODUCTO)
        $('#productos-container').on('click', '.producto-selector-trigger', function () {
            currentProductIndex = $(this).closest('.product-item').data('product-index');
            $('#buscarProductoModalCotizacion').val('');
            $('#filtroTipoProductoCotizacion').val('');
            renderizarProductosModalCotizacion('', '');
            productosModalCotizacion.show();
        });

        // Seleccionar producto desde la tabla (click botón o doble click fila)
        $(document).on('click', '.select-producto-btn-cotizacion', function () {
            // Prevenir comportamiento de submit si lo hubiera
            seleccionarProductoCotizacion($(this).data('id'));
        });

        $(document).on('dblclick', '#productosModalCotizacionTable tbody tr', function (e) {
            e.preventDefault(); // Prevenir cualquier acción por defecto
            var productoId = $(this).data('producto-id');
            if (productoId) seleccionarProductoCotizacion(productoId);
        });

        function seleccionarProductoCotizacion(productoId) {
            var producto = products.find(function (p) { return p.id == productoId; });
            if (!producto) return;

            // Función helper para cerrar modal de forma segura respetando modal padre
            var cerrarModalSeguro = function () {
                if (productosModalCotizacion) productosModalCotizacion.hide();

                // Si el modal padre está abierto, mantener su estado
                if ($('#showModal').hasClass('show')) {
                    $('body').addClass('modal-open');
                    var backdrops = $('.modal-backdrop');
                    if (backdrops.length > 1) {
                        backdrops.not(backdrops.first()).remove();
                    }
                } else {
                    // Limpieza total si no hay padre
                    $('body').removeClass('modal-open');
                    $('body').css('overflow', '');
                    $('body').css('padding-right', '');
                    $('.modal-backdrop').remove();
                }
            };

            if (currentProductIndex !== null) {
                // EDITAR ITEM EXISTENTE
                var card = $(`.product-item[data-product-index="${currentProductIndex}"]`);
                var tipoNombre = producto.tipo_producto ? producto.tipo_producto.nombre : 'Sin tipo';
                var displayName = (producto.codigo || '') + ' - ' + tipoNombre + ' ' + producto.modelo;

                // Actualizar valores visuales y ocultos
                card.find('.producto-text').text(displayName);
                card.find('.producto-text').removeClass('text-muted').addClass('text-dark fw-bold');

                card.find('.producto-id-input').val(producto.id);
                card.find('.precio-unitario-input').val(producto.precio_base);
                card.find('.precio-producto-span').text('$' + parseFloat(producto.precio_base).toFixed(2));

                // Cerrar modal
                cerrarModalSeguro();
                calculateCotizacionTotals();

            } else {
                // NUEVO ITEM
                // Cerrar modal antes de agregar
                cerrarModalSeguro();
                // Agregar item
                addProductItem(producto.id, 1, producto.precio_base, '', false, '', '', '', 1);
                // Recalcular
                calculateCotizacionTotals();
            }
        }

        function addProductItem(productoId = '', cantidad = '', precioUnitario = '', descripcion = '', llevaBordado = false, nombreLogo = '', talla = '', ubicacionLogo = '', cantidadLogo = 1) {
            // Buscar detalles del producto para mostrar nombre bonito
            var productoDisplay = 'Clic para buscar producto...';
            var textClass = 'text-muted';

            if (productoId) {
                var p = products.find(prod => prod.id == productoId);
                if (p) {
                    var tipoNombre = p.tipo_producto ? p.tipo_producto.nombre : 'Sin tipo';
                    productoDisplay = (p.codigo || '') + ' - ' + tipoNombre + ' ' + p.modelo;
                    precioUnitario = p.precio_base; // Asegurar precio base
                    textClass = 'text-dark fw-bold';
                }
            }

            // Layout IDÉNTICO a Pedidos (Screenshot)
            var itemHtml = `
            <div class="card mb-3 shadow-lg border-dark product-item" data-product-index="${productItemIndex}">
                <div class="card-body">
                    <h5 class="card-title">Nuevo Producto</h5>
                    
                    <!-- Fila 1: Buscador y Cantidad -->
                    <div class="row align-items-start mb-2">
                        <div class="col-md-9 col-sm-8">
                            <div class="producto-selector-trigger form-control d-flex align-items-center justify-content-between" style="cursor: pointer; background-color: #fff;">
                                <span class="producto-text ${textClass} text-truncate">${productoDisplay}</span>
                                <i class="ri-search-line text-primary"></i>
                            </div>
                            <input type="hidden" name="productos[${productItemIndex}][producto_id]" class="producto-id-input" value="${productoId}" required />
                            <input type="hidden" name="productos[${productItemIndex}][precio_unitario]" class="precio-unitario-input" value="${precioUnitario}" />
                            <small class="text-success fw-bold precio-producto-span">${precioUnitario ? '$' + parseFloat(precioUnitario).toFixed(2) : ''}</small>
                        </div>
                        <div class="col-md-3 col-sm-4">
                            <input type="number" name="productos[${productItemIndex}][cantidad]" class="form-control text-center cantidad-input" placeholder="Cant." min="1" value="${cantidad}" required />
                        </div>
                    </div>

                    <!-- Fila 2: Talla (Ocupando todo el ancho al quitar color) -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <select name="productos[${productItemIndex}][talla]" class="form-select" required>
                                <option value="">Seleccione una talla</option>
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
                    </div>

                    <!-- Fila 3: Descripción -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <textarea name="productos[${productItemIndex}][descripcion]" class="form-control" placeholder="Descripción del producto (opcional)" rows="2">${descripcion}</textarea>
                        </div>
                    </div>

                    <!-- Fila 4: Switch Bordado -->
                    <div class="row">
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="productos[${productItemIndex}][lleva_bordado]" value="0">
                                <input class="form-check-input lleva-bordado-checkbox" type="checkbox" id="lleva-bordado-${productItemIndex}" name="productos[${productItemIndex}][lleva_bordado]" value="1" ${llevaBordado ? 'checked' : ''}>
                                <label class="form-check-label" for="lleva-bordado-${productItemIndex}">Lleva Bordado/Logo</label>
                            </div>
                        </div>
                    </div>

                    <!-- Contenedor Logo -->
                     <div class="row mt-2 nombre-logo-container" style="display: ${llevaBordado ? 'flex' : 'none'}">
                        <div class="col-6 mb-2">
                            <input type="text" name="productos[${productItemIndex}][nombre_logo]" class="form-control nombre-logo-input" placeholder="Nombre del logo" value="${nombreLogo}" />
                        </div>
                        <div class="col-6 mb-2">
                            <input type="number" name="productos[${productItemIndex}][cantidad_logo]" class="form-control cantidad-logo-input" placeholder="Cant. Logos" min="1" value="${cantidadLogo || 1}" />
                        </div>
                        <div class="col-12">
                            <input type="text" name="productos[${productItemIndex}][ubicacion_logo]" class="form-control ubicacion-logo-input" placeholder="Ubicación (ej: Pecho)" value="${ubicacionLogo || ''}" />
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-danger btn-sm remove-producto-item">Eliminar Producto</button>
                    </div>
                </div>
            </div>
            `;
            $('#productos-container').append(itemHtml);
            productItemIndex++;
        }

        // Evento para remover producto
        $('#productos-container').on('click', '.remove-producto-item', function () {
            $(this).closest('.card').remove();
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
                        showConfirmButton: false,
                        customClass: {
                            confirmButton: 'btn btn-primary w-xs me-2',
                            cancelButton: 'btn btn-danger w-xs'
                        },
                        buttonsStyling: false,
                        showCloseButton: true,
                        timer: 1500
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
                        customClass: {
                            confirmButton: 'btn btn-primary w-xs me-2',
                            cancelButton: 'btn btn-danger w-xs'
                        },
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
                    // Formatear fechas para input date (YYYY-MM-DD)
                    var fechaCotizacion = data.fecha_cotizacion ? data.fecha_cotizacion.split('T')[0] : '';
                    var fechaValidez = data.fecha_validez ? data.fecha_validez.split('T')[0] : '';

                    $('#fecha-cotizacion-field').val(fechaCotizacion);
                    $('#fecha-validez-field').val(fechaValidez);
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

                    // Esperar un momento para que todos los elementos se rendericen
                    setTimeout(function () {
                        calculateCotizacionTotals();

                        // Intentar inicializar Select2, pero no detener la ejecución si falla
                        try {
                            // Destruir y reinicializar Select2 en todos los selectores de productos
                            $('.product-select').each(function () {
                                if ($(this).hasClass("select2-hidden-accessible")) {
                                    $(this).select2('destroy');
                                }
                                $(this).select2({
                                    theme: 'bootstrap-5',
                                    placeholder: '🔍 Buscar producto...',
                                    allowClear: true,
                                    width: '100%',
                                    dropdownParent: $('#showModal'),
                                    language: {
                                        noResults: function () {
                                            return 'No se encontraron productos';
                                        },
                                        searching: function () {
                                            return 'Buscando...';
                                        }
                                    }
                                });
                            });
                        } catch (e) {
                            console.error('Error inicializando Select2:', e);
                        }

                        // Mostrar el modal siempre, independientemente de errores en Select2
                        $('#showModal').modal('show');
                    }, 200);
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo cargar la cotización para editar',
                        customClass: {
                            confirmButton: 'btn btn-primary w-xs me-2',
                            cancelButton: 'btn btn-danger w-xs'
                        },
                        buttonsStyling: false,
                        showCloseButton: true
                    });
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
                    // Función para formatear fechas YYYY-MM-DD o ISO a dd/mm/yyyy
                    function formatDate(dateStr) {
                        if (!dateStr) return 'N/A';
                        var date = new Date(dateStr);
                        if (isNaN(date.getTime())) return dateStr;
                        var day = String(date.getDate()).padStart(2, '0');
                        var month = String(date.getMonth() + 1).padStart(2, '0');
                        var year = date.getFullYear();
                        return day + '/' + month + '/' + year;
                    }
                    $('#view-fecha-cotizacion').text(formatDate(data.fecha_cotizacion));
                    $('#view-fecha-validez').text(formatDate(data.fecha_validez));
                    // Mostrar estado con diseño unificado (Igual que en la tabla)
                    var estadoStyles = {
                        'Pendiente': 'background-color: rgba(30, 60, 114, 0.15); border: 1px solid #1e3c72; color: #1e3c72;',
                        'Aprobada': 'background-color: rgba(46, 204, 113, 0.15); border: 1px solid #2ecc71; color: #1e8449;',
                        'Convertida': 'background-color: rgba(0, 217, 165, 0.15); border: 1px solid #00d9a5; color: #006b52;',
                        'Cancelado': 'background-color: rgba(139, 58, 58, 0.15); border: 1px solid #8b3a3a; color: #8b3a3a;',
                        'Vencida': 'background-color: rgba(139, 58, 58, 0.15); border: 1px solid #8b3a3a; color: #8b3a3a;'
                    };
                    var estadoIcons = {
                        'Pendiente': 'ri-time-line',
                        'Aprobada': 'ri-check-double-line',
                        'Convertida': 'ri-exchange-line',
                        'Cancelado': 'ri-close-circle-line',
                        'Vencida': 'ri-alarm-warning-line'
                    };
                    var style = estadoStyles[data.estado] || 'background-color: #f8f9fa; color: #495057;';
                    var icon = estadoIcons[data.estado] || 'ri-question-line';
                    $('#view-estado').html('<span class="badge" style="' + style + ' padding: 5px 10px; border-radius: 4px; font-weight: 500;"><i class="' + icon + ' me-1"></i>' + data.estado + '</span>');
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
                customClass: {
                    confirmButton: 'btn btn-primary w-xs me-2',
                    cancelButton: 'btn btn-danger w-xs'
                },
                buttonsStyling: false,
                showCloseButton: true
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
                                showConfirmButton: false,
                                customClass: {
                                    confirmButton: 'btn btn-primary w-xs me-2',
                                    cancelButton: 'btn btn-danger w-xs'
                                },
                                buttonsStyling: false,
                                showCloseButton: true,
                                timer: 1500
                            })
                            table.ajax.reload();
                        },
                        error: function (xhr) {
                            Swal.fire({
                                title: 'Error!',
                                text: 'No se pudo eliminar la cotización.',
                                icon: 'error',
                                customClass: {
                                    confirmButton: 'btn btn-primary w-xs me-2',
                                    cancelButton: 'btn btn-danger w-xs'
                                },
                                buttonsStyling: false,
                                showCloseButton: true
                            })
                        }
                    });
                }
            });
        });

        // === CAMBIO DE ESTADO VIA DROPDOWN ===
        $('#cotizaciones-table').on('click', '.change-status-btn', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            var status = $(this).data('status');

            Swal.fire({
                title: '¿Cambiar estado?',
                text: "El estado cambiará a: " + status,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary w-xs me-2',
                    cancelButton: 'btn btn-danger w-xs'
                },
                buttonsStyling: false,
                showCloseButton: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/cotizaciones/' + id + '/estado',
                        method: 'PUT',
                        data: {
                            estado: status,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            Swal.fire({
                                title: '¡Actualizado!',
                                text: response.success,
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            table.ajax.reload();
                        },
                        error: function (xhr) {
                            var msg = 'No se pudo actualizar el estado.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                msg = xhr.responseJSON.error;
                            }
                            Swal.fire({
                                title: 'Error',
                                text: msg,
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });

        // === CONVERTIR COTIZACIÓN A PEDIDO ===
        $('#cotizaciones-table').on('click', '.convert-to-pedido-btn', function () {
            var cotizacionId = $(this).data('id');

            Swal.fire({
                title: '¿Convertir a Pedido?',
                html: '<p>Se creará un nuevo pedido con los datos de esta cotización:</p>' +
                    '<ul class="text-start"><li>Cliente</li><li>Productos</li><li>Precios</li></ul>' +
                    '<small class="text-muted">Después podrá editar el pedido para agregar fecha de entrega, abono y método de pago.</small>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="ri-exchange-line me-1"></i> Sí, convertir',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-success w-xs me-2',
                    cancelButton: 'btn btn-light w-xs'
                },
                buttonsStyling: false,
                showCloseButton: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    Swal.fire({
                        title: 'Convirtiendo...',
                        text: 'Creando pedido desde la cotización',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Llamar al endpoint para convertir
                    $.ajax({
                        url: '/cotizaciones/' + cotizacionId + '/convertir-a-pedido',
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Pedido Creado!',
                                html: '<p>' + response.message + '</p>' +
                                    '<p class="mt-2">¿Desea ir al pedido creado para completar los datos?</p>',
                                showCancelButton: true,
                                confirmButtonText: '<i class="ri-edit-line me-1"></i> Editar Pedido',
                                cancelButtonText: 'Quedarme aquí',
                                customClass: {
                                    confirmButton: 'btn btn-primary me-2',
                                    cancelButton: 'btn btn-light'
                                },
                                buttonsStyling: false
                            }).then((result) => {
                                // Recargar la tabla para mostrar el estado actualizado
                                table.ajax.reload();

                                if (result.isConfirmed) {
                                    // Redirigir al módulo de pedidos
                                    window.location.href = '/pedidos?editar=' + response.pedido_id;
                                }
                            });
                        },
                        error: function (xhr) {
                            var errorMsg = 'No se pudo convertir la cotización.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMsg = xhr.responseJSON.error;
                            }
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
            });
        });

        // === AUTOCOMPLETADO DE CLIENTE ===
        let clienteSeleccionado = null;
        let autocompleteTimeout = null;

        $('#ci-rif-number-field').on('input', function () {
            const query = $(this).val();
            clearTimeout(autocompleteTimeout);
            // Cambiar a 6 caracteres mínimos para búsqueda de documento
            if (query.length < 6) {
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
                                html += `<button type="button" class="list-group-item list-group-item-action cliente-autocomplete-item" data-id="${cliente.id}" data-nombre="${cliente.nombre}" data-apellido="${cliente.apellido || ''}" data-email="${cliente.email || ''}" data-telefono="${cliente.telefono || ''}" data-documento="${cliente.documento || ''}">${cliente.documento || 'Sin documento'} - ${nombreCompleto} <small class='text-muted'>${cliente.email || 'Sin email'}</small></button>`;
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
            if (!$(e.target).closest('#ci-rif-number-field, #cliente-autocomplete-list').length) {
                $('#cliente-autocomplete-list').empty().hide();
            }
        });

        // --- MODAL AGREGAR CLIENTE DESDE COTIZACIÓN ---

        // Validación en tiempo real para nombre (solo letras y espacios)
        $(document).on('input', '#nombre-field-cliente', function () {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        });

        // Validación en tiempo real para apellido (solo letras y espacios)
        $(document).on('input', '#apellido-field-cliente', function () {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        });

        // Validación en tiempo real para documento (solo números)
        $(document).on('input', '#documento-number-field-cliente', function () {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        });

        // Validación en tiempo real para teléfono (solo números)
        $(document).on('input', '#telefono-number-field-cliente', function () {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 7);
        });

        // Validación onblur para nombre
        $(document).on('blur', '#nombre-field-cliente', function () {
            let value = $(this).val().trim();
            if (value.length < 2) {
                $(this).addClass('is-invalid');
                $('#nombre-error-cliente').text('El nombre debe tener al menos 2 caracteres.').show();
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $('#nombre-error-cliente').hide();
            }
        });

        // Validación onblur para apellido
        $(document).on('blur', '#apellido-field-cliente', function () {
            let value = $(this).val().trim();
            if (value.length < 2) {
                $(this).addClass('is-invalid').removeClass('is-valid');
                $('#apellido-error-cliente').text('El apellido debe tener al menos 2 caracteres.').show();
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $('#apellido-error-cliente').hide();
            }
        });

        // Validación onblur para documento
        $(document).on('blur', '#documento-number-field-cliente', function () {
            let value = $(this).val().trim();
            if (value.length < 6) {
                $(this).addClass('is-invalid');
                $('#documento-error-cliente').text('El documento debe tener al menos 6 dígitos.').show();
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $('#documento-error-cliente').hide();
            }
        });

        // Validación onblur para teléfono
        $(document).on('blur', '#telefono-number-field-cliente', function () {
            let value = $(this).val().trim();
            if (value.length < 7) {
                $(this).addClass('is-invalid');
                $('#telefono-error-cliente').text('El teléfono debe tener 7 dígitos.').show();
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $('#telefono-error-cliente').hide();
            }
        });

        // Validación onblur para email
        $(document).on('blur', '#email-field-cliente', function () {
            let value = $(this).val().trim();
            let regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (value.length > 0 && !regex.test(value)) {
                $(this).addClass('is-invalid');
                $('#email-error-cliente').text('Ingrese un email válido.').show();
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $('#email-error-cliente').hide();
            }
        });

        // Validación onblur para dirección (capitalizar primera letra + validar obligatorio)
        $(document).on('blur', '#direccion-field-cliente', function () {
            let value = $(this).val().trim();
            // Capitalizar primera letra
            if (value.length > 0) {
                $(this).val(value.charAt(0).toUpperCase() + value.slice(1));
            }
            // Validar mínimo 5 caracteres
            if (value.length < 5) {
                $(this).addClass('is-invalid').removeClass('is-valid');
                $('#direccion-error-cliente').text('La dirección debe tener al menos 5 caracteres.').show();
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $('#direccion-error-cliente').hide();
            }
        });

        // Listener para actualizar label del checkbox de estatus
        $(document).on('change', '#estatus-field-cliente', function () {
            if ($(this).is(':checked')) {
                $('#estatus-label-cliente').text('Activo');
            } else {
                $('#estatus-label-cliente').text('Inactivo');
            }
        });

        // Dropdown dependiente: Poblar municipios cuando cambia el estado
        $(document).on('change', '#estado_territorial-field-cliente', function () {
            const estado = $(this).val();
            const municipios = getMunicipios(estado);
            const ciudadSelect = $('#ciudad-field-cliente');

            // Limpiar opciones anteriores
            ciudadSelect.empty();

            if (estado === '') {
                ciudadSelect.append('<option value="">Primero seleccione un estado</option>');
            } else {
                ciudadSelect.append('<option value="">Seleccione municipio</option>');
                municipios.forEach(function (municipio) {
                    ciudadSelect.append('<option value="' + municipio + '">' + municipio + '</option>');
                });
            }
        });

        // Limpiar validaciones al abrir modal
        $('#modalAddCliente').on('show.bs.modal', function () {
            $('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            $('.invalid-feedback').hide();
        });

        // Abrir modal de agregar cliente
        $('#open-add-cliente-modal').on('click', function () {
            $('#clienteFormCotizacion')[0].reset();
            $('#id-field-cliente').val('');
            $('#modalClienteTitle').text('Agregar Cliente');
            $('#add-btn-cliente').show();
            $('#edit-btn-cliente').hide();
            // Reset valores por defecto
            $('#documento-prefix-field-cliente').val('V-');
            $('#telefono-prefix-field-cliente').val('0424');
            $('#estatus-field-cliente').prop('checked', true);
            $('#estatus-label-cliente').text('Activo');
            $('#ciudad-field-cliente').html('<option value="">Primero seleccione un estado</option>');
            $('#modalAddCliente').modal('show');
        });

        // Submit del formulario de cliente
        $('#add-btn-cliente').off('click').on('click', function (e) {
            e.preventDefault();

            // Concatenar documento completo
            var documentoCompleto = $('#documento-prefix-field-cliente').val() + $('#documento-number-field-cliente').val();
            $('#documento-field-cliente').val(documentoCompleto);

            // Concatenar teléfono completo
            var telefonoCompleto = $('#telefono-prefix-field-cliente').val() + '-' + $('#telefono-number-field-cliente').val();
            $('#telefono-field-cliente').val(telefonoCompleto);

            // Validar campo apellido explícitamente
            var apellido = $('#apellido-field-cliente').val().trim();
            if (apellido.length < 2) {
                $('#apellido-field-cliente').addClass('is-invalid');
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: 'El campo Apellido es obligatorio (mínimo 2 caracteres)'
                });
                return;
            }
            $('#apellido-field-cliente').removeClass('is-invalid');

            // Validar campo dirección explícitamente
            var direccion = $('#direccion-field-cliente').val().trim();
            if (!direccion || direccion.length < 5) {
                $('#direccion-field-cliente').addClass('is-invalid');
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: 'El campo Dirección es obligatorio (mínimo 5 caracteres)'
                });
                return;
            }
            $('#direccion-field-cliente').removeClass('is-invalid');

            // Validar formulario antes de enviar
            var form = document.getElementById('clienteFormCotizacion');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Deshabilitar botón para evitar múltiples envíos
            $(this).prop('disabled', true);

            // Enviar por AJAX
            var formData = $('#clienteFormCotizacion').serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: '/clientes',
                type: 'POST',
                data: formData,
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message || 'Cliente creado exitosamente',
                        showConfirmButton: false,
                        timer: 2000
                    });

                    $('#modalAddCliente').modal('hide');

                    // Obtener el cliente_id del response
                    const clienteId = response.cliente_id;

                    // Rellenar los campos de la cotización con el nuevo cliente
                    const nombre = $('#nombre-field-cliente').val();
                    const apellido = $('#apellido-field-cliente').val();
                    const email = $('#email-field-cliente').val();
                    const telefono = telefonoCompleto;
                    const documento = documentoCompleto;

                    // Actualizar campo cliente_id en formulario de cotización
                    $('#cliente-id-field').val(clienteId);

                    // Rellenar campos visibles
                    $('#cliente-nombre-field').val(nombre);
                    $('#cliente-apellido-field').val(apellido || '');
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

                    // Re-habilitar botón
                    $('#add-btn-cliente').prop('disabled', false);
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
                        errorMessage = 'Ocurrió un error al crear el cliente.';
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });

                    // Re-habilitar botón
                    $('#add-btn-cliente').prop('disabled', false);
                }
            });
        });
    });
</script>