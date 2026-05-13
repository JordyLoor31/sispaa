<script setup lang="ts">
import { SelectLabel as SelectLabelPrimitive, useForwardPropsEmits, type SelectLabelProps } from 'radix-vue';
import { cn } from '@/lib/utils';

interface Props extends SelectLabelProps {
    class?: string;
}

const props = defineProps<Props>();
const emits = defineEmits();

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
    <SelectLabelPrimitive
        v-bind="forwarded"
        :class="cn('py-1.5 pl-8 pr-2 text-sm font-semibold', props.class)"
    >
        <slot />
    </SelectLabelPrimitive>
</template>
