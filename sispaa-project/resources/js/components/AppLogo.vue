<script setup lang="ts">
import { GraduationCap } from 'lucide-vue-next';

interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <div
        class="flex aspect-square size-9 shrink-0 items-center justify-center rounded-md"
        :style="{ backgroundColor: 'var(--sispaa-primary)', color: 'var(--sispaa-background)' }"
    >
        <GraduationCap class="size-7" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm group-data-[collapsible=icon]:hidden">
        <span class="mb-0.5 truncate font-semibold leading-none">SISPAA</span>
    </div>
</template>
