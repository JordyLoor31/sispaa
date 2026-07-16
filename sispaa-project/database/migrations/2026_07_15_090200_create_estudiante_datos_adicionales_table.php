<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiante_datos_adicionales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            // Datos personales
            $table->string('religion')->nullable();
            $table->string('estado_civil')->nullable();
            $table->unsignedTinyInteger('cantidad_hijos')->default(0);
            $table->string('etnia')->nullable();
            $table->string('tipo_beca')->nullable();
            $table->string('nacionalidad')->nullable();
            $table->string('pais_nacimiento')->nullable();
            $table->string('provincia_nacimiento')->nullable();
            $table->string('canton_nacimiento')->nullable();

            // Datos laborales
            $table->string('empresa')->nullable();
            $table->string('direccion_empresa')->nullable();
            $table->string('telefono_empresa', 15)->nullable();
            $table->string('cargo')->nullable();
            $table->string('ciudad_laboral')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiante_datos_adicionales');
    }
};
