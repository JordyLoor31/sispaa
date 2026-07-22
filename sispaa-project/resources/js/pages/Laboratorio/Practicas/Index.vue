<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, FlaskConical, Pencil, Trash2, ClipboardList } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog';
import { toast } from 'vue-sonner';
import type { Catalogo, PracticaItem } from './types';

const props = defineProps<{
    practicas: PracticaItem[];
    periodos: Catalogo[];
    filters: { periodo_id?: string };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const filterPeriodo = ref(props.filters.periodo_id || 'all');
const applyFilter = () => {
    router.get(route('laboratorio.practicas'), { periodo_id: filterPeriodo.value !== 'all' ? filterPeriodo.value : undefined }, { preserveState: true, replace: true });
};

const deleteTarget = ref<PracticaItem | null>(null);
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(route('laboratorio.practicas.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { toast.success('Práctica eliminada.'); deleteTarget.value = null; },
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Prácticas de Laboratorio" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Prácticas de Laboratorio</h1>
                    <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">Registro, seguimiento y asistencia de prácticas.</p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                    <Link :href="route('laboratorio.practicas.create')">
                        <Plus class="h-4 w-4" /> Nueva Práctica
                    </Link>
                </Button>
            </div>

            <div class="flex flex-wrap gap-3">
                <Select v-model="filterPeriodo" @update:model-value="applyFilter">
                    <SelectTrigger class="w-full sm:w-[200px]"><SelectValue placeholder="Período" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todos los períodos</SelectItem>
                        <SelectItem v-for="per in periodos" :key="per.id" :value="String(per.id)">{{ per.nombre }}</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div v-for="p in practicas" :key="p.id"
                    class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)] flex flex-col gap-3">
                    <div class="flex items-start justify-between">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                            <FlaskConical class="h-4.5 w-4.5" />
                        </div>
                        <span class="text-xs font-semibold opacity-50 text-[var(--sispaa-text)]">N° {{ p.numero_practica }}</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-[var(--sispaa-text)]">{{ p.tema }}</h3>
                        <p class="text-xs opacity-70 text-[var(--sispaa-text)] mt-1">{{ p.materia.nombre }} · {{ p.carrera }}</p>
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)] mt-1">{{ p.docente.name }} · {{ p.periodo }}</p>
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)]">{{ p.laboratorio?.nombre ?? 'Sin laboratorio asignado' }}</p>
                        <p v-if="p.fecha" class="text-xs opacity-50 text-[var(--sispaa-text)]">{{ p.fecha }}<span v-if="p.horario"> · {{ p.horario }}</span></p>
                        <p v-if="p.numero_estudiantes" class="text-xs opacity-50 text-[var(--sispaa-text)]">{{ p.numero_estudiantes }} estudiantes</p>
                    </div>
                    <div v-if="p.equipos.length || p.reactivos.length" class="flex flex-wrap gap-1">
                        <span v-for="e in p.equipos" :key="'e'+e.id" class="text-[10px] rounded-full px-2 py-0.5 bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-[var(--sispaa-text)] opacity-70">{{ e.nombre }} ({{ e.cantidad_usada }})</span>
                        <span v-for="r in p.reactivos" :key="'r'+r.id" class="text-[10px] rounded-full px-2 py-0.5 bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-[var(--sispaa-text)] opacity-70">{{ r.nombre }} ({{ r.cantidad_usada }})</span>
                    </div>
                    <div v-if="p.es_propio" class="flex items-center gap-2 pt-2 border-t border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                        <Link :href="route('laboratorio.practicas.asistencia', p.id)" class="inline-flex items-center gap-1 text-xs text-[var(--sispaa-primary)] hover:opacity-80 font-semibold">
                            <ClipboardList class="h-3.5 w-3.5" /> Asistencia
                        </Link>
                        <Link :href="route('laboratorio.practicas.edit', p.id)" class="ml-auto rounded-lg p-1.5 opacity-60 text-[var(--sispaa-text)] hover:opacity-100 hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                            <Pencil class="h-3.5 w-3.5" />
                        </Link>
                        <button @click="deleteTarget = p" class="rounded-lg p-1.5 text-rose-400 hover:bg-rose-50">
                            <Trash2 class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>

                <div v-if="practicas.length === 0" class="col-span-full rounded-2xl border border-dashed p-10 text-center text-sm opacity-50 border-[color:color-mix(in_srgb,var(--sispaa-text)_25%,transparent)] text-[var(--sispaa-text)]">
                    No hay prácticas registradas.
                </div>
            </div>
        </div>

        <AlertDialog :open="!!deleteTarget" @update:open="val => { if (!val) deleteTarget = null; }">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>¿Eliminar práctica?</AlertDialogTitle>
                    <AlertDialogDescription>Se eliminará "{{ deleteTarget?.tema }}" junto con su asistencia registrada. Esta acción no se puede deshacer.</AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                    <AlertDialogAction @click="confirmDelete" class="bg-rose-600 hover:bg-rose-500">Eliminar</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppSidebarLayout>
</template>
