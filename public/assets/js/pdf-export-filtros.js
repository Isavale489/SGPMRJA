/**
 * Validación lógica compartida para los filtros de exportación PDF.
 * Aplica a cualquier modal con #pdf-fecha-desde, #pdf-fecha-hasta y #btn-generar-pdf.
 *
 *  1. Vincula los date-pickers: "Hasta" no admite fechas anteriores a "Desde"
 *     (y viceversa) vía min/max dinámicos.
 *  2. Bloquea fechas futuras (max = hoy), SALVO que el botón lleve
 *     data-allow-future="1" (reportes por fecha de entrega estimada: pedidos,
 *     órdenes de producción).
 *  3. Al generar, valida que "Desde" <= "Hasta" y que no haya futuro indebido;
 *     si falla, bloquea la generación y avisa con SweetAlert.
 *
 * Es independiente del handler de cada módulo (corre antes y lo cancela si el
 * rango es inválido), por lo que no requiere tocar el JS de cada vista.
 */
(function () {
    'use strict';

    function hoyISO() {
        var d = new Date();
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return d.getFullYear() + '-' + m + '-' + day;
    }

    function init() {
        var desde = document.getElementById('pdf-fecha-desde');
        var hasta = document.getElementById('pdf-fecha-hasta');
        var btn = document.getElementById('btn-generar-pdf');
        if (!desde || !hasta || !btn) return;

        var allowFuture = btn.getAttribute('data-allow-future') === '1';
        var HOY = hoyISO();

        function aplicarTopeFuturo() {
            if (!allowFuture) {
                desde.setAttribute('max', HOY);
                hasta.setAttribute('max', HOY);
            }
        }

        function vincular() {
            // Hasta >= Desde
            if (desde.value) {
                hasta.setAttribute('min', desde.value);
            } else {
                hasta.removeAttribute('min');
            }
            // Desde <= Hasta (y <= hoy si no se permite futuro)
            var topeDesde = hasta.value || (allowFuture ? null : HOY);
            if (topeDesde) {
                desde.setAttribute('max', topeDesde);
            } else if (!allowFuture) {
                desde.setAttribute('max', HOY);
            } else {
                desde.removeAttribute('max');
            }
        }

        aplicarTopeFuturo();
        desde.addEventListener('change', vincular);
        hasta.addEventListener('change', vincular);

        // Al reabrir el modal, limpiar los vínculos previos (los valores se resetean).
        document.addEventListener('show.bs.modal', function (e) {
            if (e.target && e.target.contains && e.target.contains(desde)) {
                desde.removeAttribute('min');
                hasta.removeAttribute('min');
                aplicarTopeFuturo();
            }
        });

        // Validación al generar (capturing + registrado antes que el handler del
        // módulo → si el rango es inválido, lo cancela con stopImmediatePropagation).
        btn.addEventListener('click', function (e) {
            var d = desde.value;
            var h = hasta.value;
            var err = null;

            if (!allowFuture && ((d && d > HOY) || (h && h > HOY))) {
                err = 'No se permiten fechas futuras en este reporte.';
            } else if (d && h && d > h) {
                err = 'La fecha "Desde" no puede ser posterior a la fecha "Hasta".';
            }

            if (err) {
                e.stopImmediatePropagation();
                e.preventDefault();
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Rango de fechas inválido',
                        text: err,
                        confirmButtonColor: '#1e3c72',
                    });
                } else {
                    alert(err);
                }
            }
        }, true);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
