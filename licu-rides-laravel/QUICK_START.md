# 🚀 Quick Start - Licu Rides Laravel

## Inicio Rápido (5 minutos)

### 1. Verificar Instalación
```bash
cd c:\Users\jpabl\Documents\GitHub\Proyecto1_ISW\licu-rides-laravel
php --version  # Debe ser >= 8.2
composer --version
```

### 2. Configurar Base de Datos
El proyecto ya está configurado con SQLite. ¡No necesitas hacer nada!

### 3. Iniciar Servidor
```bash
php artisan serve
```

Abre tu navegador en: http://localhost:8000

### 4. Iniciar Sesión
Usa estos usuarios de prueba:

**Administrador:**
- Email: `admin@licurides.com`
- Password: `admin123`

**Conductor:**
- Email: `driver@example.com`
- Password: `password`

**Pasajero:**
- Email: `passenger@example.com`
- Password: `password`

---

## ✅ Lo que YA está Funcionando

1. **Base de datos** configurada con 4 tablas
2. **3 usuarios de prueba** creados automáticamente
3. **Modelos** con todas las relaciones
4. **Sistema de login** básico
5. **Middleware de roles** para proteger rutas
6. **Email de activación** configurado

---

## 📝 Lo que FALTA por Hacer

### Prioridad 1: Rutas y Controladores
1. Copiar código de controladores desde `MIGRACION_GUIA.md`
2. Configurar rutas en `routes/web.php`

### Prioridad 2: Vistas
1. Crear vista de login
2. Crear dashboard
3. Crear vistas de rides

### Prioridad 3: Frontend
1. Copiar CSS del proyecto original
2. Copiar JavaScript del proyecto original

---

## 🔧 Comandos Útiles

```bash
# Ver todas las rutas
php artisan route:list

# Ver estructura de base de datos
php artisan db:show
php artisan db:table users

# Resetear base de datos
php artisan migrate:fresh --seed

# Ejecutar tests (cuando los crees)
php artisan test

# Limpiar cache
php artisan cache:clear
php artisan config:clear
```

---

## 📚 Documentación Completa

- **RESUMEN_MIGRACION.md** - Estado actual del proyecto
- **MIGRACION_GUIA.md** - Código completo de controladores, rutas, tests
- **README.md** - Documentación del proyecto completo

---

## 🎯 Siguiente Paso Sugerido

**Implementar el login funcional:**

1. Abre `routes/web.php`
2. Agrega estas rutas:
```php
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
```

3. Crea vista simple de login en `resources/views/auth/login.blade.php`

4. Prueba: http://localhost:8000/login

---

## 🆘 Problemas Comunes

### "Target class does not exist"
```bash
php artisan optimize:clear
composer dump-autoload
```

### "Database not found"
```bash
# Verificar que existe database/database.sqlite
php artisan migrate:fresh --seed
```

### "CSRF token mismatch"
```bash
php artisan cache:clear
# Asegúrate de incluir @csrf en tus formularios
```

---

## 📧 Configurar Email (Opcional)

Para probar el sistema de emails:

1. Regístrate en https://mailtrap.io (gratis)
2. Copia las credenciales SMTP
3. Actualiza `.env`:
```env
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_username
MAIL_PASSWORD=tu_password
```

---

**¡Listo para comenzar!** 🎉

La base está completa. Ahora solo necesitas agregar las vistas y copiar el código de los controladores.
