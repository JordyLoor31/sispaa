<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('periodo_id')->constrained('periodos_academicos')->cascadeOnDelete();
            $table->foreignId('carrera_id')->constrained('carreras')->cascadeOnDelete();
            $table->enum('estado', ['activo', 'retirado', 'egresado'])->default('activo');
            $table->date('fecha_matricula');
            $table->timestamps();
 
            $table->unique(['estudiante_id', 'periodo_id']);
            $table->index(['carrera_id', 'periodo_id', 'estado']);
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};