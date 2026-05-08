<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="26"
        height="26"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="lucide lucide-user-round-key-icon lucide-user-round-key"
    >
        <path d="M19 11v6" />
        <path d="M19 13h2" />
        <path d="M2 21a8 8 0 0 1 12.868-6.349" />
        <circle cx="10" cy="8" r="5" />
        <circle cx="19" cy="19" r="2" />
    </svg>
</template>
