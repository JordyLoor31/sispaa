<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividades_vinculacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_lider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('carrera_id')->constrained('carreras')->cascadeOnDelete();
            $table->foreignId('periodo_id')->constrained('periodos_academicos')->cascadeOnDelete();
            $table->foreignId('empresa_id')->nullable()
                  ->constrained('empresas')->nullOnDelete();
            $table->string('nombre');
            $table->enum('estado', ['pendiente', 'ejecutado'])->default('pendiente');
            $table->date('fecha')->nullable();
            $table->timestamps();
            $table->softDeletes();
 
            $table->index(['carrera_id', 'periodo_id', 'estado']);
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('actividades_vinculacion');
    }
};
