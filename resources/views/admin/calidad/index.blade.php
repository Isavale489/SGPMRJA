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
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                            data-bs-target="#pdfExportModal">
                            <i class="ri-file-pdf-fill align-bottom me-1"></i> Exportar PDF
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Barra unificada de búsqueda + filtros (sección Operativa: tema emerald) --}}
                    <div class="advanced-filters-wrapper emerald-theme" id="advanced-filters">
                        <div class="navy-filter-header is-collapsed">
                            <div class="navy-header-search">
                                <i class="ri-search-line"></i>
                                <input type="text" class="navy-search-input" id="custom-search-input"
                                    placeholder="Buscar por producto, pedido o cliente..." autocomplete="off">
                            </div>
                            <div class="navy-header-divider"></div>
                            <button class="navy-filter-btn collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#filters-collapse-body"
                                aria-expanded="false" aria-controls="filters-collapse-body">
                                <i class="ri-filter-3-line"></i>
                                <span>Filtros</span>
                                <span class="navy-filter-badge d-none" id="active-filter-count"></span>
                                <i class="ri-arrow-down-s-line navy-filter-chevron"></i>
                            </button>
                        </div>
                        <div class="collapse" id="filters-collapse-body">
                            <div class="navy-filter-body">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="navy-filter-label" for="filter-estado-calidad">
                                            <i class="ri-search-eye-line"></i> Estado de calidad
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-estado-calidad">
                                            <option value="">Todas</option>
                                            <option value="pendiente">Pendiente (sin inspeccionar)</option>
                                            <option value="reinspeccion">Re-inspección (tras reproceso)</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="navy-filter-label" for="filter-orden">
                                            <i class="ri-sort-desc"></i> Ordenar por
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-orden">
                                            <option value="recientes">Finalización reciente</option>
                                            <option value="antiguos">Finalización antigua</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-2">
                                    <button type="button" class="btn btn-link" id="btn-clear-filters">Limpiar filtros</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Una fila por pedido; la cola de inspección de cada uno vive en
                         el modal "Ver órdenes" (mismo patrón que Órdenes de Producción) --}}
                    <table id="calidad-table"
                        class="table table-bordered table-striped align-middle dt-transactional table-operativa">
                        <thead>
                            <tr>
                                <th>Pedido</th>
                                <th>Cliente</th>
                                <th class="text-center">Por inspeccionar</th>
                                <th class="text-center">Última finalización</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════ Modal: órdenes por inspeccionar de un pedido ════════ --}}
    <div class="modal fade atlantico-modal atlantico-modal--op" id="pedidoCalidadModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mb-0" id="pedido-calidad-title">Inspecciones del pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Contexto del pedido: cliente (izq.) + resumen de la cola (der.) --}}
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3" id="pedido-calidad-meta"></div>

                    <table id="pedido-calidad-table" class="table table-bordered table-striped align-middle dt-transactional table-operativa w-100">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Producido</th>
                                <th class="text-center">Estado calidad</th>
                                <th class="text-center">Finalizada</th>
                                <th class="text-center">Acciones</th>
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
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-search-eye-line me-1"></i>Registrar inspección de calidad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="inspeccionForm">
                    <div class="modal-body qc-body">
                        <input type="hidden" id="cc-orden-id">
                        <input type="hidden" id="cc-resultado">

                        {{-- Contexto del lote: producto a ancho completo (puede envolver) + fila Pedido/Producidas --}}
                        <div class="qc-context">
                            <div class="qc-ctx qc-ctx--full">
                                <span class="qc-ctx-label">Producto</span>
                                <span class="qc-ctx-value qc-ctx-prod" id="cc-producto">—</span>
                            </div>
                            <div class="qc-context-row">
                                <div class="qc-ctx">
                                    <span class="qc-ctx-label">Pedido</span>
                                    <span class="qc-ctx-value" id="cc-pedido">—</span>
                                </div>
                                <div class="qc-ctx qc-ctx--count">
                                    <span class="qc-ctx-label">Producidas</span>
                                    <span class="qc-ctx-value" id="cc-producido">—</span>
                                </div>
                            </div>
                        </div>

                        {{-- Control de partición (compacto, una fila): Inspeccionadas · Defectuosas · Conformes --}}
                        <div class="qc-controls">
                            <div class="qc-field">
                                <span class="qc-field-label">Inspeccionadas</span>
                                <input type="number" id="cc-inspeccionada" min="1" class="qc-mini-input">
                            </div>
                            <div class="qc-field">
                                <span class="qc-field-label qc-label-bad">Defectuosas</span>
                                <div class="qc-stepper">
                                    <button type="button" class="qc-step" data-step="-1" aria-label="Quitar una">−</button>
                                    <input type="number" id="cc-rechazada" class="qc-step-input" value="0" min="0" inputmode="numeric">
                                    <button type="button" class="qc-step" data-step="1" aria-label="Sumar una">+</button>
                                </div>
                            </div>
                            <div class="qc-field qc-field--ok">
                                <span class="qc-field-label qc-label-ok">Conformes</span>
                                <span class="qc-field-num" id="cc-aprobada-num">0</span>
                            </div>
                        </div>

                        {{-- Barra de conformidad (verde conformes / rojo defectuosas) --}}
                        <div class="qc-bar" id="cc-bar">
                            <div class="qc-bar-seg qc-bar-ok" id="cc-bar-ok"></div>
                            <div class="qc-bar-seg qc-bar-bad" id="cc-bar-bad"></div>
                        </div>
                        <div class="invalid-feedback d-block" id="cc-qty-error"></div>

                        {{-- Veredicto en vivo (slim) --}}
                        <div class="qc-verdict" id="cc-verdict">
                            <i class="qc-verdict-icon" id="cc-verdict-icon"></i>
                            <div class="qc-verdict-text">
                                <span class="qc-verdict-title" id="cc-verdict-title"></span>
                                <span class="qc-verdict-sub" id="cc-verdict-sub"></span>
                            </div>
                        </div>

                        {{-- Atribución del rechazo por empleado: solo con equipo (2+) y defectuosas --}}
                        <div class="qc-atrib d-none" id="cc-atrib-wrap">
                            <label class="form-label required mb-1"><i class="ri-team-line me-1"></i>¿De quién son las defectuosas?</label>
                            <div id="cc-atrib-rows"></div>
                            <div class="qc-atrib-total" id="cc-atrib-total"></div>
                            <div class="invalid-feedback d-block" id="cc-atrib-error"></div>
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
    {{-- ════════ Modal: Exportar PDF (historial de inspecciones) ════════ --}}
    <div class="modal fade atlantico-modal" id="pdfExportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-file-pdf-line me-2"></i>Exportar PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Filtra qué inspecciones incluir en el reporte.</p>
                    <div class="mb-0">
                        <label class="form-label fw-semibold" for="pdf-filter-resultado">Resultado</label>
                        <select class="form-select" id="pdf-filter-resultado">
                            <option value="">Todos los resultados</option>
                            <option value="aprobado">Aprobado</option>
                            <option value="observado">Aprobado con observaciones</option>
                            <option value="rechazado">Rechazado</option>
                        </select>
                    </div>
                    <div class="row g-2 mt-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="pdf-fecha-desde">Inspección desde</label>
                            <input type="date" class="form-control" id="pdf-fecha-desde">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="pdf-fecha-hasta">Inspección hasta</label>
                            <input type="date" class="form-control" id="pdf-fecha-hasta">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="btn-generar-pdf">
                        <i class="ri-file-pdf-fill me-1"></i>Generar PDF
                    </button>
                </div>
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

            function debounce(fn, wait) {
                var t;
                return function () { var ctx = this, a = arguments; clearTimeout(t); t = setTimeout(function () { fn.apply(ctx, a); }, wait || 300); };
            }

            function chipPorInspeccionar(n) {
                return '<span class="ord-count-chip ord-count-chip--calidad">' + n + ' por inspeccionar</span>';
            }

            // Desglose de la cola del pedido (meta del modal): pendientes vs re-inspección
            function chipsCalidadPedido(row) {
                var total = parseInt(row.total_ordenes, 10) || 0;
                var rei = parseInt(row.reinspecciones, 10) || 0;
                var pend = total - rei;
                var out = '';
                if (pend > 0) out += '<span class="badge badge-status badge-soft-info rounded-pill"><i class="ri-time-line me-1"></i>' + pend + ' Pendiente' + (pend === 1 ? '' : 's') + '</span> ';
                if (rei > 0) out += '<span class="badge badge-status badge-soft-warning rounded-pill"><i class="ri-restart-line me-1"></i>' + rei + ' Re-inspección</span>';
                return out;
            }

            // Tabla principal: una fila por pedido con su cola de inspección.
            // El detalle por orden vive en el modal "Ver órdenes".
            var table = $('#calidad-table').DataTable({
                processing: true,
                serverSide: true,
                dom: 'rtip',
                ajax: {
                    url: '{{ route('calidad.pedidos-data') }}',
                    data: function (d) {
                        d.filter_estado_calidad = $('#filter-estado-calidad').val();
                        d.filter_orden = $('#filter-orden').val();
                    }
                },
                columns: [
                    {
                        data: 'pedido_id', orderable: false, searchable: false,
                        className: 'align-middle', width: '18%',
                        render: function (data) {
                            // Tile navy + escudo (ícono del módulo): diferencia Calidad
                            // de Órdenes, cuyas primeras columnas son casi idénticas.
                            if (data != null) {
                                return '<div class="ped-cell">'
                                    + '<span class="ped-cell-ic ped-cell-ic--calidad"><i class="ri-search-eye-line"></i></span>'
                                    + '<span class="ped-cell-txt"><span class="ped-cell-eyebrow">Pedido</span><span class="ped-cell-num">#' + data + '</span></span>'
                                    + '</div>';
                            }
                            return '<div class="ped-cell">'
                                + '<span class="ped-cell-ic ped-cell-ic--manual"><i class="ri-tools-line"></i></span>'
                                + '<span class="ped-cell-txt"><span class="ped-cell-eyebrow">Sin pedido</span><span class="ped-cell-num">Manuales</span></span>'
                                + '</div>';
                        }
                    },
                    {
                        data: 'cliente_nombre', orderable: false, searchable: false,
                        className: 'align-middle', width: '30%',
                        render: function (data) {
                            return data ? ccEsc(data) : '<span class="text-muted">—</span>';
                        }
                    },
                    {
                        data: 'total_ordenes', orderable: false, searchable: false,
                        className: 'align-middle text-center', width: '18%',
                        render: function (data) {
                            return chipPorInspeccionar(parseInt(data, 10) || 0);
                        }
                    },
                    { data: 'ultima_fin', orderable: false, searchable: false, className: 'align-middle text-center', width: '16%' },
                    {
                        data: null, orderable: false, searchable: false,
                        className: 'align-middle text-center', width: '18%',
                        render: function () {
                            return '<button type="button" class="btn btn-sm btn-soft-success ver-ordenes-btn">'
                                + '<i class="ri-search-eye-line me-1"></i>Inspeccionar</button>';
                        }
                    }
                ],
                order: [],
                autoWidth: false,
                responsive: false,
                language: lenguajeData
            });

            // ══════════════════════════════════════════════════════
            // Modal "Ver órdenes" — cola de inspección del pedido
            // ══════════════════════════════════════════════════════
            var pedidoCalidadTable = null;
            var pedidoCalidadKey = null; // id del pedido o 'manual'

            // Los conteos del pedido abierto cambian al inspeccionar: recargar ambas.
            function reloadCalidadTables() {
                table.ajax.reload(null, false);
                if (pedidoCalidadTable) pedidoCalidadTable.ajax.reload(null, false);
            }

            function renderPedidoCalidadMeta(row) {
                var esManual = row.pedido_id == null;
                var total = parseInt(row.total_ordenes, 10) || 0;

                var left;
                if (!esManual && row.cliente_nombre) {
                    var inicial = ccEsc((row.cliente_nombre || '?').trim().charAt(0).toUpperCase() || '?');
                    left = '<span class="wiz-client-banner wiz-client-banner--sm" title="Cliente del pedido">'
                        + '<span class="wiz-client-banner-avatar">' + inicial + '</span>'
                        + '<span class="wiz-client-banner-main">'
                        + '<span class="wiz-client-banner-eyebrow">Cliente</span>'
                        + '<span class="wiz-client-banner-name">' + ccEsc(row.cliente_nombre) + '</span>'
                        + '</span></span>';
                } else {
                    left = '<span class="text-muted fs-12"><i class="ri-tools-line me-1"></i>Órdenes creadas sin pedido asociado</span>';
                }

                var right = chipPorInspeccionar(total) + ' ' + chipsCalidadPedido(row);

                $('#pedido-calidad-meta').html(
                    '<div class="d-flex align-items-center">' + left + '</div>'
                    + '<div class="d-flex align-items-center gap-2 flex-wrap">' + right + '</div>'
                );
            }

            // Re-render de la meta tras cada redraw (los conteos cambian al inspeccionar)
            table.on('draw', function () {
                if (pedidoCalidadKey === null || !$('#pedidoCalidadModal').hasClass('show')) return;
                var row = table.rows().data().toArray().find(function (r) {
                    return (r.pedido_id == null ? 'manual' : String(r.pedido_id)) === pedidoCalidadKey;
                });
                if (row) renderPedidoCalidadMeta(row);
            });

            function abrirPedidoCalidad(row) {
                var esManual = row.pedido_id == null;
                pedidoCalidadKey = esManual ? 'manual' : String(row.pedido_id);

                $('#pedido-calidad-title').text(esManual ? 'Órdenes manuales' : 'Inspecciones del Pedido #' + row.pedido_id);
                renderPedidoCalidadMeta(row);

                if (!pedidoCalidadTable) {
                    // Primera apertura: inicializar con el modal MEDIBLE pero
                    // invisible (display:block + visibility:hidden un instante).
                    // DataTables calcula los anchos reales del header del scroll —
                    // al arrancar el fade la tabla ya está construida: cero pop-in.
                    var $modal = $('#pedidoCalidadModal');
                    $modal.css({ display: 'block', visibility: 'hidden' });
                    initPedidoCalidadTable();
                    $modal.css({ display: '', visibility: '' });
                } else {
                    pedidoCalidadTable.ajax.reload();
                }

                $('#pedidoCalidadModal').modal('show');
            }

            function initPedidoCalidadTable() {
                    pedidoCalidadTable = $('#pedido-calidad-table').DataTable({
                        processing: true,
                        serverSide: true,
                        dom: 'rtip',
                        // Altura FIJA del visor (mismo criterio que el modal de Órdenes):
                        // el modal no cambia de tamaño según la cantidad de filas.
                        scrollY: 'min(44vh, 26rem)',
                        scrollCollapse: false,
                        ajax: {
                            url: '{{ route('calidad.data') }}',
                            data: function (d) {
                                d.pedido_id = pedidoCalidadKey;
                            }
                        },
                        columns: [
                            { data: 'producto_info', name: 'producto', className: 'align-middle', orderable: false, searchable: false, width: '34%' },
                            {
                                data: null, className: 'align-middle text-center', orderable: false, searchable: false, width: '14%',
                                render: function (row) { return row.cantidad_producida + ' / ' + row.cantidad_solicitada; }
                            },
                            {
                                data: 'reinspeccion', className: 'align-middle text-center', orderable: false, searchable: false, width: '18%',
                                render: function (v) {
                                    return v
                                        ? '<span class="badge badge-status badge-soft-warning rounded-pill"><i class="ri-restart-line me-1"></i>Re-inspección</span>'
                                        : '<span class="badge badge-status badge-soft-info rounded-pill"><i class="ri-time-line me-1"></i>Pendiente</span>';
                                }
                            },
                            { data: 'fecha_fin', className: 'align-middle text-center', orderable: false, searchable: false, width: '14%' },
                            {
                                data: 'id', className: 'align-middle text-center', orderable: false, searchable: false, width: '20%',
                                render: function (id) {
                                    return '<button type="button" class="btn btn-sm btn-soft-success inspeccionar-btn" data-id="' + id + '">'
                                        + '<i class="ri-search-eye-line"></i> Inspeccionar</button>';
                                }
                            }
                        ],
                        order: [],
                        autoWidth: false,
                        responsive: false,
                        language: lenguajeData
                    });
            }

            // Respaldo: re-sincroniza anchos con el modal plenamente visible.
            $('#pedidoCalidadModal').on('shown.bs.modal', function () {
                if (pedidoCalidadTable) pedidoCalidadTable.columns.adjust();
            });

            $(document).on('click', '.ver-ordenes-btn', function () {
                var row = table.row($(this).closest('tr')).data();
                if (row) abrirPedidoCalidad(row);
            });

            // Toda la fila abre el modal, salvo clicks sobre botones/enlaces.
            $('#calidad-table tbody').on('click', 'tr', function (e) {
                if ($(e.target).closest('button, a').length) return;
                var row = table.row(this).data();
                if (row) abrirPedidoCalidad(row);
            });

            // ── Barra unificada: búsqueda + filtros ──
            function actualizarBadge() {
                var n = 0;
                if ($('#filter-estado-calidad').val()) n++;
                if (($('#filter-orden').val() || 'recientes') !== 'recientes') n++;
                $('#active-filter-count').text(n).toggleClass('d-none', n === 0);
            }
            $('#custom-search-input').on('input', debounce(function () {
                table.search(this.value).draw();
            }, 300));
            $('#filter-estado-calidad, #filter-orden').on('change', function () {
                actualizarBadge();
                table.ajax.reload();
            });
            $('#btn-clear-filters').on('click', function () {
                $('#filter-estado-calidad').val('');
                $('#filter-orden').val('recientes');
                $('#custom-search-input').val('');
                actualizarBadge();
                table.search('').draw();
            });
            $('#filters-collapse-body')
                .on('show.bs.collapse', function () { $('#advanced-filters .navy-filter-header').removeClass('is-collapsed'); })
                .on('hidden.bs.collapse', function () { $('#advanced-filters .navy-filter-header').addClass('is-collapsed'); });

            // ── Exportar PDF — historial de inspecciones ──
            // La validación de rango de fechas la aplica el script global pdf-export-filtros.js
            $('#btn-generar-pdf').on('click', function () {
                var baseUrl = '{{ route('calidad.reporte.pdf') }}';
                var params = [];
                var resultado = $('#pdf-filter-resultado').val();
                var fdesde = $('#pdf-fecha-desde').val();
                var fhasta = $('#pdf-fecha-hasta').val();
                if (resultado) params.push('resultado=' + encodeURIComponent(resultado));
                if (fdesde) params.push('fecha_desde=' + encodeURIComponent(fdesde));
                if (fhasta) params.push('fecha_hasta=' + encodeURIComponent(fhasta));
                window.open(baseUrl + (params.length ? '?' + params.join('&') : ''), '_blank');
                bootstrap.Modal.getInstance(document.getElementById('pdfExportModal'))?.hide();
            });
            $('#pdfExportModal').on('show.bs.modal', function () {
                $('#pdf-filter-resultado, #pdf-fecha-desde, #pdf-fecha-hasta').val('');
            });

            // Estado del lote actual en el modal
            var producidas = 0;
            var equipo = [];   // [{id, nombre, producida}] de la orden inspeccionada
            var atrib = {};     // { empleadoId: defectuosas atribuidas }

            function ccEsc(s) {
                return String(s == null ? '' : s)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }
            // Reparte equitativo las defectuosas respetando lo producido por cada uno.
            function distribuirCapado(total, team) {
                var out = {}; team.forEach(function (e) { out[e.id] = 0; });
                var rem = total, activos = team.slice();
                while (rem > 0 && activos.length) {
                    var base = Math.max(1, Math.floor(rem / activos.length)), avanzo = false;
                    for (var i = 0; i < activos.length && rem > 0; i++) {
                        var e = activos[i], puede = e.producida - out[e.id];
                        if (puede <= 0) continue;
                        var add = Math.min(base, puede, rem);
                        out[e.id] += add; rem -= add; if (add > 0) avanzo = true;
                    }
                    activos = activos.filter(function (e) { return (e.producida - out[e.id]) > 0; });
                    if (!avanzo) break;
                }
                return out;
            }
            function ensureAtrib(rech) {
                var ids = equipo.map(function (e) { return String(e.id); });
                var sameKeys = ids.length === Object.keys(atrib).length && ids.every(function (id) { return atrib[id] != null; });
                var sum = ids.reduce(function (a, id) { return a + (parseInt(atrib[id], 10) || 0); }, 0);
                if (sameKeys && sum === rech) return; // conserva manual válido
                atrib = distribuirCapado(rech, equipo);
            }
            function actualizarAtribTotal(rech) {
                var sum = equipo.reduce(function (a, e) { return a + (parseInt(atrib[e.id], 10) || 0); }, 0);
                var ok = sum === rech;
                $('#cc-atrib-total').toggleClass('is-ok', ok).toggleClass('is-bad', !ok)
                    .html('<i class="ri-' + (ok ? 'checkbox-circle' : 'error-warning') + '-line me-1"></i>Atribuido <strong>' + sum + '</strong> / ' + rech);
            }
            function renderAtrib() {
                var rech = leer().rech;
                var show = equipo.length > 1 && rech > 0;
                $('#cc-atrib-wrap').toggleClass('d-none', !show);
                if (!show) return;
                ensureAtrib(rech);
                $('#cc-atrib-rows').html(equipo.map(function (e) {
                    return '<div class="qc-atrib-row">'
                        + '<span class="qc-atrib-name"><i class="ri-user-line me-1"></i>' + ccEsc(e.nombre)
                        +   ' <small class="text-muted">(prod. ' + e.producida + ')</small></span>'
                        + '<input type="number" class="qc-atrib-input" data-emp="' + e.id + '" min="0" max="' + e.producida + '" value="' + (parseInt(atrib[e.id], 10) || 0) + '">'
                        + '</div>';
                }).join(''));
                actualizarAtribTotal(rech);
            }
            $(document).on('input', '.qc-atrib-input', function () {
                var id = String($(this).data('emp'));
                var v = parseInt(this.value, 10); if (isNaN(v) || v < 0) v = 0;
                atrib[id] = v;
                actualizarAtribTotal(leer().rech);
            });

            // ── Abrir modal de inspección: cargar detalle de la orden ──
            // (delegado en document: el botón vive en el modal "Ver órdenes")
            $(document).on('click', '.inspeccionar-btn', function () {
                var id = $(this).data('id');
                $.get('{{ url('calidad') }}/' + id + '/detalle', function (d) {
                    $('#inspeccionForm')[0].reset();
                    $('.is-invalid').removeClass('is-invalid');
                    producidas = parseInt(d.cantidad_producida, 10) || 0;
                    equipo = (d.equipo || []).map(function (e) { return { id: e.id, nombre: e.nombre, producida: parseInt(e.producida, 10) || 0 }; });
                    atrib = {};

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
                            var motivo = h.observaciones
                                ? '<div class="cc-hist-motivo cc-hist-motivo--' + tono + '">'
                                    + '<i class="ri-chat-quote-line"></i><span>' + ccEsc(h.observaciones) + '</span></div>'
                                : '';
                            return '<div class="cc-historial-item">'
                                + '<div class="cc-hist-top">'
                                +   '<span class="badge bg-' + tono + '-subtle text-' + tono + '">' + (RESULTADO_LABEL[h.resultado] || h.resultado) + '</span>'
                                +   '<small class="text-muted">' + ccEsc(h.fecha) + ' · ' + ccEsc(h.inspector) + '</small>'
                                + '</div>'
                                + '<div class="cc-hist-nums">Insp. ' + h.inspeccionada + ' · Conf. ' + h.aprobada + ' · Def. ' + h.rechazada + '</div>'
                                + motivo
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

                // Atribución por empleado (equipo 2+ con defectuosas)
                renderAtrib();

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
                $('#cc-atrib-error').text('');
                if (q.insp < 1) { $('#cc-inspeccionada').addClass('is-invalid'); $('#cc-qty-error').text('Inspecciona al menos 1 unidad.'); ok = false; }
                if (q.rech > 0 && !$('#cc-observaciones').val().trim()) {
                    $('#cc-observaciones').addClass('is-invalid');
                    $('#cc-observaciones-error').text('El motivo es obligatorio cuando hay unidades defectuosas.');
                    ok = false;
                }

                // Atribución por empleado: con equipo (2+) y defectuosas debe cuadrar.
                var multi = equipo.length > 1;
                var rechazos = null;
                if (multi && q.rech > 0) {
                    var sumA = equipo.reduce(function (a, e) { return a + (parseInt(atrib[e.id], 10) || 0); }, 0);
                    if (sumA !== q.rech) { $('#cc-atrib-error').text('La atribución por empleado debe sumar las unidades defectuosas.'); ok = false; }
                    rechazos = equipo.map(function (e) { return { empleado_id: e.id, cantidad: parseInt(atrib[e.id], 10) || 0 }; });
                }
                if (!ok) return;

                var insp = q.insp, aprob = q.aprob, rech = q.rech;
                var id = $('#cc-orden-id').val();
                var $btn = $('#cc-submit-btn').prop('disabled', true);
                var payload = {
                    _token: '{{ csrf_token() }}',
                    cantidad_inspeccionada: insp,
                    cantidad_aprobada: aprob,
                    cantidad_rechazada: rech,
                    resultado: $('#cc-resultado').val(),
                    observaciones: $('#cc-observaciones').val().trim() || null
                };
                if (rechazos) payload.rechazos = rechazos;
                $.ajax({
                    url: '{{ url('calidad') }}/' + id + '/inspeccionar',
                    method: 'POST',
                    data: payload,
                    success: function (resp) {
                        $('#inspeccionModal').modal('hide');
                        reloadCalidadTables();
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
