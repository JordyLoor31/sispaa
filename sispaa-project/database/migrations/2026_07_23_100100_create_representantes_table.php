<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Representante de los beneficiarios. Entidad distinta del líder/supervisor
 * interno del equipo. Es reutilizable: un mismo representante puede usarse en
 * varias actividades (la actividad apunta con representante_id). El vínculo con
 * un beneficiario es opcional.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('representantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiario_id')->nullable()
                  ->constrained('beneficiarios')->nullOnDelete();
            $table->string('nombre');
            $table->string('cedula', 10)->nullable();
            $table->string('cargo')->nullable();       // p. ej. "Presidente del barrio"
            $table->string('telefono', 10)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('representantes');
    }
};
