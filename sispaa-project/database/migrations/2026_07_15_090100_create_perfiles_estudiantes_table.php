<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfiles_estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            // Datos académicos
            $table->string('tipo_alumno')->nullable();
            $table->string('nivel')->nullable();
            $table->string('sede')->nullable();
            $table->foreignId('facultad_id')->nullable()->constrained('facultades')->nullOnDelete();
            $table->foreignId('carrera_id')->nullable()->constrained('carreras')->nullOnDelete();
            $table->string('itinerario')->nullable();
            $table->string('pensum')->nullable();
            $table->unsignedSmallInteger('anio_ingreso')->nullable();
            $table->boolean('graduado_pregrado')->default(false);
            $table->date('fecha_graduacion')->nullable();

            // Trayectoria educativa previa
            $table->string('colegio')->nullable();
            $table->unsignedSmallInteger('anio_graduacion_colegio')->nullable();
            $table->string('provincia_colegio')->nullable();
            $table->string('universidad_procedencia')->nullable();
            $table->string('provincia_universidad')->nullable();

            // Residencia
            $table->boolean('residente')->default(false);
            $table->string('direccion')->nullable();
            $table->string('provincia_residencia')->nullable();
            $table->string('canton_residencia')->nullable();
            $table->string('telefono_casa', 15)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfiles_estudiantes');
    }
};
