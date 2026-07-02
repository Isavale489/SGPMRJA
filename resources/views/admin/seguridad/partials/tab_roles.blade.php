{{-- Tab Roles: grilla de tarjetas (son pocos) + alta/edición vía modal atlantico-modal.
     Cada tarjeta muestra identidad (ícono/nombre/badge sistema), descripción, métricas
     (usuarios y permisos) y acciones — incluida "Configurar permisos", que salta a la
     pestaña Permisos con el rol ya seleccionado. --}}
<div class="d-flex align-items-center justify-content-between gap-3 mb-3">
    <div>
        <h5 class="mb-1">Roles del sistema</h5>
        <p class="cfg-intro mb-0 small">
            Los roles <strong>de sistema</strong> (Administrador, Supervisor) no se renombran ni eliminan.<br>
            Crea roles a medida y asígnales permisos en la pestaña <em>Permisos</em>.
        </p>
    </div>
    <button type="button" class="btn btn-success add-btn flex-shrink-0" id="seg-nuevo-rol-btn">
        <i class="ri-add-line align-bottom me-1"></i> Nuevo rol
    </button>
</div>

<div class="seg-roles-grid" id="seg-roles-grid">
    @foreach ($roles as $rol)
        @php $esAdmin = $rol->es_sistema && $rol->nombre === 'Administrador'; @endphp
        <div class="seg-rol-card{{ $esAdmin ? ' seg-rol-card--admin' : '' }}"
            data-rol-id="{{ $rol->id }}"
            data-rol-nombre="{{ $rol->nombre }}"
            data-rol-descripcion="{{ $rol->descripcion }}"
            data-es-sistema="{{ $rol->es_sistema ? '1' : '0' }}"
            data-usuarios="{{ $rol->usuarios_count }}">
            <div class="seg-rol-card-head">
                <span class="seg-rol-card-ic">
                    <i class="{{ $esAdmin ? 'ri-shield-star-line' : ($rol->es_sistema ? 'ri-shield-user-line' : 'ri-user-settings-line') }}"></i>
                </span>
                <div class="seg-rol-card-id">
                    <span class="seg-rol-card-nombre seg-rol-nombre">{{ $rol->nombre }}</span>
                    @if ($rol->es_sistema)
                        <span class="badge bg-primary-subtle text-primary">Sistema</span>
                    @endif
                </div>
            </div>
            <p class="seg-rol-card-desc seg-rol-desc{{ $rol->descripcion ? '' : ' is-empty' }}">
                {{ $rol->descripcion ?: 'Sin descripción.' }}
            </p>
            <div class="seg-rol-card-meta">
                <span class="seg-rol-meta-chip">
                    <i class="ri-group-line"></i>
                    <span class="seg-rol-usuarios">{{ $rol->usuarios_count }}</span>
                    usuario{{ $rol->usuarios_count === 1 ? '' : 's' }}
                </span>
                @if ($esAdmin)
                    <span class="seg-rol-meta-chip seg-rol-meta-chip--total">
                        <i class="ri-vip-crown-line"></i> Acceso total
                    </span>
                @else
                    <span class="seg-rol-meta-chip">
                        <i class="ri-key-2-line"></i>
                        <span class="seg-rol-permisos">{{ $rol->permisos_count }} permiso{{ $rol->permisos_count === 1 ? '' : 's' }}</span>
                    </span>
                @endif
            </div>
            <div class="seg-rol-card-foot">
                @if ($esAdmin)
                    <span class="seg-rol-foot-nota">
                        <i class="ri-lock-2-line me-1"></i>Siempre tiene todos los permisos.
                    </span>
                @else
                    <button type="button" class="btn btn-sm btn-soft-success seg-config-rol">
                        <i class="ri-lock-2-line align-bottom me-1"></i>Configurar permisos
                    </button>
                @endif
                <div class="d-inline-flex gap-1 ms-auto">
                    <button type="button" class="btn btn-sm btn-soft-primary seg-edit-rol"
                        title="{{ $rol->es_sistema ? 'Editar descripción' : 'Editar' }}">
                        <i class="ri-pencil-fill"></i>
                    </button>
                    @unless ($esAdmin)
                        @php
                            $motivoBloqueo = $rol->es_sistema ? 'sistema' : ($rol->usuarios_count > 0 ? 'usuarios' : '');
                        @endphp
                        <button type="button"
                            class="btn btn-sm btn-soft-danger seg-del-rol{{ $motivoBloqueo ? ' is-blocked' : '' }}"
                            data-motivo="{{ $motivoBloqueo }}"
                            title="{{ $motivoBloqueo === 'sistema' ? 'Rol de sistema (no eliminable)' : ($motivoBloqueo === 'usuarios' ? 'Tiene usuarios asignados' : 'Eliminar') }}">
                            <i class="ri-delete-bin-fill"></i>
                        </button>
                    @endunless
                </div>
            </div>
        </div>
    @endforeach
</div>
