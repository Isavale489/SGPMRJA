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
            {{-- Nav-pills vertical: un pill por módulo del registry --}}
            <div class="col-lg-3 col-xxl-2">
                <div class="card card-config">
                    <div class="card-body p-2">
                        <div class="nav nav-pills flex-column config-pills" id="config-pills" role="tablist"
                            aria-orientation="vertical">
                            <div class="config-pills-label">Parámetros</div>
                            @foreach ($modulos as $modulo)
                                <button class="nav-link text-start {{ $loop->first ? 'active' : '' }}"
                                    id="pill-{{ $modulo['slug'] }}" data-bs-toggle="pill"
                                    data-bs-target="#mod-{{ $modulo['slug'] }}" data-config-slug="{{ $modulo['slug'] }}"
                                    type="button" role="tab" aria-controls="mod-{{ $modulo['slug'] }}"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    <i class="ri-settings-4-line me-1 align-middle"></i> {{ $modulo['nombre'] }}
                                </button>
                            @endforeach

                            {{-- Accesos a las demás configuraciones del sistema --}}
                            <div class="config-pills-label config-pills-label--separado">Otras configuraciones</div>
                            <a href="{{ url('users') }}" class="nav-link text-start config-pill-link">
                                <i class="ri-group-line me-1 align-middle"></i> Configuración de usuarios
                                <i class="ri-arrow-right-up-line config-pill-link-arrow"></i>
                            </a>
                            <a href="{{ route('profile.edit') }}" class="nav-link text-start config-pill-link">
                                <i class="ri-user-settings-line me-1 align-middle"></i> Configuración de perfil
                                <i class="ri-arrow-right-up-line config-pill-link-arrow"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contenido: un form por módulo, dirigido por el registry --}}
            <div class="col-lg-9 col-xxl-10">
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
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('admin.configuracion.scripts.main')
@endpush
