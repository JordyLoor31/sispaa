<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Send, History, Users } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

interface HistorialItem {
    titulo: string;
    mensaje: string;
    enviado_en: string;
    total_destinatarios: number;
}

const props = defineProps<{
    historial: HistorialItem[];
    roles: string[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const form = useForm({
    titulo: '',
    mensaje: '',
    roles: [] as string[],
});

const toggleRole = (role: string) => {
    const idx = form.roles.indexOf(role);
    if (idx >= 0) form.roles.splice(idx, 1);
    else form.roles.push(role);
};

const submit = () => {
    if (form.roles.length === 0) {
        toast.error('Selecciona al menos un rol destinatario.');
        return;
    }

    form.post(route('secretaria.notificaciones-masivas.store'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Notificación enviada correctamente.');
            form.reset();
        },
        onError: () => toast.error('Revisa los campos del formulario.'),
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Notificaciones Masivas" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Notificaciones Masivas</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Envía un aviso a todos los usuarios de uno o más roles del sistema.
                </p>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <Send class="h-4.5 w-4.5 text-indigo-500" />
                        Nueva notificación
                    </h3>

                    <form @submit.prevent="submit" class="mt-5 space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Título *</label>
                            <input v-model="form.titulo" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                            <p v-if="form.errors.titulo" class="text-xs text-rose-500 mt-1">{{ form.errors.titulo }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mensaje *</label>
                            <textarea v-model="form.mensaje" rows="4" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
                            <p v-if="form.errors.mensaje" class="text-xs text-rose-500 mt-1">{{ form.errors.mensaje }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Roles destinatarios *</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="role in roles"
                                    :key="role"
                                    type="button"
                                    @click="toggleRole(role)"
                                    :class="['inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold border transition-colors',
                                        form.roles.includes(role)
                                            ? 'bg-indigo-600 border-indigo-600 text-white'
                                            : 'bg-white border-slate-200 text-slate-600 hover:border-indigo-300 dark:bg-slate-950 dark:border-slate-700 dark:text-slate-300']"
                                >
                                    <Users class="h-3.5 w-3.5" />
                                    {{ role }}
                                </button>
                            </div>
                        </div>

                        <button type="submit" :disabled="form.processing"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50 transition-colors">
                            <Send class="h-4 w-4" />
                            {{ form.processing ? 'Enviando...' : 'Enviar notificación' }}
                        </button>
                    </form>
                </div>

                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <History class="h-4.5 w-4.5 text-indigo-500" />
                        Historial de envíos
                    </h3>

                    <div class="mt-5 space-y-3 max-h-[520px] overflow-y-auto">
                        <div v-for="(item, i) in historial" :key="i"
                            class="rounded-xl border border-slate-100 dark:border-slate-800/80 bg-slate-50/30 dark:bg-slate-950 p-4">
                            <div class="flex items-start justify-between gap-2">
                                <h4 class="text-sm font-semibold text-slate-900 dark:text-white">{{ item.titulo }}</h4>
                                <span class="shrink-0 inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-400">
                                    {{ item.total_destinatarios }} destinatarios
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ item.mensaje }}</p>
                            <p class="text-xxs text-slate-400 mt-2">{{ item.enviado_en }}</p>
                        </div>

                        <div v-if="historial.length === 0" class="text-center text-sm text-slate-400 py-8">
                            Aún no se han enviado notificaciones masivas.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
