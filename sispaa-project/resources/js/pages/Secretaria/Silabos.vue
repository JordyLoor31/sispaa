<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Search, CheckCircle2, XCircle, BookOpen, ArrowUpRight } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
import { useDebounceFn } from '@vueuse/core';

interface SilaboItem {
    id: number;
    estado: 'pendiente' | 'subido' | 'aprobado';
    archivo_url: string | null;
    observaciones: string | null;
    fecha_subida: string | null;
    docente: { id: number; name: string; email: string };
    materia: string;
    carrera: string | null;
    periodo: string;
}
interface Paginated<T> { data: T[]; current_page: number; last_page: number; per_page: number; total: number; links: any[] }
interface Stats { pendientes: number; aprobados: number; total: number }

const props = defineProps<{
    silabos: Paginated<SilaboItem>;
    filters: { q?: string; estado?: string; per_page?: string };
    stats: Stats;
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Secretaría', href: '#' },
    { title: 'Revisión de Sílabos', href: route('secretaria.silabos.index') },
];

const search = ref(props.filters.q || '');
const filterEstado = ref(props.filters.estado || 'all');

const applyFilters = () => {
    router.get(route('secretaria.silabos.index'), {
        q: search.value || undefined,
        estado: filterEstado.value !== 'all' ? filterEstado.value : undefined,
        per_page: props.silabos.per_page,
    }, { preserveState: true, replace: true });
};
const debouncedSearch = useDebounceFn(applyFilters, 300);

// ── Revisión ──────────────────────────────────────
const reviewTarget = ref<SilaboItem | null>(null);
const reviewForm = useForm({ accion: 'aprobar' as 'aprobar' | 'rechazar', observaciones: '' });

const openReview = (item: SilaboItem, accion: 'aprobar' | 'rechazar') => {
    reviewTarget.value = item;
    reviewForm.accion = accion;
    reviewForm.observaciones = '';
    reviewForm.clearErrors();
};

const submitReview = () => {
    if (!reviewTarget.value) return;
    reviewForm.patch(route('secretaria.silabos.review', reviewTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(reviewForm.accion === 'aprobar' ? 'Sílabo aprobado.' : 'Sílabo rechazado.');
            reviewTarget.value = null;
        },
        onError: () => toast.error('Revisa los campos del formulario.'),
    });
};

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        aprobado: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
        subido: 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400',
        pendiente: 'bg-slate-100 text-slate-500 dark:bg-slate-900 dark:text-slate-500',
    };
    return map[estado] ?? map.pendiente;
};

const navigateToPage = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Revisión de Sílabos" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Revisión de Sílabos</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Aprueba o rechaza los sílabos subidos por los docentes.</p>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <p class="text-xs font-semibold text-slate-500 uppercase">Total</p>
                    <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.total }}</p>
                </div>
                <div class="rounded-2xl border border-amber-200/60 bg-amber-50/40 p-5 shadow-sm dark:border-amber-900/30 dark:bg-amber-950/10">
                    <p class="text-xs font-semibold text-amber-700 uppercase">Pendientes de revisión</p>
                    <p class="mt-1 text-3xl font-extrabold text-amber-700 dark:text-amber-300">{{ stats.pendientes }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-200/60 bg-emerald-50/40 p-5 shadow-sm dark:border-emerald-900/30 dark:bg-emerald-950/10">
                    <p class="text-xs font-semibold text-emerald-700 uppercase">Aprobados</p>
                    <p class="mt-1 text-3xl font-extrabold text-emerald-700 dark:text-emerald-300">{{ stats.aprobados }}</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800">
                <div class="flex-1 relative min-w-[200px]">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <input v-model="search" @input="debouncedSearch" type="text" placeholder="Buscar docente..."
                        class="pl-9 w-full rounded-lg border-slate-200 bg-slate-50/30 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200" />
                </div>
                <Select v-model="filterEstado" @update:model-value="applyFilters">
                    <SelectTrigger class="w-[160px]"><SelectValue placeholder="Estado" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todos</SelectItem>
                        <SelectItem value="subido">Subidos</SelectItem>
                        <SelectItem value="pendiente">Pendientes</SelectItem>
                        <SelectItem value="aprobado">Aprobados</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div v-for="item in silabos.data" :key="item.id"
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950 flex flex-col gap-3">
                    <div class="flex items-start justify-between">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                            <BookOpen class="h-4.5 w-4.5" />
                        </div>
                        <span :class="['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', estadoBadge(item.estado)]">
                            {{ item.estado.charAt(0).toUpperCase() + item.estado.slice(1) }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ item.materia }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ item.carrera }} — {{ item.periodo }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ item.docente.name }} · {{ item.docente.email }}</p>
                        <p v-if="item.fecha_subida" class="text-xs text-slate-400">Subido: {{ item.fecha_subida }}</p>
                        <div v-if="item.observaciones" class="mt-2 text-xs border-l-2 border-rose-500 pl-2 text-rose-600 dark:text-rose-400 font-medium">
                            {{ item.observaciones }}
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <a v-if="item.archivo_url" :href="item.archivo_url" target="_blank"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-500 dark:border-slate-800 dark:text-slate-400 dark:hover:bg-slate-900">
                            <ArrowUpRight class="h-4 w-4" />
                        </a>
                        <template v-if="item.estado !== 'aprobado'">
                            <button @click="openReview(item, 'aprobar')"
                                class="inline-flex items-center gap-1 text-xs text-emerald-600 hover:text-emerald-700 border border-emerald-200 hover:border-emerald-300 dark:border-emerald-900/40 rounded-lg px-2.5 py-1.5 transition-colors">
                                <CheckCircle2 class="h-3.5 w-3.5" /> Aprobar
                            </button>
                            <button @click="openReview(item, 'rechazar')"
                                class="inline-flex items-center gap-1 text-xs text-rose-500 hover:text-rose-600 border border-rose-200 hover:border-rose-300 dark:border-rose-900/40 rounded-lg px-2.5 py-1.5 transition-colors">
                                <XCircle class="h-3.5 w-3.5" /> Rechazar
                            </button>
                        </template>
                    </div>
                </div>

                <div v-if="silabos.data.length === 0" class="col-span-full rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 p-10 text-center text-sm text-slate-400">
                    No hay sílabos que coincidan con el filtro.
                </div>
            </div>

            <div class="flex items-center justify-between px-2">
                <span class="text-xs text-slate-500">Mostrando {{ silabos.data.length }} de {{ silabos.total }} sílabos</span>
                <div class="flex items-center gap-1">
                    <button v-for="link in silabos.links" :key="link.label" @click="navigateToPage(link.url)"
                        :disabled="!link.url || link.active" v-html="link.label"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                        :class="[link.active ? 'bg-indigo-600 text-white' : 'border border-slate-200/80 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300 disabled:opacity-40']" />
                </div>
            </div>
        </div>

        <!-- Dialog: Revisión -->
        <Dialog :open="!!reviewTarget" @update:open="val => { if (!val) reviewTarget = null; }">
            <DialogContent class="sm:max-w-sm">
                <DialogHeader>
                    <DialogTitle>{{ reviewForm.accion === 'aprobar' ? 'Aprobar sílabo' : 'Rechazar sílabo' }}</DialogTitle>
                    <DialogDescription v-if="reviewTarget">{{ reviewTarget.materia }} — {{ reviewTarget.docente.name }}</DialogDescription>
                </DialogHeader>
                <div class="py-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Observaciones {{ reviewForm.accion === 'rechazar' ? '*' : '(opcional)' }}
                    </label>
                    <textarea v-model="reviewForm.observaciones" rows="3"
                        class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
                    <p v-if="reviewForm.errors.observaciones" class="text-xs text-rose-500 mt-1">{{ reviewForm.errors.observaciones }}</p>
                </div>
                <DialogFooter class="gap-2">
                    <Button variant="outline" @click="reviewTarget = null">Cancelar</Button>
                    <Button @click="submitReview" :disabled="reviewForm.processing"
                        :class="reviewForm.accion === 'aprobar' ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-rose-600 hover:bg-rose-500'" class="text-white">
                        {{ reviewForm.processing ? 'Guardando...' : (reviewForm.accion === 'aprobar' ? 'Aprobar' : 'Rechazar') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppSidebarLayout>
</template>
