<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fechas límite / convocatorias configurables por módulo
     * (equivalente a `convocatorias` del sistema Next.js origen).
     */
    public function up(): void
    {
        Schema::create('convocatorias', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('modulo'); // ej: Académico, Titulación, Vinculación, Investigación
            $table->string('tipo_documento')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->index(['modulo', 'activa']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convocatorias');
    }
};
