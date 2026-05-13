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
            $table->foreignId('docente_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
            $table->foreignId('periodo_id')->constrained('periodos_academicos')->onDelete('cascade');
            $table->string('tipo_rol');
            
            // Grupo paralelo: A, B, C ... null = materia sin grupos paralelos
            $table->string('grupo', 5)
                  ->nullable()
                  ->default(null)
                  ->comment('Grupo paralelo: A, B, C, etc. Null si la materia no tiene paralelos.');
            
            $table->unique(
                ['docente_id', 'materia_id', 'periodo_id', 'grupo'],
                'asignaciones_docente_unique'
            );
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_docente');
    }
};