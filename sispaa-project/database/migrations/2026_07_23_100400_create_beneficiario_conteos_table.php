<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Celdas de la matriz género x edad para cada registro de conteo. Formato
 * "largo"/tidy: una fila por combinación (genero, rango_edad) con su cantidad.
 * Esto conserva el desglose completo y facilita reportes por género, por rango
 * de edad y el cruce entre ambos (p. ej. mujeres jóvenes).
 *
 * Rangos de edad: ninos 0-11, jovenes 12-18, adultos 19-64, adultos_mayores 65+.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficiario_conteos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registro_id')
                  ->constrained('beneficiario_registros')->cascadeOnDelete();
            $table->enum('genero', ['hombre', 'mujer']);
            $table->enum('rango_edad', ['ninos', 'jovenes', 'adultos', 'adultos_mayores']);
            $table->unsignedInteger('cantidad')->default(0);
            $table->timestamps();

            $table->unique(['registro_id', 'genero', 'rango_edad']);
            $table->index(['genero', 'rango_edad']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficiario_conteos');
    }
};
