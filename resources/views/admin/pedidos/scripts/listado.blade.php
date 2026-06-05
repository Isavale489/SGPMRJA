<script>
    // Catálogo de productos expuesto globalmente — el wizard (scripts/main.blade.php)
    // lo lee en `var pedProdCatalogo = window.products || []`. Definir fuera de ready
    // para que esté disponible antes de cualquier callback de DOM ready.
    window.products = @json($productos);

    $(document).ready(function () {

        // ╔══════════════════════════════════════════════════════════════════
        // ║ LISTADO DE PEDIDOS — DataTable, filtros y acciones (ver/editar/eliminar)
        // ║ El wizard de creación/edición vive en scripts/main.blade.php
        // ╚══════════════════════════════════════════════════════════════════

        function debounce(func, wait) {
            let timeout;
            return function () {
                const context = this;
                const args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(function () {
                    func.apply(context, args);
                }, wait);
            };
        }

        function updateFilterBadge() {
            let count = 0;
            const ordenValue = $('#filter-orden').val();
            if ($('#filter-estado').val()) {
                count++;
            }
            if ($('#filter-fecha-entrega').val()) {
                count++;
            }
            if (ordenValue && ordenValue !== 'recientes') {
                count++;
            }
            $('#active-filter-count').text(count).toggleClass('d-none', count === 0);
        }

        var table = $('#pedidos-table').DataTable({
            autoWidth: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('pedidos.data') }}",
                data: function (d) {
                    d.filter_estado = $('#filter-estado').val();
                    d.filter_fecha_entrega = $('#filter-fecha-entrega').val();
                    d.filter_orden = $('#filter-orden').val();
                }
            },
            columns: [
                { data: 'id', name: 'id', title: 'Pedido', className: 'text-center', width: '8%' },
                { data: 'cliente_nombre_display', name: 'cliente_nombre_display', defaultContent: 'N/A', width: '30%' },
                { data: 'fecha_entrega_estimada', name: 'fecha_entrega_estimada', width: '14%' },
                {
                    data: 'total',
                    name: 'total',
                    width: '12%',
                    render: $.fn.dataTable.render.number(',', '.', 2, '$')
                },
                {
                    data: 'estado',
                    name: 'estado',
                    className: 'text-center',
                    width: '14%',
                    render: function (data, type, row) {
                        var estadoClasses = {
                            'Pendiente': 'status-pendiente',
                            'Procesando': 'status-procesando',
                            'Completado': 'status-completado',
                            'Cancelado': 'status-cancelado'
                        };
                        var estadoIcons = {
                            'Pendiente': 'ri-time-line',
                            'Procesando': 'ri-loader-4-line',
                            'Completado': 'ri-check-double-line',
                            'Cancelado': 'ri-close-circle-line'
                        };
                        var badgeClass = estadoClasses[data] || '';
                        var icon = estadoIcons[data] || 'ri-question-line';
                        return '<span class="badge badge-status ' + badgeClass + ' rounded-pill"><i class="' + icon + ' me-1"></i>' + data + '</span>';
                    }
                },
                {
                    data: 'id',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    width: '22%',
                    render: function (data, type, row) {
                        var isAdmin = {{ Auth::user()->isAdmin() ? 'true' : 'false' }};
                        // Ver inline (acción rápida, en todas las filas) + menú "⋮ Más" con
                        // el resto de acciones, que son contextuales. Así cada fila es
                        // idéntica ([Ver][⋮]): columna alineada y sin huecos en blanco.
                        var sVer = `<button class="btn btn-sm btn-soft-info view-btn" data-id="${data}" title="Ver"><i class="ri-eye-fill"></i></button>`;

                        var items = '';
                        // Editar / Eliminar (bloqueado si hay producción activa o el pedido
                        // ya está completado/cancelado).
                        var puedeEditar = isAdmin && row.estado !== 'Completado' && row.estado !== 'Cancelado' && !row.tiene_produccion;
                        if (puedeEditar) {
                            items += `<li><button type="button" class="dropdown-item act-item act-edit edit-btn" data-id="${data}"><span class="act-ic"><i class="ri-pencil-fill"></i></span>Editar</button></li>`;
                            items += `<li><button type="button" class="dropdown-item act-item act-del remove-btn" data-id="${data}"><span class="act-ic"><i class="ri-delete-bin-fill"></i></span>Eliminar</button></li>`;
                        }
                        // Cancelar (Pendiente/Procesando) o Reactivar (Cancelado).
                        if (isAdmin && (row.estado === 'Pendiente' || row.estado === 'Procesando')) {
                            items += `<li><button type="button" class="dropdown-item act-item act-warn cancelar-btn" data-id="${data}"><span class="act-ic"><i class="ri-close-circle-line"></i></span>Cancelar pedido</button></li>`;
                        } else if (isAdmin && row.estado === 'Cancelado') {
                            items += `<li><button type="button" class="dropdown-item act-item act-restore reactivar-btn" data-id="${data}"><span class="act-ic"><i class="ri-refresh-line"></i></span>Reactivar pedido</button></li>`;
                        }
                        // Separador antes del PDF si hubo acciones previas.
                        if (items) items += `<li><hr class="dropdown-divider"></li>`;
                        items += `<li><a class="dropdown-item act-item act-pdf" href="/pedidos/${data}/pdf" target="_blank"><span class="act-ic"><i class="ri-file-pdf-fill"></i></span>Ver / Descargar PDF</a></li>`;

                        var menu = `
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-sm btn-soft-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Más acciones"><i class="ri-more-2-fill"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end actions-menu">${items}</ul>
                            </div>`;

                        return `<div class="d-flex gap-1 justify-content-center align-items-center">${sVer}${menu}</div>`;
                    }
                }
            ],
            order: [],
            ordering: false,
            dom: 'rtip',
            buttons: [
                {
                    extend: 'copy',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4]
                    }
                },
                {
                    extend: 'excel',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    }
                }
            ],
            language: lenguajeData
        });

        $('#filters-collapse-body')
            .on('show.bs.collapse', function () {
                $('.navy-filter-header').removeClass('is-collapsed');
            })
            .on('hidden.bs.collapse', function () {
                $('.navy-filter-header').addClass('is-collapsed');
            });

        $('#custom-search-input').on('input', debounce(function () {
            table.search(this.value).draw();
        }, 300));

        $('.navy-filter-select').on('change', function () {
            table.ajax.reload();
            updateFilterBadge();
        });

        $('#btn-clear-filters').on('click', function () {
            $('#filter-estado').val('');
            $('#filter-fecha-entrega').val('');
            $('#filter-orden').val('recientes');
            $('.navy-filter-select').trigger('change');
            $('#custom-search-input').val('');
            table.search('').draw();
            updateFilterBadge();
        });

        updateFilterBadge();

        // === Catálogos para la vista de detalle (viewModal) =================
        var tallasCatalogo = @json($tallas->mapWithKeys(function ($talla) {
            return [$talla->id => ($talla->etiqueta ?: $talla->nombre)];
        }));
        var coloresCatalogo = @json($colores->mapWithKeys(function ($color) {
            return [$color->id => ['nombre' => $color->nombre, 'hex' => $color->hex_referencial]];
        }));
        var logosCatalogo = @json($logos->pluck('name', 'id'));

        function getTallaLabel(tallaId) {
            if (!tallaId) return 'N/A';
            return tallasCatalogo[tallaId] || 'N/A';
        }

        function getColorNombre(colorId) {
            return (colorId && coloresCatalogo[colorId]) ? coloresCatalogo[colorId].nombre : '';
        }

        function getLogoNombre(logoId) {
            return (logoId && logosCatalogo[logoId]) ? logosCatalogo[logoId] : '';
        }

        // === Editar → abre el wizard en modo edición (scripts/main.blade.php) =
        $('#pedidos-table').on('click', '.edit-btn', function () {
            var id = $(this).data('id');
            if (typeof window.pedAbrirEnEdit === 'function') {
                window.pedAbrirEnEdit(id);
            } else {
                console.error('window.pedAbrirEnEdit no está disponible');
            }
        });

        // === Cancelar / Reactivar pedido (acción manual de estado) ==========
        function pedCambiarEstado(id, accion, titulo, texto, confirmText) {
            Swal.fire({
                title: titulo, text: texto, icon: 'warning',
                showCancelButton: true, confirmButtonText: confirmText, cancelButtonText: 'No',
                customClass: { confirmButton: 'btn btn-primary w-xs me-2', cancelButton: 'btn btn-light w-xs' },
                buttonsStyling: false
            }).then(function (r) {
                if (!r.isConfirmed) return;
                $.ajax({
                    url: '/pedidos/' + id + '/' + accion,
                    method: 'POST',
                    data: { _method: 'PATCH', _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function (resp) {
                        Swal.fire({ icon: 'success', title: resp.success || 'Listo', showConfirmButton: false, timer: 1600 });
                        if ($.fn.DataTable.isDataTable('#pedidos-table')) {
                            $('#pedidos-table').DataTable().ajax.reload(null, false);
                        }
                    },
                    error: function (xhr) {
                        var msg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) || 'No se pudo completar la acción.';
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    }
                });
            });
        }

        $('#pedidos-table').on('click', '.cancelar-btn', function () {
            pedCambiarEstado($(this).data('id'), 'cancelar', '¿Cancelar pedido?',
                'El pedido quedará cancelado. Podrás reactivarlo más adelante.', 'Sí, cancelar');
        });
        $('#pedidos-table').on('click', '.reactivar-btn', function () {
            pedCambiarEstado($(this).data('id'), 'reactivar', '¿Reactivar pedido?',
                'El estado volverá a calcularse según el avance de producción.', 'Sí, reactivar');
        });

        // === Ver → viewModal (read-only) ====================================
        $('#pedidos-table').on('click', '.view-btn', function () {
            var id = $(this).data('id');
            $.ajax({
                url: '/pedidos/' + id,
                method: 'GET',
                success: function (data) {
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
                    $('#view-cliente-nombre').text(data.cliente_nombre_completo);
                    $('#view-cliente-email').text(data.cliente_email_normalizado || 'N/A');
                    $('#view-cliente-telefono').text(data.cliente_telefono_normalizado || 'N/A');
                    $('#view-ci-rif').text(data.cliente_documento || 'N/A');
                    $('#view-fecha-pedido').text(formatDate(data.fecha_pedido));
                    $('#view-fecha-entrega-estimada').text(formatDate(data.fecha_entrega_estimada));

                    // Mostrar estado con badge
                    let estadoBadgeClass = '';
                    switch (data.estado) {
                        case 'Pendiente':
                            estadoBadgeClass = 'bg-info';
                            break;
                        case 'Procesando':
                            estadoBadgeClass = 'bg-warning';
                            break;
                        case 'Completado':
                            estadoBadgeClass = 'bg-success';
                            break;
                        case 'Cancelado':
                            estadoBadgeClass = 'bg-danger';
                            break;
                        default:
                            estadoBadgeClass = 'bg-secondary';
                    }
                    $('#view-estado').html(`<span class="badge ${estadoBadgeClass}">${data.estado}</span>`);

                    var totalUsd = parseFloat(data.total);
                    $('#view-total-resumen').text('$' + totalUsd.toFixed(2));
                    var bsLbl = (typeof window.bsEquivalente === 'function') ? window.bsEquivalente(totalUsd) : null;
                    $('#view-total-resumen-bs').text(bsLbl || 'Sin tasa BCV');
                    $('#view-usuario-creador').text(data.user ? data.user.name : 'N/A');

                    // Cargar y mostrar nuevos campos de pago y prioridad
                    $('#view-abono').text('$' + parseFloat(data.abono).toFixed(2));
                    let restante = parseFloat(data.total) - parseFloat(data.abono);
                    $('#view-restante').text('$' + restante.toFixed(2));

                    // Mostrar pagos normalizados desde data.pagos
                    let metodosPago = [];
                    let detallesPagoHtml = '';
                    const metodoLabels = { efectivo: 'Efectivo', transferencia: 'Transferencia', pago_movil: 'Pago Móvil' };
                    const metodoIcons = { efectivo: 'ri-money-dollar-circle-line', transferencia: 'ri-bank-card-line', pago_movil: 'ri-smartphone-line' };
                    const metodoClasses = { efectivo: 'efectivo', transferencia: 'transferencia', pago_movil: 'pago-movil' };

                    if (data.pagos && data.pagos.length > 0) {
                        data.pagos.forEach(function (pago) {
                            var label = metodoLabels[pago.metodo] || pago.metodo;
                            var icon = metodoIcons[pago.metodo] || 'ri-money-dollar-circle-line';
                            var cls = metodoClasses[pago.metodo] || 'none';
                            metodosPago.push('<span class="metodo-pago-pill metodo-pago-pill--' + cls + '"><i class="' + icon + '"></i>' + label + ' $' + parseFloat(pago.monto).toFixed(2) + '</span>');

                            if (pago.metodo !== 'efectivo') {
                                var bancoNombre = pago.banco ? pago.banco.nombre : 'Sin banco';
                                var referencia = pago.referencia || 'Sin referencia';
                                detallesPagoHtml += '<div class="mt-1"><small class="text-muted">' + label + ':</small> Banco: <strong>' + bancoNombre + '</strong> — Ref: <strong>' + referencia + '</strong></div>';
                            }
                        });
                    }
                    $('#view-metodo-pago').html(metodosPago.join('') || '<span class="metodo-pago-pill metodo-pago-pill--none">Sin método</span>');
                    $('#view-bloque-transferencia-container').hide();
                    $('#view-bloque-pago-movil-container').hide();
                    if (detallesPagoHtml) {
                        $('#view-bloque-transferencia-container').html(detallesPagoHtml).show();
                    }

                    // Mostrar prioridad con badge
                    let prioridadBadgeClass = '';
                    switch (data.prioridad) {
                        case 'Normal':
                            prioridadBadgeClass = 'bg-primary';
                            break;
                        case 'Alta':
                            prioridadBadgeClass = 'bg-warning';
                            break;
                        case 'Urgente':
                            prioridadBadgeClass = 'bg-danger';
                            break;
                        default:
                            prioridadBadgeClass = 'bg-secondary';
                    }
                    $('#view-prioridad').html(`<span class="badge ${prioridadBadgeClass}">${data.prioridad || 'N/A'}</span>`);

                    // Llenar productos del pedido en la vista
                    var productosBody = $('#view-productos-container');
                    productosBody.empty();
                    if (data.productos && data.productos.length > 0) {
                        data.productos.forEach(function (item, index) {
                            var subtotal = item.cantidad * item.precio_unitario;
                            var colorDisplay = getColorNombre(item.color_id);
                            var colorHtml = colorDisplay
                                ? `<span class="fw-semibold" style="font-size: 0.85rem;">${colorDisplay}</span>`
                                : `<span class="badge bg-soft-warning text-atlantico-dark" style="font-size:0.68rem;">Sin color</span>`;
                            var tallaLabel = getTallaLabel(item.talla_id);
                            var tallaHtml = (item.talla_id && tallaLabel !== 'N/A')
                                ? `<span class="fw-semibold" style="font-size: 0.85rem;">${tallaLabel}</span>`
                                : `<span class="badge bg-soft-primary text-atlantico-dark" style="font-size:0.68rem;">Sin talla</span>`;
                            productosBody.append(`
                                <div class="card border-0 shadow-sm mb-3" style="border-left: 4px solid #00d9a5 !important;">
                                    <div class="card-body p-3">
                                        <!-- Header del Producto -->
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="rounded-circle me-3 d-flex align-items-center justify-content-center"
                                                style="width: 45px; height: 45px; background: #1e3c72;">
                                                <i class="ri-shopping-bag-line text-white fs-5"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold" style="color: #1e3c72;">${item.producto.codigo || ''} - ${item.producto.tipo_producto ? item.producto.tipo_producto.nombre : 'Sin tipo'}</h6>
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
                                                        style="width: 28px; height: 28px; background: rgba(30, 60, 114, 0.15);">
                                                        <i class="ri-stack-line" style="color: #1e3c72; font-size: 0.85rem;"></i>
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
                                                        <i class="ri-palette-line" style="color: #1e3c72; font-size: 0.85rem;"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size: 0.7rem;">Color</small>
                                                        ${colorHtml}
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
                                                        ${tallaHtml}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                        style="width: 28px; height: 28px; background: rgba(30, 60, 114, 0.15);">
                                                        <i class="ri-money-dollar-circle-line" style="color: #1e3c72; font-size: 0.85rem;"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size: 0.7rem;">P. Unitario</small>
                                                        <span class="fw-semibold" style="font-size: 0.85rem;">$${parseFloat(item.precio_unitario).toFixed(2)}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Bordado/Logo si aplica -->
                                        ${item.lleva_bordado ? (() => {
                                    const bordados = Array.isArray(item.bordados) ? item.bordados : [];
                                    const bordadosHtml = bordados.length
                                        ? bordados.map((bordado) => {
                                            const nombreAplicado = bordado.nombre_aplicado || 'Ubicación';
                                            const logoAplicado = (bordado.logo ? bordado.logo.name : null) || bordado.nombre_logo_aplicado || 'Sin logo';
                                            const cantidadAplicada = Math.max(1, parseInt(bordado.cantidad || 1, 10));
                                            return `
                                                        <div class="pb-1 mb-1" style="border-bottom:1px dashed rgba(30,60,114,0.2);">
                                                        <span class="fw-semibold" style="font-size:0.84rem;color:#1e3c72;">${logoAplicado} → ${nombreAplicado} x${cantidadAplicada}</span>
                                                </div>
                                            `;
                                        }).join('')
                                        : `<div class="pb-1" style="border-bottom:1px dashed rgba(30,60,114,0.2);"><span class="fw-semibold" style="font-size:0.84rem;color:#1e3c72;">Sin logo → ${item.ubicacion_logo || 'Sin ubicación'} x${item.cantidad_logo || 1}</span></div>`;

                                    const recargoBordado = bordados.reduce(function (s, b) {
                                        return s + (parseFloat(b.precio_aplicado) || 0) * Math.max(1, parseInt(b.cantidad || 1, 10));
                                    }, 0);
                                    const tasaPedido = parseFloat(data.tasa_cambio_valor) || 0;
                                    const bsBordadoTxt = (recargoBordado && typeof window.bsEquivalente === 'function') ? window.bsEquivalente(recargoBordado, tasaPedido) : null;
                                    const tasaPedidoTxt = (typeof window.bsTasaFmt === 'function') ? window.bsTasaFmt(tasaPedido) : null;
                                    const bordadoBsHtml = recargoBordado
                                        ? `<div class="d-flex justify-content-between align-items-center mt-2 pt-2" style="border-top:1px dashed rgba(30,60,114,0.25);">
                                                <span class="text-muted" style="font-size:0.72rem;"><i class="ri-exchange-dollar-line me-1"></i>Servicio de bordado (unit.)${tasaPedidoTxt ? ' · Tasa ' + tasaPedidoTxt : ''}</span>
                                                <span class="fw-semibold" style="font-size:0.82rem;color:#1e3c72;">$${recargoBordado.toFixed(2)}${bsBordadoTxt ? ' · ' + bsBordadoTxt : ''}</span>
                                           </div>`
                                        : '';

                                    return `
                                                        <div class="rounded p-2 mb-3" style="background: rgba(30, 60, 114, 0.08);">
                                                            <div class="d-flex align-items-center mb-2">
                                                                <i class="ri-scissors-cut-line me-2" style="color: #1e3c72;"></i>
                                                                <span class="fw-semibold" style="color: #1e3c72; font-size: 0.85rem;">Logos</span>
                                                            </div>
                                                            ${bordadosHtml}
                                                            ${bordadoBsHtml}
                                                        </div>
                                                    `;
                                })() : ''}

                                        <!-- Descripción -->
                                        ${item.descripcion ? `
                                            <div class="rounded p-2 mb-3" style="background: rgba(30, 60, 114, 0.05);">
                                                <div class="d-flex align-items-start">
                                                    <i class="ri-file-text-line me-2 mt-1" style="color: #1e3c72;"></i>
                                                    <div>
                                                        <small class="text-muted d-block">Descripción</small>
                                                        <span style="font-size: 0.85rem;">${item.descripcion}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            ` : ''}

                                        <!-- Insumos -->
                                        ${item.insumos && item.insumos.length > 0 ? `
                                            <div class="rounded p-2" style="background: rgba(46, 204, 113, 0.08);">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="ri-tools-line me-2" style="color: #2ecc71;"></i>
                                                    <span class="fw-semibold" style="color: #2ecc71; font-size: 0.85rem;">Insumos Requeridos</span>
                                                </div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    ${item.insumos.map(insumo => `
                                                        <span class="badge" style="background: rgba(46, 204, 113, 0.2); color: #1e3c72;">
                                                            ${insumo.nombre}
                                                            <span class="badge bg-primary ms-1">${parseFloat(insumo.pivot.cantidad_estimada).toFixed(2)} ${insumo.unidad_medida}</span>
                                                        </span>
                                                    `).join('')}
                                                </div>
                                            </div>
                                            ` : ''}
                                    </div>
                                </div>
                                `);
                        });
                    } else {
                        productosBody.append('<p class="text-muted text-center py-4"><i class="ri-shopping-bag-line fs-1 d-block mb-2"></i>No hay productos en este pedido.</p>');
                    }

                    $('#viewModal').modal('show');
                },
                error: function (xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'No se pudo cargar los detalles del pedido.',
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

        // === Eliminar pedido ================================================
        $('#pedidos-table').on('click', '.remove-btn', function () {
            var id = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminarlo!',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary w-xs me-2',
                    cancelButton: 'btn btn-danger w-xs'
                },
                buttonsStyling: false,
                showCloseButton: true
            }).then(function (result) {
                if (result.value) {
                    $.ajax({
                        url: '/pedidos/' + id,
                        method: 'POST', // Usamos POST y _method para simular DELETE
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
                                text: 'No se pudo eliminar el pedido.',
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

    });
</script>
