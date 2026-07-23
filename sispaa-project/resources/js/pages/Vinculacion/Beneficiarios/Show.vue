<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Users } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { type Beneficiario, TIPO_BENEFICIARIO_LABELS } from './types';

const props = defineProps<{
    beneficiario: Beneficiario;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const formatDate = (date?: string) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.beneficiario.nombre" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                        <Users class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">{{ beneficiario.nombre }}</h1>
                        <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">{{ TIPO_BENEFICIARIO_LABELS[beneficiario.tipo] }}<span v-if="beneficiario.sector"> · {{ beneficiario.sector }}</span></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button as-child class="text-white bg-[var(--sispaa-accent)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-accent)_85%,black)]">
                        <Link :href="route('vinculacion.beneficiarios')">
                            <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                        </Link>
                    </Button>
                    <Button as-child class="text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                        <Link :href="route('vinculacion.beneficiarios.edit', beneficiario.id)">
                            <Pencil class="h-4 w-4 mr-1.5" /> Editar
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="mx-auto grid w-full max-w-2xl gap-4 sm:gap-6 md:grid-cols-2">
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">RUC</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">{{ beneficiario.ruc ?? '—' }}</p>
                </div>
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Contacto</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">{{ beneficiario.contacto ?? '—' }}</p>
                </div>
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Actividades vinculadas</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-primary)]">{{ beneficiario.actividades_count ?? 0 }}</p>
                </div>
            </div>

            <div class="mx-auto w-full max-w-2xl rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                <h4 class="mb-3 text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Auditoría</h4>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Creado por</p>
                        <p class="text-sm font-semibold text-[var(--sispaa-text)]">{{ beneficiario.creator?.name ?? '—' }}</p>
                        <p class="mt-0.5 text-xs opacity-50 text-[var(--sispaa-text)]">{{ formatDate(beneficiario.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Última actualización por</p>
                        <p class="text-sm font-semibold text-[var(--sispaa-text)]">{{ beneficiario.updater?.name ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
