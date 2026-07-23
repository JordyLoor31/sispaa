<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historial de informes trimestrales que el líder de proyecto sube como
 * evidencia de avance. Sigue el mismo patrón de archivo que Silabo:
 * archivo_url guarda "/storage/..." y se sirve por una ruta autenticada
 * (Storage::disk('public')->response(...)) en vez de depender del symlink
 * public/storage (ver SilaboController::ver()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informes_trimestrales_investigacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigacion_id')->constrained('investigaciones')->cascadeOnDelete();
            $table->string('archivo_url');
            $table->string('nombre_original')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_subida')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('investigacion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informes_trimestrales_investigacion');
    }
};
