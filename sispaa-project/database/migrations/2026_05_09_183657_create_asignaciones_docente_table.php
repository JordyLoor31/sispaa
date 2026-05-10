<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_docente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->foreignId('periodo_id')->constrained('periodos_academicos')->cascadeOnDelete();
            $table->enum('tipo_rol', ['titular', 'auxiliar'])->default('titular');
            $table->timestamps();
 
            $table->unique(['docente_id', 'materia_id', 'periodo_id']);
            $table->index(['docente_id', 'periodo_id']);
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('asignaciones_docente');
    }
};
