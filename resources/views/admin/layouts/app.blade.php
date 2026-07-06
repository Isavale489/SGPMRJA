<!doctype html>
<html lang="en" data-layout="vertical" data-layout-style="detached" data-sidebar="light" data-topbar="dark"
    data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>

    <meta charset="utf-8" />
    <title>Manufacturas R.J Atlantico</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de Gestión de Producción — Manufacturas R.J Atlántico" name="description" />
    <meta content="SGPMRJA" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <!-- jsvectormap css -->
    <link href="{{ asset('assets/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />

    <!--Swiper slider css-->
    <link href="{{ asset('assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- DataTables css se carga en cada vista individual -->

    <!-- FOUC Prevention: Apply saved theme BEFORE layout.js reads sessionStorage -->
    <script>
        (function () {
            var savedTheme = localStorage.getItem('sgpmrja-theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-bs-theme', savedTheme);
                // Sync into sessionStorage so layout.js doesn't reset it
                var defaults = sessionStorage.getItem('defaultAttribute');
                if (defaults) {
                    try {
                        var parsed = JSON.parse(defaults);
                        parsed['data-bs-theme'] = savedTheme;
                        sessionStorage.setItem('defaultAttribute', JSON.stringify(parsed));
                    } catch (e) { }
                }
                sessionStorage.setItem('data-bs-theme', savedTheme);
            }

            // Sidebar colapsado: hidratar la preferencia guardada (solo escritorio).
            // En tablet/móvil la plantilla maneja el tamaño de forma responsiva.
            var savedSidebar = localStorage.getItem('sgpmrja-sidebar-size');
            if (savedSidebar && (savedSidebar === 'lg' || savedSidebar === 'sm') && window.innerWidth > 1025) {
                document.documentElement.setAttribute('data-sidebar-size', savedSidebar);
                var defaultsSb = sessionStorage.getItem('defaultAttribute');
                if (defaultsSb) {
                    try {
                        var parsedSb = JSON.parse(defaultsSb);
                        parsedSb['data-sidebar-size'] = savedSidebar;
                        sessionStorage.setItem('defaultAttribute', JSON.stringify(parsedSb));
                    } catch (e) { }
                }
                sessionStorage.setItem('data-sidebar-size', savedSidebar);
            }
        })();
    </script>
    <!-- Layout config Js -->
    <script src="{{ asset('assets/js/layout.js') }}"></script>
    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="{{ asset('assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Custom Css Personalizado — cache-busting por fecha de modificación del archivo -->
    <link href="{{ asset('assets/css/custom.css') }}?v={{ filemtime(public_path('assets/css/custom.css')) }}" rel="stylesheet" type="text/css" />

    <!-- Estilo para campos obligatorios -->
    <style>
        .form-label.required::after,
        label.required::after {
            content: " *";
            color: #dc3545;
            font-weight: bold;
        }

        .required-note {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .required-note span {
            color: #dc3545;
            font-weight: bold;
        }

        {{-- Dark mode de layout (navbar, sidebar, cards, tables, modals) — movido a public/assets/css/custom.css (sección DARK MODE — LAYOUT) --}}
        {{-- SweetAlert2 — movido a public/assets/css/custom.css (sección SWEETALERT2 ESTÁNDAR ATLÁNTICO) --}}
    </style>

    @stack('styles')
</head>

<body>
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>

    <!-- Toast: restaurar pantalla completa tras navegación (V-10) -->
    <div id="fs-restore-toast" role="alert" aria-live="assertive" aria-atomic="true"
         style="position:fixed;top:70px;right:20px;z-index:9999;min-width:290px;display:none;
                background:#fff;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,.15);
                border-left:4px solid #1e3c72;overflow:hidden;">
        <div class="d-flex align-items-center gap-3 p-3">
            <i class="bx bx-fullscreen text-primary" style="font-size:1.4rem;flex-shrink:0;"></i>
            <div class="flex-grow-1 lh-sm">
                <div class="fw-semibold" style="font-size:.83rem;color:#1e3c72;">Pantalla completa</div>
                <div class="text-muted" style="font-size:.76rem;">Estaba activa antes de navegar</div>
            </div>
            <button type="button" id="fs-restore-btn"
                    class="btn btn-sm btn-primary px-3" style="white-space:nowrap;font-size:.8rem;">
                Restaurar
            </button>
            <button type="button" id="fs-restore-dismiss"
                    class="btn-close" style="flex-shrink:0;" aria-label="Cerrar"></button>
        </div>
        <div id="fs-restore-progress"
             style="height:3px;width:100%;background:#1e3c72;transform-origin:left;"></div>
    </div>

    <!-- Begin page -->
    <div id="layout-wrapper">
        @include('admin.layouts.header')
        @include('admin.layouts.sidebar')
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')

                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            @include('admin.layouts.footer')
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    {{-- ── Tasa BCV global (la inyecta AppServiceProvider via View::composer('admin.*')) ── --}}
    <script>
        window.tasaBcv = @if(isset($tasaBcv) && $tasaBcv) {
            valor: {{ $tasaBcv->valor }},
            fecha: '{{ $tasaBcv->fecha_bcv->format('Y-m-d') }}',
            fuente: @json($tasaBcv->fuente ?? '')
        } @else null @endif;

        // Helper: convierte un monto en USD a string "Bs X.XXX,XX" (formato Venezuela).
        // Acepta una tasa específica (rateOverride) para respetar la tasa guardada en el
        // documento (cotización/pedido); si no se pasa, usa la tasa BCV vigente global.
        // Devuelve null si no hay tasa disponible — el caller decide qué mostrar.
        window.bsEquivalente = function (usd, rateOverride) {
            var rate = (rateOverride != null && Number(rateOverride) > 0)
                ? Number(rateOverride)
                : ((window.tasaBcv && window.tasaBcv.valor) ? Number(window.tasaBcv.valor) : null);
            if (!rate) return null;
            var bs = Number(usd || 0) * rate;
            return 'Bs ' + bs.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };

        // Helper: formatea una tasa de cambio como "Bs X.XXX,XXXX" (4 decimales).
        window.bsTasaFmt = function (rateOverride) {
            var rate = (rateOverride != null && Number(rateOverride) > 0)
                ? Number(rateOverride)
                : ((window.tasaBcv && window.tasaBcv.valor) ? Number(window.tasaBcv.valor) : null);
            if (!rate) return null;
            return 'Bs ' + rate.toLocaleString('es-VE', { minimumFractionDigits: 4, maximumFractionDigits: 4 });
        };

        // Rellena toda píldora BCV declarativa: cualquier elemento con [data-bcv-pill]
        // que contenga spans [data-bcv-fecha] y [data-bcv-val]. Permite mostrar la tasa
        // del día en headers de modales sin repetir el script por módulo.
        document.addEventListener('DOMContentLoaded', function () {
            var fecha = '', valor = null;
            if (window.tasaBcv && window.tasaBcv.valor) {
                valor = 'Bs. ' + Number(window.tasaBcv.valor)
                    .toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                var p = String(window.tasaBcv.fecha || '').split('-');
                fecha = (p.length === 3) ? (p[2] + '/' + p[1]) : '';
            }
            document.querySelectorAll('[data-bcv-pill]').forEach(function (pill) {
                var f = pill.querySelector('[data-bcv-fecha]');
                var v = pill.querySelector('[data-bcv-val]');
                if (f) f.textContent = fecha;
                if (v) v.textContent = valor || 'N/D';
            });
        });
    </script>

    <script>
        // Avatar de respaldo (silueta neutra) para chips de usuario cuando no hay
        // foto, el registro no tiene creador, o falla la carga (p. ej. ui-avatars bloqueado).
        window.AMS_AVATAR_FALLBACK = "data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPSc0MCcgaGVpZ2h0PSc0MCc+PHJlY3Qgd2lkdGg9JzQwJyBoZWlnaHQ9JzQwJyByeD0nMjAnIGZpbGw9JyNjYmQ1ZTEnLz48Y2lyY2xlIGN4PScyMCcgY3k9JzE2JyByPSc2JyBmaWxsPScjZWVmMmY3Jy8+PHBhdGggZD0nTTkgMzRjMS41LTcuNSAyMC41LTcuNSAyMiAweicgZmlsbD0nI2VlZjJmNycvPjwvc3ZnPg==";
    </script>

    <!-- JAVASCRIPT -->
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        // Solo un menú de acciones (⋮) abierto a la vez en las tablas.
        // Listener en FASE DE CAPTURA: corre antes que cualquier otro handler de la
        // página, así que cierra de forma fiable los demás menús de acciones abiertos
        // sin importar qué interfiera con el auto-cierre nativo de Bootstrap.
        document.addEventListener('click', function (e) {
            var clickedToggle = e.target.closest('[data-bs-toggle="dropdown"]');
            document.querySelectorAll('.dropdown-menu.actions-menu.show').forEach(function (menu) {
                var dd = menu.closest('.dropdown');
                var toggle = dd ? dd.querySelector('[data-bs-toggle="dropdown"]') : null;
                if (toggle && toggle !== clickedToggle) {
                    bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
                }
            });
        }, true);
    </script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
    <script src="{{ asset('assets/js/plugins.js') }}"></script>
    {{-- Charts: AG Charts se carga POR VISTA (dashboard y reportes) — ApexCharts y el
         init demo de Velzon (dashboard-ecommerce) se retiraron al estandarizar (2026-07-05) --}}

    <!-- datatables se cargan en cada vista individual -->

    <!-- Vector map-->
    <script src="{{ asset('assets/libs/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jsvectormap/maps/world-merc.js') }}"></script>

    <!--Swiper slider js-->
    <script src="{{ asset('assets/libs/swiper/swiper-bundle.min.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>

    <!-- SweetAlert2 — global para todos los módulos y para AtlanticoGuard -->
    <link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <!-- Validación lógica global de los filtros de exportación PDF (rango de fechas) -->
    <script src="{{ asset('assets/js/pdf-export-filtros.js') }}"></script>

    <!-- Notificaciones del header (campanita) -->
    @auth
        <script src="{{ asset('assets/js/pages/notifications.js') }}"></script>
    @endauth

    <!-- Alpine.js (necesario para x-data, x-show, x-init en perfil y otros) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>

    <!-- Theme Persistence & Icon Sync -->
    <script>
        (function () {
            // Sync toggle icon on page load
            function syncThemeIcon() {
                var html = document.documentElement;
                var icon = document.querySelector('.light-dark-mode i');
                if (!icon) return;
                if (html.getAttribute('data-bs-theme') === 'dark') {
                    icon.classList.remove('bx-moon');
                    icon.classList.add('bx-sun');
                } else {
                    icon.classList.remove('bx-sun');
                    icon.classList.add('bx-moon');
                }
            }

            // Run on DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', syncThemeIcon);
            } else {
                syncThemeIcon();
            }

            // Watch for theme changes (covers toggle clicks and any programmatic changes)
            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (m) {
                    if (m.attributeName === 'data-bs-theme') {
                        var theme = document.documentElement.getAttribute('data-bs-theme');
                        localStorage.setItem('sgpmrja-theme', theme);
                        syncThemeIcon();
                    }
                });
            });
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
        })();
    </script>

    <!-- Fullscreen Persistence (mismo patrón que el tema oscuro) -->
    <script>
        (function () {
            var FS_KEY = 'sgpmrja-fullscreen';
            // true cuando el usuario inició la salida del fullscreen (botón o Esc).
            // false cuando el browser lo fuerza por navegación — en ese caso no guardamos 'false'.
            var userExiting = false;

            function syncFullscreenIcon() {
                var btn = document.querySelector('[data-toggle="fullscreen"]');
                if (!btn) return;
                var icon = btn.querySelector('i');
                if (!icon) return;
                if (document.fullscreenElement || document.webkitFullscreenElement) {
                    icon.classList.remove('bx-fullscreen');
                    icon.classList.add('bx-exit-fullscreen');
                } else {
                    icon.classList.remove('bx-exit-fullscreen');
                    icon.classList.add('bx-fullscreen');
                }
            }

            function enterFullscreen() {
                var docEl = document.documentElement;
                if (docEl.requestFullscreen) {
                    docEl.requestFullscreen().catch(function () { });
                } else if (docEl.webkitRequestFullscreen) {
                    docEl.webkitRequestFullscreen();
                }
            }

            // Al ENTRAR fullscreen: siempre guardar 'true'.
            // Al SALIR: solo guardar 'false' si fue una salida intencional del usuario.
            // La salida por navegación no toca localStorage → 'true' persiste para la siguiente página.
            function onFsChange(isFS) {
                if (isFS) {
                    localStorage.setItem(FS_KEY, 'true');
                    userExiting = false;
                } else if (userExiting) {
                    localStorage.setItem(FS_KEY, 'false');
                    userExiting = false;
                }
                syncFullscreenIcon();
            }
            document.addEventListener('fullscreenchange', function () {
                onFsChange(!!document.fullscreenElement);
            });
            document.addEventListener('webkitfullscreenchange', function () {
                onFsChange(!!document.webkitFullscreenElement);
            });

            // Detectar salida INTENCIONAL del usuario:
            // 1. Tecla Escape mientras está en fullscreen
            document.addEventListener('keydown', function (e) {
                if ((e.key === 'Escape' || e.keyCode === 27) &&
                    (document.fullscreenElement || document.webkitFullscreenElement)) {
                    userExiting = true;
                }
            });

            // 2. Clic en el botón de fullscreen cuando ya está en fullscreen (= quiere salir)
            document.addEventListener('DOMContentLoaded', function () {
                var btn = document.querySelector('[data-toggle="fullscreen"]');
                if (btn) {
                    btn.addEventListener('click', function () {
                        if (document.fullscreenElement || document.webkitFullscreenElement) {
                            userExiting = true;
                        }
                    });
                }
            });

            // Al cargar la página: si el usuario estaba en fullscreen antes de navegar,
            // mostrar un toast con botón para restaurarlo (el browser exige gesto del usuario).
            function setupAutoRestore() {
                if (localStorage.getItem(FS_KEY) !== 'true') return;
                if (document.fullscreenElement || document.webkitFullscreenElement) return;

                var toast   = document.getElementById('fs-restore-toast');
                var btnOk   = document.getElementById('fs-restore-btn');
                var btnX    = document.getElementById('fs-restore-dismiss');
                if (!toast) return;

                toast.style.display = 'block';
                // Arrancar barra de progreso
                var progress = document.getElementById('fs-restore-progress');
                if (progress) progress.classList.add('running');

                // Auto-ocultar tras 8 s si el usuario no interactúa
                var autoHide = setTimeout(function () {
                    toast.style.display = 'none';
                }, 8000);

                btnOk.addEventListener('click', function () {
                    clearTimeout(autoHide);
                    toast.style.display = 'none';
                    enterFullscreen();
                }, { once: true });

                // Si el usuario cierra el toast con la X, interpreta que no quiere
                // restaurar → guardar 'false' para no volver a molestar.
                btnX.addEventListener('click', function () {
                    clearTimeout(autoHide);
                    toast.style.display = 'none';
                    localStorage.setItem(FS_KEY, 'false');
                }, { once: true });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () {
                    setupAutoRestore();
                    syncFullscreenIcon();
                });
            } else {
                setupAutoRestore();
                syncFullscreenIcon();
            }
        })();
    </script>

    <script>
        const lenguajeData = {
            emptyTable: "No hay datos disponibles",
            info: "Mostrando _START_–_END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0–0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            lengthMenu: "Mostrar _MENU_ registros",
            loadingRecords: "Cargando...",
            processing: "Procesando...",
            search: "Buscar:",
            zeroRecords: "No se encontraron resultados",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            },
            aria: {
                sortAscending: ": activar para ordenar la columna ascendente",
                sortDescending: ": activar para ordenar la columna descendente"
            }
        }
    </script>

    <!-- Script global para validación de campos -->
    <script>
        // ============================================================
        // FUNCIONES GLOBALES DE VALIDACIÓN — disponibles en todos los módulos
        // ============================================================

        /**
         * Valida la política de seguridad de contraseña.
         * Retorna null si es válida, o un mensaje de error con los requisitos faltantes.
         */
        function validarContrasena(valor) {
            if (valor.length === 0) return null;
            let errores = [];
            if (valor.length < 8)              errores.push('al menos 8 caracteres');
            if (!/[A-Z]/.test(valor))          errores.push('una letra mayúscula');
            if (!/[0-9]/.test(valor))          errores.push('un número');
            if (!/[^a-zA-Z0-9]/.test(valor))   errores.push('un carácter especial');
            if (errores.length === 0) return null;
            return 'La contraseña debe contener ' + errores.join(', ') + '.';
        }

        function marcarInvalido($campo, mensaje) {
            $campo.addClass('is-invalid').removeClass('is-valid');
            let $feedback = $campo.siblings('.invalid-feedback');
            if ($feedback.length === 0) {
                $feedback = $campo.parent().find('.invalid-feedback');
            }
            if ($feedback.length === 0) {
                $campo.after('<div class="invalid-feedback">' + mensaje + '</div>');
            } else {
                $feedback.text(mensaje).show();
            }
        }

        function marcarValido($campo) {
            $campo.removeClass('is-invalid').addClass('is-valid');
            $campo.siblings('.invalid-feedback').hide();
            $campo.parent().find('.invalid-feedback').hide();
        }

        function limpiarValidacion($campo) {
            $campo.removeClass('is-invalid is-valid');
            $campo.siblings('.invalid-feedback').hide();
            $campo.parent().find('.invalid-feedback').hide();
        }

        function validarCampoTexto($campo, minLength, mensaje) {
            let value = $campo.val().trim();
            if (value.length < minLength) {
                marcarInvalido($campo, mensaje);
            } else {
                marcarValido($campo);
            }
        }

        $(document).ready(function () {
            // ============================================
            // VALIDACIONES EN TIEMPO REAL (MIENTRAS ESCRIBE)
            // ============================================

            // Campos de NOMBRE/APELLIDO - Solo letras y espacios
            // #razon-social-field excluido — tiene su propio filtro permisivo (permite números)
            const camposNombre = [
                '#nombre-field',
                '#apellido-field',
                '#nombre-contacto-field',
                'input[name="nombre"]:not(#razon-social-field)',
                'input[name="apellido"]',
                'input[name="nombre_contacto"]'
            ];

            camposNombre.forEach(function (selector) {
                $(document).on('input', selector, function () {
                    this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '');
                });
            });

            // Razón Social — permite letras, números, puntos, comas, guiones y espacios
            $(document).on('input', '#razon-social-field, input[name="razon_social"]', function () {
                this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ0-9.,\-\s]/g, '');
            });

            // Campos de DOCUMENTO - Solo números (máximo 10 dígitos)
            const camposDocumento = [
                '#documento-number-field',
                '#documento-identidad-field',
                '#rif-number-field',
                '#ci-rif-number-field',
                'input[name="documento_identidad"]',
                'input[name="rif_numero"]'
            ];

            camposDocumento.forEach(function (selector) {
                $(document).on('input', selector, function () {
                    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
                });
            });

            // Campos de TELÉFONO - Formato 0424-1234567
            const camposTelefono = [
                '#telefono-field',
                '#telefono-contacto-field',
                'input[name="telefono"]',
                'input[name="telefono_contacto"]'
            ];

            camposTelefono.forEach(function (selector) {
                $(document).on('input', selector, function () {
                    let value = this.value.replace(/[^0-9]/g, '');
                    if (value.length > 4) {
                        value = value.slice(0, 4) + '-' + value.slice(4, 11);
                    }
                    this.value = value.slice(0, 12);
                });
            });

            // Campos de PRECIO/MONTO - Solo números y punto decimal
            const camposPrecio = [
                '#precio-field',
                '#precio_base-field',
                '#abono-field',
                '#total-field',
                'input[name="precio"]',
                'input[name="precio_base"]',
                'input[name="abono"]',
                'input[name="total"]',
                'input[name="costo_unitario"]'
            ];

            camposPrecio.forEach(function (selector) {
                $(document).on('input', selector, function () {
                    // Los type="number" los maneja el navegador nativamente — tocarlos resetea el cursor
                    if (this.type === 'number') return;
                    this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
                });
            });

            // Campos de CANTIDAD/STOCK - Solo números enteros
            const camposCantidad = [
                '#cantidad-field',
                '#stock_actual-field',
                '#stock_minimo-field',
                'input[name="cantidad"]',
                'input[name="stock_actual"]',
                'input[name="stock_minimo"]',
                'input[name="cantidad_producida"]',
                'input[name="cantidad_defectuosa"]'
            ];

            camposCantidad.forEach(function (selector) {
                $(document).on('input', selector, function () {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            });

            // Código prefijo de tipo producto - solo letras, mayúsculas automáticas, máx 5
            $(document).on('input', '#tipo-prefijo-field, input[name="prefijo"]', function () {
                this.value = this.value.replace(/[^a-zA-Z]/g, '').toUpperCase().slice(0, 5);
            });

            // ============================================
            // VALIDACIONES ONBLUR (AL SALIR DEL CAMPO)
            // ============================================

            // Validación de nombres (mínimo 2 caracteres)
            // Se excluye #razon-social-field — tiene su propio handler en clientes (min 3 chars)
            $(document).on('blur', '#nombre-field, input[name="nombre"]:not(#razon-social-field)', function () {
                validarCampoTexto($(this), 2, 'El nombre debe tener al menos 2 caracteres.');
            });

            // Validación de apellidos (mínimo 2 caracteres si no está vacío)
            $(document).on('blur', '#apellido-field, input[name="apellido"]', function () {
                let value = $(this).val().trim();
                if (value.length > 0 && value.length < 2) {
                    marcarInvalido($(this), 'El apellido debe tener al menos 2 caracteres.');
                } else {
                    marcarValido($(this));
                }
            });

            // Validación de documento (mínimo 6 dígitos).
            // NOTA: #documento-number-field (Clientes) se excluye a propósito: ese
            // módulo tiene su propia validación con rango "entre 6 y N" sobre
            // #documento-error; el handler global creaba un feedback duplicado.
            $(document).on('blur', '#documento-identidad-field, input[name="documento_identidad"]', function () {
                let value = $(this).val().trim();
                if (value.length < 6) {
                    marcarInvalido($(this), 'El documento debe tener al menos 6 dígitos.');
                } else {
                    marcarValido($(this));
                }
            });

            // Validación de teléfono (formato 0424-1234567)
            $(document).on('blur', '#telefono-field, input[name="telefono"]', function () {
                let value = $(this).val().trim();
                if (value.length > 0) {
                    let regex = /^[0-9]{4}-[0-9]{7}$/;
                    if (!regex.test(value)) {
                        marcarInvalido($(this), 'El teléfono debe tener el formato 0424-1234567.');
                    } else {
                        marcarValido($(this));
                    }
                } else {
                    limpiarValidacion($(this));
                }
            });

            // Validación de email
            $(document).on('blur', '#email-field, input[type="email"], input[name="email"]', function () {
                let value = $(this).val().trim();
                if (value.length > 0) {
                    let regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!regex.test(value)) {
                        marcarInvalido($(this), 'Ingrese un email válido.');
                    } else {
                        marcarValido($(this));
                    }
                } else {
                    limpiarValidacion($(this));
                }
            });

            // Validación de RIF (mínimo 9 dígitos)
            $(document).on('blur', '#rif-number-field, input[name="rif_numero"]', function () {
                let value = $(this).val().trim();
                if (value.length < 9) {
                    marcarInvalido($(this), 'El RIF debe tener al menos 9 dígitos.');
                } else {
                    marcarValido($(this));
                }
            });

            // Validación de precio_base (debe ser mayor a cero)
            $(document).on('blur', '#precio-base-field, input[name="precio_base"]', function () {
                let value = parseFloat($(this).val());
                if (isNaN(value) || value <= 0) {
                    marcarInvalido($(this), 'El precio base debe ser mayor a cero.');
                } else {
                    marcarValido($(this));
                }
            });

            // Validación de costo_unitario (debe ser mayor a cero)
            $(document).on('blur', '#field-costo_unitario, input[name="costo_unitario"]', function () {
                let value = parseFloat($(this).val());
                if (isNaN(value) || value <= 0) {
                    marcarInvalido($(this), 'El costo unitario debe ser mayor a cero.');
                } else {
                    marcarValido($(this));
                }
            });

            // Validación de contraseña (política de seguridad ERP)
            $(document).on('blur', 'input[name="password"]', function () {
                let value = $(this).val();
                if (value.length === 0) {
                    limpiarValidacion($(this));
                    return;
                }
                let error = validarContrasena(value);
                if (error) {
                    marcarInvalido($(this), error);
                } else {
                    marcarValido($(this));
                }
            });

            // Validación de confirmación de contraseña
            $(document).on('blur', 'input[name="password_confirmation"]', function () {
                let value = $(this).val();
                let password = $('input[name="password"]').val();
                if (value.length > 0) {
                    if (value !== password) {
                        marcarInvalido($(this), 'Las contraseñas no coinciden.');
                    } else {
                        marcarValido($(this));
                    }
                } else {
                    limpiarValidacion($(this));
                }
            });

            // Validación de cargo (mínimo 3 caracteres)
            $(document).on('blur', 'input[name="cargo"]', function () {
                let value = $(this).val().trim();
                if (value.length === 0) {
                    marcarInvalido($(this), 'El cargo es obligatorio.');
                } else if (value.length < 3) {
                    marcarInvalido($(this), 'El cargo debe tener al menos 3 caracteres.');
                } else {
                    marcarValido($(this));
                }
            });

            // Validación de código prefijo (solo letras, máx 5)
            $(document).on('blur', '#tipo-prefijo-field, input[name="prefijo"]', function () {
                let value = $(this).val().trim();
                if (value.length === 0) {
                    marcarInvalido($(this), 'El código prefijo es obligatorio.');
                } else if (!/^[a-zA-Z]+$/.test(value)) {
                    marcarInvalido($(this), 'El código prefijo solo puede contener letras.');
                } else {
                    marcarValido($(this));
                }
            });

            // Validación de fecha de nacimiento (mayor de 18 años)
            $(document).on('blur', 'input[name="fecha_nacimiento"]', function () {
                let value = $(this).val();
                if (value) {
                    let birthDate = new Date(value + 'T00:00:00');
                    let today = new Date();
                    let age = today.getFullYear() - birthDate.getFullYear();
                    let m = today.getMonth() - birthDate.getMonth();
                    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                        age--;
                    }
                    if (age < 18) {
                        marcarInvalido($(this), 'El empleado debe ser mayor de 18 años.');
                    } else {
                        marcarValido($(this));
                    }
                } else {
                    limpiarValidacion($(this));
                }
            });

            // Validación de fecha de ingreso (no puede ser futura)
            $(document).on('blur', 'input[name="fecha_ingreso"]', function () {
                let value = $(this).val();
                if (value) {
                    let selected = new Date(value + 'T00:00:00');
                    let today = new Date();
                    today.setHours(0, 0, 0, 0);
                    if (selected > today) {
                        marcarInvalido($(this), 'La fecha de ingreso no puede ser futura.');
                    } else {
                        marcarValido($(this));
                    }
                }
            });

            // Validación de fecha de producción (no puede ser futura)
            $(document).on('blur', '#edit_fecha_produccion, input[name="fecha_produccion"]', function () {
                let value = $(this).val();
                if (value) {
                    let selected = new Date(value + 'T00:00:00');
                    let today = new Date();
                    today.setHours(0, 0, 0, 0);
                    if (selected > today) {
                        marcarInvalido($(this), 'La fecha de producción no puede ser futura.');
                    } else {
                        marcarValido($(this));
                    }
                }
            });

            // Validación de cantidad producida (mínimo 1)
            $(document).on('blur', '#edit_cantidad_producida, input[name="cantidad_producida"]', function () {
                let value = parseFloat($(this).val());
                if (isNaN(value) || value < 1) {
                    marcarInvalido($(this), 'La cantidad producida debe ser al menos 1.');
                } else {
                    marcarValido($(this));
                }
            });

            // Validación de motivo (requerido, máx 500 caracteres)
            $(document).on('blur', '#field-motivo, textarea[name="motivo"]', function () {
                let value = $(this).val().trim();
                if (value.length === 0) {
                    marcarInvalido($(this), 'El motivo es obligatorio.');
                } else if (value.length > 500) {
                    marcarInvalido($(this), 'El motivo no puede superar 500 caracteres.');
                } else {
                    marcarValido($(this));
                }
            });

            // Limpiar validaciones al abrir cualquier modal
            $(document).on('show.bs.modal', '.modal', function () {
                $(this).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                $(this).find('.invalid-feedback').hide();
            });

            // ====================================================
            // FIX: Modales anidados en Bootstrap 5
            // Regla: los botones que abren modales hijos desde dentro
            // de un modal padre NO deben usar data-bs-toggle="modal".
            // En su lugar usan un click handler que llama .modal('show')
            // directamente. Así Bootstrap nunca ejecuta su lógica de
            // "cierra el modal abierto antes de abrir el nuevo".
            //
            // Estos handlers gestionan el z-index para que el hijo
            // (1075) aparezca encima del padre (1055) con su backdrop
            // (1070) entre ambos.
            // ====================================================
            $(document).on('show.bs.modal', '.modal', function () {
                var $nuevo    = $(this);
                var $abiertos = $('.modal.show').not(this);
                if ($abiertos.length > 0) {
                    $nuevo.css('z-index', 1075);
                    $nuevo.data('parentModals', $abiertos);
                }
            });

            $(document).on('shown.bs.modal', '.modal', function () {
                if ($(this).data('parentModals')) {
                    $('.modal-backdrop').last().css('z-index', 1070);
                }
            });

            $(document).on('hidden.bs.modal', '.modal', function () {
                var $cerrado = $(this);
                var $padres  = $cerrado.data('parentModals');
                if ($padres && $padres.length > 0) {
                    $cerrado.css('z-index', '');
                    $cerrado.removeData('parentModals');
                }
                // Bootstrap quita `modal-open` del body al cerrar CUALQUIER modal,
                // incluso si hay padres abiertos. Lo restauramos para mantener scroll-lock.
                if ($('.modal.show').length > 0) {
                    $('body').addClass('modal-open');
                }
            });
        });
    </script>
    {{-- Guard de cambios sin guardar — auto-detecta todos los .atlantico-modal con <form> --}}
    <script>
        const AtlanticoGuard = (function () {
            'use strict';

            function init() {
                $('.atlantico-modal').each(function () {
                    var $modal     = $(this);
                    var $form      = $modal.find('form').first();
                    if (!$form.length) return;

                    var isDirty    = false;
                    var forceClose = false;

                    // Resetear al abrir el modal
                    $modal.on('shown.bs.modal', function () {
                        isDirty = false;
                    });

                    // Cualquier interacción del usuario en un campo activa el flag
                    $modal.on('input.guard change.guard',
                        'input:not([type="hidden"]), select, textarea',
                        function () { isDirty = true; }
                    );

                    // Al enviar el form (submit via jQuery o nativo) → limpiar flag
                    // para que el cierre post-guardado no dispare el guard
                    $form.on('submit.guard', function () {
                        isDirty = false;
                    });

                    // Módulos que guardan via click (no form submit, ej. pedidos con e.preventDefault)
                    // usan data-guard-save-btn="btn-id" para indicar su botón de guardado
                    var saveBtnId = $modal.data('guardSaveBtn');
                    if (saveBtnId) {
                        $(document).on('click.guard-' + saveBtnId, '#' + saveBtnId, function () {
                            isDirty = false;
                        });
                    }

                    $modal.on('hide.bs.modal', function (e) {
                        if (forceClose) {
                            forceClose = false;
                            return;
                        }

                        // Solo activar si: el usuario hizo cambios Y hay un registro existente
                        // data-guard-id-field permite que cada modal declare su campo ID (default: id-field)
                        var idField = $modal.data('guardIdField') || 'id-field';
                        var editandoExistente = !!$form.find('#' + idField).val();
                        if (!isDirty || !editandoExistente) return;

                        e.preventDefault();

                        Swal.fire({
                            title: '¿Tienes cambios sin guardar?',
                            text: 'Si cierras ahora, perderás los cambios realizados.',
                            icon: 'warning',
                            showCancelButton: true,
                            showDenyButton:   true,
                            confirmButtonColor: '#1e3c72',
                            denyButtonColor:    '#e74c3c',
                            cancelButtonColor:  '#6c757d',
                            confirmButtonText:  'Guardar',
                            denyButtonText:     'Descartar',
                            cancelButtonText:   'Seguir editando',
                            reverseButtons: true
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                // Si el módulo declara un botón de guardado custom (ej. pedidos),
                                // lo clickeamos directamente en vez de hacer trigger('submit')
                                var customSave = $modal.data('guardSaveBtn');
                                if (customSave) {
                                    $('#' + customSave).trigger('click');
                                } else {
                                    $form.trigger('submit');
                                }
                            } else if (result.isDenied) {
                                forceClose = true;
                                $modal.modal('hide');
                            }
                        });
                    });
                });
            }

            return { init: init };
        })();

        $(document).ready(function () {
            AtlanticoGuard.init();
        });

        // ──────────────────────────────────────────────────────────────────
        // Sidebar colapsable — persistir la preferencia del usuario.
        // El botón hamburguesa alterna data-sidebar-size lg↔sm; guardamos el
        // estado en localStorage para que sobreviva al cerrar el navegador.
        // Solo persistimos en escritorio (>1025px); por debajo la plantilla
        // gestiona el tamaño de forma responsiva (overlay off-canvas).
        // ──────────────────────────────────────────────────────────────────
        (function () {
            var html = document.documentElement;
            new MutationObserver(function () {
                if (window.innerWidth <= 1025) return;
                var size = html.getAttribute('data-sidebar-size');
                if (size === 'lg' || size === 'sm') {
                    localStorage.setItem('sgpmrja-sidebar-size', size);
                }
            }).observe(html, { attributes: true, attributeFilter: ['data-sidebar-size'] });
        })();

        // ──────────────────────────────────────────────────────────────────
        // Sidebar colapsado (sm) — tooltip + click para expandir.
        // En modo icono NO queremos el flyout de submenú de Velzon; en su lugar:
        //   · hover  → tooltip flotante con el nombre de la opción
        //   · click  → si el item tiene submenú, expande el sidebar a lg y abre el grupo
        // El flyout en sí se neutraliza por CSS (ver sidebar.blade.php).
        // El tooltip se anexa a <body> con position:fixed para que NO lo recorte
        // el overflow del scroll del sidebar (simplebar) ni choque con los
        // pseudo-elementos (chevron) de los items.
        // ──────────────────────────────────────────────────────────────────
        (function () {
            var html = document.documentElement;
            var nav = document.getElementById('navbar-nav');
            if (!nav) return;

            function isCollapsed() {
                return html.getAttribute('data-sidebar-size') === 'sm';
            }

            var tip = document.createElement('div');
            tip.className = 'sb-collapsed-tooltip';
            document.body.appendChild(tip);

            function showTip(link) {
                if (!isCollapsed()) return;
                var span = link.querySelector('span');
                var text = span ? span.textContent.trim() : '';
                if (!text) return;
                var r = link.getBoundingClientRect();
                tip.textContent = text;
                tip.style.top = (r.top + r.height / 2) + 'px';
                tip.style.left = (r.right + 12) + 'px';
                tip.classList.add('show');
            }
            function hideTip() { tip.classList.remove('show'); }

            // NB: usamos selector de descendiente (no hijo directo). SimpleBar
            // reparenta los <li> dentro de .simplebar-content, así que
            // "#navbar-nav > li > a" matchearía 0. La clase .menu-link solo
            // existe en los items de primer nivel (los submenús usan .nav-link).
            nav.querySelectorAll('a.menu-link').forEach(function (link) {
                link.addEventListener('mouseenter', function () { showTip(link); });
                link.addEventListener('mouseleave', hideTip);

                // Click en un item con submenú estando colapsado → expandir a lg.
                // Fase de captura: el sidebar ya está en lg cuando Bootstrap
                // procesa el toggle del collapse, así que abre el grupo.
                link.addEventListener('click', function () {
                    if (isCollapsed() && link.getAttribute('data-bs-toggle') === 'collapse') {
                        hideTip();
                        html.setAttribute('data-sidebar-size', 'lg');
                        if (window.innerWidth > 1025) localStorage.setItem('sgpmrja-sidebar-size', 'lg');
                        var hb = document.querySelector('.hamburger-icon');
                        if (hb) hb.classList.add('open');
                    }
                }, true);
            });

            // El tooltip flotante debe seguir/ocultarse ante scroll o resize.
            window.addEventListener('scroll', hideTip, true);
            window.addEventListener('resize', hideTip);
        })();

        // ──────────────────────────────────────────────────────────────────
        // AtlanticoCopy — copiar al portapapeles desde los modales "Ver".
        // Inyecta un botón de copiar junto a cada valor real y avisa con un
        // toast. Global, cubre dos familias de modales:
        //   · Gestión General (clientes, empleados, insumos, proveedores,
        //     users): #viewModal con .cli-view-card-body span.fs-13
        //   · Gestión Operativa (cotizaciones, pedidos): #viewModal con
        //     .card-body span.fs-13; compras: #viewCompraModal con .cli-copyable
        // ──────────────────────────────────────────────────────────────────
        (function () {
            function copyText(text) {
                if (navigator.clipboard && window.isSecureContext) {
                    return navigator.clipboard.writeText(text);
                }
                return new Promise(function (resolve, reject) {
                    var ta = document.createElement('textarea');
                    ta.value = text;
                    ta.style.position = 'fixed';
                    ta.style.opacity = '0';
                    document.body.appendChild(ta);
                    ta.select();
                    try { document.execCommand('copy') ? resolve() : reject(); }
                    catch (e) { reject(e); }
                    finally { document.body.removeChild(ta); }
                });
            }

            var copyToast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false,
                timer: 1600, timerProgressBar: true,
                backdrop: false, heightAuto: false, scrollbarPadding: false
            });

            // Valores copiables dentro de un modal "Ver":
            //   · .cli-view-card-body span.fs-13 → estándar hero + cards (Gestión General)
            //   · .card-body span.fs-13          → modales "Ver" de cotizaciones y pedidos
            //   · .cli-copyable                  → marca explícita (compras: factura, doc/tel/email)
            var COPY_VAL_SELECTOR = '.cli-view-card-body span.fs-13, .card-body span.fs-13, .cli-copyable';

            function inyectarBotonesCopiar($modal) {
                $modal.find('.cli-copy-btn').remove();   // evita duplicados al reabrir
                $modal.find(COPY_VAL_SELECTOR).each(function () {
                    if ($(this).next('.cli-copy-btn').length) return;   // ya marcado en esta pasada
                    var val = $.trim($(this).text());
                    if (!val || val === '-' || val === '—') return;     // salta vacíos / placeholder
                    $('<button type="button" class="cli-copy-btn" title="Copiar">' +
                      '<i class="ri-file-copy-line"></i></button>').insertAfter(this);
                });
            }

            // Inyecta los botones al abrir cualquier modal "Ver" del estándar.
            // (#viewCompraModal usa otro id por convención del módulo de compras.)
            $(document).on('shown.bs.modal', '#viewModal, #viewCompraModal', function () {
                inyectarBotonesCopiar($(this));
            });

            // Click delegado: copia el valor hermano y avisa.
            $(document).on('click', '#viewModal .cli-copy-btn, #viewCompraModal .cli-copy-btn', function () {
                var $btn = $(this);
                var text = $.trim($btn.prev().text());
                if (!text) return;
                copyText(text).then(function () {
                    $btn.addClass('is-copied').find('i')
                        .removeClass('ri-file-copy-line').addClass('ri-check-line');
                    setTimeout(function () {
                        $btn.removeClass('is-copied').find('i')
                            .removeClass('ri-check-line').addClass('ri-file-copy-line');
                    }, 1200);
                    copyToast.fire({ icon: 'success', title: 'Copiado al portapapeles' });
                }).catch(function () {
                    copyToast.fire({ icon: 'error', title: 'No se pudo copiar' });
                });
            });
        })();

        // ──────────────────────────────────────────────────────────────────
        // AtlanticoSelect — realza los <select> a un dropdown Bootstrap cuyo menú
        // muestra ~4 ítems con scroll (el <select> nativo no permite limitar la
        // altura de su lista). Aplica a TODO el sistema con resguardos:
        //   · omite Select2, multiple, size>1, sin opciones y [data-no-afs]
        //   · el <select> nativo se conserva (oculto sr-only) como fuente de verdad
        //     → required/validación nativa y envío de formularios siguen OK
        //   · reconstruye el menú si las opciones cambian por JS (MutationObserver)
        //   · re-sincroniza la etiqueta al abrir modales (tras poblarse los valores)
        // El JS existente (lectura de .val() y evento change) sigue igual.
        // ──────────────────────────────────────────────────────────────────
        (function () {
            'use strict';

            function isEligible(sel) {
                var $s = $(sel);
                if ($s.data('afsEnhanced')) return false;
                if (sel.multiple || sel.size > 1) return false;             // listbox/multiple
                if ($s.is('[data-no-afs]')) return false;                   // opt-out explícito
                if ($s.hasClass('select2-hidden-accessible')) return false; // gestionado por Select2
                if ($s.closest('.afs-wrap').length) return false;           // ya realzado
                if (!sel.options || sel.options.length === 0) return false; // aún sin opciones
                return true;
            }

            function rebuildMenu(sel, $menu) {
                $menu.empty();
                $.each(sel.options, function (i, opt) {
                    var $btn = $('<button type="button" class="dropdown-item afs-option"></button>')
                        .attr('data-value', opt.value)
                        .text(opt.text);
                    // Honrar <option disabled>: se muestra atenuado y no clickeable.
                    if (opt.disabled) $btn.addClass('disabled').attr('aria-disabled', 'true');
                    $('<li></li>').append($btn).appendTo($menu);
                });
            }

            function syncToggle(sel, $wrap) {
                var opt = sel.options[sel.selectedIndex];
                $wrap.children('.afs-toggle')
                    .find('.afs-label').text(opt ? opt.text : '').end()
                    .prop('disabled', !!sel.disabled);   // refleja disabled (p.ej. cargo en cascada)
                $wrap.find('.afs-option').each(function () {
                    $(this).toggleClass('active', this.getAttribute('data-value') === sel.value);
                });
            }

            function enhance(sel) {
                if (!isEligible(sel)) return;
                var $select = $(sel);
                $select.data('afsEnhanced', true);

                var $wrap = $('<div class="dropdown afs-wrap"></div>');
                $select.before($wrap);
                $wrap.append($select);

                // Dentro de input-group: el wrap actúa como flex item de Bootstrap y
                // hereda las restricciones de ancho del <select> nativo (prefijos
                // V-/J-, 0424… usan .tipo-doc-select/.phone-prefix-select con max-width;
                // dept/cargo no las tienen y crecen para llenar). Así no se rompe el
                // layout flex ni la continuidad de bordes (el CSS .afs-wrap--ig remata).
                if ($select.closest('.input-group').length) {
                    $wrap.addClass('afs-wrap--ig');
                    var cs = window.getComputedStyle(sel);
                    if (cs.maxWidth && cs.maxWidth !== 'none') $wrap.css('max-width', cs.maxWidth);
                    if (cs.minWidth && cs.minWidth !== '0px') $wrap.css('min-width', cs.minWidth);
                }

                var $toggle = $('<button type="button" class="afs-toggle form-select" data-bs-toggle="dropdown" aria-expanded="false"><span class="afs-label"></span></button>');
                var $menu = $('<ul class="dropdown-menu afs-menu w-100"></ul>');
                rebuildMenu(sel, $menu);
                $wrap.append($toggle).append($menu);
                syncToggle(sel, $wrap);

                // Elegir opción → refleja en el select + dispara change
                $menu.on('click', '.afs-option', function () {
                    if (this.classList.contains('disabled')) return;   // opción deshabilitada
                    var val = this.getAttribute('data-value');
                    if (sel.value !== val) { $select.val(val).trigger('change'); }
                    syncToggle(sel, $wrap);
                });
                // Cambios del select (incl. programáticos con change) → re-sincroniza
                $select.on('change', function () { syncToggle(sel, $wrap); });
                // Al abrir, re-sincroniza por si el valor cambió sin disparar change
                $wrap.on('show.bs.dropdown', function () { syncToggle(sel, $wrap); });

                // Si el JS cambia las opciones (childList) o habilita/deshabilita el
                // select (attributes → disabled), reconstruye el menú y re-sincroniza.
                if (window.MutationObserver) {
                    new MutationObserver(function () {
                        rebuildMenu(sel, $menu);
                        syncToggle(sel, $wrap);
                    }).observe(sel, { childList: true, attributes: true, attributeFilter: ['disabled'] });
                }

                // Recién aquí se oculta el nativo (degradación elegante si algo falló).
                $select.addClass('afs-native');
            }

            function enhanceVisible(ctx) {
                $(ctx || document).find('select').each(function () {
                    // En el barrido general omite los no visibles (plantillas y
                    // selects de modales cerrados): se realzan al mostrarse el modal.
                    if (!$(this).is(':visible')) return;
                    enhance(this);
                });
            }

            // Filtros: al ready (sin parpadeo en barras visibles, aunque estén en
            // un panel colapsado).
            $(document).ready(function () {
                $('select.navy-filter-select').each(function () { enhance(this); });
            });

            // Resto del sistema: tras load, cuando Select2 (init en ready) ya es
            // detectable y se puede omitir.
            $(window).on('load', function () { enhanceVisible(document); });

            // Selects dentro de modales: realzar y re-sincronizar etiquetas DESPUÉS
            // de que el modal inicialice Select2 y pueble sus valores (defer 0ms).
            $(document).on('shown.bs.modal', function (e) {
                var modal = e.target;
                setTimeout(function () {
                    enhanceVisible(modal);
                    $(modal).find('select.afs-native').each(function () {
                        syncToggle(this, $(this).closest('.afs-wrap'));
                    });
                }, 0);
            });

            // API pública: realzar selects agregados dinámicamente por JS
            // (filas clonadas de <template>, repetidores, etc.). Pasa el nodo/
            // contexto recién insertado; solo realza los aún no realzados y visibles.
            window.AtlanticoSelect = { enhance: enhanceVisible, enhanceOne: enhance };
        })();
    </script>

    {{-- Catálogo geográfico (estado → municipios) desde BD, consumido por municipios-venezuela.js --}}
    <script>
        window.municipiosVenezuela = @json(($mapaMunicipiosVe ?? []) ?: null);
    </script>
    @stack('scripts')
</body>

</html>