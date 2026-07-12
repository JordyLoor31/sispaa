<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'vue-sonner';
import type { Catalogo, PracticaEditItem } from './types';

const props = defineProps<{
    practica?: PracticaEditItem;
    materias: Catalogo[];
    laboratorios: Catalogo[];
    periodos: Catalogo[];
    equiposCatalogo: Catalogo[];
    reactivosCatalogo: Catalogo[];
}>();

const isEdit = !!props.practica;

const form = useForm({
    materia_id: props.practica?.materia_id ?? ('' as number | ''),
    periodo_id: props.practica?.periodo_id ?? ('' as number | ''),
    laboratorio_id: props.practica?.laboratorio_id ?? ('' as number | ''),
    numero_practica: props.practica?.numero_practica ?? 1,
    fecha: props.practica?.fecha ?? '',
    tema: props.practica?.tema ?? '',
    subtema: props.practica?.subtema ?? '',
    logro_aprendizaje: props.practica?.logro_aprendizaje ?? '',
    semestre: props.practica?.semestre ?? '',
    numero_estudiantes: props.practica?.numero_estudiantes ?? (null as number | null),
    horario: props.practica?.horario ?? '',
    objetivo: props.practica?.objetivo ?? '',
    metodologia: props.practica?.metodologia ?? '',
    resultados: props.practica?.resultados ?? '',
    conclusiones: props.practica?.conclusiones ?? '',
    equipos: props.practica?.equipos ?? ([] as { id: number; cantidad_usada: number }[]),
    reactivos: props.practica?.reactivos ?? ([] as { id: number; cantidad_usada: number }[]),
});

const toggleEquipo = (id: number, checked: boolean) => {
    if (checked) form.equipos.push({ id, cantidad_usada: 1 });
    else form.equipos = form.equipos.filter((e) => e.id !== id);
};
const toggleReactivo = (id: number, checked: boolean) => {
    if (checked) form.reactivos.push({ id, cantidad_usada: 1 });
    else form.reactivos = form.reactivos.filter((r) => r.id !== id);
};
const equipoChecked = (id: number) => form.equipos.some((e) => e.id === id);
const reactivoChecked = (id: number) => form.reactivos.some((r) => r.id === id);

const submit = () => {
    if (isEdit && props.practica) {
        form.put(route('laboratorio.practicas.update', props.practica.id), {
            preserveScroll: true,
            onSuccess: () => toast.success('Práctica actualizada.'),
        });
    } else {
        form.post(route('laboratorio.practicas.store'), {
            onSuccess: () => toast.success('Práctica registrada.'),
        });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-5">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Materia *</label>
                <Select v-model="form.materia_id">
                    <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona..." /></SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="m in materias" :key="m.id" :value="m.id">{{ m.nombre }}</SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.materia_id" class="text-xs text-rose-500 mt-1">{{ form.errors.materia_id }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Período *</label>
                <Select v-model="form.periodo_id">
                    <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona..." /></SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="per in periodos" :key="per.id" :value="per.id">{{ per.nombre }}</SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.periodo_id" class="text-xs text-rose-500 mt-1">{{ form.errors.periodo_id }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Laboratorio</label>
                <Select v-model="form.laboratorio_id">
                    <SelectTrigger class="w-full"><SelectValue placeholder="Sin asignar" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="l in laboratorios" :key="l.id" :value="l.id">{{ l.nombre }}</SelectItem>
                    </SelectContent>
                </Select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">N° de práctica *</label>
                <input v-model.number="form.numero_practica" type="number" min="1" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                <p v-if="form.errors.numero_practica" class="text-xs text-rose-500 mt-1">{{ form.errors.numero_practica }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Fecha *</label>
                <input v-model="form.fecha" type="date" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                <p v-if="form.errors.fecha" class="text-xs text-rose-500 mt-1">{{ form.errors.fecha }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Horario</label>
                <input v-model="form.horario" type="text" placeholder="Ej: 08:00 - 10:00" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tema *</label>
            <input v-model="form.tema" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
            <p v-if="form.errors.tema" class="text-xs text-rose-500 mt-1">{{ form.errors.tema }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Subtema</label>
                <input v-model="form.subtema" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Logro de aprendizaje</label>
                <input v-model="form.logro_aprendizaje" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Semestre</label>
                <input v-model="form.semestre" type="text" placeholder="Ej: Quinto semestre" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">N° de estudiantes</label>
                <input v-model.number="form.numero_estudiantes" type="number" min="0" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Objetivo</label>
            <textarea v-model="form.objetivo" rows="2" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Metodología</label>
            <textarea v-model="form.metodologia" rows="2" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Resultados</label>
            <textarea v-model="form.resultados" rows="2" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Conclusiones</label>
            <textarea v-model="form.conclusiones" rows="2" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Equipos usados</label>
            <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-800 p-3">
                <label v-for="e in equiposCatalogo" :key="e.id" class="flex items-center gap-2 text-xs">
                    <input type="checkbox" :checked="equipoChecked(e.id)" @change="toggleEquipo(e.id, ($event.target as HTMLInputElement).checked)" class="rounded accent-indigo-600" />
                    {{ e.nombre }}
                </label>
                <p v-if="equiposCatalogo.length === 0" class="text-xs text-slate-400 col-span-2">No hay equipos registrados.</p>
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Reactivos usados</label>
            <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-800 p-3">
                <label v-for="r in reactivosCatalogo" :key="r.id" class="flex items-center gap-2 text-xs">
                    <input type="checkbox" :checked="reactivoChecked(r.id)" @change="toggleReactivo(r.id, ($event.target as HTMLInputElement).checked)" class="rounded accent-indigo-600" />
                    {{ r.nombre }}
                </label>
                <p v-if="reactivosCatalogo.length === 0" class="text-xs text-slate-400 col-span-2">No hay reactivos registrados.</p>
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                {{ form.processing ? 'Guardando...' : 'Guardar' }}
            </Button>
        </div>
    </form>
</template>
