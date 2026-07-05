<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('justificaciones_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('falta_id')->constrained('faltas')->cascadeOnDelete();
            $table->text('motivo_estudiante');
            $table->string('archivo_adjunto')->nullable();
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');
            $table->text('comentario_docente')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('justificaciones_solicitudes');
    }
};
