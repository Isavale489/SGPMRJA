@extends('admin.layouts.app')

@section('title', 'Reporte de Producción')
@push('styles')
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" /> 
@endpush
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Reporte de Producción</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Órdenes por Estado</h4>
            </div>
            <div class="card-body">
                <div id="ordenesPorEstadoChart" class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Producción Mensual</h4>
            </div>
            <div class="card-body">
                <div id="produccionMensualChart" class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Estadísticas de Producción</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-centered table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mes</th>
                                <th>Año</th>
                                <th>Total Producido</th>
                                <th>Total Defectuoso</th>
                                <th>Eficiencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($produccionMensual as $produccion)
                            <tr>
                                <td>{{ date('F', mktime(0, 0, 0, $produccion->mes, 1)) }}</td>
                                <td>{{ $produccion->año }}</td>
                                <td>{{ $produccion->total_producido }}</td>
                                <td>{{ $produccion->total_defectuoso }}</td>
                                <td>
                                    @php
                                        $eficiencia = $produccion->total_producido > 0 ? 
                                            ($produccion->total_producido - $produccion->total_defectuoso) / $produccion->total_producido * 100 : 0;
                                    @endphp
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $eficiencia }}%;" aria-valuenow="{{ $eficiencia }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span>{{ number_format($eficiencia, 2) }}%</span>
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
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script> 
<script src="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var ordenesPorEstadoOptions = {
            series: [
                @foreach($ordenesPorEstado as $orden)
                    {{ $orden->total }},
                @endforeach
            ],
            chart: {
                type: 'donut',
                height: 350
            },
            labels: [
                @foreach($ordenesPorEstado as $orden)
                    '{{ $orden->estado }}',
                @endforeach
            ],
            colors: ['#0ab39c', '#299cdb', '#f7b84b', '#f06548'],
            legend: {
                position: 'bottom'
            }
        };

        var ordenesPorEstadoChart = new ApexCharts(document.querySelector("#ordenesPorEstadoChart"), ordenesPorEstadoOptions);
        ordenesPorEstadoChart.render();

        var meses = [];
        var producido = [];
        var defectuoso = [];

        @foreach($produccionMensual->reverse() as $produccion)
            meses.push('{{ date('M', mktime(0, 0, 0, $produccion->mes, 1)) }}-{{ $produccion->año }}');
            producido.push({{ $produccion->total_producido }});
            defectuoso.push({{ $produccion->total_defectuoso }});
        @endforeach

        var produccionMensualOptions = {
            series: [{
                name: 'Producido',
                data: producido
            }, {
                name: 'Defectuoso',
                data: defectuoso
            }],
            chart: {
                type: 'bar',
                height: 350,
                stacked: false
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: meses,
            },
            yaxis: {
                title: {
                    text: 'Cantidad'
                }
            },
            fill: {
                opacity: 1
            },
            colors: ['#0ab39c', '#f06548'],
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " unidades"
                    }
                }
            }
        };

        var produccionMensualChart = new ApexCharts(document.querySelector("#produccionMensualChart"), produccionMensualOptions);
        produccionMensualChart.render();
    });
</script>
@endpush
