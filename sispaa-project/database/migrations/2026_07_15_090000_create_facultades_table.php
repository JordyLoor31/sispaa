<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de apoyo requerida por perfiles_estudiantes.facultad_id: no existía
 * en el esquema (las carreras hoy no cuelgan de una facultad), así que se
 * crea aquí como catálogo mínimo. Sigue el mismo patrón que "carreras".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facultades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo', 10)->unique();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facultades');
    }
};
