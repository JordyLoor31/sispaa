<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed, type HTMLAttributes } from 'vue';
import { useFormField } from './useFormField';

const props = defineProps<{ class?: HTMLAttributes['class'] }>();

const { formDescriptionId } = useFormField();

const delegatedProps = computed(() => props);
</script>

<template>
    <p :id="formDescriptionId" :class="cn('text-sm text-muted-foreground', delegatedProps.class)">
        <slot />
    </p>
</template>
