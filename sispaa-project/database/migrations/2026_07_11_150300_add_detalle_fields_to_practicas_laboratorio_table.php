<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Amplía practicas_laboratorio al modelo más rico del sistema origen
     * (Next.js), que además de materia/docente/periodo/ubicación registraba
     * tema, subtema, logro de aprendizaje, semestre, número de estudiantes,
     * horario y el desarrollo completo de la práctica (objetivo, metodología,
     * resultados, conclusiones). 'ubicacion' se conserva para no romper datos
     * existentes, pero el nuevo flujo usa 'laboratorio_id'.
     */
    public function up(): void
    {
        Schema::table('practicas_laboratorio', function (Blueprint $table) {
            $table->foreignId('laboratorio_id')->nullable()->after('periodo_id')
                ->constrained('laboratorios')->nullOnDelete();
            $table->string('tema')->nullable()->after('numero_practica');
            $table->string('subtema')->nullable()->after('tema');
            $table->string('logro_aprendizaje')->nullable()->after('subtema');
            $table->string('semestre', 50)->nullable()->after('logro_aprendizaje');
            $table->unsignedSmallInteger('numero_estudiantes')->nullable()->after('semestre');
            $table->string('horario', 100)->nullable()->after('numero_estudiantes');
            $table->text('objetivo')->nullable()->after('horario');
            $table->text('metodologia')->nullable()->after('objetivo');
            $table->text('resultados')->nullable()->after('metodologia');
            $table->text('conclusiones')->nullable()->after('resultados');
        });
    }

    public function down(): void
    {
        Schema::table('practicas_laboratorio', function (Blueprint $table) {
            $table->dropConstrainedForeignId('laboratorio_id');
            $table->dropColumn([
                'tema', 'subtema', 'logro_aprendizaje', 'semestre',
                'numero_estudiantes', 'horario', 'objetivo', 'metodologia',
                'resultados', 'conclusiones',
            ]);
        });
    }
};
