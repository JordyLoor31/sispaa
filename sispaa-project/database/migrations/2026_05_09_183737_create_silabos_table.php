<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('silabos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->foreignId('periodo_id')->constrained('periodos_academicos')->cascadeOnDelete();
            $table->enum('estado', ['pendiente', 'subido', 'aprobado'])->default('pendiente');
            $table->string('archivo_url')->nullable();
            $table->timestamp('fecha_subida')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
 
            $table->unique(['docente_id', 'materia_id', 'periodo_id']);
            $table->index(['periodo_id', 'estado']);
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('silabos');
    }
};
