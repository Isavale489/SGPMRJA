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

    // Renderizar cotizaciones como cards
    function renderCotizaciones(cotizaciones) {
        const container = $('#cotizaciones-container');
        container.empty();

        cotizaciones.forEach(function (cotizacion) {
            const card = `
                <div class="cotizacion-card" data-cotizacion-id="${cotizacion.id}">
                    <div class="cotizacion-header">
                        <span class="cotizacion-numero">
                            <i class="ri-file-text-line"></i> Cotización #${cotizacion.id}
                        </span>
                        <span class="cotizacion-total">$${cotizacion.total}</span>
                    </div>
                    <div class="cotizacion-info">
                        <div class="cotizacion-info-item">
                            <i class="ri-user-line"></i>
                            <span>${cotizacion.cliente_nombre}</span>
                        </div>
                        <div class="cotizacion-info-item">
                            <i class="ri-calendar-line"></i>
                            <span>${cotizacion.fecha_cotizacion}</span>
                        </div>
                        <div class="cotizacion-info-item">
                            <i class="ri-bank-card-line"></i>
                            <span>${cotizacion.cliente_documento}</span>
                        </div>
                        <div class="cotizacion-info-item">
                            <i class="ri-shopping-bag-line"></i>
                            <span>${cotizacion.cantidad_productos} producto(s)</span>
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
            return cot.cliente_nombre.toLowerCase().includes(searchTerm) ||
                cot.cliente_documento.toLowerCase().includes(searchTerm) ||
                cot.id.toString().includes(searchTerm);
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