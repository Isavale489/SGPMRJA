<x-app-layout>
    <div class="container">
        <h2 class="mb-4">Reporte General de Cotizaciones</h2>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Fecha Cotización</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Elaborado por</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cotizaciones as $cotizacion)
                <tr>
                    <td>{{ $cotizacion->id }}</td>
                    <td>{{ $cotizacion->cliente_nombre }}</td>
                    <td>{{ \Carbon\Carbon::parse($cotizacion->fecha_cotizacion)->format('d/m/Y') }}</td>
                    <td>{{ number_format($cotizacion->total, 2) }}</td>
                    <td>{{ $cotizacion->estado }}</td>
                    <td>{{ $cotizacion->user->name ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
