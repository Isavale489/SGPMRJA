<section class="delete-account-section">
    <div class="mb-4">
        <h2 class="h4 text-danger">
            {{ __('Eliminar Cuenta') }}
        </h2>

        <p class="text-muted">
            {{ __('Una vez que se elimine su cuenta, todos sus recursos y datos se eliminarán permanentemente. Antes de eliminar su cuenta, descargue cualquier dato o información que desee conservar.') }}
        </p>
    </div>

    <button 
        type="button" 
        class="btn btn-danger" 
        data-bs-toggle="modal" 
        data-bs-target="#confirmUserDeletionModal"
    >
        {{ __('Eliminar Cuenta') }}
    </button>

    <!-- Modal -->
    <div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel" aria-hidden="true"
        x-data="{ show: {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }} }"
        x-show="show"
        x-init="() => {
            if (show) {
                new bootstrap.Modal(document.getElementById('confirmUserDeletionModal')).show();
            }
        }"
    >
        <div class="modal-dialog">
            <form method="post" action="{{ route('profile.destroy') }}" class="modal-content needs-validation" novalidate>
                @csrf
                @method('delete')

                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="confirmUserDeletionModalLabel">
                        {{ __('¿Está seguro que desea eliminar su cuenta?') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <p class="text-muted mb-4">
                        {{ __('Una vez que se elimine su cuenta, todos sus recursos y datos se eliminarán permanentemente. Ingrese su contraseña para confirmar que desea eliminar permanentemente su cuenta.') }}
                    </p>

                    <div class="mb-3">
                        <label for="password" class="form-label visually-hidden">
                            {{ __('Contraseña') }}
                        </label>
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            class="form-control @error('password', 'userDeletion') is-invalid @enderror" 
                            placeholder="{{ __('Contraseña') }}"
                            required
                        >
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('Cancelar') }}
                    </button>
                    <button type="submit" class="btn btn-danger">
                        {{ __('Eliminar Cuenta') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

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
</section>
