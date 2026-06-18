@extends('admin.layouts.app')

@section('title', 'Configuración de seguridad')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Configuración de seguridad</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Administración</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('configuracion.index') }}">Configuración</a></li>
                            <li class="breadcrumb-item active">Seguridad</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Nav lateral compartido con la página de Configuración (a la derecha en desktop) --}}
            <div class="col-lg-3 col-xxl-2 order-lg-2">
                @include('admin.configuracion.partials.nav', ['modo' => 'links', 'activo' => 'seguridad'])
            </div>

            {{-- Contenido: tabs Roles + Permisos --}}
            <div class="col-lg-9 col-xxl-10 order-lg-1">
                <div class="card card-config seg-card">
                    <div class="card-header p-0">
                        <ul class="nav nav-tabs nav-tabs-custom seg-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#seg-tab-roles" role="tab">
                                    <i class="ri-shield-user-line me-1 align-middle"></i> Roles
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#seg-tab-permisos" role="tab">
                                    <i class="ri-lock-2-line me-1 align-middle"></i> Permisos
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="seg-tab-roles" role="tabpanel">
                                @include('admin.seguridad.partials.tab_roles')
                            </div>
                            <div class="tab-pane fade" id="seg-tab-permisos" role="tabpanel">
                                @include('admin.seguridad.partials.tab_permisos')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.seguridad.partials.modal_rol')
@endsection

@push('scripts')
    @include('admin.seguridad.scripts.main')
@endpush
