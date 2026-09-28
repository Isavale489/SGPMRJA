<x-guest-layout title="Nueva contraseña" icon="bx-lock-alt">

    <div class="recovery-verified-badge mb-4">
        <span class="recovery-verified-icon"><i class="bx bx-check-shield"></i></span>
        <div>
            <div class="recovery-verified-title">Identidad verificada</div>
            <div class="recovery-verified-sub">Establece tu nueva contraseña a continuación.</div>
        </div>
    </div>

    <form method="POST" action="{{ route('recovery.reset.process') }}" id="recoveryResetForm" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="recovery-q-block mb-3">
            <div class="recovery-q-header">
                <span class="recovery-q-num"><i class="bx bx-lock-alt" style="font-size:.7rem;"></i></span>
                <label for="password" class="recovery-q-text mb-0">Nueva contraseña</label>
            </div>
            <div class="input-group pass-wrapper">
                <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    required
                    autocomplete="new-password"
                    minlength="8"
                    maxlength="72"
                    placeholder="{{ \App\Rules\ContrasenaSegura::DESCRIPCION }}"
                >
                <button type="button" class="btn-show-pass" data-target="password" title="Mostrar/ocultar">
                    <i class="bx bx-show"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="recovery-q-block mb-4">
            <div class="recovery-q-header">
                <span class="recovery-q-num"><i class="bx bx-lock" style="font-size:.7rem;"></i></span>
                <label for="password_confirmation" class="recovery-q-text mb-0">Confirmar contraseña</label>
            </div>
            <div class="input-group pass-wrapper">
                <span class="input-group-text"><i class="bx bx-lock"></i></span>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    class="form-control"
                    required
                    autocomplete="new-password"
                    minlength="8"
                    placeholder="Repite la contraseña"
                >
                <button type="button" class="btn-show-pass" data-target="password_confirmation" title="Mostrar/ocultar">
                    <i class="bx bx-show"></i>
                </button>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-atlantico" id="submitBtn">
                <span id="submitText">
                    <i class="bx bx-save me-1"></i>Guardar nueva contraseña
                </span>
                <span id="submitSpinner" class="d-none">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Guardando…
                </span>
            </button>
        </div>
    </form>

    @push('scripts')
    <style>
        .recovery-verified-badge {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .75rem 1rem;
            border: 1px solid rgba(16, 185, 129, 0.25);
            border-left: 3px solid #10b981;
            border-radius: 8px;
            background: rgba(16, 185, 129, 0.07);
        }
        [data-bs-theme="dark"] .recovery-verified-badge {
            border-color: rgba(52, 211, 153, 0.2);
            border-left-color: #34d399;
            background: rgba(52, 211, 153, 0.06);
        }
        .recovery-verified-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px; height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #064e3b, #10b981);
            color: #fff;
            font-size: 1.1rem;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35);
        }
        .recovery-verified-title {
            font-weight: 600;
            font-size: .9rem;
            color: #065f46;
        }
        .recovery-verified-sub {
            font-size: .8rem;
            color: #6b7280;
        }
        [data-bs-theme="dark"] .recovery-verified-title { color: #6ee7b7; }
        [data-bs-theme="dark"] .recovery-verified-sub   { color: #9ca3af; }

        .recovery-q-block {
            padding: .85rem 1rem 1rem;
            border: 1px solid rgba(30, 60, 114, 0.12);
            border-left: 3px solid #1e3c72;
            border-radius: 8px;
            background: rgba(30, 60, 114, 0.025);
            transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
        }
        .recovery-q-block:focus-within {
            border-color: rgba(30, 60, 114, 0.40);
            background: rgba(30, 60, 114, 0.04);
            box-shadow: 0 2px 12px rgba(30, 60, 114, 0.10);
        }
        [data-bs-theme="dark"] .recovery-q-block {
            border-color: rgba(96, 165, 250, 0.15);
            border-left-color: #60a5fa;
            background: rgba(96, 165, 250, 0.04);
        }
        [data-bs-theme="dark"] .recovery-q-block:focus-within {
            border-color: rgba(96, 165, 250, 0.45);
            background: rgba(96, 165, 250, 0.07);
        }
        .recovery-q-header {
            display: flex;
            align-items: center;
            gap: .55rem;
            margin-bottom: .5rem;
        }
        .recovery-q-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px; height: 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: #fff;
            font-size: .75rem;
            font-weight: 700;
            flex-shrink: 0;
            box-shadow: 0 2px 4px rgba(30,60,114,0.30);
        }
        .recovery-q-text {
            font-weight: 500;
            font-size: .9rem;
            color: #1f2937;
            line-height: 1.35;
        }
        [data-bs-theme="dark"] .recovery-q-text { color: #e5e7eb; }
    </style>
    <script>
        document.querySelectorAll('.btn-show-pass[data-target]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(this.dataset.target);
                var icon  = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('bx-show', 'bx-hide');
                } else {
                    input.type = 'password';
                    icon.classList.replace('bx-hide', 'bx-show');
                }
            });
        });

        document.getElementById('recoveryResetForm').addEventListener('submit', function () {
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
