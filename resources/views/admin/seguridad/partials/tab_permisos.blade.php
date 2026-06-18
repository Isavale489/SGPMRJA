{{-- Tab Permisos: selector de rol → matriz módulo × acción (desde config/modulos.php).
     El Administrador no aparece (acceso total). 'ver' es prerrequisito del módulo. --}}
@php
    // Roles editables en la matriz: todos menos el Administrador (acceso total).
    $rolesEditables = $roles->reject(fn ($r) => $r->es_sistema && $r->nombre === 'Administrador');
@endphp

<div class="row g-3 align-items-end mb-3">
    <div class="col-sm-6 col-lg-4">
        <label for="seg-rol-select" class="form-label">Rol a configurar</label>
        <select class="form-select" id="seg-rol-select">
            <option value="">— Selecciona un rol —</option>
            @foreach ($rolesEditables as $rol)
                <option value="{{ $rol->id }}">
                    {{ $rol->nombre }}@if ($rol->es_sistema) (sistema)@endif
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-6 col-lg-8">
        <div class="alert alert-info d-flex align-items-center mb-0 py-2 seg-admin-nota">
            <i class="ri-information-line fs-18 me-2"></i>
            <span class="small">
                El <strong>Administrador</strong> tiene <strong>acceso total</strong> a todo el sistema y no es
                configurable. Marcar cualquier acción de un módulo activa automáticamente <em>Ver</em>.
            </span>
        </div>
    </div>
</div>

<div id="seg-matriz-wrapper" class="seg-matriz-wrapper d-none">
    <div class="table-responsive">
        <table class="table table-bordered align-middle seg-matriz mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 28%;">Módulo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($modulos as $modulo)
                    <tr data-modulo="{{ $modulo['slug'] }}">
                        <td>
                            <div class="fw-medium">{{ $modulo['nombre'] }}</div>
                            <div class="form-check form-check-sm mt-1">
                                <input class="form-check-input seg-all" type="checkbox"
                                    id="seg-all-{{ $modulo['slug'] }}">
                                <label class="form-check-label text-muted small" for="seg-all-{{ $modulo['slug'] }}">
                                    Todo el módulo
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($modulo['acciones'] as $accion => $descripcion)
                                    <label class="seg-accion-chip" title="{{ $descripcion }}">
                                        <input class="form-check-input seg-perm" type="checkbox"
                                            value="{{ $modulo['slug'] }}.{{ $accion }}"
                                            data-accion="{{ $accion }}">
                                        <span>{{ ucfirst($accion) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-end mt-3">
        <button type="button" class="btn btn-success" id="seg-guardar-permisos" disabled>
            <i class="ri-save-3-line align-bottom me-1"></i> Guardar permisos
        </button>
    </div>
</div>

<div id="seg-matriz-placeholder" class="text-center text-muted py-5">
    <i class="ri-lock-unlock-line fs-1 d-block mb-2 opacity-50"></i>
    Selecciona un rol para configurar sus permisos.
</div>
