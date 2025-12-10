<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::create([
            'role' => 'admin',
            'nombre' => 'Admin',
            'apellido' => 'Sistema',
            'cedula' => '000000000',
            'email' => 'admin@licurides.com',
            'password' => Hash::make('admin123'),
            'status' => 'active',
            'telefono' => '8888-8888',
        ]);

        // Driver user
        User::create([
            'role' => 'driver',
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'cedula' => '123456789',
            'email' => 'driver@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'telefono' => '8888-1234',
            'fecha_nacimiento' => '1990-05-15',
        ]);

        // Passenger user
        User::create([
            'role' => 'passenger',
            'nombre' => 'María',
            'apellido' => 'González',
            'cedula' => '987654321',
            'email' => 'passenger@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'telefono' => '8888-5678',
            'fecha_nacimiento' => '1995-08-20',
        ]);
    }
}
