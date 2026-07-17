<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mismo cambio que en silabos (ver migración
 * dedupe_and_scope_silabos_por_materia_periodo): un informe de asignatura
 * ahora se comparte por materia+período+tipo entre los distintos docentes
 * (paralelos) de una misma materia, en vez de guardarse uno por docente.
 * Se fusionan duplicados existentes antes de agregar el índice único.
 */
return new class extends Migration
{
    public function up(): void
    {
        $prioridad = ['aprobado' => 3, 'subido' => 2, 'pendiente' => 1];

        $grupos = DB::table('informes_docente')
            ->whereNull('deleted_at')
            ->select('materia_id', 'periodo_id', 'tipo')
            ->groupBy('materia_id', 'periodo_id', 'tipo')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($grupos as $grupo) {
            $filas = DB::table('informes_docente')
                ->where('materia_id', $grupo->materia_id)
                ->where('periodo_id', $grupo->periodo_id)
                ->where('tipo', $grupo->tipo)
                ->whereNull('deleted_at')
                ->get()
                ->all();

            usort($filas, function ($a, $b) use ($prioridad) {
                $pa = $prioridad[$a->estado] ?? 0;
                $pb = $prioridad[$b->estado] ?? 0;
                if ($pa !== $pb) {
                    return $pb <=> $pa;
                }

                return strtotime($b->fecha_subida ?? $b->updated_at) <=> strtotime($a->fecha_subida ?? $a->updated_at);
            });

            $idsAEliminar = array_slice(array_column($filas, 'id'), 1);

            if (!empty($idsAEliminar)) {
                DB::table('informes_docente')->whereIn('id', $idsAEliminar)->delete();
            }
        }

        Schema::table('informes_docente', function ($table) {
            $table->unique(['materia_id', 'periodo_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::table('informes_docente', function ($table) {
            $table->dropUnique(['materia_id', 'periodo_id', 'tipo']);
        });
    }
};
