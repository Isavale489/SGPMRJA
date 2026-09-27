{{--
    Nav-pills lateral compartido del panel de Configuración (FEAT-004/FEAT-005).
    Reutilizado por la página de Configuración del Sistema y por Seguridad, para
    que ambas compartan el mismo shell de dos columnas.

    Parámetros:
      $modo   'tabs'  → los módulos de parámetros son pills que togglean tab-panes
                        en la MISMA página (página de Configuración del Sistema).
              'links' → son enlaces a configuracion.index#slug (otras páginas).
      $activo  cuál ítem de "Otras configuraciones" resaltar:
               'usuarios' | 'perfil' | 'seguridad' | null.
--}}
@php
    use Illuminate\Support\Str;

    $modo   = $modo   ?? 'tabs';
    $activo = $activo ?? null;

    // Módulos de parámetros desde el registry (mismo orden que la página de config).
    $paramModulos = collect(config('parametros', []))
        ->pluck('modulo')
        ->unique()
        ->map(fn ($nombre) => ['slug' => Str::slug($nombre), 'nombre' => $nombre])
        ->values();
@endphp
<div class="card card-config">
    <div class="card-body p-2">
        <div class="nav nav-pills flex-column config-pills" id="config-pills" role="tablist"
            aria-orientation="vertical">
            <div class="config-pills-label">Parámetros</div>
            @foreach ($paramModulos as $i => $modulo)
                @if ($modo === 'tabs')
                    <button class="nav-link text-start {{ $i === 0 ? 'active' : '' }}"
                        id="pill-{{ $modulo['slug'] }}" data-bs-toggle="pill"
                        data-bs-target="#mod-{{ $modulo['slug'] }}" data-config-slug="{{ $modulo['slug'] }}"
                        type="button" role="tab" aria-controls="mod-{{ $modulo['slug'] }}"
                        aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                        <i class="ri-settings-4-line me-1 align-middle"></i> {{ $modulo['nombre'] }}
                    </button>
                @else
                    <a href="{{ route('configuracion.index') }}#{{ $modulo['slug'] }}"
                        class="nav-link text-start config-pill-link">
                        <i class="ri-settings-4-line me-1 align-middle"></i> {{ $modulo['nombre'] }}
                        <i class="ri-arrow-right-up-line config-pill-link-arrow"></i>
                    </a>
                @endif
            @endforeach

            {{-- Impuestos: catálogo gestionable (tabla `impuesto`), no es un
                 parámetro del registry. Mismo comportamiento tabs/links. --}}
            @if ($modo === 'tabs')
                <button class="nav-link text-start" id="pill-impuestos" data-bs-toggle="pill"
                    data-bs-target="#mod-impuestos" data-config-slug="impuestos" type="button" role="tab"
                    aria-controls="mod-impuestos" aria-selected="false">
                    <i class="ri-percent-line me-1 align-middle"></i> Impuestos
                </button>
            @else
                <a href="{{ route('configuracion.index') }}#impuestos" class="nav-link text-start config-pill-link">
                    <i class="ri-percent-line me-1 align-middle"></i> Impuestos
                    <i class="ri-arrow-right-up-line config-pill-link-arrow"></i>
                </a>
            @endif

            {{-- Accesos a las demás configuraciones del sistema --}}
            <div class="config-pills-label config-pills-label--separado">Otras configuraciones</div>
            <a href="{{ url('users') }}"
                class="nav-link text-start config-pill-link {{ $activo === 'usuarios' ? 'active' : '' }}">
                <i class="ri-group-line me-1 align-middle"></i> Configuración de usuarios
                <i class="ri-arrow-right-up-line config-pill-link-arrow"></i>
            </a>
            <a href="{{ route('profile.edit') }}"
                class="nav-link text-start config-pill-link {{ $activo === 'perfil' ? 'active' : '' }}">
                <i class="ri-user-settings-line me-1 align-middle"></i> Configuración de perfil
                <i class="ri-arrow-right-up-line config-pill-link-arrow"></i>
            </a>
            <a href="{{ route('seguridad.index') }}"
                class="nav-link text-start config-pill-link {{ $activo === 'seguridad' ? 'active' : '' }}">
                <i class="ri-shield-keyhole-line me-1 align-middle"></i> Configuración de seguridad
                @if ($activo !== 'seguridad')
                    <i class="ri-arrow-right-up-line config-pill-link-arrow"></i>
                @endif
            </a>
        </div>
    </div>
</div>
