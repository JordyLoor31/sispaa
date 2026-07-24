<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rediseño de Faltas: Secretaría deja de gestionar justificaciones
 * individuales de faltas (una fila por estudiante+materia+fecha, con
 * solicitud de justificación del estudiante y aprobación/rechazo). En su
 * lugar, Secretaría registra semanalmente un número agregado de faltas por
 * carrera (sin detalle por estudiante), que se muestra en gráficos de
 * Reportes igual que el resto de indicadores "por carrera".
 *
 * Se eliminan las tablas `justificaciones_solicitudes` (depende de
 * `faltas` vía FK, se elimina primero) y `faltas`, y se crea
 * `faltas_semanales_carrera`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('justificaciones_solicitudes');
        Schema::dropIfExists('faltas');

        Schema::create('faltas_semanales_carrera', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_id')->constrained('carreras')->cascadeOnDelete();
            $table->foreignId('periodo_id')->constrained('periodos_academicos')->cascadeOnDelete();
            $table->unsignedTinyInteger('semana');
            $table->unsignedInteger('cantidad_faltas')->default(0);
            $table->text('observaciones')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['carrera_id', 'periodo_id', 'semana']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faltas_semanales_carrera');

        Schema::create('faltas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->foreignId('periodo_id')->constrained('periodos_academicos')->cascadeOnDelete();
            $table->date('fecha');
            $table->boolean('justificada')->default(false);
            $table->text('motivo')->nullable();
            $table->timestamps();

            $table->index(['estudiante_id', 'periodo_id']);
            $table->index(['materia_id', 'periodo_id']);
        });

        Schema::create('justificaciones_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('falta_id')->constrained('faltas')->cascadeOnDelete();
            $table->text('motivo_estudiante');
            $table->string('archivo_adjunto')->nullable();
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');
            $table->text('comentario_revisor')->nullable();
            $table->timestamps();
        });
    }
};
