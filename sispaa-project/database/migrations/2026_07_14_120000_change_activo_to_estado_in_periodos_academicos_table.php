<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reemplaza el booleano 'activo' de periodos_academicos por un ciclo de
     * vida de 3 estados ('planificado', 'activo', 'finalizado'), más
     * representativo de cómo se usa un período en la práctica: se planifica
     * con anticipación, se activa cuando arranca, y queda finalizado cuando
     * el siguiente período lo reemplaza.
     */
    public function up(): void
    {
        Schema::table('periodos_academicos', function (Blueprint $table) {
            $table->string('estado', 20)->default('planificado')->after('tipo');
        });

        // Backfill: los que estaban activo=true pasan a 'activo'; el resto,
        // como no había forma de distinguir "planificado" de "finalizado" con
        // solo un booleano, se infiere por fecha_fin (si ya pasó, finalizado).
        DB::table('periodos_academicos')->where('activo', true)->update(['estado' => 'activo']);
        DB::table('periodos_academicos')
            ->where('activo', false)
            ->whereDate('fecha_fin', '<', now()->toDateString())
            ->update(['estado' => 'finalizado']);

        Schema::table('periodos_academicos', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }

    public function down(): void
    {
        Schema::table('periodos_academicos', function (Blueprint $table) {
            $table->boolean('activo')->default(false)->after('tipo');
        });

        DB::table('periodos_academicos')->where('estado', 'activo')->update(['activo' => true]);

        Schema::table('periodos_academicos', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
