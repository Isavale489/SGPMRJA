/**
 * DtGroupRows — agrupación visual por "fila cabecera" para DataTables server-side.
 *
 * Inserta una fila cabecera (<tr class="dtg-row">) antes de cada corrida de filas
 * consecutivas que comparten la misma clave de grupo (p. ej. el pedido). Es 100%
 * presentacional: se reconstruye en cada draw y NO altera paginación, filtros,
 * búsqueda ni conteos del DataTable. Requisito: el backend debe ORDENAR el
 * resultset de forma que las filas de un mismo grupo queden contiguas.
 *
 * La cabecera es colapsable con click/Enter. El estado abierto/cerrado de cada
 * grupo se recuerda entre draws (paginación, filtros, reloads tras una acción)
 * mientras la página siga cargada.
 *
 * Uso (después de inicializar el DataTable):
 *   DtGroupRows.attach(table, {
 *       colspan: 6,
 *       startCollapsed: true, // opcional: grupos plegados al dibujar (default: false)
 *       accordion: true,      // opcional: abrir un grupo pliega los demás (default: false)
 *       groupKey: function (row) { return row.pedido_id ? 'p' + row.pedido_id : 'manual'; },
 *       renderHeader: function (rows, key) { return '<span class="dtg-title">…</span>'; }
 *   });
 */
window.DtGroupRows = (function ($) {
    'use strict';

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function attach(dt, opts) {
        var $tbody = $(dt.table().body());

        // Estado abierto/cerrado por clave de grupo. Persiste entre draws
        // (paginación, filtros, reloads tras una acción) mientras viva la página.
        var estado = {};
        function estaAbierto(key) {
            return Object.prototype.hasOwnProperty.call(estado, key)
                ? estado[key]
                : !opts.startCollapsed;
        }

        dt.on('draw.dtGroupRows', function () {
            var rows = dt.rows({ page: 'current' });
            var data = rows.data().toArray();
            var nodes = rows.nodes().toArray();
            if (!data.length) return;

            // Corridas consecutivas con la misma clave = un grupo
            var grupos = [];
            var actual = null;
            data.forEach(function (row, i) {
                var key = String(opts.groupKey(row));
                if (!actual || actual.key !== key) {
                    actual = { key: key, rows: [], nodes: [] };
                    grupos.push(actual);
                }
                actual.rows.push(row);
                actual.nodes.push(nodes[i]);
            });

            grupos.forEach(function (g) {
                var abierto = estaAbierto(g.key);
                g.nodes.forEach(function (n) { $(n).attr('data-dtg', g.key).toggleClass('d-none', !abierto); });

                $('<tr class="dtg-row" role="button" tabindex="0"></tr>')
                    .attr('aria-expanded', String(abierto))
                    .toggleClass('dtg-collapsed', !abierto)
                    .attr('data-dtg-key', g.key)
                    .append(
                        $('<td></td>').attr('colspan', opts.colspan).html(
                            '<div class="dtg-head">'
                            + '<i class="ri-arrow-down-s-line dtg-caret"></i>'
                            + opts.renderHeader(g.rows, g.key)
                            + '</div>'
                        )
                    )
                    .insertBefore(g.nodes[0]);
            });
        });

        // ── Plegado/desplegado ──
        // Toggle instantáneo (como un <details> nativo) con un micro-fade de
        // entrada vía CSS (.dtg-show). El repliegue es inmediato, sin animación.
        var REDUCED = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        $tbody.on('click', 'tr.dtg-row', function () {
            var $h = $(this);
            var key = $h.attr('data-dtg-key');
            var $rows = $tbody.find('tr[data-dtg="' + key + '"]');
            var abrir = $h.hasClass('dtg-collapsed');

            // Modo acordeón: abrir un grupo pliega cualquier otro abierto
            // (incluidos los recordados en otras páginas del DataTable)
            if (abrir && opts.accordion) {
                $tbody.find('tr.dtg-row').not($h).not('.dtg-collapsed').trigger('click');
                for (var k in estado) { estado[k] = false; }
            }

            estado[key] = abrir;
            $h.toggleClass('dtg-collapsed', !abrir).attr('aria-expanded', String(abrir));

            $rows.toggleClass('d-none', !abrir);
            if (abrir && !REDUCED) {
                $rows.addClass('dtg-show');
                setTimeout(function () { $rows.removeClass('dtg-show'); }, 200);
            }
        });
        $tbody.on('keydown', 'tr.dtg-row', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); $(this).trigger('click'); }
        });
    }

    return { attach: attach, esc: esc };
})(jQuery);
