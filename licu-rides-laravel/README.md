# 🚗 Licu Rides - Laravel Edition

Sistema de gestión de viajes compartidos (carpooling) desarrollado con Laravel 12, aplicando arquitectura MVC profesional.

## 📋 Descripción del Proyecto

**Licu Rides** es una plataforma web que conecta conductores y pasajeros para compartir viajes y reducir costos de transporte. Esta versión representa la migración profesional del proyecto original PHP vanilla a Laravel Framework.

### Características Principales

- 🔐 **Autenticación Completa**: Registro, login, activación por email
- 👥 **Sistema de Roles**: Admin, Driver (Conductor), Passenger (Pasajero)
- 🚗 **Gestión de Vehículos**: Registro y administración de vehículos para conductores
- 🗺️ **Gestión de Rides**: Crear, editar y eliminar viajes
- 📅 **Sistema de Reservaciones**: Solicitar, aceptar, rechazar reservas
- 📧 **Notificaciones por Email**: Activación, confirmaciones, recordatorios
- 📊 **Panel Administrativo**: Gestión completa de usuarios y rides
- ✅ **Tests Unitarios**: Cobertura con PHPUnit

## 🛠️ Tecnologías Utilizadas

- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Base de Datos**: SQLite (desarrollo) / MySQL (producción)
- **Mail**: SMTP / Mailtrap
- **Testing**: PHPUnit
- **Frontend**: Blade Templates + CSS/JavaScript

## 📦 Instalación

### Requisitos Previos

- PHP >= 8.2
- Composer
- MySQL (para producción) o SQLite (para desarrollo)
- Node.js y NPM (opcional, para compilar assets)

### Pasos de Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/tu-usuario/licu-rides-laravel.git
cd licu-rides-laravel

# 2. Instalar dependencias
composer install

# 3. Copiar archivo de configuración
cp .env.example .env

# 4. Generar key de aplicación
php artisan key:generate

# 5. Configurar base de datos en .env
# Para SQLite (desarrollo):
DB_CONNECTION=sqlite
# (El archivo database.sqlite ya está creado)

# Para MySQL (producción):
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=licu_rides
DB_USERNAME=root
DB_PASSWORD=tu_password

# 6. Ejecutar migraciones y seeders
php artisan migrate --seed

# 7. Crear enlace simbólico para storage
php artisan storage:link

# 8. Iniciar servidor de desarrollo
php artisan serve
```

Accede a la aplicación en: `http://localhost:8000`

## 👤 Usuarios de Prueba

Después de ejecutar los seeders, tendrás estos usuarios disponibles:

| Rol       | Email                  | Password    |
|-----------|------------------------|-------------|
| Admin     | admin@licurides.com    | admin123    |
| Driver    | driver@example.com     | password    |
| Passenger | passenger@example.com  | password    |

## 🏗️ Arquitectura del Proyecto

### Estructura de Directorios

```
licu-rides-laravel/
├── app/
│   ├── Console/Commands/     # Comandos Artisan personalizados
│   ├── Http/
│   │   ├── Controllers/      # Controladores MVC
│   │   └── Middleware/       # Middleware personalizados (CheckRole)
│   ├── Mail/                 # Mailables para emails
│   ├── Models/               # Modelos Eloquent
│   └── Policies/             # Políticas de autorización
├── database/
│   ├── factories/            # Factories para testing
│   ├── migrations/           # Migraciones de base de datos
│   └── seeders/              # Seeders de datos iniciales
├── resources/
│   └── views/                # Vistas Blade
├── routes/
│   ├── web.php              # Rutas web
│   └── api.php              # Rutas API
├── tests/
│   ├── Feature/             # Tests de integración
│   └── Unit/                # Tests unitarios
└── public/                  # Assets públicos (CSS, JS, imágenes)
```

### Modelos y Relaciones

#### User
- Tiene muchos: Vehicles, Rides, Reservations
- Roles: admin, driver, passenger
- Estados: pending, active, inactive

#### Vehicle
- Pertenece a: User (driver)
- Tiene muchos: Rides

#### Ride
- Pertenece a: User (driver), Vehicle
- Tiene muchos: Reservations
- Atributo calculado: `espacios_disponibles`

#### Reservation
- Pertenece a: Ride, User (passenger)
- Estados: pending, accepted, rejected, cancelled

## 🔑 Funcionalidades por Rol

### 🔴 Administrador
- Ver estadísticas generales del sistema
- Gestionar usuarios (activar, desactivar, eliminar)
- Ver todos los rides y reservaciones
- Acceso completo a todas las funcionalidades

### 🟢 Conductor (Driver)
- Registrar y gestionar vehículos
- Crear, editar y eliminar rides
- Ver y gestionar solicitudes de reservación
- Aceptar o rechazar reservas

### 🔵 Pasajero (Passenger)
- Buscar rides disponibles
- Solicitar reservaciones
- Ver historial de reservaciones
- Cancelar reservaciones propias

## 📧 Sistema de Correos

### Configuración

Edita tu `.env` para configurar el servicio de correo:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@licurides.com
MAIL_FROM_NAME="Licu Rides"
```

### Para Gmail:
1. Habilita "Verificación en 2 pasos"
2. Genera una "Contraseña de aplicación"
3. Usa esa contraseña en `MAIL_PASSWORD`

### Para Desarrollo (Mailtrap):
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu-username-mailtrap
MAIL_PASSWORD=tu-password-mailtrap
```

### Tipos de Emails

- **Activación de cuenta**: Enviado al registrarse
- **Confirmación de reserva**: Al aceptar/rechazar reservas
- **Recordatorios**: Para reservas pendientes

## 🧪 Testing

### Ejecutar Tests

```bash
# Todos los tests
php artisan test

# Tests específicos
php artisan test --filter=UserTest
php artisan test --filter=RideTest

# Con cobertura
php artisan test --coverage
```

## 🚀 Comandos Artisan

### Comandos Estándar

```bash
# Ver rutas
php artisan route:list

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Ejecutar migraciones
php artisan migrate

# Resetear base de datos con seeders
php artisan migrate:fresh --seed
```

### Comandos Personalizados

```bash
# Notificar reservas pendientes por más de 30 minutos
php artisan reservations:notify-pending 30
```

## 📝 Guía de Migración

Consulta `MIGRACION_GUIA.md` para obtener información detallada sobre:
- Controladores completos con código
- Configuración de rutas
- Creación de vistas Blade
- Políticas de autorización
- Mailables adicionales
- Tests unitarios
- Comandos Artisan personalizados

## 🤝 Contribuciones

Este es un proyecto educativo. Las contribuciones son bienvenidas siguiendo las mejores prácticas de Laravel.

## 📄 Licencia

Proyecto académico bajo licencia MIT.

## 👨‍💻 Autor

**Proyecto Académico - Ingeniería de Software**  
Migración de PHP Vanilla a Laravel Framework

---

**¡Pura vida!** 🇨🇷 Disfruta compartiendo rides con Licu Rides.
