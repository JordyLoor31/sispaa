<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('periodos_academicos', function (Blueprint $table) {
            $table->date('fecha_limite_silabo')->nullable()->after('activo');
            $table->date('fecha_limite_informe')->nullable()->after('fecha_limite_silabo');
        });
    }

    public function down(): void
    {
        Schema::table('periodos_academicos', function (Blueprint $table) {
            $table->dropColumn(['fecha_limite_silabo', 'fecha_limite_informe']);
        });
    }
};
