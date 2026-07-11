<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enlaza cada documento subido por un estudiante al catálogo dinámico
     * (grupo/requisito) y, opcionalmente, a la convocatoria bajo la que se
     * subió. Todos nullable para no romper documentos ya existentes.
     */
    public function up(): void
    {
        Schema::table('documentos_estudiante', function (Blueprint $table) {
            $table->foreignId('grupo_id')->nullable()->after('estudiante_id')
                ->constrained('grupos_documentos')->nullOnDelete();
            $table->foreignId('requisito_id')->nullable()->after('grupo_id')
                ->constrained('requisitos_grupo')->nullOnDelete();
            $table->foreignId('convocatoria_id')->nullable()->after('requisito_id')
                ->constrained('convocatorias')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documentos_estudiante', function (Blueprint $table) {
            $table->dropConstrainedForeignId('grupo_id');
            $table->dropConstrainedForeignId('requisito_id');
            $table->dropConstrainedForeignId('convocatoria_id');
        });
    }
};
