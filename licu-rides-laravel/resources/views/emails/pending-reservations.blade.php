<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas Pendientes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .alert {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .reservation-list {
            background: white;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .reservation-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .reservation-item:last-child {
            border-bottom: none;
        }
        .ride-name {
            font-weight: bold;
            color: #667eea;
            font-size: 1.1em;
        }
        .passenger-name {
            color: #666;
            margin: 5px 0;
        }
        .time-pending {
            color: #ffc107;
            font-size: 0.9em;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #999;
            font-size: 0.9em;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⏰ Recordatorio de Reservas Pendientes</h1>
    </div>
    
    <div class="content">
        <p>Hola <strong>{{ $driver->nombre }}</strong>,</p>
        
        <div class="alert">
            <strong>⚠️ Tienes {{ $reservations->count() }} {{ $reservations->count() == 1 ? 'solicitud de reserva pendiente' : 'solicitudes de reserva pendientes' }}</strong> que necesitan tu atención.
        </div>
        
        <p>Las siguientes reservas están esperando tu respuesta:</p>
        
        <div class="reservation-list">
            @foreach($reservations as $reservation)
                <div class="reservation-item">
                    <div class="ride-name">{{ $reservation->ride->nombre }}</div>
                    <div style="color: #999; font-size: 0.9em;">{{ $reservation->ride->origen }} → {{ $reservation->ride->destino }}</div>
                    <div class="passenger-name">
                        👤 Pasajero: {{ $reservation->passenger->nombre }} {{ $reservation->passenger->apellido }}
                    </div>
                    <div style="margin: 5px 0;">
                        🪑 Asientos solicitados: <strong>{{ $reservation->seats }}</strong>
                    </div>
                    <div class="time-pending">
                        📅 Solicitada: {{ $reservation->created_at->diffForHumans() }}
                    </div>
                </div>
            @endforeach
        </div>
        
        <p style="text-align: center;">
            <a href="{{ route('reservations.index') }}" class="button">Ver y Gestionar Reservas</a>
        </p>
        
        <p style="color: #666; font-size: 0.95em;">
            Recuerda que es importante responder a las solicitudes lo antes posible para brindar una mejor experiencia a los pasajeros.
        </p>
    </div>
    
    <div class="footer">
        <p>Este es un email automático de recordatorio de Licu Rides</p>
        <p>Si tienes alguna pregunta, contáctanos.</p>
    </div>
</body>
</html>
