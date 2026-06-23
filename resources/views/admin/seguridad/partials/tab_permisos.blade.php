{{-- Tab Permisos: selector de rol → grilla de tarjetas por módulo (módulo × acción
     desde config/modulos.php). El Administrador no aparece (acceso total).
     'ver' es prerrequisito del módulo. --}}
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

    {{-- Barra de herramientas: buscar módulo + acciones globales + contador --}}
    <div class="seg-matriz-toolbar">
        <div class="seg-matriz-search">
            <i class="ri-search-line"></i>
            <input type="text" id="seg-mod-search" class="form-control"
                placeholder="Buscar módulo…" autocomplete="off">
        </div>
        <div class="seg-matriz-toolbar-actions">
            <span class="seg-global-count" id="seg-global-count">0 permisos</span>
            <button type="button" class="btn btn-sm btn-light" id="seg-marcar-todo">
                <i class="ri-checkbox-multiple-line align-bottom me-1"></i>Marcar todo
            </button>
            <button type="button" class="btn btn-sm btn-light" id="seg-limpiar-todo">
                <i class="ri-eraser-line align-bottom me-1"></i>Limpiar
            </button>
        </div>
    </div>

    {{-- Grilla de tarjetas: una por módulo --}}
    <div class="seg-matriz-grid" id="seg-matriz-grid">
        @foreach ($modulos as $modulo)
            <div class="seg-mod-card" data-modulo="{{ $modulo['slug'] }}"
                data-nombre="{{ \Illuminate\Support\Str::lower($modulo['nombre']) }}">
                <div class="seg-mod-card-head">
                    <span class="seg-mod-name">{{ $modulo['nombre'] }}</span>
                    <span class="seg-mod-count" title="Acciones otorgadas">0/{{ count($modulo['acciones']) }}</span>
                </div>
                <div class="seg-mod-actions">
                    @foreach ($modulo['acciones'] as $accion => $descripcion)
                        <label class="seg-accion-chip" title="{{ $descripcion }}">
                            <input class="form-check-input seg-perm" type="checkbox"
                                value="{{ $modulo['slug'] }}.{{ $accion }}" data-accion="{{ $accion }}">
                            <span>{{ ucfirst($accion) }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="seg-mod-card-foot">
                    <label class="form-check mb-0 seg-all-wrap" for="seg-all-{{ $modulo['slug'] }}">
                        <input class="form-check-input seg-all" type="checkbox" id="seg-all-{{ $modulo['slug'] }}">
                        <span class="form-check-label">Todo el módulo</span>
                    </label>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Estado vacío del buscador --}}
    <div id="seg-mod-search-empty" class="text-center text-muted py-4 d-none">
        <i class="ri-search-eye-line fs-3 d-block mb-2 opacity-50"></i>
        Ningún módulo coincide con la búsqueda.
    </div>

    {{-- Barra de guardado fija al pie --}}
    <div class="seg-save-bar">
        <span class="text-muted small seg-save-hint">
            <i class="ri-information-line align-bottom me-1"></i>Los cambios se aplican al guardar.
        </span>
        <button type="button" class="btn btn-success" id="seg-guardar-permisos" disabled>
            <i class="ri-save-3-line align-bottom me-1"></i> Guardar permisos
        </button>
    </div>
</div>

<div id="seg-matriz-placeholder" class="text-center text-muted py-5">
    <i class="ri-lock-unlock-line fs-1 d-block mb-2 opacity-50"></i>
    Selecciona un rol para configurar sus permisos.
</div>
