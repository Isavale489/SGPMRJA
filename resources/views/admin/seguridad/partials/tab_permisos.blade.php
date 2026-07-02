{{-- Tab Permisos: selector de rol → matriz agrupada por sección (espejo del
     sidebar) con tarjetas por módulo (módulo × acción desde config/modulos.php).
     El Administrador no aparece (acceso total). 'ver' es prerrequisito del módulo.
     El guardado es explícito: el botón se habilita solo con cambios pendientes y
     cambiar de rol con cambios sin guardar dispara el guard Guardar/Descartar. --}}
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
                <option value="{{ $rol->id }}"
                    data-descripcion="{{ $rol->descripcion }}"
                    data-usuarios="{{ $rol->usuarios_count }}"
                    data-sistema="{{ $rol->es_sistema ? 1 : 0 }}">
                    {{ $rol->nombre }}@if ($rol->es_sistema) (sistema)@endif
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-6 col-lg-8">
        <div class="seg-admin-nota">
            <span class="seg-admin-nota-ic"><i class="ri-information-line"></i></span>
            <span class="seg-admin-nota-text">
                El <strong>Administrador</strong> tiene <strong>acceso total</strong> a todo el sistema y no es configurable.<br>
                Marcar cualquier acción de un módulo activa automáticamente <em>Ver</em>.
            </span>
        </div>
    </div>
</div>

<div id="seg-matriz-wrapper" class="seg-matriz-wrapper d-none">

    {{-- Resumen del rol en edición: nombre + chips (usuarios, sistema) + descripción --}}
    <div class="seg-rol-resumen" id="seg-rol-resumen">
        <span class="seg-rol-resumen-ic"><i class="ri-shield-user-line"></i></span>
        <div class="seg-rol-resumen-main">
            <div class="seg-rol-resumen-top">
                <span class="seg-rol-resumen-nombre" id="seg-res-nombre">—</span>
                <span class="badge bg-primary-subtle text-primary d-none" id="seg-res-sistema">Rol de sistema</span>
                <span class="seg-rol-resumen-chip" id="seg-res-usuarios">0 usuarios</span>
            </div>
            <div class="seg-rol-resumen-desc" id="seg-res-desc"></div>
        </div>
    </div>

    {{-- Barra de herramientas: buscar módulo + acciones globales + contador --}}
    <div class="seg-matriz-toolbar">
        <div class="seg-matriz-search">
            <i class="ri-search-line"></i>
            <input type="text" id="seg-mod-search" class="form-control"
                placeholder="Buscar módulo…" autocomplete="off">
        </div>
        <div class="seg-matriz-toolbar-actions">
            <span class="seg-global-count" id="seg-global-count">0 permisos</span>
            {{-- Copiar permisos de otro rol: precarga la matriz (queda como cambio sin guardar) --}}
            <div class="dropdown" id="seg-copiar-wrap">
                <button type="button" class="btn btn-sm btn-light dropdown-toggle" id="seg-copiar-btn"
                    data-bs-toggle="dropdown" aria-expanded="false"
                    title="Precargar la matriz con los permisos de otro rol">
                    <i class="ri-file-copy-2-line align-bottom me-1"></i>Copiar de…
                </button>
                <ul class="dropdown-menu dropdown-menu-end" id="seg-copiar-menu"></ul>
            </div>
            <button type="button" class="btn btn-sm btn-light" id="seg-solo-ver"
                title="Dejar únicamente la acción Ver en todos los módulos visibles (rol de consulta)">
                <i class="ri-eye-line align-bottom me-1"></i>Solo Ver
            </button>
            <button type="button" class="btn btn-sm btn-light" id="seg-marcar-todo">
                <i class="ri-checkbox-multiple-line align-bottom me-1"></i>Marcar todo
            </button>
            <button type="button" class="btn btn-sm btn-light" id="seg-limpiar-todo">
                <i class="ri-eraser-line align-bottom me-1"></i>Limpiar
            </button>
        </div>
    </div>

    {{-- Matriz agrupada por sección (misma organización que el sidebar) --}}
    <div id="seg-matriz-grid" class="seg-secciones">
        @foreach ($modulos as $seccion => $def)
            <section class="seg-seccion seg-sec-{{ $def['tema'] }}" data-seccion="{{ \Illuminate\Support\Str::slug($seccion) }}">
                <div class="seg-seccion-head">
                    <i class="{{ $def['icono'] }} seg-seccion-ic"></i>
                    <span class="seg-seccion-title">{{ $seccion }}</span>
                    <span class="seg-seccion-count" data-seccion-count>0/0</span>
                    <span class="seg-seccion-line"></span>
                    <div class="seg-seccion-tools">
                        <button type="button" class="seg-seccion-btn seg-sec-marcar" title="Marcar toda la sección">
                            <i class="ri-checkbox-multiple-line"></i>
                        </button>
                        <button type="button" class="seg-seccion-btn seg-sec-limpiar" title="Limpiar la sección">
                            <i class="ri-eraser-line"></i>
                        </button>
                    </div>
                </div>
                <div class="seg-matriz-grid">
                    @foreach ($def['modulos'] as $modulo)
                        <div class="seg-mod-card" data-modulo="{{ $modulo['slug'] }}"
                            data-nombre="{{ \Illuminate\Support\Str::lower($modulo['nombre']) }}">
                            <div class="seg-mod-card-head">
                                <span class="seg-mod-ic"><i class="{{ $modulo['icono'] }}"></i></span>
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
            </section>
        @endforeach
    </div>

    {{-- Estado vacío del buscador --}}
    <div id="seg-mod-search-empty" class="text-center text-muted py-4 d-none">
        <i class="ri-search-eye-line fs-3 d-block mb-2 opacity-50"></i>
        Ningún módulo coincide con la búsqueda.
    </div>

    {{-- Barra de guardado fija al pie --}}
    <div class="seg-save-bar">
        <span class="text-muted small seg-save-hint" id="seg-save-hint">
            <i class="ri-information-line align-bottom me-1"></i>Los cambios se aplican al guardar.
        </span>
        <span class="seg-cambios-chip d-none" id="seg-cambios-chip"></span>
        <button type="button" class="btn btn-success" id="seg-guardar-permisos" disabled>
            <i class="ri-save-3-line align-bottom me-1"></i> Guardar permisos
        </button>
    </div>
</div>

<div id="seg-matriz-placeholder" class="text-center text-muted py-5">
    <i class="ri-lock-unlock-line fs-1 d-block mb-2 opacity-50"></i>
    Selecciona un rol para configurar sus permisos.
</div>
