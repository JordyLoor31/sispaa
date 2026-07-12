<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { computed, ref } from 'vue';
import { Search, UserCheck } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
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
    return props.estudiantes
        .filter((e) => e.name.toLowerCase().includes(q) || (e.cedula && e.cedula.includes(q)) || e.email.toLowerCase().includes(q))
        .slice(0, 30);
});

const requiredSelect = (message: string) =>
    z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, { message });

const formSchema = toTypedSchema(
    z.object({
        estudiante_id: requiredSelect('Selecciona un estudiante.'),
        periodo_id: requiredSelect('Selecciona un período.'),
        carrera_id: requiredSelect('Selecciona una carrera.'),
        fecha_matricula: z.string().min(1, 'La fecha de matrícula es obligatoria.'),
    }),
);

const { handleSubmit, setErrors, defineField, values } = useForm({
    validationSchema: formSchema,
    initialValues: {
        estudiante_id: '',
        periodo_id: '',
        carrera_id: '',
        fecha_matricula: new Date().toISOString().split('T')[0],
    },
});

const [estudianteId] = defineField('estudiante_id');

const selectedEstudiante = computed(() => props.estudiantes.find((e) => e.id === estudianteId.value));

const processing = ref(false);

const onSubmit = handleSubmit((submitValues) => {
    processing.value = true;

    router.post(route('secretaria.matriculas.store'), submitValues, {
        preserveScroll: true,
        onSuccess: () => toast.success('Matrícula registrada correctamente ✓'),
        onError: (serverErrors: Record<string, string>) => {
            setErrors(serverErrors);
            const firstError = Object.values(serverErrors)[0];
            toast.error(firstError || 'Error al registrar la matrícula.');
        },
        onFinish: () => {
            processing.value = false;
        },
    });
});
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Nueva Matrícula" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="flex items-center gap-2 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    <UserCheck class="h-6 w-6 text-indigo-500" /> Nueva Matrícula
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Inscribe un estudiante en un período académico y carrera.</p>
            </div>

            <div class="max-w-xl mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <form class="space-y-5" @submit="onSubmit">
                    <FormField v-slot="{ errorMessage }" name="estudiante_id">
                        <FormItem>
                            <FormLabel>Estudiante *</FormLabel>
                            <div class="relative">
                                <Search class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                                <input
                                    v-model="estudianteSearch"
                                    type="text"
                                    placeholder="Buscar por nombre o cédula..."
                                    class="w-full rounded-lg border-slate-300 bg-white pl-9 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                                />
                            </div>
                            <div
                                v-if="estudianteSearch || !estudianteId"
                                class="mt-1 max-h-48 overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900"
                            >
                                <button
                                    v-for="e in filteredEstudiantes"
                                    :key="e.id"
                                    type="button"
                                    class="w-full border-b border-slate-100 px-3 py-2.5 text-left text-sm transition-colors last:border-0 hover:bg-indigo-50 dark:border-slate-800 dark:hover:bg-indigo-950/20"
                                    :class="{ 'bg-indigo-50 dark:bg-indigo-950/20': estudianteId === e.id }"
                                    @click="
                                        () => {
                                            estudianteId = e.id;
                                            estudianteSearch = e.name;
                                        }
                                    "
                                >
                                    <p class="font-semibold text-slate-800 dark:text-slate-200">{{ e.name }}</p>
                                    <p class="text-xs text-slate-400">{{ e.cedula ?? e.email }}</p>
                                </button>
                                <p v-if="filteredEstudiantes.length === 0" class="px-3 py-4 text-center text-sm text-slate-400">Sin resultados</p>
                            </div>
                            <div v-if="estudianteId && !estudianteSearch.length" class="mt-1 text-xs font-semibold text-emerald-600">
                                ✓ {{ selectedEstudiante?.name }}
                            </div>
                            <p v-if="errorMessage" class="mt-1 text-sm font-medium text-destructive">{{ errorMessage }}</p>
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="periodo_id">
                        <FormItem>
                            <FormLabel>Período Académico *</FormLabel>
                            <Select v-bind="componentField">
                                <FormControl>
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Selecciona un período..." />
                                    </SelectTrigger>
                                </FormControl>
                                <SelectContent>
                                    <SelectItem v-for="p in periodos" :key="p.id" :value="p.id">
                                        {{ p.nombre }}
                                        <span v-if="p.activo" class="ml-1 text-xs text-emerald-500">(activo)</span>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="carrera_id">
                        <FormItem>
                            <FormLabel>Carrera *</FormLabel>
                            <Select v-bind="componentField">
                                <FormControl>
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Selecciona una carrera..." />
                                    </SelectTrigger>
                                </FormControl>
                                <SelectContent>
                                    <SelectItem v-for="c in carreras" :key="c.id" :value="c.id"> {{ c.codigo }} — {{ c.nombre }} </SelectItem>
                                </SelectContent>
                            </Select>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="fecha_matricula">
                        <FormItem>
                            <FormLabel>Fecha de Matrícula *</FormLabel>
                            <FormControl>
                                <input
                                    v-bind="componentField"
                                    type="date"
                                    class="w-full rounded-lg border-slate-300 bg-white text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                                />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <div class="flex items-center gap-2 pt-2">
                        <Button
                            type="submit"
                            :disabled="processing || !values.estudiante_id || !values.periodo_id || !values.carrera_id"
                            class="bg-indigo-600 font-semibold text-white hover:bg-indigo-500 disabled:opacity-50"
                        >
                            {{ processing ? 'Registrando...' : 'Registrar Matrícula' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
