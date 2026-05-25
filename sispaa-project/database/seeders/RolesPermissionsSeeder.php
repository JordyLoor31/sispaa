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
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $docentePermissions = [
            'silabos.upload',
            'informes.upload',
            'faltas.create',
            'faltas.view',
            'justificaciones.review',
            'justificaciones.approve',
            'investigacion.create',
            'investigacion.update-own',
            'hitos.manage',
            'seguimiento.respond',
            'vinculacion.view-own',
            'laboratorio.register',
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

        $permissions = array_values(array_unique(array_merge($docentePermissions, $estudiantePermissions)));

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $docenteRole = Role::findOrCreate('docente', 'web');
        $docenteRole->syncPermissions($docentePermissions);

        $estudianteRole = Role::findOrCreate('estudiante', 'web');
        $estudianteRole->syncPermissions($estudiantePermissions);
    }
}