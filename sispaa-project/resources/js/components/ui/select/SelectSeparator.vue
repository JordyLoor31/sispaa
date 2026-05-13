<script setup lang="ts">
import { SelectSeparator as SelectSeparatorPrimitive, useForwardPropsEmits, type SelectSeparatorProps } from 'radix-vue';
import { cn } from '@/lib/utils';

interface Props extends SelectSeparatorProps {
    class?: string;
}

const props = defineProps<Props>();
const emits = defineEmits();

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
    <SelectSeparatorPrimitive v-bind="forwarded" :class="cn('-mx-1 my-1 h-px bg-muted', props.class)" />
</template>
