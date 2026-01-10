<section>
    <div class="mb-4">
        <h2 class="h4 text-dark">
            {{ __('Información del Perfil') }}
        </h2>

        <p class="text-muted">
            {{ __("Actualice la información de su perfil y dirección de correo electrónico.") }}
        </p>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="needs-validation" novalidate>
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label">{{ __('Nombre') }}</label>
            <input 
                id="name" 
                name="name" 
                type="text" 
                class="form-control @error('name') is-invalid @enderror" 
                value="{{ old('name', $user->name) }}" 
                required 
                autofocus 
                autocomplete="name"
            >
            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Correo Electrónico') }}</label>
            <input 
                id="email" 
                name="email" 
                type="email" 
                class="form-control @error('email') is-invalid @enderror" 
                value="{{ old('email', $user->email) }}" 
                required 
                autocomplete="username"
            >
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="alert alert-warning mt-2">
                    <p class="mb-0">
                        {{ __('Su dirección de correo electrónico no está verificada.') }}

                        <button 
                            form="send-verification" 
                            class="btn btn-link p-0 m-0 align-baseline"
                        >
                            {{ __('Haga clic aquí para reenviar el correo de verificación.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="text-success mt-2 mb-0">
                            {{ __('Se ha enviado un nuevo enlace de verificación a su dirección de correo electrónico.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center">
            <button type="submit" class="btn btn-primary me-3">
                {{ __('Guardar') }}
            </button>

            @if (session('status') === 'profile-updated')
                <div 
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-success"
                >
                    {{ __('Guardado.') }}
                </div>
            @endif
        </div>
    </form>
</section>

<script>
// Validación de formulario Bootstrap
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>
