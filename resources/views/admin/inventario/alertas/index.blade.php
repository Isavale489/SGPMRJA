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
                <div class="card card-transactional">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">Insumos con Stock Bajo</h5>
                            <div class="flex-shrink-0 d-flex align-items-center gap-3">
                                <a href="{{ route('inventario.movimientos.index') }}" class="btn btn-secondary">
                                    <i class="ri-arrow-go-back-line align-bottom me-1"></i> Volver
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="advanced-filters-wrapper emerald-theme" id="advanced-filters">
                            <div class="navy-filter-header is-collapsed">
                                <div class="navy-header-search">
                                    <i class="ri-search-line"></i>
                                    <input type="text" class="navy-search-input" id="custom-search-input"
                                        placeholder="Buscar insumo..." autocomplete="off">
                                </div>
                                <div class="navy-header-divider"></div>
                                <button class="navy-filter-btn collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#filters-collapse-body"
                                    aria-expanded="false" aria-controls="filters-collapse-body">
                                    <i class="ri-filter-3-line"></i>
                                    <span>Filtros</span>
                                    <span class="navy-filter-badge d-none" id="active-filter-count"></span>
                                    <i class="ri-arrow-down-s-line navy-filter-chevron"></i>
                                </button>
                            </div>
                            <div class="collapse" id="filters-collapse-body">
                                <div class="navy-filter-body d-flex justify-content-end">
                                    <button type="button" class="btn btn-link" id="btn-clear-filters">Limpiar filtros</button>
                                </div>
                            </div>
                        </div>
                        @if(count($insumosConBajoStock) > 0)
                            <div class="alert alert-warning" role="alert">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="ri-alert-line fs-18 align-middle me-2"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong>¡Atención!</strong> Se han detectado {{ count($insumosConBajoStock) }} insumos
                                        con stock por debajo del mínimo requerido.
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="alertas-table"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle dt-transactional table-operativa"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Nro. Insumo</th>
                                            <th>Insumo</th>
                                            <th>Tipo</th>
                                            <th>Stock Actual</th>
                                            <th>Stock Mínimo</th>
                                            <th>Diferencia</th>
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
                                                    <span class="badge bg-danger">{{ number_format($insumo->stock_actual, 2) }}
                                                        {{ $insumo->unidad_medida }}</span>
                                                </td>
                                                <td>{{ number_format($insumo->stock_minimo, 2) }} {{ $insumo->unidad_medida }}</td>
                                                <td>
                                                    {{ number_format($insumo->stock_actual - $insumo->stock_minimo, 2) }}
                                                    {{ $insumo->unidad_medida }}
                                                </td>
                                                <td>
                                                    <div class="dropdown d-inline-block">
                                                        <button class="btn btn-soft-secondary btn-sm dropdown" type="button"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="ri-more-fill align-middle"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <a href="{{ route('inventario.movimientos.historial', $insumo->id) }}"
                                                                    class="dropdown-item">
                                                                    <i class="ri-history-line align-bottom me-2 text-muted"></i> Ver
                                                                    Historial
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#" class="dropdown-item add-movement"
                                                                    data-id="{{ $insumo->id }}" data-nombre="{{ $insumo->nombre }}">
                                                                    <i class="ri-add-circle-line align-bottom me-2 text-muted"></i>
                                                                    Registrar Entrada
                                                                </a>
                                                            </li>
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
                                        <strong>¡Excelente!</strong> Todos los insumos tienen stock por encima del mínimo
                                        requerido.
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            function debounce(func, wait) {
                let timeout;
                return function (...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            function updateFilterBadge() {
                let count = 0;
                $('.navy-filter-select').each(function () {
                    if ($(this).val() && $(this).val() !== '') {
                        count++;
                    }
                });
                $('#active-filter-count').text(count).toggleClass('d-none', count === 0);
            }

            $('#filters-collapse-body')
                .on('show.bs.collapse', function () {
                    $('.navy-filter-header').removeClass('is-collapsed');
                })
                .on('hidden.bs.collapse', function () {
                    $('.navy-filter-header').addClass('is-collapsed');
                });

            // Inicializar DataTable
            var table = $('#alertas-table').DataTable({
                language: lenguajeData,
                responsive: true,
                dom: 'Brtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf'
                ]
            });

            // Buscador personalizado
            $('#custom-search-input').on('input', debounce(function () {
                table.search(this.value).draw();
            }, 300));

            $('#btn-clear-filters').on('click', function () {
                $('#custom-search-input').val('');
                table.search('').draw();
                updateFilterBadge();
            });

            updateFilterBadge();

            // Manejar clic en enlace de agregar movimiento
            $('.add-movement').on('click', function (e) {
                e.preventDefault();
                var id = $(this).data('id');

                // Redirigir a la página de movimientos y seleccionar el insumo
                window.location.href = "{{ route('inventario.movimientos.index') }}?insumo_id=" + id + "&tipo=Entrada";
            });
        });
    </script>
@endsection