<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega 'descripcion' a plantillas_documentos: texto opcional que explica
 * para qué sirve cada formato institucional, mostrado junto al nombre en el
 * formulario de Secretaría (crear/editar plantilla).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plantillas_documentos', function (Blueprint $table) {
            $table->text('descripcion')->nullable()->after('nombre_doc');
        });
    }

    public function down(): void
    {
        Schema::table('plantillas_documentos', function (Blueprint $table) {
            $table->dropColumn('descripcion');
        });
    }
};
