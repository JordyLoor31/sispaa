<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, FlaskConical, ChevronRight, Trash2, Pencil } from 'lucide-vue-next';
import { BRAND_GRADIENT } from '@/lib/brand';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog';
import { toast } from 'vue-sonner';
import type { ProyectoItem, Catalogo } from './types';

const props = defineProps<{
    proyectos: ProyectoItem[];
    periodos: Catalogo[];
    coordinadores: Catalogo[];
    filters: { estado?: string };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const filterEstado = ref(props.filters.estado || 'all');
const applyFilter = () => {
    router.get(route('investigacion.index'), { estado: filterEstado.value !== 'all' ? filterEstado.value : undefined }, { preserveState: true, replace: true });
};

// ── Cambiar estado (acción inline, no amerita página propia) ─────────
const changeEstado = (p: ProyectoItem, estado: string) => {
    router.put(route('investigacion.update', p.id), { estado }, {
        preserveScroll: true,
        onSuccess: () => toast.success('Estado actualizado.'),
    });
};

// ── Eliminar ──────────────────────────────────────────
const deleteTarget = ref<ProyectoItem | null>(null);
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(route('investigacion.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { toast.success('Proyecto eliminado.'); deleteTarget.value = null; },
    });
};

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        en_curso: 'bg-[color:color-mix(in_srgb,var(--sispaa-accent)_20%,transparent)] text-[var(--sispaa-accent)]',
        pausada: 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_55%,var(--sispaa-text))]',
        finalizada: 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_55%,var(--sispaa-text))]',
    };
    return map[estado] ?? map.en_curso;
};
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

            <div class="w-full max-w-5xl mx-auto space-y-4">
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

                <div class="grid gap-4 md:grid-cols-2">
                    <div v-for="p in proyectos" :key="p.id"
                        class="flex flex-col gap-3 rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                        <div class="flex items-start justify-between">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                                <FlaskConical class="h-4.5 w-4.5" />
                            </div>
                            <span :class="['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', estadoBadge(p.estado)]">
                                {{ p.estado.replace('_', ' ') }}
                            </span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-[var(--sispaa-text)]">{{ p.titulo }}</h3>
                            <p v-if="p.objetivo" class="mt-1 text-xs opacity-70 text-[var(--sispaa-text)] line-clamp-2">{{ p.objetivo }}</p>
                            <p class="mt-2 text-xs opacity-60 text-[var(--sispaa-text)]">Docente: {{ p.docente.name }}</p>
                            <p class="text-xs opacity-60 text-[var(--sispaa-text)]">Coordinador: {{ p.coordinador.name }} · {{ p.periodo }}</p>
                            <p class="mt-1 text-xs opacity-60 text-[var(--sispaa-text)]">Hitos: {{ p.hitos_completados }}/{{ p.total_hitos }} completados</p>
                        </div>

                        <div v-if="p.es_propio || p.es_coordinador" class="flex items-center gap-2">
                            <Select :model-value="p.estado" @update:model-value="val => changeEstado(p, val as string)">
                                <SelectTrigger class="h-8 w-full bg-[var(--sispaa-background)] text-xs"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="en_curso">En curso</SelectItem>
                                    <SelectItem value="pausada">Pausada</SelectItem>
                                    <SelectItem value="finalizada">Finalizada</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 border-t pt-2 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                            <Link :href="route('investigacion.hitos', p.id)"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-[var(--sispaa-primary)] hover:opacity-80">
                                Ver hitos y seguimiento <ChevronRight class="h-3.5 w-3.5" />
                            </Link>
                            <div class="ml-auto flex items-center gap-1" v-if="p.es_propio">
                                <Link :href="route('investigacion.edit', p.id)" class="rounded-lg p-1.5 opacity-60 text-[var(--sispaa-text)] hover:opacity-100 hover:bg-[color:color-mix(in_srgb,var(--sispaa-background)_60%,transparent)]">
                                    <Pencil class="h-3.5 w-3.5" />
                                </Link>
                                <button @click="deleteTarget = p" class="rounded-lg p-1.5 text-rose-500 hover:bg-rose-50">
                                    <Trash2 class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="proyectos.length === 0" class="col-span-full rounded-2xl border border-dashed p-10 text-center text-sm opacity-50 border-[color:color-mix(in_srgb,var(--sispaa-text)_25%,transparent)] text-[var(--sispaa-text)]">
                        No hay proyectos de investigación todavía.
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmar eliminación -->
        <AlertDialog :open="!!deleteTarget" @update:open="val => { if (!val) deleteTarget = null; }">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>¿Eliminar proyecto?</AlertDialogTitle>
                    <AlertDialogDescription>Se eliminará "{{ deleteTarget?.titulo }}" y sus hitos/seguimiento. Esta acción no se puede deshacer.</AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                    <AlertDialogAction @click="confirmDelete" class="bg-rose-600 hover:bg-rose-500">Eliminar</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppSidebarLayout>
</template>
