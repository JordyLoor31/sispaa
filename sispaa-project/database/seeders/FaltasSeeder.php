<?php

namespace Database\Seeders;

use App\Models\Estudiantes\Falta;
use App\Models\Documentos\DocumentoEstudiante;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaltasSeeder extends Seeder
{
    public function run(): void
    {
        $students = DB::table('users')
            ->where('email', 'like', 'estudiante.%@uleam.edu.ec')
            ->pluck('id')
            ->toArray();

        $totalStudents = count($students);

        if ($totalStudents === 0) {
            $this->command?->warn('No se encontraron estudiantes semillados (estudiante.%@uleam.edu.ec). Ejecuta EstudiantesSeeder primero.');
            return;
        }

        $materias = DB::table('materias')->pluck('id')->toArray();
        $periodos = DB::table('periodos_academicos')->pluck('id')->toArray();

        if (empty($materias) || empty($periodos)) {
            $this->command?->warn('Faltan materias o periodos. Ejecuta los seeders correspondientes.');
            return;
        }

        shuffle($students);

        $created = 0;
        $justified = 0;

        foreach ($students as $studentId) {
            // Create at most one falta per student (so total faltas == total students)
            $falta = Falta::create([
                'estudiante_id' => $studentId,
                'materia_id' => $materias[array_rand($materias)],
                'periodo_id' => $periodos[array_rand($periodos)],
                'fecha' => Carbon::now()->subDays(rand(1, 120))->toDateString(),
                'justificada' => false,
                'motivo' => 'Falta registrada por prueba de seeder',
            ]);

            $created++;

            // Randomly mark ~20% as justified and create a document record for justifications
            if (rand(1, 100) <= 20) {
                $falta->update(['justificada' => true]);

                DocumentoEstudiante::create([
                    'estudiante_id' => $studentId,
                    'secretaria_id' => null,
                    'tipo_documento' => 'Justificación',
                    'archivo_url' => '/storage/justificaciones/justificacion_' . $studentId . '.pdf',
                    'estado' => (rand(1, 100) <= 60) ? 'aprobado' : 'pendiente',
                    'observacion' => 'Documento generado por seeder',
                    'reviewed_at' => (rand(1, 100) <= 60) ? Carbon::now() : null,
                ]);

                $justified++;
            }
        }

        $this->command?->info("Faltas creadas: {$created} | Justificadas: {$justified}");
    }
}
