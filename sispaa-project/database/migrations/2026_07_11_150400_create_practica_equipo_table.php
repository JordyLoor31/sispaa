<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practica_equipo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practica_id')->constrained('practicas_laboratorio')->cascadeOnDelete();
            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();
            $table->unsignedInteger('cantidad_usada')->default(1);
            $table->timestamps();

            $table->unique(['practica_id', 'equipo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practica_equipo');
    }
};
