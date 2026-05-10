<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hitos_investigacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigacion_id')->constrained('investigaciones')->cascadeOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->date('fecha_planificada')->nullable();
            $table->date('fecha_cumplida')->nullable();
            $table->unsignedTinyInteger('porcentaje_avance')->default(0)
                  ->comment('0 a 100');
            $table->timestamps();
 
            $table->index('investigacion_id');
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('hitos_investigacion');
    }
};