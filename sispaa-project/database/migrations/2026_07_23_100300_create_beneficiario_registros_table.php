<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registros de conteo de beneficiarios de una actividad. Cada registro es una
 * "tanda" de conteo:
 *  - inicial: el conteo capturado al crear la actividad.
 *  - adicional: conteos que se suman después, mientras la actividad sigue
 *    "En ejecución" (avances). Nunca se sobrescribe un total manual: el total
 *    de la actividad es la suma de todos los registros.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficiario_registros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_vinculacion_id')
                  ->constrained('actividades_vinculacion')->cascadeOnDelete();
            $table->enum('tipo', ['inicial', 'adicional'])->default('adicional');
            $table->date('fecha')->nullable();
            $table->string('observacion')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['actividad_vinculacion_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficiario_registros');
    }
};
