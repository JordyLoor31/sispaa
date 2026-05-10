<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos_academicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_id')->constrained('carreras')->cascadeOnDelete();
            $table->string('nombre');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->enum('tipo', ['semestral', 'anual'])->default('semestral');
            $table->boolean('activo')->default(false);
            $table->timestamps();
 
            $table->index(['carrera_id', 'activo']);
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('periodos_academicos');
    }
};
