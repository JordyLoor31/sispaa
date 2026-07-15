<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Etiqueta de color por carrera (hexadecimal), usada para distinguir
     * visualmente cada carrera en toda la UI y, sobre todo, en los gráficos
     * estadísticos de Reportes (barras "por carrera" coloreadas de forma
     * consistente con la etiqueta asignada).
     */
    private const PALETA = [
        '#6366f1', '#f59e0b', '#10b981', '#ef4444', '#0ea5e9',
        '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#84cc16',
    ];

    public function up(): void
    {
        Schema::table('carreras', function (Blueprint $table) {
            $table->string('color', 7)->nullable()->after('codigo');
        });

        // Backfill: a las carreras existentes se les asigna un color cíclico
        // de la paleta según su orden, para que ya tengan variedad visual sin
        // necesidad de que un administrador las edite una por una.
        $carreras = DB::table('carreras')->orderBy('id')->pluck('id');
        foreach ($carreras as $index => $id) {
            DB::table('carreras')->where('id', $id)->update([
                'color' => self::PALETA[$index % count(self::PALETA)],
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('carreras', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
