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
        $finalizados = PeriodoAcademico::finalizarVencidos();

        $this->info("Periodos desactivados: {$finalizados}");

        return self::SUCCESS;
    }
}
