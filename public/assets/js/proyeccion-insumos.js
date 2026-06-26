/* ============================================================================
 * proyeccion-insumos.js — Aviso NO bloqueante de stock para producción.
 *
 * Renderer compartido entre Cotizaciones y Pedidos. Pide al backend la
 * proyección de insumos (requerido vs stock) y la pinta en un panel con tres
 * estados por insumo: falta / ajustado / ok. NO bloquea nada: solo informa y,
 * si faltan insumos, ofrece un atajo para crear una compra precargada.
 *
 * Uso:
 *   ProyeccionInsumos.cargar({
 *     url, method, payload, bodyEl, badgeEl, csrf, contexto
 *   });
 *
 * Depende solo de fetch + DOM (no jQuery).
 * ==========================================================================*/
(function (window, document) {
    'use strict';

    var COMPRAS_URL = '/compras';
    var PREFILL_KEY = 'sgpmrja_compra_prefill';
    var STOCK_CHANNEL = 'sgpmrja_stock';
    var STOCK_LS_KEY = 'sgpmrja_stock_change';

    // ── Sincronización entre pestañas (stock cambió) ────────────────────────
    // Cuando una compra se procesa/anula (cambia stock) en una pestaña, avisamos
    // a las demás para que recalculen su proyección al instante. BroadcastChannel
    // donde exista; si no, fallback al evento 'storage' de localStorage.
    var _bc = null;
    function _channel() {
        if (_bc === null) {
            _bc = ('BroadcastChannel' in window)
                ? (function () { try { return new BroadcastChannel(STOCK_CHANNEL); } catch (e) { return false; } })()
                : false;
        }
        return _bc || null;
    }
    function notifyStockChange(motivo) {
        var ch = _channel();
        if (ch) { try { ch.postMessage({ type: 'stock-change', motivo: motivo || '' }); } catch (e) {} }
        // Fallback (y refuerzo): el evento 'storage' dispara en las OTRAS pestañas.
        try { window.localStorage.setItem(STOCK_LS_KEY, String(Date.now())); } catch (e) {}
    }
    function onStockChange(cb) {
        var ch = _channel();
        if (ch) {
            ch.addEventListener('message', function (ev) {
                if (ev && ev.data && ev.data.type === 'stock-change') cb(ev.data);
            });
        }
        window.addEventListener('storage', function (ev) {
            if (ev.key === STOCK_LS_KEY) cb({ motivo: 'storage' });
        });
    }

    // Formatea un número quitando ceros decimales sobrantes (1100, 16.5, 2).
    function fmt(n) {
        var v = Math.round((parseFloat(n) || 0) * 100) / 100;
        return v.toLocaleString('es-VE', { maximumFractionDigits: 2 });
    }

    function esc(str) {
        return String(str == null ? '' : str).replace(/[&<>"']/g, function (c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
        });
    }

    var ESTADO_META = {
        falta:    { cls: 'proy-falta',    icon: 'ri-error-warning-fill',  txt: 'Falta' },
        ajustado: { cls: 'proy-ajustado', icon: 'ri-alert-fill',          txt: 'Ajustado' },
        ok:       { cls: 'proy-ok',       icon: 'ri-checkbox-circle-fill', txt: 'OK' }
    };

    function setLoading(bodyEl) {
        bodyEl.innerHTML =
            '<div class="proy-state proy-state--muted">' +
            '<span class="spinner-border spinner-border-sm me-2"></span>Calculando disponibilidad…</div>';
    }

    function setError(bodyEl) {
        bodyEl.innerHTML =
            '<div class="proy-state proy-state--muted">' +
            '<i class="ri-wifi-off-line me-1"></i>No se pudo calcular la proyección de insumos.</div>';
    }

    function setBadge(badgeEl, data) {
        if (!badgeEl) return;
        var nFalta = (data.items || []).filter(function (i) { return i.estado === 'falta'; }).length;
        if (data.hay_faltantes) {
            badgeEl.className = 'badge rounded-pill bg-danger-subtle text-danger';
            badgeEl.innerHTML = '<i class="ri-error-warning-line me-1"></i>' + nFalta + ' por comprar';
            badgeEl.hidden = false;
        } else if (data.hay_alertas) {
            badgeEl.className = 'badge rounded-pill bg-warning-subtle text-warning';
            badgeEl.innerHTML = '<i class="ri-alert-line me-1"></i>Stock ajustado';
            badgeEl.hidden = false;
        } else if ((data.items || []).length) {
            badgeEl.className = 'badge rounded-pill bg-success-subtle text-success';
            badgeEl.innerHTML = '<i class="ri-checkbox-circle-line me-1"></i>Stock OK';
            badgeEl.hidden = false;
        } else {
            badgeEl.hidden = true;
        }
    }

    function render(bodyEl, badgeEl, data, contexto) {
        setBadge(badgeEl, data);

        var items = data.items || [];
        if (!items.length) {
            bodyEl.innerHTML =
                '<div class="proy-state proy-state--muted">' +
                '<i class="ri-information-line me-1"></i>No hay insumos de producción que proyectar para estas líneas.</div>';
            return;
        }

        // Encabezado resumen según el peor estado.
        var head;
        if (data.hay_faltantes) {
            head = '<div class="proy-summary proy-falta"><i class="ri-error-warning-fill"></i>' +
                '<div><strong>Faltan insumos para producir.</strong>' +
                '<span>Puedes guardar igual; registra una compra para reponer antes de producir.</span></div></div>';
        } else if (data.hay_alertas) {
            head = '<div class="proy-summary proy-ajustado"><i class="ri-alert-fill"></i>' +
                '<div><strong>Alcanza, pero el stock queda ajustado.</strong>' +
                '<span>Producir esto deja uno o más insumos por debajo del mínimo.</span></div></div>';
        } else {
            head = '<div class="proy-summary proy-ok"><i class="ri-checkbox-circle-fill"></i>' +
                '<div><strong>Hay stock suficiente para producir todo.</strong></div></div>';
        }

        var rows = items.map(function (it) {
            var m = ESTADO_META[it.estado] || ESTADO_META.ok;
            var detalle = (it.estado === 'falta')
                ? '<span class="proy-row-falta">Comprar ' + fmt(it.faltante) + ' ' + esc(it.unidad) + '</span>'
                : '<span class="proy-row-rest">Quedan ' + fmt(it.restante) + ' ' + esc(it.unidad) + '</span>';
            return '<li class="proy-row ' + m.cls + '">' +
                '<i class="proy-row-ic ' + m.icon + '"></i>' +
                '<div class="proy-row-main">' +
                    '<span class="proy-row-nombre">' + esc(it.nombre) + '</span>' +
                    '<span class="proy-row-meta">Necesita ' + fmt(it.requerido) + ' ' + esc(it.unidad) +
                        ' · stock ' + fmt(it.stock) + '</span>' +
                '</div>' +
                '<div class="proy-row-end">' + detalle + '</div>' +
            '</li>';
        }).join('');

        var cta = '';
        if (data.hay_faltantes) {
            // Etiquetas según el módulo (la cotización/el pedido no dependen del stock).
            var registroLabel = contexto === 'pedido' ? 'el pedido'
                : (contexto === 'cotizacion' ? 'la cotización' : 'este registro');
            var accionLabel = contexto === 'pedido' ? 'guardar el pedido'
                : (contexto === 'cotizacion' ? 'crear la cotización' : 'guardar');

            cta = '<button type="button" class="btn btn-sm btn-soft-danger w-100 mt-2 proy-crear-compra">' +
                '<i class="ri-shopping-cart-2-line me-1"></i>Crear compra con los faltantes' +
                '<i class="ri-arrow-right-up-line ms-1"></i></button>' +
                '<p class="proy-note"><i class="ri-information-line me-1"></i>' +
                'La compra se abre en otra pestaña: ' + registroLabel + ' no se pierde. ' +
                'Puedes ' + accionLabel + ' igual y comprar antes, durante o después.</p>';
        }

        bodyEl.innerHTML = head + '<ul class="proy-list">' + rows + '</ul>' + cta;

        var btn = bodyEl.querySelector('.proy-crear-compra');
        if (btn) {
            btn.addEventListener('click', function () {
                irACompraConFaltantes(items, contexto);
            });
        }
    }

    // Guarda los faltantes en localStorage y abre el módulo de Compras, que los
    // precarga en el wizard de nueva compra (borrador).
    function irACompraConFaltantes(items, contexto) {
        var faltantes = items.filter(function (i) { return i.estado === 'falta'; }).map(function (i) {
            return {
                insumo_id: i.insumo_id,
                nombre: i.nombre,
                codigo: i.codigo,
                unidad: i.unidad,
                cantidad: i.faltante
            };
        });
        try {
            window.localStorage.setItem(PREFILL_KEY, JSON.stringify({
                origen: contexto || 'produccion',
                ts: Date.now(),
                insumos: faltantes
            }));
        } catch (e) { /* storage no disponible: continuamos igual */ }
        window.open(COMPRAS_URL + '?prefill=1', '_blank');
    }

    // Pide la proyección al backend y la renderiza.
    function cargar(opts) {
        var bodyEl = opts.bodyEl;
        var badgeEl = opts.badgeEl || null;
        if (!bodyEl) return;
        setLoading(bodyEl);

        var headers = { 'Accept': 'application/json' };
        var fetchOpts = { method: opts.method || 'GET', headers: headers };
        if ((opts.method || 'GET').toUpperCase() === 'POST') {
            headers['Content-Type'] = 'application/json';
            headers['X-CSRF-TOKEN'] = opts.csrf || '';
            fetchOpts.body = JSON.stringify(opts.payload || {});
        }

        fetch(opts.url, fetchOpts)
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(function (data) { render(bodyEl, badgeEl, data, opts.contexto); })
            .catch(function () { setError(bodyEl); });
    }

    window.ProyeccionInsumos = {
        cargar: cargar,
        render: render,
        notifyStockChange: notifyStockChange,
        onStockChange: onStockChange,
        PREFILL_KEY: PREFILL_KEY
    };
})(window, document);
