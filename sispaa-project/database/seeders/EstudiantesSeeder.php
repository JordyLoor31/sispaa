<?php

namespace Database\Seeders;

use App\Models\Admin\PeriodoAcademico;
use App\Models\Estudiantes\Matricula;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EstudiantesSeeder extends Seeder
{
    public function run(): void
    {
        $carreras = DB::table('carreras')
            ->whereIn('codigo', ['IA', 'AGN', 'AGP'])
            ->orderBy('id')
            ->get()
            ->keyBy('codigo');

        if ($carreras->count() !== 3) {
            $this->command?->warn('No se encontraron las 3 carreras base. Ejecuta CarreraSeeder antes de EstudiantesSeeder.');

            return;
        }

        $carreraDistribution = [
            ['codigo' => 'IA', 'cantidad' => 34],
            ['codigo' => 'AGN', 'cantidad' => 33],
            ['codigo' => 'AGP', 'cantidad' => 33],
        ];

        $fake = fake('es_ES');
        $created = 0;
        $existing = 0;

        foreach ($carreraDistribution as $distribution) {
            $carrera = $carreras[$distribution['codigo']];

            $periodo = PeriodoAcademico::firstOrCreate(
                [
                    'carrera_id' => $carrera->id,
                    'nombre' => '2026-1',
                ],
                [
                    'fecha_inicio' => Carbon::now()->startOfMonth()->toDateString(),
                    'fecha_fin' => Carbon::now()->addMonths(5)->endOfMonth()->toDateString(),
                    'tipo' => 'semestral',
                    'activo' => true,
                ]
            );

            for ($index = 1; $index <= $distribution['cantidad']; $index++) {
                $suffix = str_pad((string) $index, 3, '0', STR_PAD_LEFT);
                $email = strtolower('estudiante.' . $distribution['codigo'] . '.' . $suffix . '@uleam.edu.ec');

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $fake->name(),
                        'cedula' => '09' . str_pad((string) ($carrera->id * 1000000 + $index), 8, '0', STR_PAD_LEFT),
                        'telefono' => '09' . str_pad((string) (7000000 + ($carrera->id * 1000) + $index), 8, '0', STR_PAD_LEFT),
                        'is_active' => true,
                        'email_verified_at' => now(),
                        'password' => Hash::make('Estudiante2026!'),
                        'carrera_id' => $carrera->id,
                    ]
                );

                $user->assignRole('estudiante');

                Matricula::firstOrCreate(
                    [
                        'estudiante_id' => $user->id,
                        'periodo_id' => $periodo->id,
                    ],
                    [
                        'carrera_id' => $carrera->id,
                        'estado' => 'activo',
                        'fecha_matricula' => Carbon::now()->toDateString(),
                    ]
                );

                if ($user->wasRecentlyCreated) {
                    $created++;
                } else {
                    $existing++;
                }
            }
        }

        $this->command?->info("Estudiantes creados: {$created} | Ya existían: {$existing}");
        $this->command?->warn('Contraseña temporal de todos los estudiantes: Estudiante2026!');
    }
}