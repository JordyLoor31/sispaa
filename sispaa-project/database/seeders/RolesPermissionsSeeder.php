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

        $permissions = [
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

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $docenteRole = Role::findOrCreate('docente', 'web');
        $docenteRole->syncPermissions($permissions);
    }
}