<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_estudiante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('secretaria_id')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->string('tipo_documento');
            $table->string('archivo_url');
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->text('observacion')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
 
            $table->index(['estudiante_id', 'estado']);
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('documentos_estudiante');
    }
};
