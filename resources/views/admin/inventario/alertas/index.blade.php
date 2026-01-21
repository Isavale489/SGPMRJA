@extends('admin.layouts.app')

@section('title', 'Alertas de Stock')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Alertas de Stock</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Inventario</a></li>
                        <li class="breadcrumb-item active">Alertas</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Insumos con Stock Bajo</h5>
                        <div class="flex-shrink-0">
                            <a href="{{ route('inventario.movimientos.index') }}" class="btn btn-secondary">
                                <i class="ri-arrow-go-back-line align-bottom me-1"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($insumosConBajoStock) > 0)
                        <div class="alert alert-warning" role="alert">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="ri-alert-line fs-18 align-middle me-2"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>¡Atención!</strong> Se han detectado {{ count($insumosConBajoStock) }} insumos con stock por debajo del mínimo requerido.
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="alertas-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Nro. Insumo</th>
                                        <th>Insumo</th>
                                        <th>Tipo</th>
                                        <th>Stock Actual</th>
                                        <th>Stock Mínimo</th>
                                        <th>Diferencia</th>
                                        <th>Proveedor</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($insumosConBajoStock as $insumo)
                                    <tr>
                                        <td>{{ $insumo->id }}</td>
                                        <td>{{ $insumo->nombre }}</td>
                                        <td>{{ $insumo->tipo }}</td>
                                        <td>
                                            <span class="badge bg-danger">{{ number_format($insumo->stock_actual, 2) }} {{ $insumo->unidad_medida }}</span>
                                        </td>
                                        <td>{{ number_format($insumo->stock_minimo, 2) }} {{ $insumo->unidad_medida }}</td>
                                        <td>
                                            {{ number_format($insumo->stock_actual - $insumo->stock_minimo, 2) }} {{ $insumo->unidad_medida }}
                                        </td>
                                        <td>{{ $insumo->proveedor ? $insumo->proveedor->razon_social : 'No asignado' }}</td>
                                        <td>
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ri-more-fill align-middle"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a href="{{ route('inventario.movimientos.historial', $insumo->id) }}" class="dropdown-item">
                                                            <i class="ri-history-line align-bottom me-2 text-muted"></i> Ver Historial
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#" class="dropdown-item add-movement" data-id="{{ $insumo->id }}" data-nombre="{{ $insumo->nombre }}">
                                                            <i class="ri-add-circle-line align-bottom me-2 text-muted"></i> Registrar Entrada
                                                        </a>
                                                    </li>
                                                    @if($insumo->proveedor)
                                                    <li>
                                                        <a href="#" class="dropdown-item contact-provider" 
                                                           data-proveedor="{{ $insumo->proveedor->razon_social }}"
                                                           data-telefono="{{ $insumo->proveedor->telefono }}"
                                                           data-email="{{ $insumo->proveedor->email }}">
                                                            <i class="ri-phone-line align-bottom me-2 text-muted"></i> Contactar Proveedor
                                                        </a>
                                                    </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-success" role="alert">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="ri-checkbox-circle-line fs-18 align-middle me-2"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>¡Excelente!</strong> Todos los insumos tienen stock por encima del mínimo requerido.
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar información de contacto del proveedor -->
<div class="modal fade" id="proveedorModal" tabindex="-1" aria-labelledby="proveedorModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="proveedorModalLabel">Información de Contacto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="avatar-lg mx-auto">
                        <div class="avatar-title bg-light text-primary display-5 rounded-circle">
                            <i class="ri-building-line"></i>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <h5 id="proveedor-nombre"></h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th><i class="ri-phone-line fs-16 align-middle me-2"></i> Teléfono:</th>
                                <td id="proveedor-telefono"></td>
                            </tr>
                            <tr>
                                <th><i class="ri-mail-line fs-16 align-middle me-2"></i> Email:</th>
                                <td id="proveedor-email"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Inicializar DataTable
        var table = $('#alertas-table').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
            },
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf'
            ]
        });

        // Manejar clic en enlace de agregar movimiento
        $('.add-movement').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            
            // Redirigir a la página de movimientos y seleccionar el insumo
            window.location.href = "{{ route('inventario.movimientos.index') }}?insumo_id=" + id + "&tipo=Entrada";
        });

        // Manejar clic en enlace de contactar proveedor
        $('.contact-provider').on('click', function(e) {
            e.preventDefault();
            var proveedor = $(this).data('proveedor');
            var telefono = $(this).data('telefono') || 'No disponible';
            var email = $(this).data('email') || 'No disponible';
            
            $('#proveedor-nombre').text(proveedor);
            $('#proveedor-telefono').text(telefono);
            $('#proveedor-email').text(email);
            
            $('#proveedorModal').modal('show');
        });
    });
</script>
@endsection
