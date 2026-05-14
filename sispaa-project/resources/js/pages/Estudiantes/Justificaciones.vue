<template>
  <Head title="Justificaciones" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
      <h1 class="text-2xl font-semibold">Justificaciones</h1>
      <p class="mt-2 text-sm text-muted-foreground">Registro y revisión de justificaciones de inasistencia.</p>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Estudiantes',
    href: '/estudiantes',
  },
  {
    title: 'Justificaciones',
    href: '/estudiantes/justificaciones',
  },
];
</script>
