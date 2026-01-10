@extends('admin.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ __('Perfil') }}</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-xxl-6 col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title mb-0">Actualizar información de perfil</h4>
                                    </div>
                                    <div class="card-body">
                                        @include('profile.partials.update-profile-information-form')
                                    </div>
                                </div>
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title mb-0">Actualizar contraseña</h4>
                                    </div>
                                    <div class="card-body">
                                        @include('profile.partials.update-password-form')
                                    </div>
                                </div>
                            </div>

                            <div class="col-xxl-12 col-md-12">
                                <div class="card border-danger">
                                    <div class="card-header bg-soft-danger">
                                        <h4 class="card-title mb-0 text-danger">Eliminar cuenta</h4>
                                    </div>
                                    <div class="card-body">
                                        @include('profile.partials.delete-user-form')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
