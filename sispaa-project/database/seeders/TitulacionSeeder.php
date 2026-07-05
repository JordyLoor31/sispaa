<?php

namespace Database\Seeders;

use App\Models\Titulacion\Titulacion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TitulacionSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::role('estudiante')->pluck('id')->toArray();
        $teachers = User::role('docente')->pluck('id')->toArray();

        if (empty($students) || empty($teachers)) {
            $this->command?->warn('Faltan estudiantes o docentes para semillar Titulaciones.');
            return;
        }

        $temas = [
            'Diseño de un sistema de control de riego inteligente para cultivos de ciclo corto en Manta',
            'Evaluación del rendimiento agroindustrial de la caña de azúcar utilizando abonos orgánicos',
            'Plan de negocios para la exportación de cacao fino de aroma procesado hacia la Unión Europea',
            'Monitoreo y optimización del uso de recursos en laboratorios agrícolas mediante IoT',
            'Desarrollo de un modelo predictivo para la prevención de plagas en cultivos de banano',
            'Estudio de factibilidad para la producción y comercialización de snacks a base de plátano verde en Manabí',
            'Análisis de la cadena de valor de la producción de leche en la zona rural de Portoviejo',
        ];

        $created = 0;

        foreach ($students as $studentId) {
            // Asignar tema de titulación a aproximadamente el 40% de los estudiantes
            if (rand(1, 100) > 40) {
                continue;
            }

            $exists = Titulacion::where('estudiante_id', $studentId)->exists();
            if ($exists) {
                continue;
            }

            Titulacion::create([
                'estudiante_id' => $studentId,
                'tutor_id' => $teachers[array_rand($teachers)],
                'tema' => $temas[array_rand($temas)],
                'estado' => 'en_proceso',
                'fecha_inicio' => now()->subMonths(rand(1, 6))->toDateString(),
                'fecha_graduacion' => null,
            ]);

            $created++;
        }

        $this->command?->info("Titulaciones creadas: {$created}");
    }
}
