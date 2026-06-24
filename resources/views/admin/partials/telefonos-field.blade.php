{{--
    Componente reutilizable: gestión de varios teléfonos por persona.
    Uso:  @include('admin.partials.telefonos-field', ['telId' => 'cli-tel'])
    - `telId` (opcional, default 'tel'): prefijo único si hay más de un repeater por página.
    Requiere: public/assets/js/telefonos-repeater.js + estilos .tel-* (custom.css).
    El JS arma los inputs ocultos telefonos[i][numero|tipo|es_principal] al enviar.
--}}
@php($telId = $telId ?? 'tel')

<div class="modal-form-section">
    <div class="modal-form-section-title"><i class="ri-phone-line"></i>Teléfonos</div>

    <div class="tel-repeater" id="{{ $telId }}-repeater" data-tel-repeater data-tel-name="{{ $telId }}-principal">
        <div class="tel-repeater-list" data-tel-list></div>
        <button type="button" class="btn btn-soft-primary btn-sm tel-add-btn" data-tel-add>
            <i class="ri-add-line align-bottom"></i> Agregar teléfono
        </button>
        <div class="tel-repeater-error invalid-feedback d-block" data-tel-error style="display:none;"></div>
    </div>

    {{-- Plantilla de fila (clonada por JS) --}}
    <template data-tel-template>
        <div class="tel-row" data-tel-row>
            <label class="tel-principal" title="Marcar como principal">
                <input type="radio" data-tel-principal>
                <span class="tel-star"><i class="ri-star-fill"></i></span>
            </label>
            <select class="form-select tel-tipo" data-tel-tipo aria-label="Tipo de teléfono">
                @foreach (\App\Models\Telefono::TIPOS as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            <div class="input-group tel-numero-group">
                <select class="form-select tel-prefijo" data-tel-prefijo aria-label="Prefijo">
                    @foreach (['0412', '0422', '0414', '0424', '0416', '0426'] as $pfx)
                        <option value="{{ $pfx }}" @if($pfx === '0424') selected @endif>{{ $pfx }}</option>
                    @endforeach
                </select>
                <input type="text" class="form-control tel-numero" data-tel-numero maxlength="7"
                    inputmode="numeric" placeholder="1234567" aria-label="Número">
            </div>
            <button type="button" class="btn tel-remove" data-tel-remove title="Quitar teléfono">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>
    </template>
</div>
