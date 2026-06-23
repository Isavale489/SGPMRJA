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

        // Insumos de una orden a partir del consumo POR UNIDAD × unidades asignadas.
        // `por_unidad` se conserva para reescalar sin deriva de redondeo.
        function insumosDesdeUnitarios(unitarios, unidades) {
            return (unitarios || []).map(function (i) {
                const pu = parseFloat(i.cantidad_unitaria) || 0;
                return { id: i.id, nombre: i.nombre, unidad: i.unidad || '', por_unidad: pu, cantidad: +(pu * unidades).toFixed(2) };
            });
        }

        // Cambia las unidades de una orden reescalando sus insumos en proporción
        // (preserva agregados/ediciones manuales del paso 3 vía su por_unidad).
        // En edición NO se tocan los insumos: ya comprometieron stock al crear.
        function setCantidadOrden(l, nueva) {
            nueva = Math.max(1, parseInt(nueva, 10) || 1);
            const previa = parseInt(l.cantidad, 10) || 0;
            if (nueva === previa) return;
            if (!isEditMode()) {
                (l.insumos || []).forEach(function (i) {
                    if (!(i.por_unidad > 0) && previa > 0) i.por_unidad = i.cantidad / previa;
                    i.cantidad = +((i.por_unidad || 0) * nueva).toFixed(2);
                });
            }
            l.cantidad = nueva;
        }

        // Entradas del wizard que reparten la MISMA línea del pedido (cada entrada = una orden)
        function grupoLinea(detalleId) {
            return ordWiz.lineas.filter(function (x) { return x.detalle_id === detalleId; });
        }
        function unidadesUsadasGrupo(detalleId) {
            return grupoLinea(detalleId).reduce(function (s, x) { return s + (parseInt(x.cantidad, 10) || 0); }, 0);
        }

        // Construye una "línea" del wizard (= una orden) desde una línea del pedido
        // disponible. Arranca con todas las unidades aún sin asignar de la línea;
        // "Dividir" la reparte en más órdenes.
        function makeLineaDesde(pedido, l) {
            const pendiente = (l.cantidad_pendiente != null) ? l.cantidad_pendiente : l.cantidad;
            return {
                detalle_id: l.detalle_id,
                producto_id: l.producto_id,
                producto_nombre: l.producto_nombre,
                linea_cantidad: l.cantidad,
                cantidad_pendiente: pendiente,
                cantidad: pendiente,
                color: l.color,
                talla: l.talla,
                lleva_bordado: l.lleva_bordado,
                bordados_count: l.bordados_count,
                empleado_ids: [],
                fecha_inicio: hoyISO(),
                fecha_fin_estimada: finEstimadoDefault(pedido.fecha_entrega, hoyISO()),
                estado: 'Pendiente',
                insumos_unitarios: Array.isArray(l.insumos_default) ? l.insumos_default : [],
                insumos: insumosDesdeUnitarios(l.insumos_default, pendiente)
            };
        }

        // Clona una entrada como nueva "parte" de la misma línea (otra orden)
        function makeParteDesde(l, unidades) {
            return {
                detalle_id: l.detalle_id,
                producto_id: l.producto_id,
                producto_nombre: l.producto_nombre,
                linea_cantidad: l.linea_cantidad,
                cantidad_pendiente: l.cantidad_pendiente,
                cantidad: unidades,
                color: l.color,
                talla: l.talla,
                lleva_bordado: l.lleva_bordado,
                bordados_count: l.bordados_count,
                empleado_ids: [],
                fecha_inicio: l.fecha_inicio,
                fecha_fin_estimada: l.fecha_fin_estimada,
                estado: 'Pendiente',
                insumos_unitarios: l.insumos_unitarios,
                insumos: insumosDesdeUnitarios(l.insumos_unitarios, unidades)
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
                    const pendiente = (l.cantidad_pendiente != null) ? l.cantidad_pendiente : l.cantidad;
                    const asignada  = l.cantidad_asignada || 0;
                    // Línea con TODAS sus unidades en órdenes activas → solo informativa
                    if (!(pendiente > 0)) {
                        const nOrd = l.ordenes_activas || 1;
                        return `
                            <div class="list-group-item d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                <div>
                                    <div class="fw-semibold text-muted">${escHtml(l.producto_nombre)}${bordadoBadge}</div>
                                    <small class="text-muted">${escHtml(meta)}</small>
                                </div>
                                <span class="badge bg-secondary"><i class="ri-check-line"></i> ${nOrd > 1 ? nOrd + ' órdenes activas' : 'Orden activa'}</span>
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
                    // Reparto previo parcial: se informa cuánto queda por asignar
                    const parcialBadge = asignada > 0
                        ? `<span class="badge bg-warning-subtle text-warning ms-1"><i class="ri-scales-3-line"></i> ${pendiente} de ${l.cantidad} u por asignar</span>`
                        : '';
                    // Líneas con unidades por asignar → checkbox seleccionable
                    return `
                        <label class="list-group-item d-flex align-items-center gap-2 flex-wrap" style="cursor: pointer;">
                            <input type="checkbox" class="form-check-input linea-check"
                                data-pedido-id="${p.id}" data-detalle-id="${l.detalle_id}">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">${escHtml(l.producto_nombre)}${bordadoBadge}${parcialBadge}</div>
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
                                ${p.lineas_pendientes} de ${p.total_lineas} por asignar
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

            // Si la selección no cambió, conservar lo ya capturado (asignación/insumos/
            // divisiones). Con líneas divididas hay detalle_id repetidos → comparar únicos.
            const prev = Array.from(new Set(ordWiz.lineas.map(l => l.detalle_id))).sort().join(',');
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

        // Pills-checkbox de empleados: para una card (idx entero) o la barra global (idx null)
        function empleadoCheckboxesHtml(idx, selectedIds) {
            selectedIds = (selectedIds || []).map(String);
            var nameAttr = idx !== null ? 'data-idx="' + idx + '"' : '';
            var chkClass = idx !== null ? 'ord-asig-emp-chk' : 'ord-default-emp-chk';
            var html = '';
            $('#ord-empleados-tpl option').each(function () {
                var val = $(this).val();
                if (!val) return;
                var checked = selectedIds.indexOf(val) !== -1 ? ' checked' : '';
                html += '<label class="ord-emp-check-item">'
                    + '<input type="checkbox" class="' + chkClass + '" value="' + val + '" ' + nameAttr + checked + '>'
                    + '<span>' + escHtml($(this).text()) + '</span>'
                    + '</label>';
            });
            return html;
        }

        function asignacionCardHtml(l, idx) {
            const meta      = lineaMetaChips(l);
            const edit      = isEditMode();
            const fechaCol  = edit ? 'col-md-3' : 'col-md-5';
            // 'Cancelado' no se ofrece aquí: la cancelación va por su propia acción
            // (define reposición de stock condicional y exige motivo de merma).
            const estadoBlock = edit
                ? '<div class="col-md-4"><label class="form-label form-label-sm required mb-1" for="ord-asig-estado-' + idx + '"><i class="ri-flag-line me-1"></i>Estado</label>'
                  + '<select class="form-select form-select-sm ord-asig-estado" id="ord-asig-estado-' + idx + '" data-idx="' + idx + '">'
                  + '<option value="Pendiente">Pendiente</option><option value="En Proceso">En Proceso</option>'
                  + '<option value="Finalizado">Finalizado</option></select></div>'
                : '';

            // Reparto: una línea puede dividirse en varias órdenes (partes)
            const grupo = grupoLinea(l.detalle_id);
            const gn = grupo.length, gi = grupo.indexOf(l);
            const parteChip = gn > 1
                ? '<span class="badge badge-soft-info ord-asig-parte"><i class="ri-git-branch-line me-1"></i>Parte ' + (gi + 1) + ' de ' + gn + '</span>'
                : '';
            const splitBtns = !edit
                ? '<div class="ord-asig-split-btns">'
                  + '<button type="button" class="btn btn-sm btn-soft-primary ord-asig-split" data-idx="' + idx + '" title="Dividir estas unidades en otra orden (otro empleado)"><i class="ri-scissors-line"></i><span class="d-none d-lg-inline ms-1">Dividir</span></button>'
                  + (gn > 1 ? '<button type="button" class="btn btn-sm btn-soft-danger ord-asig-unsplit" data-idx="' + idx + '" title="Quitar esta parte (devuelve sus unidades)"><i class="ri-close-line"></i></button>' : '')
                  + '</div>'
                : '';

            // Unidades de ESTA orden. En edición solo es editable con la orden Pendiente.
            const cantMax = edit ? (l.cantidad_maxima || l.cantidad) : (l.cantidad_pendiente || l.cantidad);
            const cantDisabled = (edit && l.estado !== 'Pendiente')
                ? ' disabled title="Solo editable con la orden Pendiente"' : '';
            const cantBlock = '<div class="col-6 col-md-2"><label class="form-label form-label-sm required mb-1" for="ord-asig-cant-' + idx + '"><i class="ri-stack-line me-1"></i>Unidades</label>'
                + '<input type="number" inputmode="numeric" class="form-control form-control-sm ord-asig-cant" id="ord-asig-cant-' + idx + '" data-idx="' + idx + '" min="1" max="' + cantMax + '" value="' + l.cantidad + '"' + cantDisabled + '>'
                + '</div>';

            return '<div class="ord-asig-card" data-idx="' + idx + '">'
                + '<div class="ord-asig-card-head">'
                +   '<span class="ord-asig-num">' + (idx + 1) + '</span>'
                +   '<div class="ord-asig-prod">'
                +     '<div class="ord-asig-prod-name">' + escHtml(l.producto_nombre) + ' <span class="ord-asig-qty">· ' + l.cantidad + ' u</span></div>'
                +     '<div class="ord-asig-chips">' + meta + parteChip + '</div>'
                +   '</div>'
                +   '<span class="ord-asig-resto" id="ord-asig-resto-' + idx + '"></span>'
                +   '<span class="ord-asig-dur" id="ord-asig-dur-' + idx + '"></span>'
                +   splitBtns
                + '</div>'
                + '<div class="ord-asig-card-body"><div class="row g-2">'
                +   '<div class="col-12">'
                +     '<label class="form-label form-label-sm required mb-1"><i class="ri-team-line me-1"></i>Empleados asignados</label>'
                +     '<div class="ord-asig-emp-checks" id="ord-asig-emp-chks-' + idx + '">'
                +       empleadoCheckboxesHtml(idx, l.empleado_ids || [])
                +     '</div>'
                +     '<div class="ord-emp-feedback">Selecciona al menos un empleado.</div>'
                +   '</div>'
                +   cantBlock
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
                ? 'Asigna empleados, unidades y fechas a cada orden. Usa "Aplicar a todas" para ir más rápido, o "Dividir" para repartir una línea entre varios empleados.'
                : 'Define quién produce la orden, cuántas unidades y sus fechas. Con "Dividir" puedes repartir la línea entre varios empleados.');

            if (multi) {
                if (!$('#ord-default-empleado-wrap .ord-default-emp-chk').length) {
                    $('#ord-default-empleado-wrap').html(empleadoCheckboxesHtml(null, []));
                }
                $('#ord-default-inicio').val(hoyISO());
                $('#ord-default-fin').val(ordWiz.lineas[0].fecha_fin_estimada || '');
            }

            $('#ord-asignacion-cards').html(ordWiz.lineas.map(function (l, idx) {
                return asignacionCardHtml(l, idx);
            }).join(''));

            ordWiz.lineas.forEach(function (l, idx) {
                if (isEditMode()) $('#ord-asig-estado-' + idx).val(l.estado || 'Pendiente');
                actualizarDur($('#ord-asig-dur-' + idx), l.fecha_inicio, l.fecha_fin_estimada);
            });
            actualizarChipsReparto();
        }

        // Chip de cobertura por card: ¿la línea quedará completa tras guardar o
        // quedarán unidades sin asignar (asignación parcial diferida, permitida)?
        function actualizarChipsReparto() {
            if (isEditMode()) { $('.ord-asig-resto').empty(); return; }
            ordWiz.lineas.forEach(function (l, idx) {
                const $chip = $('#ord-asig-resto-' + idx);
                if (!$chip.length) return;
                const resto = (l.cantidad_pendiente || 0) - unidadesUsadasGrupo(l.detalle_id);
                if (resto > 0) {
                    $chip.html('<i class="ri-error-warning-line me-1"></i>' + resto + ' u sin asignar')
                        .attr('title', 'Podrás asignarlas luego creando otra orden para esta línea')
                        .addClass('is-warn').removeClass('is-ok');
                } else {
                    $chip.html('<i class="ri-checkbox-circle-line me-1"></i>Línea completa')
                        .attr('title', 'Todas las unidades de la línea quedarán asignadas')
                        .addClass('is-ok').removeClass('is-warn');
                }
            });
        }

        // Unidades de una orden: tope vivo = pendiente de la línea menos lo que
        // usan las otras partes (nunca se puede sobre-asignar desde la UI).
        $(document).on('input', '.ord-asig-cant', function () {
            const idx = parseInt($(this).data('idx'), 10);
            const l = ordWiz.lineas[idx];
            if (!l) return;
            let v = parseInt(this.value, 10);
            if (isNaN(v)) return; // permitir seguir escribiendo
            if (v < 1) v = 1;
            if (isEditMode()) {
                const tope = l.cantidad_maxima || v;
                if (v > tope) v = tope;
            } else {
                const otras = unidadesUsadasGrupo(l.detalle_id) - (parseInt(l.cantidad, 10) || 0);
                const tope = Math.max(1, (l.cantidad_pendiente || 0) - otras);
                if (v > tope) v = tope;
            }
            if (String(v) !== this.value) this.value = v;
            setCantidadOrden(l, v);
            $(this).closest('.ord-asig-card').find('.ord-asig-qty').first().text('· ' + v + ' u');
            actualizarChipsReparto();
        });

        // Si el campo queda vacío al salir, restaurar las unidades del estado
        $(document).on('blur', '.ord-asig-cant', function () {
            const idx = parseInt($(this).data('idx'), 10);
            const l = ordWiz.lineas[idx];
            if (l && (!this.value || parseInt(this.value, 10) < 1)) this.value = l.cantidad;
        });

        // Dividir: crea otra orden (parte) para la misma línea. Si quedan unidades
        // libres se las lleva; si no, parte las de esta orden a la mitad.
        $(document).on('click', '.ord-asig-split', function () {
            syncAsignacion();
            const idx = parseInt($(this).data('idx'), 10);
            const l = ordWiz.lineas[idx];
            if (!l) return;
            const libre = (l.cantidad_pendiente || 0) - unidadesUsadasGrupo(l.detalle_id);
            let unidades;
            if (libre >= 1) {
                unidades = libre;
            } else if ((parseInt(l.cantidad, 10) || 0) >= 2) {
                unidades = Math.floor(l.cantidad / 2);
                setCantidadOrden(l, l.cantidad - unidades);
            } else {
                Swal.fire({ icon: 'info', title: 'Nada que dividir', text: 'Esta parte tiene 1 unidad y la línea no tiene unidades libres.', toast: true, position: 'top-end', showConfirmButton: false, timer: 2200 });
                return;
            }
            ordWiz.lineas.splice(idx + 1, 0, makeParteDesde(l, unidades));
            renderAsignacion();
        });

        // Quitar una parte: sus unidades vuelven a quedar libres (chip ámbar)
        $(document).on('click', '.ord-asig-unsplit', function () {
            syncAsignacion();
            const idx = parseInt($(this).data('idx'), 10);
            const l = ordWiz.lineas[idx];
            if (!l || grupoLinea(l.detalle_id).length < 2) return;
            ordWiz.lineas.splice(idx, 1);
            renderAsignacion();
        });

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
                l.empleado_ids = [];
                $(this).find('.ord-asig-emp-chk:checked').each(function () {
                    l.empleado_ids.push($(this).val());
                });
                const v = parseInt($(this).find('.ord-asig-cant').val(), 10);
                if (!isNaN(v) && v >= 1) setCantidadOrden(l, v);
                l.fecha_inicio = $(this).find('.ord-asig-inicio').val() || '';
                l.fecha_fin_estimada = $(this).find('.ord-asig-fin').val() || '';
                const $est = $(this).find('.ord-asig-estado');
                if ($est.length) l.estado = $est.val();
            });
        }

        $(document).on('click', '#ord-apply-defaults', function () {
            const selEmpIds = [];
            $('#ord-default-empleado-wrap .ord-default-emp-chk:checked').each(function () {
                selEmpIds.push($(this).val());
            });
            const ini = $('#ord-default-inicio').val();
            const fin = $('#ord-default-fin').val();
            $('#ord-asignacion-cards .ord-asig-card').each(function () {
                const idx = parseInt($(this).data('idx'), 10);
                if (selEmpIds.length) {
                    $(this).find('.ord-asig-emp-chk').each(function () {
                        $(this).prop('checked', selEmpIds.indexOf($(this).val()) !== -1);
                    });
                }
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
                const $chks = $('#ord-asig-emp-chks-' + idx);
                const $ini  = $('#ord-asig-inicio-' + idx);
                const $fin  = $('#ord-asig-fin-' + idx);
                const $cant = $('#ord-asig-cant-' + idx);
                if (!l.empleado_ids || !l.empleado_ids.length) {
                    $chks.addClass('is-invalid-group');
                    ok = false; $first = $first || $chks;
                } else {
                    $chks.removeClass('is-invalid-group');
                }
                if (!l.cantidad || l.cantidad < 1) { marcarInvalido($cant, 'Al menos 1 unidad.'); ok = false; $first = $first || $cant; } else marcarValido($cant);
                if (!l.fecha_inicio) { marcarInvalido($ini, 'Fecha de inicio requerida.'); ok = false; $first = $first || $ini; } else marcarValido($ini);
                if (!l.fecha_fin_estimada) { marcarInvalido($fin, 'Fecha fin requerida.'); ok = false; $first = $first || $fin; }
                else if (l.fecha_inicio && l.fecha_fin_estimada <= l.fecha_inicio) { marcarInvalido($fin, 'El fin debe ser posterior al inicio.'); ok = false; $first = $first || $fin; }
                else marcarValido($fin);
            });

            // Sobre-asignación por línea (los inputs se capan en vivo; red de seguridad)
            if (ok && !isEditMode()) {
                const vistos = {};
                ordWiz.lineas.forEach(function (l) {
                    if (vistos[l.detalle_id]) return;
                    vistos[l.detalle_id] = true;
                    if (unidadesUsadasGrupo(l.detalle_id) > (l.cantidad_pendiente || 0)) {
                        ok = false;
                        Swal.fire({ icon: 'warning', title: 'Reparto excedido', text: '"' + l.producto_nombre + '" tiene asignadas más unidades de las disponibles. Ajusta las partes.' });
                    }
                });
            }

            // Asignación parcial diferida: permitida, solo se informa una vez
            if (ok && !isEditMode()) {
                const restos = [], vistos2 = {};
                ordWiz.lineas.forEach(function (l) {
                    if (vistos2[l.detalle_id]) return;
                    vistos2[l.detalle_id] = true;
                    const resto = (l.cantidad_pendiente || 0) - unidadesUsadasGrupo(l.detalle_id);
                    if (resto > 0) restos.push(l.producto_nombre + ': ' + resto + ' u');
                });
                if (restos.length) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Quedarán unidades sin asignar',
                        text: restos.join(' · ') + ' — podrás asignarlas luego creando más órdenes.',
                        toast: true, position: 'top-end', showConfirmButton: false, timer: 3200
                    });
                }
            }

            if (!ok && $first) $first[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
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
            const grupo = grupoLinea(l.detalle_id);
            const parteChip = grupo.length > 1
                ? '<span class="badge badge-soft-info ord-asig-parte"><i class="ri-git-branch-line me-1"></i>Parte ' + (grupo.indexOf(l) + 1) + ' de ' + grupo.length
                  + ' · ' + escHtml(empNames(l.empleado_ids)) + '</span>'
                : '';
            return '<div class="ord-asig-card ord-ins-card" data-idx="' + idx + '">'
                + '<div class="ord-asig-card-head">'
                +   '<span class="ord-asig-num">' + (idx + 1) + '</span>'
                +   '<div class="ord-asig-prod">'
                +     '<div class="ord-asig-prod-name">' + escHtml(l.producto_nombre) + ' <span class="ord-asig-qty">· ' + l.cantidad + ' u a producir</span></div>'
                +     '<div class="ord-asig-chips">' + lineaMetaChips(l) + parteChip
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
            const L = ordWiz.lineas[ordInsLineIdx];
            if (!L) { $('#insumoAddModal').modal('hide'); return; }
            const unidadesOrden = parseInt(L.cantidad, 10) || 1;
            const item = {
                id: parseInt(id, 10),
                nombre: $opt.data('nombre') || $opt.text().replace(/\s*\(.*\)\s*$/, ''),
                unidad: $opt.data('unidad') || '',
                // por_unidad permite reescalar el insumo si luego cambian las
                // unidades de la orden (división de la línea entre empleados)
                por_unidad: cantidad / unidadesOrden,
                cantidad: +cantidad.toFixed(2)
            };
            if (ordInsEditIdx != null) {
                L.insumos[ordInsEditIdx] = item;
            } else {
                const existing = L.insumos.findIndex(x => x.id === item.id);
                if (existing !== -1) {
                    const tot = +(L.insumos[existing].cantidad + item.cantidad).toFixed(2);
                    L.insumos[existing].cantidad = tot;
                    L.insumos[existing].por_unidad = tot / unidadesOrden;
                } else {
                    L.insumos.push(item);
                }
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
        function empNames(ids) {
            if (!ids || !ids.length) return '—';
            return ids.map(function (id) { return empName(id); }).join(', ');
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
                const grupo = grupoLinea(l.detalle_id);
                const parteChip = grupo.length > 1
                    ? '<span class="badge badge-soft-info"><i class="ri-git-branch-line me-1"></i>Parte ' + (grupo.indexOf(l) + 1) + ' de ' + grupo.length + '</span>'
                    : '';
                return '<tr>'
                    + '<td class="cot-col-num">' + (idx + 1) + '</td>'
                    + '<td><div class="fw-semibold">' + escHtml(l.producto_nombre) + '</div><div class="d-flex flex-wrap gap-1 mt-1">' + lineaMetaChips(l) + parteChip + '</div></td>'
                    + '<td class="text-center fw-semibold">' + l.cantidad + '</td>'
                    + '<td>' + escHtml(empNames(l.empleado_ids)) + '</td>'
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
                    linea_cantidad: data.linea_cantidad || data.cantidad_solicitada,
                    // Tope al editar: sus unidades + las que la línea tiene sin asignar
                    cantidad_maxima: data.cantidad_maxima || data.cantidad_solicitada,
                    color: det.color ? det.color.nombre : null,
                    talla: det.talla ? (det.talla.etiqueta || det.talla.nombre) : null,
                    lleva_bordado: !!(det.bordados && det.bordados.length),
                    bordados_count: det.bordados ? det.bordados.length : 0,
                    empleado_ids: (data.empleados_asignados || []).map(function (e) { return String(e.id); }),
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
                if (!l.empleado_ids || !l.empleado_ids.length || !l.cantidad || l.cantidad < 1
                    || !l.fecha_inicio || !l.fecha_fin_estimada || l.fecha_fin_estimada <= l.fecha_inicio) {
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
                    empleados: l.empleado_ids, cantidad: l.cantidad,
                    fecha_inicio: l.fecha_inicio, fecha_fin_estimada: l.fecha_fin_estimada,
                    estado: l.estado || 'Pendiente', notas: notas, insumos: mapInsumos(l.insumos)
                };
            } else if (ordWiz.lineas.length === 1) {
                const l = ordWiz.lineas[0];
                url = "{{ route('ordenes.store') }}";
                payload = {
                    _token: '{{ csrf_token() }}',
                    detalle_pedido_id: l.detalle_id, empleados: l.empleado_ids, cantidad: l.cantidad,
                    fecha_inicio: l.fecha_inicio, fecha_fin_estimada: l.fecha_fin_estimada,
                    notas: notas, insumos: mapInsumos(l.insumos)
                };
            } else {
                url = "{{ route('ordenes.batch') }}";
                payload = {
                    _token: '{{ csrf_token() }}',
                    pedido_id: ordWiz.pedido.id,
                    ordenes: ordWiz.lineas.map(l => ({
                        detalle_pedido_id: l.detalle_id, empleados: l.empleado_ids, cantidad: l.cantidad,
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
            // "Aplicar a todas" siempre reabre colapsada (no estorba a la vista)
            $('#ord-apply-collapse').removeClass('show');
            $('#ord-apply-bar .ord-apply-head').attr('aria-expanded', 'false');
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

                // Nombre legible: cubre líneas dinámicas (producto_id null) vía accessor
                $('#view-producto').text(data.nombre_producto || (data.producto ? data.producto.nombre : 'Producto'));
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
                if (data.fecha_fin_real) {
                    $('#view-fecha-fin-real').text(formatDate(data.fecha_fin_real)).removeClass('fst-italic text-muted');
                } else {
                    $('#view-fecha-fin-real').text('Aún en curso').addClass('fst-italic text-muted');
                }
                $('#view-estado').html(`<span class="badge badge-status ${estadoClases[data.estado] || 'badge-soft-secondary'} rounded-pill"><i class="${iconEstadoOrden(data.estado)} me-1"></i>${data.estado}</span>`);
                $('#view-creado-por').text(data.creado_por ? data.creado_por.name : 'Sin especificar');

                // Equipo completo (multi-empleado); fallback al responsable legacy
                const empNoms = (data.empleados_asignados && data.empleados_asignados.length)
                    ? data.empleados_asignados.map(e => (e.persona && e.persona.nombre_completo) || ('Empleado #' + e.id))
                    : (data.empleado && data.empleado.persona ? [data.empleado.persona.nombre_completo] : []);
                $('#view-empleado').text(empNoms.length ? empNoms.join(', ') : 'Sin asignar');
                $('#view-empleado-label').text(empNoms.length > 1 ? 'Empleados (' + empNoms.length + ')' : 'Empleado');

                // Subtítulo del header: pedido + unidades + variante (color/talla)
                const detSub = data.detalle_pedido || {};
                const subInfo = [data.pedido_id ? 'Pedido #' + data.pedido_id : 'Orden manual',
                                 data.cantidad_solicitada + (data.cantidad_solicitada === 1 ? ' unidad' : ' unidades')];
                if (detSub.color && detSub.color.nombre) subInfo.push(detSub.color.nombre);
                if (detSub.talla) subInfo.push(detSub.talla.etiqueta || detSub.talla.nombre);
                $('#view-pedido-info').text(subInfo.join(' · '));

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
                    insumos.forEach((insumo, idx) => {
                        let pct = (insumo.pivot.cantidad_utilizada / insumo.pivot.cantidad_estimada * 100).toFixed(2);
                        $('#view-insumos').append(`
                            <tr class="cot-grouped-row">
                                <td class="cot-col-num text-center">${idx + 1}</td>
                                <td><div class="cot-prod-modelo">${escHtml(insumo.nombre)}</div></td>
                                <td class="cot-cell-num">${insumo.pivot.cantidad_estimada} ${insumo.unidad_medida}</td>
                                <td class="cot-cell-num">${insumo.pivot.cantidad_utilizada} ${insumo.unidad_medida}</td>
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
                                error: function (xhr) {
                                    // Revierte moviendo el ticket de vuelta a la columna original
                                    var $fromCol = $('#kanban-col-' + colSlug(viejoEstado));
                                    if (evt.oldIndex === 0) {
                                        $fromCol.prepend($ticket);
                                    } else {
                                        $fromCol.append($ticket);
                                    }
                                    // 422 con motivo (p. ej. faltan avances por registrar) → mostrarlo
                                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'No se pudo actualizar el estado.';
                                    var esBloqueo = xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message;
                                    Swal.fire(esBloqueo
                                        ? { icon: 'warning', title: 'Etapa bloqueada', text: msg }
                                        : { icon: 'error', title: 'Error', text: msg, timer: 2500, showConfirmButton: false });
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
