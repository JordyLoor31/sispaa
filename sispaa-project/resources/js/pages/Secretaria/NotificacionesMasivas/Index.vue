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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Notificaciones Masivas</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                    Envía un aviso a todos los usuarios de uno o más roles del sistema.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-2">
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                    <h3 class="flex items-center gap-2 text-base font-bold text-[var(--sispaa-text)]">
                        <Send class="h-4.5 w-4.5 text-[var(--sispaa-primary)]" />
                        Nueva notificación
                    </h3>

                    <form @submit.prevent="submit" class="mt-5 space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-[var(--sispaa-text)] mb-1.5">Título *</label>
                            <input v-model="form.titulo" type="text" class="w-full rounded-lg border-0 bg-[var(--sispaa-background)] text-sm text-[var(--sispaa-text)] focus:ring-2 focus:ring-[var(--sispaa-primary)]" />
                            <p v-if="form.errors.titulo" class="text-xs text-rose-500 mt-1">{{ form.errors.titulo }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[var(--sispaa-text)] mb-1.5">Mensaje *</label>
                            <textarea v-model="form.mensaje" rows="4" class="w-full rounded-lg border-0 bg-[var(--sispaa-background)] text-sm text-[var(--sispaa-text)] focus:ring-2 focus:ring-[var(--sispaa-primary)]"></textarea>
                            <p v-if="form.errors.mensaje" class="text-xs text-rose-500 mt-1">{{ form.errors.mensaje }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[var(--sispaa-text)] mb-2">Roles destinatarios *</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="role in roles"
                                    :key="role"
                                    type="button"
                                    @click="toggleRole(role)"
                                    :class="['inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold transition-colors',
                                        form.roles.includes(role)
                                            ? 'bg-[var(--sispaa-primary)] border-[var(--sispaa-primary)] text-white'
                                            : 'bg-[var(--sispaa-background)] text-[var(--sispaa-text)] opacity-70 hover:opacity-100 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]']"
                                >
                                    <Users class="h-3.5 w-3.5" />
                                    {{ role }}
                                </button>
                            </div>
                        </div>

                        <button type="submit" :disabled="form.processing"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold text-white transition-colors disabled:opacity-50 bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                            <Send class="h-4 w-4" />
                            {{ form.processing ? 'Enviando...' : 'Enviar notificación' }}
                        </button>
                    </form>
                </div>

                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                    <h3 class="flex items-center gap-2 text-base font-bold text-[var(--sispaa-text)]">
                        <History class="h-4.5 w-4.5 text-[var(--sispaa-primary)]" />
                        Historial de envíos
                    </h3>

                    <div class="mt-5 max-h-[520px] space-y-3 overflow-y-auto">
                        <div v-for="(item, i) in historial" :key="i"
                            class="rounded-xl p-4 bg-[color:color-mix(in_srgb,var(--sispaa-text)_5%,transparent)]">
                            <div class="flex items-start justify-between gap-2">
                                <h4 class="text-sm font-semibold text-[var(--sispaa-text)]">{{ item.titulo }}</h4>
                                <span class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                                    {{ item.total_destinatarios }} destinatarios
                                </span>
                            </div>
                            <p class="mt-1 line-clamp-2 text-xs opacity-60 text-[var(--sispaa-text)]">{{ item.mensaje }}</p>
                            <p class="mt-2 text-xxs opacity-50 text-[var(--sispaa-text)]">{{ item.enviado_en }}</p>
                        </div>

                        <div v-if="historial.length === 0" class="py-8 text-center text-sm opacity-50 text-[var(--sispaa-text)]">
                            Aún no se han enviado notificaciones masivas.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
