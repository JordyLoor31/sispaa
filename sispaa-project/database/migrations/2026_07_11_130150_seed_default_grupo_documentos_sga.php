<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migra los 4 tipos de documento que antes vivían hardcodeados en
     * StudentPortalController a un grupo real en el catálogo dinámico, para
     * que el sistema arranque con el mismo comportamiento que tenía antes
     * de introducir Grupos de Documentos/Requisitos.
     */
    public function up(): void
    {
        $ahora = now();

        $grupoId = DB::table('grupos_documentos')->insertGetId([
            'nombre' => 'Expediente SGA',
            'descripcion' => 'Documentos habilitantes de matrícula requeridos a todo estudiante.',
            'creado_por' => null,
            'activo' => true,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);

        $requisitos = [
            'Cédula y Papeleta de Votación',
            'Título de Bachiller',
            'Solicitud de Matrícula',
            'Ficha Socioeconómica',
        ];

        foreach ($requisitos as $orden => $nombre) {
            DB::table('requisitos_grupo')->insert([
                'grupo_id' => $grupoId,
                'nombre' => $nombre,
                'orden' => $orden,
                'activo' => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        }
    }

    public function down(): void
    {
        $grupo = DB::table('grupos_documentos')->where('nombre', 'Expediente SGA')->first();

        if ($grupo) {
            DB::table('requisitos_grupo')->where('grupo_id', $grupo->id)->delete();
            DB::table('grupos_documentos')->where('id', $grupo->id)->delete();
        }
    }
};
