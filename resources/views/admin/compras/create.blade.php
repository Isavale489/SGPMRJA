@extends('admin.layouts.app')

@section('title', 'Nueva Compra')

@push('styles')
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Nueva Compra</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Gestión Operativa</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('compras.index') }}">Compras</a></li>
                            <li class="breadcrumb-item active">Nueva</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <form id="compraForm" novalidate>
            @csrf
            <div class="row g-3">

                {{-- ── Columna principal ───────────────────────────────────── --}}
                <div class="col-lg-8">

                    {{-- Card: Proveedor y Factura --}}
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header border-0 bg-soft-primary py-2 px-3">
                            <h6 class="mb-0 text-atlantico-dark fs-13">
                                <i class="ri-store-2-line me-1"></i>Proveedor y Datos de Factura
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label form-label-sm required mb-1" for="proveedor_id">Proveedor</label>
                                    <select id="proveedor_id" name="proveedor_id" class="form-select form-select-sm" required>
                                        <option value="">Seleccione un proveedor...</option>
                                        @foreach ($proveedores as $proveedor)
                                            <option value="{{ $proveedor->id }}">{{ $proveedor->nombre_completo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label form-label-sm mb-1" for="numero_factura">N° de Factura</label>
                                    <input type="text" id="numero_factura" name="numero_factura"
                                        class="form-control form-control-sm" maxlength="30"
                                        placeholder="Ej: 0001-000456">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm required mb-1" for="fecha_compra">Fecha de Compra</label>
                                    <input type="date" id="fecha_compra" name="fecha_compra"
                                        class="form-control form-control-sm" required
                                        value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm mb-1" for="tipo_pago">Tipo de Pago</label>
                                    <select id="tipo_pago" name="tipo_pago" class="form-select form-select-sm">
                                        <option value="contado">Contado</option>
                                        <option value="credito">Crédito</option>
                                    </select>
                                </div>
                                <div class="col-md-4" id="vencimiento-wrapper" style="display:none;">
                                    <label class="form-label form-label-sm mb-1" for="fecha_vencimiento">Fecha de Vencimiento</label>
                                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento"
                                        class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Ítems --}}
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header border-0 bg-soft-primary py-2 px-3 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 text-atlantico-dark fs-13">
                                <i class="ri-archive-line me-1"></i>Ítems de la Compra
                                <span class="text-muted fw-normal ms-1" id="items-count">(0)</span>
                            </h6>
                            <button type="button" class="btn btn-sm btn-soft-primary py-0 px-2" id="add-item-btn">
                                <i class="ri-add-line"></i> Agregar ítem
                            </button>
                        </div>
                        <div class="card-body p-0">
                            {{-- Empty state --}}
                            <div id="items-empty" class="text-center py-4 text-muted">
                                <i class="ri-archive-line d-block opacity-50 mb-1" style="font-size: 1.75rem;"></i>
                                <p class="fs-12 mb-0">Aún no agregaste ítems. Haz click en <strong>"Agregar ítem"</strong>.</p>
                            </div>

                            {{-- Tabla de ítems --}}
                            <div id="items-table-wrap" class="cot-grouped-tablewrap" hidden>
                                <table class="cot-grouped-table">
                                    <thead>
                                        <tr>
                                            <th class="cot-col-num text-center" style="width:36px;">#</th>
                                            <th>Insumo</th>
                                            <th class="text-center" style="width:105px;">Cantidad</th>
                                            <th class="text-center" style="width:120px;">Costo Unit.</th>
                                            <th class="text-end" style="width:100px;">Subtotal</th>
                                            <th class="text-center" style="width:48px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-tbody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Observaciones --}}
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header border-0 bg-soft-primary py-2 px-3">
                            <h6 class="mb-0 text-atlantico-dark fs-13">
                                <i class="ri-sticky-note-line me-1"></i>Observaciones
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <textarea id="observaciones" name="observaciones"
                                class="form-control form-control-sm" rows="2" maxlength="500"
                                placeholder="Notas o comentarios sobre esta compra (opcional)..."></textarea>
                        </div>
                    </div>
                </div>

                {{-- ── Columna: Resumen ─────────────────────────────────────── --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm" style="position: sticky; top: 80px;">
                        <div class="card-header border-0 bg-soft-primary py-2 px-3">
                            <h6 class="mb-0 text-atlantico-dark fs-13">
                                <i class="ri-calculator-line me-1"></i>Resumen de la Compra
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="mb-3">
                                <label class="form-label form-label-sm mb-1" for="iva_porcentaje">% IVA</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" id="iva_porcentaje" name="iva_porcentaje"
                                        class="form-control form-control-sm" value="16" min="0" max="100" step="0.01">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted fs-13">Subtotal</span>
                                <span class="fw-semibold fs-13" id="resumen-subtotal">0.00</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted fs-13">IVA (<span id="resumen-iva-pct">16</span>%)</span>
                                <span class="fw-semibold fs-13" id="resumen-iva">0.00</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold">Total</span>
                                <span class="fw-bold fs-16" id="resumen-total">0.00</span>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-sm" id="submit-btn">
                                    <i class="ri-save-line me-1"></i>Registrar Compra
                                </button>
                                <a href="{{ route('compras.index') }}" class="btn btn-light btn-sm">
                                    <i class="ri-arrow-left-line me-1"></i>Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Datos de insumos disponibles para el JS --}}
    <script>
        window.INSUMOS_DATA = @json($insumos);
        window.ROUTES = { store: "{{ route('compras.store') }}", show: "{{ url('compras') }}/" };
    </script>
@endsection

@push('scripts')
    <script src="{{ URL::asset('/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @include('admin.compras.scripts.create')
@endpush
