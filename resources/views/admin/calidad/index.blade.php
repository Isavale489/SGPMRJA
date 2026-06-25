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
                    <div class="modal-body qc-body">
                        <input type="hidden" id="cc-orden-id">
                        <input type="hidden" id="cc-resultado">

                        {{-- Contexto del lote --}}
                        <div class="qc-context">
                            <div class="qc-ctx">
                                <span class="qc-ctx-label">Producto</span>
                                <span class="qc-ctx-value" id="cc-producto">—</span>
                            </div>
                            <div class="qc-ctx">
                                <span class="qc-ctx-label">Pedido</span>
                                <span class="qc-ctx-value" id="cc-pedido">—</span>
                            </div>
                            <div class="qc-ctx qc-ctx--count">
                                <span class="qc-ctx-label">Producidas</span>
                                <span class="qc-ctx-value" id="cc-producido">—</span>
                            </div>
                        </div>

                        {{-- Control de partición: defectuosas (entrada) → conformes (derivado) --}}
                        <p class="qc-prompt">Del lote, marca cuántas unidades resultaron <strong>defectuosas</strong>.</p>
                        <div class="qc-split">
                            <div class="qc-counter qc-counter--bad">
                                <span class="qc-counter-label"><i class="ri-close-circle-line"></i> Defectuosas</span>
                                <div class="qc-stepper">
                                    <button type="button" class="qc-step" data-step="-1" aria-label="Quitar una">−</button>
                                    <input type="number" id="cc-rechazada" class="qc-step-input" value="0" min="0" inputmode="numeric">
                                    <button type="button" class="qc-step" data-step="1" aria-label="Sumar una">+</button>
                                </div>
                            </div>
                            <div class="qc-counter qc-counter--ok">
                                <span class="qc-counter-label"><i class="ri-checkbox-circle-line"></i> Conformes</span>
                                <span class="qc-counter-num" id="cc-aprobada-num">0</span>
                            </div>
                        </div>

                        {{-- Barra de conformidad (verde conformes / rojo defectuosas) --}}
                        <div class="qc-bar" id="cc-bar">
                            <div class="qc-bar-seg qc-bar-ok" id="cc-bar-ok"></div>
                            <div class="qc-bar-seg qc-bar-bad" id="cc-bar-bad"></div>
                        </div>

                        {{-- Inspeccionadas (subconjunto opcional) --}}
                        <div class="qc-inspeccionadas">
                            <label for="cc-inspeccionada">Unidades inspeccionadas</label>
                            <input type="number" id="cc-inspeccionada" min="1" class="form-control form-control-sm">
                            <small class="text-muted">Por defecto se inspeccionan todas las producidas.</small>
                            <div class="invalid-feedback d-block" id="cc-qty-error"></div>
                        </div>

                        {{-- Veredicto en vivo --}}
                        <div class="qc-verdict" id="cc-verdict">
                            <i class="qc-verdict-icon" id="cc-verdict-icon"></i>
                            <div class="qc-verdict-text">
                                <span class="qc-verdict-title" id="cc-verdict-title"></span>
                                <span class="qc-verdict-sub" id="cc-verdict-sub"></span>
                            </div>
                        </div>

                        {{-- Motivo: solo cuando hay defectuosas --}}
                        <div class="qc-motivo d-none" id="cc-motivo-wrap">
                            <label for="cc-observaciones" class="form-label required">Motivo del rechazo</label>
                            <textarea class="form-control" id="cc-observaciones" rows="2" maxlength="1000"
                                placeholder="Describe el defecto encontrado (costura, mancha, talla…)"></textarea>
                            <div class="invalid-feedback" id="cc-observaciones-error"></div>
                        </div>

                        {{-- Historial de inspecciones previas --}}
                        <div id="cc-historial-wrap" class="qc-historial-wrap d-none">
                            <h6 class="qc-historial-title"><i class="ri-history-line me-1"></i>Inspecciones previas</h6>
                            <div id="cc-historial" class="cc-historial-list"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" id="cc-submit-btn">
                            <i class="ri-shield-check-line me-1"></i><span id="cc-submit-label">Aprobar inspección</span>
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

            // Estado del lote actual en el modal
            var producidas = 0;

            // ── Abrir modal: cargar detalle de la orden ──
            $('#calidad-table').on('click', '.inspeccionar-btn', function () {
                var id = $(this).data('id');
                $.get('{{ url('calidad') }}/' + id + '/detalle', function (d) {
                    $('#inspeccionForm')[0].reset();
                    $('.is-invalid').removeClass('is-invalid');
                    producidas = parseInt(d.cantidad_producida, 10) || 0;

                    $('#cc-orden-id').val(d.id);
                    $('#cc-producto').text(d.producto);
                    $('#cc-pedido').text(d.pedido);
                    $('#cc-producido').text(producidas);
                    // Defaults: inspeccionar todo, 0 defectuosas
                    $('#cc-inspeccionada').val(producidas).attr('max', producidas);
                    $('#cc-rechazada').val(0).attr('max', producidas);
                    render();

                    // Historial
                    if (d.historial && d.historial.length) {
                        $('#cc-historial').html(d.historial.map(function (h) {
                            var tono = h.resultado === 'rechazado' ? 'danger' : (h.resultado === 'observado' ? 'warning' : 'success');
                            return '<div class="cc-historial-item">'
                                + '<span class="badge bg-' + tono + '-subtle text-' + tono + '">' + (RESULTADO_LABEL[h.resultado] || h.resultado) + '</span> '
                                + '<small class="text-muted">' + h.fecha + ' · ' + h.inspector + '</small><br>'
                                + '<small>Insp. ' + h.inspeccionada + ' · Conf. ' + h.aprobada + ' · Def. ' + h.rechazada + (h.observaciones ? ' · ' + h.observaciones : '') + '</small>'
                                + '</div>';
                        }).join(''));
                        $('#cc-historial-wrap').removeClass('d-none');
                    } else {
                        $('#cc-historial-wrap').addClass('d-none');
                    }

                    $('#inspeccionModal').modal('show');
                });
            });

            // ── Lee y normaliza las cantidades del modal ──
            function leer() {
                var insp = Math.min(Math.max(parseInt($('#cc-inspeccionada').val(), 10) || 0, 0), producidas || 0);
                var rech = Math.min(Math.max(parseInt($('#cc-rechazada').val(), 10) || 0, 0), insp);
                return { insp: insp, rech: rech, aprob: insp - rech };
            }

            // ── Render en vivo: conformes, barra, veredicto, motivo, botón ──
            function render() {
                var q = leer();
                $('#cc-rechazada').val(q.rech);
                $('#cc-aprobada-num').text(q.aprob);

                // Barra de conformidad
                var total = q.insp || 1;
                $('#cc-bar-ok').css('width', (q.aprob / total * 100) + '%');
                $('#cc-bar-bad').css('width', (q.rech / total * 100) + '%');

                var conforme = q.rech === 0;
                $('#cc-resultado').val(conforme ? 'aprobado' : 'rechazado');

                // Veredicto
                var $v = $('#cc-verdict').removeClass('is-ok is-reproceso').addClass(conforme ? 'is-ok' : 'is-reproceso');
                $('#cc-verdict-icon').attr('class', 'qc-verdict-icon ' + (conforme ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill'));
                $('#cc-verdict-title').text(conforme ? 'Conforme' : 'Reproceso');
                $('#cc-verdict-sub').text(conforme
                    ? (q.aprob + ' unidad' + (q.aprob === 1 ? '' : 'es') + ' lista' + (q.aprob === 1 ? '' : 's') + ' para entrega.')
                    : (q.rech + ' unidad' + (q.rech === 1 ? '' : 'es') + ' vuelve' + (q.rech === 1 ? '' : 'n') + ' a producción para rehacerse.'));

                // Motivo solo si hay defectuosas
                $('#cc-motivo-wrap').toggleClass('d-none', conforme);

                // Botón primario
                $('#cc-submit-label').text(conforme ? 'Aprobar inspección' : 'Registrar reproceso');
                $('#cc-submit-btn').toggleClass('btn-success', conforme).toggleClass('btn-warning', !conforme);
            }

            // Stepper de defectuosas
            $('.qc-stepper .qc-step').on('click', function () {
                var step = parseInt($(this).data('step'), 10);
                $('#cc-rechazada').val((parseInt($('#cc-rechazada').val(), 10) || 0) + step);
                render();
            });
            $('#cc-rechazada, #cc-inspeccionada').on('input', render);

            // ── Submit ──
            $('#inspeccionForm').on('submit', function (e) {
                e.preventDefault();
                $('.is-invalid').removeClass('is-invalid');
                $('#cc-qty-error').text('');

                var q = leer();
                var ok = true;
                if (q.insp < 1) { $('#cc-inspeccionada').addClass('is-invalid'); $('#cc-qty-error').text('Inspecciona al menos 1 unidad.'); ok = false; }
                if (q.rech > 0 && !$('#cc-observaciones').val().trim()) {
                    $('#cc-observaciones').addClass('is-invalid');
                    $('#cc-observaciones-error').text('El motivo es obligatorio cuando hay unidades defectuosas.');
                    ok = false;
                }
                if (!ok) return;

                var insp = q.insp, aprob = q.aprob, rech = q.rech;
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
