<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CarreraSeeder extends Seeder
{
    public function run(): void
    {
        $carreras = [
            ['Ingeniería Agroindustrial', 'IA'],
            ['Agronegocios', 'AGN'],
            ['Ingeniería Agropecuaria', 'AGP']
        ];

        foreach ($carreras as [$nombre, $codigo]) {
            $nombreUpper = mb_strtoupper($nombre, 'UTF-8');

            if (DB::table('carreras')->where('codigo', $codigo)->exists()) {
                DB::table('carreras')
                    ->where('codigo', $codigo)
                    ->update([
                        'nombre' => $nombreUpper,
                        'activa' => true,
                        'updated_at' => Carbon::now(),
                    ]);

                if ($this->command) {
                    $this->command->info("Carrera '{$codigo}' actualizada a mayúsculas.");
                }
            } else {
                DB::table('carreras')->insert([
                    'nombre' => $nombreUpper,
                    'codigo' => $codigo,
                    'activa' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                if ($this->command) {
                    $this->command->info("Carrera '{$codigo}' creada.");
                }
            }
        }
    }
}
