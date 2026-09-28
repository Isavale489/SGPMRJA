@extends('admin.layouts.app')

@section('title', 'Cambiar contraseña')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-header bg-soft-warning">
                        <h4 class="card-title mb-0 text-warning">
                            <i class="ri-lock-password-line me-1"></i>
                            Cambio de contraseña obligatorio
                        </h4>
                    </div>
                    <div class="card-body">
                        @if (session('warning_force_password'))
                            <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
                                <i class="ri-alert-line fs-5"></i>
                                <span>{{ session('warning_force_password') }}</span>
                            </div>
                        @endif

                        <p class="text-muted mb-4">
                            Un administrador reseteó tu contraseña con una temporal. Por seguridad, debes
                            establecer una contraseña personal antes de continuar.
                        </p>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                            @csrf
                        </form>

                        <form method="POST" action="{{ route('auth.force-password-change.process') }}" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label for="current_password" class="form-label">
                                    Contraseña temporal <span class="text-danger">*</span>
                                </label>
                                <input
                                    id="current_password"
                                    type="password"
                                    name="current_password"
                                    class="form-control"
                                    required
                                    autocomplete="current-password"
                                    placeholder="La que te proporcionó el administrador"
                                >
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    Nueva contraseña <span class="text-danger">*</span>
                                </label>
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    class="form-control"
                                    required
                                    autocomplete="new-password"
                                    minlength="8"
                                    maxlength="72"
                    placeholder="{{ \App\Rules\ContrasenaSegura::DESCRIPCION }}"
                                >
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label">
                                    Confirmar nueva contraseña <span class="text-danger">*</span>
                                </label>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    class="form-control"
                                    required
                                    autocomplete="new-password"
                                    minlength="8"
                                >
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <a href="#" onclick="document.getElementById('logoutForm').submit(); return false;" class="link-secondary">
                                    <i class="ri-logout-box-line me-1"></i>Cerrar sesión
                                </a>

                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i>Guardar y continuar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
