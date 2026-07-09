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
                {
                    data: 'id', name: 'id', title: 'Pedido', className: 'align-middle', width: '14%',
                    render: function (data) {
                        return `<div class="ped-cell">
                            <span class="ped-cell-ic"><i class="ri-shopping-bag-3-line"></i></span>
                            <span class="ped-cell-txt"><span class="ped-cell-eyebrow">Pedido</span><span class="ped-cell-num">#${data}</span></span>
                        </div>`;
                    }
                },
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
                        var puedeGestionar = {{ tienePermiso('pedidos.gestionar') ? 'true' : 'false' }};
                        // Ver inline (acción rápida, en todas las filas) + menú "⋮ Más" con
                        // el resto de acciones, que son contextuales. Así cada fila es
                        // idéntica ([Ver][⋮]): columna alineada y sin huecos en blanco.
                        var sVer = `<button class="btn btn-sm btn-soft-info view-btn" data-id="${data}" title="Ver"><i class="ri-eye-fill"></i></button>`;

                        var items = '';
                        // Editar / Eliminar (bloqueado si hay producción activa o el pedido
                        // ya está completado/cancelado).
                        var puedeEditar = puedeGestionar && row.estado !== 'Completado' && row.estado !== 'Cancelado' && !row.tiene_produccion;
                        if (puedeEditar) {
                            items += `<li><button type="button" class="dropdown-item act-item act-edit edit-btn" data-id="${data}"><span class="act-ic"><i class="ri-pencil-fill"></i></span>Editar</button></li>`;
                            items += `<li><button type="button" class="dropdown-item act-item act-del remove-btn" data-id="${data}"><span class="act-ic"><i class="ri-delete-bin-fill"></i></span>Eliminar</button></li>`;
                        }
                        // Cancelar (Pendiente/Procesando) o Reactivar (Cancelado).
                        if (puedeGestionar && (row.estado === 'Pendiente' || row.estado === 'Procesando')) {
                            items += `<li><button type="button" class="dropdown-item act-item act-warn cancelar-btn" data-id="${data}"><span class="act-ic"><i class="ri-close-circle-line"></i></span>Cancelar pedido</button></li>`;
                        } else if (puedeGestionar && row.estado === 'Cancelado') {
                            items += `<li><button type="button" class="dropdown-item act-item act-restore reactivar-btn" data-id="${data}"><span class="act-ic"><i class="ri-refresh-line"></i></span>Reactivar pedido</button></li>`;
                        }
                        // Separador antes del PDF si hubo acciones previas.
                        if (items) items += `<li><hr class="dropdown-divider"></li>`;
                        items += `<li><a class="dropdown-item act-item act-pdf" href="/pedidos/${data}/pdf" target="_blank"><span class="act-ic"><i class="ri-file-pdf-fill"></i></span>Ver PDF</a></li>`;

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

        // ── Navegación wizard viewModal pedidos (solo lectura, 3 pasos) ──────
        (function () {
            var VIEW_PED_STEPS = 3;
            var viewPedStep = 1;

            function viewPedShowStep(n) {
                viewPedStep = n;
                $('#viewModal .wiz-step-content').each(function () {
                    var s = parseInt($(this).data('step'));
                    if (s === n) { $(this).addClass('is-active').removeAttr('hidden'); }
                    else         { $(this).removeClass('is-active').attr('hidden', ''); }
                });
                $('#viewModal .wiz-step-marker').each(function () {
                    var s = parseInt($(this).data('step'));
                    $(this).toggleClass('is-active', s === n).toggleClass('is-done', s < n);
                });
                $('#viewModal .wiz-step-line-fill').each(function () {
                    var l = parseInt($(this).data('line'));
                    $(this).toggleClass('is-filled', l < n);
                });
                $('#btn-view-ped-prev').toggle(n > 1);
                $('#btn-view-ped-next').toggle(n < VIEW_PED_STEPS);
            }

            $('#viewModal').on('show.bs.modal', function () { viewPedShowStep(1); });
            $(document).on('click', '#btn-view-ped-next',  function () { if (viewPedStep < VIEW_PED_STEPS) viewPedShowStep(viewPedStep + 1); });
            $(document).on('click', '#btn-view-ped-prev',  function () { if (viewPedStep > 1)             viewPedShowStep(viewPedStep - 1); });
            $(document).on('click', '#viewModal .wiz-step-marker', function () { viewPedShowStep(parseInt($(this).data('step'))); });
        }());

        // ── Grilla de productos del pedido (solo lectura, cot-grouped-table) ─
        // tasaPedido: tasa heredada de la cotización de origen (para bordado en Bs)
        function viewPedRenderGrilla(productos, tasaPedido) {
            var $cont  = $('#view-productos-container');
            var $empty = $('#view-ped-productos-empty');
            $cont.empty();

            if (!productos || !productos.length) {
                $empty.removeAttr('hidden');
                $('#view-ped-kpi-lineas').text(0);
                $('#view-ped-kpi-unidades').text(0);
                $('#view-ped-kpi-total').text('$0.00');
                return;
            }
            $empty.attr('hidden', '');

            var tasa = parseFloat(tasaPedido) || 0;

            // Agrupar por variante + color_id (las líneas dinámicas no tienen
            // producto materializado: se identifican por tipo + sku/nombre)
            var groups = {}, groupOrder = [];
            productos.forEach(function (item) {
                var variante = item.producto
                    ? 'p' + item.producto.id
                    : 'd' + (item.tipo_producto_id || '') + '|' + (item.sku || item.producto_nombre || '');
                var key = variante + '_' + (item.color_id || 'nc');
                if (!groups[key]) {
                    groups[key] = { ref: item, items: [], precio_unitario: item.precio_unitario };
                    groupOrder.push(key);
                }
                groups[key].items.push(item);
            });

            // KPIs
            var grandTotal   = productos.reduce(function (a, it) { return a + parseFloat(it.precio_unitario) * parseInt(it.cantidad); }, 0);
            var grandUnits   = productos.reduce(function (a, it) { return a + parseInt(it.cantidad); }, 0);
            $('#view-ped-kpi-lineas').text(groupOrder.length);
            $('#view-ped-kpi-unidades').text(grandUnits);
            $('#view-ped-kpi-total').text('$' + grandTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));

            var rows = groupOrder.map(function (key, idx) {
                var g        = groups[key];
                var items    = g.items;
                var prod     = g.ref.producto || {};
                var colorId  = g.ref.color_id;
                var colorObj = (colorId && coloresCatalogo[colorId]) ? coloresCatalogo[colorId] : {};
                var colorNom = colorObj.nombre || '—';
                var colorHex = colorObj.hex || '#94a3b8';
                var precioU  = parseFloat(g.precio_unitario);
                var totalQty = items.reduce(function (a, it) { return a + parseInt(it.cantidad); }, 0);
                var subtotal = items.reduce(function (a, it) { return a + parseFloat(it.precio_unitario) * parseInt(it.cantidad); }, 0);
                var llevaBordado = items.some(function (it) { return it.lleva_bordado; });

                // Chips de talla + género (patrón cotizaciones / wizard de pedido)
                var tallasHtml = items.map(function (it) {
                    var lbl = getTallaLabel(it.talla_id) || 'S/T';
                    var genObj = it.genero || null;
                    var genLbl = genObj ? (genObj.etiqueta || genObj.nombre) : '';
                    var gen = genLbl ? '<span class="cot-chip-gen">' + genLbl + '</span>' : '';
                    return '<span class="cot-chip cot-chip-talla">' + lbl + gen + '<span class="cot-chip-x">×</span>' + it.cantidad + '</span>';
                }).join('');

                // Detalle bordados compacto en la columna producto
                var bordadosDetalle = '';
                if (llevaBordado) {
                    var blines = [];
                    items.forEach(function (it) {
                        if (it.bordados && it.bordados.length) {
                            it.bordados.forEach(function (b) {
                                var bNom = (b.logo ? b.logo.name : null) || b.nombre_logo_aplicado || 'Logo';
                                blines.push(bNom + ' → ' + (b.nombre_aplicado || '—') + ' ×' + (b.cantidad || 1));
                            });
                        }
                    });
                    if (blines.length) {
                        bordadosDetalle = '<div class="cot-prod-variant text-truncate" title="' + blines.join(' | ') + '">' +
                            '<i class="ri-scissors-cut-line me-1"></i>' + blines[0] +
                            (blines.length > 1 ? ' +' + (blines.length - 1) : '') + '</div>';
                    }
                }

                // Estado bordado + precio BCV en línea inferior de tallas
                var bordadoLineClass = llevaBordado ? 'cot-grouped-bordado-line--ok' : 'cot-grouped-bordado-line--none';
                var bordadoText      = llevaBordado ? 'Con bordado' : 'Sin bordado';
                var recargo = items.reduce(function (a, it) {
                    if (!it.bordados || !it.bordados.length) return a;
                    var recU = it.bordados.reduce(function (s, b) {
                        return s + (parseFloat(b.precio_aplicado) || 0) * Math.max(1, parseInt(b.cantidad || 1, 10));
                    }, 0);
                    return a + recU * parseInt(it.cantidad);
                }, 0);
                var bordadoBsExtra = '';
                if (llevaBordado && recargo > 0) {
                    var bsB = (typeof window.bsEquivalente === 'function') ? window.bsEquivalente(recargo, tasa) : null;
                    bordadoBsExtra = ' · $' + recargo.toFixed(2) + (bsB ? ' · ' + bsB : '');
                }

                var prodNombre = g.ref.producto_nombre || prod.nombre_completo || prod.nombre ||
                    (prod.tipo_producto ? prod.tipo_producto.nombre : '') || 'Producto';
                var prodCodigo = prod.codigo || g.ref.sku || '';

                return '<tr class="cot-grouped-row">' +
                    '<td class="cot-col-num">' + (idx + 1) + '</td>' +
                    '<td class="cot-col-prod">' +
                        '<div class="cot-prod-modelo">' + prodNombre + '</div>' +
                        (prodCodigo ? '<div class="cot-prod-codigo"><i class="ri-barcode-line me-1"></i>' + prodCodigo + '</div>' : '') +
                        bordadosDetalle +
                    '</td>' +
                    '<td class="cot-col-color">' +
                        '<span class="cot-color-cell">' +
                            '<span class="cot-color-dot" style="background:' + colorHex + '"></span>' +
                            colorNom +
                        '</span>' +
                    '</td>' +
                    '<td class="cot-col-tallas">' +
                        '<div class="cot-tallas-wrap">' + tallasHtml + '</div>' +
                        '<div class="cot-grouped-bordado-line ' + bordadoLineClass + '">' +
                            '<i class="ri-scissors-cut-line"></i>' + bordadoText + bordadoBsExtra +
                        '</div>' +
                    '</td>' +
                    '<td class="cot-col-num cot-cell-num"><strong>' + totalQty + '</strong></td>' +
                    '<td class="cot-cell-num">$' + precioU.toFixed(2) + '</td>' +
                    '<td class="cot-cell-num cot-cell-subtotal">$' + subtotal.toFixed(2) + '</td>' +
                    '</tr>';
            }).join('');

            $cont.html(
                '<div class="cot-grouped-tablewrap">' +
                '<table class="cot-grouped-table">' +
                '<thead><tr>' +
                    '<th class="cot-col-num">#</th>' +
                    '<th class="cot-col-prod">Producto</th>' +
                    '<th class="cot-col-color">Color</th>' +
                    '<th class="cot-col-tallas">Tallas</th>' +
                    '<th class="cot-col-num cot-cell-num">Cant.</th>' +
                    '<th class="cot-cell-num">P/U</th>' +
                    '<th class="cot-cell-num">Subtotal</th>' +
                '</tr></thead>' +
                '<tbody>' + rows + '</tbody>' +
                '</table></div>'
            );
        }

        // === Ver → viewModal (read-only) ====================================
        $('#pedidos-table').on('click', '.view-btn', function () {
            var id = $(this).data('id');
            $('#view-ped-pdf-btn').attr('href', '/pedidos/' + id + '/pdf');
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

                    // Mostrar estado con badge — estilo + ícono consistentes con el índice
                    var pedEstadoClasses = { 'Pendiente':'status-pendiente','Procesando':'status-procesando','Completado':'status-completado','Cancelado':'status-cancelado' };
                    var pedEstadoIcons   = { 'Pendiente':'ri-time-line','Procesando':'ri-loader-4-line','Completado':'ri-check-double-line','Cancelado':'ri-close-circle-line' };
                    let estadoBadgeClass = pedEstadoClasses[data.estado] || '';
                    let estadoIcon       = pedEstadoIcons[data.estado] || 'ri-question-line';
                    $('#view-estado').html(`<span class="badge badge-status ${estadoBadgeClass} rounded-pill"><i class="${estadoIcon} me-1"></i>${data.estado}</span>`);

                    var totalUsd = parseFloat(data.total);
                    $('#view-total-resumen').text('$' + totalUsd.toFixed(2));
                    var bsLbl = (typeof window.bsEquivalente === 'function') ? window.bsEquivalente(totalUsd) : null;
                    $('#view-total-resumen-bs').text(bsLbl || 'Sin tasa BCV');
                    // Chip "Creado por" (gutter derecho del stepper)
                    var pedCreador = data.creador || (data.user ? { name: data.user.name } : null);
                    $('#view-usuario-creador').text(pedCreador ? pedCreador.name : 'N/A');
                    $('#view-ped-creador-avatar').attr('src', (pedCreador && pedCreador.avatar_url) ? pedCreador.avatar_url : window.AMS_AVATAR_FALLBACK).css('display', '');
                    $('#view-ped-creador-fecha').text(pedCreador && pedCreador.fecha ? pedCreador.fecha : '—');

                    // Cargar y mostrar nuevos campos de pago y prioridad
                    $('#view-abono').text('$' + parseFloat(data.abono).toFixed(2));
                    let restante = parseFloat(data.total) - parseFloat(data.abono);
                    $('#view-restante').text('$' + restante.toFixed(2));

                    // Mostrar pagos normalizados desde data.pagos
                    var $pagosEl = $('#view-pagos-list');
                    $pagosEl.empty();

                    var metodoLabels = { efectivo: 'Efectivo', transferencia: 'Transferencia', pago_movil: 'Pago Móvil' };
                    var metodoIcons  = { efectivo: 'ri-money-dollar-circle-line', transferencia: 'ri-bank-card-line', pago_movil: 'ri-smartphone-line' };
                    var metodoBoxCls = { efectivo: 'emp-icon-box--green', transferencia: 'emp-icon-box--navy', pago_movil: 'emp-icon-box--teal' };
                    var metodoIcoCls = { efectivo: 'emp-icon--green', transferencia: 'emp-icon--navy', pago_movil: 'emp-icon--teal' };

                    var totalPagado = 0;
                    var nPagos = (data.pagos && data.pagos.length) ? data.pagos.length : 0;

                    if (nPagos > 0) {
                        var cardsHtml = '';
                        data.pagos.forEach(function (pago) {
                            var label  = metodoLabels[pago.metodo]  || pago.metodo;
                            var icon   = metodoIcons[pago.metodo]   || 'ri-money-dollar-circle-line';
                            var boxCls = metodoBoxCls[pago.metodo]  || 'emp-icon-box--navy';
                            var icoCls = metodoIcoCls[pago.metodo]  || 'emp-icon--navy';
                            var montoNum = parseFloat(pago.monto) || 0;
                            totalPagado += montoNum;
                            var monto = montoNum.toFixed(2);

                            var extraHtml = '';
                            if (pago.metodo !== 'efectivo') {
                                var bancoNombre = pago.banco ? pago.banco.nombre : 'Sin banco';
                                var referencia  = pago.referencia || 'Sin referencia';
                                extraHtml =
                                    '<div class="ped-pago-meta">' +
                                        '<span><i class="ri-bank-line"></i>' + bancoNombre + '</span>' +
                                        '<span><i class="ri-hashtag"></i>' + referencia + '</span>' +
                                    '</div>';
                            }

                            cardsHtml +=
                                '<div class="ped-pago-item">' +
                                    '<div class="ped-pago-icon ' + boxCls + '">' +
                                        '<i class="' + icon + ' ' + icoCls + '"></i>' +
                                    '</div>' +
                                    '<div class="ped-pago-main">' +
                                        '<div class="ped-pago-top">' +
                                            '<span class="ped-pago-metodo">' + label + '</span>' +
                                            '<span class="ped-pago-monto">$' + monto + '</span>' +
                                        '</div>' +
                                        extraHtml +
                                    '</div>' +
                                '</div>';
                        });
                        $pagosEl.html(cardsHtml);
                    } else {
                        $pagosEl.html(
                            '<div class="ped-pagos-empty">' +
                                '<i class="ri-wallet-3-line"></i>' +
                                '<span>Sin pagos registrados.</span>' +
                            '</div>'
                        );
                    }

                    // Footer del card: conteo, total pagado y estado de pago
                    $('#view-pagos-count').text(nPagos + (nPagos === 1 ? ' pago' : ' pagos'));
                    $('#view-pagos-total').text('$' + totalPagado.toFixed(2));

                    var totalPed = parseFloat(data.total) || 0;
                    var $estadoPago = $('#view-pagos-status');
                    if (nPagos === 0) {
                        $estadoPago.attr('class', 'ped-pagos-status ped-pagos-status--none')
                            .html('<i class="ri-information-line"></i>Sin pagos');
                    } else if (totalPagado + 0.001 >= totalPed) {
                        $estadoPago.attr('class', 'ped-pagos-status ped-pagos-status--full')
                            .html('<i class="ri-checkbox-circle-line"></i>Pagado completo');
                    } else {
                        $estadoPago.attr('class', 'ped-pagos-status ped-pagos-status--part')
                            .html('<i class="ri-progress-3-line"></i>Abono parcial');
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

                    // Tasa BCV heredada — paso 3
                    var tasaHeredada = parseFloat(data.tasa_cambio_valor) || 0;
                    if (tasaHeredada > 0 && typeof window.bsTasaFmt === 'function') {
                        $('#view-ped-tasa').text(window.bsTasaFmt(tasaHeredada));
                        $('#view-ped-tasa-fecha').text(data.tasa_fecha_fmt ? ' (' + data.tasa_fecha_fmt + ')' : '');
                    } else if (window.tasaBcv && window.tasaBcv.valor) {
                        $('#view-ped-tasa').text(window.bsTasaFmt ? window.bsTasaFmt() : 'Bs ' + parseFloat(window.tasaBcv.valor).toLocaleString('es-VE', { minimumFractionDigits: 4, maximumFractionDigits: 4 }));
                        var pedTasaFecha = (typeof window.bcvFechaFmt === 'function') ? window.bcvFechaFmt() : '';
                        $('#view-ped-tasa-fecha').text(pedTasaFecha ? ' (' + pedTasaFecha + ')' : '');
                    }

                    // Grilla de productos — paso 2
                    viewPedRenderGrilla(data.productos, data.tasa_cambio_valor);

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
