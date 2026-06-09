<!-- SortableJS — solo para el módulo Órdenes (Kanban de sub-órdenes) -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
    // ─────────────────────────────────────────────────────────────
    // Validaciones onblur de modales auxiliares (insumo nested + avance)
    // ─────────────────────────────────────────────────────────────
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
        var viewKanbanOrdenId = null; // ID de la OP actualmente en viewModal

        function escHtml(s) {
            return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
        const hoyISO = () => new Date().toISOString().split('T')[0];
        const formatDateForInput = (dateString) => {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toISOString().split('T')[0];
        };
        // Fin estimado sugerido: entrega del pedido − 2 días, pero NUNCA antes del
        // inicio (si la entrega ya pasó, usa inicio + 3 días como margen razonable).
        function finEstimadoDefault(fechaEntrega, inicioISO) {
            const inicio = inicioISO || hoyISO();
            let fin = '';
            if (fechaEntrega) {
                const fe = new Date(fechaEntrega);
                fe.setDate(fe.getDate() - 2);
                fin = fe.toISOString().split('T')[0];
            }
            if (!fin || fin <= inicio) {
                const d = new Date(inicio);
                d.setDate(d.getDate() + 3);
                fin = d.toISOString().split('T')[0];
            }
            return fin;
        }
        function parseDMY(s) { // "dd/mm/yyyy" -> "yyyy-mm-dd" para comparar
            if (!s) return '';
            const m = String(s).split('/');
            return m.length === 3 ? (m[2] + '-' + m[1] + '-' + m[0]) : '';
        }
        function debounce(func, wait) {
            let timeout;
            return function () {
                const context = this, args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        // Select2 dentro del nested modal de agregar insumo
        $('#insumo-add-select').select2({
            theme: 'bootstrap-5',
            placeholder: 'Seleccione insumo...',
            width: '100%',
            dropdownParent: $('#insumoAddModal')
        });

        // ══════════════════════════════════════════════════════════════
        // WIZARD ORDEN — estado unificado (escala de 1 a N líneas)
        // ══════════════════════════════════════════════════════════════
        const TOTAL_STEPS = 4;
        let currentStep = 1;

        // Estado del wizard. Cada "línea" = una orden a crear/editar.
        // línea: { detalle_id, producto_id, producto_nombre, cantidad, color, talla,
        //          lleva_bordado, bordados_count, empleado_id, fecha_inicio,
        //          fecha_fin_estimada, estado, insumos:[{id,nombre,unidad,cantidad}] }
        let ordWiz = { mode: 'create', editId: null, pedido: null, lineas: [] };
        function isEditMode() { return ordWiz.mode === 'edit'; }

        // Pedidos disponibles (paso 1)
        let pedidosOrdenData = [];

        // Insumo nested modal — línea destino + índice del insumo en edición
        let ordInsLineIdx = null;
        let ordInsEditIdx = null;

        function empleadoOptionsHtml() { return $('#ord-empleados-tpl').html(); }

        // Chips de cabecera (cliente + creador)
        function ordSetCreador(name, avatar) {
            $('#ord-creador-name').text(name || '—');
            if (avatar) $('#ord-creador-avatar').attr('src', avatar);
        }
        function ordResetCreador() {
            const $b = $('#ord-creador-banner');
            ordSetCreador($b.data('default-name'), $b.data('default-avatar'));
        }
        function ordSetClienteBanner(name, doc) {
            const n = (name || '').trim();
            $('#ord-banner-name').text(n || '—');
            $('#ord-banner-doc').text(doc || '');
            $('#ord-banner-avatar').text(n ? n.charAt(0).toUpperCase() : '—');
        }

        // Chips de atributos de una línea (color / talla / bordado)
        function lineaMetaChips(l) {
            const cls = 'badge rounded-pill badge-soft-secondary fw-normal';
            const chips = [];
            chips.push('<span class="' + cls + '"><i class="ri-palette-line me-1"></i>' + escHtml(l.color || 'Sin color') + '</span>');
            chips.push('<span class="' + cls + '"><i class="ri-ruler-line me-1"></i>' + escHtml(l.talla || 'Talla única') + '</span>');
            if (l.lleva_bordado) {
                chips.push('<span class="' + cls + '"><i class="ri-scissors-cut-line me-1"></i>' + (l.bordados_count || 0) + ' bordado(s)</span>');
            }
            return chips.join('');
        }

        // Construye una "línea" del wizard desde una línea del pedido disponible
        function makeLineaDesde(pedido, l) {
            return {
                detalle_id: l.detalle_id,
                producto_id: l.producto_id,
                producto_nombre: l.producto_nombre,
                cantidad: l.cantidad,
                color: l.color,
                talla: l.talla,
                lleva_bordado: l.lleva_bordado,
                bordados_count: l.bordados_count,
                empleado_id: '',
                fecha_inicio: hoyISO(),
                fecha_fin_estimada: finEstimadoDefault(pedido.fecha_entrega, hoyISO()),
                estado: 'Pendiente',
                insumos: Array.isArray(l.insumos_default)
                    ? l.insumos_default.map(function (i) {
                        return { id: i.id, nombre: i.nombre, unidad: i.unidad || '', cantidad: parseFloat(i.cantidad) || 0 };
                    })
                    : []
            };
        }

        // ── Navegación del wizard ────────────────────────────────────
        function ordShowStep(n) {
            n = Math.max(1, Math.min(TOTAL_STEPS, n));
            currentStep = n;

            $('#showModal .wiz-step-content').removeClass('is-active').attr('hidden', true);
            $('#showModal .wiz-step-content[data-step="' + n + '"]').addClass('is-active').removeAttr('hidden');

            $('#showModal .wiz-step-marker').each(function () {
                const s = parseInt($(this).data('step'), 10);
                $(this).removeClass('is-active is-complete').attr('aria-selected', 'false');
                if (s < n) $(this).addClass('is-complete');
                else if (s === n) $(this).addClass('is-active').attr('aria-selected', 'true');
            });

            for (let i = 1; i < TOTAL_STEPS; i++) {
                $('#showModal .wiz-step-line-fill[data-line="' + i + '"]').css('width', i < n ? '100%' : '0%');
            }

            $('#ord-step-current').text(n);
            $('#btn-ord-prev').toggle(n > 1);
            $('#btn-ord-next').toggle(n < TOTAL_STEPS);
            $('#ord-wiz-submit-btn').toggle(n === TOTAL_STEPS);

            actualizarBanners(n);

            if (n === 2) renderAsignacion();
            if (n === 3) renderInsumosAcc();
            if (n === 4) renderResumen();
        }

        function actualizarBanners(n) {
            const hayCliente = ordWiz.pedido && ordWiz.pedido.cliente_nombre;
            if (hayCliente && n >= 2) {
                ordSetClienteBanner(ordWiz.pedido.cliente_nombre, ordWiz.pedido.cliente_documento || '');
                $('#ord-cliente-banner').removeAttr('hidden').attr('aria-hidden', 'false');
            } else {
                $('#ord-cliente-banner').attr('hidden', true).attr('aria-hidden', 'true');
            }
            if (!isEditMode()) ordResetCreador();
            $('#ord-creador-banner').removeAttr('hidden').attr('aria-hidden', 'false');
        }

        function ordSyncStep(n) {
            if (n === 2) syncAsignacion();
            if (n === 4) { /* notas se leen al enviar */ }
        }

        function validateStep(n) {
            if (n === 1) return validateStep1();
            if (n === 2) return validateStep2();
            if (n === 3) return validateStep3();
            return true;
        }

        // ══════════════════════════════════════════════════════
        // PASO 1 — Pedido y líneas
        // ══════════════════════════════════════════════════════
        function ordCargarPedidos() {
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
                    aplicarFiltrosPedidos();
                },
                error: function () {
                    $loading.hide();
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los pedidos disponibles.' });
                }
            });
        }

        function renderPedidosOrden(pedidos) {
            const $cont = $('#pedidos-orden-container');
            $cont.empty();

            pedidos.forEach(function (p) {
                // Regla de negocio: sin abono mínimo no se pueden generar órdenes.
                const abonoOk = p.cumple_abono !== false;
                const lineasHtml = p.lineas.map(function (l) {
                    const meta = [l.cantidad + ' u', l.color || 'Sin color', l.talla || 'Talla única'].join(' · ');
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
                    // Sin abono mínimo: línea visible pero no seleccionable (bloqueo de negocio).
                    if (!abonoOk) {
                        return `
                            <div class="list-group-item d-flex align-items-center gap-2 flex-wrap opacity-75">
                                <i class="ri-lock-2-line text-warning"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">${escHtml(l.producto_nombre)}${bordadoBadge}</div>
                                    <small class="text-muted">${escHtml(meta)}</small>
                                </div>
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

                const abonoBanner = abonoOk ? '' : `
                        <div class="alert alert-warning d-flex align-items-center gap-2 py-1 px-2 small mb-2">
                            <i class="ri-lock-2-line"></i>
                            <span>Abono ${p.porcentaje_abonado}% — requiere ${p.abono_minimo_pct}% para iniciar producción.</span>
                        </div>`;

                const hayPendientes = p.lineas_pendientes > 0;
                const inicialCliente = (p.cliente_nombre || '?').trim().charAt(0).toUpperCase() || '?';
                const card = `
                    <div class="cotizacion-card" data-pedido-id="${p.id}">
                        <div class="cotizacion-header">
                            <span class="cotizacion-numero"><i class="ri-shopping-bag-line"></i> Pedido #${p.id}</span>
                            <span class="badge ${hayPendientes ? 'bg-success-subtle text-success' : 'bg-secondary'}">
                                ${p.lineas_pendientes} de ${p.total_lineas} sin orden
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mt-1 mb-2">
                            <span class="wiz-client-banner wiz-client-banner--sm" title="Cliente del pedido">
                                <span class="wiz-client-banner-avatar">${escHtml(inicialCliente)}</span>
                                <span class="wiz-client-banner-main">
                                    <span class="wiz-client-banner-name">${escHtml(p.cliente_nombre)}</span>
                                </span>
                            </span>
                            <span class="cotizacion-info-item"><i class="ri-bank-card-line"></i><span>${escHtml(p.cliente_documento)}</span></span>
                            <span class="cotizacion-info-item"><i class="ri-calendar-line"></i><span>${p.fecha_pedido || 'N/A'}</span></span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="progress flex-grow-1" style="height: 6px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: ${p.progreso}%;"></div>
                            </div>
                            <small class="text-muted fw-semibold" style="white-space: nowrap;">Progreso ${p.progreso}%</small>
                        </div>
                        ${abonoBanner}
                        <div class="list-group">${lineasHtml}</div>
                    </div>`;
                $cont.append(card);
            });

            // Restaurar marca visual de selección (si seguimos en el mismo pedido)
            ordResaltarSeleccion();
        }

        // Selección limitada a UN pedido (un batch = un pedido)
        $(document).on('change', '.linea-check', function () {
            if (this.checked) {
                const pid = $(this).data('pedido-id');
                let limpiado = false;
                $('#pedidos-orden-container .linea-check:checked').each(function () {
                    if ($(this).data('pedido-id') != pid) { $(this).prop('checked', false); limpiado = true; }
                });
                if (limpiado) {
                    Swal.fire({ icon: 'info', title: 'Una orden cubre un solo pedido', text: 'Se limpió la selección del pedido anterior.', toast: true, position: 'top-end', showConfirmButton: false, timer: 1800 });
                }
            }
            ordActualizarSeleccionChip();
            ordResaltarSeleccion();
        });

        function ordActualizarSeleccionChip() {
            const n = $('#pedidos-orden-container .linea-check:checked').length;
            const $chip = $('#ord-lineas-chip');
            if (n > 0) { $chip.removeClass('d-none').text(n === 1 ? '1 línea seleccionada' : n + ' líneas seleccionadas'); }
            else { $chip.addClass('d-none'); }
        }

        function ordResaltarSeleccion() {
            $('#pedidos-orden-container .cotizacion-card').removeClass('border border-2 border-success');
            const $checked = $('#pedidos-orden-container .linea-check:checked').first();
            if ($checked.length) {
                $checked.closest('.cotizacion-card').addClass('border border-2 border-success');
            }
        }

        function validateStep1() {
            if (isEditMode()) return true; // la línea queda fija al editar
            const $checked = $('#pedidos-orden-container .linea-check:checked');
            if (!$checked.length) {
                Swal.fire({ icon: 'warning', title: 'Sin selección', text: 'Selecciona al menos una línea del pedido para producir.' });
                return false;
            }
            const pid = $checked.first().data('pedido-id');
            const pedido = pedidosOrdenData.find(p => p.id == pid);
            if (!pedido) return false;
            if (pedido.cumple_abono === false) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Abono insuficiente',
                    html: `El Pedido #${pedido.id} tiene ${pedido.porcentaje_abonado}% abonado y requiere `
                        + `${pedido.abono_minimo_pct}% para iniciar producción.<br>Registra el abono en el pedido antes de generar órdenes.`,
                });
                return false;
            }
            const detalleIds = $checked.map(function () { return parseInt($(this).data('detalle-id'), 10); }).get();

            // Si la selección no cambió, conservar lo ya capturado (asignación/insumos)
            const prev = ordWiz.lineas.map(l => l.detalle_id).slice().sort().join(',');
            const now = detalleIds.slice().sort().join(',');
            if (ordWiz.pedido && ordWiz.pedido.id == pid && prev === now && ordWiz.lineas.length) return true;

            ordWiz.pedido = pedido;
            ordWiz.lineas = detalleIds.map(function (id) {
                const l = pedido.lineas.find(x => x.detalle_id == id);
                return l ? makeLineaDesde(pedido, l) : null;
            }).filter(Boolean);
            return ordWiz.lineas.length > 0;
        }

        // ── Filtros del paso 1 ───────────────────────────────────────
        function pedordUpdateBadge() {
            let count = 0;
            if ($('#pedord-filter-estado').val())    count++;
            if ($('#pedord-filter-cobertura').val()) count++;
            if ($('#pedord-filter-desde').val())     count++;
            if ($('#pedord-filter-hasta').val())     count++;
            if (($('#pedord-filter-orden').val() || 'recientes') !== 'recientes') count++;
            $('#pedord-filter-count').text(count).toggleClass('d-none', count === 0);
        }

        function pedordResetFiltros() {
            $('#pedord-search').val('');
            $('#pedord-filter-estado').val('');
            $('#pedord-filter-cobertura').val('');
            $('#pedord-filter-desde').val('');
            $('#pedord-filter-hasta').val('');
            $('#pedord-filter-orden').val('recientes');
            pedordUpdateBadge();
        }

        function aplicarFiltrosPedidos() {
            const $cont  = $('#pedidos-orden-container');
            const $empty = $('#pedidos-orden-empty');
            const term      = ($('#pedord-search').val() || '').toLowerCase().trim();
            const estado    = $('#pedord-filter-estado').val();
            const cobertura = $('#pedord-filter-cobertura').val();
            const desde     = $('#pedord-filter-desde').val();
            const hasta     = $('#pedord-filter-hasta').val();
            const orden     = $('#pedord-filter-orden').val();

            let arr = pedidosOrdenData.filter(function (p) {
                if (term) {
                    const hit = (p.cliente_nombre || '').toLowerCase().includes(term) ||
                        (p.cliente_documento || '').toLowerCase().includes(term) ||
                        String(p.id).includes(term);
                    if (!hit) return false;
                }
                if (estado && p.estado !== estado) return false;
                if (cobertura === 'pendientes' && !(p.lineas_pendientes > 0)) return false;
                if (cobertura === 'cubiertos'  && p.lineas_pendientes !== 0)  return false;
                if (desde || hasta) {
                    const fp = parseDMY(p.fecha_pedido);
                    if (desde && (!fp || fp < desde)) return false;
                    if (hasta && (!fp || fp > hasta)) return false;
                }
                return true;
            });

            if (orden === 'entrega') {
                arr = arr.slice().sort(function (a, b) {
                    const ea = a.fecha_entrega || '9999-12-31';
                    const eb = b.fecha_entrega || '9999-12-31';
                    return ea < eb ? -1 : (ea > eb ? 1 : 0);
                });
            } else if (orden === 'pendientes') {
                arr = arr.slice().sort(function (a, b) { return (b.lineas_pendientes || 0) - (a.lineas_pendientes || 0); });
            }

            renderPedidosOrden(arr);
            $cont.toggle(arr.length > 0);
            $empty.toggle(arr.length === 0);
            pedordUpdateBadge();
        }

        $('#pedord-filters-collapse')
            .on('show.bs.collapse',   function () { $('#pedord-advanced-filters .navy-filter-header').removeClass('is-collapsed'); })
            .on('hidden.bs.collapse', function () { $('#pedord-advanced-filters .navy-filter-header').addClass('is-collapsed'); });

        $('#pedord-search').on('keyup', debounce(aplicarFiltrosPedidos, 250));
        $('#pedord-advanced-filters .navy-filter-select').on('change', aplicarFiltrosPedidos);
        $('#pedord-clear-filters').on('click', function () { pedordResetFiltros(); aplicarFiltrosPedidos(); });

        // ══════════════════════════════════════════════════════
        // PASO 2 — Asignación (empleado + cronograma) por línea
        // ══════════════════════════════════════════════════════
        // Texto/clase de la píldora de duración (días entre inicio y fin)
        function actualizarDur($el, ini, fin) {
            if (!$el || !$el.length) return;
            if (!ini || !fin) { $el.html('').removeClass('is-bad'); return; }
            const dias = Math.round((new Date(fin) - new Date(ini)) / 86400000);
            if (isNaN(dias)) { $el.html('').removeClass('is-bad'); return; }
            if (dias < 0) { $el.html('<i class="ri-error-warning-line me-1"></i>Revisa fechas').addClass('is-bad'); return; }
            $el.html('<i class="ri-time-line me-1"></i>' + dias + (dias === 1 ? ' día' : ' días')).removeClass('is-bad');
        }

        function asignacionCardHtml(l, idx) {
            const meta = lineaMetaChips(l);
            const edit = isEditMode();
            const empCol = edit ? 'col-md-3' : 'col-md-4';
            const fechaCol = edit ? 'col-md-3' : 'col-md-4';
            // 'Cancelado' no se ofrece aquí: la cancelación va por su propia acción
            // (define reposición de stock condicional y exige motivo de merma).
            const estadoBlock = edit
                ? '<div class="col-md-3"><label class="form-label form-label-sm required mb-1" for="ord-asig-estado-' + idx + '"><i class="ri-flag-line me-1"></i>Estado</label>'
                  + '<select class="form-select form-select-sm ord-asig-estado" id="ord-asig-estado-' + idx + '" data-idx="' + idx + '">'
                  + '<option value="Pendiente">Pendiente</option><option value="En Proceso">En Proceso</option>'
                  + '<option value="Finalizado">Finalizado</option></select></div>'
                : '';
            return '<div class="ord-asig-card" data-idx="' + idx + '">'
                + '<div class="ord-asig-card-head">'
                +   '<span class="ord-asig-num">' + (idx + 1) + '</span>'
                +   '<div class="ord-asig-prod">'
                +     '<div class="ord-asig-prod-name">' + escHtml(l.producto_nombre) + ' <span class="ord-asig-qty">· ' + l.cantidad + ' u</span></div>'
                +     '<div class="ord-asig-chips">' + meta + '</div>'
                +   '</div>'
                +   '<span class="ord-asig-dur" id="ord-asig-dur-' + idx + '"></span>'
                + '</div>'
                + '<div class="ord-asig-card-body"><div class="row g-2">'
                +   '<div class="' + empCol + '"><label class="form-label form-label-sm required mb-1" for="ord-asig-emp-' + idx + '">Empleado asignado</label>'
                +     '<div class="input-group input-group-sm"><span class="input-group-text"><i class="ri-user-star-line"></i></span>'
                +     '<select class="form-select ord-asig-emp" id="ord-asig-emp-' + idx + '" data-idx="' + idx + '">' + empleadoOptionsHtml() + '</select></div></div>'
                +   '<div class="' + fechaCol + '"><label class="form-label form-label-sm required mb-1" for="ord-asig-inicio-' + idx + '">Inicio</label>'
                +     '<div class="input-group input-group-sm"><span class="input-group-text"><i class="ri-calendar-event-line"></i></span>'
                +     '<input type="date" class="form-control ord-asig-inicio" id="ord-asig-inicio-' + idx + '" data-idx="' + idx + '" value="' + (l.fecha_inicio || '') + '"></div></div>'
                +   '<div class="' + fechaCol + '"><label class="form-label form-label-sm required mb-1" for="ord-asig-fin-' + idx + '">Fin estimado</label>'
                +     '<div class="input-group input-group-sm"><span class="input-group-text"><i class="ri-calendar-check-line"></i></span>'
                +     '<input type="date" class="form-control ord-asig-fin" id="ord-asig-fin-' + idx + '" data-idx="' + idx + '" value="' + (l.fecha_fin_estimada || '') + '"></div></div>'
                +   estadoBlock
                + '</div></div>'
                + '</div>';
        }

        function renderAsignacion() {
            const multi = ordWiz.lineas.length > 1;
            $('#ord-apply-bar').attr('hidden', !multi);
            $('#ord-porlinea-sep').attr('hidden', !multi);
            $('#ord-porlinea-count').text(multi ? ordWiz.lineas.length : '');
            $('#ord-asignacion-desc').text(multi
                ? 'Asigna empleado y fechas a cada línea. Usa "Aplicar a todas" para ir más rápido.'
                : 'Define quién produce la orden y sus fechas.');

            if (multi) {
                if (!$('#ord-default-empleado option').length) $('#ord-default-empleado').html(empleadoOptionsHtml());
                $('#ord-default-inicio').val(hoyISO());
                $('#ord-default-fin').val(ordWiz.lineas[0].fecha_fin_estimada || '');
            }

            $('#ord-asignacion-cards').html(ordWiz.lineas.map(function (l, idx) {
                return asignacionCardHtml(l, idx);
            }).join(''));

            ordWiz.lineas.forEach(function (l, idx) {
                $('#ord-asig-emp-' + idx).val(l.empleado_id || '');
                if (isEditMode()) $('#ord-asig-estado-' + idx).val(l.estado || 'Pendiente');
                actualizarDur($('#ord-asig-dur-' + idx), l.fecha_inicio, l.fecha_fin_estimada);
            });
        }

        // Duración en vivo al cambiar fechas de una línea
        $(document).on('change', '.ord-asig-inicio, .ord-asig-fin', function () {
            const idx = parseInt($(this).data('idx'), 10);
            actualizarDur($('#ord-asig-dur-' + idx), $('#ord-asig-inicio-' + idx).val(), $('#ord-asig-fin-' + idx).val());
        });

        function syncAsignacion() {
            $('#ord-asignacion-cards .ord-asig-card').each(function () {
                const idx = parseInt($(this).data('idx'), 10);
                const l = ordWiz.lineas[idx];
                if (!l) return;
                l.empleado_id = $(this).find('.ord-asig-emp').val() || '';
                l.fecha_inicio = $(this).find('.ord-asig-inicio').val() || '';
                l.fecha_fin_estimada = $(this).find('.ord-asig-fin').val() || '';
                const $est = $(this).find('.ord-asig-estado');
                if ($est.length) l.estado = $est.val();
            });
        }

        $(document).on('click', '#ord-apply-defaults', function () {
            const emp = $('#ord-default-empleado').val();
            const ini = $('#ord-default-inicio').val();
            const fin = $('#ord-default-fin').val();
            $('#ord-asignacion-cards .ord-asig-card').each(function () {
                const idx = parseInt($(this).data('idx'), 10);
                if (emp) $(this).find('.ord-asig-emp').val(emp);
                if (ini) $(this).find('.ord-asig-inicio').val(ini);
                if (fin) $(this).find('.ord-asig-fin').val(fin);
                actualizarDur($('#ord-asig-dur-' + idx), $('#ord-asig-inicio-' + idx).val(), $('#ord-asig-fin-' + idx).val());
            });
            Swal.fire({ icon: 'success', title: 'Aplicado a todas', toast: true, position: 'top-end', showConfirmButton: false, timer: 1400 });
        });

        // Validación en vivo de fechas por línea
        $(document).on('blur', '.ord-asig-fin', function () {
            const $row = $(this).closest('.ord-asig-card');
            const ini = $row.find('.ord-asig-inicio').val();
            const fin = $(this).val();
            if (fin && ini && fin <= ini) marcarInvalido($(this), 'El fin debe ser posterior al inicio.');
            else if (fin) marcarValido($(this));
        });

        function validateStep2() {
            syncAsignacion();
            let ok = true, $first = null;
            ordWiz.lineas.forEach(function (l, idx) {
                const $emp = $('#ord-asig-emp-' + idx), $ini = $('#ord-asig-inicio-' + idx), $fin = $('#ord-asig-fin-' + idx);
                if (!l.empleado_id) { marcarInvalido($emp, 'Selecciona el empleado.'); ok = false; $first = $first || $emp; } else marcarValido($emp);
                if (!l.fecha_inicio) { marcarInvalido($ini, 'Fecha de inicio requerida.'); ok = false; $first = $first || $ini; } else marcarValido($ini);
                if (!l.fecha_fin_estimada) { marcarInvalido($fin, 'Fecha fin requerida.'); ok = false; $first = $first || $fin; }
                else if (l.fecha_inicio && l.fecha_fin_estimada <= l.fecha_inicio) { marcarInvalido($fin, 'El fin debe ser posterior al inicio.'); ok = false; $first = $first || $fin; }
                else marcarValido($fin);
            });
            if (!ok && $first) $first.trigger('focus');
            return ok;
        }

        // ══════════════════════════════════════════════════════
        // PASO 3 — Insumos por línea
        // ══════════════════════════════════════════════════════
        function insumosPanelHtml(l, idx) {
            // Al EDITAR, los insumos quedan fijos: ya comprometieron stock al crear
            // la orden. Solo lectura (corregirlos = cancelar la orden y recrearla).
            const ro = isEditMode();
            const nIns = l.insumos ? l.insumos.length : 0;
            const rows = (l.insumos || []).map(function (it, j) {
                const accionesTd = ro
                    ? '<td class="text-center text-muted"><i class="ri-lock-line" title="Insumos fijos tras crear la orden"></i></td>'
                    : '<td class="text-center text-nowrap">'
                      + '<button type="button" class="btn btn-sm btn-soft-primary ord-ins-edit me-1" data-l="' + idx + '" data-i="' + j + '" title="Editar"><i class="ri-pencil-line"></i></button>'
                      + '<button type="button" class="btn btn-sm btn-soft-danger ord-ins-del" data-l="' + idx + '" data-i="' + j + '" title="Quitar"><i class="ri-delete-bin-line"></i></button>'
                      + '</td>';
                return '<tr>'
                    + '<td class="ord-ins-c-num">' + (j + 1) + '</td>'
                    + '<td><div class="fw-semibold">' + escHtml(it.nombre) + '</div>'
                        + (it.unidad ? '<small class="text-muted">' + escHtml(it.unidad) + '</small>' : '') + '</td>'
                    + '<td class="text-end fw-semibold">' + parseFloat(it.cantidad).toFixed(2) + '</td>'
                    + accionesTd + '</tr>';
            }).join('');
            const cuerpo = nIns
                ? '<table class="ord-ins-table"><thead><tr>'
                  + '<th class="ord-ins-c-num">#</th><th>Insumo</th>'
                  + '<th class="text-end">Cantidad</th><th class="text-center">' + (ro ? '' : 'Acciones') + '</th>'
                  + '</tr></thead><tbody>' + rows + '</tbody></table>'
                : '<div class="ord-ins-empty"><i class="ri-tools-line"></i>'
                  + '<span>Sin insumos registrados.</span></div>';
            const addBtn = ro
                ? '<span class="badge rounded-pill badge-soft-secondary flex-shrink-0"><i class="ri-lock-line me-1"></i>Insumos fijos</span>'
                : '<button type="button" class="btn btn-sm btn-soft-primary ord-ins-add flex-shrink-0" data-l="' + idx + '"><i class="ri-add-line me-1"></i>Agregar insumo</button>';
            return '<div class="ord-asig-card ord-ins-card" data-idx="' + idx + '">'
                + '<div class="ord-asig-card-head">'
                +   '<span class="ord-asig-num">' + (idx + 1) + '</span>'
                +   '<div class="ord-asig-prod">'
                +     '<div class="ord-asig-prod-name">' + escHtml(l.producto_nombre) + ' <span class="ord-asig-qty">· ' + l.cantidad + ' u a producir</span></div>'
                +     '<div class="ord-asig-chips">' + lineaMetaChips(l)
                +       '<span class="badge rounded-pill badge-soft-info"><i class="ri-tools-line me-1"></i>' + nIns + ' insumo' + (nIns === 1 ? '' : 's') + '</span></div>'
                +   '</div>'
                +   addBtn
                + '</div>'
                + '<div class="ord-ins-card-body">' + cuerpo + '</div>'
                + '</div>';
        }

        function renderInsumosAcc() {
            $('#ord-insumos-acc').html(ordWiz.lineas.map(function (l, idx) {
                return insumosPanelHtml(l, idx);
            }).join(''));
        }

        function abrirInsumoModal(lineIdx, insIdx) {
            ordInsLineIdx = lineIdx;
            ordInsEditIdx = (insIdx == null ? null : insIdx);
            $('#insumoAddModal').find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            const L = ordWiz.lineas[lineIdx];
            if (insIdx != null && L && L.insumos[insIdx]) {
                const it = L.insumos[insIdx];
                $('#insumo-add-edit-idx').val(insIdx);
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

        $(document).on('click', '.ord-ins-add', function () { abrirInsumoModal(parseInt($(this).data('l'), 10), null); });
        $(document).on('click', '.ord-ins-edit', function () { abrirInsumoModal(parseInt($(this).data('l'), 10), parseInt($(this).data('i'), 10)); });
        $(document).on('click', '.ord-ins-del', function () {
            const li = parseInt($(this).data('l'), 10), ii = parseInt($(this).data('i'), 10);
            if (ordWiz.lineas[li]) { ordWiz.lineas[li].insumos.splice(ii, 1); renderInsumosAcc(); }
        });

        $(document).on('click', '#insumo-add-confirm', function () {
            const $sel = $('#insumo-add-select');
            const id = $sel.val();
            const cantidad = parseFloat($('#insumo-add-cantidad').val());
            if (!id) { marcarInvalido($sel, 'Selecciona un insumo.'); return; }
            if (isNaN(cantidad) || cantidad <= 0) { marcarInvalido($('#insumo-add-cantidad'), 'La cantidad debe ser mayor a cero.'); return; }

            const $opt = $sel.find('option:selected');
            const item = {
                id: parseInt(id, 10),
                nombre: $opt.data('nombre') || $opt.text().replace(/\s*\(.*\)\s*$/, ''),
                unidad: $opt.data('unidad') || '',
                cantidad: +cantidad.toFixed(2)
            };
            const L = ordWiz.lineas[ordInsLineIdx];
            if (!L) { $('#insumoAddModal').modal('hide'); return; }
            if (ordInsEditIdx != null) {
                L.insumos[ordInsEditIdx] = item;
            } else {
                const existing = L.insumos.findIndex(x => x.id === item.id);
                if (existing !== -1) L.insumos[existing].cantidad = +(L.insumos[existing].cantidad + item.cantidad).toFixed(2);
                else L.insumos.push(item);
            }
            renderInsumosAcc();
            $('#insumoAddModal').modal('hide');
        });

        $('#insumoAddModal').on('hidden.bs.modal', function () {
            ordInsLineIdx = null; ordInsEditIdx = null;
            $('#insumo-add-edit-idx').val('');
            $('#insumo-add-select').val('').trigger('change');
            $('#insumo-add-cantidad').val('');
            $('#insumoAddModal').find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
        });

        function validateStep3() {
            const bad = ordWiz.lineas.findIndex(l => !l.insumos || !l.insumos.length);
            if (bad !== -1) {
                Swal.fire({ icon: 'warning', title: 'Faltan insumos', text: 'La línea #' + (bad + 1) + ' no tiene insumos. Agrega al menos uno.' });
                return false;
            }
            return true;
        }

        // ══════════════════════════════════════════════════════
        // PASO 4 — Resumen
        // ══════════════════════════════════════════════════════
        function empName(id) {
            const t = $('#ord-empleados-tpl option[value="' + id + '"]').text();
            return t || '—';
        }

        function renderResumen() {
            const multi = ordWiz.lineas.length > 1;
            const pedidoId = ordWiz.pedido ? ordWiz.pedido.id : '—';
            $('#ord-resumen-desc').text(isEditMode()
                ? 'Revisa los cambios antes de guardar.'
                : (multi ? ('Se crearán ' + ordWiz.lineas.length + ' órdenes para el Pedido #' + pedidoId + '.') : 'Revisa la orden antes de confirmar.'));
            $('#ord-notas-scope').text(multi ? '(se aplican a todas las órdenes)' : '');

            const estadoTh = isEditMode() ? '<th class="text-center">Estado</th>' : '';
            const rows = ordWiz.lineas.map(function (l, idx) {
                const estadoTd = isEditMode() ? '<td class="text-center"><span class="badge badge-soft-secondary">' + escHtml(l.estado || 'Pendiente') + '</span></td>' : '';
                return '<tr>'
                    + '<td class="cot-col-num">' + (idx + 1) + '</td>'
                    + '<td><div class="fw-semibold">' + escHtml(l.producto_nombre) + '</div><div class="d-flex flex-wrap gap-1 mt-1">' + lineaMetaChips(l) + '</div></td>'
                    + '<td class="text-center fw-semibold">' + l.cantidad + '</td>'
                    + '<td>' + escHtml(empName(l.empleado_id)) + '</td>'
                    + '<td class="text-center fs-12">' + (l.fecha_inicio || '—') + '<br><span class="text-muted">→ ' + (l.fecha_fin_estimada || '—') + '</span></td>'
                    + '<td class="text-center"><span class="badge badge-soft-info">' + ((l.insumos || []).length) + ' insumo(s)</span></td>'
                    + estadoTd
                    + '</tr>';
            }).join('');

            const html = '<div class="cot-grouped-tablewrap"><table class="cot-grouped-table"><thead><tr>'
                + '<th class="cot-col-num">#</th><th>Producto</th><th class="text-center">Cant.</th>'
                + '<th>Empleado</th><th class="text-center">Cronograma</th><th class="text-center">Insumos</th>' + estadoTh
                + '</tr></thead><tbody>' + rows + '</tbody></table></div>';
            $('#ord-resumen').html(html);
        }

        // ══════════════════════════════════════════════════════
        // Navegación — botones y markers
        // ══════════════════════════════════════════════════════
        $('#btn-ord-next').on('click', function () {
            ordSyncStep(currentStep);
            if (validateStep(currentStep)) ordShowStep(currentStep + 1);
        });
        $('#btn-ord-prev').on('click', function () {
            ordSyncStep(currentStep);
            ordShowStep(currentStep - 1);
        });
        $('#showModal').on('click', '.wiz-step-marker', function () {
            const target = parseInt($(this).data('step'), 10);
            ordSyncStep(currentStep);
            if (target <= currentStep) { ordShowStep(target); return; }
            for (let s = currentStep; s < target; s++) {
                if (!validateStep(s)) { ordShowStep(s); return; }
                ordShowStep(s + 1);
            }
        });

        // ══════════════════════════════════════════════════════
        // Abrir wizard — modo CREAR
        // ══════════════════════════════════════════════════════
        $(document).on('click', '#create-btn', function () {
            ordWiz = { mode: 'create', editId: null, pedido: null, lineas: [] };
            $('#ord-wiz-id-field').val('');
            $('#modalTitle').text('Nueva Orden de Producción');
            $('#ord-edit-locked').attr('hidden', true);
            $('#ord-select-wrap').removeAttr('hidden');
            $('#ord-wiz-submit-label').text('Crear Orden');
            $('#ord-notas-global').val('');
            $('#ord-lineas-chip').addClass('d-none');
            $('#ordenForm').find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            ordShowStep(1);
            $('#showModal').modal('show');
        });

        $('#showModal').on('shown.bs.modal', function () {
            if (ordWiz.mode === 'create') {
                pedordResetFiltros();
                ordCargarPedidos();
            }
        });

        // ══════════════════════════════════════════════════════
        // Abrir wizard — modo EDITAR
        // ══════════════════════════════════════════════════════
        function ordEditSummaryHtml(l, pedidoId) {
            return '<div class="card border-0 shadow-sm">'
                + '<div class="card-body p-3 d-flex align-items-center justify-content-between gap-3 flex-wrap">'
                +   '<div><div class="text-muted fs-11 mb-1">Pedido #' + escHtml(pedidoId) + '</div>'
                +     '<div class="fw-semibold">' + escHtml(l.producto_nombre) + '</div>'
                +     '<div class="d-flex flex-wrap gap-1 mt-1">' + lineaMetaChips(l) + '</div></div>'
                +   '<div class="text-end"><div class="fw-bold fs-4 lh-1">' + l.cantidad + '</div><small class="text-muted">unidades</small></div>'
                + '</div></div>';
        }

        $(document).on('click', '.edit-btn', function () {
            const id = $(this).data('id');
            $.get("{{ route('ordenes.edit', ':id') }}".replace(':id', id), function (data) {
                const det = data.detalle_pedido || {};
                const linea = {
                    detalle_id: data.detalle_pedido_id,
                    producto_id: data.producto_id,
                    producto_nombre: data.nombre_producto || (data.producto ? data.producto.nombre : (data.producto_id ? 'Producto #' + data.producto_id : 'Producto')),
                    cantidad: data.cantidad_solicitada,
                    color: det.color ? det.color.nombre : null,
                    talla: det.talla ? (det.talla.etiqueta || det.talla.nombre) : null,
                    lleva_bordado: !!(det.bordados && det.bordados.length),
                    bordados_count: det.bordados ? det.bordados.length : 0,
                    empleado_id: data.empleado_id || '',
                    fecha_inicio: formatDateForInput(data.fecha_inicio),
                    fecha_fin_estimada: formatDateForInput(data.fecha_fin_estimada),
                    estado: data.estado || 'Pendiente',
                    insumos: (data.insumos || []).map(function (i) {
                        return { id: i.id, nombre: i.nombre || ('Insumo #' + i.id), unidad: i.unidad_medida || '', cantidad: parseFloat(i.pivot && i.pivot.cantidad_estimada) || 0 };
                    })
                };
                ordWiz = {
                    mode: 'edit',
                    editId: data.id,
                    pedido: { id: data.pedido_id, cliente_nombre: data.cliente_nombre || '', cliente_documento: det.documento || '' },
                    lineas: [linea]
                };

                $('#ord-wiz-id-field').val(data.id);
                $('#modalTitle').text('Editar Orden de Producción');
                $('#ord-select-wrap').attr('hidden', true);
                $('#ord-edit-locked').removeAttr('hidden');
                $('#ord-edit-line-summary').html(ordEditSummaryHtml(linea, data.pedido_id || '—'));
                $('#ord-wiz-submit-label').text('Actualizar Orden');

                if (data.creador) ordSetCreador(data.creador.name, data.creador.avatar_url);
                else ordResetCreador();

                $('#ord-notas-global').val(data.notas || '');
                $('#ord-lineas-chip').addClass('d-none');
                $('#ordenForm').find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');

                ordShowStep(1);
                $('#showModal').modal('show');
            });
        });

        // ══════════════════════════════════════════════════════
        // Enviar — crea 1 (store), N (batch) o actualiza (update)
        // ══════════════════════════════════════════════════════
        function ordFinalCheck() {
            if (!ordWiz.lineas.length) {
                Swal.fire({ icon: 'warning', title: 'Sin líneas', text: 'No hay líneas para producir.' });
                ordShowStep(1);
                return false;
            }
            for (let i = 0; i < ordWiz.lineas.length; i++) {
                const l = ordWiz.lineas[i];
                if (!l.empleado_id || !l.fecha_inicio || !l.fecha_fin_estimada || l.fecha_fin_estimada <= l.fecha_inicio) {
                    ordShowStep(2); validateStep2(); return false;
                }
                if (!l.insumos || !l.insumos.length) {
                    ordShowStep(3);
                    Swal.fire({ icon: 'warning', title: 'Faltan insumos', text: 'La línea #' + (i + 1) + ' no tiene insumos.' });
                    return false;
                }
            }
            return true;
        }

        $('#ordenForm').on('submit', function (e) {
            e.preventDefault();
            if (currentStep !== TOTAL_STEPS) return;      // enviar solo desde el resumen
            if (!ordFinalCheck()) return;

            const notas = $('#ord-notas-global').val() || null;
            const $btn = $('#ord-wiz-submit-btn').prop('disabled', true);
            const mapInsumos = (arr) => arr.map(i => ({ id: i.id, cantidad_estimada: i.cantidad }));

            let url, payload;
            if (isEditMode()) {
                const l = ordWiz.lineas[0];
                url = "{{ route('ordenes.update', ':id') }}".replace(':id', ordWiz.editId);
                payload = {
                    _token: '{{ csrf_token() }}', _method: 'PUT',
                    empleado_id: l.empleado_id, fecha_inicio: l.fecha_inicio, fecha_fin_estimada: l.fecha_fin_estimada,
                    estado: l.estado || 'Pendiente', notas: notas, insumos: mapInsumos(l.insumos)
                };
            } else if (ordWiz.lineas.length === 1) {
                const l = ordWiz.lineas[0];
                url = "{{ route('ordenes.store') }}";
                payload = {
                    _token: '{{ csrf_token() }}',
                    detalle_pedido_id: l.detalle_id, empleado_id: l.empleado_id,
                    fecha_inicio: l.fecha_inicio, fecha_fin_estimada: l.fecha_fin_estimada,
                    notas: notas, insumos: mapInsumos(l.insumos)
                };
            } else {
                url = "{{ route('ordenes.batch') }}";
                payload = {
                    _token: '{{ csrf_token() }}',
                    pedido_id: ordWiz.pedido.id,
                    ordenes: ordWiz.lineas.map(l => ({
                        detalle_pedido_id: l.detalle_id, empleado_id: l.empleado_id,
                        fecha_inicio: l.fecha_inicio, fecha_fin_estimada: l.fecha_fin_estimada,
                        notas: notas, insumos: mapInsumos(l.insumos)
                    }))
                };
            }

            $.ajax({
                url: url, method: 'POST', data: payload,
                success: function (resp) {
                    $btn.prop('disabled', false);
                    $('#showModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: '¡Listo!', text: resp.message, timer: 2200, showConfirmButton: false });
                },
                error: function (xhr) {
                    $btn.prop('disabled', false);
                    let msg = 'Ocurrió un error al procesar la solicitud.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).map(v => Array.isArray(v) ? v[0] : v).join('\n');
                        } else if (xhr.responseJSON.message) { msg = xhr.responseJSON.message; }
                    }
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
            });
        });

        // ══════════════════════════════════════════════════════
        // Reset al cerrar el wizard
        // ══════════════════════════════════════════════════════
        $('#showModal').on('hidden.bs.modal', function () {
            ordWiz = { mode: 'create', editId: null, pedido: null, lineas: [] };
            $('#ord-wiz-id-field').val('');
            $('#ordenForm').find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            $('#ord-asignacion-cards').empty();
            $('#ord-insumos-acc').empty();
            $('#ord-resumen').empty();
            $('#ord-edit-line-summary').empty();
            $('#ord-notas-global').val('');
            $('#pedidos-orden-container').empty();
            $('#ord-lineas-chip').addClass('d-none');
            $('#ord-cliente-banner').attr('hidden', true).attr('aria-hidden', 'true');
            $('#ord-creador-banner').attr('hidden', true).attr('aria-hidden', 'true');
            $('#ord-edit-locked').attr('hidden', true);
            $('#ord-select-wrap').removeAttr('hidden');
            $('#modalTitle').text('Nueva Orden de Producción');
            ordResetCreador();
            ordShowStep(1);
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
                    const r = resp.resumen;
                    $('#mo-total').text(r.total);
                    $('#mo-pendientes').text(r.pendientes);
                    $('#mo-en-proceso').text(r.en_proceso);
                    $('#mo-finalizadas').text(r.finalizadas);
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
                { data: 'id', name: 'id', className: 'align-middle text-center', width: '8%' },
                { data: 'pedido_info', name: 'pedido.id', className: 'align-middle text-center', orderable: false, width: '9%' },
                { data: 'producto_info', name: 'producto.nombre', className: 'align-middle', orderable: false, searchable: false, width: '26%' },
                { data: 'cantidad_solicitada', name: 'cantidad_solicitada', className: 'align-middle text-center', width: '10%' },
                {
                    data: null, className: 'align-middle', width: '18%',
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
                    data: 'estado', className: 'align-middle text-center', width: '13%',
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
                        const estado = row.estado;
                        const estadoActivo = ['Pendiente', 'En Proceso'].includes(estado);
                        const esCancelado = estado === 'Cancelado';

                        const sVer = `<button class="btn btn-sm btn-soft-info view-btn" data-id="${data}" title="Ver detalle"><i class="ri-eye-fill"></i></button>`;

                        let items = '';
                        if (estadoActivo) {
                            items += `<li><button type="button" class="dropdown-item act-item act-primary avance-btn" data-id="${data}"><span class="act-ic"><i class="ri-add-circle-line"></i></span>Registrar avance</button></li>`;
                        }
                        if (!esCancelado) {
                            items += `<li><button type="button" class="dropdown-item act-item act-edit edit-btn" data-id="${data}"><span class="act-ic"><i class="ri-pencil-fill"></i></span>Editar</button></li>`;
                        }
                        // Cancelar (Pendiente/En Proceso): reposición de stock condicional + merma.
                        if (estadoActivo) {
                            items += `<li><button type="button" class="dropdown-item act-item act-warning cancelar-btn" data-id="${data}" data-estado="${estado}"><span class="act-ic"><i class="ri-close-circle-line"></i></span>Cancelar orden</button></li>`;
                        }
                        // Eliminar (hard delete) solo aplica a órdenes Pendientes.
                        if (estado === 'Pendiente') {
                            items += `<li><button type="button" class="dropdown-item act-item act-del remove-btn" data-id="${data}"><span class="act-ic"><i class="ri-delete-bin-fill"></i></span>Eliminar</button></li>`;
                        }

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
                { extend: 'copy',  exportOptions: { columns: [0, 1, 2, 3, 4, 5] } },
                { extend: 'csv',   exportOptions: { columns: [0, 1, 2, 3, 4, 5] } },
                { extend: 'excel', exportOptions: { columns: [0, 1, 2, 3, 4, 5] } },
                { extend: 'pdf',   exportOptions: { columns: [0, 1, 2, 3, 4, 5] } },
                { extend: 'print', exportOptions: { columns: [0, 1, 2, 3, 4, 5] } }
            ],
            language: lenguajeData
        });

        $('#filters-collapse-body')
            .on('show.bs.collapse', function () { $('#advanced-filters .navy-filter-header').removeClass('is-collapsed'); })
            .on('hidden.bs.collapse', function () { $('#advanced-filters .navy-filter-header').addClass('is-collapsed'); });

        $('#custom-search-input').on('input', debounce(function () {
            table.search(this.value).draw();
        }, 300));

        $('#advanced-filters .navy-filter-select').on('change', function () {
            table.ajax.reload();
            updateFilterBadge();
        });

        $('#btn-clear-filters').on('click', function () {
            $('#filter-estado').val('');
            $('#filter-fecha-desde').val('');
            $('#filter-fecha-hasta').val('');
            $('#filter-orden').val('recientes');
            $('#advanced-filters .navy-filter-select').trigger('change');
            $('#custom-search-input').val('');
            table.search('').draw();
            updateFilterBadge();
        });

        updateFilterBadge();

        // ══════════════════════════════════════════════════════
        // Ver orden
        // ══════════════════════════════════════════════════════
        $(document).on('click', '.view-btn', function () {
            let id = $(this).data('id');
            viewKanbanOrdenId = id;
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
                var insumos = data.insumos || [];
                $('#view-insumos').empty();
                if (insumos.length === 0) {
                    $('#view-insumos-tablewrap').hide();
                    $('#view-insumos-empty').show();
                } else {
                    $('#view-insumos-tablewrap').show();
                    $('#view-insumos-empty').hide();
                    insumos.forEach(insumo => {
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
                }

                const motivoCancel = (data.estado === 'Cancelado' && data.motivo_cancelacion)
                    ? '<div class="alert alert-danger py-2 px-3 mt-2 mb-0 fs-12"><i class="ri-close-circle-line me-1"></i><b>Motivo de cancelación:</b> ' + escHtml(data.motivo_cancelacion) + '</div>'
                    : '';
                $('#view-notas').html(escHtml(data.notas || 'Sin notas adicionales.') + motivoCancel);
                const formatDateTime = (dateString) => {
                    if (!dateString) return 'N/A';
                    const date = new Date(dateString);
                    return date.toLocaleString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                };
                $('#view-created').text(formatDateTime(data.created_at));

                viewOrdShowStep(1);
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
        // Cancelar orden (reposición de stock condicional / merma textil)
        // ══════════════════════════════════════════════════════
        $(document).on('click', '.cancelar-btn', function () {
            const id = $(this).data('id');
            const estado = String($(this).data('estado'));
            const enProduccion = estado !== 'Pendiente';   // tela ya cortada = merma

            const enviar = function (motivo) {
                $.ajax({
                    url: "{{ route('ordenes.cancelar', ':id') }}".replace(':id', id),
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'PATCH', motivo_cancelacion: motivo || null },
                    success: function (resp) {
                        table.ajax.reload(null, false);
                        if (misOrdenesEmpleadoId) { cargarMisOrdenes(misOrdenesEmpleadoId); }
                        Swal.fire({ icon: 'success', title: 'Orden cancelada', text: resp.message, timer: 2800, showConfirmButton: false });
                    },
                    error: function (xhr) {
                        let msg = 'No se pudo cancelar la orden.';
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.errors) { msg = Object.values(xhr.responseJSON.errors).flat().join('\n'); }
                            else if (xhr.responseJSON.message) { msg = xhr.responseJSON.message; }
                        }
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    }
                });
            };

            if (enProduccion) {
                // Cancelación tardía: la tela ya se cortó → merma. Motivo obligatorio.
                Swal.fire({
                    icon: 'warning',
                    title: 'Cancelar orden en producción',
                    html: 'El material ya está en producción (cortado). Al cancelar <b>no se repone el stock</b> (se registra como merma).<br>Indica el motivo de la cancelación:',
                    input: 'textarea',
                    inputPlaceholder: 'Motivo / justificación de la pérdida del material...',
                    inputAttributes: { maxlength: 500 },
                    showCancelButton: true,
                    confirmButtonText: 'Cancelar orden',
                    cancelButtonText: 'Volver',
                    confirmButtonColor: '#d33',
                    inputValidator: function (value) {
                        if (!value || !value.trim()) return 'El motivo es obligatorio para justificar la merma.';
                    }
                }).then(function (r) {
                    if (r.isConfirmed) enviar(r.value.trim());
                });
            } else {
                // Cancelación temprana (Pendiente): tela sin cortar → se repone el stock.
                Swal.fire({
                    icon: 'question',
                    title: '¿Cancelar la orden?',
                    text: 'La orden aún no inicia producción: se repondrá el stock de los insumos al inventario.',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cancelar',
                    cancelButtonText: 'Volver',
                    confirmButtonColor: '#d33'
                }).then(function (r) {
                    if (r.isConfirmed) enviar(null);
                });
            }
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
            viewOrdShowStep(1);
            kanbanReset();
            viewKanbanOrdenId = null;
        });

        // ══════════════════════════════════════════════════════
        // Wizard navegación — viewModal (read-only, 4 pasos)
        // ══════════════════════════════════════════════════════
        (function () {
            var TOTAL = 4;
            var currentStep = 1;

            window.viewOrdShowStep = function (step) {
                currentStep = step;
                $('#viewModal .wiz-step-content').removeClass('is-active');
                $('#viewModal .wiz-step-content[data-step="' + step + '"]').addClass('is-active');
                $('#viewModal .wiz-step-marker').removeClass('is-active is-complete');
                $('#viewModal .wiz-step-marker').each(function () {
                    var s = parseInt($(this).data('step'));
                    if (s < step) $(this).addClass('is-complete');
                    else if (s === step) $(this).addClass('is-active');
                });
                for (var i = 1; i < TOTAL; i++) {
                    $('#viewModal .wiz-step-line-fill[data-line="' + i + '"]')
                        .css('width', i < step ? '100%' : '0%');
                }
                $('#btn-view-ord-prev').toggle(step > 1);
                $('#btn-view-ord-next').toggle(step < TOTAL);
                $('#btn-view-ord-close').toggle(step === TOTAL);

                // Carga lazy del Kanban al llegar al paso 4
                if (step === 4 && viewKanbanOrdenId) {
                    kanbanLoad(viewKanbanOrdenId);
                }
            };

            $(document).on('click', '#btn-view-ord-next', function () {
                if (currentStep < TOTAL) viewOrdShowStep(currentStep + 1);
            });
            $(document).on('click', '#btn-view-ord-prev', function () {
                if (currentStep > 1) viewOrdShowStep(currentStep - 1);
            });
            $('#viewModal').on('click', '.wiz-step-marker', function () {
                viewOrdShowStep(parseInt($(this).data('step')));
            });
        }());

        // ══════════════════════════════════════════════════════
        // KANBAN — Tablero por sub-órdenes
        // ══════════════════════════════════════════════════════
        (function () {
            var ESTADOS = ['Pendiente', 'En Proceso', 'Finalizado', 'Cancelado'];
            var COL_CSS  = { 'Pendiente': 'pendiente', 'En Proceso': 'en-proceso', 'Finalizado': 'finalizado', 'Cancelado': 'cancelado' };
            var sortables = [];
            var kanbanLoaded = false; // evita recargas si ya está montado

            // Convierte estado a slug CSS
            function colSlug(estado) { return COL_CSS[estado] || 'pendiente'; }

            // Crea el HTML de un ticket
            function ticketHtml(sub) {
                var empBadges = (sub.empleados || []).map(function (e) {
                    var nombre = (e.persona && e.persona.nombre_completo)
                        ? e.persona.nombre_completo.split(' ')[0]
                        : ('Emp. ' + e.id);
                    return '<span class="kanban-ticket-emp">' + escHtml(nombre) + '</span>';
                }).join('');
                var cantHtml = sub.cantidad_asignada
                    ? '<span class="kanban-ticket-cant"><i class="ri-stack-line me-1"></i>' + sub.cantidad_asignada + ' uds.</span>'
                    : '';
                return '<div class="kanban-ticket kanban-ticket--' + colSlug(sub.estado) + '" data-suborden-id="' + sub.id + '">'
                    + '<div class="kanban-ticket-nombre">' + escHtml(sub.nombre) + '</div>'
                    + '<div class="kanban-ticket-meta">' + cantHtml + empBadges + '</div>'
                    + '</div>';
            }

            // Renderiza todas las columnas con sus tickets
            function renderBoard(subordenes) {
                var $board = $('#kanban-board').empty();

                ESTADOS.forEach(function (estado) {
                    var slug = colSlug(estado);
                    var tickets = subordenes.filter(function (s) { return s.estado === estado; });
                    var countBadge = tickets.length > 0 ? tickets.length : '&mdash;';

                    var $col = $('<div class="kanban-col kanban-col--' + slug + '" data-estado="' + escHtml(estado) + '">'
                        + '<div class="kanban-col-header">'
                        + '<span class="kanban-col-title">' + escHtml(estado) + '</span>'
                        + '<span class="kanban-col-count" id="kanban-count-' + slug + '">' + countBadge + '</span>'
                        + '</div>'
                        + '<div class="kanban-col-body" id="kanban-col-' + slug + '" data-estado="' + escHtml(estado) + '"></div>'
                        + '</div>');

                    tickets.forEach(function (sub) {
                        $col.find('.kanban-col-body').append(ticketHtml(sub));
                    });

                    $board.append($col);
                });

                // Inicializa SortableJS en cada columna
                destroySortables();
                ESTADOS.forEach(function (estado) {
                    var el = document.getElementById('kanban-col-' + colSlug(estado));
                    if (!el) return;
                    sortables.push(Sortable.create(el, {
                        group: 'kanban-op',
                        animation: 150,
                        ghostClass: 'kanban-ghost',
                        dragClass: 'kanban-drag',
                        onStart: function () {
                            document.querySelectorAll('.kanban-col-body').forEach(function (c) {
                                c.classList.add('sortable-over');
                            });
                        },
                        onEnd: function (evt) {
                            document.querySelectorAll('.kanban-col-body').forEach(function (c) {
                                c.classList.remove('sortable-over');
                            });
                            var nuevoEstado = evt.to.dataset.estado;
                            var viejoEstado = evt.from.dataset.estado;
                            if (nuevoEstado === viejoEstado) return;

                            var subId = evt.item.dataset.subordenId;
                            var $ticket = $(evt.item);

                            $.ajax({
                                url: '{{ url("ordenes") }}/' + viewKanbanOrdenId + '/subordenes/' + subId + '/estado',
                                method: 'PATCH',
                                data: { estado: nuevoEstado, _token: '{{ csrf_token() }}' },
                                success: function (res) {
                                    // Actualiza clase del ticket
                                    $ticket.removeClass(function (i, cls) {
                                        return (cls.match(/kanban-ticket--\S+/g) || []).join(' ');
                                    }).addClass('kanban-ticket--' + colSlug(nuevoEstado));

                                    // Actualiza contadores de columnas
                                    updateColCounts();

                                    // Actualiza badge estado de la OP en el header del modal
                                    if (res.op_estado) {
                                        updateViewEstadoBadge(res.op_estado);
                                        // Refresca la DataTable para reflejar el nuevo estado
                                        if (table) table.ajax.reload(null, false);
                                    }
                                },
                                error: function () {
                                    // Revierte moviendo el ticket de vuelta a la columna original
                                    var $fromCol = $('#kanban-col-' + colSlug(viejoEstado));
                                    if (evt.oldIndex === 0) {
                                        $fromCol.prepend($ticket);
                                    } else {
                                        $fromCol.append($ticket);
                                    }
                                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar el estado.', timer: 2500, showConfirmButton: false });
                                }
                            });
                        }
                    }));
                });
            }

            // Actualiza los contadores de cada columna
            function updateColCounts() {
                ESTADOS.forEach(function (estado) {
                    var slug = colSlug(estado);
                    var count = $('#kanban-col-' + slug + ' .kanban-ticket').length;
                    $('#kanban-count-' + slug).text(count > 0 ? count : '—');
                });
            }

            // Destruye instancias previas de SortableJS
            function destroySortables() {
                sortables.forEach(function (s) { try { s.destroy(); } catch (e) {} });
                sortables = [];
            }

            // Actualiza el badge de estado de la OP en el header del viewModal
            function updateViewEstadoBadge(estado) {
                var estadoClases = {
                    'Pendiente':  'status-pendiente badge-soft-warning',
                    'En Proceso': 'status-procesando badge-soft-info',
                    'Finalizado': 'status-finalizado badge-soft-success',
                    'Cancelado':  'status-cancelado badge-soft-danger'
                };
                $('#view-estado').html(
                    '<span class="badge badge-status ' + (estadoClases[estado] || 'badge-soft-secondary') + ' rounded-pill">'
                    + '<i class="' + iconEstadoOrden(estado) + ' me-1"></i>' + escHtml(estado) + '</span>'
                );
            }

            // Carga el Kanban desde el backend (llamado al activar paso 4)
            window.kanbanLoad = function (ordenId) {
                if (kanbanLoaded) return; // ya montado para esta OP
                $('#kanban-loading').show();
                $('#kanban-empty').hide();
                $('#kanban-board').hide();

                $.get('{{ url("ordenes") }}/' + ordenId + '/subordenes', function (res) {
                    var subs = res.subordenes || [];
                    $('#kanban-loading').hide();
                    if (!subs.length) {
                        $('#kanban-empty').show();
                    } else {
                        renderBoard(subs);
                        $('#kanban-board').show();
                    }
                    kanbanLoaded = true;
                }).fail(function () {
                    $('#kanban-loading').hide();
                    $('#kanban-empty').show();
                });
            };

            // Limpia el Kanban al cerrar el modal
            window.kanbanReset = function () {
                destroySortables();
                $('#kanban-board').empty().hide();
                $('#kanban-loading').hide();
                $('#kanban-empty').hide();
                kanbanLoaded = false;
            };
        }());

    });
</script>
