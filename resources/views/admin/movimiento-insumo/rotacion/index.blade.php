@extends('admin.layouts.app')

@section('title', 'Análisis de Rotación')

@push('styles')
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Análisis de Rotación de Insumos</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('movimiento-insumo.index') }}">Insumos</a></li>
                            <li class="breadcrumb-item active">Análisis de Rotación</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card card-reportes">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1">
                                    <i class="ri-loop-right-line align-bottom me-1"></i>Insumos por rotación (histórico)
                                </h5>
                                <small class="text-muted">Ordenados por salidas acumuladas. Se resaltan los de alta rotación con stock cerca del mínimo.</small>
                            </div>
                            <a href="{{ route('movimiento-insumo.index') }}" class="btn btn-secondary">
                                <i class="ri-arrow-go-back-line align-bottom me-1"></i> Volver
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="rotacion-table" class="table table-bordered table-striped align-middle" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Insumo</th>
                                        <th class="text-end">Total Salidas</th>
                                        <th class="text-end">Stock Actual</th>
                                        <th class="text-end">Stock Mínimo</th>
                                        <th class="text-center">Reposición</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($insumos as $insumo)
                                        @php
                                            $totalSalidas = $insumo->total_salidas ?? 0;
                                            $critico = $insumo->stock_actual <= $insumo->stock_minimo;
                                            // Alta rotación (tiene salidas) + stock cerca del mínimo (<= 1.5x)
                                            $reponerPronto = $totalSalidas > 0 && $insumo->stock_actual <= $insumo->stock_minimo * 1.5;
                                        @endphp
                                        <tr>
                                            <td>
                                                @if($insumo->codigo)
                                                    <span class="rot-code-pill">{{ $insumo->codigo }}</span>
                                                @endif
                                                <span class="fw-semibold">{{ $insumo->nombre }}</span>
                                                <small class="text-muted d-block">{{ $insumo->tipo }} · {{ $insumo->unidad_medida }}</small>
                                            </td>
                                            <td class="text-end fw-bold">{{ number_format($totalSalidas, 2) }}</td>
                                            <td class="text-end">
                                                <span class="{{ $critico ? 'text-danger fw-bold' : '' }}">{{ number_format($insumo->stock_actual, 2) }}</span>
                                            </td>
                                            <td class="text-end text-muted">{{ number_format($insumo->stock_minimo, 2) }}</td>
                                            <td class="text-center">
                                                @if($critico)
                                                    <span class="badge bg-danger mb-1 d-inline-block"><i class="ri-alarm-warning-line me-1"></i>Crítico</span>
                                                    <a href="{{ route('compras.index') }}" class="btn btn-sm btn-danger d-block mx-auto" style="max-width:150px">
                                                        <i class="ri-shopping-cart-2-line me-1"></i>Reponer Stock
                                                    </a>
                                                @elseif($reponerPronto)
                                                    <span class="badge bg-warning text-dark mb-1 d-inline-block"><i class="ri-error-warning-line me-1"></i>Reponer pronto</span>
                                                    <a href="{{ route('compras.index') }}" class="btn btn-sm btn-warning d-block mx-auto" style="max-width:150px">
                                                        <i class="ri-shopping-cart-2-line me-1"></i>Reponer Stock
                                                    </a>
                                                @else
                                                    <span class="badge bg-success"><i class="ri-check-line me-1"></i>OK</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            // order: [] conserva el orden del backend (salidas desc), evita que
            // DataTables reordene por la primera columna al iniciar.
            $('#rotacion-table').DataTable({
                language: lenguajeData,
                responsive: true,
                order: [],
                pageLength: 25
            });
        });
    </script>
@endpush
