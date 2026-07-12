<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Search, UserCheck } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'vue-sonner';
import type { Estudiante, Periodo, Carrera } from './types';

const props = defineProps<{
    estudiantes: Estudiante[];
    periodos: Periodo[];
    carreras: Carrera[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const estudianteSearch = ref('');
const filteredEstudiantes = computed(() => {
    const q = estudianteSearch.value.toLowerCase();
    if (!q) return props.estudiantes.slice(0, 30);
    return props.estudiantes.filter(e =>
        e.name.toLowerCase().includes(q) ||
        (e.cedula && e.cedula.includes(q)) ||
        e.email.toLowerCase().includes(q)
    ).slice(0, 30);
});

const form = useForm({
    estudiante_id: '' as number | '',
    periodo_id: '' as number | '',
    carrera_id: '' as number | '',
    fecha_matricula: new Date().toISOString().split('T')[0],
});

const selectedEstudiante = computed(() =>
    props.estudiantes.find(e => e.id === form.estudiante_id)
);

const submit = () => {
    form.post(route('secretaria.matriculas.store'), {
        preserveScroll: true,
        onSuccess: () => toast.success('Matrícula registrada correctamente ✓'),
        onError: (errors) => {
            const firstError = Object.values(errors)[0] as string;
            toast.error(firstError || 'Error al registrar la matrícula.');
        },
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Nueva Matrícula" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                    <UserCheck class="h-6 w-6 text-indigo-500" /> Nueva Matrícula
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Inscribe un estudiante en un período académico y carrera.
                </p>
            </div>

            <div class="max-w-xl rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Estudiante *</label>
                        <div class="relative">
                            <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400 pointer-events-none" />
                            <input
                                v-model="estudianteSearch"
                                type="text"
                                placeholder="Buscar por nombre o cédula..."
                                class="pl-9 w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                        <div v-if="estudianteSearch || !form.estudiante_id"
                            class="mt-1 max-h-48 overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm">
                            <button
                                type="button"
                                v-for="e in filteredEstudiantes"
                                :key="e.id"
                                @click="() => { form.estudiante_id = e.id; estudianteSearch = e.name; }"
                                class="w-full text-left px-3 py-2.5 text-sm hover:bg-indigo-50 dark:hover:bg-indigo-950/20 transition-colors border-b border-slate-100 dark:border-slate-800 last:border-0"
                                :class="{ 'bg-indigo-50 dark:bg-indigo-950/20': form.estudiante_id === e.id }"
                            >
                                <p class="font-semibold text-slate-800 dark:text-slate-200">{{ e.name }}</p>
                                <p class="text-xs text-slate-400">{{ e.cedula ?? e.email }}</p>
                            </button>
                            <p v-if="filteredEstudiantes.length === 0" class="px-3 py-4 text-sm text-center text-slate-400">
                                Sin resultados
                            </p>
                        </div>
                        <div v-if="form.estudiante_id && !estudianteSearch.length" class="mt-1 text-xs text-emerald-600 font-semibold">
                            ✓ {{ selectedEstudiante?.name }}
                        </div>
                        <p v-if="form.errors.estudiante_id" class="text-xs text-rose-500 mt-1">{{ form.errors.estudiante_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Período Académico *</label>
                        <Select v-model="form.periodo_id">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Selecciona un período..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="p in periodos" :key="p.id" :value="p.id">
                                    {{ p.nombre }}
                                    <span v-if="p.activo" class="ml-1 text-emerald-500 text-xs">(activo)</span>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.periodo_id" class="text-xs text-rose-500 mt-1">{{ form.errors.periodo_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Carrera *</label>
                        <Select v-model="form.carrera_id">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Selecciona una carrera..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="c in carreras" :key="c.id" :value="c.id">
                                    {{ c.codigo }} — {{ c.nombre }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.carrera_id" class="text-xs text-rose-500 mt-1">{{ form.errors.carrera_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Fecha de Matrícula *</label>
                        <input
                            type="date"
                            v-model="form.fecha_matricula"
                            class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <p v-if="form.errors.fecha_matricula" class="text-xs text-rose-500 mt-1">{{ form.errors.fecha_matricula }}</p>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <Button
                            type="submit"
                            :disabled="form.processing || !form.estudiante_id || !form.periodo_id || !form.carrera_id"
                            class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold disabled:opacity-50"
                        >
                            {{ form.processing ? 'Registrando...' : 'Registrar Matrícula' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
