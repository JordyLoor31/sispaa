<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, FlaskConical } from 'lucide-vue-next';
import { BRAND_GRADIENT } from '@/lib/brand';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DataTable } from '@/components/ui/data-table';
import type { ProyectoItem, Catalogo } from './types';
import makeProyectoColumns from './columns';

const props = defineProps<{
    proyectos: ProyectoItem[];
    periodos: Catalogo[];
    filters: { estado?: string };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const filterEstado = ref(props.filters.estado || 'all');
const applyFilter = () => {
    router.get(route('investigacion.index'), { estado: filterEstado.value !== 'all' ? filterEstado.value : undefined }, { preserveState: true, replace: true });
};

const columns = makeProyectoColumns();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Investigación" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-sm" :style="BRAND_GRADIENT">
                        <FlaskConical class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Investigación</h1>
                        <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">Tus proyectos de investigación y los que supervisas como coordinador.</p>
                    </div>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 rounded-lg font-semibold text-white shadow-md shadow-[color:color-mix(in_srgb,var(--sispaa-primary)_30%,transparent)] transition-all bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)] hover:shadow-lg">
                    <Link :href="route('investigacion.create')">
                        <Plus class="h-4 w-4" />
                        Nuevo Proyecto
                    </Link>
                </Button>
            </div>

            <div class="w-full space-y-4">
                <div class="flex gap-3">
                    <Select v-model="filterEstado" @update:model-value="applyFilter">
                        <SelectTrigger class="w-full sm:w-[180px]"><SelectValue placeholder="Estado" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos los estados</SelectItem>
                            <SelectItem value="en_curso">En curso</SelectItem>
                            <SelectItem value="pausada">Pausada</SelectItem>
                            <SelectItem value="finalizada">Finalizada</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <DataTable :columns="columns" :data="proyectos">
                    <template #empty>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)]">
                            <FlaskConical class="h-5 w-5 opacity-40 text-[var(--sispaa-text)]" />
                        </div>
                        <p class="text-sm font-medium opacity-70 text-[var(--sispaa-text)]">No hay proyectos de investigación todavía.</p>
                    </template>
                </DataTable>
            </div>
        </div>
    </AppSidebarLayout>
</template>
