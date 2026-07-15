<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Calendar, Plus, Edit, Clock, X, Check, Save, ChevronsUpDown, PlayCircle, FlagTriangleRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxGroup,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList,
    ComboboxTrigger,
} from '@/components/ui/combobox';
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

type EstadoPeriodo = 'planificado' | 'activo' | 'finalizado';

interface Periodo {
    id: number;
    nombre: string;
    fecha_inicio: string;
    fecha_fin: string;
    tipo: 'semestral' | 'anual' | string;
    estado: EstadoPeriodo;
    fecha_limite_silabo: string | null;
    fecha_limite_informe: string | null;
}

const ESTADO_LABELS: Record<EstadoPeriodo, string> = {
    planificado: 'Planificado',
    activo: 'Activo',
    finalizado: 'Finalizado',
};

const props = defineProps<{
    periodos: Periodo[];
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Administración', href: '#' },
    { title: 'Fechas Límite y Convocatorias', href: '/admin/fechas' },
];

const showPeriodModal = ref(false);
const editingPeriod = ref<Periodo | null>(null);

const showDeadlinesModal = ref(false);
const activePeriodForDeadlines = ref<Periodo | null>(null);

// Form for Periodo
const periodForm = useForm({
    nombre: '',
    fecha_inicio: '',
    fecha_fin: '',
    tipo: 'semestral',
    estado: 'planificado' as EstadoPeriodo
});

// Form for Deadlines
const deadlinesForm = useForm({
    fecha_limite_silabo: '',
    fecha_limite_informe: ''
});

const selectedTipoObj = ref<{ value: string, label: string } | null>(null);

watch(() => periodForm.tipo, (newVal) => {
    if (!newVal) {
        selectedTipoObj.value = { value: 'semestral', label: 'Semestral' };
    } else {
        const formattedLabel = newVal.charAt(0).toUpperCase() + newVal.slice(1);
        selectedTipoObj.value = { value: newVal, label: formattedLabel };
    }
}, { immediate: true });

watch(selectedTipoObj, (newVal) => {
    periodForm.tipo = newVal ? newVal.value : 'semestral';
});

const selectedEstadoObj = ref<{ value: EstadoPeriodo, label: string } | null>(null);

watch(() => periodForm.estado, (newVal) => {
    selectedEstadoObj.value = { value: newVal, label: ESTADO_LABELS[newVal] };
}, { immediate: true });

watch(selectedEstadoObj, (newVal) => {
    if (newVal) periodForm.estado = newVal.value;
});

// Period actions
const openAddPeriod = () => {
    periodForm.reset();
    periodForm.clearErrors();
    editingPeriod.value = null;
    showPeriodModal.value = true;
};

const openEditPeriod = (period: Periodo) => {
    editingPeriod.value = period;
    periodForm.nombre = period.nombre;
    periodForm.fecha_inicio = period.fecha_inicio;
    periodForm.fecha_fin = period.fecha_fin;
    periodForm.tipo = period.tipo;
    periodForm.estado = period.estado;
    periodForm.clearErrors();
    showPeriodModal.value = true;
};

const submitPeriod = () => {
    if (editingPeriod.value) {
        periodForm.put(route('admin.periodos.update', editingPeriod.value.id), {
            onSuccess: () => {
                showPeriodModal.value = false;
                periodForm.reset();
            }
        });
    } else {
        periodForm.post(route('admin.periodos.store'), {
            onSuccess: () => {
                showPeriodModal.value = false;
                periodForm.reset();
            }
        });
    }
};

// Deadlines actions
const openDeadlinesModal = (period: Periodo) => {
    activePeriodForDeadlines.value = period;
    deadlinesForm.fecha_limite_silabo = period.fecha_limite_silabo || '';
    deadlinesForm.fecha_limite_informe = period.fecha_limite_informe || '';
    deadlinesForm.clearErrors();
    showDeadlinesModal.value = true;
};

const submitDeadlines = () => {
    if (!activePeriodForDeadlines.value) return;

    deadlinesForm.put(route('admin.periodos.deadlines.update', activePeriodForDeadlines.value.id), {
        onSuccess: () => {
            showDeadlinesModal.value = false;
            deadlinesForm.reset();
        }
    });
};

const cambiarEstadoPeriodo = (period: Periodo, estado: EstadoPeriodo) => {
    router.put(route('admin.periodos.update', period.id), {
        nombre: period.nombre,
        fecha_inicio: period.fecha_inicio,
        fecha_fin: period.fecha_fin,
        estado
    }, {
        preserveScroll: true
    });
};

const activarPeriodo = (period: Periodo) => cambiarEstadoPeriodo(period, 'activo');
const finalizarPeriodo = (period: Periodo) => cambiarEstadoPeriodo(period, 'finalizado');
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Fechas Límite y Convocatorias" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Fechas Límite y Convocatorias
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Configura las fechas límite y plazos máximos para que los docentes suban sílabos e informes a la plataforma.
                    </p>
                </div>
                <Button
                    @click="openAddPeriod"
                    class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold transition-colors"
                >
                    <Plus class="h-4 w-4" />
                    Nuevo Periodo
                </Button>
            </div>

            <!-- Listado de Periodos -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="period in periodos"
                    :key="period.id"
                    class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950 flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden"
                >
                    <!-- Ribbon de Estado -->
                    <div
                        v-if="period.estado === 'activo'"
                        class="absolute top-0 right-0 bg-emerald-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl flex items-center gap-1 shadow-sm"
                    >
                        <Check class="h-3 w-3" />
                        Activo
                    </div>
                    <div
                        v-else-if="period.estado === 'planificado'"
                        class="absolute top-0 right-0 bg-amber-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl flex items-center gap-1 shadow-sm"
                    >
                        <Clock class="h-3 w-3" />
                        Planificado
                    </div>
                    <div
                        v-else
                        class="absolute top-0 right-0 bg-slate-400 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl flex items-center gap-1 shadow-sm"
                    >
                        <FlagTriangleRight class="h-3 w-3" />
                        Finalizado
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ period.nombre }}</h3>
                            <p class="text-xs text-slate-400 mt-1">
                                Duración: {{ period.fecha_inicio }} al {{ period.fecha_fin }} ({{ period.tipo }})
                            </p>
                        </div>

                        <!-- Fechas Límites -->
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-850 space-y-3">
                            <div>
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Fecha Límite Sílabos</h4>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <Clock class="h-4 w-4 text-slate-400" />
                                    <span class="text-sm font-semibold" :class="[period.fecha_limite_silabo ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 italic']">
                                        {{ period.fecha_limite_silabo ?? 'Sin configurar' }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Fecha Límite Informes</h4>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <Clock class="h-4 w-4 text-slate-400" />
                                    <span class="text-sm font-semibold" :class="[period.fecha_limite_informe ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 italic']">
                                        {{ period.fecha_limite_informe ?? 'Sin configurar' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4 dark:border-slate-850">
                        <!-- Acción contextual según estado -->
                        <button
                            v-if="period.estado === 'planificado'"
                            @click="activarPeriodo(period)"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-500"
                        >
                            <PlayCircle class="h-3.5 w-3.5" />
                            Activar
                        </button>
                        <button
                            v-else-if="period.estado === 'activo'"
                            @click="finalizarPeriodo(period)"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-rose-500 hover:text-rose-600"
                        >
                            <FlagTriangleRight class="h-3.5 w-3.5" />
                            Finalizar
                        </button>
                        <span v-else class="text-xs font-semibold text-slate-400 italic">
                            Sin acciones
                        </span>

                        <div class="flex items-center gap-2">
                            <!-- Botón Configurar Plazos -->
                            <button
                                @click="openDeadlinesModal(period)"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-350 dark:hover:bg-slate-900 transition-colors"
                            >
                                <Save class="h-3.5 w-3.5" />
                                Plazos
                            </button>

                            <!-- Botón Editar Periodo -->
                            <button
                                @click="openEditPeriod(period)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-350 dark:hover:bg-slate-900 transition-colors"
                            >
                                <Edit class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL: Crear / Editar Periodo (AlertDialog Shadcn) -->
            <AlertDialog :open="showPeriodModal" @update:open="showPeriodModal = $event">
                <AlertDialogContent class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-950 p-6 shadow-xl border border-slate-100 dark:border-slate-900">
                    <AlertDialogHeader class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800/85">
                        <div class="flex flex-col gap-1 text-left w-full">
                            <AlertDialogTitle class="text-lg font-bold text-slate-900 dark:text-white">
                                {{ editingPeriod ? 'Editar Periodo' : 'Crear Periodo Académico' }}
                            </AlertDialogTitle>
                            <AlertDialogDescription class="text-xs text-slate-400">
                                Configure los rangos de fechas del ciclo académico.
                            </AlertDialogDescription>
                        </div>
                    </AlertDialogHeader>

                    <form @submit.prevent="submitPeriod" class="mt-4 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase">Nombre del Periodo * (Ej: 2026-1)</label>
                            <input v-model="periodForm.nombre" type="text" required class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase">Fecha Inicio *</label>
                                <input v-model="periodForm.fecha_inicio" type="date" required class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase">Fecha Fin *</label>
                                <input v-model="periodForm.fecha_fin" type="date" required class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                            </div>
                        </div>

                        <div v-if="!editingPeriod">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tipo *</label>
                            <Combobox v-model="selectedTipoObj" by="value">
                                <ComboboxAnchor as-child>
                                    <ComboboxTrigger as-child>
                                        <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350 mt-1">
                                            {{ selectedTipoObj ? selectedTipoObj.label : 'Semestral' }}
                                            <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                        </Button>
                                    </ComboboxTrigger>
                                </ComboboxAnchor>

                                <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[200px] bg-white dark:bg-slate-950 border border-slate-100 dark:border-slate-900 rounded-lg shadow-lg">
                                    <ComboboxInput placeholder="Buscar tipo..." class="w-full border-0 border-b border-slate-105 dark:border-slate-850 bg-transparent text-sm focus:ring-0 py-2.5 px-3" />
                                    <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron tipos.</ComboboxEmpty>
                                    <ComboboxGroup class="p-1">
                                        <ComboboxItem :value="{ value: 'semestral', label: 'Semestral' }" class="flex items-center justify-between px-3 py-2 text-sm rounded-md cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-900 data-[state=checked]:bg-slate-100 dark:data-[state=checked]:bg-slate-800">
                                            Semestral
                                            <ComboboxItemIndicator>
                                                <Check class="h-4 w-4 text-indigo-650" />
                                            </ComboboxItemIndicator>
                                        </ComboboxItem>
                                        <ComboboxItem :value="{ value: 'anual', label: 'Anual' }" class="flex items-center justify-between px-3 py-2 text-sm rounded-md cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-900 data-[state=checked]:bg-slate-100 dark:data-[state=checked]:bg-slate-800">
                                            Anual
                                            <ComboboxItemIndicator>
                                                <Check class="h-4 w-4 text-indigo-650" />
                                            </ComboboxItemIndicator>
                                        </ComboboxItem>
                                    </ComboboxGroup>
                                </ComboboxList>
                            </Combobox>
                        </div>

                        <div v-if="editingPeriod">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Estado *</label>
                            <Combobox v-model="selectedEstadoObj" by="value">
                                <ComboboxAnchor as-child>
                                    <ComboboxTrigger as-child>
                                        <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350 mt-1">
                                            {{ selectedEstadoObj ? selectedEstadoObj.label : 'Planificado' }}
                                            <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                        </Button>
                                    </ComboboxTrigger>
                                </ComboboxAnchor>

                                <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[200px] bg-white dark:bg-slate-950 border border-slate-100 dark:border-slate-900 rounded-lg shadow-lg">
                                    <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron estados.</ComboboxEmpty>
                                    <ComboboxGroup class="p-1">
                                        <ComboboxItem
                                            v-for="opt in (['planificado', 'activo', 'finalizado'] as EstadoPeriodo[])"
                                            :key="opt"
                                            :value="{ value: opt, label: ESTADO_LABELS[opt] }"
                                            class="flex items-center justify-between px-3 py-2 text-sm rounded-md cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-900 data-[state=checked]:bg-slate-100 dark:data-[state=checked]:bg-slate-800"
                                        >
                                            {{ ESTADO_LABELS[opt] }}
                                            <ComboboxItemIndicator>
                                                <Check class="h-4 w-4 text-indigo-650" />
                                            </ComboboxItemIndicator>
                                        </ComboboxItem>
                                    </ComboboxGroup>
                                </ComboboxList>
                            </Combobox>
                            <p class="mt-1 text-[11px] text-slate-400">
                                Al activar este periodo, cualquier otro periodo activo pasará automáticamente a Finalizado.
                            </p>
                        </div>

                        <div class="flex justify-end gap-3 border-t border-slate-100 pt-4 mt-6 dark:border-slate-850">
                            <Button type="button" variant="outline" @click="showPeriodModal = false">
                                Cancelar
                            </Button>
                            <Button type="submit" :disabled="periodForm.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white">
                                {{ editingPeriod ? 'Guardar Cambios' : 'Crear Periodo' }}
                            </Button>
                        </div>
                    </form>
                </AlertDialogContent>
            </AlertDialog>

            <!-- MODAL: Configurar Fechas Límites (AlertDialog Shadcn) -->
            <AlertDialog :open="showDeadlinesModal" @update:open="showDeadlinesModal = $event">
                <AlertDialogContent class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-950 p-6 shadow-xl border border-slate-100 dark:border-slate-900">
                    <AlertDialogHeader class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800/85">
                        <div class="flex flex-col gap-1 text-left w-full">
                            <AlertDialogTitle class="text-lg font-bold text-slate-900 dark:text-white">
                                Configurar Plazos: {{ activePeriodForDeadlines?.nombre }}
                            </AlertDialogTitle>
                            <AlertDialogDescription class="text-xs text-slate-400">
                                Establezca las fechas topes para la entrega de la documentación docente.
                            </AlertDialogDescription>
                        </div>
                    </AlertDialogHeader>

                    <form @submit.prevent="submitDeadlines" class="mt-4 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase">Fecha Límite para Sílabos</label>
                            <input v-model="deadlinesForm.fecha_limite_silabo" type="date" class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase">Fecha Límite para Informes</label>
                            <input v-model="deadlinesForm.fecha_limite_informe" type="date" class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                        </div>

                        <div class="flex justify-end gap-3 border-t border-slate-100 pt-4 mt-6 dark:border-slate-850">
                            <Button type="button" variant="outline" @click="showDeadlinesModal = false">
                                Cancelar
                            </Button>
                            <Button type="submit" :disabled="deadlinesForm.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white">
                                Guardar Plazos
                            </Button>
                        </div>
                    </form>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </AppSidebarLayout>
</template>
