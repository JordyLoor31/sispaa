<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrige perfiles_estudiantes.facultad_id = null en filas ya guardadas.
 * Ocurría porque el controlador buscaba la facultad por defecto con un
 * código fijo ('CVT') que no coincidía con el código real en la tabla
 * facultades (insertado manualmente con otro código, ej. 'FCVT'). Ya se
 * corrigió el controlador para tomar la única facultad activa sin
 * depender de un código exacto; esta migración solo repara los datos que
 * quedaron con facultad_id null mientras existía el bug.
 */
return new class extends Migration
{
    public function up(): void
    {
        $facultadId = DB::table('facultades')->where('activa', true)->orderBy('id')->value('id');

        if ($facultadId) {
            DB::table('perfiles_estudiantes')
                ->whereNull('facultad_id')
                ->update(['facultad_id' => $facultadId]);
        }
    }

    public function down(): void
    {
        // No reversible de forma segura: no se sabe cuáles filas eran
        // originalmente null antes de este backfill.
    }
};
