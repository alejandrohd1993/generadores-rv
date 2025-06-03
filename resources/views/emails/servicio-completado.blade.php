<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Servicio Completado - Listo para Facturar</title>
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
        .highlight {
            background-color: #e6f7ff;
            padding: 10px;
            border-left: 4px solid #E02317;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Servicio Completado - Listo para Facturar</h2>
        </div>
        <div class="content">
            <p>Estimado equipo de contabilidad,</p>
            
            <div class="highlight">
                <p>El siguiente servicio ha sido marcado como <strong>COMPLETADO</strong> y está listo para ser facturado:</p>
            </div>
            
            <table>
                <tr>
                    <th>Nombre del Servicio</th>
                    <td>{{ $servicio->nombre }}</td>
                </tr>
                <tr>
                    <th>Cliente</th>
                    <td>{{ $servicio->customer->nombre }}</td>
                </tr>
                <tr>
                    <th>Lugar</th>
                    <td>{{ $servicio->lugar }}</td>
                </tr>
                <tr>
                    <th>Fecha de Inicio</th>
                    <td>{{ $servicio->date_start }}</td>
                </tr>
                <tr>
                    <th>Fecha de Finalización</th>
                    <td>{{ $servicio->date_final }}</td>
                </tr>
                <tr>
                    <th>Operador</th>
                    <td>{{ $servicio->user->name }}</td>
                </tr>
                <tr>
                    <th>Valor Servicio</th>
                    <td>${{ number_format($servicio->valor_servicio, 2, ',', '.') }}</td>
                </tr>
            </table>
            
            <h3>Generadores Utilizados:</h3>
            
            @if(count($generadores) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Marca</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($generadores as $generador)
                            <tr>
                                <td>{{ $generador->codigo }}</td>
                                <td>{{ $generador->marca }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No hay generadores asignados a este servicio.</p>
            @endif
            
            {{-- <p>
                <a href="{{ url('/admin/services/' . $servicio->id) }}" class="button">Ver Detalles del Servicio</a>
            </p> --}}
        </div>
        <div class="footer">
            <p>Este es un mensaje automático del sistema de Generadores RV. Por favor no responda a este correo.</p>
        </div>
    </div>
</body>
</html>