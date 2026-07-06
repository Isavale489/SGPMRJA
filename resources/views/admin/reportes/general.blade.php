@extends('admin.layouts.app')

@section('title', 'Reportes Generales')
@push('styles')
    {{-- Estilos en public/assets/css/custom.css — sección "MÓDULO REPORTES — Reportes Generales (hub)" --}}
@endpush
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Reportes Generales</h4>
        </div>
    </div>
</div>

{{-- ── Resumen ejecutivo del mes en curso ── --}}
<div class="row g-3 mb-1">
    <div class="col-xl-3 col-md-6">
        {{-- KPIs operativos en emerald: mismo código de color de su sección de origen --}}
        <div class="card rep-kpi rep-kpi--emerald mb-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rep-kpi-icon"><i class="ri-shopping-bag-3-line"></i></div>
                <div>
                    <h3 class="rep-kpi-valor mb-0">{{ $kpis['pedidos_mes'] }}</h3>
                    <p class="rep-kpi-label mb-0">Pedidos este mes</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card rep-kpi rep-kpi--emerald mb-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rep-kpi-icon"><i class="ri-file-list-3-line"></i></div>
                <div>
                    <h3 class="rep-kpi-valor mb-0">{{ $kpis['cotizaciones_mes'] }}</h3>
                    <p class="rep-kpi-label mb-0">Cotizaciones este mes</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card rep-kpi rep-kpi--emerald mb-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rep-kpi-icon"><i class="ri-hammer-line"></i></div>
                <div>
                    <h3 class="rep-kpi-valor mb-0">{{ $kpis['ordenes_activas'] }}</h3>
                    <p class="rep-kpi-label mb-0">Órdenes activas</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card rep-kpi {{ $kpis['insumos_criticos'] > 0 ? 'rep-kpi--red' : 'rep-kpi--green' }} mb-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rep-kpi-icon"><i class="ri-alarm-warning-line"></i></div>
                <div>
                    <h3 class="rep-kpi-valor mb-0">{{ $kpis['insumos_criticos'] }}</h3>
                    <p class="rep-kpi-label mb-0">Insumos bajo mínimo</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Catálogo de reportes (registry config/reportes.php, filtrado por permisos) ── --}}
@forelse($grupos as $grupo)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card card-reportes rep-hub-group--{{ $grupo['color'] ?? 'sky' }}">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="{{ $grupo['icono'] }} fs-5 rep-hub-grupo-ic"></i>
                    <div>
                        <h4 class="card-title mb-0">{{ $grupo['titulo'] }}</h4>
                        <p class="rep-hub-grupo-desc mb-0">{{ $grupo['descripcion'] }}</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($grupo['reportes'] as $reporte)
                            <div class="col-xxl-4 col-md-6">
                                <a href="{{ route($reporte['ruta']) }}"
                                   class="rep-hub-card"
                                   @if ($reporte['formato'] === 'pdf') target="_blank" rel="noopener" @endif>
                                    <div class="rep-hub-card-icon"><i class="{{ $reporte['icono'] }}"></i></div>
                                    <div class="rep-hub-card-body">
                                        <span class="rep-hub-card-titulo">{{ $reporte['titulo'] }}</span>
                                        <span class="rep-hub-card-desc">{{ $reporte['descripcion'] }}</span>
                                    </div>
                                    <div class="rep-hub-card-meta">
                                        <span class="badge rounded-pill {{ $reporte['formato'] === 'pdf' ? 'rep-hub-badge-pdf' : 'rep-hub-badge-vista' }}">
                                            <i class="{{ $reporte['formato'] === 'pdf' ? 'ri-file-pdf-line' : 'ri-bar-chart-box-line' }} me-1"></i>{{ $reporte['formato'] === 'pdf' ? 'PDF' : 'Consulta' }}
                                        </span>
                                        <i class="ri-arrow-right-s-line rep-hub-card-flecha"></i>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="row mt-3">
        <div class="col-12">
            <div class="card card-reportes">
                <div class="card-body text-center text-muted py-5">
                    <i class="ri-file-forbid-line fs-1 d-block mb-2"></i>
                    Tu rol no tiene acceso a ningún reporte. Contacta al administrador si necesitas acceso.
                </div>
            </div>
        </div>
    </div>
@endforelse
@endsection
