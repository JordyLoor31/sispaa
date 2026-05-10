<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titulaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tutor_id')->constrained('users')->cascadeOnDelete();
            $table->string('tema');
            $table->enum('estado', ['en_proceso', 'defendido', 'graduado'])->default('en_proceso');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_graduacion')->nullable();
            $table->timestamps();
            $table->softDeletes();
 
            $table->index(['estado']);
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('titulaciones');
    }
};
 