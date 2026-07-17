<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega 'observaciones' a informes_docente, igual que ya tiene 'silabos',
 * para poder soportar el mismo flujo de revisión (aprobar/rechazar con
 * motivo) desde Secretaría.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('informes_docente', function (Blueprint $table) {
            $table->text('observaciones')->nullable()->after('archivo_url');
        });
    }

    public function down(): void
    {
        Schema::table('informes_docente', function (Blueprint $table) {
            $table->dropColumn('observaciones');
        });
    }
};
