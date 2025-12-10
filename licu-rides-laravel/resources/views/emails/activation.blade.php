<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activa tu cuenta</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">🚗 Licu Rides</h1>
    </div>
    
    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
        <h2 style="color: #667eea;">¡Bienvenido a Licu Rides, {{ $user->nombre }}!</h2>
        
        <p>Estás a un paso de empezar a compartir rides y ahorrar en transporte.</p>
        
        <p>Para activar tu cuenta, por favor haz clic en el botón de abajo:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('activate', $activationToken) }}" 
               style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                      color: white; 
                      padding: 15px 40px; 
                      text-decoration: none; 
                      border-radius: 5px; 
                      display: inline-block;
                      font-weight: bold;">
                Activar mi cuenta
            </a>
        </div>
        
        <p style="font-size: 14px; color: #666;">
            Si el botón no funciona, copia y pega este enlace en tu navegador:
        </p>
        <p style="font-size: 12px; word-break: break-all; color: #667eea;">
            {{ route('activate', $activationToken) }}
        </p>
        
        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
        
        <p style="font-size: 12px; color: #999; text-align: center;">
            Si no creaste esta cuenta, puedes ignorar este correo.
        </p>
    </div>
</body>
</html>
