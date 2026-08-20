<?php

namespace App\Console\Commands;

use App\Models\Admin\PeriodoAcademico;
use Illuminate\Console\Command;

/**
 * Marca como finalizados los períodos académicos activos cuya fecha_fin ya
 * venció. Programado a diario (ver bootstrap/app.php); el listado del admin
 * también aplica el mismo criterio, así el estado se mantiene consistente
 * aunque no haya cron ejecutándose.
 */
class FinalizarPeriodosVencidos extends Command
{
    protected $signature = 'periodos:finalizar-vencidos';

    protected $description = 'Desactiva los periodos academicos activos cuya fecha_fin ya vencio';

    public function handle(): int
    {
        $periodosAVencer = PeriodoAcademico::query()
            ->where('estado', PeriodoAcademico::ESTADO_ACTIVO)
            ->whereDate('fecha_fin', '<', now()->toDateString())
            ->pluck('id');

        $finalizados = PeriodoAcademico::whereIn('id', $periodosAVencer)
            ->update(['estado' => PeriodoAcademico::ESTADO_FINALIZADO]);

        $this->info("Periodos desactivados: {$finalizados}");

        $estudiantesMarcados = 0;
        foreach ($periodosAVencer as $periodoId) {
            $estudiantesMarcados += PeriodoAcademico::marcarEstudiantesPeriodoVencido($periodoId);
        }

        if ($estudiantesMarcados > 0) {
            $this->info("Estudiantes marcados para actualizar perfil: {$estudiantesMarcados}");
        }

        return self::SUCCESS;
    }
}
