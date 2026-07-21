<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Acerca de',
        href: '/settings/about',
    },
];

const teamMembers = [
    {
        name: 'Jesús Bosada',
        email: 'jesusbosada2003@gmail.com'
    },
    {
        name: 'Jordy Loor',
        email: 'loorjordy62@gmail.com'
    },
    {
        name: 'Douglas Parrales',
        email: 'douglas.app.26@gmail.com'
    },
    {
        name: 'José Santos',
        email: 'josetoala623@gmail.com'
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Acerca de" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Acerca de" description="Equipo de desarrollo de la aplicación" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="(member, index) in teamMembers"
                        :key="index"
                        class="flex flex-col space-y-1 p-4 rounded-lg border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900 shadow-sm"
                    >
                        <span class="font-semibold text-neutral-900 dark:text-neutral-100">{{ member.name }}</span>
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ member.email }}</span>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
