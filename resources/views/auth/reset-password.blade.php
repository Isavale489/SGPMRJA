<x-guest-layout>

    <form method="POST" action="{{ route('password.store') }}" id="resetForm" novalidate>
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bx bx-envelope"></i>
                </span>
                <input
                    id="email"
                    type="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $request->email) }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="correo@empresa.com"
                >
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Nueva contraseña -->
        <div class="mb-3">
            <label for="password" class="form-label">Nueva contraseña</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bx bx-lock-alt"></i>
                </span>
                <div class="pass-wrapper flex-grow-1">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                        aria-describedby="password-ayuda"
                    >
                    <button class="btn-show-pass" type="button" id="togglePassword" tabindex="-1" aria-label="Mostrar contraseña">
                        <i class="bx bx-hide" id="toggleIcon"></i>
                    </button>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div id="password-ayuda" class="form-text">{{ \App\Rules\ContrasenaSegura::DESCRIPCION }}</div>
        </div>

        <!-- Confirmar contraseña -->
        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bx bx-lock-open-alt"></i>
                </span>
                <div class="pass-wrapper flex-grow-1">
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="form-control @error('password_confirmation') is-invalid @enderror"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    >
                    <button class="btn-show-pass" type="button" id="toggleConfirm" tabindex="-1" aria-label="Mostrar confirmación">
                        <i class="bx bx-hide" id="toggleConfirmIcon"></i>
                    </button>
                    @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-atlantico" id="submitBtn">
                <span id="submitText">
                    <i class="bx bx-key me-1"></i>Restablecer contraseña
                </span>
                <span id="submitSpinner" class="d-none">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Guardando…
                </span>
            </button>
        </div>
    </form>

    @push('scripts')
    <script>
        // Toggle nueva contraseña
        document.getElementById('togglePassword').addEventListener('click', function () {
            var input = document.getElementById('password');
            var icon  = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bx-hide', 'bx-show');
            } else {
                input.type = 'password';
                icon.classList.replace('bx-show', 'bx-hide');
            }
        });

        // Toggle confirmar contraseña
        document.getElementById('toggleConfirm').addEventListener('click', function () {
            var input = document.getElementById('password_confirmation');
            var icon  = document.getElementById('toggleConfirmIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bx-hide', 'bx-show');
            } else {
                input.type = 'password';
                icon.classList.replace('bx-show', 'bx-hide');
            }
        });

        // Loading state on submit
        document.getElementById('resetForm').addEventListener('submit', function () {
            var btn     = document.getElementById('submitBtn');
            var text    = document.getElementById('submitText');
            var spinner = document.getElementById('submitSpinner');
            btn.disabled = true;
            text.classList.add('d-none');
            spinner.classList.remove('d-none');
        });
    </script>
    @endpush

</x-guest-layout>
