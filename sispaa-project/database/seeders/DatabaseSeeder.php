<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\CarreraSeeder;
use Database\Seeders\MateriasSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Database\Seeders\DocentesSeeder;
use Database\Seeders\EstudiantesSeeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call(CarreraSeeder::class);
        $this->call(MateriasSeeder::class);
        $this->call(RolesPermissionsSeeder::class);
        $this->call(DocentesSeeder::class);
        $this->call(EstudiantesSeeder::class);
    }
}
