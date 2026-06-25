@extends('admin.layouts.app')

@push('styles')
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Control de Calidad</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Operativa</a></li>
                        <li class="breadcrumb-item active">Control de Calidad</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-transactional">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Órdenes finalizadas pendientes de inspección</h5>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Inspecciona las órdenes de producción finalizadas. Si hay unidades defectuosas,
                        la orden vuelve a producción (reproceso); si todo está conforme, queda lista para entrega.
                    </p>
                    <table id="calidad-table"
                        class="table table-bordered table-striped align-middle dt-transactional table-operativa">
                        <thead>
                            <tr>
                                <th>Pedido</th>
                                <th>Producto</th>
                                <th>Producido</th>
                                <th>Estado calidad</th>
                                <th>Finalizada</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════ Modal de inspección ════════ --}}
    <div class="modal fade atlantico-modal atlantico-modal--op" id="inspeccionModal" tabindex="-1"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-shield-check-line me-1"></i>Registrar inspección de calidad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="inspeccionForm">
                    <div class="modal-body">
                        <input type="hidden" id="cc-orden-id">

                        {{-- Resumen de la orden --}}
                        <div class="row g-2 mb-3">
                            <div class="col-md-6"><small class="text-muted d-block">Producto</small>
                                <span class="fw-semibold" id="cc-producto">-</span></div>
                            <div class="col-md-3"><small class="text-muted d-block">Pedido</small>
                                <span class="fw-semibold" id="cc-pedido">-</span></div>
                            <div class="col-md-3"><small class="text-muted d-block">Producido</small>
                                <span class="fw-semibold" id="cc-producido">-</span></div>
                        </div>

                        {{-- Cantidades --}}
                        <div class="row g-2 mb-2">
                            <div class="col-md-4">
                                <label for="cc-inspeccionada" class="form-label required">Inspeccionadas</label>
                                <input type="number" min="1" class="form-control" id="cc-inspeccionada" required>
                                <div class="invalid-feedback" id="cc-inspeccionada-error"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="cc-aprobada" class="form-label required">Aprobadas (conformes)</label>
                                <input type="number" min="0" class="form-control" id="cc-aprobada" required>
                                <div class="invalid-feedback" id="cc-aprobada-error"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="cc-rechazada" class="form-label">Rechazadas (defectuosas)</label>
                                <input type="number" class="form-control bg-light" id="cc-rechazada" readonly>
                            </div>
                        </div>

                        {{-- Resultado (derivado) + motivo --}}
                        <div class="row g-2 mb-2">
                            <div class="col-md-4">
                                <label class="form-label">Resultado</label>
                                <input type="text" class="form-control bg-light" id="cc-resultado-label" readonly>
                                <input type="hidden" id="cc-resultado">
                            </div>
                            <div class="col-md-8">
                                <label for="cc-observaciones" class="form-label" id="cc-observaciones-label">Motivo / observaciones</label>
                                <textarea class="form-control" id="cc-observaciones" rows="2" maxlength="1000"
                                    placeholder="Describe el defecto si hay unidades rechazadas..."></textarea>
                                <div class="invalid-feedback" id="cc-observaciones-error"></div>
                            </div>
                        </div>

                        {{-- Historial de inspecciones previas --}}
                        <div id="cc-historial-wrap" class="mt-3 d-none">
                            <h6 class="fs-13 text-muted mb-2"><i class="ri-history-line me-1"></i>Inspecciones previas</h6>
                            <div id="cc-historial" class="cc-historial-list"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" id="cc-submit-btn">
                            <i class="ri-save-line me-1"></i>Registrar inspección
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(function () {
            var RESULTADO_LABEL = { aprobado: 'Aprobado', rechazado: 'Rechazado', observado: 'Aprobado con observaciones' };

            var table = $('#calidad-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: { url: '{{ route('calidad.data') }}' },
                columns: [
                    { data: 'pedido_info', name: 'pedido.id', className: 'align-middle text-center', orderable: false, searchable: false },
                    { data: 'producto_info', name: 'producto', className: 'align-middle', orderable: false, searchable: false },
                    {
                        data: null, className: 'align-middle text-center', orderable: false, searchable: false,
                        render: function (row) { return row.cantidad_producida + ' / ' + row.cantidad_solicitada; }
                    },
                    {
                        data: 'reinspeccion', className: 'align-middle text-center', orderable: false, searchable: false,
                        render: function (v) {
                            return v
                                ? '<span class="badge bg-warning-subtle text-warning">Re-inspección</span>'
                                : '<span class="badge bg-info-subtle text-info">Pendiente</span>';
                        }
                    },
                    { data: 'fecha_fin', className: 'align-middle text-center', orderable: false, searchable: false },
                    {
                        data: 'id', className: 'align-middle text-center', orderable: false, searchable: false,
                        render: function (id) {
                            return '<button type="button" class="btn btn-sm btn-soft-success inspeccionar-btn" data-id="' + id + '">'
                                + '<i class="ri-shield-check-line"></i> Inspeccionar</button>';
                        }
                    }
                ],
                order: [],
                responsive: false,
                language: lenguajeData
            });

            // ── Abrir modal: cargar detalle de la orden ──
            $('#calidad-table').on('click', '.inspeccionar-btn', function () {
                var id = $(this).data('id');
                $.get('{{ url('calidad') }}/' + id + '/detalle', function (d) {
                    $('#inspeccionForm')[0].reset();
                    $('.is-invalid').removeClass('is-invalid');
                    $('#cc-orden-id').val(d.id);
                    $('#cc-producto').text(d.producto);
                    $('#cc-pedido').text(d.pedido);
                    $('#cc-producido').text(d.cantidad_producida);
                    // Defaults: inspeccionar todo lo producido, todo conforme
                    $('#cc-inspeccionada').val(d.cantidad_producida).attr('max', d.cantidad_producida);
                    $('#cc-aprobada').val(d.cantidad_producida).attr('max', d.cantidad_producida);
                    recalcular();

                    // Historial
                    if (d.historial && d.historial.length) {
                        $('#cc-historial').html(d.historial.map(function (h) {
                            return '<div class="cc-historial-item">'
                                + '<span class="badge bg-' + (h.resultado === 'rechazado' ? 'danger' : (h.resultado === 'observado' ? 'warning' : 'success')) + '-subtle text-' + (h.resultado === 'rechazado' ? 'danger' : (h.resultado === 'observado' ? 'warning' : 'success')) + '">' + (RESULTADO_LABEL[h.resultado] || h.resultado) + '</span> '
                                + '<small class="text-muted">' + h.fecha + ' · ' + h.inspector + '</small><br>'
                                + '<small>Insp. ' + h.inspeccionada + ' · Aprob. ' + h.aprobada + ' · Rech. ' + h.rechazada + (h.observaciones ? ' · ' + h.observaciones : '') + '</small>'
                                + '</div>';
                        }).join(''));
                        $('#cc-historial-wrap').removeClass('d-none');
                    } else {
                        $('#cc-historial-wrap').addClass('d-none');
                    }

                    $('#inspeccionModal').modal('show');
                });
            });

            // ── Recalcular rechazadas + resultado derivado ──
            function recalcular() {
                var insp = parseInt($('#cc-inspeccionada').val(), 10) || 0;
                var aprob = parseInt($('#cc-aprobada').val(), 10) || 0;
                var rech = Math.max(0, insp - aprob);
                $('#cc-rechazada').val(rech);
                var resultado = rech > 0 ? 'rechazado' : 'aprobado';
                $('#cc-resultado').val(resultado);
                $('#cc-resultado-label').val(RESULTADO_LABEL[resultado]);
                // Motivo obligatorio si hay rechazadas
                $('#cc-observaciones-label').toggleClass('required', rech > 0);
            }
            $('#cc-inspeccionada, #cc-aprobada').on('input', recalcular);

            // ── Submit ──
            $('#inspeccionForm').on('submit', function (e) {
                e.preventDefault();
                $('.is-invalid').removeClass('is-invalid');

                var insp = parseInt($('#cc-inspeccionada').val(), 10) || 0;
                var aprob = parseInt($('#cc-aprobada').val(), 10) || 0;
                var rech = parseInt($('#cc-rechazada').val(), 10) || 0;
                var ok = true;

                if (insp < 1) { $('#cc-inspeccionada').addClass('is-invalid'); $('#cc-inspeccionada-error').text('Indica al menos 1 unidad.'); ok = false; }
                if (aprob < 0 || aprob > insp) { $('#cc-aprobada').addClass('is-invalid'); $('#cc-aprobada-error').text('Las aprobadas no pueden superar las inspeccionadas.'); ok = false; }
                if (rech > 0 && !$('#cc-observaciones').val().trim()) {
                    $('#cc-observaciones').addClass('is-invalid');
                    $('#cc-observaciones-error').text('El motivo es obligatorio cuando hay unidades rechazadas.');
                    ok = false;
                }
                if (!ok) return;

                var id = $('#cc-orden-id').val();
                var $btn = $('#cc-submit-btn').prop('disabled', true);
                $.ajax({
                    url: '{{ url('calidad') }}/' + id + '/inspeccionar',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        cantidad_inspeccionada: insp,
                        cantidad_aprobada: aprob,
                        cantidad_rechazada: rech,
                        resultado: $('#cc-resultado').val(),
                        observaciones: $('#cc-observaciones').val().trim() || null
                    },
                    success: function (resp) {
                        $('#inspeccionModal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: '¡Listo!', text: resp.message, showConfirmButton: false, timer: 1800 });
                        $btn.prop('disabled', false);
                    },
                    error: function (xhr) {
                        var msg = xhr.responseJSON && (xhr.responseJSON.message || (xhr.responseJSON.errors && Object.values(xhr.responseJSON.errors)[0][0]));
                        Swal.fire({ icon: 'error', title: 'Error', text: msg || 'No se pudo registrar la inspección.' });
                        $btn.prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endpush
