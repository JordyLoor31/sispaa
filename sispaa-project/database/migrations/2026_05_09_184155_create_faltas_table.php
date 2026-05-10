<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faltas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->foreignId('periodo_id')->constrained('periodos_academicos')->cascadeOnDelete();
            $table->date('fecha');
            $table->boolean('justificada')->default(false);
            $table->text('motivo')->nullable();
            $table->timestamps();
 
            $table->index(['estudiante_id', 'periodo_id']);
            $table->index(['materia_id', 'periodo_id']);
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('faltas');
    }
};
