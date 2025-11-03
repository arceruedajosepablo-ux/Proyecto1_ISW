-- Script para crear la base de datos y todas las tablas del sistema Licu Rides
-- Ejecutar este archivo para inicializar la base de datos desde cero

CREATE DATABASE IF NOT EXISTS licu_rides DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE licu_rides;

-- Tabla de usuarios - aquí van conductores, pasajeros y administradores
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role ENUM('admin','driver','passenger') NOT NULL DEFAULT 'passenger',  -- Tipo de usuario
  nombre VARCHAR(100) NOT NULL,
  apellido VARCHAR(100) NOT NULL,
  cedula VARCHAR(50) NOT NULL,                    -- Cédula costarricense
  fecha_nacimiento DATE NULL,
  email VARCHAR(150) NOT NULL UNIQUE,             -- Email único para cada usuario
  telefono VARCHAR(50) NULL,
  foto VARCHAR(255) NULL,                         -- Ruta a la foto de perfil
  password VARCHAR(255) NOT NULL,                 -- Contraseña hasheada
  status ENUM('pending','active','inactive') NOT NULL DEFAULT 'pending',  -- Estado de la cuenta
  activation_token VARCHAR(255) NULL,             -- Token para activar cuenta por email
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de vehículos - solo los conductores pueden tener vehículos
CREATE TABLE IF NOT EXISTS vehicles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,                           -- ID del conductor dueño del vehículo
  placa VARCHAR(50) NOT NULL,                     -- Placa del vehículo
  color VARCHAR(50) NULL,
  marca VARCHAR(100) NULL,
  modelo VARCHAR(100) NULL,
  anio INT NULL,
  capacidad INT DEFAULT 4,                        -- Cuántos pasajeros puede llevar
  foto VARCHAR(255) NULL,                         -- Foto del vehículo
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla de rides/viajes - los conductores publican sus viajes aquí
CREATE TABLE IF NOT EXISTS rides (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,                           -- ID del conductor que ofrece el ride
  vehicle_id INT NOT NULL,                        -- Vehículo que se va a usar
  nombre VARCHAR(150) NOT NULL,                   -- Nombre descriptivo del ride
  origen VARCHAR(150) NOT NULL,                   -- De dónde sale
  destino VARCHAR(150) NOT NULL,                  -- A dónde va
  fecha DATE NOT NULL,                            -- Fecha del viaje
  hora TIME NOT NULL,                             -- Hora de salida
  costo DECIMAL(10,2) DEFAULT 0,                  -- Precio por persona en colones
  espacios INT DEFAULT 1,                         -- Cuántos espacios disponibles
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

-- Tabla de reservaciones - cuando los pasajeros solicitan espacios en rides
CREATE TABLE IF NOT EXISTS reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ride_id INT NOT NULL,                           -- ID del ride que se quiere reservar
  passenger_id INT NOT NULL,                      -- ID del pasajero que hace la reserva
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('pending','accepted','rejected','cancelled') NOT NULL DEFAULT 'pending',
  seats INT DEFAULT 1,
  FOREIGN KEY (ride_id) REFERENCES rides(id) ON DELETE CASCADE,
  FOREIGN KEY (passenger_id) REFERENCES users(id) ON DELETE CASCADE
);
