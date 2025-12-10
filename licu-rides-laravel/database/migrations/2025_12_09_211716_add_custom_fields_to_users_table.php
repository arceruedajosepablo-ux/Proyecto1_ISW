<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'driver', 'passenger'])->default('passenger')->after('id');
            $table->string('nombre', 100)->after('role');
            $table->string('apellido', 100)->after('nombre');
            $table->string('cedula', 50)->after('apellido');
            $table->date('fecha_nacimiento')->nullable()->after('cedula');
            $table->string('telefono', 50)->nullable()->after('email');
            $table->string('foto')->nullable()->after('telefono');
            $table->enum('status', ['pending', 'active', 'inactive'])->default('pending')->after('password');
            $table->string('activation_token')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'nombre', 'apellido', 'cedula', 'fecha_nacimiento',
                'telefono', 'foto', 'status', 'activation_token'
            ]);
        });
    }
};
