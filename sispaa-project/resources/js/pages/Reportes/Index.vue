<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import { FileSpreadsheet, FileText, FileDown, BarChart3 } from 'lucide-vue-next';
import { BRAND_GRADIENT } from '@/lib/brand';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';

interface Catalogo { id: number; nombre: string }
type Fila = (string | number | null)[];
interface Paginated<T> { data: T[]; current_page: number; last_page: number; per_page: number; total: number; links: any[] }

const props = defineProps<{
    tipos: Record<string, string>;
    tipoActual: string;
    columnas: string[];
    filas: Paginated<Fila>;
    periodos: Catalogo[];
    carreras: Catalogo[];
    grupos: Catalogo[];
    filters: { periodo_id?: string; carrera_id?: string; grupo_id?: string; estado?: string; per_page?: string };
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Reportes', href: route('reportes.index') },
];

const tipo = ref(props.tipoActual);
const periodoId = ref(props.filters.periodo_id || 'all');
const carreraId = ref(props.filters.carrera_id || 'all');
const grupoId = ref(props.filters.grupo_id || 'all');
const estadoDoc = ref(props.filters.estado || 'all');

const filtrosActivos = computed(() => {
    const f: Record<string, string> = { tipo: tipo.value };
    if (tipo.value === 'matriculados') {
        if (periodoId.value !== 'all') f.periodo_id = periodoId.value;
        if (carreraId.value !== 'all') f.carrera_id = carreraId.value;
    } else if (tipo.value === 'faltas') {
        if (periodoId.value !== 'all') f.periodo_id = periodoId.value;
        if (carreraId.value !== 'all') f.carrera_id = carreraId.value;
    } else if (tipo.value === 'documentos') {
        if (grupoId.value !== 'all') f.grupo_id = grupoId.value;
        if (estadoDoc.value !== 'all') f.estado = estadoDoc.value;
    }
    return f;
});

const aplicar = () => {
    router.get(route('reportes.index'), filtrosActivos.value, { preserveState: true, replace: true });
};

const urlExport = (formato: 'csv' | 'xlsx' | 'pdf') => {
    const params = new URLSearchParams(filtrosActivos.value as Record<string, string>).toString();
    return `${route('reportes.export.' + formato)}?${params}`;
};

const navigateToPage = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true });
};

// ── Tabla (@tanstack/vue-table + shadcn Table), columnas dinámicas ──
const columns = computed<ColumnDef<Fila>[]>(() =>
    props.columnas.map((label, idx) => ({
        id: `col-${idx}`,
        header: label,
        cell: ({ row }) => row.original[idx] ?? '—',
    })),
);

const table = useVueTable(
    reactive({
        get data() { return props.filas.data; },
        get columns() { return columns.value; },
        getCoreRowModel: getCoreRowModel(),
    }),
);
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Reportes" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div class="flex items-center gap-3.5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-sm" :style="BRAND_GRADIENT">
                    <BarChart3 class="h-5 w-5" />
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Reportes</h1>
                    <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">Exporta información del sistema en CSV, Excel o PDF.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-end gap-3 rounded-2xl border p-4 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Tipo de reporte</label>
                    <Select v-model="tipo" @update:model-value="aplicar">
                        <SelectTrigger class="w-full sm:w-[240px] bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))]"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="(label, key) in tipos" :key="key" :value="key">{{ label }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <template v-if="tipo === 'matriculados'">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Período</label>
                        <Select v-model="periodoId" @update:model-value="aplicar">
                            <SelectTrigger class="w-full sm:w-[180px] bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))]"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todos</SelectItem>
                                <SelectItem v-for="p in periodos" :key="p.id" :value="String(p.id)">{{ p.nombre }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Carrera</label>
                        <Select v-model="carreraId" @update:model-value="aplicar">
                            <SelectTrigger class="w-full sm:w-[180px] bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))]"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todas</SelectItem>
                                <SelectItem v-for="c in carreras" :key="c.id" :value="String(c.id)">{{ c.nombre }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>

                <template v-else-if="tipo === 'faltas'">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Período</label>
                        <Select v-model="periodoId" @update:model-value="aplicar">
                            <SelectTrigger class="w-full sm:w-[180px] bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))]"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todos</SelectItem>
                                <SelectItem v-for="p in periodos" :key="p.id" :value="String(p.id)">{{ p.nombre }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Carrera</label>
                        <Select v-model="carreraId" @update:model-value="aplicar">
                            <SelectTrigger class="w-full sm:w-[180px] bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))]"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todas</SelectItem>
                                <SelectItem v-for="c in carreras" :key="c.id" :value="String(c.id)">{{ c.nombre }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>

                <template v-else-if="tipo === 'documentos'">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Grupo</label>
                        <Select v-model="grupoId" @update:model-value="aplicar">
                            <SelectTrigger class="w-full sm:w-[200px] bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))]"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todos</SelectItem>
                                <SelectItem v-for="g in grupos" :key="g.id" :value="String(g.id)">{{ g.nombre }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Estado</label>
                        <Select v-model="estadoDoc" @update:model-value="aplicar">
                            <SelectTrigger class="w-full sm:w-[160px] bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))]"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todos</SelectItem>
                                <SelectItem value="pendiente">Pendiente</SelectItem>
                                <SelectItem value="aprobado">Aprobado</SelectItem>
                                <SelectItem value="rechazado">Rechazado</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>

                <div class="flex gap-2 sm:ml-auto">
                    <Button as="a" :href="urlExport('csv')" variant="outline" class="inline-flex items-center gap-1.5">
                        <FileDown class="h-4 w-4" /> CSV
                    </Button>
                    <Button as="a" :href="urlExport('xlsx')" variant="outline" class="inline-flex items-center gap-1.5">
                        <FileSpreadsheet class="h-4 w-4" /> Excel
                    </Button>
                    <Button as="a" :href="urlExport('pdf')" class="inline-flex items-center gap-1.5 rounded-lg font-semibold text-white shadow-md shadow-[color:color-mix(in_srgb,var(--sispaa-primary)_30%,transparent)] transition-all bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)] hover:shadow-lg">
                        <FileText class="h-4 w-4" /> PDF
                    </Button>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                <div class="flex items-center gap-2 border-b px-4 py-3.5 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                    <BarChart3 class="h-4 w-4 text-[var(--sispaa-primary)]" />
                    <h2 class="text-sm font-bold text-[var(--sispaa-text)]">Vista previa — {{ tipos[tipoActual] }}</h2>
                </div>

                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow v-for="hg in table.getHeaderGroups()" :key="hg.id"
                                class="border-b bg-[color:color-mix(in_srgb,var(--sispaa-text)_3%,transparent)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                                <TableHead v-for="header in hg.headers" :key="header.id"
                                    class="h-9 whitespace-nowrap px-3 text-xs font-semibold uppercase tracking-wider opacity-60 text-[var(--sispaa-text)]">
                                    <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-sm">
                            <template v-if="table.getRowModel().rows.length">
                                <TableRow v-for="row in table.getRowModel().rows" :key="row.id"
                                    class="transition-colors hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_5%,transparent)]">
                                    <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id"
                                        class="whitespace-nowrap px-3 py-2 text-sm opacity-80 text-[var(--sispaa-text)]">
                                        <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell :colspan="columnas.length" class="h-32 text-center text-sm opacity-50 text-[var(--sispaa-text)]">
                                    No hay datos para los filtros seleccionados.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Paginación -->
                <div class="flex flex-col gap-3 border-t px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                    <span class="text-xs opacity-60 text-[var(--sispaa-text)]">Mostrando {{ filas.data.length }} de {{ filas.total }} registros</span>
                    <div class="flex flex-wrap items-center gap-1">
                        <button v-for="link in filas.links" :key="link.label" @click="navigateToPage(link.url)"
                            :disabled="!link.url || link.active" v-html="link.label"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="[link.active ? 'text-white shadow-sm bg-[var(--sispaa-primary)]' : 'text-[var(--sispaa-text)] bg-[var(--sispaa-background)] border border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] disabled:opacity-40']" />
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
