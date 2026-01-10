<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Clientes</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 0; }
        .container {
            max-width: 750px;
            width: 100%;
            margin: 0 auto;
            padding-left: 0px;
            padding-right: 30px;
            padding-top: 10px;
            padding-bottom: 10px;
        }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 10px;
            word-break: normal;
            overflow-wrap: break-word;
            text-align: left;
            vertical-align: top;
        }
        th { background-color: #f0f0f0; }
        th:nth-child(1) { width: 11%; }
        th:nth-child(2) { width: 7%; }
        th:nth-child(3) { width: 20%; }
        th:nth-child(4) { width: 10%; }
        th:nth-child(5) { width: 12%; }
        th:nth-child(6) { width: 17%; }
        th:nth-child(7) { width: 8%; }
        th:nth-child(8) { width: 7%; }
        th:nth-child(9) { width: 8%; }
    </style>
</head>
<body>
    <div class="container">
        <div style="position: relative; min-height: 100px; margin-bottom: 8px;">
            <img src="{{ public_path('logo.jpg') }}" alt="Logo" style="width: 100px; position: absolute; left: 0; top: 0;">
            <div style="text-align: center; padding-left: 30px;">
                <div class="company-name" style="font-size: 18px; font-weight: bold;">Manufacturas R.J. ATLANTICO C.A.</div>
                <div class="company-info" style="font-size: 11px;">
                    Rif: J-40391423-0 &nbsp;&nbsp; Telf.: 0414-3558537 - 0255-6640625 &nbsp;&nbsp; Email: rjatlantico@gmail.com
                </div>
                <div class="company-info" style="font-size: 11px;">
                    Av. Esquina calle 35 locales 1 y 2 sector centro Acarigua - Edo. Portuguesa
                </div>
            </div>
        </div>
        <div style="text-align:center; font-size:16px; font-weight:bold; margin: 0 0 8px 0;">
            Reporte General de Clientes
        </div>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Documento</th>
                    <th>Dirección</th>
                    <th>Ciudad</th>
                    <th>Estado</th>
                    <th>Creado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clientes as $cliente)
                    <tr>
                        <td>{{ $cliente->nombre }}</td>
                        <td>{{ ucfirst($cliente->tipo_cliente) }}</td>
                        <td>{{ $cliente->email }}</td>
                        <td>{{ $cliente->telefono }}</td>
                        <td>{{ $cliente->documento }}</td>
                        <td>{{ $cliente->direccion }}</td>
                        <td>{{ $cliente->ciudad }}</td>
                        <td>{{ $cliente->estado == 1 ? 'Activo' : 'Inactivo' }}</td>
                        <td>{{ $cliente->created_at ? \Carbon\Carbon::parse($cliente->created_at)->format('d/m/Y') : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html> 