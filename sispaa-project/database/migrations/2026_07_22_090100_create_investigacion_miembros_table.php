<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Miembros del equipo de un proyecto de investigación (aparte de líder y
 * colíder): relación muchos-a-muchos investigacion <-> users. Es la primera
 * relación belongsToMany hacia User en el proyecto (no hay un pivote
 * análogo que copiar 1:1; el patrón de UI multi-select sí se adapta del
 * checkbox-array de Laboratorio/Practicas/PracticaForm.vue).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investigacion_miembros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigacion_id')->constrained('investigaciones')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['investigacion_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investigacion_miembros');
    }
};
