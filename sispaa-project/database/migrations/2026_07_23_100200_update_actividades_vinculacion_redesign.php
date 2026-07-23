<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rediseño de la actividad de vinculación:
 *  - empresa_id -> beneficiario_id (renombre)
 *  - fecha      -> fecha_inicio (se ingresa al crear, estado "En ejecución")
 *  - nuevos:  supervisor_id, representante_id, fecha_fin (al Ejecutar),
 *             fecha_cierre + motivo_cancelacion (al Cancelar)
 *  - estado: enum { pendiente, ejecutado } -> { en_ejecucion, ejecutado, cancelado }
 *            (pendiente existentes se migran a en_ejecucion)
 *
 * Nota: en PostgreSQL los enum de Laravel son varchar con un CHECK; el CHECK se
 * elimina ANTES de migrar los valores y se recrea después. Los cambios de
 * esquema usan guardas (hasColumn) para poder re-ejecutarse sin error.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actividades_vinculacion', function (Blueprint $table) {
            if (Schema::hasColumn('actividades_vinculacion', 'empresa_id')) {
                $table->renameColumn('empresa_id', 'beneficiario_id');
            }
            if (Schema::hasColumn('actividades_vinculacion', 'fecha')) {
                $table->renameColumn('fecha', 'fecha_inicio');
            }
        });

        Schema::table('actividades_vinculacion', function (Blueprint $table) {
            if (!Schema::hasColumn('actividades_vinculacion', 'supervisor_id')) {
                $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('actividades_vinculacion', 'representante_id')) {
                $table->foreignId('representante_id')->nullable()->constrained('representantes')->nullOnDelete();
            }
            if (!Schema::hasColumn('actividades_vinculacion', 'fecha_fin')) {
                $table->date('fecha_fin')->nullable();
            }
            if (!Schema::hasColumn('actividades_vinculacion', 'fecha_cierre')) {
                $table->date('fecha_cierre')->nullable();
            }
            if (!Schema::hasColumn('actividades_vinculacion', 'motivo_cancelacion')) {
                $table->text('motivo_cancelacion')->nullable();
            }
        });

        // El CHECK viejo debe eliminarse ANTES de migrar los valores.
        DB::statement('ALTER TABLE actividades_vinculacion DROP CONSTRAINT IF EXISTS actividades_vinculacion_estado_check');
        DB::statement("UPDATE actividades_vinculacion SET estado = 'en_ejecucion' WHERE estado = 'pendiente'");
        DB::statement("ALTER TABLE actividades_vinculacion ADD CONSTRAINT actividades_vinculacion_estado_check CHECK (estado IN ('en_ejecucion', 'ejecutado', 'cancelado'))");
        DB::statement("ALTER TABLE actividades_vinculacion ALTER COLUMN estado SET DEFAULT 'en_ejecucion'");
    }

    public function down(): void
    {
        Schema::table('actividades_vinculacion', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supervisor_id');
            $table->dropConstrainedForeignId('representante_id');
            $table->dropColumn(['fecha_fin', 'fecha_cierre', 'motivo_cancelacion']);
        });

        DB::statement('ALTER TABLE actividades_vinculacion DROP CONSTRAINT IF EXISTS actividades_vinculacion_estado_check');
        DB::statement("UPDATE actividades_vinculacion SET estado = 'ejecutado' WHERE estado = 'cancelado'");
        DB::statement("UPDATE actividades_vinculacion SET estado = 'pendiente' WHERE estado = 'en_ejecucion'");
        DB::statement("ALTER TABLE actividades_vinculacion ADD CONSTRAINT actividades_vinculacion_estado_check CHECK (estado IN ('pendiente', 'ejecutado'))");
        DB::statement("ALTER TABLE actividades_vinculacion ALTER COLUMN estado SET DEFAULT 'pendiente'");

        Schema::table('actividades_vinculacion', function (Blueprint $table) {
            $table->renameColumn('beneficiario_id', 'empresa_id');
            $table->renameColumn('fecha_inicio', 'fecha');
        });
    }
};
