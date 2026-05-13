<script setup lang="ts">
import { SelectItemText as SelectItemTextPrimitive, useForwardPropsEmits } from 'radix-vue';

const props = defineProps();
const emits = defineEmits();

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
    <SelectItemTextPrimitive v-bind="forwarded">
        <slot />
    </SelectItemTextPrimitive>
</template>
