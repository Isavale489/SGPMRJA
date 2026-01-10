<section>
    <div class="mb-4">
        <h2 class="h4 text-dark">
            {{ __('Actualizar Contraseña') }}
        </h2>

        <p class="text-muted">
            {{ __('Asegúrese de que su cuenta esté utilizando una contraseña larga y aleatoria para mantenerse segura.') }}
        </p>
    </div>

    <form method="post" action="{{ route('password.update') }}" class="needs-validation" novalidate>
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label">
                {{ __('Contraseña Actual') }}
            </label>
            <input 
                id="update_password_current_password" 
                name="current_password" 
                type="password" 
                class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
                autocomplete="current-password"
                required
            >
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label">
                {{ __('Nueva Contraseña') }}
            </label>
            <input 
                id="update_password_password" 
                name="password" 
                type="password" 
                class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
                autocomplete="new-password"
                required
            >
            @error('password', 'updatePassword')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password_confirmation" class="form-label">
                {{ __('Confirmar Contraseña') }}
            </label>
            <input 
                id="update_password_password_confirmation" 
                name="password_confirmation" 
                type="password" 
                class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" 
                autocomplete="new-password"
                required
            >
            @error('password_confirmation', 'updatePassword')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="d-flex align-items-center">
            <button type="submit" class="btn btn-primary me-3">
                {{ __('Guardar') }}
            </button>

            @if (session('status') === 'password-updated')
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
