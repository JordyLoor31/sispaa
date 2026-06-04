<?php

namespace Database\Seeders;

use App\Models\Documentos\DocumentoEstudiante;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DocumentosSeeder extends Seeder
{
    public function run(): void
    {
        $students = DB::table('users')
            ->where('email', 'like', 'estudiante.%@uleam.edu.ec')
            ->pluck('id')
            ->toArray();

        $totalStudents = count($students);

        if ($totalStudents === 0) {
            $this->command?->warn('No se encontraron estudiantes semillados. Ejecuta EstudiantesSeeder primero.');
            return;
        }

        $created = 0;

        foreach ($students as $studentId) {
            // If the student already has any documento, skip to avoid exceeding student count
            $exists = DB::table('documentos_estudiante')->where('estudiante_id', $studentId)->exists();

            if ($exists) {
                continue;
            }

            // Create a document for about 30% of students
            if (rand(1, 100) > 30) {
                continue;
            }

            DocumentoEstudiante::create([
                'estudiante_id' => $studentId,
                'secretaria_id' => null,
                'tipo_documento' => (rand(1, 100) <= 50) ? 'Constancia' : 'Certificado',
                'archivo_url' => '/storage/documentos/doc_' . $studentId . '.pdf',
                'estado' => 'pendiente',
                'observacion' => 'Documento de prueba generado por seeder',
                'reviewed_at' => null,
            ]);

            $created++;
        }

        $this->command?->info("Documentos creados: {$created} (no exceden el número de estudiantes)");
    }
}
