<style>
    /* Paleta de colores marca Atlántico */
    :root {
        --atlantico-dark-blue: #1e3c72;
        --atlantico-green: #2ecc71;
        --atlantico-cyan: #00d9a5;
        --atlantico-light-cyan: #38ef7d;
    }

    /* Estilos para dropdowns de estado - Paleta Atlántico sobria */
    .estado-pendiente {
        background-color: rgba(30, 60, 114, 0.15) !important;
        border-color: #1e3c72 !important;
        color: #1e3c72 !important;
    }

    .estado-aprobada {
        background-color: rgba(0, 217, 165, 0.15) !important;
        border-color: #00d9a5 !important;
        color: #006b52 !important;
    }

    .estado-rechazada {
        background-color: rgba(139, 58, 58, 0.15) !important;
        border-color: #8b3a3a !important;
        color: #8b3a3a !important;
    }

    /* Soft background colors for icons - Paleta Atlántico */
    .bg-soft-primary {
        background-color: rgba(30, 60, 114, 0.1) !important;
        /* azul oscuro */
    }

    .bg-soft-secondary {
        background-color: rgba(46, 204, 113, 0.15) !important;
        /* verde */
    }

    .bg-soft-success {
        background-color: rgba(0, 217, 165, 0.1) !important;
        /* cyan */
    }

    .bg-soft-info {
        background-color: rgba(56, 239, 125, 0.1) !important;
        /* verde claro */
    }

    .bg-soft-warning {
        background-color: rgba(46, 204, 113, 0.2) !important;
        /* verde más intenso */
    }

    .bg-soft-danger {
        background-color: rgba(30, 60, 114, 0.15) !important;
        /* azul oscuro más intenso */
    }

    /* Colores de texto personalizados */
    .text-atlantico-dark {
        color: #1e3c72 !important;
    }

    .text-atlantico-green {
        color: #2ecc71 !important;
    }

    .text-atlantico-cyan {
        color: #00d9a5 !important;
    }

    /* Estilo para buscador personalizado */
    .search-box {
        position: relative;
    }

    .search-box .search-icon {
        position: absolute;
        top: 50%;
        left: 10px;
        transform: translateY(-50%);
        color: #878a99;
    }

    .search-box input {
        padding-left: 30px;
    }

    .lleva-bordado-checkbox {
        border-color: #1e3c72 !important;
    }

    .lleva-bordado-checkbox:checked {
        background-color: var(--atlantico-cyan) !important;
        border-color: var(--atlantico-cyan) !important;
    }

    .bordado-label {
        color: #6c757d;
        transition: color 0.15s ease;
    }

    .bordado-label.active {
        color: var(--atlantico-dark-blue);
    }

    .bordado-resumen-box {
        border: 1px dashed rgba(30, 60, 114, 0.25);
        border-radius: 8px;
        background: rgba(30, 60, 114, 0.06);
    }

    .bordado-resumen-title {
        font-size: 0.72rem;
        color: #5a6a85;
        font-weight: 600;
    }

    .bordado-resumen-value {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--atlantico-dark-blue);
    }

    .bordado-resumen-estado {
        font-size: 0.73rem;
    }

    .bordado-linea-total-value.total-updated {
        color: #00a884;
        text-shadow: 0 0 4px rgba(0, 168, 132, 0.18);
    }

    .ubicacion-std-row,
    .ubicacion-personalizada-row {
        transition: border-color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease;
    }

    .ubicacion-row-disabled {
        background: rgba(30, 60, 114, 0.03);
        border-color: rgba(30, 60, 114, 0.16) !important;
    }

    .ubicacion-row-pending {
        background: rgba(46, 204, 113, 0.06);
        border-color: rgba(46, 204, 113, 0.32) !important;
    }

    .ubicacion-row-complete {
        background: rgba(0, 217, 165, 0.08);
        border-color: rgba(0, 217, 165, 0.4) !important;
        box-shadow: inset 0 0 0 1px rgba(0, 217, 165, 0.16);
    }

    .ubicacion-mini-field {
        width: 95px;
    }

    .ubicacion-mini-field.is-cantidad {
        width: 75px;
    }

    .ubicacion-mini-label {
        display: block;
        font-size: 0.66rem;
        color: #6c7a94;
        font-weight: 700;
        margin-bottom: 0.2rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .ubicacion-estado-ayuda {
        font-size: 0.72rem;
        line-height: 1.2;
    }

    /* ─── Modal configurador de bordados (sobre el modal de cotización) ──── */
    /* z-index por encima del modal padre (#showModal, 1055). El backdrop se
       ajusta en JS para quedar entre ambos (patrón del modal de logos). */
    .bordado-modal {
        z-index: 1075;
    }

    .bordado-modal-dialog {
        max-width: 560px;
    }

    /* modal-content acotado en alto + flex column para que la lista scrollee
       internamente y header/buscador/footer queden fijos. */
    .bordado-modal .modal-content {
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: none;
    }

    #bordadoOffcanvas .modal-body {
        min-height: 0;
    }

    .bordado-oc-header {
        background: linear-gradient(135deg, #132649 0%, #1e3c72 100%);
        border-bottom: 2px solid rgba(0, 217, 165, 0.30);
        padding: 1rem 1.1rem;
        flex-shrink: 0;
    }

    .bordado-oc-icon {
        width: 38px;
        height: 38px;
        background: rgba(0, 217, 165, 0.16);
        border: 1.5px solid rgba(0, 217, 165, 0.4);
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: var(--atlantico-cyan);
        font-size: 1.1rem;
    }

    .bordado-oc-eyebrow {
        font-size: 0.66rem;
        color: rgba(255, 255, 255, 0.5);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
    }

    #bordadoOffcanvas .offcanvas-title {
        color: #fff !important;
        font-size: 0.88rem;
    }

    .bordado-oc-color-badge {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.74rem;
        color: rgba(255, 255, 255, 0.65);
        margin-top: 2px;
    }

    .bordado-oc-color-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, 0.28);
        display: inline-block;
        flex-shrink: 0;
    }

    .bordado-oc-search {
        background: #f8f9fa;
        border-bottom: 1px solid rgba(30, 60, 114, 0.08);
        flex-shrink: 0;
    }

    .bordado-oc-search .bordado-oc-search-icon {
        background: rgba(30, 60, 114, 0.08);
        border-color: rgba(30, 60, 114, 0.2);
        color: #1e3c72;
    }

    .bordado-oc-search .form-control {
        border-color: rgba(30, 60, 114, 0.2);
    }

    .bordado-oc-hint {
        font-size: 0.75rem;
        color: #6c7a94;
    }

    .bordado-oc-section-label {
        font-size: 0.69rem;
        font-weight: 700;
        color: #5a6a85;
        text-transform: uppercase;
        letter-spacing: 0.045em;
    }

    .bordado-oc-divider {
        border-color: rgba(30, 60, 114, 0.1);
    }

    .bordado-oc-footer {
        padding: 0.85rem 1rem;
        background: #fff;
        border-top: 1px solid rgba(30, 60, 114, 0.1);
        box-shadow: 0 -4px 18px rgba(0, 0, 0, 0.07);
        flex-shrink: 0;
    }

    .bordado-oc-recargo-value {
        color: var(--atlantico-dark-blue);
        font-size: 1.05rem;
    }

    /* Dark mode — modal bordados */
    [data-bs-theme="dark"] #bordadoOffcanvas .modal-body {
        background: #1a2035;
    }

    [data-bs-theme="dark"] .bordado-oc-search {
        background: #151c2e;
        border-bottom-color: rgba(255, 255, 255, 0.08);
    }

    [data-bs-theme="dark"] .bordado-oc-search .form-control {
        background: #1e2840;
        color: #c8cfe0;
        border-color: rgba(255, 255, 255, 0.12);
    }

    [data-bs-theme="dark"] .bordado-oc-footer {
        background: #1a2035;
        border-top-color: rgba(255, 255, 255, 0.08);
    }

    [data-bs-theme="dark"] .ubicacion-std-row,
    [data-bs-theme="dark"] .ubicacion-personalizada-row {
        background: #1e2840;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
</style>
<script>
    // Vigencia de precios por defecto (config del sistema). La fecha_validez
    // de cada cotización es la que manda; esto solo pre-siembra el default.
    window.COT_DIAS_VIGENCIA = {{ (int) \App\Models\Cotizacion::diasVigencia() }};

    // Fecha en formato YYYY-MM-DD según la hora LOCAL del navegador. No usar
    // toISOString() (devuelve UTC): en zonas con offset negativo (ej. Venezuela
    // UTC-4) al crear en la tarde/noche la fecha UTC ya es el día siguiente y la
    // emisión/validez quedaba corrida un día.
    window.cotFechaLocalISO = function (d) {
        d = (d instanceof Date && !isNaN(d.getTime())) ? d : new Date();
        var y = d.getFullYear();
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + day;
    };

    // Setea #fecha-validez-field a base + días y sincroniza los chips de
    // "Validez rápida" (activa el que coincida con los días indicados).
    window.cotSeedValidez = function (baseIso, dias) {
        var base = baseIso ? new Date(baseIso + 'T00:00:00') : new Date();
        if (isNaN(base.getTime())) base = new Date();
        base.setDate(base.getDate() + dias);
        $('#fecha-validez-field').val(window.cotFechaLocalISO(base));
        $('.cot-date-chip').removeClass('is-active')
            .filter('[data-days="' + dias + '"]').addClass('is-active');
    };

    // Validación onblur: fecha_validez debe ser >= fecha_cotizacion
    $(document).on('blur', '#fecha-validez-field, input[name="fecha_validez"]', function () {
        let validezVal = $(this).val();
        let cotizacionVal = $('#fecha-cotizacion-field').val() || $('input[name="fecha_cotizacion"]').val();
        if (validezVal && cotizacionVal) {
            if (validezVal < cotizacionVal) {
                marcarInvalido($(this), 'La fecha de validez no puede ser anterior a la fecha de cotización.');
            } else {
                marcarValido($(this));
            }
        } else {
            limpiarValidacion($(this));
        }
    });

    $(document).ready(function () {
        // === FUNCIÓN GLOBAL: Capitalizar solo la primera letra ===
        function capitalizeFirstLetter(str) {
            if (!str) return str;
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        // === LAYOUT DINÁMICO DE CLIENTE EN COTIZACIÓN ===
        function aplicarLayoutCliente(prefix, nombre, apellido) {
            if (prefix === 'J-' || prefix === 'G-') {
                $('#block-cot-nombre').addClass('d-none');
                $('#block-cot-apellido').addClass('d-none');
                $('#block-cot-razon-social').removeClass('d-none');
                $('#cliente-nombre-field').val(nombre);
                $('#cliente-apellido-field').val('');
                $('#cliente-razon-social-display').val(nombre);
            } else {
                $('#block-cot-nombre').removeClass('d-none');
                $('#block-cot-apellido').removeClass('d-none');
                $('#block-cot-razon-social').addClass('d-none');
                $('#cliente-nombre-field').val(nombre);
                $('#cliente-apellido-field').val(apellido || '');
            }
        }

        // Resetear layout de cliente al abrir el modal en modo agregar
        document.getElementById('showModal').addEventListener('show.bs.modal', function () {
            if (!$('#id-field').val()) {
                $('#block-cot-nombre').removeClass('d-none');
                $('#block-cot-apellido').removeClass('d-none');
                $('#block-cot-razon-social').addClass('d-none');
            }
        });

        function formatMoney(value) {
            var amount = Number(value || 0);
            return '$' + amount.toLocaleString('es-VE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Equivalente en Bs a la tasa BCV del día, para acompañar montos en $.
        // Devuelve '' si no hay tasa disponible — el span queda vacío y no estorba.
        function bsApprox(usd) {
            var txt = (typeof window.bsEquivalente === 'function') ? window.bsEquivalente(usd) : null;
            return txt ? ('≈ ' + txt) : '';
        }

        // Aplicar a campos de dirección al perder foco
        $(document).on('blur', '#direccion-field, #direccion-field-cliente, [name="direccion"]', function () {
            var val = $(this).val();
            if (val && val.length > 0) {
                $(this).val(capitalizeFirstLetter(val));
            }
        });

        function debounce(func, wait) {
            let timeout;
            return function () {
                const context = this;
                const args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(function () {
                    func.apply(context, args);
                }, wait);
            };
        }

        function updateFilterBadge() {
            let count = 0;
            const ordenValue = $('#filter-orden').val();
            if ($('#filter-estado').val()) {
                count++;
            }
            if ($('#filter-fecha').val()) {
                count++;
            }
            if (ordenValue && ordenValue !== 'recientes') {
                count++;
            }
            $('#active-filter-count').text(count).toggleClass('d-none', count === 0);
        }

        var table = $('#cotizaciones-table').DataTable({
            responsive: true,
            autoWidth: false,
            dom: 'rtip', /* Ocultar buscador (f) y selector de longitud (l) para máxima limpieza */
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('cotizaciones.data') }}",
                data: function (d) {
                    d.filter_estado = $('#filter-estado').val();
                    d.filter_fecha = $('#filter-fecha').val();
                    d.filter_orden = $('#filter-orden').val();
                }
            },
            columns: [
                {
                    data: 'id', name: 'id', title: 'Nro.', className: 'align-middle', width: '15%',
                    render: function (data) {
                        return `<div class="ped-cell">
                            <span class="ped-cell-ic"><i class="ri-file-list-3-line"></i></span>
                            <span class="ped-cell-txt"><span class="ped-cell-eyebrow">Cotización</span><span class="ped-cell-num">#${data}</span></span>
                        </div>`;
                    }
                },
                { data: 'cliente_nombre', name: 'cliente_nombre', width: '25%' },
                { data: 'fecha_cotizacion', name: 'fecha_cotizacion', width: '12%' },
                {
                    data: 'total',
                    name: 'total',
                    width: '12%',
                    render: $.fn.dataTable.render.number(',', '.', 2, '$')
                },
                {
                    data: 'estado',
                    name: 'estado',
                    width: '12%',
                    className: 'text-center',
                    render: function (data, type, row) {
                        var estadoClasses = {
                            'Pendiente': 'status-pendiente',
                            'Aprobada': 'status-aprobada',
                            'Convertida': 'badge-soft-info',
                            'Cancelada': 'status-cancelado',
                            'Vencida': 'status-cancelado'
                        };
                        var estadoIcons = {
                            'Pendiente': 'ri-time-line',
                            'Aprobada': 'ri-check-double-line',
                            'Convertida': 'ri-exchange-line',
                            'Cancelada': 'ri-close-circle-line',
                            'Vencida': 'ri-alarm-warning-line'
                        };
                        var badgeClass = estadoClasses[data] || '';
                        var icon = estadoIcons[data] || 'ri-question-line';

                        var badge = '<span class="badge badge-status ' + badgeClass + ' rounded-pill"><i class="' + icon + ' me-1"></i>' + data + '</span>';

                        var puedeConvertir = {{ tienePermiso('cotizaciones.convertir') ? 'true' : 'false' }};

                        // Sin permiso de conversión/cambio de estado, o ya convertida: solo badge
                        if (!puedeConvertir || data === 'Convertida') {
                            return badge;
                        }

                        // Construir opciones del dropdown
                        var opciones = '';

                        if (data === 'Pendiente') {
                            opciones += '<li><a class="dropdown-item change-status-btn" href="#" data-id="' + row.id + '" data-status="Aprobada"><i class="ri-check-double-line text-success me-2"></i>Aprobar</a></li>';
                            opciones += '<li><a class="dropdown-item change-status-btn" href="#" data-id="' + row.id + '" data-status="Cancelada"><i class="ri-close-circle-line text-danger me-2"></i>Cancelar</a></li>';
                        } else if (data === 'Aprobada') {
                            opciones += '<li><a class="dropdown-item change-status-btn" href="#" data-id="' + row.id + '" data-status="Pendiente"><i class="ri-time-line text-warning me-2"></i>Volver a Pendiente</a></li>';
                            opciones += '<li><a class="dropdown-item change-status-btn" href="#" data-id="' + row.id + '" data-status="Cancelada"><i class="ri-close-circle-line text-danger me-2"></i>Cancelar</a></li>';
                        } else if (data === 'Cancelada' || data === 'Vencida') {
                            opciones += '<li><a class="dropdown-item change-status-btn" href="#" data-id="' + row.id + '" data-status="Pendiente"><i class="ri-time-line text-warning me-2"></i>Reactivar (Pendiente)</a></li>';
                        }

                        if (opciones === '') return badge;

                        return `
                            <div class="dropdown">
                                <a href="#" role="button" id="dropdownMenuLink${row.id}" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none;">
                                    ${badge} <i class="ri-arrow-down-s-line ms-1 text-muted"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink${row.id}">
                                    ${opciones}
                                </ul>
                            </div>
                        `;
                    }
                },
                {
                    data: 'id',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        var puedeConvertir = {{ tienePermiso('cotizaciones.convertir') ? 'true' : 'false' }};
                        var puedeGestionar = {{ tienePermiso('cotizaciones.gestionar') ? 'true' : 'false' }};

                        // Ver inline
                        var sVer = `<button class="btn btn-sm btn-soft-info view-btn" data-id="${data}" title="Ver"><i class="ri-eye-fill"></i></button>`;

                        // Items del menú ⋮
                        var items = '';
                        var hadItems = false;

                        // Convertir a Pedido (solo si está Aprobada)
                        if (row.estado === 'Aprobada' && puedeConvertir) {
                            items += `<li><button type="button" class="dropdown-item act-item act-primary convert-to-pedido-btn" data-id="${data}"><span class="act-ic"><i class="ri-exchange-line"></i></span>Convertir a Pedido</button></li>`;
                            hadItems = true;
                        }

                        // Reactivar (solo si está Vencida)
                        if (puedeConvertir && row.estado === 'Vencida') {
                            items += `<li><button type="button" class="dropdown-item act-item act-primary reactivar-btn" data-id="${data}"><span class="act-ic"><i class="ri-refresh-line"></i></span>Reactivar cotización</button></li>`;
                            hadItems = true;
                        }

                        // Editar y Eliminar (solo si NO está Convertida ni Cancelada ni Vencida)
                        if (puedeGestionar && row.estado !== 'Convertida' && row.estado !== 'Cancelada' && row.estado !== 'Vencida') {
                            items += `<li><button type="button" class="dropdown-item act-item act-edit edit-btn" data-id="${data}"><span class="act-ic"><i class="ri-pencil-fill"></i></span>Editar</button></li>`;
                            items += `<li><button type="button" class="dropdown-item act-item act-del remove-btn" data-id="${data}"><span class="act-ic"><i class="ri-delete-bin-fill"></i></span>Eliminar</button></li>`;
                            hadItems = true;
                        }

                        // Separador antes del PDF (solo si hubo items antes)
                        if (hadItems) {
                            items += `<li><hr class="dropdown-divider"></li>`;
                        }

                        // PDF como enlace
                        items += `<li><a class="dropdown-item act-item act-pdf" href="/cotizaciones/${data}/pdf" target="_blank"><span class="act-ic"><i class="ri-file-pdf-fill"></i></span>Ver PDF</a></li>`;

                        var menu = `
                            <div class="dropdown d-inline-block">
                              <button class="btn btn-sm btn-soft-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Más acciones"><i class="ri-more-2-fill"></i></button>
                              <ul class="dropdown-menu dropdown-menu-end actions-menu">${items}</ul>
                            </div>
                        `;

                        return `<div class="d-flex gap-1 justify-content-center align-items-center">${sVer}${menu}</div>`;
                    }
                }
            ],
            order: [],
            ordering: false,
            language: lenguajeData
        });

        $('#filters-collapse-body')
            .on('show.bs.collapse', function () {
                $('.navy-filter-header').removeClass('is-collapsed');
            })
            .on('hidden.bs.collapse', function () {
                $('.navy-filter-header').addClass('is-collapsed');
            });

        $('#custom-search-input').on('input', debounce(function () {
            table.search(this.value).draw();
        }, 300));

        $('.navy-filter-select').on('change', function () {
            table.ajax.reload();
            updateFilterBadge();
        });

        $('#btn-clear-filters').on('click', function () {
            $('#filter-estado').val('');
            $('#filter-fecha').val('');
            $('#filter-orden').val('recientes');
            $('.navy-filter-select').trigger('change');
            $('#custom-search-input').val('');
            table.search('').draw();
            updateFilterBadge();
        });

        updateFilterBadge();

        // Ajustar columnas cuando se redimensiona la ventana
        $(window).on('resize', function () {
            table.columns.adjust();
        });
        // Ajustar después de carga inicial
        setTimeout(function () {
            table.columns.adjust();
        }, 100);

        // === Lógica de productos (Adaptada de Pedidos) ===
        var products = @json($productos);
        // Catálogo = Tipo de Producto (FEAT-003). El grid del catálogo se arma desde los Tipos,
        // no desde filas producto: el cliente elige tela + variaciones al cotizar.
        var catalogoTipos = @json($tiposProducto ?? []);
        var productItemIndex = 0;
        var productosModalCotizacion = null;
        var currentProductIndex = null; // Para saber qué items editar si fuera el caso

        // Inicializar Modal y cargar datos
        try {
            productosModalCotizacion = new bootstrap.Modal(document.getElementById('productosModalCotizacion'));
            cargarTiposEnFiltroCotizacion();
        } catch (e) {
            console.error('Error inicializando modal cotización:', e);
        }

        // Eventos del Modal
        $('#buscarProductoModalCotizacion').on('keyup', function () {
            renderizarProductosModalCotizacion($(this).val(), $('#filtroTipoProductoCotizacion').val());
        });
        $('#filtroTipoProductoCotizacion').on('change', function () {
            renderizarProductosModalCotizacion($('#buscarProductoModalCotizacion').val(), $(this).val());
        });

        // Botón "Agregar Otro Producto" (Añade tarjeta vacía)
        $('#add-producto-item').on('click', function () {
            addProductItem(); // Agrega tarjeta vacía
            // Opcional: Scrollear hacia el nuevo item
            // var newDetails = $('#productos-container').children().last();
            // if(newDetails.length) newDetails[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        });

        function cargarTiposEnFiltroCotizacion() {
            var tipos = [];
            if (typeof products !== 'undefined' && products) {
                products.forEach(function (p) {
                    if (p.tipo_producto && !tipos.find(t => t.id === p.tipo_producto.id)) {
                        tipos.push(p.tipo_producto);
                    }
                });
            }
            var select = $('#filtroTipoProductoCotizacion');
            select.find('option:not(:first)').remove();
            tipos.forEach(function (tipo) {
                select.append('<option value="' + tipo.id + '">' + tipo.nombre + '</option>');
            });
        }

        function renderizarProductosModalCotizacion(filtro, tipoId) {
            filtro = filtro || '';
            tipoId = tipoId || '';
            var tbody = $('#productosModalCotizacionBody');
            tbody.empty();

            if (typeof products === 'undefined' || !products) {
                tbody.append('<tr><td colspan="6" class="text-center text-danger py-4"><i class="ri-error-warning-line fs-2 d-block mb-2"></i>Error: No se pudieron cargar los productos.</td></tr>');
                return;
            }

            if (products.length === 0) {
                tbody.append('<tr><td colspan="6" class="text-center text-muted py-4"><i class="ri-inbox-line fs-2 d-block mb-2"></i>No hay productos registrados en el sistema.</td></tr>');
                return;
            }

            var productosFiltrados = products.filter(function (p) {
                var matchFiltro = true;
                var matchTipo = true;
                if (filtro) {
                    var busqueda = filtro.toLowerCase();
                    var codigo = (p.codigo || '').toLowerCase();
                    var modelo = (p.modelo || '').toLowerCase();
                    var tipo = p.tipo_producto ? p.tipo_producto.nombre.toLowerCase() : '';
                    matchFiltro = codigo.startsWith(busqueda) || modelo.startsWith(busqueda) || tipo.startsWith(busqueda);
                }
                if (tipoId) {
                    matchTipo = p.tipo_producto && p.tipo_producto.id == tipoId;
                }
                return matchFiltro && matchTipo;
            });

            if (productosFiltrados.length === 0) {
                tbody.append('<tr><td colspan="6" class="text-center text-muted py-4"><i class="ri-search-2-line fs-2 d-block mb-2"></i>No se encontraron coincidencias.</td></tr>');
                return;
            }

            productosFiltrados.forEach(function (p) {
                var tipoNombre = p.tipo_producto ? p.tipo_producto.nombre : 'Sin tipo';
                var imgHtml = p.imagen ? '<img src="' + p.imagen + '" class="producto-img-thumb" alt="">' : '<span class="text-muted text-center d-block" style="width:40px; font-size:10px;">Sin IMG</span>';
                var row = '<tr data-producto-id="' + p.id + '" data-precio="' + p.precio_base + '">' +
                    '<td>' + imgHtml + '</td>' +
                    '<td><span class="badge bg-dark">' + (p.codigo || '-') + '</span></td>' +
                    '<td><span class="badge bg-primary">' + tipoNombre + '</span></td>' +
                    '<td>' + p.modelo + '</td>' +
                    '<td class="text-success fw-bold text-end">' + formatMoney(parseFloat(p.precio_base || 0)) + '</td>' +
                    '<td class="text-center"><button type="button" class="btn btn-sm btn-atlantico-brand select-producto-btn-cotizacion" data-id="' + p.id + '"><i class="ri-check-line"></i></button></td>' +
                    '</tr>';
                tbody.append(row);
            });
        }

        // Asegurar renderizado al abrir el modal por cualquier vía
        document.getElementById('productosModalCotizacion').addEventListener('shown.bs.modal', function () {
            renderizarProductosModalCotizacion($('#buscarProductoModalCotizacion').val(), $('#filtroTipoProductoCotizacion').val());
        });

        // Ajuste inteligente de backdrop: Solo limpiar si no queda otro modal abierto
        document.getElementById('productosModalCotizacion').addEventListener('hidden.bs.modal', function () {
            // Si el modal principal de cotización está abierto, NO quitar la clase modal-open del body
            if ($('#showModal').hasClass('show')) {
                $('body').addClass('modal-open');
                // Bootstrap debería encargarse de quitar el backdrop del modal hijo automáticamente.
                // Si quedaran backdrops residuales, eliminamos solo los extra (manteniendo 1)
                var backdrops = $('.modal-backdrop');
                if (backdrops.length > 1) {
                    // Dejar solo uno
                    backdrops.not(backdrops.first()).remove();
                }
            } else {
                // Si no hay modal padre, limpiar todo
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                $('body').css('overflow', 'auto');
                $('body').css('padding-right', '');
            }
        });

        // ============================================================
        // === LÓGICA DEL MODAL DE LOGOS (Catálogo de Logos) ===
        // ============================================================

        // Logos cargados desde servidor (mismo patrón que products)
        var logos = @json($logos);
        var logoModal = null;
        var currentLogoInput = null; // Referencia al nombre-logo-input de la fila activa

        // Estándar de negocio: máximo de bordados por producto (suma de cantidades
        // de todas las ubicaciones), configurable en /configuracion →
        // cotizaciones.max_bordados_producto. El backend valida lo mismo; este tope
        // es la primera línea de defensa en el configurador.
        var MAX_BORDADOS = parseInt(@json($maxBordadosProducto ?? 6), 10) || 6;

        // Inicializar modal de logos
        try {
            logoModal = new bootstrap.Modal(document.getElementById('logoSearchModal'));
        } catch (e) {
            console.error('Error inicializando modal de logos:', e);
        }

        // Filtrar y renderizar logos en la tabla del modal
        function renderizarLogosModal(filtro) {
            filtro = (filtro || '').toLowerCase().trim();
            var tbody = $('#logoModalBody');
            tbody.empty();

            if (typeof logos === 'undefined' || !logos) {
                tbody.append('<tr><td colspan="3" class="text-center text-danger py-4"><i class="ri-error-warning-line fs-2 d-block mb-2"></i>Error: No se pudieron cargar los logos.</td></tr>');
                $('#logoModalCount').text('0 logos');
                return;
            }

            if (logos.length === 0) {
                tbody.append('<tr><td colspan="3" class="text-center text-muted py-4"><i class="ri-inbox-line fs-2 d-block mb-2"></i>No hay logos registrados en el sistema.<br><small>Ejecute el LogoSeeder para cargar el catálogo.</small></td></tr>');
                $('#logoModalCount').text('0 logos');
                return;
            }

            var logosFiltrados = logos.filter(function (l) {
                if (!filtro) return true;
                return (l.name || '').toLowerCase().includes(filtro) ||
                    (l.original_filename || '').toLowerCase().includes(filtro);
            });

            $('#logoModalCount').text(logosFiltrados.length + ' logo' + (logosFiltrados.length !== 1 ? 's' : ''));

            if (logosFiltrados.length === 0) {
                tbody.append('<tr><td colspan="3" class="text-center text-muted py-4"><i class="ri-search-2-line fs-2 d-block mb-2"></i>No se encontraron coincidencias.</td></tr>');
                return;
            }

            logosFiltrados.forEach(function (l) {
                var safeName = $('<div>').text(l.name).html();
                var safeFile = $('<div>').text(l.original_filename).html();
                var row = '<tr data-logo-id="' + l.id + '" data-logo-name="' + safeName + '">' +
                    '<td style="width:65%">' +
                    '<i class="ri-threads-line logo-row-icon me-2"></i>' +
                    '<span class="fw-semibold">' + safeName + '</span>' +
                    '</td>' +
                    '<td class="logo-filename-cell" style="width:25%" title="' + safeFile + '">' + safeFile + '</td>' +
                    '<td class="text-center" style="width:10%">' +
                    '<button type="button" class="btn btn-sm btn-atlantico-brand select-logo-btn px-2 py-1"' +
                    ' data-logo-id="' + l.id + '" data-logo-name="' + safeName + '">' +
                    '<i class="ri-check-line"></i>' +
                    '</button>' +
                    '</td>' +
                    '</tr>';
                tbody.append(row);
            });
        }

        // Preparar capas antes de animar el modal de logos (evita parpadeos)
        document.getElementById('logoSearchModal').addEventListener('show.bs.modal', function () {
            var zIndex = 1085;
            if ($('#bordadoOffcanvas').hasClass('show')) {
                zIndex = 1105;
            } else if ($('#showModal').hasClass('show')) {
                zIndex = 1095;
            }

            $('#logoSearchModal').css('z-index', zIndex);
            window.requestAnimationFrame(function () {
                var $lastBackdrop = $('.modal-backdrop').last();
                if ($lastBackdrop.length) {
                    $lastBackdrop.css('z-index', zIndex - 1).addClass('logo-modal-backdrop');
                }
            });
        });

        // Renderizar al abrir el modal de logos
        document.getElementById('logoSearchModal').addEventListener('shown.bs.modal', function () {

            $('#buscarLogoModal').val('').focus();
            renderizarLogosModal('');
        });

        // Limpieza de backdrop al cerrar el modal de logos (mismo patrón que productos)
        document.getElementById('logoSearchModal').addEventListener('hidden.bs.modal', function () {
            $('#logoSearchModal').css('z-index', '');
            $('.modal-backdrop.logo-modal-backdrop').removeClass('logo-modal-backdrop').css('z-index', '');

            if ($('#showModal').hasClass('show')) {
                $('body').addClass('modal-open');
                var backdrops = $('.modal-backdrop');
                if (backdrops.length > 1) {
                    backdrops.not(backdrops.first()).remove();
                }
            } else {
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                $('body').css('overflow', 'auto');
                $('body').css('padding-right', '');
            }
        });

        // Filtrado en tiempo real al escribir
        $('#buscarLogoModal').on('keyup', function () {
            renderizarLogosModal($(this).val());
        });

        // Abrir modal de logos desde inputs de bordado (y compatibilidad legacy)
        $(document).on('click', '.buscar-logo-trigger, .bordado-logo-picker', function (e) {
            e.preventDefault();
            var $targetGroup = $(this).closest('.input-group');
            currentLogoInput = $targetGroup.find('.nombre-logo-input, .bordado-logo-input').first();
            if (!currentLogoInput || !currentLogoInput.length || currentLogoInput.prop('disabled')) {
                currentLogoInput = null;
                return;
            }
            if (logoModal) logoModal.show();
        });

        // Seleccionar logo por clic en botón ✓
        $(document).on('click', '.select-logo-btn', function (e) {
            e.preventDefault();
            var logoId = $(this).data('logo-id');
            var logoName = $(this).data('logo-name');
            seleccionarLogo(logoId, logoName);
        });

        // Seleccionar logo por doble clic en fila
        $(document).on('dblclick', '#logoSearchModalTable tbody tr', function (e) {
            e.preventDefault();
            var logoId = $(this).data('logo-id');
            var logoName = $(this).data('logo-name');
            if (logoId && logoName) seleccionarLogo(logoId, logoName);
        });

        // Función central de selección de logo
        function seleccionarLogo(logoId, logoName) {
            if (!logoId || !logoName || !currentLogoInput) return;

            currentLogoInput.val(logoName).data('logo-id', logoId);

            var ubicacionRow = currentLogoInput.closest('.ubicacion-std-row, .ubicacion-personalizada-row');
            if (ubicacionRow && ubicacionRow.length) {
                if (ubicacionRow.hasClass('ubicacion-std-row')) {
                    actualizarEstadoUbicacionStdRow(ubicacionRow);
                } else {
                    actualizarEstadoUbicacionPersonalizadaRow(ubicacionRow);
                }
                actualizarResumenRecargoModal();
            }

            // Cerrar modal de logos
            if (logoModal) logoModal.hide();

            // Mantener modal padre abierto (mismo patrón que productos)
            if ($('#showModal').hasClass('show')) {
                $('body').addClass('modal-open');
                var backdrops = $('.modal-backdrop');
                if (backdrops.length > 1) {
                    backdrops.not(backdrops.first()).remove();
                }
            }

            currentLogoInput = null;
        }

        // ============================================================
        // === FIN LÓGICA MODAL DE LOGOS ===
        // ============================================================

        // ============================================================
        // === INICIO LÓGICA MODAL DE COLORES ===
        // ============================================================
        var coloresArray = [];
        var colorModal = null;
        var currentColorInput = null;  // referencia al input que disparó el modal

        // Inicializar modal de colores
        if (document.getElementById('colorCatalogoModal')) {
            colorModal = new bootstrap.Modal(document.getElementById('colorCatalogoModal'));
        }

        // Cargar colores via AJAX al iniciar
        $.get("{{ route('colores.data') }}", function (data) {
            coloresArray = data;
        });

        // Renderizar swatch grid agrupado por categoría
        function renderizarColoresModal(filtro) {
            var grid = $('#coloresSwatchGrid');
            grid.empty();
            filtro = (filtro || '').toLowerCase().trim();

            // Agrupar colores por grupo
            var grupos = {};
            coloresArray.forEach(function (c) {
                if (filtro && c.nombre.toLowerCase().indexOf(filtro) === -1) return;
                var g = c.grupo || 'Otros';
                if (!grupos[g]) grupos[g] = [];
                grupos[g].push(c);
            });

            if (Object.keys(grupos).length === 0) {
                grid.html('<div class="text-center text-muted py-4"><i class="ri-palette-line d-block" style="font-size:2rem;opacity:0.3;"></i><small>No se encontraron colores</small></div>');
                return;
            }

            // Renderizar cada grupo
            Object.keys(grupos).forEach(function (grupoNombre) {
                grid.append('<div class="color-grupo-header">' + grupoNombre + '</div>');
                var items = grupos[grupoNombre];
                items.forEach(function (c) {
                    // Bordes especiales para blanco/crema (se pierden contra fondo blanco)
                    var borderStyle = (c.hex_referencial === '#FFFFFF' || c.hex_referencial === '#FFFDD0')
                        ? 'border: 2px solid #ddd;'
                        : 'border: 2px solid rgba(0,0,0,0.12);';
                    grid.append(
                        '<div class="color-swatch-item select-color-btn" data-color-id="' + c.id + '" data-color-nombre="' + c.nombre + '" data-color-hex="' + c.hex_referencial + '">' +
                        '  <span class="color-swatch-circle" style="background-color:' + c.hex_referencial + '; ' + borderStyle + '"></span>' +
                        '  <span class="fw-medium">' + c.nombre + '</span>' +
                        '</div>'
                    );
                });
            });
        }

        // Abrir modal y focus en el buscador
        document.getElementById('colorCatalogoModal').addEventListener('shown.bs.modal', function () {
            $('#buscarColorModal').val('').trigger('focus');
            renderizarColoresModal('');
        });

        // Limpiar backdrop al cerrar
        document.getElementById('colorCatalogoModal').addEventListener('hidden.bs.modal', function () {
            if ($('#showModal').hasClass('show')) {
                $('body').addClass('modal-open');
                var backdrops = $('.modal-backdrop');
                if (backdrops.length > 1) {
                    backdrops.not(backdrops.first()).remove();
                }
            }
        });

        // Filtrado en tiempo real
        $('#buscarColorModal').on('keyup', function () {
            renderizarColoresModal($(this).val());
        });

        // Abrir modal de colores desde la fila del producto
        $('#productos-container').on('click', '.buscar-color-trigger', function (e) {
            e.preventDefault();
            currentColorInput = $(this).closest('.input-group').find('.color-display');
            if (colorModal) colorModal.show();
        });

        // Seleccionar color por clic en swatch
        $(document).on('click', '.select-color-btn', function (e) {
            e.preventDefault();
            var id = $(this).data('color-id');
            var nombre = $(this).data('color-nombre');
            var hex = $(this).data('color-hex');
            seleccionarColor(id, nombre, hex);
        });

        // Función central de selección de color
        function seleccionarColor(id, nombre, hex) {
            if (!nombre || !currentColorInput) return;

            // Llenar el input display con el nombre del color
            currentColorInput.val(nombre);

            // Actualizar hidden input con el ID
            currentColorInput.closest('.input-group').find('.color-id-input').val(id);

            // Actualizar el dot indicador con el hex del color
            var dotSpan = currentColorInput.closest('.input-group').find('.color-dot-indicator');
            if (dotSpan.length) {
                dotSpan.css({
                    'background-color': hex,
                    'border': (hex === '#FFFFFF' || hex === '#FFFDD0')
                        ? '1.5px solid #ddd' : '1.5px solid rgba(0,0,0,0.15)'
                });
            }

            // Cerrar modal
            if (colorModal) colorModal.hide();

            // Mantener modal padre abierto
            if ($('#showModal').hasClass('show')) {
                $('body').addClass('modal-open');
                var backdrops = $('.modal-backdrop');
                if (backdrops.length > 1) {
                    backdrops.not(backdrops.first()).remove();
                }
            }

            currentColorInput = null;
        }

        // ============================================================
        // === FIN LÓGICA MODAL DE COLORES ===
        // ============================================================

        // ============================================================
        // === INICIO LÓGICA MODAL DE TALLAS ===
        // ============================================================
        var tallaModal = null;
        var tallaModalEl = document.getElementById('tallaCatalogoModal');
        var currentTallaInput = null;
        var currentTallaValueInput = null;

        if (tallaModalEl) {
            tallaModal = new bootstrap.Modal(tallaModalEl);
        }

        // Abrir modal desde un item existente (EDITAR/CAMBIAR PRODUCTO)
        $('#productos-container').on('click', '.producto-selector-trigger', function () {
            currentProductIndex = $(this).closest('.product-item').data('product-index');
            $('#buscarProductoModalCotizacion').val('');
            $('#filtroTipoProductoCotizacion').val('');
            renderizarProductosModalCotizacion('', '');
            productosModalCotizacion.show();
        });

        // Seleccionar producto desde la tabla (click botón o doble click fila)
        $(document).on('click', '.select-producto-btn-cotizacion', function () {
            // Prevenir comportamiento de submit si lo hubiera
            seleccionarProductoCotizacion($(this).data('id'));
        });

        $(document).on('dblclick', '#productosModalCotizacionTable tbody tr', function (e) {
            e.preventDefault(); // Prevenir cualquier acción por defecto
            var productoId = $(this).data('producto-id');
            if (productoId) seleccionarProductoCotizacion(productoId);
        });

        function seleccionarProductoCotizacion(productoId) {
            var producto = products.find(function (p) { return p.id == productoId; });
            if (!producto) return;

            // Función helper para cerrar modal de forma segura respetando modal padre
            var cerrarModalSeguro = function () {
                if (productosModalCotizacion) productosModalCotizacion.hide();

                // Si el modal padre está abierto, mantener su estado
                if ($('#showModal').hasClass('show')) {
                    $('body').addClass('modal-open');
                    var backdrops = $('.modal-backdrop');
                    if (backdrops.length > 1) {
                        backdrops.not(backdrops.first()).remove();
                    }
                } else {
                    // Limpieza total si no hay padre
                    $('body').removeClass('modal-open');
                    $('body').css('overflow', '');
                    $('body').css('padding-right', '');
                    $('.modal-backdrop').remove();
                }
            };

            if (currentProductIndex !== null) {
                // EDITAR ITEM EXISTENTE
                var card = $(`.product-item[data-product-index="${currentProductIndex}"]`);
                var tipoNombre = producto.tipo_producto ? producto.tipo_producto.nombre : 'Sin tipo';
                var displayName = (producto.codigo || '') + ' - ' + tipoNombre;

                // Actualizar el dropdown con el producto seleccionado
                card.find('.producto-dropdown').val(producto.id);
                card.find('.producto-id-input').val(producto.id);
                card.find('.precio-unitario-input').val(producto.precio_base);

                // Cerrar modal
                cerrarModalSeguro();
                calculateCotizacionTotals();

            } else {
                // NUEVO ITEM
                // Cerrar modal antes de agregar
                cerrarModalSeguro();
                // Agregar item
                addProductItem(producto.id, 1, producto.precio_base, '', false, '', '', '', defaultGeneroId(), []);
                // Recalcular
                calculateCotizacionTotals();
            }
        }

        var tallasArray = [];

        // Catálogo de género de prenda (Dama/Caballero/Unisex), inyectado desde el
        // View Composer (set estable, cacheado). Lo consume el configurador para el
        // cruce talla × género.
        var generosArray = @json($generosCatalogo ?? []);

        function getGenerosArray() {
            return Array.isArray(generosArray) ? generosArray : [];
        }

        function getGeneroNombre(generoId) {
            if (!generoId) return '';
            var g = getGenerosArray().find(function (x) { return x.id == generoId; });
            return g ? (g.etiqueta || g.nombre) : '';
        }

        // Género por defecto (Unisex) para rutas de alta rápida sin configurador.
        function defaultGeneroId() {
            var arr = getGenerosArray();
            var uni = arr.find(function (g) { return String(g.nombre || '').toLowerCase() === 'unisex'; });
            return uni ? uni.id : (arr[0] ? arr[0].id : '');
        }

        function cargarTallasCatalogo(callback) {
            $.get("{{ route('tallas.data') }}", function (data) {
                tallasArray = Array.isArray(data) ? data : [];
                if (typeof callback === 'function') callback();
            });
        }

        // Cargar tallas via AJAX al iniciar (patrón análogo a colores)
        cargarTallasCatalogo();

        function getTallaLabel(tallaId) {
            if (!tallaId) return '';
            var tallaItem = tallasArray.find(function (t) { return t.id == tallaId; });
            return tallaItem ? (tallaItem.etiqueta || tallaItem.nombre) : '';
        }

        function getColorById(colorId) {
            return coloresArray.find(function (c) { return c.id == colorId; }) || null;
        }

        function renderizarTallasModal(filtro) {
            var grid = $('#tallasCatalogoGrid');
            grid.empty();
            filtro = (filtro || '').toLowerCase().trim();

            var grupos = {};
            tallasArray.forEach(function (item) {
                var value = item.nombre;
                var label = item.etiqueta || item.nombre;
                var labelLc = String(label).toLowerCase();
                var valueLc = String(value).toLowerCase();
                if (filtro && labelLc.indexOf(filtro) === -1 && valueLc.indexOf(filtro) === -1) return;

                var groupName = item.grupo || 'Letras';
                if (!grupos[groupName]) grupos[groupName] = [];
                grupos[groupName].push({ id: item.id, value: value, label: label });
            });

            var groupOrder = ['Única', 'Numéricas', 'Letras'];
            var rendered = false;

            groupOrder.forEach(function (groupName) {
                var items = grupos[groupName];
                if (!items || !items.length) return;

                rendered = true;
                grid.append('<div class="color-grupo-header">' + groupName + '</div>');
                var chipsContainer = $('<div class="mb-2"></div>');

                items.forEach(function (t) {
                    chipsContainer.append(
                        '<button type="button" class="talla-chip-item select-talla-btn" data-talla-id="' + t.id + '" data-talla-value="' + t.value + '">' + t.label + '</button>'
                    );
                });

                grid.append(chipsContainer);
            });

            if (!rendered) {
                grid.html('<div class="text-center text-muted py-4"><i class="ri-t-shirt-line d-block" style="font-size:2rem;opacity:0.3;"></i><small>No se encontraron tallas</small></div>');
            }
        }

        if (tallaModalEl) {
            tallaModalEl.addEventListener('shown.bs.modal', function () {
                $('#buscarTallaModal').val('').trigger('focus');

                if (!tallasArray.length) {
                    $('#tallasCatalogoGrid').html('<div class="text-center text-muted py-4"><small>Cargando tallas...</small></div>');
                    cargarTallasCatalogo(function () {
                        renderizarTallasModal('');
                    });
                    return;
                }

                renderizarTallasModal('');
            });

            tallaModalEl.addEventListener('hidden.bs.modal', function () {
                if ($('#showModal').hasClass('show')) {
                    $('body').addClass('modal-open');
                    var backdrops = $('.modal-backdrop');
                    if (backdrops.length > 1) {
                        backdrops.not(backdrops.first()).remove();
                    }
                }
            });
        }

        $('#buscarTallaModal').on('keyup', function () {
            renderizarTallasModal($(this).val());
        });

        $('#productos-container').on('click', '.buscar-talla-trigger', function (e) {
            e.preventDefault();
            var group = $(this).closest('.input-group');
            currentTallaInput = group.find('.talla-input-display');
            currentTallaValueInput = group.find('.talla-input-value');
            if (tallaModal) tallaModal.show();
        });

        $(document).on('click', '.select-talla-btn', function (e) {
            e.preventDefault();
            seleccionarTalla($(this).data('talla-id'));
        });

        function seleccionarTalla(tallaId) {
            if (!tallaId || !currentTallaInput || !currentTallaValueInput) return;

            currentTallaInput.val(getTallaLabel(tallaId));
            currentTallaValueInput.val(tallaId).trigger('change');

            if (tallaModal) tallaModal.hide();

            if ($('#showModal').hasClass('show')) {
                $('body').addClass('modal-open');
                var backdrops = $('.modal-backdrop');
                if (backdrops.length > 1) {
                    backdrops.not(backdrops.first()).remove();
                }
            }

            currentTallaInput = null;
            currentTallaValueInput = null;
        }

        // ============================================================
        // === FIN LÓGICA MODAL DE TALLAS ===
        // ============================================================

        // ============================================================
        // === INICIO LÓGICA MODAL DE UBICACIÓN DE BORDADO ===
        // ============================================================
        var ubicacionModal = null;
        var ubicacionModalEl = document.getElementById('bordadoOffcanvas');
        var currentBordadoCard = null;       // legacy fallback: card DOM ref
        var currentBordadoGroupKey = null;   // fuente de verdad: "productoId|colorId"
        var ubicacionesBordadoArray = [];
        window.cotGroupBordadosState = window.cotGroupBordadosState || {};

        if (ubicacionModalEl) {
            ubicacionModal = new bootstrap.Modal(ubicacionModalEl);
        }

        function cargarUbicacionesBordado(callback) {
            $.get("{{ route('cotizaciones.ubicacionesBordado.data') }}", function (data) {
                ubicacionesBordadoArray = Array.isArray(data) ? data : [];
                if (typeof callback === 'function') callback();
            });
        }

        cargarUbicacionesBordado();

        function getCardBordados($card) {
            var bordados = $card.data('bordados');
            return Array.isArray(bordados) ? bordados : [];
        }

        function setCardBordados($card, bordados) {
            var normalized = Array.isArray(bordados) ? bordados.map(function (item) {
                return {
                    ubicacion_bordado_id: item.ubicacion_bordado_id || null,
                    nombre_aplicado: item.nombre_aplicado || '',
                    logo_id: item.logo_id || null,
                    nombre_logo_aplicado: item.nombre_logo_aplicado || (item.logo ? item.logo.name : '') || '',
                    es_personalizada: !!item.es_personalizada,
                    precio_aplicado: parseFloat(item.precio_aplicado || 0),
                    cantidad: Math.max(1, parseInt(item.cantidad || 1, 10))
                };
            }) : [];

            $card.data('bordados', normalized);
        }

        function getGroupBordados(key) {
            var st = window.cotGroupBordadosState || {};
            return (st[key] && Array.isArray(st[key].bordados)) ? st[key].bordados : [];
        }

        // Bordados efectivos de una card: prioriza cotGroupBordadosState (fuente de verdad,
        // key prodId|colorId); si no hay, usa el data() de la card. Lo usan la grilla y el total.
        function getEffectiveBordadosForCard($card) {
            var prodId = $card.find('.producto-id-input').val();
            var colorId = $card.find('.color-id-input').val() || '';
            var stKey = (prodId || '') + '|' + (colorId || '');
            var st = (window.cotGroupBordadosState && window.cotGroupBordadosState[stKey] &&
                Array.isArray(window.cotGroupBordadosState[stKey].bordados))
                ? window.cotGroupBordadosState[stKey].bordados : null;
            if (st && st.length) return st;
            return getCardBordados($card);
        }

        function setGroupBordados(key, bordados) {
            window.cotGroupBordadosState = window.cotGroupBordadosState || {};
            window.cotGroupBordadosState[key] = { bordados: bordados };
            var parts = String(key).split('|');
            var pid = parts[0];
            var cid = parts[1] || '';
            $('#productos-container .product-item').each(function () {
                var $c = $(this);
                if (String($c.find('.producto-id-input').val()) === String(pid) &&
                    String($c.find('.color-id-input').val() || '') === String(cid)) {
                    setCardBordados($c, bordados);
                    sincronizarCamposOcultosBordados($c);
                    actualizarResumenBordadosEnCard($c);
                    var $chk = $c.find('.lleva-bordado-checkbox');
                    if (bordados.length > 0 && !$chk.is(':checked')) {
                        $chk.prop('checked', true).trigger('change');
                    }
                }
            });
        }

        function calcularRecargoUnitarioBordadoDesdeLista(bordados) {
            if (!Array.isArray(bordados) || !bordados.length) return 0;
            return bordados.reduce(function (acc, item) {
                var precio = parseFloat(item.precio_aplicado) || 0;
                var cantidad = Math.max(1, parseInt(item.cantidad || 1, 10));
                return acc + (precio * cantidad);
            }, 0);
        }

        function sincronizarCamposOcultosBordados($card) {
            var container = $card.find('.bordados-hidden-fields');
            container.empty();

            var bordados = getCardBordados($card);
            bordados.forEach(function (item, idx) {
                var fields = [
                    { key: 'ubicacion_bordado_id', value: item.ubicacion_bordado_id || '' },
                    { key: 'nombre_aplicado', value: item.nombre_aplicado || '' },
                    { key: 'logo_id', value: item.logo_id || '' },
                    { key: 'es_personalizada', value: item.es_personalizada ? 1 : 0 },
                    { key: 'precio_aplicado', value: parseFloat(item.precio_aplicado || 0).toFixed(2) },
                    { key: 'cantidad', value: Math.max(1, parseInt(item.cantidad || 1, 10)) }
                ];

                fields.forEach(function (field) {
                    container.append(
                        '<input type="hidden" name="productos[' + $card.data('product-index') + '][bordados][' + idx + '][' + field.key + ']" value="' + field.value + '">'
                    );
                });
            });
        }

        function actualizarResumenBordadosEnCard($card) {
            var bordados = getCardBordados($card);
            var resumenInput = $card.find('.bordados-summary-input');

            if (!bordados.length) {
                resumenInput.val('Sin configuración de bordado');
            } else {
                var resumenItems = bordados.map(function (item) {
                    var cantidad = Math.max(1, parseInt(item.cantidad || 1, 10));
                    var precio = formatMoney(parseFloat(item.precio_aplicado || 0));
                    var logo = String(item.nombre_logo_aplicado || '').trim();
                    var logoTexto = logo ? (logo + ' → ') : '';
                    return logoTexto + item.nombre_aplicado + ' x' + cantidad + ' (' + precio + ')';
                });

                var resumen = resumenItems.length > 2
                    ? resumenItems.slice(0, 2).join(' · ') + ' · +' + (resumenItems.length - 2) + ' más'
                    : resumenItems.join(' · ');

                resumenInput.val(resumen);
            }

            var recargo = calcularRecargoUnitarioBordadoDesdeLista(bordados);
            var base = parseFloat($card.find('.precio-unitario-input').val()) || 0;
            var finalUnit = base + recargo;
            var llevaBordado = parseInt($card.find('.lleva-bordado-value').val() || 0, 10) === 1;
            actualizarPanelResumenMonetario($card, recargo, finalUnit, llevaBordado);
            sincronizarCamposOcultosBordados($card);
        }

        function actualizarPanelResumenMonetario($card, recargo, finalUnit, llevaBordado) {
            var cantidad = parseFloat($card.find('.cantidad-input').val()) || 0;
            var totalLinea = finalUnit * cantidad;
            var $totalLineaEl = $card.find('.bordado-linea-total-value');
            var totalAnterior = parseFloat(($totalLineaEl.data('total-prev') || 0));

            $card.find('.bordado-recargo-value').text(formatMoney(recargo));
            $card.find('.bordado-final-value').text(formatMoney(finalUnit));
            $totalLineaEl.text(formatMoney(totalLinea));

            if (Math.abs(totalLinea - totalAnterior) > 0.0001) {
                $totalLineaEl.addClass('total-updated');
                setTimeout(function () {
                    $totalLineaEl.removeClass('total-updated');
                }, 380);
            }

            $totalLineaEl.data('total-prev', totalLinea);

            var bordadosCount = getCardBordados($card).length;
            var estadoVisible = !llevaBordado || bordadosCount > 0;
            var estadoTexto = !llevaBordado
                ? 'Servicio de bordado inactivo'
                : (bordadosCount > 0 ? 'Configuración aplicada' : '');
            $card.find('.bordado-resumen-estado')
                .text(estadoTexto)
                .toggleClass('d-none', !estadoVisible);
        }

        function renderizarUbicacionesModal(filtro) {
            var grid = $('#ubicacionesCatalogoGrid');
            grid.empty();
            filtro = (filtro || '').toLowerCase().trim();

            var bordadosActivos = currentBordadoGroupKey
                ? getGroupBordados(currentBordadoGroupKey)
                : (currentBordadoCard ? getCardBordados(currentBordadoCard) : []);
            var grouped = {};
            ubicacionesBordadoArray.forEach(function (item) {
                var nombre = String(item.nombre || '').trim();
                var grupo = String(item.grupo || 'General').trim();
                if (!nombre) return;
                if (filtro && nombre.toLowerCase().indexOf(filtro) === -1 && grupo.toLowerCase().indexOf(filtro) === -1) return;

                if (!grouped[grupo]) grouped[grupo] = [];
                grouped[grupo].push(item);
            });

            var nombresGrupos = Object.keys(grouped);
            if (!nombresGrupos.length) {
                grid.html('<div class="text-center text-muted py-4"><i class="ri-map-pin-line d-block" style="font-size:2rem;opacity:0.3;"></i><small>No se encontraron ubicaciones</small></div>');
                return;
            }

            nombresGrupos.sort(function (a, b) { return a.localeCompare(b, 'es', { sensitivity: 'base' }); });
            nombresGrupos.forEach(function (grupo) {
                grid.append('<div class="color-grupo-header">' + grupo + '</div>');

                grouped[grupo].forEach(function (item) {
                    var found = bordadosActivos.find(function (b) {
                        return !b.es_personalizada && String(b.ubicacion_bordado_id) === String(item.id);
                    });

                    var checked = !!found;
                    var precio = found ? parseFloat(found.precio_aplicado || item.precio_base || 0) : parseFloat(item.precio_base || 0);
                    var cantidad = found ? Math.max(1, parseInt(found.cantidad || 1, 10)) : 1;
                    var logoId = found ? (found.logo_id || '') : '';
                    var logoNombre = found ? String(found.nombre_logo_aplicado || '').trim() : '';

                    grid.append(
                        '<div class="mb-2 border rounded px-2 py-2 ubicacion-std-row" style="border-color:rgba(30,60,114,0.18)!important;">' +
                        '  <div class="d-flex align-items-start gap-2">' +
                        '    <input type="checkbox" class="form-check-input mt-0 ubicacion-std-check" data-id="' + item.id + '" data-nombre="' + item.nombre + '" ' + (checked ? 'checked' : '') + '>' +
                        '    <div class="flex-grow-1">' +
                        '      <div class="d-flex align-items-center justify-content-between gap-2">' +
                        '        <div class="fw-semibold" style="font-size:0.82rem;color:#1e3c72;">' + item.nombre + '</div>' +
                        '        <span class="badge rounded-pill bg-soft-primary text-atlantico-dark ubicacion-estado-badge">No incluida</span>' +
                        '      </div>' +
                        '      <small class="text-muted">Base catálogo: $' + parseFloat(item.precio_base || 0).toFixed(2) + '</small>' +
                        '    </div>' +
                        '    <div class="ubicacion-mini-field">' +
                        '      <span class="ubicacion-mini-label">Precio</span>' +
                        '      <input type="number" class="form-control form-control-sm ubicacion-std-precio" step="0.01" min="0" value="' + precio.toFixed(2) + '">' +
                        '    </div>' +
                        '    <div class="ubicacion-mini-field is-cantidad">' +
                        '      <span class="ubicacion-mini-label">Cant.</span>' +
                        '      <input type="number" class="form-control form-control-sm ubicacion-std-cantidad text-center" step="1" min="1" value="' + cantidad + '">' +
                        '    </div>' +
                        '  </div>' +
                        '  <div class="input-group input-group-sm mt-2">' +
                        '    <input type="text" class="form-control bordado-logo-input ubicacion-std-logo" placeholder="Logo para esta ubicación" value="' + logoNombre + '" data-logo-id="' + logoId + '" readonly autocomplete="off" style="background-color:#fff;cursor:default;" ' + (checked ? '' : 'disabled') + '>' +
                        '    <button type="button" class="btn btn-sm btn-atlantico-brand bordado-logo-picker" ' + (checked ? '' : 'disabled') + '>' +
                        '      <i class="ri-search-line" style="color:#fff;"></i>' +
                        '    </button>' +
                        '  </div>' +
                        '  <small class="text-muted d-block mt-1 ubicacion-estado-ayuda"></small>' +
                        '</div>'
                    );
                });
            });

            actualizarResumenRecargoModal();
            actualizarEstadosUbicacionesModal();
        }

        function renderizarUbicacionesPersonalizadasModal() {
            var container = $('#ubicacionesPersonalizadasContainer');
            container.empty();

            if (!currentBordadoGroupKey && !currentBordadoCard) return;

            var _bordados = currentBordadoGroupKey
                ? getGroupBordados(currentBordadoGroupKey)
                : getCardBordados(currentBordadoCard);
            var customItems = _bordados.filter(function (item) {
                return !!item.es_personalizada;
            });

            if (!customItems.length) {
                container.html('<small class="text-muted">No hay ubicaciones personalizadas.</small>');
                return;
            }

            customItems.forEach(function (item) {
                container.append(crearFilaUbicacionPersonalizada(item.nombre_aplicado, item.precio_aplicado, item.cantidad, item.logo_id, item.nombre_logo_aplicado));
            });

            actualizarEstadosUbicacionesModal();
        }

        function crearFilaUbicacionPersonalizada(nombre, precio, cantidad, logoId, logoNombre) {
            return '<div class="d-flex flex-column gap-2 ubicacion-personalizada-row border rounded p-2" style="border-color:rgba(30,60,114,0.18)!important;">' +
                '  <div class="d-flex align-items-center justify-content-between">' +
                '    <small class="text-muted fw-semibold">Ubicación personalizada</small>' +
                '    <span class="badge rounded-pill bg-soft-warning text-atlantico-dark ubicacion-estado-badge">Incompleta</span>' +
                '  </div>' +
                '  <div class="d-flex align-items-center gap-2">' +
                '    <input type="text" class="form-control form-control-sm ubicacion-personalizada-nombre" placeholder="Ubicación especial..." value="' + (nombre || '') + '">' +
                '    <input type="number" class="form-control form-control-sm ubicacion-personalizada-precio" style="max-width:100px;" step="0.01" min="0" value="' + (parseFloat(precio || 0)).toFixed(2) + '">' +
                '    <input type="number" class="form-control form-control-sm ubicacion-personalizada-cantidad text-center" style="max-width:75px;" step="1" min="1" value="' + (Math.max(1, parseInt(cantidad || 1, 10))) + '">' +
                '    <button type="button" class="btn btn-sm btn-outline-danger eliminar-ubicacion-personalizada-btn"><i class="ri-delete-bin-line"></i></button>' +
                '  </div>' +
                '  <div class="input-group input-group-sm">' +
                '    <input type="text" class="form-control form-control-sm bordado-logo-input ubicacion-personalizada-logo" placeholder="Logo para esta ubicación" value="' + (logoNombre || '') + '" data-logo-id="' + (logoId || '') + '" readonly autocomplete="off" style="background-color:#fff;cursor:default;">' +
                '    <button type="button" class="btn btn-sm btn-atlantico-brand bordado-logo-picker"><i class="ri-search-line" style="color:#fff;"></i></button>' +
                '  </div>' +
                '  <small class="text-muted ubicacion-estado-ayuda"></small>' +
                '</div>';
        }

        function aplicarEstadoVisualUbicacion($row, state, text, helpText) {
            if (!$row || !$row.length) return;

            var $badge = $row.find('.ubicacion-estado-badge').first();
            var $help = $row.find('.ubicacion-estado-ayuda').first();

            $row.removeClass('ubicacion-row-disabled ubicacion-row-pending ubicacion-row-complete');
            $badge.removeClass('bg-soft-primary bg-soft-warning bg-soft-success text-atlantico-dark');

            if (state === 'disabled') {
                $row.addClass('ubicacion-row-disabled');
                $badge.addClass('bg-soft-primary text-atlantico-dark');
            } else if (state === 'complete') {
                $row.addClass('ubicacion-row-complete');
                $badge.addClass('bg-soft-success text-atlantico-dark');
            } else {
                $row.addClass('ubicacion-row-pending');
                $badge.addClass('bg-soft-warning text-atlantico-dark');
            }

            $badge.text(text || '');
            $help.text(helpText || '');
        }

        function actualizarEstadoUbicacionStdRow($row) {
            if (!$row || !$row.length) return;

            var checked = $row.find('.ubicacion-std-check').is(':checked');
            var logo = String($row.find('.ubicacion-std-logo').val() || '').trim();

            if (!checked) {
                aplicarEstadoVisualUbicacion($row, 'disabled', 'No incluida', 'Actívala para sumar esta ubicación al bordado.');
                return;
            }

            if (!logo) {
                aplicarEstadoVisualUbicacion($row, 'complete', 'Sin logo', 'Ubicación lista (sin logo asignado).');
                return;
            }

            aplicarEstadoVisualUbicacion($row, 'complete', 'Completa', 'Ubicación lista para aplicar en esta prenda.');
        }

        function actualizarEstadoUbicacionPersonalizadaRow($row) {
            if (!$row || !$row.length) return;

            var nombre = String($row.find('.ubicacion-personalizada-nombre').val() || '').trim();
            var logo = String($row.find('.ubicacion-personalizada-logo').val() || '').trim();

            if (!nombre && !logo) {
                aplicarEstadoVisualUbicacion($row, 'pending', 'Sin definir', 'Completa nombre y logo para usar esta ubicación personalizada.');
                return;
            }

            if (!nombre) {
                aplicarEstadoVisualUbicacion($row, 'pending', 'Falta nombre', 'Define el nombre de la ubicación personalizada.');
                return;
            }

            if (!logo) {
                aplicarEstadoVisualUbicacion($row, 'complete', 'Sin logo', 'Ubicación personalizada lista (sin logo asignado).');
                return;
            }

            aplicarEstadoVisualUbicacion($row, 'complete', 'Completa', 'Ubicación personalizada lista para aplicar.');
        }

        function actualizarEstadosUbicacionesModal() {
            $('#ubicacionesCatalogoGrid .ubicacion-std-row').each(function () {
                actualizarEstadoUbicacionStdRow($(this));
            });

            $('#ubicacionesPersonalizadasContainer .ubicacion-personalizada-row').each(function () {
                actualizarEstadoUbicacionPersonalizadaRow($(this));
            });
        }

        // Total de bordados seleccionados en el configurador = SUMA de cantidades
        // por línea (no número de líneas): una ubicación con cantidad 10 son 10
        // bordados. Es la unidad que se compara contra MAX_BORDADOS (igual que el
        // backend en BordadoPricingService::indicesQueExcedenMaximo).
        function contarBordadosSeleccionados() {
            var total = 0;
            $('#ubicacionesCatalogoGrid .ubicacion-std-check:checked').each(function () {
                var row = $(this).closest('.ubicacion-std-row');
                total += Math.max(1, parseInt(row.find('.ubicacion-std-cantidad').val() || 1, 10));
            });
            $('#ubicacionesPersonalizadasContainer .ubicacion-personalizada-row').each(function () {
                if (!String($(this).find('.ubicacion-personalizada-nombre').val() || '').trim()) return;
                total += Math.max(1, parseInt($(this).find('.ubicacion-personalizada-cantidad').val() || 1, 10));
            });
            return total;
        }

        function actualizarResumenRecargoModal() {
            var recargo = 0;
            var activeCount = 0;

            $('#ubicacionesCatalogoGrid .ubicacion-std-check:checked').each(function () {
                var row = $(this).closest('.ubicacion-std-row');
                var precio = parseFloat(row.find('.ubicacion-std-precio').val()) || 0;
                var cantidad = Math.max(1, parseInt(row.find('.ubicacion-std-cantidad').val() || 1, 10));
                recargo += (precio * cantidad);
                activeCount++;
            });

            $('#ubicacionesPersonalizadasContainer .ubicacion-personalizada-row').each(function () {
                var nombre = String($(this).find('.ubicacion-personalizada-nombre').val() || '').trim();
                if (!nombre) return;
                var precio = parseFloat($(this).find('.ubicacion-personalizada-precio').val()) || 0;
                var cantidad = Math.max(1, parseInt($(this).find('.ubicacion-personalizada-cantidad').val() || 1, 10));
                recargo += (precio * cantidad);
                activeCount++;
            });

            $('#resumenRecargoBordadoModal').text(formatMoney(recargo));
            $('#bordado-oc-active-count')
                .text(activeCount + ' / ' + MAX_BORDADOS)
                .toggleClass('text-danger', activeCount >= MAX_BORDADOS);

            // Equivalente en Bolívares (VES) del servicio de bordado — tasa BCV vigente
            var bsBordado = (typeof window.bsEquivalente === 'function') ? window.bsEquivalente(recargo) : null;
            $('#resumenRecargoBordadoModalBs').text(bsBordado || 'Sin tasa BCV');
            var tasaFmt = (typeof window.bsTasaFmt === 'function') ? window.bsTasaFmt() : null;
            $('#resumenRecargoBordadoTasa').text(tasaFmt ? '· Tasa ' + tasaFmt : '');
        }

        function aplicarBordadosDesdeModal() {
            // Permitir aplicar tanto desde una card concreta como desde un grupo de la grilla
            if (!currentBordadoCard && !currentBordadoGroupKey) return;

            // Tope de líneas de bordado por producto (estándar configurable).
            if (contarBordadosSeleccionados() > MAX_BORDADOS) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Máximo de bordados por producto',
                    text: 'No se pueden agregar más de ' + MAX_BORDADOS + ' bordados por producto. Quita alguna ubicación antes de aplicar.',
                    customClass: { confirmButton: 'btn btn-primary w-xs me-2' },
                    buttonsStyling: false,
                    showCloseButton: true
                });
                return;
            }

            var bordados = [];

            $('#ubicacionesCatalogoGrid .ubicacion-std-check:checked').each(function () {
                var row = $(this).closest('.ubicacion-std-row');
                var ubicacionId = $(this).data('id');
                var nombre = $(this).data('nombre');
                var precio = parseFloat(row.find('.ubicacion-std-precio').val()) || 0;
                var cantidad = Math.max(1, parseInt(row.find('.ubicacion-std-cantidad').val() || 1, 10));
                var $logoInput = row.find('.ubicacion-std-logo');
                // Logo opcional: si no se asignó, se envía sin logo.
                var logoId = $logoInput.data('logo-id') || null;
                var logoNombre = String($logoInput.val() || '').trim();

                bordados.push({
                    ubicacion_bordado_id: ubicacionId,
                    nombre_aplicado: nombre,
                    logo_id: logoId,
                    nombre_logo_aplicado: logoNombre,
                    es_personalizada: false,
                    precio_aplicado: precio,
                    cantidad: cantidad
                });
            });

            $('#ubicacionesPersonalizadasContainer .ubicacion-personalizada-row').each(function () {
                var nombre = String($(this).find('.ubicacion-personalizada-nombre').val() || '').trim();
                if (!nombre) return;
                var precio = parseFloat($(this).find('.ubicacion-personalizada-precio').val()) || 0;
                var cantidad = Math.max(1, parseInt($(this).find('.ubicacion-personalizada-cantidad').val() || 1, 10));
                var $logoInput = $(this).find('.ubicacion-personalizada-logo');
                // Logo opcional: la ubicación personalizada solo requiere nombre.
                var logoId = $logoInput.data('logo-id') || null;
                var logoNombre = String($logoInput.val() || '').trim();

                bordados.push({
                    ubicacion_bordado_id: null,
                    nombre_aplicado: nombre,
                    logo_id: logoId,
                    nombre_logo_aplicado: logoNombre,
                    es_personalizada: true,
                    precio_aplicado: precio,
                    cantidad: cantidad
                });
            });

            if (currentBordadoGroupKey) {
                setGroupBordados(currentBordadoGroupKey, bordados);
                currentBordadoGroupKey = null;
            } else if (currentBordadoCard) {
                setCardBordados(currentBordadoCard, bordados);
                actualizarResumenBordadosEnCard(currentBordadoCard);
            }
            calculateCotizacionTotals();
            // refreshGroupedList vive en otro IIFE; se accede vía window.cotRefreshGroupedList
            if (typeof window.cotRefreshGroupedList === 'function') window.cotRefreshGroupedList();

            if (ubicacionModal) ubicacionModal.hide();
        }

        if (ubicacionModalEl) {
            // El configurador es un modal SOBRE el modal de cotización (#showModal).
            // Se ajusta su z-index y el de su backdrop para quedar por encima del
            // modal padre (mismo patrón que el modal de logos).
            ubicacionModalEl.addEventListener('show.bs.modal', function () {
                var zIndex = $('#showModal').hasClass('show') ? 1080 : 1075;
                $(ubicacionModalEl).css('z-index', zIndex);
                window.requestAnimationFrame(function () {
                    var $lastBackdrop = $('.modal-backdrop').last();
                    if ($lastBackdrop.length) {
                        $lastBackdrop.css('z-index', zIndex - 1).addClass('bordado-modal-backdrop');
                    }
                });
            });

            ubicacionModalEl.addEventListener('shown.bs.modal', function () {
                $('#buscarUbicacionModal').val('').trigger('focus');

                if (!ubicacionesBordadoArray.length) {
                    $('#ubicacionesCatalogoGrid').html('<div class="text-center text-muted py-4"><small>Cargando ubicaciones...</small></div>');
                    cargarUbicacionesBordado(function () {
                        renderizarUbicacionesPersonalizadasModal();
                        renderizarUbicacionesModal('');
                    });
                    return;
                }

                renderizarUbicacionesPersonalizadasModal();
                renderizarUbicacionesModal('');
            });

            // Al cerrar, el modal de cotización sigue abierto: se restaura su
            // scroll-lock y se eliminan backdrops sobrantes (igual que el de logos).
            ubicacionModalEl.addEventListener('hidden.bs.modal', function () {
                $(ubicacionModalEl).css('z-index', '');
                $('.modal-backdrop.bordado-modal-backdrop').removeClass('bordado-modal-backdrop').css('z-index', '');

                if ($('#showModal').hasClass('show')) {
                    $('body').addClass('modal-open');
                    var backdrops = $('.modal-backdrop');
                    if (backdrops.length > 1) {
                        backdrops.not(backdrops.first()).remove();
                    }
                }
            });
        }

        $('#buscarUbicacionModal').on('keyup', function () {
            renderizarUbicacionesModal($(this).val());
        });

        $(document).on('change', '.ubicacion-std-check', function () {
            var enabled = $(this).is(':checked');

            // Tope de bordados: al marcar (el check ya suma su cantidad al total),
            // si se supera el máximo se revierte y se avisa.
            if (enabled && contarBordadosSeleccionados() > MAX_BORDADOS) {
                $(this).prop('checked', false);
                Swal.fire({
                    icon: 'warning',
                    title: 'Máximo de bordados por producto',
                    text: 'Solo se permiten ' + MAX_BORDADOS + ' bordados por producto.',
                    customClass: { confirmButton: 'btn btn-primary w-xs me-2' },
                    buttonsStyling: false,
                    showCloseButton: true
                });
                return;
            }

            var row = $(this).closest('.ubicacion-std-row');
            row.find('.ubicacion-std-logo, .bordado-logo-picker').prop('disabled', !enabled);
            if (!enabled) {
                row.find('.ubicacion-std-logo').val('');
            }
            actualizarEstadoUbicacionStdRow(row);
            actualizarResumenRecargoModal();
        });

        $('#productos-container').on('click', '.configurar-bordados-trigger', function (e) {
            e.preventDefault();
            var $c = $(this).closest('.product-item');
            currentBordadoCard = $c;
            currentBordadoGroupKey = $c.find('.producto-id-input').val() + '|' + ($c.find('.color-id-input').val() || '');
            if (ubicacionModal) ubicacionModal.show();
        });

        $('#productos-container').on('click', '.bordados-summary-input', function () {
            var $c = $(this).closest('.product-item');
            currentBordadoCard = $c;
            currentBordadoGroupKey = $c.find('.producto-id-input').val() + '|' + ($c.find('.color-id-input').val() || '');
            if (ubicacionModal) ubicacionModal.show();
        });

        $(document).on('input change', '.ubicacion-std-check, .ubicacion-std-precio, .ubicacion-std-cantidad, .ubicacion-personalizada-precio, .ubicacion-personalizada-cantidad, .ubicacion-personalizada-nombre', function () {
            var row = $(this).closest('.ubicacion-std-row, .ubicacion-personalizada-row');
            if (row.hasClass('ubicacion-std-row')) {
                actualizarEstadoUbicacionStdRow(row);
            } else if (row.hasClass('ubicacion-personalizada-row')) {
                actualizarEstadoUbicacionPersonalizadaRow(row);
            }
            actualizarResumenRecargoModal();
        });

        // Validación lógica de la cantidad por línea (on-blur): la SUMA de bordados
        // del producto no puede pasar de MAX_BORDADOS. Si al salir del campo la suma
        // se excede, se capea la cantidad de ESTA línea al máximo posible y se avisa.
        // Ej.: con tope 6, poner 10 en la manga se recorta a 6.
        $(document).on('blur', '.ubicacion-std-cantidad, .ubicacion-personalizada-cantidad', function () {
            var $inp = $(this);
            var val = Math.max(1, parseInt($inp.val() || 1, 10));
            $inp.val(val);

            var total = contarBordadosSeleccionados();
            if (total > MAX_BORDADOS) {
                var capped = Math.max(1, val - (total - MAX_BORDADOS));
                $inp.val(capped);
                Swal.fire({
                    icon: 'warning',
                    title: 'Máximo de bordados por producto',
                    text: 'El total de bordados por producto no puede pasar de ' + MAX_BORDADOS +
                        '. La cantidad de esta ubicación se ajustó a ' + capped + '.',
                    customClass: { confirmButton: 'btn btn-primary w-xs me-2' },
                    buttonsStyling: false,
                    showCloseButton: true
                });
                var row = $inp.closest('.ubicacion-std-row, .ubicacion-personalizada-row');
                if (row.hasClass('ubicacion-std-row')) {
                    actualizarEstadoUbicacionStdRow(row);
                } else {
                    actualizarEstadoUbicacionPersonalizadaRow(row);
                }
                actualizarResumenRecargoModal();
            }
        });

        $('#agregarUbicacionPersonalizadaBtn').on('click', function () {
            // Tope de bordados: no permitir agregar otra ubicación si el total ya llegó.
            if (contarBordadosSeleccionados() >= MAX_BORDADOS) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Máximo de bordados por producto',
                    text: 'Ya alcanzaste el máximo de ' + MAX_BORDADOS + ' bordados por producto.',
                    customClass: { confirmButton: 'btn btn-primary w-xs me-2' },
                    buttonsStyling: false,
                    showCloseButton: true
                });
                return;
            }
            var container = $('#ubicacionesPersonalizadasContainer');
            if (container.find('small.text-muted').length) container.empty();
            container.append(crearFilaUbicacionPersonalizada('', 0, 1, null, ''));
            actualizarEstadosUbicacionesModal();
        });

        $(document).on('click', '.eliminar-ubicacion-personalizada-btn', function () {
            $(this).closest('.ubicacion-personalizada-row').remove();
            if (!$('#ubicacionesPersonalizadasContainer .ubicacion-personalizada-row').length) {
                $('#ubicacionesPersonalizadasContainer').html('<small class="text-muted">No hay ubicaciones personalizadas.</small>');
            }
            actualizarResumenRecargoModal();
        });

        $('#aplicarUbicacionesBordadoBtn').on('click', function () {
            aplicarBordadosDesdeModal();
        });

        // ============================================================
        // === FIN LÓGICA MODAL DE UBICACIÓN DE BORDADO ===
        // ============================================================

        function addProductItem(productoId = '', cantidad = '', precioUnitario = '', descripcion = '', llevaBordado = false, _unused = '', colorId = null, tallaId = null, generoId = null, bordados = []) {
            var productoDisplay = 'Clic para buscar producto...';
            var textClass = 'text-muted';
            var cardVariant = productItemIndex % 2;
            var cardBorderColor = cardVariant === 0 ? 'var(--atlantico-cyan)' : 'var(--atlantico-dark-blue)';
            var cardHeaderBg = cardVariant === 0 ? '#f0f4f8' : '#edf2f9';
            var colorObj = getColorById(colorId);
            var colorNombreInit = colorObj ? colorObj.nombre : '';
            var colorHexInit = colorObj ? colorObj.hex_referencial : null;
            var tallaLabel = getTallaLabel(tallaId);
            var llevaBordadoActivo = (llevaBordado === true || llevaBordado === 1 || llevaBordado === '1');

            var pDyn = null; // producto virtual (variante dinámica) si aplica
            if (productoId) {
                var p = products.find(prod => prod.id == productoId);
                if (p) {
                    var tipoNombre = p.tipo_producto ? p.tipo_producto.nombre : 'Sin tipo';
                    productoDisplay = (p.codigo || '') + ' - ' + tipoNombre + ' ' + (p.modelo || '');
                    precioUnitario = precioUnitario || p.precio_base;
                    textClass = 'text-dark fw-semibold';
                    if (p._dynamic) pDyn = p;
                }
            }

            // Campo de producto: select normal (legacy) o, para variante dinámica,
            // display de solo lectura + hidden de tipo/tela/atributos para el submit.
            var escAttr = function (s) {
                return String(s == null ? '' : s)
                    .replace(/&/g, '&amp;').replace(/"/g, '&quot;')
                    .replace(/</g, '&lt;').replace(/>/g, '&gt;');
            };
            var productoFieldHtml;
            if (pDyn) {
                var vd = pDyn._variante;
                productoFieldHtml =
                    `<input type="text" class="form-control form-control-sm" value="${escAttr((pDyn.codigo || '') + ' · ' + (pDyn.nombre_completo || ''))}" readonly title="Variante configurada">` +
                    `<input type="hidden" class="producto-id-input" value="${escAttr(productoId)}">` +
                    `<input type="hidden" name="productos[${productItemIndex}][tipo_producto_id]" value="${vd.tipo_producto_id}">` +
                    (vd.insumo_tela_id ? `<input type="hidden" name="productos[${productItemIndex}][insumo_tela_id]" value="${vd.insumo_tela_id}">` : '') +
                    (vd.atributo_valor_ids || []).map(function (vid) {
                        return `<input type="hidden" name="productos[${productItemIndex}][atributo_valor_ids][]" value="${vid}">`;
                    }).join('');
            } else {
                productoFieldHtml =
                    `<select name="productos[${productItemIndex}][producto_id]"
                            class="form-select form-select-sm producto-id-input producto-dropdown"
                            required>
                            <option value="">— Seleccionar producto —</option>
                            ${products.filter(prod => !prod._dynamic).map(prod => `<option value="${prod.id}" data-precio="${prod.precio_base}" ${prod.id == productoId ? 'selected' : ''}>${prod.codigo ? prod.codigo + ' · ' : ''}${prod.nombre_completo || prod.nombre}</option>`).join('')}
                        </select>`;
            }

            var itemHtml = `
            <div class="card border-0 shadow-sm mb-3 product-item" data-product-index="${productItemIndex}"
                style="border-left: 3px solid ${cardBorderColor} !important; overflow: hidden;">
                <!-- Mini Header Bar -->
                <div class="d-flex align-items-center justify-content-between px-3 py-2"
                    style="background: ${cardHeaderBg}; border-bottom: 1px solid #e9ecef;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-flex align-items-center justify-content-center flex-shrink-0 fw-bold product-badge"
                            title="Producto #${productItemIndex + 1}"
                            style="min-width:22px;height:20px;padding:0 6px;background:var(--atlantico-dark-blue);color:#fff;font-size:0.65rem;border-radius:5px;border:1px solid var(--atlantico-cyan);">
                            ${productItemIndex + 1}
                        </span>
                        <span class="product-label" style="font-size:0.75rem;color:#5a6a85;font-weight:500;">Producto</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-producto-item p-0 d-flex align-items-center justify-content-center"
                        title="Eliminar producto" style="width:24px;height:24px;border-radius:6px;font-size:0.7rem;">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
                <!-- Card Body -->
                <div class="card-body p-3">

                    <!-- Fila 1: Selector de producto (dropdown) -->
                    <div class="mb-2">
                        <label class="form-label mb-1 small fw-medium" style="color:#495057;">
                            <i class="ri-shopping-bag-line me-1"></i>Producto
                        </label>
                        ${productoFieldHtml}
                    </div>

                    <!-- Fila 2: Color + Talla + Cantidad + Precio Unitario -->
                    <div class="row g-2 mb-2">
                        <div class="col-6 col-md-4">
                            <label class="form-label mb-1 small fw-medium" style="color:#495057;"><i class="ri-palette-line me-1"></i>Color</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text color-dot-display"
                                    style="background: #fff; padding: 0 6px;">
                                    <span class="color-dot-indicator"
                                        style="background-color: ${colorHexInit || 'transparent'}; ${!colorHexInit ? 'border:1.5px dashed #ccc;' : 'border:1.5px solid rgba(0,0,0,0.15);'}"></span>
                                </span>
                                <input type="text"
                                    class="form-control form-control-sm color-display"
                                    placeholder="Seleccionar color..." value="${colorNombreInit}"
                                    readonly autocomplete="off"
                                    style="background-color: #fff !important;" />
                                <input type="hidden" name="productos[${productItemIndex}][color_id]"
                                    class="color-id-input" value="${colorId || ''}" />
                                <button type="button"
                                    class="btn btn-sm btn-atlantico-brand buscar-color-trigger"
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Catálogo">
                                    <i class="ri-palette-line" style="color:#fff;"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label mb-1 small fw-medium" style="color:#495057;"><i class="ri-t-shirt-line me-1"></i>Talla</label>
                            <div class="input-group input-group-sm">
                                <input type="text"
                                    class="form-control form-control-sm text-center talla-input-display"
                                    value="${tallaLabel}"
                                    placeholder="Seleccionar..."
                                    required readonly autocomplete="off"
                                    style="background-color: #fff !important; cursor:text;" />
                                <input type="hidden" name="productos[${productItemIndex}][talla_id]"
                                    class="talla-input-value" value="${tallaId || ''}" />
                                <input type="hidden" name="productos[${productItemIndex}][genero_id]"
                                    class="genero-id-input" value="${generoId || ''}"
                                    data-genero-label="${escAttr(getGeneroNombre(generoId))}" />
                                <button type="button"
                                    class="btn btn-sm btn-atlantico-brand buscar-talla-trigger px-2"
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Tallas" aria-label="Abrir catálogo de tallas">
                                    <i class="ri-t-shirt-line" style="color:#fff;"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label mb-1 small fw-medium" style="color:#495057;">Cant.</label>
                            <input type="number" name="productos[${productItemIndex}][cantidad]"
                                class="form-control form-control-sm text-center cantidad-input"
                                placeholder="0" min="1" value="${cantidad}" required />
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label mb-1 small fw-medium" style="color:#495057;">Precio Base ($)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text text-success"
                                    style="background: rgba(46,204,113,0.1); border-color: #2ecc71;">$</span>
                                <input type="number" name="productos[${productItemIndex}][precio_unitario]"
                                    class="form-control precio-unitario-input"
                                    placeholder="0.00" step="0.01" min="0" value="${precioUnitario}" required
                                    style="border-color: #2ecc71;" />
                            </div>
                        </div>
                    </div>

                    <!-- Fila 3: Notas + Servicio de Bordado -->
                    <div class="row g-2">
                        <div class="col-md-8">
                            <label class="form-label mb-1 small fw-medium" style="color:#495057;"><i class="ri-file-text-line me-1"></i>Notas del producto</label>
                            <textarea name="productos[${productItemIndex}][descripcion]"
                                class="form-control form-control-sm"
                                placeholder="Notas u observaciones (opcional)"
                                rows="1" style="resize: none;">${descripcion}</textarea>
                        </div>
                        <div class="col-md-4 d-flex align-items-center">
                            <div class="w-100">
                                <label class="form-label mb-1 small fw-medium" style="color:#495057;"><i class="ri-scissors-cut-line me-1"></i>Servicio</label>
                                <input type="hidden" name="productos[${productItemIndex}][lleva_bordado]"
                                    class="lleva-bordado-value" value="${llevaBordadoActivo ? 1 : 0}">
                                <div class="form-check d-flex align-items-center mb-0 mt-1">
                                    <input class="form-check-input lleva-bordado-checkbox" type="checkbox"
                                        id="lleva-bordado-${productItemIndex}"
                                        ${llevaBordadoActivo ? 'checked' : ''}
                                        style="width:1.05rem;height:1.05rem;cursor:pointer; margin-top:0;">
                                    <label class="form-check-label ms-2 small fw-semibold mb-0 bordado-label ${llevaBordadoActivo ? 'active' : ''}" for="lleva-bordado-${productItemIndex}"
                                        style="cursor:pointer; user-select:none;">
                                        Servicio de Bordado
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenedor Servicio de Bordado (condicional) -->
                    <div class="row g-2 mt-1 nombre-logo-container" style="display: ${llevaBordadoActivo ? 'flex' : 'none'}">
                        <div class="col-12">
                            <label class="form-label mb-1 small fw-medium" style="color:#495057;"><i class="ri-map-pin-line me-1"></i>Configuración del servicio</label>
                            <div class="input-group input-group-sm">
                                <input type="text"
                                    class="form-control form-control-sm ubicacion-logo-input bordados-summary-input"
                                    placeholder="Configurar servicio de bordado completo..."
                                    value=""
                                    readonly autocomplete="off"
                                    style="background-color: #fff !important;" />
                                <button type="button"
                                    class="btn btn-sm btn-atlantico-brand configurar-bordados-trigger"
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Configurar servicio">
                                    <i class="ri-settings-3-line" style="color:#fff;"></i>
                                </button>
                            </div>
                            <div class="bordados-hidden-fields"></div>
                            <small class="text-muted">Define logos, ubicaciones, cantidades y tarifas del servicio de bordado.</small>
                            <div class="bordado-resumen-box p-2 mt-2">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="bordado-resumen-title">Recargo unitario por bordado</span>
                                    <span class="badge bg-soft-info text-atlantico-dark bordado-recargo-value">$0.00</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-1">
                                    <span class="bordado-resumen-title">Precio final unitario</span>
                                    <span class="bordado-resumen-value bordado-final-value">$0.00</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-1">
                                    <span class="bordado-resumen-title">Total de la línea</span>
                                    <span class="bordado-resumen-value bordado-linea-total-value">$0.00</span>
                                </div>
                                <div class="text-muted mt-1 bordado-resumen-estado d-none"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            `;
            $('#productos-container').append(itemHtml);
            // Inicializar tooltips de Bootstrap en la fila recién insertada
            $('#productos-container .card').last().find('[data-bs-toggle="tooltip"]').each(function () {
                new bootstrap.Tooltip(this, { trigger: 'hover' });
            });

            var $newCard = $('#productos-container .card').last();
            setCardBordados($newCard, Array.isArray(bordados) ? bordados : []);
            $newCard.find('.bordados-summary-input').val('Sin configuración de bordado');
            actualizarResumenBordadosEnCard($newCard);

            productItemIndex++;
            reindexProductItems(); // Re-secuenciar tras agregar
        }

        // ══════════════════════════════════════════════════════════════════════
        // Re-indexar filas de productos tras agregar/eliminar
        // ══════════════════════════════════════════════════════════════════════
        // Itera TODOS los .product-item en orden DOM y re-calcula:
        //   1. Badge visual (el círculo con el número)
        //   2. data-product-index en la card
        //   3. name="productos[X][campo]" en TODOS los inputs, selects y textareas
        //   4. Sincronización del contador global
        //
        // ¿Por qué es seguro?
        //   - Solo se mutan ATRIBUTOS HTML (name, id, for, data-*, title, textContent)
        //   - NUNCA se reemplaza/recrea un nodo DOM, por lo tanto el .value que el
        //     usuario ya escribió permanece intacto (jQuery .val() lee .value, no el
        //     atributo HTML "value").
        // ══════════════════════════════════════════════════════════════════════
        function reindexProductItems() {
            $('#productos-container .product-item').each(function (i) {
                var $card = $(this);
                var cardVariant = i % 2;
                var cardBorderColor = cardVariant === 0 ? 'var(--atlantico-cyan)' : 'var(--atlantico-dark-blue)';
                var cardHeaderBg = cardVariant === 0 ? '#f0f4f8' : '#edf2f9';

                // 1. Actualizar data-product-index
                $card.attr('data-product-index', i);
                $card.css('border-left', '3px solid ' + cardBorderColor);
                $card.find('> .d-flex').first().css('background', cardHeaderBg);

                // 2. Actualizar badge visual y label en el header bar
                var $badge = $card.find('.product-badge').first();
                $badge.text(i + 1);
                $badge.attr('title', 'Producto #' + (i + 1));
                $card.find('.product-label').first().text('Producto');

                // 3. Re-numerar TODOS los name="productos[X][campo]"
                $card.find('input, select, textarea').each(function () {
                    var name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/productos\[\d+\]/, 'productos[' + i + ']'));
                    }
                });

                var $checkbox = $card.find('.lleva-bordado-checkbox').first();
                if ($checkbox.length) {
                    var newId = 'lleva-bordado-' + i;
                    $checkbox.attr('id', newId);
                    $card.find('label[for^="lleva-bordado-"]').first().attr('for', newId);
                }

            });

            // Sincronizar el contador global con la cantidad real de filas
            productItemIndex = $('#productos-container .product-item').length;
        }

        // Evento para remover producto
        $('#productos-container').on('click', '.remove-producto-item', function () {
            $(this).closest('.card').remove();
            reindexProductItems();        // Re-secuenciar tras eliminar
            calculateCotizacionTotals();  // Recalcular totales
        });

        // Mostrar/ocultar bloque de logo usando check moderno (0/1)
        $('#productos-container').on('change', '.lleva-bordado-checkbox', function () {
            var $checkbox = $(this);
            var $card = $checkbox.closest('.product-item');
            var selectedValue = $checkbox.is(':checked') ? 1 : 0;
            $card.find('.bordado-label').toggleClass('active', selectedValue === 1);

            $card.find('.lleva-bordado-value').val(selectedValue).trigger('change');

            var container = $card.find('.nombre-logo-container');
            if (selectedValue === 1) {
                container.show();
            } else {
                container.hide();
                setCardBordados($card, []);
                actualizarResumenBordadosEnCard($card);
                $card.find('.lleva-bordado-value').val(0);
            }

            calculateCotizacionTotals();
        });

        // Al cerrar el panel de bordados: una línea que quedó marcada como
        // "lleva bordado" pero SIN ubicaciones vuelve a "Sin bordado".
        // (Abrir y cerrar el panel sin agregar nada NO debe comprometer bordado
        // ni bloquear el guardado pidiendo una ubicación.)
        $(document).on('hidden.bs.modal', '#bordadoOffcanvas', function () {
            $('#productos-container .product-item').each(function () {
                var $card = $(this);
                var marcado = parseInt($card.find('.lleva-bordado-value').val() || 0, 10) === 1;
                var eff = getEffectiveBordadosForCard($card);
                if (marcado && (!eff || eff.length === 0)) {
                    $card.find('.lleva-bordado-checkbox').prop('checked', false);
                    $card.find('.bordado-label').removeClass('active');
                    $card.find('.lleva-bordado-value').val(0);
                    $card.find('.nombre-logo-container').hide();
                    setCardBordados($card, []);
                    actualizarResumenBordadosEnCard($card);
                }
            });
            if (typeof window.cotRefreshGroupedList === 'function') window.cotRefreshGroupedList();
            calculateCotizacionTotals();
        });

        // Calcular el total de la cotización y el restante
        function calculateCotizacionTotals() {
            let sum = 0;
            $('#productos-container .product-item').each(function () {
                var $card = $(this);
                let quantity = parseFloat($card.find('.cantidad-input').val()) || 0;
                let basePrice = parseFloat($card.find('.precio-unitario-input').val()) || 0;
                let bordEff = getEffectiveBordadosForCard($card);
                let llevaBordado = bordEff.length > 0 ||
                    parseInt($card.find('.lleva-bordado-value').val() || 0, 10) === 1;
                let recargoBordado = bordEff.length ? calcularRecargoUnitarioBordadoDesdeLista(bordEff) : 0;
                let finalUnitPrice = basePrice + recargoBordado;

                actualizarPanelResumenMonetario($card, recargoBordado, finalUnitPrice, llevaBordado);
                sum += (quantity * finalUnitPrice);
            });
            $('#total-display-field').val(sum.toFixed(2));
            $('#total-display-value').text(formatMoney(sum));
            updateCotizacionRemaining();
        }
        function updateCotizacionRemaining() {
            let abono = parseFloat($('#abono-field').val()) || 0;
            let total = parseFloat($('#total-display-field').val()) || 0;
            let restante = total - abono;
            $('#restante-display-field').val(restante.toFixed(2));
        }
        // Recalcular total cuando cambia la cantidad o el precio negociado
        $('#productos-container').on('change keyup', '.cantidad-input, .precio-unitario-input', calculateCotizacionTotals);
        $('#productos-container').on('change', '.product-select, .producto-dropdown', function () {
            var selectedOption = $(this).find('option:selected');
            var precio = selectedOption.data('precio');
            var $item = $(this).closest('.product-item');
            var spanPrecio = $item.find('.precio-producto-span');

            if (precio) {
                $item.find('.precio-unitario-input').val(precio);
                spanPrecio.text(formatMoney(parseFloat(precio)));
            } else {
                spanPrecio.text('');
            }

            calculateCotizacionTotals();
            refreshKPIs();
        });
        $('#abono-field').on('change keyup', updateCotizacionRemaining);
        // Reset al abrir el modal en modo creación (wizard 3 pasos)
        // El usuario agregará productos desde el paso 2 vía catálogo (Fase 2)
        // o "Agregar manual" (legacy). El empty-state aparece si no hay productos.
        $('#create-btn').on('click', function () {
            $('#modalTitle').text('Nueva Cotización');
            $('#cotizacionForm')[0].reset();
            $('#id-field').val('');
            $('#cliente-id-field').val('').prop('disabled', false).removeClass('campo-protegido');
            $('#fecha-cotizacion-field').val(window.cotFechaLocalISO()).prop('readonly', false).removeClass('campo-protegido');
            // Default de validez: emisión + vigencia configurada (ajustable con los chips)
            window.cotSeedValidez($('#fecha-cotizacion-field').val(), window.COT_DIAS_VIGENCIA);
            $('#prioridad-field').val('Normal');

            $('#productos-container').empty();
            window.cotCart = [];
            window.cotGroupBordadosState = {};
            if (typeof window.cotRefreshGroupedList === 'function') window.cotRefreshGroupedList();
            calculateCotizacionTotals();
            // showStep(1) y visibilidad de add-btn/edit-btn se ajustan en show.bs.modal
        });



        // ══════════════════════════════════════════════════════
        // VALIDACIONES onblur — Cotizaciones
        // ══════════════════════════════════════════════════════

        // Fecha cotización (obligatoria, solo en modo creación)
        $(document).on('blur', '#fecha-cotizacion-field', function () {
            if ($(this).prop('readonly')) return;
            let val = $(this).val();
            if (!val) {
                marcarInvalido($(this), 'La fecha de cotización es obligatoria.');
            } else {
                marcarValido($(this));
                // Re-disparar validación de fecha_validez si ya tiene valor
                let $fv = $('#fecha-validez-field');
                if ($fv.val()) $fv.trigger('blur');
            }
        });

        // Cantidad de producto (mín. 1) — event delegation
        $(document).on('blur', '.cantidad-input', function () {
            let val = parseInt($(this).val());
            if (isNaN(val) || val < 1) {
                marcarInvalido($(this), 'La cantidad debe ser al menos 1.');
            } else {
                marcarValido($(this));
            }
        });

        // Precio unitario (≥ 0) — event delegation
        $(document).on('blur', '.precio-unitario-input', function () {
            let val = parseFloat($(this).val());
            if (isNaN(val) || val < 0) {
                marcarInvalido($(this), 'El precio no puede ser negativo.');
            } else {
                marcarValido($(this));
            }
        });

        function validarFormularioCotizacion() {
            let esValido = true;

            // Fecha cotización (obligatoria en modo creación)
            let $fechaCot = $('#fecha-cotizacion-field');
            if (!$fechaCot.prop('readonly')) {
                if (!$fechaCot.val()) {
                    marcarInvalido($fechaCot, 'La fecha de cotización es obligatoria.');
                    esValido = false;
                } else {
                    marcarValido($fechaCot);
                }
            }

            // Fecha validez (obligatoria y ≥ fecha cotización)
            let $fechaVal = $('#fecha-validez-field');
            if (!$fechaVal.val()) {
                marcarInvalido($fechaVal, 'La fecha de validez es obligatoria.');
                esValido = false;
            } else if ($fechaCot.val() && $fechaVal.val() < $fechaCot.val()) {
                marcarInvalido($fechaVal, 'La fecha de validez no puede ser anterior a la fecha de cotización.');
                esValido = false;
            } else {
                marcarValido($fechaVal);
            }

            // Al menos 1 producto
            let $rows = $('#productos-container .product-item');
            if ($rows.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Sin productos', text: 'Debe agregar al menos un producto a la cotización.', showConfirmButton: false, timer: 2500 });
                return false;
            }

            // Validar cada fila de producto
            let productoSinSeleccionar = false;
            $rows.each(function () {
                let $row = $(this);

                if (!$row.find('.producto-id-input').val()) {
                    productoSinSeleccionar = true;
                }

                let $cant = $row.find('.cantidad-input');
                let cant = parseInt($cant.val());
                if (isNaN(cant) || cant < 1) {
                    marcarInvalido($cant, 'La cantidad debe ser al menos 1.');
                    esValido = false;
                } else {
                    marcarValido($cant);
                }

                let $precio = $row.find('.precio-unitario-input');
                let precio = parseFloat($precio.val());
                if (isNaN(precio) || precio < 0) {
                    marcarInvalido($precio, 'El precio no puede ser negativo.');
                    esValido = false;
                } else {
                    marcarValido($precio);
                }
            });

            if (productoSinSeleccionar) {
                Swal.fire({ icon: 'warning', title: 'Producto no seleccionado', text: 'Cada fila de producto debe tener un producto asignado antes de guardar.', showConfirmButton: false, timer: 3000 });
                esValido = false;
            }

            return esValido;
        }

        // Envío del formulario de cotización (crear/editar)
        $('#cotizacionForm').on('submit', function (e) {
            e.preventDefault();
            if (!validarFormularioCotizacion()) return;
            let formData = new FormData(this);
            var id = $('#id-field').val();
            var url = id ? '/cotizaciones/' + id : '/cotizaciones';

            const clienteId = $('#cliente-id-field').val();
            if (!formData.get('cliente_id') && clienteId) {
                formData.set('cliente_id', clienteId);
            }

            if (id) {
                formData.append('_method', 'PUT');
            }
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: response.success,
                        icon: 'success',
                        showConfirmButton: false,
                        customClass: {
                            confirmButton: 'btn btn-primary w-xs me-2',
                            cancelButton: 'btn btn-danger w-xs'
                        },
                        buttonsStyling: false,
                        showCloseButton: true,
                        timer: 1500
                    })
                    $('#showModal').modal('hide');
                    table.ajax.reload();
                },
                error: function (xhr) {
                    var errorMessage = '';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        for (var key in errors) {
                            errorMessage += errors[key][0] + '\n';
                        }
                    } else {
                        errorMessage = 'Ocurrió un error inesperado.';
                    }
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        customClass: {
                            confirmButton: 'btn btn-primary w-xs me-2',
                            cancelButton: 'btn btn-danger w-xs'
                        },
                        buttonsStyling: false,
                        showCloseButton: true
                    })
                }
            });
        });

        // Botón de editar
        $('#cotizaciones-table').on('click', '.edit-btn', function () {
            var id = $(this).data('id');
            $('#modalTitle').text('Editar Cotización');
            $('#id-field').val(id);
            $('#add-btn').hide();
            $('#edit-btn').show();

            $('#productos-container').empty();
            window.cotCart = [];
            window.cotGroupBordadosState = {};
            if (typeof window.cotRefreshGroupedList === 'function') window.cotRefreshGroupedList();
            $.ajax({
                url: '/cotizaciones/' + id,
                method: 'GET',
                success: function (data) {
                    $('#cliente-id-field').val(data.cliente_id || '').prop('disabled', false).addClass('campo-protegido');
                    // Obtener datos del cliente desde la relación
                    if (data.cliente) {
                        $('#cliente-email-field').val(data.cliente.email || '');
                        $('#cliente-telefono-field').val(data.cliente.telefono || '');
                        var documento = data.cliente.documento || '';
                        var prefix = 'V-';
                        if (documento) {
                            prefix = documento.substring(0, 2);
                            var number = documento.substring(2);
                            $('#ci-rif-prefix-field').val(prefix);
                            $('#ci-rif-number-field').val(number);
                            $('#ci-rif-full-field').val(documento);
                        }
                        aplicarLayoutCliente(prefix, data.cliente.nombre, data.cliente.apellido || '');

                        // Pintar la tarjeta + banner del cliente (antes quedaba el placeholder).
                        if (typeof window.cotMostrarTarjetaCliente === 'function') {
                            window.cotMostrarTarjetaCliente({
                                persona_id:     data.cliente.id,
                                tipo_documento: data.cliente.tipo_documento || prefix,
                                documento:      documento,
                                nombre:         data.cliente.nombre || '',
                                apellido:       data.cliente.apellido || '',
                                razon_social:   data.cliente.razon_social || '',
                                telefono:       data.cliente.telefono || '',
                                email:          data.cliente.email || '',
                                roles:          data.cliente.roles || []
                            }, data.cliente_id);
                        }
                    }
                    // Formatear fechas para input date (YYYY-MM-DD)
                    var fechaCotizacion = data.fecha_cotizacion ? data.fecha_cotizacion.split('T')[0] : '';
                    var fechaValidez = data.fecha_validez ? data.fecha_validez.split('T')[0] : '';

                    $('#fecha-cotizacion-field').val(fechaCotizacion).prop('readonly', true).addClass('campo-protegido');
                    if (fechaValidez) {
                        $('#fecha-validez-field').val(fechaValidez);
                        $('.cot-date-chip').removeClass('is-active');
                    } else {
                        // Legacy sin fecha de validez: pre-sembrar el fallback vigente
                        // (emisión + vigencia por defecto) para que pueda guardarse.
                        window.cotSeedValidez(fechaCotizacion, window.COT_DIAS_VIGENCIA);
                    }
                    $('#estado-field').val(data.estado);
                    $('#prioridad-field').val(data.prioridad || 'Normal');
                    $('#notas-field').val(data.notas || '');
                    // Actualizar banner de fecha (val() programático no dispara change)
                    if (window.cotWizard && window.cotWizard.refreshBannerFecha) window.cotWizard.refreshBannerFecha();
                    // Creador real: mostrar quién creó la cotización (no el editor)
                    if (data.creador) {
                        $('#cot-creador-name').text(data.creador.name || '—');
                        if (data.creador.avatar_url) $('#cot-creador-avatar').attr('src', data.creador.avatar_url);
                    }
                    // Cargar productos existentes
                    $('#productos-container').empty();
                    var __detalles = (data.productos || []);

                    // Precargar los tipos de las líneas DINÁMICAS (producto_id null + tipo_producto_id):
                    // el snapshot guarda nombres atributo→valor, así que necesitamos los valores del tipo
                    // para resolver los atributo_valor_ids al reconstruir el producto virtual.
                    var __tipoCache = {};
                    var __tipoFetches = [];
                    var __tipoIds = {};
                    __detalles.forEach(function (d) {
                        if (!d.producto_id && d.tipo_producto_id) __tipoIds[d.tipo_producto_id] = true;
                    });
                    Object.keys(__tipoIds).forEach(function (tid) {
                        __tipoFetches.push(
                            $.getJSON("{{ url('tipo-productos') }}/" + tid).done(function (t) { __tipoCache[tid] = t; })
                        );
                    });

                    function __finalizarCargaEdicion() {
                        setTimeout(function () {
                            calculateCotizacionTotals();
                            if (typeof window.cotRefreshGroupedList === 'function') window.cotRefreshGroupedList();
                            if (window.cotWizard) window.cotWizard.refreshKPIs();
                            try {
                                $('.product-select').each(function () {
                                    if ($(this).hasClass("select2-hidden-accessible")) { $(this).select2('destroy'); }
                                    $(this).select2({
                                        theme: 'bootstrap-5', placeholder: '🔍 Buscar producto...',
                                        allowClear: true, width: '100%', dropdownParent: $('#showModal'),
                                        language: {
                                            noResults: function () { return 'No se encontraron productos'; },
                                            searching: function () { return 'Buscando...'; }
                                        }
                                    });
                                });
                            } catch (e) { console.error('Error inicializando Select2:', e); }
                            $('#showModal').modal('show');
                        }, 200);
                    }

                    $.when.apply($, __tipoFetches).always(function () {
                        productItemIndex = 0;
                        __detalles.forEach(function (detalle) {
                            var recargoUnitario = (detalle.bordados || []).reduce(function (acc, bordado) {
                                var precio = parseFloat(bordado.precio_aplicado || 0);
                                var cantidad = Math.max(1, parseInt(bordado.cantidad || 1, 10));
                                return acc + (precio * cantidad);
                            }, 0);
                            var precioBase = Math.max(0, (parseFloat(detalle.precio_unitario || 0) - recargoUnitario));

                            // Línea dinámica (sin producto_id): reconstruir el producto virtual desde el snapshot.
                            var productoId = detalle.producto_id;
                            if (!productoId && detalle.tipo_producto_id && typeof window.cotReconstruirVirtualDesdeDetalle === 'function') {
                                productoId = window.cotReconstruirVirtualDesdeDetalle(detalle, __tipoCache[detalle.tipo_producto_id], precioBase);
                            }

                            addProductItem(
                                productoId,
                                detalle.cantidad,
                                precioBase,
                                detalle.descripcion,
                                detalle.lleva_bordado,
                                '',
                                detalle.color_id || null,
                                detalle.talla_id || null,
                                detalle.genero_id || defaultGeneroId(),
                                detalle.bordados || []
                            );

                            // Poblar cotGroupBordadosState con bordados del server (usar el id real de la línea)
                            if ((detalle.bordados || []).length > 0) {
                                var gKey = String(productoId) + '|' + String(detalle.color_id || '');
                                if (!window.cotGroupBordadosState[gKey]) {
                                    window.cotGroupBordadosState[gKey] = { bordados: detalle.bordados };
                                }
                            }
                        });
                        __finalizarCargaEdicion();
                    });
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo cargar la cotización para editar',
                        customClass: {
                            confirmButton: 'btn btn-primary w-xs me-2',
                            cancelButton: 'btn btn-danger w-xs'
                        },
                        buttonsStyling: false,
                        showCloseButton: true
                    });
                }
            });
        });

        // Botón de ver detalles
        // ── Wizard de solo lectura — navegación ──────────────────────────
        (function () {
            var VIEW_TOTAL_STEPS = 3;
            var viewCurrentStep  = 1;

            function viewShowStep(n) {
                viewCurrentStep = n;
                $('#viewModal .wiz-step-content').each(function () {
                    var s = parseInt($(this).data('step'));
                    $(this).toggleClass('is-active', s === n);
                    s === n ? $(this).removeAttr('hidden') : $(this).attr('hidden', '');
                });
                $('#viewModal .wiz-step-marker').each(function () {
                    var s = parseInt($(this).data('step'));
                    $(this).toggleClass('is-active', s === n).toggleClass('is-done', s < n);
                    $(this).attr('aria-selected', s === n ? 'true' : 'false');
                });
                $('#viewModal .wiz-step-line-fill').each(function () {
                    $(this).toggleClass('is-filled', parseInt($(this).data('line')) < n);
                });
                $('#btn-view-prev').toggle(n > 1);
                $('#btn-view-next').toggle(n < VIEW_TOTAL_STEPS);
            }

            window.viewCotShowStep = viewShowStep;

            $('#viewModal').on('show.bs.modal', function () { viewShowStep(1); });
            $(document).on('click', '#btn-view-next',  function () { if (viewCurrentStep < VIEW_TOTAL_STEPS) viewShowStep(viewCurrentStep + 1); });
            $(document).on('click', '#btn-view-prev',  function () { if (viewCurrentStep > 1) viewShowStep(viewCurrentStep - 1); });
            $(document).on('click', '#viewModal .wiz-step-marker', function () { viewShowStep(parseInt($(this).data('step'))); });
        }());

        // ── Render grilla de productos (solo lectura) ─────────────────
        function viewRenderProductosGrilla(productos, tasaCotizacion) {
            var $cont  = $('#view-productos-container');
            var $empty = $('#view-productos-empty');
            $cont.empty();

            if (!productos || !productos.length) {
                $empty.removeAttr('hidden');
                $('#view-kpi-lineas').text('0');
                $('#view-kpi-total').text('$0.00');
                $('#view-kpi-total-bs').text('');
                return;
            }
            $empty.attr('hidden', '');

            // Agrupar por producto_id + color_id
            var groups = {}, groupOrder = [];
            productos.forEach(function (item) {
                var key = (item.producto_id || 'x') + '_' + (item.color_id || 'x');
                if (!groups[key]) {
                    groups[key] = { key: key, items: [], producto: item.producto, color_id: item.color_id, precio_unitario: item.precio_unitario };
                    groupOrder.push(key);
                }
                groups[key].items.push(item);
            });

            // KPIs
            var grandTotal = productos.reduce(function (acc, it) {
                return acc + parseFloat(it.precio_unitario) * parseInt(it.cantidad);
            }, 0);
            $('#view-kpi-lineas').text(groupOrder.length);
            $('#view-kpi-total').text('$' + grandTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            // Respeta la tasa guardada en la cotización; si no tiene, usa la BCV del día
            var viewKpiBs = (typeof window.bsEquivalente === 'function')
                ? window.bsEquivalente(grandTotal, tasaCotizacion) : null;
            $('#view-kpi-total-bs').text(viewKpiBs ? ('≈ ' + viewKpiBs) : '');

            // Filas de la tabla
            var rows = groupOrder.map(function (key, idx) {
                var g       = groups[key];
                var items   = g.items;
                var prod    = g.producto || {};
                var colorObj = getColorById(g.color_id) || {};
                var colorNom = colorObj.nombre || '—';
                var colorHex = colorObj.hex || colorObj.color_hex || '#94a3b8';

                var tallasHtml = items.map(function (it) {
                    var lbl = getTallaLabel(it.talla_id) || 'S/T';
                    var gen = getGeneroNombre(it.genero_id);
                    var genHtml = gen ? '<span class="cot-chip-gen">' + gen + '</span>' : '';
                    return '<span class="cot-chip cot-chip-talla">' + lbl + genHtml + '<span class="cot-chip-x">×</span>' + it.cantidad + '</span>';
                }).join('');

                var totalQty = items.reduce(function (a, it) { return a + parseInt(it.cantidad); }, 0);
                var subtotal = items.reduce(function (a, it) { return a + parseFloat(it.precio_unitario) * parseInt(it.cantidad); }, 0);
                var llevaBordado = items.some(function (it) { return it.lleva_bordado; });

                // Detalle de bordados para tooltip/línea extra
                var bordadosDetalle = '';
                if (llevaBordado) {
                    var blines = [];
                    items.forEach(function (it) {
                        if (it.bordados && it.bordados.length) {
                            it.bordados.forEach(function (b) {
                                var bNom = (b.logo ? b.logo.name : null) || b.nombre_logo_aplicado || 'Logo';
                                blines.push(bNom + ' → ' + (b.nombre_aplicado || '—') + ' ×' + (b.cantidad || 1));
                            });
                        }
                    });
                    if (blines.length) {
                        bordadosDetalle = '<div class="cot-prod-variant text-truncate" title="' + blines.join(' | ') + '">' +
                            '<i class="ri-scissors-cut-line me-1"></i>' + blines[0] + (blines.length > 1 ? ' +' + (blines.length - 1) : '') + '</div>';
                    }
                }

                var bordadoLineClass = llevaBordado ? 'cot-grouped-bordado-line--ok' : 'cot-grouped-bordado-line--none';
                var bordadoText      = llevaBordado ? 'Con bordado' : 'Sin bordado';
                // Servicio de bordado: precio del recargo + equivalente en Bs (tasa guardada de la cotización)
                var recargoBordadoGrupo = items.reduce(function (a, it) {
                    if (!it.bordados || !it.bordados.length) return a;
                    var recU = it.bordados.reduce(function (s, b) { return s + (parseFloat(b.precio_aplicado) || 0) * Math.max(1, parseInt(b.cantidad || 1, 10)); }, 0);
                    return a + recU * parseInt(it.cantidad);
                }, 0);
                var bordadoBsExtra = '';
                if (llevaBordado && recargoBordadoGrupo > 0) {
                    var bsB = (typeof window.bsEquivalente === 'function') ? window.bsEquivalente(recargoBordadoGrupo, tasaCotizacion) : null;
                    bordadoBsExtra = ' · $' + recargoBordadoGrupo.toFixed(2) + (bsB ? ' · ' + bsB : '');
                }
                var prodNombre = prod.nombre_completo || prod.nombre || prod.codigo || 'Producto';
                var prodCodigo = prod.codigo || '';
                var precioU    = parseFloat(g.precio_unitario);

                return '<tr class="cot-grouped-row">' +
                    '<td class="cot-col-num">' + (idx + 1) + '</td>' +
                    '<td class="cot-col-prod">' +
                        '<div class="cot-prod-modelo">' + prodNombre + '</div>' +
                        (prodCodigo ? '<div class="cot-prod-codigo"><i class="ri-barcode-line me-1"></i>' + prodCodigo + '</div>' : '') +
                        bordadosDetalle +
                    '</td>' +
                    '<td class="cot-col-color">' +
                        '<span class="cot-color-cell">' +
                            '<span class="cot-color-dot" style="background:' + colorHex + '"></span>' +
                            colorNom +
                        '</span>' +
                    '</td>' +
                    '<td class="cot-col-tallas">' +
                        '<div class="cot-tallas-wrap">' + tallasHtml + '</div>' +
                        '<div class="cot-grouped-bordado-line ' + bordadoLineClass + '">' +
                            '<i class="ri-scissors-cut-line"></i>' + bordadoText + bordadoBsExtra +
                        '</div>' +
                    '</td>' +
                    '<td class="cot-col-num cot-cell-num"><strong>' + totalQty + '</strong></td>' +
                    '<td class="cot-cell-num">$' + precioU.toFixed(2) + '</td>' +
                    '<td class="cot-cell-num cot-cell-subtotal">$' + subtotal.toFixed(2) + '</td>' +
                    '</tr>';
            }).join('');

            $cont.html(
                '<div class="cot-grouped-tablewrap">' +
                '<table class="cot-grouped-table">' +
                '<thead><tr>' +
                    '<th class="cot-col-num">#</th>' +
                    '<th class="cot-col-prod">Producto</th>' +
                    '<th class="cot-col-color">Color</th>' +
                    '<th class="cot-col-tallas">Tallas y cantidades</th>' +
                    '<th class="cot-col-num">Unid.</th>' +
                    '<th class="cot-cell-num">Precio U.</th>' +
                    '<th class="cot-cell-num">Subtotal</th>' +
                '</tr></thead>' +
                '<tbody>' + rows + '</tbody>' +
                '</table></div>'
            );
        }

        // ── Click en botón "Ver" ──────────────────────────────────────
        $('#cotizaciones-table').on('click', '.view-btn', function () {
            var id = $(this).data('id');
            $.ajax({
                url: '/cotizaciones/' + id,
                method: 'GET',
                success: function (data) {
                    // Paso 1 — Cliente
                    if (data.cliente) {
                        // Jurídico (J-/G-): mostrar Razón Social como nombre y ocultar "Apellido".
                        // Natural (V-/E-): Nombre + Apellido como siempre.
                        var esJuridico = ['J-', 'G-'].includes(String(data.cliente.tipo_documento || '').toUpperCase());
                        var nombreBase = esJuridico
                            ? (data.cliente.razon_social || data.cliente.nombre || 'N/A')
                            : (data.cliente.nombre || 'N/A');
                        var nombreHtml = nombreBase;
                        if (data.cliente.eliminado) {
                            nombreHtml += ' <span class="badge bg-danger ms-1" title="Este cliente fue eliminado">Eliminado</span>';
                        }
                        $('#view-cliente-nombre').html(nombreHtml);
                        var muted = data.cliente.eliminado;
                        function vm(v) { return muted ? '<span class="text-muted">' + (v || '') + '</span>' : (v || 'N/A'); }
                        $('#view-cliente-email').html(vm(data.cliente.email));
                        $('#view-cliente-telefono').html(vm(data.cliente.telefono));
                        $('#view-ci-rif').html(vm(data.cliente.documento));
                    } else {
                        $('#view-cliente-nombre').html('<span class="text-danger">Cliente no encontrado</span>');
                        $('#view-cliente-email, #view-cliente-telefono, #view-ci-rif').text('N/A');
                    }

                    function formatDate(dateStr) {
                        if (!dateStr) return 'N/A';
                        var d = new Date(dateStr);
                        if (isNaN(d.getTime())) return dateStr;
                        return String(d.getDate()).padStart(2,'0') + '/' +
                               String(d.getMonth()+1).padStart(2,'0') + '/' + d.getFullYear();
                    }
                    $('#view-fecha-cotizacion').text(formatDate(data.fecha_cotizacion));
                    $('#view-fecha-validez').text(formatDate(data.fecha_validez));

                    var estadoClasses = { 'Pendiente':'status-pendiente','Aprobada':'status-aprobada','Convertida':'badge-soft-info','Cancelada':'status-cancelado','Vencida':'status-cancelado' };
                    var estadoIcons   = { 'Pendiente':'ri-time-line','Aprobada':'ri-check-double-line','Convertida':'ri-exchange-line','Cancelada':'ri-close-circle-line','Vencida':'ri-alarm-warning-line' };
                    var badgeClass = estadoClasses[data.estado] || '';
                    var icon       = estadoIcons[data.estado]   || 'ri-question-line';
                    $('#view-estado').html('<span class="badge badge-status ' + badgeClass + ' rounded-pill"><i class="' + icon + ' me-1"></i>' + data.estado + '</span>');
                    // Chip "Creada por" (gutter derecho del stepper)
                    var cotCreador = data.creador || (data.user ? { name: data.user.name } : null);
                    $('#view-usuario-creador').text(cotCreador ? cotCreador.name : '');
                    $('#view-cot-creador-avatar').attr('src', (cotCreador && cotCreador.avatar_url) ? cotCreador.avatar_url : window.AMS_AVATAR_FALLBACK).css('display', '');
                    $('#view-cot-creador-fecha').text(cotCreador && cotCreador.fecha ? cotCreador.fecha : '—');

                    // Paso 2 — Productos (grilla)
                    viewRenderProductosGrilla(data.productos, data.tasa_cambio_valor);

                    // Paso 3 — Resumen
                    var subtotalUsd = (data.productos || []).reduce(function (acc, it) {
                        return acc + parseFloat(it.precio_unitario) * parseInt(it.cantidad);
                    }, 0);
                    var ivaUsd   = subtotalUsd * 0.16;
                    var totalUsd = subtotalUsd + ivaUsd;
                    $('#view-resumen-subtotal').text(formatMoney(subtotalUsd));
                    $('#view-resumen-iva').text(formatMoney(ivaUsd));
                    $('#view-total').text(formatMoney(totalUsd));
                    // Equivalentes en Bs con la tasa GUARDADA de la cotización (snapshot
                    // a su fecha), no la del día. bsEquivalente/bsTasaFmt caen a la tasa
                    // vigente solo si la cotización no tiene snapshot.
                    var bsLbl = (typeof window.bsEquivalente === 'function')
                        ? window.bsEquivalente(totalUsd, data.tasa_cambio_valor) : null;
                    $('#view-total-bs').text(bsLbl || 'Sin tasa BCV');
                    var tasaCotFmt = (typeof window.bsTasaFmt === 'function')
                        ? window.bsTasaFmt(data.tasa_cambio_valor) : null;
                    $('#view-resumen-tasa').text(tasaCotFmt || '—');
                    $('#view-resumen-tasa-fecha').text(data.tasa_fecha_fmt ? ' (' + data.tasa_fecha_fmt + ')' : '');

                    // PDF de ESTA cotización
                    $('#view-pdf-btn').attr('href', '/cotizaciones/' + id + '/pdf');

                    $('#viewModal').modal('show');
                }
            });
        });

        // Botón de eliminar
        $('#cotizaciones-table').on('click', '.remove-btn', function () {
            var id = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary w-xs me-2',
                    cancelButton: 'btn btn-danger w-xs'
                },
                buttonsStyling: false,
                showCloseButton: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/cotizaciones/' + id,
                        method: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            Swal.fire({
                                title: '¡Eliminado!',
                                text: response.success,
                                icon: 'success',
                                showConfirmButton: false,
                                customClass: {
                                    confirmButton: 'btn btn-primary w-xs me-2',
                                    cancelButton: 'btn btn-danger w-xs'
                                },
                                buttonsStyling: false,
                                showCloseButton: true,
                                timer: 1500
                            })
                            table.ajax.reload();
                        },
                        error: function (xhr) {
                            Swal.fire({
                                title: 'Error!',
                                text: 'No se pudo eliminar la cotización.',
                                icon: 'error',
                                customClass: {
                                    confirmButton: 'btn btn-primary w-xs me-2',
                                    cancelButton: 'btn btn-danger w-xs'
                                },
                                buttonsStyling: false,
                                showCloseButton: true
                            })
                        }
                    });
                }
            });
        });

        // === REACTIVAR COTIZACIÓN VENCIDA ===
        $('#cotizaciones-table').on('click', '.reactivar-btn', function () {
            var id = $(this).data('id');
            Swal.fire({
                title: '¿Reactivar cotización?',
                text: 'La cotización volverá a estado Pendiente con {{ \App\Models\Cotizacion::diasVigencia() }} días de validez desde hoy.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, reactivar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary w-xs me-2',
                    cancelButton: 'btn btn-light w-xs'
                },
                buttonsStyling: false,
                showCloseButton: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/cotizaciones/' + id + '/reactivar',
                        method: 'POST',
                        data: { _token: $('meta[name="csrf-token"]').attr('content') },
                        success: function (response) {
                            Swal.fire({ icon: 'success', title: '¡Reactivada!', text: response.success, showConfirmButton: false, timer: 1800 });
                            table.ajax.reload();
                        },
                        error: function (xhr) {
                            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'No se pudo reactivar.';
                            Swal.fire({ icon: 'error', title: 'Error', text: msg, showCloseButton: true });
                        }
                    });
                }
            });
        });

        // === CAMBIO DE ESTADO VIA DROPDOWN ===
        $('#cotizaciones-table').on('click', '.change-status-btn', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            var status = $(this).data('status');

            Swal.fire({
                title: '¿Cambiar estado?',
                text: "El estado cambiará a: " + status,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary w-xs me-2',
                    cancelButton: 'btn btn-danger w-xs'
                },
                buttonsStyling: false,
                showCloseButton: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/cotizaciones/' + id + '/estado',
                        method: 'PUT',
                        data: {
                            estado: status,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            Swal.fire({
                                title: '¡Actualizado!',
                                text: response.success,
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            table.ajax.reload();
                        },
                        error: function (xhr) {
                            var msg = 'No se pudo actualizar el estado.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                msg = xhr.responseJSON.error;
                            }
                            Swal.fire({
                                title: 'Error',
                                text: msg,
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });

        // === CONVERTIR COTIZACIÓN A PEDIDO ===
        // Redirige al wizard de pedidos pre-hidratado con los datos de la cotización
        $('#cotizaciones-table').on('click', '.convert-to-pedido-btn', function () {
            var cotizacionId = $(this).data('id');
            window.location.href = '/pedidos?convertir=' + cotizacionId;
        });

        // === AUTOCOMPLETADO UNIFICADO DE PERSONA (cliente + empleado + proveedor) ===
        // Busca en la tabla 'persona' para evitar duplicar identidades cuando un
        // empleado o proveedor también compra. Si la persona no es cliente todavía,
        // ofrece crear el cliente reutilizando los datos existentes.
        let clienteSeleccionado = null;
        let autocompleteTimeout = null;

        // Mapeo de roles → etiquetas y clases de badge (estándar Bootstrap del proyecto)
        const ROLE_BADGES = {
            cliente:           { label: 'Cliente',   cls: 'bg-success-subtle text-success' },
            empleado:          { label: 'Empleado',  cls: 'bg-info-subtle text-info' },
            proveedor_natural: { label: 'Proveedor', cls: 'bg-warning-subtle text-warning' },
            proveedor_juridico:{ label: 'Proveedor', cls: 'bg-warning-subtle text-warning' },
        };

        function renderRoleBadges(roles) {
            // Deduplicar (ej. proveedor_natural y proveedor_juridico nunca van juntos, pero por seguridad)
            const seen = new Set();
            return (roles || []).map(function (r) {
                const meta = ROLE_BADGES[r];
                if (!meta || seen.has(meta.label)) return '';
                seen.add(meta.label);
                return `<span class="badge ${meta.cls} ms-1">${meta.label}</span>`;
            }).join('');
        }

        function rolesLabelLegible(roles) {
            const labels = [];
            const seen = new Set();
            (roles || []).forEach(function (r) {
                const meta = ROLE_BADGES[r];
                if (meta && !seen.has(meta.label)) {
                    seen.add(meta.label);
                    labels.push(meta.label);
                }
            });
            if (labels.length === 0) return '';
            if (labels.length === 1) return labels[0];
            return labels.slice(0, -1).join(', ') + ' y ' + labels[labels.length - 1];
        }

        // Aplica los datos de una persona/cliente al formulario de cotización
        function aplicarPersonaACotizacion(persona, clienteId) {
            $('#cliente-id-field').val(clienteId || '');

            const docString = String(persona.documento || '').trim();
            let prefix = 'V-', number = '';
            if (docString) {
                if (/^[VJEG]-/.test(docString)) {
                    prefix = docString.substring(0, 2);
                    number = docString.substring(2);
                } else {
                    number = docString;
                    prefix = (docString.length >= 8 && /^[2-9]/.test(docString)) ? 'J-' : 'V-';
                }
            }
            $('#ci-rif-prefix-field').val(prefix);
            $('#ci-rif-number-field').val(number);
            $('#ci-rif-full-field').val(prefix + number);

            // Para jurídicos, usar razon_social como nombre si está disponible
            const nombreMostrar = (prefix === 'J-' || prefix === 'G-') && persona.razon_social
                ? persona.razon_social
                : persona.nombre;

            aplicarLayoutCliente(prefix, nombreMostrar, persona.apellido || '');
            $('#cliente-email-field').val(persona.email || '');
            $('#cliente-telefono-field').val(persona.telefono || '');
            $('#cliente-autocomplete-list').empty().hide();
            clienteSeleccionado = true;

            // Wizard UX: mostrar tarjeta visual del cliente seleccionado
            if (typeof window.cotMostrarTarjetaCliente === 'function') {
                window.cotMostrarTarjetaCliente(persona, clienteId);
            }
        }

        $('#ci-rif-number-field').on('input', function () {
            const query = $(this).val();
            clearTimeout(autocompleteTimeout);
            if (query.length < 6) {
                $('#cliente-autocomplete-list').empty().hide();
                if (typeof window.cotShowLoading === 'function') window.cotShowLoading(false);
                return;
            }
            // Mostrar skeleton si no hay cliente cargado
            if (!$('#cliente-id-field').val() && typeof window.cotShowLoading === 'function') {
                window.cotShowLoading(true);
            }
            autocompleteTimeout = setTimeout(function () {
                $.ajax({
                    url: '/personas-search',
                    data: { q: query },
                    complete: function () {
                        if (typeof window.cotShowLoading === 'function') window.cotShowLoading(false);
                    },
                    success: function (personas) {
                        let html = '';
                        if (personas.length > 0) {
                            personas.forEach(function (p, idx) {
                                const isJuridico = p.tipo_documento === 'J-' || p.tipo_documento === 'G-';
                                const nombreCompleto = isJuridico && p.razon_social
                                    ? p.razon_social
                                    : (p.apellido ? `${p.nombre} ${p.apellido}` : p.nombre);
                                const badges = renderRoleBadges(p.roles);
                                html += `<button type="button" class="list-group-item list-group-item-action persona-autocomplete-item" data-idx="${idx}">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                                        <div>
                                            <span class="fw-semibold">${p.documento || 'Sin documento'}</span>
                                            <span class="text-muted">— ${nombreCompleto}</span>
                                            <small class="text-muted d-block">${p.email || 'Sin email'}</small>
                                        </div>
                                        <div>${badges}</div>
                                    </div>
                                </button>`;
                            });
                            // Guardar la respuesta para acceso rápido al hacer clic
                            $('#cliente-autocomplete-list').data('personas', personas);
                        } else {
                            html = '<div class="list-group-item disabled">No se encontraron registros</div>';
                            $('#cliente-autocomplete-list').removeData('personas');
                        }
                        $('#cliente-autocomplete-list').html(html).show();
                    }
                });
            }, 300);
        });

        // Selección de persona de la lista
        $(document).on('click', '.persona-autocomplete-item', function () {
            const idx = $(this).data('idx');
            const personas = $('#cliente-autocomplete-list').data('personas') || [];
            const persona = personas[idx];
            if (!persona) return;

            // Caso 1: Ya es cliente → aplicar directo
            if (persona.cliente_id) {
                aplicarPersonaACotizacion(persona, persona.cliente_id);
                return;
            }

            // Caso 2: No es cliente todavía → confirmar creación reutilizando datos
            const rolesTexto = rolesLabelLegible(persona.roles);
            const nombreMostrar = (persona.tipo_documento === 'J-' || persona.tipo_documento === 'G-') && persona.razon_social
                ? persona.razon_social
                : `${persona.nombre} ${persona.apellido || ''}`.trim();

            Swal.fire({
                title: '¿Crear cliente con datos existentes?',
                html: `<strong>${nombreMostrar}</strong> está registrado en el sistema como <strong>${rolesTexto}</strong> pero aún no es cliente.<br><br>¿Deseas crear el cliente reutilizando estos datos?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="ri-check-line me-1"></i>Sí, crear cliente',
                cancelButtonText: 'Cancelar',
                customClass: { confirmButton: 'btn btn-success w-xs me-2', cancelButton: 'btn btn-light w-xs' },
                buttonsStyling: false
            }).then(function (r) {
                if (!r.isConfirmed) return;

                $.ajax({
                    url: `/clientes/from-persona/${persona.persona_id}`,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (resp) {
                        if (resp.success && resp.cliente_id) {
                            aplicarPersonaACotizacion(persona, resp.cliente_id);
                            Swal.fire({
                                icon: 'success',
                                title: resp.reused ? '¡Listo!' : 'Cliente creado',
                                text: resp.message,
                                showConfirmButton: false,
                                timer: 1600
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo crear el cliente.' });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Error al crear el cliente.' });
                    }
                });
            });
        });

        // Ocultar lista al hacer click fuera
        $(document).on('click', function (e) {
            if (!$(e.target).closest('#ci-rif-number-field, #cliente-autocomplete-list').length) {
                $('#cliente-autocomplete-list').empty().hide();
            }
        });

        // ════════════════════════════════════════════════════════════════════
        //  MODAL DE BÚSQUEDA DE CLIENTE (lupa del paso 1)
        //  Abierto vía JS (no data-bs-toggle) para no cerrar el wizard padre.
        // ════════════════════════════════════════════════════════════════════
        (function () {
            var bclTimeout = null;

            function bclRender(clientes) {
                var $tbody = $('#bcl-tbody');
                var $empty = $('#bcl-empty');
                $tbody.empty();

                if (!clientes || clientes.length === 0) {
                    $empty.removeClass('d-none');
                    $('#bcl-count-label').text('');
                    return;
                }

                $empty.addClass('d-none');
                $('#bcl-count-label').text(clientes.length + ' cliente(s) encontrado(s)');

                clientes.forEach(function (c) {
                    var doc = c.documento || '';
                    var esJuridico = /^[JG]-/.test(doc);
                    var tipoBadge = esJuridico
                        ? '<span class="badge bg-soft-primary text-primary">Jurídico</span>'
                        : '<span class="badge bg-soft-success text-success">Natural</span>';
                    var nombre = (c.apellido ? c.nombre + ' ' + c.apellido : c.nombre) || '—';

                    $('<tr style="cursor:pointer;">')
                        .html(
                            '<td class="fw-semibold">' + nombre + '</td>'
                            + '<td class="font-monospace small">' + (doc || '—') + '</td>'
                            + '<td>' + tipoBadge + '</td>'
                            + '<td class="text-muted small">' + (c.telefono || '—') + '</td>'
                        )
                        .on('click', function () {
                            aplicarPersonaACotizacion(c, c.id);
                            $('#buscarClienteModal').modal('hide');
                            $('#bcl-input').val('');
                        })
                        .appendTo($tbody);
                });
            }

            function bclSearch(q) {
                $('#bcl-loading').removeClass('d-none');
                $('#bcl-empty').addClass('d-none');
                $.ajax({
                    url: '{{ route("clientes.search") }}',
                    data: { q: q },
                    success: function (data) {
                        $('#bcl-loading').addClass('d-none');
                        bclRender(data);
                    },
                    error: function () {
                        $('#bcl-loading').addClass('d-none');
                    }
                });
            }

            $('#cot-cliente-browse-btn').on('click', function () {
                $('#buscarClienteModal').modal('show');
            });

            $('#buscarClienteModal').on('shown.bs.modal', function () {
                $('#bcl-input').trigger('focus');
                if ($('#bcl-tbody tr').length === 0) {
                    bclSearch('');
                }
            }).on('hidden.bs.modal', function () {
                clearTimeout(bclTimeout);
            });

            $('#bcl-input').on('input', function () {
                clearTimeout(bclTimeout);
                bclTimeout = setTimeout(function () {
                    bclSearch($('#bcl-input').val().trim());
                }, 300);
            });

            $('#bcl-clear-btn').on('click', function () {
                $('#bcl-input').val('').trigger('focus');
                bclSearch('');
            });
        })();

        // --- MODAL AGREGAR CLIENTE DESDE COTIZACIÓN ---

        // Validación en tiempo real para nombre (solo letras y espacios)
        $(document).on('input', '#nombre-field-cliente', function () {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        });

        // Validación en tiempo real para apellido (solo letras y espacios)
        $(document).on('input', '#apellido-field-cliente', function () {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        });

        // Validación en tiempo real para documento (solo números)
        $(document).on('input', '#documento-number-field-cliente', function () {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        });

        // (Teléfonos los maneja telefonos-repeater.js)

        // Validación onblur para nombre
        $(document).on('blur', '#nombre-field-cliente', function () {
            let value = $(this).val().trim();
            if (value.length < 2) {
                $(this).addClass('is-invalid');
                $('#nombre-error-cliente').text('El nombre debe tener al menos 2 caracteres.').show();
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $('#nombre-error-cliente').hide();
            }
        });

        // Validación onblur para apellido
        $(document).on('blur', '#apellido-field-cliente', function () {
            let value = $(this).val().trim();
            if (value.length < 2) {
                $(this).addClass('is-invalid').removeClass('is-valid');
                $('#apellido-error-cliente').text('El apellido debe tener al menos 2 caracteres.').show();
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $('#apellido-error-cliente').hide();
            }
        });

        // Validación onblur para documento
        $(document).on('blur', '#documento-number-field-cliente', function () {
            let value = $(this).val().trim();
            if (value.length < 6) {
                $(this).addClass('is-invalid');
                $('#documento-error-cliente').text('El documento debe tener al menos 6 dígitos.').show();
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $('#documento-error-cliente').hide();
            }
        });


        // Validación onblur para email
        $(document).on('blur', '#email-field-cliente', function () {
            let value = $(this).val().trim();
            let regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (value.length > 0 && !regex.test(value)) {
                $(this).addClass('is-invalid');
                $('#email-error-cliente').text('Ingrese un email válido.').show();
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $('#email-error-cliente').hide();
            }
        });

        // Validación onblur para dirección (capitalizar primera letra + validar obligatorio)
        $(document).on('blur', '#direccion-field-cliente', function () {
            let value = $(this).val().trim();
            // Capitalizar primera letra
            if (value.length > 0) {
                $(this).val(value.charAt(0).toUpperCase() + value.slice(1));
            }
            // Validar mínimo 5 caracteres
            if (value.length < 5) {
                $(this).addClass('is-invalid').removeClass('is-valid');
                $('#direccion-error-cliente').text('La dirección debe tener al menos 5 caracteres.').show();
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $('#direccion-error-cliente').hide();
            }
        });

        // Dropdown dependiente: Poblar municipios cuando cambia el estado
        $(document).on('change', '#estado_territorial-field-cliente', function () {
            const estado = $(this).val();
            const municipios = getMunicipios(estado);
            const ciudadSelect = $('#ciudad-field-cliente');

            // Limpiar opciones anteriores
            ciudadSelect.empty();

            if (estado === '') {
                ciudadSelect.append('<option value="">Primero seleccione un estado</option>');
            } else {
                ciudadSelect.append('<option value="">Seleccione municipio</option>');
                municipios.forEach(function (municipio) {
                    ciudadSelect.append('<option value="' + municipio + '">' + municipio + '</option>');
                });
            }
        });

        // Limpiar validaciones al abrir modal
        $('#modalAddCliente').on('show.bs.modal', function () {
            $('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            $('.invalid-feedback').hide();
        });

        // === Lógica dinámica: Natural vs Jurídico/Gubernamental (modal cliente cotización) ===
        function toggleClienteFieldsCotizacion() {
            var tipo = $('#tipo_cliente-field-cliente').val();
            var $prefixSelect = $('#documento-prefix-field-cliente');
            var $docInput = $('#documento-number-field-cliente');

            if (tipo === 'natural' || tipo === '') {
                $('#campos-persona-natural-cliente').removeClass('d-none');
                $('#nombre-field-cliente').prop('required', true).prop('disabled', false);
                $('#apellido-field-cliente').prop('required', true).prop('disabled', false);

                $('#campos-razon-social-cliente').addClass('d-none');
                $('#razon-social-field-cliente').prop('required', false).prop('disabled', true).val('');

                $prefixSelect.html('<option value="V-">V-</option><option value="E-">E-</option>');
                $prefixSelect.prop('disabled', false);
                $docInput.attr('maxlength', '8');
                if ($docInput.val().length > 8) $docInput.val($docInput.val().slice(0, 8));

            } else if (tipo === 'juridico') {
                $('#campos-persona-natural-cliente').addClass('d-none');
                $('#nombre-field-cliente').prop('required', false).prop('disabled', true).val('');
                $('#apellido-field-cliente').prop('required', false).prop('disabled', true).val('');

                $('#campos-razon-social-cliente').removeClass('d-none');
                $('#razon-social-field-cliente').prop('required', true).prop('disabled', false);

                $prefixSelect.html('<option value="J-">J-</option>');
                $prefixSelect.prop('disabled', true);
                $docInput.attr('maxlength', '9');
                if ($docInput.val().length > 9) $docInput.val($docInput.val().slice(0, 9));

            } else if (tipo === 'gubernamental') {
                $('#campos-persona-natural-cliente').addClass('d-none');
                $('#nombre-field-cliente').prop('required', false).prop('disabled', true).val('');
                $('#apellido-field-cliente').prop('required', false).prop('disabled', true).val('');

                $('#campos-razon-social-cliente').removeClass('d-none');
                $('#razon-social-field-cliente').prop('required', true).prop('disabled', false);

                $prefixSelect.html('<option value="G-">G-</option>');
                $prefixSelect.prop('disabled', true);
                $docInput.attr('maxlength', '9');
                if ($docInput.val().length > 9) $docInput.val($docInput.val().slice(0, 9));
            }
        }
        $(document).on('change', '#tipo_cliente-field-cliente', toggleClienteFieldsCotizacion);

        // Abrir modal de agregar cliente
        $('#open-add-cliente-modal').on('click', function () {
            $('#clienteFormCotizacion')[0].reset();
            $('#id-field-cliente').val('');
            $('#modalClienteTitle').text('Agregar Cliente');
            $('#add-btn-cliente').show();
            $('#edit-btn-cliente').hide();
            // Reset valores por defecto
            $('#documento-prefix-field-cliente').val('V-');
            // Reset teléfonos del repetidor
            (function () {
                var r = document.getElementById('cot-cli-tel-repeater');
                if (window.TelefonosRepeater && r) { TelefonosRepeater.init(r); TelefonosRepeater.load(r, []); }
            })();
            $('#ciudad-field-cliente').html('<option value="">Primero seleccione un estado</option>');
            $('#tipo_cliente-field-cliente').val('');
            toggleClienteFieldsCotizacion();
            $('#modalAddCliente').modal('show');
        });

        // Submit del formulario de cliente
        $('#add-btn-cliente').off('click').on('click', function (e) {
            e.preventDefault();

            // Concatenar documento completo
            var documentoCompleto = $('#documento-prefix-field-cliente').val() + $('#documento-number-field-cliente').val();
            $('#documento-field-cliente').val(documentoCompleto);

            // Teléfonos: validar el repetidor del bloque
            var telRootCot = document.getElementById('cot-cli-tel-repeater');
            if (window.TelefonosRepeater && telRootCot) {
                var telChkCot = TelefonosRepeater.validate(telRootCot);
                if (!telChkCot.ok) {
                    Swal.fire({ icon: 'warning', title: 'Teléfonos', text: telChkCot.message });
                    return;
                }
            }
            var telTels = (window.TelefonosRepeater && telRootCot) ? TelefonosRepeater.collect(telRootCot) : [];
            var telefonoCompleto = ((telTels.find(function (t) { return t.es_principal; }) || telTels[0] || {}).numero) || '';

            // Validar campo apellido explícitamente
            var apellido = $('#apellido-field-cliente').val().trim();
            if (apellido.length < 2) {
                $('#apellido-field-cliente').addClass('is-invalid');
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: 'El campo Apellido es obligatorio (mínimo 2 caracteres)'
                });
                return;
            }
            $('#apellido-field-cliente').removeClass('is-invalid');

            // Validar campo dirección explícitamente
            var direccion = $('#direccion-field-cliente').val().trim();
            if (!direccion || direccion.length < 5) {
                $('#direccion-field-cliente').addClass('is-invalid');
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: 'El campo Dirección es obligatorio (mínimo 5 caracteres)'
                });
                return;
            }
            $('#direccion-field-cliente').removeClass('is-invalid');

            // Validar formulario antes de enviar
            var form = document.getElementById('clienteFormCotizacion');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Deshabilitar botón para evitar múltiples envíos
            $(this).prop('disabled', true);

            // Enviar por AJAX (sincronizar teléfonos[] en el form antes de serializar)
            if (window.TelefonosRepeater && telRootCot) {
                TelefonosRepeater.syncHiddenInputs(document.getElementById('clienteFormCotizacion'), telRootCot);
            }
            var formData = $('#clienteFormCotizacion').serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: '/clientes',
                type: 'POST',
                data: formData,
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message || 'Cliente creado exitosamente',
                        showConfirmButton: false,
                        timer: 2000
                    });

                    $('#modalAddCliente').modal('hide');

                    // Obtener el cliente_id del response
                    const clienteId = response.cliente_id;

                    // Rellenar los campos de la cotización con el nuevo cliente
                    const nombre = $('#nombre-field-cliente').val();
                    const apellido = $('#apellido-field-cliente').val();
                    const email = $('#email-field-cliente').val();
                    const telefono = telefonoCompleto;
                    const documento = documentoCompleto;

                    // Actualizar campo cliente_id en formulario de cotización
                    $('#cliente-id-field').val(clienteId);

                    // Separar prefijo y número del documento
                    let prefix = 'V-';
                    let number = '';
                    if (documento) {
                        if (documento.startsWith('V-') || documento.startsWith('J-') || documento.startsWith('E-') || documento.startsWith('G-')) {
                            prefix = documento.substring(0, 2);
                            number = documento.substring(2);
                        } else {
                            number = documento;
                            if (documento.length >= 8 && ['2', '3', '4', '5', '6', '7', '8', '9'].includes(documento.charAt(0))) {
                                prefix = 'J-';
                            } else {
                                prefix = 'V-';
                            }
                        }
                    }
                    $('#ci-rif-prefix-field').val(prefix);
                    $('#ci-rif-number-field').val(number);
                    $('#ci-rif-full-field').val(prefix + number);

                    // Layout dinámico según tipo de cliente
                    aplicarLayoutCliente(prefix, nombre, apellido || '');
                    $('#cliente-email-field').val(email || '');
                    $('#cliente-telefono-field').val(telefono || '');

                    // Re-habilitar botón
                    $('#add-btn-cliente').prop('disabled', false);
                },
                error: function (xhr) {
                    var errorMessage = '';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        for (var key in errors) {
                            errorMessage += errors[key][0] + '\n';
                        }
                    } else {
                        errorMessage = 'Ocurrió un error al crear el cliente.';
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });

                    // Re-habilitar botón
                    $('#add-btn-cliente').prop('disabled', false);
                }
            });
        });

        // ╔══════════════════════════════════════════════════════════════════
        // ║ COTIZACIONES — STEP 1 UX  (cliente card visual + chips + dates)
        // ║ Tarjeta del cliente seleccionado, prioridad/estado como chips,
        // ║ atajos de fecha, empty state y skeleton loading.
        // ╚══════════════════════════════════════════════════════════════════
        (function () {
            'use strict';

            // === Avatar: iniciales + color basado en hash ====================
            var AVATAR_COLORS = [
                { bg: '#1e3c72', fg: '#ffffff' },
                { bg: '#00b88c', fg: '#ffffff' },
                { bg: '#0c4a6e', fg: '#ffffff' },
                { bg: '#6b21a8', fg: '#ffffff' },
                { bg: '#b45309', fg: '#ffffff' },
                { bg: '#be123c', fg: '#ffffff' },
                { bg: '#0e7490', fg: '#ffffff' },
                { bg: '#365314', fg: '#ffffff' }
            ];

            function hashStr(s) {
                var h = 0;
                for (var i = 0; i < s.length; i++) {
                    h = ((h << 5) - h) + s.charCodeAt(i);
                    h |= 0;
                }
                return Math.abs(h);
            }

            function buildIniciales(persona) {
                if (!persona) return '—';
                var docPrefix = String(persona.tipo_documento || '').toUpperCase();
                var esJuridico = docPrefix === 'J-' || docPrefix === 'G-';
                if (esJuridico && persona.razon_social) {
                    var rs = persona.razon_social.trim();
                    var parts = rs.split(/\s+/).filter(Boolean);
                    if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
                    return rs.substring(0, 2).toUpperCase();
                }
                var n = (persona.nombre || '').trim();
                var a = (persona.apellido || '').trim();
                if (n && a) return (n[0] + a[0]).toUpperCase();
                if (n) return n.substring(0, 2).toUpperCase();
                return '—';
            }

            function pickAvatarColor(key) {
                var idx = hashStr(String(key || 'default')) % AVATAR_COLORS.length;
                return AVATAR_COLORS[idx];
            }

            // === Roles → badges ==============================================
            var ROLE_LABELS = {
                cliente:            { label: 'Cliente',   cls: 'cot-role-pill cot-role-cliente' },
                empleado:           { label: 'Empleado',  cls: 'cot-role-pill cot-role-empleado' },
                proveedor_natural:  { label: 'Proveedor', cls: 'cot-role-pill cot-role-proveedor' },
                proveedor_juridico: { label: 'Proveedor', cls: 'cot-role-pill cot-role-proveedor' }
            };

            function buildRolesBadges(roles) {
                if (!Array.isArray(roles) || !roles.length) return '';
                var seen = {};
                return roles.map(function (r) {
                    var meta = ROLE_LABELS[r];
                    if (!meta || seen[meta.label]) return '';
                    seen[meta.label] = true;
                    return '<span class="' + meta.cls + '">' + meta.label + '</span>';
                }).filter(Boolean).join('');
            }

            // === Tarjeta del cliente =========================================
            window.cotMostrarTarjetaCliente = function (persona, clienteId) {
                if (!persona) return;
                var docPrefix = String(persona.tipo_documento || '').toUpperCase();
                var esJuridico = docPrefix === 'J-' || docPrefix === 'G-';
                var nombre = esJuridico && persona.razon_social
                    ? persona.razon_social
                    : (persona.apellido ? persona.nombre + ' ' + persona.apellido : (persona.nombre || ''));

                var iniciales = buildIniciales(persona);
                var color = pickAvatarColor(persona.documento || persona.persona_id || nombre);

                $('#cot-cliente-empty').hide();
                $('#cot-cliente-loading').attr('hidden', true);
                $('#cot-cliente-card').removeAttr('hidden').show();

                var $av = $('#cot-cliente-avatar');
                $av.text(iniciales).css({ background: color.bg, color: color.fg });

                $('#cot-cliente-name-display').text(nombre || '—');
                $('#cot-cliente-doc-display').text(persona.documento || '—');

                var tel = persona.telefono || '';
                var email = persona.email || '';
                $('#cot-cliente-tel-wrap').toggle(!!tel);
                $('#cot-cliente-tel-display').text(tel || '—');
                $('#cot-cliente-email-wrap').toggle(!!email);
                $('#cot-cliente-email-display').text(email || '—');

                $('#cot-cliente-roles').html(buildRolesBadges(persona.roles));

                // Banner persistente (pasos 2+): misma fuente de datos que la card
                $('#cot-banner-avatar').text(iniciales).css({ background: color.bg, color: color.fg });
                $('#cot-banner-name').text(nombre || '—');
                $('#cot-banner-doc').text(persona.documento || '—');
                if (typeof window.cotActualizarBannerCliente === 'function') window.cotActualizarBannerCliente();

                var count = parseInt(persona.cotizaciones_count, 10);
                if (clienteId && !isNaN(count) && count > 0) {
                    $('#cot-cliente-stat-count').text(count);
                    var lastDate = persona.cotizaciones_last_date || '';
                    if (lastDate) {
                        var parts = String(lastDate).split('T')[0].split('-');
                        var pretty = parts.length === 3 ? (parts[2] + '/' + parts[1] + '/' + parts[0]) : lastDate;
                        $('#cot-cliente-stat-last').text(pretty);
                        $('#cot-cliente-stat-last-wrap').removeAttr('hidden').show();
                    } else {
                        $('#cot-cliente-stat-last-wrap').attr('hidden', true).hide();
                    }
                    $('#cot-cliente-stats').removeAttr('hidden').show();
                } else {
                    $('#cot-cliente-stats').attr('hidden', true).hide();
                }
            };

            window.cotResetearCliente = function () {
                $('#cot-cliente-card').attr('hidden', true).hide();
                $('#cot-cliente-loading').attr('hidden', true).hide();
                $('#cot-cliente-empty').show();
                $('#cliente-id-field').val('');
                $('#cliente-nombre-field').val('');
                $('#cliente-apellido-field').val('');
                $('#cliente-telefono-field').val('');
                $('#cliente-email-field').val('');
                $('#cliente-razon-social-display').val('');
                if (typeof clienteSeleccionado !== 'undefined') clienteSeleccionado = false;
                // Banner persistente: ocultar al limpiar el cliente
                $('#cot-cliente-banner').attr('hidden', true).attr('aria-hidden', 'true');
            };

            // Muestra/oculta el banner según paso y cliente seleccionado.
            // Visible solo en pasos 2+ (en el paso 1 ya está la card grande).
            window.cotActualizarBannerCliente = function () {
                var hasClient = !!$('#cliente-id-field').val();
                var step = (typeof currentStep !== 'undefined') ? currentStep : 1;
                var show = hasClient && step > 1;
                $('#cot-cliente-banner').attr('hidden', !show).attr('aria-hidden', show ? 'false' : 'true');
            };

            window.cotShowLoading = function (show) {
                if (show) {
                    if ($('#cliente-id-field').val()) return;
                    $('#cot-cliente-empty').hide();
                    $('#cot-cliente-loading').removeAttr('hidden').show();
                } else {
                    $('#cot-cliente-loading').attr('hidden', true).hide();
                    if (!$('#cliente-id-field').val()) $('#cot-cliente-empty').show();
                }
            };

            // Botón "Cambiar cliente"
            $(document).on('click', '#cot-cliente-change-btn', function (e) {
                e.preventDefault();
                window.cotResetearCliente();
                $('#ci-rif-number-field').val('').trigger('focus');
            });

            // === Chips de prioridad ==========================================
            $(document).on('click', '.cot-priority-chip', function () {
                var $b = $(this);
                var val = $b.data('value');
                $('.cot-priority-chip').removeClass('is-active').attr('aria-checked', 'false');
                $b.addClass('is-active').attr('aria-checked', 'true');
                $('#prioridad-field').val(val).trigger('change');
            });
            $(document).on('change', '#prioridad-field', function () {
                var val = $(this).val() || 'Normal';
                $('.cot-priority-chip').removeClass('is-active').attr('aria-checked', 'false');
                $('.cot-priority-chip[data-value="' + val + '"]').addClass('is-active').attr('aria-checked', 'true');
            });



            // === Atajos de fecha (validez) ==================================
            $(document).on('click', '.cot-date-chip', function () {
                var days = parseInt($(this).data('days'), 10) || 0;
                var emisionVal = $('#fecha-cotizacion-field').val();
                var base = emisionVal ? new Date(emisionVal + 'T00:00:00') : new Date();
                if (isNaN(base.getTime())) base = new Date();
                base.setDate(base.getDate() + days);
                var iso = window.cotFechaLocalISO(base);
                $('#fecha-validez-field').val(iso).trigger('change').trigger('blur');
                $('.cot-date-chip').removeClass('is-active');
                $(this).addClass('is-active');
            });

            // Reset al abrir modal en modo crear
            $('#showModal').on('show.bs.modal', function () {
                if (!$('#id-field').val()) {
                    window.cotResetearCliente();
                }
            });
        })();

        // ╔══════════════════════════════════════════════════════════════════
        // ║ COTIZACIONES — CATÁLOGO DE PRODUCTOS  (Fase 2)
        // ║ Modal anidado sobre el wizard que ofrece:
        // ║   · sidebar de filtros (búsqueda, tipos, precio)
        // ║   · grilla central de cards
        // ║   · carrito lateral (estructura, lleno en Fase 4)
        // ║ Click en card → abre Configurador (placeholder Fase 3).
        // ╚══════════════════════════════════════════════════════════════════
        (function () {
            'use strict';

            var catState = {
                search: '',
                tipos: new Set(),
                priceMin: null,
                priceMax: null,
                sort: 'relevance'
            };
            var catModalInstance = null;

            function getProductsList() {
                return (typeof products !== 'undefined' && Array.isArray(products)) ? products : [];
            }

            function getCatalogoTipos() {
                return (typeof catalogoTipos !== 'undefined' && Array.isArray(catalogoTipos)) ? catalogoTipos : [];
            }

            function getTipoCount(tipoId) {
                var t = getCatalogoTipos().find(function (x) { return x.id == tipoId; });
                return t ? (t.telas_count || 0) : 0;
            }

            function uniqueTipos() {
                return getCatalogoTipos().slice().sort(function (a, b) {
                    return String(a.nombre || '').localeCompare(String(b.nombre || ''));
                });
            }

            // Filtra los TIPOS del catálogo por búsqueda, tipos seleccionados y orden.
            function getFilteredTipos() {
                var list = getCatalogoTipos().slice();
                var q = (catState.search || '').toLowerCase().trim();
                list = list.filter(function (t) {
                    if (q) {
                        var nombre = (t.nombre || '').toLowerCase();
                        var prefijo = (t.prefijo || '').toLowerCase();
                        if (!nombre.startsWith(q) && !prefijo.startsWith(q)) return false;
                    }
                    if (catState.tipos.size && !catState.tipos.has(t.id)) return false;
                    return true;
                });
                if (catState.sort === 'price-asc') list.sort(function (a, b) { return (parseFloat(a.precio_confeccion) || 0) - (parseFloat(b.precio_confeccion) || 0); });
                else if (catState.sort === 'price-desc') list.sort(function (a, b) { return (parseFloat(b.precio_confeccion) || 0) - (parseFloat(a.precio_confeccion) || 0); });
                else list.sort(function (a, b) { return String(a.nombre || '').localeCompare(String(b.nombre || '')); });
                return list;
            }

            function renderFilterTipos() {
                var $cont = $('#cat-filter-tipos');
                var tipos = uniqueTipos();
                if (!tipos.length) {
                    $cont.html('<p class="text-muted small mb-0 ps-1"><em>Sin tipos disponibles</em></p>');
                    return;
                }
                var html = tipos.map(function (t) {
                    var checked = catState.tipos.has(t.id) ? 'checked' : '';
                    var count = getTipoCount(t.id);
                    return (
                        '<label class="cat-filter-item">' +
                            '<input type="checkbox" class="form-check-input cat-tipo-check" value="' + t.id + '" ' + checked + '>' +
                            '<span class="cat-filter-item-label">' + escapeForHtml(t.nombre) + '</span>' +
                            '<span class="cat-filter-item-count">' + count + '</span>' +
                        '</label>'
                    );
                }).join('');
                $cont.html(html);
            }

            function escapeForHtml(s) {
                return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
                    return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
                });
            }

            function getFilteredProducts() {
                var list = getProductsList().slice();
                var q = (catState.search || '').toLowerCase().trim();
                list = list.filter(function (p) {
                    if (p._dynamic) return false; // los productos virtuales no van al catálogo
                    if (q) {
                        var hay = ((p.codigo || '') + ' ' + (p.modelo || '') + ' ' +
                                   (p.tipo_producto ? p.tipo_producto.nombre : '')).toLowerCase();
                        if (!hay.includes(q)) return false;
                    }
                    if (catState.tipos.size && !catState.tipos.has(p.tipo_producto ? p.tipo_producto.id : null)) {
                        return false;
                    }
                    var price = parseFloat(p.precio_base || 0);
                    if (catState.priceMin != null && price < catState.priceMin) return false;
                    if (catState.priceMax != null && price > catState.priceMax) return false;
                    return true;
                });
                if (catState.sort === 'price-asc') list.sort(function (a, b) { return (parseFloat(a.precio_base) || 0) - (parseFloat(b.precio_base) || 0); });
                else if (catState.sort === 'price-desc') list.sort(function (a, b) { return (parseFloat(b.precio_base) || 0) - (parseFloat(a.precio_base) || 0); });
                else if (catState.sort === 'name') list.sort(function (a, b) {
                    return String(a.modelo || '').localeCompare(String(b.modelo || ''));
                });
                return list;
            }

            // Agrupa los productos filtrados por tipo. Un producto sin tipo
            // queda en una clave especial "sin-tipo".
            function groupByTipo(items) {
                var map = {};
                items.forEach(function (p) {
                    var tipo = p.tipo_producto;
                    var key = tipo ? ('t-' + tipo.id) : 'sin-tipo';
                    if (!map[key]) {
                        map[key] = {
                            tipo: tipo,
                            tipoNombre: tipo ? tipo.nombre : 'Sin tipo',
                            productos: [],
                            precioMin: Infinity,
                            precioMax: 0,
                            firstImg: null
                        };
                    }
                    map[key].productos.push(p);
                    var price = parseFloat(p.precio_base || 0);
                    if (price < map[key].precioMin) map[key].precioMin = price;
                    if (price > map[key].precioMax) map[key].precioMax = price;
                    if (!map[key].firstImg && p.imagen) map[key].firstImg = p.imagen;
                });
                return Object.values(map).sort(function (a, b) {
                    return String(a.tipoNombre || '').localeCompare(String(b.tipoNombre || ''));
                });
            }

            function renderGrid() {
                var $grid = $('#cat-grid');
                var $empty = $('#cat-grid-empty');
                var tipos = getFilteredTipos();

                $('#cat-results-count').text(tipos.length + ' tipo' + (tipos.length === 1 ? '' : 's'));

                if (!tipos.length) {
                    $grid.html('');
                    $empty.removeClass('d-none');
                    return;
                }
                $empty.addClass('d-none');

                $grid.html(tipos.map(function (t) {
                    var imgBlock = t.imagen_url
                        ? '<img src="' + escapeForHtml(t.imagen_url) + '" alt="" class="cat-card-img">'
                        : '<div class="cat-card-img-placeholder"><i class="ri-t-shirt-2-line"></i></div>';
                    var nTelas = t.telas_count || 0;
                    var nAtrib = t.atributos_count || 0;
                    var meta = t.requiere_tela
                        ? (nTelas + ' tela' + (nTelas === 1 ? '' : 's') + ' · ' + nAtrib + ' atributo' + (nAtrib === 1 ? '' : 's'))
                        : ('Sin tela · ' + nAtrib + ' atributo' + (nAtrib === 1 ? '' : 's'));
                    var precio = parseFloat(t.precio_confeccion || 0);
                    var precioTxt = precio > 0 ? ('desde ' + formatMoney(precio)) : 'Precio al configurar';
                    var precioBs = precio > 0 ? bsApprox(precio) : '';
                    if (precioBs) {
                        precioTxt += '<span class="money-bs-eq">' + precioBs + '</span>';
                    }
                    return (
                        '<button type="button" class="cat-card cat-card-tipo" data-tipo-id="' + t.id + '">' +
                            '<div class="cat-card-media">' + imgBlock +
                                '<span class="cat-card-tipo-badge">' + escapeForHtml(t.nombre) + '</span>' +
                            '</div>' +
                            '<div class="cat-card-body">' +
                                '<p class="cat-card-codigo"><i class="ri-shirt-line me-1"></i>' + meta + '</p>' +
                                '<h6 class="cat-card-modelo">' + escapeForHtml(t.nombre) + '</h6>' +
                                '<div class="cat-card-foot">' +
                                    '<span class="cat-card-price">' + precioTxt + '</span>' +
                                    '<span class="cat-card-cta">Elegir variante <i class="ri-arrow-right-line"></i></span>' +
                                '</div>' +
                            '</div>' +
                        '</button>'
                    );
                }).join(''));
            }

            // === Carrito (Fase 2: estructura; Fase 4 implementa la lógica completa) ====
            window.cotCart = window.cotCart || []; // [{productoId, colorId, tallasMap, bordados, ...}]

            function catCartHas(productoId) {
                return window.cotCart.some(function (it) { return it.productoId == productoId; });
            }

            function renderCart() {
                var list = window.cotCart || [];
                $('#cat-cart-count').text(list.length);
                var $list = $('#cat-cart-list');
                var $empty = $('#cat-cart-empty');
                var $btn = $('#btn-cat-confirmar');

                if (!list.length) {
                    $list.html('').hide();
                    $empty.show();
                    $('#cat-cart-total').text(formatMoney(0));
                    $('#cat-cart-total-bs').text(bsApprox(0));
                    $btn.prop('disabled', true);
                    return;
                }
                $empty.hide();
                $list.show();

                var total = 0;
                $list.html(list.map(function (it) {
                    var p = getProductsList().find(function (x) { return x.id == it.productoId; });
                    if (!p) return '';
                    var sub = parseFloat(it.subtotal || 0);
                    total += sub;
                    // El modelo suele venir vacío; mostrar la familia (tipo) como en las tarjetas, luego código
                    var nombreProd = (p.modelo && String(p.modelo).trim())
                        ? p.modelo
                        : ((p.tipo_producto && p.tipo_producto.nombre) ? p.tipo_producto.nombre : (p.codigo || '—'));
                    return (
                        '<div class="cat-cart-item">' +
                            '<div class="cat-cart-item-info">' +
                                '<p class="cat-cart-item-name">' + escapeForHtml(nombreProd) + '</p>' +
                                '<p class="cat-cart-item-meta">' + escapeForHtml(it.summary || '') + '</p>' +
                            '</div>' +
                            '<div class="cat-cart-item-actions">' +
                                '<span class="cat-cart-item-price">' + formatMoney(sub) + '</span>' +
                                '<button type="button" class="cat-cart-item-remove" data-producto-id="' + p.id + '" title="Quitar"><i class="ri-close-line"></i></button>' +
                            '</div>' +
                        '</div>'
                    );
                }).join(''));
                $('#cat-cart-total').text(formatMoney(total));
                $('#cat-cart-total-bs').text(bsApprox(total));
                $btn.prop('disabled', false);
            }

            function clearFilters() {
                catState.search = '';
                catState.tipos.clear();
                catState.priceMin = null;
                catState.priceMax = null;
                catState.sort = 'relevance';
                $('#cat-search').val('');
                $('#cat-price-min').val('');
                $('#cat-price-max').val('');
                $('#cat-sort').val('relevance');
                renderFilterTipos();
                renderGrid();
            }

            function openCatalog() {
                if (!catModalInstance) {
                    var el = document.getElementById('catalogoProductosModal');
                    if (!el) return;
                    catModalInstance = bootstrap.Modal.getOrCreateInstance(el);
                }
                $('#cat-eyebrow').text('Catálogo · ' + getCatalogoTipos().length + ' tipos disponibles');
                renderFilterTipos();
                renderGrid();
                renderCart();
                catModalInstance.show();
                setTimeout(function () { $('#cat-search').trigger('focus'); }, 250);
            }

            // === LISTENERS ====================================================
            $(document).on('click', '#btn-explorar-catalogo', openCatalog);

            $(document).on('input', '#cat-search', function () {
                catState.search = $(this).val();
                renderGrid();
            });
            $(document).on('change', '.cat-tipo-check', function () {
                var id = parseInt($(this).val(), 10);
                if (this.checked) catState.tipos.add(id);
                else catState.tipos.delete(id);
                renderGrid();
            });
            $(document).on('input', '#cat-price-min', function () {
                var v = parseFloat($(this).val());
                catState.priceMin = isNaN(v) ? null : v;
                renderGrid();
            });
            $(document).on('input', '#cat-price-max', function () {
                var v = parseFloat($(this).val());
                catState.priceMax = isNaN(v) ? null : v;
                renderGrid();
            });
            $(document).on('change', '#cat-sort', function () {
                catState.sort = $(this).val();
                renderGrid();
            });
            $(document).on('click', '#cat-clear-filters', clearFilters);

            // ============== Selector de Variante (Fase 4) ==============
            // Click en card de tipo → abre modal con chips de tela y atributos.
            // Al elegir combinación, se resuelve el producto via endpoint y se
            // abre el configurador clásico.
            var vsState = {
                tipoId: null,
                tipo: null,            // objeto tipo cargado vía /tipo-productos/{id}
                productos: [],         // productos del tipo (subconjunto del catálogo)
                telaId: null,          // tela seleccionada (insumo_id)
                valoresPorAtributo: {} // {atributo_id: valor_id}
            };

            function getProductosDelTipo(tipoId) {
                return getProductsList().filter(function (p) {
                    return p.tipo_producto && p.tipo_producto.id == tipoId;
                });
            }

            // Telas permitidas del tipo (FEAT-003): vienen de tipo_producto_tela,
            // ya no se deducen de los productos existentes.
            function telasDelTipo() {
                var telas = (vsState.tipo && Array.isArray(vsState.tipo.telas)) ? vsState.tipo.telas.slice() : [];
                return telas.sort(function (a, b) {
                    return String(a.nombre || '').localeCompare(String(b.nombre || ''));
                });
            }

            // Chip seleccionable (radio oculto + label estilizada con check animado)
            function vsChipHtml(opts) {
                return '' +
                    '<input type="radio" class="btn-check ' + opts.radioClass + '" name="' + opts.name + '" ' +
                        'id="' + opts.id + '" value="' + opts.value + '"' + (opts.data || '') +
                        (opts.checked ? ' checked' : '') + '>' +
                    '<label class="vs-chip" for="' + opts.id + '">' +
                        '<i class="ri-check-line vs-chip-check"></i>' +
                        '<span>' + escapeForHtml(opts.label) + '</span>' +
                        (opts.code ? '<span class="vs-chip-code">' + escapeForHtml(opts.code) + '</span>' : '') +
                    '</label>';
            }

            function vsRenderTelas() {
                var telas = telasDelTipo();
                var $cont = $('#vs-tela-options');
                if (!telas.length) {
                    $('#vs-tela-section').hide();
                    return;
                }
                $('#vs-tela-section').show();
                $cont.html(telas.map(function (t) {
                    return vsChipHtml({
                        radioClass: 'vs-tela-radio', name: 'vs-tela',
                        id: 'vs-tela-' + t.id, value: t.id,
                        checked: vsState.telaId == t.id,
                        label: t.nombre, code: t.codigo || ''
                    });
                }).join(''));
            }

            function vsRenderAtributos() {
                var $cont = $('#vs-atributos-section');
                if (!vsState.tipo || !vsState.tipo.atributos || !vsState.tipo.atributos.length) {
                    $cont.html('<p class="text-muted small mb-0">Este tipo no tiene atributos asociados.</p>');
                    vsRefrescarUI();
                    return;
                }
                var html = vsState.tipo.atributos.map(function (atr) {
                    if (!atr.valores || !atr.valores.length) {
                        return '<div class="vs-section"><div class="vs-section-head">' +
                            '<span class="vs-section-title">' + escapeForHtml(atr.nombre) + '</span></div>' +
                            '<p class="text-muted small fst-italic mb-0">Sin valores definidos.</p></div>';
                    }
                    var seleccionado = vsState.valoresPorAtributo[atr.id];
                    var chips = atr.valores.map(function (v) {
                        return vsChipHtml({
                            radioClass: 'vs-atributo-radio', name: 'vs-atr-' + atr.id,
                            id: 'vs-val-' + v.id, value: v.id,
                            data: ' data-atr-id="' + atr.id + '"',
                            checked: seleccionado == v.id,
                            label: v.nombre, code: ''
                        });
                    }).join('');
                    return '<div class="vs-section" data-vs-atr="' + atr.id + '">' +
                        '<div class="vs-section-head">' +
                            '<span class="vs-section-status" id="vs-status-atr-' + atr.id + '"><i class="ri-check-line"></i></span>' +
                            '<span class="vs-section-title">' + escapeForHtml(atr.nombre) + '</span>' +
                            '<span class="vs-section-pick" id="vs-pick-atr-' + atr.id + '"></span>' +
                        '</div>' +
                        '<div class="vs-chip-group">' + chips + '</div>' +
                    '</div>';
                }).join('');
                $cont.html(html);
                vsRefrescarUI();
            }

            // Estado vivo del selector: marca secciones completas (check verde +
            // valor elegido en la cabecera) y actualiza el contador de progreso.
            function vsRefrescarUI() {
                var total = 0, hechos = 0;
                var telas = telasDelTipo();

                if (telas.length) {
                    total++;
                    var tela = telas.find(function (t) { return t.id == vsState.telaId; });
                    var done = !!tela;
                    if (done) hechos++;
                    $('#vs-tela-section').toggleClass('is-done', done);
                    $('#vs-pick-tela').text(done ? tela.nombre : '');
                }

                ((vsState.tipo && vsState.tipo.atributos) || []).forEach(function (atr) {
                    if (!atr.valores || !atr.valores.length) return;
                    total++;
                    var vid = vsState.valoresPorAtributo[atr.id];
                    var val = (atr.valores || []).find(function (v) { return v.id == vid; });
                    var done = !!val;
                    if (done) hechos++;
                    $('[data-vs-atr="' + atr.id + '"]').toggleClass('is-done', done);
                    $('#vs-pick-atr-' + atr.id).text(done ? val.nombre : '');
                });

                var $prog = $('#vs-progress');
                if (!total) { $prog.attr('hidden', true); return; }
                $prog.removeAttr('hidden')
                    .toggleClass('is-complete', hechos >= total)
                    .html(hechos >= total
                        ? '<i class="ri-checkbox-circle-fill me-1"></i>Combinación completa'
                        : '<i class="ri-focus-2-line me-1"></i>' + hechos + ' de ' + total + ' elegidos');
            }

            function vsResolverVariante() {
                if (!vsState.tipoId) return;
                var valoresIds = Object.values(vsState.valoresPorAtributo).filter(function (x) { return !!x; });
                var nAtributos = vsState.tipo && vsState.tipo.atributos ? vsState.tipo.atributos.length : 0;
                var requiereTela = vsState.tipo && vsState.tipo.requiere_tela;

                // No resolver hasta que TODO esté seleccionado
                var faltaTela = requiereTela && !vsState.telaId;
                var faltanAtributos = valoresIds.length < nAtributos;
                if (faltaTela || faltanAtributos) {
                    $('#vs-result-found, #vs-result-missing').hide();
                    $('#vs-confirm').prop('disabled', true).removeData('producto-id').removeData('variante');
                    return;
                }

                $.getJSON("{{ route('productos.resolver-variante') }}", {
                    tipo_producto_id: vsState.tipoId,
                    insumo_tela_id: vsState.telaId || null,
                    'atributo_valor_ids[]': valoresIds
                }).done(function (resp) {
                    if (resp.found) {
                        $('#vs-result-codigo').text(resp.producto.codigo);
                        $('#vs-result-precio').text(formatMoney(resp.producto.precio_base));
                        $('#vs-result-precio-bs').text(bsApprox(resp.producto.precio_base));
                        $('#vs-result-found').show();
                        $('#vs-result-missing').hide();
                        if (resp.dynamic) {
                            // Variante calculada al vuelo (no existe como Producto):
                            // se confirma registrando un producto virtual en memoria.
                            $('#vs-confirm').prop('disabled', false)
                                .removeData('producto-id').data('variante', resp);
                        } else {
                            $('#vs-confirm').prop('disabled', false)
                                .removeData('variante').data('producto-id', resp.producto.id);
                        }
                    } else {
                        $('#vs-result-found').hide();
                        $('#vs-result-missing').show();
                        $('#vs-confirm').prop('disabled', true).removeData('producto-id').removeData('variante');
                    }
                });
            }

            // Registra un "producto virtual" en memoria a partir de la respuesta
            // dinámica del resolver, para que el resto del pipeline (configurador,
            // carrito, tabla agrupada) lo trate como un producto normal por id.
            // En el submit, addProductItem emite tipo_producto_id/tela/atributos.
            var cotVirtualSeq = 0;
            function cotRegistrarProductoVirtual(resp) {
                var v = resp.variante, pr = resp.producto, tipo = vsState.tipo || {};
                var existente = getProductsList().find(function (p) {
                    return p._dynamic && p.codigo === v.sku;
                });
                if (existente) return existente.id;

                var atributoValores = [];
                (tipo.atributos || []).forEach(function (atr) {
                    var vid = vsState.valoresPorAtributo[atr.id];
                    if (!vid) return;
                    var val = (atr.valores || []).find(function (x) { return x.id == vid; });
                    if (val) atributoValores.push({ id: val.id, atributo_id: atr.id, nombre: val.nombre, codigo: val.codigo });
                });

                var nombreCompleto = [pr.tipo_nombre, pr.tela_nombre]
                    .concat(atributoValores.map(function (x) { return x.nombre; }))
                    .filter(Boolean).join(' ');

                // La variante no tiene imagen propia: hereda la del Tipo (catálogo = Tipo).
                var imagenTipo = tipo.imagen_url
                    || ((getCatalogoTipos().find(function (x) { return x.id == v.tipo_producto_id; }) || {}).imagen_url)
                    || null;

                var virtual = {
                    id: 'v' + (++cotVirtualSeq),
                    _dynamic: true,
                    _variante: {
                        tipo_producto_id: v.tipo_producto_id,
                        insumo_tela_id: v.insumo_tela_id || null,
                        atributo_valor_ids: v.atributo_valor_ids || []
                    },
                    codigo: v.sku,
                    precio_base: pr.precio_base,
                    nombre: pr.tipo_nombre || 'Variante',
                    nombre_completo: nombreCompleto,
                    modelo: '',
                    imagen: imagenTipo,
                    tipo_producto: { id: v.tipo_producto_id, nombre: pr.tipo_nombre },
                    tela: v.tela_snapshot ? { id: v.tela_snapshot.id, nombre: v.tela_snapshot.nombre, codigo: v.tela_snapshot.codigo } : null,
                    atributo_valores: atributoValores
                };
                products.push(virtual);
                return virtual.id;
            }

            // Reconstruye un producto virtual a partir de un DETALLE guardado (edición de
            // cotización con línea dinámica: producto_id null + snapshots). Resuelve los
            // atributo_valor_ids por NOMBRE usando los atributos/valores del tipo (tipoData).
            // Devuelve el id virtual ('v...') para usar en addProductItem.
            window.cotReconstruirVirtualDesdeDetalle = function (detalle, tipoData, precioBase) {
                var tela = detalle.tela_snapshot || null;
                var snap = detalle.atributos_snapshot || {}; // { atributoNombre: valorNombre }
                var atributoValores = [];
                var atrIds = [];
                (tipoData && tipoData.atributos ? tipoData.atributos : []).forEach(function (atr) {
                    var valorNombre = snap[atr.nombre];
                    if (valorNombre == null) return;
                    var val = (atr.valores || []).find(function (v) { return String(v.nombre) === String(valorNombre); });
                    if (val) {
                        atrIds.push(val.id);
                        atributoValores.push({ id: val.id, atributo_id: atr.id, nombre: val.nombre, codigo: val.codigo });
                    }
                });

                var tipoNombre = tipoData ? tipoData.nombre : 'Variante';
                var nombreCompleto = [tipoNombre, tela ? tela.nombre : null]
                    .concat(atributoValores.map(function (x) { return x.nombre; }))
                    .filter(Boolean).join(' ');
                var imagenTipo = tipoData ? tipoData.imagen_url : null;

                var virtual = {
                    id: 'v' + (++cotVirtualSeq),
                    _dynamic: true,
                    _variante: {
                        tipo_producto_id: detalle.tipo_producto_id,
                        insumo_tela_id: tela ? tela.id : null,
                        atributo_valor_ids: atrIds
                    },
                    codigo: detalle.sku_snapshot || '',
                    precio_base: precioBase,
                    nombre: tipoNombre,
                    nombre_completo: nombreCompleto,
                    modelo: '',
                    imagen: imagenTipo,
                    tipo_producto: { id: detalle.tipo_producto_id, nombre: tipoNombre },
                    tela: tela ? { id: tela.id, nombre: tela.nombre, codigo: tela.codigo } : null,
                    atributo_valores: atributoValores
                };
                products.push(virtual);
                return virtual.id;
            };

            function vsAbrir(tipoId, opts) {
                opts = opts || {};
                vsState.tipoId = tipoId;
                vsState.productos = getProductosDelTipo(tipoId);
                vsState.telaId = opts.telaId || null;
                vsState.valoresPorAtributo = opts.valoresPorAtributo ? Object.assign({}, opts.valoresPorAtributo) : {};
                vsState.editContexto = opts.editContexto || null;
                vsState.tipo = null;

                $('#vs-tipo-nombre').text(vsState.productos[0]?.tipo_producto?.nombre || 'Variante');
                $('#vs-result-found, #vs-result-missing').hide();
                $('#vs-progress').attr('hidden', true);
                $('#vs-confirm').prop('disabled', true).removeData('producto-id');

                // Cargar info del tipo (atributos con sus valores)
                $.getJSON("{{ url('tipo-productos') }}/" + tipoId).done(function (tipo) {
                    vsState.tipo = tipo;
                    if (tipo && tipo.nombre) $('#vs-tipo-nombre').text(tipo.nombre);
                    vsRenderTelas();
                    vsRenderAtributos();
                    // Si vino con preselección, intentar resolver de inmediato
                    if (opts.telaId || (opts.valoresPorAtributo && Object.keys(opts.valoresPorAtributo).length)) {
                        vsResolverVariante();
                    }
                });

                new bootstrap.Modal(document.getElementById('varianteSelectorModal')).show();
            }
            window.cotVarianteSelectorAbrir = vsAbrir;

            // ════════════════════════════════════════════════════════════════
            //  CREAR TELA INLINE (Insumo tipo='Tela') desde el selector
            //  Solo se monta si el botón existe (gated por rol en Blade).
            // ════════════════════════════════════════════════════════════════
            (function () {
                if (!document.getElementById('vs-add-tela-btn')) return;
                var ctModal = new bootstrap.Modal(document.getElementById('crearTelaRapidaModal'));
                var CT_CSRF = $('meta[name="csrf-token"]').attr('content');

                function ctReset() {
                    $('#ctForm')[0].reset();
                    $('#ct-inventariable').prop('checked', true);
                    $('#ct-stock-wrapper').show();
                    $('#ct-nombre, #ct-codigo, #ct-costo').removeClass('is-invalid');
                    $('#ct-nombre-error, #ct-codigo-error, #ct-costo-error').hide();
                }

                $('#ct-inventariable').on('change', function () {
                    $('#ct-stock-wrapper').toggle(this.checked);
                });

                $('#vs-add-tela-btn').on('click', function () {
                    if (!vsState.tipoId) return;
                    ctReset();
                    ctModal.show();
                });

                $('#ctForm').on('submit', function (e) {
                    e.preventDefault();
                    var nombre = $('#ct-nombre').val().trim();
                    var costo = $('#ct-costo').val();
                    var ok = true;
                    if (!nombre) { $('#ct-nombre').addClass('is-invalid'); $('#ct-nombre-error').text('El nombre es obligatorio.').show(); ok = false; }
                    else { $('#ct-nombre').removeClass('is-invalid'); $('#ct-nombre-error').hide(); }
                    if (!costo || parseFloat(costo) <= 0) { $('#ct-costo').addClass('is-invalid'); $('#ct-costo-error').text('El costo debe ser mayor a 0.').show(); ok = false; }
                    else { $('#ct-costo').removeClass('is-invalid'); $('#ct-costo-error').hide(); }
                    if (!ok) return;

                    var inv = $('#ct-inventariable').is(':checked');
                    var payload = {
                        _token: CT_CSRF,
                        nombre: nombre,
                        codigo: $('#ct-codigo').val().trim().toUpperCase() || null,
                        unidad_medida: $('#ct-unidad').val(),
                        is_inventoriable: inv ? 1 : 0,
                        costo_unitario: costo,
                        estado: $('#ct-estado').val(),
                    };
                    if (inv) {
                        payload.stock_minimo = $('#ct-stock-min').val() || 0;
                        payload.stock_actual = $('#ct-stock-actual').val() || 0;
                        payload.stock_maximo = $('#ct-stock-max').val() || 0;
                    }

                    var $btn = $('#ct-submit-btn'), original = $btn.html();
                    $btn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i>Guardando...');

                    $.ajax({
                        url: "{{ url('tipo-productos') }}/" + vsState.tipoId + "/telas",
                        method: 'POST',
                        data: payload,
                        success: function (resp) {
                            ctModal.hide();
                            if (resp.tela && vsState.tipo) {
                                vsState.tipo.telas = vsState.tipo.telas || [];
                                vsState.tipo.telas.push(resp.tela);
                                vsState.telaId = resp.tela.id;      // auto-seleccionar la nueva tela
                                vsRenderTelas();
                                vsRefrescarUI();
                                vsResolverVariante();
                            }
                            Swal.fire({ icon: 'success', title: 'Tela creada', text: resp.message, showConfirmButton: false, timer: 1600 });
                        },
                        error: function (xhr) {
                            var errs = xhr.responseJSON && xhr.responseJSON.errors || {};
                            if (errs.codigo) { $('#ct-codigo').addClass('is-invalid'); $('#ct-codigo-error').text(errs.codigo[0]).show(); }
                            if (errs.nombre) { $('#ct-nombre').addClass('is-invalid'); $('#ct-nombre-error').text(errs.nombre[0]).show(); }
                            if (errs.costo_unitario) { $('#ct-costo').addClass('is-invalid'); $('#ct-costo-error').text(errs.costo_unitario[0]).show(); }
                            if (!errs.codigo && !errs.nombre && !errs.costo_unitario) {
                                Swal.fire({ icon: 'error', title: 'Error', text: (xhr.responseJSON && xhr.responseJSON.message) || 'No se pudo crear la tela.' });
                            }
                        },
                        complete: function () { $btn.prop('disabled', false).html(original); }
                    });
                });
            })();

            // Listeners del selector
            $(document).on('change', '.vs-tela-radio', function () {
                vsState.telaId = parseInt(this.value);
                vsRefrescarUI();
                vsResolverVariante();
            });
            $(document).on('change', '.vs-atributo-radio', function () {
                var atrId = $(this).data('atr-id');
                vsState.valoresPorAtributo[atrId] = parseInt(this.value);
                vsRefrescarUI();
                vsResolverVariante();
            });
            $(document).on('click', '#vs-confirm', function () {
                var pid = $(this).data('producto-id');
                var variante = $(this).data('variante');
                if (!pid && variante) {
                    pid = cotRegistrarProductoVirtual(variante);
                }
                if (!pid) return;
                var ctx = vsState.editContexto;
                bootstrap.Modal.getInstance(document.getElementById('varianteSelectorModal'))?.hide();
                if (typeof window.cotConfiguradorAbrir === 'function') {
                    if (ctx) {
                        // Cambio de variante en plena edición: reabre configurador con el nuevo
                        // producto pero conservando color/tallas/precio que ya estaban configurados.
                        window.cotConfiguradorAbrir(pid, {
                            existing: {
                                colorId: ctx.colorId,
                                colorNombre: ctx.colorNombre,
                                colorHex: ctx.colorHex,
                                tallas: ctx.tallas,
                                precioUnitario: ctx.precioUnitario
                            },
                            cartItemId: ctx.cartItemId,
                            productoIdOriginal: ctx.productoIdOriginal
                        });
                    } else {
                        window.cotConfiguradorAbrir(pid);
                    }
                }
            });

            // Click en card de tipo → abrir selector
            $(document).on('click', '#cat-grid .cat-card-tipo', function () {
                var tipoId = parseInt(this.dataset.tipoId, 10);
                if (!tipoId) return;
                vsAbrir(tipoId);
            });

            // Quitar item del carrito (delegado)
            $(document).on('click', '.cat-cart-item-remove', function () {
                // El id puede ser virtual ('v1') o numérico → comparar como string.
                var pid = String(this.dataset.productoId);
                window.cotCart = (window.cotCart || []).filter(function (it) { return String(it.productoId) !== pid; });
                renderCart();
                renderGrid();
            });

            // Confirmar carrito → trigger Fase 4 hook
            $(document).on('click', '#btn-cat-confirmar', function () {
                if (typeof window.cotCartConfirmar === 'function') {
                    window.cotCartConfirmar();
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Próximamente',
                        text: 'La conversión carrito → líneas de cotización llega en la Fase 4.',
                        timer: 2200,
                        showConfirmButton: false
                    });
                }
            });

            // Reset de filtros al cerrar
            $('#catalogoProductosModal').on('hidden.bs.modal', function () { /* mantener filtros entre aperturas */ });

            // Exponer
            window.cotCatalog = {
                open: openCatalog,
                renderGrid: renderGrid,
                renderCart: renderCart
            };
        })();

        // ╔══════════════════════════════════════════════════════════════════
        // ║ COTIZACIONES — CONFIGURADOR DE PRODUCTO  (Fase 3)
        // ║ Modal anidado sobre el catálogo. Selección de:
        // ║   1. Color (chips)
        // ║   2. Tallas con cantidades (matrix)
        // ║ Bordados se configuran por línea en el paso 2 (Fase 4).
        // ║ Al guardar, agrega al carrito (window.cotCart) e invoca render.
        // ╚══════════════════════════════════════════════════════════════════
        (function () {
            'use strict';

            var cfgState = {
                producto: null,         // referencia al producto del catálogo
                productoIdOriginal: null, // si edita un bloque y cambia la variante, recuerda el SKU original para reemplazo
                colorId: null,
                colorNombre: '',
                colorHex: null,
                tallas: {},             // { tallaId: { generoId: cantidad } }
                precioUnitario: null,   // precio editable, default = producto.precio_base
                cartItemId: null        // si edita un item existente del carrito
            };
            var cfgModalInstance = null;
            var cfgTallaGrupo = null;   // escala de tallas visible (Letras/Numéricas/Única)

            function $cfg(id) { return document.getElementById(id); }

            function getColorByIdLocal(id) {
                if (typeof coloresArray === 'undefined' || !Array.isArray(coloresArray)) return null;
                return coloresArray.find(function (c) { return c.id == id; }) || null;
            }
            function getTallasArray() {
                return (typeof tallasArray !== 'undefined' && Array.isArray(tallasArray)) ? tallasArray : [];
            }

            function escForHtml(s) {
                return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
                    return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
                });
            }

            function openConfigurador(productoId, opts) {
                opts = opts || {};
                // products vive en el closure del $(document).ready exterior;
                // accederlo directamente evita el shadowing del var hoisting.
                var prodList = (typeof products !== 'undefined' && Array.isArray(products)) ? products : [];
                var p = prodList.find(function (x) { return x.id == productoId; });
                if (!p) {
                    Swal.fire({ icon: 'error', title: 'Producto no encontrado', timer: 1800, showConfirmButton: false });
                    return;
                }

                // Estado inicial — si edita un item existente, carga sus datos
                cfgState.producto = p;
                cfgState.cartItemId = opts.cartItemId || null;
                // Si veníamos editando un bloque de un producto distinto (cambio de variante),
                // recuerda el id original para que el patcher de guardado pueda borrar las cards correctas.
                cfgState.productoIdOriginal = (opts.productoIdOriginal != null)
                    ? opts.productoIdOriginal
                    : p.id;
                var basePrice = parseFloat(p.precio_base || 0) || 0;
                if (opts.existing) {
                    cfgState.colorId = opts.existing.colorId || null;
                    cfgState.colorNombre = opts.existing.colorNombre || '';
                    cfgState.colorHex = opts.existing.colorHex || null;
                    // Clon profundo: la estructura es 2D { tallaId: { generoId: qty } }
                    cfgState.tallas = JSON.parse(JSON.stringify(opts.existing.tallas || {}));
                    cfgState.precioUnitario = (opts.existing.precioUnitario != null)
                        ? parseFloat(opts.existing.precioUnitario) : basePrice;
                } else {
                    cfgState.colorId = null;
                    cfgState.colorNombre = '';
                    cfgState.colorHex = null;
                    cfgState.tallas = {};
                    cfgState.precioUnitario = basePrice;
                }

                cfgTallaGrupo = null; // re-elige escala según los datos del producto
                renderInfo();
                renderColorGrid();
                renderTallasGrid();
                renderPrecio();
                refreshSummary();

                // Cambiar texto del botón si es edición
                $('#cfg-save-btn-label').text(opts.cartItemId ? 'Actualizar selección' : 'Agregar al carrito');

                if (!cfgModalInstance) {
                    var el = document.getElementById('cotConfiguradorModal');
                    if (!el) return;
                    cfgModalInstance = bootstrap.Modal.getOrCreateInstance(el);
                }
                cfgModalInstance.show();
            }
            window.cotConfiguradorAbrir = openConfigurador;

            function renderInfo() {
                var p = cfgState.producto;
                if (!p) return;
                var tipoNombre = p.tipo_producto ? p.tipo_producto.nombre : 'Producto';
                $('#cfg-eyebrow').text(tipoNombre + ' · ' + (p.codigo || '—'));
                $('#cfg-title').text(tipoNombre);
                $('#cfg-info-codigo').text(p.codigo || '—');
                // h6 principal de la columna info: muestra el tipo de prenda
                $('#cfg-info-modelo').text(tipoNombre);
                // Línea pequeña: nombre completo (tipo + tela + atributos) si está disponible
                var variantLabel = (typeof window.cotBuildVariantLabel === 'function')
                    ? window.cotBuildVariantLabel(p) : '';
                $('#cfg-info-tipo').text(variantLabel || ('Tipo: ' + tipoNombre));
                $('#cfg-info-precio').text(formatMoney(parseFloat(p.precio_base || 0)));
                $('#cfg-info-precio-bs').text(bsApprox(parseFloat(p.precio_base || 0)));

                // Mostrar botón "Cambiar variante" solo si el tipo tiene >1 producto activo
                var tipoId = p.tipo_producto ? p.tipo_producto.id : null;
                var prodList = (typeof products !== 'undefined' && Array.isArray(products)) ? products : [];
                var hermanos = tipoId ? prodList.filter(function (x) {
                    return x.tipo_producto && x.tipo_producto.id === tipoId;
                }).length : 0;
                $('#cfg-cambiar-variante').toggle(hermanos > 1);

                var $media = $('#cfg-media-inner');
                if (p.imagen) {
                    $media.html('<img src="' + escForHtml(p.imagen) + '" alt="" class="cfg-media-img">');
                } else {
                    $media.html('<i class="ri-t-shirt-2-line"></i>');
                }
            }

            // Filtra los chips de color por nombre según el buscador. Oculta los
            // títulos de grupo que queden sin chips visibles y muestra el estado
            // vacío. Usa la clase .cfg-hidden (display:none !important) porque el
            // atributo [hidden] no oculta elementos inline-flex como los chips.
            function filterColorChips() {
                var term = ($('#cfg-color-search').val() || '').trim().toLowerCase();
                $('#cfg-color-search-clear').prop('hidden', term === '');
                var $grid = $('#cfg-color-grid');
                var anyVisible = false;
                $grid.find('.cfg-color-group-items').each(function () {
                    var $items = $(this);
                    var groupHasVisible = false;
                    $items.find('.cfg-color-chip').each(function () {
                        var name = ($(this).data('color-nombre') || '').toString().toLowerCase();
                        var match = term === '' || name.indexOf(term) !== -1;
                        $(this).toggleClass('cfg-hidden', !match);
                        if (match) { groupHasVisible = true; anyVisible = true; }
                    });
                    $items.toggleClass('cfg-hidden', !groupHasVisible);
                    $items.prev('.cfg-color-group-title').toggleClass('cfg-hidden', !groupHasVisible);
                });
                $('#cfg-color-noresults').toggleClass('cfg-hidden', anyVisible || term === '');
            }

            function renderColorGrid() {
                var colors = (typeof coloresArray !== 'undefined' && Array.isArray(coloresArray)) ? coloresArray : [];
                var $grid = $('#cfg-color-grid');
                if (!colors.length) {
                    $grid.html('<p class="text-muted small mb-0"><em>Cargando colores…</em></p>');
                    return;
                }

                // Agrupar por categoría
                var byGroup = {};
                colors.forEach(function (c) {
                    var g = c.grupo || 'Otros';
                    (byGroup[g] = byGroup[g] || []).push(c);
                });

                var html = '';
                Object.keys(byGroup).sort().forEach(function (g) {
                    html += '<div class="cfg-color-group-title">' + escForHtml(g) + '</div>';
                    html += '<div class="cfg-color-group-items">';
                    byGroup[g].forEach(function (c) {
                        var hex = c.hex_referencial || '#cccccc';
                        var sel = cfgState.colorId == c.id;
                        var lightHex = (hex.toUpperCase() === '#FFFFFF' || hex.toUpperCase() === '#FFFDD0');
                        html += (
                            '<button type="button" class="cfg-color-chip' + (sel ? ' is-selected' : '') + (lightHex ? ' is-light' : '') + '"' +
                                ' data-color-id="' + c.id + '" data-color-nombre="' + escForHtml(c.nombre) + '" data-color-hex="' + escForHtml(hex) + '"' +
                                ' title="' + escForHtml(c.nombre) + '">' +
                                '<span class="cfg-color-chip-swatch" style="background:' + hex + ';"></span>' +
                                '<span class="cfg-color-chip-label">' + escForHtml(c.nombre) + '</span>' +
                            '</button>'
                        );
                    });
                    html += '</div>';
                });
                $grid.html(html);

                // Indicador de color seleccionado en el header
                $('#cfg-color-selected').text(cfgState.colorNombre || 'Sin seleccionar');

                // Buscador arranca limpio en cada render (nuevo producto o color creado)
                $('#cfg-color-search').val('');
                filterColorChips();
            }

            // ── Escala de tallas activa (Letras / Numéricas / Única) ──
            // Solo se muestran las tallas del grupo elegido para no abrumar con
            // las ~15 del catálogo. Los datos de otros grupos se conservan en
            // cfgState.tallas y cuentan en el total.
            function tallaGruposDisponibles() {
                var seen = [];
                getTallasArray().forEach(function (t) {
                    var g = t.grupo || 'Otras';
                    if (seen.indexOf(g) === -1) seen.push(g);
                });
                return seen;
            }
            function tallaGrupoDeId(tid) {
                var t = getTallasArray().find(function (x) { return x.id == tid; });
                return t ? (t.grupo || 'Otras') : null;
            }
            function unidadesPorGrupo() {
                var acc = {};
                Object.keys(cfgState.tallas).forEach(function (tid) {
                    var g = tallaGrupoDeId(tid);
                    if (!g) return;
                    var byGen = cfgState.tallas[tid] || {};
                    Object.keys(byGen).forEach(function (gid) {
                        acc[g] = (acc[g] || 0) + (parseInt(byGen[gid] || 0, 10) || 0);
                    });
                });
                return acc;
            }
            function ensureTallaGrupo() {
                var grupos = tallaGruposDisponibles();
                if (!grupos.length) { cfgTallaGrupo = null; return; }
                if (cfgTallaGrupo && grupos.indexOf(cfgTallaGrupo) !== -1) return;
                // Preferir el grupo que ya tiene datos (al editar); si no, Letras; si no, el primero.
                var conDatos = Object.keys(unidadesPorGrupo());
                if (conDatos.length && grupos.indexOf(conDatos[0]) !== -1) {
                    cfgTallaGrupo = conDatos[0];
                } else if (grupos.indexOf('Letras') !== -1) {
                    cfgTallaGrupo = 'Letras';
                } else {
                    cfgTallaGrupo = grupos[0];
                }
            }

            // Grilla 2D: filas = tallas (solo del grupo activo), columnas = género.
            // cfgState.tallas tiene forma { tallaId: { generoId: cantidad } }.
            function renderTallasGrid() {
                var tallas = getTallasArray();
                var generos = getGenerosArray();
                var $grid = $('#cfg-tallas-grid');
                if (!tallas.length || !generos.length) {
                    $grid.html('<p class="text-muted small mb-0"><em>Cargando tallas…</em></p>');
                    return;
                }

                ensureTallaGrupo();
                var grupos = tallaGruposDisponibles();
                var porGrupo = unidadesPorGrupo();

                // Toggle de escala (solo si hay más de un grupo). El badge muestra
                // cuántas unidades hay cargadas en escalas no visibles.
                // Ícono por escala de tallas (con respaldo neutro si aparece otra).
                var TG_GROUP_ICONS = { 'Única': 'ri-focus-3-line', 'Numéricas': 'ri-hashtag', 'Letras': 'ri-font-size' };
                var toggle = '';
                if (grupos.length > 1) {
                    toggle = '<div class="cfg-tg-groups">' + grupos.map(function (g) {
                        var n = porGrupo[g] || 0;
                        var badge = (n && g !== cfgTallaGrupo) ? '<span class="cfg-tg-group-badge">' + n + '</span>' : '';
                        var ico = '<i class="' + (TG_GROUP_ICONS[g] || 'ri-ruler-2-line') + '"></i>';
                        return '<button type="button" class="cfg-tg-group-btn' + (g === cfgTallaGrupo ? ' is-active' : '') +
                            '" data-grupo="' + escForHtml(g) + '">' + ico + '<span>' + escForHtml(g) + '</span>' + badge + '</button>';
                    }).join('') + '</div>';
                }

                var visibles = tallas.filter(function (t) { return (t.grupo || 'Otras') === cfgTallaGrupo; });

                // Slug semántico del género para colorear su columna por CSS
                // (no por posición → robusto ante cambios de orden/catálogo).
                function generoSlug(g) {
                    var n = String(g.nombre || g.etiqueta || '').toLowerCase().trim();
                    if (n.indexOf('dama') !== -1 || n.indexOf('mujer') !== -1) return 'dama';
                    if (n.indexOf('caballero') !== -1 || n.indexOf('hombre') !== -1) return 'caballero';
                    if (n.indexOf('unisex') !== -1) return 'unisex';
                    return 'otro';
                }

                var head = '<div class="cfg-tg-row cfg-tg-head">' +
                    '<span class="cfg-tg-talla cfg-tg-corner"><i class="ri-ruler-2-line"></i> Talla</span>' +
                    generos.map(function (g) {
                        var ic = g.icono ? '<i class="' + escForHtml(g.icono) + '"></i> ' : '';
                        return '<span class="cfg-tg-genhead" data-genero="' + generoSlug(g) + '">' + ic + escForHtml(g.etiqueta || g.nombre) + '</span>';
                    }).join('') +
                    '</div>';

                var rows = visibles.map(function (t) {
                    var label = t.etiqueta || t.nombre || '—';
                    var byGen = cfgState.tallas[t.id] || {};
                    var cells = generos.map(function (g) {
                        var qty = byGen[g.id] || '';
                        return (
                            '<div class="cfg-tg-cell' + (qty ? ' is-active' : '') + '" data-genero="' + generoSlug(g) + '">' +
                                '<button type="button" class="cfg-tg-step" data-step="-1" tabindex="-1" aria-label="Restar">&minus;</button>' +
                                '<input type="number" class="cfg-tg-input" inputmode="numeric"' +
                                    ' min="0" step="1" placeholder="0" value="' + qty + '"' +
                                    ' data-talla-id="' + t.id + '" data-genero-id="' + g.id + '"' +
                                    ' aria-label="' + escForHtml(label + ' · ' + (g.etiqueta || g.nombre)) + '">' +
                                '<button type="button" class="cfg-tg-step" data-step="1" tabindex="-1" aria-label="Sumar">+</button>' +
                            '</div>'
                        );
                    }).join('');
                    return (
                        '<div class="cfg-tg-row" data-talla-id="' + t.id + '">' +
                            '<span class="cfg-tg-talla">' + escForHtml(label) + '</span>' + cells +
                        '</div>'
                    );
                }).join('');

                $grid.html(toggle + '<div class="cfg-tg" style="--cfg-tg-cols:' + generos.length + '">' + head + rows + '</div>');
            }

            function totalTallas() {
                var total = 0;
                Object.keys(cfgState.tallas).forEach(function (tid) {
                    var byGen = cfgState.tallas[tid] || {};
                    Object.keys(byGen).forEach(function (gid) {
                        total += parseInt(byGen[gid] || 0, 10) || 0;
                    });
                });
                return total;
            }

            function renderPrecio() {
                var basePrice = parseFloat(cfgState.producto ? cfgState.producto.precio_base : 0) || 0;
                $('#cfg-precio-base-hint-value').text(formatMoney(basePrice));
                var current = (cfgState.precioUnitario != null) ? cfgState.precioUnitario : basePrice;
                $('#cfg-precio-input').val(parseFloat(current).toFixed(2));
            }

            function refreshSummary() {
                var qty = totalTallas();
                var unit = (cfgState.precioUnitario != null && !isNaN(cfgState.precioUnitario))
                    ? parseFloat(cfgState.precioUnitario)
                    : (parseFloat(cfgState.producto ? cfgState.producto.precio_base : 0) || 0);
                var subtotal = qty * unit;

                $('#cfg-tallas-total').text(qty);
                $('#cfg-summary-qty').text(qty);
                $('#cfg-summary-unit').text(formatMoney(unit));
                $('#cfg-summary-unit-bs').text(bsApprox(unit));
                $('#cfg-summary-subtotal').text(formatMoney(subtotal));
                $('#cfg-summary-subtotal-bs').text(bsApprox(subtotal));

                $('#cfg-save-btn').prop('disabled', !(cfgState.colorId && qty > 0 && unit > 0));
            }

            // Buscador de color: filtra en vivo y botón para limpiar
            $(document).on('input', '#cfg-color-search', filterColorChips);
            $(document).on('click', '#cfg-color-search-clear', function () {
                $('#cfg-color-search').val('').trigger('input').focus();
            });

            // Click en chip de color
            $(document).on('click', '#cfg-color-grid .cfg-color-chip', function () {
                var $b = $(this);
                cfgState.colorId = parseInt($b.data('color-id'), 10);
                cfgState.colorNombre = $b.data('color-nombre');
                cfgState.colorHex = $b.data('color-hex');
                $('#cfg-color-grid .cfg-color-chip').removeClass('is-selected');
                $b.addClass('is-selected');
                $('#cfg-color-selected').text(cfgState.colorNombre || 'Sin seleccionar');
                refreshSummary();
            });

            // ════════════════════════════════════════════════════════════════
            //  CREAR COLOR INLINE (extensión del maestro Colores)
            //  Solo se monta si el botón existe (gated por rol en Blade).
            // ════════════════════════════════════════════════════════════════
            (function () {
                if (!document.getElementById('cfg-add-color-btn')) return;
                var CC_HEX_RE = /^#[0-9A-Fa-f]{6}$/;
                var ccModal = new bootstrap.Modal(document.getElementById('crearColorRapidoModal'));

                function ccSetHex(hex) {
                    hex = (hex || '').toUpperCase();
                    $('#cc-hex-text').val(hex);
                    if (CC_HEX_RE.test(hex)) {
                        $('#cc-hex').val(hex);
                        $('#cc-preview-dot').css('background', hex);
                    }
                }
                function ccGetGrupo() {
                    var sel = $('#cc-grupo').val();
                    if (sel === '__new__') return $('#cc-grupo-nuevo').val().trim();
                    return sel || '';
                }
                function ccPoblarGrupos() {
                    var grupos = [];
                    (coloresArray || []).forEach(function (c) {
                        if (c.grupo && grupos.indexOf(c.grupo) === -1) grupos.push(c.grupo);
                    });
                    grupos.sort(function (a, b) { return a.localeCompare(b, 'es'); });
                    var opts = '<option value="">Sin grupo</option>';
                    grupos.forEach(function (g) {
                        opts += '<option value="' + escForHtml(g) + '">' + escForHtml(g) + '</option>';
                    });
                    opts += '<option value="__new__">➕ Nuevo grupo…</option>';
                    $('#cc-grupo').html(opts);
                }
                function ccReset() {
                    $('#ccForm')[0].reset();
                    $('#cc-nombre, #cc-hex-text').removeClass('is-invalid is-valid');
                    $('#cc-nombre-error, #cc-hex-error').hide();
                    $('#cc-grupo-nuevo').addClass('d-none').val('');
                    ccPoblarGrupos();
                    ccSetHex('#1B3A5C');
                    $('#cc-preview-name').text('Nombre del color');
                }

                $('#cc-hex').on('input', function () { ccSetHex(this.value); });
                $('#cc-hex-text').on('input', function () { ccSetHex(this.value); });
                $('#cc-nombre').on('input', function () {
                    $('#cc-preview-name').text($(this).val().trim() || 'Nombre del color');
                });
                $('#cc-grupo').on('change', function () {
                    var nuevo = this.value === '__new__';
                    $('#cc-grupo-nuevo').toggleClass('d-none', !nuevo);
                    if (nuevo) $('#cc-grupo-nuevo').val('').focus();
                });

                $('#cfg-add-color-btn').on('click', function () {
                    ccReset();
                    // Prefijar el grupo con el contexto actual si el color elegido tenía uno
                    ccModal.show();
                });

                $('#ccForm').on('submit', function (e) {
                    e.preventDefault();
                    var nombre = $('#cc-nombre').val().trim();
                    var hex = $('#cc-hex-text').val().trim().toUpperCase();
                    var ok = true;
                    if (nombre.length < 2) {
                        $('#cc-nombre').removeClass('is-valid').addClass('is-invalid');
                        $('#cc-nombre-error').text('El nombre debe tener al menos 2 caracteres.').show();
                        ok = false;
                    } else { $('#cc-nombre').removeClass('is-invalid'); $('#cc-nombre-error').hide(); }
                    if (!CC_HEX_RE.test(hex)) {
                        $('#cc-hex-text').removeClass('is-valid').addClass('is-invalid');
                        $('#cc-hex-error').text('El HEX debe tener el formato #RRGGBB.').show();
                        ok = false;
                    } else { $('#cc-hex-text').removeClass('is-invalid'); $('#cc-hex-error').hide(); }
                    if (!ok) return;

                    var $btn = $('#cc-submit-btn'), original = $btn.html();
                    $btn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i>Guardando...');

                    $.ajax({
                        url: "{{ route('colores.store') }}",
                        method: 'POST',
                        data: { nombre: nombre, grupo: ccGetGrupo(), hex_referencial: hex, _token: '{{ csrf_token() }}' },
                        success: function (resp) {
                            ccModal.hide();
                            var nuevo = resp.color;
                            if (nuevo) {
                                coloresArray.push(nuevo);
                                // Auto-seleccionar el color recién creado
                                cfgState.colorId = nuevo.id;
                                cfgState.colorNombre = nuevo.nombre;
                                cfgState.colorHex = nuevo.hex_referencial;
                                renderColorGrid();
                                refreshSummary();
                            }
                            Swal.fire({ icon: 'success', title: 'Color creado', text: resp.message, showConfirmButton: false, timer: 1500 });
                        },
                        error: function (xhr) {
                            var errs = xhr.responseJSON && xhr.responseJSON.errors || {};
                            if (errs.nombre) { $('#cc-nombre').removeClass('is-valid').addClass('is-invalid'); $('#cc-nombre-error').text(errs.nombre[0]).show(); }
                            if (errs.hex_referencial) { $('#cc-hex-text').removeClass('is-valid').addClass('is-invalid'); $('#cc-hex-error').text(errs.hex_referencial[0]).show(); }
                            if (!errs.nombre && !errs.hex_referencial) {
                                Swal.fire({ icon: 'error', title: 'Error', text: (xhr.responseJSON && xhr.responseJSON.message) || 'No se pudo crear el color.' });
                            }
                        },
                        complete: function () { $btn.prop('disabled', false).html(original); }
                    });
                });
            })();

            // Cambio en cantidad por talla
            // Cambiar de escala de tallas (Letras / Numéricas / Única)
            $(document).on('click', '#cfg-tallas-grid .cfg-tg-group-btn', function () {
                cfgTallaGrupo = $(this).data('grupo');
                renderTallasGrid();
            });

            $(document).on('input', '#cfg-tallas-grid .cfg-tg-input', function () {
                var tid = parseInt($(this).data('talla-id'), 10);
                var gid = parseInt($(this).data('genero-id'), 10);
                var v = parseInt($(this).val(), 10);
                var has = !(isNaN(v) || v <= 0);
                if (!cfgState.tallas[tid]) cfgState.tallas[tid] = {};
                if (!has) {
                    delete cfgState.tallas[tid][gid];
                    if (Object.keys(cfgState.tallas[tid]).length === 0) delete cfgState.tallas[tid];
                } else {
                    cfgState.tallas[tid][gid] = v;
                }
                $(this).closest('.cfg-tg-cell').toggleClass('is-active', has);
                refreshSummary();
            });

            // Steppers +/- de cada celda: ajustan el input y reusan su lógica de estado.
            $(document).on('click', '#cfg-tallas-grid .cfg-tg-step', function () {
                var $inp = $(this).closest('.cfg-tg-cell').find('.cfg-tg-input');
                var delta = parseInt($(this).data('step'), 10) || 0;
                var v = (parseInt($inp.val(), 10) || 0) + delta;
                if (v < 0) v = 0;
                $inp.val(v > 0 ? v : '').trigger('input');
            });

            // Distribuir uniforme: reparte un total entre las tallas para un
            // género elegido (las celdas de los otros géneros se conservan).
            $(document).on('click', '#cfg-distribute-btn', function () {
                var generos = getGenerosArray();
                if (!generos.length) return;
                var inputOptions = {};
                generos.forEach(function (g) { inputOptions[g.id] = g.etiqueta || g.nombre; });

                Swal.fire({
                    title: 'Distribuir uniforme',
                    text: '¿Para qué género?',
                    input: 'select',
                    inputOptions: inputOptions,
                    inputValue: String(generos[0].id),
                    showCancelButton: true,
                    confirmButtonText: 'Siguiente',
                    cancelButtonText: 'Cancelar',
                    customClass: { container: 'swal-over-modal' }
                }).then(function (r1) {
                    if (!r1.isConfirmed) return;
                    var gid = parseInt(r1.value, 10);
                    Swal.fire({
                        title: 'Distribuir uniforme',
                        text: '¿Cuántas unidades en total para ' + (inputOptions[gid] || '') + '?',
                        input: 'number',
                        inputAttributes: { min: 1, step: 1 },
                        inputValue: 50,
                        showCancelButton: true,
                        confirmButtonText: 'Distribuir',
                        cancelButtonText: 'Cancelar',
                        customClass: { container: 'swal-over-modal' }
                    }).then(function (r2) {
                        if (!r2.isConfirmed) return;
                        var total = parseInt(r2.value, 10);
                        if (!total || total < 1) return;
                        // Repartir solo entre las tallas de la escala visible.
                        var tallas = getTallasArray().filter(function (t) { return (t.grupo || 'Otras') === cfgTallaGrupo; });
                        if (!tallas.length) return;
                        var per = Math.floor(total / tallas.length);
                        var rem = total % tallas.length;
                        tallas.forEach(function (t, i) {
                            var v = per + (i < rem ? 1 : 0);
                            if (!cfgState.tallas[t.id]) cfgState.tallas[t.id] = {};
                            if (v > 0) cfgState.tallas[t.id][gid] = v;
                            else delete cfgState.tallas[t.id][gid];
                            if (Object.keys(cfgState.tallas[t.id]).length === 0) delete cfgState.tallas[t.id];
                        });
                        renderTallasGrid();
                        refreshSummary();
                    });
                });
            });

            // Cambio en el precio unitario
            $(document).on('input', '#cfg-precio-input', function () {
                var v = parseFloat($(this).val());
                cfgState.precioUnitario = isNaN(v) ? 0 : v;
                refreshSummary();
            });

            // Restaurar precio base
            $(document).on('click', '#cfg-precio-reset', function () {
                if (!cfgState.producto) return;
                cfgState.precioUnitario = parseFloat(cfgState.producto.precio_base || 0) || 0;
                $('#cfg-precio-input').val(cfgState.precioUnitario.toFixed(2));
                refreshSummary();
            });

            // Volver al catálogo
            $(document).on('click', '#cfg-back-to-catalog', function () {
                if (cfgModalInstance) cfgModalInstance.hide();
            });

            // Cambiar variante: cierra el configurador y abre el selector con
            // los valores actuales pre-seleccionados, manteniendo color/tallas
            // ya configurados para que no se pierda el avance.
            $(document).on('click', '#cfg-cambiar-variante', function () {
                var p = cfgState.producto;
                if (!p || !p.tipo_producto) return;

                var preTela = p.tela ? p.tela.id : null;
                var preValores = {};
                if (Array.isArray(p.atributo_valores)) {
                    p.atributo_valores.forEach(function (v) {
                        if (v.atributo) preValores[v.atributo.id] = v.id;
                    });
                }

                var contexto = {
                    productoIdOriginal: cfgState.productoIdOriginal || p.id,
                    colorId: cfgState.colorId,
                    colorNombre: cfgState.colorNombre,
                    colorHex: cfgState.colorHex,
                    tallas: Object.assign({}, cfgState.tallas),
                    precioUnitario: cfgState.precioUnitario,
                    cartItemId: cfgState.cartItemId
                };

                if (cfgModalInstance) cfgModalInstance.hide();

                if (typeof window.cotVarianteSelectorAbrir === 'function') {
                    window.cotVarianteSelectorAbrir(p.tipo_producto.id, {
                        telaId: preTela,
                        valoresPorAtributo: preValores,
                        editContexto: contexto
                    });
                }
            });

            // Guardar (agregar al carrito)
            $(document).on('click', '#cfg-save-btn', function () {
                if (!cfgState.colorId || totalTallas() === 0) return;
                var p = cfgState.producto;

                // Una entrada por celda (talla × género) con cantidad > 0.
                var tallasItems = [];
                Object.keys(cfgState.tallas).forEach(function (tid) {
                    var t = getTallasArray().find(function (x) { return x.id == tid; });
                    var byGen = cfgState.tallas[tid] || {};
                    Object.keys(byGen).forEach(function (gid) {
                        var qty = parseInt(byGen[gid], 10) || 0;
                        if (qty <= 0) return;
                        tallasItems.push({
                            tallaId: parseInt(tid, 10),
                            tallaLabel: t ? (t.etiqueta || t.nombre) : '—',
                            generoId: parseInt(gid, 10),
                            generoNombre: getGeneroNombre(gid),
                            qty: qty
                        });
                    });
                });

                var totalQty = tallasItems.reduce(function (a, x) { return a + x.qty; }, 0);
                var basePrice = parseFloat(p.precio_base || 0);
                var unit = (cfgState.precioUnitario != null && cfgState.precioUnitario > 0)
                    ? parseFloat(cfgState.precioUnitario) : basePrice;
                var subtotal = totalQty * unit;

                var item = {
                    id: cfgState.cartItemId || ('cit_' + Date.now() + '_' + Math.random().toString(36).slice(2, 7)),
                    productoId: p.id,
                    productoIdOriginal: cfgState.productoIdOriginal || p.id,
                    productoCodigo: p.codigo || '',
                    productoModelo: p.modelo || '',
                    productoTipo: p.tipo_producto ? p.tipo_producto.nombre : '',
                    productoPrecio: basePrice,
                    colorId: cfgState.colorId,
                    colorNombre: cfgState.colorNombre,
                    colorHex: cfgState.colorHex,
                    tallas: tallasItems,
                    totalQty: totalQty,
                    unitPrice: unit,                 // precio efectivo (puede diferir del precio_base)
                    precioCustom: unit !== basePrice,
                    subtotal: subtotal,
                    summary: cfgState.colorNombre + ' · ' +
                             tallasItems.map(function (x) { return x.tallaLabel + '·' + x.generoNombre + '×' + x.qty; }).join(' · ') +
                             (unit !== basePrice ? ' · @' + formatMoney(unit) : '')
                };

                window.cotCart = window.cotCart || [];
                if (cfgState.cartItemId) {
                    var idx = window.cotCart.findIndex(function (c) { return c.id === cfgState.cartItemId; });
                    if (idx >= 0) window.cotCart[idx] = item;
                    else window.cotCart.push(item);
                } else {
                    window.cotCart.push(item);
                }

                if (cfgModalInstance) cfgModalInstance.hide();

                if (window.cotCatalog) {
                    window.cotCatalog.renderCart();
                    window.cotCatalog.renderGrid();
                }

                // Toast suave
                Swal.fire({
                    icon: 'success',
                    title: cfgState.cartItemId ? 'Actualizado' : 'Añadido al carrito',
                    text: p.modelo + ' · ' + cfgState.colorNombre + ' · ' + totalQty + ' unidades',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1700
                });
            });

            // Cargar colores/tallas si aún no están cargados al abrir
            $('#cotConfiguradorModal').on('show.bs.modal', function () {
                // El catálogo (más ancho) queda abierto detrás; ocultarlo para que
                // su cabecera/✕ no asomen por los bordes del configurador.
                var catEl = document.getElementById('catalogoProductosModal');
                if (catEl && catEl.classList.contains('show')) catEl.classList.add('cot-modal-hidden-behind');

                if (typeof coloresArray !== 'undefined' && (!coloresArray || coloresArray.length === 0)) {
                    $.get("{{ route('colores.data') }}", function (data) {
                        coloresArray = data;
                        renderColorGrid();
                    });
                }
                if (typeof tallasArray !== 'undefined' && (!tallasArray || tallasArray.length === 0)) {
                    if (typeof cargarTallasCatalogo === 'function') {
                        cargarTallasCatalogo(renderTallasGrid);
                    }
                }
            });

            // Al cerrar el configurador, restaurar el catálogo (para "Volver al catálogo")
            $('#cotConfiguradorModal').on('hide.bs.modal', function () {
                var catEl = document.getElementById('catalogoProductosModal');
                if (catEl) catEl.classList.remove('cot-modal-hidden-behind');
            });

            // Reset al cerrar
            $('#cotConfiguradorModal').on('hidden.bs.modal', function () {
                cfgState = { producto: null, colorId: null, colorNombre: '', colorHex: null, tallas: {}, cartItemId: null };
            });

            // Permitir editar item del carrito (lateral) — abrir configurador con datos cargados
            $(document).on('click', '.cat-cart-item', function (e) {
                if ($(e.target).closest('.cat-cart-item-remove').length) return;
                var $info = $(this).find('.cat-cart-item-info');
                if (!$info.length) return;
                // El item id se almacena en el li padre via data-cart-item-id (lo agregaré en Fase 4)
                // Por ahora doble-click no hace nada en Fase 3.
            });

            window.cotConfigurador = {
                open: openConfigurador,
                state: cfgState
            };
        })();

        // ╔══════════════════════════════════════════════════════════════════
        // ║ COTIZACIONES — CARRITO → LÍNEAS + TABLA AGRUPADA  (Fase 4)
        // ║ Convierte cada item del carrito en N llamadas a addProductItem
        // ║ (una por talla con qty>0). Renderiza una vista agrupada por
        // ║ (producto + color) sobre el productos-container que se mantiene
        // ║ oculto como fuente de verdad para FormData.
        // ╚══════════════════════════════════════════════════════════════════
        (function () {
            'use strict';

            // === Confirmación del carrito ====================================
            window.cotCartConfirmar = function () {
                var cart = window.cotCart || [];
                if (!cart.length) return;

                var totalLineas = 0;
                cart.forEach(function (item) {
                    var unit = parseFloat(item.unitPrice || item.productoPrecio || 0) || 0;
                    item.tallas.forEach(function (t) {
                        if (!t.qty || t.qty <= 0) return;
                        addProductItem(
                            item.productoId,
                            t.qty,
                            unit,
                            '',                 // descripcion
                            false,              // lleva_bordado: el usuario lo activa después por línea/bloque
                            '',                 // _unused
                            item.colorId,
                            t.tallaId,
                            t.generoId,
                            []                  // bordados vacíos
                        );
                        totalLineas++;
                    });
                });

                // Limpiar carrito
                window.cotCart = [];
                if (window.cotCatalog) {
                    window.cotCatalog.renderCart();
                    window.cotCatalog.renderGrid();
                }

                // Cerrar catálogo
                var catEl = document.getElementById('catalogoProductosModal');
                var catInst = catEl ? bootstrap.Modal.getInstance(catEl) : null;
                if (catInst) catInst.hide();

                // Refrescar wizard
                if (window.cotWizard) window.cotWizard.refreshKPIs();
                refreshGroupedList();

                Swal.fire({
                    icon: 'success',
                    title: 'Productos agregados',
                    text: totalLineas + ' línea(s) agregada(s) a la cotización.',
                    toast: true, position: 'top-end',
                    showConfirmButton: false, timer: 1900
                });
            };

            // === Render de la tabla agrupada =================================
            function escForHtml(s) {
                return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
                    return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
                });
            }

            // Hash compacto del array de bordados para agrupar líneas con misma config
            function bordadosKey(bordados) {
                if (!Array.isArray(bordados) || !bordados.length) return 'sb';
                return bordados.map(function (b) {
                    return [
                        b.ubicacion_bordado_id || 'p',
                        b.logo_id || '0',
                        b.nombre_aplicado || '',
                        parseFloat(b.precio_aplicado || 0).toFixed(2),
                        parseInt(b.cantidad || 1, 10)
                    ].join('|');
                }).sort().join(';');
            }

            function readGroupsFromContainer() {
                var groups = [];
                var byKey = {};
                $('#productos-container .product-item').each(function () {
                    var $card = $(this);
                    var prodId = $card.find('.producto-id-input').val();
                    var colorId = $card.find('.color-id-input').val() || '';
                    // Fuente de verdad de bordados: cotGroupBordadosState; fallback al data() de la card.
                    var bordados = getEffectiveBordadosForCard($card);
                    var llevaBordado = (bordados.length > 0) ||
                        (parseInt($card.find('.lleva-bordado-value').val() || 0, 10) === 1);
                    var bKey = llevaBordado ? bordadosKey(bordados) : 'sb';
                    var key = (prodId || 'x') + '|' + (colorId || 'x') + '|' + bKey;

                    if (!byKey[key]) {
                        var producto = (typeof products !== 'undefined' && Array.isArray(products))
                            ? products.find(function (p) { return p.id == prodId; }) : null;
                        var colorObj = (typeof getColorById === 'function') ? getColorById(parseInt(colorId, 10) || 0) : null;
                        byKey[key] = {
                            key: key,
                            productoId: prodId,
                            producto: producto,
                            colorId: colorId,
                            color: colorObj,
                            llevaBordado: llevaBordado,
                            bordados: bordados,
                            cards: [],
                            totalQty: 0,
                            totalSubtotal: 0,
                            unitBase: 0,
                            unitWithBordado: 0
                        };
                        groups.push(byKey[key]);
                    }
                    var qty = parseFloat($card.find('.cantidad-input').val()) || 0;
                    var base = parseFloat($card.find('.precio-unitario-input').val()) || 0;
                    var recargo = llevaBordado && typeof calcularRecargoUnitarioBordadoDesdeLista === 'function'
                        ? calcularRecargoUnitarioBordadoDesdeLista(bordados) : 0;
                    var unit = base + recargo;
                    var tallaId = $card.find('.talla-input-value').val() || '';
                    var tallaLabel = $card.find('.talla-input-display').val() || '';
                    var generoId = $card.find('.genero-id-input').val() || '';
                    var generoNombre = $card.find('.genero-id-input').attr('data-genero-label') || getGeneroNombre(generoId);
                    byKey[key].cards.push({
                        $card: $card,
                        productIndex: $card.data('product-index'),
                        tallaId: tallaId,
                        tallaLabel: tallaLabel,
                        generoId: generoId,
                        generoNombre: generoNombre,
                        qty: qty,
                        base: base,
                        unit: unit
                    });
                    byKey[key].totalQty += qty;
                    byKey[key].totalSubtotal += qty * unit;
                    byKey[key].unitBase = base;
                    byKey[key].unitWithBordado = unit;
                });
                return groups;
            }

            // Construye la línea de variante (tela + atributos) a partir de un producto
            function buildVariantLabel(producto) {
                if (!producto) return '';
                var partes = [];
                if (producto.tela && producto.tela.nombre) {
                    partes.push(producto.tela.nombre);
                }
                var snap = producto.atributos_snapshot;
                if (snap && typeof snap === 'object' && !Array.isArray(snap)) {
                    Object.keys(snap).forEach(function (atr) {
                        partes.push(atr + ': ' + snap[atr]);
                    });
                } else if (Array.isArray(producto.atributo_valores) && producto.atributo_valores.length) {
                    // Fallback si el snapshot no está poblado pero sí los valores
                    producto.atributo_valores.forEach(function (v) {
                        if (v.atributo && v.atributo.nombre) {
                            partes.push(v.atributo.nombre + ': ' + v.nombre);
                        }
                    });
                }
                return partes.join(' · ');
            }
            window.cotBuildVariantLabel = buildVariantLabel;

            function refreshGroupedList() {
                var groups = readGroupsFromContainer();
                var $list = $('#cot-grouped-list');
                var $empty = $('#cot-empty-state');

                if (!groups.length) {
                    $list.empty();
                    $empty.show();
                    return;
                }
                $empty.hide();

                var rowsHtml = groups.map(function (g, idx) {
                    var prodCodigo = g.producto && g.producto.codigo ? g.producto.codigo : '—';
                    var tipoLabel = g.producto && g.producto.tipo_producto ? g.producto.tipo_producto.nombre : '';
                    // Si hay modelo propio: pill de familia + modelo como título.
                    // Si no: el título es la familia (sin pill redundante).
                    var hasModelo = !!(g.producto && g.producto.modelo && String(g.producto.modelo).trim());
                    var prodTitulo = hasModelo ? g.producto.modelo : (tipoLabel || '(producto sin definir)');
                    var prodPill = (hasModelo && tipoLabel)
                        ? '<span class="cot-tipo-pill">' + escForHtml(tipoLabel) + '</span>'
                        : '';
                    var variantLabel = buildVariantLabel(g.producto);
                    var colorName = g.color ? g.color.nombre : (g.colorId ? '#' + g.colorId : 'Sin color');
                    var colorHex = g.color ? g.color.hex_referencial : '#cccccc';
                    var lightHex = (String(colorHex).toUpperCase() === '#FFFFFF' || String(colorHex).toUpperCase() === '#FFFDD0');

                    var tallasChips = g.cards.map(function (c) {
                        var gen = c.generoNombre ? '<span class="cot-chip-gen">' + escForHtml(c.generoNombre) + '</span>' : '';
                        return '<span class="cot-chip cot-chip-talla">' +
                                    escForHtml(c.tallaLabel || '?') + gen + '<span class="cot-chip-x">×</span>' +
                                    '<strong>' + c.qty + '</strong>' +
                               '</span>';
                    }).join('');

                    var bordadoCount = g.bordados ? g.bordados.length : 0;
                    var bordadoRecargo = (g.unitWithBordado || 0) - (g.unitBase || 0);
                    var bordadoLine;
                    if (bordadoCount > 0) {
                        bordadoLine = '<div class="cot-grouped-bordado-line cot-grouped-bordado-line--ok">' +
                            '<i class="ri-scissors-cut-line"></i>' +
                            bordadoCount + ' bordado' + (bordadoCount === 1 ? '' : 's') +
                            (bordadoRecargo > 0 ? ' · +' + formatMoney(bordadoRecargo) + '/u' : '') +
                            '</div>';
                    } else if (g.llevaBordado) {
                        bordadoLine = '<div class="cot-grouped-bordado-line cot-grouped-bordado-line--pending">' +
                            '<i class="ri-error-warning-line"></i>Bordado sin configurar</div>';
                    } else {
                        bordadoLine = '<div class="cot-grouped-bordado-line cot-grouped-bordado-line--none">' +
                            '<i class="ri-subtract-line"></i>Sin bordado</div>';
                    }

                    var indices = g.cards.map(function (c) { return c.productIndex; }).join(',');
                    var unitDisplay = formatMoney(g.unitWithBordado);
                    var unitNote = (g.llevaBordado && g.unitWithBordado !== g.unitBase)
                        ? '<small class="cot-grouped-unit-note">' + formatMoney(g.unitBase) + ' + ' + formatMoney(g.unitWithBordado - g.unitBase) + '</small>'
                        : '';

                    return (
                        '<tr class="cot-grouped-row" data-group-key="' + escForHtml(g.key) + '" data-product-id="' + escForHtml(g.productoId) + '" data-color-id="' + escForHtml(g.colorId) + '" data-card-indices="' + escForHtml(indices) + '">' +
                            '<td class="cot-col-num">' + (idx + 1) + '</td>' +
                            '<td class="cot-col-prod">' +
                                prodPill +
                                '<div class="cot-prod-modelo">' + escForHtml(prodTitulo) + '</div>' +
                                '<div class="cot-prod-codigo"><i class="ri-barcode-line"></i>' + escForHtml(prodCodigo) + '</div>' +
                                (variantLabel
                                    ? '<div class="cot-prod-variant"><i class="ri-shape-2-line"></i>' + escForHtml(variantLabel) + '</div>'
                                    : '') +
                            '</td>' +
                            '<td class="cot-col-color">' +
                                '<span class="cot-color-cell">' +
                                    '<span class="cot-color-dot" style="background:' + colorHex + ';' + (lightHex ? 'border-color:#cbd5e1;' : '') + '"></span>' +
                                    escForHtml(colorName) +
                                '</span>' +
                            '</td>' +
                            '<td class="cot-col-tallas">' +
                                '<div class="cot-tallas-wrap">' + tallasChips + '</div>' +
                                bordadoLine +
                            '</td>' +
                            '<td class="cot-col-num cot-cell-num"><strong>' + g.totalQty + '</strong></td>' +
                            '<td class="cot-cell-num">' + unitDisplay + unitNote + '</td>' +
                            '<td class="cot-cell-num cot-cell-subtotal">' + formatMoney(g.totalSubtotal) + '</td>' +
                            '<td class="cot-col-acc">' +
                                '<button type="button" class="cot-grouped-action-btn cot-action-edit" title="Editar"><i class="ri-edit-2-line"></i></button>' +
                                '<button type="button" class="cot-grouped-action-btn cot-action-bordado" title="Configurar bordado"><i class="ri-scissors-cut-line"></i></button>' +
                                '<button type="button" class="cot-grouped-action-btn cot-action-delete" title="Eliminar"><i class="ri-delete-bin-line"></i></button>' +
                            '</td>' +
                        '</tr>'
                    );
                }).join('');

                var tableHtml =
                    '<div class="cot-grouped-tablewrap">' +
                        '<table class="cot-grouped-table">' +
                            '<thead>' +
                                '<tr>' +
                                    '<th class="cot-col-num">#</th>' +
                                    '<th class="cot-col-prod">Producto</th>' +
                                    '<th class="cot-col-color">Color</th>' +
                                    '<th class="cot-col-tallas">Tallas y cantidades</th>' +
                                    '<th class="cot-col-num">Unid.</th>' +
                                    '<th class="cot-cell-num">Precio U.</th>' +
                                    '<th class="cot-cell-num">Subtotal</th>' +
                                    '<th class="cot-col-acc">Acc.</th>' +
                                '</tr>' +
                            '</thead>' +
                            '<tbody>' + rowsHtml + '</tbody>' +
                        '</table>' +
                    '</div>';

                $list.html(tableHtml);
            }

            window.cotRefreshGroupedList = refreshGroupedList;
            window.cotReadGroups = readGroupsFromContainer;

            // === Acciones de bloque ==========================================
            // Eliminar bloque entero
            $(document).on('click', '.cot-action-delete', function () {
                var $blk = $(this).closest('.cot-grouped-row');
                // .attr() (no .data()): jQuery coacciona data-card-indices="0" al numero 0,
                // y `0 || ''` daria '' (0 es falsy) dejando sin indices al primer producto.
                var indices = String($blk.attr('data-card-indices') || '').split(',').filter(Boolean);
                Swal.fire({
                    icon: 'warning',
                    title: '¿Eliminar bloque?',
                    text: 'Se quitarán todas las líneas asociadas a este producto y color.',
                    showCancelButton: true,
                    confirmButtonText: 'Eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#c0392b'
                }).then(function (res) {
                    if (!res.isConfirmed) return;
                    indices.forEach(function (idx) {
                        $('#productos-container .product-item[data-product-index="' + idx + '"]').remove();
                    });
                    if (typeof reindexProductItems === 'function') reindexProductItems();
                    if (typeof calculateCotizacionTotals === 'function') calculateCotizacionTotals();
                    refreshGroupedList();
                    if (window.cotWizard) window.cotWizard.refreshKPIs();
                });
            });

            // Editar bloque → reabre configurador con datos cargados
            $(document).on('click', '.cot-action-edit', function () {
                var $blk = $(this).closest('.cot-grouped-row');
                var prodId = $blk.data('product-id');          // puede ser virtual ('v1') o numérico
                var colorId = parseInt($blk.data('color-id'), 10);
                var groupKey = $blk.data('group-key');

                // Reconstruir tallas y precio del bloque
                var tallasMap = {};
                var precioFromCards = null;
                $('#productos-container .product-item').each(function () {
                    var $c = $(this);
                    var pid = String($c.find('.producto-id-input').val());
                    var cid = parseInt($c.find('.color-id-input').val(), 10);
                    if (pid !== String(prodId) || cid !== colorId) return;
                    var tid = parseInt($c.find('.talla-input-value').val(), 10);
                    var gid = parseInt($c.find('.genero-id-input').val(), 10);
                    var qty = parseInt($c.find('.cantidad-input').val(), 10) || 0;
                    if (tid && gid && qty > 0) {
                        if (!tallasMap[tid]) tallasMap[tid] = {};
                        tallasMap[tid][gid] = qty;
                    }
                    var price = parseFloat($c.find('.precio-unitario-input').val());
                    if (precioFromCards == null && !isNaN(price)) precioFromCards = price;
                });

                var color = (typeof getColorById === 'function') ? getColorById(colorId) : null;

                window.__cotEditGroupKey = groupKey;
                if (typeof window.cotConfiguradorAbrir === 'function') {
                    window.cotConfiguradorAbrir(prodId, {
                        existing: {
                            colorId: colorId,
                            colorNombre: color ? color.nombre : '',
                            colorHex: color ? color.hex_referencial : null,
                            tallas: tallasMap,
                            precioUnitario: precioFromCards
                        },
                        cartItemId: 'edit_' + groupKey  // marca de edición
                    });
                }
            });

            // Bordados — usa cotGroupBordadosState como fuente de verdad (desacoplado de DOM)
            $(document).on('click', '.cot-action-bordado', function () {
                var $blk = $(this).closest('.cot-grouped-row');
                var productoId = $blk.attr('data-product-id') || '';
                var colorId = $blk.attr('data-color-id') || '';
                var groupKey = productoId + '|' + colorId;

                // Asegurar que el checkbox esté activo en la primera card del bloque
                var indicesAttr = $blk.attr('data-card-indices') || '';
                var firstIdx = indicesAttr.split(',').filter(Boolean)[0];
                if (firstIdx) {
                    var $firstCard = $('#productos-container .product-item[data-product-index="' + firstIdx + '"]');
                    if ($firstCard.length) {
                        currentBordadoCard = $firstCard;
                        var $chk = $firstCard.find('.lleva-bordado-checkbox');
                        if (!$chk.is(':checked')) $chk.prop('checked', true).trigger('change');
                    }
                }

                currentBordadoGroupKey = groupKey;

                // Poblar el header del offcanvas con identidad del producto y color
                var prodObj = (typeof products !== 'undefined' && Array.isArray(products))
                    ? products.find(function (p) { return p.id == productoId; }) : null;
                var colorObj = (typeof getColorById === 'function') ? getColorById(parseInt(colorId, 10) || 0) : null;
                var prodLabel = prodObj
                    ? ((prodObj.modelo || prodObj.descripcion || '') + (prodObj.codigo ? ' · ' + prodObj.codigo : ''))
                    : '(producto)';
                var colorName = colorObj ? colorObj.nombre : (colorId ? 'Color #' + colorId : 'Sin color');
                var colorHex  = colorObj ? (colorObj.hex_referencial || '#ccc') : '#ccc';

                $('#bordado-oc-producto').text(prodLabel);
                $('#bordado-oc-color-name').text(colorName);
                $('#bordado-oc-color-dot').css('background', colorHex);

                var offcanvasEl = document.getElementById('bordadoOffcanvas');
                if (typeof ubicacionModal !== 'undefined' && ubicacionModal && typeof ubicacionModal.show === 'function') {
                    ubicacionModal.show();
                } else if (offcanvasEl) {
                    bootstrap.Modal.getOrCreateInstance(offcanvasEl).show();
                } else {
                    Swal.fire({ icon: 'error', title: 'Panel de bordados no disponible',
                        text: 'El sistema de bordados no se cargó correctamente.',
                        timer: 2200, showConfirmButton: false });
                }
            });

            // Hook configurador en modo edición → reemplazar bloque viejo por el nuevo
            // Interceptamos el guardado mediante override del listener; pero como el listener original
            // ya empuja al cart, aquí en modo edit el cartItemId arranca con "edit_" → manejamos especial.
            (function patchCfgSaveForEdit() {
                $(document).on('click', '#cfg-save-btn', function () {
                    // Diferimos un poco para que el push al cart suceda primero
                    setTimeout(function () {
                        if (!window.cotConfigurador) return;
                        var lastItem = (window.cotCart || []).slice(-1)[0];
                        if (!lastItem || !lastItem.id || String(lastItem.id).indexOf('edit_') !== 0) return;

                        // Es una edición de un bloque existente:
                        // 1) Eliminar las cards viejas del bloque (todas las que comparten producto+color)
                        //    productoIdOriginal apunta al SKU que estaba antes del cambio de variante;
                        //    si no hubo cambio, equivale al productoId actual.
                        var prodIdParaBorrar = lastItem.productoIdOriginal || lastItem.productoId;
                        var colorId = lastItem.colorId;
                        $('#productos-container .product-item').each(function () {
                            var $c = $(this);
                            var pid = String($c.find('.producto-id-input').val());
                            var cid = parseInt($c.find('.color-id-input').val(), 10);
                            if (pid === String(prodIdParaBorrar) && cid === colorId) $c.remove();
                        });

                        // 2) Insertar nuevas cards del item editado (una por talla)
                        var unit = parseFloat(lastItem.unitPrice || lastItem.productoPrecio || 0) || 0;
                        lastItem.tallas.forEach(function (t) {
                            if (!t.qty || t.qty <= 0) return;
                            addProductItem(
                                lastItem.productoId, t.qty, unit, '', false, '',
                                lastItem.colorId, t.tallaId, t.generoId, []
                            );
                        });

                        // 2b) Si cambió la variante (productoId distinto al original), migrar
                        // los bordados del groupKey viejo al nuevo para no perderlos.
                        if (prodIdParaBorrar && prodIdParaBorrar !== lastItem.productoId &&
                            window.cotGroupBordadosState) {
                            var oldKey = prodIdParaBorrar + '|' + colorId;
                            var newKey = lastItem.productoId + '|' + colorId;
                            if (window.cotGroupBordadosState[oldKey]) {
                                window.cotGroupBordadosState[newKey] = window.cotGroupBordadosState[oldKey];
                                delete window.cotGroupBordadosState[oldKey];
                            }
                        }

                        // 3) Quitar el item del carrito (era una edición, no debe quedar como item nuevo)
                        window.cotCart = (window.cotCart || []).filter(function (it) {
                            return it.id !== lastItem.id;
                        });
                        if (window.cotCatalog) window.cotCatalog.renderCart();

                        // 4) Reindex y refresh
                        if (typeof reindexProductItems === 'function') reindexProductItems();
                        if (typeof calculateCotizacionTotals === 'function') calculateCotizacionTotals();
                        if (window.cotWizard) window.cotWizard.refreshKPIs();
                        refreshGroupedList();

                        Swal.fire({
                            icon: 'success', title: 'Bloque actualizado',
                            toast: true, position: 'top-end',
                            showConfirmButton: false, timer: 1500
                        });
                    }, 60);
                });
            })();

            // Refrescar cuando algo cambie en productos-container
            $(document).on('change keyup', '#productos-container .cantidad-input, #productos-container .precio-unitario-input', function () {
                clearTimeout(window.__cotGroupedDebounce);
                window.__cotGroupedDebounce = setTimeout(refreshGroupedList, 220);
            });
            $(document).on('change', '#productos-container .lleva-bordado-checkbox', function () {
                refreshGroupedList();
            });
            $(document).on('click', '#productos-container .remove-producto-item', function () {
                refreshGroupedList();
            });

            // También refrescar cuando se carga edit (Fase 6) o se agrega manual
            $(document).on('click', '#add-producto-item', function () {
                refreshGroupedList();
            });
        })();

        // ╔══════════════════════════════════════════════════════════════════
        // ║ COTIZACIONES — WIZARD 3 PASOS  (Cliente · Productos · Resumen)
        // ║ Fase 1: navegación, validación mínima, sincronización footer,
        // ║ refresco de KPIs (paso 2) y resumen visual (paso 3).
        // ╚══════════════════════════════════════════════════════════════════
        (function () {
            'use strict';
            var TOTAL_STEPS = 3;
            var IVA_RATE = 0.16;
            var currentStep = 1;

            function isEditMode() { return !!$('#id-field').val(); }

            function escHtmlW(str) {
                return String(str == null ? '' : str).replace(/[&<>"']/g, function (c) {
                    return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
                });
            }

            // Datos del usuario logueado para el chip "Creada por" en modo crear
            window.cotCreadorDefault = { name: @json(Auth::user()->name), avatar: @json(Auth::user()->avatar_url) };

            function showStep(n) {
                n = Math.max(1, Math.min(TOTAL_STEPS, n));
                currentStep = n;

                document.querySelectorAll('#showModal .wiz-step-content').forEach(function (sec) {
                    var step = parseInt(sec.dataset.step, 10);
                    var active = step === n;
                    sec.classList.toggle('is-active', active);
                    sec.hidden = !active;
                });

                document.querySelectorAll('#showModal .wiz-step-marker').forEach(function (mk) {
                    var step = parseInt(mk.dataset.step, 10);
                    mk.classList.toggle('is-active', step === n);
                    mk.classList.toggle('is-complete', step < n);
                    mk.setAttribute('aria-selected', step === n ? 'true' : 'false');
                });

                document.querySelectorAll('#showModal .wiz-step-line-fill').forEach(function (lf) {
                    var line = parseInt(lf.dataset.line, 10);
                    lf.style.width = (line < n) ? '100%' : '0%';
                });

                var ind = document.getElementById('cot-step-current');
                if (ind) ind.textContent = String(n);

                var $prev = $('#btn-cot-prev');
                var $next = $('#btn-cot-next');
                var $add = $('#add-btn');
                var $edit = $('#edit-btn');

                $prev.toggle(n > 1);

                if (n === TOTAL_STEPS) {
                    $next.hide();
                    if (isEditMode()) { $add.hide(); $edit.show(); }
                    else { $edit.hide(); $add.show(); }
                    refreshResumen();
                } else {
                    $next.show();
                    $add.hide(); $edit.hide();
                }

                if (n === 2) refreshKPIs();

                // Banner cliente persistente: visible en pasos 2+ si hay cliente
                var hasClient = !!$('#cliente-id-field').val();
                var showBanner = hasClient && n > 1;
                $('#cot-cliente-banner').attr('hidden', !showBanner).attr('aria-hidden', showBanner ? 'false' : 'true');

                // Chip "Creada por": siempre visible. Crear → usuario logueado; editar → creador real (hidratado).
                if (!isEditMode() && window.cotCreadorDefault) {
                    $('#cot-creador-name').text(window.cotCreadorDefault.name);
                    $('#cot-creador-avatar').attr('src', window.cotCreadorDefault.avatar);
                }
                $('#cot-creador-banner').removeAttr('hidden').attr('aria-hidden', 'false');
            }

            function nextStep() {
                if (currentStep < TOTAL_STEPS) {
                    if (!validateStep(currentStep)) return;
                    showStep(currentStep + 1);
                }
            }
            function prevStep() {
                if (currentStep > 1) showStep(currentStep - 1);
            }

            function validateStep(n) {
                if (n === 1) {
                    var clienteId = $('#cliente-id-field').val();
                    var fecha = $('#fecha-cotizacion-field').val();
                    var validez = $('#fecha-validez-field').val();
                    if (!clienteId) {
                        Swal.fire({
                            icon: 'warning', title: 'Cliente requerido',
                            text: 'Debes seleccionar o crear un cliente antes de continuar.',
                            timer: 2200, showConfirmButton: false
                        });
                        $('#ci-rif-number-field').trigger('focus');
                        return false;
                    }
                    if (!fecha) {
                        Swal.fire({
                            icon: 'warning', title: 'Fecha requerida',
                            text: 'La fecha de emisión es obligatoria.',
                            timer: 2200, showConfirmButton: false
                        });
                        $('#fecha-cotizacion-field').trigger('focus');
                        return false;
                    }
                    if (!validez) {
                        Swal.fire({
                            icon: 'warning', title: 'Fecha de validez requerida',
                            text: 'Indica hasta cuándo son válidos los precios (puedes usar los atajos de validez rápida).',
                            timer: 2600, showConfirmButton: false
                        });
                        $('#fecha-validez-field').trigger('focus');
                        return false;
                    }
                    if (validez < fecha) {
                        Swal.fire({
                            icon: 'warning', title: 'Fechas inconsistentes',
                            text: 'La fecha de validez no puede ser anterior a la fecha de emisión.',
                            timer: 2400, showConfirmButton: false
                        });
                        $('#fecha-validez-field').trigger('focus');
                        return false;
                    }
                    // Si cualquier campo del paso 1 está marcado is-invalid, frenar
                    var $invalid = $('#cot-step-1 .is-invalid:visible');
                    if ($invalid.length) {
                        Swal.fire({
                            icon: 'warning', title: 'Corrige los errores',
                            text: 'Revisa los campos marcados en rojo antes de continuar.',
                            timer: 2200, showConfirmButton: false
                        });
                        $invalid.first().trigger('focus');
                        return false;
                    }
                    return true;
                }
                if (n === 2) {
                    var rows = $('#productos-container .product-item').length;
                    if (!rows) {
                        Swal.fire({
                            icon: 'warning', title: 'Sin productos',
                            text: 'Agrega al menos un producto desde el catálogo para continuar.',
                            timer: 2400, showConfirmButton: false
                        });
                        return false;
                    }
                    // Validar que cada línea tenga producto, color, talla y cantidad>0
                    var problemas = [];
                    $('#productos-container .product-item').each(function (i) {
                        var $c = $(this);
                        if (!$c.find('.producto-id-input').val()) problemas.push('línea ' + (i + 1) + ': producto');
                        if (!$c.find('.talla-input-value').val()) problemas.push('línea ' + (i + 1) + ': talla');
                        var qty = parseInt($c.find('.cantidad-input').val(), 10) || 0;
                        if (qty <= 0) problemas.push('línea ' + (i + 1) + ': cantidad');
                        var precio = parseFloat($c.find('.precio-unitario-input').val()) || 0;
                        if (precio <= 0) problemas.push('línea ' + (i + 1) + ': precio');
                    });
                    if (problemas.length) {
                        Swal.fire({
                            icon: 'warning', title: 'Datos incompletos',
                            html: 'Faltan datos en:<br><strong>' + problemas.slice(0, 6).join('<br>') +
                                  (problemas.length > 6 ? '<br>…' : '') + '</strong>',
                            timer: 3500, showConfirmButton: true
                        });
                        return false;
                    }
                    return true;
                }
                return true;
            }

            function readLineState($card) {
                var qty = parseFloat($card.find('.cantidad-input').val()) || 0;
                var base = parseFloat($card.find('.precio-unitario-input').val()) || 0;
                var lleva = parseInt($card.find('.lleva-bordado-value').val() || 0, 10) === 1;
                var recargo = lleva && typeof calcularRecargoUnitarioBordadoDesdeLista === 'function'
                    ? calcularRecargoUnitarioBordadoDesdeLista(getCardBordados($card))
                    : 0;
                return { qty: qty, unit: base + recargo };
            }

            function refreshEmptyState() {
                var hasItems = $('#productos-container .product-item').length > 0;
                $('#cot-empty-state').toggle(!hasItems);
            }

            function refreshKPIs() {
                refreshEmptyState();
                var $items = $('#productos-container .product-item');
                var subtotal = 0;
                $items.each(function () {
                    var s = readLineState($(this));
                    subtotal += s.qty * s.unit;
                });
                $('#cot-kpi-items').text($items.length);
                $('#cot-kpi-subtotal').text(formatMoney(subtotal));
                $('#cot-kpi-subtotal-bs').text(bsApprox(subtotal));
                $('#cot-kpi-total').text(formatMoney(subtotal));
                $('#cot-kpi-total-bs').text(bsApprox(subtotal));
            }

            function refreshResumen() {
                var subtotal = 0;
                var rows = [];

                if (typeof window.cotReadGroups === 'function') {
                    // Vista agrupada (Fase 4): una fila por bloque
                    var groups = window.cotReadGroups();
                    groups.forEach(function (g, idx) {
                        subtotal += g.totalSubtotal;
                        var prodLabel = g.producto
                            ? ((g.producto.codigo ? g.producto.codigo + ' · ' : '') + (g.producto.tipo_producto ? g.producto.tipo_producto.nombre : ''))
                            : '(producto sin definir)';
                        var variantLabel = (typeof window.cotBuildVariantLabel === 'function')
                            ? window.cotBuildVariantLabel(g.producto)
                            : '';
                        var colorName = g.color ? g.color.nombre : (g.colorId ? '#' + g.colorId : '');
                        var tallasTxt = g.cards.map(function (c) { return c.tallaLabel + (c.generoNombre ? '·' + c.generoNombre : '') + '×' + c.qty; }).join(' · ');
                        var bordadoBadge = g.llevaBordado
                            ? ' <span class="cot-resumen-bordado-pill"><i class="ri-scissors-cut-line"></i> bordado</span>'
                            : '';
                        var unitDisplay = formatMoney(g.unitWithBordado);
                        var unitNoteTxt = (g.llevaBordado && g.unitWithBordado !== g.unitBase)
                            ? (formatMoney(g.unitBase) + ' + ' + formatMoney(g.unitWithBordado - g.unitBase) + ' bordado')
                            : '';
                        var codigo = (g.producto && g.producto.codigo) ? g.producto.codigo : '';
                        var tipoNombre = (g.producto && g.producto.tipo_producto) ? g.producto.tipo_producto.nombre : '(sin tipo)';
                        var colorHex = g.color ? g.color.hex_referencial : null;
                        var tallasPills = g.cards.map(function (c) {
                            var gen = c.generoNombre ? '<span class="cot-linea-genero">' + escHtmlW(c.generoNombre) + '</span>' : '';
                            return '<span class="cot-linea-talla">' + escHtmlW(c.tallaLabel) + gen + '<b>×' + c.qty + '</b></span>';
                        }).join('');
                        var imgUrl = (g.producto && g.producto.imagen) ? g.producto.imagen : '';
                        var thumb = imgUrl
                            ? '<div class="cot-linea-thumb"><img src="' + escHtmlW(imgUrl) + '" alt="" class="cot-linea-thumb-img"></div>'
                            : '<div class="cot-linea-thumb"><span class="cot-linea-thumb-ph"><i class="ri-t-shirt-2-line"></i></span></div>';

                        rows.push(
                            '<div class="cot-linea-card">' +
                                thumb +
                                '<div class="cot-linea-info">' +
                                    '<div class="cot-linea-top">' +
                                        (codigo ? '<span class="cot-linea-codigo">' + escHtmlW(codigo) + '</span>' : '') +
                                        '<span class="cot-linea-nombre">' + escHtmlW(tipoNombre) + '</span>' +
                                        bordadoBadge +
                                    '</div>' +
                                    '<div class="cot-linea-chips">' +
                                        (variantLabel ? '<span class="cot-linea-chip"><i class="ri-shape-2-line"></i>' + escHtmlW(variantLabel) + '</span>' : '') +
                                        (colorName ? '<span class="cot-linea-chip"><span class="cot-linea-swatch" style="background:' + (colorHex || '#cbd5e1') + '"></span>' + escHtmlW(colorName) + '</span>' : '') +
                                    '</div>' +
                                    (tallasPills ? '<div class="cot-linea-tallas">' + tallasPills + '</div>' : '') +
                                '</div>' +
                                '<div class="cot-linea-precio">' +
                                    '<div class="cot-linea-qxu">' + g.totalQty + ' u × ' + unitDisplay + '</div>' +
                                    (unitNoteTxt ? '<div class="cot-linea-unitnote">' + unitNoteTxt + '</div>' : '') +
                                    '<div class="cot-linea-sub">' + formatMoney(g.totalSubtotal) + '</div>' +
                                '</div>' +
                            '</div>'
                        );
                    });
                } else {
                    // Fallback línea-por-línea (vista antigua, por si los grupos no están)
                    var $items = $('#productos-container .product-item');
                    $items.each(function () {
                        var $c = $(this);
                        var s = readLineState($c);
                        var rowSubtotal = s.qty * s.unit;
                        subtotal += rowSubtotal;
                        var $prodSel = $c.find('.producto-dropdown');
                        var prodName = $prodSel.length
                            ? ($prodSel.find('option:selected').text() || 'Producto sin definir')
                            : ($c.find('.producto-text-display').val() || 'Producto sin definir');
                        var colorName = $c.find('.color-display').val() || '';
                        var tallaName = $c.find('.talla-input-display').val() || '';
                        var bits = [colorName, tallaName].filter(Boolean).join(' · ');
                        rows.push(
                            '<div class="cot-linea-card">' +
                                '<div class="cot-linea-thumb"><span class="cot-linea-thumb-ph"><i class="ri-t-shirt-2-line"></i></span></div>' +
                                '<div class="cot-linea-info">' +
                                    '<div class="cot-linea-top">' +
                                        '<span class="cot-linea-nombre">' + escHtmlW(prodName) + '</span>' +
                                    '</div>' +
                                    (bits ? '<div class="cot-linea-chips"><span class="cot-linea-chip">' + escHtmlW(bits) + '</span></div>' : '') +
                                '</div>' +
                                '<div class="cot-linea-precio">' +
                                    '<div class="cot-linea-qxu">' + s.qty + ' u × ' + formatMoney(s.unit) + '</div>' +
                                    '<div class="cot-linea-sub">' + formatMoney(rowSubtotal) + '</div>' +
                                '</div>' +
                            '</div>'
                        );
                    });
                }

                if (!rows.length) {
                    rows.push('<div class="cot-lineas-empty text-center text-muted py-3 small">Sin productos agregados</div>');
                }
                var iva = subtotal * IVA_RATE;
                var total = subtotal + iva;
                $('#cot-resumen-lineas').html(rows.join(''));
                $('#cot-resumen-subtotal').text(formatMoney(subtotal));
                $('#cot-resumen-iva').text(formatMoney(iva));
                $('#cot-resumen-total').text(formatMoney(total));
                // Tasa BCV del día (con su fecha en el label) y equivalente en Bs
                var cotResFecha = (typeof window.bcvFechaFmt === 'function') ? window.bcvFechaFmt() : '';
                $('#cot-resumen-tasa-fecha').text(cotResFecha ? ' (' + cotResFecha + ')' : '');
                if (window.tasaBcv && window.tasaBcv.valor) {
                    $('#cot-resumen-tasa').text('Bs ' + parseFloat(window.tasaBcv.valor).toLocaleString('es-VE', { minimumFractionDigits: 4, maximumFractionDigits: 4 }));
                } else {
                    $('#cot-resumen-tasa').text('No disponible');
                }
                var bsLabel = (typeof window.bsEquivalente === 'function') ? window.bsEquivalente(total) : null;
                $('#cot-resumen-total-bs').text(bsLabel || 'Sin tasa BCV');

                cotRefreshProyeccion();
            }

            // Recolecta las líneas actuales del wizard (fuente de verdad: los
            // inputs ocultos de #productos-container) para la proyección de stock.
            function cotCollectLineasProyeccion() {
                var lineas = [];
                $('#productos-container .product-item').each(function () {
                    var $c = $(this);
                    var pick = function (suffix) {
                        var $el = $c.find('[name$="[' + suffix + ']"]').first();
                        return $el.length ? $el.val() : null;
                    };
                    var cantidad = parseInt(pick('cantidad') || '0', 10);
                    if (!cantidad || cantidad < 1) return;
                    lineas.push({
                        producto_id: pick('producto_id') || null,
                        tipo_producto_id: pick('tipo_producto_id') || null,
                        tela_id: pick('insumo_tela_id') || null,
                        cantidad: cantidad
                    });
                });
                return lineas;
            }

            // Pide y pinta la proyección NO bloqueante de insumos para producción.
            function cotRefreshProyeccion() {
                var bodyEl = document.getElementById('cot-proyeccion-body');
                var badgeEl = document.getElementById('cot-proyeccion-badge');
                if (!bodyEl || !window.ProyeccionInsumos) return;

                var lineas = cotCollectLineasProyeccion();
                if (!lineas.length) {
                    bodyEl.innerHTML = '<div class="proy-state proy-state--muted">' +
                        '<i class="ri-information-line me-1"></i>Agrega productos para ver la proyección de insumos.</div>';
                    if (badgeEl) badgeEl.hidden = true;
                    return;
                }

                ProyeccionInsumos.cargar({
                    url: '{{ route("cotizaciones.proyeccionInsumos") }}',
                    method: 'POST',
                    csrf: $('meta[name="csrf-token"]').attr('content'),
                    payload: { lineas: lineas },
                    bodyEl: bodyEl,
                    badgeEl: badgeEl,
                    contexto: 'cotizacion'
                });
            }

            // Recalcular al volver a esta pestaña (p.ej. tras procesar una compra
            // en otra pestaña con el atajo). offsetParent != null ⇒ el panel está
            // realmente visible (modal abierto + paso 3), así no se pide en vano.
            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState !== 'visible') return;
                var body = document.getElementById('cot-proyeccion-body');
                if (body && body.offsetParent !== null) cotRefreshProyeccion();
            });

            // Tiempo real entre pestañas: si en otra pestaña se procesa/anula una
            // compra, recalcular al instante (sin esperar a recuperar el foco).
            if (window.ProyeccionInsumos && ProyeccionInsumos.onStockChange) {
                ProyeccionInsumos.onStockChange(function () {
                    var body = document.getElementById('cot-proyeccion-body');
                    if (body && body.offsetParent !== null) cotRefreshProyeccion();
                });
            }

            // === LISTENERS =====================================================
            $('#btn-cot-next').on('click', nextStep);
            $('#btn-cot-prev').on('click', prevStep);

            // Click en marker del stepper: retroceder libre, avanzar con validación
            $(document).on('click', '#showModal .wiz-step-marker', function () {
                var target = parseInt(this.dataset.step, 10);
                if (target === currentStep) return;
                if (target < currentStep) { showStep(target); return; }
                for (var s = currentStep; s < target; s++) {
                    if (!validateStep(s)) return;
                }
                showStep(target);
            });

            // KPIs en vivo en paso 2
            $(document).on('change keyup',
                '#productos-container .cantidad-input, #productos-container .precio-unitario-input',
                function () { if (currentStep === 2) refreshKPIs(); });
            $(document).on('change', '#productos-container .lleva-bordado-checkbox', function () {
                if (currentStep === 2) refreshKPIs();
            });
            $(document).on('click', '#add-producto-item, #productos-container .remove-producto-item', function () {
                refreshKPIs();
            });
            // Cambios en los selectores de producto/color/talla
            $(document).on('click', '#productos-container .producto-selector-trigger, #productos-container .buscar-color-trigger, #productos-container .buscar-talla-trigger', function () {
                setTimeout(refreshKPIs, 600);
            });

            // Actualiza la hora visible (formato 12h con am/pm)
            function refreshBannerHora() {
                var now = new Date();
                var h = now.getHours();
                var ampm = h >= 12 ? 'p.m.' : 'a.m.';
                h = h % 12; if (h === 0) h = 12;
                var mm = String(now.getMinutes()).padStart(2, '0');
                $('#cot-banner-hora').text(h + ':' + mm + ' ' + ampm);
            }

            // Actualiza la fecha visible: usa la fecha de emisión si está, si no, hoy
            function refreshBannerFecha() {
                var raw = $('#fecha-cotizacion-field').val();
                if (raw) {
                    var parts = raw.split('-');
                    $('#cot-banner-fecha-val').text(parts[2] + '/' + parts[1] + '/' + parts[0]);
                } else {
                    var now = new Date();
                    var dd = String(now.getDate()).padStart(2, '0');
                    var mo = String(now.getMonth() + 1).padStart(2, '0');
                    $('#cot-banner-fecha-val').text(dd + '/' + mo + '/' + now.getFullYear());
                }
            }

            // Refleja la tasa BCV del día en el header (formato del header global)
            function refreshBannerBcv() {
                if (window.tasaBcv && window.tasaBcv.valor) {
                    var val = Number(window.tasaBcv.valor)
                        .toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    $('#cot-banner-bcv-val').text('Bs. ' + val);
                    if (window.tasaBcv.fecha) {
                        var p = String(window.tasaBcv.fecha).split('-');
                        $('#cot-banner-bcv-fecha').text(p.length === 3 ? (p[2] + '/' + p[1]) : '');
                    }
                } else {
                    $('#cot-banner-bcv-val').text('N/D');
                    $('#cot-banner-bcv-fecha').text('');
                }
            }

            // Sincronizar banner-fecha al cambiar el input de fecha
            $(document).on('change', '#fecha-cotizacion-field', refreshBannerFecha);

            // Reloj en vivo: actualiza la hora mientras el modal está abierto
            var horaInterval = null;

            // Reset al abrir el modal
            $('#showModal').on('show.bs.modal', function () {
                refreshBannerHora();
                refreshBannerFecha();
                refreshBannerBcv();
                showStep(1);
                refreshKPIs();
                clearInterval(horaInterval);
                horaInterval = setInterval(refreshBannerHora, 1000);
            });
            $('#showModal').on('shown.bs.modal', function () { showStep(currentStep); });
            $('#showModal').on('hidden.bs.modal', function () {
                currentStep = 1;
                clearInterval(horaInterval);
                horaInterval = null;
            });

            // API global por si otras funciones quieren usarlo
            window.cotWizard = {
                show: showStep, next: nextStep, prev: prevStep,
                refreshKPIs: refreshKPIs, refreshResumen: refreshResumen,
                refreshBannerFecha: refreshBannerFecha
            };
        })();
    });
</script>