Proyecto Licu Rides - Instrucciones (PHP + MySQL / XAMPP)

Resumen
-------
Este proyecto ha sido adaptado para usar PHP procedural y MySQL (XAMPP). El envío de correos en desarrollo se guarda como archivos HTML en la carpeta `emails/`.

Pasos para preparar el entorno
-----------------------------
1. Instalar XAMPP y arrancar Apache + MySQL.
2. El sistema está configurado para usar el dominio `http://isw.paw.com`. Asegúrate de que este dominio esté configurado en tu servidor web y apunte al directorio del proyecto.
3. Importar el archivo `db/init.sql` usando phpMyAdmin o correrlo en MySQL para crear la estructura de la BD.
   Alternativa: edita `config.php` y cambia `DB_NAME` a una existente, luego ejecuta `php db/seed.php` desde la línea de comandos para crear las tablas y el admin.

Crear usuario administrador
--------------------------
Ejecuta desde la línea de comandos (usar PHP de XAMPP si no está en PATH):

```powershell
C:\xampp\php\php.exe db\seed.php
```

Credenciales por defecto (seed)
-------------------------------
- Email: admin@local.test
- Contraseña: Admin123!

Probar registro y activación
----------------------------
1. Abre `http://isw.paw.com/registro.html`.
2. Regístrate como pasajero o chofer; el sistema dejará la cuenta en estado `pending`.
3. Se generará un archivo HTML con el correo de activación en la carpeta `emails/` (ver `config.php`). Abre ese archivo y copia el enlace de activación o haz clic en él.

Ejecutar script de notificaciones (reservas pendientes)
-----------------------------------------------------
Ejemplo: notificar reservas pendientes de más de 30 minutos

```powershell
C:\xampp\php\php.exe scripts\notify_pending.php 30
```

Implementación actual y siguientes pasos
--------------------------------------
- Endpoints básicos: registro (`api/register.php`), activación (`api/activate.php`), login (`api/login.php`).
- Utilidad para guardar correos en `emails/` (`scripts/send_mail.php`).
- Scripts DB: `db/init.sql` (esquema) y `db/seed.php` (seed admin).

Próximas tareas recomendadas
---------------------------
- Implementar endpoints CRUD para vehículos (`api/vehicles.php`), rides (`api/rides.php`) y reservas (`api/reservations.php`).
- Añadir comprobaciones de sesión/rol (includes) y páginas protegidas.
- Mejorar UI para consumir la API (AJAX) y manejar estados (pending/active/inactive).

Si deseas que continúe implementando los endpoints CRUD y la integración completa con las páginas (`addRide.html`, `dashboard.html`), dime y lo haré en la siguiente iteración.
