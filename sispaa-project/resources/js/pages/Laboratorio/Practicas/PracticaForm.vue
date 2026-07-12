<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
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

const usoItemSchema = z.object({ id: z.number(), cantidad_usada: z.number().int().min(1) });

const formSchema = toTypedSchema(
    z.object({
        materia_id: z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, { message: 'Selecciona una materia.' }),
        periodo_id: z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, { message: 'Selecciona un período.' }),
        laboratorio_id: z.union([z.string(), z.number()]).nullable(),
        numero_practica: z.coerce.number({ invalid_type_error: 'Ingresa un número válido.' }).int().min(1, 'Debe ser al menos 1.'),
        fecha: z.string().min(1, 'La fecha es obligatoria.'),
        tema: z.string().min(1, 'El tema es obligatorio.').max(255, 'El tema no puede superar los 255 caracteres.'),
        subtema: z.string().max(255).nullable().optional(),
        logro_aprendizaje: z.string().max(255).nullable().optional(),
        semestre: z.string().max(50).nullable().optional(),
        numero_estudiantes: z.coerce.number({ invalid_type_error: 'Ingresa un número válido.' }).int().min(0).nullable().optional(),
        horario: z.string().max(100).nullable().optional(),
        objetivo: z.string().max(2000).nullable().optional(),
        metodologia: z.string().max(2000).nullable().optional(),
        resultados: z.string().max(2000).nullable().optional(),
        conclusiones: z.string().max(2000).nullable().optional(),
        equipos: z.array(usoItemSchema),
        reactivos: z.array(usoItemSchema),
    }),
);

const { handleSubmit, setErrors, defineField } = useForm({
    validationSchema: formSchema,
    initialValues: {
        materia_id: props.practica?.materia_id ?? '',
        periodo_id: props.practica?.periodo_id ?? '',
        laboratorio_id: props.practica?.laboratorio_id ?? '',
        numero_practica: props.practica?.numero_practica ?? 1,
        fecha: props.practica?.fecha ?? '',
        tema: props.practica?.tema ?? '',
        subtema: props.practica?.subtema ?? '',
        logro_aprendizaje: props.practica?.logro_aprendizaje ?? '',
        semestre: props.practica?.semestre ?? '',
        numero_estudiantes: props.practica?.numero_estudiantes ?? null,
        horario: props.practica?.horario ?? '',
        objetivo: props.practica?.objetivo ?? '',
        metodologia: props.practica?.metodologia ?? '',
        resultados: props.practica?.resultados ?? '',
        conclusiones: props.practica?.conclusiones ?? '',
        equipos: props.practica?.equipos ?? [],
        reactivos: props.practica?.reactivos ?? [],
    },
});

const [equipos] = defineField('equipos');
const [reactivos] = defineField('reactivos');

const toggleEquipo = (id: number, checked: boolean) => {
    if (checked) {
        equipos.value = [...(equipos.value ?? []), { id, cantidad_usada: 1 }];
    } else {
        equipos.value = (equipos.value ?? []).filter((e) => e.id !== id);
    }
};
const toggleReactivo = (id: number, checked: boolean) => {
    if (checked) {
        reactivos.value = [...(reactivos.value ?? []), { id, cantidad_usada: 1 }];
    } else {
        reactivos.value = (reactivos.value ?? []).filter((r) => r.id !== id);
    }
};
const equipoChecked = (id: number) => (equipos.value ?? []).some((e) => e.id === id);
const reactivoChecked = (id: number) => (reactivos.value ?? []).some((r) => r.id === id);

const processing = ref(false);

const onSubmit = handleSubmit((values) => {
    processing.value = true;

    const options = {
        preserveScroll: true,
        onSuccess: () => toast.success(isEdit ? 'Práctica actualizada.' : 'Práctica registrada.'),
        onError: (serverErrors: Record<string, string>) => setErrors(serverErrors),
        onFinish: () => {
            processing.value = false;
        },
    };

    if (isEdit && props.practica) {
        router.put(route('laboratorio.practicas.update', props.practica.id), values, options);
    } else {
        router.post(route('laboratorio.practicas.store'), values, options);
    }
});
</script>

<template>
    <form class="space-y-5" @submit="onSubmit">
        <div class="grid grid-cols-2 gap-4">
            <FormField v-slot="{ componentField }" name="materia_id">
                <FormItem>
                    <FormLabel>Materia *</FormLabel>
                    <Select v-bind="componentField">
                        <FormControl>
                            <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona..." /></SelectTrigger>
                        </FormControl>
                        <SelectContent>
                            <SelectItem v-for="m in materias" :key="m.id" :value="m.id">{{ m.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="periodo_id">
                <FormItem>
                    <FormLabel>Período *</FormLabel>
                    <Select v-bind="componentField">
                        <FormControl>
                            <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona..." /></SelectTrigger>
                        </FormControl>
                        <SelectContent>
                            <SelectItem v-for="per in periodos" :key="per.id" :value="per.id">{{ per.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="laboratorio_id">
                <FormItem>
                    <FormLabel>Laboratorio</FormLabel>
                    <Select v-bind="componentField">
                        <FormControl>
                            <SelectTrigger class="w-full"><SelectValue placeholder="Sin asignar" /></SelectTrigger>
                        </FormControl>
                        <SelectContent>
                            <SelectItem v-for="l in laboratorios" :key="l.id" :value="l.id">{{ l.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="numero_practica">
                <FormItem>
                    <FormLabel>N° de práctica *</FormLabel>
                    <FormControl>
                        <Input type="number" min="1" v-bind="componentField" />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="fecha">
                <FormItem>
                    <FormLabel>Fecha *</FormLabel>
                    <FormControl>
                        <Input type="date" v-bind="componentField" />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="horario">
                <FormItem>
                    <FormLabel>Horario</FormLabel>
                    <FormControl>
                        <Input type="text" placeholder="Ej: 08:00 - 10:00" v-bind="componentField" />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
        </div>

        <FormField v-slot="{ componentField }" name="tema">
            <FormItem>
                <FormLabel>Tema *</FormLabel>
                <FormControl>
                    <Input type="text" v-bind="componentField" />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <div class="grid grid-cols-2 gap-4">
            <FormField v-slot="{ componentField }" name="subtema">
                <FormItem>
                    <FormLabel>Subtema</FormLabel>
                    <FormControl>
                        <Input type="text" v-bind="componentField" />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="logro_aprendizaje">
                <FormItem>
                    <FormLabel>Logro de aprendizaje</FormLabel>
                    <FormControl>
                        <Input type="text" v-bind="componentField" />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="semestre">
                <FormItem>
                    <FormLabel>Semestre</FormLabel>
                    <FormControl>
                        <Input type="text" placeholder="Ej: Quinto semestre" v-bind="componentField" />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="numero_estudiantes">
                <FormItem>
                    <FormLabel>N° de estudiantes</FormLabel>
                    <FormControl>
                        <Input type="number" min="0" v-bind="componentField" />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
        </div>

        <FormField v-slot="{ componentField }" name="objetivo">
            <FormItem>
                <FormLabel>Objetivo</FormLabel>
                <FormControl>
                    <textarea
                        v-bind="componentField"
                        rows="2"
                        class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                    ></textarea>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>
        <FormField v-slot="{ componentField }" name="metodologia">
            <FormItem>
                <FormLabel>Metodología</FormLabel>
                <FormControl>
                    <textarea
                        v-bind="componentField"
                        rows="2"
                        class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                    ></textarea>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>
        <FormField v-slot="{ componentField }" name="resultados">
            <FormItem>
                <FormLabel>Resultados</FormLabel>
                <FormControl>
                    <textarea
                        v-bind="componentField"
                        rows="2"
                        class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                    ></textarea>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>
        <FormField v-slot="{ componentField }" name="conclusiones">
            <FormItem>
                <FormLabel>Conclusiones</FormLabel>
                <FormControl>
                    <textarea
                        v-bind="componentField"
                        rows="2"
                        class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                    ></textarea>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">Equipos usados</label>
            <div class="grid max-h-40 grid-cols-2 gap-2 overflow-y-auto rounded-lg border border-slate-200 p-3 dark:border-slate-800">
                <label v-for="e in equiposCatalogo" :key="e.id" class="flex items-center gap-2 text-xs">
                    <input type="checkbox" :checked="equipoChecked(e.id)" class="rounded accent-indigo-600" @change="toggleEquipo(e.id, ($event.target as HTMLInputElement).checked)" />
                    {{ e.nombre }}
                </label>
                <p v-if="equiposCatalogo.length === 0" class="col-span-2 text-xs text-slate-400">No hay equipos registrados.</p>
            </div>
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">Reactivos usados</label>
            <div class="grid max-h-40 grid-cols-2 gap-2 overflow-y-auto rounded-lg border border-slate-200 p-3 dark:border-slate-800">
                <label v-for="r in reactivosCatalogo" :key="r.id" class="flex items-center gap-2 text-xs">
                    <input type="checkbox" :checked="reactivoChecked(r.id)" class="rounded accent-indigo-600" @change="toggleReactivo(r.id, ($event.target as HTMLInputElement).checked)" />
                    {{ r.nombre }}
                </label>
                <p v-if="reactivosCatalogo.length === 0" class="col-span-2 text-xs text-slate-400">No hay reactivos registrados.</p>
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <Button type="submit" :disabled="processing" class="bg-indigo-600 font-semibold text-white hover:bg-indigo-500">
                {{ processing ? 'Guardando...' : 'Guardar' }}
            </Button>
        </div>
    </form>
</template>
