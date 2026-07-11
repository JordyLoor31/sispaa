<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Limpieza defensiva: en el código fuente solo existe un rol de nivel
     * administrador ('SystemAdministrador', antes 'administrador'). Esta
     * migración garantiza que la base de datos quede igual, por si en algún
     * ambiente se llegó a crear manualmente un rol suelto 'admin'/'Admin'
     * (por ejemplo, vía tinker o pruebas manuales). Reasigna a cualquier
     * usuario que lo tenga hacia 'SystemAdministrador' y luego lo elimina.
     * Si ese rol no existe, esta migración no hace nada.
     */
    public function up(): void
    {
        $strayNames = ['admin', 'Admin', 'ADMIN'];

        $systemAdminRoleId = DB::table('roles')
            ->where('name', 'SystemAdministrador')
            ->where('guard_name', 'web')
            ->value('id');

        if (!$systemAdminRoleId) {
            return;
        }

        $strayRoles = DB::table('roles')
            ->whereIn('name', $strayNames)
            ->get(['id', 'name']);

        foreach ($strayRoles as $strayRole) {
            // Reasignar usuarios que tenían el rol suelto
            $userIds = DB::table('model_has_roles')
                ->where('role_id', $strayRole->id)
                ->where('model_type', \App\Models\User::class)
                ->pluck('model_id');

            foreach ($userIds as $userId) {
                $yaLoTiene = DB::table('model_has_roles')
                    ->where('role_id', $systemAdminRoleId)
                    ->where('model_id', $userId)
                    ->where('model_type', \App\Models\User::class)
                    ->exists();

                if (!$yaLoTiene) {
                    DB::table('model_has_roles')->insert([
                        'role_id' => $systemAdminRoleId,
                        'model_type' => \App\Models\User::class,
                        'model_id' => $userId,
                    ]);
                }
            }

            // Eliminar el rol suelto y sus relaciones
            DB::table('model_has_roles')->where('role_id', $strayRole->id)->delete();
            DB::table('role_has_permissions')->where('role_id', $strayRole->id)->delete();
            DB::table('roles')->where('id', $strayRole->id)->delete();
        }
    }

    public function down(): void
    {
        // No reversible: no se puede reconstruir un rol que se eliminó
        // sin saber sus permisos originales. No-op intencional.
    }
};
