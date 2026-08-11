<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner';
import { toast } from 'vue-sonner';
import 'vue-sonner/style.css';
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface Flash {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
}

const page = usePage();

// Único punto global que convierte los flash de Inertia en toasts de sonner.
// Cada tipo recibe su color: success verde, error rojo, warning amarillo e
// info azul. Se monta en app.ts, así que aplica a todas las vistas.
watch(
    () => page.props.flash as Flash,
    (flash) => {
        if (!flash) return;
        if (flash.success) toast.success(flash.success);
        if (flash.error) toast.error(flash.error);
        if (flash.warning) toast.warning(flash.warning);
        if (flash.info) toast.info(flash.info);
    },
    { deep: true, immediate: true },
);
</script>

<template>
    <Toaster position="top-center" richColors />
</template>
