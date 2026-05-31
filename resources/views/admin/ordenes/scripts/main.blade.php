<script>
    // Validación onblur: fecha_fin_estimada debe ser posterior a fecha_inicio
    $(document).on('blur', '#fecha-fin-estimada-field', function () {
        let finVal = $(this).val();
        let inicioVal = $('#fecha-inicio-field').val();
        if (finVal && inicioVal) {
            if (finVal <= inicioVal) {
                marcarInvalido($(this), 'La fecha fin estimada debe ser posterior a la fecha de inicio.');
            } else {
                marcarValido($(this));
            }
        } else if (finVal) {
            marcarValido($(this));
        }
    });

    // Validación onblur: fecha_inicio — obligatoria
    $(document).on('blur', '#fecha-inicio-field', function () {
        if (!$(this).val()) {
            marcarInvalido($(this), 'La fecha de inicio es obligatoria.');
        } else {
            marcarValido($(this));
        }
        let $fin = $('#fecha-fin-estimada-field');
        if ($fin.val()) { $fin.trigger('blur'); }
    });

    // Validación al cerrar Select2 — insumo obligatorio (modal nested)
    $(document).on('select2:close', '#insumo-add-select', function () {
        if (!$(this).val()) {
            marcarInvalido($(this), 'Seleccione un insumo.');
        } else {
            marcarValido($(this));
        }
    });

    // Validación onblur: cantidad del insumo en el modal nested
    $(document).on('blur', '#insumo-add-cantidad', function () {
        let val = parseFloat($(this).val());
        if (isNaN(val) || val <= 0) {
            marcarInvalido($(this), 'La cantidad debe ser mayor a cero.');
        } else {
            marcarValido($(this));
        }
    });

    // Validación onblur: avanceModal — cantidad_producida (máx = piezas restantes)
    $(document).on('blur', '#am-cantidad-producida', function () {
        let producida = parseFloat($(this).val());
        let restante  = parseInt($('#am-restante').val()) || 0;
        if (isNaN(producida) || producida < 1) {
            marcarInvalido($(this), 'La cantidad producida debe ser al menos 1.');
        } else if (restante > 0 && producida > restante) {
            marcarInvalido($(this), 'No puede superar las ' + restante + ' piezas restantes de la orden.');
        } else {
            marcarValido($(this));
        }
        if ($('#am-cantidad-defectuosa').val() !== '') {
            $('#am-cantidad-defectuosa').trigger('blur');
        }
    });

    // Validación onblur: avanceModal — cantidad_defectuosa ≤ cantidad_producida
    $(document).on('blur', '#am-cantidad-defectuosa', function () {
        let defectuosa = parseFloat($(this).val());
        let producida  = parseFloat($('#am-cantidad-producida').val());
        if (isNaN(defectuosa) || defectuosa < 0) {
            marcarInvalido($(this), 'La cantidad defectuosa no puede ser negativa.');
        } else if (!isNaN(producida) && defectuosa > producida) {
            marcarInvalido($(this), 'La cantidad defectuosa no puede superar la cantidad producida (' + producida + ').');
        } else {
            marcarValido($(this));
        }
    });

    $(document).ready(function () {
        // ══════════════════════════════════════════════════════
        // Helpers
        // ══════════════════════════════════════════════════════
        function fmtMoneda(n) {
            return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        const hoyISO = () => new Date().toISOString().split('T')[0];
        const formatDateForInput = (dateString) => {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toISOString().split('T')[0];
        };

        // ── Insumos: estado + grilla + nested modal ──────────────────
        // Estado en memoria. Render → tabla visible + hidden inputs en #insumos-container
        // (fuente de verdad para FormData).
        let ordenInsumosState = []; // [{ id, nombre, unidad, cantidad }]

        // Select2 dentro del nested modal de agregar
        $('#insumo-add-select').select2({
            theme: 'bootstrap-5',
            placeholder: 'Seleccione insumo...',
            width: '100%',
            dropdownParent: $('#insumoAddModal')
        });

        function renderInsumosGrid() {
            const $tbody    = $('#orden-insumos-tbody');
            const $empty    = $('#orden-insumos-empty');
            const $wrap     = $('#orden-insumos-table-wrap');
            const $hidden   = $('#insumos-container');
            $('#orden-insumos-count').text('(' + ordenInsumosState.length + ')');

            if (!ordenInsumosState.length) {
                $tbody.empty();
                $hidden.empty();
                $wrap.attr('hidden', true);
                $empty.show();
                return;
            }
            $empty.hide();
            $wrap.removeAttr('hidden');

            $tbody.html(ordenInsumosState.map(function (it, idx) {
                const cant = parseFloat(it.cantidad).toFixed(2);
                return '<tr>' +
                    '<td class="cot-col-num">' + (idx + 1) + '</td>' +
                    '<td class="cot-col-prod">' +
                        '<div class="fw-semibold">' + escHtml(it.nombre) + '</div>' +
                        (it.unidad ? '<small class="text-muted">' + escHtml(it.unidad) + '</small>' : '') +
                    '</td>' +
                    '<td class="cot-col-num text-end fw-semibold">' + cant + '</td>' +
                    '<td class="cot-col-acc text-center">' +
                        '<button type="button" class="btn btn-sm btn-soft-primary edit-insumo-btn me-1" data-idx="' + idx + '" title="Editar"><i class="ri-pencil-line"></i></button>' +
                        '<button type="button" class="btn btn-sm btn-soft-danger remove-insumo" data-idx="' + idx + '" title="Quitar"><i class="ri-delete-bin-line"></i></button>' +
                    '</td>' +
                '</tr>';
            }).join(''));

            // Sincronizar hidden inputs (fuente de verdad para FormData)
            $hidden.html(ordenInsumosState.map(function (it, idx) {
                return '<input type="hidden" name="insumos[' + idx + '][id]" value="' + it.id + '">' +
                       '<input type="hidden" name="insumos[' + idx + '][cantidad_estimada]" value="' + it.cantidad + '">';
            }).join(''));
        }

        function resetInsumos() {
            ordenInsumosState = [];
            renderInsumosGrid();
        }

        function abrirInsumoAddModal(idx) {
            $('#insumoAddModal').find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            if (idx != null && ordenInsumosState[idx]) {
                const it = ordenInsumosState[idx];
                $('#insumo-add-edit-idx').val(idx);
                $('#insumoAddModal-title').text('Editar insumo');
                $('#insumo-add-confirm-label').text('Guardar');
                $('#insumo-add-select').val(it.id).trigger('change');
                $('#insumo-add-cantidad').val(it.cantidad);
            } else {
                $('#insumo-add-edit-idx').val('');
                $('#insumoAddModal-title').text('Agregar insumo');
                $('#insumo-add-confirm-label').text('Agregar');
                $('#insumo-add-select').val('').trigger('change');
                $('#insumo-add-cantidad').val('');
            }
            $('#insumoAddModal').modal('show');
        }

        // ══════════════════════════════════════════════════════
        // SELECCIÓN DE PEDIDO / LÍNEA (modal de cards)
        // ══════════════════════════════════════════════════════
        let pedidosOrdenData = [];

        $(document).on('shown.bs.modal', '#seleccionarPedidoModal', function () {
            cargarPedidosDisponibles();
        });

        function cargarPedidosDisponibles() {
            const $cont = $('#pedidos-orden-container');
            const $empty = $('#pedidos-orden-empty');
            const $loading = $('#pedidos-orden-loading');

            $cont.hide().empty();
            $empty.hide();
            $loading.show();

            $.ajax({
                url: '{{ route("ordenes.pedidos-disponibles") }}',
                method: 'GET',
                success: function (data) {
                    $loading.hide();
                    pedidosOrdenData = data || [];
                    if (!pedidosOrdenData.length) { $empty.show(); return; }
                    renderPedidosOrden(pedidosOrdenData);
                    $cont.show();
                },
                error: function () {
                    $loading.hide();
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los pedidos disponibles.' });
                }
            });
        }

        function escHtml(s) {
            return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function renderPedidosOrden(pedidos) {
            const $cont = $('#pedidos-orden-container');
            $cont.empty();

            pedidos.forEach(function (p) {
                const lineasHtml = p.lineas.map(function (l) {
                    const meta = [
                        l.cantidad + ' u',
                        l.color || 'Sin color',
                        l.talla || 'Talla única'
                    ].join(' · ');
                    const bordadoBadge = l.lleva_bordado
                        ? `<span class="badge bg-info-subtle text-info ms-1"><i class="ri-scissors-cut-line"></i> ${l.bordados_count} bordado(s)</span>`
                        : '';
                    // Líneas con orden activa → solo badge informativo
                    if (l.orden_id) {
                        return `
                            <div class="list-group-item d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                <div>
                                    <div class="fw-semibold text-muted">${escHtml(l.producto_nombre)}${bordadoBadge}</div>
                                    <small class="text-muted">${escHtml(meta)}</small>
                                </div>
                                <span class="badge bg-secondary"><i class="ri-check-line"></i> Orden #${l.orden_id}</span>
                            </div>`;
                    }
                    // Líneas pendientes → checkbox seleccionable
                    return `
                        <label class="list-group-item d-flex align-items-center gap-2 flex-wrap" style="cursor: pointer;">
                            <input type="checkbox" class="form-check-input linea-check"
                                data-pedido-id="${p.id}" data-detalle-id="${l.detalle_id}">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">${escHtml(l.producto_nombre)}${bordadoBadge}</div>
                                <small class="text-muted">${escHtml(meta)}</small>
                            </div>
                        </label>`;
                }).join('');

                const hayPendientes = p.lineas_pendientes > 0;
                const footerHtml = hayPendientes
                    ? `<div class="d-flex justify-content-end mt-2">
                          <button type="button" class="btn btn-sm btn-success crear-batch-btn"
                              data-pedido-id="${p.id}" disabled>
                              <i class="ri-add-line me-1"></i><span class="batch-btn-label">Selecciona líneas</span>
                          </button>
                       </div>`
                    : '';

                const card = `
                    <div class="cotizacion-card" data-pedido-id="${p.id}">
                        <div class="cotizacion-header">
                            <span class="cotizacion-numero"><i class="ri-shopping-bag-line"></i> Pedido #${p.id}</span>
                            <span class="badge ${hayPendientes ? 'bg-success-subtle text-success' : 'bg-secondary'}">
                                ${p.lineas_pendientes} de ${p.total_lineas} sin orden
                            </span>
                        </div>
                        <div class="cotizacion-info">
                            <div class="cotizacion-info-item"><i class="ri-user-line"></i><span>${escHtml(p.cliente_nombre)}</span></div>
                            <div class="cotizacion-info-item"><i class="ri-bank-card-line"></i><span>${escHtml(p.cliente_documento)}</span></div>
                            <div class="cotizacion-info-item"><i class="ri-calendar-line"></i><span>${p.fecha_pedido || 'N/A'}</span></div>
                            <div class="cotizacion-info-item"><i class="ri-bar-chart-line"></i><span>Progreso ${p.progreso}%</span></div>
                        </div>
                        <div class="list-group mt-2">${lineasHtml}</div>
                        ${footerHtml}
                    </div>`;
                $cont.append(card);
            });
        }

        // Actualizar texto y estado disabled del botón "Crear N" por pedido
        function actualizarBotonBatch(pedidoId) {
            const $card = $('#pedidos-orden-container .cotizacion-card[data-pedido-id="' + pedidoId + '"]');
            const seleccionadas = $card.find('.linea-check:checked').length;
            const $btn = $card.find('.crear-batch-btn');
            $btn.prop('disabled', seleccionadas === 0);
            const $label = $btn.find('.batch-btn-label');
            if (seleccionadas === 0)      $label.text('Selecciona líneas');
            else if (seleccionadas === 1) $label.text('Crear 1 orden');
            else                          $label.text('Crear ' + seleccionadas + ' órdenes');
        }

        // Tracking de checkboxes
        $(document).on('change', '.linea-check', function () {
            actualizarBotonBatch($(this).data('pedido-id'));
        });

        // Búsqueda
        $('#buscarPedidoOrden').on('keyup', function () {
            const term = $(this).val().toLowerCase();
            if (!term) { renderPedidosOrden(pedidosOrdenData); return; }
            const filtradas = pedidosOrdenData.filter(function (p) {
                return (p.cliente_nombre || '').toLowerCase().includes(term) ||
                    (p.cliente_documento || '').toLowerCase().includes(term) ||
                    String(p.id).includes(term);
            });
            renderPedidosOrden(filtradas);
        });

        // Click "Crear N órdenes" → 1 línea: modal single; 2+ líneas: modal batch
        $(document).on('click', '.crear-batch-btn', function () {
            const pedidoId = $(this).data('pedido-id');
            const pedido = pedidosOrdenData.find(p => p.id == pedidoId);
            if (!pedido) return;
            const $card = $(this).closest('.cotizacion-card');
            const detalleIds = $card.find('.linea-check:checked').map(function () {
                return parseInt($(this).data('detalle-id'), 10);
            }).get();
            if (!detalleIds.length) return;

            const lineas = detalleIds.map(id => pedido.lineas.find(l => l.detalle_id == id)).filter(Boolean);
            $('#seleccionarPedidoModal').modal('hide');

            // 1 línea → modal individual (UX completa, edita insumos)
            if (lineas.length === 1) {
                setTimeout(() => ordenAbrirDesdeLinea(pedido, lineas[0]), 300);
                return;
            }
            // 2+ líneas → modal batch (defaults compartidos, insumos por template)
            setTimeout(() => batchAbrir(pedido, lineas), 300);
        });

        // ══════════════════════════════════════════════════════
        // HIDRATAR FORM DESDE UNA LÍNEA (modo crear)
        // ══════════════════════════════════════════════════════
        function llenarPanelLinea(d) {
            $('#orden-linea-pedido').text('Pedido #' + d.pedido_id);
            $('#orden-linea-cliente').text(d.cliente_nombre || '');
            $('#orden-linea-producto').text(d.producto_nombre || '—');
            $('#orden-linea-cantidad').text(d.cantidad != null ? d.cantidad : 0);

            // Chips translúcidos sobre el gradiente del hero
            const chipCls = 'badge rounded-pill bg-white bg-opacity-10 text-white fw-normal';
            const chips = [];
            chips.push('<span class="' + chipCls + '"><i class="ri-palette-line me-1"></i>' + escHtml(d.color || 'Sin color') + '</span>');
            chips.push('<span class="' + chipCls + '"><i class="ri-ruler-line me-1"></i>' + escHtml(d.talla || 'Talla única') + '</span>');
            if (d.lleva_bordado) {
                chips.push('<span class="' + chipCls + '"><i class="ri-scissors-cut-line me-1"></i>' + (d.bordados_count || 0) + ' bordado(s)</span>');
            }
            $('#orden-linea-meta').html(chips.join(''));
        }

        function ordenAbrirDesdeLinea(pedido, linea) {
            // Modo crear
            $('#id-field').val('');
            $('#modalTitle').text('Nueva Orden de Producción');
            $('#estado-container').hide();
            $('#add-btn').show();
            $('#edit-btn').hide();

            // Hidden
            $('#detalle-pedido-id-field').val(linea.detalle_id);
            $('#pedido-id-hidden-field').val(pedido.id);
            $('#producto-id-field').val(linea.producto_id);

            // Panel solo lectura
            llenarPanelLinea({
                pedido_id: pedido.id,
                cliente_nombre: pedido.cliente_nombre,
                producto_nombre: linea.producto_nombre,
                cantidad: linea.cantidad,
                color: linea.color,
                talla: linea.talla,
                lleva_bordado: linea.lleva_bordado,
                bordados_count: linea.bordados_count
            });

            // Empleado
            $('#empleado-id-field').val('');

            // Fechas sugeridas
            $('#fecha-inicio-field').val(hoyISO());
            if (pedido.fecha_entrega) {
                const fe = new Date(pedido.fecha_entrega);
                fe.setDate(fe.getDate() - 2); // 2 días antes de la entrega
                $('#fecha-fin-estimada-field').val(fe.toISOString().split('T')[0]);
            } else {
                $('#fecha-fin-estimada-field').val('');
            }

            // Insumos: prefill desde el template del tipo_producto (si tiene)
            resetInsumos();
            if (Array.isArray(linea.insumos_default) && linea.insumos_default.length) {
                ordenInsumosState = linea.insumos_default.map(function (i) {
                    return {
                        id: i.id,
                        nombre: i.nombre,
                        unidad: i.unidad || '',
                        cantidad: parseFloat(i.cantidad) || 0
                    };
                });
                renderInsumosGrid();
            }
            $('#notas-field').val('');

            // Limpiar validaciones
            $('#ordenForm').find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');

            $('#showModal').modal('show');
        }

        // ══════════════════════════════════════════════════════
        // BATCH: crear varias órdenes del mismo pedido en un solo flow
        // ══════════════════════════════════════════════════════
        // Estado en memoria. Cada fila = una orden a crear. Los insumos vienen
        // del template del tipo (Feature D) y se envían sin edición en batch.
        let batchState = { pedido: null, filas: [] };

        function batchAbrir(pedido, lineas) {
            batchState.pedido = pedido;
            batchState.filas = lineas.map(function (l) {
                return {
                    detalle_id: l.detalle_id,
                    producto_nombre: l.producto_nombre,
                    color: l.color,
                    talla: l.talla,
                    lleva_bordado: l.lleva_bordado,
                    bordados_count: l.bordados_count,
                    cantidad: l.cantidad,
                    insumos: Array.isArray(l.insumos_default) ? l.insumos_default.slice() : [],
                    // editables
                    empleado_id: '',
                    fecha_inicio: hoyISO(),
                    fecha_fin_estimada: pedido.fecha_entrega ? (function () {
                        const fe = new Date(pedido.fecha_entrega);
                        fe.setDate(fe.getDate() - 2);
                        return fe.toISOString().split('T')[0];
                    })() : '',
                };
            });

            // Header
            $('#batch-pedido-label').text('Pedido #' + pedido.id + ' — ' + pedido.cliente_nombre);
            $('#batch-submit-count').text(batchState.filas.length);

            // Defaults arriba: vacíos
            $('#batch-default-empleado').val('');
            $('#batch-default-inicio').val(hoyISO());
            $('#batch-default-fin').val(batchState.filas[0]?.fecha_fin_estimada || '');

            batchRenderFilas();
            $('#batchOrdenModal').modal('show');
        }

        function batchRenderFilas() {
            const $tbody = $('#batch-ordenes-tbody');
            // Cache de opciones de empleado (las mismas que en el select default)
            const empleadosOpts = $('#batch-default-empleado').html();

            $tbody.html(batchState.filas.map(function (f, idx) {
                const meta = [
                    f.color || 'Sin color',
                    f.talla || 'Talla única',
                    f.lleva_bordado ? (f.bordados_count + ' bord.') : ''
                ].filter(Boolean).join(' · ');
                return '<tr data-idx="' + idx + '">' +
                    '<td class="cot-col-num">' + (idx + 1) + '</td>' +
                    '<td class="cot-col-prod">' +
                        '<div class="fw-semibold">' + escHtml(f.producto_nombre) + '</div>' +
                        '<small class="text-muted">' + escHtml(meta) + '</small>' +
                    '</td>' +
                    '<td class="cot-col-num text-center fw-semibold">' + f.cantidad + '</td>' +
                    '<td><select class="form-select form-select-sm batch-empleado">' + empleadosOpts + '</select></td>' +
                    '<td><input type="date" class="form-control form-control-sm batch-inicio" value="' + (f.fecha_inicio || '') + '"></td>' +
                    '<td><input type="date" class="form-control form-control-sm batch-fin" value="' + (f.fecha_fin_estimada || '') + '"></td>' +
                    '<td class="text-center"><span class="badge bg-info-subtle text-info" title="Insumos prellenados desde el template del tipo">' +
                        '<i class="ri-tools-line"></i> ' + (f.insumos ? f.insumos.length : 0) +
                    '</span></td>' +
                '</tr>';
            }).join(''));

            // Setear el valor de empleado seleccionado por fila
            batchState.filas.forEach(function (f, idx) {
                $('#batch-ordenes-tbody tr[data-idx="' + idx + '"] .batch-empleado').val(f.empleado_id || '');
            });
        }

        // Sincronizar inputs visibles → estado (antes de submit / aplicar defaults)
        function batchSyncFilasDesdeDom() {
            $('#batch-ordenes-tbody tr').each(function () {
                const idx = parseInt($(this).data('idx'), 10);
                batchState.filas[idx].empleado_id        = $(this).find('.batch-empleado').val() || '';
                batchState.filas[idx].fecha_inicio       = $(this).find('.batch-inicio').val();
                batchState.filas[idx].fecha_fin_estimada = $(this).find('.batch-fin').val();
            });
        }

        // "Aplicar a todas" → propaga defaults a cada fila
        $(document).on('click', '#batch-apply-defaults', function () {
            const emp = $('#batch-default-empleado').val();
            const ini = $('#batch-default-inicio').val();
            const fin = $('#batch-default-fin').val();
            $('#batch-ordenes-tbody tr').each(function () {
                if (emp) $(this).find('.batch-empleado').val(emp);
                if (ini) $(this).find('.batch-inicio').val(ini);
                if (fin) $(this).find('.batch-fin').val(fin);
            });
            Swal.fire({
                icon: 'success', title: 'Defaults aplicados', toast: true, position: 'top-end',
                showConfirmButton: false, timer: 1500
            });
        });

        // Submit del batch
        $(document).on('click', '#batch-submit-btn', function () {
            batchSyncFilasDesdeDom();

            // Validación cliente: empleado + fechas + costo + fin > inicio
            const errores = [];
            batchState.filas.forEach(function (f, idx) {
                if (!f.empleado_id)                    errores.push('Fila ' + (idx + 1) + ': empleado requerido');
                if (!f.fecha_inicio)                   errores.push('Fila ' + (idx + 1) + ': fecha inicio requerida');
                if (!f.fecha_fin_estimada)             errores.push('Fila ' + (idx + 1) + ': fecha fin requerida');
                if (f.fecha_inicio && f.fecha_fin_estimada && f.fecha_fin_estimada <= f.fecha_inicio)
                    errores.push('Fila ' + (idx + 1) + ': la fecha fin debe ser posterior al inicio');
                if (!f.insumos || !f.insumos.length)
                    errores.push('Fila ' + (idx + 1) + ': sin insumos (configura el template del tipo)');
            });
            if (errores.length) {
                Swal.fire({ icon: 'warning', title: 'Revisa los datos', html: errores.join('<br>') });
                return;
            }

            const payload = {
                _token: '{{ csrf_token() }}',
                pedido_id: batchState.pedido.id,
                ordenes: batchState.filas.map(function (f) {
                    return {
                        detalle_pedido_id: f.detalle_id,
                        empleado_id: f.empleado_id,
                        fecha_inicio: f.fecha_inicio,
                        fecha_fin_estimada: f.fecha_fin_estimada,
                        insumos: f.insumos.map(function (i) {
                            return { id: i.id, cantidad_estimada: i.cantidad };
                        })
                    };
                })
            };

            const $btn = $('#batch-submit-btn').prop('disabled', true);
            $.ajax({
                url: "{{ route('ordenes.batch') }}",
                method: 'POST',
                data: payload,
                success: function (resp) {
                    $btn.prop('disabled', false);
                    $('#batchOrdenModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire({
                        icon: 'success', title: '¡Listo!', text: resp.message,
                        timer: 2200, showConfirmButton: false
                    });
                },
                error: function (xhr) {
                    $btn.prop('disabled', false);
                    let msg = 'Error al crear las órdenes.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).map(v => Array.isArray(v) ? v[0] : v).join('\n');
                        } else if (xhr.responseJSON.message) { msg = xhr.responseJSON.message; }
                    }
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
            });
        });

        // Reset al cerrar
        $('#batchOrdenModal').on('hidden.bs.modal', function () {
            batchState = { pedido: null, filas: [] };
            $('#batch-ordenes-tbody').empty();
        });

        // ══════════════════════════════════════════════════════
        // MIS ÓRDENES (consulta por empleado + registrar avance)
        // ══════════════════════════════════════════════════════
        let misOrdenesEmpleadoId = '';

        const estadoBadgeCls = {
            'Pendiente':  'badge-soft-warning',
            'En Proceso': 'badge-soft-info',
            'Finalizado': 'badge-soft-success',
            'Cancelado':  'badge-soft-danger'
        };

        // Íconos del semáforo de estado (consistente con Pedidos/Cotizaciones).
        const estadoIconCls = {
            'Pendiente':  'ri-time-line',
            'En Proceso': 'ri-loader-4-line',
            'Finalizado': 'ri-check-double-line',
            'Cancelado':  'ri-close-circle-line'
        };
        function iconEstadoOrden(estado) {
            return estadoIconCls[estado] || 'ri-question-line';
        }

        function renderMisOrdenes(ordenes) {
            const $cont = $('#mis-ordenes-container');
            $cont.empty();

            ordenes.forEach(function (o) {
                const badge = estadoBadgeCls[o.estado] || 'badge-soft-secondary';
                const activa = (o.estado === 'Pendiente' || o.estado === 'En Proceso');
                const pedidoTxt = o.pedido_id ? ('Pedido #' + o.pedido_id) : 'Orden manual';
                const avanceBtn = activa
                    ? `<button type="button" class="btn btn-sm btn-success avance-btn" data-id="${o.id}">
                           <i class="ri-add-circle-line me-1"></i>Registrar avance
                       </button>`
                    : '';
                const card = `
                    <div class="cotizacion-card">
                        <div class="cotizacion-header">
                            <span class="cotizacion-numero"><i class="ri-file-list-3-line"></i> Orden #${o.id}</span>
                            <span class="badge badge-status ${badge} rounded-pill"><i class="${iconEstadoOrden(o.estado)} me-1"></i>${escHtml(o.estado)}</span>
                        </div>
                        <div class="cotizacion-info">
                            <div class="cotizacion-info-item"><i class="ri-t-shirt-line"></i><span>${escHtml(o.producto)}</span></div>
                            <div class="cotizacion-info-item"><i class="ri-shopping-bag-line"></i><span>${escHtml(pedidoTxt)}</span></div>
                            <div class="cotizacion-info-item"><i class="ri-calendar-check-line"></i><span>Entrega: ${escHtml(o.fecha_fin_estimada || 'N/A')}</span></div>
                        </div>
                        <div class="mt-2">
                            <div class="d-flex justify-content-between fs-12 text-muted mb-1">
                                <span>${o.cantidad_producida} / ${o.cantidad_solicitada} u producidas</span>
                                <span>${o.progreso}%</span>
                            </div>
                            <div class="progress" style="height: 12px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: ${o.progreso}%"
                                    aria-valuenow="${o.progreso}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        ${avanceBtn ? `<div class="d-flex justify-content-end mt-2">${avanceBtn}</div>` : ''}
                    </div>`;
                $cont.append(card);
            });
        }

        function cargarMisOrdenes(empleadoId) {
            const $cont   = $('#mis-ordenes-container');
            const $empty  = $('#mis-ordenes-empty');
            const $hold   = $('#mis-ordenes-placeholder');
            const $load   = $('#mis-ordenes-loading');
            const $resumen = $('#mis-ordenes-resumen');

            $cont.empty().hide();
            $empty.hide();
            $hold.hide();
            $resumen.hide();

            if (!empleadoId) { $hold.show(); return; }
            $load.show();

            $.ajax({
                url: "{{ route('ordenes.por-empleado', ':id') }}".replace(':id', empleadoId),
                method: 'GET',
                success: function (resp) {
                    $load.hide();
                    $('#mo-total').text(resp.resumen.total);
                    $('#mo-pendientes').text(resp.resumen.pendientes);
                    $('#mo-en-proceso').text(resp.resumen.en_proceso);
                    $('#mo-finalizadas').text(resp.resumen.finalizadas);
                    $resumen.css('display', 'flex');

                    if (!resp.ordenes.length) { $empty.show(); return; }
                    renderMisOrdenes(resp.ordenes);
                    $cont.show();
                },
                error: function () {
                    $load.hide();
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar las órdenes del empleado.' });
                }
            });
        }

        $('#mis-ordenes-empleado').on('change', function () {
            misOrdenesEmpleadoId = $(this).val();
            cargarMisOrdenes(misOrdenesEmpleadoId);
        });

        // Reset al cerrar
        $('#misOrdenesModal').on('hidden.bs.modal', function () {
            misOrdenesEmpleadoId = '';
            $('#mis-ordenes-empleado').val('');
            $('#mis-ordenes-container').empty().hide();
            $('#mis-ordenes-empty').hide();
            $('#mis-ordenes-resumen').hide();
            $('#mis-ordenes-placeholder').show();
        });

        // ══════════════════════════════════════════════════════
        // DataTable
        // ══════════════════════════════════════════════════════
        function debounce(func, wait) {
            let timeout;
            return function () {
                const context = this, args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        function updateFilterBadge() {
            let count = 0;
            const ordenValue = $('#filter-orden').val();
            if ($('#filter-estado').val()) count++;
            if ($('#filter-fecha-desde').val()) count++;
            if ($('#filter-fecha-hasta').val()) count++;
            if (ordenValue && ordenValue !== 'recientes') count++;
            $('#active-filter-count').text(count).toggleClass('d-none', count === 0);
        }

        var table = $('#ordenes-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('ordenes.data') }}",
                data: function (d) {
                    d.filter_estado = $('#filter-estado').val();
                    d.filter_fecha_desde = $('#filter-fecha-desde').val();
                    d.filter_fecha_hasta = $('#filter-fecha-hasta').val();
                    d.filter_orden = $('#filter-orden').val();
                }
            },
            dom: 'rtip',
            columns: [
                { data: 'id', name: 'id', className: 'align-middle text-center', width: '7%' },
                { data: 'pedido_info', name: 'pedido.id', className: 'align-middle text-center', orderable: false, width: '9%' },
                { data: 'producto_info', name: 'producto.nombre', className: 'align-middle', orderable: false, searchable: false, width: '23%' },
                { data: 'cantidad_solicitada', name: 'cantidad_solicitada', className: 'align-middle text-center', width: '9%' },
                {
                    data: null, className: 'align-middle', width: '16%',
                    render: function (data) {
                        let porcentaje = data.cantidad_solicitada > 0
                            ? (data.cantidad_producida / data.cantidad_solicitada * 100).toFixed(2) : '0.00';
                        return `<div class="progress" style="height: 15px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: ${porcentaje}%"
                                aria-valuenow="${porcentaje}" aria-valuemin="0" aria-valuemax="100">${porcentaje}%</div>
                        </div>`;
                    }
                },
                {
                    data: 'fecha_fin_estimada', name: 'fecha_fin_estimada', className: 'align-middle', width: '12%',
                    render: function (data) {
                        let fecha = new Date(data);
                        return `<div class="text-nowrap"><div class="fw-medium">${fecha.toLocaleDateString('es-ES')}</div></div>`;
                    }
                },
                {
                    data: 'estado', className: 'align-middle text-center', width: '10%',
                    render: function (data) {
                        let clases = {
                            'Pendiente': 'status-pendiente badge-soft-warning',
                            'En Proceso': 'status-procesando badge-soft-info',
                            'Finalizado': 'status-finalizado badge-soft-success',
                            'Cancelado': 'status-cancelado badge-soft-danger'
                        };
                        let badgeClass = clases[data] || 'badge-soft-secondary';
                        return `<span class="badge badge-status ${badgeClass} rounded-pill"><i class="${iconEstadoOrden(data)} me-1"></i>${data}</span>`;
                    }
                },
                {
                    data: 'id',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    className: 'align-middle text-center',
                    width: '16%',
                    render: function (data, type, row) {
                        const estadoActivo = ['Pendiente', 'En Proceso'].includes(row.estado);

                        const sVer = `<button class="btn btn-sm btn-soft-info view-btn" data-id="${data}" title="Ver detalle"><i class="ri-eye-fill"></i></button>`;

                        let items = '';
                        if (estadoActivo) {
                            items += `<li><button type="button" class="dropdown-item act-item act-primary avance-btn" data-id="${data}"><span class="act-ic"><i class="ri-add-circle-line"></i></span>Registrar avance</button></li>`;
                        }
                        items += `<li><button type="button" class="dropdown-item act-item act-edit edit-btn" data-id="${data}"><span class="act-ic"><i class="ri-pencil-fill"></i></span>Editar</button></li>`;
                        items += `<li><button type="button" class="dropdown-item act-item act-del remove-btn" data-id="${data}"><span class="act-ic"><i class="ri-delete-bin-fill"></i></span>Eliminar</button></li>`;

                        const menu = `
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
            autoWidth: false,
            responsive: false,
            buttons: [
                { extend: 'copy',  exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } },
                { extend: 'csv',   exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } },
                { extend: 'excel', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } },
                { extend: 'pdf',   exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } },
                { extend: 'print', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } }
            ],
            language: lenguajeData
        });

        $('#filters-collapse-body')
            .on('show.bs.collapse', function () { $('.navy-filter-header').removeClass('is-collapsed'); })
            .on('hidden.bs.collapse', function () { $('.navy-filter-header').addClass('is-collapsed'); });

        $('#custom-search-input').on('input', debounce(function () {
            table.search(this.value).draw();
        }, 300));

        $('.navy-filter-select').on('change', function () {
            table.ajax.reload();
            updateFilterBadge();
        });

        $('#btn-clear-filters').on('click', function () {
            $('#filter-estado').val('');
            $('#filter-fecha-desde').val('');
            $('#filter-fecha-hasta').val('');
            $('#filter-orden').val('recientes');
            $('.navy-filter-select').trigger('change');
            $('#custom-search-input').val('');
            table.search('').draw();
            updateFilterBadge();
        });

        updateFilterBadge();

        // ══════════════════════════════════════════════════════
        // Insumos: abrir/editar/eliminar/confirmar (vía nested modal)
        // ══════════════════════════════════════════════════════
        $(document).on('click', '#add-insumo-btn', function () {
            abrirInsumoAddModal(null);
        });
        $(document).on('click', '.edit-insumo-btn', function () {
            abrirInsumoAddModal(parseInt($(this).data('idx'), 10));
        });
        $(document).on('click', '.remove-insumo', function () {
            const idx = parseInt($(this).data('idx'), 10);
            if (isNaN(idx)) return;
            ordenInsumosState.splice(idx, 1);
            renderInsumosGrid();
        });
        $(document).on('click', '#insumo-add-confirm', function () {
            const $sel = $('#insumo-add-select');
            const id = $sel.val();
            const cantidad = parseFloat($('#insumo-add-cantidad').val());
            if (!id) {
                marcarInvalido($sel, 'Selecciona un insumo.');
                return;
            }
            if (isNaN(cantidad) || cantidad <= 0) {
                marcarInvalido($('#insumo-add-cantidad'), 'La cantidad debe ser mayor a cero.');
                return;
            }

            const $opt = $sel.find('option:selected');
            const item = {
                id: parseInt(id, 10),
                nombre: $opt.data('nombre') || $opt.text().replace(/\s*\(.*\)\s*$/, ''),
                unidad: $opt.data('unidad') || '',
                cantidad: +cantidad.toFixed(2)
            };

            const idxStr = $('#insumo-add-edit-idx').val();
            if (idxStr !== '') {
                ordenInsumosState[parseInt(idxStr, 10)] = item;
            } else {
                // Si el insumo ya estaba en la lista, sumamos las cantidades
                const existing = ordenInsumosState.findIndex(x => x.id === item.id);
                if (existing !== -1) {
                    ordenInsumosState[existing].cantidad = +(ordenInsumosState[existing].cantidad + item.cantidad).toFixed(2);
                } else {
                    ordenInsumosState.push(item);
                }
            }

            renderInsumosGrid();
            $('#insumoAddModal').modal('hide');
        });
        $('#insumoAddModal').on('hidden.bs.modal', function () {
            $('#insumo-add-edit-idx').val('');
            $('#insumo-add-select').val('').trigger('change');
            $('#insumo-add-cantidad').val('');
            $('#insumoAddModal').find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
        });

        // ══════════════════════════════════════════════════════
        // VALIDACIÓN AL SUBMIT
        // ══════════════════════════════════════════════════════
        function validarFormularioOrden() {
            let esValido = true;

            // Línea del pedido seleccionada (solo en creación)
            let isEdit = $('#id-field').val() !== '';
            if (!isEdit && !$('#detalle-pedido-id-field').val()) {
                Swal.fire({ icon: 'warning', title: 'Sin pedido', text: 'Selecciona un pedido y producto antes de crear la orden.' });
                return false;
            }

            // Empleado — obligatorio
            let $emp = $('#empleado-id-field');
            if (!$emp.val()) { marcarInvalido($emp, 'Selecciona el empleado asignado.'); esValido = false; }
            else { marcarValido($emp); }

            // Fecha Inicio
            let $inicio = $('#fecha-inicio-field');
            if (!$inicio.val()) { marcarInvalido($inicio, 'La fecha de inicio es obligatoria.'); esValido = false; }
            else { marcarValido($inicio); }

            // Fecha Fin Estimada
            let $fin = $('#fecha-fin-estimada-field');
            if (!$fin.val()) { marcarInvalido($fin, 'La fecha fin estimada es obligatoria.'); esValido = false; }
            else if ($inicio.val() && $fin.val() <= $inicio.val()) { marcarInvalido($fin, 'La fecha fin estimada debe ser posterior a la fecha de inicio.'); esValido = false; }
            else { marcarValido($fin); }

            // Insumos: al menos 1 en el estado (cada uno se valida al agregarlo)
            if (!ordenInsumosState.length) {
                Swal.fire({ icon: 'warning', title: 'Sin insumos', text: 'Agrega al menos un insumo a la orden.', timer: 2200, showConfirmButton: false });
                esValido = false;
            }

            return esValido;
        }

        // Crear / actualizar orden
        $('#ordenForm').on('submit', function (e) {
            e.preventDefault();
            if (!validarFormularioOrden()) return;

            let formData = new FormData(this);
            let editId = $('#id-field').val();
            let url = editId
                ? "{{ route('ordenes.update', ':id') }}".replace(':id', editId)
                : "{{ route('ordenes.store') }}";
            if (editId) { formData.append('_method', 'PUT'); }

            let $btn = $(editId ? '#edit-btn' : '#add-btn').prop('disabled', true);

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $btn.prop('disabled', false);
                    $('#showModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Éxito', text: response.message, timer: 2000, showConfirmButton: false });
                },
                error: function (xhr) {
                    $btn.prop('disabled', false);
                    let msg = 'Ocurrió un error al procesar la solicitud.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).map(v => Array.isArray(v) ? v[0] : v).join('\n');
                        } else if (xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                    }
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
            });
        });

        // ══════════════════════════════════════════════════════
        // Editar orden
        // ══════════════════════════════════════════════════════
        $(document).on('click', '.edit-btn', function () {
            let id = $(this).data('id');
            $.get("{{ route('ordenes.edit', ':id') }}".replace(':id', id), function (data) {
                $('#modalTitle').text('Editar Orden de Producción');
                $('#id-field').val(data.id);
                $('#detalle-pedido-id-field').val(data.detalle_pedido_id || '');
                $('#pedido-id-hidden-field').val(data.pedido_id || '');
                $('#producto-id-field').val(data.producto_id || '');

                const det = data.detalle_pedido || {};
                llenarPanelLinea({
                    pedido_id: data.pedido_id || '—',
                    cliente_nombre: '',
                    producto_nombre: data.producto ? data.producto.nombre : ('Producto #' + data.producto_id),
                    cantidad: data.cantidad_solicitada,
                    color: det.color ? det.color.nombre : null,
                    talla: det.talla ? (det.talla.etiqueta || det.talla.nombre) : null,
                    lleva_bordado: !!(det.bordados && det.bordados.length),
                    bordados_count: det.bordados ? det.bordados.length : 0
                });

                $('#empleado-id-field').val(data.empleado_id || '');
                $('#fecha-inicio-field').val(formatDateForInput(data.fecha_inicio));
                $('#fecha-fin-estimada-field').val(formatDateForInput(data.fecha_fin_estimada));
                $('#estado-field').val(data.estado);
                $('#notas-field').val(data.notas);

                // Insumos: hidratar estado desde la orden y rerenderear la grilla
                ordenInsumosState = (data.insumos || []).map(function (i) {
                    return {
                        id: i.id,
                        nombre: i.nombre || ('Insumo #' + i.id),
                        unidad: i.unidad_medida || '',
                        cantidad: parseFloat(i.pivot && i.pivot.cantidad_estimada) || 0
                    };
                });
                renderInsumosGrid();

                $('#estado-container').show();
                $('#add-btn').hide();
                $('#edit-btn').show();
                $('#ordenForm').find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                $('#showModal').modal('show');
            });
        });

        // ══════════════════════════════════════════════════════
        // Ver orden
        // ══════════════════════════════════════════════════════
        $(document).on('click', '.view-btn', function () {
            let id = $(this).data('id');
            $.get("{{ route('ordenes.show', ':id') }}".replace(':id', id), function (data) {
                const estadoClases = {
                    'Pendiente':  'status-pendiente badge-soft-warning',
                    'En Proceso': 'status-procesando badge-soft-info',
                    'Finalizado': 'status-finalizado badge-soft-success',
                    'Cancelado':  'status-cancelado badge-soft-danger'
                };

                $('#view-producto').text(data.producto ? data.producto.nombre : 'N/A');
                $('#view-cantidad-solicitada').text(data.cantidad_solicitada);
                $('#view-cantidad-producida').text(data.cantidad_producida);

                let porcentaje = data.cantidad_solicitada > 0
                    ? (data.cantidad_producida / data.cantidad_solicitada * 100).toFixed(1) : '0.0';
                $('#view-progreso').css('width', porcentaje + '%').attr('aria-valuenow', porcentaje);
                $('#view-progreso-pct').text(porcentaje);

                const formatDate = (dateString) => {
                    if (!dateString) return 'N/A';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
                };

                $('#view-fecha-inicio').text(formatDate(data.fecha_inicio));
                $('#view-fecha-fin-estimada').text(formatDate(data.fecha_fin_estimada));
                $('#view-estado').html(`<span class="badge badge-status ${estadoClases[data.estado] || 'badge-soft-secondary'} rounded-pill"><i class="${iconEstadoOrden(data.estado)} me-1"></i>${data.estado}</span>`);
                $('#view-creado-por').text(data.creado_por ? data.creado_por.name : 'Sin especificar');
                $('#view-empleado').text(
                    data.empleado && data.empleado.persona ? data.empleado.persona.nombre_completo : 'Sin asignar'
                );
                $('#view-pedido-info').text(data.pedido_id ? 'Pedido #' + data.pedido_id : 'Orden Manual');

                // Diseño / Bordado — desde la línea del pedido (los bordados se definen por producto)
                const bordados = (data.detalle_pedido && data.detalle_pedido.bordados) || [];
                let disenoHtml;
                if (!bordados.length) {
                    disenoHtml = `<span class="text-muted fst-italic">Producto sin bordado / diseño.</span>`;
                } else {
                    disenoHtml = bordados.map(function (b) {
                        const logoName = b.logo ? b.logo.name : (b.nombre_logo_aplicado || 'Logo');
                        return `<div class="mb-2 pb-2 border-bottom">
                            <div class="d-flex gap-1 mb-1"><span class="text-muted fs-11">Aplicación:</span><span class="fw-medium fs-12">${escHtml(b.nombre_aplicado || '—')}</span></div>
                            <div class="d-flex gap-1 mb-1"><span class="text-muted fs-11">Logo:</span><span class="fw-medium fs-12">${escHtml(logoName)}</span></div>
                            <div class="d-flex gap-1"><span class="text-muted fs-11">Cantidad:</span><span class="fw-medium fs-12">${b.cantidad || 1}</span></div>
                        </div>`;
                    }).join('');
                }
                $('#view-logo').html(disenoHtml);

                // Insumos
                $('#view-insumos').empty();
                (data.insumos || []).forEach(insumo => {
                    let pct = (insumo.pivot.cantidad_utilizada / insumo.pivot.cantidad_estimada * 100).toFixed(2);
                    $('#view-insumos').append(`
                        <tr>
                            <td><h6 class="fs-13 mb-0">${escHtml(insumo.nombre)}</h6></td>
                            <td class="text-center">${insumo.pivot.cantidad_estimada} ${insumo.unidad_medida}</td>
                            <td class="text-center">${insumo.pivot.cantidad_utilizada} ${insumo.unidad_medida}</td>
                            <td>
                                <div class="progress animated-progress custom-progress progress-sm">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: ${pct}%"
                                        aria-valuenow="${pct}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="text-muted fs-11 mt-1 text-center">${pct}%</div>
                            </td>
                        </tr>`);
                });

                $('#view-notas').text(data.notas || 'Sin notas adicionales.');
                const formatDateTime = (dateString) => {
                    if (!dateString) return 'N/A';
                    const date = new Date(dateString);
                    return date.toLocaleString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                };
                $('#view-created').text(formatDateTime(data.created_at));

                new bootstrap.Tab(document.getElementById('tab-detalles-btn')).show();
                $('#viewModal').modal('show');
            });
        });

        // ══════════════════════════════════════════════════════
        // Eliminar orden
        // ══════════════════════════════════════════════════════
        $(document).on('click', '.remove-btn', function () {
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
                customClass: { confirmButton: 'btn btn-primary w-xs me-2', cancelButton: 'btn btn-danger w-xs', container: 'swal2-container' },
                buttonsStyling: false,
                showCloseButton: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('ordenes.destroy', ':id') }}".replace(':id', id),
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (response) {
                            table.ajax.reload();
                            Swal.fire('Eliminado', response.message, 'success');
                        },
                        error: function (xhr) {
                            Swal.fire('Error', (xhr.responseJSON && xhr.responseJSON.message) || 'Ocurrió un error', 'error');
                        }
                    });
                }
            });
        });

        // ══════════════════════════════════════════════════════
        // Avance de Producción (acumula sobre la orden)
        // ══════════════════════════════════════════════════════
        $(document).on('click', '.avance-btn', function () {
            const id = $(this).data('id');
            $.get("{{ route('ordenes.show', ':id') }}".replace(':id', id), function (data) {
                const restante = data.cantidad_solicitada - data.cantidad_producida;
                $('#am-orden-id').val(data.id);
                $('#am-restante').val(restante);
                $('#am-orden-info').text(`${data.producto ? data.producto.nombre : 'Orden'} · ${restante} piezas restantes`);
                $('#am-restante-hint').text(`(máx. ${restante})`);
                $('#am-cantidad-producida').attr('max', restante);
                $('#avanceModal').modal('show');
            });
        });

        $('#am-btn-save').on('click', function () {
            const ordenId    = $('#am-orden-id').val();
            const producida  = $('#am-cantidad-producida').val();
            const defectuosa = $('#am-cantidad-defectuosa').val();
            const restante   = parseInt($('#am-restante').val()) || 0;

            if (!producida || parseInt(producida) < 1) {
                Swal.fire({ icon: 'warning', title: 'Cantidad requerida', text: 'Ingresa una cantidad producida válida.', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                return;
            }
            if (parseInt(producida) > restante) {
                Swal.fire({ icon: 'warning', title: 'Cantidad excedida', text: `Solo quedan ${restante} piezas por producir en esta orden.`, toast: true, position: 'top-end', showConfirmButton: false, timer: 4000 });
                return;
            }
            if (defectuosa && parseInt(defectuosa) > parseInt(producida)) {
                Swal.fire({ icon: 'warning', title: 'Defectuosos inválidos', text: 'No pueden superar la cantidad producida.', toast: true, position: 'top-end', showConfirmButton: false, timer: 3500 });
                return;
            }

            $.ajax({
                url: "{{ route('ordenes.avance', ':id') }}".replace(':id', ordenId),
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    cantidad_producida: producida,
                    cantidad_defectuosa: defectuosa || 0
                },
                success: function () {
                    $('#avanceModal').modal('hide');
                    table.ajax.reload(null, false);
                    // Si "Mis Órdenes" está abierto, refrescar su lista con el avance recién registrado
                    if (misOrdenesEmpleadoId) { cargarMisOrdenes(misOrdenesEmpleadoId); }
                    Swal.fire({ icon: 'success', title: 'Avance registrado', toast: true, position: 'top-end', showConfirmButton: false, timer: 2500 });
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'No se pudo guardar el avance.' });
                }
            });
        });

        $('#avanceModal').on('hidden.bs.modal', function () {
            $('#am-orden-id').val('');
            $('#am-restante').val('');
            $('#am-orden-info').text('');
            $('#am-cantidad-producida').val('');
            $('#am-cantidad-defectuosa').val('0');
        });

        $('#viewModal').on('hidden.bs.modal', function () {
            new bootstrap.Tab(document.getElementById('tab-detalles-btn')).show();
        });

        // ══════════════════════════════════════════════════════
        // Reset del form al cerrar
        // ══════════════════════════════════════════════════════
        $('#showModal').on('hidden.bs.modal', function () {
            $('#ordenForm')[0].reset();
            $('#id-field').val('');
            $('#detalle-pedido-id-field').val('');
            $('#pedido-id-hidden-field').val('');
            $('#producto-id-field').val('');
            $('#orden-linea-pedido').text('Pedido #—');
            $('#orden-linea-cliente').text('');
            $('#orden-linea-producto').text('—');
            $('#orden-linea-cantidad').text('0');
            $('#orden-linea-meta').empty();
            $('#modalTitle').text('Nueva Orden de Producción');
            $('#estado-container').hide();
            $('#add-btn').show();
            $('#edit-btn').hide();
            $('#ordenForm').find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            resetInsumos();
        });
    });
</script>
