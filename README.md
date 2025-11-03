# Licu Rides - Sistema de Gestión de Viajes Compartidos

## Descripción
Licu Rides es una plataforma web que facilita la coordinación de viajes compartidos entre conductores y pasajeros. El sistema permite a los conductores publicar sus rutas disponibles y a los pasajeros reservar espacios en estos viajes.

## Características Principales
- 🚗 Gestión de viajes (rides) con origen, destino, fecha y espacios disponibles
- 👥 Sistema de roles (administrador, conductor, pasajero)
- 🔐 Autenticación y activación de cuentas por correo
- 📱 Interfaz responsive y amigable
- 💰 Gestión de reservas con costos en colones (₡)
- 📧 Sistema de notificaciones por correo

## Requisitos Técnicos
- PHP 7.4 o superior
- MySQL/MariaDB
- Servidor web (Apache recomendado)
- Composer para dependencias
- SMTP para envío de correos (configurable)

## Instalación

### 1. Preparación del Entorno
```bash
# Clonar el repositorio
git clone [url-del-repositorio]
cd licu-rides

# Instalar dependencias
composer install
```

### 2. Configuración de la Base de Datos
1. Crear una base de datos MySQL
2. Importar el esquema:
```bash
mysql -u tu_usuario -p tu_base_de_datos < db/init.sql
```
3. Configurar credenciales en `config.php`

### 3. Configuración del Servidor Web
1. Configurar el dominio `isw.paw.com` en tu servidor web
2. Asegurar que apunte al directorio del proyecto
3. Habilitar mod_rewrite si usas Apache

### 4. Configuración de Correos
1. Copiar `scripts/smtp_config.example.php` a `scripts/smtp_config.php`
2. Configurar credenciales SMTP
3. Asegurar permisos de escritura en la carpeta `emails/`

## Estructura del Proyecto
```
licu-rides/
├── api/                # Endpoints REST
├── CSS/               # Estilos por componente
├── db/                # Scripts de base de datos
├── emails/            # Registro de correos enviados
├── imagenes/         # Recursos estáticos
├── includes/         # Archivos PHP compartidos
├── JS/               # Scripts JavaScript
├── scripts/          # Utilidades y scripts
├── uploads/          # Archivos subidos
└── vendor/           # Dependencias
```

## Uso del Sistema

### Roles de Usuario
1. **Administrador**
   - Gestión de usuarios
   - Supervisión de rides y reservas
   - Acceso a estadísticas

2. **Conductor**
   - Crear y gestionar rides
   - Gestionar vehículos
   - Aceptar/rechazar reservas

3. **Pasajero**
   - Buscar rides disponibles
   - Realizar reservaciones
   - Ver historial de viajes

### Flujo de Trabajo Típico
1. Registro y activación de cuenta
2. Conductores registran vehículos y rides
3. Pasajeros buscan y reservan rides
4. Conductores gestionan solicitudes
5. Sistema notifica por correo los cambios

## Mantenimiento

### Scripts de Utilidad
```bash
# Crear usuario administrador
php db/seed.php

# Notificar reservas pendientes (>30 min)
php scripts/notify_pending.php 30
```

### Limpieza de Archivos
- Revisar y limpiar `/emails/` periódicamente
- Mantener actualizadas las dependencias
- Realizar backups de la base de datos

## Seguridad
- Contraseñas hasheadas con algoritmos seguros
- Validación de sesiones y permisos
- Protección contra SQL injection
- Sanitización de datos de entrada
- Tokens CSRF en formularios

## Credenciales por Defecto
```
Administrador:
- Email: admin@local.test
- Contraseña: Admin123!
```

## Soporte y Contacto
Para reportar problemas o sugerir mejoras:
- Abrir un issue en el repositorio
- Contactar al equipo de desarrollo

## Licencia
Derechos reservados © 2025 Licu Rides
