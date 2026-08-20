<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { AlertCircle, ClipboardEdit } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';

const props = defineProps<{
    periodoActivo: { id: number; nombre: string } | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inicio', href: route('dashboard') },
    { title: 'Actualizar Perfil', href: '' },
];

const needsProfileUpdate = computed(() => usePage().props.auth?.user?.needs_profile_update ?? false);

if (!needsProfileUpdate.value) {
    router.visit(route('dashboard'));
}

function irAlWizard() {
    router.visit(route('student.perfil.edit'));
}
</script>

<template>
    <Head title="Actualizar Perfil Requerido" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full items-center justify-center p-4">
            <div class="w-full max-w-lg">
                <div
                    class="rounded-xl border border-amber-200 bg-amber-50 p-8 text-center shadow-sm dark:border-amber-800 dark:bg-amber-950/30"
                >
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/50"
                    >
                        <AlertCircle class="h-8 w-8 text-amber-600 dark:text-amber-400" />
                    </div>

                    <h1 class="mb-2 text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Actualizar Perfil Requerido
                    </h1>

                    <div v-if="props.periodoActivo" class="mb-4 flex justify-center">
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                            {{ props.periodoActivo.nombre }}
                        </span>
                    </div>

                    <p class="mb-6 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        Tu periodo académico ha cambiado y necesitamos que actualices tus datos
                        personales para continuar usando el sistema. Por favor completa tu perfil
                        antes de acceder a las demás funcionalidades.
                    </p>

                    <Button size="lg" @click="irAlWizard" class="gap-2">
                        <ClipboardEdit class="h-4 w-4" />
                        Actualizar mi Perfil
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
