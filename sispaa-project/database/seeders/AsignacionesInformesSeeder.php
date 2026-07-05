<?php

namespace Database\Seeders;

use App\Models\Admin\PeriodoAcademico;
use App\Models\Docencia\Materia;
use App\Models\Docencia\InformeDocente;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsignacionesInformesSeeder extends Seeder
{
    public function run(): void
    {
        $docentes = User::role('docente')->get();
        $materias = Materia::all();
        $periodos = PeriodoAcademico::all();

        if ($docentes->isEmpty() || $materias->isEmpty() || $periodos->isEmpty()) {
            $this->command?->warn('Faltan docentes, materias o periodos para semillar asignaciones e informes.');
            return;
        }

        $createdAssignments = 0;
        $createdReports = 0;

        foreach ($docentes as $docente) {
            // Asignar de 2 a 3 materias por periodo a cada docente
            $docenteMaterias = $materias->random(rand(2, 3));
            foreach ($docenteMaterias as $materia) {
                foreach ($periodos as $periodo) {
                    // 1. Crear asignación
                    $exists = DB::table('asignaciones_docente')->where([
                        'docente_id' => $docente->id,
                        'materia_id' => $materia->id,
                        'periodo_id' => $periodo->id,
                    ])->exists();

                    if (!$exists) {
                        DB::table('asignaciones_docente')->insert([
                            'docente_id' => $docente->id,
                            'materia_id' => $materia->id,
                            'periodo_id' => $periodo->id,
                            'tipo_rol' => 'Principal',
                            'grupo' => rand(0, 1) ? 'A' : 'B',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $createdAssignments++;
                    }

                    // 2. Crear informe docente para esta asignatura y periodo
                    $estadoRand = rand(1, 100);
                    $estado = 'pendiente';
                    $url = null;
                    $fechaSubida = null;

                    if ($estadoRand <= 60) {
                        $estado = 'aprobado';
                        $url = '/storage/informes/inf_' . $docente->id . '_' . $materia->id . '.pdf';
                        $fechaSubida = now()->subDays(rand(1, 30));
                    } elseif ($estadoRand <= 90) {
                        $estado = 'subido';
                        $url = '/storage/informes/inf_' . $docente->id . '_' . $materia->id . '.pdf';
                        $fechaSubida = now()->subDays(rand(1, 5));
                    }

                    InformeDocente::create([
                        'docente_id' => $docente->id,
                        'materia_id' => $materia->id,
                        'periodo_id' => $periodo->id,
                        'tipo' => 'asignatura',
                        'estado' => $estado,
                        'archivo_url' => $url,
                        'fecha_subida' => $fechaSubida,
                    ]);
                    $createdReports++;
                }
            }
        }

        $this->command?->info("Asignaciones creadas: {$createdAssignments} | Informes docentes creados: {$createdReports}");
    }
}
