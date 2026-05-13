<script setup lang="ts">
import { SelectGroup as SelectGroupPrimitive, useForwardPropsEmits, type SelectGroupProps } from 'radix-vue';

const props = defineProps<SelectGroupProps>();
const emits = defineEmits();

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
    <SelectGroupPrimitive v-bind="forwarded">
        <slot />
    </SelectGroupPrimitive>
</template>
