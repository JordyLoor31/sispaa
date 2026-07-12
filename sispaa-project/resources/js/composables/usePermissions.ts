import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { SharedData } from '@/types';

/**
 * Composable central de permisos/roles para el frontend, para dejar de
 * repetir "roles.includes('coordinador')" o similar en cada .vue.
 *
 * A diferencia de DPPDI (que usa permisos finos por acción: resource.view/
 * create/edit/delete), en sispaa los permisos de Spatie ya sembrados son
 * gruesos por módulo (ej. 'titulacion.manage', 'silabos.review', ver
 * database/seeders/RolesPermissionsSeeder.php). Este composable se adapta a
 * ese modelo: can() valida contra esos mismos strings, sin inventar
 * permisos nuevos ni cambiar el gating real de las rutas (que sigue siendo
 * por rol vía middleware 'role:').
 *
 * SystemAdministrador siempre pasa (bypass), igual que en el backend vía
 * Gate::before (ver AppServiceProvider).
 */
export function usePermissions() {
    const page = usePage<SharedData>();

    const user = computed(() => page.props.auth?.user ?? null);
    const roles = computed(() => user.value?.roles ?? []);
    const permissions = computed(() => user.value?.permissions ?? []);

    const hasRole = (role: string): boolean => roles.value.includes(role);

    const hasAnyRole = (...roleList: string[]): boolean =>
        roleList.some((role) => roles.value.includes(role));

    const hasAllRoles = (...roleList: string[]): boolean =>
        roleList.every((role) => roles.value.includes(role));

    const isSystemAdmin = (): boolean => hasRole('SystemAdministrador');

    /** ¿Tiene el permiso indicado? (ej. can('titulacion.manage')) */
    const can = (permission: string): boolean => {
        if (isSystemAdmin()) return true;
        return permissions.value.includes(permission);
    };

    /** ¿Tiene alguno de los permisos indicados? */
    const canAny = (...permissionList: string[]): boolean => {
        if (isSystemAdmin()) return true;
        return permissionList.some((permission) => permissions.value.includes(permission));
    };

    /** ¿Tiene todos los permisos indicados? */
    const canAll = (...permissionList: string[]): boolean => {
        if (isSystemAdmin()) return true;
        return permissionList.every((permission) => permissions.value.includes(permission));
    };

    /** ¿Tiene el rol O el permiso indicado? (útil cuando el gating real es por rol) */
    const hasRoleOrPermission = (role: string, permission: string): boolean =>
        hasRole(role) || can(permission);

    return {
        user,
        roles,
        permissions,
        hasRole,
        hasAnyRole,
        hasAllRoles,
        isSystemAdmin,
        can,
        canAny,
        canAll,
        hasRoleOrPermission,
    };
}
