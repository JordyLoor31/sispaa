<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_id')->constrained('carreras')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('codigo', 20)->unique();
            $table->unsignedTinyInteger('creditos');
            $table->unsignedTinyInteger('nivel')->comment('1 al 10');
            $table->boolean('activa')->default(true);
            $table->timestamps();
 
            $table->index('carrera_id');
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};
 
