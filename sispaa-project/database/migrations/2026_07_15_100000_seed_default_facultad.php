<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hoy todos los estudiantes pertenecen a una sola facultad institucional
 * ("Ciencias de la Vida y Tecnologías"), así que se siembra como la
 * facultad por defecto que PerfilEstudianteController fuerza al guardar
 * el perfil (el estudiante no la elige). El código 'CVT' es la referencia
 * estable que usa el controlador (más segura que buscar por nombre).
 */
return new class extends Migration
{
    public function up(): void
    {
        $ahora = now();

        DB::table('facultades')->updateOrInsert(
            ['codigo' => 'CVT'],
            [
                'nombre' => 'Ciencias de la Vida y Tecnologías',
                'activa' => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]
        );
    }

    public function down(): void
    {
        DB::table('facultades')->where('codigo', 'CVT')->delete();
    }
};
