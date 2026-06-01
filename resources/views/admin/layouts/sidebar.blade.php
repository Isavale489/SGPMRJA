<style>
    /* ══════════════════════════════════════════════════════════
       SIDEBAR — Estándar de color por sección (Light Mode)
       Refleja los mismos colores que cards y DataTables
    ══════════════════════════════════════════════════════════ */

    /* Base común: font-weight y border para todos los activos */
    .navbar-nav .nav-link.active {
        font-weight: 600;
        border-left: 3px solid;
    }

    /* ── GESTIÓN GENERAL — Navy #1e3c72 ── */
    .section-maestros .nav-link.active {
        background: rgba(30, 60, 114, 0.12) !important;
        color: #1e3c72 !important;
        border-left-color: #1e3c72 !important;
    }
    .section-maestros .nav-link.active i {
        color: #1e3c72 !important;
    }
    /* Header del grupo cuando hay subitem activo */
    .section-maestros.section-is-active > .menu-link {
        color: #1e3c72 !important;
        border-left: 3px solid #1e3c72 !important;
        background: rgba(30, 60, 114, 0.08) !important;
        font-weight: 600 !important;
    }
    .section-maestros.section-is-active > .menu-link i {
        color: #1e3c72 !important;
    }

    /* ── GESTIÓN OPERATIVA — Emerald #10b981 ── */
    .section-operativa .nav-link.active {
        background: rgba(16, 185, 129, 0.12) !important;
        color: #059669 !important;
        border-left-color: #10b981 !important;
    }
    .section-operativa .nav-link.active i {
        color: #10b981 !important;
    }
    .section-operativa.section-is-active > .menu-link {
        color: #059669 !important;
        border-left: 3px solid #10b981 !important;
        background: rgba(16, 185, 129, 0.08) !important;
        font-weight: 600 !important;
    }
    .section-operativa.section-is-active > .menu-link i {
        color: #10b981 !important;
    }

    /* ── CONSULTAS Y REPORTES — Sky #0ea5e9 ── */
    .section-reportes .nav-link.active {
        background: rgba(14, 165, 233, 0.10) !important;
        color: #0369a1 !important;
        border-left-color: #0ea5e9 !important;
    }
    .section-reportes .nav-link.active i {
        color: #0ea5e9 !important;
    }
    .section-reportes.section-is-active > .menu-link {
        color: #0369a1 !important;
        border-left: 3px solid #0ea5e9 !important;
        background: rgba(14, 165, 233, 0.08) !important;
        font-weight: 600 !important;
    }
    .section-reportes.section-is-active > .menu-link i {
        color: #0ea5e9 !important;
    }

    /* ── HOVER por sección (Light Mode) ──
       Sutil (opacidad ~0.06) para no competir con el activo (~0.12).
       Selectores reforzados para superar la regla genérica de Velzon. */
    .navbar-menu .navbar-nav .section-maestros .nav-link:hover {
        background: rgba(30, 60, 114, 0.06) !important;
        color: #1e3c72 !important;
    }
    .navbar-menu .navbar-nav .section-maestros .nav-link:hover i {
        color: #1e3c72 !important;
    }
    .navbar-menu .navbar-nav .section-maestros .nav-sm .nav-link:hover::before {
        background-color: #1e3c72 !important;
        opacity: 1;
    }

    .navbar-menu .navbar-nav .section-operativa .nav-link:hover {
        background: rgba(16, 185, 129, 0.06) !important;
        color: #059669 !important;
    }
    .navbar-menu .navbar-nav .section-operativa .nav-link:hover i {
        color: #10b981 !important;
    }
    .navbar-menu .navbar-nav .section-operativa .nav-sm .nav-link:hover::before {
        background-color: #10b981 !important;
        opacity: 1;
    }

    .navbar-menu .navbar-nav .section-reportes .nav-link:hover {
        background: rgba(14, 165, 233, 0.06) !important;
        color: #0369a1 !important;
    }
    .navbar-menu .navbar-nav .section-reportes .nav-link:hover i {
        color: #0ea5e9 !important;
    }
    .navbar-menu .navbar-nav .section-reportes .nav-sm .nav-link:hover::before {
        background-color: #0ea5e9 !important;
        opacity: 1;
    }

    /* ── Espaciado entre items y subitems ── */
    .menu-dropdown {
        margin-top: 8px;
        margin-bottom: 8px;
        padding-top: 4px;
        padding-bottom: 4px;
    }

    .menu-dropdown .nav-item {
        margin-bottom: 2px;
    }

    /* Ocultar la línea/guión de los subitems */
    .menu-dropdown .nav-link::before {
        display: none !important;
    }

    /* ── Sub-dropdown anidado (nivel 2: Productos→Atributos, RRHH→Empleados/Depto/Cargo) ── */
    /* Chevron del toggle anidado: a la derecha (Velzon lo pone a la izquierda por defecto) */
    .menu-dropdown .nav-sm .nav-link[data-bs-toggle="collapse"] {
        position: relative;
    }
    .menu-dropdown .nav-sm .nav-link[data-bs-toggle="collapse"]::after {
        left: auto !important;
        right: 14px !important;
        margin-right: 0 !important;
        font-size: 1rem;
    }
    /* Indentar los items del segundo nivel para mostrar jerarquía */
    .menu-dropdown .menu-dropdown .nav-sm .nav-link {
        padding-left: 2.6rem !important;
    }
    /* Margen del submenú anidado para que respire */
    .menu-dropdown .menu-dropdown {
        margin-top: 2px;
        margin-bottom: 4px;
    }

    /* ════════════════════════════════════════════════════════════
       Sub-grupo "Recursos Humanos" — header tipo chip + sub-maestros
       conectados por línea guía vertical (file-tree style)
       ════════════════════════════════════════════════════════════ */

    /* ── Header del subgrupo ──
       Estado por defecto (cuando NO estás en módulos de RRHH): neutro y sutil.
       Estado activo (.is-active): chip navy remarcado con borde y fondo. */
    .menu-dropdown .nav-item.menu-subtitle {
        margin: 14px 12px 6px;
        padding: 6px 10px 6px 12px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba(0, 0, 0, 0.42);
        background: transparent;
        border-left: 3px solid transparent;
        border-radius: 0 4px 4px 0;
        display: flex;
        align-items: center;
        gap: 8px;
        pointer-events: none;
        transition: color .15s ease, background .15s ease, border-color .15s ease;
    }
    .menu-dropdown .nav-item.menu-subtitle::before {
        content: "\f1ee"; /* ri-team-line */
        font-family: "remixicon" !important;
        font-style: normal;
        font-size: 0.95rem;
        line-height: 1;
        color: rgba(0, 0, 0, 0.38);
        font-weight: normal;
        letter-spacing: 0;
        transition: color .15s ease;
    }
    .menu-dropdown .nav-item.menu-subtitle > span {
        flex: 1;
    }

    /* ── Estado activo: píldora con gradiente navy (mismo del proyecto) ──
       Se diferencia a propósito del patrón "barra lateral + fondo translúcido"
       que usan los items normales activos. */
    .menu-dropdown .nav-item.menu-subtitle.is-active {
        color: #fff;
        font-weight: 700;
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border-left-color: transparent;
        border-radius: 999px;
        padding: 6px 14px 6px 14px;
        box-shadow: 0 2px 6px rgba(30, 60, 114, 0.25);
    }
    .menu-dropdown .nav-item.menu-subtitle.is-active::before {
        color: #fff;
    }

    /* ── Dark mode ── */
    [data-bs-theme="dark"] .menu-dropdown .nav-item.menu-subtitle {
        color: rgba(255, 255, 255, 0.42);
    }
    [data-bs-theme="dark"] .menu-dropdown .nav-item.menu-subtitle::before {
        color: rgba(255, 255, 255, 0.38);
    }
    [data-bs-theme="dark"] .menu-dropdown .nav-item.menu-subtitle.is-active {
        color: #fff;
        background: linear-gradient(135deg, #2a5298 0%, #3b6cb5 100%);
        box-shadow: 0 2px 8px rgba(147, 197, 253, 0.18);
    }
    [data-bs-theme="dark"] .menu-dropdown .nav-item.menu-subtitle.is-active::before {
        color: #fff;
    }

    /* ── Sub-maestros (Departamentos, Cargos) — hijos de Empleados ──
       Se les agrega clase .menu-subitem-child en el blade.
       Visualmente: indentación + línea guía vertical que conecta con
       Empleados arriba (estilo file-tree de IDE moderno).
    */
    .menu-dropdown .nav-item.menu-subitem-child {
        position: relative;
        margin-left: 18px;
    }
    .menu-dropdown .nav-item.menu-subitem-child > .nav-link {
        font-size: 0.86rem;
        padding-left: 20px !important;
        position: relative;
    }
    /* Línea vertical (guide) que conecta padre→hijos.
       Items intermedios: la línea atraviesa de top a bottom.
       Último ítem: la línea solo llega hasta el centro (forma en L). */
    .menu-dropdown .nav-item.menu-subitem-child::before {
        content: "";
        position: absolute;
        left: 8px;
        top: -2px;
        bottom: 0;
        width: 1.5px;
        background: rgba(30, 60, 114, 0.25);
    }
    .menu-dropdown .nav-item.menu-subitem-child:last-child::before {
        bottom: 50%;
    }
    /* Conector horizontal (de la línea vertical hacia el ítem) */
    .menu-dropdown .nav-item.menu-subitem-child::after {
        content: "";
        position: absolute;
        left: 8px;
        top: 50%;
        width: 10px;
        height: 1.5px;
        background: rgba(30, 60, 114, 0.25);
    }
    /* La línea guía mantiene su color neutro incluso cuando el sub-item está activo,
       para que solo destaque el item seleccionado y no se vea recargado. */
    [data-bs-theme="dark"] .menu-dropdown .nav-item.menu-subitem-child::before,
    [data-bs-theme="dark"] .menu-dropdown .nav-item.menu-subitem-child::after {
        background: rgba(147, 197, 253, 0.25);
    }
</style>
<div class="app-menu navbar-menu">
    <div class="navbar-brand-box">
        === App Menu Dark Logo-->
        <a href="{{ route('dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ asset('atlantico-logo-wide.png') }}" alt="" height="32">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('atlantico-logo-wide.png') }}" alt=""
                    style="height: auto; width: 100%; max-width: 180px;">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ route('dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ asset('atlantico-logo-wide.png') }}" alt="" height="32">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('atlantico-logo-wide.png') }}" alt=""
                    style="height: auto; width: 100%; max-width: 180px;">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span data-key="t-menu">Menu</span></li>

                {{-- ================================== --}}
                {{-- 1. INICIO (Dashboard) --}}
                {{-- ================================== --}}
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Inicio</span>
                    </a>
                </li>

                @auth
                    @if (Auth::user()->hasRole(['Administrador', 'Supervisor']))

                        {{-- ================================== --}}
                        {{-- 2. MAESTROS --}}
                        {{-- ================================== --}}
                        <li class="nav-item section-maestros {{ request()->is('clientes*', 'productos*', 'atributos*', 'proveedores*', 'insumos*', 'empleados*', 'departamentos*', 'cargos*') ? 'section-is-active' : '' }}">
                            <a class="nav-link menu-link" href="#sidebarMaestros" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->is('clientes*') || request()->is('productos*') || request()->is('atributos*') || request()->is('proveedores*') || request()->is('insumos*') || request()->is('empleados*') || request()->is('departamentos*') || request()->is('cargos*') ? 'true' : 'false' }}"
                                aria-controls="sidebarMaestros">
                                <i class="ri-database-2-line"></i> <span data-key="t-maestros">Gestión General</span>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->is('clientes*') || request()->is('productos*') || request()->is('atributos*') || request()->is('proveedores*') || request()->is('insumos*') || request()->is('empleados*') || request()->is('departamentos*') || request()->is('cargos*') ? 'show' : '' }}"
                                id="sidebarMaestros">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ url('clientes') }}"
                                            class="nav-link {{ request()->is('clientes*') ? 'active' : '' }}">
                                            <i class="ri-user-star-line me-1"></i> Clientes
                                        </a>
                                    </li>

                                    {{-- Productos con sub-dropdown (Atributos) --}}
                                    <li class="nav-item">
                                        <a href="#sidebarProductos" data-bs-toggle="collapse" role="button"
                                            class="nav-link {{ request()->is('productos*', 'atributos*') ? 'active' : 'collapsed' }}"
                                            aria-expanded="{{ request()->is('productos*', 'atributos*') ? 'true' : 'false' }}"
                                            aria-controls="sidebarProductos">
                                            <i class="ri-t-shirt-line me-1"></i> Productos
                                        </a>
                                        <div class="collapse menu-dropdown {{ request()->is('productos*', 'atributos*') ? 'show' : '' }}"
                                            id="sidebarProductos">
                                            <ul class="nav nav-sm flex-column">
                                                <li class="nav-item">
                                                    <a href="{{ url('productos') }}"
                                                        class="nav-link {{ request()->is('productos*') ? 'active' : '' }}">
                                                        <i class="ri-list-check-2 me-1"></i> Catálogo
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('atributos') }}"
                                                        class="nav-link {{ request()->is('atributos*') ? 'active' : '' }}">
                                                        <i class="ri-list-settings-line me-1"></i> Atributos
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ url('proveedores') }}"
                                            class="nav-link {{ request()->is('proveedores*') ? 'active' : '' }}">
                                            <i class="ri-truck-line me-1"></i> Proveedores
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('insumos') }}"
                                            class="nav-link {{ request()->is('insumos*') ? 'active' : '' }}">
                                            <i class="ri-archive-line me-1"></i> Insumos
                                        </a>
                                    </li>

                                    {{-- Empleados con sub-dropdown (Departamentos, Cargos) --}}
                                    <li class="nav-item">
                                        <a href="#sidebarEmpleados" data-bs-toggle="collapse" role="button"
                                            class="nav-link {{ request()->is('empleados*', 'departamentos*', 'cargos*') ? 'active' : 'collapsed' }}"
                                            aria-expanded="{{ request()->is('empleados*', 'departamentos*', 'cargos*') ? 'true' : 'false' }}"
                                            aria-controls="sidebarEmpleados">
                                            <i class="ri-team-line me-1"></i> Recursos Humanos
                                        </a>
                                        <div class="collapse menu-dropdown {{ request()->is('empleados*', 'departamentos*', 'cargos*') ? 'show' : '' }}"
                                            id="sidebarEmpleados">
                                            <ul class="nav nav-sm flex-column">
                                                <li class="nav-item">
                                                    <a href="{{ url('empleados') }}"
                                                        class="nav-link {{ request()->is('empleados*') ? 'active' : '' }}">
                                                        <i class="ri-user-settings-line me-1"></i> Empleados
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('departamentos') }}"
                                                        class="nav-link {{ request()->is('departamentos*') ? 'active' : '' }}">
                                                        <i class="ri-building-line me-1"></i> Departamentos
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('cargos') }}"
                                                        class="nav-link {{ request()->is('cargos*') ? 'active' : '' }}">
                                                        <i class="ri-briefcase-line me-1"></i> Cargos
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        {{-- ================================== --}}
                        {{-- 3. TRANSACCIONES --}}
                        {{-- ================================== --}}
                        <li class="nav-item section-operativa {{ request()->is('cotizaciones*', 'pedidos*', 'ordenes*', 'calidad*', 'inventario*', 'garantias*') ? 'section-is-active' : '' }}">
                            <a class="nav-link menu-link" href="#sidebarTransacciones" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->is('cotizaciones*') || request()->is('pedidos*') || request()->is('ordenes*') || request()->is('calidad*') || request()->is('inventario*') || request()->is('garantias*') ? 'true' : 'false' }}"
                                aria-controls="sidebarTransacciones">
                                <i class="ri-exchange-funds-line"></i> <span data-key="t-transacciones">Gestión Operativa</span>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->is('cotizaciones*') || request()->is('pedidos*') || request()->is('ordenes*') || request()->is('calidad*') || request()->is('inventario*') || request()->is('garantias*') ? 'show' : '' }}"
                                id="sidebarTransacciones">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ url('cotizaciones') }}"
                                            class="nav-link {{ request()->is('cotizaciones*') ? 'active' : '' }}">
                                            <i class="ri-file-list-3-line me-1"></i> Cotizaciones
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('pedidos') }}"
                                            class="nav-link {{ request()->is('pedidos*') ? 'active' : '' }}">
                                            <i class="ri-shopping-cart-line me-1"></i> Pedidos
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('ordenes.index') }}"
                                            class="nav-link {{ request()->is('ordenes*') ? 'active' : '' }}">
                                            <i class="ri-calendar-check-line me-1"></i> Orden de Producción
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        {{-- TODO: Crear ruta y controlador para Control de Calidad --}}
                                        <a href="#"
                                            class="nav-link {{ request()->is('calidad*') ? 'active' : '' }}">
                                            <i class="ri-shield-check-line me-1"></i> Control de Calidad
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('inventario.movimientos.index') }}"
                                            class="nav-link {{ request()->is('inventario/movimientos*') ? 'active' : '' }}">
                                            <i class="ri-arrow-left-right-line me-1"></i> Movimiento de Inventario
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        {{-- TODO: Crear ruta y controlador para Garantías --}}
                                        <a href="#"
                                            class="nav-link {{ request()->is('garantias*') ? 'active' : '' }}">
                                            <i class="ri-shield-star-line me-1"></i> Garantías
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        {{-- ================================== --}}
                        {{-- 4. CONSULTAS Y REPORTES --}}
                        {{-- ================================== --}}
                        <li class="nav-item section-reportes {{ request()->is('reportes*') ? 'section-is-active' : '' }}">
                            <a class="nav-link menu-link" href="#sidebarReportes" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->is('reportes*') ? 'true' : 'false' }}"
                                aria-controls="sidebarReportes">
                                <i class="ri-bar-chart-box-line"></i> <span data-key="t-reportes">Consultas y Reportes</span>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->is('reportes*') ? 'show' : '' }}"
                                id="sidebarReportes">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('reportes.produccion') }}"
                                            class="nav-link {{ request()->routeIs('reportes.produccion') ? 'active' : '' }}">
                                            <i class="ri-building-2-line me-1"></i> Producción
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reportes.eficiencia') }}"
                                            class="nav-link {{ request()->routeIs('reportes.eficiencia') ? 'active' : '' }}">
                                            <i class="ri-speed-line me-1"></i> Eficiencia
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reportes.insumos') }}"
                                            class="nav-link {{ request()->routeIs('reportes.insumos') ? 'active' : '' }}">
                                            <i class="ri-stack-line me-1"></i> Consumo de Insumos
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reportes.empleados') }}"
                                            class="nav-link {{ request()->routeIs('reportes.empleados') ? 'active' : '' }}">
                                            <i class="ri-team-line me-1"></i> Rendimiento de Empleados
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        {{-- TODO: Crear vista de Reportes Generales unificada --}}
                                        <a href="#"
                                            class="nav-link">
                                            <i class="ri-file-chart-line me-1"></i> Reportes Generales
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                    @endif


                @endauth
            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Sidebar acordeón: solo un submenú abierto a la vez
        document.querySelectorAll('.nav-link.menu-link[data-bs-toggle="collapse"]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                var targetId = link.getAttribute('href') || link.getAttribute('data-bs-target');
                if (targetId && targetId.startsWith('#')) {
                    // Cierra otros submenús abiertos
                    document.querySelectorAll('.menu-dropdown.collapse.show').forEach(function (openMenu) {
                        if (openMenu.id !== targetId.replace('#', '')) {
                            var bsCollapse = bootstrap.Collapse.getOrCreateInstance(openMenu);
                            bsCollapse.hide();
                        }
                    });
                }
            });
        });
        // Mantener smooth scroll para submenús
        function smoothScrollTo(container, target, duration = 600) {
            var containerTop = container.getBoundingClientRect().top;
            var targetTop = target.getBoundingClientRect().top;
            var scrollTop = container.scrollTop;
            var offset = targetTop - containerTop - (container.clientHeight / 2) + (target.clientHeight / 2);
            var start = scrollTop;
            var change = offset;
            var startTime = performance.now();
            function animateScroll(currentTime) {
                var elapsed = currentTime - startTime;
                var progress = Math.min(elapsed / duration, 1);
                container.scrollTop = start + change * progress;
                if (progress < 1) {
                    requestAnimationFrame(animateScroll);
                }
            }
            requestAnimationFrame(animateScroll);
        }
        document.querySelectorAll('.nav-link.menu-link[data-bs-toggle="collapse"]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                var targetId = link.getAttribute('href') || link.getAttribute('data-bs-target');
                if (targetId && targetId.startsWith('#')) {
                    setTimeout(function () {
                        var submenu = document.querySelector(targetId);
                        var sidebarContainer = document.querySelector('.app-menu .container-fluid');
                        if (submenu && submenu.classList.contains('show') && sidebarContainer) {
                            smoothScrollTo(sidebarContainer, submenu, 600);
                        }
                    }, 350);
                }
            });
        });
    });
</script>