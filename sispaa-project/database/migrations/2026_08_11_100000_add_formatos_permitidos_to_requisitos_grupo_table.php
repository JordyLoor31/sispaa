<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Formatos de archivo permitidos por requisito (ej. solo PDF). Guarda un
     * arreglo JSON de extensiones; null significa "todos los permitidos por el
     * sistema" (pdf, jpg, png, jpeg).
     */
    public function up(): void
    {
        Schema::table('requisitos_grupo', function (Blueprint $table) {
            $table->json('formatos_permitidos')->nullable()->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('requisitos_grupo', function (Blueprint $table) {
            $table->dropColumn('formatos_permitidos');
        });
    }
};
