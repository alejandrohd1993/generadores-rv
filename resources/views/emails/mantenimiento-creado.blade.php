<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nuevo Mantenimiento Asignado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
        }
        .header {
            background-color: #E02317;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 5px 5px 0 0;
            margin-bottom: 20px;
        }
        .content {
            padding: 20px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        .button {
            display: inline-block;
            background-color: #E02317;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Nuevo Mantenimiento Asignado</h2>
        </div>
        <div class="content">
            <p>Estimado usuario,</p>
            
            <p>Se le ha asignado un nuevo mantenimiento con los siguientes detalles:</p>
            
            <table>
                <tr>
                    <th>Nombre del Mantenimiento</th>
                    <td>{{ $mantenimiento->nombre }}</td>
                </tr>
                <tr>
                    <th>Fecha</th>
                    <td>{{ $mantenimiento->fecha }}</td>
                </tr>
                <tr>
                    <th>Generador</th>
                    <td>{{ $generador->codigo }} - {{ $generador->marca }}</td>
                </tr>
                <tr>
                    <th>Proveedor</th>
                    <td>{{ $proveedor->nombre }}</td>
                </tr>
                <tr>
                    <th>Descripción</th>
                    <td>{{ $mantenimiento->descripcion }}</td>
                </tr>
            </table>
            
        </div>
        <div class="footer">
            <p>Este es un mensaje automático del sistema de Generadores RV. Por favor no responda a este correo.</p>
        </div>
    </div>
</body>
</html>