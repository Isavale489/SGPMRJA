<script>
// ══════════════════════════════════════════════════════════════════════════
//  Sub-órdenes de Producción — modal dinámico (lista + alta con empleados)
// ══════════════════════════════════════════════════════════════════════════
$(document).ready(function () {
    var CSRF = $('meta[name="csrf-token"]').attr('content');
    var SO_BADGE = { 'Pendiente': 'warning', 'En Proceso': 'info', 'Finalizado': 'success', 'Cancelado': 'danger' };

    function soEsc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
        });
    }

    // Construye la lista de checkboxes de empleados (una sola vez).
    function soPoblarEmpleados() {
        var $c = $('#so-empleados');
        if ($c.children().length) return;
        var emps = window.OP_EMPLEADOS || [];
        if (!emps.length) {
            $c.html('<div class="text-muted small">No hay empleados de Producción disponibles.</div>');
            return;
        }
        $c.html(emps.map(function (e) {
            return '<label class="d-flex align-items-center gap-2 py-1" style="cursor:pointer;">'
                + '<input type="checkbox" class="form-check-input so-emp-check" value="' + e.id + '">'
                + '<span>' + soEsc(e.name) + '</span></label>';
        }).join(''));
    }

    function soRender(data) {
        $('#so-orden-nombre').text(data.orden.producto || ('Orden #' + data.orden.id));
        $('#so-orden-meta').text(data.orden.pedido_id
            ? (' · Pedido #' + data.orden.pedido_id + ' · ' + data.orden.estado)
            : (' · ' + data.orden.estado));

        var subs = data.subordenes || [];
        $('#so-count').text('(' + subs.length + ')');
        $('#so-empty').toggleClass('d-none', subs.length > 0);

        $('#so-lista').html(subs.map(function (s) {
            var emps = (s.empleados || []).map(function (e) {
                return '<span class="badge bg-soft-primary text-primary me-1 mb-1">'
                    + '<i class="ri-user-line me-1"></i>' + soEsc(e.nombre)
                    + (e.rol ? (' · ' + soEsc(e.rol)) : '') + '</span>';
            }).join('') || '<span class="text-muted small fst-italic">Sin empleados asignados</span>';

            var color = SO_BADGE[s.estado] || 'secondary';
            return '<div class="card border-0 shadow-sm mb-2"><div class="card-body py-2 px-3">'
                + '<div class="d-flex justify-content-between align-items-start gap-2">'
                + '<div class="flex-grow-1">'
                + '<div class="fw-semibold">' + soEsc(s.nombre)
                + ' <span class="badge bg-' + color + ' ms-1">' + soEsc(s.estado) + '</span></div>'
                + (s.cantidad_asignada ? ('<small class="text-muted">Cantidad: ' + s.cantidad_asignada + '</small>') : '')
                + (s.notas ? ('<div class="small text-muted fst-italic">' + soEsc(s.notas) + '</div>') : '')
                + '<div class="mt-1">' + emps + '</div>'
                + '</div>'
                + '<button type="button" class="btn btn-sm btn-soft-danger so-del-btn" data-id="' + s.id + '" title="Eliminar sub-orden"><i class="ri-delete-bin-line"></i></button>'
                + '</div></div></div>';
        }).join(''));
    }

    function soCargar(ordenId) {
        $('#so-orden-id').val(ordenId);
        $.get('/ordenes/' + ordenId + '/subordenes', soRender).fail(function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar las sub-órdenes.' });
        });
    }

    function soResetForm() {
        $('#soForm')[0].reset();
        $('#so-empleados .so-emp-check').prop('checked', false);
    }

    // Abrir el modal desde la fila de la OP.
    $(document).on('click', '.subordenes-btn', function () {
        soPoblarEmpleados();
        soResetForm();
        soCargar($(this).data('id'));
        $('#subordenesModal').modal('show');
    });

    // Crear sub-orden + asignar empleados.
    $('#soForm').on('submit', function (e) {
        e.preventDefault();
        var ordenId = $('#so-orden-id').val();
        var nombre = $('#so-nombre').val().trim();
        var empleados = $('#so-empleados .so-emp-check:checked').map(function () {
            return { id: parseInt(this.value, 10), rol: null };
        }).get();

        if (!nombre) { Swal.fire({ icon: 'warning', title: 'Falta la etapa', text: 'Indica el nombre de la etapa/tarea.' }); return; }
        if (!empleados.length) { Swal.fire({ icon: 'warning', title: 'Sin empleados', text: 'Asigna al menos un empleado a la sub-orden.' }); return; }

        var $btn = $('#so-add-btn');
        $btn.attr('disabled', true).html('<i class="ri-loader-4-line me-1"></i>Guardando...');

        $.ajax({
            url: '/ordenes/' + ordenId + '/subordenes',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            data: {
                nombre: nombre,
                cantidad_asignada: $('#so-cantidad').val() || null,
                notas: $('#so-notas').val() || null,
                empleados: empleados
            },
            success: function () {
                soResetForm();
                soCargar(ordenId);
                Swal.fire({ icon: 'success', title: 'Sub-orden creada', showConfirmButton: false, timer: 1300 });
            },
            error: function (xhr) {
                var json = xhr.responseJSON, msg = 'No se pudo crear la sub-orden.';
                if (json && json.errors) { msg = Object.values(json.errors).flat().join('<br>'); }
                else if (json && json.message) { msg = json.message; }
                Swal.fire({ icon: 'error', title: 'Error', html: msg });
            },
            complete: function () {
                $btn.attr('disabled', false).html('<i class="ri-save-line me-1"></i>Agregar sub-orden');
            }
        });
    });

    // Eliminar sub-orden.
    $(document).on('click', '.so-del-btn', function () {
        var subId = $(this).data('id');
        var ordenId = $('#so-orden-id').val();
        Swal.fire({
            title: '¿Eliminar sub-orden?',
            text: 'Se quitará la sub-orden y sus asignaciones de empleados.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.ajax({
                url: '/ordenes/' + ordenId + '/subordenes/' + subId,
                method: 'POST',
                data: { _method: 'DELETE', _token: CSRF },
                success: function () {
                    soCargar(ordenId);
                    Swal.fire({ icon: 'success', title: 'Eliminada', showConfirmButton: false, timer: 1100 });
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Error', text: (xhr.responseJSON && xhr.responseJSON.message) || 'No se pudo eliminar.' });
                }
            });
        });
    });
});
</script>
