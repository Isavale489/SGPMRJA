@extends('admin.layouts.app')

@section('title', 'Configuración del Sistema')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Configuración del Sistema</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Administración</a></li>
                            <li class="breadcrumb-item active">Configuración</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Nav-pills vertical: un pill por módulo del registry.
                 A la DERECHA (order-lg-2) para no chocar visualmente con el
                 sidebar general del sistema; en móvil queda arriba del contenido. --}}
            <div class="col-lg-3 col-xxl-2 order-lg-2">
                @include('admin.configuracion.partials.nav', ['modo' => 'tabs'])
            </div>

            {{-- Contenido: un form por módulo, dirigido por el registry --}}
            <div class="col-lg-9 col-xxl-10 order-lg-1">
                <div class="tab-content" id="config-content">
                    @foreach ($modulos as $modulo)
                        @php
                            // El módulo pide confirmación si CUALQUIERA de sus
                            // parámetros declara confirmar_guardado en el registry.
                            $confirmacion = collect($modulo['parametros'])
                                ->first(fn ($p) => !empty($p['confirmar_guardado']));
                        @endphp
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="mod-{{ $modulo['slug'] }}"
                            role="tabpanel" aria-labelledby="pill-{{ $modulo['slug'] }}">
                            <div class="card card-config">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="ri-settings-4-line me-1 align-middle"></i> {{ $modulo['nombre'] }}
                                    </h5>
                                </div>
                                <form class="config-form" id="form-{{ $modulo['slug'] }}" novalidate
                                    data-update-url="{{ route('configuracion.update', $modulo['slug']) }}"
                                    @if ($confirmacion) data-confirmar="1"
                                        data-confirmar-mensaje="{{ $confirmacion['mensaje_confirmacion'] ?? '¿Confirmas guardar los cambios de este módulo?' }}" @endif>
                                    <div class="card-body">
                                        @foreach ($modulo['parametros'] as $parametro)
                                            @include('admin.configuracion.partials.campo', [
                                                'parametro' => $parametro,
                                                'modulo' => $modulo,
                                            ])
                                        @endforeach
                                    </div>
                                    <div class="card-footer d-flex justify-content-end">
                                        <button type="submit" class="btn btn-success">
                                            <i class="ri-save-3-line align-bottom me-1"></i> Guardar cambios
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach

                    {{-- ── Impuestos: catálogo gestionable (tabla `impuesto`) ── --}}
                    <div class="tab-pane fade" id="mod-impuestos" role="tabpanel" aria-labelledby="pill-impuestos">
                        <div class="card card-config">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">
                                    <i class="ri-percent-line me-1 align-middle"></i> Impuestos
                                </h5>
                                <button type="button" class="btn btn-sm btn-success" id="btn-nuevo-impuesto">
                                    <i class="ri-add-line align-bottom me-1"></i> Nuevo impuesto
                                </button>
                            </div>
                            <div class="card-body">
                                <p class="cfg-intro small mb-3">
                                    Catálogo central de impuestos. La tasa del IVA aquí definida aplica a las
                                    líneas gravables de las compras nuevas;<br>
                                    las compras ya registradas conservan su porcentaje original (snapshot).
                                </p>
                                <div class="table-responsive">
                                    <table class="table table-nowrap align-middle mb-0 table-admin" id="tabla-impuestos">
                                        <thead>
                                            <tr>
                                                <th>Código</th>
                                                <th>Nombre</th>
                                                <th class="text-end">Porcentaje</th>
                                                <th>Estado</th>
                                                <th class="text-end">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($impuestos as $imp)
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-light text-dark">{{ $imp->codigo }}</span>
                                                        @if ($imp->codigo === \App\Models\Impuesto::CODIGO_IVA)
                                                            <i class="ri-lock-2-line text-muted ms-1 align-middle"
                                                                title="Tasa base de las compras — no se puede eliminar"></i>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="fw-medium">{{ $imp->nombre }}</span>
                                                        @if ($imp->descripcion)
                                                            <div class="imp-desc-line text-muted">{{ $imp->descripcion }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="text-end fw-semibold fs-15">{{ rtrim(rtrim(number_format($imp->porcentaje, 2), '0'), '.') }}%</td>
                                                    <td>
                                                        @if ($imp->estado === 'activo')
                                                            <span class="badge bg-success-subtle text-success">Activo</span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary">Inactivo</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        <button type="button"
                                                            class="btn btn-sm btn-soft-primary btn-editar-impuesto"
                                                            data-id="{{ $imp->id }}"
                                                            data-codigo="{{ $imp->codigo }}"
                                                            data-nombre="{{ $imp->nombre }}"
                                                            data-porcentaje="{{ $imp->porcentaje }}"
                                                            data-descripcion="{{ $imp->descripcion }}"
                                                            data-estado="{{ $imp->estado }}"
                                                            data-es-iva="{{ $imp->codigo === \App\Models\Impuesto::CODIGO_IVA ? '1' : '0' }}"
                                                            data-update-url="{{ route('impuestos.update', $imp) }}"
                                                            title="Editar">
                                                            <i class="ri-pencil-line"></i>
                                                        </button>
                                                        @if ($imp->codigo !== \App\Models\Impuesto::CODIGO_IVA)
                                                            <button type="button"
                                                                class="btn btn-sm btn-soft-danger btn-eliminar-impuesto"
                                                                data-nombre="{{ $imp->nombre }}"
                                                                data-delete-url="{{ route('impuestos.destroy', $imp) }}"
                                                                title="Eliminar">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">
                                                        No hay impuestos registrados.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.configuracion.partials.impuesto-modal')
@endsection

@push('scripts')
    @include('admin.configuracion.scripts.main')
@endpush
