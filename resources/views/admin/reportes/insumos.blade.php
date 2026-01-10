@extends('admin.layouts.app')

@section('title', 'Reporte de Consumo de Insumos')
@push('styles')
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" /> 
@endpush
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Reporte de Consumo de Insumos</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Consumo por Tipo de Insumo</h4>
            </div>
            <div class="card-body">
                <div id="consumoPorTipoChart" class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Top 10 Insumos Más Utilizados</h4>
            </div>
            <div class="card-body">
                <div id="topInsumosChart" class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Detalle de Consumo de Insumos</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-centered table-nowrap mb-0" id="insumosTable">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Unidad de Medida</th>
                                <th>Total Utilizado</th>
                                <th>Total Órdenes</th>
                                <th>Promedio por Orden</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($consumoInsumos as $insumo)
                            <tr>
                                <td>{{ $insumo->nombre }}</td>
                                <td>{{ $insumo->tipo }}</td>
                                <td>{{ $insumo->unidad_medida }}</td>
                                <td>{{ number_format($insumo->total_utilizado, 2) }}</td>
                                <td>{{ $insumo->total_ordenes }}</td>
                                <td>{{ $insumo->total_ordenes > 0 ? number_format($insumo->total_utilizado / $insumo->total_ordenes, 2) : 0 }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script> 
<script src="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('#insumosTable').DataTable({
            language: lenguajeData,
            order: [[4, 'desc']]
        });

        var consumoPorTipo = {};
        
        @foreach($consumoInsumos as $insumo)
            if (typeof consumoPorTipo['{{ $insumo->tipo }}'] === 'undefined') {
                consumoPorTipo['{{ $insumo->tipo }}'] = 0;
            }
            consumoPorTipo['{{ $insumo->tipo }}'] += {{ $insumo->total_utilizado }};
        @endforeach
        
        var tiposInsumo = Object.keys(consumoPorTipo);
        var valoresConsumo = tiposInsumo.map(function(tipo) {
            return consumoPorTipo[tipo];
        });

        var consumoPorTipoOptions = {
            series: valoresConsumo,
            chart: {
                type: 'donut',
                height: 350
            },
            labels: tiposInsumo,
            colors: ['#0ab39c', '#299cdb', '#f7b84b', '#f06548', '#6559cc', '#74788d'],
            legend: {
                position: 'bottom'
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val.toFixed(2);
                    }
                }
            }
        };

        var consumoPorTipoChart = new ApexCharts(document.querySelector("#consumoPorTipoChart"), consumoPorTipoOptions);
        consumoPorTipoChart.render();

        var topInsumos = [];
        var topCantidades = [];
        
        @foreach($consumoInsumos->take(10) as $insumo)
            topInsumos.push('{{ $insumo->nombre }}');
            topCantidades.push({{ $insumo->total_utilizado }});
        @endforeach

        var topInsumosOptions = {
            series: [{
                name: 'Cantidad Utilizada',
                data: topCantidades
            }],
            chart: {
                type: 'bar',
                height: 350
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true,
                }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: topInsumos,
            },
            colors: ['#0ab39c'],
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val.toFixed(2);
                    }
                }
            }
        };

        var topInsumosChart = new ApexCharts(document.querySelector("#topInsumosChart"), topInsumosOptions);
        topInsumosChart.render();
    });
</script>
@endpush
