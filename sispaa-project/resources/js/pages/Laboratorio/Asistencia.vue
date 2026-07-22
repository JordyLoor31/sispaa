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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <Link :href="route('laboratorio.practicas')" class="inline-flex items-center gap-1 text-xs opacity-70 text-[var(--sispaa-text)] hover:opacity-100 hover:text-[var(--sispaa-primary)] font-semibold mb-2">
                    <ArrowLeft class="h-3.5 w-3.5" /> Volver a prácticas
                </Link>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">{{ practica.tema }}</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">{{ practica.materia }} · {{ practica.carrera }}<span v-if="practica.fecha"> · {{ practica.fecha }}</span></p>
            </div>

            <div class="rounded-2xl shadow-sm bg-[var(--sispaa-surface)]">
                <div class="flex flex-col gap-2 p-4 border-b border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="text-sm font-bold text-[var(--sispaa-text)]">Roster ({{ roster.length }} estudiantes)</h2>
                    <div class="flex flex-wrap gap-2">
                        <button @click="marcarTodos(true)" class="text-xs font-semibold text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)] hover:opacity-80">Marcar todos presentes</button>
                        <button @click="marcarTodos(false)" class="text-xs font-semibold text-rose-500 hover:text-rose-600">Marcar todos ausentes</button>
                    </div>
                </div>

                <div class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                    <div v-for="(a, index) in form.asistencias" :key="a.estudiante_id" class="flex items-center justify-between px-4 py-3">
                        <span class="text-sm text-[var(--sispaa-text)]">{{ roster[index]?.name }}</span>
                        <button
                            type="button"
                            @click="toggle(index)"
                            :class="[
                                'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold transition-colors',
                                a.asistio
                                    ? 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]'
                                    : 'bg-rose-50 text-rose-600',
                            ]"
                        >
                            <Check v-if="a.asistio" class="h-3.5 w-3.5" />
                            <X v-else class="h-3.5 w-3.5" />
                            {{ a.asistio ? 'Presente' : 'Ausente' }}
                        </button>
                    </div>

                    <div v-if="roster.length === 0" class="px-4 py-10 text-center text-sm opacity-50 text-[var(--sispaa-text)]">
                        No hay estudiantes matriculados en esta carrera para el período de la práctica.
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <Button @click="submit" :disabled="form.processing || roster.length === 0" class="bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)] text-white font-semibold">
                    {{ form.processing ? 'Guardando...' : 'Guardar asistencia' }}
                </Button>
            </div>
        </div>
    </AppSidebarLayout>
</template>
