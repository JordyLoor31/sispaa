<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Restructuración de equipo del proyecto de investigación: el antiguo campo
 * "Coordinador Supervisor" (coordinador_id) pasa a ser "Líder de Proyecto"
 * (lider_id), y se agrega un "Colíder" (colider_id) opcional. Los
 * "miembros" (varios) se modelan en una tabla pivote aparte
 * (investigacion_miembros, ver migración siguiente).
 *
 * docente_id (quien crea/gestiona el registro administrativamente) se
 * mantiene sin cambios: sigue siendo el dueño del proyecto para efectos de
 * edición de título/objetivo y eliminación.
 *
 * No se usa Schema::table()->change() (requiere doctrine/dbal, no instalado
 * en este proyecto): en su lugar, se agregan columnas nuevas, se rellenan
 * con un backfill desde coordinador_id, y se elimina la columna vieja.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investigaciones', function (Blueprint $table) {
            $table->foreignId('lider_id')->nullable()->after('coordinador_id')->constrained('users')->nullOnDelete();
            $table->foreignId('colider_id')->nullable()->after('lider_id')->constrained('users')->nullOnDelete();
        });

        // Backfill: el coordinador supervisor asignado pasa a ser el líder de proyecto.
        DB::statement('UPDATE investigaciones SET lider_id = coordinador_id');

        Schema::table('investigaciones', function (Blueprint $table) {
            $table->dropForeign(['coordinador_id']);
            $table->dropColumn('coordinador_id');
        });
    }

    public function down(): void
    {
        Schema::table('investigaciones', function (Blueprint $table) {
            $table->foreignId('coordinador_id')->nullable()->after('docente_id')->constrained('users')->cascadeOnDelete();
        });

        DB::statement('UPDATE investigaciones SET coordinador_id = lider_id');

        Schema::table('investigaciones', function (Blueprint $table) {
            $table->dropForeign(['lider_id']);
            $table->dropForeign(['colider_id']);
            $table->dropColumn(['lider_id', 'colider_id']);
        });
    }
};
