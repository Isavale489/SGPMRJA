<x-guest-layout>
    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input 
                id="email" 
                type="email" 
                name="email" 
                class="form-control form-control-lg border border-1 @error('email') is-invalid @else needs-validation @enderror" 
                value="{{ old('email') }}" 
                required 
                autofocus 
                autocomplete="username"
                placeholder="{{ __('Ingrese su correo electrónico') }}"
                style="border-color: #ced4da !important;"
            >
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @else
                <div class="valid-feedback">
                    {{ __('¡Se ve bien!') }}
                </div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label">{{ __('Password') }}</label>
            <input 
                id="password" 
                type="password" 
                name="password" 
                class="form-control form-control-lg border border-1 @error('password') is-invalid @else needs-validation @enderror" 
                required 
                autocomplete="current-password"
                placeholder="{{ __('Ingrese su contraseña') }}" 
                style="border-color: #ced4da !important;"
            >
            @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @else
                <div class="valid-feedback">
                    {{ __('¡Se ve bien!') }}
                </div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="mb-3 form-check">
            <input 
                id="remember_me" 
                type="checkbox" 
                name="remember" 
                class="form-check-input"
            >
            <label for="remember_me" class="form-check-label">
                {{ __('Recuérdame') }}
            </label>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            @if (Route::has('password.request'))
                <a 
                    href="{{ route('password.request') }}" 
                    class="text-decoration-none text-primary"
                >
                    {{ __('¿Olvidaste tu contraseña?') }}
                </a>
            @endif

            <button type="submit" class="btn btn-primary btn-lg">
                {{ __('Iniciar Sesión') }}
            </button>
        </div>
    </form>

    <script>
        // Bootstrap form validation script
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                // Fetch all the forms we want to apply custom Bootstrap validation styles to
                var forms = document.getElementsByClassName('needs-validation');
                
                // Loop over them and prevent submission
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
</x-guest-layout>
