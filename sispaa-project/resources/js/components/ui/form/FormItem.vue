<script setup lang="ts">
import { cn } from '@/lib/utils';
import { useId, type HTMLAttributes } from 'vue';
import { provide, computed } from 'vue';
import { FORM_ITEM_INJECTION_KEY } from './injectionKeys';

const props = defineProps<{ class?: HTMLAttributes['class'] }>();

const id = useId();
provide(FORM_ITEM_INJECTION_KEY, id);

const delegatedProps = computed(() => props);
</script>

<template>
    <div :class="cn('space-y-2', delegatedProps.class)">
        <slot />
    </div>
</template>
