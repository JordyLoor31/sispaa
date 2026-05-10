<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investigaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('coordinador_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('periodo_id')->constrained('periodos_academicos')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('objetivo')->nullable();
            $table->enum('estado', ['en_curso', 'pausada', 'finalizada'])->default('en_curso');
            $table->timestamps();
            $table->softDeletes();
 
            $table->index(['docente_id', 'periodo_id']);
            $table->index('estado');
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('investigaciones');
    }
};