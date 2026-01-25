<style>
    /* Estilo para item activo (Sombreado azul original) */
    .navbar-nav .nav-link.active {
        background: rgba(59, 130, 246, 0.15) !important;
        color: #3b82f6 !important;
        border-left: 3px solid #3b82f6;
        font-weight: 600;
    }

    .navbar-nav .nav-link.active i {
        color: #3b82f6 !important;
    }

    /* Separación entre items y subitems */
    .menu-dropdown {
        margin-top: 8px;
        margin-bottom: 8px;
        padding-top: 4px;
        padding-bottom: 4px;
    }

    /* Subitems con el mismo estilo de sombreado azul */
    .menu-dropdown .nav-link.active {
        background: rgba(59, 130, 246, 0.15) !important;
        color: #3b82f6 !important;
        border-left: 3px solid #3b82f6;
        font-weight: 600;
    }

    .menu-dropdown .nav-link.active i {
        color: #3b82f6 !important;
    }

    /* Espaciado entre subitems */
    .menu-dropdown .nav-item {
        margin-bottom: 2px;
    }

    /* Ocultar la línea/guión de los subitems */
    .menu-dropdown .nav-link::before {
        display: none !important;
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
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        <i class="ri-home-4-line"></i> <span data-key="t-dashboards">Inicio</span>
                    </a>
                </li>

                @auth
                    @if (Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#sidebarPersonas" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->is('users*') || request()->is('clientes*') || request()->is('empleados*') || request()->is('proveedores*') ? 'true' : 'false' }}"
                                aria-controls="sidebarPersonas">
                                <i class="ri-group-line"></i> <span data-key="t-personas">Gestión de Personas</span>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->is('users*') || request()->is('clientes*') || request()->is('empleados*') || request()->is('proveedores*') ? 'show' : '' }}"
                                id="sidebarPersonas">
                                <ul class="nav nav-sm flex-column">

                                    <li class="nav-item">
                                        <a href="{{ url('clientes') }}"
                                            class="nav-link {{ request()->is('clientes*') ? 'active' : '' }}"
                                            data-key="t-clientes">
                                            <i class="ri-user-add-line me-1"></i> Clientes
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('empleados') }}"
                                            class="nav-link {{ request()->is('empleados*') ? 'active' : '' }}"
                                            data-key="t-empleados">
                                            <i class="ri-user-settings-line me-1"></i> Empleados
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('proveedores') }}"
                                            class="nav-link {{ request()->is('proveedores*') ? 'active' : '' }}"
                                            data-key="t-proveedores">
                                            <i class="ri-user-2-line me-1"></i> Proveedores
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif
                @endauth
                @auth
                    @if (Auth::user()->hasRole(['Administrador', 'Supervisor']))
                        {{-- Menú desplegable: Ventas --}}
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#sidebarVentas" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->is('cotizaciones*') || request()->is('pedidos*') ? 'true' : 'false' }}"
                                aria-controls="sidebarVentas">
                                <i class="ri-clipboard-line"></i> <span data-key="t-ventas">Gestión de Pedidos</span>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->is('cotizaciones*') || request()->is('pedidos*') ? 'show' : '' }}"
                                id="sidebarVentas">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ url('cotizaciones') }}"
                                            class="nav-link {{ request()->is('cotizaciones*') ? 'active' : '' }}"
                                            data-key="t-cotizaciones">
                                            <i class="ri-file-list-3-line me-1"></i> Cotizaciones
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('pedidos') }}"
                                            class="nav-link {{ request()->is('pedidos*') ? 'active' : '' }}"
                                            data-key="t-pedidos">
                                            <i class="ri-shopping-cart-line me-1"></i> Pedidos
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        {{-- Menú desplegable: Catálogo --}}
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#sidebarCatalogo" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->is('productos*') ? 'true' : 'false' }}"
                                aria-controls="sidebarCatalogo">
                                <i class="ri-store-2-line"></i> <span data-key="t-catalogo">Catálogo</span>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->is('productos*') ? 'show' : '' }}"
                                id="sidebarCatalogo">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ url('productos') }}"
                                            class="nav-link {{ request()->is('productos*') ? 'active' : '' }}"
                                            data-key="t-productos">
                                            <i class="ri-t-shirt-line me-1"></i> Productos
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif
                    @if (Auth::user()->hasRole(['Administrador', 'Supervisor']))
                        {{-- Menú desplegable: Producción e Inventario --}}
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#sidebarProduccion" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->is('ordenes*') || request()->is('insumos*') || request()->is('inventario*') ? 'true' : 'false' }}"
                                aria-controls="sidebarProduccion">
                                <i class="ri-building-2-line"></i> <span data-key="t-produccion">Producción e Inventario</span>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->is('ordenes*') || request()->is('insumos*') || request()->is('inventario*') ? 'show' : '' }}"
                                id="sidebarProduccion">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('ordenes.index') }}"
                                            class="nav-link {{ request()->is('ordenes*') ? 'active' : '' }}"
                                            data-key="t-ordenes">
                                            <i class="ri-calendar-check-line me-1"></i> Órdenes de Producción
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('insumos.index') }}"
                                            class="nav-link {{ request()->is('insumos*') ? 'active' : '' }}"
                                            data-key="t-insumos">
                                            <i class="ri-stack-line me-1"></i> Insumos
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('inventario.movimientos.index') }}"
                                            class="nav-link {{ request()->is('inventario*') ? 'active' : '' }}"
                                            data-key="t-inventario">
                                            <i class="ri-archive-line me-1"></i> Movimientos de Inventario
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif
                    @if (Auth::user()->hasRole(['Administrador', 'Supervisor']))
                        <li class="menu-title"><span>Reportes</span></li>
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->is('reportes*') ? 'active' : '' }}"
                                href="#sidebarReportes" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->is('reportes*') ? 'true' : 'false' }}"
                                aria-controls="sidebarReportes">
                                <i class="ri-bar-chart-line"></i> <span data-key="t-reportes">Reportes Generales</span>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->is('reportes*') ? 'show' : '' }}"
                                id="sidebarReportes">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('reportes.produccion') }}"
                                            class="nav-link {{ request()->routeIs('reportes.produccion') ? 'active' : '' }}"
                                            data-key="t-produccion">
                                            Producción
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reportes.eficiencia') }}"
                                            class="nav-link {{ request()->routeIs('reportes.eficiencia') ? 'active' : '' }}"
                                            data-key="t-eficiencia">
                                            Eficiencia
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reportes.insumos') }}"
                                            class="nav-link {{ request()->routeIs('reportes.insumos') ? 'active' : '' }}"
                                            data-key="t-insumos">
                                            Consumo de Insumos
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reportes.empleados') }}"
                                            class="nav-link {{ request()->routeIs('reportes.empleados') ? 'active' : '' }}"
                                            data-key="t-empleados">
                                            Rendimiento de Empleados
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