<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cada grupo de documentos tiene N requisitos (ej. "Cédula", "Título de
     * Bachiller"). Cada requisito es lo que el estudiante realmente sube.
     */
    public function up(): void
    {
        Schema::create('requisitos_grupo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained('grupos_documentos')->cascadeOnDelete();
            $table->string('nombre');
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisitos_grupo');
    }
};
