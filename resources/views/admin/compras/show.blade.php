@extends('admin.layouts.app')

@section('title', 'Compra #' . $compra->id)

@push('styles')
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    @php
        $badgeMap   = ['recibida' => 'success', 'borrador' => 'warning', 'anulada' => 'danger'];
        $badgeColor = $badgeMap[$compra->estado] ?? 'secondary';

        $provName = $compra->proveedor?->nombre_completo ?? 'N/A';
        $provIni  = collect(explode(' ', trim($provName)))
                        ->filter()->take(2)
                        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                        ->implode('') ?: '—';
        $provTipo = match ($compra->proveedor?->tipo_proveedor) {
            'natural'  => 'Natural',
            'juridico' => 'Jurídico',
            default    => 'Proveedor',
        };
        $provDoc   = $compra->proveedor?->documento;
        $provTel   = $compra->proveedor?->telefono_unificado;
        $provEmail = $compra->proveedor?->email_unificado;
    @endphp

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Detalle de Compra #{{ $compra->id }}</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Gestión Operativa</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('compras.index') }}">Compras</a></li>
                            <li class="breadcrumb-item active">#{{ $compra->id }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Hero: resumen de la compra ──────────────────────────────────── --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3 p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="c-show-hero-icon"><i class="ri-shopping-bag-3-line"></i></div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h4 class="mb-0">Compra #{{ $compra->id }}</h4>
                            <span class="badge bg-{{ $badgeColor }}">{{ ucfirst($compra->estado) }}</span>
                        </div>
                        <p class="text-muted mb-0 mt-1">
                            <i class="ri-store-2-line me-1"></i>{{ $provName }}
                            <span class="mx-2 text-muted opacity-50">·</span>
                            <i class="ri-calendar-line me-1"></i>{{ $compra->fecha_compra?->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
                <div class="text-lg-end">
                    <small class="text-muted d-block mb-1">Total de la compra</small>
                    <span class="c-show-hero-total text-atlantico-emerald">{{ number_format($compra->total, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="row g-3">
            {{-- Columna principal --}}
            <div class="col-lg-8">
                {{-- Card: Información de la compra (proveedor + factura) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-soft-primary">
                        <h6 class="mb-0 text-atlantico-dark">
                            <i class="ri-file-list-3-line me-2"></i>Información de la Compra
                        </h6>
                    </div>
                    <div class="card-body">
                        {{-- Proveedor (mismo estilo que el wizard) --}}
                        <div class="cot-cliente-card mb-3">
                            <div class="cot-cliente-avatar">{{ $provIni }}</div>
                            <div class="cot-cliente-info flex-grow-1">
                                <div class="cot-cliente-name-row">
                                    <h5 class="cot-cliente-name">{{ $provName }}</h5>
                                    <span class="cot-cliente-roles">
                                        <span class="cot-role-pill cot-role-proveedor">{{ $provTipo }}</span>
                                    </span>
                                </div>
                                <p class="cot-cliente-doc">
                                    <i class="ri-bank-card-line"></i>{{ $provDoc ?: 'Sin documento' }}
                                </p>
                                <div class="cot-cliente-contact-row">
                                    @if($provTel)
                                        <span class="cot-cliente-contact-item">
                                            <i class="ri-phone-line"></i>{{ $provTel }}
                                        </span>
                                    @endif
                                    @if($provEmail)
                                        <span class="cot-cliente-contact-item">
                                            <i class="ri-mail-line"></i>{{ $provEmail }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Datos del comprobante --}}
                        <div class="row g-3">
                            <div class="col-md-4">
                                <small class="text-muted d-block mb-1"><i class="ri-receipt-line me-1"></i>N° de Factura</small>
                                <span class="fw-semibold">{{ $compra->numero_factura ?? 'S/N' }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block mb-1"><i class="ri-calendar-event-line me-1"></i>Fecha de Compra</small>
                                <span>{{ $compra->fecha_compra?->format('d/m/Y') }}</span>
                            </div>
                            @if($compra->observaciones)
                            <div class="col-12">
                                <small class="text-muted d-block mb-1"><i class="ri-sticky-note-line me-1"></i>Observaciones</small>
                                <span class="fst-italic">{{ $compra->observaciones }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card: Ítems --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-soft-primary">
                        <h6 class="mb-0 text-atlantico-dark">
                            <i class="ri-archive-line me-2"></i>Ítems de la Compra
                            <span class="text-muted fw-normal ms-1">({{ $compra->detalles->count() }})</span>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="cot-grouped-tablewrap">
                            <table class="cot-grouped-table">
                                <thead>
                                    <tr>
                                        <th class="cot-col-num text-center" style="width:40px;">#</th>
                                        <th>Insumo</th>
                                        <th class="text-center" style="width:110px;">Tipo</th>
                                        <th class="text-center" style="width:90px;">Unidad</th>
                                        <th class="text-end" style="width:100px;">Cantidad</th>
                                        <th class="text-end" style="width:110px;">Costo Unit.</th>
                                        <th class="text-end" style="width:110px;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($compra->detalles as $i => $detalle)
                                    <tr>
                                        <td class="text-center cot-col-num">{{ $i + 1 }}</td>
                                        <td class="fw-semibold">{{ $detalle->insumo?->nombre ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <span class="cot-tipo-pill">{{ $detalle->insumo?->tipo ?? '—' }}</span>
                                        </td>
                                        <td class="text-center text-muted">{{ $detalle->insumo?->unidad_medida ?? '—' }}</td>
                                        <td class="text-end">{{ number_format($detalle->cantidad, 2) }}</td>
                                        <td class="text-end">{{ number_format($detalle->costo_unitario, 2) }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($detalle->subtotal, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Columna: metadatos + acciones --}}
            <div class="col-lg-4">
                {{-- Registro --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-soft-primary">
                        <h6 class="mb-0 text-atlantico-dark">
                            <i class="ri-user-line me-2"></i>Registro
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <img src="{{ $compra->registradoPor?->avatar_url }}" alt=""
                                class="rounded-circle" width="40" height="40" style="object-fit:cover;">
                            <div>
                                <small class="text-muted d-block">Registrado por</small>
                                <span class="fw-semibold">{{ $compra->registradoPor?->name ?? 'Sistema' }}</span>
                            </div>
                        </div>
                        <div>
                            <small class="text-muted d-block mb-1"><i class="ri-time-line me-1"></i>Fecha de registro</small>
                            <span>{{ $compra->created_at?->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Totales + acciones --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 bg-soft-primary">
                        <h6 class="mb-0 text-atlantico-dark">
                            <i class="ri-calculator-line me-2"></i>Totales
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="c-ticket mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-semibold">{{ number_format($compra->subtotal, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">IVA</span>
                                <span class="fw-semibold">{{ number_format($compra->iva, 2) }}</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold fs-15">Total</span>
                                <span class="fw-bold fs-18 text-atlantico-emerald">{{ number_format($compra->total, 2) }}</span>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="{{ route('compras.pdf', $compra->id) }}" class="btn btn-success btn-sm" target="_blank">
                                <i class="ri-file-pdf-2-line me-1"></i>Descargar PDF
                            </a>
                            <a href="{{ route('compras.index') }}" class="btn btn-light btn-sm">
                                <i class="ri-arrow-left-line me-1"></i>Volver al listado
                            </a>
                            @if($compra->estado !== 'anulada')
                                <button type="button" class="btn btn-danger btn-sm" id="btn-anular"
                                    data-id="{{ $compra->id }}">
                                    <i class="ri-close-circle-line me-1"></i>Anular Compra
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ URL::asset('/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
    $(document).ready(function () {
        $('#btn-anular').on('click', function () {
            var compraId = $(this).data('id');

            Swal.fire({
                title: '¿Anular esta compra?',
                text: 'Se revertirán todos los movimientos de stock generados. Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, anular',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: '/compras/' + compraId + '/anular',
                    method: 'POST',
                    data: { _method: 'PATCH', _token: '{{ csrf_token() }}' },
                    success: function (response) {
                        Swal.fire({
                            title: 'Anulada',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        }).then(function () {
                            window.location.reload();
                        });
                    },
                    error: function (xhr) {
                        var msg = xhr.responseJSON?.message ?? 'Error al anular la compra.';
                        Swal.fire({ title: 'Error', text: msg, icon: 'error', confirmButtonText: 'Entendido' });
                    }
                });
            });
        });
    });
    </script>
@endpush
