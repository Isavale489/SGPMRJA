<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nueva Solicitud de Crédito</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: #2b3d51;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }

        .content {
            background: #ffffff;
            padding: 20px;
            border: 1px solid #e1e1e1;
            border-radius: 0 0 5px 5px;
        }

        .info-box {
            background: #f8f9fa;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #2b3d51;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #2b3d51;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            color: #666;
            font-size: 0.9em;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Nueva Solicitud de Crédito</h1>
    </div>

    <div class="content">
        <p>Estimado(a) Secretaria,</p>

        <p>Se ha recibido una nueva solicitud de crédito con los siguientes detalles:</p>

        <div class="info-box">
            <table>
                <tr>
                    <th>ID Solicitud:</th>
                    <td>#{{ $solicitud->id }}</td>
                </tr>
                <tr>
                    <th>Nombres y Apellidos:</th>
                    <td>{{ $solicitud->nombres_apellidos }}</td>
                </tr>
                <tr>
                    <th>DNI:</th>
                    <td>{{ $solicitud->dni }}</td>
                </tr>
                <tr>
                    <th>Teléfono:</th>
                    <td>{{ $solicitud->telefono }}</td>
                </tr>
                <tr>
                    <th>Correo Electrónico:</th>
                    <td>{{ $solicitud->correo_electronico }}</td>
                </tr>
                <tr>
                    <th>Tipo de Crédito:</th>
                    <td>{{ $solicitud->tipo_credito }}</td>
                </tr>
                <tr>
                    <th>Fecha de Solicitud:</th>
                    <td>{{ $solicitud->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            </table>
        </div>

        <p>Por favor, revise esta solicitud lo antes posible.</p>

        <p>Por favor, ingrese al sistema para revisar los detalles completos de la solicitud.</p>

        <div class="footer">
            <p>Este es un correo automático, por favor no responder.</p>
            <p>© {{ date('Y') }} Sistema de Gestión de Créditos. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
