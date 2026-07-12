<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Los periodos académicos (ej. "2026-1") se estaban creando uno por cada
     * carrera en vez de ser una sola entidad compartida por todas las
     * carreras (visible, por ejemplo, en el filtro de período de Reportes
     * mostrando "2026-1" repetido varias veces). Esta migración:
     *
     *   1. Agrupa los periodos duplicados por 'nombre' y elige uno canónico
     *      (el de menor id) por grupo.
     *   2. Reasigna las referencias periodo_id en las tablas dependientes,
     *      depurando en vez de reasignar cuando hacerlo violaría una
     *      restricción unique (matriculas, silabos, asignaciones_docente).
     *   3. Elimina las filas de período duplicadas.
     *   4. Quita la columna carrera_id (con su FK e índice) de
     *      periodos_academicos y agrega un unique a 'nombre' para que la
     *      duplicación no pueda volver a ocurrir.
     */
    public function up(): void
    {
        DB::transaction(function () {
            $gruposDuplicados = DB::table('periodos_academicos')
                ->select('nombre')
                ->groupBy('nombre')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('nombre');

            foreach ($gruposDuplicados as $nombre) {
                $periodos = DB::table('periodos_academicos')
                    ->where('nombre', $nombre)
                    ->orderBy('id')
                    ->get();

                $canonico = $periodos->first();
                $duplicados = $periodos->slice(1)->pluck('id')->all();

                if (empty($duplicados)) {
                    continue;
                }

                // Si algún duplicado estaba activo y el canónico no, el
                // canónico hereda el estado activo para no perder esa señal.
                if ($periodos->firstWhere('activo', true) && !$canonico->activo) {
                    DB::table('periodos_academicos')->where('id', $canonico->id)->update(['activo' => true]);
                }

                // Tablas con clave única que incluye periodo_id: si ya existe
                // una fila equivalente apuntando al canónico, se elimina la
                // duplicada en vez de reasignar (evita violar el unique).
                $this->remapConClaveUnica('matriculas', ['estudiante_id'], $canonico->id, $duplicados);
                $this->remapConClaveUnica('silabos', ['docente_id', 'materia_id'], $canonico->id, $duplicados);
                $this->remapConClaveUnica('asignaciones_docente', ['docente_id', 'materia_id', 'grupo'], $canonico->id, $duplicados);

                // Tablas sin restricción de unicidad sobre periodo_id: reasignación directa.
                foreach (['informes_docente', 'practicas_laboratorio', 'actividades_vinculacion', 'investigaciones', 'faltas'] as $tabla) {
                    DB::table($tabla)->whereIn('periodo_id', $duplicados)->update(['periodo_id' => $canonico->id]);
                }

                DB::table('periodos_academicos')->whereIn('id', $duplicados)->delete();
            }
        });

        Schema::table('periodos_academicos', function (Blueprint $table) {
            $table->dropForeign(['carrera_id']);
            $table->dropIndex(['carrera_id', 'activo']);
            $table->dropColumn('carrera_id');
            $table->unique('nombre');
        });
    }

    /**
     * Reasigna periodo_id de $duplicados hacia $canonicoId en $tabla, pero si
     * ya existe una fila con la misma combinación de $clave + periodo_id
     * canónico, elimina la fila duplicada en vez de violar un unique.
     */
    private function remapConClaveUnica(string $tabla, array $clave, int $canonicoId, array $duplicados): void
    {
        $filas = DB::table($tabla)->whereIn('periodo_id', $duplicados)->get();

        foreach ($filas as $fila) {
            $condicionCanonico = ['periodo_id' => $canonicoId];
            foreach ($clave as $campo) {
                $condicionCanonico[$campo] = $fila->{$campo};
            }

            $yaExiste = DB::table($tabla)->where($condicionCanonico)->exists();

            if ($yaExiste) {
                DB::table($tabla)->where('id', $fila->id)->delete();
            } else {
                DB::table($tabla)->where('id', $fila->id)->update(['periodo_id' => $canonicoId]);
            }
        }
    }

    public function down(): void
    {
        // La fusión de duplicados y reasignación de periodo_id no es
        // reversible (no se puede reconstruir qué carrera tenía cada periodo
        // original); down() solo restaura la forma de la columna, nullable
        // porque ya no hay con qué rellenarla.
        Schema::table('periodos_academicos', function (Blueprint $table) {
            $table->dropUnique(['nombre']);
            $table->foreignId('carrera_id')->nullable()->after('id')->constrained('carreras')->cascadeOnDelete();
            $table->index(['carrera_id', 'activo']);
        });
    }
};
