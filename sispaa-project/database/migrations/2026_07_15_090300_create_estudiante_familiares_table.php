<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiante_familiares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Enum a nivel de aplicación (no DB enum), mismo patrón que
            // periodos_academicos.estado / silabos.estado en este proyecto.
            $table->string('parentesco', 20);

            $table->string('nombres');
            $table->string('cedula', 10)->nullable();
            $table->string('telefono', 15)->nullable();
            $table->string('correo')->nullable();
            $table->string('ocupacion')->nullable();
            $table->string('empresa')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'parentesco']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiante_familiares');
    }
};
