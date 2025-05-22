<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notificación de Mantenimiento</title>
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
            background-color: {{ $esProximo ? '#3498db' : '#e74c3c' }};
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
            background-color: {{ $esProximo ? '#3498db' : '#e74c3c' }};
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ $esProximo ? 'Mantenimiento Próximo' : 'Mantenimiento Requerido' }}</h2>
        </div>
        <div class="content">
            <p>Estimado equipo de gerencia,</p>
            
            @if($esProximo)
                <p>El generador <strong>{{ $generador->codigo }}</strong> necesitará mantenimiento de <strong>{{ $tipoMantenimiento }}</strong> pronto.</p>
                <p>Ha acumulado <strong>{{ number_format($horasAcumuladas, 2) }}</strong> horas desde el último mantenimiento.</p>
                <p>Faltan aproximadamente <strong>{{ number_format($horasFaltantes, 2) }}</strong> horas para alcanzar el límite de <strong>{{ $limiteHoras }}</strong> horas.</p>
                <p>Por favor, planifique el mantenimiento con anticipación para evitar problemas operativos.</p>
            @else
                <p>El generador <strong>{{ $generador->codigo }}</strong> requiere mantenimiento de <strong>{{ $tipoMantenimiento }}</strong> inmediatamente.</p>
                <p>Ha acumulado <strong>{{ number_format($horasAcumuladas, 2) }}</strong> horas desde el último mantenimiento (límite: <strong>{{ $limiteHoras }}</strong> horas).</p>
                <p>Por favor, programe el mantenimiento lo antes posible para evitar daños en el equipo.</p>
            @endif
            
            
        </div>
        <div class="footer">
            <p>Este es un mensaje automático del sistema de Generadores RV. Por favor no responda a este correo.</p>
        </div>
    </div>
</body>
</html>