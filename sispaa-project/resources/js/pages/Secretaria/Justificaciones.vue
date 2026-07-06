<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import { Search, CheckCircle2, XCircle, Clock } from 'lucide-vue-next';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from '@/components/ui/dialog';
import { toast } from 'vue-sonner';
import { useDebounceFn } from '@vueuse/core';
import makeJustificacionColumns, { type JustificacionRow } from './justificacion-columns';

// ── Types ────────────────────────────────────────────────
interface PaginatedJustificaciones {
    data: JustificacionRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    justificaciones: PaginatedJustificaciones;
    filters: { q?: string; estado?: string; per_page?: string };
    stats: { pendientes: number; aprobadas: number; rechazadas: number };
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Secretaría', href: '#' },
    { title: 'Justificaciones', href: route('secretaria.justificaciones.index') },
];

// ── Filtros ──────────────────────────────────────────────
const search       = ref(props.filters.q || '');
const filterEstado = ref(props.filters.estado || 'all');

const applyFilters = () => router.get(
    route('secretaria.justificaciones.index'),
    {
        q:        search.value || undefined,
        estado:   filterEstado.value !== 'all' ? filterEstado.value : undefined,
        per_page: props.justificaciones.per_page,
    },
    { preserveState: true, replace: true }
);
const debouncedSearch = useDebounceFn(applyFilters, 300);

// ── Review Modal ─────────────────────────────────────────
const reviewItem = ref<JustificacionRow | null>(null);
const reviewForm = useForm({
    accion: '' as 'aprobar' | 'rechazar',
    comentario_revisor: '',
});

const openReview = (item: JustificacionRow, accion: 'aprobar' | 'rechazar') => {
    reviewItem.value = item;
    reviewForm.accion = accion;
    reviewForm.comentario_revisor = '';
    reviewForm.clearErrors();
};
const closeReview = () => {
    reviewItem.value = null;
    reviewForm.reset();
};
const submitReview = () => {
    if (!reviewItem.value) return;
    reviewForm.patch(route('secretaria.justificacion.review', reviewItem.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(reviewForm.accion === 'aprobar'
                ? '✓ Justificación aprobada. Falta marcada como justificada.'
                : 'Justificación rechazada. Estudiante notificado.');
            closeReview();
        },
        onError: () => toast.error('Error al procesar la revisión.'),
    });
};

// ── TanStack Table ────────────────────────────────────────
const columns = makeJustificacionColumns({ onReview: openReview });

const table = useVueTable(reactive({
    get data() { return props.justificaciones.data; },
    columns,
    getCoreRowModel: getCoreRowModel(),
}));

const navigateToPage = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Justificaciones — Secretaría" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">

            <!-- Header -->
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Revisión de Justificaciones
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Aprueba o rechaza las solicitudes de justificación de faltas enviadas por los estudiantes.
                </p>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-2xl border border-amber-200/60 bg-amber-50/40 p-5 shadow-sm dark:border-amber-900/30 dark:bg-amber-950/10">
                    <div class="flex items-center gap-2 mb-1">
                        <Clock class="h-4 w-4 text-amber-600 animate-pulse" />
                        <p class="text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wide">Pendientes</p>
                    </div>
                    <p class="text-3xl font-extrabold text-amber-700 dark:text-amber-300">{{ stats.pendientes }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-200/60 bg-emerald-50/40 p-5 shadow-sm dark:border-emerald-900/30 dark:bg-emerald-950/10">
                    <div class="flex items-center gap-2 mb-1">
                        <CheckCircle2 class="h-4 w-4 text-emerald-600" />
                        <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wide">Aprobadas</p>
                    </div>
                    <p class="text-3xl font-extrabold text-emerald-700 dark:text-emerald-300">{{ stats.aprobadas }}</p>
                </div>
                <div class="rounded-2xl border border-rose-200/60 bg-rose-50/40 p-5 shadow-sm dark:border-rose-900/30 dark:bg-rose-950/10">
                    <div class="flex items-center gap-2 mb-1">
                        <XCircle class="h-4 w-4 text-rose-600" />
                        <p class="text-xs font-semibold text-rose-700 dark:text-rose-400 uppercase tracking-wide">Rechazadas</p>
                    </div>
                    <p class="text-3xl font-extrabold text-rose-700 dark:text-rose-300">{{ stats.rechazadas }}</p>
                </div>
            </div>

            <!-- Filtros -->
            <div class="flex flex-col sm:flex-row gap-3 bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800">
                <div class="flex-1 relative">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <input
                        v-model="search"
                        @input="debouncedSearch"
                        type="text"
                        placeholder="Buscar por estudiante o cédula..."
                        class="pl-9 w-full rounded-lg border-slate-200 bg-slate-50/30 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200"
                    />
                </div>
                <Select v-model="filterEstado" @update:model-value="applyFilters">
                    <SelectTrigger class="w-[160px]"><SelectValue placeholder="Estado" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todos</SelectItem>
                        <SelectItem value="pendiente">Pendiente</SelectItem>
                        <SelectItem value="aprobada">Aprobada</SelectItem>
                        <SelectItem value="rechazada">Rechazada</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Tabla principal -->
            <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950 overflow-hidden">
                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow
                                v-for="hg in table.getHeaderGroups()" :key="hg.id"
                                class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30"
                            >
                                <TableHead
                                    v-for="header in hg.headers" :key="header.id"
                                    class="py-3.5 px-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide"
                                >
                                    <FlexRender
                                        v-if="!header.isPlaceholder"
                                        :render="header.column.columnDef.header"
                                        :props="header.getContext()"
                                    />
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                            <template v-if="table.getRowModel().rows?.length">
                                <TableRow
                                    v-for="row in table.getRowModel().rows" :key="row.id"
                                    class="hover:bg-slate-50/30 dark:hover:bg-slate-900/10 transition-colors"
                                >
                                    <TableCell
                                        v-for="cell in row.getVisibleCells()" :key="cell.id"
                                        class="py-3.5 px-4"
                                    >
                                        <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell :colspan="columns.length" class="h-32 text-center">
                                    <div class="flex flex-col items-center gap-2 text-slate-400">
                                        <Clock class="h-8 w-8" />
                                        <span class="text-sm font-medium">No hay justificaciones para mostrar</span>
                                        <span class="text-xs">Intenta cambiar los filtros de búsqueda</span>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Paginación -->
                <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800 px-6 py-4">
                    <span class="text-xs text-slate-500">
                        Mostrando {{ justificaciones.data.length }} de {{ justificaciones.total }} solicitudes
                    </span>
                    <div class="flex items-center gap-1">
                        <button
                            v-for="link in justificaciones.links"
                            :key="link.label"
                            @click="navigateToPage(link.url)"
                            :disabled="!link.url || link.active"
                            v-html="link.label"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                            :class="[
                                link.active
                                    ? 'bg-indigo-600 text-white'
                                    : 'border border-slate-200/80 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300 disabled:opacity-40'
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Dialog de Revisión -->
        <Dialog :open="!!reviewItem" @update:open="val => { if (!val) closeReview(); }">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>
                        <span v-if="reviewForm.accion === 'aprobar'" class="flex items-center gap-2 text-emerald-600">
                            <CheckCircle2 class="h-5 w-5" /> Aprobar justificación
                        </span>
                        <span v-else class="flex items-center gap-2 text-rose-600">
                            <XCircle class="h-5 w-5" /> Rechazar justificación
                        </span>
                    </DialogTitle>
                    <DialogDescription v-if="reviewItem">
                        <strong>{{ reviewItem.estudiante.name }}</strong> —
                        Falta del {{ reviewItem.falta.fecha }} en {{ reviewItem.falta.materia ?? 'Sin materia' }}
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-2">
                    <!-- Motivo del estudiante (solo lectura) -->
                    <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-xs">
                        <p class="font-semibold text-slate-500 dark:text-slate-400 mb-1 uppercase">Motivo del estudiante</p>
                        <p class="text-slate-700 dark:text-slate-200">{{ reviewItem?.motivo_estudiante }}</p>
                    </div>

                    <!-- Comentario revisor -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ reviewForm.accion === 'rechazar' ? 'Motivo del rechazo *' : 'Comentario para el estudiante (opcional)' }}
                        </label>
                        <textarea
                            v-model="reviewForm.comentario_revisor"
                            :required="reviewForm.accion === 'rechazar'"
                            rows="3"
                            placeholder="Escribe un comentario que verá el estudiante..."
                            class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <p v-if="reviewForm.errors.comentario_revisor" class="text-xs text-rose-500 mt-1">
                            {{ reviewForm.errors.comentario_revisor }}
                        </p>
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <Button variant="outline" @click="closeReview()">Cancelar</Button>
                    <Button
                        @click="submitReview"
                        :disabled="reviewForm.processing"
                        :class="reviewForm.accion === 'aprobar'
                            ? 'bg-emerald-600 hover:bg-emerald-500 text-white'
                            : 'bg-rose-600 hover:bg-rose-500 text-white'"
                    >
                        {{ reviewForm.processing
                            ? 'Procesando...'
                            : reviewForm.accion === 'aprobar' ? 'Confirmar Aprobación' : 'Confirmar Rechazo' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppSidebarLayout>
</template>
