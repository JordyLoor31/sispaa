<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CarreraSeeder::class);
        $this->call(MateriasSeeder::class);
        $this->call(RolesPermissionsSeeder::class);

        // 1. Crear SystemAdministrador de Prueba
        $admin = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['SystemAdministrador']);

        // 2. Crear Secretaria de Prueba
        $secretaria = User::firstOrCreate(
            ['email' => 'secretaria@example.com'],
            [
                'name' => 'Test Secretaria',
                'password' => Hash::make('Secretaria2026!'),
                'email_verified_at' => now(),
            ]
        );
        $secretaria->syncRoles(['secretaria']);

        // 3. Crear Coordinador de Prueba
        $coordinador = User::firstOrCreate(
            ['email' => 'coordinador@example.com'],
            [
                'name' => 'Test Coordinador',
                'password' => Hash::make('Coordinador2026!'),
                'email_verified_at' => now(),
            ]
        );
        $coordinador->syncRoles(['coordinador']);

        // 4. Ejecutar el resto de seeders
        $this->call(DocentesSeeder::class);
        $this->call(EstudiantesSeeder::class);
        $this->call(FaltasSeeder::class);
        $this->call(DocumentosSeeder::class);
        $this->call(TitulacionSeeder::class);
        $this->call(AsignacionesInformesSeeder::class);
    }
}
