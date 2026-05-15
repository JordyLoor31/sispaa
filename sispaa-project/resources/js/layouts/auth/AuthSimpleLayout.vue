<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center bg-[var(--sispaa-surface)] p-6 md:p-10">
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-6 rounded-xl bg-[var(--sispaa-background)] p-6 md:p-8 shadow-sm border border-[var(--sispaa-text)]/10">
                <div class="flex flex-col items-center gap-2">
                    <Link :href="route('home')" class="flex flex-col items-center gap-2 font-medium">
                        <div class="mb-1 flex h-10 w-10 items-center justify-center rounded-md bg-[var(--sispaa-primary)]/10">
                            <AppLogoIcon class="size-6 text-[var(--sispaa-primary)]" />
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-1 text-center">
                        <h1 class="text-xl font-bold text-[var(--sispaa-text)]">{{ title }}</h1>
                        <p class="text-center text-sm text-[var(--sispaa-text)]/70">{{ description }}</p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
