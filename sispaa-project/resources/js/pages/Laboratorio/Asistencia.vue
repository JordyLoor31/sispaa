<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Check, X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';

interface RosterItem { id: number; name: string; asistio: boolean }

const props = defineProps<{
    practica: { id: number; tema: string; fecha: string | null; materia: string; carrera: string };
    roster: RosterItem[];
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Laboratorio', href: route('laboratorio.index') },
    { title: 'Prácticas', href: route('laboratorio.practicas') },
    { title: 'Asistencia', href: route('laboratorio.practicas.asistencia', props.practica.id) },
];

const form = useForm({
    asistencias: props.roster.map((r) => ({ estudiante_id: r.id, asistio: r.asistio })),
});

const toggle = (index: number) => {
    form.asistencias[index].asistio = !form.asistencias[index].asistio;
};

const marcarTodos = (valor: boolean) => {
    form.asistencias = form.asistencias.map((a) => ({ ...a, asistio: valor }));
};

const submit = () => {
    form.post(route('laboratorio.practicas.asistencia.store', props.practica.id), {
        preserveScroll: true,
        onSuccess: () => toast.success('Asistencia guardada.'),
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Asistencia — ${practica.tema}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <Link :href="route('laboratorio.practicas')" class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-indigo-600 font-semibold mb-2">
                    <ArrowLeft class="h-3.5 w-3.5" /> Volver a prácticas
                </Link>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ practica.tema }}</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ practica.materia }} · {{ practica.carrera }}<span v-if="practica.fecha"> · {{ practica.fecha }}</span></p>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <div class="flex items-center justify-between p-4 border-b border-slate-100 dark:border-slate-800">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Roster ({{ roster.length }} estudiantes)</h2>
                    <div class="flex gap-2">
                        <button @click="marcarTodos(true)" class="text-xs font-semibold text-emerald-600 hover:text-emerald-500">Marcar todos presentes</button>
                        <button @click="marcarTodos(false)" class="text-xs font-semibold text-rose-500 hover:text-rose-600">Marcar todos ausentes</button>
                    </div>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    <div v-for="(a, index) in form.asistencias" :key="a.estudiante_id" class="flex items-center justify-between px-4 py-3">
                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ roster[index]?.name }}</span>
                        <button
                            type="button"
                            @click="toggle(index)"
                            :class="[
                                'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold transition-colors',
                                a.asistio
                                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400'
                                    : 'bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-400',
                            ]"
                        >
                            <Check v-if="a.asistio" class="h-3.5 w-3.5" />
                            <X v-else class="h-3.5 w-3.5" />
                            {{ a.asistio ? 'Presente' : 'Ausente' }}
                        </button>
                    </div>

                    <div v-if="roster.length === 0" class="px-4 py-10 text-center text-sm text-slate-400">
                        No hay estudiantes matriculados en esta carrera para el período de la práctica.
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <Button @click="submit" :disabled="form.processing || roster.length === 0" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    {{ form.processing ? 'Guardando...' : 'Guardar asistencia' }}
                </Button>
            </div>
        </div>
    </AppSidebarLayout>
</template>
