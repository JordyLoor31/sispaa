<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Plantillas de Documentos: formatos institucionales (solicitudes,
 * certificados, etc.) que Secretaría sube para que cualquier estudiante
 * pueda descargarlos desde su portal (menú "Plantillas").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantillas_documentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_doc');
            $table->string('url_doc');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantillas_documentos');
    }
};
