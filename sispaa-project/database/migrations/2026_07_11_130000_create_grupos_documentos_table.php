<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo dinámico de grupos de documentos (ej. "Expediente SGA").
     * Reemplaza el arreglo hardcodeado de 4 tipos que hoy vive en
     * StudentPortalController — Secretaría podrá crear/editar los tipos
     * de documento requeridos sin tocar código.
     */
    public function up(): void
    {
        Schema::create('grupos_documentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos_documentos');
    }
};
