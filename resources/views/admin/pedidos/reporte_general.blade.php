<x-app-layout>
    <div class="container">
        <h2 class="mb-4">Reporte General de Pedidos</h2>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Elaborado por</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedidos as $pedido)
                <tr>
                    <td>{{ $pedido->id }}</td>
                    <td>{{ $pedido->cliente_nombre }}</td>
                    <td>{{ \Carbon\Carbon::parse($pedido->fecha_pedido)->format('d/m/Y') }}</td>
                    <td>{{ number_format($pedido->total, 2) }}</td>
                    <td>{{ $pedido->estado }}</td>
                    <td>{{ $pedido->user->name ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
