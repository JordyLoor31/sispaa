<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed, type HTMLAttributes } from 'vue';
import { useFormField } from './useFormField';

const props = defineProps<{ class?: HTMLAttributes['class'] }>();

const { error, formMessageId } = useFormField();

const delegatedProps = computed(() => props);

const body = computed(() => (error?.value ? error.value : ''));
</script>

<template>
    <p v-if="body" :id="formMessageId" :class="cn('text-sm font-medium text-destructive', delegatedProps.class)">
        {{ body }}
    </p>
</template>
