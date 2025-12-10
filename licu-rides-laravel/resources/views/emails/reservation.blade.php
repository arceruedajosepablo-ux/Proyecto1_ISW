<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
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
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 0 0 5px 5px;
        }
        .info-box {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #4CAF50;
        }
        .info-row {
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #777;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Licu Rides</h1>
    </div>
    
    <div class="content">
        @if($type === 'new')
            <h2>¡Nueva Solicitud de Reserva!</h2>
            <p>Hola {{ $reservation->ride->user->nombre }},</p>
            <p>Tienes una nueva solicitud de reserva para tu ride:</p>
        @elseif($type === 'accepted')
            <h2>¡Tu Reserva Fue Aceptada!</h2>
            <p>Hola {{ $reservation->passenger->nombre }},</p>
            <p>¡Buenas noticias! Tu solicitud de reserva ha sido aceptada:</p>
        @elseif($type === 'rejected')
            <h2>Actualización de Reserva</h2>
            <p>Hola {{ $reservation->passenger->nombre }},</p>
            <p>Lamentablemente tu solicitud de reserva no fue aceptada:</p>
        @endif

        <div class="info-box">
            <div class="info-row">
                <span class="label">Ride:</span> {{ $reservation->ride->nombre }}
            </div>
            <div class="info-row">
                <span class="label">Origen:</span> {{ $reservation->ride->origen }}
            </div>
            <div class="info-row">
                <span class="label">Destino:</span> {{ $reservation->ride->destino }}
            </div>
            <div class="info-row">
                <span class="label">Fecha:</span> {{ $reservation->ride->fecha->format('d/m/Y') }}
            </div>
            <div class="info-row">
                <span class="label">Hora:</span> {{ $reservation->ride->hora }}
            </div>
            <div class="info-row">
                <span class="label">Espacios solicitados:</span> {{ $reservation->seats }}
            </div>
            @if($type === 'new')
            <div class="info-row">
                <span class="label">Pasajero:</span> {{ $reservation->passenger->nombre }} {{ $reservation->passenger->apellido }}
            </div>
            <div class="info-row">
                <span class="label">Teléfono:</span> {{ $reservation->passenger->telefono }}
            </div>
            @else
            <div class="info-row">
                <span class="label">Conductor:</span> {{ $reservation->ride->user->nombre }} {{ $reservation->ride->user->apellido }}
            </div>
            <div class="info-row">
                <span class="label">Teléfono:</span> {{ $reservation->ride->user->telefono }}
            </div>
            <div class="info-row">
                <span class="label">Vehículo:</span> {{ $reservation->ride->vehicle->marca }} {{ $reservation->ride->vehicle->modelo }} - {{ $reservation->ride->vehicle->placa }}
            </div>
            @endif
        </div>

        @if($type === 'new')
            <p>Puedes revisar y gestionar esta solicitud desde tu panel de control:</p>
            <center>
                <a href="{{ route('reservations.index') }}" class="button">Ver Solicitudes</a>
            </center>
        @elseif($type === 'accepted')
            <p>¡Prepárate para tu viaje! Puedes ver los detalles completos en tu panel:</p>
            <center>
                <a href="{{ route('reservations.index') }}" class="button">Ver Mis Reservas</a>
            </center>
        @else
            <p>Puedes buscar otros rides disponibles en la plataforma:</p>
            <center>
                <a href="{{ route('rides.index') }}" class="button">Buscar Rides</a>
            </center>
        @endif
    </div>
    
    <div class="footer">
        <p>Este es un correo automático de Licu Rides. Por favor no respondas a este mensaje.</p>
        <p>&copy; {{ date('Y') }} Licu Rides. Todos los derechos reservados.</p>
    </div>
</body>
</html>
