<script>
    // ============================================
    // FUNCIONALIDAD DE SELECCIÓN DE COTIZACIONES
    // pedWizardImportMode=true → importar en paso 2
    // pedWizardImportMode=false → abrir wizard completo (TASK-015)
    // ============================================

    let cotizacionesDisponibles = [];

    // Cargar cotizaciones cuando se abre el modal
    $(document).on('shown.bs.modal', '#seleccionarCotizacionModal', function () {
        cargarCotizacionesDisponibles();
    });

    // Función para cargar cotizaciones disponibles
    function cargarCotizacionesDisponibles() {
        const container = $('#cotizaciones-container');
        const emptyState = $('#empty-state');
        const loadingState = $('#loading-state');

        // Mostrar loading
        container.hide();
        emptyState.hide();
        loadingState.show();

        $.ajax({
            url: '{{ route("pedidos.cotizacionesDisponibles") }}',
            method: 'GET',
            success: function (data) {
                loadingState.hide();

                if (data.length === 0) {
                    emptyState.show();
                    return;
                }

                cotizacionesDisponibles = data;
                renderCotizaciones(data);
                container.show();
            },
            error: function (xhr) {
                loadingState.hide();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar las cotizaciones disponibles',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            }
        });
    }

    function escCotSel(s) {
        return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // Renderizar cotizaciones como cards
    function renderCotizaciones(cotizaciones) {
        const container = $('#cotizaciones-container');
        container.empty();

        cotizaciones.forEach(function (cotizacion) {
            const nombre  = escCotSel(cotizacion.cliente_nombre);
            const inicial = escCotSel((cotizacion.cliente_nombre || '?').trim().charAt(0).toUpperCase() || '?');
            const nProd   = parseInt(cotizacion.cantidad_productos, 10) || 0;
            // Equivalente Bs con la tasa pactada en la cotización (fallback: BCV vigente)
            const bs = window.bsEquivalente ? window.bsEquivalente(cotizacion.total_raw, cotizacion.tasa_cambio_valor) : null;

            const card = `
                <div class="cotizacion-card" data-cotizacion-id="${cotizacion.id}">
                    <div class="cotizacion-header">
                        <div class="ped-cell">
                            <span class="ped-cell-ic"><i class="ri-file-list-3-line"></i></span>
                            <span class="ped-cell-txt"><span class="ped-cell-eyebrow">Cotización</span><span class="ped-cell-num">#${cotizacion.id}</span></span>
                        </div>
                        <span class="wiz-client-banner wiz-client-banner--sm" title="Cliente de la cotización">
                            <span class="wiz-client-banner-avatar">${inicial}</span>
                            <span class="wiz-client-banner-main">
                                <span class="wiz-client-banner-eyebrow">Cliente</span>
                                <span class="wiz-client-banner-name">${nombre}</span>
                                <span class="wiz-client-banner-sub"><span class="wiz-client-banner-doc">${escCotSel(cotizacion.cliente_documento)}</span></span>
                            </span>
                        </span>
                    </div>
                    <div class="cot-card-body mb-1">
                        <div class="cot-facts">
                            <div class="cot-fact"><span class="cot-fact-label">Emitida</span><span class="cot-fact-val">${cotizacion.fecha_cotizacion}</span></div>
                            <div class="cot-fact"><span class="cot-fact-label">Vence</span><span class="cot-fact-val">${cotizacion.fecha_validez}</span></div>
                            <div class="cot-fact"><span class="cot-fact-label">Productos</span><span class="cot-fact-val">${nProd}</span></div>
                        </div>
                        <div class="cot-total-block">
                            <span class="cot-total-eyebrow">Total</span>
                            <span class="cotizacion-total">$${cotizacion.total}</span>
                            ${bs ? `<span class="cot-total-bs">${bs}</span>` : ''}
                        </div>
                    </div>
                    <div class="cotizacion-footer">
                        <button type="button" class="btn btn-sm btn-success seleccionar-cotizacion-btn">
                            <i class="ri-check-line"></i> Seleccionar Cotización
                        </button>
                    </div>
                </div>
            `;
            container.append(card);
        });
    }

    // Búsqueda de cotizaciones
    $('#buscarCotizacion').on('keyup', function () {
        const searchTerm = $(this).val().toLowerCase();

        if (searchTerm === '') {
            renderCotizaciones(cotizacionesDisponibles);
            return;
        }

        const filtradas = cotizacionesDisponibles.filter(function (cot) {
            return cot.cliente_nombre.toLowerCase().startsWith(searchTerm) ||
                cot.cliente_documento.toLowerCase().startsWith(searchTerm) ||
                cot.id.toString().startsWith(searchTerm);
        });

        renderCotizaciones(filtradas);
    });

    // Handler para seleccionar cotización — importar o conversión atómica
    $(document).on('click', '.seleccionar-cotizacion-btn', function () {
        const card = $(this).closest('.cotizacion-card');
        const cotizacionId = card.data('cotizacion-id');

        // Modo importar: hidratar paso 2 del wizard en lugar de convertir
        if (window.pedWizardImportMode) {
            window.pedWizardImportMode = false;
            $('#seleccionarCotizacionModal').modal('hide');
            setTimeout(function () {
                if (typeof window.pedHidratarDesde === 'function') {
                    window.pedHidratarDesde(cotizacionId);
                }
            }, 300);
            return;
        }

        // Abrir wizard de pedidos pre-hidratado con los datos de esta cotización
        $('#seleccionarCotizacionModal').modal('hide');
        setTimeout(function () {
            if (typeof window.pedAbrirDesdeCotizacion === 'function') {
                window.pedAbrirDesdeCotizacion(cotizacionId);
            }
        }, 300);
    });
</script>