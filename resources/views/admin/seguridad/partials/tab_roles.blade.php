{{-- Tab Roles: tabla simple (son pocos) + alta/edición vía modal atlantico-modal --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="mb-1">Roles del sistema</h5>
        <p class="text-muted mb-0 small">
            Los roles <strong>de sistema</strong> (Administrador, Supervisor) no se renombran ni eliminan.
            El Administrador tiene acceso total. Crea roles a medida y asígnales permisos en la pestaña
            <em>Permisos</em>.
        </p>
    </div>
    <button type="button" class="btn btn-success add-btn flex-shrink-0" id="seg-nuevo-rol-btn">
        <i class="ri-add-line align-bottom me-1"></i> Nuevo rol
    </button>
</div>

<div class="table-responsive">
    <table class="table align-middle table-nowrap mb-0 seg-roles-table">
        <thead class="table-light">
            <tr>
                <th>Rol</th>
                <th>Descripción</th>
                <th class="text-center">Usuarios</th>
                <th class="text-center" style="width: 120px;">Acciones</th>
            </tr>
        </thead>
        <tbody id="seg-roles-tbody">
            @foreach ($roles as $rol)
                @php $esAdmin = $rol->es_sistema && $rol->nombre === 'Administrador'; @endphp
                <tr data-rol-id="{{ $rol->id }}"
                    data-rol-nombre="{{ $rol->nombre }}"
                    data-rol-descripcion="{{ $rol->descripcion }}"
                    data-es-sistema="{{ $rol->es_sistema ? '1' : '0' }}"
                    data-usuarios="{{ $rol->usuarios_count }}">
                    <td>
                        <span class="fw-medium seg-rol-nombre">{{ $rol->nombre }}</span>
                        @if ($rol->es_sistema)
                            <span class="badge bg-primary-subtle text-primary ms-1">Sistema</span>
                        @endif
                    </td>
                    <td class="text-muted seg-rol-desc">{{ $rol->descripcion ?: '—' }}</td>
                    <td class="text-center">
                        <span class="badge bg-light text-body seg-rol-usuarios">{{ $rol->usuarios_count }}</span>
                    </td>
                    <td class="text-center">
                        @if ($esAdmin)
                            <span class="badge bg-light text-muted">Acceso total</span>
                        @else
                            @php
                                $motivoBloqueo = $rol->es_sistema ? 'sistema' : ($rol->usuarios_count > 0 ? 'usuarios' : '');
                            @endphp
                            <div class="d-inline-flex gap-1">
                                @if (!$rol->es_sistema)
                                    <button type="button" class="btn btn-sm btn-soft-primary seg-edit-rol"
                                        title="Editar"><i class="ri-pencil-fill"></i></button>
                                @endif
                                <button type="button"
                                    class="btn btn-sm btn-soft-danger seg-del-rol{{ $motivoBloqueo ? ' is-blocked' : '' }}"
                                    data-motivo="{{ $motivoBloqueo }}"
                                    title="{{ $motivoBloqueo === 'sistema' ? 'Rol de sistema (no eliminable)' : ($motivoBloqueo === 'usuarios' ? 'Tiene usuarios asignados' : 'Eliminar') }}">
                                    <i class="ri-delete-bin-fill"></i>
                                </button>
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
