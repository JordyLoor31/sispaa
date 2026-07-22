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
        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                        Centro de Notificaciones
                    </h1>
                    <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                        Entérate de las novedades y cambios de estado en tus justificaciones y documentos del expediente.
                    </p>
                </div>
                <button
                    v-if="hasUnread()"
                    @click="markAsRead"
                    :disabled="form.processing"
                    class="inline-flex items-center gap-1.5 self-start rounded-lg border px-4 py-2 text-sm font-semibold transition-colors disabled:opacity-50 bg-[var(--sispaa-background)] text-[var(--sispaa-text)] border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_8%,transparent)]"
                >
                    <MailOpen class="h-4 w-4" />
                    Marcar todo como leído
                </button>
            </div>

            <!-- Listado de Notificaciones -->
            <div class="mx-auto w-full max-w-3xl rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                <div v-if="notificaciones.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
                    <Inbox class="mb-3 h-14 w-14 opacity-30 text-[var(--sispaa-text)]" />
                    <p class="text-sm font-bold opacity-80 text-[var(--sispaa-text)]">Bandeja de entrada vacía</p>
                    <p class="mt-1 text-xs opacity-50 text-[var(--sispaa-text)]">
                        No has recibido alertas del sistema en este periodo.
                    </p>
                </div>

                <div v-else class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                    <div
                        v-for="noti in notificaciones"
                        :key="noti.id"
                        class="relative flex items-start gap-4 py-4 first:pt-0 last:pb-0"
                    >
                        <!-- Indicador de No Leído -->
                        <span v-if="!noti.leido" class="absolute left-0 top-5 flex h-2 w-2 rounded-full bg-[var(--sispaa-primary)]"></span>

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl pl-0.5 bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)] text-[var(--sispaa-primary)]" :class="{'pl-2': !noti.leido}">
                            <Bell class="h-5 w-5" />
                        </div>

                        <div class="flex-1 space-y-1">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                <h4 class="text-sm font-bold" :class="[noti.leido ? 'opacity-80 text-[var(--sispaa-text)]' : 'font-extrabold text-[var(--sispaa-text)]']">
                                    {{ noti.titulo }}
                                </h4>
                                <span class="text-xxs flex items-center gap-1 opacity-50 text-[var(--sispaa-text)]">
                                    <Clock class="h-3 w-3" />
                                    {{ new Date(noti.created_at).toLocaleDateString() }}
                                </span>
                            </div>
                            <p class="text-xs leading-relaxed" :class="[noti.leido ? 'opacity-60 text-[var(--sispaa-text)]' : 'opacity-80 text-[var(--sispaa-text)]']">
                                {{ noti.mensaje }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
