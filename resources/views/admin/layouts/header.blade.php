<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="{{ route('dashboard') }}" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{ asset('atlantico-logo-wide.png') }}" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ asset('atlantico-logo-wide.png') }}" alt=""
                                style="height: auto; width: auto; max-width: 100%; max-height: 70px; display: block; margin: 0 auto;">
                        </span>
                    </a>

                    <a href="{{ route('dashboard') }}" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="{{ asset('atlantico-logo-wide.png') }}" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ asset('atlantico-logo-wide.png') }}" alt=""
                                style="height: auto; width: auto; max-width: 100%; max-height: 70px; display: block; margin: 0 auto;">
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger"
                    id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>

                <!-- App Search-->
                <form class="app-search d-none ">
                    <div class="position-relative">
                        <input type="text" class="form-control" placeholder="Search..." autocomplete="off"
                            id="search-options" value="">
                        <span class="mdi mdi-magnify search-widget-icon"></span>
                        <span class="mdi mdi-close-circle search-widget-icon search-widget-icon-close d-none"
                            id="search-close-options"></span>
                    </div>
                    <div class="dropdown-menu dropdown-menu-lg" id="search-dropdown">
                        <div data-simplebar style="max-height: 320px;">
                            <!-- item-->
                            <div class="dropdown-header">
                                <h6 class="text-overflow text-muted mb-0 text-uppercase">Recent Searches</h6>
                            </div>

                            <div class="dropdown-item bg-transparent text-wrap">
                                <a href="index.html" class="btn btn-soft-secondary btn-sm rounded-pill">how to setup <i
                                        class="mdi mdi-magnify ms-1"></i></a>
                                <a href="index.html" class="btn btn-soft-secondary btn-sm rounded-pill">buttons <i
                                        class="mdi mdi-magnify ms-1"></i></a>
                            </div>
                            <!-- item-->
                            <div class="dropdown-header mt-2">
                                <h6 class="text-overflow text-muted mb-1 text-uppercase">Pages</h6>
                            </div>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <i class="ri-bubble-chart-line align-middle fs-18 text-muted me-2"></i>
                                <span>Analytics Dashboard</span>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <i class="ri-lifebuoy-line align-middle fs-18 text-muted me-2"></i>
                                <span>Help Center</span>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <i class="ri-user-settings-line align-middle fs-18 text-muted me-2"></i>
                                <span>My account settings</span>
                            </a>

                            <!-- item-->
                            <div class="dropdown-header mt-2">
                                <h6 class="text-overflow text-muted mb-2 text-uppercase">Members</h6>
                            </div>

                            <div class="notification-list">
                                <!-- item -->
                                <a href="javascript:void(0);" class="dropdown-item notify-item py-2">
                                    <div class="d-flex">
                                        <img src="{{ asset('assets/images/users/avatar-2.jpg') }}"
                                            class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                        <div class="flex-grow-1">
                                            <h6 class="m-0">Angela Bernier</h6>
                                            <span class="fs-11 mb-0 text-muted">Manager</span>
                                        </div>
                                    </div>
                                </a>
                                <!-- item -->
                                <a href="javascript:void(0);" class="dropdown-item notify-item py-2">
                                    <div class="d-flex">
                                        <img src="{{ asset('assets/images/users/avatar-3.jpg') }}"
                                            class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                        <div class="flex-grow-1">
                                            <h6 class="m-0">David Grasso</h6>
                                            <span class="fs-11 mb-0 text-muted">Web Designer</span>
                                        </div>
                                    </div>
                                </a>
                                <!-- item -->
                                <a href="javascript:void(0);" class="dropdown-item notify-item py-2">
                                    <div class="d-flex">
                                        <img src="{{ asset('assets/images/users/avatar-5.jpg') }}"
                                            class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                        <div class="flex-grow-1">
                                            <h6 class="m-0">Mike Bunch</h6>
                                            <span class="fs-11 mb-0 text-muted">React Developer</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div class="text-center pt-3 pb-1">
                            <a href="pages-search-results.html" class="btn btn-primary btn-sm">View All Results <i
                                    class="ri-arrow-right-line align-middle"></i></a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- CENTER SECTION: Company Info -->
            <div class="d-none d-lg-flex flex-grow-1 justify-content-center align-items-center px-4 topbar-brand-info">
                <div class="text-center">
                    <h4 class="topbar-brand-title mb-1 text-white fw-bold">
                        Manufacturas R.J. Atlántico
                    </h4>
                    <p class="topbar-brand-subtitle mb-0 text-white-50">
                        Software para la gestión de pedidos en Manufacturas R.J Atlántico C.A
                    </p>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <div class="dropdown d-md-none topbar-head-dropdown header-item">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                        id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <i class="bx bx-search fs-22"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                        aria-labelledby="page-header-search-dropdown">
                        <form class="p-3">
                            <div class="form-group m-0">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search ..."
                                        aria-label="Recipient's username">
                                    <button class="btn btn-primary" type="submit"><i
                                            class="mdi mdi-magnify"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Fecha y Hora -->
                <div class="ms-1 header-item d-none d-sm-flex">
                    <div class="d-flex align-items-center px-3 py-1 rounded-pill topbar-info-pill">
                        <i class="ri-calendar-line text-white me-2 fs-18"></i>
                        <div class="text-white text-center">
                            <small id="header-date" class="d-block fw-bold"
                                style="font-size: 0.7rem; line-height: 1.2;">
                                <!-- Date will be populated by JavaScript -->
                            </small>
                            <small id="header-time" class="d-block"
                                style="font-size: 0.65rem; line-height: 1.2; opacity: 0.9;">
                                <!-- Time will be populated by JavaScript -->
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Tasa BCV del Dólar -->
                @if(isset($tasaBcv) && $tasaBcv)
                    <div class="ms-1 header-item d-none d-sm-flex">
                        <div class="d-flex align-items-center px-3 py-1 rounded-pill topbar-info-pill">
                            <i class="ri-money-dollar-circle-line text-white me-2 fs-18"></i>
                            <div class="text-white text-center">
                                <small class="d-block" style="font-size: 0.6rem; line-height: 1; opacity: 0.8;">BCV
                                    {{ $tasaBcv->fecha_bcv->format('d/m') }}</small>
                                <span class="fw-bold" style="font-size: 0.85rem;">Bs.
                                    {{ number_format($tasaBcv->valor, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                        data-toggle="fullscreen">
                        <i class='bx bx-fullscreen fs-22'></i>
                    </button>
                </div>

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button"
                        class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle light-dark-mode">
                        <i class='bx bx-moon fs-22'></i>
                    </button>
                </div>

                <div class="dropdown topbar-head-dropdown ms-1 header-item" id="notificationDropdown"
                    data-notif-endpoint="{{ route('notificaciones.sistema') }}">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                        id="page-header-notifications-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                        aria-haspopup="true" aria-expanded="false">
                        <i class='bx bx-bell fs-22'></i>
                        <span
                            class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger d-none"
                            id="notif-badge">0<span class="visually-hidden">notificaciones</span></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                        aria-labelledby="page-header-notifications-dropdown">

                        <div class="notif-dropdown-head">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="notif-title">
                                        <i class="ri-notification-3-line"></i>
                                        Notificaciones
                                    </h6>
                                    <p class="notif-subtitle">Alertas activas del sistema</p>
                                </div>
                                <button type="button" class="btn-notif-action"
                                    id="notif-clear-dismissed"
                                    title="Restaurar notificaciones ocultas en esta sesión">
                                    <i class="ri-refresh-line"></i>
                                </button>
                            </div>
                        </div>

                        <div class="position-relative" id="notificationItemsTabContent">
                            <div class="py-2 ps-2">
                                <div data-simplebar style="max-height: 320px;" class="pe-2">
                                    <div id="notif-list"></div>

                                    <div id="notif-empty" class="text-center py-4 px-3 d-none">
                                        <div class="avatar-md mx-auto mb-3">
                                            <div
                                                class="avatar-title bg-success-subtle text-success rounded-circle fs-24">
                                                <i class="ri-check-double-line"></i>
                                            </div>
                                        </div>
                                        <h6 class="fs-14 mb-1">Todo al día</h6>
                                        <p class="text-muted mb-0 fs-12">No hay notificaciones del sistema.</p>
                                    </div>

                                    <div id="notif-loading" class="text-center py-4 px-3">
                                        <div class="spinner-border spinner-border-sm text-muted" role="status">
                                            <span class="visually-hidden">Cargando…</span>
                                        </div>
                                        <p class="text-muted mb-0 fs-12 mt-2">Cargando notificaciones…</p>
                                    </div>

                                    <div id="notif-error" class="text-center py-4 px-3 d-none">
                                        <div class="avatar-md mx-auto mb-3">
                                            <div
                                                class="avatar-title bg-danger-subtle text-danger rounded-circle fs-24">
                                                <i class="ri-wifi-off-line"></i>
                                            </div>
                                        </div>
                                        <h6 class="fs-14 mb-1">No se pudieron cargar</h6>
                                        <p class="text-muted mb-0 fs-12">Reintenta más tarde.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="notif-footer">
                            <a href="{{ route('inventario.alertas') }}">
                                Ver todas las alertas de stock
                                <i class="ri-arrow-right-line"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    @auth
                        <button type="button"
                            class="btn bg-white bg-opacity-10 border border-light border-opacity-25 rounded-pill px-3 py-1 text-white"
                            id="page-header-user-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            aria-haspopup="true" aria-expanded="false">
                            <span class="d-flex align-items-center">
                                <img class="rounded-circle header-profile-user border border-2 border-light border-opacity-25"
                                    src="{{ Auth::user()->avatar_url }}"
                                    alt="Header Avatar">
                                <span class="text-start ms-xl-2">
                                    <span
                                        class="d-none d-xl-inline-block ms-1 fw-semibold text-white">Configuración</span>
                                    <span class="d-none d-xl-block ms-1 fs-13 text-white-50">{{ Auth::user()->name }}</span>
                                </span>
                                <i class="mdi mdi-chevron-down text-white-50 ms-2 d-none d-xl-inline-block"></i>
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-0 overflow-hidden border-0 shadow-lg profile-dropdown-menu"
                            id="profile-dropdown-menu" aria-labelledby="page-header-user-dropdown"
                            style="min-width: 240px;">
                            <div class="profile-dropdown-header px-3 pt-2 pb-2 border-bottom bg-light-subtle">
                                <h6 class="profile-dropdown-title mb-0">{{ Auth::user()->role }}</h6>
                            </div>
                            <div class="py-1">
                                <a class="dropdown-item d-flex align-items-center" href="{{ route('profile.edit') }}">
                                    <i class="mdi mdi-account-circle fs-16 align-middle me-2 text-primary"></i>
                                    <span class="align-middle">Configuración de perfil</span>
                                </a>
                                {{-- <a class="dropdown-item" href="apps-chat.html"><i
                                        class="mdi mdi-message-text-outline text-muted fs-16 align-middle me-1"></i> <span
                                        class="align-middle">Messages</span></a>
                                <a class="dropdown-item" href="apps-tasks-kanban.html"><i
                                        class="mdi mdi-calendar-check-outline text-muted fs-16 align-middle me-1"></i> <span
                                        class="align-middle">Taskboard</span></a>
                                <a class="dropdown-item" href="pages-faqs.html"><i
                                        class="mdi mdi-lifebuoy text-muted fs-16 align-middle me-1"></i> <span
                                        class="align-middle">Help</span></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="pages-profile.html"><i
                                        class="mdi mdi-wallet text-muted fs-16 align-middle me-1"></i> <span
                                        class="align-middle">Balance : <b>$5971.67</b></span></a>
                                <a class="dropdown-item" href="pages-profile-settings.html"><span
                                        class="badge bg-success-subtle text-success mt-1 float-end">New</span><i
                                        class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> <span
                                        class="align-middle">Settings</span></a>
                                <a class="dropdown-item" href="auth-lockscreen-basic.html"><i
                                        class="mdi mdi-lock text-muted fs-16 align-middle me-1"></i> <span
                                        class="align-middle">Lock screen</span></a>--}}
                                @if (Auth::user()->isAdmin())
                                    <a class="dropdown-item d-flex align-items-center" href="{{ url('users') }}">
                                        <i class="mdi mdi-account-group-outline fs-16 align-middle me-2 text-primary"></i>
                                        <span class="align-middle">Configuración de usuarios</span>
                                    </a>
                                @endif
                            </div>
                            <div class="dropdown-divider my-0"></div>
                            <div class="py-1">
                                <a class="dropdown-item d-flex align-items-center text-danger" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="mdi mdi-logout fs-16 align-middle me-2"></i>
                                    <span class="align-middle" data-key="t-logout">Cerrar sesión</span>
                                </a>
                            </div>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    @endauth
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-primary">Iniciar Sesión</a>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</header>

<!-- removeNotificationModal -->
<div id="removeNotificationModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                    id="NotificationModalbtn-close"></button>
            </div>
            <div class="modal-body">
                <div class="mt-2 text-center">
                    <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop"
                        colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>
                    <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                        <h4>Are you sure ?</h4>
                        <p class="text-muted mx-4 mb-0">Are you sure you want to remove this Notification ?</p>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                    <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn w-sm btn-danger" id="delete-notification">Yes, Delete
                        It!</button>
                </div>
            </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<style>
    .topbar-info-pill {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(8px);
        transition: background-color .2s ease, border-color .2s ease, box-shadow .2s ease;
    }

    .topbar-info-pill:hover {
        background-color: rgba(255, 255, 255, 0.18);
        border-color: rgba(255, 255, 255, 0.35);
        box-shadow: 0 0 0 0.15rem rgba(255, 255, 255, 0.12);
    }

    #page-header-user-dropdown {
        backdrop-filter: blur(8px);
        transition: background-color .2s ease, border-color .2s ease;
    }

    #page-header-user-dropdown:hover,
    #page-header-user-dropdown:focus,
    #page-header-user-dropdown:active,
    #page-header-user-dropdown.active,
    #page-header-user-dropdown.show {
        color: #fff;
        background-color: rgba(255, 255, 255, 0.18);
        border-color: rgba(255, 255, 255, 0.35) !important;
        box-shadow: 0 0 0 0.15rem rgba(255, 255, 255, 0.12) !important;
    }

    #page-header-user-dropdown:hover .header-profile-user,
    #page-header-user-dropdown:focus .header-profile-user,
    #page-header-user-dropdown:active .header-profile-user,
    #page-header-user-dropdown.active .header-profile-user,
    #page-header-user-dropdown.show .header-profile-user {
        border-color: rgba(255, 255, 255, 0.45) !important;
    }

    .profile-dropdown-menu .dropdown-item {
        padding: .5rem 1rem;
    }

    .profile-dropdown-header {
        min-height: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .profile-dropdown-title {
        font-size: 1.02rem;
        font-weight: 600;
        line-height: 1.15;
        color: var(--vz-body-color);
    }

    .profile-dropdown-subtitle {
        display: block;
        margin-top: 0;
        margin-bottom: 0;
        font-size: .76rem;
        line-height: 1;
        opacity: .75;
    }

    .profile-dropdown-menu .dropdown-divider {
        margin: .2rem 0;
    }
</style>

<!-- Real-time Clock Script -->
<script>
    function updateHeaderDateTime() {
        const now = new Date();

        // Format date as DD/MM/YYYY
        const day = String(now.getDate()).padStart(2, '0');
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const year = now.getFullYear();

        // Format time as HH:MM AM/PM
        let hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // 0 should be 12

        const dateString = `${day}/${month}/${year}`;
        const timeString = `${hours}:${minutes} ${ampm}`;

        const dateElement = document.getElementById('header-date');
        const timeElement = document.getElementById('header-time');

        if (dateElement) {
            dateElement.textContent = dateString;
        }
        if (timeElement) {
            timeElement.textContent = timeString;
        }
    }

    // Update immediately on page load
    document.addEventListener('DOMContentLoaded', function () {
        updateHeaderDateTime();
        // Update every second
        setInterval(updateHeaderDateTime, 1000);

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            const profileDropdownTrigger = document.getElementById('page-header-user-dropdown');
            if (!profileDropdownTrigger || profileDropdownTrigger.getAttribute('aria-expanded') !== 'true') {
                return;
            }

            const dropdownInstance = bootstrap.Dropdown.getOrCreateInstance(profileDropdownTrigger);
            dropdownInstance.hide();
            profileDropdownTrigger.blur();
        });

        const profileDropdownTrigger = document.getElementById('page-header-user-dropdown');
        if (profileDropdownTrigger) {
            bootstrap.Dropdown.getOrCreateInstance(profileDropdownTrigger, {
                autoClose: 'outside'
            });

            const profileDropdownMenu = document.getElementById('profile-dropdown-menu');

            const getProfileDropdownItems = () => {
                if (!profileDropdownMenu) {
                    return [];
                }

                return Array.from(
                    profileDropdownMenu.querySelectorAll('.dropdown-item:not(.disabled):not([disabled])')
                );
            };

            const focusProfileItemAt = (index) => {
                const items = getProfileDropdownItems();
                if (!items.length) {
                    return;
                }

                const safeIndex = (index + items.length) % items.length;
                items[safeIndex].focus();
            };

            profileDropdownTrigger.addEventListener('keydown', function (event) {
                const dropdownInstance = bootstrap.Dropdown.getOrCreateInstance(profileDropdownTrigger, {
                    autoClose: 'outside'
                });
                const isExpanded = profileDropdownTrigger.getAttribute('aria-expanded') === 'true';

                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    if (!isExpanded) {
                        dropdownInstance.show();
                    }
                    focusProfileItemAt(0);
                }

                if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    if (!isExpanded) {
                        dropdownInstance.show();
                    }
                    const items = getProfileDropdownItems();
                    focusProfileItemAt(items.length - 1);
                }
            });

            if (profileDropdownMenu) {
                profileDropdownMenu.addEventListener('keydown', function (event) {
                    const items = getProfileDropdownItems();
                    if (!items.length) {
                        return;
                    }

                    const currentIndex = items.indexOf(document.activeElement);

                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        focusProfileItemAt(currentIndex + 1);
                    }

                    if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        focusProfileItemAt(currentIndex - 1);
                    }

                    if (event.key === 'Home') {
                        event.preventDefault();
                        focusProfileItemAt(0);
                    }

                    if (event.key === 'End') {
                        event.preventDefault();
                        focusProfileItemAt(items.length - 1);
                    }
                });
            }
        }
    });
</script>