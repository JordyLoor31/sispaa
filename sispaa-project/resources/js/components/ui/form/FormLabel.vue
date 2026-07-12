<script setup lang="ts">
import Label from '@/components/ui/label/Label.vue';
import { cn } from '@/lib/utils';
import { computed, type HTMLAttributes } from 'vue';
import { useFormField } from './useFormField';

const props = defineProps<{ class?: HTMLAttributes['class'] }>();

const { error, formItemId } = useFormField();

const delegatedProps = computed(() => props);
</script>

<template>
    <Label
        :class="cn(error && 'text-destructive', delegatedProps.class)"
        :for="formItemId"
    >
        <slot />
    </Label>
</template>
