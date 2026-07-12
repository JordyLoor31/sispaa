<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Bell, CheckCircle2, Clock, Inbox, MailOpen } from 'lucide-vue-next';

interface Notificacion {
    id: number;
    titulo: string;
    mensaje: string;
    leido: boolean;
    created_at: string;
}

const props = defineProps<{
    notificaciones: Notificacion[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Estudiante',
        href: '/dashboard',
    },
    {
        title: 'Mis Notificaciones',
        href: '/estudiante/notificaciones',
    },
];

const form = useForm({});

const markAsRead = () => {
    form.post(route('student.notificaciones.read'), {
        preserveScroll: true,
    });
};

const hasUnread = () => {
    return props.notificaciones.some(n => !n.leido);
};
</script>

<template>
    <Head title="Mis Notificaciones" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Centro de Notificaciones
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Entérate de las novedades y cambios de estado en tus justificaciones y documentos del expediente.
                    </p>
                </div>
                <button
                    v-if="hasUnread()"
                    @click="markAsRead"
                    :disabled="form.processing"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300 dark:hover:bg-slate-900 transition-colors disabled:opacity-50"
                >
                    <MailOpen class="h-4 w-4" />
                    Marcar todo como leído
                </button>
            </div>

            <!-- Listado de Notificaciones -->
            <div class="max-w-3xl w-full mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <div v-if="notificaciones.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
                    <Inbox class="h-14 w-14 text-slate-350 dark:text-slate-700 mb-3" />
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Bandeja de entrada vacía</p>
                    <p class="text-xs text-slate-455 dark:text-slate-500 mt-1">
                        No has recibido alertas del sistema en este periodo.
                    </p>
                </div>

                <div v-else class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    <div
                        v-for="noti in notificaciones"
                        :key="noti.id"
                        class="flex items-start gap-4 py-4 first:pt-0 last:pb-0 relative"
                    >
                        <!-- Indicador de No Leído -->
                        <span v-if="!noti.leido" class="absolute top-5 left-0 flex h-2 w-2 rounded-full bg-indigo-500"></span>

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 pl-0.5" :class="{'pl-2': !noti.leido}">
                            <Bell class="h-5 w-5" />
                        </div>

                        <div class="flex-1 space-y-1">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-bold" :class="[noti.leido ? 'text-slate-800 dark:text-slate-300' : 'text-slate-950 dark:text-white font-extrabold']">
                                    {{ noti.titulo }}
                                </h4>
                                <span class="text-xxs text-slate-400 dark:text-slate-500 flex items-center gap-1">
                                    <Clock class="h-3 w-3" />
                                    {{ new Date(noti.created_at).toLocaleDateString() }}
                                </span>
                            </div>
                            <p class="text-xs leading-relaxed" :class="[noti.leido ? 'text-slate-500 dark:text-slate-400' : 'text-slate-800 dark:text-slate-300']">
                                {{ noti.mensaje }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
