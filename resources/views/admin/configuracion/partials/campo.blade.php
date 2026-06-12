{{--
    Render de UN parámetro del registry según su tipo.
    Recibe: $parametro (definición + clave, valor, es_default), $modulo.
    La vista no conoce parámetros concretos: todo sale del registry.
--}}
@php
    $campoId   = 'campo-' . str_replace('.', '-', $parametro['clave']);
    $reglas    = $parametro['reglas'] ?? '';
    $requerido = str_contains($reglas, 'required');

    $min = preg_match('/(?:^|\|)min:([\d.]+)/', $reglas, $m) ? $m[1] : null;
    $max = preg_match('/(?:^|\|)max:([\d.]+)/', $reglas, $m) ? $m[1] : null;

    // Valor por defecto del parámetro (para el badge y el botón restablecer)
    $valorDefault = $parametro['default'] ?? (isset($parametro['config_key']) ? config($parametro['config_key']) : null);
    if ($parametro['tipo'] === 'booleano') {
        $valorDefaultLegible = filter_var($valorDefault, FILTER_VALIDATE_BOOLEAN) ? 'Sí' : 'No';
    } else {
        $valorDefaultLegible = $valorDefault;
    }

    $resetUrl = route('configuracion.reset', [$modulo['slug'], $parametro['clave']]);
@endphp

<div class="config-campo" data-clave="{{ $parametro['clave'] }}" data-tipo="{{ $parametro['tipo'] }}">
    <div class="d-flex align-items-center justify-content-between mb-1">
        <label class="form-label mb-0" for="{{ $campoId }}">
            {{ $parametro['nombre'] }}
            @if ($requerido)
                <span class="text-danger">*</span>
            @endif
        </label>
        <div class="d-flex align-items-center gap-2">
            <span class="badge config-badge-default {{ $parametro['es_default'] ? '' : 'd-none' }}">
                Por defecto: {{ $valorDefaultLegible }}
            </span>
            <button type="button"
                class="btn btn-sm btn-soft-secondary config-reset-btn {{ $parametro['es_default'] ? 'd-none' : '' }}"
                data-reset-url="{{ $resetUrl }}" data-default="{{ $valorDefault }}"
                title="Volver al valor por defecto ({{ $valorDefaultLegible }})">
                <i class="ri-arrow-go-back-line align-bottom"></i> Restablecer
            </button>
        </div>
    </div>

    @switch($parametro['tipo'])
        @case('decimal')
        @case('entero')
            <input type="number" class="form-control config-input" id="{{ $campoId }}"
                value="{{ $parametro['valor'] }}" step="{{ $parametro['tipo'] === 'entero' ? '1' : '0.01' }}"
                @if (!is_null($min)) min="{{ $min }}" @endif
                @if (!is_null($max)) max="{{ $max }}" @endif
                @if ($requerido) data-requerido="1" @endif>
            @break

        @case('booleano')
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input config-input" id="{{ $campoId }}" role="switch"
                    {{ filter_var($parametro['valor'], FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
            </div>
            @break

        @default
            <input type="text" class="form-control config-input" id="{{ $campoId }}" value="{{ $parametro['valor'] }}"
                @if (!is_null($max)) maxlength="{{ $max }}" @endif
                @if ($requerido) data-requerido="1" @endif>
    @endswitch

    <div class="invalid-feedback"></div>

    @if (!empty($parametro['descripcion']))
        <p class="config-help text-muted mb-0 mt-1">
            <i class="ri-information-line align-bottom"></i> {{ $parametro['descripcion'] }}
        </p>
    @endif
</div>
