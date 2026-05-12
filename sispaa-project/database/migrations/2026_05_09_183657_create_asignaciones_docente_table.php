<?php

// database/migrations/2024_01_01_000008_add_grupo_to_asignaciones_docente_table.php
// Ejecutar DESPUÉS de create_asignaciones_docente_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asignaciones_docente', function (Blueprint $table) {

            // Grupo paralelo: A, B, C ... null = materia sin grupos paralelos
            $table->string('grupo', 5)
                  ->nullable()
                  ->default(null)
                  ->after('tipo_rol')
                  ->comment('Grupo paralelo: A, B, C, etc. Null si la materia no tiene paralelos.');
            $table->dropUnique(['docente_id', 'materia_id', 'periodo_id']);

            $table->unique(
                ['docente_id', 'materia_id', 'periodo_id', 'grupo'],
                'asignaciones_docente_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('asignaciones_docente', function (Blueprint $table) {
            $table->dropUnique('asignaciones_docente_unique');
            $table->dropColumn('grupo');
            $table->unique(['docente_id', 'materia_id', 'periodo_id']);
        });
    }
};