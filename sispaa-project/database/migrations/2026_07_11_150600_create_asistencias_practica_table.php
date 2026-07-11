<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias_practica', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practica_id')->constrained('practicas_laboratorio')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('asistio')->default(false);
            $table->timestamps();

            $table->unique(['practica_id', 'estudiante_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias_practica');
    }
};
