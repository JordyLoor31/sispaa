<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practicas_laboratorio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->foreignId('docente_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('periodo_id')->constrained('periodos_academicos')->cascadeOnDelete();
            $table->string('ubicacion');
            $table->unsignedSmallInteger('numero_practica');
            $table->date('fecha');
            $table->timestamps();
 
            $table->index(['materia_id', 'periodo_id']);
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('practicas_laboratorio');
    }
};
