<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { h, ref, reactive } from 'vue';
import { Search, Plus, MoreHorizontal, ClipboardList } from 'lucide-vue-next';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { toast } from 'vue-sonner';
import { useDebounceFn } from '@vueuse/core';
import type { Periodo, Carrera, MatriculaItem } from './types';

interface PaginatedMatriculas {
    data: MatriculaItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: any[];
}
interface Stats { activos: number; retirados: number; egresados: number; total: number }

const props = defineProps<{
    matriculas: PaginatedMatriculas;
    periodos: Periodo[];
    carreras: Carrera[];
    filters: { q?: string; estado?: string; periodo_id?: string; carrera_id?: string; per_page?: string };
    stats: Stats;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const search = ref(props.filters.q || '');
const filterEstado = ref(props.filters.estado || 'all');
const filterPeriodo = ref(props.filters.periodo_id || 'all');
const filterCarrera = ref(props.filters.carrera_id || 'all');

const applyFilters = () => {
    router.get(route('secretaria.matriculas.index'), {
        q: search.value || undefined,
        estado: filterEstado.value !== 'all' ? filterEstado.value : undefined,
        periodo_id: filterPeriodo.value !== 'all' ? filterPeriodo.value : undefined,
        carrera_id: filterCarrera.value !== 'all' ? filterCarrera.value : undefined,
        per_page: props.matriculas.per_page,
    }, { preserveState: true, replace: true });
};
const debouncedSearch = useDebounceFn(applyFilters, 300);

// ── Cambio de Estado (acción inline, no amerita página propia) ──────────
const changeEstadoItem = ref<MatriculaItem | null>(null);
const estadoForm = useForm({ estado: '' as 'activo' | 'retirado' | 'egresado' });

const openChangeEstado = (item: MatriculaItem) => {
    changeEstadoItem.value = item;
    estadoForm.estado = item.estado;
};

const submitChangeEstado = () => {
    if (!changeEstadoItem.value) return;
    estadoForm.patch(route('secretaria.matriculas.update-estado', changeEstadoItem.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Estado de matrícula actualizado.');
            changeEstadoItem.value = null;
        },
        onError: () => toast.error('Error al actualizar el estado.'),
    });
};

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        activo: 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]',
        retirado: 'bg-rose-50 text-rose-800',
        egresado: 'bg-[color:color-mix(in_srgb,var(--sispaa-accent)_25%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-accent)_75%,black)]',
    };
    return map[estado] ?? 'bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-text)_60%,transparent)]';
};

const columns: ColumnDef<MatriculaItem>[] = [
    { id: 'estudiante', header: 'Estudiante', cell: ({ row }) => row.original.estudiante },
    { id: 'carrera', header: 'Carrera', cell: ({ row }) => row.original.carrera },
    { id: 'periodo', header: 'Período', cell: ({ row }) => row.original.periodo },
    { accessorKey: 'estado', header: 'Estado', cell: ({ row }) => row.original.estado },
    { accessorKey: 'fecha_matricula', header: 'Fecha Matrícula', cell: ({ row }) => row.original.fecha_matricula },
    { id: 'actions', header: () => h('div', { class: 'text-right' }, 'Acciones'), cell: ({ row }) => row.original },
];

const table = useVueTable(reactive({
    get data() { return props.matriculas.data; },
    columns,
    getCoreRowModel: getCoreRowModel(),
}));

const navigateToPage = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Registro de Matrículas" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                        Registro de Matrículas
                    </h1>
                    <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                        Inscribe estudiantes en materias y períodos académicos.
                    </p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                    <Link :href="route('secretaria.matriculas.create')">
                        <Plus class="h-4 w-4" />
                        Nueva Matrícula
                    </Link>
                </Button>
            </div>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Total</p>
                    <p class="mt-1 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ stats.total }}</p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <p class="text-xs font-semibold uppercase text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]">Activos</p>
                    <p class="mt-1 text-3xl font-extrabold text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]">{{ stats.activos }}</p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <p class="text-xs font-semibold uppercase text-rose-700">Retirados</p>
                    <p class="mt-1 text-3xl font-extrabold text-rose-700">{{ stats.retirados }}</p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <p class="text-xs font-semibold uppercase text-[color:color-mix(in_srgb,var(--sispaa-accent)_75%,black)]">Egresados</p>
                    <p class="mt-1 text-3xl font-extrabold text-[color:color-mix(in_srgb,var(--sispaa-accent)_75%,black)]">{{ stats.egresados }}</p>
                </div>
            </div>

            <div class="w-full space-y-4">
                <div class="flex flex-col flex-wrap gap-3 sm:flex-row">
                    <div class="relative w-full max-w-sm">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 opacity-40 text-[var(--sispaa-text)]" />
                        <Input
                            v-model="search"
                            @input="debouncedSearch"
                            type="text"
                            placeholder="Buscar estudiante, cédula..."
                            class="pl-9"
                        />
                    </div>
                    <Select v-model="filterEstado" @update:model-value="applyFilters">
                        <SelectTrigger class="w-full sm:w-[140px]"><SelectValue placeholder="Estado" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos</SelectItem>
                            <SelectItem value="activo">Activo</SelectItem>
                            <SelectItem value="retirado">Retirado</SelectItem>
                            <SelectItem value="egresado">Egresado</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="filterPeriodo" @update:model-value="applyFilters">
                        <SelectTrigger class="w-full sm:w-[180px]"><SelectValue placeholder="Período" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos los períodos</SelectItem>
                            <SelectItem v-for="p in periodos" :key="p.id" :value="String(p.id)">{{ p.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="filterCarrera" @update:model-value="applyFilters">
                        <SelectTrigger class="w-full sm:w-[180px]"><SelectValue placeholder="Carrera" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todas las carreras</SelectItem>
                            <SelectItem v-for="c in carreras" :key="c.id" :value="String(c.id)">{{ c.codigo }} — {{ c.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="overflow-hidden rounded-lg bg-[var(--sispaa-surface)]">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow v-for="hg in table.getHeaderGroups()" :key="hg.id"
                                    class="border-b border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                                    <TableHead v-for="header in hg.headers" :key="header.id"
                                        class="h-12 px-4 text-sm font-medium opacity-60 text-[var(--sispaa-text)]">
                                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-sm">
                                <template v-if="table.getRowModel().rows.length">
                                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id"
                                        class="transition-colors hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_5%,transparent)]">
                                        <TableCell class="px-4 py-4">
                                            <div class="font-semibold text-[var(--sispaa-text)]">{{ row.original.estudiante.name }}</div>
                                            <div class="text-xs opacity-50 text-[var(--sispaa-text)]">{{ row.original.estudiante.cedula ?? row.original.estudiante.email }}</div>
                                        </TableCell>
                                        <TableCell class="px-4 py-4 text-xs opacity-70 text-[var(--sispaa-text)]">
                                            {{ row.original.carrera.codigo }} — {{ row.original.carrera.nombre }}
                                        </TableCell>
                                        <TableCell class="px-4 py-4 text-xs opacity-70 text-[var(--sispaa-text)]">
                                            {{ row.original.periodo.nombre }}
                                        </TableCell>
                                        <TableCell class="px-4 py-4">
                                            <span :class="['inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold', estadoBadge(row.original.estado)]">
                                                {{ row.original.estado.charAt(0).toUpperCase() + row.original.estado.slice(1) }}
                                            </span>
                                        </TableCell>
                                        <TableCell class="px-4 py-4 text-xs opacity-60 text-[var(--sispaa-text)]">{{ row.original.fecha_matricula }}</TableCell>
                                        <TableCell class="px-4 py-4 text-right">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                                                        <span class="sr-only">Abrir menú</span>
                                                        <MoreHorizontal class="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem @click="openChangeEstado(row.original)">
                                                        Cambiar estado
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <TableRow v-else>
                                    <TableCell :colspan="6" class="h-32 text-center">
                                        <div class="flex flex-col items-center gap-2 opacity-40 text-[var(--sispaa-text)]">
                                            <ClipboardList class="h-8 w-8" />
                                            <span class="text-sm font-medium">No hay matrículas registradas</span>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div class="flex flex-col gap-2 border-t px-4 py-4 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <span class="text-xs opacity-60 text-[var(--sispaa-text)]">Mostrando {{ matriculas.data.length }} de {{ matriculas.total }} matrículas</span>
                        <div class="flex flex-wrap items-center gap-1">
                            <button
                                v-for="link in matriculas.links"
                                :key="link.label"
                                @click="navigateToPage(link.url)"
                                :disabled="!link.url || link.active"
                                v-html="link.label"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
                                :class="[link.active ? 'bg-[var(--sispaa-primary)] text-white' : 'bg-[var(--sispaa-background)] text-[var(--sispaa-text)] opacity-70 hover:opacity-100 disabled:opacity-40']"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dialog: Cambiar Estado -->
        <Dialog :open="!!changeEstadoItem" @update:open="val => { if (!val) changeEstadoItem = null; }">
            <DialogContent class="sm:max-w-sm">
                <DialogHeader>
                    <DialogTitle>Cambiar estado de matrícula</DialogTitle>
                    <DialogDescription v-if="changeEstadoItem">
                        {{ changeEstadoItem.estudiante.name }} — {{ changeEstadoItem.periodo.nombre }}
                    </DialogDescription>
                </DialogHeader>
                <div class="py-4">
                    <label class="block text-sm font-semibold text-[var(--sispaa-text)] mb-1.5">Nuevo estado</label>
                    <Select v-model="estadoForm.estado">
                        <SelectTrigger class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="activo">Activo</SelectItem>
                            <SelectItem value="retirado">Retirado</SelectItem>
                            <SelectItem value="egresado">Egresado</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <DialogFooter class="gap-2">
                    <Button variant="outline" @click="changeEstadoItem = null">Cancelar</Button>
                    <Button @click="submitChangeEstado" :disabled="estadoForm.processing" class="text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                        {{ estadoForm.processing ? 'Guardando...' : 'Guardar cambio' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppSidebarLayout>
</template>
