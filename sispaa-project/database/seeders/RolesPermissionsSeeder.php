<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 1. Definición de permisos por rol
        $adminPermissions = [
            'usuarios.manage',
            'malla.manage',
            'kpis.view-global',
            'fechas.manage',
        ];

        $secretariaPermissions = [
            'expediente.review',
            'justificaciones.review',
            'matriculas.manage',
        ];

        $docentePermissions = [
            'silabos.upload',
            'informes.upload',
            'laboratorio.manage',
            'inventario.view',
            'investigacion.manage',
        ];

        $coordinadorPermissions = [
            'titulacion.manage',
            'curricular.approve',
            'vinculacion.manage',
        ];

        $estudiantePermissions = [
            'indicadores.view-own',
            'matriculas.view-own',
            'faltas.view-own',
            'justificaciones.create',
            'documentos.upload',
            'documentos.view-own',
            'notificaciones.view-own',
        ];

        // 2. Unificar y crear todos los permisos en la base de datos
        $allPermissions = array_values(array_unique(array_merge(
            $adminPermissions,
            $secretariaPermissions,
            $docentePermissions,
            $coordinadorPermissions,
            $estudiantePermissions
        )));

        foreach ($allPermissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        // 3. Crear roles y asignar sus respectivos permisos
        $adminRole = Role::findOrCreate('administrador', 'web');
        $adminRole->syncPermissions($adminPermissions);

        $secretariaRole = Role::findOrCreate('secretaria', 'web');
        $secretariaRole->syncPermissions($secretariaPermissions);

        $docenteRole = Role::findOrCreate('docente', 'web');
        $docenteRole->syncPermissions($docentePermissions);

        $coordinadorRole = Role::findOrCreate('coordinador', 'web');
        $coordinadorRole->syncPermissions($coordinadorPermissions);

        $estudianteRole = Role::findOrCreate('estudiante', 'web');
        $estudianteRole->syncPermissions($estudiantePermissions);
    }
}