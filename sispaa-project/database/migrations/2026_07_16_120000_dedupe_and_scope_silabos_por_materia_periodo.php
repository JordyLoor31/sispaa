<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cambia el sentido de "quién sube el sílabo": antes era por
 * docente+materia+período (cada profesor de cada paralelo tenía su propia
 * fila), ahora es por materia+período nada más. Una materia puede tener
 * dos docentes distintos en paralelos distintos (A y B); el sílabo que
 * suba cualquiera de los dos es el mismo documento y le sirve al otro.
 *
 * Antes de cambiar el índice único hay que fusionar los duplicados que ya
 * existan por materia+período (creados bajo la semántica vieja): se
 * conserva la fila más avanzada (aprobado > subido > pendiente; entre
 * empates, la más reciente) y se elimina el resto.
 */
return new class extends Migration
{
    public function up(): void
    {
        $prioridad = ['aprobado' => 3, 'subido' => 2, 'pendiente' => 1];

        $grupos = DB::table('silabos')
            ->whereNull('deleted_at')
            ->select('materia_id', 'periodo_id')
            ->groupBy('materia_id', 'periodo_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($grupos as $grupo) {
            $filas = DB::table('silabos')
                ->where('materia_id', $grupo->materia_id)
                ->where('periodo_id', $grupo->periodo_id)
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
                DB::table('silabos')->whereIn('id', $idsAEliminar)->delete();
            }
        }

        Schema::table('silabos', function ($table) {
            $table->dropUnique(['docente_id', 'materia_id', 'periodo_id']);
            $table->unique(['materia_id', 'periodo_id']);
        });
    }

    public function down(): void
    {
        Schema::table('silabos', function ($table) {
            $table->dropUnique(['materia_id', 'periodo_id']);
            $table->unique(['docente_id', 'materia_id', 'periodo_id']);
        });
    }
};
