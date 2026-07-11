<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Renombra el rol 'administrador' a 'SystemAdministrador' en bases de datos
     * que ya fueron sembradas antes de este cambio. Los usuarios que ya tenían
     * el rol conservan su asignación (model_has_roles referencia el role_id,
     * no el nombre), por lo que basta con actualizar la fila en `roles`.
     */
    public function up(): void
    {
        DB::table('roles')
            ->where('name', 'administrador')
            ->update(['name' => 'SystemAdministrador']);
    }

    public function down(): void
    {
        DB::table('roles')
            ->where('name', 'SystemAdministrador')
            ->update(['name' => 'administrador']);
    }
};
