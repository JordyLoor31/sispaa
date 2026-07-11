<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { FileSpreadsheet, FileText, FileDown, BarChart3 } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';

interface Catalogo { id: number; nombre: string }

const props = defineProps<{
    tipos: Record<string, string>;
    tipoActual: string;
    columnas: string[];
    filas: (string | number | null)[][];
    periodos: Catalogo[];
    carreras: Catalogo[];
    grupos: Catalogo[];
    filters: { periodo_id?: string; carrera_id?: string; grupo_id?: string; estado?: string };
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
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Reportes" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Reportes</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Exporta información del sistema en CSV, Excel o PDF.</p>
            </div>

            <div class="flex flex-wrap items-end gap-3 bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Tipo de reporte</label>
                    <Select v-model="tipo" @update:model-value="aplicar">
                        <SelectTrigger class="w-[240px]"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="(label, key) in tipos" :key="key" :value="key">{{ label }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <template v-if="tipo === 'matriculados'">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Período</label>
                        <Select v-model="periodoId" @update:model-value="aplicar">
                            <SelectTrigger class="w-[180px]"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todos</SelectItem>
                                <SelectItem v-for="p in periodos" :key="p.id" :value="String(p.id)">{{ p.nombre }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Carrera</label>
                        <Select v-model="carreraId" @update:model-value="aplicar">
                            <SelectTrigger class="w-[180px]"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todas</SelectItem>
                                <SelectItem v-for="c in carreras" :key="c.id" :value="String(c.id)">{{ c.nombre }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>

                <template v-else-if="tipo === 'faltas'">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Período</label>
                        <Select v-model="periodoId" @update:model-value="aplicar">
                            <SelectTrigger class="w-[180px]"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todos</SelectItem>
                                <SelectItem v-for="p in periodos" :key="p.id" :value="String(p.id)">{{ p.nombre }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>

                <template v-else-if="tipo === 'documentos'">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Grupo</label>
                        <Select v-model="grupoId" @update:model-value="aplicar">
                            <SelectTrigger class="w-[200px]"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todos</SelectItem>
                                <SelectItem v-for="g in grupos" :key="g.id" :value="String(g.id)">{{ g.nombre }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Estado</label>
                        <Select v-model="estadoDoc" @update:model-value="aplicar">
                            <SelectTrigger class="w-[160px]"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todos</SelectItem>
                                <SelectItem value="pendiente">Pendiente</SelectItem>
                                <SelectItem value="aprobado">Aprobado</SelectItem>
                                <SelectItem value="rechazado">Rechazado</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>

                <div class="ml-auto flex gap-2">
                    <Button as="a" :href="urlExport('csv')" variant="outline" class="inline-flex items-center gap-1.5">
                        <FileDown class="h-4 w-4" /> CSV
                    </Button>
                    <Button as="a" :href="urlExport('xlsx')" variant="outline" class="inline-flex items-center gap-1.5">
                        <FileSpreadsheet class="h-4 w-4" /> Excel
                    </Button>
                    <Button as="a" :href="urlExport('pdf')" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                        <FileText class="h-4 w-4" /> PDF
                    </Button>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950 overflow-hidden">
                <div class="flex items-center gap-2 p-4 border-b border-slate-100 dark:border-slate-800">
                    <BarChart3 class="h-4 w-4 text-indigo-500" />
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Vista previa — {{ tipos[tipoActual] }}</h2>
                    <span class="ml-auto text-xs text-slate-400">{{ filas.length }} registros</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-900 text-xs uppercase text-slate-500">
                            <tr>
                                <th v-for="col in columnas" :key="col" class="px-4 py-3 text-left whitespace-nowrap">{{ col }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(fila, i) in filas" :key="i" class="border-t border-slate-100 dark:border-slate-800">
                                <td v-for="(valor, j) in fila" :key="j" class="px-4 py-3 text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ valor ?? '—' }}</td>
                            </tr>
                            <tr v-if="filas.length === 0">
                                <td :colspan="columnas.length" class="px-4 py-10 text-center text-sm text-slate-400">No hay datos para los filtros seleccionados.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
