import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Building2 } from 'lucide-vue-next';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';
import { type Beneficiario, type TipoBeneficiario, TIPO_BENEFICIARIO_LABELS } from './types';

const tipoBadge = (tipo: TipoBeneficiario) => {
    const map: Record<TipoBeneficiario, string> = {
        persona_juridica: 'bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)] text-[var(--sispaa-primary)]',
        comunidad_organizacion: 'bg-[color:color-mix(in_srgb,var(--sispaa-accent)_20%,transparent)] text-[var(--sispaa-accent)]',
        persona_natural: 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_55%,var(--sispaa-text))]',
    };
    return map[tipo] ?? map.persona_juridica;
};

export function makeBeneficiarioColumns(): ColumnDef<Beneficiario>[] {
    return [
        {
            id: 'tipo',
            meta: { label: 'Tipo' },
            header: 'Tipo',
            cell: ({ row }) => {
                const b = row.original;
                return h(
                    'span',
                    { class: ['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', tipoBadge(b.tipo)] },
                    TIPO_BENEFICIARIO_LABELS[b.tipo],
                );
            },
        },
        {
            accessorKey: 'nombre',
            meta: { label: 'Nombre' },
            header: 'Nombre',
            cell: ({ row }) => {
                const b = row.original;
                return h('div', { class: 'flex min-w-0 items-center gap-2.5' }, [
                    h('div', { class: 'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]' }, [
                        h(Building2, { class: 'h-4 w-4' }),
                    ]),
                    h('span', { class: 'truncate text-sm font-semibold text-[var(--sispaa-text)]' }, b.nombre),
                ]);
            },
        },
        {
            id: 'documento',
            meta: { label: 'Documento' },
            header: 'Documento',
            cell: ({ row }) => {
                const b = row.original;
                return h('span', { class: 'text-xs opacity-60 text-[var(--sispaa-text)]' }, b.ruc ?? b.cedula ?? '—');
            },
        },
        {
            accessorKey: 'sector',
            meta: { label: 'Sector' },
            header: 'Sector',
            cell: ({ row }) => h('span', { class: 'text-sm opacity-70 text-[var(--sispaa-text)]' }, row.original.sector ?? '—'),
        },
        {
            accessorKey: 'contacto',
            meta: { label: 'Contacto' },
            header: 'Contacto',
            cell: ({ row }) => h('span', { class: 'text-sm opacity-70 text-[var(--sispaa-text)]' }, row.original.contacto ?? '—'),
        },
        {
            id: 'actividades_count',
            meta: { label: 'Actividades' },
            header: 'Actividades',
            cell: ({ row }) => h('span', { class: 'text-sm font-semibold text-[var(--sispaa-text)]' }, String(row.original.actividades_count ?? 0)),
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right font-semibold' }, 'Acciones'),
            enableHiding: false,
            cell: ({ row }) => {
                const b = row.original;
                return h(ResourceActionsDropdown, {
                    resourceName: 'el beneficiario',
                    showRoute: 'vinculacion.beneficiarios.show',
                    editRoute: 'vinculacion.beneficiarios.edit',
                    deleteRoute: 'vinculacion.beneficiarios.destroy',
                    routeParams: b.id,
                    itemLabel: `el beneficiario "${b.nombre}"`,
                });
            },
        },
    ];
}

export default makeBeneficiarioColumns;
