/**
 * TelefonosRepeater — gestor reutilizable de varios teléfonos por persona.
 *
 * Marcado: ver resources/views/admin/partials/telefonos-field.blade.php
 * (un contenedor [data-tel-repeater] + un <template data-tel-template> en la
 * misma sección). Cada teléfono tiene tipo (móvil/casa/trabajo), prefijo+número
 * y un radio "principal" (exclusivo). Al enviar, syncHiddenInputs() escribe en
 * el form los inputs telefonos[i][numero|tipo|es_principal].
 *
 * Convención de número: "PREFIJO-NUMERO" (ej. 0414-1234567).
 */
(function (window) {
    'use strict';

    var FORMATO = /^[0-9]{4}-[0-9]{7}$/;

    function $tpl(root) {
        return root.closest('.modal-form-section').querySelector('[data-tel-template]');
    }
    function list(root) {
        return root.querySelector('[data-tel-list]');
    }
    function rows(root) {
        return Array.prototype.slice.call(root.querySelectorAll('[data-tel-row]'));
    }
    function maxRows(root) {
        return parseInt(root.dataset.telMax, 10) || 3;
    }
    // Habilita/inhabilita el botón "Agregar" según el tope de teléfonos.
    function refreshAddButton(root) {
        var btn = root.querySelector('[data-tel-add]');
        if (!btn) return;
        var lleno = rows(root).length >= maxRows(root);
        btn.disabled = lleno;
        btn.classList.toggle('disabled', lleno);
        btn.title = lleno ? ('Máximo ' + maxRows(root) + ' teléfonos') : '';
    }

    function bindRow(root, row) {
        var radio = row.querySelector('[data-tel-principal]');
        radio.name = root.dataset.telName || 'telefono_principal';

        // Solo números en el campo
        var num = row.querySelector('[data-tel-numero]');
        num.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
        });

        // Quitar fila (no permitir quedarse sin ninguna)
        row.querySelector('[data-tel-remove]').addEventListener('click', function () {
            // Evita que el tooltip nativo del botón quede flotando al remover
            // el elemento que estaba bajo el cursor (bug visual en Chromium).
            this.removeAttribute('title');
            this.blur();
            var eraPrincipal = radio.checked;
            row.remove();
            var restantes = rows(root);
            if (restantes.length === 0) {
                addRow(root); // siempre al menos una
            } else if (eraPrincipal) {
                restantes[0].querySelector('[data-tel-principal]').checked = true;
            }
            refreshAddButton(root);
        });
    }

    function addRow(root, data) {
        data = data || {};
        var frag = $tpl(root).content.cloneNode(true);
        var row = frag.querySelector('[data-tel-row]');

        if (data.tipo) row.querySelector('[data-tel-tipo]').value = data.tipo;

        if (data.numero && data.numero.indexOf('-') !== -1) {
            var partes = data.numero.split('-');
            row.querySelector('[data-tel-prefijo]').value = partes[0];
            row.querySelector('[data-tel-numero]').value = partes[1];
        }

        var radio = row.querySelector('[data-tel-principal]');
        // Principal si lo indica el dato, o si es la primera fila
        radio.checked = !!data.es_principal || rows(root).length === 0;

        bindRow(root, row);
        list(root).appendChild(row);
        refreshAddButton(root);
        return row;
    }

    function load(root, telefonos) {
        list(root).innerHTML = '';
        if (Array.isArray(telefonos) && telefonos.length) {
            telefonos.forEach(function (t) { addRow(root, t); });
            // Garantizar un principal
            if (!root.querySelector('[data-tel-principal]:checked')) {
                var first = root.querySelector('[data-tel-principal]');
                if (first) first.checked = true;
            }
        } else {
            addRow(root); // una fila vacía marcada principal
        }
        refreshAddButton(root);
    }

    function collect(root) {
        return rows(root).map(function (row) {
            var pfx = row.querySelector('[data-tel-prefijo]').value;
            var num = row.querySelector('[data-tel-numero]').value.trim();
            return {
                numero: num ? (pfx + '-' + num) : '',
                tipo: row.querySelector('[data-tel-tipo]').value,
                es_principal: row.querySelector('[data-tel-principal]').checked
            };
        }).filter(function (t) { return t.numero !== ''; });
    }

    function validate(root) {
        var tels = collect(root);
        if (tels.length === 0) {
            return { ok: false, message: 'Agrega al menos un teléfono.' };
        }
        for (var i = 0; i < tels.length; i++) {
            if (!FORMATO.test(tels[i].numero)) {
                return { ok: false, message: 'Teléfono inválido (formato 0414-1234567): ' + tels[i].numero };
            }
        }
        var principales = tels.filter(function (t) { return t.es_principal; });
        if (principales.length !== 1) {
            return { ok: false, message: 'Marca exactamente un teléfono como principal.' };
        }
        return { ok: true };
    }

    function syncHiddenInputs(form, root) {
        var $form = form.jquery ? form : window.jQuery(form);
        $form.find('input[data-tel-hidden]').remove();
        collect(root).forEach(function (t, i) {
            ['numero', 'tipo', 'es_principal'].forEach(function (k) {
                var val = k === 'es_principal' ? (t.es_principal ? 1 : 0) : t[k];
                window.jQuery('<input type="hidden" data-tel-hidden>')
                    .attr('name', 'telefonos[' + i + '][' + k + ']')
                    .val(val)
                    .appendTo($form);
            });
        });
    }

    function init(root) {
        if (!root || root.dataset.telInit === '1') return;
        root.dataset.telInit = '1';
        root.querySelector('[data-tel-add]').addEventListener('click', function () {
            if (rows(root).length < maxRows(root)) addRow(root);
        });
        if (rows(root).length === 0) addRow(root);
        refreshAddButton(root);
    }

    window.TelefonosRepeater = {
        init: init,
        load: load,
        collect: collect,
        validate: validate,
        syncHiddenInputs: syncHiddenInputs,
        addRow: addRow
    };
})(window);
