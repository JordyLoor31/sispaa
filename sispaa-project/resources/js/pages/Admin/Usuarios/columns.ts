import type { ColumnDef } from '@tanstack/vue-table';
import { Shield } from 'lucide-vue-next';
import { h } from 'vue';
import { Switch } from '@/components/ui/switch';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';
import { BRAND_GRADIENT, SWITCH_ACTIVE_CLASS, getInitials, tintedBadgeStyle } from '@/lib/brand';

// Re-export para los consumidores del módulo (Show.vue, Edit.vue).
export { BRAND_GRADIENT, getInitials } from '@/lib/brand';

export interface Role {
    id: number;
    name: string;
}

export interface Carrera {
    id: number;
    nombre: string;
}

export interface Usuario {
    id: number;
    name: string;
    email: string;
    cedula: string | null;
    telefono: string | null;
    is_active: boolean;
    carrera_id: number | null;
    carrera?: Carrera | null;
    roles: Role[];
    created_at?: string;
}

interface UserColumnsOptions {
    onToggleStatus: (user: Usuario) => void;
}

/** Color identitario de cada rol (ver tintedBadgeStyle en @/lib/brand). */
const ROLE_BADGE_COLORS: Record<string, string> = {
    SystemAdministrador: 'var(--sispaa-accent)',
    secretaria: 'var(--sispaa-primary)',
    coordinador: 'var(--sispaa-accent)',
    docente: '#E4BC57',
};

export const getRoleBadgeStyle = (rolName: string): string =>
    tintedBadgeStyle(ROLE_BADGE_COLORS[rolName] ?? 'var(--sispaa-secondary)');

export function makeUserColumns({ onToggleStatus }: UserColumnsOptions): ColumnDef<Usuario>[] {
    return [
        {
            accessorKey: 'name',
            meta: { label: 'Usuario' },
            header: 'Usuario',
            cell: ({ row }) => {
                const user = row.original;
                return h('div', { class: 'flex items-center gap-2.5' }, [
                    h(
                        'div',
                        {
                            class: 'flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[11px] font-bold text-white shadow-sm',
                            style: BRAND_GRADIENT,
                        },
                        getInitials(user.name),
                    ),
                    h('div', { class: 'min-w-0' }, [
                        h('div', { class: 'truncate font-semibold text-[var(--sispaa-text)]' }, user.name),
                        h('div', { class: 'truncate text-xs opacity-75 text-[var(--sispaa-text)]' }, user.email),
                    ]),
                ]);
            },
        },
        {
            accessorKey: 'cedula',
            meta: { label: 'Identificación' },
            header: 'Identificación',
            cell: ({ row }) =>
                row.original.cedula
                    ? h('span', { class: 'text-[var(--sispaa-text)]' }, row.original.cedula)
                    : h('span', { class: 'opacity-40 text-[var(--sispaa-text)]' }, '—'),
        },
        {
            accessorKey: 'telefono',
            meta: { label: 'Teléfono' },
            header: 'Teléfono',
            cell: ({ row }) =>
                row.original.telefono
                    ? h('span', { class: 'opacity-70 text-[var(--sispaa-text)]' }, row.original.telefono)
                    : h('span', { class: 'opacity-40 text-[var(--sispaa-text)]' }, '—'),
        },
        {
            id: 'carrera',
            meta: { label: 'Carrera' },
            header: 'Carrera',
            cell: ({ row }) =>
                row.original.carrera?.nombre
                    ? h(
                          'span',
                          {
                              // Los nombres de carrera vienen en MAYÚSCULAS largas:
                              // se atenúan y compactan para que no dominen la fila.
                              class: 'block max-w-[22ch] truncate text-xs font-medium opacity-75 text-[var(--sispaa-text)]',
                              title: row.original.carrera.nombre,
                          },
                          row.original.carrera.nombre,
                      )
                    : h('span', { class: 'text-xs opacity-50 text-[var(--sispaa-text)]' }, 'General / Institucional'),
        },
        {
            id: 'rol',
            meta: { label: 'Roles' },
            header: 'Roles',
            cell: ({ row }) => {
                const user = row.original;

                if (user.roles.length === 0) {
                    return h('span', { class: 'text-xs opacity-60 text-[var(--sispaa-text)]' }, 'Ninguno');
                }

                return h(
                    'div',
                    { class: 'flex flex-wrap gap-1' },
                    user.roles.map((rol) => {
                        const formattedRol = rol.name.charAt(0).toUpperCase() + rol.name.slice(1);
                        return h(
                            'span',
                            {
                                key: rol.id,
                                class: 'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold',
                                style: getRoleBadgeStyle(rol.name),
                            },
                            [h(Shield, { class: 'h-3 w-3' }), formattedRol],
                        );
                    }),
                );
            },
        },
        {
            accessorKey: 'is_active',
            meta: { label: 'Estado' },
            header: 'Estado',
            cell: ({ row }) => {
                const user = row.original;
                const isActive = !!user.is_active;

                return h('div', { class: 'flex items-center gap-2' }, [
                    h(Switch, {
                        checked: isActive,
                        modelValue: isActive,
                        'onUpdate:checked': () => onToggleStatus(user),
                        'onUpdate:modelValue': () => onToggleStatus(user),
                        class: SWITCH_ACTIVE_CLASS,
                    }),
                    h(
                        'span',
                        {
                            class: `text-xs font-semibold ${
                                isActive
                                    ? 'text-[color:color-mix(in_srgb,var(--sispaa-secondary)_55%,var(--sispaa-text))]'
                                    : 'opacity-50 text-[var(--sispaa-text)]'
                            }`,
                        },
                        isActive ? 'Activo' : 'Inactivo',
                    ),
                ]);
            },
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right font-semibold' }, 'Acciones'),
            enableHiding: false,
            cell: ({ row }) => {
                const user = row.original;

                return h(ResourceActionsDropdown, {
                    resourceName: 'el usuario',
                    showRoute: 'admin.usuarios.show',
                    editRoute: 'admin.usuarios.edit',
                    routeParams: user.id,
                    itemLabel: `el usuario "${user.name}"`,
                    canDelete: false, // no se elimina, se desactiva (ver columna Estado)
                });
            },
        },
    ];
}

export default makeUserColumns;
