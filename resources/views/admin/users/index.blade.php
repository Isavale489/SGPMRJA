@extends('admin.layouts.app')

@push('styles')
    <!-- Sweet Alert css-->
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet"
        type="text/css" />
    <!-- Se eliminó la referencia a los estilos de botones -->
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Gestión de Usuarios</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Seguridad</a></li>
                        <li class="breadcrumb-item active">Usuarios</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>

    {{-- Estilos en public/assets/css/custom.css — sección "MÓDULO MAESTROS — Usuarios" --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-maestros">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Listado de Usuarios</h5>
                        <div class="flex-shrink-0 d-flex align-items-center gap-3">
                            <!-- Toggle Historial -->
                            @if($historial)
                                <a href="{{ route('users.index') }}" class="btn-historial btn-historial-volver">
                                    <i class="ri-arrow-left-line"></i> Solo Activos
                                </a>
                            @else
                                <a href="{{ route('users.index', ['historial' => true]) }}"
                                    class="btn-historial btn-historial-ver">
                                    <i class="ri-archive-line"></i> Inhabilitados
                                </a>
                            @endif
                            <div class="d-flex gap-2">
                                @if(!$historial)
                                    <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn"
                                        data-bs-target="#showModal">
                                        <i class="ri-add-line align-bottom me-1"></i> Agregar Usuario
                                    </button>
                                @endif
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#pdfExportModal">
                                    <i class="ri-file-pdf-fill align-bottom me-1"></i> Exportar PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="advanced-filters-wrapper navy-theme" id="advanced-filters">
                        <div class="navy-filter-header is-collapsed">
                            <div class="navy-header-search">
                                <i class="ri-search-line"></i>
                                <input type="text" class="navy-search-input" id="custom-search-input"
                                    placeholder="Buscar usuario..." autocomplete="off">
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
                                    <div class="col-12">
                                        <label class="navy-filter-label" for="filter-role">
                                            <i class="ri-shield-user-line"></i> Rol
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-role">
                                            <option value="">Todos los roles</option>
                                            @foreach($roles as $rol)
                                                <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-2">
                                    <button type="button" class="btn btn-link" id="btn-clear-filters">Limpiar filtros</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <table id="users-table" class="table table-bordered table-striped table-sm align-middle table-operativa table-maestro">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles del Usuario -->
    <div class="modal fade atlantico-modal" id="viewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title">Detalles del Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">

                    {{-- Hero strip --}}
                    <div class="cli-view-hero">
                        <div class="cli-view-hero-avatar">
                            <img id="user-avatar" src="/assets/images/users/user-dummy-img.jpg" alt="Avatar"
                                style="width:100%;height:100%;object-fit:cover;">
                        </div>
                        <div class="cli-view-hero-info">
                            <div class="cli-view-hero-name" id="view-name">—</div>
                            <div class="cli-view-hero-doc" id="view-email">—</div>
                        </div>
                        <div class="cli-view-hero-badge text-end">
                            <div><span class="badge rounded-pill" id="view-estado">—</span></div>
                            <div class="cli-view-hero-date mt-1"><i class="ri-calendar-line me-1"></i><span id="view-created">—</span></div>
                        </div>
                    </div>

                    {{-- Secciones --}}
                    <div class="px-4 py-3" style="background:#fbfcfe;">

                        {{-- Acceso al Sistema --}}
                        <div class="cli-view-card">
                        <div class="cli-view-card-header"><i class="ri-shield-user-line"></i>Acceso al Sistema</div>
                        <div class="cli-view-card-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-start">
                                    <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                        <i class="ri-shield-user-line emp-icon--navy"></i>
                                    </div>
                                    <div><small class="text-muted d-block fs-12">Rol</small>
                                    <span class="fw-semibold fs-13" id="view-role">-</span></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-start">
                                    <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                        <i class="ri-mail-line emp-icon--navy"></i>
                                    </div>
                                    <div><small class="text-muted d-block fs-12">Correo electrónico</small>
                                    <span class="fw-semibold fs-13" id="view-email-card">-</span></div>
                                </div>
                            </div>
                        </div>
                        </div></div>

                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar/editar -->
    <div class="modal fade atlantico-modal" id="showModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="modalTitle">Agregar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="userForm">
                    <div class="modal-body">
                        <input type="hidden" id="id-field" />

                        <div class="modal-form-section">
                            <div class="modal-form-section-title"><i class="ri-shield-keyhole-line"></i>Credenciales de Acceso</div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <x-forms.input name="name" label="Nombre" placeholder="Nombre completo" required />
                                </div>
                                <div class="col-md-6">
                                    <x-forms.input name="email" label="Email" type="email" placeholder="correo@ejemplo.com" required />
                                </div>
                            </div>

                            <div class="row mb-0" id="password-group">
                                <div class="col-md-6">
                                    <x-forms.input name="password" label="Contraseña" type="password"
                                        placeholder="Contraseña"
                                        hint="Dejar en blanco para mantener la actual al editar" />
                                </div>
                                <div class="col-md-6">
                                    <x-forms.input name="password_confirmation" label="Confirmar Contraseña" type="password"
                                        placeholder="Confirmar Contraseña" required />
                                </div>
                            </div>
                        </div>

                        <div class="modal-form-section mb-0">
                            <div class="modal-form-section-title"><i class="ri-user-settings-line"></i>Perfil de Usuario</div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <x-forms.select name="role_id" label="Rol" required
                                        :options="$roles->pluck('nombre', 'id')"
                                        placeholder="Seleccione un rol" />
                                </div>
                            </div>
                            <div class="d-flex align-items-start gap-2 mb-3 p-2 rounded-3 bg-info-subtle border border-info-subtle">
                                <i class="ri-information-line text-info fs-5 lh-1 mt-1"></i>
                                <small class="text-info-emphasis mb-0 lh-sm">
                                    El estado controla el acceso al sistema. Un usuario nuevo queda <strong>Activo</strong>.
                                    Para bloquear su ingreso usa <strong>Inhabilitar</strong> (no podrá iniciar sesión y pasa al historial);
                                    <strong>Habilitar</strong> lo reactiva.
                                </small>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6">
                                    <label for="field-avatar" class="form-label">Avatar</label>
                                    <input type="file" id="field-avatar" name="avatar" class="form-control"
                                        accept="image/*" />
                                    <div id="avatar-preview" class="mt-2 text-center" style="display: none;">
                                        <img src="" alt="Vista previa del avatar" class="img-fluid rounded-circle"
                                            style="max-width: 100px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer bg-light border-0">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                <i class="ri-close-line me-1"></i>Cerrar
                            </button>
                            <x-ui.button-save id="add-btn" text="Agregar" icon="ri-add-line" loading-text="Agregando..." />
                            <x-ui.button-save id="edit-btn" text="Actualizar" icon="ri-save-line"
                                loading-text="Actualizando..." style="display: none;" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Resetear Contraseña --}}
    <div class="modal fade atlantico-modal" id="resetPasswordModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-3">
                    <h5 class="modal-title">
                        <i class="bx bx-lock-alt me-2"></i>Resetear contraseña
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center gap-2 p-2 mb-3 rounded"
                        style="background:rgba(30,60,114,0.06);border-left:3px solid #1e3c72;">
                        <i class="bx bx-user-circle fs-4 text-primary flex-shrink-0"></i>
                        <div style="min-width:0;">
                            <div id="rp-user-name" class="fw-semibold text-truncate" style="font-size:.875rem;"></div>
                            <div id="rp-user-email" class="text-muted text-truncate" style="font-size:.775rem;"></div>
                        </div>
                    </div>
                    <p class="text-muted mb-3" style="font-size:.825rem;">
                        Asigna una contraseña temporal. El usuario deberá cambiarla en su próximo inicio de sesión.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold mb-1" style="font-size:.825rem;">Contraseña temporal</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                            <input id="rp-password" type="password" class="form-control"
                                placeholder="Mínimo 8 caracteres"
                                autocomplete="new-password" autocorrect="off" autocapitalize="off" maxlength="191">
                            <button type="button" class="btn btn-outline-secondary" id="rp-toggle-pass" tabindex="-1">
                                <i class="bx bx-show"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold mb-1" style="font-size:.825rem;">Confirmar contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-lock"></i></span>
                            <input id="rp-password-confirm" type="password" class="form-control"
                                placeholder="Repite la contraseña"
                                autocomplete="new-password" autocorrect="off" autocapitalize="off" maxlength="191">
                            <button type="button" class="btn btn-outline-secondary" id="rp-toggle-pass-confirm" tabindex="-1">
                                <i class="bx bx-show"></i>
                            </button>
                        </div>
                    </div>
                    <div id="rp-error" class="text-danger mt-2 d-none" style="font-size:.8rem;"></div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Cancelar
                        </button>
                        <button type="button" class="btn" id="rp-submit-btn"
                            style="background:linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);color:#fff;border:none;font-weight:600;">
                            <span id="rp-submit-text"><i class="bx bx-check me-1"></i>Resetear</span>
                            <span id="rp-submit-spinner" class="d-none">
                                <span class="spinner-border spinner-border-sm me-1"></span>Reseteando…
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Modal: Exportar PDF --}}
    <div class="modal fade atlantico-modal" id="pdfExportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-file-pdf-line me-2"></i>Exportar PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Filtra qué usuarios incluir en el reporte.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="pdf-filter-role">Rol</label>
                        <select class="form-select" id="pdf-filter-role">
                            <option value="">Todos los roles</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold" for="pdf-filter-estatus">Estatus</label>
                        <select class="form-select" id="pdf-filter-estatus">
                            <option value="">Todos</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <div class="row g-2 mt-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="pdf-fecha-desde">Registro desde</label>
                            <input type="date" class="form-control" id="pdf-fecha-desde">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="pdf-fecha-hasta">Registro hasta</label>
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
    <script src="{{ URL::asset('/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>


    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            function debounce(func, wait) {
                let timeout;
                return function (...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            function updateFilterBadge() {
                let count = 0;
                $('.navy-filter-select').each(function () {
                    if ($(this).val() && $(this).val() !== '') count++;
                });
                $('#active-filter-count').text(count).toggleClass('d-none', count === 0);
            }

            $('#filters-collapse-body')
                .on('show.bs.collapse', function () {
                    $('.navy-filter-header').removeClass('is-collapsed');
                })
                .on('hidden.bs.collapse', function () {
                    $('.navy-filter-header').addClass('is-collapsed');
                });

            function generateButtons(userId, recoveryLocked, isSelf, userName, userEmail, estado) {
                var sVer =
                    '<button class="btn btn-sm btn-soft-info view-item-btn" data-id="' + userId + '" title="Ver"><i class="ri-eye-fill"></i></button>';

                var items = '';

                if (estado != 1) {
                    // Inhabilitado → menú con solo Habilitar
                    items +=
                        '<li><button type="button" class="dropdown-item act-item act-restore restore-item-btn" data-id="' + userId + '"><span class="act-ic"><i class="ri-arrow-go-back-line"></i></span>Habilitar</button></li>';
                } else {
                    // Activo → Editar + [desbloquear] + [resetear pass] + [separador + Inhabilitar]
                    items +=
                        '<li><button type="button" class="dropdown-item act-item act-edit edit-item-btn" data-id="' + userId + '"><span class="act-ic"><i class="ri-pencil-fill"></i></span>Editar</button></li>';

                    if (recoveryLocked) {
                        items +=
                            '<li><button type="button" class="dropdown-item act-item act-warn unlock-recovery-btn" data-id="' + userId + '"><span class="act-ic"><i class="ri-lock-unlock-line"></i></span>Desbloquear recuperación</button></li>';
                    }

                    if (!isSelf) {
                        items +=
                            '<li><button type="button" class="dropdown-item act-item act-primary reset-password-btn" data-id="' + userId + '" data-name="' + (userName || '') + '" data-email="' + (userEmail || '') + '"><span class="act-ic"><i class="ri-key-2-line"></i></span>Resetear contraseña</button></li>';
                        // No permitir auto-inhabilitarse desde la UI (el backend también lo bloquea)
                        items +=
                            '<li><hr class="dropdown-divider"></li>' +
                            '<li><button type="button" class="dropdown-item act-item act-del remove-item-btn" data-id="' + userId + '"><span class="act-ic"><i class="ri-forbid-line"></i></span>Inhabilitar</button></li>';
                    }
                }

                var menu =
                    '<div class="dropdown d-inline-block">' +
                    '<button class="btn btn-sm btn-soft-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Más acciones"><i class="ri-more-2-fill"></i></button>' +
                    '<ul class="dropdown-menu dropdown-menu-end actions-menu">' + items + '</ul>' +
                    '</div>';

                return '<div class="d-flex gap-1 justify-content-center align-items-center">' + sVer + menu + '</div>';
            }

            var currentUserId = {{ auth()->id() ?? 'null' }};

            function renderEllipsis(value) {
                if (!value) return '<span class="text-muted">—</span>';
                return '<span title="' + value + '" style="cursor:default;">' + value + '</span>';
            }

            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('users.data') }}",
                    data: function (d) {
                        d.filter_role = $('#filter-role').val();
                        d.historial = @json($historial);
                    }
                },
                columns: [
                    {
                        data: 'name',
                        render: function (data, type, row) {
                            return `
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0 me-2">
                                                                        <div class="avatar-xs">
                                                                            <img src="${row.avatar || '/assets/images/users/user-dummy-img.jpg'}" alt="Avatar" class="img-fluid rounded-circle">
                                                                        </div>
                                                                    </div>
                                                                    <div class="flex-grow-1 text-truncate" title="${data || ''}">${data || '—'}</div>
                                                                </div>
                                                            `;
                        }
                    },
                    {
                        data: 'email',
                        render: function (data) {
                            return renderEllipsis(data);
                        }
                    },
                    {
                        data: 'role',
                        render: function (data) {
                            if (data === 'Administrador') {
                                return '<span class="badge-tipo badge-rol-admin"><i class="ri-shield-star-line"></i> Administrador</span>';
                            } else if (data === 'Supervisor') {
                                return '<span class="badge-tipo badge-rol-supervisor"><i class="ri-shield-user-line"></i> Supervisor</span>';
                            }
                            return data || 'Sin rol';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return generateButtons(row.id, row.recovery_locked, row.id === currentUserId, row.name, row.email, row.estado);
                        }
                    }
                ],
                order: [
                    [0, 'asc']
                ],
                dom: 'rtip',
                responsive: false,
                language: lenguajeData
            });

            // Buscador personalizado
            $('#custom-search-input').on('input', debounce(function () {
                table.search(this.value).draw();
            }, 300));

            $('.navy-filter-select').on('change', function () {
                table.ajax.reload(null, true);
                updateFilterBadge();
            });

            $('#btn-clear-filters').on('click', function () {
                $('.navy-filter-select').val('');
                $('#custom-search-input').val('');
                table.search('').draw();
                table.ajax.reload(null, true);
                updateFilterBadge();
            });

            updateFilterBadge();

            // ── Si se llegó por toggle historial (?historial=true) → mostrar inhabilitados ──
            function validarFormularioUsuario() {
                let esValido = true;
                let esCreacion = $('#id-field').val() === '';

                let $nombre = $('#field-name');
                let nombreVal = $nombre.val().trim();
                if (!nombreVal) {
                    marcarInvalido($nombre, 'El nombre es obligatorio.');
                    esValido = false;
                } else if (nombreVal.length < 2) {
                    marcarInvalido($nombre, 'El nombre debe tener al menos 2 caracteres.');
                    esValido = false;
                } else {
                    marcarValido($nombre);
                }

                let $email = $('#field-email');
                let emailVal = $email.val().trim();
                let emailRegex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
                if (!emailVal) {
                    marcarInvalido($email, 'El email es obligatorio.');
                    esValido = false;
                } else if (!emailRegex.test(emailVal)) {
                    marcarInvalido($email, 'Ingrese un email válido (ej: usuario@dominio.com).');
                    esValido = false;
                } else {
                    marcarValido($email);
                }

                let $role = $('#field-role_id');
                if (!$role.val()) {
                    marcarInvalido($role, 'El rol es obligatorio.');
                    esValido = false;
                } else {
                    marcarValido($role);
                }

                if (esCreacion) {
                    let $pass = $('#field-password');
                    let passVal = $pass.val();
                    if (passVal.length === 0) {
                        marcarInvalido($pass, 'La contraseña es obligatoria.');
                        esValido = false;
                    } else {
                        let errorContrasena = validarContrasena(passVal);
                        if (errorContrasena) {
                            marcarInvalido($pass, errorContrasena);
                            esValido = false;
                        } else {
                            marcarValido($pass);
                        }
                    }

                    let $confirm = $('#field-password_confirmation');
                    let confirmVal = $confirm.val();
                    if (!confirmVal) {
                        marcarInvalido($confirm, 'La confirmación de contraseña es obligatoria.');
                        esValido = false;
                    } else if (confirmVal !== passVal) {
                        marcarInvalido($confirm, 'Las contraseñas no coinciden.');
                        esValido = false;
                    } else {
                        marcarValido($confirm);
                    }
                }

                return esValido;
            }

            function resetForm() {
                $('#modalTitle').text('Agregar Usuario');
                $('#userForm')[0].reset();
                $('#userForm input[type="hidden"]').val('');
                $('#avatar-preview').hide().find('img').attr('src', '');
                $('#add-btn').show();
                $('#edit-btn').hide();
                $('#password-group').show();
                $('#field-password').prop('required', true); // Requerir contraseña al crear
                $('#field-password_confirmation').prop('required', true);

                // Reiniciar validaciones
                $('#userForm').find('input, select, textarea').removeClass('is-invalid is-valid');
                $('#userForm').find('.invalid-feedback').hide();
            }

            function setEditMode() {
                $("#modalTitle").text("Actualizar Usuario");
                $("#add-btn").hide();
                $("#edit-btn").show();
                $('#password-group').hide(); // Ocultar campo de contraseña al editar
                $('#field-password').prop('required', false); // No requerir contraseña al editar
                $('#field-password_confirmation').prop('required', false);
            }

            // Función para mostrar vista previa de imágenes
            function readURL(input, previewId) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $(previewId).find('img').attr('src', e.target.result);
                        $(previewId).show();
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            // Vista previa de imágenes al seleccionarlas
            $('#field-avatar').change(function () {
                readURL(this, '#avatar-preview');
            });

            $("#create-btn").click(function () {
                resetForm();
                // Ocultar vista previa
                $('#avatar-preview').hide();
            });

            $("#showModal").on('hidden.bs.modal', function () {
                resetForm();
            });

            $('#add-btn').click(function (e) {
                e.preventDefault();

                if (!validarFormularioUsuario()) {
                    return;
                }

                $("#userForm").submit();
            });

            $("#userForm").on("submit", function (e) {
                e.preventDefault();
                var id = $("#id-field").val();
                var url = id ? "{{ route('users.update', ':id') }}".replace(':id', id) : "{{ route('users.store') }}";
                var method = id ? "PUT" : "POST";

                var formData = new FormData(this);
                if (method === 'PUT') {
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $("#showModal").modal("hide");
                        $("#userForm").trigger("reset");
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            customClass: {
                                confirmButton: 'btn btn-primary w-xs me-2',
                                cancelButton: 'btn btn-danger w-xs'
                            },
                            buttonsStyling: false,
                            showCloseButton: true,
                            showConfirmButton: true,
                            timer: 2000
                        });
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON.message,
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

            $(document).on("click", ".view-item-btn", function () {
                var id = $(this).data("id");
                $.get("{{ route('users.show', '') }}/" + id, function (data) {
                    $("#viewModal").modal("show");
                    $("#view-name").text(data.name);
                    $("#view-email").text(data.email);
                    $("#view-email-card").text(data.email);
                    $("#view-role").text(data.role || 'Sin rol');
                    $("#view-created").text(data.created_at);
                    var _activo = (data.estado == 1 || data.estado === true);
                    $("#view-estado")
                        .text(_activo ? 'Activo' : 'Inhabilitado')
                        .removeClass('bg-success bg-danger')
                        .addClass(_activo ? 'bg-success' : 'bg-danger');

                    // Mostrar avatar
                    if (data.avatar) {
                        $("#user-avatar").attr("src", data.avatar);
                    } else {
                        $("#user-avatar").attr("src", "/assets/images/users/user-dummy-img.jpg");
                    }


                });
            });

            $(document).on("click", ".edit-item-btn", function () {
                var id = $(this).data("id");

                $.get("{{ route('users.edit', ':id') }}".replace(':id', id), function (data) {
                    setEditMode();
                    $("#id-field").val(data.id);
                    $("#field-name").val(data.name);
                    $("#field-email").val(data.email);
                    $("#field-role_id").val(data.role_id);

                    // Mostrar las imágenes existentes si las hay
                    if (data.avatar) {
                        $("#avatar-preview img").attr('src', data.avatar);
                        $("#avatar-preview").show();
                    }


                    $("#showModal").modal("show");
                });
            });

            $(document).on("click", ".reset-password-btn", function () {
                var id        = $(this).data("id");
                var userName  = $(this).data("name") || '—';
                var userEmail = $(this).data("email") || '—';

                $('#rp-user-name').text(userName);
                $('#rp-user-email').text(userEmail);
                $('#resetPasswordModal').data('user-id', id);
                $('#rp-password, #rp-password-confirm').val('').attr('type', 'password');
                $('#rp-toggle-pass i, #rp-toggle-pass-confirm i').removeClass('bx-hide').addClass('bx-show');
                $('#rp-error').addClass('d-none').text('');
                $('#resetPasswordModal').modal('show');
            });

            $('#rp-toggle-pass, #rp-toggle-pass-confirm').on('click', function () {
                var input = document.getElementById(this.id === 'rp-toggle-pass' ? 'rp-password' : 'rp-password-confirm');
                var icon  = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('bx-show', 'bx-hide');
                } else {
                    input.type = 'password';
                    icon.classList.replace('bx-hide', 'bx-show');
                }
            });

            $('#rp-submit-btn').on('click', function () {
                var id      = $('#resetPasswordModal').data('user-id');
                var pass    = $('#rp-password').val();
                var confirm = $('#rp-password-confirm').val();
                var errDiv  = $('#rp-error');

                errDiv.addClass('d-none').text('');

                if (!pass || pass.length < 8) {
                    errDiv.removeClass('d-none').text('La contraseña debe tener al menos 8 caracteres.');
                    return;
                }
                if (pass !== confirm) {
                    errDiv.removeClass('d-none').text('Las contraseñas no coinciden.');
                    return;
                }

                $('#rp-submit-text').addClass('d-none');
                $('#rp-submit-spinner').removeClass('d-none');
                $('#rp-submit-btn').prop('disabled', true);

                $.ajax({
                    url: "{{ url('users') }}/" + id + "/reset-password",
                    type: "POST",
                    data: { password: pass },
                    success: function (response) {
                        $('#resetPasswordModal').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: '¡Reseteada!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 2500
                        });
                    },
                    error: function (xhr) {
                        var msg = xhr.responseJSON?.message
                            || (xhr.responseJSON?.errors?.password ? xhr.responseJSON.errors.password[0] : null)
                            || 'No se pudo resetear la contraseña.';
                        errDiv.removeClass('d-none').text(msg);
                    },
                    complete: function () {
                        $('#rp-submit-text').removeClass('d-none');
                        $('#rp-submit-spinner').addClass('d-none');
                        $('#rp-submit-btn').prop('disabled', false);
                    }
                });
            });

            $(document).on("click", ".unlock-recovery-btn", function () {
                var id = $(this).data("id");
                Swal.fire({
                    title: '¿Desbloquear recuperación?',
                    text: 'Se reseteará el contador de intentos fallidos y el bloqueo temporal del usuario.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, desbloquear',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-primary w-xs me-2',
                        cancelButton: 'btn btn-danger w-xs'
                    },
                    buttonsStyling: false,
                    showCloseButton: true
                }).then(function (result) {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: "{{ url('users') }}/" + id + "/unlock-recovery",
                        type: "POST",
                        success: function (response) {
                            table.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: '¡Desbloqueado!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1800
                            });
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'No se pudo desbloquear.'
                            });
                        }
                    });
                });
            });

            $(document).on("click", ".remove-item-btn", function () {
                var id = $(this).data("id");
                Swal.fire({
                    title: '¿Inhabilitar usuario?',
                    text: "No podrá iniciar sesión y pasará al historial. Puedes habilitarlo de nuevo cuando quieras.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, inhabilitar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-primary w-xs me-2',
                        cancelButton: 'btn btn-danger w-xs'
                    },
                    buttonsStyling: false,
                    showCloseButton: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('users.destroy', '') }}/" + id,
                            type: "DELETE",
                            success: function (response) {
                                table.ajax.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Inhabilitado!',
                                    text: response.success,
                                    buttonsStyling: false,
                                    showConfirmButton: false,
                                    timer: 1800
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON.message,
                                    customClass: {
                                        confirmButton: 'btn btn-primary w-xs me-2',
                                        cancelButton: 'btn btn-danger w-xs'
                                    },
                                    buttonsStyling: false,
                                    showCloseButton: true
                                });
                            }
                        });
                    }
                });
            });

            // ══════════════════════════════════════════════════════
            // HABILITAR (Restaurar) — estándar de inhabilitación por estado
            // ══════════════════════════════════════════════════════
            $(document).on("click", ".restore-item-btn", function () {
                var id = $(this).data("id");
                Swal.fire({
                    title: '¿Habilitar usuario?',
                    text: "El usuario volverá a estar activo y podrá iniciar sesión.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, habilitar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-success w-xs me-2',
                        cancelButton: 'btn btn-light w-xs'
                    },
                    buttonsStyling: false,
                    showCloseButton: true
                }).then(function (result) {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: "{{ url('users') }}/" + id + "/restore",
                        type: "POST",
                        success: function (response) {
                            table.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: '¡Habilitado!',
                                text: response.success,
                                buttonsStyling: false,
                                showConfirmButton: false,
                                timer: 1800
                            });
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'No se pudo habilitar el usuario.'
                            });
                        }
                    });
                });
            });

            $("#create-btn").click(function () {
                $("#id-field").val("");
                $("#userForm").trigger("reset");
                $(".modal-title").text("Agregar Usuario");
                $("#add-btn").show();
                $("#edit-btn").hide();
            });

            $("#edit-btn").on("click", function () {
                $("#userForm").submit();
            });

            // Sanitización nombre: solo letras, acentos y espacios
            $(document).on('input', '#field-name', function () {
                this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '');
            });

            // Validación onblur para nombre
            $(document).on('blur', '#field-name', function () {
                let value = $(this).val().trim();
                if (value.length === 0) {
                    marcarInvalido($(this), 'El nombre es obligatorio.');
                } else if (value.length < 2) {
                    marcarInvalido($(this), 'El nombre debe tener al menos 2 caracteres.');
                } else {
                    marcarValido($(this));
                }
            });

            // Validación onblur para email con verificación de duplicado
            $(document).on('blur', '#field-email', function () {
                let value = $(this).val().trim();
                let $input = $(this);
                let emailRegex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;

                if (value.length === 0) {
                    limpiarValidacion($input);
                    return;
                }

                if (!emailRegex.test(value)) {
                    marcarInvalido($input, 'Ingrese un email válido (ej: usuario@dominio.com).');
                    return;
                }

                let excludeId = $('#id-field').val();
                $.ajax({
                    url: "{{ route('users.check-email') }}",
                    type: "GET",
                    data: { email: value, exclude_id: excludeId },
                    success: function (response) {
                        if (response.exists) {
                            marcarInvalido($input, 'Este correo ya está registrado.');
                            $('#add-btn').prop('disabled', true);
                        } else {
                            marcarValido($input);
                            $('#add-btn').prop('disabled', false);
                        }
                    },
                    error: function () {
                        console.error('Error al verificar email');
                    }
                });
            });
        });

        // Exportar PDF — Usuarios
        $('#btn-generar-pdf').on('click', function () {
            var baseUrl = '{{ route('users.reporte.pdf') }}';
            var params = [];
            var role    = $('#pdf-filter-role').val();
            var estatus = $('#pdf-filter-estatus').val();
            var fdesde  = $('#pdf-fecha-desde').val();
            var fhasta  = $('#pdf-fecha-hasta').val();
            if (role)            params.push('role_id=' + encodeURIComponent(role));
            if (estatus !== '')  params.push('estatus=' + encodeURIComponent(estatus));
            if (fdesde)          params.push('fecha_desde=' + encodeURIComponent(fdesde));
            if (fhasta)          params.push('fecha_hasta=' + encodeURIComponent(fhasta));
            window.open(baseUrl + (params.length ? '?' + params.join('&') : ''), '_blank');
            bootstrap.Modal.getInstance(document.getElementById('pdfExportModal'))?.hide();
        });
        $('#pdfExportModal').on('show.bs.modal', function () {
            $('#pdf-filter-role, #pdf-filter-estatus, #pdf-fecha-desde, #pdf-fecha-hasta').val('');
        });
    </script>
@endpush