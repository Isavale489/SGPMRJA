<!doctype html>
<html lang="en" data-layout="vertical" data-layout-style="detached" data-sidebar="light" data-topbar="dark"
    data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>

    <meta charset="utf-8" />
    <title>Manufacturas R.J Atlantico</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <!-- jsvectormap css -->
    <link href="{{ asset('assets/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />

    <!--Swiper slider css-->
    <link href="{{ asset('assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- DataTables css se carga en cada vista individual -->

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
    </style>

    @stack('styles')
</head>

<body>
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
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

    <!-- JAVASCRIPT -->
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
    <script src="{{ asset('assets/js/plugins.js') }}"></script>
    <!-- apexcharts -->
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- datatables se cargan en cada vista individual -->

    <!-- Vector map-->
    <script src="{{ asset('assets/libs/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jsvectormap/maps/world-merc.js') }}"></script>

    <!--Swiper slider js-->
    <script src="{{ asset('assets/libs/swiper/swiper-bundle.min.js') }}"></script>

    <!-- Dashboard init -->
    <script src="{{ asset('assets/js/pages/dashboard-ecommerce.init.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script>
        const lenguajeData = {
            emptyTable: "No hay datos disponibles",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            lengthMenu: "Mostrar _MENU_ registros",
            loadingRecords: "Cargando...",
            processing: "Procesando...",
            search: "Buscar:",
            zeroRecords: "No se encontraron coincidencias",
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
        $(document).ready(function() {
            // ============================================
            // VALIDACIONES EN TIEMPO REAL (MIENTRAS ESCRIBE)
            // ============================================
            
            // Campos de NOMBRE/APELLIDO - Solo letras y espacios
            const camposNombre = [
                '#nombre-field',
                '#apellido-field',
                '#razon-social-field',
                '#nombre-contacto-field',
                'input[name="nombre"]',
                'input[name="apellido"]',
                'input[name="razon_social"]',
                'input[name="nombre_contacto"]'
            ];
            
            camposNombre.forEach(function(selector) {
                $(document).on('input', selector, function() {
                    this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '');
                });
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
            
            camposDocumento.forEach(function(selector) {
                $(document).on('input', selector, function() {
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
            
            camposTelefono.forEach(function(selector) {
                $(document).on('input', selector, function() {
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
                'input[name="total"]'
            ];
            
            camposPrecio.forEach(function(selector) {
                $(document).on('input', selector, function() {
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
                'input[name="stock_minimo"]'
            ];
            
            camposCantidad.forEach(function(selector) {
                $(document).on('input', selector, function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            });
            
            // ============================================
            // VALIDACIONES ONBLUR (AL SALIR DEL CAMPO)
            // ============================================
            
            // Validación de nombres (mínimo 2 caracteres)
            $(document).on('blur', '#nombre-field, input[name="nombre"]', function() {
                validarCampoTexto($(this), 2, 'El nombre debe tener al menos 2 caracteres.');
            });
            
            // Validación de apellidos (mínimo 2 caracteres si no está vacío)
            $(document).on('blur', '#apellido-field, input[name="apellido"]', function() {
                let value = $(this).val().trim();
                if (value.length > 0 && value.length < 2) {
                    marcarInvalido($(this), 'El apellido debe tener al menos 2 caracteres.');
                } else {
                    marcarValido($(this));
                }
            });
            
            // Validación de documento (mínimo 6 dígitos)
            $(document).on('blur', '#documento-number-field, #documento-identidad-field, input[name="documento_identidad"]', function() {
                let value = $(this).val().trim();
                if (value.length < 6) {
                    marcarInvalido($(this), 'El documento debe tener al menos 6 dígitos.');
                } else {
                    marcarValido($(this));
                }
            });
            
            // Validación de teléfono (formato 0424-1234567)
            $(document).on('blur', '#telefono-field, input[name="telefono"]', function() {
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
            $(document).on('blur', '#email-field, input[type="email"], input[name="email"]', function() {
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
            $(document).on('blur', '#rif-number-field, input[name="rif_numero"]', function() {
                let value = $(this).val().trim();
                if (value.length < 9) {
                    marcarInvalido($(this), 'El RIF debe tener al menos 9 dígitos.');
                } else {
                    marcarValido($(this));
                }
            });
            
            // ============================================
            // FUNCIONES AUXILIARES DE VALIDACIÓN
            // ============================================
            
            function validarCampoTexto($campo, minLength, mensaje) {
                let value = $campo.val().trim();
                if (value.length < minLength) {
                    marcarInvalido($campo, mensaje);
                } else {
                    marcarValido($campo);
                }
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
            
            // Limpiar validaciones al abrir cualquier modal
            $(document).on('show.bs.modal', '.modal', function() {
                $(this).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                $(this).find('.invalid-feedback').hide();
            });
        });
    </script>
    @stack('scripts')
</body>

</html>