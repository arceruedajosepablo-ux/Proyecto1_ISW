-- Crear base de datos y tablas para Licu Rides
CREATE DATABASE IF NOT EXISTS licu_rides DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE licu_rides;

-- Usuarios
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role ENUM('admin','driver','passenger') NOT NULL DEFAULT 'passenger',
  nombre VARCHAR(100) NOT NULL,
  apellido VARCHAR(100) NOT NULL,
  cedula VARCHAR(50) NOT NULL,
  fecha_nacimiento DATE NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  telefono VARCHAR(50) NULL,
  foto VARCHAR(255) NULL,
  password VARCHAR(255) NOT NULL,
  status ENUM('pending','active','inactive') NOT NULL DEFAULT 'pending',
  activation_token VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vehículos
CREATE TABLE IF NOT EXISTS vehicles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  placa VARCHAR(50) NOT NULL,
  color VARCHAR(50) NULL,
  marca VARCHAR(100) NULL,
  modelo VARCHAR(100) NULL,
  anio INT NULL,
  capacidad INT DEFAULT 4,
  foto VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Rides
CREATE TABLE IF NOT EXISTS rides (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  vehicle_id INT NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  origen VARCHAR(150) NOT NULL,
  destino VARCHAR(150) NOT NULL,
  fecha DATE NOT NULL,
  hora TIME NOT NULL,
  costo DECIMAL(10,2) DEFAULT 0,
  espacios INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

-- Reservas
CREATE TABLE IF NOT EXISTS reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ride_id INT NOT NULL,
  passenger_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('pending','accepted','rejected','cancelled') NOT NULL DEFAULT 'pending',
  seats INT DEFAULT 1,
  FOREIGN KEY (ride_id) REFERENCES rides(id) ON DELETE CASCADE,
  FOREIGN KEY (passenger_id) REFERENCES users(id) ON DELETE CASCADE
);
