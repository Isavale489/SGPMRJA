@extends('admin.layouts.app')

@section('title', 'Compra #' . $compra->id)

@push('styles')
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
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

        <div class="row g-3">
            {{-- Columna principal --}}
            <div class="col-lg-8">
                {{-- Card: Datos de la compra --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-soft-primary py-2 px-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 text-atlantico-dark fs-13">
                            <i class="ri-file-list-3-line me-1"></i>Datos de la Factura
                        </h6>
                        @php
                            $badgeMap = ['recibida' => 'success', 'borrador' => 'warning', 'anulada' => 'danger'];
                            $badgeColor = $badgeMap[$compra->estado] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $badgeColor }}">{{ ucfirst($compra->estado) }}</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1"><i class="ri-store-2-line me-1"></i>Proveedor</small>
                                <span class="fw-semibold">{{ $compra->proveedor?->nombre_completo ?? 'N/A' }}</span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1"><i class="ri-receipt-line me-1"></i>N° de Factura</small>
                                <span class="fw-semibold">{{ $compra->numero_factura ?? '—' }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block mb-1"><i class="ri-calendar-line me-1"></i>Fecha de Compra</small>
                                <span>{{ $compra->fecha_compra?->format('d/m/Y') }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block mb-1"><i class="ri-bank-card-line me-1"></i>Tipo de Pago</small>
                                @if($compra->tipo_pago === 'credito')
                                    <span class="badge bg-secondary">Crédito</span>
                                @else
                                    <span class="badge bg-info text-dark">Contado</span>
                                @endif
                            </div>
                            @if($compra->fecha_vencimiento)
                            <div class="col-md-4">
                                <small class="text-muted d-block mb-1"><i class="ri-calendar-check-line me-1"></i>Vencimiento</small>
                                <span>{{ $compra->fecha_vencimiento->format('d/m/Y') }}</span>
                            </div>
                            @endif
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
                    <div class="card-header border-0 bg-soft-primary py-2 px-3">
                        <h6 class="mb-0 text-atlantico-dark fs-13">
                            <i class="ri-archive-line me-1"></i>Ítems de la Compra
                            <span class="text-muted fw-normal ms-1">({{ $compra->detalles->count() }})</span>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width:40px;">#</th>
                                        <th>Insumo</th>
                                        <th class="text-center">Tipo</th>
                                        <th class="text-center">Unidad</th>
                                        <th class="text-end">Cantidad</th>
                                        <th class="text-end">Costo Unit.</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($compra->detalles as $i => $detalle)
                                    <tr>
                                        <td class="text-center text-muted">{{ $i + 1 }}</td>
                                        <td class="fw-semibold">{{ $detalle->insumo?->nombre ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark">{{ $detalle->insumo?->tipo ?? '—' }}</span>
                                        </td>
                                        <td class="text-center text-muted">{{ $detalle->insumo?->unidad_medida ?? '—' }}</td>
                                        <td class="text-end">{{ number_format($detalle->cantidad, 2) }}</td>
                                        <td class="text-end">{{ number_format($detalle->costo_unitario, 2) }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($detalle->subtotal, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="6" class="text-end text-muted">Subtotal</td>
                                        <td class="text-end fw-semibold">{{ number_format($compra->subtotal, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-end text-muted">IVA</td>
                                        <td class="text-end fw-semibold">{{ number_format($compra->iva, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Total</td>
                                        <td class="text-end fw-bold fs-14">{{ number_format($compra->total, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Columna: metadatos + acciones --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-soft-primary py-2 px-3">
                        <h6 class="mb-0 text-atlantico-dark fs-13">
                            <i class="ri-user-line me-1"></i>Registro
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Registrado por</small>
                            <span class="fw-semibold">{{ $compra->registradoPor?->name ?? 'Sistema' }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Fecha de registro</small>
                            <span>{{ $compra->created_at?->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 bg-soft-primary py-2 px-3">
                        <h6 class="mb-0 text-atlantico-dark fs-13">
                            <i class="ri-calculator-line me-1"></i>Totales
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-semibold">{{ number_format($compra->subtotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">IVA</span>
                            <span class="fw-semibold">{{ number_format($compra->iva, 2) }}</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold fs-16">{{ number_format($compra->total, 2) }}</span>
                        </div>
                        <div class="d-grid gap-2">
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
